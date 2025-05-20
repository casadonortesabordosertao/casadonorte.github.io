<?php
session_start(); // Inicia a sessão
include_once("conexao.php"); // Conecta ao banco de dados

// Verifica se o usuário está logado (precisa de identificação)
if (!isset($_SESSION['telefone'])) {
    header('Location: login.php');
    exit;
}

$totalGeral = 0; // Inicializa o total geral

// Recupera os itens do carrinho do banco de dados, agora incluindo o preço da tabela produtos
$usuario_id = $_SESSION['telefone']; // Supondo que o telefone seja o ID do usuário
$query = "
    SELECT c.id, c.qtd_produto AS quantidade, p.nome, p.cod_barras, p.foto, p.preco, p.estoque
    FROM carrinho c
    JOIN produtos p ON c.id_produto = p.id
    WHERE c.telefone = :usuario_id
";
$stmt = $pdo->prepare($query);  // Substituindo $conn por $pdo
$stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Seu Carrinho - ShopGrids</title>
    <link rel="stylesheet" href="purged-css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="purged-css/main.css" />
    <style>
        .cart-item { background-color: #fff; border-radius: 15px; padding: 15px; margin-bottom: 20px; }
        .cart-summary { background-color: #fff; border-radius: 15px; padding: 25px; border: 1px solid #eee; }
        .cart-summary .list-group-item { border: none; padding-left: 0; padding-right: 0; background-color: transparent; }
        .breadcrumb { background-color: transparent; margin-bottom: 0; }
        .btn-remove { color: #dc3545; border: none; background: transparent; }
        .btn-remove:hover { color: #a71d2a; }
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

    <section class="shopping-cart section py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">
                    <?php
$itensExibidos = 0;

if (count($result) > 0):
    foreach ($result as $item):
        if ($item['estoque'] >= 1):
            $subtotal = $item['preco'] * $item['quantidade'];
            $totalGeral += $subtotal;
            $itensExibidos++;
?>
            <div class="cart-item">
                <div class="row align-items-center">
                    <div class="col-3 col-md-2">
                        <img src="assets/images/products/<?php echo htmlspecialchars($item['foto'] ?? 'sem-imagem.jpg'); ?>" alt="<?php echo htmlspecialchars($item['nome']); ?>">
                    </div>

                    <div class="col-9 col-md-4">
                        <h6 class="mb-1 fw-semibold"><?php echo htmlspecialchars($item['nome']); ?></h6>
                        <small class="text-muted">Código: <?php echo htmlspecialchars($item['cod_barras']); ?></small>
                    </div>

                    <div class="col-6 col-md-2 mt-3 mt-md-0">
                        <span class="text-muted">Preço:</span><br>
                        <strong>R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></strong>
                    </div>

                    <div class="col-6 col-md-2 mt-3 mt-md-0">
                        <span class="text-muted">Quantidade:</span>
                        <form action="atualizar_quantidade.php" method="POST" class="d-flex mt-1">
                            <input type="hidden" name="id_carrinho" value="<?php echo $item['id']; ?>">
                            <input type="number" name="quantidade" value="<?php echo $item['quantidade']; ?>" min="1" max="<?= htmlspecialchars($item['estoque']) ?>"  class="form-control form-control-sm me-1" style="width: 70px;">
                            <button type="submit" class="btn btn-sm btn-outline-primary" style="display: none;"></button>
                        </form>
                    </div>

                    <div class="col-12 col-md-2 mt-3 mt-md-0 d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-success" style="margin-top: 21px;">R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
                        <form method="POST" action="remover_item.php" style="margin-top: 20px;">
                            <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="btn-remove ms-2" title="Remover item">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
<?php
        endif;
    endforeach;

    if ($itensExibidos == 0):
        echo '<p>Seu carrinho está vazio ou os produtos não estão mais disponíveis em estoque.</p>';
    endif;
else:
    echo '<p>Seu carrinho está vazio.</p>';
endif;
?>


                    <a href="index.php" class="btn btn-outline-secondary mt-3">
                        <i class="lni lni-arrow-left"></i> Continuar Comprando
                    </a>
                </div>

                <div class="col-lg-4">
                    <div class="cart-summary" style="position: sticky; top: 100px; z-index: 10;">
                        <h5 class="mb-4">Resumo do Pedido</h5>
                        <ul class="list-group mb-4">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Total</span>
                                <strong class="text-primary">R$ <?php echo number_format($totalGeral, 2, ',', '.'); ?></strong>
                            </li>
                        </ul>
                        <a href="checkout.php" class="btn btn-primary w-100">Checkout</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include_once("footer.php"); ?>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
