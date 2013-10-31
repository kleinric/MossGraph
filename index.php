<?php

require_once('./config.php');


class graph{
    public $adj;
    public $gv;

    public function graph(){
        $this->adj = array();
    }

    public function addEdge($name1, $name2, $weight){
        if(!array_key_exists($name1, $this->adj)){
            $this->adj[$name1] = array();
        }
        if(!array_key_exists($name2, $this->adj)){
            $this->adj[$name2] = array();
        }

        $this->adj[$name1][$name2] = $weight;
        $this->adj[$name2][$name1] = $weight;

    }

    public function toString(){
        var_dump($this->adj);
    }

    public function dot($lines){
        $text  = "strict graph myGraph {\nsize=\"(20,100)\";\n ";
        foreach($this->adj as $s => $d){
            foreach($d as $v => $w){
                if($w >= $lines){
                    $text .= "\t\t\"" . $s . "\" -- \"" . $v . "\"[label=$w];\n";
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

        exec("unflatten -f  -l 100 $filename.dot | /usr/bin/dot -Tpng -o $filename.png");

        return "$filename.png";
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

        $pattern = "|\./|";
        $name1 = trim(preg_replace($pattern, "", $name1));
        $name2 = trim(preg_replace($pattern, "", $name2));
        $pattern = "| \(..*%\)|";
        $name1 = trim(preg_replace($pattern, "", $name1));
        $name2 = trim(preg_replace($pattern, "", $name2));
        $linem = trim($linem);

        $linematches->addEdge($name1, $name2, $linem);
    }

    $filename = $linematches->image($result, $lines);
    header("Content-type: image/png");
    //$image=imagecreatefromjpeg($_GET['img']);
    //imagejpeg($image);
    readfile($filename);
}

function check_tmp() {
    global $config;

    if (!file_exists($config['tmp'])) {
        mkdir($config['tmp'], 0777);
    }
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
    showMoss($_GET['moss'], $lines);
}else{
    getMossNumber();
}

?>

