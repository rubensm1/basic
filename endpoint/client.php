<html>
    <head>
        <script>
            var ClientWebSocket;
			ClientWebSocket = (function (){
				function ClientWebSocket() {
					this.porta = 5555;
					//this.conexao = new WebSocket("ws://"+document.location.hostname+":"+this.porta);
					this.conexao = new WebSocket("ws://"+document.location.hostname+"/basic/ws");
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
        <input id="inputMensagem" type="text" value="" /> <button id="iniciarClient">Start</button>
        <pre id="conteudoMensagem">
            
        </pre>
        <script>
            var conec = null;
            document.getElementById("inputMensagem").onkeyup = function (event) {
                if (conec && event.keyCode == 13 && this.value.trim() != "") 
                {
                    conec.chat(this.value); 
                    this.value='';
                }
            };
			document.getElementById("iniciarClient").onclick = function () {
				conec = new ClientWebSocket();
				this.setAttribute("disabled","");
			}
        </script>
    </body>
</html>


