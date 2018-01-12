<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * While in test specification feature, assign TEST CASE version to multiple
 * ACTIVE test plans
 *
 * @package     TestLink
 * @author      Amit Khullar - amkhullar@gmail.com
 * @copyright   2007-2014, TestLink community 
 * @filesource  tcAssign2Tplan.php,v 1.8 2010/05/20 18:20:46 franciscom Exp $
 *
 *
 * @internal revisions
 * 20160613 zhouzhaoxin  assign testcases to build
 **/

require_once("../../config.inc.php");
require_once("common.php");
testlinkInitPage($db);
$templateCfg = templateConfiguration();

$tcase_mgr=new testcase($db);
$tplan_mgr=new testplan($db);
$tproject_mgr=new testproject($db);

$glue = config_get('testcase_cfg')->glue_character;
$args = init_args();
$gui = initializeGui($args);

$options['output'] = 'essential';
$tcase_all_info = $tcase_mgr->get_by_id($args->tcase_id,testcase::ALL_VERSIONS,null,$options);

if( !is_null($tcase_all_info) )
{
  foreach($tcase_all_info as $tcversion_info)
  {
    if($tcversion_info['id'] == $args->tcversion_id )
    {
      $version = $tcversion_info['version'];
      $gui->pageTitle=lang_get('test_case') . ':' . $tcversion_info['name'];
      $gui->tcaseIdentity = $tproject_mgr->getTestCasePrefix($args->tproject_id);
      $gui->tcaseIdentity .= $glue . $tcversion_info['tc_external_id'] . ':' . $tcversion_info['name'];
      break;      
    }   
  } 
}

$link_info = $tcase_mgr->get_linked_builds($args->tcase_id);
$buildSet = $tproject_mgr->get_all_builds($args->tproject_id,array('build_status' => 1));

if (!is_null($buildSet))
{
  $linked_builds = null;
  $has_links = array_fill_keys(array_keys($buildSet), false);
  $linked_builds = array();
  
  //initall the selected build info
  if (!is_null($link_info))
  {
      foreach($link_info as $build_id => $build_info)
      {
          $has_links[$build_id] = true;          
          $linked_builds[$build_id] = array();
          
          $linked_builds[$build_id]['tcversion_id'] = $build_info['tcversion_id'];
          $linked_builds[$build_id]['version'] = $build_info['version'];
          $linked_builds[$build_id]['draw_checkbox'] = false;
      }
  }
  
  //enable link of target testcase version to all builds
  foreach ($buildSet as $build_id => $build_info)
  {
      $target_version = $version;
      $target_version_id = $args->tcversion_id;
      $draw_checkbox = true;
      
      // if testcases has assigned to builds, change the version to assign version
      if ($has_links[$build_id])
      {
          $target_version = $linked_builds[$build_id]['version'];
          $target_version_id = $linked_builds[$build_id]['tcversion_id'];
          $draw_checkbox = false;
      }
      
      // set the gui build info
      $gui->builds[$build_id] = $build_info;
      $gui->builds[$build_id]['version'] = $target_version;
      $gui->builds[$build_id]['tcversion_id'] = $target_version_id;
      $gui->builds[$build_id]['draw_checkbox'] = $draw_checkbox;
  }

  // Check if submit button can be displayed.
  $gui->can_do=false;  // because an OR logic will be used
  foreach($gui->builds as $build_id => $build_info)  
  {
      $gui->can_do = $gui->can_do || $gui->builds[$build_id]['draw_checkbox'];     
  }   
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * init_args
 * creates a sort of namespace
 *
 * @return  object with some REQUEST and SESSION values as members.
 */
function init_args()
{
  $_REQUEST = strings_stripSlashes($_REQUEST);

  // if any piece of context is missing => we will display nothing instead of crashing WORK TO BE DONE
  $args = new stdClass();
  $args->tplan_id = isset($_REQUEST['tplan_id']) ? $_REQUEST['tplan_id'] : $_SESSION['testplanID'];
  $args->tproject_id = isset($_REQUEST['tproject_id']) ? $_REQUEST['tproject_id'] : $_SESSION['testprojectID'];
  $args->tcase_id = isset($_REQUEST['tcase_id']) ? $_REQUEST['tcase_id'] : 0;
  $args->tcversion_id = isset($_REQUEST['tcversion_id']) ? $_REQUEST['tcversion_id'] : 0;
  return $args; 
}

/**
 * 
 *
 */
function initializeGui($argsObj)
{
  $guiObj = new stdClass();
  $guiObj->pageTitle='';
  $guiObj->tcaseIdentity='';
  $guiObj->mainDescription=lang_get('add_tcversion_to_plans');;
  $guiObj->tcase_id=$argsObj->tcase_id;
  $guiObj->tcversion_id=$argsObj->tcversion_id;
  $guiObj->can_do=false;
  $guiObj->item_sep=config_get('gui')->title_separator_2;
  $guiObj->cancelActionJS = 'location.href=fRoot+' . "'" . "lib/testcases/archiveData.php?" .
                            'edit=testcase&id=' . intval($argsObj->tcase_id) . "'"; 
  
  return $guiObj;
}
