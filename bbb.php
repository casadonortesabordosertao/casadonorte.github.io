<?php
session_start();
require_once 'conexao.php'; // Conexão com o banco de dados

// Adicionar produto
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_produto'])) {
    $codigo = $_POST['cod_barras'];
    $nome = $_POST['nome'];
    $preco = $_POST['preco'];
    $estoque = $_POST['estoque'];
    $descricao = $_POST['descricao'];
    $foto = $_POST['foto'];
    $data = date('d/m/Y H:i:s'); // Ex: 20/05/2025 15:42:10

    $sql = "INSERT INTO produtos (cod_barras, nome, preco, estoque, descricao, foto) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$codigo, $nome, $preco, $estoque, $descricao, $foto]);

    header("Location: bbb.php");
    exit();
}

// Editar produto
if (isset($_GET['edit']) && $_GET['edit'] != '') {
    $id = $_GET['edit'];
    $sql = "SELECT * FROM produtos WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_produto'])) {
        $codigo = $_POST['cod_barras'];
        $nome = $_POST['nome'];
        $preco = $_POST['preco'];
        $estoque = $_POST['estoque'];
        $descricao = $_POST['descricao'];
        $foto = $_POST['foto'];

        $sql = "UPDATE produtos SET cod_barras = ?, nome = ?, preco = ?, estoque = ?, descricao = ?, foto = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$codigo, $nome, $preco, $estoque, $descricao, $foto, $id]);

        header("Location: bbb.php");
        exit();
    }
}

// Excluir produto
if (isset($_GET['delete']) && $_GET['delete'] != '') {
    $id = $_GET['delete'];
    $sql = "DELETE FROM produtos WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    header("Location: bbb.php");
    exit();
}

// Buscar todos os produtos
$sql = "SELECT * FROM produtos ORDER BY data_criacao DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Produtos - Painel</title>
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
            <li class="nav-item active">
                <a class="nav-link" href="bbb.php">
                    <i class="fas fa-box"></i>
                    <span>Produtos</span>
                </a>
            </li>
            <li class="nav-item">
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

                <div class="container-fluid">
                    
    <h1 class="h3 mb-4 text-gray-800">Lista de Produtos</h1>

    <!-- Botão para abrir o modal -->
    <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addProdutoModal">
        <i class="fas fa-plus"></i> Adicionar Produto
    </button>
    
        <div id="pagination" class="mb-3 text-center"></div>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Cód. Barras</th>
                            <th>Foto</th>
                            <th>Nome</th>
                            <th>Preço</th>
                            <th>Estoque</th>
                            <th>Descrição</th>
                            <th>Data Criação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['cod_barras']) ?></td>
                                <td>
                                    <?php if ($p['foto']): ?>
                                        <img src="assets/images/products/<?= htmlspecialchars($p['foto']) ?>" alt="Foto <?= htmlspecialchars($p['nome']) ?>" style="max-width:60px;">
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($p['nome']) ?></td>
                                <td>R$ <?= number_format($p['preco'], 2, ',', '.') ?></td>
                                <td><?= htmlspecialchars($p['estoque']) ?></td>
                                <td><?= nl2br(htmlspecialchars($p['descricao'])) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($p['data_criacao'])) ?></td>
                                <td>
                                    <a href="bbb.php?edit=<?= $p['id'] ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="bbb.php?delete=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')">
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

    <!-- Modal de Adicionar Produto -->
    <div class="modal fade" id="addProdutoModal" tabindex="-1" role="dialog" aria-labelledby="addProdutoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProdutoModalLabel">Adicionar Produto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="bbb.php" method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nome">Codigo de barras</label>
                            <input type="number" class="form-control" id="cod_barras" name="cod_barras" required>
                        </div>
                        <div class="form-group">
                            <label for="foto">Foto (Nome com extensão)</label>
                            <input type="text" class="form-control" id="foto" name="foto">
                        </div>
                        <div class="form-group">
                            <label for="nome">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome">
                        </div>
                        <div class="form-group">
                            <label for="preco">Preço</label>
                            <input type="number" class="form-control" id="preco" name="preco" step="0.01">
                        </div>
                        <div class="form-group">
                            <label for="estoque">Estoque</label>
                            <input type="number" class="form-control" id="estoque" name="estoque" required>
                        </div>
                        <div class="form-group">
                            <label for="descricao">Descrição</label>
                            <textarea class="form-control" id="descricao" name="descricao"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary" name="add_produto">Adicionar Produto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

 <?php if (isset($produto) && isset($_GET['edit'])): ?>
    <div class="modal fade" id="editProdutoModal" tabindex="-1" role="dialog" aria-labelledby="editProdutoModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProdutoModalLabel">Editar Produto</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="bbb.php?edit=<?= $produto['id'] ?>" method="POST">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nome">Codigo de barras</label>
                            <input type="number" class="form-control" id="cod_barras" name="cod_barras" value="<?= htmlspecialchars($produto['cod_barras']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="foto">Foto (URL ou caminho)</label>
                            <input type="text" class="form-control" id="foto" name="foto" value="<?= $produto['foto'] ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="nome">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" value="<?= $produto['nome'] ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="preco">Preço</label>
                            <input type="number" class="form-control" id="preco" name="preco" value="<?= $produto['preco'] ?>" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="estoque">Estoque</label>
                            <input type="number" class="form-control" id="estoque" name="estoque" value="<?= $produto['estoque'] ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="descricao">Descrição</label>
                            <textarea class="form-control" id="descricao" name="descricao" required><?= $produto['descricao'] ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                        <button type="submit" class="btn btn-primary" name="edit_produto">Atualizar Produto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/startbootstrap-sb-admin-2@4.1.4/js/sb-admin-2.min.js"></script>

    <?php if (isset($_GET['edit']) && isset($produto)): ?>
