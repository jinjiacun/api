<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 * 
 * @filesource  testproject.class.php
 * @package     TestLink
 * @copyright   2005-2015, TestLink community 
 * @link        http://testlink.sourceforge.net/
 *
 * @internal revisions
 * @since 1.9.14
 * 
 **/

/** related functions */ 
require_once('attachments.inc.php');
require_once('schema_mgr.class.php');

/**
 * class is responsible to get project related data and CRUD test project
 * @package   TestLink
 */
class testproject extends tlObjectWithAttachments
{
  const RECURSIVE_MODE = true;
  const EXCLUDE_TESTCASES = true;
  const INCLUDE_TESTCASES = false;
  const TESTCASE_PREFIX_MAXLEN = 16; // must be changed if field dimension changes
  const GET_NOT_EMPTY_REQSPEC = 1;
  const GET_EMPTY_REQSPEC = 0;
  
  /** @var database handler */
  var $db;
  var $tree_manager;
  var $cfield_mgr;

  // Node Types (NT)
  var $nt2exclude=array('testplan' => 'exclude_me','requirement_spec'=> 'exclude_me','requirement'=> 'exclude_me');

  var $nt2exclude_children=array('testcase' => 'exclude_my_children','requirement_spec'=> 'exclude_my_children');

  var $debugMsg;
  
  var $schema_mgr;

  /** 
   * Class constructor
   * 
   * @param resource &$db reference to database handler
   */
  function __construct(&$db)
  {
    $this->db = &$db;
    $this->tree_manager = new tree($this->db);
    $this->cfield_mgr=new cfield_mgr($this->db);
    $this->debugMsg = 'Class:' . __CLASS__ . ' - Method: ';
    tlObjectWithAttachments::__construct($this->db,'nodes_hierarchy');
    $this->object_table = $this->tables['testprojects'];
    $this->schema_mgr = new schema_manager($this->db);
  }

/**
 * Create a new Test project
 * 
 * @param string $name Name of project
 * @param string $color value according to CSS color definition
 * @param string $notes project description (HTML text)
 * @param array $options project features/options
 *         bolean keys: inventoryEnabled, automationEnabled, 
 *         testPriorityEnabled, requirementsEnabled 
 * @param boolean $active [1,0] optional
 * @param string $tcasePrefix [''] 
 * @param boolean $is_public [1,0] optional
 *
 * @return integer test project id or 0 (if fails)
 *
 * @internal revisions
 * 
 */
function create($item,$opt=null)
{
  $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
  $my['opt'] = array('doChecks' => false, 'setSessionProject' => true);
  $my['opt'] = array_merge($my['opt'],(array)$opt);
  
  if (isset($item->options))
  {
      $serOptions = serialize($item->options);
  }
  else 
  {
      $serOptions = "N;";
  }

  try 
  {
    $tcPrefix = $this->formatTcPrefix($item->prefix); // will truncate prefix is len() > limit

    // mandatory checks
    if(strlen($item->name)==0)
    {
      throw new Exception('Empty name is not allowed');      
    }  
   
    if($my['opt']['doChecks'])
    {
      $check = $this->checkNameSintax($item->name);
      if($check['status_ok'])
      {  
        $check = $this->checkNameExistence($item->name);
      }
      if($check['status_ok'])
      {  
        $check = $this->checkTestCasePrefixExistence($tcPrefix);
      }

      if(!$check['status_ok'])
      {
        throw new Exception($check['msg']);  
      }  
    }
  }   
  catch (Exception $e) 
  {
    throw $e;  // rethrow
  }

  // Create API KEY 64 bit long
  $api_key = md5(rand()) . md5(rand());
  
  //modify by zhouzhaoxin 20161107 for divide schema for projects, first create project
  $sql = " INSERT INTO " . $this->db->get_table($this->object_table) . " (color," .
         " options,notes,active,is_public,prefix,api_key,name) " .
         " VALUES ('" . $this->db->prepare_string($item->color) . "','" .
                       $serOptions . "','" .
                       $this->db->prepare_string($item->notes) . "'," .
                       $item->active . "," . $item->is_public . ",'" .
                       $this->db->prepare_string($tcPrefix) . "','" .
                       $this->db->prepare_string($api_key) . "','" .
                       $item->name . "')";
  $result = $this->db->exec_query($sql);
  
  $evt = new stdClass();
  $evt->message = TLS("audit_testproject_created", $item->name); 
  $evt->code = "CREATE";
  $evt->source = $this->auditCfg->eventSource;
  $evt->objectType = 'testprojects';
 
  $project_id = 0;
  if ($result)
  {
      // when project created, next to create project schema and insert project first node
      $project_id = $this->db->insert_id($this->db->get_table($this->object_table));
      $schema_name = $this->schema_mgr->create_schema($project_id);
      if ($schema_name == '')
      {
          $id = 0;
          $evt->logLevel = 'ERROR';
      }
      else 
      {
          //insert root node to project
          $id = $this->tree_manager->new_root_node($item->name, $schema_name, $project_id);
          
          //update root node id info in project table for link project info
          $sql = " UPDATE " . $this->db->get_table($this->object_table) . " set node_id = " .
              $id . " where id = " . $project_id . ";";
          $result = $this->db->exec_query($sql);
          
          // set project to session if not defined (the first project) or update the current
          if (!isset($_SESSION['testprojectID']) && $my['opt']['setSessionProject'])
          {
              $this->setSessionProject($id);
          }
          $evt->logLevel = 'AUDIT';
      }
  }
  else
  {
      $project_id = 0;
      $evt->logLevel = 'ERROR';
  }
  
  $evt->objectID = $project_id;
  // var_dump($evt);
  logEvent($evt);

  return $project_id;
}

/**
 * Update Test project data in DB and (if applicable) current session data
 *
 * @param integer $id project Identifier
 * @param string $name Name of project
 * @param string $color value according to CSS color definition
 * @param string $notes project description (HTML text)
 * @param array $options project features/options
 *         bolean keys: inventoryEnabled, automationEnabled, 
 *         testPriorityEnabled, requirementsEnabled 
 * 
 * @return boolean result of DB update
 *
 * @internal
 *
 **/
function update($id, $name, $color, $notes,$options,$active=null,
                $tcasePrefix=null,$is_public=null)
{
  $status_ok=1;
  $status_msg = 'ok';
  $log_msg = 'Test project ' . $name . ' update: Ok.';
  $log_level = 'INFO';
  $safeID = intval($id);

  $add_upd='';
  if( !is_null($active) )
  {
    $add_upd .=',active=' . (intval($active) > 0 ? 1:0);
  }

  if( !is_null($is_public) )
  {
    $add_upd .=',is_public=' . (intval($is_public) > 0 ? 1:0);
  }

  if( !is_null($tcasePrefix) )
  {
    $tcprefix=$this->formatTcPrefix($tcasePrefix);
    $add_upd .=",prefix='" . $this->db->prepare_string($tcprefix) . "'" ;
  }
  $serOptions = serialize($options);

  // modify by zhouzhaoxin 20161107 to divide schema to add name info
  $sql = " UPDATE " . $this->db->get_table($this->object_table)." SET color='" . $this->db->prepare_string($color) . "', ".
         " options='" .  $serOptions . "', " .
         " name='" .  $name . "', " .
         " notes='" . $this->db->prepare_string($notes) . "' {$add_upd} " .
         " WHERE id=" . $safeID;
  $result = $this->db->exec_query($sql);

  if ($result)
  {
    // update related node
    // modify by zhouzhaoxin 20161107 to divide schema
    $schema_name = $this->schema_mgr->get_schema($safeID);
    
    $sql = "UPDATE " . $schema_name . ".nodes_hierarchy" . " SET name='" .
           $this->db->prepare_string($name) .  "' WHERE node_type_id = " . "1";
    $result = $this->db->exec_query($sql);
  }

  if ($result)
  {
    // update session data
    $this->setSessionProject($safeID);
  }
  else
  {
    $status_msg = 'Update FAILED!';
    $status_ok = 0;
    $log_level ='ERROR';
    $log_msg = $status_msg;
  }

  tLog($log_msg,$log_level);
  return ($status_ok);
}

/**
 * Set session data related to a Test project
 * 
 * @param integer $projectId Project ID; zero causes unset data
 */
public function setSessionProject($projectId)
{
  $tproject_info = null;
  
  if ($projectId)
  {
    $tproject_info = $this->get_by_id($projectId);
  }

  if ($tproject_info)
  {
    $_SESSION['testprojectID'] = $tproject_info['id'];
    $_SESSION['testprojectName'] = $tproject_info['name'];
    $_SESSION['testprojectColor'] = $tproject_info['color'];
    $_SESSION['testprojectPrefix'] = $tproject_info['prefix'];

        if(!isset($_SESSION['testprojectOptions']) )
        {
          $_SESSION['testprojectOptions'] = new stdClass();
        }
    $_SESSION['testprojectOptions']->requirementsEnabled = 
            isset($tproject_info['opt']->requirementsEnabled) 
            ? $tproject_info['opt']->requirementsEnabled : 0;
    $_SESSION['testprojectOptions']->testPriorityEnabled = 
            isset($tproject_info['opt']->testPriorityEnabled) 
            ? $tproject_info['opt']->testPriorityEnabled : 0;
    $_SESSION['testprojectOptions']->automationEnabled = 
            isset($tproject_info['opt']->automationEnabled) 
            ? $tproject_info['opt']->automationEnabled : 0;
    $_SESSION['testprojectOptions']->inventoryEnabled = 
            isset($tproject_info['opt']->inventoryEnabled) 
            ? $tproject_info['opt']->inventoryEnabled : 0;

    tLog("Test Project was activated: [" . $tproject_info['id'] . "]" . 
        $tproject_info['name'], 'INFO');
  }
  else
  {
    if (isset($_SESSION['testprojectID']))
    {
      tLog("Test Project deactivated: [" . $_SESSION['testprojectID'] . "] " . 
          $_SESSION['testprojectName'], 'INFO');
    }
    unset($_SESSION['testprojectID']);
    unset($_SESSION['testprojectName']);
    unset($_SESSION['testprojectColor']);
    unset($_SESSION['testprojectOptions']);
    unset($_SESSION['testprojectPrefix']);
  }

}


/**
 * Unserialize project options
 * 
 * @param array $recorset produced by getTestProject() 
 */
protected function parseTestProjectRecordset(&$recordset)
{
  if (count($recordset) > 0)
  {
    foreach ($recordset as $number => $row)
    {
      $recordset[$number]['opt'] = unserialize($row['options']);
    }
  }
  else
  {
    $recordset = null;
    tLog('parseTestProjectRecordset: No project on query', 'DEBUG');
  }
}


/**
 * Get Test project data according to parameter with unique value
 * 
 * @param string $condition (optional) additional SQL condition(s)
 * @return array map with test project info; null if query fails
 */
protected function getTestProject($condition = null, $opt=null)
{
  $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

  $my = array('options' => array('output' => 'full'));
  $my['options'] = array_merge($my['options'],(array)$opt);
  $doParse = true;
  
  switch($my['options']['output'])
  {
    case 'existsByID':
      $doParse = false;
      $sql = "/* debugMsg */ SELECT testprojects.id ".
             " FROM " . $this->db->get_table($this->object_table) . " testprojects " .
             " WHERE 1=1 ";
    break;

    case 'existsByName':
      $doParse = false;
      $sql = "/* debugMsg */ SELECT testprojects.id ".
             " FROM " . $this->db->get_table($this->object_table)." testprojects " . 
             " WHERE 1=1 ";
    break;
  
    case 'full':
    default:
      $doParse = true;
      $sql = "/* debugMsg */ SELECT testprojects.* ".
             " FROM " . $this->db->get_table($this->object_table) . " testprojects " .
             " WHERE 1=1 ";
    break;
  }  
  if (!is_null($condition) )
  {
    $sql .= " AND " . $condition;
  }
  
  $rs = $this->db->get_recordset($sql);
  if($doParse)
  {
    $this->parseTestProjectRecordset($rs);
  }
  return $rs;
}


/**
 * Get Test project data according to name
 * 
 * @param string $name 
 * @param string $addClause (optional) additional SQL condition(s)
 * 
 * @return array map with test project info; null if query fails
 */
public function get_by_name($name, $addClause = null, $opt=null)
{
  $condition = "testprojects.name='" . $this->db->prepare_string($name) . "'";
  $condition .= is_null($addClause) ? '' : " AND {$addClause} ";

  return $this->getTestProject($condition);
}


/**
 * Get Test project data according to ID
 * 
 * @param integer $id test project
 * @return array map with test project info; null if query fails
 */
public function get_by_id($id, $opt=null)
{
  $condition = "testprojects.id=". intval($id);
  $result = $this->getTestProject($condition,$opt);
  return $result[0];
}


/**
 * Get Test project data according to prefix
 * 
 * @param string $prefix 
 * @param string $addClause optional additional SQL 'AND filter' clause
 * 
 * @return array map with test project info; null if query fails
 */
public function get_by_prefix($prefix, $addClause = null)
{
    $safe_prefix = $this->db->prepare_string($prefix);
  $condition = "testprojects.prefix='{$safe_prefix}'";
  $condition .= is_null($addClause) ? '' : " AND {$addClause} ";

  $result = $this->getTestProject($condition);
  return $result[0];
}


/**
 * Get Test project data according to APIKEY
 * 
 * @param string 64 chars
 * @return array map with test project info; null if query fails
 */
public function getByAPIKey($apiKey, $opt=null)
{
  $condition = "testprojects.api_key='{$apiKey}'";
  $result = $this->getTestProject($condition,$opt);
  return $result[0];
}


/*
 function: get_all
           get array of info for every test project
           without any kind of filter.
           Every array element contains an assoc array with test project info

args:[order_by]: default " ORDER BY nodes_hierarchy.name " -> testproject name
modify by zhouzhaoxin fro change order by testprojects.name for divide schema 

*/
function get_all($filters=null,$options=null)
{
  $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
  $my = array ('filters' => '', 'options' => '');
  
  
  $my['filters'] = array('active' => null);
  $my['options'] = array('order_by' => " ORDER BY testprojects.name ", 'access_key' => null);
  
  $my['filters'] = array_merge($my['filters'], (array)$filters);
  $my['options'] = array_merge($my['options'], (array)$options);
    
  
  $sql = "/* $debugMsg */ SELECT testprojects.* ".
         " FROM " . $this->db->get_table($this->object_table) . " testprojects " .
         " WHERE 1=1 ";
  
  if (!is_null($my['filters']['active']) )
  {
    $sql .= " AND active=" . intval($my['filters']['active']) . " ";
  }

  if( !is_null($my['options']['order_by']) )
  {
    $sql .= $my['options']['order_by'];
  }
  
  if( is_null($my['options']['access_key']))
  {
    $recordset = $this->db->get_recordset($sql);
    $this->parseTestProjectRecordset($recordset);
  }
  else
  {
    $recordset = $this->db->fetchRowsIntoMap($sql,$my['options']['access_key']);
    if (count($recordset) > 0)
    {
      foreach ($recordset as $number => $row)
      {
        $recordset[$number]['opt'] = unserialize($row['options']);
      }
    }
  }  


  return $recordset;
}


/*
function: get_accessible_for_user
          get list of testprojects, considering user roles.
          Remember that user has:
          1. one default role, assigned when user was created
          2. a different role can be assigned for every testproject.

          For users roles that has not rigth to modify testprojects
          only active testprojects are returned.

args:
      user_id
      [output_type]: choose the output data structure.
                     possible values: map, map_of_map
                     map: key -> test project id
                          value -> test project name

                     map_of_map: key -> test project id
                                 value -> array ('name' => test project name,
                                                 'active' => active status)

                     array_of_map: value -> array  with all testproject table fields plus name.


                     default: map
     [order_by]: default: ORDER BY name

@internal revisions
@since 1.9.7

*/
function get_accessible_for_user($user_id,$opt = null,$filters = null)
{
  $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
  $my = array();
  $my['opt'] = array('output' => 'map', 'order_by' => ' ORDER BY name ', 'field_set' => 'full',
                     'format' => 'std', 'add_issuetracker' => false, 'add_reqmgrsystem' => false);
  $my['opt'] = array_merge($my['opt'],(array)$opt);
  
  // key = field name
  // value = array('op' => Domain ('=','like'), 'value' => the value)
  $my['filters'] = array('name' => null, 'id' => null, 'prefix' => null);
  $my['filters'] = array_merge($my['filters'],(array)$filters);

                     
  $items = array();
  $safe_user_id = intval($user_id);

  // Get default/global role
  $sql = "/* $debugMsg */ SELECT id,role_id FROM ".$this->db->get_table('users')." where id=" . $safe_user_id;
  $user_info = $this->db->get_recordset($sql);
  $globalRoleID = intval($user_info[0]['role_id']);

  $itsql = '';
  $itf = '';
  if($my['opt']['add_issuetracker'])
  {
    $itsql = " LEFT OUTER JOIN ".$this->db->get_table('testproject_issuetracker')." AS TIT " .
             " ON TIT.testproject_id  = TPROJ.id " .
             " LEFT OUTER JOIN ".$this->db->get_table('issuetrackers')." AS ITMD " .
             " ON ITMD.id = TIT.issuetracker_id ";     
    $itf = ",ITMD.name AS itname,ITMD.type AS ittype";
  }        

  $rmssql = '';
  $rmsf = '';
  if($my['opt']['add_reqmgrsystem'])
  {
    $rmssql = " LEFT OUTER JOIN ".$this->db->get_table('testproject_reqmgrsystem')." AS TRMS " .
              " ON TRMS.testproject_id  = TPROJ.id " .
              " LEFT OUTER JOIN ".$this->db->get_table('reqmgrsystems')." AS RMSMD " .
              " ON RMSMD.id = TRMS.reqmgrsystem_id ";     
    $rmsf =   ",RMSMD.name AS rmsname,RMSMD.type AS rmstype";
  }
  
  $pj_order_by = "";

  switch($my['opt']['field_set'])
  {
    case 'id':
      $cols = ' TPROJ.id, TPROJ.name ';
      $my['opt']['format'] = 'do not parse';
    break;

    case 'prefix':
      $cols = ' TPROJ.id,TPROJ.prefix,TPROJ.active,TPROJ.name ';
      $my['opt']['format'] = 'do not parse';
      $pj_order_by = " order by TPROJ.prefix asc";
    break;

    case 'full':
    default:
      $cols = ' TPROJ.*,COALESCE(UTR.role_id,U.role_id) AS effective_role ';
      $pj_order_by = " order by TPROJ.prefix asc";
    break;
  } 
  
  $sql = " /* $debugMsg */ SELECT {$cols} {$itf} {$rmsf} " .
         " FROM " . $this->db->get_table($this->object_table) . " TPROJ" .
         " JOIN ".$this->db->get_table('users')." U ON U.id = {$safe_user_id} ";
  
  if ($globalRoleID == TL_ROLES_ADMIN || $globalRoleID == 10)
  {
      $sql .= " LEFT OUTER JOIN ".$this->db->get_table('user_testproject_roles')." UTR " .
         " ON TPROJ.id = UTR.testproject_id " .
         " AND UTR.user_id =" . $safe_user_id . $itsql . $rmssql .
         " WHERE 1=1 ";
  }
  else 
  {
      $sql .= " JOIN ".$this->db->get_table('user_testproject_roles')." UTR " .
          " ON TPROJ.id = UTR.testproject_id " .
          " AND UTR.user_id =" . $safe_user_id . $itsql . $rmssql .
          " WHERE 1=1 ";
  }
  
  // Private test project feature
  if( $globalRoleID != TL_ROLES_ADMIN )
  {
    if ($globalRoleID != TL_ROLES_NO_RIGHTS)
    {
      $sql .=  " AND "; 
      $sql_public = " ( TPROJ.is_public = 1 AND (UTR.role_id IS NULL OR UTR.role_id != " . TL_ROLES_NO_RIGHTS. ") )";
      $sql_private = " ( TPROJ.is_public = 0 AND UTR.role_id != " . TL_ROLES_NO_RIGHTS. ") ";
      $sql .= " ( {$sql_public}  OR {$sql_private} ) ";
    }
    else
    {
      // User needs specific role
      $sql .=  " AND (UTR.role_id IS NOT NULL AND UTR.role_id != ".TL_ROLES_NO_RIGHTS.")";
    }
  }

  $userObj = tlUser::getByID($this->db,$safe_user_id,tlUser::TLOBJ_O_GET_DETAIL_MINIMUM);
  if ($userObj->hasRight($this->db,'mgt_modify_product') != 'yes')
  {
    $sql .= " AND TPROJ.active=1 ";
  }
  unset($userObj);
  
  foreach($my['filters'] as $fname => $fspec)
  {
    if(!is_null($fspec))
    {
      switch($fname)
      {
        case 'prefix':
          $sql .= " AND TPROJ.$fname";
          $sm = 'prepare_string';
        break;

        case 'name':
          $sql .= " AND TPROJ.$fname";
          $sm = 'prepare_string';
        break;

        case 'id':
          $sql .= " AND TPROJ.$fname";
          $sm = 'prepare_int';
        break;
      }

      $safe = $this->db->$sm($fspec['value']);
      switch($fspec['op'])
      {
        case '=':
          if($sm == 'prepare_string')
          {
            $sql .= "='" . $safe . "'";
          }  
          else
          {
            $sql .= "=" . $safe;
          }  
        break;

        case 'like':
          $sql .= " LIKE '%" . $safe ."%'";         
        break;
      }
    }  
  }  
  
  $sql .= $pj_order_by;
 
  $parseOpt = false;
  $do_post_process = 0;
  
  switch($my['opt']['output'])
  {
    case 'array_of_map':
      $items = $this->db->get_recordset($sql); //,null,3,1);
      $parseOpt = true;
    break;

    case 'map_of_map_full':
      $items = $this->db->fetchRowsIntoMap($sql,'id');
      $parseOpt = true;
    break;

    case 'map':
      $items = $this->db->fetchRowsIntoMap($sql,'id');
    break;

    case 'map_with_inactive_mark':
    default:
      $arrTemp = $this->db->fetchRowsIntoMap($sql,'id');
      $do_post_process=1;
    break;
  }
    
  if($my['opt']['format'] == 'std' && $parseOpt)
  {
    $this->parseTestProjectRecordset($items);
  }

  if ($do_post_process && sizeof($arrTemp))
  {
    switch ($my['opt']['output'])
    {
      case 'map_name_with_inactive_mark':
      foreach($arrTemp as $id => $row)
      {
        $noteActive = '';
        if (!$row['active'])
        {
         $noteActive = TL_INACTIVE_MARKUP;
        }
        $items[$id] = $noteActive . 
                      ( ($my['opt']['field_set'] =='prefix') ? ($row['prefix'] . ':') : '' ) . $row['name'];
      }
      break;
      
      case 'map_of_map':
      foreach($arrTemp as $id => $row)
      {
        $items[$id] = array('name' => $row['name'],'active' => $row['active']);
      }
      break;       
    }
    unset($arrTemp);
  }
  
  return $items;
}


/*
  function: get_subtree
            Get subtree that has choosen testproject as root.
            Only nodes of type:
            testsuite and testcase are explored and retrieved.

  args: id: testsuite id
        [recursive_mode]: default false
        [exclude_testcases]: default: false
        [exclude_branches]
        [additionalWhereClause]:


  returns: map
           see tree->get_subtree() for details.


*/
function get_subtree($id,$filters=null,$opt=null)
{
  $my = array();
  $my['options'] = array('recursive' => false, 'exclude_testcases' => false, 'output' => 'full');
  $my['filters'] = array('exclude_node_types' => $this->nt2exclude,
                          'exclude_children_of' => $this->nt2exclude_children,
                          'exclude_branches' => null,
                          'additionalWhereClause' => '');      
    
  $my['options'] = array_merge($my['options'],(array)$opt);
  $my['filters'] = array_merge($my['filters'],(array)$filters);

  if($my['options']['exclude_testcases'])
  {
    $my['filters']['exclude_node_types']['testcase']='exclude me';
  }
    
  $subtree = $this->tree_manager->get_subtree(intval($id),$my['filters'],$my['options']);
  return $subtree;   
}


/**
 * Displays smarty template to show test project info to users.
 *
 * @param type $smarty [ref] smarty object
 * @param type $id test project
 * @param type $sqlResult [default = '']
 * @param type $action [default = 'update']
 * @param type $modded_item_id [default = 0]
 *
 * @internal revisions
 *
 **/
function show(&$smarty,$guiObj,$template_dir,$id,$sqlResult='', $action = 'update',$modded_item_id = 0)
{
  $gui = $guiObj;
  $gui->modify_tc_rights = has_rights($this->db,"mgt_modify_tc");
  $gui->mgt_modify_product = has_rights($this->db,"mgt_modify_product");

  $gui->sqlResult = '';
  $gui->sqlAction = '';
  if($sqlResult)
  {
    $gui->sqlResult = $sqlResult;
  }

  $p2ow = array('refreshTree' => false, 'user_feedback' => '');
  foreach($p2ow as $prop => $value)
  {
    if( !property_exists($gui,$prop) )
    {
      $gui->$prop = $value;
    }
  }

  $safeID = intval($id);
  $gui->container_data = $this->get_by_id($safeID);
  $gui->moddedItem = $gui->container_data;
  $gui->level = 'testproject';
  $gui->page_title = lang_get('testproject');
  $gui->refreshTree = property_exists($gui,'refreshTree') ? $gui->refreshTree : false;
  $gui->attachmentInfos = getAttachmentInfosFrom($this,$safeID);
   
  // attachments management on page
  $gui->fileUploadURL = $_SESSION['basehref'] . $this->getFileUploadRelativeURL($safeID);
  $gui->delAttachmentURL = $_SESSION['basehref'] . $this->getDeleteAttachmentRelativeURL($safeID);
  $gui->import_limit = TL_REPOSITORY_MAXFILESIZE;
  $gui->fileUploadMsg = '';
  
  $exclusion = array( 'testcase', 'me', 'testplan' => 'me', 'requirement_spec' => 'me');
  $gui->canDoExport = count($this->tree_manager->get_children($safeID,$exclusion)) > 0;
  if ($modded_item_id)
  {
    $gui->moddedItem = $this->get_by_id(intval($modded_item_id));
  }
  $smarty->assign('gui', $gui);  
  $smarty->display($template_dir . 'containerView.tpl');
}


/**
 * Count testcases without considering active/inactive status.
 * 
 * @param integer $id: test project identifier
 * @return integer count of test cases presents on test project.
 */
function count_testcases($id)
{
  $tcIDs = array();
  $this->get_all_testcases_id($id,$tcIDs);
  $qty = sizeof($tcIDs);
  return $qty;
}


