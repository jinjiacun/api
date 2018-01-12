<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later.
 *  
 * Functions related to tree menu building ONLY for test execution feature
 * This is a refactoring, this functions are included using treeMenu.inc.php
 * This is a provisory approach
 *
 *
 * @filesource  execTreeMenu.inc.php
 * @package     TestLink
 * @author      Francisco Mancardi
 * @copyright   2013,2014 TestLink community 
 * @link        http://testlink.sourceforge.net/ 
 * @uses        config.inc.php
 * @uses        const.inc.php
 *
 * @internal revisions
 * @since 1.9.13
 */

/**
 * @param $dbHandler
 * @param $menuUrl
 * @param array $context => keys tproject_id,tproject_name,tplan_id,tplan_name
 * @param $objFilters
 * @param $objOptions
 * @return array
 */

// $tproject_id,$tproject_name,$tplan_id,                  $tplan_name,

function execTree(&$dbHandler,$build_id, &$menuUrl,$context,$objFilters,$objOptions) 
{
  $chronos[] = microtime(true);

  $treeMenu = new stdClass(); 
  $treeMenu->rootnode = null;
  $treeMenu->menustring = '';
  $resultsCfg = config_get('results');
  $glueChar=config_get('testcase_cfg')->glue_character;
  
  $menustring = null;
  $tplan_tcases = null;
  $tck_map = null;
  $idx=0;
  $testCaseQty=0;
  $testCaseSet=null;
   
  $keyword_id = 0;
  $keywordsFilterType = 'Or';
  if (property_exists($objFilters, 'filter_keywords') && !is_null($objFilters->filter_keywords)) 
  {
    $keyword_id = $objFilters->filter_keywords;
    $keywordsFilterType = $objFilters->filter_keywords_filter_type;
  }
  
  $renderTreeNodeOpt = array();
  $renderTreeNodeOpt['showTestCaseID'] = config_get('treemenu_show_testcase_id');
  list($filters,$options,
       $renderTreeNodeOpt['showTestSuiteContents'],
       $renderTreeNodeOpt['useCounters'],
       $renderTreeNodeOpt['useColors'],$colorBySelectedBuild) = initExecTree($objFilters,$objOptions);

  $renderTreeNodeOpt['showTestCaseExecStatus'] = $options['showTestCaseExecStatus'];

  if( property_exists($objOptions, 'actionJS'))
  {
    if(isset($objOptions->actionJS['testproject']))
    {
      $renderTreeNodeOpt['actionJS']['testproject'] = $objOptions->actionJS['testproject'];
    }  
  }  

  $tplan_mgr = new testplan($dbHandler);
  $tproject_mgr = new testproject($dbHandler);
  $tcase_node_type = $tplan_mgr->tree_manager->node_descr_id['testcase'];

  $hash_descr_id = $tplan_mgr->tree_manager->get_available_node_types();
  $hash_id_descr = array_flip($hash_descr_id);      
  
  $tcase_prefix = $tproject_mgr->getTestCasePrefix($context['tproject_id']) . $glueChar;
  
  // remove test spec, test suites (or branches) that have ZERO test cases linked to test plan
  // 
  // IMPORTANT:
  // using 'order_cfg' => array("type" =>'exec_order',"tplan_id" => $tplan_id))
  // makes the magic of ignoring test cases not linked to test plan.
  // This unexpected bonus can be useful on export test plan as XML.
  //
  $my['options']=array('recursive' => true, 
                       'remove_empty_nodes_of_type' => $tplan_mgr->tree_manager->node_descr_id['testsuite'],
                       'order_cfg' => array("type" =>'exec_order',"tplan_id" => $context['tplan_id']));

  $my['filters'] = array('exclude_node_types' => array('testplan' => 'exclude_me','requirement_spec'=> 'exclude_me',
                                                       'requirement'=> 'exclude_me'),
                         'exclude_children_of' => array('testcase' => 'exclude_my_children',
                                                        'requirement_spec'=> 'exclude_my_children') );

  // added for filtering by toplevel testsuite
  if (isset($objFilters->filter_toplevel_testsuite) && is_array($objFilters->filter_toplevel_testsuite)) 
  {
    $my['filters']['exclude_branches'] = $objFilters->filter_toplevel_testsuite;
  }

  if (isset($objFilters->filter_custom_fields) && is_array($objFilters->filter_custom_fields))
  {
    $my['filters']['filter_custom_fields'] = $objFilters->filter_custom_fields;
  }
  
  // add by zhouzhaoxin 20160607 to filter the testcases by build not only by plan
  if (property_exists($objFilters, 'filter_build_id'))
  {
      $my['filters']['filter_build_id'] = $objFilters->filter_build_id;
  }
   
  // Document why this is needed, please  
  // 20170605 modify by zhouzhaoxin to improve performance
  $test_suites = $tplan_mgr->getAllTestsuitesByDepth($context['tproject_id'], $my['filters']);
  //$test_spec = $tplan_mgr->getSkeleton($context['tplan_id'],$build_id,$context['tproject_id'],$my['filters'],$my['options']);
  $test_spec = array();
  $test_spec['name'] = $context['tproject_name'] . " / " . $context['tplan_name'];  // To be discussed
  $test_spec['id'] = $context['tproject_id'];
  $test_spec['node_type_id'] = $hash_descr_id['testproject'];
  $test_spec['node_type'] = 'testproject';
  
  $tplan_tcases = null;
  $linkedTestCasesSet = null;

  if($test_spec)
  {
    if(is_null($filters['tcase_id']) || $filters['tcase_id'] > 0)   // 20120519 TO BE CHECKED
    {
      // Step 1 - get item set with exec status.
      // This has to scopes:
      // 1. tree coloring according exec status on (Test plan, platform, build ) context
      // 2. produce sql that can be used to reduce item set on combination with filters
      //    that can not be used on this step like:
      //    a. test cases belonging to branch with root TEST SUITE
      //    b. keyword filter on AND MODE
      //    c. execution results on other builds, any build etc
      //
      // WE NEED TO ADD FILTERING on CUSTOM FIELD VALUES, WE HAVE NOT REFACTORED
      // THIS YET.
      //
      // 20170601 modify by zhouzhaoxin to improve the performance for join table cost long time
      // 
      if( !is_null($sql2do = $tplan_mgr->getLinkedForExecTreeWithoutUA($context['tplan_id'],$filters,$options)) )
      {
        $tplan_tcv_set = $dbHandler->get_recordset($sql2do['assign']);
        $tcv_count = count($tplan_tcv_set, COUNT_NORMAL);
        $exec_build_set = $dbHandler->get_recordset($sql2do['exec_build']);
        $exec_count = count($exec_build_set, COUNT_NORMAL);
        $assign_user_set = array();
        $assigned_set = array();
       
        // get user_assign info and filter it
        if (isset($filters['assigned_to']) && !is_null($filters['assigned_to']) &&
            !in_array(TL_USER_ANYBODY,(array)$filters['assigned_to']) )
        {
            if ($sql2do['ua'] != '')
            {
                $assign_user_set = $dbHandler->fetchRowsIntoMap($sql2do['ua'], 'feature_id');
            }
            
            if ($sql2do['ua_assigned'] != '')
            {
                $assigned_set = $dbHandler->fetchRowsIntoMap($sql2do['ua_assigned'], 'feature_id');
            }

            $ff = (array)$filters['assigned_to'];
            
            if (in_array(TL_USER_NOBODY,$ff))
            {
                for ($idx = 0; $idx < $tcv_count; $idx++)
                {
                    if (array_key_exists($tplan_tcv_set[$idx]['feature_id'], $assigned_set))
                    {
                        $tplan_tcv_set[$idx]['drop_info'] = true;
                    }
                }
            
            }
            else if (in_array(TL_USER_SOMEBODY,$ff))
            {
                for ($idx = 0; $idx < $tcv_count; $idx++)
                {
                    if (!array_key_exists($tplan_tcv_set[$idx]['feature_id'], $assigned_set))
                    {
                        $tplan_tcv_set[$idx]['drop_info'] = true;
                    }
                }
            }
            else
            {
                if( $my['options']['include_unassigned'] )
                {
                    for ($idx = 0; $idx < $tcv_count; $idx++)
                    {
                        if (!array_key_exists($tplan_tcv_set[$idx]['feature_id'], $assign_user_set)
                            && array_key_exists($tplan_tcv_set[$idx]['feature_id'], $assigned_set))
                        {
                            $tplan_tcv_set[$idx]['drop_info'] = true;
                        }
                    }
                }
                else
                {
                    if (count($assign_user_set, COUNT_NORMAL) <= 0)
                    {
                        for ($idx = 0; $idx < $tcv_count; $idx++)
                        {
                            $tplan_tcv_set[$idx]['drop_info'] = true;
                        }
                    }
                    else 
                    {
                        for ($idx = 0; $idx < $tcv_count; $idx++)
                        {
                            if (!array_key_exists($tplan_tcv_set[$idx]['feature_id'], $assign_user_set))
                            {
                                $tplan_tcv_set[$idx]['drop_info'] = true;
                            }
                        }
                    }
                }
            }
        }
  
        // add execute status and filter it by bug id and execute status
        $idx = 0;
        $jdx = 0;
        $to_end = false;
        while ($idx < $tcv_count)
        {
            // get recordset all order by tcversion id, so can loop once to charge status
            if ($tplan_tcv_set[$idx]['drop_info'])
            {
                // to deleted, no need to add exec info
                $idx++;
            }
            
            if ($to_end)
            {
                $tplan_tcv_set[$idx]['exec_status'] = 'n';
                $idx++;
                continue;
            }
            
            if ($tplan_tcv_set[$idx]['tcversion_id'] == $exec_build_set[$jdx]['tcversion_id'])
            {
                $tplan_tcv_set[$idx]['exec_status'] = $exec_build_set[$jdx]['status'];
                $idx++;
                $jdx++;
            }
            else if ($tplan_tcv_set[$idx]['tcversion_id'] > $exec_build_set[$jdx]['tcversion_id']
                && $jdx < $exec_count)
            {
                $jdx++;
            }
            else
            {
                $tplan_tcv_set[$idx]['exec_status'] = 'n';
                $idx++;
            }
            
            if ($jdx >= $exec_count)
            {
                //need to add process for index of exec_build
                $to_end = true;
            }
        }
        
        if( isset($filters['bug_id']) && !is_null($filters['bug_id']) )
        {
            $exec_set = $dbHandler->fetchRowsIntoMap($sql2do['exec'], 'tcversion_id');
            for ($idx = 0; $idx < $tcv_count; $idx++)
            {
                if (!array_key_exists($tplan_tcv_set[$idx]['tcversion_id'], $exec_set))
                {
                    $tplan_tcv_set[$idx]['drop_info'] = true;
                }
            }
        }
        
        // if filter include all status item, no need to filter it
        $targetExecStatus = (array)(isset($objFilters->filter_result_result) ? $objFilters->filter_result_result : null);
        if( !is_null($targetExecStatus) && count($targetExecStatus) > 0 && (!in_array($resultsCfg['status_code']['all'], $targetExecStatus)) )
        {
            $dummy = $targetExecStatus;
        
            for ($idx = 0; $idx < $tcv_count; $idx++)
            {
                if( !in_array($tplan_tcv_set[$idx]['exec_status'],$dummy) )
                {
                    $tplan_tcv_set[$idx]['drop_info'] = true;
                }
            }
        }
        
        // rebuild record
        $setTestCaseStatus = array();
        for ($idx = 0; $idx < $tcv_count; $idx++)
        {
            if (!$tplan_tcv_set[$idx]['drop_info'])
            {
                if (isset($setTestCaseStatus[$tplan_tcv_set[$idx]['tsuite_id']]))
                {
                    $setTestCaseStatus[$tplan_tcv_set[$idx]['tsuite_id']][$tplan_tcv_set[$idx]['tcase_id']] = $tplan_tcv_set[$idx];
                }
                else 
                {
                    $setTestCaseStatus[$tplan_tcv_set[$idx]['tsuite_id']] = array();
                    $setTestCaseStatus[$tplan_tcv_set[$idx]['tsuite_id']][$tplan_tcv_set[$idx]['tcase_id']] = $tplan_tcv_set[$idx];     
                }
            }
        }
        
        $tplan_tcases = $setTestCaseStatus;
      }
    }   

    if( !is_null($tplan_tcases) )
    {
      // OK, now we need to work on status filters
      // if "any" was selected as filtering status, don't filter by status
      /* 20170606 hide by zhouzhaoxin for exec status filter go to last part, cf field filter menu hide
      $targetExecStatus = (array)(isset($objFilters->filter_result_result) ? $objFilters->filter_result_result : null);
      if( !is_null($targetExecStatus) && (!in_array($resultsCfg['status_code']['all'], $targetExecStatus)) ) 
      {
        applyStatusFilters($context['tplan_id'],$tplan_tcases,$objFilters,$tplan_mgr,$resultsCfg['status_code']);       
      }

      if (isset($my['filters']['filter_custom_fields']) && isset($test_spec['childNodes']))
      {
        // need to separate cf 4 design that cf 4 testplan_design.
        // Here we ONLY use cf 4 design
        $cfx = cfForDesign($dbHandler,$my['filters']['filter_custom_fields']);
        if( !is_null($cfx) )
        {
          $test_spec['childNodes'] = filter_by_cf_values($dbHandler,$test_spec['childNodes'],$cfx,$hash_descr_id);
        }  
      }
      */
      // ATTENTION: sometimes we use $my['options'], other $options
      $pnOptions = array('hideTestCases' => $options['hideTestCases'], 'viewType' => 'executionTree');
      $pnFilters = null;  
      $renderTreeNodeOpt['hideTestCases'] = $options['hideTestCases'];
      $testcase_counters = prepareExecTreeNodeFast($dbHandler,$test_suites, $test_spec,
          $hash_id_descr,$menuUrl,$tcase_prefix,$tplan_tcases,$pnFilters,$pnOptions, $renderTreeNodeOpt);
    }
    else
    {
      $tplan_tcases = array();
      if (isset($test_spec['childNodes']))
      {
          unset($test_spec['childNodes']);
      }

      $testcase_counters = helperInitCounters();
      foreach($testcase_counters as $key => $value)
      {
        $test_spec[$key] = $testcase_counters[$key];
      }
    }  

    /* modify by zhouzhaoxin 20170607 steps add to prepareExecTreeNodeFast to improve performance
    $renderTreeNodeOpt['hideTestCases'] = $options['hideTestCases'];
    $renderTreeNodeOpt['tc_action_enabled'] = 1;

    // CRITIC: renderExecTreeNode() WILL MODIFY $tplan_tcases, can empty it completely
    $linkedTestCasesSet = array_keys((array)$tplan_tcases);
    renderExecTreeNodeFast(1,$test_suites,$test_spec,$tplan_tcases,$hash_id_descr,$menuUrl,$tcase_prefix,$renderTreeNodeOpt);
    */
  }
  
  $treeMenu->rootnode=new stdClass();
  $treeMenu->rootnode->name=$test_spec['text'];
  $treeMenu->rootnode->id=$test_spec['id'];
  if (isset($test_spec['leaf']))
  {
      $treeMenu->rootnode->leaf=$test_spec['leaf'];
  }
  else
  {
      $treeMenu->rootnode->leaf=false;
  }
  
  $treeMenu->rootnode->text=$test_spec['text'];
  $treeMenu->rootnode->position=$test_spec['position'];     
  $treeMenu->rootnode->href=$test_spec['href'];
  
  // Change key ('childNodes')  to the one required by Ext JS tree.
  $menustring = '';
  if(isset($test_spec['childNodes'])) 
  {
    $menustring = str_ireplace('childNodes', 'children', json_encode($test_spec['childNodes']));
  }
   
  // Remove null elements (Ext JS tree do not like it ).
  // :null happens on -> "children":null,"text" that must become "children":[],"text"
  // $menustring = str_ireplace(array(':null',',null','null,'),array(':[]','',''), $menustring); 
  // $menustring = str_ireplace(array(':null',',null','null,','null'),array(':[]','','',''), $menustring); 
  //   
  // 20140928 - order of replace is CRITIC
  $target = array(',"' . REMOVEME .'"','"' . REMOVEME . '",');
  $menustring = str_ireplace($target,array('',''), $menustring); 

  $target = array(':' . REMOVEME,'"' . REMOVEME . '"');
  $menustring = str_ireplace($target,array(':[]',''), $menustring); 

  $treeMenu->menustring = $menustring;
  return array($treeMenu, $linkedTestCasesSet);
}


