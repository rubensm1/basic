<?php

require_once 'WSException.php';

/** 
 * Classe para comunicação entre cliente e servidor através de WebSocket
 */

class WSMensagem {
	
	public $type;
	public $dados;
	public $erro;
	
	public function WSMensagem($msg, $classe = NULL, $dados = NULL, $erro = NULL, $preencode = FALSE) {
		if (gettype($msg) == "string") {
			$this->type = $msg;
			if (!$this->hasErroMensagem($erro)) 
				$this->encode($classe, $dados, $preencode);
		}
		else {
			$this->type = $msg->type;
			if (!$this->hasErroMensagem(isset ($msg->erro) ? $msg->erro : "")) 
				$this->encode($msg->classe, $msg->dados, $preencode);
		}
	}
	
	public function encode($classe, $dados, $preencode = FALSE) {
		if (strcasecmp($classe, "Bool") == 0 || 
			strcasecmp($classe, "Boolean") == 0 || 
			strcasecmp($classe, "Int") == 0 ||
			strcasecmp($classe, "Integer") == 0 ||
			strcasecmp($classe, "Float") == 0 ||
			strcasecmp($classe, "Double") == 0 ||
			strcasecmp($classe, "String") == 0 ) {
			eval ('$this->dados' . " = ($classe) '$dados';");
		}
		elseif (strcasecmp($classe, "Array") == 0) {
			$this->dados = json_decode ($dados, true);
		}
		elseif (strcasecmp($classe, "Object") == 0) {
			$this->dados = json_decode ($dados);
		}
		elseif (class_exists($classe)) {
			$this->dados = $preencode ? $dados : new $classe (json_decode($dados,true));
		}
		else {
			$this->dados = NULL;
			$this->erro = new WSException("Classe não identificada!", WSException::ENCODE_ERROR);
		}
	}
	
	public function hasErroMensagem ($erro) {
		if ($erro != "") 
			$this->erro = new WSException ($erro, WSException::ENCODE_ERROR);
		else
			$this->erro = NULL;
	}
	
}