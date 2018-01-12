<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 * This script is distributed under the GNU General Public License 2 or later.
 *
 * @filesource	resultsByTesterPerBuild.php
 * @package     TestLink
 * @author      Andreas Simon
 * @copyright   2010 - 2014 TestLink community
 *
 * Lists results and progress by tester per build.
 * 
 * @internal revisions
 * @since  1.9.10
 *
 * add
 * author:jinjiacun
 * time:2018-1-5 10:53
 */

require_once("../../config.inc.php");
require_once("common.php");
require_once('exttable.class.php');
$templateCfg = templateConfiguration();

list($args,$tproject_mgr,$tplan_mgr) = init_args($db);
$user = new tlUser($db);
$testcase = new testcase($db);


$gui = init_gui($args);
$charset = config_get('charset');

#get stage map
$gui->stage_map = $tlCfg->build_stage;
unset($gui->stage_map[0]);
// By default Only open builds are displayed
// we will check if we have open builds
$openBuildsQty = $tplan_mgr->getNumberOfBuilds($args->tplan_id,null,testplan::OPEN_BUILDS);

$openBuildsQty = 1;
// not too wise duplicated code, but effective => Quick & Dirty
if( $openBuildsQty <= 0 && !$args->show_closed_builds)
{
	$gui->warning_message = lang_get('no_open_builds');
  $gui->tableSet = null;
	$smarty = new TLSmarty();
	$smarty->assign('gui',$gui);
	$smarty->display($templateCfg->template_dir . $templateCfg->default_template);
	exit();
}

$metricsMgr = new tlTestPlanMetrics($db);
$statusCfg = $metricsMgr->getStatusConfig();
// Here need to work, because all queries consider ONLY ACTIVE STATUS
$option = $args->show_closed_builds ? null : testplan::GET_OPEN_BUILD;
$build_set = $metricsMgr->get_builds($args->tplan_id, testplan::GET_ACTIVE_BUILD, $option);
if($build_set
&& count($build_set) > 0){
  foreach($build_set as $v){
    $gui->build[$v['id']] = $v['name'];
  }
  $gui->build[0] = '全部';
}

$gui->select_stage_id = 1;
if($_REQUEST['query']){
  #stage
  $stage_id = isset($_REQUEST['stage_id'])?intval($_REQUEST['stage_id']):1;
  $gui->select_stage_id = $stage_id;
  
  #build
  $build_id = isset($_REQUEST['build_id'])?intval($_REQUEST['build_id']):0;
  $gui->select_build_id = $build_id;
  $metrics = $metricsMgr->getStatusTotalsByBuildUAForRenderExAll($args->tplan_id,
                                                                 $build_id,
                                                          array('processClosedBuilds' => $args->show_closed_builds));

}else if($_REQUEST['radio_chg']){
  if($args->stage_id != 0){
    $template_sql = "select id,name "
                   ." from ".$db->get_table("builds")
                   ." where is_open = 1 and active = 1 "
                   ." and testplan_id=%d and stage_id=%d ";
    $sql = sprintf($template_sql, $args->tplan_id, $args->stage_id);
    $gui->build = $db->fetchColumnsIntoMap($sql, "id", "name");
    $gui->select_stage_id = $args->stage_id;
    if(count($gui->build) > 0){
      $gui->build[0] = '全部';  
    }
  }
}else{
  $template_sql = "select id,name "
                   ." from ".$db->get_table("builds")
                   ." where is_open = 1 and active = 1 "
                   ." and testplan_id=%d and stage_id=%d ";
    $sql = sprintf($template_sql, $args->tplan_id, 1);
    $gui->build = $db->fetchColumnsIntoMap($sql, "id", "name");
    $gui->select_stage_id = $args->stage_id;
    if(count($gui->build) > 0){
      $gui->build[0] = '全部';  
    }
    $gui->select_stage_id = 1;
}


$matrix = $metrics->info;
/*print_r($metrics);
die;*/

