<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * Functions for execution feature (add test results) 
 * Legacy code (party covered by classes now)
 *
 * @package     TestLink
 * @author      Martin Havlat
 * @copyright   2005-2015, TestLink community 
 * @filesource  exec.inc.php
 * @link        http://www.testlink.org/
 *
 * @internal revisions
 * @since 1.9.14
 * 
 *
 **/

require_once('common.php');
require_once('attachments.inc.php');


/** 
 * Building the dropdown box of results filter
 * 
 * @return array map of 'status_code' => localized string
 **/
function createResultsMenu()
{
  $resultsCfg = config_get('results');
  
  // Fixed values, that has to be added always
  $my_all = isset($resultsCfg['status_label']['all'])?$resultsCfg['status_label']['all']:'';
  $menu_data[$resultsCfg['status_code']['all']] = $my_all;
  $menu_data[$resultsCfg['status_code']['not_run']] = lang_get($resultsCfg['status_label']['not_run']);
  
  // loop over status for user interface, because these are the statuses
  // user can assign while executing test cases
  foreach($resultsCfg['status_label_for_exec_ui'] as $verbose_status => $status_label)
  {
    $code = $resultsCfg['status_code'][$verbose_status];
    $menu_data[$code] = lang_get($status_label); 
  }
  
  return $menu_data;
}
  
  
/**
 * write execution result to DB
 * 
 * @param resource &$db reference to database handler
 * @param obj &$exec_signature object with tproject_id,tplan_id,build_id,platform_id,user_id
 * 
 * @internal revisions
 * 
 */
