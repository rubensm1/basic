<?php

$width = 0;
$height= 0;

/**
 * Controller da Home
 */
class HomeController extends Controller{
    var $name = 'home';
    
    public function index($dados = NULL){
		
		if ($dados){
			global $width, $height;
			$width = $dados['post']["width"];
			$height= $dados['post']["height"];
		}
		
		//$this->uses('Host');
		
		//$host = new Host(array('endereco' => $_SERVER['REMOTE_ADDR'], 'dia' => date("Y-m-d H:i:s") ));
		
		//$host->persist();
    }
}

?>
