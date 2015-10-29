<!DOCTYPE HTML>
<html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title><?php echo $titulo; ?></title>

        <!-- CSS -->
        <link href="common/framework/jquery-ui.min.css" rel="stylesheet" />
        <link href="common/framework/bootstrap.min.css" rel="stylesheet" />
        <link href="common/css/style.css" rel="stylesheet" />
        <link href="common/css/estilo-svg-png.css" rel="stylesheet" />

        <!-- Javascript -->
        <script src="common/framework/jquery.min.js"></script>
        <script src="common/framework/bootstrap.min.js"></script>
        <script src="common/framework/jquery-ui.min.js"></script>
        <!-- http://igorescobar.github.io/jQuery-Mask-Plugin/ -->
        <script src="common/framework/jquery.mask.min.js"></script>


        <script type="text/javascript" src="common/framework/rgbcolor.js"></script> 
        <script type="text/javascript" src="common/framework/canvg.js"></script>

        <script src="view/view.js"></script>
        <script src="view/<?php echo $name . '/' . $name . '.js' ?>"></script>

        <script>
            jQuery.fn.serializeObject = function () {
                var data = {};
                this.serializeArray().map(function (x) {
                    data[x.name] = x.value;
                });
                return data;
            };
            /*Array.prototype.switch = function (value, index) {
                var indexOld = this.indexOf(value);
                if (indexOld >= 0)
                    this.splice(indexOld, 1);
                if (index !== undefined && index !== null)
                    this.splice(index, 0, value);
                return indexOld;
            };*/
            String.prototype.capitalize = function (lower) {
                return (lower ? this.toLowerCase() : this).replace(/(?:^|\s)\S/g, function (a) {
                    return a.toUpperCase();
                });
            };
            function extende(obj, classe) {
                for (var property in classe.prototype)
                    if (!(property in obj))
                        obj [ property ] = classe.prototype[ property ];
            }
            function ajaxPadrao(view, action, data) {
                var resposta;
                $.ajax({
                    //context: $(this),
                    type: "POST",
                    url: "ajax.php?view=" + view + "&action=" + action,
                    //dataType: 'json',
                    data: data,
                    async: false
                })
                        .done(function (result, a, b) {
                            if (result.search("xdebug-error") < 0)
                                resposta = result;
                            else {
                                $("#errors").html($("#errors").html() + result);
                                $("#errors").dialog('open');
                                console.log(result);
                            }
                        })
                        .fail(function (result, a, b) {
                            $("#errors").html($("#errors").html() + result);
                            $("#errors").dialog('open');
                            console.log(result);
                        });
                return resposta;
            }
            function echo(texto) {
                $("#echo").html($("#echo").html() + texto);
                $("#echo").dialog('open');
            }
            var view = new View();
        </script>

    </head>
    <body>
        <?php
        func_include('common/menu/default.php');
        ?>

        <div id="conteudo" >
            <?php
            func_include($page);
            //var_dump($this);
            ?>
        </div>
        <div id="echo" style="display:none;"><?php if (isset($echo)) echo $echo; ?></div>
        <div id="infos" style="display:none;"><?php if (isset($infos)) var_dump($infos); ?></div>
        <div id="errors" style="color: black; display:none;"><?php
            if (isset($errors)) {
                foreach ($errors as $inf) {
                    echo "<table>";
                    echo $inf->xdebug_message;
                    echo "</table>";
                }
                echo '<script>$("#errors table tr td:nth-child(3)").attr("align", "");</script>';
            }
            //echo $inf->getTraceAsString() . "\n";
            //var_dump($inf);
            ?>
        </div>
        <script>
            $("#echo").dialog({
                title: "Echo",
                width: 500,
                height: 300,
                autoOpen: <?php echo isset($echo) ? "true" : "false"; ?>,
                buttons: [
                    {text: "Limpar", width: 100, click: function () {$("#echo").html("");}},
                    {text: "OK", width: 100, click: function () {  $(this).dialog("close"); }}
                ]
            });
            $("#infos").dialog({
                title: "Infos",
                width: 650,
                height: 400,
                autoOpen: <?php echo isset($infos) ? "true" : "false"; ?>,
                buttons: [
                    {text: "Limpar", width: 100, click: function () {$("#infos").html(""); }},
                    {text: "OK", width: 100, click: function () { $(this).dialog("close"); }}
                ]
            });
            $("#errors").dialog({
                title: "Errors",
                width: 800,
                height: 500,
                autoOpen: <?php echo isset($errors) ? "true" : "false"; ?>,
                buttons: [
                    {text: "Limpar", width: 100, click: function () {$("#errors").html(""); }},
                    {text: "OK", width: 100, click: function () { $(this).dialog("close"); }}
                ]
            });
            $("input.input-time").mask("00:00:00");
        </script>
    </body>
</html>