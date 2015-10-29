<html>
    <head>
        <script>
            var ClientWebSocket;
            ClientWebSocket = (function (){
                function ClientWebSocket() {
                    this.porta = 5555;
                    this.conexao = new WebSocket("ws://"+document.location.hostname+":"+this.porta);
                    var thisLocal = this;
                    this.conexao.onopen = function(msg) {
                        
                    };
                    this.conexao.onmessage = function(msg){
                        console.log(msg.data);
                        var obj = JSON.parse(msg.data);
                        if (obj.error != null && obj.error != "")
                        {
                            //thisLocal.panel.abrir(obj.error, true);
                            return;
                        }
                        switch(obj.type) {
                            case "chat":
                                document.getElementById("conteudoMensagem").innerHTML += "\n" + obj.mensagem;
                                //thisLocal.chat.conector(obj);
                                break;
                        }
                    };
                    this.conexao.onerror = function(msg) {
                        console.error(msg)
                    }; 
                    this.conexao.onclose = function(msg){
                        console.log("Conexão fechada\n" + msg);
                    };
                }
                ClientWebSocket.prototype.chat = function (mensagem) {
                    var msg = new Object();
                    msg.type = "chat";
                    msg.mensagem = mensagem; 
                    this.conexao.send(JSON.stringify(msg));
                };
	
                return ClientWebSocket;
            })();
            
        </script>
    </head>
    <body>
        <pre id="conteudoMensagem">
            
        </pre>
        <input id="inputMensagem" type="text" value="" />
        <script>
            var conec = new ClientWebSocket();
            document.getElementById("inputMensagem").onkeyup = function (event) {
                if (event.keyCode == 13 && this.value.trim() != "") 
                {
                    conec.chat(this.value); 
                    this.value='';
                }
            };
        </script>
    </body>
</html>


