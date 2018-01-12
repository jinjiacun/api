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

// By default Only open builds are displayed
// we will check if we have open builds
$openBuildsQty = $tplan_mgr->getNumberOfBuilds($args->tplan_id,null,testplan::OPEN_BUILDS);

#$openBuildsQty = 1;
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

/*print_r($metrics);
die;*/
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
$names        = $user->getNames($db);
$gui->user = get_select_tcproject_user_id($_SESSION['testprojectID'], $db);
$gui->user[0] = '全部';
$module_names = $testcase->get_modules($_SESSION['testprojectID']);
$gui->module  = $module_names;
$gui->module[0] = '全部';

$build_ids_str = '';
$gui->select_build_id = 0;
$gui->select_user_id  = 0;
$gui->select_mod_id   = 0;
$gui->begin_date      = '';
$gui->end_date        = '';
$collect_build_total = array();
$collect_build_exec  = array();
$collect_build_status_exec = array();
$plan_progress_list        = array();
if($_REQUEST['query']){
  if($args->build_id == 0){
     $sql_build = "select id "
                  ." from ".$db->get_table('builds')
                  ." where testplan_id = $args->tplan_id and is_open=1 and active = 1";
      $tmp_list = $db->fetchRowsIntoMap($sql_build, "id");
      if($tmp_list == null)
        return array();
      $build_id_list = array_keys($tmp_list);   
      if(isset($build_id_list) 
      && count($build_id_list) > 0){
        $build_ids_str = implode(",", $build_id_list);
        $gui->select_build_id = 0;
      }
  }else{
      $build_ids_str = $args->build_id;
      $gui->select_build_id = $args->build_id;
  }
  $gui->select_user_id = $args->user_id;
  $gui->select_mod_id  = $args->mod_id;
  if($args->begin_date != ''){
    $gui->begin_date = $args->begin_date;
  }
  if($args->end_date != ''){
    $gui->end_date = $args->end_date;
  }

if($args->build_id == 0){
  if($args->begin_date != '' && $args->end_date == ''){
    $sql_template = "select id "
                    ." from ".$db->get_table('builds')
                    ." where is_open = 1 and active=1 "
                    ." and begin_date <= '$args->begin_date' "
                    ." and begin_date is not null "
                    ." and end_date is not null ";
    $sql = sprintf($sql_template, $args->begin_date);
    unset($sql_template);
    $build_id_list = $db->fetchColumnsIntoArray($sql,"id");
    unset($sql);
    if(count($build_id_list) > 0){
      $build_id_str = implode(",", $build_id_list);  
    }
  }else if($args->end_date != '' && $args->begin_date == ''){
    $sql_template = "select id "
                    ." from ".$db->get_table('builds')
                    ." where is_open = 1 and active=1 "
                    ." and $begin_date <= '$args->end_date' "
                    ." and begin_date is not null "
                    ." and end_date is not null ";
    $sql = sprintf($sql_template, $args->end_date);
    unset($sql_template);
    $build_id_list = $db->fetchColumnsIntoArray($sql,"id");
    unset($sql);
    if(count($build_id_list) > 0){
      $build_id_str = implode(",", $build_id_list);
    }    
  }else if($args->begin_date != '' 
    &&     $args->end_date != ''){
    $sql_template = "select id "
                    ." from ".$db->get_table('builds')
                    ." where is_open = 1 and active=1 "
                    ." and ('$args->begin_date' >= end_date "
                    ."   or ('$args->end_date' >= begin_date) "
                    ."   or (begin_date <= '$args->begin_date' and end_date >= '$args->end_date') "
                    ."  )"
                    ." and begin_date is not null "
                    ." and end_date is not null ";
    $sql = sprintf($sql_template, $args->end_date);
    unset($sql_template);
    $build_id_list = $db->fetchColumnsIntoArray($sql,"id");
    unset($sql);
    if(count($build_id_list) > 0){
      $build_ids_str = implode(",", $build_id_list);
    }
  }
}
  #module_name
 // die;
  $metrics = $metricsMgr->getStatusTotalsByBuildUAForRenderEx($args->tplan_id,
                                                              $build_ids_str,
                                                              $args->user_id,
                                                              $args->mod_id,
                                                              $args->begin_date,
                                                              $args->end_date,
                                                              array('processClosedBuilds' => $args->show_closed_builds));  
  #get collect by build
  $sql_template = "select build_id,count(distinct(feature_id)) as count "
                  ." from ".$db->get_table("user_assignments")
                  ." where build_id in (%s) "
                  ." group by build_id";
  $sql = sprintf($sql_template, $build_ids_str);
  unset($sql_template);
  $collect_build_total = $db->fetchColumnsIntoMap($sql, "build_id", "count");
  unset($sql);
  $sql_template =  "select build_id,count(distinct(tcversion_id)) as count "
                  ." from ".$db->get_table("executions")
                  ." where build_id in (%s) "
                  ." group by build_id ";
  $sql = sprintf($sql_template, $build_ids_str);
  unset($sql_template);
  $collect_build_exec = $db->fetchColumnsIntoMap($sql, "build_id", "count");
  unset($sql);
  $sql_template = "select build_id, group_concat(status,'-',c) as count"
                  ." from ( "
                  ."   select build_id,status,count(distinct(tcversion_id)) as c "
                  ." from ".$db->get_table('executions')
                  ." where build_id in (%s) "
                  ."group by build_id,status ) as t";
  $sql = sprintf($sql_template, $build_ids_str);
  unset($sql_template);
  $collect_build_status_exec = $db->fetchColumnsIntoMap($sql, "build_id", "count");
  unset($sql);

  /*echo 'tmp_build_total:';
  print_r($collect_build_total);
  echo "<br/>tmp_build_exec:";
  print_r($collect_build_exec);
  echo "<br/>tmp_build_status_exec:";
  print_r($collect_build_status_exec);*/
  #die;
  if($build_ids_str != ''){
    #plan progress 
    $sql_template = "select id,begin_date,end_date "
                    ." from ".$db->get_table("builds")
                    ." where id in (%s) and is_open = 1 and active = 1";
    $sql = sprintf($sql_template, $build_ids_str);
    unset($sql_template);
    $plan_progress_list = $db->fetchRowsIntoMap($sql, "id");
    unset($sql);
  }
}


