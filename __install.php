<!DOCTYPE html>
<html lang="pt-br">
    <head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Instalação</title>
	<style type="text/css">
	    body{
		padding: 5px;
		border: solid 1px;
		border-radius: 4px;
	    }
	    div.msg {
		background: #F1F1F1; 
		border: solid 1px #000; 
		width: 400px; 
		height: 100px; 
		border-radius: 4px;
		text-align: center;
		color: red;
		margin: auto;
		padding: 5px;
	    }
	    form {
		text-align: center;
	    }
	    table {
		margin: 10px auto;
		border: solid 1px #000;
		border-radius: 4px;
		padding: 3px;
	    }
	    td:first-child {
		text-align: right;
	    }
	    td:last-child, td:last-child input {
		text-align: left;
	    }
	</style>
    </head>
    <body>
<?php
ini_set('display_errors', 1);

$app = isset($_GET['app']) ? $_GET['app'] : '';

?>

<div style="text-align: center;">
    <h1>Configuração do banco de dados</h1>
    <form action="index.php" method="POST">
	<input type="hidden" name="remove" value="true" />
	<table>
	    <tr>
		<td>Host:</td>
		<td><input name="host" value="localhost" /></td>
	    </tr>
	    <tr>
		<td>Porta:</td>
		<td><input name="port" value="3306" /></td>
	    </tr>
	    <tr>
		<td>Database:</td>
		<td><input name="database" value="<?php echo $app; ?>" /></td>
	    </tr>
	    <tr>
		<td>Usuário:</td>
		<td><input name="user" value="root" /></td>
	    </tr>
	    <tr>
		<td>Senha:</td>
		<td><input name="pass" type="password" value="" /></td>
	    </tr>
	    <tr>
		<td colspan="2" style="text-align: center;"><input value="Enviar" type="submit"/></td>
	    </tr>
	</table>
    </form>
</div>

<?php

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

if ($msg != '') {
    ?>
        <div class="msg">
            <?php
            echo $msg;
            ?>
        </div>

    <?php
}
?>
    </body>
</html>