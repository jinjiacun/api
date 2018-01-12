<?php
/*
 * filename ExcelImportClass.php
 * useage   to import testcase from excel
 * Creater  liuchunping
 * history  2016/5/30
 *          2016/11/21 zhouzhaoxin restructure
 * */

require_once('ExDataBin.php');
require_once('PHPExcel.php');
require_once('PHPExcel/Reader/Excel2007.php');
require_once('PHPExcel/Reader/Excel5.php');
require_once('../public/publicCommand.php');

class DicNode
{
    // dic node index
    private $id;
    
    // dic node nodes_hirerachy id, if null, mean need to create
    private $nh_id;
    
    // dic name
    private $name;
    
    // dic parent , DicNode id
    private $parent;
    
    // tc step list
    private $tc_steps;
    
    /**
     *  construct
     * @author:  zhouzhaoxin
     * @param :  $id => node inside id
     * @param :  $name => node name
     * @version: 20161117 restructure
     * **/
    function  __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
        
        $this->tc_steps = array();
        $this->parent = null;
        $this->nh_id = null;
    }
    
    /**
     * get node inside id
     * @param :  void
     * @return:  inside id
     * @author:  zhouzhaoxin
     * @version: 20161122 created by zhouzhaoxin
     * **/
    function get_id()
    {
        return $this->id;
    }
    
    /**
     * get node name
     * @param :  void
     * @return:  node name
     * @author:  zhouzhaoxin
     * @version: 20161122 created by zhouzhaoxin
     * **/
    function get_name()
    {
        return $this->name;
    }
    
    /**
     * set nodes_hirerachy id
     * @param :  $nh_id => nodes_hirerachy id
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161122 created by zhouzhaoxin
     * **/
    function set_nh_id($nh_id)
    {
        $this->nh_id = $nh_id;
    }
    
    /**
     * get nodes_hirerachy id
     * @param :  void
     * @return:  nodes_hirerachy id
     * @author:  zhouzhaoxin
     * @version: 20161122 created by zhouzhaoxin
     * **/
    function get_nh_id()
    {
        return $this->nh_id;
    }
    
    /**
     * set parent inside id
     * @param :  $parent => parent inside id
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161122 created by zhouzhaoxin
     * **/
    function set_parent($parent)
    {
        $this->parent = $parent;
    }
    
    /**
     * get parent inside id
     * @param :  void
     * @return:  parent inside id
     * @author:  zhouzhaoxin
     * @version: 20161122 created by zhouzhaoxin
     * **/
    function get_parent()
    {
        return $this->parent;
    }
    
    /**
     * add a tc step
     * @param :  $parent => parent inside id
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161122 created by zhouzhaoxin
     * **/
    function add_tc_step($step)
    {
        $this->tc_steps[] = $step;
    }
    
    /**
     * add a tc step
     * @param :  void
     * @return:  tc_step array
     * @author:  zhouzhaoxin
     * @version: 20161122 created by zhouzhaoxin
     * **/
    function get_tc_steps()
    {
        return $this->tc_steps;
    }
}

class ExcelImportClass
{
    //database operater handler
    private $db_handler;
    
    //testcase import type
    private $import_type;
     
    // import result list
    private $result_message = array();
    
    // import result index
    private $result_index;
    
    // testcase manager
    private $tcase_manager;
    
    // testsuite managet
    private $tsuite_manager;
    
    // testproject id
    private $project_id;
    
    // existed directory
    // 1 - 5 is list of directory id and name map
    private $existed_directory_array;
    
    // testcase directory
    // 1 - 5 is list of DicNode
    private $directory_array;
    
    // directory inside index
    private $directory_index;
    
    // testcase node data bean list
    // key : inside id
    // value : ExDataBin
    private $tc_step_data_list;
    
    // testcase step inside id
    private $tc_step_index;
    
    // keyword map <keyword => id>
    private $keywords_map;
    
    // user map <login => id>
    private $users_map;

    
    /**
     *  construct
     * @author:  zhouzhaoxin
     * @param :  $db_handler => data base operate handler
     * @version: 20161117 restructure
     * **/
    function  __construct($db_handler)
    {
        $this->db_handler = $db_handler;
        $this->tcase_manager = new testcase($db_handler);
        $this->tsuite_manager = new testsuite($db_handler);
        
        $this->project_id = $_SESSION['testprojectID'];
        
        $this->directory_array = array();
        $this->directory_index = array();
        $this->directory_array[1] = array();
        $this->directory_index[1] = 0;
        $this->directory_array[2] = array();
        $this->directory_index[2] = 0;
        $this->directory_array[3] = array();
        $this->directory_index[3] = 0;
        $this->directory_array[4] = array();
        $this->directory_index[4] = 0;
        $this->directory_array[5] = array();
        $this->directory_index[5] = 0;
        
        $this->existed_directory_array[1] = array();
        $this->existed_directory_array[2] = array();
        $this->existed_directory_array[3] = array();
        $this->existed_directory_array[4] = array();
        $this->existed_directory_array[5] = array();
        
        $this->tc_step_data_list = array();
        $this->tc_step_index = 0;
        
        $this->result_message = array();
        $this->result_index = 0;
        
        $this->keywords_map = array();
        
        $this->users_map = array();
        $this->init_users_map();
    }
    
