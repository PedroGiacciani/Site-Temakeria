<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nossos produtos</title>
    <link rel="stylesheet" href="estilos/main.css">
    <link rel="stylesheet" href="estilos/feed.css">
    <script src="https://kit.fontawesome.com/52ff4c741b.js" crossorigin="anonymous"></script>
</head>
<body>
    <header>
        <?php include("menu.php");?>
    </header>
    <main>
        <div id="cardapio-ancora" class="gaveta-cardapio-japa">
            <div class="filtros-cardapio-japa">
                <button class="btn-filtro-japa ativo">Todos</button>
                <button class="btn-filtro-japa">Temakis</button>
                <button class="btn-filtro-japa">Hot Rolls</button>
                <button class="btn-filtro-japa">Combos</button>
                <button class="btn-filtro-japa">Bebidas</button>
                <button class="btn-filtro-japa">Sobremesas</button>
                <button class="btn-filtro-japa">Adicionais</button>
            </div>
            <section class="container-feed-temakeria">
                <article class="card-produto-japa">
                    <div class="foto-produto-japa">
                        <img src="https://images.unsplash.com/photo-1611143669185-af224c5e3252?q=80&w=600&auto=format&fit=cover" alt="Temaki Salmão">
                        <span class="badge-tag-japa">Mais Pedido</span>
                    </div>
                    <div class="info-produto-japa">
                        <h3>Temaki Salmão Completo</h3>
                        <p>Salmão fresco em cubos, cebolinha picada, cream cheese e nossa alga super crocante.</p>
                        <div class="footer-card-japa">
                            <span class="preco-japa">R$ 28,90</span>
                            <button class="btn-add-japa"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>
                </article>
                <article class="card-produto-japa">
                    <div class="foto-produto-japa">
                        <img src="https://images.unsplash.com/photo-1579871494447-9811cf80d66c?q=80&w=600&auto=format&fit=cover" alt="Hot Roll">
                    </div>
                    <div class="info-produto-japa">
                        <h3>Hot Roll Premium (10 un)</h3>
                        <p>Sushi frito na farinha panko, recheado com salmão grelhado, cream cheese e tarê.</p>
                        <div class="footer-card-japa">
                            <span class="preco-japa">R$ 32,00</span>
                            <button class="btn-add-japa"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>
                </article>
                <article class="card-produto-japa">
                    <div class="foto-produto-japa">
                        <img src="https://images.unsplash.com/photo-1617196034796-73dfa7b1fd56?q=80&w=600&auto=format&fit=cover" alt="Combo Executivo">
                        <span class="badge-tag-japa">Promoção</span>
                    </div>
                    <div class="info-produto-japa">
                        <h3>Combo Bia Sushi</h3>
                        <p>5 sushis variados, 5 hossomakis, 5 uramakis e 1 temaki clássico de salmão.</p>
                        <div class="footer-card-japa">
                            <span class="preco-japa">R$ 54,90</span>
                            <button class="btn-add-japa"><i class="fa-solid fa-plus"></i></button>
                        </div>
                    </div>
                </article>
            </section>
        </div>
    </main>
    <footer>
        <?php include("rodape.php");?>
    </footer>
</body>
</html>