<?php
/**

 *  @filesource   ExDataBin.php
 *  @author       liuchunping
 *
 *  just a testcase bean class
 *  used by testcase import and export
 *  testcase info node match one testcase
 *
 *  @internal revision
 *  @since 20160705  
 *         restructure by zhouzhaoxin on 20161122
 */
class ExDataBin
{
    //system and directory info
    private $system;
    private $first_level;
    private $second_level;
    private $third_level;
    private $fourth_level;
    private $fifth_level;
    
    //test case info
    private $name;
    private $tc_id;  
    private $version;
    private $summary;
    private $preconditions;
    private $step_number;
    private $actions;
    private $expected_results;
    private $execution_type;
    private $importance;
    private $complexity;
    private $execute_time;
    private $author;
    private $creation_ts;
    private $reviewer;
    private $reviewed_status;
    private $bpm_id;
    private $keywords;
    
    // testcase process status
    private $status;
    
    function  __construct()
    {
        $status = false;
    }
    
    public function get_system()
    {
        return $this->system;
    }

    public function get_first_level()
    {
        return $this->first_level;
    }
    
    public function get_second_level()
    {
        return $this->second_level;
    }

    public function get_third_level()
    {
        return $this->third_level;
    }

    public function get_fourth_level()
    {
        return $this->fourth_level;
    }

    public function get_fifth_level()
    {
        return $this->fifth_level;
    }

    public function get_name()
    {
        return $this->name;
    }

    public function get_tc_id()
    {
        return $this->tc_id;
    }

    public function get_version()
    {
        return $this->version;
    }

    public function get_summary()
    {
        return $this->summary;
    }
    

    public function get_preconditions()
    {
        return $this->preconditions;
    }
    
    public function get_step_number()
    {
        return $this->step_number;
    }
    
    public function get_actions()
    {
        return $this->actions;
    }
    
    public function get_expected_results()
    {
        return $this->expected_results;
    }
    
    public function get_execution_type()
    {
        return $this->execution_type;
    }
    
    public function get_importance()
    {
        return $this->importance;
    }
    
    public function get_complexity()
    {
        return $this->complexity;
    }
    
    public function get_execute_time()
    {
        return $this->execute_time;
    }
    
    public function get_author()
    {
        return $this->author;
    }
    
    public function get_creation_ts()
    {
        return $this->creation_ts;
    }
    
    public function get_reviewer()
    {
        return $this->reviewer;
    }
    
    public function get_reviewed_status()
    {
        return $this->reviewed_status;
    }
    
    public function get_bpm_id()
    {
        return $this->bpm_id;
    }
    
    public function get_keywords()
    {
        return $this->keywords;
    }
    
    public function get_status()
    {
        return $this->status;
    }

    public function set_system($system)
    {
        $this->system = $system;
    }

    public function set_first_level($first_level)
    {
        $this->first_level = $first_level;
    }

    public function set_second_level($second_level)
    {
        $this->second_level = $second_level;
    }

    public function set_third_level($third_level)
    {
        $this->third_level = $third_level;
    }

    public function set_fourth_level($fourth_level)
    {
        $this->fourth_level = $fourth_level;
    }

    public function set_fifth_level($fifth_level)
    {
        $this->fifth_level = $fifth_level;
    }

    public function set_name($tc_name)
    {
        $this->name = $tc_name;
    }

    public function set_tc_id($tc_id)
    {
        $this->tc_id = $tc_id;
    }

    public function set_version($version)
    {
        $this->version = $version;
    }

    public function set_summary($summary)
    {
        $this->summary = $summary;
    }

    public function set_preconditions($preconditions)
    {
        $this->preconditions = $preconditions;
    }

    public function set_step_number($step_number)
    {
        $this->step_number = $step_number;
    }

    public function set_actions($actions)
    {
        $this->actions = $actions;
    }

    public function set_expected_results($expected_results)
    {
        $this->expected_results = $expected_results;
    }

    public function set_execution_type($execution_type)
    {
        $this->execution_type = $execution_type;
    }

    public function set_importance($importance)
    {
        $this->importance = $importance;
    }

    public function set_complexity($complexity)
    {
        $this->complexity = $complexity;
    }

    public function set_execute_time($execute_time)
    {
        $this->execute_time = $execute_time;
    }

    public function set_author($author)
    {
        $this->author = $author;
    }

    public function set_creation_ts($creation_ts)
    {
        $this->creation_ts = $creation_ts;
    }

    public function set_reviewer($reviewer)
    {
        $this->reviewer = $reviewer;
    }

    public function set_reviewed_status($reviewed_status)
    {
        $this->reviewed_status = $reviewed_status;
    }

    public function set_bpm_id($bpm_id)
    {
        $this->bpm_id = $bpm_id;
    }
    
    public function set_keywords($tc_keywords)
    {
        $this->keywords = $tc_keywords;
    }
    
    public function set_status($status)
    {
        $this->status = $status;
    }
}
