<?php
/**
 * TestLink Open Source Project - http://testlink.sourceforge.net/ 
 * This script is distributed under the GNU General Public License 2 or later. 
 *
 * @filesource	bugAdd.php
 * @internal revisions
 * @since 1.9.14
 * 
 */
require_once('../../config.inc.php');
require_once('common.php');

require_once('exec.inc.php');
testlinkInitPage($db,false,false,"checkRights");

$templateCfg = templateConfiguration();
list($args,$gui,$its,$issueT) = initEnv($db);

if( ($args->user_action == 'create' || $args->user_action == 'doCreate') && 
    $gui->issueTrackerCfg->tlCanCreateIssue)
{
  // get matadata
  $gui->issueTrackerMetaData = getIssueTrackerMetaData($its);
  
  switch($args->user_action)
  {
    case 'create':
     $dummy = generateIssueText($db,$args,$its); 
     $gui->bug_summary = $dummy->summary;
    break;

    case 'doCreate':
     $gui->bug_summary = $args->bug_summary;
     $ret = addIssue($db,$args,$its);
     $gui->issueTrackerCfg->tlCanCreateIssue = $ret['status_ok'];
     $gui->msg = $ret['msg'];
    break;

  }
}  
else if($args->user_action == 'link' || $args->user_action == 'add_note')
{
  // Well do not think is very elegant to check for $args->bug_id != ""
  // to understand if user has pressed ADD Button
  if(!is_null($issueT) && $args->bug_id != "")
  {
  	$l18n = init_labels(array("error_wrong_BugID_format" => null,"error_bug_does_not_exist_on_bts" => null));

    switch($args->user_action)
    {
      case 'link':
        $gui->msg = $l18n["error_wrong_BugID_format"];
        if ($its->checkBugIDSyntax($args->bug_id))
        {
          if ($its->checkBugIDExistence($args->bug_id))
          {     
            if (write_execution_bug($db,$args->exec_id, $args->bug_id))
            {
              $gui->msg = lang_get("bug_added");
              logAuditEvent(TLS("audit_executionbug_added",$args->bug_id),"CREATE",$args->exec_id,"executions");

              // blank notes will not be added :).
              if($gui->issueTrackerCfg->tlCanAddIssueNote && (strlen($gui->bug_notes) > 0) )
              {
                // will do call to update issue Notes
                $its->addNote($args->bug_id,$gui->bug_notes);
              }  
            }
          }
          else
          {
            $gui->msg = sprintf($l18n["error_bug_does_not_exist_on_bts"],$gui->bug_id);
          }  
        }
      break;
      
      case 'add_note':
        // blank notes will not be added :).
        $gui->msg = '';
        if($gui->issueTrackerCfg->tlCanAddIssueNote && (strlen($gui->bug_notes) > 0) )
        {
          $its->addNote($args->bug_id,$gui->bug_notes);
        }  
      break;
    }
  }
}
$smarty = new TLSmarty();
$smarty->assign('gui',$gui);
$smarty->display($templateCfg->template_dir . $templateCfg->default_template);



/**
 * 
 * 
 */