function write_execution(&$db,&$exec_signature,&$exec_data,&$issueTracker)
{
  static $docRepo;

  if(is_null($docRepo))
  {
    $docRepo = tlAttachmentRepository::create($db);
  }  

  $executions_table = DB_TABLE_PREFIX . 'executions';
  $resultsCfg = config_get('results');
  $execCfg = config_get('exec_cfg');

  $db_now = $db->db_now();
  $cfield_mgr = New cfield_mgr($db);
  $cf_prefix = $cfield_mgr->get_name_prefix();
  $len_cfp = tlStringLen($cf_prefix);
  $cf_nodeid_pos = 4;
  $bulk_notes = '';
  
  $ENABLED = 1;
  $cf_map = $cfield_mgr->get_linked_cfields_at_execution($exec_signature->tproject_id,$ENABLED,'testcase');
  $has_custom_fields = is_null($cf_map) ? 0 : 1;
  
  // extract custom fields id.
  $map_nodeid_array_cfnames=null;
  foreach($exec_data as $input_name => $value)
  {
    if( strncmp($input_name,$cf_prefix,$len_cfp) == 0 )
    {
      $dummy=explode('_',$input_name);
      $map_nodeid_array_cfnames[$dummy[$cf_nodeid_pos]][]=$input_name;
    } 
  }
  
  if( isset($exec_data['do_bulk_save']) )
  {
    // create structure to use common algoritm
    $item2loop= $exec_data['status'];
    $is_bulk_save=1;
    $bulk_notes = $db->prepare_string(trim($exec_data['bulk_exec_notes']));   
    $execStatusKey = 'status';

  } 
  else
  {
    $item2loop= $exec_data['save_results'];
    $is_bulk_save=0;
    $execStatusKey = 'statusSingle';
  }

  $addIssueOp = null;
  foreach ( $item2loop as $tcversion_id => $val)
  {
    $tcase_id=$exec_data['tc_version'][$tcversion_id];
    $current_status = $exec_data[$execStatusKey][$tcversion_id];
    $version_number=$exec_data['version_number'][$tcversion_id];;
    $has_been_executed = ($current_status != $resultsCfg['status_code']['not_run'] ? TRUE : FALSE);
    if($has_been_executed)
    { 
      
      $my_notes = $is_bulk_save ? $bulk_notes : $db->prepare_string(trim($exec_data['notes'][$tcversion_id])); 

      $sql = "INSERT INTO " . $db->get_table($executions_table) .
             "(build_id,tester_id,status,testplan_id,tcversion_id," .
             " execution_ts,notes,tcversion_number,platform_id,execution_duration)".
             " VALUES ( {$exec_signature->build_id}, {$exec_signature->user_id}, '{$exec_data[$execStatusKey][$tcversion_id]}',".
             "{$exec_signature->tplan_id}, {$tcversion_id},{$db_now},'{$my_notes}'," .
             "{$version_number},{$exec_signature->platform_id}";

      if(trim($exec_data['execution_duration']) == '')
      {
        $dura = 'NULL ';  
      } 
      else
      {
        $dura = floatval($exec_data['execution_duration']);
      }  

      $sql .= ',' .$dura . ")";


      $db->exec_query($sql);    
      
      
      $execution_id = $db->insert_id($executions_table);
      $execSet[$tcversion_id] = $execution_id;
      
      if( $has_custom_fields )
      {
        // test useful when doing bulk update, because some type of custom fields
        // like checkbox can not exist on exec_data. => why ??
        //
        $hash_cf = null;
        $access_key = $is_bulk_save ? 0 : $tcase_id;
        if( isset($map_nodeid_array_cfnames[$access_key]) )
        { 
          foreach($map_nodeid_array_cfnames[$access_key] as $cf_v)
          {
            $hash_cf[$cf_v]=$exec_data[$cf_v];
          }  
        }                          
        $cfield_mgr->execution_values_to_db($hash_cf,$tcversion_id, $execution_id, $exec_signature->tplan_id,$cf_map);
      }               

      $hasMoreData = new stdClass();
      $hasMoreData->step_notes = isset($exec_data['step_notes']);
      
      /*new code by chenye 20160602
       * if 'step_status' is null,get default as executions status;
       * 
       */
      if(isset($exec_data['step_status'])==false){
          $hasMoreData->step_status = $exec_data['step_status'];
      }
      else{
          $hasMoreData->step_status =$current_status;
      }
      
      $hasMoreData->nike = $execCfg->steps_exec && 
                           ($hasMoreData->step_notes || $hasMoreData->step_status);
        
      if( $hasMoreData->nike )
      {
        $target = DB_TABLE_PREFIX . 'execution_tcsteps';
        $key2loop = array_keys($exec_data['step_notes']);
        foreach( $key2loop as $step_id )
        {
            /*new code by chenye 20160602
             * if no choice'step_status' get default as executions status;
             *
             */
          if($exec_data['step_status'][$step_id]=="n"){
                 
                $exec_data['step_status'][$step_id]=$current_status;
          }
            
          $doIt = (!is_null($exec_data['step_notes'][$step_id]) && 
                   trim($exec_data['step_notes'][$step_id]) != '') || 
                  $exec_data['step_status'][$step_id] != $resultsCfg['status_code']['not_run'];

          if( $doIt )
          {
            $sql = " INSERT INTO " . $db->get_table($target) . " (execution_id,tcstep_id,notes";
            $values = " VALUES ( {$execution_id}, {$step_id}," . 
                      "'" . $db->prepare_string($exec_data['step_notes'][$step_id]) . "'";

            $status = strtolower(trim($exec_data['step_status'][$step_id]));
            $status = $status[0];
            if( $status != $resultsCfg['status_code']['not_run'] )
            {
              $sql .= ",status";
              $values .= ",'" . $db->prepare_string($status) . "'";
            }  
            $sql .= ") " . $values . ")";
            $db->exec_query($sql);

            $execution_tcsteps_id = $db->insert_id($target);

            // NOW MANAGE attachments
            if( isset($_FILES['uploadedFile']['name'][$step_id]) && 
                !is_null($_FILES['uploadedFile']['name'][$step_id])) 
            {
              // May be we have enabled MULTIPLE on file upload
              if( is_array($_FILES['uploadedFile']['name'][$step_id])) 
              {
                $curly = count($_FILES['uploadedFile']['name'][$step_id]);
                for($moe=0; $moe < $curly; $moe++)
                {
                  $fSize = isset($_FILES['uploadedFile']['size'][$step_id][$moe]) ? 
                           $_FILES['uploadedFile']['size'][$step_id][$moe] : 0;

                  $fTmpName = isset($_FILES['uploadedFile']['tmp_name'][$step_id][$moe]) ? 
                              $_FILES['uploadedFile']['tmp_name'][$step_id][$moe] : '';

                  if ($fSize && $fTmpName != "")
                  {
                    $fk2loop = array_keys($_FILES['uploadedFile']);
                    foreach($fk2loop as $tk)
                    {
                      $fInfo[$tk] = $_FILES['uploadedFile'][$tk][$step_id][$moe];
                    }  
                    $uploaded = $docRepo->insertAttachment($execution_tcsteps_id,$target,'',$fInfo);
                  }
                }  
              } 
              else
              {
                $fSize = isset($_FILES['uploadedFile']['size'][$step_id]) ? $_FILES['uploadedFile']['size'][$step_id] : 0;
                $fTmpName = isset($_FILES['uploadedFile']['tmp_name'][$step_id]) ? 
                            $_FILES['uploadedFile']['tmp_name'][$step_id] : '';

                if ($fSize && $fTmpName != "")
                {
                  $fk2loop = array_keys($_FILES['uploadedFile']);
                  foreach($fk2loop as $tk)
                  {
                    $fInfo[$tk] = $_FILES['uploadedFile'][$tk][$step_id];
                  }  
                  $uploaded = $docRepo->insertAttachment($execution_tcsteps_id,$target,'',$fInfo);
                }
              } 
              
            }
          }         
        }  
      }  

      if(isset($exec_data['createIssue']) && !is_null($issueTracker) && method_exists($issueTracker,'addIssue'))
      {
        $execContext = new stdClass();
        $execContext->exec_id = $execution_id;
        $execContext->tcversion_id = $tcversion_id;
        $execContext->user = $exec_signature->user;
        $execContext->basehref = $exec_signature->basehref;
        $execContext->tplan_apikey = $exec_signature->tplan_apikey;

        if(property_exists($exec_signature,'bug_summary'))
        {
          $execContext->bug_summary = $exec_signature->bug_summary;
        }  
        if(property_exists($exec_signature,'bug_notes'))
        {
          $execContext->bug_notes = $exec_signature->bug_notes;
        }  
       
        if(property_exists($exec_signature,'issueType'))
        {
          $execContext->issueType = $exec_signature->issueType;
        }  

        if(property_exists($exec_signature,'issuePriority'))
        {
          $execContext->issuePriority = $exec_signature->issuePriority;
        }  

        if(property_exists($exec_signature,'artifactVersion'))
        {
          $execContext->artifactVersion = $exec_signature->artifactVersion;
        }  

        if(property_exists($exec_signature,'artifactComponent'))
        {
          $execContext->artifactComponent = $exec_signature->artifactComponent;
        }          

        $addIssueOp = addIssue($db,$execContext,$issueTracker);
      }  
    }
  }

  return array($execSet,$addIssueOp);
}

