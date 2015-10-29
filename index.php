<?php

ini_set('display_errors', 1);
date_default_timezone_set("Brazil/East");

define ("APLICACAO", ":)");

function func_include($dir, $ctx = NULL) {
    return include $dir;
}

function func_include_x($dir, $vars) {
	if ($vars)
		extract($vars); /* cria as variaveis da pagina, setadas pelo controle */
	ob_start();
    include $dir;
	return ob_get_clean();
}

function func_require($dir, $ctx = NULL) {
	require_once ($dir);
}

/* Concexão com o banco */
include_once 'common/bd/Connexao.php';

if (file_exists("install.php")) {
    if (isset($_POST['remove'])) {
	if (isset($_POST['host']) && isset($_POST['port']) && isset($_POST['database']) && isset($_POST['user']) && isset($_POST['pass'])) {
	    try {
		Connexao::createDataBase($_POST['host'], $_POST['port'], $_POST['database'], $_POST['user'], $_POST['pass']);
		rename("install.php", "__install.php");
	    } catch (Exception $ex) {
		header("Location:install.php?msg=".$ex->getMessage()."&app=" . APLICACAO);
	    }
	}
	else 
	    header("Location:install.php?msg=Falha%20na%20configuração%20do%20Banco%20de%20Dados&app=" . APLICACAO);
    }
    else 
	header("Location:install.php?app=" . APLICACAO);
}

/* Classe de funções uteis */
include_once 'common/Utils.php';
include_once 'common/Session.php';

/* classes principais */
include_once 'model/Model.php';
include_once 'controller/Controller.php';
include_once 'view/View.php';

//Session::start("dota");

$view = isset($_GET['view']) && $_GET['view'] ? $_GET['view'] : 'home'; /* pega a visão */
$action = isset($_GET['action']) && $_GET['action'] ? $_GET['action'] : NULL; /* pega a pagina */
$method = isset($_GET['method']) && $_GET['method'] ? $_GET['method'] : NULL; /* pega o methodo a executar antes de abrir a pagina */

$data = NULL;

if ($method != NULL) {
    if (isset($_GET['data'])) {
		$data = (int) $_GET['data'];
    } else {
	if ($_POST)
	    $data['post'] = $_POST;
	if ($_FILES)
	    $data['file'] = $_FILES;
    }
}
//if(empty($_FILES)) $file = $_FILES;

Utils::incluirMC($view); /* Inclui o medelo e controller da visão */


/*echo $view;
  var_dump($action);
  var_dump($method);
  var_dump($data); */
try {
    $obj = new View($view, $action, $data);
	$obj->incluirTemplate();
	$obj->render();
} catch (Exception $exc) {
    echo "<table>" .
    '<thead><tr><th colspan="5" style="background-color: black; color: red; padding: 8px;">' . $exc->getMessage() . "</th></tr></thead>" .
    $exc->xdebug_message .
    "</table>";
}
//$conexao = new PDO('mysql:host=localhost;port=3306;dbname=datamart',"root", "1234");

/* try{
  $resultado = $conexao->prepare("select * from dimensao_academico");
  $resultado->execute(array());
  var_dump($resultado->fetchAll());
  }catch(PDOException $e){
  $this->errors[] =  'error: function queryAll<br/>'.$e->getMessage();
  return NULL;
  } */

//var_dump($obj->depur);
//echo ("<html><head></head><body><![CDATA[".$view."]]></body></html>");
