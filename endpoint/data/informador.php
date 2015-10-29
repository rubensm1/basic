<?php

$comando = isset($_POST["comando"]) ? strip_tags($_POST["comando"]) : "";

$resposta = "";

switch ($comando)
{
    case "port":
        $filename = 'port.txt';
        $handle = @fopen($filename, 'r');
        if ($handle === FALSE)
            $resposta = "Falha na abertura do arquivo $filename";
        else
        {
            $resposta = json_encode(fread($handle, filesize($filename)));
            fclose($handle);
        }
        break;
    case "phpDir":
        $filename = 'phpDir.txt';
        $handle = fopen($filename, 'r');
        if ($handle === FALSE)
            $resposta = "Falha na abertura do arquivo $filename";
        else
        {
            $resposta = json_encode(fread($handle, filesize($filename)));
            fclose($handle);
        }
        break;
    case "mapas":
        include_once '../bd/Connexao.php';
        include_once '../model/Model.php';
        include_once '../model/mapaModel.php';
        include_once '../controller/Controller.php';
        include_once '../controller/mapaController.php';

        $mapasCont = new mapaController();
        $mapas = $mapasCont->select('id , nome');
        $mapasEd = array();

        foreach ($mapas as $mapa)
            $mapasEd[$mapa['id']] = $mapa['nome'];

        $resposta = json_encode($mapasEd);
        break;
    case "w-port":
        $port = isset($_POST["port"]) ? strip_tags($_POST["port"]) : "";
        if ($port != "")
        {
            $handle = @fopen("port.txt", 'w');
            if ($handle === FALSE)
            {
                $resposta = "Falha na abertura do arquivo port.txt";
            }
            else
            {
                fwrite($handle, $port);
                fclose($handle);
				$resposta = json_encode("");
            }
        }
        else
            $resposta = "Um valor para porta deve ser informado";
        break;
    case "w-phpDir":
        $phpDir = isset($_POST["phpDir"]) ? strip_tags($_POST["phpDir"]) : "";
        if ($phpDir != "")
        {
            $handle = @fopen("phpDir.txt", 'w');
            if ($handle === FALSE)
            {
                $resposta = "Falha na abertura do arquivo phpDir.txt";
            }
            else
            {
                fwrite($handle, $phpDir);
                fclose($handle);
				$resposta = json_encode("");
            }
        }
        else
            $resposta = "Um valor para o diretorio php deve ser informado";
        break;
}

echo $resposta;
/* class Server 
  {
  public $host;
  public $porta;
  } */
?>