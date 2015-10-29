<?php

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
/* Classe de funções uteis */
include_once 'common/Utils.php';
include_once 'common/Session.php';

/* classes principais */
include_once 'model/Model.php';
include_once 'controller/Controller.php';
include_once 'view/View.php';

$view = isset($_GET['view']) && $_GET['view'] ? $_GET['view'] : 'home'; /* pega a visão */
$action = isset($_GET['action']) && $_GET['action'] ? $_GET['action'] : NULL; /* pega a pagina */

$data = NULL;

if (isset($_GET['data'])) {
    $data = (int) $_GET['data'];
} else {
    if ($_POST)
	$data = $_POST;
}

Utils::incluirMC($view);

$obj = new View($view, $action, $data, TRUE);
$obj->render();