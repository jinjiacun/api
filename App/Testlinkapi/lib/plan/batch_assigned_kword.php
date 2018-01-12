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

switch($args->doAction)
{
  case 'std':
    if(!is_null($args->achecked_tc))
    {
      $db_now = $db->db_now();
      $features2 = array( 'upd' => array(), 'ins' => array(), 'del' => array());
      $method2call = array( 'upd' => 'update', 'ins' => 'assign', 'del' => 'delete_by_feature_id_and_build_id');
      $called = array( 'upd' => false, 'ins' => false, 'del' => false);
      
      foreach($args->tester_for_tcid as $key_tc => $platform_tcversion)
      {
          $tcase_mgr->addKeywords($key_tc, $platform_tcversion[0]);
      }
    }  
  break;


  case 'doBulkRemove':
    if(!is_null($args->achecked_tc))
    {
      $op='del';
      $features2[$op] = array();
      foreach($args->achecked_tc as $key_tc => $platform_tcversion)
      {
          $assignment_mgr->delete_by_feature_id_and_keyword_id($key_tc, $args->tester_for_tcid);
          $called[$key]=true;
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
    $assignment_mgr->deleteBybq($args->targetUser,$args->targetFeature);
  break; 
}

switch($args->edit)
{
  case 'testcase':
    // build the data need to call gen_spec_view
    $tc_out = $tplan_mgr->getLinkInfoForTc($args->id);
    
    $out = array();
    $out['spec_view'][0] = $tc_out;
    $out['num_tc'] = 1;
  break;
    
  case 'testsuite':
    $filters = array(
        'filter_tc_id' => $args->control_panel['filter_tc_id'],
        'filter_testcase_name' => $args->filter_testcase_name,
        'filter_toplevel_testsuite' => $args->filter_toplevel_testsuite,
        'filter_workflow_status' => $args->filter_workflow_status,
        'filter_importance' => $args->filter_importance,
        'filter_execution_type' => $args->filter_execution_type);
    
    foreach ($filters as $key => $value)
    {
        if (isset($args->control_panel) && isset($args->control_panel[$key]))
        {
            $filters[$key] = $args->control_panel[$key];
        }
    }
    
    $opt = array();
    $out = getFilteredSpecViewFlatNew($db, $args, $tplan_mgr, $tcase_mgr, $filters, $opt);
  break;

  default:
    show_instructions('batch_assigned_kword');
  break;
}


$gui->items = $out['spec_view'];

// useful to avoid error messages on smarty template.
$gui->items_qty = is_null($gui->items) ? 0 : count($gui->items);
$gui->has_tc = $out['num_tc'] > 0 ? 1 : 0;
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
                    'tester_for_tcid' => null, 'feature_id' => null, 'id' => 0, 'edit' => null);
    
  foreach($key2loop as $key => $value)
  {
     $args->$key = isset($_REQUEST[$key]) ? $_REQUEST[$key] : $value;
  }
  

  // For more information about the data accessed in session here, see the comment
  // in the file header of lib/functions/tlTestCaseFilterControl.class.php.
  $form_token = isset($_REQUEST['form_token']) ? $_REQUEST['form_token'] : 0;
  $mode = 'plan_assignedkword_mode';
  $session_data = isset($_SESSION[$mode]) && isset($_SESSION[$mode][$form_token]) ? $_SESSION[$mode][$form_token] : null;

  $args->control_panel = $session_data;
    
  $key2loop = array('refreshTree' => array('key' => 'setting_refresh_tree_on_action', 'value' => 0),
                    'filter_assigned_to' => array('key' => 'filter_assigned_user', 'value' => null));
  
  foreach($key2loop as $key => $info)
  {
    $args->$key = isset($session_data[$info['key']]) ? $session_data[$info['key']] : $info['value']; 
  }
  
  $args->testcases_to_show = null;
  if (isset($session_data['testcases_to_show'])) 
  {
    $args->testcases_to_show = $session_data['testcases_to_show'];
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
    
  $gui->send_mail = $argsObj->send_mail;
  $gui->send_mail_checked = "";
  if($gui->send_mail)
  {
    $gui->send_mail_checked = ' checked="checked" ';
  }
    
  $gui->glueChar=$tcase_cfg->glue_character;
    
  if ($argsObj->edit != 'testproject')
  {
    $gui->testCasePrefix = $tcaseMgr->tproject_mgr->getTestCasePrefix($argsObj->tproject_id);
    $gui->testCasePrefix .= $tcase_cfg->glue_character;
    $gui->main_descr = sprintf(lang_get('title_tc_exec_assignment'),$gui->buildName, $gui->testPlanName);
    $tproject_mgr = new testproject($dbHandler);
    $tproject_info = $tproject_mgr->get_by_id($argsObj->tproject_id);  
    $gui->bq = $tcaseMgr->get_keywords_projectid($argsObj->tproject_id);
  }

  return $gui;
}


function checkRights(&$db,&$user)
{
  return $user->hasRight($db,'testplan_planning');
}