/**
 * DELETE + INSERT => this way we will not add duplicates
 *
 */
function write_execution_bug(&$db,$exec_id, $bug_id,$just_delete=false)
{
  $execution_bugs = DB_TABLE_PREFIX . 'execution_bugs';
  
  // Instead of Check if record exists before inserting, do delete + insert
  $prep_bug_id = $db->prepare_string($bug_id);
  
  $sql = "DELETE FROM " . $db->get_table($execution_bugs) . " WHERE execution_id={$exec_id} " .
         "AND bug_id='" . $prep_bug_id ."'";
  $result = $db->exec_query($sql);
  
  
  if(!$just_delete)
  {
    $sql = "INSERT INTO " . $db->get_table($execution_bugs) . " (execution_id,bug_id) " .
           "VALUES({$exec_id},'" . $prep_bug_id . "')";
    $result = $db->exec_query($sql);         
  }
  
  return $result ? 1 : 0;
}


/**
 * get data about bug from external tool
 * 
 * @param resource &$db reference to database handler
 * @param object &$bug_interface reference to instance of bugTracker class
 * @param integer $execution_id Identifier of execution record
 * 
 * @return array list of 'bug_id' with values: build_name,link_to_bts,isResolved
 */
function get_bugs_for_exec(&$db,&$bug_interface,$execution_id,$raw = null)
{
  $tables = tlObjectWithDB::getDBTables(array('executions','execution_bugs','builds'));
  $bug_list=array();
  $cfg = config_get('exec_cfg');

  $debugMsg = 'FILE:: ' . __FILE__ . ' :: FUNCTION:: ' . __FUNCTION__;
  if( is_object($bug_interface) )
  {
    
    $sql =  "/* $debugMsg */ SELECT execution_id,bug_id,builds.name AS build_name " .
//             " FROM {$tables['execution_bugs']}, {$tables['executions']} executions, " .
//             " {$tables['builds']} builds ".
            " FROM ".$db->get_table('execution_bugs').", ".$db->get_table('executions')." executions, " .
            " ".$db->get_table('builds')." builds ".
            " WHERE execution_id = " . intval($execution_id) .
            " AND   execution_id = executions.id " .
            " AND   executions.build_id = builds.id " .
            " {$cfg->bugs_order_clause}";

    $map = $db->get_recordset($sql);
    if( !is_null($map) )
    {   
      $opt['raw'] = $raw;
      $addAttr = !is_null($raw);
      foreach($map as $elem)
      {
        if(!isset($bug_list[$elem['bug_id']]))
        {
          $dummy = $bug_interface->buildViewBugLink($elem['bug_id'],$opt);
          $bug_list[$elem['bug_id']]['link_to_bts'] = $dummy->link;
          $bug_list[$elem['bug_id']]['build_name'] = $elem['build_name'];
          $bug_list[$elem['bug_id']]['isResolved'] = $dummy->isResolved;
        }  
        if($addAttr)
        {
          foreach($raw as $kj)
          {
          	if( property_exists($dummy,$kj) )
          	{
              $bug_list[$elem['bug_id']][$kj] = $dummy->$kj;
          	}
          } 
        }       
        unset($dummy);
      }
    }
  }

  return($bug_list);
}