/*
 *
 *
 */
function initExecTree($filtersObj,$optionsObj)
{
  $filters = array();
  $options = array();
  
  $buildSettingsPanel = null;
  $buildFiltersPanel = isset($filtersObj->filter_result_build) ? $filtersObj->filter_result_build : null;
  $build2filter_assignments = is_null($buildFiltersPanel) ? $buildSettingsPanel : $buildFiltersPanel;

  $keymap = array('tcase_id' => 'filter_tc_id', 'assigned_to' => 'filter_assigned_user',
                  'platform_id' => 'setting_platform', 'exec_type' => 'filter_execution_type',
                  'urgencyImportance' => 'filter_priority', 'tcase_name' => 'filter_testcase_name',
                  'cf_hash' => 'filter_custom_fields', 'build_id' => array('setting_build','build_id'),
                  'bug_id' => 'filter_bugs');
  
  if( property_exists($optionsObj,'buildIDKeyMap') && !is_null($filtersObj->filter_result_build) )
  {
    $keymap['build_id'] = $optionsObj->buildIDKeyMap;
  }
  
  foreach($keymap as $key => $prop)
  {
    if( is_array($prop) )
    {
      foreach($prop as $tryme)
      {
        if( isset($filtersObj->$tryme) )
        {
          $filters[$key] = $filtersObj->$tryme;
          break;
        }
        else
        {
          $filters[$key] = null;
        }
      } 
    }
    else
    {
      $filters[$key] = isset($filtersObj->$prop) ? $filtersObj->$prop : null; 
    } 
  }


  $filters['keyword_id'] = 0;
  $filters['keyword_filter_type'] = 'Or';
  if ( !is_null($filtersObj) && property_exists($filtersObj, 'filter_keywords') && !is_null($filtersObj->filter_keywords)) 
  {
    $filters['keyword_id'] = $filtersObj->filter_keywords;
    $filters['keyword_filter_type'] = $filtersObj->filter_keywords_filter_type;
  }


  $options['hideTestCases'] = isset($optionsObj->hideTestCases) ?
                                    $optionsObj->hideTestCases : false;

  $options['include_unassigned'] = isset($filtersObj->filter_assigned_user_include_unassigned) ?
                                         $filtersObj->filter_assigned_user_include_unassigned : false;

  // useful when using tree on set urgent test cases
  $options['allow_empty_build'] = isset($optionsObj->allow_empty_build) ?
                                    $optionsObj->allow_empty_build : false;


  // NOT CLEAR what to do
  // $status = isset($filters->filter_result_result) ? $filters->filter_result_result : null;
  $show_testsuite_contents = isset($filtersObj->show_testsuite_contents) ? 
                             $filtersObj->show_testsuite_contents : true;

  
  $useCounters=isset($optionsObj->useCounters) ? $optionsObj->useCounters : null;
  $useColors=isset($optionsObj->useColours) ? $optionsObj->useColours : null;
  $colorBySelectedBuild = isset($optionsObj->testcases_colouring_by_selected_build) ? 
                          $optionsObj->testcases_colouring_by_selected_build : null;

  $options['tc_action_enabled'] = isset($optionsObj->tc_action_enabled) ?  $optionsObj->tc_action_enabled : true;
  $options['showTestCaseExecStatus'] = isset($optionsObj->showTestCaseExecStatus) ?  $optionsObj->showTestCaseExecStatus : true;

  // add by zhouzhaoxin 20160707 for filter single version testcases not show on update tc page
  if (isset($filtersObj) && property_exists($filtersObj, 'filtersingletc'))
  {
      $filters['filtersingletc'] = $filtersObj->filtersingletc;
  }
  
  return array($filters,$options,$show_testsuite_contents,$useCounters,$useColors,$colorBySelectedBuild);
}


