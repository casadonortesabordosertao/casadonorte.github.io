<?php
session_start(); // Inicia a sessão

// Se o usuário já estiver logado, redireciona para a página inicial
if (isset($_SESSION['telefone'])) {
    header("Location: index.php");
    exit;
}

// Se o formulário foi enviado por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome']; // Obtém o nome do formulário
    $telefone = $_POST['Telefone']; // Obtém o telefone
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT); // Cria o hash da senha

    require_once 'conexao.php'; // Inclui o arquivo de conexão com o banco

    try {
        // Prepara e executa o INSERT no banco de dados
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, Telefone, senha) VALUES (:nome, :telefone, :senha)");
        $stmt->execute([
            ':nome' => $nome,
            ':telefone' => $telefone,
            ':senha' => $senha
        ]);

        // Mostra alerta de sucesso e redireciona para login
        echo "<script>alert('Cadastro realizado!'); location.href='login.php';</script>";
    } catch (PDOException $e) {
        // Mostra erro se houver falha na inserção
        echo "Erro: " . $e->getMessage();
    }
}
?>

<!-- Página de Cadastro HTML -->
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" /> <!-- Define a codificação -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" /> <!-- Responsividade -->
    <title>Cadastro</title> <!-- Título da aba -->
    
    <!-- Estilos do ShopGrids -->
    <link rel="stylesheet" href="purged-css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="purged-css/main.css" />

    <!-- Estilos personalizados -->
    <style>
        body {
            background-color: #f9f9f9; /* Cor de fundo clara */
        }
        .auth-box {
            max-width: 420px; /* Largura máxima */
            margin: 60px auto; /* Centraliza na vertical e horizontal */
            padding: 40px; /* Espaçamento interno */
            background: #fff; /* Fundo branco */
            border-radius: 12px; /* Cantos arredondados */
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1); /* Sombra suave */
        }
        .auth-box .form-control {
            height: 45px; /* Altura dos campos */
        }
        input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0px 1000px white inset !important; /* Previne autofill amarelo */
            box-shadow: 0 0 0px 1000px white inset !important;
            background-color: white !important;
            transition: background-color 5000s ease-in-out 0s;
        }
    </style>
</head>

<body>
    <!-- Caixa centralizada do formulário -->
    <div class="auth-box">
        <h3 class="text-center mb-4">Criar Conta</h3> <!-- Título -->

        <!-- Formulário de cadastro -->
        <form method="POST" action="cadastro.php" class="needs-validation" autocomplete="off">
            <!-- Campos falsos para burlar autocomplete -->
            <input type="text" name="fakeusernameremembered" style="display:none" autocomplete="off">
            <input type="password" name="fakepasswordremembered" style="display:none" autocomplete="new-password">

            <!-- Campo nome -->
            <div class="mb-3">
                <label for="nome" class="form-label">Nome completo</label>
                <input type="text" name="nome" class="form-control" id="nome" placeholder="Seu nome" required />
                <small class="text-muted">Insira seu Nome</small>
            </div>

            <!-- Campo telefone -->
            <div class="mb-3">
                <label for="Telefone" class="form-label">Número de telefone</label>
                <input type="tel" name="Telefone" class="form-control" id="telefone" maxlength="19" placeholder="+55 (11) 99999-9999" required />
                <small class="text-muted">Insira um Telefone</small>
            </div>

            <!-- Campo senha -->
            <div class="mb-3">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" name="senha" class="form-control" id="senha" placeholder="Senha" required />
                <small class="text-muted">Insira uma senha</small>
            </div>

            <!-- Botão de cadastro -->
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Cadastrar</button>
            </div>

            <!-- Link para login -->
            <div class="text-center mt-3">
                <a href="login.php" class="text-decoration-none">Já tem uma conta? Entrar</a>
            </div>
        </form>
    </div>

    <!-- Scripts Bootstrap -->
    <script src="/assets/js/bootstrap.min.js"></script>

    <!-- Script JS para máscara de telefone e validação -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const telefoneInput = document.getElementById('telefone'); // Pega o campo telefone

            // Máscara do telefone
            telefoneInput.addEventListener('input', function () {
                let value = telefoneInput.value.replace(/\D/g, ''); // Remove não dígitos
                value = value.slice(0, 13); // Limita a 13 números

                if (value.length > 0) value = '+' + value;
                if (value.length > 3) value = value.replace(/^(\+\d{2})(\d)/, '$1 ($2');
                if (value.length > 8) value = value.replace(/^(\+\d{2}) \((\d{2})(\d)/, '$1 ($2) $3');
                if (value.length > 15) value = value.replace(/^(\+\d{2}) \((\d{2})\) (\d{5})(\d)/, '$1 ($2) $3-$4');

                telefoneInput.value = value.slice(0, 19); // Máx 19 caracteres
            });

            // Validação de campos obrigatórios com destaque visual
            const submitButton = document.querySelector('button[type="submit"]');
            const forms = document.querySelectorAll('.needs-validation');

            document.querySelectorAll('.form-control[required], textarea[required], select[required]').forEach(el => {
                el.addEventListener('focus', () => {
                    el.classList.remove('is-valid', 'is-invalid'); // Limpa estados anteriores
                });
                el.addEventListener('input', () => {
                    el.classList.remove('is-valid', 'is-invalid');
                });
            });

            // Ao clicar no botão de envio
            submitButton.addEventListener('click', function (event) {
                event.preventDefault(); // Previne envio automático

                forms.forEach(function (form) {
                    let hasInvalidField = false;

                    // Valida cada campo obrigatório
                    form.querySelectorAll('.form-control[required]').forEach(input => {
                        const small = input.closest('div').querySelector('small');

                        if (!input.checkValidity()) {
                            input.classList.add('is-invalid');
                            input.classList.remove('is-valid');
                            if (small) small.classList.add('text-muted');
                            hasInvalidField = true;
                        } else {
                            input.classList.add('is-valid');
                            input.classList.remove('is-invalid');
                            if (small) small.classList.add('text-muted');
                        }
                    });

                    // Envia o formulário se tudo estiver válido
                    if (!hasInvalidField) {
                        form.submit();
                    } else {
                        event.stopPropagation(); // Previne envio se inválido
                    }
                });
            });
        });
    </script>
</body>
</html>