    /**
     * init keywords map
     * @param :  void
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161117 created by zhouzhaoxin
     * **/
    private function init_keywords_map()
    {
        $debugMsg='Class:' .__CLASS__ . ' - Method:' . __FUNCTION__ . ' :: ';

        $sql = "/* {$debugMsg} */ " .
            " select id, keyword from " .
            $this->db_handler->get_table('keywords') .
            " where testproject_id = " . $this->project_id;
        
        $result = $this->db_handler->fetchRowsIntoMap($sql, 'id');
        if (count($result, COUNT_NORMAL) < 1)
        {
            return ;
        }
        
        foreach ($result as $id => $record)
        {
            $this->keywords_map[$record['keyword']] = $record['id'];
        }
    }
    
    /**
     * init keywords map
     * @param :  void
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161117 created by zhouzhaoxin
     * **/
    private function init_users_map()
    {
        $debugMsg='Class:' .__CLASS__ . ' - Method:' . __FUNCTION__ . ' :: ';
        
        $sql = "/* {$debugMsg} */ " .
        " select id, login from " .
        $this->db_handler->get_table('users');
        
        $result = $this->db_handler->fetchRowsIntoMap($sql, 'id');
        if (count($result, COUNT_NORMAL) < 1)
        {
            return ;
        }
        
        foreach ($result as $id => $record)
        {
            $this->users_map[$record['login']] = $record['id'];
        }
    }
    
    /**
     * create php excel object
     * @param :  $xls_file_name => excel file
     * @return:  php opearte handler
     * @author:  zhouzhaoxin
     * @version: create by liuchunpig, 20161122 restructure by zhouzhaoxin
     * **/
    public function create_php_excel($xls_file_name)
    {
        $php_excel_reader = new PHPExcel_Reader_Excel2007();
    
        if (!$php_excel_reader->canRead($xls_file_name))
        {
            $php_excel_reader = new PHPExcel_Reader_Excel5();
            if (!$php_excel_reader->canRead($xls_file_name))
            {
                die('Excel not existed');
                return ;
            }
        }
    
        $php_excel = $php_excel_reader->load($xls_file_name);
        return $php_excel;
    }
    
    /**
     * import testcase from excel
     * @param :  $php_excel => php excel info and operate node
     * @param :  $import_type => import type
     * @return:  result message
     * @author:  zhouzhaoxin
     * @version: create by liuchunpig, 20161117 restructure by zhouzhaoxin
     * **/
    public function import_testcase_from_excel($php_excel, $import_type, $new_keyword_list)
    {
        $this->import_type = $import_type;
        
        // create new keyword first
        if (count($new_keyword_list, COUNT_NORMAL) > 0)
        {
            foreach ($new_keyword_list as $id => $new_keyword)
            {
                $sql = "insert into " . $this->db_handler->get_table('keywords') . " (keyword, testproject_id, notes)" .
                    " values ('" . $new_keyword . "' , '" . $this->project_id . "' , '')";
                $this->db_handler->exec_query($sql);
            }
        }
        
        $this->init_keywords_map();
        
        // parse testcase step info into node array
        $this->get_tc_step_list($php_excel);
        
        // get the dic tree info from node list
        $this->get_existed_directory_tree();
        
        // generate directionary tree ,include dic and testcases under dic
        $this->parse_directory_tree();
        
        // write data to db
        $this->write_data_to_db();
        
        return $this->result_message;
    }
    
    /**
     * get testcase step list
     * @param :  $php_excel => php excel info and operate node
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161117 created by zhouzhaoxin
     * **/
    private function get_tc_step_list($php_excel)
    {
        $sheet_count = $php_excel->getSheetCount();
        for ($sheet_index = 0; $sheet_index < $sheet_count; $sheet_index++)
        {
            $php_excel->setActiveSheetIndex($sheet_index);
            $sheet_row_count = $php_excel->getActiveSheet()->getHighestRow();
        
            // first row is title , no need to import
            for ($row = FIRST_DATA_ROW; $row <= $sheet_row_count; $row++)
            {
                $tc_step_node = $this->parse_row_from_excel($php_excel, $row);
                $this->tc_step_data_list[$this->tc_step_index] = $tc_step_node;
                $this->tc_step_index++;
            }
        }
    }
    
