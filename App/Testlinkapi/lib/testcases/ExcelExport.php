<?php
header("Content-Type: text/html;charset=utf-8");
require_once('ExDataBin.php');
require_once('PHPExcel.php');
require_once('PHPExcel/Writer/Excel2007.php');
require_once("../public/publicDefine.php");
require_once("../public/publicCommand.php");
require_once("../functions/common.php");

class ExExcelContrl
{
    // root node id
    public $root_id = "";
    
    // root node name as system node
    public $root_name = "";           
   
    //export record
    public $export_array = array();    
   
    //dictionary for current testcase
    public $dic_array = array(1 => '', 2 => '', 3 => '', 4 => '', 5 => '');
    
    //database operater handler
    var $dbHandler;
    
    //export info root id
    var $container_id;
   
    /**
     *  construct
     * @author:  zhouzhaoxin
     * @version: 20161117 create
     * **/
    function  __construct($root_id, $root_name, $container_id, $dbHandler)
    {
        $this->dbHandler = $dbHandler;
        $this->root_id = $root_id;
        $this->root_name = $root_name;
        $this->container_id = $container_id;
    }
    
    /**
     *  get test case vim testsuite rec
     * @param: void
     * @return: void
     * @author:  zhouzhaoxin
     * @version: 20161117 create
     * **/
    public function export_tc_to_xls()
    {
       //load tc info to list
       $this->get_sub_tc($this->container_id, 0);
       
       //export to excel
       $this->export_to_excel();
    }
    
    /**
     *  get test case vim testsuite rec
     * @param: void
     * @return: void
     * @author:  zhouzhaoxin
     * @version: 20161117 create
     * **/
    private function get_sub_tc($parent_id, $depth)
    {
        static $first_node = true;
        
        if ($first_node)
        {
            // have no child testsuite
            $tcase_reslut = $this->get_sub_tcase_from_db($parent_id);
            $this->get_tcsuite_parent_from_db($parent_id, $depth);
            if ($depth != 5)
            {
                for ($index = $depth + 1; $index <= 5; $index++)
                {
                    $this->dic_array[$index] = "";
                }
            }
            
            if (count($tcase_reslut, COUNT_NORMAL) > 0)
            {
                $this->create_xls_node($tcase_reslut);
            }
            
            $first_node = false;
        }
        
        $tcsuite_result = $this->get_sub_tcsuite_from_db($parent_id);
        
        if (count($tcsuite_result, COUNT_NORMAL) > 0)
        {
            $depth++;
            
            // max depth is 5
            if ($depth > 5)
            {
                return ;
            }
            
            foreach ($tcsuite_result as $tsuite_id => $tsuite_info)
            {
                // query testcases under tcsuite
                $tcase_reslut = $this->get_sub_tcase_from_db($tsuite_id);
                $this->dic_array[$depth] = $tsuite_info['tsuiteName'];
                if ($depth != 5)
                {
                    for ($index = $depth + 1; $index <= 5; $index++)
                    {
                        $this->dic_array[$index] = "";
                    }
                }
                
                if (count($tcase_reslut, COUNT_NORMAL) > 0)
                {
                    $this->create_xls_node($tcase_reslut);
                }
                
                // process tcsuite recuisive
                $this->get_sub_tc($tsuite_info['tsutieID'], $depth);
            }
        }
    }
    
