<!DOCTYPE HTML>
<html>
    <?php
    if (getenv("REMOTE_ADDR") != "127.0.0.1" && getenv("REMOTE_ADDR") != "::1") {
        echo "<p style='text-align: center;'>Acesso Não permitido ao controle do Servidor</p>";
        exit;
    }
    ?>
    <head>
        <title>Teste Start</title>
        <script src="data/jquery-2.1.1.min.js"></script>
        <style type="text/css">
            html {
                height: 100%;
            }
            body {
                text-align: center;
                height: calc(100% - 16px);
            }
            table {
                display: inline-table;
            }
            table td {
                width: 50%;
            }
            table td:nth-child(1) {
                text-align: right;
            }
            table td:nth-child(2) {
                text-align: left;
            }
            div#titulo {
                font-size: 30px;
                font-weight: bold;
            }
            div#painel {
                height: 300px;
            }
            button {
                width: 100px;
                height: 38px;
            }
            pre#saida {
                text-align: left;
                height: calc(100% - 335px);
                margin: 0px 20px;
                padding: 5px;
                box-shadow: 0px 0px 5px black;
                overflow: auto;
            }
            input[type="text"] {
                margin: 0px;
            }
            input#port {
                width: 40px;
            }
            input#dir {
                width: 200px;
            }
            input#comando {
                margin: 5px;
                width: calc(100% - 41px);
                font-family: "courier new" monospace;
            }
        </style>
        <script>
            /** @constant String Link para o arquivo que inicia o servidor */
            const SERVER = "data/startserv.php";
            /** @var WebSocket Web Socket para a conexão com o servidor */
            var conexao = null;
            /** @var String Estado atual da interface ("desativado" | "conectado" | "desconectado" | "testando");*/
            var estadoInterface;
            /** @var int tempo controle de expiração de visualização de mensagens */
            var timeout;
            /** @var String cache de comandos para interagir com o servidor */
            var comandos = [0];

			WSAdmin = new (function () {
				function WSAdmin (subtype, comando) {
					this.subtype = subtype;
					this.comando = comando;
				}
				return WSAdmin;
			})();
			
            /**
             * Método que faz a invocação ao arquivo php que inicia o servidor
             * @param {String} URL local do arquivo php que inicia o servidor
             * @param {boolean} asy determina se será feita uma chamada síncrona (false) ou assincrona (true)
             * @return {void}
             */
            function invovaServ(URL, asy)
            {
                var diretorioPHP = document.getElementById("dir").value;
                var porta = parseInt(document.getElementById("port").value);
                if (diretorioPHP == "")
                {
                    mensagem("Deve ser informado o caminho do executável php", true, false);
                    return;
                }
                if (isNaN(porta) || porta < 1024 || porta > 49151)
                {
                    mensagem("Valor inválido para porta!", true, false);
                    return;
                }
                $.ajax({
                    type: "POST",
                    url: URL,
                    dataType: 'json',
                    data: {"port": porta, "dir": diretorioPHP},
                    // função para de sucesso
                    success: function (resposta) {
                        console.log(resposta);
                        //$("#saida").html(resposta);
                    },
                    // função para erros
                    error: function (msg) {
                        //console.log(msg.responseText);
                        logs(msg.responseText);
                        setEstado("desativado");
                    },
                    async: asy
                });
                setEstado("testando");
                //testarConexao(true);
                timeout = window.setTimeout(function () {
                    testarConexao(true);
                }, 2000);
            }
            /**
             * Exibe uma mensagem
             * @param {String}  msg    o texto da mensagem
             * @param {boolean} isErro destaca a mensagem em vermelho, indicando que é mensagem de erro, se informado 'true'
             * @param {boolean} fix    se true, a mensagem permanece visível; se false, depois de 3 segundos desaparece
             * @return {void}
             */
            function mensagem(msg, isErro, fix)
            {
                var elMens = document.getElementById("mensagens");
                if (isErro)
                    msg = "<span style='color: red;'>" + msg + "</span>";
                elMens.innerHTML = msg;
                if (typeof timeout == "number")
                    window.clearTimeout(timeout);
                if (!fix)
                    timeout = window.setTimeout(function () {
                        elMens.innerHTML = "";
                    }, 3000);
            }
            /**
             * Exibe uma mensagem na area de logs do servidor
             * @param {String} msg o texto para exibir
             * @return {void}
             */
            function logs(msg)
            {
                var saida = document.getElementById("saida");
                saida.innerHTML += msg;
                saida.scrollTop = (saida.scrollWidth == saida.offsetWidth ? saida.scrollHeight - saida.offsetHeight : saida.scrollHeight - saida.offsetHeight + 20);
            }
            /**
             * Testa se já existe um servidor ativo na porta especificada no input da interface
             * @param {boolean} salvaDir indica se o diretório do php.exe será salvo caso o teste de conexão seja bem sucedido
             * @return {void}
             */
            function testarConexao(salvaDir)
            {
                if (conexao != null)
                {

                    return;
                }
                mensagem("Testando...", false, false);
                setEstado("testando");
                var porta = parseInt(document.getElementById("port").value);
                if (isNaN(porta) || porta < 1024 || porta > 49151)
                {
                    mensagem("Valor inválido para porta!", true);
                    return;
                }
                conexao = new WebSocket("ws://" + document.location.hostname + ":" + porta);
                conexao.onopen = function (msg) {
                    setEstado("desconectado");
                    mensagem("", false, false);
                    conexao.close();
                    if (salvaDir)
                        salvaPhpDir();
                };
                conexao.onmessage = function (msg) {
                    var obj = JSON.parse(msg.data);
                    console.log(msg.data);
                    if (obj.error != null && obj.error != "")
                    {
                        mensagem(obj.error, true, true);
                        return;
                    }
                };
                conexao.onerror = function (msg) {
                    mensagem("Servidor indisponível!", true, false);
                    setEstado("desativado");
                };
                conexao.onclose = function (msg) {
                    console.log(msg);
                    conexao = null;
                };
            }
            /**
             * Conecta ao servidor que está respondendo na na porta especificada no input da interface
             * @return void
             */
            function conectar()
            {
                mensagem("Conectando...", false, true);
                setEstado("testando");
                var deuErro = false;
                var porta = parseInt(document.getElementById("port").value);
                if (isNaN(porta) || porta < 1024 || porta > 49151)
                {
                    mensagem("Valor inválido para porta!", true);
                    return;
                }
                conexao = new WebSocket("ws://" + document.location.hostname + ":" + porta);
                conexao.onopen = function (msg) {
                    setEstado("conectado");
                };
                conexao.onmessage = function (msg) {
                    var obj = JSON.parse(msg.data);
                    console.log(msg.data);
                    if (obj.error != null && obj.error != "")
                    {
                        mensagem(obj.error, true, true);
                        return;
                    }
                    switch (obj.type) {
                        case "restart":

                            break;
                        case "login":
                            if (conexao != null && obj.dados != "init")
                                return;
                            var msg = new Object();
                            msg.type = "admin";
							msg.classe = "Object";
							msg.dados = JSON.stringify(new WSAdmin("init", ""));
                            conexao.send(JSON.stringify(msg));
                            mensagem("Conectado!", false, false);
                            break;
                        case "log":
                            if (document.getElementById("habLog").checked)
                                logs(obj.dados);
                            break;
                        case "echo":
                            logs(obj.dados);
                            break;
                    }

                };
                conexao.onerror = function (msg)
                {
                    deuErro = true;
                    setEstado("desativado");
                    mensagem("Falha na conexão com o servidor!", true, true);
                };
                conexao.onclose = function (msg) {
                    if (!deuErro)
                    {
                        mensagem("Desconectado do servidor!", true, true);
                        setEstado("desconectado");
                        deuErro = false;
                    }
                };
            }
            /**
             * Desconecta o WebSocket do servidor;
             * @return void
             */
            function desconectar()
            {
                conexao.close();
                conexao = null;
                setEstado("desconectado");
                mensagem("Desconectado!", false, false);
            }
            /**
             * Desliga o serviço do servidor
             * @return void
             */
            function desligaServ()
            {
                if (conexao != null && conexao.readyState == WebSocket.OPEN)
                {
                    var msg = new Object();
                    msg.type = "admin";
					msg.classe = "Object";
					msg.dados = JSON.stringify(new WSAdmin("matar", ""));
                    conexao.send(JSON.stringify(msg));
                    conexao.close();
                    conexao = null;
                    mensagem("Servidor Finalizado!", false, false);
                }
            }
            /**
             * Altera o estado da interface
             * @param {String} estado ("desativado" | "conectado" | "desconectado" | "testando");
             * @return {void}
             */
            function setEstado(estado)
            {
                var statusLabel = document.getElementById("status");
                var portInput = document.getElementById("port");
                var dirInput = document.getElementById("dir");
                var invocarButton = document.getElementById("invocar");
                var conectarButton = document.getElementById("conectar");
                switch (estado)
                {
                    case "desativado":
                        statusLabel.innerHTML = "Desativado";
                        portInput.disabled = false;
                        dirInput.disabled = false;
                        invocarButton.innerHTML = "Iniciar Servidor";
                        conectarButton.innerHTML = "Conectar";
                        conectarButton.disabled = true;
                        invocarButton.disabled = false;
                        break;
                    case "conectado":
                        statusLabel.innerHTML = "Conectado!";
                        portInput.disabled = true;
                        dirInput.disabled = true;
                        invocarButton.innerHTML = "Parar Servidor";
                        conectarButton.innerHTML = "Desconectar";
                        conectarButton.disabled = false;
                        invocarButton.disabled = false;
                        break;
                    case "desconectado":
                        statusLabel.innerHTML = "Ativado / Admin Desconectado";
                        portInput.disabled = true;
                        dirInput.disabled = true;
                        invocarButton.innerHTML = "Parar Servidor";
                        conectarButton.innerHTML = "Conectar";
                        conectarButton.disabled = false;
                        invocarButton.disabled = true;
                        break;
                    case "testando":
                        //statusLabel.innerHTML = "Desativado";
                        portInput.disabled = true;
                        dirInput.disabled = true;
                        invocarButton.innerHTML = "Testando...";
                        conectarButton.innerHTML = "Conectar";
                        conectarButton.disabled = true;
                        invocarButton.disabled = true;
                        break;
                    default:
                        return;
                }
                estadoInterface = estado;
            }
            /**
             * Obtem o valor da última porta que foi ativa no arquivo
             * @return void
             */
            function obtemPorta()
            {
                var portInput = document.getElementById("port");
                $.ajax({
                    //context: $(this),
                    type: "POST",
                    url: "data/informador.php",
                    dataType: 'json',
                    data: {comando: "port"},
                    // função para de sucesso
                    success: function (resposta) {
                        if (!isNaN(parseInt(resposta)))
                            portInput.value = resposta;
                        else
                            mensagem("Erro na obtenção da porta", false, true);
                    },
                    // função para erros
                    error: function (Msg) {
                        mensagem("Erro na obtenção da porta", false, true);
                    },
                    async: false
                });
            }
            /**
             * Obtem o valor do diretório do php.exe que está salvo no arquivo
             * @return void
             */
            function obtemPhpDir()
            {
                var phpDirInput = document.getElementById("dir");
                $.ajax({
                    //context: $(this),
                    type: "POST",
                    url: "data/informador.php",
                    dataType: 'json',
                    data: {comando: "phpDir"},
                    // função para de sucesso
                    success: function (resposta) {
                        console.log(resposta);
                        if (resposta != "")
                            phpDirInput.value = resposta;
                        else
                            mensagem("Erro na obtenção do diretório do php", false, true);
                    },
                    // função para erros
                    error: function (Msg) {
                        console.log(Msg);
                        console.log("erro");
                        mensagem("Erro na obtenção do diretório do php", false, true);
                    },
                    async: false
                });
            }
            /**
             * Envia o valor do diretório do php.exe do campo de texto para ser salvo no arquivo
             * @return void
             */
            function salvaPhpDir()
            {
                var phpDirInput = document.getElementById("dir");
                $.ajax({
                    //context: $(this),
                    type: "POST",
                    url: "data/informador.php",
                    dataType: 'json',
                    data: {comando: "w-phpDir", phpDir: phpDirInput.value},
                    // função para de sucesso
                    success: function (resposta) {
                        if (resposta != "")
                            mensagem(resposta, false, true);
                    },
                    // função para erros
                    error: function (Msg) {
                        console.log(Msg);
                        mensagem("Erro na obtenção do diretório do php", false, true);
                    },
                    async: false
                });
            }

            function enviaComando(event)
            {
                switch (event.keyCode)
                {
                    case 13:
                        var msg = new Object();
                        msg.type = 'admin';
						msg.classe = "Object";
                        var comando = event.srcElement.value;
                        if (comando == "")
                            return;
                        if (comandos[comandos.length - 1] != comando && comandos[comandos[0]] != comando)
                        {
                            comandos.push(comando);
                            comandos[0] = comandos.length;
                        }
                        else
                            comandos[0]++;
						msg.dados = JSON.stringify(new WSAdmin("eval", comando));
                        conexao.send(JSON.stringify(msg));
                        event.srcElement.value = "";
                        break;
                    case 38:
                        if (comandos[0] > 0)
                        {
                            comandos[0]--;
                            if (comandos[0] == 0)
                                event.srcElement.value = "";
                            else
                                event.srcElement.value = comandos[comandos[0]];
                        }
                        break;
                    case 40:
                        if (comandos[0] < comandos.length)
                        {
                            comandos[0]++;
                            if (comandos[0] == comandos.length)
                                event.srcElement.value = "";
                            else
                                event.srcElement.value = comandos[comandos[0]];
                        }
                        break;
                }
            }
            //$GLOBALS["az"] = ControladorSalas::buscaSala('aaa')->buscaJogadorPorCor('azul'); $GLOBALS["vm"] = ControladorSalas::buscaSala('aaa')->buscaJogadorPorCor('vermelho');$GLOBALS["am"] = ControladorSalas::buscaSala('aaa')->buscaJogadorPorCor('amarelo');
            //global $az, $vm, $am; echo "Azul:\n"; foreach ($az->getCartas() as $car) echo "\t" . $car->getId() . " - " . $car->getFigura() . " - " . $car->getNome() . "\n"; echo "\nVermelho:\n"; foreach ($vm->getCartas() as $car) echo "\t" . $car->getId() . " - " . $car->getFigura() . " - " . $car->getNome() . "\n";echo "\nAmarelo:\n"; foreach ($am->getCartas() as $car) echo "\t" . $car->getId() . " - ". $car->getFigura() . " - " . $car->getNome() . "\n";
        </script>
    </head>

    <body id="body">
        <div id="painel">
            <div id="titulo">Server Administrador</div>
            <table>
                <tr>
                    <td>Status Servidor:</td>
                    <td id="status">Desativado</td>
                </tr>
                <tr>
                    <td>Diretório php.exe:</td>
                    <td><input type="text" id="dir" value="" /></td>
                </tr>
                <tr>
                    <td>Porta:</td>
                    <td><input type="text" id="port" value="" /></td>
                </tr>
                <tr>
                    <td style="text-align: center; height: 35px;" id="mensagens" colspan="2"></td>
                </tr>
                <tr>
                    <td><button id="invocar" onclick="this.innerHTML == 'Iniciar Servidor' ? invovaServ(SERVER, true) : desligaServ()">Iniciar</button></td>
                    <td><button id="conectar" onclick="this.innerHTML == 'Conectar' ? conectar() : desconectar()">Conectar</button>
                </tr>
            </table>

            <br />
            <label>Habilitar Logs</label><input type="checkbox" id="habLog"></input>
            <button style="bottom: 0px;" onclick="$('#saida').html('')">Limpar Log</button>
        </div>
        <pre id="saida"></pre>
        <input type="text" id="comando"></input> 
        <script>
            obtemPhpDir();
            obtemPorta();
            testarConexao(false);
            document.getElementById("comando").onkeyup = enviaComando;
        </script>
    </body>
</html>