    /**
     * parse excel row data into ExDataBin object
     * @param :  $php_excel => php excel info and operate node
     * @param :  $row => row number of current sheet
     * @return:  ExDataBin Node
     * @author:  zhouzhaoxin
     * @version: 20161117 created by zhouzhaoxin
     * **/
    private function parse_row_from_excel($php_excel, $row)
    {
        $xls_line = new ExDataBin();
        
        $xls_line->set_system($php_excel->getActiveSheet()->getCell(idx_col_system . $row)->getValue());
        $xls_line->set_first_level($php_excel->getActiveSheet()->getCell(idx_col_firstlevel . $row)->getValue());
        $xls_line->set_second_level($php_excel->getActiveSheet()->getCell(idx_col_secondlevel . $row)->getValue());
        $xls_line->set_third_level($php_excel->getActiveSheet()->getCell(idx_col_thirdlevel . $row)->getValue());
        $xls_line->set_fourth_level($php_excel->getActiveSheet()->getCell(idx_col_fourthlevel . $row)->getValue());
        $xls_line->set_fifth_level($php_excel->getActiveSheet()->getCell(idx_col_fifthlevel . $row)->getValue()); 
        $xls_line->set_name(htmlspecialchars($php_excel->getActiveSheet()->getCell(idx_col_testcase_name . $row)->getValue()));
        $xls_line->set_tc_id($php_excel->getActiveSheet()->getCell(idx_col_tc_id . $row)->getValue());
        $xls_line->set_summary(htmlspecialchars($php_excel->getActiveSheet()->getCell(idx_col_testcase_summary . $row)->getValue()));
        $xls_line->set_preconditions(htmlspecialchars($php_excel->getActiveSheet()->getCell(idx_col_testcase_preconditions . $row)->getValue()));
        $xls_line->set_step_number($php_excel->getActiveSheet()->getCell(idx_col_testcase_stepnum . $row)->getValue());
        $xls_line->set_actions(htmlspecialchars($php_excel->getActiveSheet()->getCell(idx_col_testcase_stepaction . $row)->getValue()));
        $xls_line->set_expected_results(htmlspecialchars($php_excel->getActiveSheet()->getCell(idx_col_testcase_expectedresults . $row)->getValue()));
        $xls_line->set_execution_type($php_excel->getActiveSheet()->getCell(idx_col_testcase_execution_type . $row)->getValue());
        $xls_line->set_importance($php_excel->getActiveSheet()->getCell(idx_col_testcase_importance . $row)->getValue());
        $xls_line->set_complexity($php_excel->getActiveSheet()->getCell(idx_col_testcase_complexity . $row)->getValue());      
        $xls_line->set_execute_time($php_excel->getActiveSheet()->getCell(idx_col_testcase_extimated_exec_duration . $row)->getValue());
        $xls_line->set_author($php_excel->getActiveSheet()->getCell(idx_col_testcase_designer . $row)->getValue());
        $xls_line->set_creation_ts($php_excel->getActiveSheet()->getCell(idx_col_testcase_creation_ts . $row)->getValue());
        $xls_line->set_reviewer($php_excel->getActiveSheet()->getCell(idx_col_testcase_reviewer_id . $row)->getValue());
        $xls_line->set_reviewed_status($php_excel->getActiveSheet()->getCell(idx_col_testcase_reviewed_status . $row)->getValue());
        $xls_line->set_bpm_id($php_excel->getActiveSheet()->getCell(idx_col_testcase_bpm_id . $row)->getValue());
        $xls_line->set_keywords($php_excel->getActiveSheet()->getCell(idx_col_testcase_keywords . $row)->getValue());
        
        return $xls_line;
    }
    
    /**
     * get existed directory list
     * @param :  void
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161117 created by zhouzhaoxin
     * **/
    private function get_existed_directory_tree()
    {
        $node_abs_length = 0;
        
        for ($dic_depth = 1; $dic_depth < 6; $dic_depth++)
        {
            $node_abs_length = $dic_depth * TL_NODE_ABS_LENGTH;
            
            $sql = "select id, name, parent_id from " .
                $this->db_handler->get_table('nodes_hierarchy') . " n where n.node_type_id = 2 " .
                " and LENGTH(n.node_depth_abs) = " . $node_abs_length;
            $result = $this->db_handler->fetchRowsIntoMap($sql, 'id');
                
            if (count($result, COUNT_NORMAL) > 0)
            {
                $this->existed_directory_array[$dic_depth] = $result;
            }
        }
    }
    
