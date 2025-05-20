<?php
// Inicia a sessão para gerenciar dados de sessão do usuário
session_start();

// Verifica se o usuário já está logado (se existe a variável de sessão 'telefone')
if (isset($_SESSION['telefone'])) {
    header("Location: index.php");
    exit;
}

$erro = ""; // Variável para armazenar mensagens de erro

// Verifica se o método da requisição é POST (se o formulário foi enviado)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe os dados do formulário
    $telefone = $_POST['Telefone'];
    $senha = $_POST['senha'];

    // Inclui o arquivo de conexão com o banco de dados utilizando PDO
    require_once 'conexao.php';

    try {
        // Prepara a consulta SQL para verificar o usuário pelo telefone
        $stmt = $pdo->prepare("SELECT senha FROM usuarios WHERE Telefone = :telefone");
        $stmt->bindParam(':telefone', $telefone);
        $stmt->execute();
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Se o usuário for encontrado e a senha for válida, faz login
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['telefone'] = $telefone; // Salva apenas o telefone
            header("Location: index.php");
            exit;
        } else {
            $erro = "Telefone ou senha incorretos.";
        }

    } catch (PDOException $e) {
        $erro = "Erro na conexão com o banco de dados: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/main.css" />
    <style>
      /* Estilo personalizado para o fundo e a caixa de autenticação */
      body {
        background-color: #f9f9f9;
      }
      .auth-box {
        max-width: 420px;
        margin: 60px auto;
        padding: 40px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
      }
      .auth-box .form-control {
        height: 45px;
        position: relative;
      }

      /* Estilo para indicar campo válido com emoji de sucesso ✅ */
      .form-control.is-valid {
        border-color: #198754;
        padding-right: 2.5rem;
      }

      .form-control.is-valid::after {
        content: '✅';
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 9999;
        pointer-events: none;
        font-size: 1.2rem;
      }

      /* Estilo para campos inválidos com borda vermelha */
      .is-invalid {
        border-color: #dc3545;
      }

      .is-invalid ~ small {
        color: #dc3545;
      }

      /* Remove o fundo autofill de campos */
      .input:-webkit-autofill {
        -webkit-box-shadow: 0 0 0px 1000px white inset !important;
        box-shadow: 0 0 0px 1000px white inset !important;
        background-color: white !important;
      }
    </style>
  </head>
  <body>
    <div class="auth-box">
      <h3 class="text-center mb-4">Login</h3>
      <!-- Formulário de login -->
      <form method="POST" action="login.php" class="needs-validation" novalidate>
        <input type="text" name="fakeusernameremembered" style="display:none" autocomplete="off">
        <input type="password" name="fakepasswordremembered" style="display:none" autocomplete="new-password">
        
        <!-- Campo para o telefone -->
        <div class="mb-3">
          <label for="telefone" class="form-label">Telefone</label>
          <input
            type="tel"
            name="Telefone"
            class="form-control"
            id="telefone"
            maxlength="19" 
            placeholder="+55 (11) 99999-9999"
            required
            value="<?= isset($telefone) ? htmlspecialchars($telefone) : '' ?>"
          />
          <small class="text-muted">Insira seu Telefone de acesso</small>
        </div>
        
        <!-- Campo para a senha -->
        <div class="mb-3">
          <label for="senha" class="form-label">Senha</label>
          <input
            type="password"
            name="senha"
            class="form-control"
            id="senha"
            placeholder="••••••••"
            autocomplete="new-password"
            required
          />
          <small class="text-muted">Insira sua senha</small>
        </div>
        
        <!-- Exibição de erro caso a autenticação falhe -->
        <?php if ($erro): ?>
          <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1055">
            <div id="erroToast" class="toast align-items-center text-white bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
              <div class="d-flex">
                <div class="toast-body">
                  <?= "⚠️ " . htmlspecialchars($erro) ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
              </div>
            </div>
          </div>
        <?php endif; ?>
        
        <!-- Botão de login -->
        <div class="d-grid">
          <button type="submit" class="btn btn-primary">Entrar</button>
        </div>
        
        <!-- Links para recuperação de senha e cadastro -->
        <div class="text-center mt-3">
          <a href="forgot-password.php" class="text-decoration-none d-block">Esqueceu a senha?</a>
          <a href="Cadastro.php" class="text-decoration-none">Não tem uma conta? Cadastre-se</a>
        </div>
      </form>
    </div>

    <script src="https://demo.graygrids.com/themes/shopgrids/assets/js/bootstrap.min.js"></script>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const telefoneInput = document.getElementById('telefone');
        
        telefoneInput.addEventListener('input', function () {
          let value = telefoneInput.value.replace(/\D/g, ''); // Remove caracteres não numéricos

          // Limita a 13 números (ex: 55 11 912345678)
          value = value.slice(0, 13);

          if (value.length > 0) value = '+' + value;
          if (value.length > 3) value = value.replace(/^(\+\d{2})(\d)/, '$1 ($2');
          if (value.length > 8) value = value.replace(/^(\+\d{2}) \((\d{2})(\d)/, '$1 ($2) $3');
          if (value.length > 15) value = value.replace(/^(\+\d{2}) \((\d{2})\) (\d{5})(\d)/, '$1 ($2) $3-$4');

          telefoneInput.value = value.slice(0, 19); // Máx 19 caracteres
        });

        // Validação visual
        const submitButton = document.querySelector('button[type="submit"]');
        const forms = document.querySelectorAll('.needs-validation');

        document.querySelectorAll('.form-control[required]').forEach(el => {
          el.addEventListener('focus', () => {
            el.classList.remove('is-valid', 'is-invalid');
          });
          el.addEventListener('input', () => {
            el.classList.remove('is-valid', 'is-invalid');
          });
        });

        submitButton.addEventListener('click', function (event) {
          event.preventDefault();

          forms.forEach(function (form) {
            let hasInvalidField = false;

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

            if (!hasInvalidField) {
              form.submit();
            } else {
              event.stopPropagation();
            }
          });
        });
      });
    </script>
  </body>
</html>
