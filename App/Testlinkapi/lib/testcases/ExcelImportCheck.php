<?php
require_once('PHPExcel.php');
require_once('PHPExcel/Reader/Excel2007.php');
require_once('PHPExcel/Reader/Excel5.php');
require_once('../public/publicCommand.php');

class ExcelImportCheck
{  
    //database operater handler
    var $dbHandler;
    
    //user id and login list
    var $user_list;
    
    //keyword list
    var $keyword_list;
    
    //new keyword list
    var $new_keyword_list;
    
    /**
     *  construct
     * @author:  zhouzhaoxin
     * @param :  $dbHandler => data base operate handler
     * @version: 20161117 restructure 
     * **/
    function  __construct($dbHandler)
    {
        $this->dbHandler = $dbHandler;
        $this->user_list = array();
        $this->keyword_list = array();
        $this->new_keyword_list = array();
        $this->init_user_list();
        $this->init_keyword_list();
    }
    
    /**
     * initialize the user login and id list
     * @param :  void
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161117 create
     * **/
    private function init_user_list()
    {
        $debug_msg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
        
        $sql = " select id,login FROM " . $this->dbHandler->get_table('users');
        $result = $this->dbHandler->fetchRowsIntoMap($sql, 'id');
        if (count($result, COUNT_NORMAL) > 0)
        {
            foreach ($result as $id => $user_info)
            {
                $this->user_list[$user_info['login']] = $user_info['id'];
            }
        }
    }
    
    /**
     * initialize the keyword list
     * @param :  void
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161117 create
     * **/
    private function init_keyword_list()
    {
        $debug_msg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    
        $sql = "select keyword from " . $this->dbHandler->get_table('keywords')." where testproject_id = " . $_SESSION['testprojectID'];
        $this->keyword_list = $this->dbHandler->fetchColumnsIntoArray($sql, 'keyword');
    }
    
    /**
     * check the import data is right or not
     * @param :  $php_excel => import excel object, get import data vim it
     * @return:  check result
     * @author:  zhouzhaoxin
     * @version: 20161117 restructure
     * **/
    public function get_new_keyword_list()
    {
        return $this->new_keyword_list;
    }
    
    /**
     * check the import data is right or not
     * @param :  $php_excel => import excel object, get import data vim it
     * @return:  check result
     * @author:  zhouzhaoxin
     * @version: 20161117 restructure 
     * **/
    public function import_check($php_excel)
    {
        $sheet_count = $php_excel->getSheetCount();
        for ($sheet_num = 0; $sheet_num < $sheet_count; $sheet_num++)
        {
            //check sheet by sheet
            $php_excel->setActiveSheetIndex($sheet_num);
            
            //get current active sheet row number
            $row_count = $php_excel->getActiveSheet()->getHighestRow();
            for ($row_num = 2; $row_num <= $row_count; $row_num++)
            {
                $this->check_system($php_excel, $row_num);
                $this->check_first_level($php_excel, $row_num);
                $this->check_second_level($php_excel, $row_num);
                $this->check_third_level($php_excel, $row_num);
                $this->check_fourth_level($php_excel, $row_num);
                $this->check_fifth_level($php_excel, $row_num);
                
                //20170430 modified by zhouzhaoxin to ignore testcase id check, id will be null for import duplicate check
                //$this->check_testcase_tc_id($php_excel, $row_num);
                $this->check_testcase_name($php_excel, $row_num);
                $this->check_testcase_step_num($php_excel, $row_num);
                $this->check_testcase_execution_type($php_excel, $row_num);
                $this->check_testcase_importance($php_excel, $row_num);
                $this->check_testcase_complexity($php_excel, $row_num);
                $this->check_testcase_extimated_exec_duration($php_excel, $row_num);
                $this->check_testcase_designer($php_excel, $row_num);
                $this->check_testcase_creation_ts($php_excel, $row_num);
                $this->check_testcase_reviewed_status($php_excel, $row_num);
                $this->check_testcase_reviewer($php_excel, $row_num);
                $this->check_testcase_bpm_id($php_excel, $row_num);
                $this->check_testcase_keywords($php_excel, $row_num);
            }
        }
    }
    
