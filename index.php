<?php
session_start();
// Inclui o arquivo de conexão com o banco de dados
include_once("conexao.php");

// Obtém o parâmetro 'busca' da URL ou define como string vazia
$busca = $_GET['busca'] ?? '';

// Inicializa a variável para armazenar mensagens de erro
$erro = '';

// Verifica se há uma busca
if ($busca) {
    // Prepara a consulta SQL com LIKE para buscar por nome ou código de barras
    $sql = "SELECT * FROM produtos WHERE nome LIKE :busca OR cod_barras LIKE :busca";
    $stmt = $pdo->prepare($sql);
    // Executa a consulta passando o termo de busca com coringas %
    $stmt->execute([':busca' => "%$busca%"]);
} else {
    // Se não houver busca, seleciona todos os produtos
    $stmt = $pdo->query("SELECT * FROM produtos");
}

// Recupera os resultados da consulta
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Se houve busca mas nenhum produto foi encontrado, define mensagem de erro
if ($busca && count($produtos) == 0) {
    $erro = "Nenhum produto encontrado para '" . htmlspecialchars($busca) . "'";
}
?>

<!-- Exibe um toast de erro no canto inferior direito, se houver erro -->
<?php if ($erro): ?>
  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055">
    <div id="erroToast" class="toast align-items-center text-white bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">
          ⚠️ <?= $erro ?>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- Início do documento HTML -->
<!DOCTYPE html>
<html class="no-js" lang="zxx">

<head>
    <!-- Configurações básicas de codificação e compatibilidade -->
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title>Rudley Mini Mercado</title>
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    
    <!-- Ícone da aba do navegador -->
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/favicon.svg" />
    
    <!-- Inclusão dos arquivos de estilo CSS -->
    <link rel="stylesheet" href="purged-css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="purged-css/tiny-slider.css" />
    <link rel="stylesheet" href="purged-css/glightbox.min.css" />
    <link rel="stylesheet" href="purged-css/main.css" />
</head>

<body>

<!-- Inclui o cabeçalho do site -->
<?php include_once("header.php"); ?>



<!-- Área de produtos em destaque -->
<section class="trending-product section">
    <div class="container-lg">

        <!-- Título da seção baseado em busca -->
        <div class="row">
            <div class="col-12">
                <div class="section-title">
                    <h1>Rudley Mini Mercado</h1>
                    <hr>
                    <p>Aqui você pode consultar os nossos produtos</p>
                </div>
            </div>
        </div>

        <!-- Listagem dos produtos -->
        <div class="row">
            <?php foreach ($produtos as $produto): ?>
                <?php if ($produto['estoque'] >= 1): ?>
            <div class="col-lg-3 col-md-4 col-6">
                <div class="single-product">
                    <div class="product-image">
                        <!-- Imagem do produto -->
                        <img src="assets/images/products/<?php echo htmlspecialchars($produto['foto']); ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>">
                        <div class="button">
                            <!-- Botão para ver detalhes do produto -->
                            <a href="produto.php?id=<?php echo $produto['id']; ?>" class="btn">
                                detalhes
                            </a>
                        </div>
                    </div>
                    <div class="product-info">
                        <!-- Nome do produto -->
                        <h4 class="title">
                            <a href="produto.php?id=<?php echo $produto['id']; ?>"><?php echo htmlspecialchars($produto['nome']); ?></a>
                        </h4>
                        <!-- Preço do produto formatado -->
                        <div class="price">
                            <span>R$<?php echo number_format($produto['preco'], 2, '.', ','); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- Inclui o rodapé -->
<?php include_once("footer.php"); ?>

<!-- Botão de rolar para o topo -->
<a href="#" class="scroll-top">
    <i class="bi bi-chevron-up"></i>
</a>

<!-- Scripts JavaScript necessários para o funcionamento do site -->
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/tiny-slider.js"></script>
<script src="assets/js/glightbox.min.js"></script>
<script src="assets/js/main.js"></script>
<script src="dist/bundle.js"></script>

<!-- Script de inicialização do carrossel de slides -->
<script type="text/javascript">
    //========= Hero Slider 
    tns({
        container: '.hero-slider',
        slideBy: 'page',
        autoplay: true,
        autoplayButtonOutput: false,
        mouseDrag: true,
        gutter: 0,
        items: 1,
        nav: false,
        controls: true,
        controlsText: ['<i class="lni lni-chevron-left"></i>', '<i class="lni lni-chevron-right"></i>'],
    });
</script>

</body>
</html>
