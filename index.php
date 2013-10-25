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
        $text = $this->dot($lines);

        unlink("/tmp/moss/$code.dot");
        unlink("/tmp/moss/$code.png");
        file_put_contents ("/tmp/moss/$code.dot", $text);

        exec("unflatten -f  -l 100 /tmp/moss/$code.dot | /usr/bin/dot -Tpng -o /tmp/moss/$code.png");

        return "/tmp/moss/$code.png";
    }


}

function showMoss($result, $lines){
    $doc = new DOMDocument();

    $context = array(
        'http' => array(
                'proxy' => 'tcp://127.0.0.1:3128',
                'request_fulluri' => true,
        ),
    
    );
    $sContext = stream_context_create($context);

    $source = file_get_contents("http://moss.stanford.edu/results/$result/", false, $sContext);
    //$source = file_get_contents("results.html");
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

function getMossNumber(){
    echo "Moss Number Required";

}

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

