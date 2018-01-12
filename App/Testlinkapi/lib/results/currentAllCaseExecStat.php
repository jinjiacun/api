<?php
/**
 * Created by PhpStorm.
 * User: wuyanxiong_ht
 * Date: 2017/11/30
 * Time: 16:34
 */
require('../../config.inc.php');
require_once('common.php');
require_once('displayMgr.php');

$templateCfg = templateConfiguration();//获取数据渲染的视图模板
$args = init_args($db);
$tplan_mgr = new testplan($db);
$gui = initializeGui($db,$args,$tplan_mgr);
$gui->tplan_id = $args->tplan_id;

//add
//author:jinjaicun
//time:2017-12-29 14:29
//get build by current plan id
$gui->tplan_id = $args->tplan_id;
$build_list = $tplan_mgr->get_builds($args->tplan_id,
                                     null,
                                     null,
                                     null);
$gui->build_map = array();
$gui->build_map[0] = 'all';
if(count($build_list) > 0){
    foreach($build_list as $k=>$v){
        $gui->build_map[$k] = $v['name'];
    }
}
$gui->level_map = array(
        1 => '系统',
        2 => '验收',
        3 => '模拟',
        4 => '培训',
        5 => '数据迁移'
    );

$smarty = new TLSmarty;
$smarty->assign('gui', $gui);
displayReport($templateCfg->template_dir . $templateCfg->default_template, $smarty, $args->format,$mailCfg);

/*
  function: init_args
  args: none
  returns: array
*/
function init_args(&$dbHandler)
{
    $iParams = array("apikey"       => array(tlInputParameter::STRING_N,32,64),
                     "tproject_id"  => array(tlInputParameter::INT_N),
                     "tplan_id"     => array(tlInputParameter::INT_N),
                     "format"       => array(tlInputParameter::INT_N));
    $args = new stdClass();
    $pParams = R_PARAMS($iParams,$args);
    if( !is_null($args->apikey) )
    {
        $cerbero = new stdClass();
        $cerbero->args = new stdClass();
        $cerbero->args->tproject_id = $args->tproject_id;
        $cerbero->args->tplan_id = $args->tplan_id;

        if(strlen($args->apikey) == 32)
        {
            $cerbero->args->getAccessAttr = true;
            $cerbero->method = 'checkRights';
            $cerbero->redirect_target = "../../login.php?note=logout";
            setUpEnvForRemoteAccess($dbHandler,$args->apikey,$cerbero);
        }
        else
        {
            $args->addOpAccess = false;
            $cerbero->method = null;
            setUpEnvForAnonymousAccess($dbHandler,$args->apikey,$cerbero);
        }
    }
    else
    {
        testlinkInitPage($dbHandler,true,false,"checkRights");
        $args->tproject_id = isset($_SESSION['testprojectID']) ? intval($_SESSION['testprojectID']) : 0;
    }

    if($args->tproject_id <= 0)
    {
        $msg = __FILE__ . '::' . __FUNCTION__ . " :: Invalid Test Project ID ({$args->tproject_id})";
        throw new Exception($msg);
    }

    if (is_null($args->format))
    {
        tlog("Parameter 'format' is not defined", 'ERROR');
        exit();
    }

    return $args;
}

/**
 *
 *
 */

function initializeGui(&$dbHandler,$argsObj,&$tplanMgr)
{
    $gui = new stdClass();
    $gui->title = lang_get('current_all_case_exec_stat');
    $gui->do_report = array();
    $gui->columnsDefinition = new stdClass();
    $gui->columnsDefinition->testers = null;
    $gui->statistics = new stdClass();
    $gui->statistics->testers = null;
    $gui->statistics->overalBuildStatus = null;
    $gui->elapsed_time = 0;
    $gui->displayBuildMetrics = false;
    $mgr = new testproject($dbHandler);
    $dummy = $mgr->get_by_id($argsObj->tproject_id);
    $gui->testprojectOptions = new stdClass();
    $gui->tproject_name = $dummy['name'];

    $info = $tplanMgr->get_by_id($argsObj->tplan_id);
    $gui->tplan_name = $info['name'];
    return $gui;
}


function checkRights(&$db,&$user,$context = null)
{
    if(is_null($context))
    {
        $context = new stdClass();
        $context->tproject_id = $context->tplan_id = null;
        $context->getAccessAttr = false;
    }

    $check = $user->hasRight($db,'testplan_metrics',$context->tproject_id,$context->tplan_id,$context->getAccessAttr);
    return $check;
}
?>
