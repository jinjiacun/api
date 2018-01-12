<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	planView.php
 * @internal revisions
 * @since 1.9.12
 *
 */
require_once('../../config.inc.php');
require_once("common.php");
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();

$args=init_args();
$gui = initializeGui($db,$args);

if($args->tproject_id)
{
  $tproject_mgr = new testproject($db);
  
  $tcase_mgr = new testcase ($db);
  $tproject_mgr = new testproject($db);
  
  $gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($args->tproject_id);
  $gui->tcasePrefix .= $tcase_cfg->glue_character;
  
  //die('title:'.$gui->tcasePrefix);
  $tcase_cfg = config_get('testcase_cfg');
  $filter=null;
  $tcaseID =null;
  $items = array();
  
  if($args->targetTestCase != "" && strcmp($args->targetTestCase,$gui->tcasePrefix) != 0)
  {
      if (strpos($args->targetTestCase,$tcase_cfg->glue_character) === false)
      {
          $args->targetTestCase = $gui->tcasePrefix . $args->targetTestCase;
      }
  
      $tcaseID = $tcase_mgr->getInternalID($args->targetTestCase);
      $filter = " AND NH_TCV.parent_id = " . intval($tcaseID);
  }
  else
  {
      $tproject_mgr->get_all_testcases_id($args->tproject_id,$a_tcid);
  
      if(!is_null($a_tcid))
      {
          $filter= " AND NH_TCV.parent_id IN (" . implode(",",$a_tcid) . ") ";
      }
      else
      {
          // Force Nothing extracted, because test project
          // has no test case defined
          $filter = " AND 1 = 0 ";
      }
  }
  $alltcSql="SELECT DISTINCT NH_TC.id AS testcase_id,NH_TC.name,TCV.id AS tcversion_id, TCV.summary,TCV.tc_id,TCV.version, TCV.tc_external_id FROM nodes_hierarchy NH_TC JOIN nodes_hierarchy NH_TCV ON NH_TCV.parent_id = NH_TC.id JOIN tcversions TCV ON NH_TCV.id = TCV.id LEFT OUTER JOIN nodes_hierarchy NH_TCSTEPS ON NH_TCSTEPS.parent_id = NH_TCV.id LEFT OUTER JOIN tcsteps TCSTEPS ON NH_TCSTEPS.id = TCSTEPS.id WHERE 1=1";
  $alltcSql=$alltcSql.$filter;
  //die('title:'.$alltcSql);
  $idx=0;
  $map = $db->fetchRowsIntoMap($alltcSql,'testcase_id');
  foreach($map as $id => $row)
  {
      // die('title6:'.$row['name'].$row['tc_id'].$row['tc_external_id'].$row['summary']);
      $items[$idx] = array('tc_name' => $row['name'],'tc_id' => $row['tc_id'],'external_id'=>$gui->tcasePrefix.$row['tc_external_id'],'summary'=>$row['summary']);
      $idx=$idx+1;
  }
  //die('title9:'.$items[0]['tc_id']);
  $gui->tc_Items=$items;
  
  
  
  $gui->tplans = $args->user->getAccessibleTestPlans($db,$args->tproject_id,null,
                                                     array('output' =>'mapfull', 'active' => null));
  $gui->drawPlatformQtyColumn = false;
  
  if( !is_null($gui->tplans) && count($gui->tplans) > 0 )
  {
    // do this test project has platform definitions ?
    $tplan_mgr = new testplan($db);
    $tplan_mgr->platform_mgr->setTestProjectID($args->tproject_id);
    $dummy = $tplan_mgr->platform_mgr->testProjectCount();
    $gui->drawPlatformQtyColumn = $dummy[$args->tproject_id]['platform_qty'] > 0;

    $tplanSet = array_keys($gui->tplans);
    $dummy = $tplan_mgr->count_testcases($tplanSet,null,array('output' => 'groupByTestPlan'));
    $buildQty = $tplan_mgr->get_builds($tplanSet,null,null,array('getCount' => true));
    $rightSet = array('testplan_user_role_assignment');

    foreach($tplanSet as $idk)
    {
      $gui->tplans[$idk]['tcase_qty'] = isset($dummy[$idk]['qty']) ? intval($dummy[$idk]['qty']) : 0;
      $gui->tplans[$idk]['build_qty'] = isset($buildQty[$idk]['build_qty']) ? intval($buildQty[$idk]['build_qty']) : 0;
      if( $gui->drawPlatformQtyColumn )
      {
        $plat = $tplan_mgr->getPlatforms($idk);
        $gui->tplans[$idk]['platform_qty'] = is_null($plat) ? 0 : count($plat);
      }


      // Get rights for each test plan
      foreach($rightSet as $target)
      {
        // DEV NOTE - CRITIC
        // I've made a theorically good performance choice to 
        // assign to $roleObj a reference to different roleObj
        // UNFORTUNATELLY this choice was responsible to destroy point object
        // since second LOOP
        $roleObj = null;
        if($gui->tplans[$idk]['has_role'] > 0)
        {
          $roleObj = $args->user->tplanRoles[$gui->tplans[$idk]['has_role']];
        }  
        else if (!is_null($args->user->tprojectRoles) && 
                 isset($args->user->tprojectRoles[$args->tproject_id]) )
        {
          $roleObj = $args->user->tprojectRoles[$args->tproject_id];
        }  

        if(is_null($roleObj))
        {
          $roleObj = $args->user->globalRole;
        }  
        $gui->tplans[$idk]['rights'][$target] = $roleObj->hasRight($target);  
      }  
    }    
    unset($tplan_mgr);  
  }
  unset($tproject_mgr);  
}

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * init_args
 *
 */
function init_args()
{
    $args = new stdClass();
    $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0 ;
    $args->tproject_name = isset($_SESSION['testprojectName']) ? trim($_SESSION['testprojectName']) : '' ;

    $args->user = $_SESSION['currentUser'];
    $args->checkedItem = isset($_REQUEST['checkedItem']) ? intval($_REQUEST['checkedItem']) : "";
    return $args;
}

function initializeGui(&$dbHandler,$argsObj)
{
  $gui = new stdClass();
  $gui->tproject_id = $argsObj->tproject_id;
  $gui->tplans = null;
  $gui->user_feedback = '';
  $gui->grants = new stdClass();
  $gui->grants->testplan_create = $argsObj->user->hasRight($dbHandler,"mgt_testplan_create",$argsObj->tproject_id);
  $gui->main_descr = lang_get('testplan_title_tp_management'). " - " . 
                     lang_get('testproject') . ' ' . $argsObj->tproject_name;

  return $gui;
}


/**
 * checkRights
 *
 */
function checkRights(&$db,&$user)
{
  return $user->hasRight($db,'mgt_testplan_create');
}