<script>
    $(document).ready(function () {
        $('#editProdutoModal').modal('show');
    });
</script>
<?php endif; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const itemsPerPage = 10; // Número de itens por página
        const tableRows = document.querySelectorAll('table tbody tr'); // Todas as linhas da tabela
        const totalItems = tableRows.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage); // Número total de páginas

        let currentPage = 1; // Página inicial

        // Função para mostrar apenas os produtos da página atual
        function showPage(page) {
            // Calcular os índices dos itens a serem exibidos
            const startIndex = (page - 1) * itemsPerPage;
            const endIndex = page * itemsPerPage;

            // Ocultar todas as linhas da tabela
            tableRows.forEach((row, index) => {
                if (index >= startIndex && index < endIndex) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Função para criar a navegação de páginas
        function createPagination() {
            const paginationContainer = document.getElementById('pagination');
            paginationContainer.innerHTML = ''; // Limpar a navegação existente

            // Criar botões de navegação
            for (let i = 1; i <= totalPages; i++) {
                const pageButton = document.createElement('button');
                pageButton.textContent = i;
                pageButton.classList.add('btn', 'btn-sm', 'btn-secondary', 'mx-1');
                pageButton.onclick = () => {
                    currentPage = i;
                    showPage(currentPage);
                    updatePaginationButtons();
                };
                paginationContainer.appendChild(pageButton);
            }
        }

        // Função para atualizar o estilo dos botões de navegação
        function updatePaginationButtons() {
            const buttons = document.querySelectorAll('#pagination button');
            buttons.forEach(button => {
                if (parseInt(button.textContent) === currentPage) {
                    button.classList.add('btn-primary');
                    button.classList.remove('btn-secondary');
                } else {
                    button.classList.add('btn-secondary');
                    button.classList.remove('btn-primary');
                }
            });
        }

        // Inicializar a página
        showPage(currentPage);
        createPagination();
        updatePaginationButtons();
    });
    
</script>

</body>

</html>