    /**
     *  get test suite under parent suite with id $parent_id
     * @param: $parent_id
     * @return: result test suite map
     * @author:  zhouzhaoxin
     * @version: 20161117 create
     * **/
    private function create_xls_node($tcase_list)
    {
        foreach ($tcase_list as $tcase_id => $tcase_info)
        {
            // get tc info vim id
            $tv_info = $this->get_tcversion_info($tcase_id);
            
            // get tc version info (if muti version, get the max version)
            $tcversion_id = $tv_info['tcversion'];
            
            // get step info vim tc version id
            $step_result = $this->get_step_info($tcversion_id);
            $enum_list = new publicCommand();//实例化枚举值对象
            
            if (count($step_result, COUNT_NORMAL) > 0)
            {
                foreach ($step_result as $step_num => $step_info)
                {
                    $xls_line = new ExDataBin();
                    $xls_line->set_system($this->root_name);
                    $xls_line->set_first_level($this->dic_array[1]);
                    $xls_line->set_second_level($this->dic_array[2]);
                    $xls_line->set_third_level($this->dic_array[3]);
                    $xls_line->set_fourth_level($this->dic_array[4]);
                    $xls_line->set_fifth_level($this->dic_array[5]);
                    
                    $xls_line->set_name($tv_info['tcname']);
                    $xls_line->set_tc_id($tv_info['tc_id']);
                    $xls_line->set_summary(strip_tags($tv_info['summary']));
                    $xls_line->set_preconditions(strip_tags($tv_info['preconditions']));
                    $xls_line->set_step_number($step_info['step_number']);
                    $xls_line->set_actions(strip_tags($step_info['actions']));
                    $xls_line->set_expected_results(strip_tags($step_info['expected_results']));
                    
                    //parse execution_type
                    $exec_type_id = $tv_info['execution_type'];
                    $exec_type = $enum_list->getEnumName(ENUM_execution_type, $exec_type_id);
                    $xls_line->set_execution_type($exec_type);
                    
                    //parse importance
                    $imp_id = $tv_info['importance'];
                    $imp_type = $enum_list->getEnumName(ENUM_importance, $imp_id);
                    $xls_line->set_importance($imp_type);
                     
                    //parse complexity
                    $complexity_id = $tv_info['complexity'];
                    $complexity = $enum_list->getEnumName(ENUM_complexity, $complexity_id);
                    $xls_line->set_complexity($complexity);
                     
                    $xls_line->set_execute_time($tv_info['estimated_exec_duration']);
                    $xls_line->set_author($tv_info['author']);
                    $xls_line->set_creation_ts($tv_info['creation_ts']);
                    $xls_line->set_reviewer($tv_info['reviewer']);
                    
                    //parse review state
                    $review_status_id = $tv_info['reviewed_status'];
                    $review_satus = $enum_list->getEnumName(ENUM_reviewed_status, $review_status_id);
                    $xls_line->set_reviewed_status($review_satus);
                    $xls_line->set_bpm_id($tv_info['bpm_id']);
                    
                    //parse keywords
                    $curKw=$this->get_key_words_with_tc($tv_info['parent_id']);
                    $xls_line->set_keywords($curKw);
                    
                    // add data to array
                    $this->export_array[] = $xls_line;
                }
            }
            else 
            {
                // tcversion have no steps
                $xls_line = new ExDataBin();
                $xls_line->set_system($this->root_name);
                $xls_line->set_first_level($this->dic_array[1]);
                $xls_line->set_second_level($this->dic_array[2]);
                $xls_line->set_third_level($this->dic_array[3]);
                $xls_line->set_fourth_level($this->dic_array[4]);
                $xls_line->set_fifth_level($this->dic_array[5]);
                
                $xls_line->set_name($tv_info['tcname']);
                $xls_line->set_tc_id($tv_info['tc_id']);
                $xls_line->set_summary(strip_tags($tv_info['summary']));
                $xls_line->set_preconditions(strip_tags($tv_info['preconditions']));
                $xls_line->set_step_number("");
                $xls_line->set_actions("");
                $xls_line->set_expected_results("");
                
                //parse execution_type
                $exec_type_id = $tv_info['execution_type'];
                $exec_type = $enum_list->getEnumName(ENUM_execution_type, $exec_type_id);
                $xls_line->set_execution_type($exec_type);
                
                //parse importance
                $imp_id = $tv_info['importance'];
                $imp_type = $enum_list->getEnumName(ENUM_importance, $imp_id);
                $xls_line->set_importance($imp_type);
                 
                //parse complexity
                $complexity_id = $tv_info['complexity'];
                $complexity = $enum_list->getEnumName(ENUM_complexity, $complexity_id);
                $xls_line->set_complexity($complexity);
                 
                $xls_line->set_execute_time($tv_info['estimated_exec_duration']);
                $xls_line->set_author($tv_info['author']);
                $xls_line->set_creation_ts($tv_info['creation_ts']);
                $xls_line->set_reviewer($tv_info['reviewer']);
                
                //parse review state
                $review_status_id = $tv_info['reviewed_status'];
                $review_satus = $enum_list->getEnumName(ENUM_reviewed_status, $review_status_id);
                $xls_line->set_reviewed_status($review_satus);
                $xls_line->set_bpm_id($tv_info['bpm_id']);
                
                //parse keywords
                $curKw=$this->get_key_words_with_tc($tv_info['parent_id']);
                $xls_line->set_keywords($curKw);
                
                // add data to array
                $this->export_array[] = $xls_line;
            }
        }
    }
    