/**
 * get data about one test execution
 * 
 * @param resource &$db reference to database handler
 * @param datatype $execution_id
 * 
 * @return array all values of executions DB table in format field=>value
 */
function get_execution(&$dbHandler,$execution_id,$opt=null)
{
  $my = array('options' => array('output' => 'raw'));
  $my['options'] = array_merge($my['options'], (array)$opt);
  $tables = tlObjectWithDB::getDBTables(array('executions','nodes_hierarchy','builds','platforms'));
  
  $safe_id = intval($execution_id); 
  switch($my['options']['output'])
  {
    case 'audit':
      $sql = " SELECT B.name AS build_name,PLAT.name AS platform_name, " .
             " NH_TPLAN.name AS testplan_name, NH_TC.name AS testcase_name, " .
             " E.id AS exec_id, NH_TPROJ.name AS testproject_name " . 
//            " FROM {$tables['executions']} E " .
//            " JOIN {$tables['builds']} B ON B.id = E.build_id " . 
//            " JOIN {$tables['platforms']} PLAT ON PLAT.id = E.platform_id " . 
//            " JOIN {$tables['nodes_hierarchy']} NH_TPLAN ON NH_TPLAN.id = E.testplan_id " . 
//            " JOIN {$tables['nodes_hierarchy']} NH_TCV ON NH_TCV.id = E.tcversion_id " . 
//            " JOIN {$tables['nodes_hierarchy']} NH_TC ON NH_TC.id = NH_TCV.parent_id " . 
//            " JOIN {$tables['nodes_hierarchy']} NH_TPROJ ON NH_TPROJ.id = NH_TPLAN.parent_id " . 
           " FROM ".$dbHandler->get_table('executions')." E " .
           " JOIN ".$dbHandler->get_table('builds')." B ON B.id = E.build_id " .
           " JOIN ".$dbHandler->get_table('platforms')." PLAT ON PLAT.id = E.platform_id " .
           " JOIN ".$dbHandler->get_table('nodes_hierarchy')." NH_TPLAN ON NH_TPLAN.id = E.testplan_id " .
           " JOIN ".$dbHandler->get_table('nodes_hierarchy')." NH_TCV ON NH_TCV.id = E.tcversion_id " .
           " JOIN ".$dbHandler->get_table('nodes_hierarchy')." NH_TC ON NH_TC.id = NH_TCV.parent_id " .
           " JOIN ".$dbHandler->get_table('nodes_hierarchy')." NH_TPROJ ON NH_TPROJ.id = NH_TPLAN.parent_id " .
           " WHERE E.id = " . $safe_id;
    break;    
    
    case 'raw':
    default:
//       $sql = " SELECT * FROM {$tables['executions']} E ".
          $sql = " SELECT * FROM ".$dbHandler->get_table('executions')." E ".
           " WHERE E.id = " . $safe_id;
    break;    
  } 
  tLog(__FUNCTION__ . ':' . $sql,"DEBUG");
  $rs = $dbHandler->get_recordset($sql);
  return($rs);
}

