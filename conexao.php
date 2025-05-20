<?php
// Conectar ao banco de dados (substitua os parâmetros conforme necessário)

// Definição dos parâmetros de conexão
$host = 'localhost';  // Endereço do servidor de banco de dados (geralmente 'localhost' para servidores locais)
$db = 'rdleyminimercado';  // Nome do banco de dados que está sendo utilizado
$user = 'root';  // Nome de usuário para conexão com o banco (usualmente 'root' para MySQL local)
$pass = '';  // Senha do usuário de conexão com o banco (deixe em branco para o 'root' no MySQL local)

try {
    // Tentando criar a conexão com o banco de dados utilizando PDO
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    
    // Configura o PDO para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Caso ocorra algum erro na conexão, exibe a mensagem de erro
    echo "Erro na conexão: " . $e->getMessage();
    exit;  // Encerra o script em caso de falha na conexão
}
?>