    /**
     * get dic tree info and map to testcase
     * @param :  void
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161117 created by zhouzhaoxin
     * **/
    private function parse_directory_tree()
    {
        foreach ($this->tc_step_data_list as $tc_index => $testcase_node)
        {
            $first_level = $testcase_node->get_first_level();
            $first_id = null;
            $second_level = $testcase_node->get_second_level();
            $second_id = null;
            $third_level = $testcase_node->get_third_level();
            $third_id = null;
            $fourth_level = $testcase_node->get_fourth_level();
            $fourth_id = null;
            $fifth_level = $testcase_node->get_fifth_level();
            $fifth_id = null;
            
            if ($first_level != null && $first_level != "" && trim($first_level) != "")
            {
                $first_id = $this->parse_directory_level(1, $first_level, 
                    $tc_index, $testcase_node, null);
            }
            
            if ($second_level != null && $second_level != "" && trim($second_level) != "")
            {
                $second_id = $this->parse_directory_level(2, $second_level,
                    $tc_index, $testcase_node, $first_id);
            }
            else 
            {
                // no second level dic, means testcase in first dic
                $tc_parent = $this->directory_array[1][$first_id];
                $tc_parent->add_tc_step($tc_index);
                continue;
            }
            
            if ($third_level != null && $third_level != "" && trim($third_level) != "")
            {
                $third_id = $this->parse_directory_level(3, $third_level,
                    $tc_index, $testcase_node, $second_id);
            }
            else
            {
                // no third level dic, means testcase in second dic
                $tc_parent = $this->directory_array[2][$second_id];
                $tc_parent->add_tc_step($tc_index);
                continue;
            }
            
            if ($fourth_level != null && $fourth_level != "" && trim($fourth_level) != "")
            {
                $fourth_id = $this->parse_directory_level(4, $fourth_level,
                    $tc_index, $testcase_node, $third_id);
            }
            else
            {
                // no fourth level dic, means testcase in third dic
                $tc_parent = $this->directory_array[3][$third_id];
                $tc_parent->add_tc_step($tc_index);
                continue;
            }
            
            if ($fifth_level != null && $fifth_level != "" || trim($fifth_level) != "")
            {
                // the last level dic
                $fifth_id = $this->parse_directory_level(5, $fifth_level,
                    $tc_index, $testcase_node, $fourth_id);
                $tc_parent = $this->directory_array[5][$fifth_id];
                $tc_parent->add_tc_step($tc_index);
            }
            else
            {
                // no fifth level dic, means testcase in fourth dic
                $tc_parent = $this->directory_array[4][$fourth_id];
                $tc_parent->add_tc_step($tc_index);
            }
        }
    }
    
    
    /**
     * parse dic level info ,create or find existed node to mapping it
     * @param :  $current_depth => dic_level
     * @param :  $name => dic name
     * @param :  $tc_index => testcase index
     * @param :  $test_node => testcase info
     * @param :  $parent_id => parent inside id
     * @return:  node id
     * @author:  zhouzhaoxin
     * @version: 20161117 created by zhouzhaoxin
     * **/
    private function parse_directory_level($current_depth, $name, $tc_index, $test_node, $parent_id)
    {
        // first view directory_array, because import generally do in batches
        $dic_node_id = null;
        
        $find_in_dic = false;
        foreach ($this->directory_array[$current_depth] as $id => $dic_node)
        {
            if ($name == $dic_node->get_name() &&
                ($parent_id == $dic_node->get_parent() || $parent_id == null))
            {
                $dic_node_id = $dic_node->get_id();
                $find_in_dic = true;
                break;
            }
        }
        
        if (!$find_in_dic)
        {
            // then view the existed directory
            $find_in_existed = false;
            $parent_nh_id = null;
            
            if ($current_depth > 1)
            {
                $parent_node = $this->directory_array[$current_depth - 1][$parent_id];
                $parent_nh_id = $parent_node->get_nh_id();
                
                foreach ($this->existed_directory_array[$current_depth] as $exist_id => $exist_dic)
                {
                    if ($name == $exist_dic['name'] && $parent_nh_id == $exist_dic['parent_id'])
                    {
                        $dic_new = new DicNode($this->directory_index[$current_depth], $name);
                        $dic_new->set_nh_id($exist_dic['id']);
                        $dic_new->set_parent($parent_id);
                        $dic_node_id = $dic_new->get_id();
                        $this->directory_array[$current_depth][$this->directory_index[$current_depth]] = $dic_new;
                        $this->directory_index[$current_depth]++;
                        $find_in_existed = true;
                        break;
                    }
                }
            }
            else 
            {
                foreach ($this->existed_directory_array[$current_depth] as $exist_id => $exist_dic)
                {
                    if ($name == $exist_dic['name'])
                    {
                        $dic_new = new DicNode($this->directory_index[$current_depth], $name);
                        $dic_new->set_nh_id($exist_dic['id']);
                        $dic_new->set_parent($parent_id);
                        $dic_node_id = $dic_new->get_id();
                        $this->directory_array[$current_depth][$this->directory_index[$current_depth]] = $dic_new;
                        $this->directory_index[$current_depth]++;
                        $find_in_existed = true;
                        break;
                    }
                }
            }
            
        
            if (!$find_in_existed)
            {
                $dic_new = new DicNode($this->directory_index[$current_depth], $name);
                $dic_new->set_parent($parent_id);
                $this->directory_array[$current_depth][$this->directory_index[$current_depth]] = $dic_new;
                $this->directory_index[$current_depth]++;
                $dic_node_id = $dic_new->get_id();
            }
        }
        
        return $dic_node_id;
    }
    
    /**
     * write testcase and step data to database
     * @param :  void
     * @return:  result message
     * @author:  zhouzhaoxin
     * @version: create by zhouzhaoxin 20161123
     * **/
    private function write_data_to_db()
    {
        $dic_depth = 1;
        
        for ($dic_depth = 1; $dic_depth < 6; $dic_depth++)
        {
            foreach ($this->directory_array[$dic_depth] as $id => $dic_node)
            {
                // create node is not existed, get id when existed
                $ret = $this->add_testsuite_node_to_db($dic_depth, $dic_node);
                
                // create testcase under testsuite
                if ($ret)
                {
                    $this->add_testcase_nodes_to_db($dic_depth, $dic_node);
                }
            }
        }
    }
    
