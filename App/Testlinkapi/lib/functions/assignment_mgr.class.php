<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * Manager for assignment activities
 *
 * @filesource  assignment_mgr.class.php
 * @package     TestLink
 * @author      Francisco Mancardi
 * @copyright   2007-2015, TestLink community 
 * @link        http://www.testlink.org
 * 
 * @internal revisions
 * @since 1.9.14
 */
 
/**
 * class manage assignment users for testing
 * @package   TestLink
 */ 
class assignment_mgr extends tlObjectWithDB
{

  function __construct(&$db) 
  {
    parent::__construct($db);
  }

  /*
   $key_field: contains the filename that has to be used as the key of
               the returned hash.    
  */
  function get_available_types($key_field='description') 
  {
    static $hash_types;
    if (!$hash_types)
    {
      $sql = "SELECT * FROM ".$this->db->get_table('assignment_types')."";
      $hash_types = $this->db->fetchRowsIntoMap($sql,$key_field);
    }
    return $hash_types;
  }

  /*
   $key_field: contains the name column that has to be used as the key of
               the returned hash.    
  */
  function get_available_status($key_field='description') 
  {
    static $hash_types;
    if (!$hash_types)
    {
      $sql = " SELECT * FROM ".$this->db->get_table('assignment_status')." "; 
      $hash_types = $this->db->fetchRowsIntoMap($sql,$key_field);
    }
    
    return $hash_types;
  }

  // $feature_id can be an scalar or an array
  function delete_by_feature_id($feature_id) 
  {
    if( is_array($feature_id) )
    {
      $feature_id_list = implode(",",$feature_id);
      $where_clause = " WHERE feature_id IN ($feature_id_list) ";
    }
      else
    {
      $where_clause = " WHERE feature_id={$feature_id}";
    }
    $sql = " DELETE FROM ".$this->db->get_table('user_assignments')."  {$where_clause}"; 
    $result = $this->db->exec_query($sql);
  }
  
  /**
   * Delete the user assignments for a given build.
   * 
   * @author Andreas Simon
   * @param int $build_id The ID of the build for which the user assignments shall be deleted.
   * @param int $delete_all_types If true, all assignments regardless of type will be deleted,
   *                              else (default) only tester assignments.
   */
  function delete_by_build_id($build_id, $delete_all_types = false) 
  {
    $type_sql = "";
    
    if (!$delete_all_types) 
    {
      $types = $this->get_available_types();
      $tc_execution_type = $types['testcase_execution']['id'];
      $type_sql = " AND type = {$tc_execution_type} ";
    }
    
    $sql = " DELETE FROM ".$this->db->get_table('user_assignments')." " .
           " WHERE build_id = " . intval($build_id) . " {$type_sql} ";
    
    $this->db->exec_query($sql);
  }

  // delete assignments by feature id and build_id
  function delete_by_feature_id_and_build_id($feature_map) 
  {
    $feature_id_list = implode(",",array_keys($feature_map));
    $where_clause = " WHERE feature_id IN ($feature_id_list) ";
      
    $sql = " DELETE FROM ".$this->db->get_table('user_assignments')."  {$where_clause} ";
    
    // build_id is the same for all entries because of assignment form
    // -> skip foreach after first iteration
    $build_id = 0;
    foreach ($feature_map as $key => $feature) 
    {
      $build_id = $feature['build_id'];
      break;
    }
    
    $sql .= " AND build_id = {$build_id} ";
    $result = $this->db->exec_query($sql);
  }
  
  // delete assignments by feature id and build_id
  function delete_by_feature_id_and_keyword_id($testcaseid,$keyword_ids)
  {
      
      if(is_null($testcaseid)) {
          break;
      }
      $feature_id_list = implode(',', $keyword_ids);
      //$where_clause = " WHERE keyword_id IN ($feature_id_list) ";
      $where_clause = " WHERE ";
      $sql = " DELETE FROM ".$this->db->get_table('testcase_keywords')."  {$where_clause} ";
      $sql .= " testcase_id = {$testcaseid} ";
      $result = $this->db->exec_query($sql);
  }

