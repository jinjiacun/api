<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @package     TestLink
 * @author      Francisco Mancardi (francisco.mancardi@gmail.com)
 * @copyright   2005-2015, TestLink community 
 * @filesource  tc_exec_assignment.php
 * @link        http://www.testlink.org
 *
 * @internal revisions
 * @since 1.9.14
 */
         
require_once(dirname(__FILE__)."/../../config.inc.php");
require_once("common.php");
require_once("treeMenu.inc.php");
require_once('email_api.php');
require_once("specview.php");

// Time tracking - $chronos[] = microtime(true);$tnow = end($chronos);
testlinkInitPage($db,false,false,"checkRights");

$tree_mgr = new tree($db); 
$tplan_mgr = new testplan($db); 
$tcase_mgr = new testcase($db); 
$assignment_mgr = new assignment_mgr($db); 

$templateCfg = templateConfiguration();

$args = init_args();
$gui = initializeGui($db,$args,$tplan_mgr,$tcase_mgr);
$keywordsFilter = new stdClass();
$keywordsFilter->items = null;
$keywordsFilter->type = null;
if(is_array($args->keyword_id))
{
  $keywordsFilter->items = $args->keyword_id;
  $keywordsFilter->type = $gui->keywordsFilterType;
}
$arrData = array();

$status_map = $assignment_mgr->get_available_status();
$types_map = $assignment_mgr->get_available_types();
$task_test_execution = $types_map['testcase_execution']['id'];

//var_dump($args->doAction);
//die;
switch($args->doAction)
{
  case 'std':
    if(!is_null($args->achecked_tc))
    {
      $open = $status_map['open']['id'];
      $db_now = $db->db_now();
      $features2 = array( 'upd' => array(), 
                          'ins' => array(), 
                          'del' => array());
      $method2call = array( 'upd' => 'update', 
                            'ins' => 'assign', 
                            'del' => 'delete_by_feature_id_and_build_id');
      $called = array( 'upd' => false, 
                       'ins' => false, 
                       'del' => false);

      foreach($args->achecked_tc as $key_tc => $platform_tcversion)
      {
        foreach($platform_tcversion as $platform_id => $tcversion_id)
        {
          $feature_id = $args->feature_id[$key_tc][$platform_id];

          $op='ins';
          $features2[$op][$feature_id]['user_id'] = $args->tester_for_tcid[$key_tc][$platform_id];
          $features2[$op][$feature_id]['type'] = $task_test_execution;
          $features2[$op][$feature_id]['status'] = $open;
          $features2[$op][$feature_id]['creation_ts'] = $db_now;
          $features2[$op][$feature_id]['assigner_id'] = $args->user_id;
          $features2[$op][$feature_id]['tcase_id'] = $key_tc;
          $features2[$op][$feature_id]['tcversion_id'] = $tcversion_id;
          $features2[$op][$feature_id]['build_id'] = $args->build_id; 
        }

      }

      foreach($features2 as $key => $values)
      {
        if( count($features2[$key]) > 0 )
        {
          $assignment_mgr->assign($values);
          $called[$key]=true;
        }  
      }
          
      if($args->send_mail)
      {
        foreach($called as $ope => $ope_status)
        {
          if($ope_status)
          {
            send_mail_to_testers($db,$tcase_mgr,$gui,$args,$features2[$ope],$ope);     
          }
        }
      } // if($args->send_mail)   
    }  
  break;

  /**
   * batch check case exec
   *
   * author:jinjiacun
   *   time:2017-12-26 14:01
   */
  case 'doCheck':
    $tmp_list = $args->achecked_tc;
    $check_version_list = array();
    if(count($tmp_list) > 0){
      foreach($tmp_list as $k=>$v){
        $check_version_list[] = $v[0];
      }
      unset($k, $v);
    }
    unset($tmp_list);
    $template_sql = "update ".$db->get_table('tcversions')
                    ." set checker_id=%d where id in (%s)";
    if(count($check_version_list) > 0){
      $sql = sprintf($template_sql, 
                   $_SESSION['currentUser']->dbID, 
                   implode(",", $check_version_list));      
      $rs = $db->exec_query($sql);
      unset($sql);
      if($rs){
        echo "<script>alert('成功复核');</script>";
      }  
      unset($rs);
    }
    break;

  case 'doBulkRemove':
    if(!is_null($args->achecked_tc))
    {
      $op='del';
      $features2[$op] = array();
      foreach($args->achecked_tc as $key_tc => $platform_tcversion)
      {
        foreach($platform_tcversion as $platform_id => $tcversion_id)
        {
          $feature_id = $args->feature_id[$key_tc][$platform_id];

          $features2[$op][$feature_id]['type'] = $task_test_execution;
          $features2[$op][$feature_id]['build_id'] = $args->build_id; 
        }
      }
      
      foreach($features2 as $key => $values)
      {
        if( count($features2[$key]) > 0 )
        {
          $assignment_mgr->delete_by_feature_id_and_build_id($values);
          $called[$key]=true;
        }  
      }
         
    }  
  break; 

  case 'doRemove':
    $signature[] = array('type'       => $task_test_execution, 
                         'user_id'    => $args->targetUser, 
                         'feature_id' => $args->targetFeature, 
                         'build_id'   => $args->build_id);
    $assignment_mgr->deleteBySignature($signature);
  break; 
}