  /*
    function: gen_combo_test_suites
              create array with test suite names
              test suites are ordered in parent-child way, means
              order on array is creating traversing tree branches, reaching end
              of branch, and starting again. (recursive algorithim).


    args :  $id: test project id
            [$exclude_branches]: array with testsuite id to exclude
                                 useful to exclude myself ($id)
            [$mode]: dotted -> $level number of dot characters are appended to
                               the left of test suite name to create an indent effect.
                               Level indicates on what tree layer testsuite is positioned.
                               Example:

                                null
                                \
                               id=1   <--- Tree Root = Level 0
                                 |
                                 + ------+
                               /   \      \
                            id=9   id=2   id=8  <----- Level 1
                                    \
                                     id=3       <----- Level 2
                                      \
                                       id=4     <----- Level 3


                               key: testsuite id (= node id on tree).
                               value: every array element is an string, containing testsuite name.

                               Result example:

                                2  .TS1
                                3   ..TS2
                                9   .20071014-16:22:07 TS1
                               10   ..TS2


                     array  -> key: testsuite id (= node id on tree).
                               value: every array element is a map with the following keys
                               'name', 'level'

                                2    array(name => 'TS1',level =>  1)
                                3   array(name => 'TS2',level =>  2)
                                9    array(name => '20071014-16:22:07 TS1',level =>1)
                               10   array(name =>  'TS2', level   => 2)


    returns: map , structure depens on $mode argument.

  */
  function gen_combo_test_suites($id,$exclude_branches=null,$mode='dotted')
  {
    $ret = array();
    $test_spec = $this->get_subtree($id, array('exclude_branches' => $exclude_branches),
                    array('recursive' => !self::RECURSIVE_MODE,
                          'exclude_testcases' => self::EXCLUDE_TESTCASES));

    if(count($test_spec))
    {
      $ret = $this->_createHierarchyMap($test_spec,$mode);
    }
    return $ret;
  }

  /**
   * Checks a test project name for correctness
   *
   * @param string $name the name to check
   * @return map with keys: status_ok, msg
   **/
  function checkName($name)
  {
    $forbidden_pattern = config_get('ereg_forbidden');
    $ret['status_ok'] = 1;
    $ret['msg'] = 'ok';

    if ($name == "")
    {
      $ret['msg'] = lang_get('info_product_name_empty');
      $ret['status_ok'] = 0;
    }
    if ($ret['status_ok'] && !check_string($name,$forbidden_pattern))
    {
      $ret['msg'] = lang_get('string_contains_bad_chars');
      $ret['status_ok'] = 0;
    }
    return $ret;
  }

  /**
   * Checks a test project name for sintax correctness
   *
   * @param string $name the name to check
   * @return map with keys: status_ok, msg
   **/
  function checkNameSintax($name)
  {
    $forbidden_pattern = config_get('ereg_forbidden');
    $ret['status_ok'] = 1;
    $ret['msg'] = 'ok';

    if ($name == "")
    {
      $ret['msg'] = lang_get('info_product_name_empty');
      $ret['status_ok'] = 0;
    }
    if ($ret['status_ok'] && !check_string($name,$forbidden_pattern))
    {
      $ret['msg'] = lang_get('string_contains_bad_chars');
      $ret['status_ok'] = 0;
    }
    return $ret;
  }

  /**
   * Checks is there is another testproject with different id but same name
   *
   **/
  function checkNameExistence($name,$id=0)
  {
    $check_op['msg'] = '';
    $check_op['status_ok'] = 1;
       
    if($this->get_by_name($name,"testprojects.id <> {$id}") )
    {
      $check_op['msg'] = sprintf(lang_get('error_product_name_duplicate'),$name);
      $check_op['status_ok'] = 0;
    }
    return $check_op;
  }

  /**
   * Checks is there is another testproject with different id but same prefix
   *
   **/
  function checkTestCasePrefixExistence($prefix,$id=0)
  {
    $check_op = array('msg' => '', 'status_ok' => 1);
    $sql = " SELECT id FROM " . $this->db->get_table($this->object_table) .
           " WHERE prefix='" . $this->db->prepare_string($prefix) . "'";
           " AND id <> {$id}";

    $rs = $this->db->get_recordset($sql);
    if(!is_null($rs))
    {
      $check_op['msg'] = sprintf(lang_get('error_tcase_prefix_exists'),$prefix);
      $check_op['status_ok'] = 0;
    }
      
    return $check_op;
  }



  /** 
   * allow activate or deactivate a test project
   * 
   * @param integer $id test project ID
   * @param integer $status 1=active || 0=inactive
   */
  function activate($id, $status)
  {
    $sql = "UPDATE ".$this->db->get_table('testprojects')." SET active=" . $status . " WHERE id=" . $id;
    $result = $this->db->exec_query($sql);

    return $result ? 1 : 0;
  }

  /** @TODO add description */
  function formatTcPrefix($str)
  {
    $fstr = trim($str);
    if(tlStringLen($fstr) == 0)
    {
      throw new Exception('Empty prefix is not allowed');      
    } 

    // limit tcasePrefix len.
    if(tlStringLen($fstr) > self::TESTCASE_PREFIX_MAXLEN)
    {
      $fstr = substr($fstr,self::TESTCASE_PREFIX_MAXLEN);
    }
    return $fstr;
  }


  /*
   args : id: test project
   returns: null if query fails
   string
   */
  function getTestCasePrefix($id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $ret=null;
    $sql = "/* $debugMsg */ SELECT prefix FROM " . $this->db->get_table($this->object_table) . " WHERE id = {$id}";
    $ret = $this->db->fetchOneValue($sql);
    return ($ret);
  }


  /*
   args: id: test project
   returns: null if query fails
   a new test case number
   */
  function generateTestCaseNumber($id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    $ret=null;
    $sql = "/* $debugMsg */ UPDATE " . $this->db->get_table($this->object_table) .
           " SET tc_counter=tc_counter+1 WHERE id = {$id}";
    $recordset = $this->db->exec_query($sql);
    
    $sql = " SELECT tc_counter  FROM " . $this->db->get_table($this->object_table) . "  WHERE id = {$id}";
    $recordset = $this->db->get_recordset($sql);
    $ret=$recordset[0]['tc_counter'];
    return ($ret);
  }

  /**
   *
   *
   */
  function setTestCaseCounter($id,$value,$force=false)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    $safeValue = intval($value);
    $ret=null;
    $sql = " /* $debugMsg */ UPDATE " . $this->db->get_table($this->object_table) .
           ' SET tc_counter=' . $safeValue . 
           ' WHERE id =' . intval($id);

    if(!$force)
    {
      $sql .= ' AND tc_counter < ' . $safeValue;
    }       
    $rs = $this->db->exec_query($sql);
  }



/** 
 * @param integer $id test project ID
 */
