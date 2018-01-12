<?php
/** 
 * TestLink Open Source Project - http://testlink.sourceforge.net/
 *
 * @filesource  tcCreatedPerUserOnTestProject.php
 * @package     TestLink
 * @copyright   2005,2011 TestLink community 
 * @author      Francisco Mancardi - francisco.mancardi@gmail.com
 * @link        http://www.teamst.org/index.php
 * @since       1.9.6
 * 
 * @internal important development notice
 * Because we use ext-js grid is important/critic that you consider
 * interaction bewteen:
 *                      exttable.class.php
 *                      ext_extensions.js
 *                      inc_ext_table.tpl
 *
 * in order to avoid 'surprises' with filter behaivour
 *  
 * Generates report of test cases created per user within a project. 
 * 
 * @internal revisions
 * @since 1.9.7
 * 20130314 - franciscom - TICKET 5562: Test Cases created per User - toolbar refresh button
 *                                                                   breaks filter behaivour
 */
require_once('../testcases/PHPExcel.php');
require_once('../testcases/PHPExcel/Writer/Excel2007.php');
require_once('../testcases/PHPExcel/IOFactory.php');
require_once("../../config.inc.php");
require_once("common.php");
require_once('users.inc.php');
require_once('displayMgr.php');
require_once('exttable.class.php');

$smarty = new TLSmarty();
$imgSet = $smarty->getImages();
$templateCfg = templateConfiguration();
$args = init_args($db);
$gui = initializeGui($db,$args,$imgSet);
$tpl = $templateCfg->default_template;

$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $tpl);

/**
* initialize Gui
*/
function initializeGui(&$dbHandler,&$args,$images)
{
	global $tlCfg;
  $gui = new stdClass();
  $gui->images = $images;
  $gui->glueChar = config_get('testcase_cfg')->glue_character;
  $gui->tproject_id = $args->tproject_id;
  $gui->tproject_name = $args->tproject_name;
  $gui->warning_msg = '';
  $gui->tableSet = null;
  
  $gui->l18n = init_labels(array('tcversion_indicator'                   => null,
                                 'goto_testspec'                         => null, 
                                 'version'                               => null, 
                                 'testplan'                              => null, 
                                 'assigned_tc_overview'                  => null,
                                 'testcases_count_by_case_exec_per_user' => null,
                                 'design'                                => null, 
                                 'execution'                             => null, 
                                 'execution_history'                     => null,
                                 'testproject'                           => null,
                                 'generated_by_TestLink_on'              => null,
                                 'no_records_found'                      => null, 
                                 'low'                                   => null, 
                                 'medium'                                => null, 
                                 'high'                                  => null));
  
  $gui->pageTitle = sprintf($gui->l18n['testcases_count_by_case_exec_per_user'],
                            $gui->tproject_name);
  $gui->context = $gui->l18n['testproject'] . ': ' . $args->tproject_name;
  switch($args->do_action)
  {
    case 'uinput':
    default:
      initializeGuiForInput($dbHandler,$args,$gui);
    break;
    
    case 'result':
	    if(isset($_REQUEST['query'])){
		    initializeGuiForInput($dbHandler,$args,$gui);
		    initializeGuiForResult($dbHandler,$args,$gui);
	    }else if(isset($_REQUEST['export'])){
		    initializeGuiForInput($dbHandler, $args, $gui);
		    $result_list = array();
		    if(!file_exists($tlCfg->cache_file['stat_test_case_exec'])){
			    initializeGuiForResult($dbHandler, $args, $gui);
		    }
		    $result_list = include_once($tlCfg->cache_file['stat_test_case_exec']);
		    //export $result_list to exec file and output
        CreatTestReport($result_list, $gui->selected_start_date."到".$gui->selected_end_date);
      }
    break;
  }

  return $gui;
}


/**
 *
 */