  /**
   * $items array of signature
   * signature = array('type' => ,'feature_id' =>,'user_id' =>, 'build_id' => )
   *
   */
  function deleteBySignature($items) 
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;

    foreach($items as $signature)
    {
      $sql = " DELETE FROM ".$this->db->get_table('user_assignments')." WHERE 1=1 ";
      foreach($signature as $column => $val)
      {
        $sql .= " AND $column = " . intval($val);
      }  
      $result = $this->db->exec_query($sql);
    }  
  }

  
  /**
   * $items array of signature
   * signature = array('type' => ,'feature_id' =>,'user_id' =>, 'build_id' => )
   *
   */
  function deleteBybq($testcaseId,$keyword_id)
  {
      $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
      $sql = " DELETE FROM ".$this->db->get_table('testcase_keywords')." WHERE 1=1 and testcase_id= {$testcaseId} and keyword_id={$keyword_id}";
      $result = $this->db->exec_query($sql);
  }

  /**
    * 
    * @param $feature_map
    * $feature_map['feature_id']['user_id']
    * $feature_map['feature_id']['type']
    * $feature_map['feature_id']['status']
    * $feature_map['feature_id']['assigner_id']
    * $feature_map['feature_id']['build_id']
    * 
    *
    * Need to manage situation where user_id = 0 is passed
    * I will IGNORE IT
    *
    * @internal revisions
    */
  function assign($feature_map) 
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $ret = array();
    $types = $this->get_available_types();
    $safe = null;
   
    foreach($feature_map as $feature_id => $elem)
    {
      $safe['feature_id'] = intval($feature_id);
      $safe['build_id'] = intval($elem['build_id']);
      $safe['type'] = intval($elem['type']);
      
      $uSet = (array)$elem['user_id'];

      foreach($uSet as $user_id)
      {
        $safe['user_id'] = intval($user_id);

        // Check if exists before adding
        $check = "/* $debugMsg */ ";
        $check .= " SELECT id FROM ".$this->db->get_table('user_assignments')." " .
                  " WHERE feature_id = " . $safe['feature_id'] .
                  " AND build_id = " . $safe['build_id'] .
                  " AND type = " . $safe['type'] .
                  " AND user_id = " . $safe['user_id'];

        $rs = $this->db->get_recordset($check);
        if( is_null($rs) || count($rs) == 0 )
        {
          if($safe['user_id'] > 0)
          {
            $sql = "INSERT INTO ".$this->db->get_table('user_assignments')." " .
                   "(feature_id,user_id,assigner_id,type,status,creation_ts";
                      
            $values = "VALUES({$safe['feature_id']},{$safe['user_id']}," .
                      "{$elem['assigner_id']}," .
                      "{$safe['type']},{$elem['status']},";
            $values .= (isset($elem['creation_ts']) ? $elem['creation_ts'] : $this->db->db_now());                   
          
            if(isset($elem['deadline_ts']) )
            {
              $sql .=",deadline_ts";
              $values .="," . $elem['deadline_ts']; 
            }     
          
            if(isset($elem['build_id'])) 
            {
              $sql .= ",build_id";
              $values .= "," . $safe['build_id'];
            }
            else
            {
              if($safe['type'] == $types['testcase_execution']['id'])
              {
                throw new Exception("Error Processing Request - BUILD ID is Mandatory");
              }  
            }  
          
            $sql .= ") " . $values . ")";
            tLog(__METHOD__ . '::' . $sql,"DEBUG");
            $this->db->exec_query($sql);
            $ret[] = $sql;
          }   
        }  
      } // loop over users
    }
    return $ret;
  }
  

  /**
   * 
   * @param $feature_map
   * $feature_map: key   => feature_id
   *               value => hash with optional keys 
   *                        that have the same name of user_assignment fields
   * 
   * @internal revisions
   */
  function update($feature_map) 
  {
    foreach($feature_map as $feature_id => $elem)
    {
      $sepa = "";
      $sql = "UPDATE ".$this->db->get_table('user_assignments')." SET ";
      $simple_fields = array('user_id','assigner_id','type','status');
      $date_fields = array('deadline_ts','creation_ts');  
    
      foreach($simple_fields as $idx => $field)
      {
        if(isset($elem[$field]))
        {
          $sql .= $sepa . "$field={$elem[$field]} ";
          $sepa=",";
        }
      }
      
      foreach($date_fields as $idx => $field)
      {
        if(isset($elem[$field]))
        {
          $sql .= $sepa . "$field=" . $elem[$field] . " ";
          $sepa = ",";
        }
      }
      
      $sql .= "WHERE feature_id={$feature_id} AND build_id={$elem['build_id']}";
      
      $this->db->exec_query($sql);
    }
  }
  
  /**
   * Get the number of assigned users for a given build ID.
   * @param int $build_id ID of the build to check
   * @param int $count_all_types if true, all assignments will be counted, otherwise
   *                             only tester assignments
   * @param int $user_id if given, user ID for which the assignments per build shall be counted
   * @return int $count Number of assignments
   */
  function get_count_of_assignments_for_build_id($build_id, $count_all_types = false, $user_id = 0) 
  {
    $count = 0;
    
    $types = $this->get_available_types();
    $tc_execution_type = $types['testcase_execution']['id'];
    $type_sql = ($count_all_types) ? "" : " AND type = {$tc_execution_type} ";
      
    $user_sql = ($user_id && is_numeric($user_id)) ? "AND user_id = {$user_id} " : "";
    
    $sql = " SELECT COUNT(id) AS count FROM ".$this->db->get_table('user_assignments')." " .
           " WHERE build_id = {$build_id} {$user_sql} {$type_sql} ";
      
    $count = $this->db->fetchOneValue($sql);
      
    return $count;
  }
  
  /**
   * Get count of assigned, but not run testcases per build (and optionally user).
   * @param int $build_id
   * @param bool $all_types
   * @param int $user_id if set and != 0, counts only the assignments for the given user 
   *
   * @internal revisions
   */
  function get_not_run_tc_count_per_build($build_id, $all_types = false, $user_id = 0) 
  {
    $count = 0;
    
    $types = $this->get_available_types();
    $tc_execution_type = $types['testcase_execution']['id'];
    $type_sql = ($all_types) ? "" : " AND UA.type = {$tc_execution_type} ";
    $user_sql = ($user_id && is_numeric($user_id)) ? "AND UA.user_id = {$user_id} " : "";
    
    $sql = " SELECT UA.id as assignment_id,UA.user_id,TPTCV.testplan_id," .
           " TPTCV.platform_id,BU.id AS BUILD_ID,E.id AS EXECID, E.status " .
           " FROM ".$this->db->get_table('user_assignments')." UA " .
           " JOIN ".$this->db->get_table('builds')."  BU ON UA.build_id = BU.id " .
           " JOIN ".$this->db->get_table('testplan_tcversions')." TPTCV " .
           "     ON TPTCV.testplan_id = BU.testplan_id " .
           "     AND TPTCV.id = UA.feature_id " .
           " LEFT OUTER JOIN ".$this->db->get_table('executions')." E " .
           "     ON E.testplan_id = TPTCV.testplan_id " . 
           "     AND E.tcversion_id = TPTCV.tcversion_id " .
           "     AND E.platform_id = TPTCV.platform_id " .
           "     AND E.build_id = UA.build_id " .
           " WHERE UA.build_id = {$build_id} AND E.status IS NULL {$type_sql} {$user_sql} ";       
       
       
    if (isset($build_id) && is_numeric($build_id)) {
      $count = count($this->db->fetchRowsIntoMap($sql, 'assignment_id'));
    }
    
    return $count;
  }
  
  /**
   * Copy the test case execution assignments for a test plan
   * from one build to another.
   * During copying of assignments, the assigner id can be updated if an ID is passed
   * and the timestamp will be updated.
   * 
   * @author Andreas Simon
   * @param int $source_build_id ID of the build to copy the assignments from
   * @param int $target_build_id ID of the target build to which the assignments will be copied
   * @param int $assigner_id will be set as assigner ID of the new assignments if != 0,
   *                         otherwise old assigner ID will be copied
   * @param array $opt 
   *              key => keep_old_assignments: 
   *                     true: existing assignments in target build will be kept,
   *                     otherwise (default) every existing tester assignment will be deleted.
   *
   *              key => copy_all_types
   *                     true: all assignments of any type will be copied. 
   *                     false: only tester assignments will be copied (default).
   *              key => feature_set: array of id
   * @history 
   * 20170804 modified by zhouzhaoxin for copy assign by build and status
   */
  function copy_assignments($tplan_id, $source_build_id, $target_build_id, $assigner_id = 0, $tcv_set)
  {
      $creation_ts = $this->db->db_now();
      $types = $this->get_available_types();
      $tc_execution_type = $types['testcase_execution']['id'];

      // delete the old tester assignments in target builds if there are any
      $this->delete_by_build_id($target_build_id, true);
    
      if (count($tcv_set, COUNT_NORMAL) > 0)
      {
          foreach ($tcv_set as $id => $row)
          {
              $assign_sql = "insert into " . $this->db->get_table('testplan_tcversions') .
                  " (testplan_id, tcversion_id, node_order, urgency, platform_id, author_id, creation_ts, build_id) " .
                  " values ( " .
                  $tplan_id . ", " . 
                  $row['tcversion_id'] . ", '" .
                  $row['node_order'] . "', '" .
                  $row['urgency'] . "', " .
                  $row['platform_id'] . ", " .
                  $assigner_id . ", " .
                  $creation_ts . ", " .
                  $target_build_id . " )";
              $result = $this->db->exec_query($assign_sql);
          }
      }
  } 
  
  
  /**
   * Copy the user assignments for a build
   * 20170808 add by zhouzhaoxin for copy assign by build and status
   */
  function copy_user_assignments($tplan_id, $source_build_id, $target_build_id, $assigner_id = 0)
  {
      $creation_ts = $this->db->db_now();
      $source_set = array();
      $source_count = 0;
      $target_sql = "";
      $target_set = array();
      $target_count = 0;
      
      $source_sql = "select ua.type,ua.feature_id,ua.user_id,ua.status,tt.tcversion_id from " .
          $this->db->get_table('user_assignments') . " ua join " . 
          $this->db->get_table('testplan_tcversions') .  " tt on ua.feature_id = tt.id " .
          " where ua.build_id = " . $source_build_id . 
          " order by tt.tcversion_id ";
      $source_set = $this->db->get_recordset($source_sql);
      $source_count = count($source_set, COUNT_NORMAL);
      
      $target_sql = "select id, tcversion_id from " . 
          $this->db->get_table('testplan_tcversions') . 
          " where build_id = " . $target_build_id . 
          " order by tcversion_id";
      $target_set = $this->db->get_recordset($target_sql);
      $target_count = count($source_set, COUNT_NORMAL);
      
      if ($source_count <= 0 || $target_count <= 0)
      {
          return ;
      }
      
      $idx = 0;
      $jdx = 0;
      $to_end = false;
      
      while ($idx < $target_count)
      {
          if ($to_end)
          {
              break;
          }
          
          if ($target_set[$idx]['tcversion_id'] == $source_set[$jdx]['tcversion_id'])
          {
              $add_assign_sql = " insert into " . $this->db->get_table('user_assignments') .
                  " (type, feature_id, user_id, build_id, deadline_ts, " .
                  " assigner_id, creation_ts, status) values ('" .
                  $source_set[$jdx]['type'] . "', '" .
                  $target_set[$idx]['id'] . "', '" .
                  $source_set[$jdx]['user_id'] . "', '" .
                  $target_build_id . "', " .
                  "null" . ", '" .
                  $assigner_id . "', " .
                  $creation_ts . ", '" .
                  $source_set[$jdx]['status'] . "')";
              $ua_result = $this->db->exec_query($add_assign_sql);
              $jdx++;
          }
          else if ($target_set[$idx]['tcversion_id'] > $source_set[$jdx]['tcversion_id'])
          {
              $jdx++;
          }
          else 
          {
              $idx++;
          }
          
          if ($jdx >= $source_count)
          {
              $to_end = true;
          }
      }
  }
  

  /**
   * get hash with build id and amount of test cases assigned to testers
   * 
   * @author Francisco Mancardi
   * @param mixed $buildID can be single value or array of build ID.
   */
  function getExecAssignmentsCountByBuild($buildID)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $rs = null;
    $types = $this->get_available_types();
    $execAssign = $types['testcase_execution']['id'];
      
    $sql =  "/* $debugMsg */ ".
            " SELECT COUNT(id) AS qty, build_id " . 
            " FROM ".$this->db->get_table('user_assignments')." " .
            " WHERE build_id IN ( " . implode(",",(array)$buildID) . " ) " .
            " AND type = {$execAssign} " .
            " GROUP BY build_id ";
      $rs = $this->db->fetchRowsIntoMap($sql,'build_id');
      
    return $rs;
  }




  /**
   * get hash with build id and amount of test cases assigned to testers,
   * but NOT EXECUTED.
   * 
   * 
   * @author Francisco Mancardi
   * @param mixed $buildID can be single value or array of build ID.
   */
  function getNotRunAssignmentsCountByBuild($buildID)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $rs = null;
    $types = $this->get_available_types();
    $execAssign = $types['testcase_execution']['id'];

    $sql =  "/* $debugMsg */ ".
            " SELECT count(0) as qty, UA.build_id ".
            " FROM ".$this->db->get_table('user_assignments')." UA " .
            " JOIN ".$this->db->get_table('builds')."  BU ON UA.build_id = BU.id " .
            " JOIN ".$this->db->get_table('testplan_tcversions')." TPTCV " .
            "     ON TPTCV.testplan_id = BU.testplan_id " .
            "     AND TPTCV.id = UA.feature_id " .
            " LEFT OUTER JOIN ".$this->db->get_table('executions')." E " .
            "     ON E.testplan_id = TPTCV.testplan_id " . 
            "     AND E.tcversion_id = TPTCV.tcversion_id " .
            "     AND E.platform_id = TPTCV.platform_id " .
            "     AND E.build_id = UA.build_id " .
            " WHERE UA.build_id IN ( " . implode(",",(array)$buildID) . " ) " .
            " AND E.status IS NULL " .       
            " AND type = {$execAssign} " .
            " GROUP BY UA.build_id ";
      
      $rs = $this->db->fetchRowsIntoMap($sql,'build_id');
      
    return $rs;
  }


  /**
   *
   */
  function getUsersByFeatureBuild($featureSet,$buildID,$assignmentType)
  {
    $debugMsg = 'Class:' . __CLASS__ . ' - Method: ' . __FUNCTION__;
    $rs = null;
    
    if(is_null($assignmentType) || !is_numeric($assignmentType) )
    {
      throw new Exception(__METHOD__ . ' assignmentType can not be NULL or not numeric ');  
    }
    $sql =  "/* $debugMsg */ ".
            " SELECT UA.user_id,UA.feature_id ".
            " FROM ".$this->db->get_table('user_assignments')." UA " .
            " WHERE UA.build_id = " . intval($buildID) . 
            " AND UA.feature_id IN(" . implode(",",(array)$featureSet)  . " )" .       
            " AND type = " . intval($assignmentType);
            
    $rs = $this->db->fetchRowsIntoMap($sql,'feature_id');
    return $rs;
  }
  

  /**
   * 
   * [getAssignTestCaseCountByPlanOrBuildOrModuleGroupUser description]
   * @param  [type] $module_id_list [description]
   * @param  [type] $testplan_id    [description]
   * @param  [type] $build_id       [description]
   * @return array                  key(user_id)=>column(user_id,testcase totla)
   *
   * author:jinjiacun
   * time:2017-12-30 14:39
   */
  function getAssignTestCaseCountByPlanOrBuildOrModuleGroupUser($module_id_list, 
                                                                $module_name,
                                                                $testplan_id,
                                                                $assigner_id = 0,  
                                                                $build_id = 0){

    global $tlCfg;
    $cache_dir = $tlCfg->cache_file['cache_dir'];
    $sql_template = "";
    $re_list      = array();
    $where_sql    = "";
    var_dump($module_id_list);
    $sql_template = "select count(distinct(tcversion_id)) as assign_total, assigner_id"
                    ." from ".$this->db->get_table('testplan_tcversions').' as tt inner join '
                    .$this->db->get_table('user_assignments').' as ua on tt.id=ua.feature_id'
                    ." where %s "
                    ." group by assigner_id ";
    $where_sql     .= " testplan_id = ".$testplan_id;
    $where_sql     .= ($build_id == 0)?"":" and build_id=$build_id ";
    if(count($module_id_list) > 0){
      $where_sql   .= " and tcversion_id in (".implode(",",$module_id_list).") ";  
    }
    if($assigner_id != 0){
      $where_sql   .= " and assigner_id = $assigner_id ";
    }

    $sql     = sprintf($sql_template, $where_sql);
    //var_dump($sql);die;
    file_put_contents($cache_dir."cache.sql", $sql."\r\n", FILE_APPEND);
 #   var_dump($sql);die;
    $re_list = $this->db->fetchRowsIntoMap($sql, "assigner_id");
    /*
    $tcversion_id_list = $this->getAssignTestCaseIdListByPlanOrBuildOrModuleGroupUser($module_id_list,
                                                                                    $testplan_id,
                                                                                    $assigner_id,
                                                                                    $build_id);
    if(count($re_list) > 0){
      foreach($re_list as $k => $v){
        $re_list[$k][0]['module_name'] = $module_name; 
        $re_list[$k][0]['tcversion_id_list'] = array();
        foreach($tcversion_id_list[$k] as  $v){
          $re_list[$k][0]['tcversion_id_list'][] = $v['tcversion_id'];
        }
        unset($v);        
      }
      unset($k, $v);
    }
    unset($sql);
     */

    return $re_list;
  }

   function getAssignTestCaseIdListByPlanOrBuildOrModuleGroupUser($module_id_list, 
                                                                  $testplan_id,
                                                                  $assigner_id = 0,  
                                                                  $build_id = 0){


    $sql_template = "";
    $re_list      = array();
    $where_sql    = "";

    $sql_template = "select distinct(tcversion_id), assigner_id"
                    ." from ".$this->db->get_table('testplan_tcversions').' as tt inner join '
                    .$this->db->get_table('user_assignments').' as ua on tt.id = ua.feature_id '
		    ." where %s ";
    $where_sql     = " testplan_id = $testplan_id ";
    $where_sql     .= ($build_id == 0)?'':" and tt.build_id = $build_id ";
    if(count($module_id_list) > 0){
      $where_sql   .= " and tcversion_id in (".implode(",", $module_id_list).") ";  
    }
    if($assigner_id != 0){
      $where_sql   .= " and assigner_id = $assigner_id ";
    }


    $sql     = sprintf($sql_template, $where_sql);
    $re_list = $this->db->fetchRowsIntoMap($sql, "assigner_id", 1);    
    unset($sql);

    
    return $re_list;
  }

}