    /**
     * add testsuite and testcase to db
     * @param :  void
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: create by zhouzhaoxin 20161123
     * **/
    private function add_testsuite_node_to_db($level, $dic_node)
    {
        $debugMsg='Class:' .__CLASS__ . ' - Method:' . __FUNCTION__ . ' :: ';
        $node_nh_id = null;
        
        if ($dic_node->get_nh_id() == null)
        {
            // node not exist in db, first need to create testsuite
            $new_order = null;
            $parent_nh_id = null;
            if ($level == 1)
            {
                $parent_nh_id = $this->project_id;
            }
            else 
            {
                $parent_node = $this->directory_array[$level - 1][$dic_node->get_parent()];
                $parent_nh_id = $parent_node->get_nh_id();
            }

            if ($parent_nh_id <= 0)
            {
                tLog($debugMsg . " " . $dic_node->get_name() . " node create error with error parent id : " . $parent_nh_id, "ERROR", "INFO");
                return false;
            }
            
            $ret = $this->tsuite_manager->create($parent_nh_id, $dic_node->get_name(), "",
                $new_order, config_get('check_names_for_duplicates'), 'block');
            
            if (!$ret['status_ok'])
            {
                // add testsuite failed, mean node existed
                $node_id = $this->get_tsuite_id_by_name($dic_node->get_name(), $parent_nh_id);
                if ($node_id == 0)
                {
                    $tsresult_message = array();
                    $tsresult_message[0] = $dic_node->get_name();
                    $tsresult_message[1] = "测试用例集创建失败。";
                    $this->result_message[$this->result_index] = $tsresult_message;
                    $this->result_index++;
                    
                    $msg = $debugMsg . ' nodes_hirerachy get id error';
                    tLog($msg, "ERROR", "INFO");
                    return false;
                }
                else 
                {
                    $tsresult_message = array();
                    $tsresult_message[0] = $dic_node->get_name();
                    $tsresult_message[1] = "测试用例集已创建。";
                    $this->result_message[$this->result_index] = $tsresult_message;
                    $this->result_index++;
                    
                    $dic_node->set_nh_id($node_id);
                }
            }
            else 
            {
                $dic_node->set_nh_id($ret['id']);
            }
        }
        else 
        {
            // node exist
            $node_nh_id = $dic_node->get_nh_id();
        }
        
        return true;
    }
    
    /**
     * get testsuite id by name and parent
     * @param :  $name => node name
     * @param :  $parent_id => node parent_id
     * @return:  node id(nodes_hirerachy)
     * @author:  zhouzhaoxin
     * @version: create by zhouzhaoxin 20161123
     * **/
    private function get_tsuite_id_by_name($name, $parent_id)
    {
        $debugMsg='Class:' .__CLASS__ . ' - Method:' . __FUNCTION__ . ' :: ';
        
        if ($parent_id == null || $parent_id <= 0)
        {
            $msg = $debugMsg . ' FATAL Error $parentNodeID can not null and <= 0';
            throw new Exception($msg);
        }
        
        
        $sql = "/* {$debugMsg} */ " .
        " SELECT NHA.id  FROM " . $this->db_handler->get_table('nodes_hierarchy') . " NHA " .
        " WHERE NHA.node_type_id  = 2 " .
        " AND NHA.name = '" . $this->db_handler->prepare_string($name) . "'" .
        " AND NHA.parent_id = " . $this->db_handler->prepare_int($parent_id);
        
        $rs = $this->db_handler->get_recordset($sql);
        if (isset($rs[0]['id']) && $rs[0]['id'] > 0)
        {
            return $rs[0]['id'];
        }
        else 
        {
            return 0;
        }
    }
    
    /**
     * add testcase to db
     * @param :  void
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: create by zhouzhaoxin 20161123
     * **/
    private function add_testcase_nodes_to_db($level, $dic_node)
    {
        $debugMsg='Class:' .__CLASS__ . ' - Method:' . __FUNCTION__ . ' :: ';
        $tc_node = null;
        
        if (count($dic_node->get_tc_steps(), COUNT_NORMAL) <= 0)
        {
            return false;
        }
        
        $dic_nh_id = $dic_node->get_nh_id();
        if ($dic_nh_id == null || $dic_nh_id <= 0)
        {
            $msg = $debugMsg . ' FATAL Error invalid testsuite';
            tLog($msg, "ERROR", "INFO");
            return false;
        }
        
        foreach ($dic_node->get_tc_steps() as $key => $tc_inside_id)
        {
            $tc_node = $this->tc_step_data_list[$tc_inside_id]; 
            
            $tcase_id = $this->get_tcase_id_by_name($tc_node->get_name(), $dic_nh_id);
            
            if (!$tc_node->get_status())
            {
                if ($tcase_id == 0)
                {
                    // case not exist ,need to create     
                    $this->create_testcase($dic_nh_id, $tc_inside_id, $tc_node, $dic_node);
                }
                else 
                {
                    if ($this->import_type == "update_last_version")
                    {
                        //repeat, update laterst version
                        $this->update_testcase_latest_version($tcase_id, $dic_nh_id, $tc_inside_id, $tc_node, $dic_node);                        
                    }
                    else 
                    {
                        // create new case or create a new version
                        $this->create_testcase($dic_nh_id, $tc_inside_id, $tc_node, $dic_node);
                    }
                }
            }
        }
    }
    