function initializeGuiForResult(&$dbHandler,$argsObj,&$guiObj)
{
  global $tlCfg;
  $rcfg = config_get('results');

  $map_status_code    = $rcfg['status_code'];
  $map_code_status    = $rcfg['code_status'];
  $map_status_label   = $rcfg['status_label'];
  $map_statuscode_css = array();
  foreach($map_code_status as $code => $status) 
  {
    if (isset($map_status_label[$status])) 
    {
      $label = $map_status_label[$status];
      $map_statuscode_css[$code] = array();
      $map_statuscode_css[$code]['translation'] = lang_get($label);
      $map_statuscode_css[$code]['css_class']   = $map_code_status[$code] . '_text';
    }
  }
  
  $options = array();

  // convert starttime to iso format for database usage
  $dateFormat = config_get('date_format');
  $k2l = array('selected_start_date' => 'startTime',
               'selected_end_date'   => 'endTime');
  foreach($k2l as $in => $opt)
  {
    if (isset($argsObj->$in) 
    && sizeof($argsObj->$in) > 0) 
    {
      $dd = split_localized_date(current($argsObj->$in), $dateFormat);
      if ($dd != null) 
      {
        $options[$opt] = $dd['year'] . "-" . $dd['month'] . "-" . $dd['day'];
      }
    }
  }
  
  $options['startTime'] .= " " . (isset($argsObj->start_Hour) ? $argsObj->start_Hour : "00") . ":00:00";
  $options['endTime']   .= " " . (isset($argsObj->end_Hour) ? $argsObj->end_Hour : "23") . ":59:59";

  $mgr = new testproject($dbHandler);
  $guiObj->searchDone = 1;
  $guiObj->resultSet = $mgr->getCountExectionByUser($argsObj->tproject_id,
                                                    $argsObj->tplan_id,
                                                    $argsObj->user_id,
                                                    $options);

  #add 
  #login belong company
  #author:jinjiacun
  #time:2018-1-10 14:33
  $guiObj->user_company_map = array();
  if($guiObj->resultSet 
  && count($guiObj->resultSet)){
    $user_login_list = array_keys($guiObj->resultSet);
    $template_sql = "select idl.name, iem.ldap_emp_id "
                    ." from ".$dbHandler->get_table('inf_dynamic_list')." as idl   "
                    ." inner join ".$dbHandler->get_table('inf_emp_info')." as iei "
                    ." on idl.id = iei.belong_company "
                    ." inner join ".$dbHandler->get_table('inf_emp_mapping')." as iem "
                    ." on iei.emp_id = iem.emp_id "
                    ." where iem.ldap_emp_id in (%s)"
                    ." and idl.type = 1;";
     $sql = sprintf($template_sql, "'".implode("','", $user_login_list)."'");
     unset($template_sql);
     $guiObj->user_company_map = $dbHandler->fetchColumnsIntoMap($sql, "ldap_emp_id", "name");
     unset($sql);
  }

  $result = array();
  if($guiObj->resultSet
  && count($guiObj->resultSet) > 0){
    $result = $guiObj->resultSet;  
    foreach($result as $k => $v){
      $result[$k]['company'] = $guiObj->user_company_map[$v['login']];
    }
    unset($k, $v);
  }
  
    //save query result to cache
  file_put_contents($tlCfg->cache_file['stat_test_case_exec'], "<?php return ".var_export($result,true).";");
  //stat this time
  if($guiObj->resultSet){
    $guiObj->sum = 0;
    foreach($guiObj->resultSet as $v){
      $guiObj->sum += $v['tcv_total'];
    }
    unset($v);
  }
}

/**
 *
 */