/**
 *
 * @returns test_counters map. key exec_status
 */
function prepareExecTreeNodeFast(&$db, &$node, &$test_spec, 
    $hash_id_descr,$linkto,$testCasePrefix,&$tplan_tcases = null,$filters=null, $options=null, $opt=null)
{
    $resultsCfg = config_get('results');
    $status_descr_list = array_keys($resultsCfg['status_code']);
    $status_descr_list[] = 'testcase_count';
    $tcase_counters = array_fill_keys($status_descr_list, 0);
    
    $doColouringOn = array();
    $doColouringOn['testcase'] = 1;
    $doColouringOn['counters'] = 1;
    if( !is_null($opt['useColors']) )
    {
        $doColouringOn['testcase'] = $opt['useColors']->testcases;
        $doColouringOn['counters'] = $opt['useColors']->counters;
    }
    
    $l18n = array();
    $pf = array();
    $cssClasses = array();
    $status_descr_code = $resultsCfg['status_code'];
    foreach($resultsCfg['status_label'] as $key => $value)
    {
        $l18n[$status_descr_code[$key]] = lang_get($value);
        $cssClasses[$status_descr_code[$key]] = $doColouringOn['testcase'] ? ('class="light_' . $key . '"') : '';
    }
    
    $pf['testsuite'] = $opt['hideTestCases'] ? 'TPLAN_PTS' : ($opt['showTestSuiteContents'] ? 'STS' : null);
    $pf['testproject'] = $opt['hideTestCases'] ? 'TPLAN_PTP' : 'SP';
    
    if( isset($opt['actionJS']) )
    {
        if( isset($opt['actionJS']['testproject']) )
        {
            $pf['testproject'] = $opt['actionJS']['testproject'];
        }
    
        if( isset($opt['actionJS']['testsuite']) )
        {
            $pf['testsuite'] = $opt['actionJS']['testsuite'];
        }
    }
    
    // manage defaults
    $opt['showTestCaseExecStatus'] = isset($opt['showTestCaseExecStatus']) ? $opt['showTestCaseExecStatus'] : true;
    $opt['nodeHelpText'] = isset($opt['nodeHelpText']) ? $opt['nodeHelpText'] : array();
    
    $depth_cnt = count($node, COUNT_NORMAL);
    if (!isset($tplan_tcases))
    {
        $tcase_counters = array_fill_keys($status_descr_list, 0);
        return $tcase_counters;
    }
    
    for ($depth = $depth_cnt; $depth > 0; $depth--)
    {
        if (!isset($node[$depth]))
        {
            continue;
        }
        
        foreach ($node[$depth] as $tsuite_id => $tsuite_info)
        {
            $tcase_counters = array_fill_keys($status_descr_list, 0);
            
            //first count child testsuite info(all testcase and by status)
            if (isset($node[$depth][$tsuite_id]['childNodes']) 
                && count($node[$depth][$tsuite_id]['childNodes'], COUNT_NORMAL) > 0)
            {               
                // if testsuite has child testsuite ,first count child suite info
                foreach ($node[$depth][$tsuite_id]['childNodes'] as $id => $child_suite)
                {
                    foreach ($tcase_counters as $key => $value)
                    {
                        if (isset($child_suite[$key]))
                        {
                            $tcase_counters[$key] += $child_suite[$key];
                        }
                    }
                }
            }
            
            //then count child testcase info(all testcase and by status)
            if (isset($tplan_tcases[$tsuite_id]))
            {
                $node[$depth][$tsuite_id]['node_type'] = 'testsuite';
                $node[$depth][$tsuite_id]['node_table'] = 'testsuites';            
                
                foreach ($tplan_tcases[$tsuite_id] as $tcase_id => $tcase_info)
                {
                    $tcase_info['leaf'] = true;
                    
                    // get testcase counter by status and all
                    if( isset($tcase_info['exec_status']) )
                    {
                        $tc_status_descr = $resultsCfg['code_status'][$tcase_info['exec_status']];
                    }
                    else
                    {
                        $tc_status_descr = "not_run";
                    }
                    $tcase_counters[$tc_status_descr]++;
                    $tcase_counters['testcase_count']++;
                    
                    $node_type = 'testcase';
                    $name = htmlspecialchars($tcase_info['name'], ENT_QUOTES);
                    
                    // custom Property that will be accessed by EXT-JS using node.attributes
                    $tcase_info['testlink_node_name'] = $name;
                    $tcase_info['testlink_node_type'] = $node_type;
                    
                    $tcase_info['text'] = "<span ";

                    if($opt['showTestCaseExecStatus'])
                    {
                        $status_code = $tcase_info['exec_status'];
                        $tcase_info['text'] .= "{$cssClasses[$status_code]} " . '  title="' .  $l18n[$status_code] .
                        '" alt="' . $l18n[$status_code] . '">';
                    }
                
                    if($opt['showTestCaseID'])
                    {
                        // optimizable
                        $tcase_info['text'] .= "<b>" . htmlspecialchars($testCasePrefix . $tcase_info['external_id']) . "</b>:";
                    }         
                    $tcase_info['text'] .= "{$name}</span>";

                    $tcase_info['position'] = isset($tcase_info['node_order']) ? $tcase_info['node_order'] : 0;
                    $pfn = "ST({$tcase_info['id']},{$tcase_info['tcversion_id']})";
                    $tcase_info['href'] = "javascript:{$pfn}";                 
                    
                    // add leaf node info to parent
                    $node[$depth][$tsuite_id]['childNodes'][] = $tcase_info;
                }   
            }
            
            foreach($tcase_counters as $key => $value)
            {
                $node[$depth][$tsuite_id][$key] = $tcase_counters[$key];
            }
            
            if (count($node[$depth][$tsuite_id]['childNodes'], COUNT_NORMAL) <= 0)
            {
                // no child, need to remove it
                unset($node[$depth][$tsuite_id]);
            }
            else 
            {
                // existed node add node interface info
                $node[$depth][$tsuite_id]['leaf'] = false;               
                $node_type = 'testsuite';
                $name = htmlspecialchars($tsuite_info['name'], ENT_QUOTES);
                 
                // custom Property that will be accessed by EXT-JS using node.attributes
                $node[$depth][$tsuite_id]['testlink_node_name'] = $name;
                $node[$depth][$tsuite_id]['testlink_node_type'] = $node_type;
                
                $pfn = !is_null($pf[$node_type]) ? $pf[$node_type] . "({$tsuite_info['id']})" : null;
                
                $testcase_count = isset($node[$depth][$tsuite_id]['testcase_count']) ? $node[$depth][$tsuite_id]['testcase_count'] : 0;
                $node[$depth][$tsuite_id]['text'] = $name ." (" . $testcase_count . ")";
                if($opt['useCounters'])
                {
                    $node[$depth][$tsuite_id]['text'] .= create_counters_info($node[$depth][$tsuite_id],$doColouringOn['counters']);
                }
                
                if( isset($opt['nodeHelpText'][$node_type]) )
                {
                    $node[$depth][$tsuite_id]['text'] = '<span title="' . $opt['nodeHelpText'][$node_type] . '">' . $node[$depth][$tsuite_id]['text'] . '</span>';
                }
                
                $node[$depth][$tsuite_id]['position'] = isset($tsuite_info['node_order']) ? $tsuite_info['node_order'] : 0;
                $node[$depth][$tsuite_id]['href'] = is_null($pfn)? '' : "javascript:{$pfn}";
             
                //add node count and info to parent
                if ($depth > 1)
                {
                    $parent_id = $node[$depth][$tsuite_id]['parent_id'];
                    if (isset($node[$depth - 1][$parent_id]))
                    {
                        if (!isset($node[$depth - 1][$parent_id]['childNodes']))
                        {
                            $node[$depth - 1][$parent_id]['childNodes'] = array();
                        }

                        $node[$depth - 1][$parent_id]['childNodes'][] = $node[$depth][$tsuite_id];
                    }
                    else 
                    {
                        tLog("error parent_id " . $parent_id . " for node with id " . $node[$depth][$tsuite_id]['id']);
                    }
                }
            }
            
        }
    }
    
    // get root node counters
    $tcase_counters = array_fill_keys($status_descr_list, 0);
    $depth = 1;
    if (isset($node[$depth]) && count($node[$depth]) > 0)
    {
        foreach ($node[$depth] as $tsuite_id => $tsuite_info)
        {
            foreach($tcase_counters as $key => $value)
            {
                if (isset($tsuite_info[$key]))
                {
                    $tcase_counters[$key] += $tsuite_info[$key];
                }
            }
            $test_spec['childNodes'][] = $node[$depth][$tsuite_id];
        }
    }
    
    foreach($tcase_counters as $key => $value)
    {
        $test_spec[$key] = $tcase_counters[$key];
    }
    
    $node_type = $hash_id_descr[$test_spec['node_type_id']];
    $name = htmlspecialchars($test_spec['name'], ENT_QUOTES);
     
    // custom Property that will be accessed by EXT-JS using node.attributes
    $test_spec['testlink_node_name'] = $name;
    $test_spec['testlink_node_type'] = $node_type;
    
    $pfn = !is_null($pf[$node_type]) ? $pf[$node_type] . "({$test_spec['id']})" : null;
    $testcase_count = isset($test_spec['testcase_count']) ? $test_spec['testcase_count'] : 0;
    $test_spec['text'] = $name ." (" . $testcase_count . ")";
    if($opt['useCounters'])
    {
        $test_spec['text'] .= create_counters_info($test_spec,$doColouringOn['counters']);
    }
    
    if( isset($opt['nodeHelpText'][$node_type]) )
    {
        $test_spec['text'] = '<span title="' . $opt['nodeHelpText'][$node_type] . '">' . $test_spec['text'] . '</span>';
    }
    
    $test_spec['position'] = isset($test_spec['node_order']) ? $test_spec['node_order'] : 0;
    $test_spec['href'] = is_null($pfn)? '' : "javascript:{$pfn}";
    
    return $tcase_counters;
}