$names        = $user->getNames($db);
$module_names = $testcase->get_modules($_SESSION['testprojectID']);
// get the progress of the whole build based on executions of single users
$build_statistics = array();
foreach($matrix as $build_id => $build_execution_map) 
{
  $build_statistics[$build_id]['total'] = 0;
  $build_statistics[$build_id]['executed'] = 0;
  $build_statistics[$build_id]['total_time'] = 0;

  foreach ($build_execution_map as $user_id => $statistics) 
  {
    // total assigned test cases
    $build_statistics[$build_id]['total'] += $statistics['total'];
    
    // total executed testcases
    $executed = $statistics['total'] - $statistics['not_run']['count']; 
    $build_statistics[$build_id]['executed'] += $executed;

    $build_statistics[$build_id]['total_time'] += $statistics['total_time'];
  }

  // build progress
  $build_statistics[$build_id]['progress'] = round($build_statistics[$build_id]['executed'] / 
                                                   $build_statistics[$build_id]['total'] * 100,2);

  // We have to fill this if we want time at BUILD LEVEL
  $build_statistics[$build_id]['total_time'] = minutes2HHMMSS($build_statistics[$build_id]['total_time']);
}

// build the content of the table
$rows = array();

$lblx = array('progress_absolute' => lang_get('progress_absolute'),
              'total_time_hhmmss' => lang_get('total_time_hhmmss') );

foreach ($matrix as $build_id => $statistics) 
{

  $first_row = $build_set[$build_id]['name'] . " - " . 
               $lblx['progress_absolute'] . " {$build_statistics[$build_id]['progress']}%";
        $name = "<a href=\"javascript:openAssignmentOverviewWindow(" .
                "{$user_id}, {$build_id}, {$args->tplan_id});\"></a>";
        $current_row[] = $name;
        
            $current_row = array();
            $current_row[] = 'test';
            
            // add username and link it to tcAssignedToUser.php
            // total count of testcases assigned to this user on this build
            $current_row[] = $build_set[$build_id]['name'];#build
            $current_row[] = $statistics['total'];
            
            // add count and percentage for each possible status
            foreach ($statusCfg as $status => $code) 
            {
              $current_row[] = $statistics[$status]['count'];
              $current_row[] = $statistics[$status]['percentage'];
            }
            
            $current_row[] = $statistics['progress'];
            $current_row[] = $statistics['tc_exec_times'];
           // $current_row[] = minutes2HHMMSS($statistics['total_time']);
            
            // add this row to the others
            $rows[] = $current_row; 
}

$columns = getTableHeader($statusCfg);
$smartTable = new tlExtTable($columns, $rows, 'tl_table_results_by_tester_per_build');
$smartTable->title = lang_get('results_by_tester_per_build');
$smartTable->setGroupByColumnName(lang_get('build'));

// enable default sorting by progress column
$smartTable->setSortByColumnName(lang_get('progress'));

//define toolbar
$smartTable->showToolbar = true;
//$smartTable->toolbarExpandCollapseGroupsButton = true;
$smartTable->toolbarShowAllColumnsButton = true;

$gui->tableSet = array($smartTable);

// show warning message instead of table if table is empty
$gui->warning_message = (count($rows) > 0) ? '' : lang_get('no_testers_per_build');

$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);


/**
 * initialize user input
 * 
 * @param resource dbHandler
 * @return array $args array with user input information
 */
function init_args(&$dbHandler)
{
  $iParams = array("apikey"                     => array(tlInputParameter::STRING_N,32,64),
                   "tproject_id"                => array(tlInputParameter::INT_N), 
	                 "tplan_id"                   => array(tlInputParameter::INT_N),
                   "build_id"                   => array(tlInputParameter::INT_N),
                   'stage_id'                   => array(tlInputParameter::INT_N),
                   "format"                     => array(tlInputParameter::INT_N),
                   "show_closed_builds"         => array(tlInputParameter::CB_BOOL),
                   "show_closed_builds_hidden"  => array(tlInputParameter::CB_BOOL));

	$args    = new stdClass();
	$pParams = R_PARAMS($iParams,$args);
  if( !is_null($args->apikey) )
  {
    $cerbero = new stdClass();
    $cerbero->args = new stdClass();
    $cerbero->args->tproject_id = $args->tproject_id;
    $cerbero->args->tplan_id = $args->tplan_id;

    if(strlen($args->apikey) == 32)
    {
      $cerbero->args->getAccessAttr = true;
      $cerbero->method = 'checkRights';
      $cerbero->redirect_target = "../../login.php?note=logout";
      setUpEnvForRemoteAccess($dbHandler,$args->apikey,$cerbero);
    }
    else
    {
      $args->addOpAccess = false;
      $cerbero->method = null;
      setUpEnvForAnonymousAccess($dbHandler,$args->apikey,$cerbero);
    }  
  }
  else
  {
    testlinkInitPage($dbHandler,false,false,"checkRights");  
	  $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
  }

  $tproject_mgr = new testproject($dbHandler);
  $tplan_mgr    = new testplan($dbHandler);
	if($args->tproject_id > 0) 
	{
		$args->tproject_info        = $tproject_mgr->get_by_id($args->tproject_id);
		$args->tproject_name        = $args->tproject_info['name'];
		$args->tproject_description = $args->tproject_info['notes'];
	}
	
	if ($args->tplan_id > 0) 
	{
		$args->tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
	}
	
 	$selection = false;
  if($args->show_closed_builds) 
  {
  	$selection = true;
  } 
  else if ($args->show_closed_builds_hidden) 
  {
  	$selection = false;
  } 
  else if (isset($_SESSION['reports_show_closed_builds'])) 
  {
  	$selection = $_SESSION['reports_show_closed_builds'];
  }
  $args->show_closed_builds = $_SESSION['reports_show_closed_builds'] = $selection;

	return array($args,$tproject_mgr,$tplan_mgr);
}