    /**
     * create a testcase
     * @param :  $parent_id => node parent_id
     * @param :  $tc_node => testcase info node
     * @param :  $tc_inside_id => tc index for array key
     * @param :  $dic_node => parent node, used to get muti steps
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: create by zhouzhaoxin 20161123
     * **/
    private function create_testcase($parent_id, $tc_inside_id, $tc_node, $dic_node)
    {  
        // compute order
        $new_order = config_get('treemenu_default_testcase_order');
        $co = $this->tcase_manager->tree_manager->getBottomOrder($parent_id, array('node_type' => 'testcase'));
        if( $co > 0)
        {
            $new_order = $co + 1;
        }
        
        // init options parameter
        $options = array('check_duplicate_name' => config_get('check_names_for_duplicates'),
            'action_on_duplicate_name' => 'block',
            'status' => '1',
            'estimatedExecDuration' => $tc_node->get_execute_time());
          
        if ($this->import_type != "update_last_version")
        {
            $options['action_on_duplicate_name'] = $this->import_type;
        }
        
        // change enum name to id
        $enum_list = new publicCommand();
        $tc_execution_type_id = $enum_list->getEnumID(ENUM_execution_type,$tc_node->get_execution_type());
        $tc_importance_id = $enum_list->getEnumID(ENUM_importance,$tc_node->get_importance());
        $tc_complexity_id = $enum_list->getEnumID(ENUM_complexity,$tc_node->get_complexity());
        $tc_reviewed_status_id = $enum_list->getEnumID(ENUM_reviewed_status,$tc_node->get_reviewed_status());
        
        $tc_steps = array();
        $tc_steps_index = 0;
        $processed_list = array();
 
        //get all steps
        $dic_tc_steps = $dic_node->get_tc_steps();
        
        // and owner's step
        //not same step, but has same parent and extenal id, belong to one case
        if (($tc_node->get_step_number() != "" 
            && is_numeric($tc_node->get_step_number())) || $tc_node->get_step_number() == 0)
        {
            $tc_steps[$tc_steps_index] = array();
            $tc_steps[$tc_steps_index]['step_number'] = $tc_node->get_step_number();
            $tc_steps[$tc_steps_index]['actions'] = $tc_node->get_actions();
            $tc_steps[$tc_steps_index]['expected_results'] = $tc_node->get_expected_results();
            $tc_steps[$tc_steps_index]['execution_type'] = $tc_execution_type_id;
            $tc_steps_index++;
        }
        
        $processed_list[] = $tc_inside_id;
        
        //20170430 add by zhouzhaoxin for add steps by same tcase name
        foreach ($dic_tc_steps as $id => $tc_step_id)
        {
            if ($tc_step_id != $tc_inside_id &&
                $this->tc_step_data_list[$tc_inside_id]->get_name() ==
                $this->tc_step_data_list[$tc_step_id]->get_name())
            {
                //not same step, but has same parent and tcase name, belong to one case
                $step_node = $this->tc_step_data_list[$tc_step_id];
            
                if ($step_node->get_step_number() != "" && is_numeric($step_node->get_step_number()))
                {
                    $tc_steps[$tc_steps_index] = array('step_number' => '', 'actions' => '', 'expected_results' => '', 'execution_type' => '');
                    $tc_steps[$tc_steps_index]['step_number'] = $step_node->get_step_number();
                    $tc_steps[$tc_steps_index]['actions'] = $step_node->get_actions();
                    $tc_steps[$tc_steps_index]['expected_results'] = $step_node->get_expected_results();
            
                    $setp_execution_type_id = $enum_list->getEnumID(ENUM_execution_type,$step_node->get_execution_type());
            
                    $tc_steps[$tc_steps_index]['execution_type'] = $setp_execution_type_id;
                    $tc_steps_index++;
                    $processed_list[] = $tc_step_id;
                }
            }
        }
        
        //20170430 deleted by zhouzhaoxin for tc_id not used for duplicated check 
        /*
        foreach ($dic_tc_steps as $id => $tc_step_id)
        {
            if ($tc_step_id != $tc_inside_id && 
                $this->tc_step_data_list[$tc_inside_id]->get_tc_id() == 
                $this->tc_step_data_list[$tc_step_id]->get_tc_id())
            {
                //not same step, but has same parent, belong to one case
                $step_node = $this->tc_step_data_list[$tc_step_id];
                
                if ($step_node->get_step_number() != "" && is_numeric($step_node->get_step_number()))
                {
                    $tc_steps[$tc_steps_index] = array('step_number' => '', 'actions' => '', 'expected_results' => '', 'execution_type' => '');
                    $tc_steps[$tc_steps_index]['step_number'] = $step_node->get_step_number();
                    $tc_steps[$tc_steps_index]['actions'] = $step_node->get_actions();
                    $tc_steps[$tc_steps_index]['expected_results'] = $step_node->get_expected_results();
                    
                    $setp_execution_type_id = $enum_list->getEnumID(ENUM_execution_type,$step_node->get_execution_type());
                    
                    $tc_steps[$tc_steps_index]['execution_type'] = $setp_execution_type_id;
                    $tc_steps_index++;
                    $processed_list[] = $tc_step_id;
                }
            }
        }
        */
        
        //get keywords
        $assigned_keywords_list = "";
        if($tc_node->get_keywords() != null && trim($tc_node->get_keywords() != ""))
        {
            $a_keywords = explode(keyword_separator, $tc_node->get_keywords());
            foreach ($a_keywords as $keyw => $keyword)
            {
                if (isset($this->keywords_map[$keyword]) && trim($this->keywords_map[$keyword]) != "")
                {
                    $assigned_keywords_list .= $this->keywords_map[$keyword];
                    $assigned_keywords_list .= ",";
                }
            }
        }
        
        if (strlen($assigned_keywords_list) > 1)
        {
            $assigned_keywords_list = substr($assigned_keywords_list, 0, strlen($assigned_keywords_list) - 1);
        }
        
        // get author id and reviewer id
        $author_id = 1;
        $reviewer_id = 1;
        
        if (isset($this->users_map[$tc_node->get_reviewer()]))
        {
            $reviewer_id = $this->users_map[$tc_node->get_reviewer()];
        }
        
        if (isset($this->users_map[$tc_node->get_author()]))
        {
            $author_id = $this->users_map[$tc_node->get_author()];
        }
        
        $tcase = $this->tcase_manager->create($parent_id, $tc_node->get_name(),
            $tc_node->get_summary(), $tc_node->get_preconditions(),
            $tc_steps, $author_id, $assigned_keywords_list,
            $new_order, testcase::AUTOMATIC_ID,
            $tc_execution_type_id, $tc_importance_id,
            $tc_node->get_tc_id(), $tc_node->get_bpm_id(),
            $tc_complexity_id, $tc_reviewed_status_id,
            $reviewer_id, $options);
        
        if($tcase['status_ok'])
        {
            if (isset($tcase['has_duplicate']) && $tcase['has_duplicate'])
            {
                // repeat testcase
                $tsresult_message = array();
                $tsresult_message[0] = $tc_node->get_name();
                if ($this->import_type == 'generate_new')
                {
                    $tsresult_message[1] = "测试用例重复，创建一个新的测试用例";
                }
                else 
                {
                    $tsresult_message[1] = "创建新版本完成";
                }
                $this->result_message[$this->result_index] = $tsresult_message;
                $this->result_index++;
            }
            else
            {
                $tsresult_message = array();
                $tsresult_message[0] = $tc_node->get_name();
                $tsresult_message[1] = "创建测试用例完成";
                $this->result_message[$this->result_index] = $tsresult_message;
                $this->result_index++;
            }
            
            
        }
        elseif (isset($tcase['msg']))
        {
            $tsresult_message = array();
            $tsresult_message[0] = $tc_node->get_name();
            $tsresult_message[1] = "创建测试用例异常，异常信息" . $tcase['msg'];
            $this->result_message[$this->result_index] = $tsresult_message;
            $this->result_index++;
        }
        
        foreach ($processed_list as $keyp => $processed)
        {
            $this->tc_step_data_list[$processed]->set_status(true);
        }  
    }
    