    /**
     * check string length overflow the max length or not
     * @param :  $str => check string
     * @param :  $max_length => max length
     * @return:  true means length too long, false means ok
     * @author:  zhouzhaoxin
     * @version: 20161117 restructure 
     * **/
    private function check_string_length($str, $max_length)
    {
        $length = strlen($str);
        if ($length > $max_length)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * check system name
     * @param :  $php_excel => excel object
     * @param :  $row_num => check row index
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161117 restructure 
     * **/
    private function check_system($php_excel, $row_num)
    {
        $system_name = $php_excel->getActiveSheet()->getCell(idx_col_system . $row_num)->getValue();
        
        if ($system_name == "" || trim($system_name) == "")
        {
            die("Excel中第" . $row_num . "行系统名称不能为空，请进行修改");
        }
        
        if ($this->check_string_length($system_name, system_length))
        {
            die("Excel中第" . $row_num . "行测试用例目录名称超出数据库默认长度，请进行修改");
        }
    }
    
    /**
     * check first dictionary 
     * @param :  $php_excel => excel object
     * @param :  $row_num => check row index
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161117 restructure 
     * **/
    private function check_first_level($php_excel, $row_num)
    {
        $first_level = $php_excel->getActiveSheet()->getCell(idx_col_firstlevel . $row_num)->getValue();
        if ($first_level == "" || trim($first_level) == "")
        {
            die("Excel中第" . $row_num . "行测试用例一级名称不能为空，请进行修改");
        }

        if ($this->check_string_length($first_level, firstlevel_length))
        {
            die("Excel中第" . $row_num . "行测试用例一级名称超出数据库默认长度，请进行修改");
        }
    }
    
    /**
     * check second dictionary 
     * @param :  $php_excel => excel object
     * @param :  $row_num => check row index
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161117 restructure 
     * **/
    private function check_second_level($php_excel, $row_num)
    {
        $second_level = $php_excel->getActiveSheet()->getCell(idx_col_secondlevel.$row_num)->getValue();
        
        if (strlen($second_level) > 0 && trim($second_level) == "")
        {
            die("Excel中第" . $row_num . "行测试用例二级名称不能只包含空格");
        }

        if ($this->check_string_length($second_level, secondlevel_length))
        {
            die("Excel中第" . $row_num . "行测试用例二级名称超出数据库默认长度，请进行修改");
        }
    }
    
    /**
     * check third dictionary 
     * @param :  $php_excel => excel object
     * @param :  $row_num => check row index
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161117 restructure 
     * **/
    private function check_third_level($php_excel, $row_num)
    {
        $third_level = $php_excel->getActiveSheet()->getCell(idx_col_thirdlevel . $row_num)->getValue();
        
        if (strlen($third_level) > 0 && trim($third_level) == "")
        {
            die("Excel中第" . $row_num . "行测试用例三级名称不能只包含空格");
        }
        
        if ($this->check_string_length($third_level, thirdlevel_length))
        {
            die("Excel中第" . $row_num . "行测试用例三级名称超出数据库默认长度，请进行修改");
        }
    }
    
    /**
     * check fourth dictionary 
     * @param :  $php_excel => excel object
     * @param :  $row_num => check row index
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161121 restructure 
     * **/
    private function check_fourth_level($php_excel,$row_num)
    {
        $fourth_level = $php_excel->getActiveSheet()->getCell(idx_col_fourthlevel . $row_num)->getValue();
        
        if (strlen($fourth_level) > 0 && trim($fourth_level) == "")
        {
            die("Excel中第" . $row_num . "行测试用例四级名称不能只包含空格");
        }
        
        if ($this->check_string_length($fourth_level, fourthlevel_length))
        {
            die("Excel中第" . $row_num . "行测试用例四级名称超出数据库默认长度，请进行修改");
        }
    }
    
    /**
     * check fifth dictionary 
     * @param :  $php_excel => excel object
     * @param :  $row_num => check row index
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161121 restructure 
     * **/
    private function check_fifth_level($php_excel,$row_num)
    {
        $fifth_level = $php_excel->getActiveSheet()->getCell(idx_col_fifthlevel . $row_num)->getValue();
        
        if (strlen($fifth_level) > 0 && trim($fifth_level) == "")
        {
            die("Excel中第" . $row_num . "行测试用例五级名称不能只包含空格");
        }
        
        if ($this->check_string_length($fifth_level, fifthlevel_length))
        {
            die("Excel中第" . $row_num . "行测试用例五级名称超出数据库默认长度，请进行修改");
        }
    }
    
    /**
     * check tc prefix 
     * @param :  $php_excel => excel object
     * @param :  $row_num => check row index
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161121 restructure 
     * **/
    private function check_testcase_tc_id($php_excel, $row_num)
    {
        $testcase_tc_id = $php_excel->getActiveSheet()->getCell(idx_col_tc_id . $row_num)->getValue();
        
        if ($testcase_tc_id == "")
        {
            die("Excel中第" . $row_num . "行测试用例标识不能为空，请进行修改");
        }
        
        if ($this->check_string_length($testcase_tc_id, testcase_tc_id_length))
        {
            die("Excel中第".$row_num."行测试用例标识超出数据库默认长度，请进行修改");
        }
    }
    
    /**
     * check tc name 
     * @param :  $php_excel => excel object
     * @param :  $row_num => check row index
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161121 restructure 
     * **/
    private function check_testcase_name($php_excel, $row_num)
    {
        $testcase_name = $php_excel->getActiveSheet()->getCell(idx_col_testcase_name . $row_num)->getValue();
        
        if ($this->check_string_length($testcase_name, testcase_name_length))
        {
            die("Excel中第".$row_num."行测试用例名称超出数据库默认长度，请进行修改");
        }
    }
    
    /**
     * check tc step no 
     * @param :  $php_excel => excel object
     * @param :  $row_num => check row index
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161121 restructure 
     * **/
    private function check_testcase_step_num($php_excel, $row_num)
    {
        $testcase_step_num = $php_excel->getActiveSheet()->getCell(idx_col_testcase_stepnum . $row_num)->getValue();
        
        if ($this->check_string_length($testcase_step_num, testcase_stepnum_length))
        {
            die("Excel中第" . $row_num."行测试用例步骤号超出数据库默认长度，请进行修改");
        }
        
        if ((!(is_numeric($testcase_step_num) && ceil($testcase_step_num) == $testcase_step_num) 
            && $testcase_step_num != "") || $testcase_step_num < 0)
        {
            die("Excel中第" . $row_num . "行测试用例步骤号不是正整数，请进行修改");
        }
    }
    
    /**
     * check tc exec pre time
     * @param :  $php_excel => excel object
     * @param :  $row_num => check row index
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161121 restructure 
     * **/
    private function check_testcase_extimated_exec_duration($php_excel, $row_num)
    {
        $testcase_extimated_exec_duration = $php_excel->getActiveSheet()->getCell(idx_col_testcase_extimated_exec_duration . $row_num)->getValue();
        
        if (!is_numeric($testcase_extimated_exec_duration))
        {
            die("Excel中第" . $row_num."行测试用例估计执行时间不是数值，请进行修改");
        }
        
        if ($this->check_string_length($testcase_extimated_exec_duration, testcase_extimated_exec_duration_length))
        {
            die("Excel中第".$row_num."行测试用例估计执行时间超出数据库默认长度，请进行修改");
        }
    }
    
    /**
     * check tc bpm id
     * @param :  $php_excel => excel object
     * @param :  $row_num => check row index
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161121 restructure 
     * **/
    private function check_testcase_bpm_id($php_excel, $row_num)
    {
        $testcase_bpm_id = $php_excel->getActiveSheet()->getCell(idx_col_testcase_bpm_id . $row_num)->getValue();
        
        if ($this->check_string_length($testcase_bpm_id, testcase_bpm_id_length))
        {
            die("Excel中第" . $row_num . "行测试用例评审编号超出数据库默认长度，请进行修改");
        }
    }
    
    /**
     * check tc importance
     * @param :  $php_excel => excel object
     * @param :  $row_num => check row index
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161121 restructure 
     * **/
    private function check_testcase_importance($php_excel, $row_num)
    {
        $testcase_importance = $php_excel->getActiveSheet()->getCell(idx_col_testcase_importance . $row_num)->getValue();
        $have = false;
        $out_str = "";
        $filter_commond = new publicCommand();
        $importance_list = $filter_commond->importance;
        
        foreach ($importance_list as $lable => $describle)
        {
            if ($testcase_importance == $describle)
            {
                $have = true;
            }
        }
        
        if (!$have)
        {
            foreach ($importance_list as $lable => $describle)
            {
                $out_str .= $describle;
                $out_str .= " ";
            }
            die("Excel中第" . $row_num . "行测试用例优先级填写错误(优先级：" . $out_str . ")，请进行修改");
        }
    }
    
    /**
     * check tc type
     * @param :  $php_excel => excel object
     * @param :  $row_num => check row index
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161121 restructure 
     * **/
    private function check_testcase_execution_type($php_excel, $row_num)
    {
        $testcase_execution_type = $php_excel->getActiveSheet()->getCell(idx_col_testcase_execution_type . $row_num)->getValue();
        $have = false;
        $out_str = "";
        $filter_commond = new publicCommand();
        $type_array = $filter_commond->execution_type;
        
        foreach ($type_array as $lable => $describle)
        {var_dump($testcase_execution_type, $describle);
            echo "<br/><br/>";
            if ($testcase_execution_type == $describle)
            {
                $have = true;
                break;
            }
        }
        
        if (!$have){
            foreach ($type_array as $lable => $describle)
            {
                $out_str .= $describle;
                $out_str .= " ";
            }
            die("Excel中第" . $row_num . "行测试用例类型填写错误(用例类型：" . $out_str . ")，请进行修改");
        }
    }
    
    /**
     * check tc complexity
     * @param :  $php_excel => excel object
     * @param :  $row_num => check row index
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161121 restructure 
     * **/
    private function check_testcase_complexity($php_excel,$row_num)
    {
        $testcase_complexity = $php_excel->getActiveSheet()->getCell(idx_col_testcase_complexity . $row_num)->getValue();
        $have = false;
        $out_str = "";
        $filter_commond = new publicCommand();
        $complexity_array = $filter_commond->complexity;
        
        foreach ($complexity_array as $lable => $describle)
        {
            if ($testcase_complexity == $describle)
            {
                $have = true;
            }
        }
        if (!$have){
            foreach ($complexity_array as $lable => $describle)
            {
                $out_str .= $describle;
                $out_str .= " ";
            }
            die("Excel中第" . $row_num . "行测试用例复杂度填写错误(复杂度：" . $out_str . ")，请进行修改");
        }
    }
    
    /**
     * check tc review status
     * @param :  $php_excel => excel object
     * @param :  $row_num => check row index
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161121 restructure 
     * **/
    private function check_testcase_reviewed_status($php_excel,$row_num)
    {
        $testcase_reviewed_status = $php_excel->getActiveSheet()->getCell(idx_col_testcase_reviewed_status . $row_num)->getValue();
        $have = false;
        $out_str = "";
        $filter_commond = new publicCommand();
        $review_status_array = $filter_commond->reviewed_status;
        
        foreach ($review_status_array as $lable => $describle)
        {
            if($testcase_reviewed_status == $describle)
            {
                $have=true;
            }
            
        }
        
        if (!$have)
        {
            foreach ($review_status_array as $lable => $describle)
            {
                $out_str .= $describle;
                $out_str .= " ";
            }
            die("Excel中第".$row_num."行测试用例评审状态填写错误(评审状态：".$out_str.")，请进行修改");
        }
    }
    
    /**
     * check tc reviewer
     * @param :  $php_excel => excel object
     * @param :  $row_num => check row index
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161121 restructure 
     * **/
    private function check_testcase_reviewer($php_excel, $row_num)
    {
        $testcase_reviewer = $php_excel->getActiveSheet()->getCell(idx_col_testcase_reviewer_id . $row_num)->getValue();
        $testcase_reviewer_id = $this->get_user_id($testcase_reviewer);
        if ($testcase_reviewer_id == null)
        {
            die("Excel中第" . $row_num . "行测试用例评审人填写错误，数据库中不存在用户：" . $testcase_reviewer . "，请进行修改");
        }
    }
    
    /**
     * check tc designer
     * @param :  $php_excel => excel object
     * @param :  $row_num => check row index
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161121 restructure 
     * **/
    private function check_testcase_designer($php_excel,$row_num)
    {
        $testcase_designer = $php_excel->getActiveSheet()->getCell(idx_col_testcase_designer . $row_num)->getValue();
        $testcase_designer_id = $this->get_user_id($testcase_designer);
        if ($testcase_designer_id == null)
        {
            die("Excel中第" . $row_num . "行测试用例设计人填写错误，数据库中不存在用户：" . $testcase_designer . "，请进行修改");
        }
    }
    
    /**
     * get user id by login
     * @param :  $login => user login
     * @return:  user id, if user not exist, return null
     * @author:  zhouzhaoxin
     * @version: 20161121 restructure 
     * **/
    private function get_user_id($login)
    {
        if (array_key_exists($login, $this->user_list))
        {
            return $this->user_list[$login];
        }
        else 
        {
            return null;
        }
    }
    
    /**
     * check tc create time
     * @param :  $php_excel => excel object
     * @param :  $row_num => check row index
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161121 restructure 
     * **/
    private function check_testcase_creation_ts($php_excel, $row_num)
    {
        $testcase_creation_ts = $php_excel->getActiveSheet()->getCell(idx_col_testcase_creation_ts . $row_num)->getValue();
        $testcase_creation_ts_timestamp = strtotime($testcase_creation_ts);
        if (!$testcase_creation_ts_timestamp)
        {
            die("Excel中第" . $row_num . "行测试用例设计时间填写错误，设计时间填写格式：Y-m-d h:i:s，请进行修改");
        }
    }
       
    /**
     * check tc keywords is valid or not
     * @param :  $php_excel => excel object
     * @param :  $row_num => check row index
     * @return:  void
     * @author:  zhouzhaoxin
     * @version: 20161121 restructure 
     * **/
    private function check_testcase_keywords($php_excel, $row_num)
    {
        $testcase_keywords = $php_excel->getActiveSheet()->getCell(idx_col_testcase_keywords . $row_num)->getValue();
        if ($testcase_keywords != "")
        {
            // check system is current syste
            $system = $php_excel->getActiveSheet()->getCell(idx_col_system . $row_num)->getValue();
            if ($system != $_SESSION['testprojectName'])
            {
                die("Excel中第" . $row_num . "行系统信息" . $system . "与当前系统" . $_SESSION['testprojectName'] . "不一致");
            }
            
            $system_id = $_SESSION['testprojectID'];
                     
            $separated_keywords = explode(keyword_separator, $testcase_keywords);
            
            foreach ($separated_keywords as $key => $keyword)
            {
                $keyword = trim($keyword);
                if ($keyword != "")
                {
                    $find = false;
                    foreach ($this->keyword_list as $key2 => $system_keyword)
                    {
                        if ($keyword == $system_keyword)
                        {
                            $find = true;
                            break;
                        }
                    }
                    
                    if(!$find)
                    {
                        foreach ($this->new_keyword_list as $key3 => $new_keyword)
                        {
                            if ($keyword == $new_keyword)
                            {
                                $find = true;
                                break;
                            }
                        }
                    }
                    
                    if (!$find)
                    {
                        $this->new_keyword_list[] = $keyword;
                    }
                }
            }
        }
    }
}

?>