    /**
     *  get test case keywords info by testcase version id
     * @param: $id => testcase version id
     * @return: result test case map
     * @author:  zhouzhaoxin
     * @version: 20161117 create
     * **/
    private  function get_key_words_with_tc($id)
    {
        $keywords = '';
        
        $sql = "select nh.id, kw.keyword from " . 
                $this->dbHandler->get_table('keywords') . " kw " .
                "inner join " . $this->dbHandler->get_table('testcase_keywords') . " tk on kw.id = tk.keyword_id " .
                "inner join " . $this->dbHandler->get_table('nodes_hierarchy') . " nh on tk.testcase_id = nh.id " .
                "where nh.id = '" . $id . "'";
        $result = $this->dbHandler->fetchRowsIntoMap($sql, 'id');
        
        if (count($result, COUNT_NORMAL) > 0)
        {
            foreach ($result as $id => $keword_info)
            {
                $keywords .= $keword_info['keyword'] . ';';
            }
        }
         
        return $keywords;
    }
    
    /**
     *  get test case step info vim tc version id
     * @param: $id => tcversion id
     * @return: result test case step info
     * @author:  zhouzhaoxin
     * @version: 20161117 create
     * **/
    private function get_step_info($id)
    {
        $sql = "select ts.step_number, ts.actions, ts.expected_results " .
               "from " . $this->dbHandler->get_table('nodes_hierarchy') . " nh " .
               "inner join " . $this->dbHandler->get_table('tcsteps') . " ts on ts.id = nh.id " .
               "where nh.node_type_id = 9 and nh.parent_id = '" . $id . "'";
        
        $result = $this->dbHandler->fetchRowsIntoMap($sql, 'step_number');
        return $result;
    }
    
    /**
     *  get test case version info vim testcase id
     * @param: $id => tcase id
     * @return: test case info(max version)
     * @author:  zhouzhaoxin
     * @version: 20161117 create
     * **/
    private function get_tcversion_info($id)
    {
        $sql = "select distinct ncon.name AS tcname, " . 
               "tcmsg.tc_id, tcmsg.version, " . 
               "tcmsg.summary, tcmsg.preconditions, " .
               "tcmsg.execution_type, tcmsg.importance, " . 
               "tcmsg.complexity, tcmsg.estimated_exec_duration, " .
               "author.login as author, tcmsg.creation_ts, tcmsg.reviewed_status, " .
               "reviewer.login as reviewer, tcmsg.bpm_id, " .
               "tcmsg.id as tcversion, nct.parent_id " .
               " from " . $this->dbHandler->get_table('nodes_hierarchy') . " nct " .
               "inner join " . $this->dbHandler->get_table('tcversions') . " tcmsg on tcmsg.id = nct.id " .
               "inner join " . $this->dbHandler->get_table('nodes_hierarchy')." ncon on nct.parent_id = ncon.id " .
               "left join " . $this->dbHandler->get_table('users') . " author on tcmsg.author_id = author.id " .
               "left join " . $this->dbHandler->get_table('users')." reviewer ON tcmsg.reviewer_id = reviewer.id " . 
               "where nct.parent_id = '" . $id . "' and tcmsg.version = (select max(version) from " . 
               $this->dbHandler->get_table('tcversions') . 
               " sub where tcmsg.tc_external_id = sub.tc_external_id)";
        
        $result = $this->dbHandler->fetchFirstRow($sql);  
        return $result;
    }
    
        
    /**
     *  get test case under parent suite with id $parent_id
     * @param: $parent_id => test suite id
     * @return: result test case map
     * @author:  zhouzhaoxin
     * @version: 20161117 create
     * **/
    private function get_sub_tcase_from_db($parent_id)
    {
        $sql = "select distinct nct.name, nct.node_type_id, nct.parent_id, " .
               "nct.id from " . 
               $this->dbHandler->get_table('nodes_hierarchy') . " nct " .
               "where nct.node_type_id = 3 and nct.parent_id = '" . $parent_id . "'";
        
        $result = $this->dbHandler->fetchRowsIntoMap($sql, 'id');
        return $result;
    }
    
