<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nós | Temakeria da Bia</title>
    <link rel="stylesheet" href="estilos/main.css">
    <link rel="stylesheet" href="estilos/feed.css">
    <script src="https://kit.fontawesome.com/52ff4c741b.js" crossorigin="anonymous"></script>
    
    <style>
        /* Ajuste rápido para o container da página */
        .container-sobre {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            text-align: center;
        }
        .container-sobre h1 { color: #BB1F20; margin-bottom: 20px; }
        .container-sobre p { line-height: 1.6; font-size: 1.1rem; }
    </style>
</head>
<body>
    <div id="separador">
        <header>
            <?php include("menu.php"); ?>
        </header>

        <main>
            <!-- CONTEÚDO PRINCIPAL (SOBRE) PRIMEIRO -->
            <section class="container-sobre">
                <h1>Sobre a Temakeria da Bia</h1>
                <p>Aqui na Temakeria da Bia, a nossa paixão é levar o sabor do Japão direto para sua casa.</p>
                <p>Nascemos do sonho de servir comida de qualidade, com ingredientes frescos e aquele toque especial que só a Bia sabe dar.</p>
                <p>Seja bem-vindo à nossa família!</p>
            </section>

        </main>
    </div>

    <footer>
        <?php include("rodape.php"); ?>
    </footer>
</body>
</html>