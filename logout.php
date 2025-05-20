<?php
session_start();  // Inicia ou retoma a sessão do usuário

// Verifica se o usuário não está autenticado (sem 'usuario_id' na sessão)
if (!isset($_SESSION['telefone'])) {
    header("Location: cadastro.php");  // Redireciona para a página de cadastro se o usuário não estiver autenticado
    exit;  // Encerra a execução do script após o redirecionamento
} else {
    session_destroy();  // Destrói todos os dados da sessão, desconectando o usuário
    header("Location: login.php");  // Redireciona o usuário para a página de login
    exit;  // Encerra a execução do script após o redirecionamento
}
