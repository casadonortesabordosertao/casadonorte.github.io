<?php
session_start(); // Inicia a sessão
include_once('conexao.php'); // Conexão com o banco

$telefone = $_SESSION['telefone']; // Pega o telefone do usuário logado

// Verifica se o ID do produto foi passado
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produto) {
        echo "<script>alert('Produto não encontrado.'); window.location.href='index.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('ID do produto não informado.'); window.location.href='index.php';</script>";
    exit;
}

// Processando o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produtoId = intval($_POST['id']);
    $quantidade = max(1, intval($_POST['quantidade'])); // Garante que a quantidade seja pelo menos 1

    // Verifica se a quantidade é maior que o estoque
    if ($quantidade > $produto['estoque']) {
        $quantidade = $produto['estoque']; // Ajusta para o máximo disponível em estoque
    }

    // Verifica qual ação o usuário selecionou
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        // Adicionar ao Carrinho
        if ($action === 'add_to_cart') {
    // Verifica se o produto já está no carrinho
    $check = $pdo->prepare("SELECT * FROM carrinho WHERE telefone = :telefone AND id_produto = :produto_id");
    $check->execute([':telefone' => $telefone, ':produto_id' => $produtoId]);
    $existingItem = $check->fetch(PDO::FETCH_ASSOC);

    // Verifica se o produto já está no carrinho e se a quantidade total não ultrapassa o estoque
    if ($existingItem) {
        $newQuantity = $existingItem['qtd_produto'] + $quantidade;
        if ($newQuantity <= $produto['estoque']) {
            // Atualiza a quantidade
            $update = $pdo->prepare("UPDATE carrinho SET qtd_produto = :qtd WHERE telefone = :telefone AND id_produto = :produto_id");
            $update->execute([ 
                ':qtd' => $newQuantity,
                ':telefone' => $telefone,
                ':produto_id' => $produtoId
            ]);
        } else {
            echo "<script>alert('Quantidade máxima no estoque é $produto[estoque].'); window.location.href='produto.php?id=$produtoId';</script>";
            exit;
        }
    } else {
        // Verifica se a quantidade total não ultrapassa o estoque antes de adicionar
        if ($quantidade <= $produto['estoque']) {
            // Insere novo produto no carrinho
            $insert = $pdo->prepare("INSERT INTO carrinho (telefone, id_produto, qtd_produto) VALUES (:telefone, :produto_id, :quantidade)");
            $insert->execute([ 
                ':telefone' => $telefone,
                ':produto_id' => $produtoId,
                ':quantidade' => $quantidade
            ]);
        } else {
            echo "<script>alert('Quantidade máxima no estoque é $produto[estoque].'); window.location.href='produto.php?id=$produtoId';</script>";
            exit;
        }
    }

    header("Location: produto.php?id=$produtoId&adicionado=1");
    exit;
}

        // Adicionar aos Favoritos
        if ($action === 'add_to_favoritos') {
            // Verifica se já está nos favoritos
            $check = $pdo->prepare("SELECT * FROM favoritos WHERE telefone = :telefone AND id_produto = :produto_id");
            $check->execute([':telefone' => $telefone, ':produto_id' => $produtoId]);

            if ($check->rowCount() === 0) {
                // Adiciona aos favoritos
                $insert = $pdo->prepare("INSERT INTO favoritos (telefone, id_produto) VALUES (:telefone, :produto_id)");
                $insert->execute([
                    ':telefone' => $telefone,
                    ':produto_id' => $produtoId
                ]);
            }

            header("Location: produto.php?id=$produtoId&favoritado=1");
            exit;
        }
    }
}

?>

<!DOCTYPE html>
<html class="no-js" lang="zxx">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <title> Detalhes do produto</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/favicon.svg" />
    <link rel="stylesheet" href="purged-css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="purged-css/tiny-slider.css" />
    <link rel="stylesheet" href="purged-css/glightbox.min.css" />
    <link rel="stylesheet" href="purged-css/main.css" />
    <style>
        /* Remove spinners do input[type="number"] */
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"] {
            -moz-appearance: textfield;
        }
    </style>
</head>

<body>
<?php include_once("header.php"); ?> <!-- Inclui o cabeçalho da página -->

<!-- Breadcrumbs -->
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

