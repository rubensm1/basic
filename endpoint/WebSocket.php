<?php

require_once 'WSException.php';
require_once 'WSMensagem.php';

/**
 * @author rubensmarcon
 */
abstract class WebSocket {

    /** @var boolean se alguma conexão está ativa */
    private $conectado;

    /** @var int número da porta */
    private $port;

    /** @var resource Socket que escuta conexões */
    private $serverSocket;
    
    /** @var resource Socket para interface de administração */
    protected $adminSocket;

    /** @var array<resource> array de sockets */
    private $sockets;

    public function WebSocket() {
	    $this->port = NULL;
	    $this->conectado = FALSE;
    }

    /**
     * Começa a escutar conexões e processar as demais funcionalidades
     * @param int $port número da porta
     * @throws WSException
     */
    public function listen($port) {
	
		$this->port = $port;
		
		/* @var resource cria o socket que escutará conexões */
		$this->serverSocket = socket_create(AF_INET, SOCK_STREAM, getprotobyname('TCP'));
		
		/* configuração utilizada apenas se queira reutilizar a porta ¯\_(ツ)_/¯  */
		/*if (!socket_set_option($this->serverSocket, SOL_SOCKET, SO_REUSEADDR, 1)) { 
			echo socket_strerror(socket_last_error($this->serverSocket)); 
			exit; 
		} */

		/* seta o host e a porta */
		if (!@socket_bind($this->serverSocket, 0, $this->port))
			throw new WSException("socket_bind() falhou: resposta: " . socket_strerror(socket_last_error($this->serverSocket)), WSException::BIND_ERROR);

		/* inicia a escuta de conexões */
		if (!@socket_listen($this->serverSocket)) 
			throw new WSException("socket_listen() falhou: resposta: " . socket_strerror(socket_last_error($this->serverSocket)), WSException::LISTEN_ERROR);
		
		/* cria um array de sockets e adiciona o serverSocket no array */
		$this->sockets = array($this->serverSocket);
		
		$this->loopPrincipal();
		
		/* Fecha todos os sockets e encerra a aplicação */
		foreach ($this->sockets as $socket)
		{
			if (get_resource_type($socket) == "Socket")
			socket_close($socket);
		}
    }
    
    protected abstract function onMessage($obj, &$clientSocket);
    
    protected abstract function onOpen(&$clientSocket);
    
    protected abstract function onClose(&$clientSocket);
    
    protected abstract function onError($error, &$clientSocket);

    private function loopPrincipal() {
		/* Inicia um loop infinito para ir sempre checando os sockets e tratando os eventos */
		while (true)
		{
			/** @var resource   cria uma copia do array original de sockets, para manipulá-los sem alterar o original */
			$changedSockets = $this->sockets;

			/* obtêm todos os sockets que "ouviram" algo */
				$null = NULL;
			socket_select($changedSockets, $null, $null, 0, 10);

			$this->novaConexao($changedSockets);

			// itera entre os outros sockets "ouvintes" 
			foreach ($changedSockets as $clientSocket)
			{
			// Le um socket e toma uma decisão basada no retorno deste 
			$break = $this->loopLeituraSocket($clientSocket, $this->sockets);
			if ($break == 1)
				break;
			if ($break == 2)
				return;

			$this->encerraConexao ( $clientSocket, $break == 3);
			}
		}
    }
    
