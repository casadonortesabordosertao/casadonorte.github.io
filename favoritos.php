<?php
session_start();
if (!isset($_SESSION['telefone'])) {
    header("Location: login.php");
    exit;
}

include_once("conexao.php");
$telefone = $_SESSION['telefone'];

$queryFavoritos = "SELECT p.id, p.nome, p.foto, p.preco, p.estoque
                   FROM produtos p
                   JOIN favoritos f ON p.id = f.id_produto
                   WHERE f.telefone = :telefone";
$stmtFavoritos = $pdo->prepare($queryFavoritos);
$stmtFavoritos->bindParam(':telefone', $telefone);
$stmtFavoritos->execute();
$favoritos = $stmtFavoritos->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8" />
    <title>Meus Favoritos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="assets/images/favicon.svg" />

    <!-- Estilos -->
    <link rel="stylesheet" href="purged-css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="purged-css/tiny-slider.css" />
    <link rel="stylesheet" href="purged-css/glightbox.min.css" />
    <link rel="stylesheet" href="purged-css/main.css" />
</head>
<body>
<?php include_once("header.php"); ?>

<div class="breadcrumbs">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-6 col-12">
                    <ul class="breadcrumb-nav">
                        <li><a href="index.php"><i class="lni lni-home"></i> Home</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

<section class="trending-product section">
    <div class="container-lg">
        <div class="row">
            <?php if (empty($favoritos)): ?>
                <div class="col-12 text-center">
                    <p class="text-muted">
                        💔 Você ainda não adicionou nenhum produto aos favoritos.<br>
                        Use o botão 💖 para marcar os seus preferidos.
                    </p>
                </div>
            <?php else: ?>
                <div class="section-title">
                    <h2>💖 Meus Produtos Favoritos</h2>
                    <p>Estes são os produtos que você marcou como favoritos.</p>
                </div>

                <?php foreach ($favoritos as $produto): ?>
                    <div class="col-lg-3 col-md-4 col-6">
                        <div class="single-product">
                            <div class="product-image position-relative">
                                <!-- Formulário para remover dos favoritos -->
                                <form method="POST" action="remover_favorito.php" style="position: absolute; top: 10px; right: 10px; z-index: 10;">
                                    <input type="hidden" name="id_produto" value="<?php echo $produto['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" style="border-radius: 50%; padding: 2px 8px;">&times;</button>
                                </form>

                                <!-- Imagem do produto -->
                                <img src="assets/images/products/<?php echo htmlspecialchars($produto['foto']); ?>" 
                                     alt="<?php echo htmlspecialchars($produto['nome']); ?>">

                                     <?php if ($produto['estoque'] >= 1): ?>
                                <div class="button">
                                    <a href="produto.php?id=<?php echo $produto['id']; ?>" class="btn">
                                        </i> detalhes
                                    </a>
                                </div>
                                <?php else: ?>
                                <div class="button">
                                    <a href="produto.php?id=<?php echo $produto['id']; ?>" class="btn">
                                        Sem estoque
                                    </a>
                                </div>
                                <?php endif; ?>

                            </div>

                            <div class="product-info">
                                <h4 class="title">
                                    <a href="produto.php?id=<?php echo $produto['id']; ?>">
                                        <?php echo htmlspecialchars($produto['nome']); ?>
                                    </a>
                                </h4>

                                <div class="price">
                                    <span>R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include_once("footer.php"); ?>

<a href="#" class="scroll-top"><i class="lni lni-chevron-up"></i></a>

<!-- Scripts -->
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/tiny-slider.js"></script>
<script src="assets/js/glightbox.min.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
