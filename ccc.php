<?php
session_start();

require_once 'conexao.php'; // Inclua o arquivo de conexão com o banco

$usuarioSelecionado = null;
$carrinho = [];
$favoritos = [];

if (isset($_GET['ver'])) {
    $idUsuario = (int) $_GET['ver'];

    // Buscar dados do usuário (opcional, só para mostrar o nome no modal)
    $stmt = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
    $stmt->execute([$idUsuario]);
    $usuarioSelecionado = $stmt->fetch(PDO::FETCH_ASSOC);

    // Buscar carrinho do usuário
    $stmtCarrinho = $pdo->prepare("
        SELECT p.nome, c.qtd_produto FROM carrinho c
        INNER JOIN produtos p ON c.id_produto = p.id
        WHERE c.telefone = (SELECT telefone FROM usuarios WHERE id = ?)
    ");
    $stmtCarrinho->execute([$idUsuario]);
    $carrinho = $stmtCarrinho->fetchAll(PDO::FETCH_ASSOC);

    // Buscar favoritos do usuário (supondo tabela favoritos similar)
    $stmtFavoritos = $pdo->prepare("
        SELECT p.nome FROM favoritos f
        INNER JOIN produtos p ON f.id_produto = p.id
        WHERE f.telefone = (SELECT telefone FROM usuarios WHERE id = ?)
    ");
    $stmtFavoritos->execute([$idUsuario]);
    $favoritos = $stmtFavoritos->fetchAll(PDO::FETCH_ASSOC);
}


// Consulta para obter os usuários
$query = $pdo->prepare("SELECT id, nome, telefone, nivel FROM usuarios");
$query->execute();
$usuarios = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Usuários - Painel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,700" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="aaa.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-store"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Painel Admin</div>
            </a>
            <hr class="sidebar-divider my-0">
            <li class="nav-item">
                <a class="nav-link" href="aaa.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Gerenciamento</div>
            <li class="nav-item">
                <a class="nav-link" href="bbb.php">
                    <i class="fas fa-box"></i>
                    <span>Produtos</span>
                </a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="ccc.php">
                    <i class="fas fa-users"></i>
                    <span>Usuários</span>
                </a>
            </li>
            <hr class="sidebar-divider d-none d-md-block">
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Sair</span>
                </a>
            </li>
        </ul>

        <!-- Conteúdo principal -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link" href="#"><i class="fas fa-bell fa-fw"></i></a>
                        </li>
                        <div class="topbar-divider d-none d-sm-block"></div>
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link" href="#"><span class="mr-2 d-none d-lg-inline text-gray-600 small">Administrador</span><i class="fas fa-user-circle fa-lg"></i></a>
                        </li>
                    </ul>
                </nav>

                <!-- Conteúdo da página -->
                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Lista de Usuários</h1>

                    <div class="card shadow">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nome</th>
                                            <th>Telefone</th>
                                            <th>Nivel</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($usuarios as $usuario): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($usuario['id']) ?></td>
                                            <td><?= htmlspecialchars($usuario['nome']) ?></td>
                                            <td><?= htmlspecialchars($usuario['telefone']) ?></td>
                                            <td><?= htmlspecialchars($usuario['nivel']) ?></td>
                                            <td>
                                                <a href="ccc.php?ver=<?= $usuario['id'] ?>" class="btn btn-sm btn-info">
    <i class="fas fa-eye"></i>
</a>
                                                <a href="excluir_usuario.php?id=<?= $usuario['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <footer class="sticky-footer bg-white mt-4">
                <div class="container my-auto">
                    <div class="text-center my-auto">
                        <span>&copy; <?= date('Y') ?> Seu Sistema</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <?php if ($usuarioSelecionado): ?>
<div class="modal fade show" id="modalCarrinhoFavoritos" tabindex="-1" role="dialog" aria-labelledby="modalLabel" style="display: block;" aria-modal="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Itens do usuário: <?= htmlspecialchars($usuarioSelecionado['nome']) ?></h5>
        <a href="ccc.php" class="close" aria-label="Fechar">
          <span aria-hidden="true">&times;</span>
        </a>
      </div>
      <div class="modal-body">
        <h6>Itens no Carrinho:</h6>
        <?php if(count($carrinho) > 0): ?>
            <ul class="list-group mb-3">
                <?php foreach($carrinho as $item): ?>
                    <li class="list-group-item"><?= htmlspecialchars($item['nome']) ?> - Quantidade: <?= htmlspecialchars($item['qtd_produto']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p><em>Carrinho vazio</em></p>
        <?php endif; ?>

        <h6>Itens Favoritos:</h6>
        <?php if(count($favoritos) > 0): ?>
            <ul class="list-group">
                <?php foreach($favoritos as $fav): ?>
                    <li class="list-group-item"><?= htmlspecialchars($fav['nome']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p><em>Sem favoritos</em></p>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <a href="ccc.php" class="btn btn-secondary">Fechar</a>
      </div>
    </div>
  </div>
</div>
<div class="modal-backdrop fade show"></div>

<script>
  // Previne scroll na página enquanto modal aberto
  document.body.classList.add('modal-open');
</script>
<?php endif; ?>


    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/js/sb-admin-2.min.js"></script>
</body>

</html>
