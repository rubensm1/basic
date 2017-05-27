<?php

/**
 * <b>Classe de Visão</b><br/>
 * Responsavel por chamar o template e as paginas<br/>
 * assim como as <b>Actions</b> de cada pagina.<br/>
 * e se necess�rio os <b>Methodos</b>
 * */
class View {

    public $vars = array();
    public $controller;
    public $action;
    public $page = 'page/default.php';
    public $error = '';
    protected $name;
    protected $template = 'default';
    private $html = NULL;

    function View($name, $action = NULL, $dados = NULL, $ajax = FALSE) {

		$this->name = $name;
		$this->incluirController();
		$action = is_string($action) ? $action : 'index';
		$this->action = $action;

		if (method_exists($this->controller, $action)) { //verifica que existe aquela action
			if ($ajax) {
				$this->html = $this->controller->$action($dados);
			} else {
				$this->controller->$action($dados); //Chama a action da visão
				$this->vars = $this->controller->viewVars; //pega as variáveis da pagina
				$this->setPage(); //seta a pagina 
			}
		} else {
			$this->error .= 'A classe controller ' . $this->name . 'Controller não possui o metodo ' . $this->action . '()';
		}
		//if (!$ajax)
		//    $this->incluirTemplate();

		//$this->render();

		//Utils::pa($res);
    }

    /**
     * Inclue o Controller da View 
     * */
    public function incluirController() {
		$controller = ucfirst($this->name) . 'Controller';
		$this->controller = new $controller;
    }

    /**
     * Inclue o Template 
     * */
    public function incluirTemplate() {
		$vars = $this->vars;
		$vars['page'] = $this->page;
		$vars['name'] = $this->name;
		$this->html = func_include_x ('common/template/' . $this->template . '.php', $vars);
    }

    /**
     * Mostra a pagina
     * */
    public function render() {
		if ($this->error)
			throw new Exception($this->error);
		echo $this->html;
    }

    /**
     * Indica a pagina.
     * */
    public function setPage() {
		$this->page = 'view/' . $this->name . '/' . $this->action . '.php';
    }

}
