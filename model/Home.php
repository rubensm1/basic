<?php
/**
 * Classe homeModel
 * Não utiliza tabela
 */
class Home extends Model{
	
	protected static $useTable = FALSE;
	
	function Home($data = NULL) {
		parent::__construct();
	}
}

?>