/** 
 * delete one test execution from database (include child data and relations)
 * 
 * @param resource &$db reference to database handler
 * @param datatype $execution_id
 * 
 * @return boolean result of delete
 * 
 * @TODO delete attachments FROM DISK when are saved on Filesystem
 * @TODO run SQL as transaction if database engine allows 
 **/
function delete_execution(&$db,$exec_id)
{
  $tables = tlObjectWithDB::getDBTables(
              array('executions','execution_bugs','cfield_execution_values',
                    'execution_tcsteps','attachments'));
  
  $sid = intval($exec_id);


  // Attachments NEED special processing.
  
  // get test step exec attachments if any exists
//   $dummy = " SELECT id FROM {$tables['execution_tcsteps']} " . 
  $dummy = " SELECT id FROM ".$db->get_table('execution_tcsteps')." " .
           " WHERE execution_id = {$sid}";
  
  $rs = $db->fetchRowsIntoMap($dummy,'id');
  if(!is_null($rs))
  {
    foreach($rs as $fik => $v)
    {
      deleteAttachment($db,$fik,false);
    }  
  }  


  // execution attachments
//   $dummy = " SELECT id FROM {$tables['attachments']} " . 
  $dummy = " SELECT id FROM ".$db->get_table('attachments')." " .
           " WHERE fk_table = 'executions' AND fk_id = {$sid}";
  
  $rs = $db->fetchRowsIntoMap($dummy,'id');
  if(!is_null($rs))
  {
    foreach($rs as $fik => $v)
    {
      deleteAttachment($db,$fik,false);
    }  
  }  

  // order is CRITIC, because is DELETING ORDER => ATTENTION to Foreing Keys
  $sql = array();
  $sql[] = "DELETE FROM ".$db->get_table('execution_bugs')." WHERE execution_id = {$sid}";
  $sql[] = "DELETE FROM ".$db->get_table('cfield_execution_values')." WHERE execution_id = {$sid}"; 
  $sql[] = "DELETE FROM ".$db->get_table('execution_tcsteps')." WHERE execution_id = {$sid}";
  
  // This delete HAS TO BE THE LATEST, because is the PARENT
  $ldx = count($sql);
  $sql[$ldx] = "DELETE FROM ".$db->get_table('executions')." WHERE id = {$sid}";

  foreach ($sql as $the_stm)
  {
    $result = $db->exec_query($the_stm);
    if (!$result)
    {
      break;
    }
  }
  
  return $result;
}

