<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

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

    public function dot(){
        $text  = "strict graph myGraph {\nsize=\"(20,100)\";\n ";
        foreach($this->adj as $s => $d){
            foreach($d as $v => $w){
                $text .= "\t\t\"" . $s . "\" -- \"" . $v . "\";\n";
            }

        }
        $text .= "}";

        return $text;
    }

    public function image(){
        $text = $this->dot();

        unlink("/tmp/mossp.dot");
        unlink("/tmp/mossp.png");
        file_put_contents ("/tmp/mossp.dot", $text);

        exec("unflatten -f  -l 100 /tmp/mossp.dot | /usr/bin/dot -Tpng -o /tmp/mossp.png");
    }


}

$doc = new DOMDocument();

$source = file_get_contents("results.html");
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

$linematches->image();
header("Content-type: image/png");
//$image=imagecreatefromjpeg($_GET['img']);
//imagejpeg($image);
readfile("/tmp/mossp.png");
?>

