<?php

require_once('MantisConnectFetcher.class.php');

// authentication parameters
define('MC_USER_LOGIN',  'user');
define('MC_USER_PASSWD', 'pass');

// mantisconnect WSDL URL
define('MC_WSDL_URL', 'http://mantis112.local/api/soap/mantisconnect.php?wsdl');

// usage example
$_mcfObj = new MantisConnectFetcher();

$_mcfObj->setAuth(MC_USER_LOGIN, MC_USER_PASSWD);
$_mcfObj->setMantisConnectWsdlUrl(MC_WSDL_URL);
$_mcfObj->setHighIssueId(16);
$_mcfObj->setStorageType(MantisConnectFetcher::STORAGE_TYPE__FILES);
$_mcfObj->setLowIssueId(1);

$_mcfObj->init();

$_mcfObj->proccess();

$_mcfObj->deInit();

