<?php
$phpDir = isset($_POST["dir"]) ? strip_tags($_POST["dir"]) : "";
$port = isset($_POST["port"]) ? strip_tags($_POST["port"]) : "";

$resu = ($phpDir == "" ? "Diretório do PHP não informado" : "") . ($phpDir == "" ? "Número da porta não informada" : "");

if ($resu == "")
{
    //echo "start ".$phpDir."php -f server.php?porta=$port";
    //exec("start " . $phpDir . "php -q server.php $port", $resu);
    exec ($phpDir."php -q server.php $port", $resu);
    //exec("start ex.bat", $resu);
    //exec ("c:\\WINDOWS\\system32\\cmd.exe /c START c:\\windows\\system32\\calc.exe");
    var_dump($resu);
}
else
    echo $resu;
?>