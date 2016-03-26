<?php

/**
 * Classe com algumas funções uteis;
 */
//include_once ('common/errors/404.php');
//include_once ('common/errors/500.php');
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
		
		$errors = "";
		ob_start();
		include_once ($url);
		$errors .= ob_get_clean();
		if ($errors) {
			header($_SERVER["SERVER_PROTOCOL"]." 500 Internal Server Error");
			echo func_include_x ('common/template/default.php', ["page"=>"common/errors/500.php","titulo"=>"Internal Server Error","echo"=>$errors]);
			exit;
		}
    }

    /**
     * Inclui Model e Controller de uma classe
     * @param String $classe Nome da classe a ser inserida
     */
    public static function incluirMC($classe) {
        $model = 'model/' . ucfirst($classe) . '.php'; /*cria o caminho para incluir o modelo*/
        $controller = 'controller/' . ucfirst($classe) . 'Controller.php'; /* cira o caminho para incluir o controle*/
        
		$errors = "";
		ob_start();
		include_once ($model);
		include_once ($controller);
		$errors .= ob_get_clean();
		if ($errors) {
			header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
			echo func_include_x ('common/template/default.php', ["page"=>"common/errors/404.php","titulo"=>"Not Found","echo"=>$errors]);
			exit;
		}
    }

}

?>