/**
 *
 * @returns test_counters map. key exec_status
 */
function prepareExecTreeNode(&$db,&$node,&$map_node_tccount,&$tplan_tcases = null,
                             $filters=null, $options=null)
{
  
  static $status_descr_list;
  static $debugMsg;
  static $my;
  static $resultsCfg;

  $tpNode = null;
  if (!$debugMsg)
  {
    $debugMsg = 'Class: ' . __CLASS__ . ' - ' . 'Method: ' . __FUNCTION__ . ' - ';

    $resultsCfg = config_get('results');
    $status_descr_list = array_keys($resultsCfg['status_code']);
    $status_descr_list[] = 'testcase_count';

    $my = array();
    $my['options'] = array('hideTestCases' => 0);
    $my['options'] = array_merge($my['options'], (array)$options);


    $my['filters'] = array();
    $my['filters'] = array_merge($my['filters'], (array)$filters);

  }
    
  $tcase_counters = array_fill_keys($status_descr_list, 0);
  $node_type = isset($node['node_type']) ? $node['node_type'] : null;

  // Important Development Notes
  // It can seems that analisys of node type can be done in
  // any order, but IS NOT TRUE.
  // This is because structure of $node element.
  // Then BE VERY Carefull if you plan to refactor, to avoid unexpected
  // side effects.
  // 
  if($node_type == 'testcase')
  {

    $tpNode = isset($tplan_tcases[$node['id']]) ? $tplan_tcases[$node['id']] : null;
    $tcase_counters = array_fill_keys($status_descr_list, 0);

    if( is_null($tpNode) )
    {     
      // Dev Notes: when this happens ?
      // 1. two or more platforms on test plan (PLAT-A,PLAT-B)
      // 2. TC-1X => on PLAT-A
      //    TC-1Y => on PLAT-B
      // 3. Build Exec Tree on PLAT-A
      // 4. TC-1Y will match condition
      //
      // 5. Build Exec Tree on PLAT-B
      // 6. TC-1X will match condition
      //
      // What if Test plan has NO PLATFORMS ?
      // This piece of code will not be executed
      //
      unset($tplan_tcases[$node['id']]);
      // $node = null;
      $node = REMOVEME;
    } 
    else 
    {

      if( isset($tpNode['exec_status']) )
      {
        $tc_status_descr = $resultsCfg['code_status'][$tpNode['exec_status']];   
      }
      else
      {
        $tc_status_descr = "not_run";
      }
      $tcase_counters[$tc_status_descr] = $tcase_counters['testcase_count'] = ($node ? 1 : 0);

      if ( $my['options']['hideTestCases'] )
      {
        // $node = null;
        $node = REMOVEME;
      }
      else
      {
        $node['tcversion_id'] = $tpNode['tcversion_id'];    
        $node['version'] = $tpNode['version'];    
        $node['external_id'] = $tpNode['external_id'];    

        unset($node['childNodes']);
        $node['leaf']=true;
      }  

    }
  } 
  else 
  {
    if (isset($node['childNodes']) && is_array($node['childNodes']))
    {
      // node is a Test Suite or Test Project
      $childNodes = &$node['childNodes'];
      $childNodesQty = count($childNodes);
      for($idx = 0;$idx < $childNodesQty ;$idx++)
      {
        $current = &$childNodes[$idx];
        // I use set an element to null to filter out leaf menu items
        if(is_null($current))
        {
          $childNodes[$idx] = REMOVEME;  // 19
          continue;
        }
        
        $counters_map = prepareExecTreeNode($db,$current,$map_node_tccount,$tplan_tcases,
                                            $my['filters'],$my['options']);
        foreach($counters_map as $key => $value)
        {
          $tcase_counters[$key] += $counters_map[$key];   
        }  
      }

      foreach($tcase_counters as $key => $value)
      {
        $node[$key] = $tcase_counters[$key];
      }  
      
      // hhhm is this test needed ? Why ?
      if (isset($node['id']))
      {
        $map_node_tccount[$node['id']] = array( 'testcount' => $node['testcase_count'],
                                                'name' => $node['name']);
      }

      // need to check is this check can be TRUE on some situation
      // After mail on 20140124, it seems is useless.
      // This piece is useful only when you use platforms.
      // Use Case
      // Test plan with 2 platforms - QQ, WW
      // TC-1A -> platform QQ
      // NO TEST CASE assigned to test plan with platform WW
      // User wants to see execution tree with platform WW
      // You are going to enter here because $tplan_tcases is NULL
      // 
      if( !is_null($tplan_tcases) && !$tcase_counters['testcase_count'] && ($node_type != 'testproject'))
      {
        // echo 'nullfying-';
        // $node = null;
        $node = REMOVEME;
      }
    }
    else if ($node_type == 'testsuite')
    {
      // Empty test suite
      $map_node_tccount[$node['id']] = array( 'testcount' => 0,'name' => $node['name']);
      
      // If is an EMPTY Test suite and we have added filtering conditions, We will destroy it.
      if (!is_null($tplan_tcases))
      {
        // $node = null;
        $node = REMOVEME;
      } 
    }
  }  

  return $tcase_counters;
}


