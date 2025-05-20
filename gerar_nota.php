<?php
session_start();
include_once("conexao.php");

// Verifica se o usuário está logado
if (!isset($_SESSION['telefone'])) {
    header('Location: login.php');
    exit;
}

$telefone = $_SESSION['telefone'];

// Buscar dados do pedido
$stmt = $pdo->prepare("SELECT * FROM pedidos WHERE telefone = :telefone");
$stmt->bindParam(':telefone', $telefone);
$stmt->execute();
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    echo "Nenhuma compra encontrada para este número.";
    exit;
}

// Buscar dados do usuário
$stmt_usuario = $pdo->prepare("SELECT nome FROM usuarios WHERE telefone = :telefone");
$stmt_usuario->bindParam(':telefone', $telefone);
$stmt_usuario->execute();
$usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "Nenhum usuário encontrado para este telefone.";
    exit;
}

// Buscar itens do carrinho com nome e preço
$stmt_produtos = $pdo->prepare("
    SELECT p.nome, p.preco, c.qtd_produto
    FROM carrinho c
    JOIN produtos p ON c.id_produto = p.id
    WHERE c.telefone = :telefone
");
$stmt_produtos->bindParam(':telefone', $telefone);
$stmt_produtos->execute();
$produtos = $stmt_produtos->fetchAll(PDO::FETCH_ASSOC);

// Verificar se existem produtos no carrinho
if (!$produtos) {
    header("Location: index.php");
    exit;
}

// Calcular subtotal e total
$subtotal = 0;
foreach ($produtos as $item) {
    $subtotal += $item['preco'] * $item['qtd_produto'];
}

// Dados para enviar ao C#
$data = [
    'cliente' => $pedido['telefone'],
    'nome' => $usuario['nome'],
    'telefone' => $pedido['telefone'],
    'endereco' => $pedido['endereco'],
    'complemento' => $pedido['complemento'],
    'formaPagamento' => $pedido['forma_pagamento'],
    'produtos' => []
];

foreach ($produtos as $item) {
    $data['produtos'][] = [
        'qtd' => (int)$item['qtd_produto'],
        'desc' => $item['nome'],
        'vunit' => (double)$item['preco']
    ];
}

// Adicionar dados extras ao array
$data['entrega'] = (float)$pedido['entrega'];

// cURL para enviar dados ao servidor C#
$ch = curl_init("http://localhost:5000/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
curl_close($ch);

// Processar resposta do C#
// Processar resposta do C#
if ($response) {
    $responseData = json_decode($response, true); // Decodifica o JSON para um array associativo
    
    // Verifica se a resposta contém uma mensagem de erro
    if (isset($responseData['message']) && $responseData['message'] == "Valor insuficiente para realizar a compra. Tente novamente com um valor válido.") {
        echo "Erro: " . $responseData['message']; // Exibe a mensagem de erro para o usuário
        exit; // Encerra o script caso haja erro
    } else {
        echo "Nota fiscal gerada com sucesso.";
    }
} else {
    echo "Erro ao gerar nota fiscal.";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Nota Fiscal</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ccc; }
        th, td { padding: 10px; text-align: left; }
        .total { font-weight: bold; }
        .center { text-align: center; }
    </style>
</head>
<body>

<h1>Nota Fiscal</h1>

<p><strong>Nome:</strong> <?= htmlspecialchars($usuario['nome']) ?></p>
<p><strong>Telefone:</strong> <?= htmlspecialchars($pedido['telefone']) ?></p>
<p><strong>Endereço:</strong> <?= htmlspecialchars($pedido['endereco']) ?></p>
<p><strong>Complemento:</strong><?= htmlspecialchars($pedido['complemento']) ?></p>
<p><strong>Forma de Pagamento:</strong> <?= ucfirst($pedido['forma_pagamento']) ?></p>

<table>
    <thead>
        <tr>
            <th>Produto</th>
            <th>Qtd</th>
            <th>Preço Unit.</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($produtos as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['nome']) ?></td>
                <td><?= (int)$item['qtd_produto'] ?></td>
                <td>R$ <?= number_format((double)$item['preco'], 2, ',', '.') ?></td>
                <td>R$ <?= number_format((double)($item['qtd_produto'] * $item['preco']), 2, ',', '.') ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" class="total">Subtotal</td>
            <td>R$ <?= number_format($subtotal, 2, ',', '.') ?></td>
        </tr>
        <?php if ($pedido['entrega'] > 0): ?>
            <tr>
                <td colspan="3" class="total">Entrega</td>
                <td>R$ <?= number_format((double)$pedido['entrega'], 2, ',', '.') ?></td>
            </tr>
        <?php endif; ?>
        <tr>
            <td colspan="3" class="total">Total a Pagar</td>
            <td><strong>R$ <?= number_format((double)$pedido['valor_total'], 2, ',', '.') ?></strong></td>
        </tr>
    </tfoot>
</table>

<?php
header("Location: sucesso.php");
exit;
?>

</body>
</html>