/**
 * @param $db resource the database connecton
 * @param $execID integer the execution id whose notes should be set
 * @param $notes string the execution notes to set
 * @return unknown_type
 */
function updateExecutionNotes(&$db,$execID,$notes)
{
  $table = tlObjectWithDB::getDBTables('executions');
  $sql = "UPDATE " . $db->get_table('executions') .
         "SET notes = '" . $db->prepare_string($notes) . "' " .
         "WHERE id = " . intval($execID);
    
  return $db->exec_query($sql) ? tl::OK : tl::ERROR;     
}

/**
 * get data about bug from external tool
 * 
 * @param resource &$db reference to database handler
 * @param object &$bug_interface reference to instance of bugTracker class
 * @param integer $execution_id Identifier of execution record
 * 
 * @return array list of 'bug_id' with values: build_name,link_to_bts,isResolved
 */
function getBugsForExecutions(&$db,&$bug_interface,$execSet,$raw = null)
{
  $tables = tlObjectWithDB::getDBTables(array('executions','execution_bugs','builds'));
  $bugSet = array();
  $bugCache = array();
  $cc = 0;

  $debugMsg = 'FILE:: ' . __FILE__ . ' :: FUNCTION:: ' . __FUNCTION__;
  if( is_object($bug_interface) )
  {
    $sql =  "/* $debugMsg */ SELECT EB.execution_id,EB.bug_id,B.name AS build_name " .
//             " FROM {$tables['execution_bugs']} EB " . 
//             " JOIN {$tables['executions']} E ON E.id = EB.execution_id " .
//             " JOIN {$tables['builds']} B  ON B.id = E.build_id " .
            " FROM ".$db->get_table('execution_bugs')." EB " .
            " JOIN ".$db->get_table('executions')." E ON E.id = EB.execution_id " .
            " JOIN ".$db->get_table('builds')." B  ON B.id = E.build_id " .
            " WHERE EB.execution_id IN (" . implode(',',$execSet) . ")" .
            " ORDER BY B.name,EB.bug_id";

    $rs = $db->fetchMapRowsIntoMap($sql,'execution_id','bug_id');

    if( !is_null($rs) )
    {   
      $opt['raw'] = $raw;
      $addAttr = !is_null($raw);
      $cc = 0;
      foreach($rs as $key => $bugElem)
      {
        foreach($bugElem as $bugID => $elem)
        {
          if(!isset($bugCache[$elem['bug_id']]))
          {
            $dummy = $bug_interface->buildViewBugLink($elem['bug_id'],$opt);
            $bugCache[$elem['bug_id']]['link_to_bts'] = $dummy->link;
            $bugCache[$elem['bug_id']]['build_name'] = $elem['build_name'];
            $bugCache[$elem['bug_id']]['isResolved'] = $dummy->isResolved;
            if($addAttr)
            {
              foreach($raw as $kj)
              {
                if( property_exists($dummy,$kj) )
                {
                  $bugCache[$elem['bug_id']][$kj] = $dummy->$kj;
                }
              } 
            }       
          }  
          $bugSet[$key][$elem['bug_id']] = $bugCache[$elem['bug_id']];
          unset($dummy);
        }  
      }
    }
  }
  return $bugSet;
}


/**
 *
 */