    /**
     * update testcase latest version
     * @param :  $parent_id => node parent_id
     * @param :  $tc_node => testcase info node
     * @param :  $tc_inside_id => tc index for array key
     * @param :  $dic_node => parent node, used to get muti steps
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: create by zhouzhaoxin 20161123
     * **/
    private function update_testcase_latest_version($tcase_id, $parent_id, $tc_inside_id, $tc_node, $dic_node)
    {
        $options = array('status' => '1',
            'estimatedExecDuration' => $tc_node->get_execute_time(),
            'importbyxls' => '1'
        );
        
        // change enum name to id
        $enum_list = new publicCommand();
        $tc_execution_type_id = $enum_list->getEnumID(ENUM_execution_type,$tc_node->get_execution_type());
        $tc_importance_id = $enum_list->getEnumID(ENUM_importance,$tc_node->get_importance());
        $tc_complexity_id = $enum_list->getEnumID(ENUM_complexity,$tc_node->get_complexity());
        $tc_reviewed_status_id = $enum_list->getEnumID(ENUM_reviewed_status,$tc_node->get_reviewed_status());
        
        $tc_steps = array();
        $tc_steps_index = 0;
        $processed_list = array();
        
        //get all steps
        $dic_tc_steps = $dic_node->get_tc_steps();
        
        // and owner's step
        //not same step, but has same parent and extenal id, belong to one case
        $tc_steps[$tc_steps_index] = array();
        if ($tc_node->get_step_number() != "" && is_numeric($tc_node->get_step_number()))
        {
            $tc_steps[$tc_steps_index]['step_number'] = $tc_node->get_step_number();
            $tc_steps[$tc_steps_index]['actions'] = $tc_node->get_actions();
            $tc_steps[$tc_steps_index]['expected_results'] = $tc_node->get_expected_results();
            $tc_steps[$tc_steps_index]['execution_type'] = $tc_execution_type_id;
            $tc_steps_index++;
        }
        
        $processed_list[] = $tc_inside_id;
        
        //20170430 add by zhouzhaoxin for add steps by same tcase name
        foreach ($dic_tc_steps as $id => $tc_step_id)
        {
            if ($tc_step_id != $tc_inside_id &&
                $this->tc_step_data_list[$tc_inside_id]->get_name() ==
                $this->tc_step_data_list[$tc_step_id]->get_name())
            {
                //not same step, but has same parent and tcase name, belong to one case
                $step_node = $this->tc_step_data_list[$tc_step_id];
        
                if ($step_node->get_step_number() != "" && is_numeric($step_node->get_step_number()))
                {
                    $tc_steps[$tc_steps_index] = array('step_number' => '', 'actions' => '', 'expected_results' => '', 'execution_type' => '');
                    $tc_steps[$tc_steps_index]['step_number'] = $step_node->get_step_number();
                    $tc_steps[$tc_steps_index]['actions'] = $step_node->get_actions();
                    $tc_steps[$tc_steps_index]['expected_results'] = $step_node->get_expected_results();
        
                    $setp_execution_type_id = $enum_list->getEnumID(ENUM_execution_type,$step_node->get_execution_type());
        
                    $tc_steps[$tc_steps_index]['execution_type'] = $setp_execution_type_id;
                    $tc_steps_index++;
                    $processed_list[] = $tc_step_id;
                }
            }
        }
        
        /*
        foreach ($dic_tc_steps as $id => $tc_step_id)
        {
            if ($tc_step_id != $tc_inside_id && 
                $this->tc_step_data_list[$tc_inside_id]->get_id() == $this->tc_step_data_list[$tc_step_id]->get_id())
            {
                //not same step, but has same parent and extenal id, belong to one case
                $step_node = $this->tc_step_data_list[$tc_step_id];
                
                if ($step_node->get_step_number() != "" && is_numeric($step_node->get_step_number()))
                {
                    $tc_steps[$tc_steps_index] = array('step_number' => '', 'actions' => '', 'expected_results' => '', 'execution_type' => '');
                    $tc_steps[$tc_steps_index]['step_number'] = $step_node->get_step_number();
                    $tc_steps[$tc_steps_index]['actions'] = $step_node->get_actions();
                    $tc_steps[$tc_steps_index]['expected_results'] = $step_node->get_expected_results();
                    
                    $setp_execution_type_id = $enum_list->getEnumID(ENUM_execution_type,$step_node->get_execution_type());
                    
                    $tc_steps[$tc_steps_index]['execution_type'] = $setp_execution_type_id;
                    
                    $tc_steps_index++;
                    $processed_list[] = $tc_step_id;
                }
            }
        }
        */
        
        //get latest version id
        $tcversion_id = null;
        $item = $this->tcase_manager->get_last_active_version($tcase_id);
        if (is_null($item))
        {
            // get last version no matter if is active
            $dummy = $this->tcase_manager->get_last_version_info($tcase_id);
            $dummy['tcversion_id'] = $dummy['id'];
            $item[0] = $dummy;
        }
        
        if( is_null($item) )
        {
            $status_ok = false;
            $tsresult_message = array();
            $tsresult_message[0] = $tc_node->get_name();
            $tsresult_message[1] = "更新测试用例版本异常，没有匹配的用例版本";
            $this->result_message[$this->result_index] = $tsresult_message;
            $this->result_index++;
            return ;
        }
        
        $item = current($item);
        $tcversion_id = $item['tcversion_id'];
        
        // get keywords
        $assigned_keywords_list = "";
        if($tc_node->get_keywords() != null && trim($tc_node->get_keywords() != ""))
        {
            $a_keywords = explode(keyword_separator, $tc_node->get_keywords());
            foreach ($a_keywords as $keyw => $keyword)
            {
                if (isset($this->keywords_map[$keyword]) && trim($this->keywords_map[$keyword]) != "")
                {
                    $assigned_keywords_list .= $this->keywords_map[$keyword];
                    $assigned_keywords_list .= ",";
                }
            }
        }
        
        if (strlen($assigned_keywords_list) > 1)
        {
            $assigned_keywords_list = substr($assigned_keywords_list, 0, strlen($assigned_keywords_list) - 1);
        }
        
        // get author id and reviewer id
        $author_id = 1;
        $reviewer_id = 1;
        
        if (isset($this->users_map[$tc_node->get_reviewer()]))
        {
            $reviewer_id = $this->users_map[$tc_node->get_reviewer()];
        }
        
        if (isset($this->users_map[$tc_node->get_author()]))
        {
            $author_id = $this->users_map[$tc_node->get_author()];
        }
        
        $ret = $this->tcase_manager->update($tcase_id, $tcversion_id, $tc_node->get_name(),
            $tc_node->get_summary(), $tc_node->get_preconditions(), $tc_steps,
            $author_id, $assigned_keywords_list,
            testcase::DEFAULT_ORDER, $tc_execution_type_id,
            $tc_importance_id, $tc_node->get_tc_id(), $tc_node->get_bpm_id(),
            $tc_complexity_id, $tc_reviewed_status_id,
            $reviewer_id, null, $options);
        
        if($ret['status_ok'])
        {
            $tsresult_message = array();
            $tsresult_message[0] = $tc_node->get_name();
            $tsresult_message[1] = "更新最新版本完成";
            $this->result_message[$this->result_index] = $tsresult_message;
            $this->result_index++;
        }
        else
        {
            $tsresult_message = array();
            $tsresult_message[0] = $tc_node->get_name();
            $tsresult_message[1] = "更新测试用例最新版本失败";
            $this->result_message[$this->result_index] = $tsresult_message;
            $this->result_index++;
        }
        
        foreach ($processed_list as $keyp => $processed)
        {
            $this->tc_step_data_list[$processed]->set_status(true);
        }
    }
    
