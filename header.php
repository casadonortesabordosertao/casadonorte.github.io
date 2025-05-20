<?php
// Inicia a sessão para gerenciar dados de sessão do usuário


// Verifica se o usuário está logado
if (!isset($_SESSION['telefone'])) {
    // Se não estiver logado, redireciona para a página de login
    header("Location: login.php");
    exit;
}

// Conecta ao banco de dados
require_once 'conexao.php';

// Pega o telefone do usuário da sessão
$telefone = $_SESSION['telefone'];

// Inicializa as variáveis de contagem
$totalCarrinho = 0;
$totalFavoritos = 0;

try {
    // Consulta para contar os itens com estoque no carrinho do usuário
$stmtCarrinho = $pdo->prepare("
    SELECT COUNT(*) FROM carrinho 
    INNER JOIN produtos ON carrinho.id_produto = produtos.id 
    WHERE carrinho.telefone = :telefone AND produtos.estoque >= 1
");
$stmtCarrinho->bindParam(':telefone', $telefone);
$stmtCarrinho->execute();
$totalCarrinho = $stmtCarrinho->fetchColumn();

    
    // Consulta para contar os itens nos favoritos do usuário
    $stmtFavoritos = $pdo->prepare("SELECT COUNT(*) FROM favoritos WHERE telefone = :telefone");
    $stmtFavoritos->bindParam(':telefone', $telefone); // Vincula o telefone à consulta
    $stmtFavoritos->execute();
    $totalFavoritos = $stmtFavoritos->fetchColumn(); // Conta o número de itens nos favoritos
} catch (PDOException $e) {
    // Se houver erro, você pode lidar com ele aqui
    echo "Erro: " . $e->getMessage();
}
?>

<!-- HTML para exibir os itens de carrinho e favoritos -->
<header class="header navbar-area">
    <div class="header-middle">
        <div class="container-lg">
            <div class="row align-items-center">
                <div class="col-lg-3 col-md-3 col-7">
                    <a class="navbar-brand" href="index.php">
                        <img src="assets/images/logo/logo.svg" alt="Logo" style="max-width: 50px;">
                    </a>
                </div>
                <div class="col-lg-5 col-md-7 d-xs-none">
                    <div class="main-menu-search">
                        <form class="navbar-search search-style-5" method="GET" action="index.php">
                            <div class="search-input">
                                <input type="text" name="busca" placeholder="Buscar produto..." value="<?= htmlspecialchars($_GET['busca'] ?? '') ?>">
                            </div>
                            <div class="search-btn">
                                <button type="submit"><i class="bi bi-search"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Ícones de Carrinho e Favoritos -->
                <div class="col-lg-4 col-md-2 col-5">
                    <div class="middle-left-area">
                        <div class="navbar-cart">
                            <!-- Wishlist (Favoritos) -->
                            <div class="wishlist">
                                <a href="favoritos.php">
                                    <i class="bi bi-heart"></i>
                                    <span class="total-items"><?= $totalFavoritos ?></span> <!-- Número dinâmico de favoritos -->
                                </a>
                            </div>
                            <!-- Fim da Wishlist (Favoritos) -->
                            <!-- Carrinho de Compras -->
                            <div class="cart-items">
                                <a href="cart.php" class="main-btn">
                                    <i class="bi bi-cart"></i>
                                    <span class="total-items"><?= $totalCarrinho ?></span> <!-- Número dinâmico do carrinho, ele precisa verificar se o item no carriho tem estoque no produtos se não tiver tira esse produto da quantidade mostrada -->
                                </a>
                            </div>
                            <!-- Fim do Carrinho de Compras -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
