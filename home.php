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
        body{
            background: black url("midias/foto-temaki-intro.jpg") center center fixed;
            background-size: cover;
        }
        section#intro{
            padding: 20px;
            width: 100% !important;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
        }

        div.conteudo{
            background-color: #ffffffc9;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
            margin: 20px 0px;
            width: 450px;
        }
    </style>
</head>
<body>
    <div id="separador">
        <header>
            <?php include("menu.php"); ?>
        </header>

        <main>
            <section id="intro">
                <div class="conteudo">
                    <h2>Bem vindo ao nosso webSite</h2>
                    <p>Aqui você pode realizar pedidos, conhecer um pouco mais sobre a gente e acompanhar de perto nossas principais novidades!</p>
                </div>
                <div class="conteudo">
                    <h2>Novo por aqui!?</h2>
                    <p>Faça seu login para gerenciar seus pedidos e preferências</p>
                </div>
                <div class="conteudo">
                    <h2>Notícias da Bia</h2>
                </div>
            </section>
        </main>

    </div>
    <footer>
        <?php include("rodape.php"); ?>
    </footer>
</body>
</html>
