<?php
session_start();
include_once("conexao.php");

if (!isset($_SESSION['telefone'])) {
    header('Location: login.php');
    exit;
}

$telefone = $_SESSION['telefone'];
$totalGeral = 0;
$entrega = 0;
$endereco = '';
$complemento = '';

// Consultar itens no carrinho
$stmt = $pdo->prepare("
    SELECT c.qtd_produto AS quantidade, p.preco 
    FROM carrinho c
    JOIN produtos p ON c.id_produto = p.id
    WHERE c.telefone = :telefone
");
$stmt->bindParam(':telefone', $telefone);
$stmt->execute();
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($itens as $item) {
    $totalGeral += $item['quantidade'] * $item['preco'];
}

// Buscar último endereço e complemento se existirem
$stmt = $pdo->prepare("SELECT endereco, complemento FROM pedidos WHERE telefone = :telefone ORDER BY id DESC LIMIT 1");
$stmt->bindParam(':telefone', $telefone);
$stmt->execute();
$ultimoPedido = $stmt->fetch(PDO::FETCH_ASSOC);

if ($ultimoPedido) {
    $endereco = $ultimoPedido['endereco'];
    $complemento = $ultimoPedido['complemento'];
}


// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['endereco'])) {
    if (isset($_POST['entrega'])) {
        $entrega = 5.00;
    }

    if (isset($_POST['endereco'])) {
        $endereco = $_POST['endereco'];
    }

    if (isset($_POST['complemento'])) {
        $complemento = $_POST['complemento'];
    }

    // Calcular o valor final (sem desconto)
    $valorFinal = $totalGeral + $entrega;

    // Verificar se já existe um registro de checkout para este cliente (telefone)
    $stmt = $pdo->prepare("SELECT telefone FROM pedidos WHERE telefone = :telefone");
    $stmt->bindParam(':telefone', $telefone);
    $stmt->execute();
    $checkoutExistente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($checkoutExistente) {
        // Se o registro já existir, realizar um UPDATE
        $stmt = $pdo->prepare("
            UPDATE pedidos
            SET forma_pagamento = :forma_pagamento,
                endereco = :endereco,
                complemento = :complemento,
                entrega = :entrega,
                valor_total = :valor_total
            WHERE telefone = :telefone
        ");
        $stmt->bindParam(':forma_pagamento', $_POST['forma_pagamento']);
        $stmt->bindParam(':endereco', $endereco);
        $stmt->bindParam(':complemento', $complemento);
        $stmt->bindParam(':entrega', $entrega);
        $stmt->bindParam(':valor_total', $valorFinal);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->execute();
    } else {
        // Se não existir, realizar um INSERT
        $stmt = $pdo->prepare("
            INSERT INTO pedidos (telefone, forma_pagamento, endereco, complemento, entrega, valor_total)
            VALUES (:telefone, :forma_pagamento, :endereco, :complemento, :entrega, :valor_total)
        ");
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':forma_pagamento', $_POST['forma_pagamento']);
        $stmt->bindParam(':endereco', $endereco);
        $stmt->bindParam(':complemento', $complemento);
        $stmt->bindParam(':entrega', $entrega);
        $stmt->bindParam(':valor_total', $valorFinal);
        $stmt->execute();
    }

    // Após o processamento, redirecionar para gerar_nota.php
    header('Location: gerar_nota.php?telefone=' . $telefone .
        '&forma_pagamento=' . $_POST['forma_pagamento'] .
        '&entrega=' . $entrega .
        '&endereco=' . urlencode($endereco) .
        '&complemento=' . urlencode($complemento) .
        '&valor_total=' . $valorFinal);
    exit;
}

$valorFinal = $totalGeral + $entrega;
?>



<!DOCTYPE html>
<html class="no-js" lang="pt-br">
<head>
    <meta charset="utf-8" />
    <title>Checkout</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="purged-css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="purged-css/tiny-slider.css" />
    <link rel="stylesheet" href="purged-css/glightbox.min.css" />
    <link rel="stylesheet" href="purged-css/main.css" />
    <style>
        .invisivel {
            visibility: hidden;
            opacity: 0;
            pointer-events: none;
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

<section class="checkout-wrapper section">
    <div class="container">
        <form action="checkout.php" method="POST">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="checkout-steps-form-style-1">
                                <section class="checkout-steps-form-content collapse show" id="collapseThree">
                                    <div class="row">

                                        <!-- Forma de pagamento -->
                                        <div class="col-12 col-md-6 mb-3" id="campo-forma">
                                            <div class="form-group">
                                                <label for="forma_pagamento">Forma de pagamento</label>
                                                <select id="forma-pagamento"
                                                        name="forma_pagamento"
                                                        class="form-control form-control-lg"
                                                        onchange="toggleCampoValor()">
                                                    <option value="" disabled selected>
                                                        Selecione uma opção
                                                    </option>
                                                    <option value="pix">
                                                        Pix
                                                    </option>
                                                    <option value="dinheiro" >
                                                        Dinheiro
                                                    </option>
                                                    <option value="cartao">
                                                        Cartão
                                                    </option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Endereço -->
<div class="col-12 col-md-6 mb-3">
    <div class="form-group">
        <label for="endereco">Endereço</label>
        <input type="text" name="endereco"
               class="form-control form-control-lg"
               placeholder="Rua, bairro..."
               value="<?= $endereco ? htmlspecialchars($endereco) : '' ?>">
    </div>
</div>

<!-- Complemento -->
<div class="col-12 col-md-12 mb-12">
    <div class="form-group">
        <label for="complemento">Complemento</label>
        <input type="text" name="complemento"
               class="form-control form-control-lg"
               placeholder="Casa número, apto, proximo da..."
               value="<?= $complemento ? htmlspecialchars($complemento) : '' ?>">
    </div>
</div>


                                        <div class="col-12 col-md-12 mb-12">
                                            <div class="form-group">
                                                <label for="observaçoões">Observaçoões</label>
                                                <textarea name="obeservaçôes" class="form-control form-control-lg" maxlength="375" cols="40" rows="5" style="resize: none;"></textarea>
                                            </div>
                                        </div>

                                        <!-- Entrega -->
                                        <div class="col-12 mt-2">
                                            <div class="single-checkbox checkbox-style-3">
                                                <input type="checkbox" id="checkbox-3" name="entrega">
                                                <label for="checkbox-3"><span><p><strong>Desejo entrega em domicílio com valor adicional de 5 reais</strong></p></span></label>
                                            </div>
                                        </div>

                                    </div>
                                </section>
                    </div>
                </div>

                <div class="col-lg-4 col-md-8 mt-4 mt-lg-0">
                    <div class="checkout-sidebar">

                        <!-- Resumo -->
                        <div class="checkout-sidebar-price-table">
                            <h5 class="title" style="border-bottom-width: 0;">Resumo</h5>
                            <div class="total-payable d-flex justify-content-between">
                                <p class="value">Total a pagar:</p>
                                <p class="price">R$ <?= number_format($valorFinal,2,',','.') ?></p>
                            </div>
                            <div class="price-table-btn button mt-3">
                                <button type="submit" class="btn btn-alt w-100">Finalizar compra</button>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </form>
    </div>
</section>

<?php include_once("footer.php"); ?>

<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/tiny-slider.js"></script>
<script src="assets/js/glightbox.min.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
