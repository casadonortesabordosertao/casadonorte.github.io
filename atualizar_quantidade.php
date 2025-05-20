<?php
session_start();
include_once("conexao.php");

if (!isset($_SESSION['telefone'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_carrinho = $_POST['id_carrinho'] ?? null;
    $quantidade = $_POST['quantidade'] ?? null;

    if ($id_carrinho && $quantidade && $quantidade > 0) {
        $query = "UPDATE carrinho SET qtd_produto = :quantidade WHERE id = :id AND telefone = :telefone";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':quantidade', $quantidade, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id_carrinho, PDO::PARAM_INT);
        $stmt->bindParam(':telefone', $_SESSION['telefone'], PDO::PARAM_STR);
        $stmt->execute();
    }
}

header("Location: cart.php");
exit;
?>