function initEnv(&$dbHandler)
{
  $uaWhiteList = array();
  $uaWhiteList['elements'] = array('link','create','doCreate','add_note');
  $uaWhiteList['lenght'] = array();
  foreach($uaWhiteList['elements'] as $xmen)
  {
    $uaWhiteList['lenght'][] = strlen($xmen);
  }  
  $user_action['maxLengh'] = max($uaWhiteList['lenght']);
  $user_action['minLengh'] = min($uaWhiteList['lenght']);

	$iParams = array("exec_id" => array("GET",tlInputParameter::INT_N),
		               "bug_id" => array("REQUEST",tlInputParameter::STRING_N),
		               "tproject_id" => array("REQUEST",tlInputParameter::INT_N),
                   "tplan_id" => array("REQUEST",tlInputParameter::INT_N),
		               "tcversion_id" => array("REQUEST",tlInputParameter::INT_N),
                   "bug_notes" => array("POST",tlInputParameter::STRING_N),
                   "issueType" => array("POST",tlInputParameter::INT_N),
                   "issuePriority" => array("POST",tlInputParameter::INT_N),
                   "artifactComponent" => array("POST",tlInputParameter::ARRAY_INT),
                   "artifactVersion" => array("POST",tlInputParameter::ARRAY_INT),
		               "user_action" => array("REQUEST",tlInputParameter::STRING_N,
                                          $user_action['minLengh'],$user_action['maxLengh']));
		             
	
	$args = new stdClass();
	I_PARAMS($iParams,$args);
	if ($args->exec_id)
	{
		$_SESSION['bugAdd_execID'] = intval($args->exec_id);
	}
	else
	{
		$args->exec_id = intval(isset($_SESSION['bugAdd_execID']) ? $_SESSION['bugAdd_execID'] : 0);
	}	


  $args->user = $_SESSION['currentUser'];

  $gui = new stdClass();
  switch($args->user_action)
  {
    case 'create':
    case 'doCreate':
      $gui->pageTitle = lang_get('create_issue');
    break;

    case 'add_note':
      $gui->pageTitle = lang_get('add_issue_note');
    break;

    case 'link':
    default:
      $gui->pageTitle = lang_get('title_bug_add');
    break;
  }

  $gui->msg = '';
  $gui->bug_summary = '';
  $gui->tproject_id = $args->tproject_id;
  $gui->tplan_id = $args->tplan_id;
  $gui->tcversion_id = $args->tcversion_id;
  $gui->user_action = $args->user_action;
  $gui->bug_id = $args->bug_id;

  $gui->issueType = $args->issueType;
  $gui->issuePriority = $args->issuePriority;
  $gui->artifactVersion = $args->artifactVersion;
  $gui->artifactComponent = $args->artifactComponent;
  

  // -----------------------------------------------------------------------
  // Special processing
  list($itObj,$itCfg) = getIssueTracker($dbHandler,$args,$gui);


  // Second access to user input
  $bug_summary['minLengh'] = 1; 
  $bug_summary['maxLengh'] = $itObj->getBugSummaryMaxLength(); 

  $inputCfg = array("bug_summary" => array("POST",tlInputParameter::STRING_N,
                                           $bug_summary['minLengh'],$bug_summary['maxLengh']));

  I_PARAMS($inputCfg,$args);

  $args->bug_id = trim($args->bug_id);
  switch($args->user_action)
  {
    case 'create':
      if( $args->bug_id == '' && $args->exec_id > 0)  
      {
        $map = get_execution($dbHandler,$args->exec_id);
        $args->bug_notes = $map[0]['notes'];    
      }  
    break;
    
    case 'doCreate':
    case 'add_note':
    case 'link':
    default:
    break;
  }

  $gui->bug_notes = $args->bug_notes = trim($args->bug_notes);

  $args->basehref = $_SESSION['basehref'];
  $tables = tlObjectWithDB::getDBTables(array('testplans'));
//   $sql = ' SELECT api_key FROM ' . $tables['testplans'] . 
  $sql = ' SELECT api_key FROM ' . $dbHandler->get_table('testplans') .
         ' WHERE id=' . intval($args->tplan_id);
      
  $rs = $dbHandler->get_recordset($sql);
  $args->tplan_apikey = $rs[0]['api_key'];

  return array($args,$gui,$itObj,$itCfg);
}


/**
 *
 */
function getIssueTracker(&$dbHandler,$argsObj,&$guiObj)
{
  $its = null;
  $tprojectMgr = new testproject($dbHandler);
  $info = $tprojectMgr->get_by_id($argsObj->tproject_id);

  $guiObj->issueTrackerCfg = new stdClass();
  $guiObj->issueTrackerCfg->createIssueURL = null;
  $guiObj->issueTrackerCfg->VerboseID = '';
  $guiObj->issueTrackerCfg->VerboseType = '';
  $guiObj->issueTrackerCfg->bugIDMaxLength = 0;
  $guiObj->issueTrackerCfg->bugSummaryMaxLength = 100; // MAGIC 
  $guiObj->issueTrackerCfg->tlCanCreateIssue = false;
  $guiObj->issueTrackerCfg->tlCanAddIssueNote = true;

  if($info['issue_tracker_enabled'])
  {
  	$it_mgr = new tlIssueTracker($dbHandler);
  	$issueTrackerCfg = $it_mgr->getLinkedTo($argsObj->tproject_id);

  	if( !is_null($issueTrackerCfg) )
  	{
  		$its = $it_mgr->getInterfaceObject($argsObj->tproject_id);
  		$guiObj->issueTrackerCfg->VerboseType = $issueTrackerCfg['verboseType'];
  		$guiObj->issueTrackerCfg->VerboseID = $issueTrackerCfg['issuetracker_name'];
  		$guiObj->issueTrackerCfg->bugIDMaxLength = $its->getBugIDMaxLength();
  		$guiObj->issueTrackerCfg->createIssueURL = $its->getEnterBugURL();
      $guiObj->issueTrackerCfg->bugSummaryMaxLength = $its->getBugSummaryMaxLength();
          
      $guiObj->issueTrackerCfg->tlCanCreateIssue = method_exists($its,'addIssue');
      $guiObj->issueTrackerCfg->tlCanAddIssueNote = method_exists($its,'addNote');
  	}
  }	              
  return array($its,$issueTrackerCfg); 
}




/**
 * Checks the user rights for viewing the page
 * 
 * @param $db resource the database connection handle
 * @param $user tlUser the object of the current user
 *
 * @return boolean return true if the page can be viewed, false if not
 */
function checkRights(&$db,&$user)
{
	$hasRights = $user->hasRight($db,"testplan_execute");
	return $hasRights;
}
