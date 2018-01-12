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
$gui = initializeGui($db, $args);

if($args->tproject_id)
{
    $tcase_mgr = new testcase ($db);
    $tproject_mgr = new testproject($db);
    
    $tcase_cfg = config_get('testcase_cfg');
    
    $gui->tcasePrefix = $tproject_mgr->getTestCasePrefix($args->tproject_id);
    $gui->tcasePrefix .= $tcase_cfg->glue_character;
    
    $items = array();
    
    $tc_result = array();
    if ($args->fifty_val != null && $args->fifty_val != 'null' && $args->fifty_val !== '0')
    {
        $tc_result = get_case_by_suite_id($args->fifty_val, $db, $args->reqid);
    }
    else if ($args->fouth_val != null && $args->fouth_val != 'null' && $args->fouth_val != '0')
    {
        $tc_result = get_case_by_suite_id($args->fouth_val, $db, $args->reqid);
    }
    else if ($args->third_val != null && $args->third_val != 'null' && $args->third_val != '0')
    {
        $tc_result = get_case_by_suite_id($args->third_val, $db, $args->reqid);
    }
    else if ($args->secound_val != null && $args->secound_val != 'null' && $args->secound_val != '0')
    {
        $tc_result = get_case_by_suite_id($args->secound_val, $db, $args->reqid);
    }
    else if ($args->first_val != null && $args->first_val != 'null' && $args->first_val != '0')
    {
        $tc_result = get_case_by_suite_id($args->first_val, $db, $args->reqid);
    }
    
    $current_id = 0;
    if (is_array($tc_result)) {
        foreach ($tc_result as $index => $row)
        {
            if ($index != $current_id)
            {
                // only add the max version for one case
                $curMsg = array('tc_name' => $row['tc_name'], 
                    'tc_id' => $row['tc_id'],
                    'external_id' => $gui->tcasePrefix . $row['tc_external_id'], 
                    'summary' => $row['summary']);
                $items[] = $curMsg;
                $current_id = $index;
            } 
        }
    }
 
    $gui->tc_Items=$items;
    $gui->checked_Item='';
    $gui->isall=0;
    $gui->req_id = $args->reqid;
    
    if($args->is_all==='1')
    {
        foreach($items as $k=>$val)
        {
            $gui->checked_Item.=$val["external_id"]."/";
        }
        $gui->isall=1;
    }
 
  //=====================================================================================================
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
$tpl = 'reqAddTestCaseView.tpl';
$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir .$tpl);


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
    
    $args->first_val = null;
    $args->secound_val = null;
    $args->third_val = null;
    $args->fouth_val = null;
    $args->fifty_val = null;
    $args->is_all = null;
    $args->reqid = null;
    $args->req_version = null;

    if (isset($_GET["first"])) 
    {
        $args->first_val = $_GET["first"];
    }
    if (isset($_GET["secound"]))
    {
        $args->secound_val = $_GET["secound"];
    }
    if (isset($_GET["third"]))
    {
        $args->third_val = $_GET["third"];
    }
    if (isset($_GET["fouth"]))
    {
        $args->fouth_val = $_GET["fouth"];
    }
    if (isset($_GET["fifty"]))
    {
        $args->fifty_val = $_GET["fifty"];
    }
    if (isset($_GET["isall"]))
    {
        $args->is_all = $_GET["isall"];
    }
    if (isset($_GET["reqid"]))
    {
        $args->reqid = $_GET["reqid"];
    }
    if (isset($_GET["reqver"]))
    {
        $args->req_version = $_GET["reqver"];
    }

    return $args;
}

function initializeGui(&$dbHandler,$argsObj)
{
  $gui = new stdClass();
  $gui->tproject_id = $argsObj->tproject_id;
  $gui->req_id = $argsObj->reqid;
  $gui->req_version = $argsObj->req_version;
  $gui->tplans = null;
  $gui->user_feedback = '';
  $gui->grants = new stdClass();
  $gui->grants->testplan_create = $argsObj->user->hasRight($dbHandler,"mgt_testplan_create",$argsObj->tproject_id);
  $gui->main_descr = lang_get('testplan_title_tp_management'). " - " . 
                     lang_get('testproject') . ' ' . $argsObj->tproject_name;
  $gui->tc_Items=null;
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

/**
 * get test cast by test suite id, include test case under its child test suite
 * @param $id => test suite id
 * @param $db => db handler
 * @return test case info list
 * @author zhouzhaoxin
 * @since 20161109 add
 */
function get_case_by_suite_id($id, $db, $req_id)
{
    //get test suite node_depth_abs first
    $suite_abs = GetAbsByNodeId($id, $db);
    
    $sql = "select nh2.name as tc_name, nh2.id as tc_id, tcv.version as version, tcv.tc_external_id as tc_external_id," . 
	    " tcv.summary as summary from " . 
	    $db->get_table('nodes_hierarchy') . " nh " .
        "inner join " . $db->get_table('tcversions') . " tcv on nh.id = tcv.id " .
        "inner join " . $db->get_table('nodes_hierarchy') . " nh2 on nh.parent_id = nh2.id " .
        " where nh2.node_type_id = 3 and nh.node_depth_abs like '" . $suite_abs . "%' " . 
        "and not exists (select testcase_id from " .
		$db->get_table('req_coverage') . " where req_id = " . $req_id . " and testcase_id = nh2.id) " .
        "order by nh2.id asc, tcv.version desc" ;
           
    $result = $db->fetchRowsIntoMap($sql, 'tc_id');
    
    return $result;
}

?>