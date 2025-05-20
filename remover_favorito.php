<?php
session_start();
if (!isset($_SESSION['telefone'])) {
    header("Location: login.php");
    exit;
}

include_once("conexao.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telefone = $_SESSION['telefone'];
    $id_produto = $_POST['id_produto'] ?? null;

    if ($id_produto) {
        $stmt = $pdo->prepare("DELETE FROM favoritos WHERE telefone = :telefone AND id_produto = :id_produto");
        $stmt->execute([
            'telefone' => $telefone,
            'id_produto' => $id_produto
        ]);
    }
}

// Redireciona de volta à página de favoritos
header("Location: favoritos.php");
exit;