    private function loopLeituraSocket($clientSocket, &$sockets){

        /** @var boolean variavel de controle de fim de processo do servidor */
        $matarServer = false;

        /* Lê os dados do socket 
         * @link http://php.net/manual/pt_BR/function.socket-recv.php  */
        while ($buf = socket_read($clientSocket, 1024)) {
            //logServidor( "\n".$buf);
            $received_text = $this->unmask($buf);
            $this->logServidor("\nreceive: " . $received_text . "\n");
            if ($received_text == "") {
                if ($this->adminSocket == $clientSocket) {
                    return 3;
                } else {
                    /* @link http://php.net/manual/pt_BR/function.get-resource-type.php */
                    if (get_resource_type($clientSocket) == "Socket")
                    /* @link http://php.net/manual/pt_BR/function.socket-close.php */
                        socket_close($clientSocket);
                    return 0;
                }
            }
            $obj = json_decode($received_text);
            if ($obj == NULL)
                return 3;

            try {
                if ($obj->type == "admin") {
                    /* obtêm o endereço de IP do socket */
                    socket_getpeername($clientSocket, $ip);
                    /* Bloqueio de ip. Só permite que o Server Administrador seja usado em localhost */
                    if ($ip != "127.0.0.1" && $ip != "::1")
                        throw new Exception("Não Permitido");
                    /* bloqueio para que exista apenas um conectado */
                    if ($this->adminSocket != NULL && $this->adminSocket != $clientSocket)
                        throw new Exception("Falha! Já existe um administrador utilizando!");
                    if ($obj->subtype == "init")
                        $this->adminSocket = $clientSocket;
                    elseif ($obj->subtype == "matar")
                        $matarServer = true;
                    elseif ($obj->subtype == "eval")
                    {
                        ob_start();
                        eval($obj->comando);
                        //$resp = (object) array("type" => 'echo', "echo" => ob_get_clean());
                        $resp = (object) array("type" => 'echo', "echo" => ob_get_clean());
                        $this->enviaDadoSocket($resp, $clientSocket);
                        //ob_clean();
                    }
                }
                else
                    /* Executa instruções baseadas nos dados recebidos ou redireciona-os para o tratador apropriado */
                    $this->onMessage($obj, $clientSocket);
                        
                if ($matarServer)
                    return 2;
            } catch (Exception $ex) {
                /* seta a variavel "error" com o erro capturado */
                $obj->error = $ex->getMessage();
                $this->enviaDadoSocket($obj, $clientSocket);
            }
            return 1; //---------------------
        }
        return 3;
    }

    /**
     * Checa se o $serverSocket está na lista dos "ouvintes" e realiza os procedimentos para uma nova conexão
     * @param array<resources> $changedSockets array de sockets
     * @return boolean Se houve uma nova conexão ou não
     * @throws WSException
     */
    private function novaConexao(&$changedSockets) {
	
		if (in_array($this->serverSocket, $changedSockets))
		{
			/* aceita a nova conexão e cria um socket para a seção com este novo usuário */
			$socketNovo = socket_accept($this->serverSocket);
			if (!$socketNovo)
			throw new WSException("socket_accept() falhou: resposta: " . socket_strerror(socket_last_error($this->serverSocket)));

			/* adiciona o novo socket no array de sockets */
			$this->sockets[] = $socketNovo;
			/* lê os primeiros dados enviados pelo WebSocket do browser, responsáveis pelo 'handshaking' */
			$header = socket_read($socketNovo, 1024);
			if ($header == "") {
				$this->onError(new WSException("socket_read() falhou: resposta: " . socket_strerror(socket_last_error($socketNovo))), $socketNovo);
				return FALSE;
			}
			//logServidor("\n$header\n");

			/* realiza o processo de 'handshaking' entre o cliente e o servidor */
			$this->performHandshaking($header, $socketNovo);

			/* obtêm o endereço de IP do novo socket 
			socket_getpeername($socketNovo, $ip);

			/* Registra novo socket conectado no array de sockets sem sala, do controlador de salas 
			ControladorSalas::addNotGameSocket($socketNovo);
			logServidor("\nIp $ip solicita conexão\n");*/

			$this->onOpen($socketNovo);
				
			/* remove o $serverSocket da lista de sockets "ouvintes", pois já foi tratado */
			$foundSocket = array_search($this->serverSocket, $changedSockets);
			unset($changedSockets[$foundSocket]);
			return TRUE;
		}
		else 
			return FALSE;
    }
    
    private function encerraConexao(&$clientSocket, $force = FALSE) {
		//detecta um socket desconectado 
		if ($force || @socket_read($clientSocket, 1024) === false)
		{
			$foundSocket = array_search($clientSocket, $this->sockets);
			if (get_resource_type($clientSocket) == "Socket")
			socket_close($clientSocket);
				
			$this->onClose($clientSocket);
				
			// remove o socket desconectado do array $sockets 
			unset($this->sockets[$foundSocket]);
			if ($clientSocket == $this->adminSocket)
			$this->adminSocket = NULL;
		}
    }

