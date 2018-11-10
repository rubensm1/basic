<?php

/**
 * @author rubensmarcon
 */
class WSException extends Exception {
    
    const OK = 0;
    const BIND_ERROR = 1;
    const LISTEN_ERROR = 2;
	const ENCODE_ERROR = 3;
	const ADMIN_ERROR = 4;


    // Redefine a exceção de forma que a mensagem não seja opcional
    public function __construct($message, $code = 0, $previous = null) {
        // código
    
        // garante que tudo está corretamente inicializado
        parent::__construct($message, $code, $previous);
    }

    // personaliza a apresentação do objeto como string
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public function customFunction() {
        echo "Uma função específica desse tipo de exceção\n";
    }
}