    /**
     *  get test suite under parent suite with id $parent_id
     * @param: $parent_id => tsuite id
     * @return: result test suite map
     * @author:  zhouzhaoxin
     * @version: 20161117 create
     * **/
    private function get_sub_tcsuite_from_db($parent_id)
    {
        $sql = "select nh.name AS tsuiteName, " .
            "nh.id AS tsutieID, " .
            "nh.parent_id AS tsutiePID " .
            "from " . $this->dbHandler->get_table('nodes_hierarchy') .
            " nh where nh.parent_id = '" . $parent_id .
            "' and nh.node_type_id = 2";
    
        $result = $this->dbHandler->fetchRowsIntoMap($sql, 'tsutieID');
        return $result;
    }
    
    /**
     *  get test suite list for container id
     * @param: $id => container id  $depth suite depth
     * @return: 
     * @author:  zhouzhaoxin
     * @version: 20170721 create for import child testsuite
     * **/
    private function get_tcsuite_parent_from_db($id, &$depth)
    {
        $current_id = $id;
        $current_list = array();
        $index = 0;
        
        while($current_id != $this->root_id)
        {
            $sql = "select nh.name AS tsuiteName, " .
                "nh.id AS tsutieID, " .
                "nh.parent_id AS tsutiePID " .
                "from " . $this->dbHandler->get_table('nodes_hierarchy') .
                " nh where nh.id = '" . $current_id .
                "' and nh.node_type_id = 2";
            $result = $this->dbHandler->fetchRowsIntoMap($sql, 'tsutieID');
            if (count($result) <= 0)
            {
                break;
            }
            else 
            {
                $index++;
                $current_list[$index] = $result[$current_id]['tsuiteName'];
                $current_id = $result[$current_id]['tsutiePID'];
            }
        }
        
        $depth = $index;
        
        $idx = 1;
        while ($index > 0)
        {
            $this->dic_array[$idx] = $current_list[$index];
            $idx++;
            $index--;
        } 
    }
   
