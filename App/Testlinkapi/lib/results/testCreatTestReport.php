<?php 
require_once('../testcases/PHPExcel.php');
require_once('../testcases/PHPExcel/Writer/Excel2007.php');
require_once('../testcases/PHPExcel/IOFactory.php');

require('../../config.inc.php');
require_once('common.php');

testlinkInitPage($db,false,false,null);
$curpid=$_SESSION['testprojectID'];
$round_precision = config_get('dashboard_precision');

CreatTestReport($db);

/*
 function: get_UserMsg_by_buildid
 args : id: build id
 returns: $UserMsg for curbuild all username
 rev :add by chenye 160627
 */
function get_UserMsg_by_buildid($bdid, $dbHandler)
{
    $UserMsg ="";
    $sql= "select distinct usr.id, usr.first, usr.last from " . $dbHandler->get_table('user_assignments')." ua " .
            " inner join " . $dbHandler->get_table('users')." usr ON usr.id = ua.user_id " .
        " where ua.build_id = " . $bdid;
    $result = $dbHandler->fetchRowsIntoMap($sql, 'id');
    if (count($result, COUNT_NORMAL) > 0)
    {
        foreach ($result as $user_id => $user_info)
        {
            $UserMsg .= $user_info['first'] . $user_info['last'];
            $UserMsg .= "  ";
        }
    }

    if( $UserMsg == "")
    {
        $UserMsg = "无";
    }
    return  $UserMsg;
}
/*
 function: get_modules_by_buildid
 args :$bdid
 returns:get all modules 
 rev :add by chenye 160627
 */
