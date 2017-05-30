<?php

require_once 'WSException.php';

/** 
 * Classe para comunicação entre cliente e servidor através de WebSocket
 */

class WSMensagem {
	
	private $type;
	private $dados;
	private $erro;
	
	public function WSMensagem($msg) {
		$this->type = $msg->type;
		if (!$this->hasErroMensagem($msg->erro)) 
			$this->encode($msg->classe, $msg->dados);
	}
	
	public function encode($classe, $dados) {
		if (strcasecmp($classe, "Bool") == 0 || 
			strcasecmp($classe, "Boolean") == 0 || 
			strcasecmp($classe, "Int") == 0 ||
			strcasecmp($classe, "Integer") == 0 ||
			strcasecmp($classe, "Float") == 0 ||
			strcasecmp($classe, "Double") == 0 ||
			strcasecmp($classe, "String") == 0 ) {
			$this->dados = eval ("($classe) '$dados'");	
		}
		elseif (strcasecmp($classe, "Array") == 0) {
			$this->dados = json_decode ($dados);
		}
		elseif (strcasecmp($classe, "Object") == 0) {
			$this->dados = (object) json_decode ($dados);
		}
		elseif (class_exists($classe)) {
			$this->dados = new $classe ($dados);
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