/**
 * initialize GUI
 * 
 * @param stdClass $argsObj reference to user input
 * @return stdClass $gui gui data
 */
function init_gui(&$argsObj) 
{
	$gui = new stdClass();
	
	$gui->pageTitle          = lang_get('current_all_case_exec_stat');
	$gui->warning_msg        = '';
	$gui->tproject_name      = $argsObj->tproject_name;
	$gui->tplan_name         = $argsObj->tplan_info['name'];
	$gui->show_closed_builds = $argsObj->show_closed_builds;
	return $gui;
}

/**
 * 
 * 
 */
function getTableHeader($statusCfg)
{
	$resultsCfg = config_get('results');	

	$colCfg = array();	

  $colCfg[] = array('title_key' => 'build', 
                    'width'     => 40, 
                    'type'      => 'text', 
                    'sortType'  => 'asText',
                    'filter'    => 'string');

	
  $colCfg[] = array('title_key' => 'build', 
                    'width'     => 80, 
                    'type'      => 'text', 
                    'sortType'  => 'asText',
                    'filter'    => 'string');
	$colCfg[] = array('title_key' => 'th_tc_assigned', 
                    'width'     => 20, 
                    'sortType'  => 'asFloat',
                    'filter'    => 'numeric');

	foreach ($statusCfg as $status => $code) 
	{
		$label = $resultsCfg['status_label'][$status];
		$colCfg[] = array('title_key' => $label, 
                      'width'     => 20, 
                      'sortType'  => 'asInt',
                      'filter'    => 'numeric');
		$colCfg[] = array('title'     => lang_get($label).' '.lang_get('in_percent'),
		                  'col_id'    => 'id_'.$label.'_percent', 
                      'width'     => 20, 
		                  'type'      => 'float', 
                      'sortType'  => 'asFloat', 
                      'filter'    => 'numeric');
	}
	
	$colCfg[] = array('title_key' => 'progress', 
                    'width'     => 20, 
                    'type'      => 'float',
                    'sortType'  => 'asFloat', 
                    'filter'    => 'numeric');
	
	//add by zhouzhaoxin 20170227 for add execution testcase number
	$colCfg[] = array('title_key' => 'tc_exec_times', 
                    'width'     => 20, 
                    'sortType'  => 'asFloat',
                    'filter'    => 'numeric');

	return $colCfg;	                   
}

/**
 *
 * ATTENTION:
 * because minutes can be a decimal (i.e 131.95) if I use standard operations i can get
 * wrong results
 *
 * 
 */
function minutes2HHMMSS($minutes) 
{
  // Attention:
  // $min2sec = $minutes * 60;
  // doing echo provide expected result, but when using to do more math op
  // result was wrong, 1 second loss.
  // Example with 131.95 as input
  // $min2sec = sprintf('%d',($minutes * 60));
  $min2sec = bcmul($minutes, 60);

  // From here number will not have decimal => will return to normal operators.
  // do not know perfomance impacts related to BC* functions
  $hh = floor($min2sec/3600);
  $mmss = ($min2sec%3600);

  $mm = floor($mmss/60); 
  $ss = $mmss%60;

  return sprintf('%02d:%02d:%02d', $hh, $mm, $ss);
}




/*
 * rights check function for testlinkInitPage()
 */
function checkRights(&$db,&$user,$context = null)
{
  if(is_null($context))
  {
    $context = new stdClass();
    $context->tproject_id = $context->tplan_id = null;
    $context->getAccessAttr = false; 
  }

  $check = $user->hasRight($db,'testplan_metrics',$context->tproject_id,$context->tplan_id,$context->getAccessAttr);
  return $check;
}


