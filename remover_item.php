<?php
session_start();
include_once("conexao.php"); // Conecta ao banco de dados

// Verifica se foi enviado um ID via POST
if (isset($_POST['id'])) {
    $id = intval($_POST['id']); // Converte o ID enviado para um número inteiro

    // Remove o item do carrinho no banco de dados
    $usuario_id = $_SESSION['telefone']; // Supondo que o telefone seja o ID do usuário
    $query = "DELETE FROM carrinho WHERE id = :id AND telefone = :usuario_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_STR);
    
    if ($stmt->execute()) {
        // Redireciona para a página do carrinho após a remoção
        header('Location: cart.php');
        exit;
    } else {
        // Em caso de erro
        echo "Erro ao remover item.";
    }
} else {
    // Caso não tenha sido enviado um ID
    header('Location: cart.php');
    exit;
}
?>
