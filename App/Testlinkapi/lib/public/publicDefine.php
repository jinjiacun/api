<?php
header("Content-Type: text/html;charset=utf-8");

define('keyword_separator',';');//定义标签分隔符
/*
 * 枚举值类型定义
 */
define('ENUM_importance','importance');
define('ENUM_execution_type','execution_type');
define('ENUM_complexity','complexity');
define('ENUM_reviewed_status','reviewed_status');
/*
 * 字段长度定义
 */
define('system_length',255);
define('firstlevel_length',255);
define('secondlevel_length',255);
define('thirdlevel_length',255);
define('fourthlevel_length',255);
define('fifthlevel_length',255);

define('testcase_tc_id_length',255);
define('testcase_name_length',255);
define('testcase_stepnum_length',11);

define('testcase_extimated_exec_duration_length',6);
define('testcase_bpm_id_length',36);
define('testcase_keywords_length',100);
//======================================================
/*
 * excel表结构列定义
 */
define('FIRST_DATA_ROW',2);
define('idx_col_system','A');
define('idx_col_firstlevel','B');
define('idx_col_secondlevel','C');
define('idx_col_thirdlevel','D');
define('idx_col_fourthlevel','E');
define('idx_col_fifthlevel','F');

define('idx_col_tc_id','G');
define('idx_col_testcase_name','H');
define('idx_col_testcase_summary','I');
define('idx_col_testcase_preconditions','J');
define('idx_col_testcase_stepnum','K');
define('idx_col_testcase_stepaction','L');
define('idx_col_testcase_expectedresults','M');
define('idx_col_testcase_execution_type','N');
define('idx_col_testcase_importance','O');
define('idx_col_testcase_complexity','P');
define('idx_col_testcase_extimated_exec_duration','Q');
define('idx_col_testcase_designer','R');
define('idx_col_testcase_creation_ts','S');
define('idx_col_testcase_reviewed_status','T');
define('idx_col_testcase_reviewer_id','U');
define('idx_col_testcase_bpm_id','V');
define('idx_col_testcase_keywords','W');