    /**
     * get testcase id by name and parent
     * @param :  $name => node name
     * @param :  $parent_id => node parent_id
     * @return:  node id(nodes_hirerachy)
     * @author:  zhouzhaoxin
     * @version: create by zhouzhaoxin 20161123
     * **/
    private function get_tcase_id_by_name($name, $parent_id)
    {
        $debugMsg='Class:' .__CLASS__ . ' - Method:' . __FUNCTION__ . ' :: ';
    
        if ($parent_id == null || $parent_id <= 0)
        {
            $msg = $debugMsg . ' FATAL Error $parentNodeID can not null and <= 0';
            throw new Exception($msg);
        }
    
    
        $sql = "/* {$debugMsg} */ " .
        " SELECT NHA.id  FROM " . $this->db_handler->get_table('nodes_hierarchy') . " NHA " .
        " WHERE NHA.node_type_id  = 3 " .
        " AND NHA.name = '" . $this->db_handler->prepare_string($name) . "'" .
        " AND NHA.parent_id = " . $this->db_handler->prepare_int($parent_id);
    
        $rs = $this->db_handler->get_recordset($sql);
        if (count($rs, COUNT_NORMAL) > 0 && isset($rs[0]['id']) && $rs[0]['id'] > 0)
        {
            return $rs[0]['id'];
        }
        else
        {
            return 0;
        }
    }
}