switch($args->level)
{
  case 'testcase':
    // build the data need to call gen_spec_view
    $xx=$tcase_mgr->getPathLayered(array($args->id));
    $yy = array_keys($xx);  // done to silence warning on end()
    $tsuite_data['id'] = end($yy);
    $tsuite_data['name'] = $xx[$tsuite_data['id']]['value']; 
        
    $xx = $tplan_mgr->getLinkInfo($args->tplan_id,
                                  $args->id,
                                  $args->control_panel['setting_platform'],
                                  array('output'           => 'assignment_info',
                                        'build4assignment' => $args->build_id));
    
    $linked_items[$args->id] = $xx;
    $opt = array('write_button_only_if_linked' => 1, 
                 'user_assignments_per_build'  => $args->build_id,
                 'useOptionalArrayFields'       => true);

    $filters = array('keywords'  => $keywordsFilter->items, 
                     'testcases' => $args->id);
    
    $my_out = gen_spec_view($db,
                            'testplan',
                            $args->tplan_id,
                            $tsuite_data['id'],
                            $tsuite_data['name'],
                            $linked_items,
                            null,
                            $filters,
                            $opt);

    // index 0 contains data for the parent test suite of this test case, 
    // other elements are not needed.
    $out = array();
    $out['spec_view'][0] = $my_out['spec_view'][0];
    $out['num_tc'] = 1;
  break;
    
  case 'testsuite':
    $filters = array();
    $filters['keywordsFilter'] = $keywordsFilter;
    $filters['testcaseFilter'] = (isset($args->testcases_to_show)) ? $args->testcases_to_show : null;
    $filters['assignedToFilter'] = property_exists($args,'filter_assigned_to') ? $args->filter_assigned_to : null;
    $filters['executionTypeFilter'] = $args->control_panel['filter_execution_type'];
    $filters['cfieldsFilter'] = $args->control_panel['filter_custom_fields'];

    // ORDER IS CRITIC - Attention in refactoring    
    $opt = array('assigned_on_build' => $args->build_id, 
                 'addPriority' => true,
                 'addExecInfo' => false);
    $filters += $opt;
    $opt['accessKeyType'] = 'tcase+platform+stackOnUser';
    $opt['useOptionalArrayFields'] = true;
    $opt['tlFeature'] = 'testCaseExecTaskAssignment';

    // platform filter is generated inside getFilteredSpecView() using $args->control_panel['setting_platform'];
    // $out = getFilteredSpecView($db, $args, $tplan_mgr, $tcase_mgr, $filters, $opt);
    $out = getFilteredSpecViewFlat($db, $args, $tplan_mgr, $tcase_mgr, $filters, $opt);
  break;

  default:
    show_instructions('tc_exec_assignment');
  break;
}