    /**
     * Procedimento de 'handshaking'
     * @link http://en.wikipedia.org/wiki/WebSocket#WebSocket_protocol_handshake
     * @param string $headerRecebido  cabeçalho recebido do cliente
     * @param resource $clientSocket  socket do cliente
     * @return void  
     */
    private function performHandshaking($headerRecebido, $clientSocket, $host = "localhost") {
		$headers = array(); $this->logServidor($headerRecebido);
		$lines = preg_split("/\r\n/", $headerRecebido);
		$matches = NULL;
		foreach ($lines as $line) {
			$line = chop($line);
			if (preg_match('/\A(\S+): (.*)\z/', $line, $matches))
			$headers[$matches[1]] = $matches[2];
		}
		//var_dump ($headers);
		$secKey = $headers['Sec-WebSocket-Key'];
		$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
		/* cabeçalho de resposta */
		$upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
			"Upgrade: websocket\r\n" .
			"Connection: Upgrade\r\n" .
			"WebSocket-Origin: $host\r\n" .
			"WebSocket-Location: ws://$host:" . $this->port . "/index.php\r\n" .
			"Sec-WebSocket-Accept:$secAccept\r\n\r\n";
		socket_write($clientSocket, $upgrade, strlen($upgrade));
    }

    /**
    * Envia dados de uma variável para um socket
    * @param mixed    $obj     dados
    * @param resource $socket  socket para enviar
    * @return void  
    */
    protected function enviaDadoSocket($obj, $socket)
    {
        $this->logServidor("\nsend: " . json_encode($obj) . "\n");
        $mensagem = $this->mask(json_encode($obj));
        /* @link http://php.net/manual/pt_BR/function.socket-write.php */
        @socket_write($socket, $mensagem, strlen($mensagem));
    }

   /**
    * Envia dados de uma variável para uma lista de sockets
    * @param mixed            $obj      dados
    * @param array<resource>  $sockets  lista de sockets para enviar
    * @return void  
    */
    protected function enviaDadoMultSockets($obj, $sockets)
    {
        $this->logServidor("\nsend: " . json_encode($obj) . "\n");
        $mensagem = $this->mask(json_encode($obj));
        $msgLen = strlen($mensagem);
        foreach ($sockets as $socket)
           @socket_write($socket, $mensagem, $msgLen);
    }

   /**
    * Envia uma mensagem para um socket
    * @param string    $mensagem   mensagem a ser enviada
    * @param int       $msgLen     comprimento da mensagem 
    * @param resource  $socket     socket para enviar
    * @return void  
    */
    protected function enviaMensagemSocket($mensagem, $msgLen, $socket)
    {
        socket_write($socket, $mensagem, $msgLen);
    }
    
    /**
     * Decodifica uma mensagem recebida do WebSocket do cliente
     * @param string  $text dados "brutos" lidos do WebSocket do cliente 
     * @return string   mensagem legivel
     */
    private function unmask($text) {
		$length = ord($text[1]) & 127;
		if ($length == 126) {
			$masks = substr($text, 4, 4);
			$data = substr($text, 8);
		} elseif ($length == 127) {
			$masks = substr($text, 10, 4);
			$data = substr($text, 14);
		} else {
			$masks = substr($text, 2, 4);
			$data = substr($text, 6);
		}
		$textRet = "";
		for ($i = 0; $i < strlen($data); ++$i) {
			$textRet .= $data[$i] ^ $masks[$i % 4];
		}
		return $textRet;
    }

    /**
     * Codifica a mensagem da forma apropriada para ser transferida para o cliente
     * @param string $text  texto legível 
     * @return string   dados codificados de forma adequada ao protocolo dos WebSockets (somente a inclusão de um cabeçalho, neste caso)
     */
    private function mask($text) {
		$b1 = 0x80 | (0x1 & 0x0f);
		$length = strlen($text);

		if ($length <= 125)
			$header = pack('CC', $b1, $length);
		elseif ($length > 125 && $length < 65536)
			$header = pack('CCn', $b1, 126, $length);
		elseif ($length >= 65536)
			$header = pack('CCNN', $b1, 127, $length);
		return $header . $text;
    }

    protected function logServidor($text) {
        echo $text;
        if ($this->adminSocket != NULL && get_resource_type($this->adminSocket) == "Socket")
        {
            $log = (object) array("type" => 'log', "log" => $text);
            $mensagem = $this->mask(json_encode($log));
            $this->enviaMensagemSocket($mensagem, strlen($mensagem), $this->adminSocket);
        }
    }
    
}
