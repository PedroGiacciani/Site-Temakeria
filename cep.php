<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://fonts.googleapis.com/css?family=Inter' rel='stylesheet'>
    <title>Consulte seu CEP</title>

    <!--Script do fontAwesome para adicionar icones-->
</head>

<body>

    

    <header>

        <h1>
            Consulte o seu Endereço
        </h1>

    </header>
    <main>

        <div class="container-login">
            <label for="campo-nome">Digite seu CEP:</label>
            <input type="text" id="campo-cep" name="cep">
            <label for="campo-nome">Digite sua Rua:</label>
            <input type="text" id="campo-rua" name="rua">

            <label for="campo-nome">Digite seu Bairro:</label>
            <input type="text" id="campo-bairro" name="bairro">

            <label for="campo-nome">Digite sua Cidade:</label>
            <input type="text" id="campo-cidade" name="cidade">

            <label for="campo-nome">Digite seu Estado:</label>
            <input type="text" id="campo-estado" name="estado">
        </div>

    </main>

    <footer>
        <p><?php echo date('Y') ?> Leonardo F. 87826 - Desenvolvido em Sala de Aula </p>
    </footer>



    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            width: 100%;
        }

        html, body {
            min-height: 100vh;
            font-family: Inter, sans-serif;
            width: 100%;
            width: 100%!important;
            color: #000;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
             margin: 20px auto;

        }

        header {
        padding: 30px;
        color: #000;
        font-weight: 800;
        margin-bottom: 20px auto;

        }

        main {
            justify-content: center;
            margin: 0 auto;
            width: 100%;
            height: 100%;
            background: #ffff;
            transition: fade-in 0.3s opacity 0.3s ease;
            padding: 20px 10px;
            flex-grow: 1;


        }


        footer {
            margin-top: 20px;
            padding: 20px;
            font-size: 14px;
            border: 2px solid #000; 
            flex-shrink: 0;
            background-color: #0f0;
            border-radius: 15px;
            color: #000;
            bottom:0;
            height:auto;
            width: 100%;

        }

        h1 {
            font-size: 2rem;
            color: #000;
            margin: 40px auto;
            display: flex;
            justify-content: center;

        }

        .container-login {
            display: flex;
            flex-direction: column;
            margin: 20px auto;
            max-width: 500px;
            padding: 20px 15px;
            gap: 15px;
            border: 2px solid #000;
            background: #ccc;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur (8px);


        }


        .label  {
            padding: 10px;
        }

        .input[type=text] {
            padding: 6px 12px;
            border-radius: 10px;
             margin-bottom: 10px;
            font-size: 13px;
            max-width: 100px;
            min-height: 80px;
        }

        .campo-nome{
            font-size: 18px;
            padding: 6px;
            justify-content: left;
            border: 2px solid #000;

        }

    </style>


</body>




<script>
const campoCep = document.getElementById ('cep');
campoCep.addEventListener ('blur', function() {
    let Cep = campoCep.value.replace(/\D\g, '');
    if (cep.length === 8) {
        let url = 'https://viacep.com.br/ws/$(cep)/json';
        fetch(url)
        .then(resposta => resposta.json ())
        .then(dados =>){
            if (dados.erro) {
                alert("CEP não encontrado no banco de dados!");
                limparFormulario();
            } 
            
            else { 
                document.getElementById('rua').value = dados.logradouro;
                document.getElementById('bairro').value = dados.bairro;
                document.getElementById('cidade').value = dados.localidade;
                document.getElementById('estado').value = dados.uf;
            }
        }
    })

    .catch(erro => {
        console.error("Falha na requisição:", erro);
        alert("Erro de conexão ao buscar o CEP."); 
    })




</script>
</html>