function setPublicStatus($id,$status)
{
  $isPublic = val($status) > 0 ? 1 : 0; 
  $sql = "UPDATE " . $this->db->get_table($this->object_table)." SET is_public={$isPublic} WHERE id={$id}";
  $result = $this->db->exec_query($sql);
  return $result ? 1 : 0;
}



  /* Keywords related methods  */
  /**
   * Adds a new keyword to the given test project
   *
   * @param int  $testprojectID
   * @param string $keyword
   * @param string $notes
   *
   **/
  public function addKeyword($testprojectID,$keyword,$notes)
  {
    $kw = new tlKeyword();
    $kw->initialize(null,$testprojectID,$keyword,$notes);
    $op = array('status' => tlKeyword::E_DBERROR, 'id' => -1);
    $op['status'] = $kw->writeToDB($this->db);
    if ($op['status'] >= tl::OK)
    {
      $op['id'] = $kw->dbID;
      logAuditEvent(TLS("audit_keyword_created",$keyword),"CREATE",$op['id'],"keywords");
    }
    return $op;
  }

  /**
   * updates the keyword with the given id
   *
   * @param type $testprojectID
   * @param type $id
   * @param type $keyword
   * @param type $notes
   *
   **/
  function updateKeyword($testprojectID,$id,$keyword,$notes)
  {
    $kw = new tlKeyword($id);
    $kw->initialize($id,$testprojectID,$keyword,$notes);
    $result = $kw->writeToDB($this->db);
    if ($result >= tl::OK)
    {  
      logAuditEvent(TLS("audit_keyword_saved",$keyword),"SAVE",$kw->dbID,"keywords");
    }
    return $result;
  }

  /**
   * gets the keyword with the given id
   *
   * @param type $kwid
   **/
  public function getKeyword($id)
  {
    return tlKeyword::getByID($this->db,$id);
  }
  
  /**
   * Gets the keywords of the given test project
   *
   * @param int $tprojectID the test project id
   * @param int $keywordID [default = null] the optional keyword id
   * 
   * @return array, every elemen is map with following structure:
   *                id
   *                keyword
   *                notes
   **/
  public function getKeywords($testproject_id)
  {
    $ids = $this->getKeywordIDsFor($testproject_id);

    return tlKeyword::getByIDs($this->db,$ids);
  }

  /**
   * Deletes the keyword with the given id
   *
   * @param int $id the keywordID
   * @return int returns 1 on success, 0 else
   *
   **/
  function deleteKeyword($id, $opt=null)
  {
    $result = tl::ERROR;
    $my['opt'] = array('checkBeforeDelete' => true, 'nameForAudit' => null,
                       'context' => '');

    $my['opt'] = array_merge($my['opt'],(array)$opt);

    $doIt = !$my['opt']['checkBeforeDelete'];
    $keyword = $my['opt']['nameForAudit'];
    if($my['opt']['checkBeforeDelete'])
    {
      $kw = $this->getKeyword($id);
      if( $doIt = !is_null($kw) )
      {
        $keyword = $kw->name;
      }  
    }  
    
    if($doIt)
    {
      $result = tlDBObject::deleteObjectFromDB($this->db,$id,"tlKeyword");
    }

    if ($result >= tl::OK && $this->auditCfg->logEnabled)
    {
      logAuditEvent(TLS("audit_keyword_deleted",$keyword,$my['opt']['context']),
                    "DELETE",$id,"keywords");
    }
    return $result;
  }

  /**
   * delete Keywords
   */
  function deleteKeywords($tproject_id,$tproject_name=null)
  {
    $result = tl::OK;

    $itemSet = $this->getKeywordSet($tproject_id);
    $kwIDs = array_keys($itemSet);

    $opt = array('checkBeforeDelete' => false,
                 'context' => $tproject_name);

    $loop2do = sizeof($kwIDs);
    for($idx = 0;$idx < $loop2do; $idx++)
    {
      $opt['nameForAudit'] = $itemSet[$kwIDs[$idx]]['keyword'];

      $resultKw = $this->deleteKeyword($kwIDs[$idx],$opt);
      if ($resultKw != tl::OK)
      {  
        $result = $resultKw;
      }  
    }
    return $result;
  }


  /**
   * 
   *
   */
  protected function getKeywordIDsFor($testproject_id)
  {
    $query = " SELECT id FROM ".$this->db->get_table('keywords')."  " .
             " WHERE testproject_id = {$testproject_id}" .
             " ORDER BY keyword ASC";
    $keywordIDs = $this->db->fetchColumnsIntoArray($query,'id');
    return $keywordIDs;
  }

  /**
   * 
   *
   */
  protected function getKeywordSet($tproject_id)
  {
    $sql = " SELECT id,keyword FROM ".$this->db->get_table('keywords')."  " .
           " WHERE testproject_id = {$tproject_id}" .
           " ORDER BY keyword ASC";
    $items = $this->db->fetchRowsIntoMap($sql,'id');
    return $items;
  }


  /**
   * 
   *
   */
  function hasKeywords($id)
  {
    // seems that postgres PHP driver do not manage well UPPERCASE  in AS CLAUSE
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = "/* {$debugMsg} */ SELECT COUNT(0) AS qty FROM ".$this->db->get_table('keywords')."  " .
           " WHERE testproject_id = " . intval($id);
    $rs = $this->db->get_recordset($sql);

    return ((is_null($rs) || $rs[0]['qty'] == 0) ? false : true);
  }


  /**
   * Exports the given keywords to a XML file
   *
   * @return strings the generated XML Code
   **/
  public function exportKeywordsToXML($testproject_id,$bNoXMLHeader = false)
  {
    $kwIDs = $this->getKeywordIDsFor($testproject_id);
    $xmlCode = '';
    if (!$bNoXMLHeader)
    {
      $xmlCode .= TL_XMLEXPORT_HEADER."\n";
    }
    $xmlCode .= "<keywords>";
    for($idx = 0;$idx < sizeof($kwIDs);$idx++)
    {
      $keyword = new tlKeyword($kwIDs[$idx]);
      $keyword->readFromDb($this->db);
      $keyword->writeToXML($xmlCode,true);
    }
    $xmlCode .= "</keywords>";

    return $xmlCode;
  }

  /**
   * Exports the given keywords to CSV
   *
   * @return string the generated CSV code
   **/
  function exportKeywordsToCSV($testproject_id,$delim = ';')
  {
    $kwIDs = $this->getKeywordIDsFor($testproject_id);
    $csv = null;
    for($idx = 0;$idx < sizeof($kwIDs);$idx++)
    {
      $keyword = new tlKeyword($kwIDs[$idx]);
      $keyword->readFromDb($this->db);
      $keyword->writeToCSV($csv,$delim);
    }
    return $csv;
  }

  function importKeywordsFromCSV($testproject_id,$fileName,$delim = ';')
  {
    $handle = fopen($fileName,"r");
    if ($handle)
    {
      while($data = fgetcsv($handle, TL_IMPORT_ROW_MAX, $delim))
      {
        $kw = new tlKeyword();
        $kw->initialize(null,$testproject_id,NULL,NULL);
        if ($kw->readFromCSV(implode($delim,$data)) >= tl::OK)
        {
          if ($kw->writeToDB($this->db) >= tl::OK)
          {  
            logAuditEvent(TLS("audit_keyword_created",$kw->name),"CREATE",$kw->dbID,"keywords");
          }
        }
      }
      fclose($handle);
      return tl::OK;
    }
    else
    {
      return ERROR;
    }  
  }

  /**
   * @param $testproject_id
   * @param $fileName
    */
  function importKeywordsFromXMLFile($testproject_id,$fileName)
  {
    $simpleXMLObj = @$this->simplexml_load_file_helper($fileName);
    return $this->importKeywordsFromSimpleXML($testproject_id,$simpleXMLObj);
  }


  /**
   * @param $testproject_id
   * @param $xmlString
    */
  function importKeywordsFromXML($testproject_id,$xmlString)
  {
    $simpleXMLObj = simplexml_load_string($xmlString);
    return $this->importKeywordsFromSimpleXML($testproject_id,$simpleXMLObj);
  }

  /**
   * @param $testproject_id
   * @param $simpleXMLObj
    */
  function importKeywordsFromSimpleXML($testproject_id,$simpleXMLObj)
  {
    $status = tl::OK;
    if(!$simpleXMLObj || $simpleXMLObj->getName() != 'keywords')
    {
      $status = tlKeyword::E_WRONGFORMAT;
    }
  
    if( ($status == tl::OK) && $simpleXMLObj->keyword )
    {
      foreach($simpleXMLObj->keyword as $keyword)
      {
        $kw = new tlKeyword();
        $kw->initialize(null,$testproject_id,NULL,NULL);
        $status = tlKeyword::E_WRONGFORMAT;
        if ($kw->readFromSimpleXML($keyword) >= tl::OK)
        {
          $status = tl::OK;
          if ($kw->writeToDB($this->db) >= tl::OK)
          {
            logAuditEvent(TLS("audit_keyword_created",$kw->name),"CREATE",$kw->dbID,"keywords");
          }  
        }
      }
    }
    return $status;
  }

  /**
   * Returns all testproject keywords
   *
   *  @param  integer $testproject_id the ID of the testproject
   *  @return array   map: key: keyword_id, value: keyword
   */
  function get_keywords_map($testproject_id)
  {
    $keywordMap = null;
    $keywords = $this->getKeywords($testproject_id);
    if ($keywords)
    {
      foreach($keywords as $kw)
      {
        $keywordMap[$kw->dbID] = $kw->name;
      }
    }
    return $keywordMap;
  }
  /* END KEYWORDS RELATED */

  /* REQUIREMENTS RELATED */
  /**
   * get list of all SRS for a test project, no distinction between levels
   *
   * 
     * @used-by lib/results/uncoveredTestCases.php
     *      lib/requirements/reqTcAssign.php
     *       lib/requirements/reqSpecSearchForm.php
     *      lib/requirements/reqSearchForm.php
   *   
   * @author Martin Havlat
   * @return associated array List of titles according to IDs
   * 
   * @internal revisions
   * 
   **/
  function getOptionReqSpec($tproject_id,$get_not_empty=self::GET_EMPTY_REQSPEC)
  {
    $additional_table='';
    $additional_join='';
    if( $get_not_empty )
    {
      $additional_table=", ".$this->db->get_table('requirements')." REQ ";
      $additional_join=" AND SRS.id = REQ.srs_id ";
    }
    $sql = " SELECT SRS.id,NH.name AS title " .
           " FROM ".$this->db->get_table('req_specs')." SRS, " .
           " ".$this->db->get_table('nodes_hierarchy')." NH " . 
           $additional_table .
           " WHERE testproject_id={$tproject_id} " .
           " AND SRS.id=NH.id " .
           $additional_join .
         " ORDER BY title";
    return $this->db->fetchColumnsIntoMap($sql,'id','title');
    //return $this->db->fetchRowsIntoMap($sql,'id'); SRS.doc_id,
  } // function end


  /**
   * @author Francisco Mancardi - francisco.mancardi@gmail.com
     *
     * @TODO check who uses it, is duplicated of getOptionReqSpec?
     *
     * @used-by lib/results/uncoveredTestCases.php
     *      lib/requirements/reqTcAssign.php
     *       lib/requirements/reqSpecSearchForm.php
     *      lib/requirements/reqSearchForm.php
     *
     * @internal revisions
     * 
     *
   **/
  function genComboReqSpec($id,$mode='dotted',$dot='.')
  {
    $ret = array();
      $exclude_node_types=array('testplan' => 'exclude_me','testsuite' => 'exclude_me',
                                'testcase'=> 'exclude_me','requirement' => 'exclude_me',
                                'requirement_spec_revision' => 'exclude_me');

     $my['filters'] = array('exclude_node_types' => $exclude_node_types);
    
    $my['options'] = array('order_cfg' => array('type' => 'rspec'), 'output' => 'rspec');
      $subtree = $this->tree_manager->get_subtree($id,$my['filters'],$my['options']);
      if(count($subtree))
    {
      $ret = $this->_createHierarchyMap($subtree,$mode,$dot,'doc_id');
        }
    return $ret;
  }

  /*
  
              [$mode]: dotted -> $level number of dot characters are appended to
                               the left of item name to create an indent effect.
                               Level indicates on what tree layer item is positioned.
                               Example:

                                null
                                \
                               id=1   <--- Tree Root = Level 0
                                 |
                                 + ------+
                               /   \      \
                            id=9   id=2   id=8  <----- Level 1
                                    \
                                     id=3       <----- Level 2
                                      \
                                       id=4     <----- Level 3


                               key: item id (= node id on tree).
                               value: every array element is an string, containing item name.

                               Result example:

                                2  .TS1
                                3   ..TS2
                                9   .20071014-16:22:07 TS1
                               10   ..TS2


                     array  -> key: item id (= node id on tree).
                               value: every array element is a map with the following keys
                               'name', 'level'

                                2    array(name => 'TS1',level =>  1)
                                3   array(name => 'TS2',level =>  2)
                                9    array(name => '20071014-16:22:07 TS1',level =>1)
                               10   array(name =>  'TS2', level   => 2)

  */
  protected function _createHierarchyMap($array2map,$mode='dotted',$dot='.',$addfield=null)
  {
    $hmap=array();
    $the_level = 1;
    $level = array();
      $pivot = $array2map[0];

    $addprefix = !is_null($addfield);
    foreach($array2map as $elem)
    {
      $current = $elem;

      if ($pivot['id'] == $current['parent_id'])
      {
        $the_level++;
        $level[$current['parent_id']]=$the_level;
      }
      else if ($pivot['parent_id'] != $current['parent_id'])
      {
        $the_level = $level[$current['parent_id']];
      }

      switch($mode)
      {
          case 'dotted':
            $dm = $addprefix ? "[{$current[$addfield]}] - " : '';
            $pding = ($the_level == 1) ? 0 : $the_level+1;  
          $hmap[$current['id']] = str_repeat($dot,$pding) . $dm . $current['name'];
          break;

          case 'array':
          $hmap[$current['id']] = array('name' => $current['name'], 'level' =>$the_level);
          break;
      }

      // update pivot
      $level[$current['parent_id']]= $the_level;
      $pivot=$elem;
    }
    
      return $hmap;
  }



  /**
   * collect information about current list of Requirements Specification
   *
   * @param integer $testproject_id
   * @param string  $id optional id of the requirement specification
   *
   * @return mixed 
   *     null if no srs exits, or no srs exists for id
   *     array, where each element is a map with SRS data.
   *
   *         map keys:
   *         id
   *         testproject_id
   *         title
   *         scope
   *         total_req
   *         type
   *         author_id
   *         creation_ts
   *         modifier_id
   *         modification_ts
   *
   * @author Martin Havlat
   * @internal revisions 
   *       
   **/
  public function getReqSpec($testproject_id, $id = null, $fields=null,$access_key=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    $fields2get="RSPEC.id,testproject_id,RSPEC.scope,RSPEC.total_req,RSPEC.type," .
                "RSPEC.author_id,RSPEC.creation_ts,RSPEC.modifier_id," .
                "RSPEC.modification_ts,NH.name AS title";
    
    $fields = is_null($fields) ? $fields2get : implode(',',$fields);
    $sql = "  /* $debugMsg */ SELECT {$fields} FROM ".$this->db->get_table('req_specs')." RSPEC, " .
           " ".$this->db->get_table('nodes_hierarchy')." NH , ".$this->db->get_table('requirements')." REQ " .
           " WHERE testproject_id={$testproject_id} AND RSPEC.id=NH.id AND REQ.srs_id = RSPEC.id" ;
           
    if (!is_null($id))
      {
          $sql .= " AND RSPEC.id=" . $id;
      }
      $sql .= "  ORDER BY RSPEC.id,title";
      $rs = is_null($access_key) ? $this->db->get_recordset($sql) : $this->db->fetchRowsIntoMap($sql,$access_key);
        
    return $rs;
  }

  /**
   * create a new System Requirements Specification
   *
   * @param string $title
   * @param string $scope
   * @param string $countReq
   * @param numeric $testproject_id
   * @param numeric $user_id
   * @param string $type
   *
   * @author Martin Havlat
   *
   * rev: 20071106 - franciscom - changed return type
   */
  function createReqSpec($testproject_id,$title, $scope, $countReq,$user_id,$type = 'n')
  {
    $ignore_case=1;
    $result=array();

    $result['status_ok'] = 0;
    $result['msg'] = 'ko';
    $result['id'] = 0;

      $title=trim($title);

      $chk=$this->check_srs_title($testproject_id,$title,$ignore_case);
    if ($chk['status_ok'])
    {
      $sql = "INSERT INTO ".$this->db->get_table('req_specs')." " .
             " (testproject_id, title, scope, type, total_req, author_id, creation_ts)
              VALUES (" . $testproject_id . ",'" . $this->db->prepare_string($title) . "','" .
                          $this->db->prepare_string($scope) .  "','" . $this->db->prepare_string($type) . "','" .
                          $this->db->prepare_string($countReq) . "'," . $this->db->prepare_string($user_id) . ", " .
                          $this->db->db_now() . ")";

      if (!$this->db->exec_query($sql))
      {
        $result['msg']=lang_get('error_creating_req_spec');
      }
      else
      {
        $result['id']=$this->db->insert_id($this->tables['req_specs']);
          $result['status_ok'] = 1;
        $result['msg'] = 'ok';
      }
    }
    else
    {
      $result['msg']=$chk['msg'];
    }
    return $result;
  }



  /*
    function: get_srs_by_title
              get srs information using title as access key.

    args : tesproject_id
           title: srs title
           [ignore_case]: control case sensitive search.
                          default 0 -> case sensivite search

    returns: map.
             key: srs id
             value: srs info,  map with folowing keys:
                    id
                    testproject_id
                    title
                    scope
                    total_req
                    type
                    author_id
                    creation_ts
                    modifier_id
                    modification_ts
  */
  public function get_srs_by_title($testproject_id,$title,$ignore_case=0)
  {
    $output=null;
    $title=trim($title);
    
//    $sql = "SELECT * FROM req_specs ";
    $sql = "SELECT * FROM ".$this->db->get_table('req_specs')." ";
    
    if($ignore_case)
    {
      $sql .= " WHERE UPPER(title)='" . strtoupper($this->db->prepare_string($title)) . "'";
    }
    else
    {
      $sql .= " WHERE title='" . $this->db->prepare_string($title) . "'";
    }
    $sql .= " AND testproject_id={$testproject_id}";
    $output = $this->db->fetchRowsIntoMap($sql,'id');
    
    return $output;
  }
  


  /*
    function: check_srs_title
              Do checks on srs title, to understand if can be used.

              Checks:
              1. title is empty ?
              2. does already exist a srs with this title?

    args : tesproject_id
           title: srs title
           [ignore_case]: control case sensitive search.
                          default 0 -> case sensivite search

    returns:

  */
  function check_srs_title($testproject_id,$title,$ignore_case=0)
  {
    $ret['status_ok'] = 1;
    $ret['msg'] = '';
    
    $title = trim($title);
    
    if ($title == "")
    {
      $ret['status_ok'] = 0;
      $ret['msg'] = lang_get("warning_empty_req_title");
    }
    
    if($ret['status_ok'])
    {
      $ret['msg'] = 'ok';
      $rs = $this->get_srs_by_title($testproject_id,$title,$ignore_case);
      
      if(!is_null($rs))
      {
        $ret['msg'] = lang_get("warning_duplicate_req_title");
        $ret['status_ok'] = 0;
      }
    }
    return $ret;
  }
/* END REQUIREMENT RELATED */
// ----------------------------------------------------------------------------------------


  /**
   * Deletes all testproject related role assignments for a given testproject
   *
   * @param integer $tproject_id
   * @return integer tl::OK on success, tl::ERROR else
   **/
  function deleteUserRoles($tproject_id,$users=null,$opt=null)
  {
    $my['opt'] = array('auditlog' => true);
    $my['opt'] = array_merge($my['opt'],(array)$opt);
    $query = " DELETE FROM ".$this->db->get_table('user_testproject_roles')." " . 
             " WHERE testproject_id = " . intval($tproject_id) ;

    if(!is_null($users))
    {
      $query .= " AND user_id IN(" . implode(',',$users) . ")";
    } 

    if ($this->db->exec_query($query) && $my['opt']['auditlog'])
    {
      $testProject = $this->get_by_id($tproject_id);
    
      if ($testProject)
      {
        if(is_null($users))
        {
          logAuditEvent(TLS("audit_all_user_roles_removed_testproject",$testProject['name']),
                        "ASSIGN",$tproject_id,"testprojects");
        }  
        else
        {
          // TBD
        }  
      }
      return tl::OK;
    }
    
    return tl::ERROR;
  }

  /**
   * Gets all testproject related role assignments
   *
   * @param integer $tproject_id
   * @return array assoc array with keys take from the user_id column
   **/
  function getUserRoleIDs($tproject_id)
  {
    $query = "SELECT user_id,role_id FROM ".$this->db->get_table('user_testproject_roles')." " .
      "WHERE testproject_id = {$tproject_id}";
    $roles = $this->db->fetchRowsIntoMap($query,'user_id');
    
    return $roles;
  }

  /**
   * Inserts a testproject related role for a given user
   *
   * @param integer $userID the id of the user
   * @param integer $tproject_id
   * @param integer $roleID the role id
   * 
   * @return integer tl::OK on success, tl::ERROR else
   **/
  function addUserRole($userID,$tproject_id,$roleID)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__; 
    $query = "/* debugMsg*/ INSERT INTO ".$this->db->get_table('user_testproject_roles')." " .
             " (user_id,testproject_id,role_id) VALUES ({$userID},{$tproject_id},{$roleID})";
    if($this->db->exec_query($query))
    {
      $testProject = $this->get_by_id($tproject_id);
      $role = tlRole::getByID($this->db,$roleID,tlRole::TLOBJ_O_GET_DETAIL_MINIMUM);
      $user = tlUser::getByID($this->db,$userID,tlUser::TLOBJ_O_GET_DETAIL_MINIMUM);
      if ($user && $testProject && $role)
      {
        logAuditEvent(TLS("audit_users_roles_added_testproject",$user->getDisplayName(),
                      $testProject['name'],$role->name),"ASSIGN",$tproject_id,"testprojects");
      }
      unset($user);
      unset($role);
      unset($testProject);
      return tl::OK;
    }
    return tl::ERROR;
  }
  
  /**
   * delete test project from system, deleting all dependent data:
   *      keywords, requirements, custom fields, testsuites, testplans,
   *      testcases, results, testproject related roles,
   * 
   * @param integer $id test project id
   * @return integer status
   * 
   */
  function delete($id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
    $ret['msg']='ok';
    $ret['status_ok']=1;
    
    $error = '';
    $reqspec_mgr = new requirement_spec_mgr($this->db);
    
    // get some info for audit
    $info['name'] = '';
    if($this->auditCfg->logEnabled)
    {
      //modify by zhouzhaoxin 20161107 to change get name source to testprojects table
      $info = $this->get_by_id($id);
      $event = new stdClass();
      $event->message = TLS("audit_testproject_deleted",$info['name']);
      $event->objectID = $id;
      $event->objectType = 'testprojects';
      $event->source = $this->auditCfg->eventSource;
      $event->logLevel = 'AUDIT';
      $event->code = 'DELETE';
    }  

    //    
    // Notes on delete related to Foreing Keys
    // All link tables has to be deleted first
    //
    // req_relations
    // 
    // testplan_tcversions
    // testplan_platforms
    // object_keywords
    // user_assignments
    // builds
    // milestones
    //
    // testplans
    // keywords    
    // platforms 
    // attachtments
    // testcases
    // testsuites
    // inventory
    //
    // testproject
    
    // modify by zhouzhaoxin 20161107  for divide schema, so need to delete the project schema
    $this->schema_mgr->delete_schema($id);
    
    $this->deleteAttachments($id);
  
    $a_sql[] = array("/* $debugMsg */ UPDATE ".$this->db->get_table('users')."  " . 
                     " SET default_testproject_id = NULL " .
                     " WHERE default_testproject_id = {$id}",
                     'info_resetting_default_project_fails');


    foreach ($a_sql as $oneSQL)
    {
      if (empty($error))
      {
        $sql = $oneSQL[0];
        $result = $this->db->exec_query($sql);
        if (!$result)
        {
          $error .= lang_get($oneSQL[1]);
        }  
      }
    }
    
    
    if ($this->deleteUserRoles($id) < tl::OK)
    {
      $error .= lang_get('info_deleting_project_roles_fails');
    }
    
    // modify by zhouzhaoxi 20161107 to add more table from 2 to 
    $xSQL = array('testproject_issuetracker','testproject_reqmgrsystem', 'inventory', 'cfield_testprojects');
    foreach($xSQL as $target)
    {
      $sql = "/* $debugMsg */ DELETE FROM " . $this->db->get_table($this->tables[$target]) .
             " WHERE testproject_id = " . intval($id);                 
      $result = $this->db->exec_query($sql);
    }

    // ---------------------------------------------------------------------------------------
    // delete product itself and items directly related to it like:
    // custom fields assignments
    // custom fields values ( right now we are not using custom fields on test projects)
    // attachments
    if (empty($error))
    {
      $sql = "/* $debugMsg */ DELETE FROM " . $this->db->get_table($this->object_table) . " WHERE id = {$id}";
      
      $result = $this->db->exec_query($sql);
      if ($result)
      {
        $tproject_id_on_session = isset($_SESSION['testprojectID']) ? $_SESSION['testprojectID'] : $id;
        if ($id == $tproject_id_on_session)
        {
          $this->setSessionProject(null);
        }  
      }
      else
      {
        $error .= lang_get('info_product_delete_fails');
      }  
    }
    
    if (empty($error))
    {
      if($this->auditCfg->logEnabled)
      {
        logEvent($event);
      }  
    }
    
    if( !empty($error) )
    {
      $ret['msg']=$error;
      $ret['status_ok']=0;
    }
    
    return $ret;
  }


