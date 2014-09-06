<?php

/** @file lib.php
 *  @brief Library Functions
 *  @author Richard Klein
 *  @date 2013 - 2014
 *
 */
require_once('./config.php');
require_once('./graph.php');

/**
 * @brief Fetch Moss index.html and cache it.
 * 
 * If the cache already exists just return the relevant HTML immediately.
 * 
 * @param int $mossID Moss submission ID.
 * @return string
 */
function fetchMoss($mossID) {
    global $config;

    $sContext = stream_context_create($config['proxy']);
    check_tmp($config['tmp']);

    $folder = $config['tmp'] . "/$mossID";
    $filename = $folder . '/index.html';
    if (!file_exists($filename)) {
        mkdir($folder);
        $source = file_get_contents(
                "http://moss.stanford.edu/results/$mossID/", // Moss result
                false, // Ignore include path search
                $sContext // Use the proxy
        );
        file_put_contents($filename, $source);
    } else {
        $source = file_get_contents($filename);
    }
    return $source;
}

/**
 * @brief Opens the Moss data and constructs the graph containing all edges.
 * 
 * @param type $mossID Moss ID number to search cache or download from Stanford.
 * @param type $lines
 * @return \graph
 */
function readMoss($mossID) {
    global $config;

    $source = fetchMoss($mossID);
    
    $doc = new DOMDocument();
    $doc->loadHTML($source);

    // Fetch html table
    $table = $doc->getElementsByTagName("table")->item(0);
    // Extract the rows from the table
    $rows = $table->getElementsByTagName("tr");
    // Construct graph
    $g = new graph();
    // For each row
    for ($i = 1; $i < $rows->length; $i++) {
        $row = $rows->item($i);
        $cols = $row->getElementsByTagName("td");

        $name1 = $cols->item(0)->nodeValue;
        $name2 = $cols->item(1)->textContent;
        $linem = $cols->item(2)->textContent;
        $link = $cols->item(0)->getElementsByTagName('a')->item(0)->getAttribute('href');

        $pattern = "|\./|";
        $name1 = trim(preg_replace($pattern, "", $name1));
        $name2 = trim(preg_replace($pattern, "", $name2));

        $pattern = "|.*\((..*)%\).*|";
        $p1 = trim(preg_replace($pattern, '\1', $name1));
        $p2 = trim(preg_replace($pattern, '\1', $name2));

        $pattern = "| \(..*%\)|";
        $name1 = trim(preg_replace($pattern, "", $name1));
        $name2 = trim(preg_replace($pattern, "", $name2));
        $linem = trim($linem);

        $g->addEdge($name1, $name2, $linem, $p1, $p2, $link);
    }

    return $g;
}

/**
 * @brief Generates both a PNG image and MAP of the graph.
 * 
 * The PNG is displayed and the MAP from graphviz allows one to click on the
 *  numbers in the graph to open each pairwise comparison.
 * @param type $result
 * @param type $lines
 */
function showMoss($result, $lines) {

    $linematches = readMoss($result);
    $g = $linematches->image($result, $lines);
    $filename = $g['image_url'];

    echo "<img src=\"$filename\" USEMAP=\"#myGraph\"/>\n";
    echo file_get_contents($g['map_local']);
    //header("Content-type: image/png");
    //$image=imagecreatefromjpeg($_GET['img']);
    //imagejpeg($image);
    //readfile($filename);
}

/**
 * Check that the data location exists. If it doesn't, attempt to create it.
 * @param string $location Folder Location
 */
function check_tmp($location) {
    if (!file_exists($location)) {
        mkdir($location, 0777);
    }
}

/**
 * Reads most recent render parameters into \ref $config.
 * 
 * Parameters include:
 * -# moss_id
 * -# lines
 * -# no_lines
 * -# no_per
 * -# no_width
 */
function getParams() {
    global $config;

    if (isset($_GET['moss'])) {
        $params['moss'] = $_GET['moss'];
    }

    if (isset($_GET['lines'])) {
        $params['lines'] = $_GET['lines'];
    } else {
        $params['lines'] = 0;
    }

    if (isset($_GET['no_lines'])) {
        $params['no_lines'] = $_GET['no_lines'];
    } else {
        $params['no_lines'] = false;
    }


    if (isset($_GET['no_per'])) {
        $params['no_per'] = $_GET['no_per'];
    } else {
        $params['no_per'] = false;
    }

    if (isset($_GET['no_width'])) {
        $params['no_width'] = $_GET['no_width'];
    } else {
        $params['no_width'] = false;
    }

    $config['params'] = $params;
}

/**
 * Draw the render settings form.
 * 
 * Reads recent settings from \link config.php#$config $config['params'] \endlink
 * @param int $moss [in] Moss ID Number
 * 
 */
function drawForm($moss) {
    global $config;
    getParams();

    $cut_lines = $config['params']['lines'];
    $no_lines = $config['params']['no_lines'];
    if ($no_lines) {
        $no_lines = "checked";
    } else {
        $no_lines = "";
    }

    $no_width = $config['params']['no_width'];
    if ($no_width) {
        $no_width = "checked";
    } else {
        $no_width = "";
    }

    $no_per = $config['params']['no_per'];
    if ($no_per) {
        $no_per = "checked";
    } else {
        $no_per = "";
    }

    echo<<<FRM
    <form id="cs_parser" name="cs_parser" action="" method="GET">
        <table>
        <tr><td>Moss Number: </td><td><input type="text" name="moss" value="$moss"></td></tr>
        <tr><td>Cut Lines: </td><td><input type="text" name="lines" value="$cut_lines"></td></tr>
        <tr><td>No Line Counts: </td><td><input type="checkbox" name="no_lines" value="1" $no_lines></td></tr>
        <tr><td>No Line Thickness: </td><td><input type="checkbox" name="no_width" value="1" $no_width></td></tr>
        <tr><td>No Line Percentages: </td><td><input type="checkbox" name="no_per" value="1" $no_per></td></tr>
            </table>

        <input type="submit">
    </form>
    
FRM;
}

/**
 * Display an error message.
 * @param string $message Error Text.
 * @todo Integrate with Bootstrap error box.
 */
function error_message($message) {
    echo $message . "<br />";
}
