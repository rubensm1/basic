<?php

/**
 * Classe com algumas funções uteis;
 */

class Utils {

    /**
     * Inclui uma classe na pagina.
     * @param string $classe nome da classe a ser incluida 
     * @param string $tipo tipo de classe (Model ou Controller)
     * @param string $nivel diretorios que devem ser retornados em "../"
     */
    public static function incluir($classe, $tipo, $nivel = '') {
        /*cria o link para inclusao de arquivos*/
        $url = $nivel . $tipo . '/' . ucfirst($classe) . (ucfirst($tipo) != "Model" ? ucfirst($tipo) : "") . '.php';
        include_once $url;
    }

    /**
     * Inclui Model e Controller de uma classe
     * @param String $classe Nome da classe a ser inserida
     */
    public static function incluirMC($classe) {
        $model = 'model/' . ucfirst($classe) . '.php'; /*cria o caminho para incluir o modelo*/
        $controller = 'controller/' . ucfirst($classe) . 'Controller.php'; /* cira o caminho para incluir o controle*/
        include_once $model;
        include_once $controller;
    }

}

?>