$gui->items = $out['spec_view'];
// useful to avoid error messages on smarty template.
$gui->items_qty = is_null($gui->items) ? 0 : count($gui->items);
//var_dump($out);
//die;
$debug = true;
if($debug){
  $testcases = array();
  $count = 0;
  if($gui->items_qty > 1){
    for($index = 1; $index< $gui->items_qty; $index ++){
      $gui->items[0]['testcases'] = array_merge($gui->items[0]['testcases'], 
                                                $gui->items[$index]['testcases']);    
    }
    for($index = 1; $index< $gui->items_qty; $index ++){
      unset($gui->items[$index]['testsuite']);
      unset($gui->items[$index]['testcases']);    
    }
  }

  /**
   * select checker_id from tcversion
   *
   * author:jinjiacun
   *   time:2017-12-26 14:33
   */
  $tcversion_id_list = array();
  if(count($gui->items[0]['testcases']) > 0){
    foreach($gui->items[0]['testcases'] as $v){
      foreach($v['tcversions_execution_type'] as $s_k => $s_v){
        $tcversion_id_list[] = $s_k;  
      }
      unset($s_k, $s_v);
    }
    unset($v);
  }
  //var_dump($tcversion_id_list);die;
  //var_dump($gui->items[0]['testcases'][0]);die;
  $template_sql = "select id,checker_id "
                  ." from ".$db->get_table('tcversions')
                  ." where id in (%s) and checker_id<>'' and checker_id<>0";
  if(count($tcversion_id_list) > 0){
    $sql = sprintf($template_sql, implode(",", $tcversion_id_list));
    //var_dump($sql);
    $rs  = $db->fetchRowsIntoMap($sql,'id',database::CUMULATIVE);
    //var_dump($rs);die;
   // $gui->items[0]['testcases'][0]['tcversion_checker_id'] = 123;
    if(count($gui->items[0]['testcases']) > 0){
      $version_list = array();
      foreach($gui->items[0]['testcases'] as $k => $v){
        foreach($v['tcversions_execution_type'] as $s_k => $s_v){
          $gui->items[0]['testcases'][$k]['tcversion_checker_id'] = $rs[$s_k][0]['checker_id'];
        }
        unset($s_k, $s_v);
      }
      unset($k, $v);
    }
  }

}

$gui->has_tc = $out['num_tc'] > 0 ? 1:0;
$gui->support_array = array_keys($gui->items);

if ($_SESSION['testprojectOptions']->testPriorityEnabled) 
{
  $urgencyCfg = config_get('urgency');
  $gui->priority_labels = init_labels($urgencyCfg["code_label"]);
}

// Changing to _flat template
$tpl = $templateCfg->template_dir . $templateCfg->default_template;
$tpl = str_replace('.tpl', '_flat.tpl', $tpl);

$smarty = new TLSmarty();
$smarty->assign('gui', $gui);
$smarty->display($tpl);

