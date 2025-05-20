<?php
session_start();
include_once("conexao.php");

// Verifica se o usuário está logado
if (!isset($_SESSION['telefone'])) {
    header('Location: login.php');
    exit;
}

$telefone = $_SESSION['telefone'];

try {
    // Inicia uma transação
    $pdo->beginTransaction();

    // Buscar os itens comprados para reduzir o estoque
    $stmt = $pdo->prepare("SELECT c.id_produto, c.qtd_produto FROM carrinho c WHERE c.telefone = :telefone");
    $stmt->bindParam(':telefone', $telefone);
    $stmt->execute();
    $itensComprados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($itensComprados)) {
        throw new Exception('Nenhum item encontrado no carrinho.');
    }

    // Atualizar o estoque dos produtos comprados
    foreach ($itensComprados as $item) {
        // Verifica se há estoque suficiente
        $stmt = $pdo->prepare("SELECT estoque FROM produtos WHERE id = :id_produto");
        $stmt->bindParam(':id_produto', $item['id_produto']);
        $stmt->execute();
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$produto) {
            throw new Exception('Produto não encontrado com ID: ' . $item['id_produto']);
        }

        if ($produto['estoque'] < $item['qtd_produto']) {
            throw new Exception('Estoque insuficiente para o produto ID: ' . $item['id_produto']);
        }

        // Decrementa a quantidade de estoque
        $stmt = $pdo->prepare("UPDATE produtos SET estoque = estoque - :qtd_produto WHERE id = :id_produto");
        $stmt->bindParam(':qtd_produto', $item['qtd_produto']);
        $stmt->bindParam(':id_produto', $item['id_produto']);

        if (!$stmt->execute()) {
            throw new Exception('Erro ao atualizar o estoque do produto com ID: ' . $item['id_produto']);
        }
    }

    // Apaga todos os itens do carrinho para esse telefone
    $stmt = $pdo->prepare("DELETE FROM carrinho WHERE telefone = :telefone");
    $stmt->bindParam(':telefone', $telefone);
    if (!$stmt->execute()) {
        throw new Exception('Erro ao apagar os itens do carrinho.');
    }

    // Finaliza a transação
    $pdo->commit();

    // Exibe mensagem de sucesso
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Compra Finalizada</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet">
        <style>
            body {
                font-family: 'Segoe UI', sans-serif;
                background-color: #f9f9f9;
            }
            .hero-section {
                background: linear-gradient(135deg, #5d5fef, #8f94fb);
                color: white;
                padding: 80px 20px 60px;
                text-align: center;
                border-radius: 0 0 30px 30px;
            }
            .hero-section h1 {
                font-size: 2.5rem;
                font-weight: bold;
            }
            .content-section {
                padding: 40px 15px;
                background-color: #ffffff;
                border-radius: 20px;
                box-shadow: 0 0 30px rgba(0, 0, 0, 0.05);
                text-align: center;
            }
            .content-section h2 {
                font-size: 1.8rem;
                margin-bottom: 20px;
                color: #333;
            }
            .content-section p {
                font-size: 1.1rem;
                line-height: 1.8;
                color: #555;
            }
            .footer {
                text-align: center;
                padding: 30px 0;
                font-size: 0.9rem;
                color: #6c757d;
                background-color: #fff;
                margin-top: 40px;
                border-top: 1px solid #ddd;
            }
        </style>
    </head>
    <body>

        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container">
                <h1><i class="lni lni-checkmark-circle"></i> Compra Finalizada com Sucesso!</h1>
                <p class="lead mt-3">Obrigado por comprar conosco! Seu pedido está sendo preparado e logo estará a caminho! 🛒</p>
            </div>
        </section>

        <!-- Content Section -->
        <section class="container mt-5 mb-5">
            <div class="content-section mx-auto" style="max-width: 900px;">
                <p>
                    Sua compra foi realizada com sucesso! Agora estamos preparando os produtos para o envio. 
                    Em breve, você receberá todas as informações para o acompanhamento da sua entrega.
                </p>
                <a href="index.php" class="btn btn-primary">Voltar para a Loja</a>
            </div>
        </section>

        <!-- Footer -->
        <footer class="footer">
            <p>© <?= date('Y') ?> Rudley Mini Mercado | Todos os direitos reservados.</p>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
} catch (Exception $e) {
    // Caso ocorra qualquer erro, faz o rollback da transação
    $pdo->rollBack();
    echo "Erro ao finalizar a compra: " . $e->getMessage();
}
?>
