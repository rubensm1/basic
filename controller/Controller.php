<?php



/**
 * Classe responsável pela manibulação dos dados.<br/>
 * Suas classes filhas devem conter as <b>Actions</b><br/>
 * Actions são metodos que sempre são executados <br/>
 * Quando a pagina referente a action é chamata<br/>
 * A action é executada.<br/>
 * <b>Metodos</b> podem ser chamados quando uma pagina é aberta
 * Os metodos são executados antes da 
 * */
abstract class Controller {

    public $viewVars = array();
    protected $model;
    protected $uses = array();
    protected $name;
    
    function Controller() {

        $this->incluirModel();/*inclui o model*/
        $this->setTitulo($this->name);/*seta o titulo da pagina*/
    }

    /**
     * Inclui o model do correspondente ao controller
     * */
    public function incluirModel($model = NULL) {
        $model = ucfirst($this->name);
        $this->model = new $model();/*instansia o model*/
    }

    /**
      Inclui modulos no controller.
      @param string $model Nome do Modelo a ser utilizado.
      @param String $nivel numero de diretórios acima do atual em "../"s
      @return objectModel O novo modelo pode ser acessado pelo atributo $uses.
     * */
    protected function uses($model, $nivel = '') {
        if(!isset($this->uses[$model])){/*verifica se o model ja foi incluso*/
            //include_once $nivel . 'model/' . $model . '.php';/*inclui o model*/
			//func_require($nivel . 'model/' . ucfirst($model) . '.php', $this);
			Utils::incluir($model, "Model", $nivel);
            $nameModel = ucfirst($model);
            $classModel = $model; /*claseModel*/
            $this->uses[$model] = $nameModel; /*adiciono o model na lida te models utilizados*/
            $this->$nameModel= new $classModel(); /*instansia o model*/
        }
    }

    /* retorna o nome do model */
    protected function getName() {
        return get_class($this->model);
    }

    /**
     * Seta as Variáveis a serem utilizadas na View.<br/>
     * @param string $var <p>Nome da variavel. Pode ser uma <b>string</b> 
     * ou <b>array</b> contendo os nomes das variáveis.</p>
     * @param tipo $value <p>Valor da variável. Pode ser de qualquer 
     * tipo ou uma <b>array</b> contendo os valores das variáveis</p>
     * */
    public function set($var, $value = NULL) {
        if (is_array($var)) {/*verifica se é array*/
            if (is_array($value)) {/*caso sejam duas arrays*/
                $data = array_combine($var, $value); /*pimeira array é a chave a segunda é o valor*/
            } else {
                $data = $var;/*adiciona o arry*/
            }
        } else {
            $data = array($var => $value);/* cria array*/
        }
        $this->viewVars = array_merge($this->viewVars, $data); /*adciona na lista de variaveis*/
    }

    /**
     * Seta o titulo da página
     * @param string $titulo Titulo da Página
     * */
    public function setTitulo($titulo) {
        $this->set('titulo', APLICACAO . " - ". $titulo);
    }

}
