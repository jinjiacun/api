<?php
class schema_manager 
{
    var $db;
    
    /*
     function: constructor
     args :    $db => database connection
     returns:  void
     history:  add by zhouzhaoxin on 20161107
     */
    function __construct(&$db)
    {
        $this->db = &$db;
    }
    
    /*
     function: get schema name from project id
     args :    $project_id => project id
     returns:  schema name
     history:  add by zhouzhaoxin on 20161107
     */
    function get_schema($project_id)
    {
        if ($project_id > 0) 
        {
            return TL_SCHEMA_HEAD . $project_id;
        }
        else 
        {
            return "";
        }
    }
    
    /*
     function: create new schema for project
     args :    $project_id => new project id
     returns:  '' means failed, true return schema name
     history:  add by zhouzhaoxin on 20161101
     */
    function create_schema($project_id)
    {
        $schema_name = TL_SCHEMA_HEAD . $project_id;
        $sql = "CREATE DATABASE `" . $schema_name . "` CHARACTER SET utf8 ";
        if (!$this->db->exec_query($sql))
        {
            //create database failed
            return '';
        }
        
        if (!$this->create_tables($project_id)) 
        {
            //execute sql file to create tables failed
            return '';
        }
        
        return $schema_name;
    }
    
    /*
     function: create tables for project
     args :    $project_id => new project id
     returns:  void
     history:  add by zhouzhaoxin on 20161101
     */
    function create_tables($project_id)
    {
        //connect to project schema
        $schema_name = TL_SCHEMA_HEAD . $project_id;
        $sql = "use `" . $schema_name . "`";
        
        if (!$this->db->exec_query($sql))
        {
            //create database failed
            return false;
        }
        
        //execute sql to create tables
        $filepath = dirname(__FILE__) . "/project_sql/";
        $filename="tl_project_table_generator.sql";
        
        $file = $filepath . $filename;
        if ($this->process_create($project_id, $file) > 0)
        {
            return false;
        }
        else 
        {
            return true;    
        }
    }
    
    /*
     function: delete schema for project
     args :    $project_id => new project id
     returns:  false means failed, true means succeed
     history:  add by zhouzhaoxin on 20161101
     */
    function delete_schema($project_id)
    {
        $schema_name = TL_SCHEMA_HEAD . $project_id;
        $sql = "DROP DATABASE `" . $schema_name . "`";
        if (!$this->db->exec_query($sql))
        {
            //create failed
            return false;
        }
        
        return true;
    }
    
    /*
     function: create tables for schema
     args :    $project_id => new project id
               $file => file path and name, whole path
     returns:  error line number
     history:  add by zhouzhaoxin on 20161101
     */
    function process_create($project_id, $file)
    {
        //phrase file to sql array
        $contents = file($file);
        $cfil = array_filter($contents, array($this,"only_good_mysql"));
        $r2d2 = implode("", $cfil);
        $sql_array = explode(";", $r2d2);
    
        //execute sql
        $num = 0;
        $failed_num = 0;
        foreach ($sql_array as $sql_do)
        {
            // Needed because explode() maybe adds \r\n
            $sql_dodo =  trim(trim($sql_do, "\r\n "));
            if (strlen($sql_dodo) > 0)
            {
                $num = $num + 1;
                $status_ok = $this->db->exec_query($sql_dodo);
                if (!$status_ok)
                {
                    $failed_num++;
                }
            }
        }  // foreach
    
        return $failed_num;
    }
    
     /*
      function: filer disable sqls
      args :    $v => sql
      returns:  true means enable sql, false means sql no need to execute
      history:  add by zhouzhaoxin on 20161101
      */
     function only_good_mysql($v)
     {
         $use_v = true;
         $findme="#";
            
         //replace \r\n
         $v_c = trim($v, "\r\n ");
         $pos = strpos($v_c, $findme);
    
         if ($pos === false)
         {
             $use_v = true;
         }
         else
         {
             if ($pos == 0 )
             {
                 $use_v = false;
             }
         }
            
         // Empty line must not be used
         if( $use_v == true )
         {
             if ( strlen($v_c) == 0)
             {
                 $use_v = false;
             }
         }
            
         return ($use_v);
     }
}
?>