/**
 *
 */
function applyStatusFilters($tplan_id,&$items2filter,&$fobj,&$tplan_mgr,$statusCfg)
{
  $fm = config_get('execution_filter_methods');
  $methods = $fm['status_code'];

  $ffn = array($methods['any_build'] => 'filterStatusSetAtLeastOneOfActiveBuilds',
             $methods['all_builds'] => 'filterStatusSetAllActiveBuilds',
             $methods['specific_build'] => 'filter_by_status_for_build',
             $methods['current_build'] => 'filter_by_status_for_build',
             $methods['latest_execution'] => 'filter_by_status_for_latest_execution');
  
  $f_method = isset($fobj->filter_result_method) ? $fobj->filter_result_method : null;
  $f_result = isset($fobj->filter_result_result) ? $fobj->filter_result_result : null;
  $f_result = (array)$f_result;

  // die();
  
  // if "any" was selected as filtering status, don't filter by status
  if (in_array($statusCfg['all'], $f_result)) 
  {
    $f_result = null;
    return $items2filter; // >>---> Bye!
  }

  $filter_done = !is_null($f_method);
  if ($filter_done)
  {
    $logMsg = 'FILTER METHOD:' . $f_method . '::' .  $ffn[$f_method];
    tLog($logMsg,'DEBUG');
    
    // special case: 
    // when filtering by "current build", we set the build to filter with
    // to the build chosen in settings instead of the one in filters
    //
    // Need to understand why we need to do this 'dirty/brute force initialization'
    if ($f_method == $methods['current_build']) 
    {
      $fobj->filter_result_build = $fobj->setting_build;
    }
    
    $items = $ffn[$f_method]($tplan_mgr, $items2filter, $tplan_id, $fobj);
  }

  return $filter_done ? $items : $items2filter; 
}



