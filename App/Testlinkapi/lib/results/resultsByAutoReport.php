<?php
/**
 * Created by PhpStorm.
 * User: wuyanxiong_ht
 * Date: 2017/12/9
 * Time: 9:00
 */

require_once('../testcases/PHPExcel.php');
require_once('../testcases/PHPExcel/Writer/Excel2007.php');
require_once('../testcases/PHPExcel/IOFactory.php');
require_once('../../resultWord.php');

require('../../config.inc.php');
require_once('common.php');

$curpid=$_SESSION['testprojectID'];
testlinkInitPage($db,false,false,null);
$round_precision = config_get('dashboard_precision');
$metricsMgr = new tlTestPlanMetrics($db);
if(isset($_REQUEST['creatRpoert'])){
    CreatTestReport($db);
}else if(isset($_REQUEST['testRpoert'])){
    export_word($metricsMgr->getAutoBuildStatusForRender($_REQUEST['tplan_id']),$dummy = $metricsMgr->getTestplanTotalsTestcaseForRender($_REQUEST['tplan_id']));
    $tmp_file = "../../Public/word/result.docx";
    $content = file_get_contents($tmp_file);
    header("Cache-Control:no-cache, must-revalidate");
    header("Pragma:no-cache");
    header("Content-type:application/octet-stream");
    header("Accept-Ranges:bytes");
    header("Content-Disposition:attachment;filename=testReport.docx");
    echo $content;
    exit();
}

function CreatTestReport($curpid)
{
    global $metricsMgr;
    $id = $_REQUEST['tplan_id'];
    testlinkInitPage($db,false,false,null);
    $round_precision = config_get('dashboard_precision');
    //$metricsMgr = new tlTestPlanMetrics($db);

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

    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);//设置列宽为30
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);//设置列宽为30
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);//设置列宽为30
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);//设置列宽为30
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);//设置列宽为30
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);//设置列宽为30
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);//设置列宽为30
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);//设置列宽为30
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);//设置列宽为30

    //各个轮次的执行情况
    $objPHPExcel->getActiveSheet()->mergeCells('A1:I1'); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray1);
    $objPHPExcel->getActiveSheet()->getStyle('A1:I1')->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', '当前项目下每个轮次的执行统计');
    //详细数据
    $dummy = $metricsMgr->getautoBuildStatusForRender($id);
    $curLine=2;
    $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$curLine, '序号');

    $objPHPExcel->getActiveSheet()->getStyle('B'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('B'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$curLine, '测试轮次');

    $objPHPExcel->getActiveSheet()->getStyle('C'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('C'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$curLine, '测试范围');

    $objPHPExcel->getActiveSheet()->getStyle('D'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('D'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$curLine, '系统版本');

    $objPHPExcel->getActiveSheet()->getStyle('E'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('E'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$curLine, '计划执行用例数');

    $objPHPExcel->getActiveSheet()->getStyle('F'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('F'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$curLine, '实际执行用例数');

    $objPHPExcel->getActiveSheet()->getStyle('G'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('G'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$curLine, '累计执行用例数');

    $objPHPExcel->getActiveSheet()->getStyle('H'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('H'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$curLine, '测试结果');

    $objPHPExcel->getActiveSheet()->getStyle('I'.$curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('I'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$curLine, '测试结果说明');


    $round_precision = config_get('dashboard_precision');
    $num = 0;
    $build_name = 0;
    $total_assigned = 0;
    $fact = 0;
    $addUP = 0;
    foreach ($dummy->info as $key => $v) {
        $curLine = $curLine + 1;
        $num += 1;
        $build_name = $v['build_name'];
        $total_assigned =$v['total_assigned'];
        $fact =$v['fact'];
        $addUP = $v['addUP'];
        $objPHPExcel->getActiveSheet()->getStyle('A' . $curLine)->applyFromArray($styleArray2);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $curLine, $num);
        $objPHPExcel->getActiveSheet()->getStyle('B' . $curLine)->applyFromArray($styleArray2);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B' . $curLine,  $build_name);
        $objPHPExcel->getActiveSheet()->getStyle('C' . $curLine)->applyFromArray($styleArray2);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C' . $curLine,  '/');
        $objPHPExcel->getActiveSheet()->getStyle('D' . $curLine)->applyFromArray($styleArray2);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D' . $curLine,  '/');
        $objPHPExcel->getActiveSheet()->getStyle('E' . $curLine)->applyFromArray($styleArray2);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E' . $curLine, $total_assigned);
        $objPHPExcel->getActiveSheet()->getStyle('F' . $curLine)->applyFromArray($styleArray2);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F' . $curLine, $fact);
        $objPHPExcel->getActiveSheet()->getStyle('G' . $curLine)->applyFromArray($styleArray2);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G' . $curLine, $addUP);
        $objPHPExcel->getActiveSheet()->getStyle('H' . $curLine)->applyFromArray($styleArray2);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H' . $curLine, '/');
        $objPHPExcel->getActiveSheet()->getStyle('I' . $curLine)->applyFromArray($styleArray2);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I' . $curLine,'/');
    }
    $curLine++;
    //当前项目用例情况汇总
    $objPHPExcel->getActiveSheet()->mergeCells('A'.$curLine.':I'.$curLine); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine)->applyFromArray($styleArray1);
    $objPHPExcel->getActiveSheet()->getStyle('A'.$curLine.':I'.$curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A'.$curLine, '当前项目用例情况汇总');

    //当前项目中所有用例的统计
    $curLine += 1;
    $total = $metricsMgr->getTestplanTotalsTestcaseForRender($id);
    $objPHPExcel->getActiveSheet()->mergeCells('A'.$curLine.':C'.$curLine); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('A' . $curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('A' . $curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $curLine, '本项目共有用力数');

    $objPHPExcel->getActiveSheet()->mergeCells('D'.$curLine.':F'.$curLine); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('D' . $curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('D' . $curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D' . $curLine, '本项目通过用力数');

    $objPHPExcel->getActiveSheet()->mergeCells('G'.$curLine.':I'.$curLine); //合并单元格
    $objPHPExcel->getActiveSheet()->getStyle('G' . $curLine)->getAlignment()->setWrapText(true);
    $objPHPExcel->getActiveSheet()->getStyle('G' . $curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G' . $curLine, '用例通过率');
//    print_r($total);die;
    $curLine += 1;
    $at = $total['at'];
    $pt = $total['pt'];
    $percentage = $total['percentage'];
    $objPHPExcel->getActiveSheet()->getStyle('A' . $curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A' . $curLine,$at);

    $objPHPExcel->getActiveSheet()->getStyle('D' . $curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D' . $curLine,$pt);

    $objPHPExcel->getActiveSheet()->getStyle('G' . $curLine)->applyFromArray($styleArray2);
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G' . $curLine,$percentage.'%');

    //生成文档
    $curfilename=date("Y-m-d H:i:s")."autoReport.xlsx";
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