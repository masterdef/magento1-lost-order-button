<?php 

require_once ('app/Mage.php');
$app = Mage::app('default');

$config  = Mage::getConfig()->getResourceConnectionConfig('default_setup');

$dbinfo = array('host' => $config->host,
            'user' => $config->username,
            'pass' => $config->password,
            'dbname' => $config->dbname
);
var_dump($dbinfo);
