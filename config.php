<?php
/**
 * @file config.php
 * @brief Configuration options
 */

// Turn error reporting on or off.
error_reporting(E_ALL);
ini_set('display_errors', '1');

/// Directory to store downloaded data from Moss
$config['tmp'] = getcwd().'/data';
/// Um?
$config['data']= dirname($_SERVER['REQUEST_URI'])."/data";
/// Updated with settings of the last call to reset the form. See getParams().
$config['params'] = array ();

/**
 *  @brief Proxy Settings to fetch Data from Moss
 * 
 *  If you're behind a proxy that needs authentication it might ruin your life.
 *  I find that using cntlm to inject authentication and then proxying through
 *    it was fairly successful for our setup.
 * @code
 *   $config['proxy'] = array(
 *       'http' => array(
 *           'proxy' => 'tcp://127.0.0.1:3128',
 *           'request_fulluri' => true,
 *        ),
 *   );@endcode
 */
$config['proxy'] = array(); 
