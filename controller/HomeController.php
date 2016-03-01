<?php

/**
 * Controller da Home
 */
class HomeController extends Controller{
    
	var $name = 'home';
    
    public function index($dados = NULL) {
		$this->set("dados", "Issae!");
    }
}

?>
