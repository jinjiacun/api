<?php
require('../../config.inc.php');
require_once('common.php');
require_once('reports.class.php');
require_once("web_editor.php");
$editorCfg = getWebEditorCfg('build');
require_once(require_web_editor($editorCfg['type']));
testlinkInitPage($db,true,false,null);


$smarty = new TLSmarty();

$tplan_mgr = new testplan($db);
$build_mgr = new build_mgr($db);
$templateCfg = templateConfiguration();

$args = init_args($_SESSION, $tplan_mgr, $db);

$smarty->assign("title",lang_get('test_report_Creat_btn'));   //将标题传递给模板
$templateCfg->default_template="tcCreatMsg.tpl";

$smarty->assign('gui',$args);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);

/*
 * INITialize page ARGuments, using the $_REQUEST and $_SESSION
 * super-global hashes.
 * Important: changes in HTML input elements on the Smarty template
 *            must be reflected here.
 *
 *
 * @parameter hash request_hash the $_REQUEST
 * @parameter hash session_hash the $_SESSION
 * @return    object with html values tranformed and other
 *                   generated variables.
 * @internal revisions
 *
 */
function init_args($session_hash,&$tplan_mgr, $db)
{
    $args = new stdClass();
    //$request_hash = strings_stripSlashes($request_hash);
    
    //20160117 changed by zhouzhaoxin for change plan by left navigator select not common
    $iParams = array("tplan_id" => array(tlInputParameter::INT_N),
        "format" => array(tlInputParameter::INT_N,999));
    R_PARAMS($iParams,$args);
  
    if (!isset($args->tplan_id) || $args->tplan_id <= 0)
    {
        $args->tplan_id = isset($session_hash['testplanID']) ? intval($session_hash['testplanID']) : 0;
    }
    
    $args->tplan_info = $tplan_mgr->get_by_id($args->tplan_id);
    $args->tplan_name = $args->tplan_info['name'];
    
    $args->testprojectID = intval($session_hash['testprojectID']);
    $args->testprojectName = $session_hash['testprojectName'];
    $args->userID = intval($session_hash['userID']);
    $args->user = $_SESSION['currentUser'];

    $args->filluser=getUserName($args->userID, $db);
    $htmlMenu = array('items' => null, 'selected' => null, 'build_count' => 0);
    $htmlMenu['items'] = $tplan_mgr->get_builds_for_html_options($args->tplan_id,null,null,array('orderByDir' => 'id:DESC'));
    $args->source_build = $htmlMenu;
    $args->curdate=date("Y-m-d");
    $args->waning_round_msg= lang_get('test_waning_round_msg');
    $args->waning_percent_msg= lang_get('test_waning_percent_msg');
    $args->waning_submit_msg= lang_get('test_waning_submit_msg');
    
    return  $args;
    
}

function getUserName($userID, $dbHandler)
{
    $sql = "SELECT last,first FROM " . $dbHandler->get_table('users')." WHERE id=" . $userID;
    $result = $dbHandler->fetchFirstRow($sql);
    $user_name = $result['first'] . $result['last'];

    return $user_name;
}

?>