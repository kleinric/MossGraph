<?php
/** @file index.php
 *  @brief Main Entry Point
 * 
 *  Check the folder location and parse the Moss ID from GET.
 *  Then draw the form and render the graph.
 * 
 *  @author Richard Klein
 *  @date 2013 - 2014
 *
 */

require_once('./config.php');
require_once('./graph.php');
require_once('./lib.php');

// Check that the data file exists and contains the Moss results
check_tmp($config['tmp']);

/// Filter by: (Moss Matched Lines > #$lines)
$lines = 0;
/// Moss ID number
$moss_id = 0;

/// @cond
$filtered_input = filter_input(INPUT_GET, 'lines', FILTER_VALIDATE_INT);
if($filtered_input !== false && $filtered_input !== NULL){
    $lines = $filtered_input;
}

$filtered_input = filter_input(INPUT_GET, 'moss', FILTER_VALIDATE_INT);
if($filtered_input !== false && $filtered_input !== NULL){
    $moss_id = $filtered_input;
    drawForm($moss_id);
    showMoss($moss_id, $lines);
}else{
    error_message("Moss ID not provided.");
    drawForm('');
}
/// @endcond