    /**
     *  write data to excel
     * @param: void
     * @return: void
     * @author:  zhouzhaoxin
     * @version: 20161117 create
     * **/
    public function export_to_excel(){
       //excel line index
       $index = 1;            
       $obj_excel = new PHPExcel();
       $obj_excel->setActiveSheetIndex(0);
       $obj_excel->getActiveSheet()->setTitle($this->root_name);		 
			 
	   //set line width
	   $obj_excel->getActiveSheet()->getColumnDimension(idx_col_system)->setWidth(20);
	   $obj_excel->getActiveSheet()->getColumnDimension(idx_col_firstlevel)->setWidth(20);
	   $obj_excel->getActiveSheet()->getColumnDimension(idx_col_secondlevel)->setWidth(20);
	   $obj_excel->getActiveSheet()->getColumnDimension(idx_col_thirdlevel)->setWidth(20);
	   $obj_excel->getActiveSheet()->getColumnDimension(idx_col_fourthlevel)->setWidth(20);
	   $obj_excel->getActiveSheet()->getColumnDimension(idx_col_fifthlevel)->setWidth(20);
	   $obj_excel->getActiveSheet()->getColumnDimension(idx_col_tc_id)->setWidth(20);
	   $obj_excel->getActiveSheet()->getColumnDimension(idx_col_testcase_name)->setWidth(20);
	   $obj_excel->getActiveSheet()->getColumnDimension(idx_col_testcase_summary)->setWidth(20);
	   $obj_excel->getActiveSheet()->getColumnDimension(idx_col_testcase_preconditions)->setWidth(20);
	   $obj_excel->getActiveSheet()->getColumnDimension(idx_col_testcase_stepnum)->setWidth(20);
	   $obj_excel->getActiveSheet()->getColumnDimension(idx_col_testcase_stepaction)->setWidth(20);
	   $obj_excel->getActiveSheet()->getColumnDimension(idx_col_testcase_expectedresults)->setWidth(20);
	   $obj_excel->getActiveSheet()->getColumnDimension(idx_col_testcase_execution_type)->setWidth(20);
	   $obj_excel->getActiveSheet()->getColumnDimension(idx_col_testcase_importance)->setWidth(20);
	   $obj_excel->getActiveSheet()->getColumnDimension(idx_col_testcase_complexity)->setWidth(20);
	   $obj_excel->getActiveSheet()->getColumnDimension(idx_col_testcase_extimated_exec_duration)->setWidth(20);
	   $obj_excel->getActiveSheet()->getColumnDimension(idx_col_testcase_designer)->setWidth(20);
	   $obj_excel->getActiveSheet()->getColumnDimension(idx_col_testcase_creation_ts)->setWidth(20);
	   $obj_excel->getActiveSheet()->getColumnDimension(idx_col_testcase_reviewed_status)->setWidth(20);
	   $obj_excel->getActiveSheet()->getColumnDimension(idx_col_testcase_reviewer_id)->setWidth(20);
	   $obj_excel->getActiveSheet()->getColumnDimension(idx_col_testcase_bpm_id)->setWidth(20);
	   $obj_excel->getActiveSheet()->getColumnDimension(idx_col_testcase_keywords)->setWidth(20);
         
       //add column title
       $obj_excel->setActiveSheetIndex(0)
    	   ->setCellValue(idx_col_system.'1', lang_get('export_system'))
    	   ->setCellValue(idx_col_firstlevel.'1', lang_get('export_firstlevel'))
    	   ->setCellValue(idx_col_secondlevel.'1', lang_get('export_secondlevel'))
    	   ->setCellValue(idx_col_thirdlevel.'1',lang_get('export_thirdlevel'))
    	   ->setCellValue(idx_col_fourthlevel.'1',lang_get('export_fourthlevel'))
    	   ->setCellValue(idx_col_fifthlevel.'1',lang_get('export_fifthlevel'))
    	   ->setCellValue(idx_col_tc_id.'1', lang_get('export_tc_id'))
    	   ->setCellValue(idx_col_testcase_name.'1', lang_get('export_testcase_name'))
    	   ->setCellValue(idx_col_testcase_summary.'1', lang_get('export_testcase_summary'))
    	   ->setCellValue(idx_col_testcase_preconditions.'1', lang_get('export_testcase_preconditions'))
    	   ->setCellValue(idx_col_testcase_stepnum.'1',lang_get('export_testcase_stepnum'))
    	   ->setCellValue(idx_col_testcase_stepaction.'1', lang_get('export_testcase_stepaction'))
    	   ->setCellValue(idx_col_testcase_expectedresults.'1',lang_get('export_testcase_expectedresults'))
    	   ->setCellValue(idx_col_testcase_execution_type.'1', lang_get('export_testcase_execution_type'))
    	   ->setCellValue(idx_col_testcase_importance.'1', lang_get('export_testcase_importance'))
    	   ->setCellValue(idx_col_testcase_complexity.'1', lang_get('export_testcase_complexity'))
    	   ->setCellValue(idx_col_testcase_extimated_exec_duration.'1', lang_get('export_testcase_extimated_exec_duration'))
    	   ->setCellValue(idx_col_testcase_designer.'1',lang_get('export_testcase_designer'))
    	   ->setCellValue(idx_col_testcase_creation_ts.'1', lang_get('export_testcase_creation_ts'))
    	   ->setCellValue(idx_col_testcase_reviewed_status.'1',lang_get('export_testcase_reviewed_status'))
    	   ->setCellValue(idx_col_testcase_reviewer_id.'1', lang_get('export_testcase_reviewer_id'))
    	   ->setCellValue(idx_col_testcase_bpm_id.'1', lang_get('export_testcase_bpm_id'))
    	   ->setCellValue(idx_col_testcase_keywords.'1', lang_get('export_testcase_keywords'));

        //add data to excel one by one
        foreach ($this->export_array as $ex_line){
            $index++;
            $obj_excel->getActiveSheet(0)->setCellValue(idx_col_system. ($index), $ex_line->get_system());
            $obj_excel->getActiveSheet(0)->setCellValue(idx_col_firstlevel . ($index), $ex_line->get_first_level());
            $obj_excel->getActiveSheet(0)->setCellValue(idx_col_secondlevel . ($index), $ex_line->get_second_level());
            $obj_excel->getActiveSheet(0)->setCellValue(idx_col_thirdlevel . ($index), $ex_line->get_third_level());
            $obj_excel->getActiveSheet(0)->setCellValue(idx_col_fourthlevel . ($index), $ex_line->get_fourth_level());
            $obj_excel->getActiveSheet(0)->setCellValue(idx_col_fifthlevel . ($index), $ex_line->get_fifth_level());
            $obj_excel->getActiveSheet(0)->setCellValue(idx_col_tc_id . ($index), $ex_line->get_tc_id());
            $obj_excel->getActiveSheet(0)->setCellValue(idx_col_testcase_name . ($index), $ex_line->get_name());
            $obj_excel->getActiveSheet(0)->setCellValue(idx_col_testcase_summary . ($index), $ex_line->get_summary());
            $obj_excel->getActiveSheet(0)->setCellValue(idx_col_testcase_preconditions . ($index), $ex_line->get_preconditions());
            $obj_excel->getActiveSheet(0)->setCellValue(idx_col_testcase_stepnum. ($index), $ex_line->get_step_number());
            $obj_excel->getActiveSheet(0)->setCellValue(idx_col_testcase_stepaction. ($index), $ex_line->get_actions());
            $obj_excel->getActiveSheet(0)->setCellValue(idx_col_testcase_expectedresults. ($index), $ex_line->get_expected_results());
            $obj_excel->getActiveSheet(0)->setCellValue(idx_col_testcase_execution_type. ($index), $ex_line->get_execution_type());
            $obj_excel->getActiveSheet(0)->setCellValue(idx_col_testcase_importance. ($index), $ex_line->get_importance());
            $obj_excel->getActiveSheet(0)->setCellValue(idx_col_testcase_complexity. ($index), $ex_line->get_complexity());
            $obj_excel->getActiveSheet(0)->setCellValue(idx_col_testcase_extimated_exec_duration. ($index), $ex_line->get_execute_time());
            $obj_excel->getActiveSheet(0)->setCellValue(idx_col_testcase_designer. ($index), $ex_line->get_author());
            $obj_excel->getActiveSheet(0)->setCellValue(idx_col_testcase_creation_ts. ($index), $ex_line->get_creation_ts());
            $obj_excel->getActiveSheet(0)->setCellValue(idx_col_testcase_reviewed_status. ($index), $ex_line->get_reviewed_status());
            $obj_excel->getActiveSheet(0)->setCellValue(idx_col_testcase_reviewer_id. ($index), $ex_line->get_reviewer());
            $obj_excel->getActiveSheet(0)->setCellValue(idx_col_testcase_bpm_id. ($index), $ex_line->get_bpm_id());
            $obj_excel->getActiveSheet(0)->setCellValue(idx_col_testcase_keywords. ($index), $ex_line->get_keywords());
        }
       
        $filePath="/var/www/html/testlink/lib/testcases/TT";
	    $curfilename=date("Y-m-d H:i:s").".xlsx";

        $excel_writer= new PHPExcel_Writer_Excel2007($obj_excel);
	    header("Content-Type:application/force-download");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:inline;filename="'.$curfilename.'"');
        header("Content-Transfer-Encoding:binary");
        header("Last-Modified:".gmdate("D,d M Y H:i:s")."GMT");
        header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
        header("Pragma:no-cache");
        $excel_writer->save('php://output');	
   }
}