function addIssue($dbHandler,$argsObj,$itsObj)
{
  $ret = array();
  $ret['status_ok'] = true;             
  $ret['msg'] = '';

  $resultsCfg = config_get('results');                      
  $tcaseMgr = new testcase($dbHandler);
  $dummy = $tcaseMgr->tree_manager->get_node_hierarchy_info($argsObj->tcversion_id);
  $auditSign = $tcaseMgr->getAuditSignature((object)array('id' => $dummy['parent_id'])); 
  $exec = current($tcaseMgr->getExecution($argsObj->exec_id,$argsObj->tcversion_id));


  $issueText = generateIssueText($dbHandler,$argsObj,$itsObj);  
  if(property_exists($argsObj,'bug_summary') && strlen(trim($argsObj->bug_summary)) != 0 )
  {
    $issueText->summary = $argsObj->bug_summary;
  }


  $opt = new stdClass();
  $opt->reporter = $argsObj->user->login;
  $p2check = array('issueType','issuePriority','issuePriority',
                   'artifactComponent','artifactVersion');
  foreach($p2check as $prop)
  {
    if(property_exists($argsObj, $prop) && !is_null($argsObj->$prop))
    {
      $opt->$prop = $argsObj->$prop;    
    }   
  }  
  $rs = $itsObj->addIssue($issueText->summary,$issueText->description,$opt); 
  
  $ret['msg'] = $rs['msg'];
  if( ($ret['status_ok'] = $rs['status_ok']) )
  {                   
    if (write_execution_bug($dbHandler,$argsObj->exec_id, $rs['id']))
    {
      logAuditEvent(TLS("audit_executionbug_added",$rs['id']),"CREATE",$argsObj->exec_id,"executions");
    }
  }

  return $ret;
}




/**
 * copy issues from execution to another execution
 *
 */
function copyIssues(&$dbHandler,$source,$dest)
{
  $debugMsg = 'FILE:: ' . __FILE__ . ' :: FUNCTION:: ' . __FUNCTION__;

  $tables = tlObjectWithDB::getDBTables(array('execution_bugs'));
  $blist=array();

//   $sql = "/* $debugMsg */ SELECT bug_id FROM {$tables['execution_bugs']} " .
  $sql = "/* $debugMsg */ SELECT bug_id FROM ".$dbHandler->get_table('execution_bugs')." " .
         " WHERE execution_id = " . intval($source);

  $linkedIssues = $dbHandler->fetchRowsIntoMap($sql,'bug_id');
  if( !is_null($linkedIssues) )
  {  
    $idSet = array_keys($linkedIssues);
    $safeDest = intval($dest);

    $blist = implode("','", $idSet);
    $sql = "/* $debugMsg */ DELETE FROM ".$dbHandler->get_table('execution_bugs')." " . 
           " WHERE execution_id=" . $safeDest .
           " AND bug_id IN ('" . $blist ."')";
  
    $dbHandler->exec_query($sql);
  
    $dummy = array();
    foreach($idSet as $bi)
    {
      $dummy[] = "({$safeDest},'{$bi}')";
    }
    $sql = "INSERT INTO ".$dbHandler->get_table('execution_bugs')." (execution_id,bug_id) VALUES " .
           implode(",", $dummy);

    $dbHandler->exec_query($sql);         
  }
} 



/**
 *
 */