/*
  function: get_all_testcases_id
            All testproject testcases node id.

  args :idList: comma-separated list of IDs (should be the projectID, but could
                also be an arbitrary suiteID

  returns: array with testcases node id in parameter tcIDs.
           null is nothing found

*/
  function get_all_testcases_id($idList,&$tcIDs,$options = null)
  {
    static $tcNodeTypeID;
    static $tsuiteNodeTypeID;
    static $debugMsg;
    if (!$tcNodeTypeID)
    {
      $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
      $tcNodeTypeID = $this->tree_manager->node_descr_id['testcase'];
      $tsuiteNodeTypeID = $this->tree_manager->node_descr_id['testsuite'];
    }

    $my = array();
    $my['options'] = array('output' => 'just_id');
    $my['options'] = array_merge($my['options'], (array)$options);
  
    switch($my['options']['output']) 
    {
      case 'external_id':
        $use_array = true;
      break;
      
      case 'just_id':
      default:
        $use_array = false;
      break;
    }
    
    $sql = "/* $debugMsg */  SELECT id,node_type_id from ".$this->db->get_table('nodes_hierarchy')." " .
           " WHERE parent_id IN ({$idList})";
    $sql .= " AND node_type_id IN ({$tcNodeTypeID},{$tsuiteNodeTypeID}) "; 
    
    $result = $this->db->exec_query($sql);
    if ($result)
    {
      $suiteIDs = array();
      while($row = $this->db->fetch_array($result))
      {
        if ($row['node_type_id'] == $tcNodeTypeID)
        {
          if( $use_array )
          {
            $sql = " SELECT DISTINCT NH.parent_id, TCV.tc_external_id " . 
                   " FROM ".$this->db->get_table('nodes_hierarchy')." NH " .
                   " JOIN  ".$this->db->get_table('tcversions')." TCV ON TCV.id = NH.id " .
                   " WHERE NH.parent_id = {$row['id']} ";
            
            $rs = $this->db->fetchRowsIntoMap($sql,'parent_id');
            $tcIDs[$row['id']] = $rs[$row['id']]['tc_external_id'];
          }
          else
          {
            $tcIDs[] = $row['id'];
          }
        }
        else
        {
          $suiteIDs[] = $row['id'];
        }
      }
      if (sizeof($suiteIDs))
      {
        $suiteIDs  = implode(",",$suiteIDs);
        $this->get_all_testcases_id($suiteIDs,$tcIDs,$options);
      }
    }  
  }


/*
  function: get_keywords_tcases
            testproject keywords (with related testcase node id),
            that are used on testcases.

  args :testproject_id
        [keyword_id]= 0 -> no filter
                      <> 0 -> look only for this keyword
                      can be an array.



  returns: map: key: testcase_id
                value: map 
                          key: keyword_id
                          value: testcase_id,keyword_id,keyword

                Example:
                 [24] => Array ( [3] => Array( [testcase_id] => 24
                                               [keyword_id] => 3
                                               [keyword] => MaxFactor )
                         
                                 [2] => Array( [testcase_id] => 24
                                               [keyword_id] => 2
                                               [keyword] => Terminator ) )

@internal revisions:
  20100929 - asimon - BUGID 3814: fixed keyword filtering with "and" selected as type
*/
function get_keywords_tcases($testproject_id, $keyword_id=0, $keyword_filter_type='Or')
{
    $keyword_filter= '' ;
    $subquery='';

    if( is_array($keyword_id) )
    {
        $keyword_filter = " AND keyword_id IN (" . implode(',',$keyword_id) . ")";            

        // asimon - BUGID 3814: fixed keyword filtering with "and" selected as type
        if($keyword_filter_type == 'And')
        {
            $subquery = "AND testcase_id IN (" .
                        " SELECT FOXDOG.testcase_id FROM
                          ( SELECT COUNT(testcase_id) AS HITS,testcase_id
                            FROM ".$this->db->get_table('keywords')." K, ".$this->db->get_table('testcase_keywords')."
                            WHERE keyword_id = K.id
                            AND testproject_id = {$testproject_id}
                            {$keyword_filter}
                            GROUP BY testcase_id ) AS FOXDOG " .
                        " WHERE FOXDOG.HITS=" . count($keyword_id) . ")";
                     
            $keyword_filter ='';
        }    
    }
    else if( $keyword_id > 0 )
    {
        $keyword_filter = " AND keyword_id = {$keyword_id} ";
    }
    
    $map_keywords = null;
    $sql = " SELECT testcase_id,keyword_id,keyword
             FROM ".$this->db->get_table('keywords')." K, ".$this->db->get_table('testcase_keywords')."
             WHERE keyword_id = K.id
             AND testproject_id = {$testproject_id}
             {$keyword_filter} {$subquery}
             ORDER BY keyword ASC ";

    $map_keywords = $this->db->fetchMapRowsIntoMap($sql,'testcase_id','keyword_id');

    return($map_keywords);
} //end function

/**
 * get testcase id array order by tcase id
 *
 * @internal revisions
 * 20170611 add by zhouzhaoxin
 */
function get_only_tcase_by_keyword($testproject_id, $keyword_id=0, $keyword_filter_type='Or')
{
    $keyword_filter= '' ;
    $subquery='';

    if( is_array($keyword_id) )
    {
        $keyword_filter = " AND keyword_id IN (" . implode(',',$keyword_id) . ")";

        // asimon - BUGID 3814: fixed keyword filtering with "and" selected as type
        if($keyword_filter_type == 'And')
        {
            $subquery = "AND testcase_id IN (" .
                " SELECT FOXDOG.testcase_id FROM
                          ( SELECT COUNT(testcase_id) AS HITS,testcase_id
                            FROM ".$this->db->get_table('keywords')." K, ".$this->db->get_table('testcase_keywords')."
                            WHERE keyword_id = K.id
                            AND testproject_id = {$testproject_id}
                            {$keyword_filter}
                            GROUP BY testcase_id ) AS FOXDOG " .
                            " WHERE FOXDOG.HITS=" . count($keyword_id) . ")";
                             
                            $keyword_filter ='';
        }
    }
    else if( $keyword_id > 0 )
    {
        $keyword_filter = " AND keyword_id = {$keyword_id} ";
    }

    $map_keywords = null;
    $sql = " SELECT testcase_id
             FROM ".$this->db->get_table('keywords')." K, ".$this->db->get_table('testcase_keywords')."
             WHERE keyword_id = K.id
             AND testproject_id = {$testproject_id}
             {$keyword_filter} {$subquery}
             ORDER BY testcase_id ASC ";
    $map_keywords = $this->db->get_recordset($sql);

    return($map_keywords);
} //end function


/*
  function: get_all_testplans

  args : $testproject_id

         [$filters]: optional map, with optional keys
                     [$get_tp_without_tproject_id]
                     used just for backward compatibility (TL 1.5)
                     default: 0 -> 1.6 and up behaviour

                     [$plan_status]
                     default: null -> no filter on test plan status
                              1 -> active test plans
                              0 -> inactive test plans

                     [$exclude_tplans]: null -> do not apply exclusion
                                        id -> test plan id to exclude
         
         [options]:
         
  returns:
    20100821 - franciscom - added options

*/
function get_all_testplans($testproject_id,$filters=null,$options=null)
{

  $my['options'] = array('fields2get' => 'NH.id,NH.name,notes,active,is_public,testproject_id',
                         'outputType' => null);
  $my['options'] = array_merge($my['options'], (array)$options);

  $forHMLSelect = false;
  if( !is_null($my['options']['outputType']) && $my['options']['outputType'] == 'forHMLSelect')
  {
    $forHMLSelect = true;
    $my['options']['fields2get'] = 'NH.id,NH.name';
  }
  
  $sql = " SELECT {$my['options']['fields2get']} " .
         " FROM ".$this->db->get_table('nodes_hierarchy')." NH,".$this->db->get_table('testplans')." TPLAN";
         
  $where = " WHERE NH.id=TPLAN.id ";
  $where .= " AND (testproject_id = " . $this->db->prepare_int($testproject_id) . " ";
  if( !is_null($filters) )
  {
    $key2check=array('get_tp_without_tproject_id' => 0, 'plan_status' => null,'tplan2exclude' => null);
    
    foreach($key2check as $varname => $defValue)
    {
      $$varname=isset($filters[$varname]) ? $filters[$varname] : $defValue;   
    }                
        
    $where .= " ) ";
    
    if(!is_null($plan_status))
    {
      $my_active = to_boolean($plan_status);
      $where .= " AND active = " . $my_active;
    }
    
    if(!is_null($tplan2exclude))
    {
      $where .= " AND TPLAN.id != {$tplan2exclude} ";
    }
  }
  else
  {
    $where .= ")";  
  }  
  
  $sql .= $where . " ORDER BY name";
  if( $forHMLSelect )
  {
    $map = $this->db->fetchColumnsIntoMap($sql,'id','name');
  }
  else
  {
    $map = $this->db->fetchRowsIntoMap($sql,'id');
  }

  return($map);

}

/*
 function: get_all_builds

 args : 
 $testproject_id
 [$filters]: build_status map to active, other retained
 [options]:  null, retained
  
 returns: build and testplan info

 */
function get_all_builds($testproject_id,$filters=null,$options=null)
{
    $where =  "WHERE NH.id = TPLAN.id and bld.testplan_id = NH.id  AND TPLAN.testproject_id = " . 
        $this->db->prepare_int($testproject_id);
    if (!is_null($filters))
    {
        // add filter where clause 
        $key2check = array('build_status' => null);
        
        foreach ($key2check as $varname => $defValue)
        {
            $$varname=isset($filters[$varname]) ? $filters[$varname] : $defValue;
        }
        
        if (!is_null($build_status))
        {
            $my_active = to_boolean($build_status);
            $where .= " AND bld.active = " . $my_active;
        }
    }
    
    $sql = "SELECT bld.id as build_id, bld.name as build_name, NH.id as testplan_id, " .
        "NH.name as testplan_name, TPLAN.notes, TPLAN.active, TPLAN.is_public, TPLAN.testproject_id " .
//        "FROM builds bld, nodes_hierarchy NH, testplans TPLAN " .
        "FROM ".$this->db->get_table('builds')." bld, ".$this->db->get_table('nodes_hierarchy')." NH, ".$this->db->get_table('testplans')." TPLAN " .
        $where . " ORDER BY bld.name";
        
    $map = $this->db->fetchRowsIntoMap($sql,'build_id');
    return($map);
}


/*
  function: check_tplan_name_existence

  args :
        tproject_id:
        tplan_id:
        [case_sensitive]: 1-> do case sensitive search
                          default: 0

  returns: 1 -> tplan name exists


*/
function check_tplan_name_existence($tproject_id,$tplan_name,$case_sensitive=0)
{
  $sql = " SELECT NH.id, NH.name, testproject_id " .
         " FROM ".$this->db->get_table('nodes_hierarchy')." NH, ".$this->db->get_table('testplans')." testplans " .
           " WHERE NH.id=testplans.id " .
           " AND testproject_id = {$tproject_id} ";

  if($case_sensitive)
  {
      $sql .= " AND NH.name=";
  }
  else
  {
        $tplan_name=strtoupper($tplan_name);
      $sql .= " AND UPPER(NH.name)=";
  }
  $sql .= "'" . $this->db->prepare_string($tplan_name) . "'";
    $result = $this->db->exec_query($sql);
    $status= $this->db->num_rows($result) ? 1 : 0;

  return $status;
}


 /*
    function: gen_combo_first_level_test_suites
              create array with test suite names

    args :  id: testproject_id
            [mode]

    returns:
            array, every element is a map

    rev :
          20070219 - franciscom
          fixed bug when there are no children

*/
function get_first_level_test_suites($tproject_id,$mode='simple',$opt=null)
{
  $fl=$this->tree_manager->get_children($tproject_id,
                                        array( 'testcase', 'exclude_me',
                                               'testplan' => 'exclude_me',
                                               'requirement_spec' => 'exclude_me' ),$opt);
  switch ($mode)
  {
    case 'simple':
    break;

    case 'smarty_html_options':
    if( !is_null($fl) && count($fl) > 0)
    {
      foreach($fl as $idx => $map)
      {
        $dummy[$map['id']]=$map['name'];
      }
      $fl=null;
      $fl=$dummy;
    }
    break;
  }
  return($fl);
}