function get_modules_by_buildid($bdid, $tplan_id, $dbHandler, $cur_date)
{
    $all_moudles = array();
    
    $sql = "SELECT NH_TCSUITE.id as tsuite_id, NH_TCSUITE.name as tsuite_name, NH_TCASE.id AS tcase_id," .
	" TPTCV.tcversion_id, TCV.version,TCV.tc_external_id AS external_id, TPTCV.node_order AS exec_order," . 
	" COALESCE (E. STATUS, 'n') AS exec_status, E.execution_ts as exec_time FROM " .
//	"testplan_tcversions TPTCV " .
    "" . $dbHandler->get_table('testplan_tcversions')." TPTCV " .
//     "JOIN tcversions TCV ON TCV.id = TPTCV.tcversion_id " . 
//     "JOIN nodes_hierarchy NH_TCV ON NH_TCV.id = TPTCV.tcversion_id " .
//     "JOIN nodes_hierarchy NH_TCASE ON NH_TCASE.id = NH_TCV.parent_id " . 
//     "JOIN nodes_hierarchy NH_TCSUITE ON NH_TCSUITE.id = NH_TCASE.parent_id " . 
//     "LEFT OUTER JOIN platforms PLAT ON PLAT.id = TPTCV.platform_id " .
    "JOIN " . $dbHandler->get_table('tcversions')." TCV ON TCV.id = TPTCV.tcversion_id " .
    "JOIN " . $dbHandler->get_table('nodes_hierarchy')." NH_TCV ON NH_TCV.id = TPTCV.tcversion_id " .
    "JOIN " . $dbHandler->get_table('nodes_hierarchy')." NH_TCASE ON NH_TCASE.id = NH_TCV.parent_id " .
    "JOIN " . $dbHandler->get_table('nodes_hierarchy')." NH_TCSUITE ON NH_TCSUITE.id = NH_TCASE.parent_id " .
    "LEFT OUTER JOIN " . $dbHandler->get_table('platforms')." PLAT ON PLAT.id = TPTCV.platform_id " .
    "JOIN (SELECT EE.tcversion_id, EE.testplan_id, EE.platform_id, EE.build_id, MAX(EE.id) AS id " .
//	"FROM executions EE WHERE EE.testplan_id = " . $tplan_id .
	"FROM " . $dbHandler->get_table('executions')." EE WHERE EE.testplan_id = " . $tplan_id .
	" AND EE.build_id = " . $bdid . " GROUP BY " . 
	" EE.tcversion_id, EE.testplan_id, EE.platform_id, EE.build_id ) AS LEBBP " . 
	" ON LEBBP.build_id = TPTCV.build_id " . 
    " AND LEBBP.tcversion_id = TPTCV.tcversion_id " .
    " AND LEBBP.platform_id = TPTCV.platform_id " . 
    " AND LEBBP.build_id = " . $bdid . 
//    " JOIN executions E ON E.id = LEBBP.id " .
    " JOIN " . $dbHandler->get_table('executions')." E ON E.id = LEBBP.id " .
    " AND E.tcversion_id = TPTCV.tcversion_id " .
    " AND E.testplan_id = TPTCV.testplan_id " . 
    " AND E.platform_id = TPTCV.platform_id " . 
    " AND E.build_id = " . $bdid .
    " WHERE TPTCV.build_id = " . $bdid . 
    " UNION " .
	" SELECT NH_TCSUITE.id as tsuite_id, NH_TCSUITE.name as tsuite_name, NH_TCASE.id AS tcase_id, " . 
	" TPTCV.tcversion_id, TCV.version, TCV.tc_external_id AS external_id, TPTCV.node_order AS exec_order, " .
	" COALESCE (E. STATUS, 'n') AS exec_status, E.execution_ts as exec_time FROM " .
// 	" testplan_tcversions TPTCV " . 
// 	" JOIN tcversions TCV ON TCV.id = TPTCV.tcversion_id " . 
// 	" JOIN nodes_hierarchy NH_TCV ON NH_TCV.id = TPTCV.tcversion_id " .
// 	" JOIN nodes_hierarchy NH_TCASE ON NH_TCASE.id = NH_TCV.parent_id " .
//     " JOIN nodes_hierarchy NH_TCSUITE ON NH_TCSUITE.id = NH_TCASE.parent_id " .
// 	" LEFT OUTER JOIN platforms PLAT ON PLAT.id = TPTCV.platform_id " .
	" " . $dbHandler->get_table('testplan_tcversions')." TPTCV " .
	" JOIN " . $dbHandler->get_table('tcversions')." TCV ON TCV.id = TPTCV.tcversion_id " .
	" JOIN " . $dbHandler->get_table('nodes_hierarchy')." NH_TCV ON NH_TCV.id = TPTCV.tcversion_id " .
	" JOIN " . $dbHandler->get_table('nodes_hierarchy')." NH_TCASE ON NH_TCASE.id = NH_TCV.parent_id " .
	" JOIN " . $dbHandler->get_table('nodes_hierarchy')." NH_TCSUITE ON NH_TCSUITE.id = NH_TCASE.parent_id " .
	" LEFT OUTER JOIN " . $dbHandler->get_table('platforms')." PLAT ON PLAT.id = TPTCV.platform_id " .
	" LEFT OUTER JOIN (SELECT EE.tcversion_id, EE.testplan_id, EE.platform_id, EE.build_id, MAX(EE.id) AS id " . 
//	" FROM executions EE " . 
	" FROM " . $dbHandler->get_table('executions')." EE " .
	" WHERE EE.testplan_id = " . $tplan_id . 
	" AND EE.build_id = " .  $bdid .
	" GROUP BY EE.tcversion_id, EE.testplan_id, EE.platform_id, EE.build_id) " . 
	" AS LEBBP ON LEBBP.build_id = TPTCV.build_id " . 
	" AND LEBBP.tcversion_id = TPTCV.tcversion_id " . 
	" AND LEBBP.platform_id = TPTCV.platform_id " . 
	" AND LEBBP.build_id = " . $bdid . 
//	" LEFT OUTER JOIN executions E ON E.tcversion_id = TPTCV.tcversion_id " . 
	" LEFT OUTER JOIN " . $dbHandler->get_table('executions')." E ON E.tcversion_id = TPTCV.tcversion_id " .
	" AND E.testplan_id = TPTCV.testplan_id " . 
	" AND E.platform_id = TPTCV.platform_id " . 
	" AND E.build_id = " . $bdid . 
	" WHERE TPTCV.build_id = " . $bdid .
	" AND E.id IS NULL AND LEBBP.id IS NULL";
    
    $result = $dbHandler->fetchRowsIntoMap($sql, 'tcversion_id');
    if (count($result, COUNT_NORMAL) > 0)
    {
        foreach ($result as $tsuite_id => $row)
        {
            if (!array_key_exists($row['tsuite_id'], $all_moudles))
            {
                //create a new module
                $moudle_array = array();
                $moudle_array['name'] = $row['tsuite_name'];
                $moudle_array['run'] = 0;
                $moudle_array['todayrun'] = 0;
                $moudle_array['not_run'] = 0;
            
                $all_moudles[$row['tsuite_id']] = $moudle_array;
            }
            
            if ($row['exec_status'] == 'p' || $row['exec_status'] == 'f')
            {
                if (substr_replace($row['exec_time'], 0, 10) == $cur_date)
                {
                    $all_moudles[$row['tsuite_id']]['todayrun']++;
                }
                $all_moudles[$row['tsuite_id']]['run']++;
            }
            else
            {
                $all_moudles[$row['tsuite_id']]['not_run']++;
            }
        }
    }
    
    return  $all_moudles;
}