function initializeGuiForInput(&$dbHandler,$argsObj,&$guiObj)
{
  $room = config_get('gui_room');
  $guiObj->tproject_id     = $argsObj->tproject_id;
  $guiObj->tplan_id        = $argsObj->tplan_id;
  $guiObj->str_option_any  = sprintf($room,lang_get('any'));
  $guiObj->str_option_none = sprintf($room,lang_get('nobody'));
  $guiObj->warning_msg = '';
  $guiObj->searchDone  = 0;
  
  $guiObj->users = new stdClass();
  //$guiObj->users->items = getUsersForHtmlOptions($dbHandler, ALL_USERS_FILTER,array(TL_USER_ANYBODY => $guiObj->str_option_any));
  $guiObj->users->items = get_select_tcproject_user_id($argsObj->tproject_id, $dbHandler);
  

  $guiObj->user_id = intval($argsObj->user_id);

  $dateFormat = config_get('date_format');
  $cfg = config_get('reportsCfg');
  $now = time();

  if(is_null($argsObj->selected_start_date))
  {
    $guiObj->selected_start_date = strftime($dateFormat, $now - ($cfg->start_date_offset));
    $guiObj->selected_start_time = $cfg->start_time;
    
    $guiObj->selected_end_date = strftime($dateFormat, $now);
    $guiObj->selected_end_time = null;
  }  
  else
  {
    $guiObj->selected_start_date = $argsObj->selected_start_date[0];
    $guiObj->selected_end_date   = $argsObj->selected_end_date[0];

    // we are using html_select_time (provided by Smarty Templates)
    // then we need to provide selected in a format she likes.
    $guiObj->selected_start_time = sprintf('%02d:00',$argsObj->start_Hour);
    $guiObj->selected_end_time   = sprintf('%02d:59',$argsObj->end_Hour);
  } 


}

/**
 * Gets the arguments used to create the report. 
 * 
 * Some of these arguments are set in the $_REQUEST, and some in $_SESSION. 
 * Having these arguments in hand, the init_args method will use TestLink objects, 
 * such as a Test Project Manager (testproject class) to retrieve other information 
 * that is displayed on the screen (e.g.: project name).
 * 
 * @param $dbHandler handler to TestLink database
 * 
 * @return object of stdClass
 */
function init_args(&$dbHandler)
{
  $args = new stdClass();

  $iParams = array("apikey"              => array(tlInputParameter::STRING_N,32,32),
                   "do_action"           => array(tlInputParameter::STRING_N,6,6),
                   "tproject_id"         => array(tlInputParameter::INT_N),
                   "tplan_id"            => array(tlInputParameter::INT_N),
                   "user_id"             => array(tlInputParameter::INT_N),
                   "selected_start_date" => array(tlInputParameter::ARRAY_STRING_N),
                   "selected_end_date"   => array(tlInputParameter::ARRAY_STRING_N),
                   "start_Hour"          => array(tlInputParameter::INT_N),
                   "end_Hour"            => array(tlInputParameter::INT_N));
  
  $_REQUEST=strings_stripSlashes($_REQUEST);
  R_PARAMS($iParams,$args);
  if(!isset($args->tproject_id)){
    $args->tproject_id = $_SESSION['testprojectID'];
  }
  
  if( !is_null($args->apikey) )
  {
    $args->show_only_active = true;
    $cerbero = new stdClass();
    $cerbero->args = new stdClass();
    $cerbero->args->tproject_id = $args->tproject_id;
    $cerbero->args->tplan_id = null;
    $cerbero->args->getAccessAttr = true;
    $cerbero->method = 'checkRights';
    setUpEnvForRemoteAccess($dbHandler,$args->apikey,$cerbero);  
  }
  else
  {
    testlinkInitPage($dbHandler,false,false,"checkRights");  
  }

  if($args->tproject_id < 0)
  {
    throw new Exception('Test project id can not be empty'); 
  }
  $mgr = new testproject($dbHandler);
  //$info = $mgr->get_by_id($args->tproject_id);
  //$args->tproject_name = $info['name'];
  
  return $args;
}


/**
 * Gets the columns definitions used in the report table.
 * 
 * @return array containing columns and sort information
 */
function getColumnsDefinition()
{
  static $labels;
  if( is_null($labels) )
  {
    $lbl2get = array('user'           => null, 
                     'testsuite'      => null,
                     'testcase'       => null,
                     'importance'     => null,
                     'status'         => null,
                     'version'        => null,
                     'title_created'  => null,
                     'low'            => null,
                     'medium'         => null, 
                     'high'           => null);
    $labels = init_labels($lbl2get);
  }

  $colDef = array();
  $sortByCol = $labels['testsuite'];
  $colDef[] = array('title_key' => '',                         'width' => 80);
  $colDef[] = array('title_key' => 'testcase',                 'width' => 130);
    
  $colDef[] = array('title_key' => 'test_execution_tcase_num', 'width' => 75);

  return array($colDef, $sortByCol);
}

