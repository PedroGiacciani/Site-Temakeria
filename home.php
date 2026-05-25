<?php
session_start();
require_once 'db.php';

$usuario = usuario_logado();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temakeria da Bia</title>
    <link rel="stylesheet" href="estilos/main.css">
    <link rel="stylesheet" href="estilos/feed.css">

    <!--Script do fontAwesome para adicionar icones-->
    <script src="https://kit.fontawesome.com/52ff4c741b.js" crossorigin="anonymous"></script>

    <style>
        main{
            display: flex;
            flex-flow: column nowrap;
            align-items: center;
        }

        main > h1{
            text-align: center;
            margin: 10px;
        }

        section{
            width: 450px;
            height: 300px;
            border: 1px solid black;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div id="separador">
        <header>
            <?php include("menu.php"); ?>
        </header>
        <main>
            <h1>Olá, seja bem vindo ao nosso webSite</h1>
            <section>
                <a href="#cardapio-ancora" onclick="mostrarCardapio()">Confira nossos deliciosos produtos!</a>
            </section>
            <section>
                <a href="login.php">Faça login para conferir seus pedidos!</a>
            </section>
            <section>
                <a href="quem-somos.php">Confira nossa história!</a>
            </section>
           <?php include("cardapio.php"); ?>
        </main>
    </div>
    <footer>
        <?php include("rodape.php"); ?>
    </footer>
</body>
</html>
