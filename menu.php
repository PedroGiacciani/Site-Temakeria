<img src="midias/logo-temakeria-bia.jpg" alt="Logo Temakeria da Bia">
<h1>Temakeria da Bia</h1>
<nav id="menu">
    <i class="fa-solid fa-angle-down" onclick="toggleMenu()" id="icon"></i>
    <ul id="lista-menu">
        <li><a href="#cardapio-ancora" onclick="mostrarCardapio()">Nossos produtos</a></li>
        <li><a href="#">Seus pedidos</a></li>
        <li><a href="#">Sua conta</a></li>
        <li><a href="#">Configurações</a></li>
    </ul>
</nav>

<script>
    //Script para menu hamburguer
    function toggleMenu(){
        let lista = document.getElementById('lista-menu')
        let icone = document.getElementById('icon')

        lista.classList.toggle('aberto')

        if(lista.classList.contains('aberto')){
            icone.classList.remove('fa-angle-down')
            icone.classList.add('fa-angle-up')
        }else{
            icone.classList.remove('fa-angle-up')
            icone.classList.add('fa-angle-down')
        }
    }

    function mostrarCardapio() {
    // 1. Pega a gaveta do cardápio
    let gaveta = document.getElementById('cardapio-ancora');
    // 2. Faz ela aparecer mudando a classe do CSS
    gaveta.classList.add('mostrar');
    // 3. Fecha o menu hambúrguer automaticamente para não cobrir a tela
    let lista = document.getElementById('lista-menu');
    let icone = document.getElementById('icon');
    lista.classList.remove('aberto');
    icone.classList.remove('fa-angle-up');
    icone.classList.add('fa-angle-down');
}

</script>