function checkRights(&$db,&$user)
{
  return $user->hasRight($db,'testplan_metrics');
}

//export to excel
function CreatTestReport($data, $title)
{
    testlinkInitPage($db,false,false,null);
    $round_precision = config_get('dashboard_precision');
    $metricsMgr = new tlTestPlanMetrics($db);

    $objPHPExcel = new PHPExcel(); //实例化Excel对象
    $objPHPExcel->setActiveSheetIndex(0);//制定sheet页

    //***********************设置字体居中*****************************
    $styleArray1 = array(
        'font' => array(
            'bold' => true,
            'color' => array(
                'argb' => '00000000',
            ),
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
    );
    //***********************画出单元格边框*****************************
    $styleArray2 = array(
        'borders' => array(
            'allborders' => array(
                //'style' => PHPExcel_Style_Border::BORDER_THICK,//边框是粗的
                'style' => PHPExcel_Style_Border::BORDER_THIN,//细边框
                //'color' => array('argb' => 'FFFF0000'),
            ),
        ),
    );

    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);//设置列宽为30
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);//设置列宽为30
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);//设置列宽为30
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(40);//设置列宽为30

    //各个轮次的执行情况
    $objPHPExcel->getActiveSheet()->mergeCells('A1:D1'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray1);
    $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '按照用户查询用例执行累计次数'."($title)");
    
    $curLine=2;
    $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$curLine, '序号');

    $objPHPExcel->getActiveSheet()->getStyle('B'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('B'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$curLine, '公司');    

    $objPHPExcel->getActiveSheet()->getStyle('C'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('C'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$curLine, '用户名');

    $objPHPExcel->getActiveSheet()->getStyle('D'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('D'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$curLine, '执行次数');

    $round_precision = config_get('dashboard_precision');
    $num     = 0;
    $curLine = 2;
    $sum     = 0;
    foreach ($data as $key => $v) {
        $curLine = $curLine + 1;
        $num += 1;
        $objPHPExcel->getActiveSheet()->getStyle('A' . $curLine)->applyFromArray($styleArray2);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $curLine, $num);
        $objPHPExcel->getActiveSheet()->getStyle('B' . $curLine)->applyFromArray($styleArray2);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B' . $curLine,  $v['company']);
        $objPHPExcel->getActiveSheet()->getStyle('C' . $curLine)->applyFromArray($styleArray2);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C' . $curLine,  $v['first'].$v['last']);
        $objPHPExcel->getActiveSheet()->getStyle('D' . $curLine)->applyFromArray($styleArray2);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D' . $curLine,  $v['tcv_total']);
        $sum += $v['tcv_total'];
    }
    $curLine += 1;
    $num += 1;
    unset($key, $v);
    if(count($data) > 0){
      $objPHPExcel->getActiveSheet()->mergeCells('A'.$curLine.':C'.$curLine); //合并单元格
      $objPHPExcel->getActiveSheet()->getStyle('A' . $curLine)->applyFromArray($styleArray1);
      $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine.':D'.$curLine.'')->applyFromArray($styleArray2);
      $objPHPExcel->getActiveSheet()->getStyle('A' . $curLine)->applyFromArray($styleArray2);
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $curLine, '总计');

      $objPHPExcel->getActiveSheet()->getStyle('D' . $curLine)->applyFromArray($styleArray2);
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D' . $curLine,  $sum); 
    }
    //生成文档
    $curfilename=date("Y-m-d H:i:s")."testCaseExecReport.xlsx";
    $objWriter= new PHPExcel_Writer_Excel2007($objPHPExcel);
    header("Content-Type:application/force-download");
    header("Content-Type:application/octet-stream");
    header("Content-Type:application/download");
    header('Content-Disposition:inline;filename="'.$curfilename.'"');
    header("Content-Transfer-Encoding:binary");
    header("Last-Modified:".gmdate("D,d M Y H:i:s")."GMT");
    header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
    header("Pragma:no-cache");
    $objWriter->save('php://output');
    exit();
}
?>