$matrix = $metrics->info;
// get the progress of the whole build based on executions of single users
$build_statistics = array();
$my_status_list = array();
foreach($matrix as $build_id => $build_execution_map) 
{
  $build_statistics[$build_id]['total']      = 0;
  $build_statistics[$build_id]['not_exec']   = 0;
  $build_statistics[$build_id]['executed']   = 0;
  $build_statistics[$build_id]['pass']       = 0;
  $build_statistics[$build_id]['fail']       = 0;
  $build_statistics[$build_id]['block']      = 0;
  $build_statistics[$build_id]['total_time'] = 0;
  $build_statistics[$build_id]['exec_count'] = 0;

  $my_status_list[$build_id] = array();
  if(isset($collect_build_status_exec)#pass
  && isset($collect_build_status_exec[$build_id])
  && isset($collect_build_status_exec[$build_id])){
    $tmp_list = explode(',', $collect_build_status_exec[$build_id]);
    foreach($tmp_list as $k => $v){
      $tmp_value = explode('-', $v);
      $my_status_list[$build_id]['pass'] = 1;
      if(count($tmp_value) > 0){
        switch($tmp_value[0]){
          case 'p':
            $my_status_list[$build_id]['pass'] = intval($tmp_value[1]);
            break;
          case 'f':
            $my_status_list[$build_id]['fail'] = intval($tmp_value[1]);
            break;
          case 'b':
            $my_status_list[$build_id]['block'] = intval($tmp_value[1]);
            break;
        }
      }
    }
    unset($k, $v);
  }

  foreach ($build_execution_map as $user_id => $mod_list) 
  {
    $build_statistics[$build_id][$user_id]['total']    = 0;
    $build_statistics[$build_id][$user_id]['not_exec'] = 0;
    $build_statistics[$build_id][$user_id]['pass']     = 0;
    $build_statistics[$build_id][$user_id]['fail']     = 0;
    $build_statistics[$build_id][$user_id]['block']    = 0;
    $build_statistics[$build_id][$user_id]['exec_count'] = 0;

    foreach($mod_list as $mod_id => $statistics){
      // total assigned test cases
      $build_statistics[$build_id]['total']    += $statistics['total'];
      $build_statistics[$build_id]['not_exec'] += $statistics['not_run']['count'];
      $build_statistics[$build_id]['pass']     += $statistics['passed']['count'];
      $build_statistics[$build_id]['fail']     += $statistics['failed']['count'];
      $build_statistics[$build_id]['block']    += $statistics['blocked']['count'];
      $build_statistics[$build_id]['exec_count'] += statistics['tc_exec_times'];


      $build_statistics[$build_id][$user_id]['total']    += $statistics['total'];
      $build_statistics[$build_id][$user_id]['not_exec'] += $statistics['not_run']['count'];
      $build_statistics[$build_id][$user_id]['pass']     += $statistics['passed']['count'];
      $build_statistics[$build_id][$user_id]['fail']     += $statistics['failed']['count'];
      $build_statistics[$build_id][$user_id]['block']    += $statistics['blocked']['count'];
      $build_statistics[$build_id][$user_id]['exec_count'] += $statistics['tc_exec_times'];

      
      // total executed testcases
      $executed = $statistics['total'] - $statistics['not_run']['count']; 
      $build_statistics[$build_id]['executed'] += $executed;

      $build_statistics[$build_id]['total_time'] += $statistics['total_time'];  
    }
    unset($mod_id, $statistics);
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

foreach ($matrix as $build_id => $build_execution_map) 
{
  $first_row = $build_set[$build_id]['name'] . " - " . 
               $lblx['progress_absolute'] . " {$build_statistics[$build_id]['progress']}%";

  $progress_diff = 0;
  if(isset($plan_progress_list)
  && isset($plan_progress_list[$build_id])
  && $args->end_date != ''){              
    if(strtotime($args->end_date) > strtotime($plan_progress_list[$build_id]['end_date'])){
      #$args->end_date > $plan_progress_list[$build_id]['end_date']
      $progress_diff =  100;
    }else if(strtotime($args->end_date) < strtotime($plan_progress_list[$build_id]['begin_date'])){
      #$args->end_date < $plan_progress_list[$build_id]['begin_date']  
      $progress_diff = 0;
    }else if(strtotime($args->begin_date) >= strtotime($plan_progress_list[$build_id]['begin_date'])
      &&     strtotime($args->end_date)   <= strtotime($plan_progress_list[$build_id]['end_date'])){
      #$args->end_date - $plan_progress_list[$build_id]['begin_date']  
      $fact_days = ceil((time() - strtotime($plan_progress_list[$build_id]['begin_date']))/86400);
      $plan_days = strtotime($plan_progress_list[$build_id]['end_date'])-strtotime($plan_progress_list[$build_id]['end_date'])/86400;
      $tmp_value = (($fact_days/($plan_days+1))*100);
      $progress_diff = number_format($tmp_value, 2, '.', '');
    }
  }

  #state
  $head_row = array();
  $head_row[] = $first_row;
  $head_row[] = ' 当前汇总';
  $head_row[] = '/';
   if(isset($collect_build_total)
  && isset($collect_build_total[$build_id])){
    $tmp_value = ($collect_build_exec[$build_id]/$collect_build_total[$build_id])*100;
    $head_row[] = number_format($tmp_value, 2, '.', '') - $progress_diff;
  }else{
    $head_row[] = '';  
  }
 # $head_row[] = ;

  if(isset($collect_build_total)
  && isset($collect_build_total[$build_id])){
    $head_row[] = $collect_build_total[$build_id];
  }else{
    $head_row[] = '';  
  }
  
  $head_row[] = '';
  $head_row[] = $build_statistics[$build_id]['not_exec'];
  $tmp_value = ($build_statistics[$build_id]['not_exec']/$build_statistics[$build_id]['total'])*100;
  $head_row[] = number_format($tmp_value, 2, '.', '');
  //$head_row[] = number_format(,2,'.','');
  //pass
  if(isset($my_status_list[$build_id])
  && isset($my_status_list[$build_id]['pass'])){
    $head_row[]   = $my_status_list[$build_id]['pass'];
  }else{
    $head_row[] = '';
  } 
  $tmp_value = ($my_status_list[$build_id]['pass']/$collect_build_total[$build_id])*100;
  $head_row[] = number_format($tmp_value, 2, '.', '');
  if(isset($my_status_list)#fail
  && isset($my_status_list[$build_id])
  && isset($my_status_list[$build_id]['fail'])){
    $head_row[] = $my_status_list[$build_id]['fail'];  
  }else{
    $head_row[] = '';
  }
  $tmp_value = ($my_status_list[$build_id]['fail']/$collect_build_total[$build_id])*100;
  $head_row[] = number_format($tmp_value, 2, '.', '');
  if(isset($my_status_list)#block
  && isset($my_status_list[$build_id])
  && isset($my_status_list[$build_id]['block'])){
    $head_row[] = $my_status_list[$build_id]['block'];  
  }else{
    $head_row[] = '';
  }
  $tmp_value = ($my_status_list[$build_id]['block']/$collect_build_total[$build_id])*100;
  $head_row[] = number_format($tmp_value, 2, '.', '');
   $head_row[] = '';
  /*if($args->begin_date != ''
  && $args->end_date   != ''
  && $args->begin_date == $args->end_date){
    $head_row[] = $collect_build_exec[$build_id];  
  }else{
    $head_row[] = '';
  }*/
  $head_row[] = $collect_build_exec[$build_id];
  $rows[] = $head_row;

  foreach ($build_execution_map as $user_id => $mod_list) 
  {
        $name = "<a href=\"javascript:openAssignmentOverviewWindow(" .
                "{$user_id}, {$build_id}, {$args->tplan_id});\">{$names[$user_id]['first']}{$names[$user_id]['last']}</a>";
        $current_row[] = $name;
        // add username and link it to tcAssignedToUser.php
        $username = $names[$user_id]['first'].$names[$user_id]['last'];
        $username .= ":".$user_id;
        foreach($mod_list as $mod_id => $statistics){
            $current_row = array();
            $current_row[] = $first_row;
            
            $current_row[] = $username;
            $current_row[] = $module_names[$mod_id];

            $current_row[] = ($statistics['progress'] - $progress_diff);
            // total count of testcases assigned to this user on this build
            $current_row[] = $statistics['total'];
            $current_row[] = '/';
            
            // add count and percentage for each possible status
            foreach ($statusCfg as $status => $code) 
            {
              $current_row[] = $statistics[$status]['count'];
              $current_row[] = $statistics[$status]['percentage'];
            }
            
            //$current_row[] = $statistics['progress'];
            if($args->begin_date != ''
            && $args->end_date   != ''
            && $args->begin_date == $args->end_date){
              $current_row[] = $statistics['tc_exec_times'];  
            }else{
              $current_row[] = '';
            }            
            $current_row[] = $statistics['tc_exec_times'];
           // $current_row[] = minutes2HHMMSS($statistics['total_time']);
            
            // add this row to the others
            $rows[] = $current_row;
    }  
    $user_head_row = array();
    $user_head_row[] = $first_row;
    $user_head_row[] = $username;
    $user_head_row[] = '汇总';
    $tmp_value = ($build_statistics[$build_id][$user_id]['exec_count']/$build_statistics[$build_id][$user_id]['total'])*100;
    $user_head_row[] = number_format($tmp_value,2,".",'') - $progress_diff;
    $user_head_row[] = $build_statistics[$build_id][$user_id]['total'];
    $user_head_row[] = '/';
    $user_head_row[] = $build_statistics[$build_id][$user_id]['not_exec'];
    $tmp_value = ($build_statistics[$build_id][$user_id]['not_exec']/$build_statistics[$build_id][$user_id]['total'])*100;
    $user_head_row[] = number_format($tmp_value, 2, '.', '');
    //$head_row[] = number_format(,2,'.','');
    $user_head_row[] = $build_statistics[$build_id][$user_id]['pass'];
    $tmp_value = ($build_statistics[$build_id][$user_id]['pass']/$build_statistics[$build_id][$user_id]['total'])*100;
    $user_head_row[] = number_format($tmp_value, 2, '.', '');
    $user_head_row[] = $build_statistics[$build_id][$user_id]['fail'];
    $tmp_value = ($build_statistics[$build_id][$user_id]['fail']/$build_statistics[$build_id][$user_id]['total'])*100;
    $user_head_row[] = number_format($tmp_value, 2, '.', '');;
    $user_head_row[] = $build_statistics[$build_id][$user_id]['block'];
    $tmp_value = ($build_statistics[$build_id][$user_id]['block']/$build_statistics[$build_id][$user_id]['total'])*100;
    $user_head_row[] = number_format($tmp_value, 2, '.', '');
    if($args->begin_date != ''
      && $args->end_date != ''
      && $args->begin_date == $args->end_date){
      $user_head_row[] = $build_statistics[$build_id][$user_id]['exec_count'];
    }else{
      $user_head_row[] = '';
    }    
    $user_head_row[] = $build_statistics[$build_id][$user_id]['exec_count'];
    $rows[] = $user_head_row;  
  }
}

$columns = getTableHeader($statusCfg);
$smartTable = new tlExtTable($columns, $rows, 'current_plan_case_exec_stat');
$smartTable->title = lang_get('results_by_tester_per_build');
$smartTable->setGroupByColumnName(lang_get('build'));

// enable default sorting by progress column
$smartTable->setSortByColumnName(lang_get('user'));

//define toolbar
$smartTable->showToolbar = true;
$smartTable->toolbarExpandCollapseGroupsButton = true;
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
                   'user_id'                    => array(tlInputParameter::INT_N),
                   'mod_id'                     => array(tlINputParameter::INT_N),
                   'begin_date'                 => array(tlInputParameter::STRING_N),
                   'end_date'                   => array(tlInputParameter::STRING_N),
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
	
	$gui->pageTitle          = lang_get('current_plan_case_exec_stat');
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
	$colCfg[] = array('title_key' => 'user', 
                    'width'     => 30, 
                    'type'      => 'text', 
                    'sortType'  => 'asText',
                    'filter'    => 'string');
  	/**
   	* add
   	* author:jinjiacun
   	* time:2018-01-04 09:31
   	*/
  	$colCfg[] = array('title_key' => 'test_modules',
		    'width'     => 40,
		    'type'      => 'text',
		    'sortType'  => 'asText',
		    'filter'    => 'string');
    $colCfg[] = array('title_key' => 'progress_diff',
        'width'     => 40,
        'type'      => 'text',
        'sortType'  => 'asText',
        'filter'    => 'string');
	$colCfg[] = array('title_key' => 'th_tc_assigned', 
                    'width'     => 30, 
                    'sortType'  => 'asFloat',
                    'filter'    => 'numeric');
  $colCfg[] = array('title_key' => 'th_tc_no_assigned', 
                    'width'     => 30, 
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
                      'width'     => 30, 
		                  'type'      => 'float', 
                      'sortType'  => 'asFloat', 
                      'filter'    => 'numeric');
	}
	
	/*$colCfg[] = array('title_key' => 'progress', 
                    'width'     => 30, 
                    'type'      => 'float',
                    'sortType'  => 'asFloat', 
                    'filter'    => 'numeric');*/
	$colCfg[] = array('title_key' => 'test_cur_day_case_exec_count',
                    'width'     => 30,
                    'sortType'  => 'asFloat',
                    'filter'    => 'numeric');
	//add by zhouzhaoxin 20170227 for add execution testcase number
	$colCfg[] = array('title_key' => 'test_execution_tcase_sum', 
                    'width'     => 30, 
                    'sortType'  => 'asFloat',
                    'filter'    => 'numeric');

 /* $colCfg[] = array('title'      => lang_get('total_time_hhmmss'), 
                    'width'     => 30, 
                    'type'      => 'text',
                    'sortType'  => 'asText', 
                    'filter'    => 'string');*/

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