//add by zhouzhaoxin 20160829 for get percent from numbers
function getPercentage($denominator, $numerator, $round_precision)
{
    $percentage = ($numerator > 0) ? (round(($denominator / $numerator) * 100,$round_precision)) : 0;

    return $percentage;
}

/*
 function: CreatTestReport()
 args : 
 returns: creat report
 rev :add by chenye 160627
      modify by zhouzhaoxin 20160830 to add testcase info and change to new template
 */
function CreatTestReport($dbHandler)
{
    $tplan_id = $_REQUEST['tplan_id'];
    $sys_name = $_REQUEST['sys_name'];
    $prj_name = $_REQUEST['prj_name'];
    $cur_date = $_REQUEST['cur_date'];
    $test_stage = $_REQUEST['test_stage'];
    $test_surround = $_REQUEST['test_surround'];
    $version = $_REQUEST['version'];
    $fill_user = $_REQUEST['fill_user'];
    $sp_star = $_REQUEST['sp_star'];
    $sp_end = $_REQUEST['sp_end'];
    $jhcslc = $_REQUEST['jhcslc'];
    $acsp_star = $_REQUEST['acsp_star'];
    $acsp_end = $_REQUEST['acsp_end'];
    $curlcjd = $_REQUEST['curlcjd'];
    $ztjd = $_REQUEST['ztjd'];
    
    $lcsp_star = $_REQUEST['lcsp_star'];
    $lcsp_end = $_REQUEST['lcsp_end'];
    
    $lcacsp_star = $_REQUEST['lcacsp_star'];
    $lcacsp_end = $_REQUEST['lcacsp_end'];
    $cur_buildtxt=$_REQUEST['cur_buildtxt'];
    $sel_buildid=$_REQUEST['sel_buildid'];
    
    
    $cur_testworkrp = $_REQUEST['cur_testworkrp'];
    $cur_testsysrun = $_REQUEST['cur_testsysrun'];
    $work_plan = $_REQUEST['work_plan'];
    $problem_track = $_REQUEST['problem_track'];
    
    $objPHPExcel = new PHPExcel(); //实例化Excel对象
    $objPHPExcel->setActiveSheetIndex(0);//制定sheet页
    
    //***********************设置字体居中*****************************
    $styleArray1 = array(
        'font' => array(
            'bold' => true,
            'color'=>array(
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
    
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);//设置列宽为30
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);//设置列宽为30
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);//设置列宽为30
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);//设置列宽为30
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);//设置列宽为30
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);//设置列宽为30
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);//设置列宽为30
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);//设置列宽为30
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);//设置列宽为30
    
    //line1, show the testreport title,bold middle stype
    $objPHPExcel->getActiveSheet()->mergeCells('A1:I1'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray1);
    $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', lang_get('test_date_report')) ;
    
    //first segment for system,project,build,tester...
    //line2  system/project/date
    $objPHPExcel->getActiveSheet()->mergeCells('A2:B2'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A2:B2')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2', lang_get('test_sys_name')) ;
    $objPHPExcel->getActiveSheet()->mergeCells('C2:D2'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('C2:D2')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C2', $sys_name);  
    
    $objPHPExcel->getActiveSheet()->getStyle('E2')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E2', lang_get('test_proj_name')) ;
    $objPHPExcel->getActiveSheet()->mergeCells('F2:G2'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('F2:G2')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F2', $prj_name);
          
    $objPHPExcel->getActiveSheet()->getStyle('H2')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H2', lang_get('test_date')) ;
    $objPHPExcel->getActiveSheet()->getStyle('I2')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I2', $cur_date);
    
    //line3 test stages/version/writer
    $objPHPExcel->getActiveSheet()->mergeCells('A3:B3'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A3:B3')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A3', lang_get('test_stage')) ;
    $objPHPExcel->getActiveSheet()->mergeCells('C3:D3'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('C3:D3')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C3', $test_stage);
    
    $objPHPExcel->getActiveSheet()->getStyle('E3')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E3', lang_get('test_version')) ;
    $objPHPExcel->getActiveSheet()->mergeCells('F3:G3'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('F3:G3')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F3', $version); 
    
    $objPHPExcel->getActiveSheet()->getStyle('H3')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H3', lang_get('test_fill_user')) ;
    $objPHPExcel->getActiveSheet()->getStyle('I3')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I3', $fill_user);
        
    //line4 planed start/planed end/planed builds
    $objPHPExcel->getActiveSheet()->mergeCells('A4:B4'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A4:B4')->applyFromArray($styleArray2);
    $objPHPExcel->getActiveSheet()->getStyle('A4')->getAlignment()->setWrapText(true);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A4', lang_get('test_plan_stardate')) ;
    $objPHPExcel->getActiveSheet()->mergeCells('C4:D4'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('C4:D4')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C4', $sp_star);
      
    $objPHPExcel->getActiveSheet()->getStyle('E4')->applyFromArray($styleArray2);
    $objPHPExcel->getActiveSheet()->getStyle('E4')->getAlignment()->setWrapText(true);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E4', lang_get('test_plan_enddate')) ; 
    $objPHPExcel->getActiveSheet()->mergeCells('F4:G4'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('F4:G4')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F4', $sp_end);  
    
    $objPHPExcel->getActiveSheet()->getStyle('H4')->applyFromArray($styleArray2);
    $objPHPExcel->getActiveSheet()->getStyle('H4')->getAlignment()->setWrapText(true);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H4', lang_get('test_plan_round')) ;
    $objPHPExcel->getActiveSheet()->getStyle('I4')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I4', $jhcslc);
        
    //line5 actual start/actual end/current build
    $objPHPExcel->getActiveSheet()->mergeCells('A5:B5'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A5:B5')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A5', lang_get('test_real_stardate')) ;
    $objPHPExcel->getActiveSheet()->mergeCells('C5:D5'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('C5:D5')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C5', $acsp_star);
    
    $objPHPExcel->getActiveSheet()->getStyle('E5')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E5', lang_get('test_real_enddate')) ;  
    $objPHPExcel->getActiveSheet()->mergeCells('F5:G5'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('F5:G5')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F5', $acsp_end);
    
    $objPHPExcel->getActiveSheet()->getStyle('H5')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H5', lang_get('test_cur_source_build')) ;
    $objPHPExcel->getActiveSheet()->getStyle('I5')->applyFromArray($styleArray2);
    $objPHPExcel->getActiveSheet()->getStyle('I5')->getAlignment()->setWrapText(true);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I5', $cur_buildtxt) ;
    
    //line6 build progress/planed start date of build/planed end date of build
    $objPHPExcel->getActiveSheet()->mergeCells('A6:B6'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A6:B6')->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('A6:B6')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A6', lang_get('test_cur_build_percent')) ;
    $objPHPExcel->getActiveSheet()->mergeCells('C6:D6'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('C6:D6')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C6', $curlcjd) ;
    
    $objPHPExcel->getActiveSheet()->getStyle('E6')->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('E6')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E6', lang_get('test_cur_round_plan_stardate')) ;    
    $objPHPExcel->getActiveSheet()->mergeCells('F6:G6'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('F6:G6')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F6', $lcsp_star) ;
    
    $objPHPExcel->getActiveSheet()->getStyle('H6')->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('H6')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H6', lang_get('test_cur_round_plan_enddate')) ;
    $objPHPExcel->getActiveSheet()->getStyle('I6')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I6', $lcsp_end) ;
    
    //line7 total progress/actual start date of build/actual end date of build
    $objPHPExcel->getActiveSheet()->mergeCells('A7:B7'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A7:B7')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A7', lang_get('test_total_percent')) ;
    $objPHPExcel->getActiveSheet()->mergeCells('C7:D7'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('C7:D7')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C7', $ztjd) ;
    
    $objPHPExcel->getActiveSheet()->getStyle('E7')->applyFromArray($styleArray2);
    $objPHPExcel->getActiveSheet()->getStyle('E7')->getAlignment()->setWrapText(true);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E7', lang_get('test_cur_round_real_stardate')) ;
    $objPHPExcel->getActiveSheet()->mergeCells('F7:G7'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('F7:G7')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F7', $lcacsp_star) ;
    
    $objPHPExcel->getActiveSheet()->getStyle('H7')->applyFromArray($styleArray2);
    $objPHPExcel->getActiveSheet()->getStyle('H7')->getAlignment()->setWrapText(true);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H7', lang_get('test_cur_round_real_enddate')) ;
    $objPHPExcel->getActiveSheet()->getStyle('I7')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I7', $lcacsp_end) ;
    
    //line8 test enviroment/tester
    $objPHPExcel->getActiveSheet()->mergeCells('A8:B8'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A8:B8')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A8', lang_get('test_environment')) ;
    $objPHPExcel->getActiveSheet()->mergeCells('C8:D8'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('C8:D8')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C8', $test_surround) ;
    
    $objPHPExcel->getActiveSheet()->getStyle('E8')->applyFromArray($styleArray2);
    $objPHPExcel->getActiveSheet()->getStyle('E8')->getAlignment()->setWrapText(true);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E8', lang_get('test_cur_round_tester'));
    $objPHPExcel->getActiveSheet()->mergeCells('F8:I8'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('F8:I8')->applyFromArray($styleArray2);
    $UserMsg = get_UserMsg_by_buildid($sel_buildid, $dbHandler);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F8', $UserMsg);
    
    //line9 defect of daily 
    $objPHPExcel->getActiveSheet()->mergeCells('A9:I9'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A9:I9')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A9', lang_get('test_cur_fault_regression'));
    $objPHPExcel->getActiveSheet()->getStyle('A9')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    
    //line10
    $objPHPExcel->getActiveSheet()->mergeCells('A10:E10'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A10:E10')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A10', lang_get('test_modules'));
    $objPHPExcel->getActiveSheet()->getStyle('A10')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('F10')->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('F10')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F10', lang_get('test_cur_fault_regression_num'));
    $objPHPExcel->getActiveSheet()->getStyle('G10')->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('G10')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G10', lang_get('test_cur_fault_close_num'));
    $objPHPExcel->getActiveSheet()->getStyle('H10')->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('H10')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H10', lang_get('test_restar_open_fault'));
    $objPHPExcel->getActiveSheet()->getStyle('I10')->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('I10')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I10', lang_get('test_total_reqair_fault_num'));
   
    //next line gernate automatically, start from current line
    $curLine=11;
    
    //modify by zhouzhaoxin 20160829 to add modules, first get module info and exec info
    $all_modules = get_modules_by_buildid($sel_buildid, $tplan_id, $dbHandler, $cur_date);
    foreach ($all_modules as $key => $info)
    {
        $dirname = $info['name'];

        $objPHPExcel->getActiveSheet()->mergeCells('A'.$curLine.':E'.$curLine); //合并单元格
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$curLine, $dirname);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine.':E'.$curLine)->applyFromArray($styleArray2);
        $objPHPExcel->getActiveSheet()->mergeCells('C'.$curLine.':E'.$curLine); //合并单元格
        $objPHPExcel->getActiveSheet()->getStyle('C'.$curLine.':E'.$curLine)->applyFromArray($styleArray2);
        $objPHPExcel->getActiveSheet()->getStyle('F'.$curLine)->applyFromArray($styleArray2);
        $objPHPExcel->getActiveSheet()->getStyle('G'.$curLine)->applyFromArray($styleArray2);
        $objPHPExcel->getActiveSheet()->getStyle('H'.$curLine)->applyFromArray($styleArray2);
        $objPHPExcel->getActiveSheet()->getStyle('I'.$curLine)->applyFromArray($styleArray2);
        $curLine++;
    }
    
    //缺陷回归情况合计
    $objPHPExcel->getActiveSheet()->mergeCells('A'.$curLine.':E'.$curLine); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine.':E'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$curLine, lang_get('test_total'));
    
    $objPHPExcel->getActiveSheet()->mergeCells('C'.$curLine.':E'.$curLine); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('C'.$curLine.':E'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->getActiveSheet()->getStyle('F'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->getActiveSheet()->getStyle('G'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->getActiveSheet()->getStyle('H'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->getActiveSheet()->getStyle('I'.$curLine)->applyFromArray($styleArray2);
        
    //用例执行与缺陷汇总情况
    $curLine=$curLine+1;
    $objPHPExcel->getActiveSheet()->mergeCells('A'.$curLine.':I'.$curLine); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine.':I'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$curLine, lang_get('test_execution_fault_collect'));
    
    //缺陷详细信息部分
    $curLine=$curLine+1;
    $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$curLine, lang_get('test_modules'));
    
    $objPHPExcel->getActiveSheet()->getStyle('B'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('B'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$curLine, lang_get('test_plan_tcase_num'));
    
    $objPHPExcel->getActiveSheet()->getStyle('C'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('C'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$curLine, lang_get('test_execution_tcase_num'));
    
    $objPHPExcel->getActiveSheet()->getStyle('D'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('D'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$curLine, lang_get('test_total_execution'));
    
    $objPHPExcel->getActiveSheet()->getStyle('E'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('E'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$curLine, lang_get('test_no_execution_tcase_num'));
    
    $objPHPExcel->getActiveSheet()->getStyle('F'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('F'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$curLine, lang_get('test_modules_percent') . "(%)");
    
    $objPHPExcel->getActiveSheet()->getStyle('G'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('G'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$curLine, lang_get('test_cur_new_fault_num'));
    
    $objPHPExcel->getActiveSheet()->getStyle('H'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('H'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$curLine, lang_get('test_new_serious_fault_num'));
    
    $objPHPExcel->getActiveSheet()->getStyle('I'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('I'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$curLine, lang_get('test_total_fault_num'));
      
    //modify by zhouzhaoxin 20160829 to add statistical information
    $round_precision = config_get('dashboard_precision');
    
    $run_total = 0;
    $today_run_total = 0;
    $not_run_total = 0;
    $tc_total = 0;
    $md_precent_total = 0;
    foreach ($all_modules as $key => $info)
    {
        $curLine=$curLine+1;
        $dirname = $info['name'];
        $run_cnt = $info['run'];
        $today_run_cnt = $info['todayrun'];
        $not_run_cnt = $info['not_run'];
        $tc_cnt = $run_cnt + $not_run_cnt;
        $md_precent_cnt = getPercentage($run_cnt, $tc_cnt, $round_precision);  
        $run_total += $run_cnt;
        $today_run_total += $today_run_cnt;
        $tc_total += $tc_cnt;
        $not_run_total += $not_run_cnt;
        
        $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine)->applyFromArray($styleArray2);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$curLine, $dirname);
        $objPHPExcel->getActiveSheet()->getStyle('B'.$curLine)->applyFromArray($styleArray2);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$curLine, $tc_cnt);
        $objPHPExcel->getActiveSheet()->getStyle('C'.$curLine)->applyFromArray($styleArray2);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$curLine, $today_run_cnt);
        $objPHPExcel->getActiveSheet()->getStyle('D'.$curLine)->applyFromArray($styleArray2);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$curLine, $run_cnt);
        $objPHPExcel->getActiveSheet()->getStyle('E'.$curLine)->applyFromArray($styleArray2);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$curLine, $not_run_cnt);
        $objPHPExcel->getActiveSheet()->getStyle('F'.$curLine)->applyFromArray($styleArray2);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$curLine, $md_precent_cnt);
        $objPHPExcel->getActiveSheet()->getStyle('G'.$curLine)->applyFromArray($styleArray2);
        $objPHPExcel->getActiveSheet()->getStyle('H'.$curLine)->applyFromArray($styleArray2);
        $objPHPExcel->getActiveSheet()->getStyle('I'.$curLine)->applyFromArray($styleArray2);
    }
    
    //共记部分内容
    $md_precent_total = getPercentage($run_total, $tc_total, $round_precision); 
    $curLine=$curLine+1;
    $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$curLine, lang_get('test_total'));
    $objPHPExcel->getActiveSheet()->getStyle('B'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$curLine, $tc_total);
    $objPHPExcel->getActiveSheet()->getStyle('C'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$curLine, $today_run_total);
    $objPHPExcel->getActiveSheet()->getStyle('D'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$curLine, $run_total);
    $objPHPExcel->getActiveSheet()->getStyle('E'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$curLine, $not_run_total);
    $objPHPExcel->getActiveSheet()->getStyle('F'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$curLine, $md_precent_total);
    $objPHPExcel->getActiveSheet()->getStyle('G'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->getActiveSheet()->getStyle('H'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->getActiveSheet()->getStyle('I'.$curLine)->applyFromArray($styleArray2);
    
    //当日工作汇报
    $curLine=$curLine+1;
    $objPHPExcel->getActiveSheet()->mergeCells('A'.$curLine.':I'.$curLine); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine.':I'.$curLine)->applyFromArray($styleArray1);
    $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$curLine, lang_get('test_cur_work_reportA'));
    
    //工作汇报内容部分
    $curLine=$curLine+1;
    $starline=$curLine;
    $endline=$curLine+11;
    $objPHPExcel->getActiveSheet()->mergeCells('A'.$starline.':I'.$endline); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A'.$starline.':I'.$endline)->applyFromArray($styleArray2);
    $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$curLine, $cur_testworkrp);
    
    //主要问题列表
    $curLine=$curLine+12;
    $objPHPExcel->getActiveSheet()->mergeCells('A'.$curLine.':I'.$curLine); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine.':I'.$curLine)->applyFromArray($styleArray1);
    $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$curLine, lang_get('test_cur_problem_list'));
    
    $curLine=$curLine+1;
    $starline=$curLine;
    $endline=$curLine+11;
    $objPHPExcel->getActiveSheet()->mergeCells('A'.$starline.':I'.$endline); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A'.$starline.':I'.$endline)->applyFromArray($styleArray2);
    $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine)->getAlignment()->setWrapText(true);
    
    
    //按列显示主要问题内容信息
   
    //当日系统运行情况
    $curLine=$curLine+12;//根据实际问题数决定具体行数。
    $objPHPExcel->getActiveSheet()->mergeCells('A'.$curLine.':I'.$curLine); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine.':I'.$curLine)->applyFromArray($styleArray1);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$curLine, lang_get('test_cur_sys_run'));
    
    
    $curLine=$curLine+1;
    $starline=$curLine;
    $endline=$curLine+11;
    $objPHPExcel->getActiveSheet()->mergeCells('A'.$starline.':I'.$endline); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A'.$starline.':I'.$endline)->applyFromArray($styleArray2);
    $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$curLine, $cur_testsysrun);//内容待填充
    
    //明日工作计划
    $curLine=$curLine+12;
    $objPHPExcel->getActiveSheet()->mergeCells('A'.$curLine.':I'.$curLine); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine.':I'.$curLine)->applyFromArray($styleArray1);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$curLine, lang_get('test_work_plan'));

    $curLine=$curLine+1;
    $starline=$curLine;
    $endline=$curLine+11;
    $objPHPExcel->getActiveSheet()->mergeCells('A'.$starline.':I'.$endline); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A'.$starline.':I'.$endline)->applyFromArray($styleArray2);
    $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$curLine, $work_plan);//内容待填充
    
    //本版本未解决问题跟踪:
    $curLine=$curLine+12;
    $objPHPExcel->getActiveSheet()->mergeCells('A'.$curLine.':I'.$curLine); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine.':I'.$curLine)->applyFromArray($styleArray1);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$curLine, lang_get('test_build_problem_track'));
    $curLine=$curLine+1;
    $starline=$curLine;
    $endline=$curLine+11;
    $objPHPExcel->getActiveSheet()->mergeCells('A'.$starline.':I'.$endline); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A'.$starline.':I'.$endline)->applyFromArray($styleArray2);
    $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine)->getAlignment()->setWrapText(true);
    //$objPHPExcel->getActiveSheet()-getRowDimension(30)->setRowHeight(20);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$curLine, $problem_track);//内容待填充 
    
    //生成文档
    $curfilename=date("Y-m-d H:i:s")."tcReport.xlsx";
    $objWriter= new PHPExcel_Writer_Excel2007($objPHPExcel);
    //$objWriter->save("D:\\TT\\test.xlsx");
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