<!-- Product Details -->
<section class="item-details section">
    <div class="container">
        <div class="top-area">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-12 col-12">
                    <div class="product-images">
                        <main id="gallery">
                            <div class="main-img">
                                <img src="assets/images/products/<?php echo htmlspecialchars($produto['foto']); ?>" id="current" alt="#">
                            </div>
                        </main>
                    </div>
                </div>
                <?php if ($produto['estoque'] >= 1): ?>
                <div class="col-lg-6 col-md-12 col-12">
                    <div class="product-info">
                        <h2 class="title"><?php echo htmlspecialchars($produto['nome']); ?></h2>
                        <h3 class="price">R$<?php echo number_format($produto['preco'], 2, ',', '.'); ?></h3>
                        <p class="info-text"><?php echo nl2br(htmlspecialchars($produto['descricao'])); ?></p>

                        <!-- Formulário de Ação: Adicionar ao Carrinho e aos Favoritos -->
                        <form method="POST">
                            <input type="hidden" name="id" value="<?php echo htmlspecialchars($produto['id']); ?>">

                            <div class="row align-items-end">
                                <!-- Quantidade para o carrinho -->
                                <div class="col-lg-4 col-md-4 col-12">
                                    <div class="form-group quantity">
                                        <label for="quantidade">Quantidade</label>
                                        <input 
                                            class="form-control" 
                                            type="number" 
                                            name="quantidade" 
                                            value="1" 
                                            min="1" 
                                            required
                                        >
                                    </div>
                                </div>

                                <!-- Botão: Adicionar ao Carrinho -->
                                <div class="col-lg-4 col-md-4 col-6 d-flex">
                                    <button 
                                        type="submit" 
                                        name="action" 
                                        value="add_to_cart" 
                                        class="btn btn-success w-100"
                                        style="font-size: 0.75rem; padding: 0.375rem 0.4rem; height: 38px;"
                                    >
                                        Adicionar 🛒
                                    </button>
                                </div>

                                <!-- Botão: Adicionar aos Favoritos -->
                                <div class="col-lg-4 col-md-4 col-6 d-flex">
                                    <button 
                                        type="submit" 
                                        name="action" 
                                        value="add_to_favoritos" 
                                        class="btn btn-outline-danger w-100"
                                        style="font-size: 0.75rem; padding: 0.375rem 0.4rem; height: 38px;"
                                    >
                                        <i class="lni lni-heart"></i> Favoritar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                    <div class="col-lg-6 col-md-12 col-12">
                        <div class="product-info">
                            <h2 class="title">Produto fora de estoque</h2>
                            <p class="">Este produto está fora de estoque no momento, porém, caso os estoques voltem e você tiver este item no seu carrinho, ele será automaticamente adicionado novamente!</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include_once("footer.php"); ?> <!-- Inclui o rodapé da página -->

<a href="#" class="scroll-top">
    <i class="lni lni-chevron-up"></i>
</a>

<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/tiny-slider.js"></script>
<script src="assets/js/glightbox.min.js"></script>
<script src="assets/js/main.js"></script>

<script type="text/javascript">
    const current = document.getElementById("current");
    const opacity = 0.6;
    const imgs = document.querySelectorAll(".img");
    imgs.forEach(img => {
        img.addEventListener("click", (e) => {
            imgs.forEach(img => {
                img.style.opacity = 1;
            });
            current.src = e.target.src;
            e.target.style.opacity = opacity;
        });
    });
</script>

<?php if (isset($_GET['adicionado']) && $_GET['adicionado'] == 1 && $_GET['adicionado'] <= $produto['estoque']): ?>
<div id="toast" class="toast align-items-center text-white bg-success position-fixed bottom-0 end-0 m-3 show" role="alert" aria-live="assertive" aria-atomic="true" style="z-index: 9999; min-width: 250px; min-height: 50px;">
  <div class="d-flex">
    <div class="toast-body">
      ✅ Produto adicionado ao carrinho!
    </div>
    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
  </div>
</div>
<script>
  // Fecha automaticamente após 3 segundos
  setTimeout(() => {
    const toast = document.getElementById('toast');
    if (toast) toast.remove();
  }, 3000);
</script>
<?php endif; ?>

</body>
</html>
