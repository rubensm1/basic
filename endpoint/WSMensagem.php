<?php

require_once 'WSException.php'

/** 
 * Classe para comunica��o entre cliente e servidor atrav�s de WebSocket
 */

class WSMensagem {
	
	private $type;
	private $dados;
	private $erro;
	
	public WSMensagem($msg) {
		$this->type = $msg->type;
		if (!$this->hasErroMensagem($msg->erro)) 
			$this->encode($msg->classe, $msg->dados);
	}
	
	public encode($classe, $dados) {
		if (class_exists($classe)) {
			$this->dados = new $classe ($dados);
		}
		else {
			$this->dados = NULL;
			$this->erro = new WSException("Classe n�o identificada!", WSException::ENCODE_ERROR);
		}
	}
	
	public hasErroMensagem ($erro) {
		if ($erro != "") 
			$this->erro = new WSException ($erro, WSException::ENCODE_ERROR);
		else
			$this->erro = NULL;
	}
	
}