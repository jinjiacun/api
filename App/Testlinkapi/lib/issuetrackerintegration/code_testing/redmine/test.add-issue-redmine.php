<?php

// TEST USING RAW redmine interface.
// TestLink has a interface built using the RAW redmine interface
require_once '../../../../third_party/redmine-php-api/lib/redmine-rest-api.php';


// 20140908
$site = array(array('url' => 'http://192.168.1.174',
	                'apiKey' => 'e6f1cbed7469528389554cffcb0e5aa4e0fa0bc8'),
			  array('url' => 'http://tl.m.redmine.org', 
			  	    'apiKey' => 'b956de40bf8baf6af7344b759cd9471832f33922'),
			  array('url' => 'https://localhost:8443/redmine/', 
			  	    'apiKey' => '81538efac88d05a1dbf77b80e793526dbd4921dd'),
			  array('url' => 'http://localhost:8888/redmine/', 
			  	    'apiKey' => '81538efac88d05a1dbf77b80e793526dbd4921dd'),
			  array('url' => 'http://testlink01.m.redmine.org', 
			  	    'apiKey' => '058157d55d62b632a665491abcc003aa4554673d')
			  );

$siteID = 4;
$red = new redmine($site[$siteID]['url'],$site[$siteID]['apiKey']);

$xml = new SimpleXMLElement('<?xml version="1.0"?><issue></issue>');  
$xml->addChild('subject', htmlentities('TEST SUBJECT - MAY 1'));

// As you see we can use the project identifier (string) as project_id!!! Great
$xml->addChild('project_id', 'quantum-priv');
$xml->addChild('tracker_id', 1);
$red->addIssueFromSimpleXML($xml);
