<?php

require_once('./config.php');


class graph{
    public $adj;
    public $gv;
    
    public function graph(){
        $this->adj = array();
    }

    public function addEdge($name1, $name2, $weight, $p1, $p2, $link){
        if(!array_key_exists($name1, $this->adj)){
            $this->adj[$name1] = array();
        }
        if(!array_key_exists($name2, $this->adj)){
            $this->adj[$name2] = array();
        }

        $myEdge['w'] = $weight;
        $myEdge['l'] = $link;
        $myEdge['head'] = $p2;
        $myEdge['tail'] = $p1;
        
        $this->adj[$name1][$name2] = $myEdge;
        //$this->adj[$name2][$name1] = $myEdge;

    }

    public function toString(){
        var_dump($this->adj);
    }

    public function dot($lines){
        global $config;
        
        getParams();
        
        $text  = "strict graph myGraph {\nsize=\"(20,100)\";\n ";
        foreach ($this->adj as $s => $d) {
            foreach ($d as $v => $edge) {

                $w = $edge['w'];

                $l = $edge['l'];
                $head = $edge['head'];
                $tail = $edge['tail'];
                
                $no_width = $config['params']['no_width'];
                if($no_width){
                    $width = 1;
                }else{
                    $width = max($head, $tail)/20;
                }
                
                $no_lines = $config['params']['no_lines'];
                $no_per = $config['params']['no_per'];
                
                
                if($w >= $lines){
                    $text .= "\t\t\"" . $s . "\" -- \"" . $v . "\"[penwidth=$width";
                    if(!$no_lines){
                        $text .= ", label=$w";
                    }
                    if(!$no_per){
                        $text .= ", headlabel=\"$head%\", taillabel=\"$tail%\"";
                        
                    }
                    
                    $text .= ", URL=\"$l\"];\n";
                }
            }

        }
        $text .= "}";

        return $text;
    }

    public function image($code, $lines ){
        global $config;

        $filename = $config['tmp'] . "/$code/$code";

        $text = $this->dot($lines);

        if (file_exists("$filename.dot")) {
            unlink("$filename.dot");
        }
        if (file_exists("$filename.png")) {
            unlink("$filename.png");
        }

        file_put_contents("$filename.dot", $text);

        exec("unflatten -f  -l 100 $filename.dot | /usr/bin/dot -Tcmapx -o $filename.map -Tpng -o $filename.png");

        $out['image_url'] = $config['data']."/$code/$code.png";
        $out['map_local'] = "$filename.map";
        return $out;
    }
}

function showMoss($result, $lines){
    global $config;

    $doc = new DOMDocument();

    if(!isset($config['proxy'])){
        $config['proxy'] = array();
    }
    
    $sContext = stream_context_create($config['proxy']);

    $folder = $config['tmp'] . "/$result";
    $filename = $folder . '/index.html';
    if (!file_exists($filename)) {
        mkdir($folder);
        $source = file_get_contents("http://moss.stanford.edu/results/$result/", false, $sContext);
        file_put_contents($filename, $source);
    } else {
        $source = file_get_contents($filename);
    }
    
    $doc->loadHTML($source);

    $table = $doc->getElementsByTagName("table");
    $table = $table->item(0);

    $rows = $table->getElementsByTagName("tr");

    $linematches = new graph();
    for ($i = 1; $i < $rows->length; $i++){
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

        $linematches->addEdge($name1, $name2, $linem, $p1, $p2, $link);
    }

    
    $g = $linematches->image($result, $lines);
    $filename = $g['image_url'];
  
    echo "<img src=\"$filename\" USEMAP=\"#myGraph\"/>\n";
    echo file_get_contents($g['map_local']);
    //header("Content-type: image/png");
    //$image=imagecreatefromjpeg($_GET['img']);
    //imagejpeg($image);
    //readfile($filename);
}

function check_tmp() {
    global $config;

    if (!file_exists($config['tmp'])) {
        mkdir($config['tmp'], 0777);
    }
}

function getParams(){
    global $config;
    
    if(isset($_GET['moss'])){
        $params['moss'] = $_GET['moss'];
    }
    
    if(isset($_GET['lines'])){
        $params['lines'] = $_GET['lines'];
    }else{
        $params['lines'] = 0;        
    }
    
    if(isset($_GET['no_lines'])){
        $params['no_lines'] = $_GET['no_lines'];
    }else{
        $params['no_lines'] = false;        
    }
    
      
    if(isset($_GET['no_per'])){
        $params['no_per'] = $_GET['no_per'];
    }else{
        $params['no_per'] = false;        
    }      
    
    if(isset($_GET['no_width'])){
        $params['no_width'] = $_GET['no_width'];
    }else{
        $params['no_width'] = false;        
    }      
      
    $config['params'] = $params;
    
    
    
}

function drawForm($moss){
    global $config;
    getParams();
    
    $cut_lines = $config['params']['lines'];
    $no_lines = $config['params']['no_lines'];
    if($no_lines) {
        $no_lines = "checked";
    }else{
        $no_lines = "";
    }
    
    $no_width = $config['params']['no_width'];
    if($no_width) {
        $no_width = "checked";
    }else{
        $no_width = "";
    }
         
    $no_per = $config['params']['no_per'];
    if($no_per) {
        $no_per = "checked";
    }else{
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

function getMossNumber() {
    echo "Moss Number Required";
}

check_tmp();


$lines = 0;
if(isset($_GET['lines'])){
    $lines = $_GET['lines'];
}

if(isset($_GET['moss'])){
    drawForm($_GET['moss']);
    showMoss($_GET['moss'], $lines);
}else{
    getMossNumber();
    drawForm();
}

?>

