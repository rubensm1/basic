<?php

require_once './WebSocket.php';
require_once './EndPointException.php';

class WebSocketImpl extends WebSocket {

    protected function onMessage($obj, &$clientSocket) {
        switch ($obj->type) {
            case "chat":
                $this->enviaDadoSocket($obj, $clientSocket);
                break;
        }
        
    }

    protected function onClose(&$clientSocket) {
        
    }

    protected function onOpen(&$clientSocket) {
        /* enviar comando de aceite ao solicitante */
        $this->enviaDadoSocket(array('type' => 'login', 'subtype' => 'init'), $clientSocket);
    }

    protected function onError($error, &$clientSocket) {
        
    }

}

$aa = new WebSocketImpl();

$aa->listen(5555);