function generateIssueText($dbHandler,$argsObj,$itsObj)
{
  $ret = new stdClass();

  $opOK = false;             
  $msg = '';
  $resultsCfg = config_get('results');                      
  $tcaseMgr = new testcase($dbHandler);
  $exec = current($tcaseMgr->getExecution($argsObj->exec_id,$argsObj->tcversion_id));

  $dummy = $tcaseMgr->tree_manager->get_node_hierarchy_info($argsObj->tcversion_id);
  $ret->auditSign = $tcaseMgr->getAuditSignature((object)array('id' => $dummy['parent_id'])); 


  $dummy = $exec['status'];
  if( isset($resultsCfg['code_status'][$exec['status']]) )
  {
    $dummy = $resultsCfg['code_status'][$exec['status']];  
  }                         
  $exec['statusVerbose'] = sprintf(lang_get('issue_exec_result'),$dummy);
  
  unset($tcaseMgr);

  $platform_identity = '';
  if($exec['platform_id'] > 0)
  {
    $platform_identity = sprintf(lang_get('issue_platform') ,$exec['platform_name']); 
  }

  if(property_exists($argsObj, 'bug_notes'))
  {  
    // parse 
    $tags = array('%%EXECID%%','%%TESTER%%','%%TESTPLAN%%','%%PLATFORM%%',
                  '%%BUILD%%', '%%EXECTS%%','%%EXECSTATUS%%','%%EXECNOTES%%'); 
    $values = array(sprintf(lang_get('issue_exec_id'),$argsObj->exec_id),
                    sprintf(lang_get('issue_tester'),$exec['tester_login']),
                    sprintf(lang_get('issue_tplan'),$exec['testplan_name']),
                    $platform_identity,
                    sprintf(lang_get('issue_build'),$exec['build_name']),
                    sprintf(lang_get('execution_ts_iso'),$exec['execution_ts']),
                    $exec['statusVerbose'],
                    $exec['execution_notes']);


 
    $ret->description = str_replace($tags,$values,$argsObj->bug_notes);
   
    // @since 1.9.14
    // %%EXECATT:1%% => lnl.php?type=file&id=1&apikey=gfhdgjfgdsjgfjsg
    $target['value'] = '%%EXECATT:';
    $target['len'] = strlen($target['value']);
    $doIt = true;
    $url2use = $argsObj->basehref . 'lnl.php?type=file&id=';

    while($doIt)
    {
      $mx = strpos($ret->description,$target['value']);
      if( ($doIt = !($mx === FALSE)) )
      {
        $offset = $mx+$target['len'];
        $cx = strpos($ret->description,'%%',$offset);
        if($cx === FALSE)
        {
          // chaos! => abort
          $doIt = false;
          break;
        }  
        $old = substr($ret->description,$mx,$cx-$mx+2);  // 2 is MAGIC!!!
        $new = str_replace($target['value'],$url2use,$old);
        $new = str_replace('%%','&apikey=' . $argsObj->tplan_apikey,$new);
        $ret->description = str_replace($old,$new,$ret->description);
      }
    } 
  }
  else
  {
    $ret->description = sprintf(lang_get('issue_generated_description'),
                                $argsObj->exec_id,$exec['tester_login'],$exec['testplan_name']);
    
    $ret->description .= ($platform_identity != '') ? $platform_identity . "\n" : '';
    $ret->description .= sprintf(lang_get('issue_build') . "\n" . lang_get('execution_ts_iso') . "\n",
                                 $exec['build_name'],$exec['execution_ts']); 
    $ret->description .= "\n" . $exec['statusVerbose'] . "\n\n" . $exec['execution_notes'];
 
  }  

  $ret->timestamp = sprintf(lang_get('execution_ts_iso'),$exec['execution_ts']);
  $ret->summary = $ret->auditSign . ' - ' . $ret->timestamp;
  if(property_exists($argsObj,'bug_summary') && strlen(trim($argsObj->bug_summary)) != 0 )
  {
    $ret->summary = $argsObj->bug_summary;
  }


  return $ret;

}

/**
 *
 */
function getIssueTrackerMetaData($itsObj)
{
 
  if(!isset($_SESSION['issueTrackerCfg']) || !$_SESSION['issueTrackerCfg'][$itsObj->name])
  {
    $ret = array();
    $ret['issueTypes'] = null;
    $ret['components'] = null;
    $ret['priorities'] = null;
    $ret['versions'] = null;

    $target = array('issueTypes' => 'getIssueTypesForHTMLSelect',
                    'priorities' => 'getPrioritiesForHTMLSelect',
                    'versions' => 'getVersionsForHTMLSelect',
                    'components' => 'getComponentsForHTMLSelect');

    foreach($target as $key => $worker)
    {
      if(method_exists($itsObj, $worker) )
      {
        $ret[$key] = $itsObj->$worker();
      }  
    }

    $_SESSION['issueTrackerCfg'][$itsObj->name]=$ret;      
  }  
    
  return $_SESSION['issueTrackerCfg'][$itsObj->name];
}