/**
 * getTCasesLinkedToAnyTPlan
 *
 * for target test project id ($id) get test case id of
 * every test case that has been assigned at least to one of all test plans
 * belonging to test project. 
 *
 * @param int $id test project id
 *
 */
function getTCasesLinkedToAnyTPlan($id)
{
  $tplanNodeType = $this->tree_manager->node_descr_id['testplan'];
  
  // len of lines must be <= 100/110 as stated on development standard guide.
    $sql = " SELECT DISTINCT NHTCV.parent_id AS testcase_id " .
           " FROM ".$this->db->get_table('nodes_hierarchy')." NHTCV " .
           " JOIN ".$this->db->get_table('testplan_tcversions')." TPTCV " .
           " ON NHTCV.id = TPTCV.tcversion_id ";
    
    // get testplan id for target test�project, to get test case versions linked to testplan.
    $sql .= " JOIN ".$this->db->get_table('nodes_hierarchy')." NHTPLAN " .
            " ON TPTCV.testplan_id = NHTPLAN.id  " .
            " WHERE NHTPLAN.node_type_id = {$tplanNodeType} AND NHTPLAN.parent_id = " . intval($id);
    $rs = $this->db->fetchRowsIntoMap($sql,'testcase_id');
    
    return $rs;
}


/**
 * getFreeTestCases
 *
 *
 * @param int $id test project id
 * @param $options for future uses.
 */
function getFreeTestCases($id,$options=null)
{
  $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $retval['items']=null;
    $retval['allfree']=false;
    
    $all=array(); 
    $this->get_all_testcases_id($id,$all);
    $linked=array();
    $free=null;
    if(!is_null($all))
    {
        $all=array_flip($all);
        $linked=$this->getTCasesLinkedToAnyTPlan($id);
        $retval['allfree']=is_null($linked); 
        $free=$retval['allfree'] ? $all : array_diff_key($all,$linked);
    }
    
    if( !is_null($free) && count($free) > 0)
    {
        $in_clause=implode(',',array_keys($free));
         $sql = " /* $debugMsg */ " .
              " SELECT MAX(TCV.version) AS version, TCV.tc_external_id, " .
                " TCV.importance AS importance, NHTCV.parent_id AS id, NHTC.name " .
                " FROM ".$this->db->get_table('tcversions')." TCV " .
                " JOIN ".$this->db->get_table('nodes_hierarchy')." NHTCV " .
                " ON NHTCV.id = TCV.id " .
             " JOIN ".$this->db->get_table('nodes_hierarchy')." NHTC " .
                " ON NHTC.id = NHTCV.parent_id " .
             " WHERE NHTCV.parent_id IN ({$in_clause}) " .
             " GROUP BY NHTC.name,NHTCV.parent_id,TCV.tc_external_id,TCV.importance " . 
             " ORDER BY NHTCV.parent_id";
      $retval['items']=$this->db->fetchRowsIntoMap($sql,'id');       
    }

    
    return $retval;
}


// -------------------------------------------------------------------------------
// Custom field related methods
// -------------------------------------------------------------------------------
/*
  function: get_linked_custom_fields
            Get custom fields that has been linked to testproject.
            Search can be narrowed by:
            node type
            node id

            Important:
            custom fields id will be sorted based on the sequence number
            that can be specified at User Interface (UI) level, while
            linking is done.

  args : id: testproject id
         [node_type]: default: null -> no filter
                      verbose string that identifies a node type.
                      (see tree class, method get_available_node_types).
                      Example:
                      You want linked custom fields , but can be used
                      only on testcase -> 'testcase'.

  returns: map.
           key: custom field id
           value: map (custom field definition) with following keys

           id   (custom field id)
           name
           label
           type
           possible_values
           default_value
           valid_regexp
           length_min
           length_max
           show_on_design
           enable_on_design
           show_on_execution
           enable_on_execution
           display_order


*/
function get_linked_custom_fields($id,$node_type=null,$access_key='id')
{
  $additional_table="";
  $additional_join="";

  if( !is_null($node_type) )
  {
    $hash_descr_id = $this->tree_manager->get_available_node_types();
    $node_type_id=$hash_descr_id[$node_type];

    $additional_table=",".$this->db->get_table('cfield_node_types')." CFNT ";
    $additional_join=" AND CFNT.field_id=CF.id AND CFNT.node_type_id={$node_type_id} ";
  }
  
  $sql="SELECT CF.*,CFTP.display_order " .
       " FROM ".$this->db->get_table('custom_fields')." CF, ".$this->db->get_table('cfield_testprojects')." CFTP " .
       $additional_table .
       " WHERE CF.id=CFTP.field_id " .
       " AND   CFTP.testproject_id={$id} " .
       $additional_join .
       " ORDER BY CFTP.display_order";
  $map = $this->db->fetchRowsIntoMap($sql,$access_key);
  return($map);
}



/*
function: copy_as
          creates a new test project using an existent one as source.


args: id: source testproject id
      new_id: destination
      [new_name]: default null.
                  != null => set this as the new name

      [copy_options]: default null
                      null: do a deep copy => copy following child elements:
                      test plans
                      builds
                      linked tcversions
                      milestones
                      user_roles
                      priorities,
                      platforms
                      execution assignment.
                          
                    != null, a map with keys that controls what child elements to copy


returns: N/A

@internal revisions
20110405 - franciscom - BUGID 4374: When copying a project, external TC ID is not preserved  
*/
function copy_as($id,$new_id,$user_id,$new_name=null,$options=null)
{
  $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

  $my['options'] = array('copy_requirements' => 1,'copy_user_roles' => 1,'copy_platforms' => 1);
  $my['options'] = array_merge($my['options'], (array)$options);

  // get source test project general info
  $rs_source=$this->get_by_id($id);
  
  if(!is_null($new_name))
  {
    $sql="/* $debugMsg */ UPDATE ".$this->db->get_table('nodes_hierarchy')." " .
         "SET name='" . $this->db->prepare_string(trim($new_name)) . "' " .
         "WHERE id={$new_id}";
    $this->db->exec_query($sql);
  }


  // Copy elements that can be used by other elements
  // Custom Field assignments
  $this->copy_cfields_assignments($id,$new_id);  

  // Keywords
  $oldNewMappings['keywords'] = $this->copy_keywords($id,$new_id);

  // Platforms
  $oldNewMappings['platforms'] = $this->copy_platforms($id,$new_id);
  
  // Requirements
  if( $my['options']['copy_requirements'] )
  {
    $oldNewMappings['requirements'] = $this->copy_requirements($id,$new_id,$user_id);
  
    // need to copy relations between requirements
    $rel = null;
    if (count($oldNewMappings['requirements'], COUNT_NORMAL) > 0)
    {
        foreach ($oldNewMappings['requirements'] as $okey => $nkey) 
        {
          $sql = "/* $debugMsg */ SELECT id, source_id, destination_id," .
                 " relation_type, author_id, creation_ts " . 
                 " FROM ".$this->db->get_table('req_relations')." " .
                 " WHERE source_id=$okey OR destination_id=$okey ";
    
          $rel[$okey] = $this->db->get_recordset($sql);
        }
    }

    if(!is_null($rel))
    {
      $totti = $this->db->db_now();
      foreach($rel as $okey => $ir)
      {
        if(!is_null($ir))
        {
          foreach ($ir as $rval) 
          {
            if( isset($done[$rval['id']]) )
            {
              continue;
            }  
            
            $done[$rval['id']] = $rval['id']; 
            $sql = "/* $debugMsg */ INSERT INTO ".$this->db->get_table('req_relations')." "  . 
                   " (source_id, destination_id, relation_type, author_id, creation_ts) " .
                   " values (" .
                   $oldNewMappings['requirements'][$rval['source_id']] . "," .
                   $oldNewMappings['requirements'][$rval['destination_id']] . "," .
                   $rval['relation_type'] . "," . $rval['author_id'] . "," .
                   "$totti)";
            $this->db->exec_query($sql);
          }
        }  
      }  
    }
  }

  // need to get subtree and create a new one
  $filters = array();
  $filters['exclude_node_types'] = array('testplan' => 'exclude_me','requirement_spec' => 'exclude_me');
  $filters['exclude_children_of'] = array('testcase' => 'exclude_me', 'requirement' => 'exclude_me',
                                          'testcase_step' => 'exclude_me');
                   
  $elements = $this->tree_manager->get_children($id,$filters['exclude_node_types']);

  // Copy Test Specification
  $item_mgr['testsuites'] = new testsuite($this->db);
  $copyTSuiteOpt = array();
  $copyTSuiteOpt['preserve_external_id'] = true;
  $copyTSuiteOpt['copyKeywords'] = 1;

  // Attention: copyRequirements really means copy requirement to testcase assignments
  $copyTSuiteOpt['copyRequirements'] = $my['options']['copy_requirements'];    
  
  $oldNewMappings['test_spec'] = array();
  if (count($elements, COUNT_NORMAL) > 0)
  {
      foreach($elements as $piece)
      {
        $op = $item_mgr['testsuites']->copy_to($piece['id'],$new_id,$user_id,$copyTSuiteOpt,$oldNewMappings);        
        $oldNewMappings['test_spec'] += $op['mappings'];
      }
  }

  // Copy Test Plans and all related information
  $this->copy_testplans($id,$new_id,$user_id,$oldNewMappings);
    
  $this->copy_user_roles($id,$new_id);

  // 20120831 - need to understand if we need to change this and PRESERVE External Test case ID
  //
  // When copying a project, external TC ID is not preserved  
  // need to update external test case id numerator
  $sql = "/* $debugMsg */ UPDATE " . $this->db->get_table($this->object_table) .
         " SET tc_counter = {$rs_source['tc_counter']} " . 
         " WHERE id = {$new_id}";
  $recordset = $this->db->exec_query($sql);

  

} // end function copy_as


/**
 * function to get an array with all requirement IDs in testproject
 * 
 * @param string $IDList commaseparated list of Container-IDs - can be testproject ID or reqspec IDs 
 * @return array $reqIDs result IDs
 * 
 * @internal revisions:
 * 20100310 - asimon - removed recursion logic
 */
public function get_all_requirement_ids($IDList) {
  
  $coupleTypes = array();
  $coupleTypes['target'] = $this->tree_manager->node_descr_id['requirement'];
  $coupleTypes['container'] = $this->tree_manager->node_descr_id['requirement_spec'];
  
  $reqIDs = array();
  $this->tree_manager->getAllItemsID($IDList,$reqIDs,$coupleTypes);

  return $reqIDs;
}


/**
 * uses get_all_requirements_ids() to count all requirements in testproject
 * 
 * @param integer $tp_id ID of testproject
 * @return integer count of requirements in given testproject
 */
public function count_all_requirements($tp_id) {
  return count($this->get_all_requirement_ids($tp_id));
}

/**
 * Copy user roles to a new Test Project
 * 
 * @param int $source_id original Test Project identificator
 * @param int $target_id new Test Project identificator
 */
private function copy_user_roles($source_id, $target_id)
{
  $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
  
  $sql = "/* $debugMsg */ SELECT * FROM ".$this->db->get_table('user_testproject_roles')." " .
         "WHERE testproject_id={$source_id} ";
  $rs=$this->db->get_recordset($sql);

  if(!is_null($rs))
  {
      foreach($rs as $elem)
      {
          $sql="/* $debugMsg */ INSERT INTO ".$this->db->get_table('user_testproject_roles')."  " .
               "(testproject_id,user_id,role_id) " .
               "VALUES({$target_id}," . $elem['user_id'] ."," . $elem['role_id'] . ")";
          $this->db->exec_query($sql);
    }
  }
}


/**
 * Copy platforms
 * 
 * @param int $source_id original Test Project identificator
 * @param int $target_id new Test Project identificator
 */
private function copy_platforms($source_id, $target_id)
{
  $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
  $platform_mgr = new tlPlatform($this->db,$source_id);
  $old_new = null;
  
  $platformSet = $platform_mgr->getAll();

  if( !is_null($platformSet) )
  {
    $platform_mgr->setTestProjectID($target_id);
    foreach($platformSet as $platform)
    {
      $op = $platform_mgr->create($platform['name'],$platform['notes']);
      $old_new[$platform['id']] = $op['id'];
    }
  }
  return $old_new;
}


/**
 * Copy platforms
 * 
 * @param int $source_id original Test Project identificator
 * @param int $target_id new Test Project identificator
 */
private function copy_keywords($source_id, $target_id)
{
  $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
  $old_new = null;
  $sql = "/* $debugMsg */ SELECT * FROM ".$this->db->get_table('keywords')." " .
       " WHERE testproject_id = {$source_id}";
       
  $itemSet = $this->db->fetchRowsIntoMap($sql,'id');
  if( !is_null($itemSet) )
  {
    foreach($itemSet as $item)
    {
      $op = $this->addKeyword($target_id,$item['keyword'],$item['notes']);
      $old_new[$item['id']] = $op['id'];
    }
  }
  return $old_new;
}





/**
 * 
 *
 */
private function copy_cfields_assignments($source_id, $target_id)
{
  $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = "/* $debugMsg */ " . 
           " SELECT field_id FROM ".$this->db->get_table('cfield_testprojects')." " .
           " WHERE testproject_id = {$source_id}";
    $row_set = $this->db->fetchRowsIntoMap($sql,'field_id');   
  if( !is_null($row_set) )
  {
    $cfield_set = array_keys($row_set);
    $this->cfield_mgr->link_to_testproject($target_id,$cfield_set);
  }
}


/**
 * 
 *
 */
private function copy_testplans($source_id,$target_id,$user_id,$mappings)
{
  static $tplanMgr;
  
  $tplanSet = $this->get_all_testplans($source_id);
  if( !is_null($tplanSet) )
  {
    $keySet = array_keys($tplanSet);
    if( is_null($tplanMgr) )
    {
      $tplanMgr = new testplan($this->db);
    }
    
    foreach($keySet as $itemID)
    {
      $new_id = $tplanMgr->create($tplanSet[$itemID]['name'],$tplanSet[$itemID]['notes'],
                                  $target_id,$tplanSet[$itemID]['active'],$tplanSet[$itemID]['is_public']);

      if( $new_id > 0 )
      {
        // TICKET 5190: Copy Test projects - tester assignments to testplan+build are not copied
        $tplanMgr->copy_as($itemID,$new_id,null,$target_id,$user_id,array('copy_assigned_to' => 1),$mappings);
      }                       
    }
    
  }
}


/**
 * 
 *
 */
private function copy_requirements($source_id,$target_id,$user_id)
{
  $mappings = null;

  // need to get subtree and create a new one
  $filters = array();
  $filters['exclude_node_types'] = array('testplan' => 'exclude','testcase' => 'exclude',
                                         'testsuite' => 'exclude','requirement' => 'exclude');
                   
  $elements = $this->tree_manager->get_children($source_id,$filters['exclude_node_types']);
  if( !is_null($elements) )
  {
    $mappings = array();
    $reqSpecMgr = new requirement_spec_mgr($this->db);
    
    // Development Note - 20110817
    // why we choose to do not copy testcase_assignments ?
    // Because due to order used to copy different items, when we ask to copy
    // requirements WE DO NOT HAVE TEST CASES on new test project.
    //
    $options = array('copy_also' => array('testcase_assignments' => false), 
                     'caller' => 'copy_testproject');
    
    $rel = null;
    foreach($elements as $piece)
    {
      $op = $reqSpecMgr->copy_to($piece['id'],$target_id,$target_id,$user_id,$options);
      $mappings += $op['mappings'];
    }
  }
  return (!is_null($mappings) && isset($mappings['req'])) ? $mappings['req'] : null;
}








/**
 * getTestSpec
 * 
 * get structure with Test suites and Test Cases
 * Filters that act on test cases work on attributes that are common to all
 * test cases versions: test case name
 *
 * Development Note:
 * Due to the tree structure is not so easy to try to do as much as filter as
 * possibile using SQL.
 *
 *
 * @param int id test project ID
 * @param mixed filters
 * @param mixed options
 *        recursive true/false changes output format
 *        testcase_name filter in LIKE %string%, if will be case sensitive or not
 *        will depend of DBMS.
 *
 * 
 * @return
 *
 * @internal revisions
 * 20121010 - asimon - TICKET 4217: added filter for importance
 */
