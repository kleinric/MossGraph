<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

$config['tmp'] = getcwd().'/data';
$config['data']= dirname($_SERVER['REQUEST_URI'])."/data";

$config['proxy'] = array(
    //'http' => array(
    //    'proxy' => 'tcp://127.0.0.1:3128',
    //    'request_fulluri' => true,
    //),
);