/*
  function: 

  args :
  
  returns: 

*/
function init_args()
{
  $_REQUEST = strings_stripSlashes($_REQUEST);
  $args = new stdClass();
  $args->user_id = intval($_SESSION['userID']);
  $args->tproject_id = intval($_SESSION['testprojectID']);
  $args->tproject_name = $_SESSION['testprojectName'];
      
  $key2loop = array('doActionButton' => null, 'doAction' => null,'level' => null , 'achecked_tc' => null, 
                    'version_id' => 0, 'has_prev_assignment' => null, 'send_mail' => false,
                    'tester_for_tcid' => null, 'feature_id' => null, 'id' => 0);
    
  foreach($key2loop as $key => $value)
  {
    $args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : $value;
  }
  

  // For more information about the data accessed in session here, see the comment
  // in the file header of lib/functions/tlTestCaseFilterControl.class.php.
  $form_token = isset($_REQUEST['form_token']) ? $_REQUEST['form_token'] : 0;
  $mode = 'plan_mode';
  $session_data = isset($_SESSION[$mode]) && isset($_SESSION[$mode][$form_token]) ? $_SESSION[$mode][$form_token] : null;

  $args->control_panel = $session_data;
    
  $key2loop = array('refreshTree' => array('key' => 'setting_refresh_tree_on_action', 'value' => 0),
                    'filter_assigned_to' => array('key' => 'filter_assigned_user', 'value' => null));
  
  foreach($key2loop as $key => $info)
  {
    $args->$key = isset($session_data[$info['key']]) ? $session_data[$info['key']] : $info['value']; 
  }
  
    
  $args->keyword_id = 0;
  $fk = 'filter_keywords';
  if (isset($session_data[$fk])) 
  {
    $args->keyword_id = $session_data[$fk];
    if (is_array($args->keyword_id) && count($args->keyword_id) == 1) 
    {
      $args->keyword_id = $args->keyword_id[0];
    }
  }
  
  $args->keywordsFilterType = null;
  $fk = 'filter_keywords_filter_type';
  if (isset($session_data[$fk])) 
  {
    $args->keywordsFilterType = $session_data[$fk];
  }
  
  
  $args->testcases_to_show = null;
  if (isset($session_data['testcases_to_show'])) 
  {
    $args->testcases_to_show = $session_data['testcases_to_show'];
  }
  
  $args->build_id = intval(isset($session_data['setting_build']) ? $session_data['setting_build'] : 0);
  $args->platform_id = intval(isset($session_data['setting_platform']) ? 
                       $session_data['setting_platform'] : 0);
  
  $args->tplan_id = intval(isset($session_data['setting_testplan']) ? $session_data['setting_testplan'] : 0);
  if ($args->tplan_id) 
  {
    $args->tplan_id = intval(isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testplanID']);
  }
    

  $args->targetFeature = intval(isset($_REQUEST['targetFeature']) ? $_REQUEST['targetFeature'] : 0);  
  $args->targetUser = intval(isset($_REQUEST['targetUser']) ? $_REQUEST['targetUser'] : 0);  


  $args->doBulkUserRemove = isset($_REQUEST['doBulkUserRemove']) ? 1 : 0;
  if($args->doBulkUserRemove)
  {
    $args->doAction = 'doBulkRemove';
  }  

  return $args;
}

/*
  function: initializeGui

  args :
  
  returns: 

*/
function initializeGui(&$dbHandler,$argsObj,&$tplanMgr,&$tcaseMgr)
{
  $platform_mgr = new tlPlatform($dbHandler,$argsObj->tproject_id);
  
  $tcase_cfg = config_get('testcase_cfg');
  $gui = new stdClass();
  $gui->platforms = $platform_mgr->getLinkedToTestplanAsMap($argsObj->tplan_id);
  $gui->usePlatforms = $platform_mgr->platformsActiveForTestplan($argsObj->tplan_id);
  $gui->bulk_platforms = $platform_mgr->getLinkedToTestplanAsMap($argsObj->tplan_id);
  $gui->bulk_platforms[0] = lang_get("all_platforms");
  ksort($gui->bulk_platforms);
    
  $gui->send_mail = $argsObj->send_mail;
  $gui->send_mail_checked = "";
  if($gui->send_mail)
  {
    $gui->send_mail_checked = ' checked="checked" ';
  }
    
  $gui->glueChar=$tcase_cfg->glue_character;
    
  if ($argsObj->level != 'testproject')
  {
    $gui->testCasePrefix = $tcaseMgr->tproject_mgr->getTestCasePrefix($argsObj->tproject_id);
    $gui->testCasePrefix .= $tcase_cfg->glue_character;
    $gui->keywordsFilterType = $argsObj->keywordsFilterType;
    $gui->build_id = $argsObj->build_id;
    $gui->tplan_id = $argsObj->tplan_id;
      
    $tplan_info = $tplanMgr->get_by_id($argsObj->tplan_id);
    $gui->testPlanName = $tplan_info['name'];
  
    $build_info = $tplanMgr->get_build_by_id($argsObj->tplan_id, $argsObj->build_id);
    $gui->buildName = $build_info['name'];
    $gui->main_descr = sprintf(lang_get('title_tc_exec_assignment'),$gui->buildName, $gui->testPlanName);

    $tproject_mgr = new testproject($dbHandler);
    $tproject_info = $tproject_mgr->get_by_id($argsObj->tproject_id);

    // add by zhouzhaoxin 20160608 to reduce the load time for exec user init
    $whereClause = "where id in (select distinct user_id from ".$dbHandler->get_table('user_testproject_roles')." where testproject_id = " . 
        $argsObj->tproject_id . ") or id in (select distinct user_id from ".$dbHandler->get_table('user_testplan_roles')." where testplan_id = " . 
        $argsObj->tplan_id . ")";
        
    $gui->all_users = tlUser::getAll($dbHandler,$whereClause,"id",null);
    $gui->users = getUsersForHtmlOptions($dbHandler,null,null,null,$gui->all_users);
    $gui->testers = getTestersForHtmlOptions($dbHandler,$argsObj->tplan_id,$tproject_info,$gui->all_users);
  }

  return $gui;
}


/**
 * send_mail_to_testers
 *
 *
 * @return void
 */
function send_mail_to_testers(&$dbHandler,&$tcaseMgr,&$guiObj,&$argsObj,$features,$operation)
{
  $testers['new']=null;
  $testers['old']=null;
  $mail_details['new']=lang_get('mail_testcase_assigned') . "<br /><br />";
  $mail_details['old']=lang_get('mail_testcase_assignment_removed'). "<br /><br />";
  $mail_subject['new']=lang_get('mail_subject_testcase_assigned');
  $mail_subject['old']=lang_get('mail_subject_testcase_assignment_removed');
  $use_testers['new']= ($operation == 'del') ? false : true ;
  $use_testers['old']= ($operation == 'ins') ? false : true ;
   

  $tcaseSet=null;
  $tcnames=null;
  $email=array();
   
  $assigner=$guiObj->all_users[$argsObj->user_id]->firstName . ' ' .
            $guiObj->all_users[$argsObj->user_id]->lastName ;
              
  $email['from_address']=config_get('from_email');
  $body_first_lines = lang_get('testproject') . ': ' . $argsObj->tproject_name . '<br />' .
                      lang_get('testplan') . ': ' . $guiObj->testPlanName .'<br /><br />';


  // Get testers id
  foreach($features as $feature_id => $value)
  {
    if($use_testers['new'])
    {
      $ty = (array)$value['user_id'];
      foreach($ty as $user_id)
      {
        $testers['new'][$user_id][$value['tcase_id']]=$value['tcase_id'];              
      }  
    }
  
    if( $use_testers['old'] )
    {
      $testers['old'][$value['previous_user_id']][$value['tcase_id']]=$value['tcase_id'];              
    }
        
    $tcaseSet[$value['tcase_id']]=$value['tcase_id'];
    $tcversionSet[$value['tcversion_id']]=$value['tcversion_id'];
  } 

  $infoSet = $tcaseMgr->get_by_id_bulk($tcaseSet,$tcversionSet);
  foreach($infoSet as $value)
  {
    $tcnames[$value['testcase_id']] = $guiObj->testCasePrefix . $value['tc_external_id'] . ' ' . $value['name'];    
  }
    
  $path_info = $tcaseMgr->tree_manager->get_full_path_verbose($tcaseSet);
  $flat_path=null;
  foreach($path_info as $tcase_id => $pieces)
  {
    $flat_path[$tcase_id]=implode('/',$pieces) . '/' . $tcnames[$tcase_id];  
  }


  foreach($testers as $tester_type => $tester_set)
  {
    if( !is_null($tester_set) )
    {
      $email['subject'] = $mail_subject[$tester_type] . ' ' . $guiObj->testPlanName;  
      foreach($tester_set as $user_id => $value)
      {
        // workaround till solution will be found
        if($user_id <= 0)
        {
          continue;
        }  

        $userObj=$guiObj->all_users[$user_id];
        $email['to_address']=$userObj->emailAddress;
        $email['body'] = $body_first_lines;
        $email['body'] .= sprintf($mail_details[$tester_type],
                          $userObj->firstName . ' ' .$userObj->lastName,$assigner);
        foreach($value as $tcase_id)
        {
          $email['body'] .= $flat_path[$tcase_id] . '<br />';  
        }  
        $email['body'] .= '<br />' . date(DATE_RFC1123);
        $email_op = email_send($email['from_address'], $email['to_address'], 
        $email['subject'], $email['body'], '', true, true);
      } // foreach($tester_set as $user_id => $value)
    }                       
  }
}

function checkRights(&$db,&$user)
{
  return $user->hasRight($db,'testplan_planning');
}