function getTestSpec($id,$filters=null,$options=null)
{

  $items = array();

  $my['options'] = array('recursive' => false, 'exclude_testcases' => false, 
                           'remove_empty_branches' => false);
                 
  $my['filters'] = array('exclude_node_types' => $this->nt2exclude,
                          'exclude_children_of' => $this->nt2exclude_children,
                          'exclude_branches' => null,
                          'testcase_name' => null, 
                          'importance' => null, 'testcase_id' => null, 'execution_type' => null,
                          'status' => null,
                          'assign_status' => null,
                          'additionalWhereClause' => null);      
 
  $my['filters'] = array_merge($my['filters'], (array)$filters);
  $my['options'] = array_merge($my['options'], (array)$options);
 
  if( $my['options']['exclude_testcases'] )
  {
    $my['filters']['exclude_node_types']['testcase']='exclude me';
  }
  
  // transform some of our options/filters on something the 'worker' will understand
  // when user has request filter by test case name, we do not want to display empty branches
  // If we have choose any type of filter, we need to force remove empty test suites
  // TICKET 4217: added filter for importance
  if( !is_null($my['filters']['testcase_name']) || !is_null($my['filters']['testcase_id']) ||
      !is_null($my['filters']['execution_type']) || !is_null($my['filters']['exclude_branches']) ||
      !is_null($my['filters']['importance']) || $my['options']['remove_empty_branches'] )
  {
    $my['options']['remove_empty_nodes_of_type'] = 'testsuite';
  }
  
  $method2call = '_get_subtree_abs';
  $qnum = $this->$method2call($id,$items,$my['filters'],$my['options']);
  return $items;
}

/**
 * get tree info not by rec but abs
 * @return tree node array
 *
 * @internal revisions
 * 20170611 add by zhouzhaoxin to improve performance
 */
function getTestSpecFast($id,$filters=null,$options=null)
{

    $items = array();

    $my['options'] = array('exclude_testcases' => false,
        'remove_empty_branches' => false);
     
    $my['filters'] = array('exclude_node_types' => $this->nt2exclude,
        'exclude_children_of' => $this->nt2exclude_children,
        'exclude_branches' => null,
        'testcase_name' => null,
        'importance' => null, 'testcase_id' => null, 'execution_type' => null,
        'status' => null,
        'assign_status' => null,
        'additionalWhereClause' => null,
        'priority' => null
    );

    $my['filters'] = array_merge($my['filters'], (array)$filters);
    $my['options'] = array_merge($my['options'], (array)$options);

    if( $my['options']['exclude_testcases'] )
    {
        $my['filters']['exclude_node_types']['testcase']='exclude me';
    }

    // transform some of our options/filters on something the 'worker' will understand
    // when user has request filter by test case name, we do not want to display empty branches
    // If we have choose any type of filter, we need to force remove empty test suites
    // TICKET 4217: added filter for importance
    if( !is_null($my['filters']['testcase_name']) || !is_null($my['filters']['testcase_id']) ||
        !is_null($my['filters']['execution_type']) || !is_null($my['filters']['exclude_branches']) ||
        !is_null($my['filters']['importance']) || $my['options']['remove_empty_branches'] )
    {
        $my['options']['remove_empty_nodes_of_type'] = 'testsuite';
    }

    $items = $this->_get_subtree_abs($id,$my['filters'],$my['options']);
    return $items;
}

/**
 * get tree info not by rec but abs
 * @return tree node array
 * 
 * @internal revisions
 * 20170610 add by zhouzhaoxin to improve performance
 */
function _get_subtree_abs($node_id, $filters = null, $options = null)
{
    // init filters
    $my['filters'] = array('exclude_children_of' => null,'exclude_branches' => null,
        'additionalWhereClause' => '', 'testcase_name' => null,
        'testcase_id' => null,'active_testcase' => false,
        'importance' => null, 'status' => null, 'assign_status' => null);
     
    $my['options'] = array('remove_empty_nodes_of_type' => null);
    
    $my['filters'] = array_merge($my['filters'], (array)$filters);
    $my['options'] = array_merge($my['options'], (array)$options); 
    
    $tcaseFilter['name'] = !is_null($my['filters']['testcase_name']);
    $tcaseFilter['id'] = !is_null($my['filters']['testcase_id']); 
    
    $tcversionFilter['execution_type'] = !is_null($my['filters']['execution_type']);
    $tcversionFilter['importance'] = !is_null($my['filters']['importance']);
    $tcversionFilter['status'] = !is_null($my['filters']['status']);
    $tcversionFilter['assign_status'] = !is_null($my['filters']['assign_status']) && $my['filters']['assign_status'];
    
    if( !is_null($my['options']['remove_empty_nodes_of_type']) )
    {
        // this way I can manage code or description
        if( !is_numeric($my['options']['remove_empty_nodes_of_type']) )
        {
            $my['options']['remove_empty_nodes_of_type'] =
            $this->tree_manager->node_descr_id[$my['options']['remove_empty_nodes_of_type']];
        }
    } 
    
    // get testcase array
    $tcase_sql = "select nh.id, nh.parent_id, nh.name, nh.node_type_id, nh.node_order, " .
        " nh.node_depth_abs, '' as external_id from " .
        $this->db->get_table('nodes_hierarchy') . " nh " .
        " where nh.node_type_id = 3 ";

    if ($tcaseFilter['name'])
    {
        $tcase_sql .= " and nh.name like '%{$my['filters']['testcase_name']}%' ";
    }
    
    if ($tcaseFilter['id'])
    {
        $tcase_sql .= " and nh.id = {$my['filters']['testcase_id']} ";
    }
    
    $tcase_sql .= " order by nh.id ";
    $tcase_set = $this->db->get_recordset($tcase_sql);
    
    if (count($tcase_set) == 0)
    {
        return null;
    }
    
    $tcversion_sql = "select tvs.tc_id,tvs.tcversion_id, tv.tc_external_id from " .
        $this->db->get_table('tcversions') . " tv " .
        " join (select nhtc.parent_id as tc_id, nhtc.node_order as node_order, " .
		" tcv.id AS tcversion_id, max(tcv.version) as version from (select * from " .
		$this->db->get_table('tcversions') . " order by version desc) tcv join " .
		$this->db->get_table('nodes_hierarchy') . " nhtc on nhtc.id = tcv.id " .
		" group by nhtc.parent_id )  tvs on tv.id = tvs.tcversion_id ";

    $has_filter = false;    
    if ($tcversionFilter['execution_type'])
    {
        $has_filter = true;
        $tcversion_sql .=
        " where tv.execution_type = " . $my['filters']['execution_type'];
    }

    if ($tcversionFilter['status'])
    {
        if ($has_filter)
        {
            $tcversion_sql .=
                " and tv.status in (" . implode(',',$my['filters']['status']) . ')';
        }
        else 
        {
            $has_filter = true;
            $tcversion_sql .=
                " where  tv.status in (" . implode(',',$my['filters']['status']) . ')';
        }
    }

    if ($tcversionFilter['importance'])
    {
        if ($has_filter)
        {
            $tcversion_sql .=
                " and tv.importance in (" . implode(',',$my['filters']['importance']) . ')';
        }
        else 
        {
            $has_filter = true;
            $tcversion_sql .=
                " where tv.importance in (" . implode(',',$my['filters']['importance']) . ')';
        }
    }
    
    $tcversion_sql .= " order by tvs.tc_id, tvs.node_order";

    $tcversion_set = $this->db->get_recordset($tcversion_sql);
    
    if (count($tcase_set) == 0)
    {
        return null;
    }
     
    // filter assignment
    $tcv_assign_set = array();
    $tcase_filter_set = array();
    if ($tcversionFilter['assign_status'])
    {
        $tplan_id = $_SESSION['testplanID'];
        $session_key = $tplan_id . '_stored_setting_build';
        $build_id = $_SESSION[$session_key];
        $tcv_assign_sql = "select nh.parent_id from " .
	        $this->db->get_table('testplan_tcversions') . " tt " .
            " join " .  $this->db->get_table('nodes_hierarchy') . " nh on tt.tcversion_id = nh.id " .
            " where tt.build_id = '" . $build_id . "' order by nh.parent_id";
        $tcv_assign_set = $this->db->get_recordset($tcv_assign_sql);

        $tcase_count = count($tcase_set, COUNT_NORMAL);
        $tcv_assign_count = count($tcv_assign_set, COUNT_NORMAL);
        $idx = 0;
        $jdx = 0;
        $tcv_assign_to_end = false;
        while ($idx < $tcase_count)
        {
            if ($tcv_assign_to_end)
            {
                if ($my['filters']['assign_status'] != 1)
                {
                    $tcase_filter_set[] = $tcase_set[$idx];
                }
                $idx++;
                continue;
            }
            
            if ($tcase_set[$idx]['id'] == $tcv_assign_set[$jdx]['parent_id'])
            {
                if ($my['filters']['assign_status'] == 1)
                {
                    $tcase_filter_set[] = $tcase_set[$idx];
                }
                $idx++;
                if ($jdx < $tcv_assign_count - 1)
                {
                    $jdx++;
                }
                else 
                {
                    $tcv_assign_to_end = true;
                }
            }
            else if ($tcase_set[$idx]['id'] < $tcv_assign_set[$jdx]['parent_id'])
            {
                if ($my['filters']['assign_status'] != 1)
                {
                    $tcase_filter_set[] = $tcase_set[$idx];
                }
                $idx++;
            }
            else 
            {
                if ($jdx < $tcv_assign_count - 1)
                {
                    $jdx++;
                }
                else 
                {
                    $tcv_assign_to_end = true;
                }
            }
        }
    }
    
    //filter tcversions
    $tcase_final_set = array();
    if (!$tcversionFilter['assign_status'])
    {
        $tcase_filter_set = $tcase_set;
    }
    $tcase_count = count($tcase_filter_set, COUNT_NORMAL);
    $tcv_count = count($tcversion_set, COUNT_NORMAL);
    $idx = 0;
    $jdx = 0;
    $tcv_to_end = false;
    while ($idx < $tcase_count)
    {
        if ($tcv_to_end)
        {
            $idx++;
            continue;
        }
    
        if ($tcase_filter_set[$idx]['id'] == $tcversion_set[$jdx]['tc_id'])
        {
            $node = $tcase_filter_set[$idx];
            $node['external_id'] = $tcversion_set[$jdx]['tc_external_id'];
            $tcase_final_set[] = $node;
            $idx++;
            if ($jdx < $tcv_count - 1)
            {
                $jdx++;
            }
            else
            {
                $tcv_to_end = true;
            }
        }
        else if ($tcase_filter_set[$idx]['id'] < $tcversion_set[$jdx]['tc_id'])
        {
            $idx++;
        }
        else
        {
            if ($jdx < $tcv_count - 1)
            {
                $jdx++;
            }
            else
            {
                $tcv_to_end = true;
            }
        }
    }
    
    return $tcase_final_set;
}


/**
 * 
 * @return
 *
 * @internal revisions
 */
function _get_subtree_rec($node_id,&$pnode,$filters = null, $options = null)
{
  static $qnum;
  static $my;
  static $exclude_branches;
  static $exclude_children_of;
  static $node_types;
  static $tcaseFilter;
  static $tcversionFilter;
  static $childFilterOn;
  static $staticSql;
  static $inClause;

  if (!$my)
  {
    $qnum=0;
    $node_types = array_flip($this->tree_manager->get_available_node_types());
        
    $my['filters'] = array('exclude_children_of' => null,'exclude_branches' => null,
                           'additionalWhereClause' => '', 'testcase_name' => null,
                           'testcase_id' => null,'active_testcase' => false, 
                           'importance' => null, 'status' => null, 'assign_status' => null);
                           
    $my['options'] = array('remove_empty_nodes_of_type' => null);

    $my['filters'] = array_merge($my['filters'], (array)$filters);
    $my['options'] = array_merge($my['options'], (array)$options);

    $exclude_branches = $my['filters']['exclude_branches'];
    $exclude_children_of = $my['filters']['exclude_children_of'];  


    $tcaseFilter['name'] = !is_null($my['filters']['testcase_name']);
    $tcaseFilter['id'] = !is_null($my['filters']['testcase_id']);
    
    $tcaseFilter['is_active'] = !is_null($my['filters']['active_testcase']) && $my['filters']['active_testcase'];
    $tcaseFilter['enabled'] = $tcaseFilter['name'] || $tcaseFilter['id'] || $tcaseFilter['is_active'];

    $tcversionFilter['execution_type'] = !is_null($my['filters']['execution_type']);
    $tcversionFilter['importance'] = !is_null($my['filters']['importance']);
    $tcversionFilter['status'] = !is_null($my['filters']['status']);
    $tcversionFilter['assign_status'] = !is_null($my['filters']['assign_status']) && $my['filters']['assign_status'];

    $actOnVersion = array('execution_type','importance','status','assign_status');

    $tcversionFilter['enabled'] = false;
    foreach($actOnVersion as $target)
    {
      $tcversionFilter['enabled'] = $tcversionFilter['enabled'] ||  $tcversionFilter[$target];
    }  

    $childFilterOn = $tcaseFilter['enabled'] || $tcversionFilter['enabled'];

    if( !is_null($my['options']['remove_empty_nodes_of_type']) )
    {
      // this way I can manage code or description      
      if( !is_numeric($my['options']['remove_empty_nodes_of_type']) )
      {
        $my['options']['remove_empty_nodes_of_type'] = 
                $this->tree_manager->node_descr_id[$my['options']['remove_empty_nodes_of_type']];
      }
    }

    // Create invariant sql sentences
    $tfields = "NH.id, NH.parent_id, NH.name, NH.node_type_id, NH.node_order, '' AS external_id ";
    $staticSql = " SELECT DISTINCT {$tfields} " .
                 " FROM ".$this->db->get_table('nodes_hierarchy')." NH ";
    
    // Generate IN Clauses
    $inClause['status'] = $inClause['importance'] = ' ';
    if( $tcversionFilter['status'] )
    {
      $inClause['status'] = 
        " TCV.status IN (" . implode(',',$my['filters']['status']) . ')';
    }

    if( $tcversionFilter['importance'] )
    {
      $inClause['importance'] = 
        " TCV.importance IN (" . implode(',',$my['filters']['importance']) . ')';
    }
    
    if ($tcversionFilter['assign_status'] && $my['filters']['assign_status'])
    {
        $tplan_id = $_SESSION['testplanID'];
        $session_key = $tplan_id . '_stored_setting_build';
        $build_id = $_SESSION[$session_key];
        if ($my['filters']['assign_status'] == 1)
        {
            $inClause['assign_status'] =
                " TCV.id in (select tcversion_id from " . 
                $this->db->get_table('testplan_tcversions') . " where build_id = '" . $build_id . "')";
        }
        else 
        {
            $inClause['assign_status'] =
                " TCV.id not in (select tcversion_id from " .
                $this->db->get_table('testplan_tcversions') . " where build_id = '" . $build_id . "')";
        }
    }


  }
  $sql =  $staticSql . " WHERE NH.parent_id = {$node_id} " .
          " AND (" .
          "      NH.node_type_id = {$this->tree_manager->node_descr_id['testsuite']} " .
          "      OR (NH.node_type_id = {$this->tree_manager->node_descr_id['testcase']} ";
  
  if( $tcaseFilter['enabled'] )
  {
    foreach($tcaseFilter as $key => $apply)
    {
      if( $apply )
      {
        switch($key)
        {
          case 'name':
             $sql .= " AND NH.name LIKE '%{$my['filters']['testcase_name']}%' ";
          break;
          
          case 'id':
                   $sql .= " AND NH.id = {$my['filters']['testcase_id']} ";
          break;
        }
      }
    }
  }
  $sql .= " )) ";
  $sql .= " ORDER BY NH.node_order,NH.id";
  
  
  // Approach Change - get all 
  $rs = $this->db->fetchRowsIntoMap($sql,'id');
  if( count($rs) == 0 )
  {
    return $qnum;
  }

    // create list with test cases nodes
  $tclist = null;
  $ks = array_keys($rs);
  foreach($ks as $ikey)
  {
    if( $rs[$ikey]['node_type_id'] == $this->tree_manager->node_descr_id['testcase'] )
    {
      $tclist[$rs[$ikey]['id']] = $rs[$ikey]['id'];
    }
  }    
  if( !is_null($tclist) )
  {
    $filterOnTC = false;
    $glav = " /* Get LATEST ACTIVE tcversion ID */ " .  
            " SELECT MAX(TCVX.id) AS tcversion_id, NHTCX.parent_id AS tc_id " .
            " FROM ".$this->db->get_table('tcversions')." TCVX " . 
            " JOIN ".$this->db->get_table('nodes_hierarchy')." NHTCX " .
            " ON NHTCX.id = TCVX.id AND TCVX.active = 1 " .
            " WHERE NHTCX.parent_id IN (" . implode($tclist,',') . ")" .
            " GROUP BY NHTCX.parent_id,TCVX.tc_external_id  ";

    $ssx = " /* Get LATEST ACTIVE tcversion MAIN ATTRIBUTES */ " .
           " SELECT TCV.id AS tcversion_id, TCV.tc_external_id AS external_id, SQ.tc_id " .
            " FROM ".$this->db->get_table('tcversions')." TCV " . 
            " JOIN ( $glav ) SQ " .
            " ON TCV.id = SQ.tcversion_id ";


    // We can add here keyword filtering if exist ?
    if( $tcversionFilter['enabled'] || $tcaseFilter['is_active'] )
    {      
      $addAnd = false;
      if ($tcversionFilter['importance'] || $tcversionFilter['execution_type'] || 
          $tcversionFilter['status'] || $tcversionFilter['assign_status'])
      {
        $ssx .= " WHERE ";
      }
           
      if( $tcversionFilter['importance'] )
      {
        $ssx .= $inClause['importance'];
        $filterOnTC = true;
        $addAnd = true;
      }

      if( $addAnd && $tcversionFilter['execution_type'])
      {
        $ssx .= " AND ";
      }
            
      if( $tcversionFilter['execution_type'] )
      {
        $ssx .= " TCV.execution_type = " . $my['filters']['execution_type'];
        $filterOnTC = true;
        $addAnd = true;
      }  

      if( $addAnd && $tcversionFilter['status'])
      {
        $ssx .= " AND ";
      }
            
      if( $tcversionFilter['status'] )
      {
        $ssx .= $inClause['status'];
        $filterOnTC = true;
        $addAnd = true;
      }  
          
      if ($tcversionFilter['assign_status'] && $my['filters']['assign_status'])
      {
          if ($addAnd)
          {
              $ssx .= " AND ";
          }
          $ssx .= $inClause['assign_status'];
          $filterOnTC = true;
          $addAnd = true;
      }
    }   
    
    $highlander = $this->db->fetchRowsIntoMap($ssx,'tc_id');
    if( $filterOnTC )
    {
      $ky = !is_null($highlander) ? array_diff_key($tclist,$highlander) : $tclist;
      if( count($ky) > 0 )
      {
        foreach($ky as $tcase)
        {
          unset($rs[$tcase]);            
        }
      }
    }
    
  }
  
   foreach($rs as $row)
   {
    if(!isset($exclude_branches[$row['id']]))
    {  
      $node = $row + array('node_table' => $this->tree_manager->node_tables_by['id'][$row['node_type_id']]);
      $node['childNodes'] = null;
      if($node['node_table'] == 'testcases')
      {
        $node['leaf'] = true; 
        // TICKET 5228: Filter use on test spec causes "undefined index" warning in event log
        //              for every test case with no active version
        $node['external_id'] = isset($highlander[$row['id']]) ? $highlander[$row['id']]['external_id'] : null;
      }      
      
      // why we use exclude_children_of ?
          // 1. Sometimes we don't want the children if the parent is a testcase,
          //    due to the version management
          //
          if(!isset($exclude_children_of[$node_types[$row['node_type_id']]]))
          {
            // Keep walking (Johny Walker Whisky)
            $this->_get_subtree_rec($row['id'],$node,$my['filters'],$my['options']);
          }

         
      // Have added this logic, because when export test plan will be developed
      // having a test spec tree where test suites that do not contribute to test plan
      // are pruned/removed is very important, to avoid additional processing
      //            
      // If node has no childNodes, we check if this kind of node without children
      // can be removed.
      //
        $doRemove = is_null($node['childNodes']) && 
                  ($node['node_type_id'] == $my['options']['remove_empty_nodes_of_type']);
        if(!$doRemove)
        {
          $pnode['childNodes'][] = $node;
        }  
    } // if(!isset($exclude_branches[$rowID]))
  } //while
  return $qnum;
}