/*
 * Provides Test suites and test cases
 * @used-by Assign Test Execution Feature
 *
 * @internal revisions
 * modify by zhouzhaoxin 20161212 to add build id for assign by builds not plan
 */
function testPlanTree(&$dbHandler,&$menuUrl,$tproject_id,$tproject_name,$tplan_id,
                      $tplan_name,$build_id,$objFilters,$objOptions) 
{
  $debugMsg = ' - Method: ' . __FUNCTION__;
  $chronos[] = $tstart = microtime(true);

  $treeMenu = new stdClass(); 
  $treeMenu->rootnode = null;
  $treeMenu->menustring = '';
  

  $resultsCfg = config_get('results');
  $glueChar=config_get('testcase_cfg')->glue_character;
  $menustring = null;
  $tplan_tcases = null;

  $renderTreeNodeOpt = null;
  $renderTreeNodeOpt['showTestCaseID'] = config_get('treemenu_show_testcase_id');

  list($filters,$options,
       $renderTreeNodeOpt['showTestSuiteContents'],
       $renderTreeNodeOpt['useCounters'],
       $renderTreeNodeOpt['useColors'],$colorBySelectedBuild) = initExecTree($objFilters,$objOptions);

  $tplan_mgr = new testplan($dbHandler);
  $tproject_mgr = new testproject($dbHandler);
  $tree_manager = $tplan_mgr->tree_manager;
  $tcase_node_type = $tree_manager->node_descr_id['testcase'];
  
  $hash_descr_id = $tree_manager->get_available_node_types();
  $hash_id_descr = array_flip($hash_descr_id);      
  $tcase_prefix = $tproject_mgr->getTestCasePrefix($tproject_id) . $glueChar;
  
  $nt2exclude = array('testplan' => 'exclude_me',
                      'requirement_spec'=> 'exclude_me',
                      'requirement'=> 'exclude_me');
  
  $nt2exclude_children = array('testcase' => 'exclude_my_children',
                               'requirement_spec'=> 'exclude_my_children');
  // remove test spec, test suites (or branches) that have ZERO test cases linked to test plan
  // 
  // IMPORTANT:
  // using 'order_cfg' => array("type" =>'exec_order',"tplan_id" => $tplan_id))
  // makes the magic of ignoring test cases not linked to test plan.
  // This unexpected bonus can be useful on export test plan as XML.
  //
  $my['options']=array('recursive' => true, 'remove_empty_nodes_of_type' => $tree_manager->node_descr_id['testsuite'],
                       'order_cfg' => array("type" =>'exec_order',"tplan_id" => $tplan_id),
                       'hideTestCases' => $options['hideTestCases'],'tc_action_enabled' => $options['tc_action_enabled'],
                       'showTestCaseExecStatus' => $options['showTestCaseExecStatus']);
                         
  $my['filters'] = array('exclude_node_types' => $nt2exclude,
                         'exclude_children_of' => $nt2exclude_children);
  
  if (isset($objFilters->filter_toplevel_testsuite) && is_array($objFilters->filter_toplevel_testsuite)) 
  {
    $my['filters']['exclude_branches'] = $objFilters->filter_toplevel_testsuite;
  }

  if (isset($objFilters->filter_custom_fields) && is_array($objFilters->filter_custom_fields))
  {
    $my['filters']['filter_custom_fields'] = $objFilters->filter_custom_fields;
  }

  if( property_exists($objOptions, 'actionJS') )
  {
    foreach(array('testproject','testsuite','testcase') as $nk)
    {  
      if(isset($objOptions->actionJS[$nk]))
      {
        $renderTreeNodeOpt['actionJS'][$nk] = $objOptions->actionJS[$nk];
      }
    }  
  }  

  if( property_exists($objOptions, 'nodeHelpText') )
  {
    foreach(array('testproject','testsuite','testcase') as $nk)
    {  
      if(isset($objOptions->nodeHelpText[$nk]))
      {
        $renderTreeNodeOpt['nodeHelpText'][$nk] = $objOptions->nodeHelpText[$nk];
      }
    }  
  }  

  // add by zhouzhaoxin 20160607 to filter the testcases by build not only by plan
  if (isset($objFilters) && property_exists($objFilters, 'filter_build_id'))
  {
      $my['filters']['filter_build_id'] = $objFilters->filter_build_id;
      $filters['build_id'] = $objFilters->filter_build_id;
  }

  if (isset($build_id) && $build_id > 0)
  {
      $filters['build_id'] = $build_id;
  }
      
  $test_suites = $tplan_mgr->getAllTestsuitesByDepth($tproject_id, $my['filters']);
  $test_spec = array();
  //$test_spec = $tplan_mgr->getSkeleton($tplan_id,$build_id,$tproject_id,$my['filters'],$my['options']);
  $test_spec['name'] = $tproject_name . " / " . $tplan_name;  // To be discussed
  $test_spec['id'] = $tproject_id;
  $test_spec['node_type_id'] = $hash_descr_id['testproject'];
  $test_spec['node_type'] = 'testproject';
  $map_node_tccount = array();
  $tplan_tcases = array();
  $keys = array();
  
  if($test_spec)
  {
      if(is_null($filters['tcase_id']) || $filters['tcase_id'] > 0)   // 20120519 TO BE CHECKED
      {
          // Step 1 - get item set with exec status.
          // This has to scopes:
          // 1. tree coloring according exec status on (Test plan, platform, build ) context
          // 2. produce sql that can be used to reduce item set on combination with filters
          //    that can not be used on this step like:
          //    a. test cases belonging to branch with root TEST SUITE
          //    b. keyword filter on AND MODE
          //    c. execution results on other builds, any build etc
          //
          // WE NEED TO ADD FILTERING on CUSTOM FIELD VALUES, WE HAVE NOT REFACTORED
          // THIS YET.
          //
          // 20170601 modify by zhouzhaoxin to improve the performance for join table cost long time
          //
          if( !is_null($sql2do = $tplan_mgr->getLinkedForExecTreeWithoutUA($tplan_id,$filters,$options)) )
          {
              $tplan_tcv_set = $dbHandler->get_recordset($sql2do['assign']);
              $tcv_count = count($tplan_tcv_set, COUNT_NORMAL);
              $exec_build_set = $dbHandler->get_recordset($sql2do['exec_build']);
              $exec_count = count($exec_build_set, COUNT_NORMAL);
              $assign_user_set = array();
              $assigned_set = array();
               
              // get user_assign info and filter it
              if (isset($filters['assigned_to']) && !is_null($filters['assigned_to']) &&
                  !in_array(TL_USER_ANYBODY,(array)$filters['assigned_to']) )
              {
                  if ($sql2do['ua'] != '')
                  {
                      $assign_user_set = $dbHandler->fetchRowsIntoMap($sql2do['ua'], 'feature_id');
                  }
  
                  if ($sql2do['ua_assigned'] != '')
                  {
                      $assigned_set = $dbHandler->fetchRowsIntoMap($sql2do['ua_assigned'], 'feature_id');
                  }
  
                  $ff = (array)$filters['assigned_to'];
  
                  if (in_array(TL_USER_NOBODY,$ff))
                  {
                      for ($idx = 0; $idx < $tcv_count; $idx++)
                      {
                          if (array_key_exists($tplan_tcv_set[$idx]['feature_id'], $assigned_set))
                          {
                              $tplan_tcv_set[$idx]['drop_info'] = true;
                          }
                      }
  
                  }
                  else if (in_array(TL_USER_SOMEBODY,$ff))
                  {
                      for ($idx = 0; $idx < $tcv_count; $idx++)
                      {
                          if (!array_key_exists($tplan_tcv_set[$idx]['feature_id'], $assigned_set))
                          {
                              $tplan_tcv_set[$idx]['drop_info'] = true;
                          }
                      }
                  }
                  else
                  {
                      if( $my['options']['include_unassigned'] )
                      {
                          for ($idx = 0; $idx < $tcv_count; $idx++)
                          {
                              if (!array_key_exists($tplan_tcv_set[$idx]['feature_id'], $assign_user_set)
                                  && array_key_exists($tplan_tcv_set[$idx]['feature_id'], $assigned_set))
                              {
                                  $tplan_tcv_set[$idx]['drop_info'] = true;
                              }
                          }
                      }
                      else
                      {
                          if (count($assign_user_set, COUNT_NORMAL) <= 0)
                          {
                              for ($idx = 0; $idx < $tcv_count; $idx++)
                              {
                                  $tplan_tcv_set[$idx]['drop_info'] = true;
                              }
                          }
                          else
                          {
                              for ($idx = 0; $idx < $tcv_count; $idx++)
                              {
                                  if (!array_key_exists($tplan_tcv_set[$idx]['feature_id'], $assign_user_set))
                                  {
                                      $tplan_tcv_set[$idx]['drop_info'] = true;
                                  }
                              }
                          }
                      }
                  }
              }
  
              // add execute status and filter it by bug id and execute status
              $idx = 0;
              $jdx = 0;
              while ($idx < $tcv_count)
              {
                  // get recordset all order by tcversion id, so can loop once to charge status
                  if ($tplan_tcv_set[$idx]['drop_info'])
                  {
                      // to deleted, no need to add exec info
                      $idx++;
                  }
                  
                  if ($jdx >= $exec_count)
                  {
                      $tplan_tcv_set[$idx]['exec_status'] = 'n';
                      $idx++;
                      continue;
                  }
  
                  if ($tplan_tcv_set[$idx]['tcversion_id'] == $exec_build_set[$jdx]['tcversion_id'])
                  {
                      $tplan_tcv_set[$idx]['exec_status'] = $exec_build_set[$jdx]['status'];
                      $idx++;
                      $jdx++;
                  }
                  else if ($tplan_tcv_set[$idx]['tcversion_id'] > $exec_build_set[$jdx]['tcversion_id']
                      && $jdx < $exec_count)
                  {
                      $jdx++;
                  }
                  else
                  {
                      $tplan_tcv_set[$idx]['exec_status'] = 'n';
                      $idx++;
                  }
              }
  
              if( isset($filters['bug_id']) && !is_null($filters['bug_id']) )
              {
                  $exec_set = $dbHandler->fetchRowsIntoMap($sql2do['exec'], 'tcversion_id');
                  for ($idx = 0; $idx < $tcv_count; $idx++)
                  {
                      if (!array_key_exists($tplan_tcv_set[$idx]['tcversion_id'], $exec_set))
                      {
                          $tplan_tcv_set[$idx]['drop_info'] = true;
                      }
                  }
              }
  
              // if filter include all status item, no need to filter it
              $targetExecStatus = (array)(isset($objFilters->filter_result_result) ? $objFilters->filter_result_result : null);
              if( !is_null($targetExecStatus) && count($targetExecStatus) > 0 && (!in_array($resultsCfg['status_code']['all'], $targetExecStatus)) )
              {
                  $dummy = $targetExecStatus;
  
                  for ($idx = 0; $idx < $tcv_count; $idx++)
                  {
                      if( !in_array($tplan_tcv_set[$idx]['exec_status'],$dummy) )
                      {
                          $tplan_tcv_set[$idx]['drop_info'] = true;
                      }
                  }
              }
  
              // rebuild record
              $setTestCaseStatus = array();
              for ($idx = 0; $idx < $tcv_count; $idx++)
              {
                  if (!$tplan_tcv_set[$idx]['drop_info'])
                  {
                      if (isset($setTestCaseStatus[$tplan_tcv_set[$idx]['tsuite_id']]))
                      {
                          $setTestCaseStatus[$tplan_tcv_set[$idx]['tsuite_id']][$tplan_tcv_set[$idx]['tcase_id']] = $tplan_tcv_set[$idx];
                      }
                      else
                      {
                          $setTestCaseStatus[$tplan_tcv_set[$idx]['tsuite_id']] = array();
                          $setTestCaseStatus[$tplan_tcv_set[$idx]['tsuite_id']][$tplan_tcv_set[$idx]['tcase_id']] = $tplan_tcv_set[$idx];
                      }
                      $keys[] = $tplan_tcv_set[$idx]['tcase_id'];
                  }
              }
  
              $tplan_tcases = $setTestCaseStatus;
          }
      }
  
      if( !is_null($tplan_tcases) )
      {
          $pnOptions = array('hideTestCases' => $options['hideTestCases'], 'viewType' => 'executionTree');
          $pnFilters = null;
          $renderTreeNodeOpt['hideTestCases'] = $options['hideTestCases'];
          $testcase_counters = prepareExecTreeNodeFast($dbHandler,$test_suites, $test_spec,
              $hash_id_descr,$menuUrl,$tcase_prefix,$tplan_tcases,$pnFilters,$pnOptions, $renderTreeNodeOpt);
      }
      else
      {
          $tplan_tcases = array();
          if (isset($test_spec['childNodes']))
          {
              unset($test_spec['childNodes']);
          }
  
          $testcase_counters = helperInitCounters();
          foreach($testcase_counters as $key => $value)
          {
              $test_spec[$key] = $testcase_counters[$key];
          }
      }
  }
/*  
  if($test_spec)
  {
    if(is_null($filters['tcase_id']) || $filters['tcase_id'] > 0)   // 20120519 TO BE CHECKED
    {
      // Step 1 - get item set with exec status.
      // This has to scopes:
      // 1. tree coloring according exec status on (Test plan, platform, build ) context
      // 2. produce sql that can be used to reduce item set on combination with filters
      //    that can not be used on this step like:
      //    a. test cases belonging to branch with root TEST SUITE
      //    b. keyword filter on AND MODE
      //    c. execution results on other builds, any build etc
      //
      // WE NEED TO ADD FILTERING on CUSTOM FIELD VALUES, WE HAVE NOT REFACTORED
      // THIS YET.
      //
      if( !is_null($sql2do = $tplan_mgr->{$objOptions->getTreeMethod}($tplan_id,$build_id,$filters,$options)) )
      {
        $doPinBall = false;
        if( is_array($sql2do) )
        {       
          if( ($doPinBall = $filters['keyword_filter_type'] == 'And') )
          { 
            $kmethod = "fetchRowsIntoMapAddRC";
            $unionClause = " UNION ALL ";
          }
          else
          {
            $kmethod = "fetchRowsIntoMap";
            $unionClause = ' UNION ';
          }
          $sql2run = $sql2do['exec'] . $unionClause . $sql2do['not_run'];
        }
        else
        {
          $kmethod = "fetchRowsIntoMap";
          $sql2run = $sql2do;
        }
        
        $tplan_tcases = $dbHandler->$kmethod($sql2run,'tcase_id');
        if($doPinBall && !is_null($tplan_tcases))
        {
          $kwc = count($filters['keyword_id']);
          $ak = array_keys($tplan_tcases);
          $mx = null;
          foreach($ak as $tk)
          {
            if($tplan_tcases[$tk]['recordcount'] == $kwc)
            {
              $mx[$tk] = $tplan_tcases[$tk];
            } 
          } 
          $tplan_tcases = null;
          $tplan_tcases = $mx;
        } 
        $setTestCaseStatus = $tplan_tcases;
      }
    }   

    if (is_null($tplan_tcases))
    {
      $tplan_tcases = array();
    }

    // OK, now we need to work on status filters
    // if "any" was selected as filtering status, don't filter by status
    $targetExecStatus = (array)(isset($objFilters->filter_result_result) ? $objFilters->filter_result_result : null);
    if( !is_null($targetExecStatus) && (!in_array($resultsCfg['status_code']['all'], $targetExecStatus)) ) 
    {
      applyStatusFilters($tplan_id,$tplan_tcases,$objFilters,$tplan_mgr,$resultsCfg['status_code']);
    }

    if (isset($my['filters']['filter_custom_fields']) && isset($test_spec['childNodes']))
    {
      $test_spec['childNodes'] = filter_by_cf_values($dbHandler, $test_spec['childNodes'],
                                                     $my['filters']['filter_custom_fields'],$hash_descr_id);
    }

    // here we have LOT OF CONFUSION, sometimes we use $my['options'] other $options
    $pnFilters = null;    
    $pnOptions = array('hideTestCases' => $my['options']['hideTestCases'], 'viewType' => 'executionTree');
    $testcase_counters = prepareExecTreeNode($dbHandler,$test_spec,$map_node_tccount,
                                             $tplan_tcases,$pnFilters,$pnOptions);
    foreach($testcase_counters as $key => $value)
    {
      $test_spec[$key] = $testcase_counters[$key];
    }
  
    $keys = array_keys($tplan_tcases);
    $renderTreeNodeOpt['hideTestCases'] = $my['options']['hideTestCases'];
    $renderTreeNodeOpt['tc_action_enabled'] = isset($my['options']['tc_action_enabled']) ? 
                                              $my['options']['tc_action_enabled'] : 1;
    $renderTreeNodeOpt['showTestCaseExecStatus'] = $my['options']['showTestCaseExecStatus']; 
    renderExecTreeNode(1,$test_spec,$tplan_tcases,$hash_id_descr,$menuUrl,$tcase_prefix,$renderTreeNodeOpt);
  }  // if($test_spec)
*/  

  $treeMenu->rootnode=new stdClass();
  $treeMenu->rootnode->name=$test_spec['text'];
  $treeMenu->rootnode->id=$test_spec['id'];
  if (isset($test_spec['leaf']))
  {
      $treeMenu->rootnode->leaf = $test_spec['leaf'];
  }
  else
  {
      $treeMenu->rootnode->leaf = false;
  }
  $treeMenu->rootnode->text=$test_spec['text'];
  $treeMenu->rootnode->position=$test_spec['position'];     
  $treeMenu->rootnode->href=$test_spec['href'];


  // Change key ('childNodes')  to the one required by Ext JS tree.
  $menustring = '';
  if(isset($test_spec['childNodes'])) 
  {
    $menustring = str_ireplace('childNodes', 'children', json_encode($test_spec['childNodes']));
  }
    
  // Remove null elements (Ext JS tree do not like it ).
  // :null happens on -> "children":null,"text" that must become "children":[],"text"
  // $menustring = str_ireplace(array(':null',',null','null,'),array(':[]','',''), $menustring); 
  // $menustring = str_ireplace(array(':null',',null','null,','null'),array(':[]','','',''), $menustring); 
  $target = array(',"' . REMOVEME .'"','"' . REMOVEME . '",');
  $menustring = str_ireplace($target,array('',''), $menustring); 

  $target = array(':' . REMOVEME,'"' . REMOVEME . '"');
  $menustring = str_ireplace($target,array(':[]',''), $menustring); 
  
  $treeMenu->menustring = $menustring;
  return array($treeMenu, $keys);
}

/**
 *
 */
function helperInitCounters()
{
  $resultsCfg = config_get('results');
  $items = array_keys($resultsCfg['status_code']);
  $items[] = 'testcase_count';
  $cc = array_fill_keys($items, 0);
  return $cc;
}

/**
 *
 */
function cfForDesign(&$dbHandler,$cfSet)
{
  static $mgr;
  if(!$mgr)
  {
    $mgr = new cfield_mgr($dbHandler);
  }  

  $ret = null;
  foreach($cfSet as $id => $val)
  {
    $xx = $mgr->get_by_id($id);
    if( $xx[$id]['enable_on_design'] )
    {
      $ret[$id] = $val;
    }  
  }  
  return $ret;
}