/**
 * get just test case id filtered by keywords  
 * developed to be used on test spec tree generation
 *
 *
 * @internal revisions
 * @since 1.9.8
 * 20130528 - franciscom - -1 => WITHOUT KEYWORDS
 * 
 */
function getTCasesFilteredByKeywords($testproject_id, $keyword_id=0, $keyword_filter_type='Or')
{
  $keySet = (array)$keyword_id;
  $sql = null;

  $tcaseSet = array();
  if(in_array(-1,$keySet) || $keyword_filter_type == 'NotLinked')
  {  
    $this->get_all_testcases_id($testproject_id,$tcaseSet);
  }
  $hasTCases = count($tcaseSet) > 0;

  if(in_array(-1,$keySet) && $hasTCases)
  {  
    $sql = " /* WITHOUT KEYWORDS */ " . 
           " SELECT NHTC.id AS testcase_id FROM ".$this->db->get_table('nodes_hierarchy')." NHTC " .  
           " WHERE NHTC.id IN (" . implode(',',$tcaseSet) . ") AND NOT EXISTS " .
           " (SELECT 1 FROM ".$this->db->get_table('testcase_keywords')." TCK WHERE TCK.testcase_id = NHTC.id) ";
  }
  else
  {  
    $keyword_filter = " keyword_id IN (" . implode(',',$keySet) . ")";            

    switch($keyword_filter_type)
    {

      case 'NotLinked':
        if($hasTCases)
        {
          $sql = " /* WITHOUT SPECIFIC KEYWORDS */ " . 
                 " SELECT NHTC.id AS testcase_id FROM ".$this->db->get_table('nodes_hierarchy')." NHTC " .  
                 " WHERE NHTC.id IN (" . implode(',',$tcaseSet) . ") " .
                 " AND NOT EXISTS " .
                 " (SELECT 1 FROM ".$this->db->get_table('testcase_keywords')." TCK " . 
                 "  WHERE TCK.testcase_id = NHTC.id AND {$keyword_filter} )";
        } 
      break;


      case 'And':
        $sql = " /* Filter Type = AND */ " .
               " SELECT FOXDOG.testcase_id FROM " .
               " ( SELECT COUNT(testcase_id) AS HITS,testcase_id " .
               "   FROM ".$this->db->get_table('testcase_keywords')." " .
               "   WHERE {$keyword_filter} " .
               "   GROUP BY testcase_id ) AS FOXDOG " . 
               " WHERE FOXDOG.HITS = " . count($keyword_id );
      break;


      case 'Or':
      default:
        $sql = " /* Filter Type = OR */ " .
               " SELECT testcase_id " .
               " FROM ".$this->db->get_table('testcase_keywords')." " .
               " WHERE {$keyword_filter} ";
      break;
    }
  }

  $hits = !is_null($sql) ? $this->db->fetchRowsIntoMap($sql,'testcase_id') : null;
  return($hits);
}


/**
 *
 *
 * @internal revisions
 * @since 1.9.4
 *
 */
function isIssueTrackerEnabled($id)
{
  $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
  $sql = "/* $debugMsg */ " .
         "SELECT issue_tracker_enabled FROM " . $this->db->get_table($this->object_table) .
         " WHERE id =" . intval($id);   
       
  $ret = $this->db->get_recordset($sql);
  return $ret[0]['issue_tracker_enabled'];
}



/**
 *
 *
 * @internal revisions
 * @since 1.9.4
 *
 */
function enableIssueTracker($id)
{
  $this->setIssueTrackerEnabled($id,1);
}

/**
 *
 *
 * @internal revisions
 * @since 1.9.4
 *
 */
function disableIssueTracker($id)
{
  $this->setIssueTrackerEnabled($id,0);
}


/**
 *
 *
 * @internal revisions
 * @since 1.9.4
 *
 */
function setIssueTrackerEnabled($id,$value)
{

  $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
  $sql = "/* $debugMsg */ " .
       " UPDATE " . $this->db->get_table($this->object_table) .
       " SET issue_tracker_enabled = " . (intval($value) > 0 ? 1 : 0) .
       " WHERE id =" . intval($id);   
  $ret = $this->db->exec_query($sql);
}


function getItemCount()
{
  $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
  $sql = "/* $debugMsg */ " .
         " SELECT COUNT(0) AS qty FROM " . $this->db->get_table($this->object_table);
  $ret = $this->db->get_recordset($sql);
  return $ret[0]['qty'];
}

function getPublicAttr($id)
{
  $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
  $sql = "/* $debugMsg */ " .
         " SELECT is_public FROM " . $this->db->get_table($this->object_table) .
         " WHERE id =" . intval($id);   
  $ret = $this->db->get_recordset($sql);
  return $ret[0]['is_public'];
}




  /**
   * Gets test cases created per user. 
   * The test cases are restricted to a test project. 
   * 
   * Optional values may be passed in the options array.
   * 
   * @param integer $user_id User ID
   * @param integer $tproject_id Test Project ID
   * @param mixed $options Optional array of options
   * @return mixed Array of test cases created per user
   */
  function getTestCasesCreatedByUser($id,$user_id,$options=null)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
      
    $opt = array('startTime' => null, 'endTime' => null);
    $opt = array_merge($opt,(array)$options);
    $safe = array('user_id' => intval($user_id), 'tproject_id' => intval($id));
    
    $cfg = config_get('testcase_cfg');
    $eid = $this->db->db->concat('TPROJ.prefix',"'{$cfg->glue_character}'",'TCV.tc_external_id');
    
    // 
    $target = array();
    $this->get_all_testcases_id($id,$target);
    $itemQty = count($target);
   
    $rs = null;
    if($itemQty > 0)
    {
      $sql = " /* $debugMsg */ SELECT TPROJ.id AS tproject_id, TCV.id AS tcversion_id," .
             " TCV.version, {$eid} AS external_id, NHTC.id  AS tcase_id, NHTC.name AS tcase_name, ". 
             " TCV.creation_ts, TCV.modification_ts, " . 
             " U.first  AS first_name, U.last AS last_name, U.login, ".
             " TCV.importance " .
             " FROM ".$this->db->get_table('testprojects')." TPROJ,".$this->db->get_table('nodes_hierarchy')." NHTC " .
             " JOIN ".$this->db->get_table('nodes_hierarchy')." NHTCV ON NHTCV.parent_id = NHTC.id " .
             " JOIN ".$this->db->get_table('tcversions')." TCV ON TCV.id = NHTCV.id " . 
             " JOIN ".$this->db->get_table('users')." U ON U.id = TCV.author_id " .
             " WHERE TPROJ.id = {$safe['tproject_id']} " .
             " AND NHTC.id IN (" . implode(',', $target) . ")";
      
      if($user_id !== 0) 
      {               
        $sql .= " AND U.id = {$safe['user_id']}";
      }                                        
      if( !is_null($opt['startTime']) ) 
      {
        $sql .= " AND TCV.creation_ts >= '{$opt['startTime']}'";
      }
      if( !is_null($opt['endTime']) ) 
      {
        $sql .= " AND TCV.creation_ts <= '{$opt['endTime']}'";
      }
      $rs = $this->db->fetchRowsIntoMap($sql,'tcase_id',database::CUMULATIVE);
      if( !is_null($rs) )
      {
        $k2g = array_keys($rs);
        $path_info = $this->tree_manager->get_full_path_verbose($k2g,array('output_format' => 'path_as_string'));
        foreach($k2g as $tgx)
        {
          $rx = array_keys($rs[$tgx]);
          foreach($rx as $ex)
          {
            $rs[$tgx][$ex]['path'] = $path_info[$tgx];
          }  
        }
      }
    }
    return $rs;
  }  

  /**
   *Get count test cases version or step per user
   * 
   * author:jinjiacun date:2017.12.18
   * @param  integer $userid  User ID
   * @param  mixed   $options Optional array of options 
   * @return mixed Array of count of test cases version create or modify
   */
  function getCountCreateOrModify($id, $user_id, $options = null)
  {
    $opt = array('startTime' => null, 'endTime' => null);
    $opt = array_merge($opt,(array)$options);
    $safe = array('user_id' => intval($user_id), 'tproject_id' => intval($id));

    $eid = $this->db->concat('TPROJ.prefix',"'{$cfg->glue_character}'",'TCV.tc_external_id'); 

    $target = array();
    $this->get_all_testcases_id($id,$target);
    $itemQty   = count($target);   
    $rs_create = null;
    $rs_modify = null;
    $rs        = null;


    if($itemQty > 0)
    {
      $sql = " /* $debugMsg */ SELECT count(distinct(TCV.id)) as create_total,
              U.login,U.first,U.last ".             
             " FROM ".$this->db->get_table('testprojects')." TPROJ,".$this->db->get_table('nodes_hierarchy')." NHTC " .
             " JOIN ".$this->db->get_table('nodes_hierarchy')." NHTCV ON NHTCV.parent_id = NHTC.id " .
             " JOIN ".$this->db->get_table('tcversions')." TCV ON TCV.id = NHTCV.id " . 
             " JOIN ".$this->db->get_table('users')." U ON U.id = TCV.author_id " .
             " WHERE TPROJ.id = {$safe['tproject_id']} " .
             " AND NHTC.id IN (" . implode(',', $target) . ")";             
      /*if($user_id !== 0) 
      {               
        $sql .= " AND U.id = {$safe['user_id']}";
      } */                                       
      if( !is_null($opt['startTime']) ) 
      {
        $sql .= " AND TCV.creation_ts >= '{$opt['startTime']}'";
      }
      if( !is_null($opt['endTime']) ) 
      {
        $sql .= " AND TCV.creation_ts <= '{$opt['endTime']}'";
      }
      $sql .= "  group by U.login";    
      //var_dump($sql);die;
      $rs_create  = $this->db->fetchRowsIntoMap($sql,'login',database::CUMULATIVE);

      //$sql_modify = " /* $debugMsg */ SELECT count(TCV.id) as modify_total,U.login ".             
      //       " FROM ".$this->db->get_table('testprojects')." TPROJ,".$this->db->get_table('nodes_hierarchy')." NHTC " .
      //       " JOIN ".$this->db->get_table('nodes_hierarchy')." NHTCV ON NHTCV.parent_id = NHTC.id " .
      //       " JOIN ".$this->db->get_table('tcversions')." TCV ON TCV.id = NHTCV.id " . 
      //       " JOIN ".$this->db->get_table('users')." U ON U.id = TCV.author_id " .
      //       " WHERE TPROJ.id = {$safe['tproject_id']} " .
      //       " AND NHTC.id IN (" . implode(',', $target) . ")";             
      $sql_modify = "select user_id, count(distinct(case_id)) as modify_total "
                    ." from(select user_id, case_id,count(1) as tt from "
                    .$this->db->get_table("tccase_chg_log")." as TCCL where 1 = 1 ";
     /* if($user_id !== 0) 
      {               
        $sql_modify .= " AND U.id = {$safe['user_id']}";
      }   */                                           
      if( !is_null($opt['startTime']) ) 
      {
        $sql_modify .= " AND TCCL.update_ts >= '{$opt['startTime']}'";
      }
      if( !is_null($opt['endTime']) ) 
      {
        $sql_modify .= " AND TCCL.update_ts <= '{$opt['endTime']}'";
      }
      $sql_modify .= " group by user_id,case_id,date_format(update_ts,\"%Y-%m-%d\")) as t group by user_id";
      //$sql_modify .= " group by U.login";
      //filter some person create case and alter case step
      //but not into log
      //
      //author:jinjiacun
      //time:2017-12-28
      //
      $rs_modify_tmp   = $this->db->fetchRowsIntoMap($sql_modify,'user_id',database::CUMULATIVE);
      $sql_create_tmp  = "select tccl.user_id,count(distinct(tc.id)) as modify_total " 
                         ." from ".$this->db->get_table('tccase_chg_log')." as tccl inner "
                         ." join ".$this->db->get_table('tcversions')." as tc "
                         ." on tccl.case_id = tc.id "
                         ." where tccl.update_ts = date_format(tc.creation_ts,'%Y-%m-%d') "
                         ." and tccl.user_id = tc.author_id ";
      if( !is_null($opt['startTime']) ) 
      {
        $sql_create_tmp .= " AND tc.creation_ts >= '{$opt['startTime']}'";
      }
      if( !is_null($opt['endTime']) ) 
      {
        $sql_create_tmp .= " AND tc.creation_ts <= '{$opt['endTime']}'";
      }                 
      $sql_create_tmp .= " group by user_id";
      $rs_create_tmp = $this->db->fetchRowsIntoMap($sql_create_tmp,'user_id',database::CUMULATIVE);
      unset($sql_create_tmp);
      if(count($rs_create_tmp) > 0){
        if(count($rs_modify_tmp) > 0){
          foreach($rs_modify_tmp as $k => $v){
            if(isset($rs_create_tmp[$k])){
              $rs_modify_tmp[$k][0]['modify_total'] -= $rs_create_tmp[$k][0]['modify_total'];
            }
          }
          unset($k, $v);
        }
      }
      //end:jinjiacun/2017-12-28
      //
     // unset($rs_create_tmp);
      //var_dump($rs_modify_tmp);
      //die;
      if(count($rs_modify_tmp) > 0){
        $user_id = array();
        $user_keys = array_keys($rs_modify_tmp);
        $tmp_sql = "select id, login from ".$this->db->get_table("users")
                  ." where id in(".implode(",",$user_keys).")";
        $rs_tmp = $this->db->fetchRowsIntoMap($tmp_sql,'id',database::CUMULATIVE);
        //print_r($rs_tmp);die;
        if($rs_tmp){
          foreach($rs_tmp as $k=>$v){
            $rs_modify[$v[0]['login']] = $rs_modify_tmp[$k];
          }
          unset($k, $v);
        }
      }

      //print_r($rs_create);
      //echo "<br/><br/>";
      //print_r($rs_modify);
      //die;

      foreach($rs_create as $k => $v){
        $rs[$k] = array(
          'login' => $k, 
          'create_total' => $v[0]['create_total'],
          'modify_total' => isset($rs_modify[$k])?$rs_modify[$k][0]['modify_total']:0,
          'user_name'    => $v[0]['first'].$v[0]['last']
        );
        if(isset($rs_modify[$k]))unset($rs_modify[$k]);
      }
      unset($rs_create, $k, $v);
      $user_list = array();
      if($rs_modify){
        foreach($rs_modify as $k => $v){
          $rs[$k] = array(
            'login' => $k,
            'create_total' => 0,
            'modify_total' => $v[0]['modify_total']
          );
          $user_list[] = "'".$k."'";          
        }
        unset($k, $v);

        $tmp_sql = "select login,first,last "
                   ." from ".$this->db->get_table('users')
                   ." where login in (".implode(",", $user_list).")";
        $tmp_user_map = $this->db->fetchRowsIntoMap($tmp_sql, "login", database::CUMULATIVE);
        
        foreach($rs_modify as $k => $v){
          $rs[$k]['user_name'] = $tmp_user_map[$k][0]['first'].$tmp_user_map[$k][0]['last'];
        }
        unset($rs_modify, $k, $v);
      }

     /* if(count($rs) > 0){
        $user_list = array_keys($rs);
        if(count($user_list) > 0){
          $tmp_sql = "select login,first,last from ".$this->db->get_table('users')
                     ." where login in (".implode(",", $user_list).")";
          var_dump($tmp_sql);
          $user_map = $this->db->fetchRowsIntoMap($tmp_sql, 'login',database::CUMULATIVE);
          var_dump($user_map);
          foreach($rs as $k => $v){
            $rs[$k]['user_name'] = $user_map[$k][0]['first'].$user_map[$k][0]['last'];
          }
        }  
      }*/
      
      /*
      foreach($keys as $key){
        $rs[] = array(
              'login'         => $key,
              'create_total'  => isset($rs_create[$key])?$rs_create[$key][0]['create_total']:0,
              'modify_total'  => isset($rs_modify[$key])?$rs_modify[$key][0]['modify_total']:0
          );
      }
      */
    }

    return $rs;
  }

  /**
   *Get count test cases version by exec per user
   * 
   * author:jinjiacun date:2017.12.20
   * @param  integer $userid  User ID
   * @param  mixed   $options Optional array of options 
   * @return mixed Array of count of test cases version create or modify
   */
  function getCountExectionByUser($id, $tplan_id, $user_id, $options = null)
  {
    $opt = array('startTime' => null, 'endTime' => null);
    $opt = array_merge($opt,(array)$options);
    $safe = array('user_id' => intval($user_id), 
                  'tproject_id' => intval($id),
                  'tplan_id' => intval($tplan_id)
                  );
    $eid = $this->db->db->concat('TPROJ.prefix',"'{$cfg->glue_character}'",'TCV.tc_external_id'); 

    $target = array();
    $this->get_all_testcases_id($id,$target);
    $itemQty   = count($target);    
    $rs        = null;

    if($itemQty > 0)
    {
      $sql = " /* $debugMsg */ SELECT count(1) as tcv_total,U.login,U.first,U.last ".
             " ,NHTC.name AS tcase_name, ". 
             " NHTC.id  AS tcase_id".            
             " FROM ".$this->db->get_table('testprojects')." TPROJ,".$this->db->get_table('nodes_hierarchy')." NHTC " .
             " JOIN ".$this->db->get_table('nodes_hierarchy')." NHTCV ON NHTCV.parent_id = NHTC.id " .
             " JOIN ".$this->db->get_table('tcversions')." TCV ON TCV.id = NHTCV.id " . 
             " JOIN ".$this->db->get_table('executions')." Ex ON Ex.tcversion_id = TCV.id".
             " JOIN ".$this->db->get_table('users')." U ON U.id = Ex.tester_id " .
             " WHERE TPROJ.id = {$safe['tproject_id']} " .             
             " AND NHTC.id IN (" . implode(',', $target) . ")";             
      if($tplan_id <> 0){
          $sql .= " AND Ex.testplan_id = {$safe['tplan_id']} ";
      }
      if($user_id !== 0) 
      {               
        $sql .= " AND U.id = {$safe['user_id']}";
      }
      if( !is_null($opt['startTime']) ) 
      {
        $sql .= " AND Ex.execution_ts >= str_to_date('{$opt['startTime']}','%Y-%m-%d %H:%i:%s')";
      }
      if( !is_null($opt['endTime']) ) 
      {
        $sql .= " AND Ex.execution_ts <= str_to_date('{$opt['endTime']}', '%Y-%m-%d %H:%i:%s')";
      }
      $sql .= " group by U.login";
        //var_dump($sql);
      $rs  = $this->db->fetchRowsIntoMap($sql,'login');
    }

    return $rs;
  }

  /**
   * author:jinjiacun
   * time:2018-1-15
   * [getCaseAndBug description]
   * @param  [type] $id       [description]
   * @param  [type] $tplan_id [description]
   * @param  [type] $build_id [description]
   * @param  [type] $options  [description]
   * @return [type]           [description]
   */
  function getCaseAndBug($id, $tplan_id, $build_id, $case_no, $bug_no, $find_version_no, $options = null){
    $opt = array('startTime' => null, 'endTime' => null);
    $opt = array_merge($opt,(array)$options);
    $safe = array('user_id'         => intval($user_id), 
                  'tproject_id'     => intval($id),
                  'tplan_id'        => intval($tplan_id),
                  'build_id'        => intval($build_id),
                  'case_no'         => intval($case_no),
                  'bug_no'          => htmlspecialchars($bug_no),
                  'find_version_no' => htmlspecialchars($find_version_no),
                  );
    $eid = $this->db->db->concat('TPROJ.prefix',"'{$cfg->glue_character}'",'TCV.tc_external_id'); 

    $target = array();
    $this->get_all_testcases_id($id,$target);  
    //print_r($target);
    $end_time = microtime(true);
    //echo 'diff_time:'.($end_time - $begin_time)."<br/>";  
    $itemQty   = count($target);    
    $rs        = null;

    if($itemQty > 0)
    {
      $sql = " /* $debugMsg */ SELECT TCV.id as tcversion_id,TCV.tc_external_id as tcase_no, ".
             " Ex.testplan_id,Ex.id as exec_id,Ex.bug_no,Ex.find_version_no, ".
             " Ex.build_id, U.login,U.first,U.last,Ex.execution_ts,Ex.status, ".
             " NHTC.name AS tcase_name, ". 
             " NHTC.id  AS tcase_id".            
             " FROM ".$this->db->get_table('testprojects')." TPROJ,".$this->db->get_table('nodes_hierarchy')." NHTC " .
             " JOIN ".$this->db->get_table('nodes_hierarchy')." NHTCV ON NHTCV.parent_id = NHTC.id " .
             " JOIN ".$this->db->get_table('tcversions')." TCV ON TCV.id = NHTCV.id " . 
             " JOIN ".$this->db->get_table('executions')." Ex ON Ex.tcversion_id = TCV.id".
             " JOIN ".$this->db->get_table('users')." U ON U.id = Ex.tester_id " .
             " WHERE TPROJ.id = {$safe['tproject_id']} " .             
             " AND NHTC.id IN (" . implode(',', $target) . ")";             
      if($tplan_id <> 0){
          $sql .= " AND Ex.testplan_id = {$safe['tplan_id']} ";
      }
      if($build_id <> 0){
          $sql .= " AND Ex.build_id = {$safe['build_id']} ";
      }
      if($case_no <> 0){
          $sql .= " AND TCV.tc_external_id = {$safe['case_no']}";
      }
      if(trim($bug_no) <> ""){
          $sql .= " AND Ex.bug_no = '{$safe["bug_no"]}' ";
      }
      if(trim($find_version_no) <> ""){
          $sql .= " AND Ex.find_version_no like '%{$safe["find_version_no"]}%' ";
      }
      if(trim($bug_no) == "" || trim($find_version_no) == ""){
        $sql .= " AND (Ex.bug_no <> '' or Ex.find_version_no <> '') ";
      }
      $rs  = $this->db->fetchRowsIntoMap($sql,'exec_id');
    }
    return $rs;   
  }

  /**
   *Get count test cases per user by plan 
   * 
   * author:jinjiacun date:2017.12.20
   * @param  integer $userid  User ID
   * @param  mixed   $options Optional array of options 
   * @return mixed Array of count of test cases version create or modify
   */
  function getCountPerUserExectionByPlanBuild($id, $user_id, $options = null)
  {
    $opt = array('startTime' => null, 'endTime' => null);
    $opt = array_merge($opt,(array)$options);
    $safe = array('user_id' => intval($user_id), 'tproject_id' => intval($id));

    $eid = $this->db->db->concat('TPROJ.prefix',"'{$cfg->glue_character}'",'TCV.tc_external_id'); 

    $target = array();
    $this->get_all_testcases_id($id,$target);
    $itemQty   = count($target);    
    $rs        = null;

    if($itemQty > 0)
    {
      $sql = " /* $debugMsg */ SELECT count(1) as tcv_total,U.login".            
             " FROM ".$this->db->get_table('testprojects')." TPROJ,".$this->db->get_table('nodes_hierarchy')." NHTC " .
             " JOIN ".$this->db->get_table('nodes_hierarchy')." NHTCV ON NHTCV.parent_id = NHTC.id " .
             " JOIN ".$this->db->get_table('tcversions')." TCV ON TCV.id = NHTCV.id " . 
             " JOIN ".$this->db->get_table('executions')." Ex ON Ex.tcversion_id = TCV.id".
             " JOIN ".$this->db->get_table('users')." U ON U.id = Ex.tester_id " .
             " WHERE TPROJ.id = {$safe['tproject_id']} " .
             " AND NHTC.id IN (" . implode(',', $target) . ")";             
      /*if($user_id !== 0) 
      {               
        $sql .= " AND U.id = {$safe['user_id']}";
      }*/
      if( !is_null($opt['startTime']) ) 
      {
        $sql .= " AND date_format(Ex.execution_ts,'%Y-%m-%d') = '{$opt['startTime']}'";
      }
      if( !is_null($opt['setting_build'])){
        $sql .= " AND Ex.build_id = '{$opt['setting_build']}'";
      } 
      if( !is_null($opt['setting_testplan'])){
        $sql .= " AND Ex.testplan_id = '{$opt['setting_testplan']}'";
      }
      $sql .= " group by U.login";
      $rs  = $this->db->fetchRowsIntoMap($sql,'login',database::CUMULATIVE);
    }

    return $rs;
  }


  /**
   *
   * @since 1.9.6
   *
   * @internal revisions
   *
   */
  function isReqMgrIntegrationEnabled($id)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $targetField = 'reqmgr_integration_enabled';
    $sql = "/* $debugMsg */ " .
           "SELECT {$targetField} FROM " . $this->db->get_table($this->object_table) .
           "WHERE id =" . intval($id);   
         
    $ret = $this->db->get_recordset($sql);
    return $ret[0][$targetField];
  }

  /**
   *
   * @since 1.9.6
   *
   * @internal revisions
   *
   */
  function enableReqMgrIntegration($id)
  {
    $this->setOneZeroField($id,'reqmgr_integration_enabled',1);
  }

  /**
   *
   * @since 1.9.6
   *
   * @internal revisions
   *
   */
  function disableReqMgrIntegration($id)
  {
    $this->setOneZeroField($id,'reqmgr_integration_enabled',0);
  }

  function setReqMgrIntegrationEnabled($id,$value)
  {
    $this->setOneZeroField($id,'reqmgr_integration_enabled',$value);
  }

  /**
   *
   *
   * @internal revisions
   * @since 1.9.4
   *
   */
  function setOneZeroField($id,$field,$value)
  {
  
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $sql = "/* $debugMsg */ " .
         " UPDATE " . $this->db->get_table($this->object_table) .
         " SET {$field} = " . (intval($value) > 0 ? 1 : 0) .
         " WHERE id =" . intval($id);   
    $ret = $this->db->exec_query($sql);
  }


  /**
   *
   *
   * @internal revisions
   * @since 1.9.4
   *
   */
  function getByChildID($child)
  {
    $path = $this->tree_manager->get_path($child);
    return $this->get_by_id(intval($path[0]['parent_id']));
  }

  /**
   * @internal revisions
   * @since 1.9.8
   */
  function setActive($id)
  {
    $this->setOneZeroField($id,'active',1);
  }

  /**
   * @internal revisions
   * @since 1.9.8
   */
  function setInactive($id)
  {
    $this->setOneZeroField($id,'active',0);
  }

  /**
   *
   */
  function simplexml_load_file_helper($filename)
  {
    // http://websec.io/2012/08/27/Preventing-XXE-in-PHP.html
    libxml_disable_entity_loader(true);  
    $zebra = file_get_contents($filename);
    $xml = @simplexml_load_string($zebra);
    return $xml;
  }


  /**
   *
   * @used-by containerEdit.php
   */
  function getFileUploadRelativeURL($id)
  {
    // I've to use testsuiteID because this is how is name on containerEdit.php
    $url = "lib/testcases/containerEdit.php?containerType=testproject&doAction=fileUpload&tprojectID=" . intval($id);
    return $url;
  }

  /**
   * @used-by containerEdit.php
   */
  function getDeleteAttachmentRelativeURL($id)
  {
    // I've to use testsuiteID because this is how is name on containerEdit.php
    $url = "lib/testcases/containerEdit.php?containerType=testproject&doAction=deleteFile&tprojectID=" . intval($id) .
           "&file_id=" ; 
    return $url;
  }



  /**
   * @used-by projectEdit.php
   */
  function enableRequirements($id)
  {
    $debugMsg = $this->debugMsg . __FUNCTION__;
    $opt = $this->getOptions($safeID = intval($id));
    $opt->requirementsEnabled = 1;
    $this->setOptions($safeID,$opt);
  }  

  /**
   * @used-by projectEdit.php
   */
  function disableRequirements($id)
  {
    $debugMsg = $this->debugMsg . __FUNCTION__;
    $opt = $this->getOptions($safeID = intval($id));
    $opt->requirementsEnabled = 0;
    $this->setOptions($safeID,$opt);
  }  


  /**
   * @used-by 
   */
  function getOptions($id)
  {
    $debugMsg = $this->debugMsg . __FUNCTION__;
    $sql = "/* $debugMsg */ SELECT testprojects.options ".
           " FROM " . $this->db->get_table($this->object_table)." testprojects " .
           " WHERE testprojects.id = " . intval($id);
    $rs = $this->db->get_recordset($sql);  
    return unserialize($rs[0]['options']);       
  }  

  /**
   * @used-by 
   */
  function setOptions($id,$optObj)
  {
    $debugMsg = $this->debugMsg . __FUNCTION__;

    $nike = false;
    $itemOpt = $this->getOptions( ($safeID = intval($id)) );
    foreach($itemOpt as $prop => $value)
    {
      if( property_exists($optObj, $prop) )
      {
        $itemOpt->$prop = $optObj->$prop;
        $nike = true;
      }  
    }

    if($nike)
    {
      $sql = "/* $debugMsg */ UPDATE " . $this->db->get_table($this->object_table) . 
             " SET options = '" . $this->db->prepare_string(serialize($itemOpt)) . "'" .
             " WHERE testprojects.id = " . $safeID;

      $this->db->exec_query($sql);  
    }  
  }  


/**
 *
 */
function getActiveTestPlansCount($id)
{
  $debugMsg = $this->debugMsg . __FUNCTION__;
  $sql = "/* $debugMsg */ SELECT COUNT(0) AS qty".
         " FROM ".$this->db->get_table('nodes_hierarchy')." NH_TPLAN " .
         " JOIN ".$this->db->get_table('testplans')." TPLAN ON NH_TPLAN.id = TPLAN.id " .
         " WHERE NH_TPLAN.parent_id = " . $this->db->prepare_int($id) .
         " AND TPLAN.active = 1";

  $rs = $this->db->get_recordset($sql);
  return $rs[0]['qty'];       
}


/**
 *select test case execution list
 * 
 */
  function getTestCaseExection($id,$user_id=0,$options=null){
     $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
      
    $opt = array('startTime' => null, 'endTime' => null);
    $opt = array_merge($opt,(array)$options);
    $safe = array('user_id' => intval($user_id), 'tproject_id' => intval($id));
    
    $cfg = config_get('testcase_cfg');
    $eid = $this->db->db->concat('TPROJ.prefix',"'{$cfg->glue_character}'",'TCV.tc_external_id');
    
    // 
    $target = array();
    $this->get_all_testcases_id($id,$target);
    $itemQty = count($target);
   
    $rs = null;
    if($itemQty > 0)
    {
      $sql = " /* $debugMsg */ SELECT TPROJ.id AS tproject_id, TCV.id AS tcversion_id," .
             " TCV.version, {$eid} AS external_id, NHTC.id  AS tcase_id, NHTC.name AS tcase_name, ". 
             " Ex.execution_ts AS execution_time, Ex.id as execution_id," . 
             " U.first  AS first_name, U.last AS last_name, U.login, Ex.build_id as build_id" .
             " FROM ".$this->db->get_table('testprojects')." TPROJ,".$this->db->get_table('nodes_hierarchy')." NHTC " .
             " JOIN ".$this->db->get_table('nodes_hierarchy')." NHTCV ON NHTCV.parent_id = NHTC.id " .
             " JOIN ".$this->db->get_table('tcversions')." TCV ON TCV.id = NHTCV.id " . 
             " JOIN ".$this->db->get_table('executions')." Ex ON TCV.id  = Ex.tcversion_id ".
             " JOIN ".$this->db->get_table('users')." U ON U.id = TCV.author_id " .
             " WHERE TPROJ.id = {$safe['tproject_id']} " .
             " AND NHTC.id IN (" . implode(',', $target) . ") limit 20";
      
      $rs = $this->db->fetchRowsIntoMap($sql,'tcase_id',database::CUMULATIVE);
    }
    return $rs;
  }

  /**
   * update checker_id in execution
   */
  function updateCheckerIdOnExecution($execution_ids){
      $re_status  = true;
      //print_r($_POST['execution_ids']);
      //print_r($_SESSION['currentUser']->dbID);
      $msg = "成功复核";
      foreach($execution_ids as $execution_id){
        $sql = "update ".$this->db->get_table('executions').
              " set checker_id=".$_SESSION['currentUser']->dbID.
              " where id = ".$execution_id;
          //    var_dump($sql);die;
        if($this->db->exec_query($sql)){;}else{
          $msg = "复核提交失败";
          //die($msg);
          $re_status = false;
        }  
      }
      //print_r($_SESSION);      
      //die($msg);
      
      return $re_status;
  }


  function getTcversionIdByCaseId($case_id_list){

	  if(!is_array($case_id_list)){
		  return null;
	  }

	  $tcversion_id_list = array();
	  $sql_template = "select id "
	    		." from ".$this->db->get_table('nodes_hierarchy')
			." where parent_id in (%s) and node_type_id = 4";	
	  $sql = sprintf($sql_template, implode(",", $case_id_list));
	  $tcversion_id_list = $this->db->fetchColumnsIntoArray($sql,'id');
	  
	  return $tcversion_id_list;
  }















} // end class
