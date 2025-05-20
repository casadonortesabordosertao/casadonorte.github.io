<?php
session_start(); // Inicia a sessão para gerenciar variáveis de sessão

// Verifica se a requisição é do tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telefone = $_POST['Telefone']; // Obtém o telefone do formulário

    require_once 'conexao.php'; // Inclui o arquivo de conexão com o banco de dados

    try {
        // Prepara a consulta SQL para buscar o usuário com o telefone fornecido
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE Telefone = :telefone");
        $stmt->bindParam(':telefone', $telefone); // Vincula o parâmetro do telefone
        $stmt->execute(); // Executa a consulta
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC); // Obtém os dados do usuário
        if ($usuario) {
            // Se o usuário for encontrado, exibe uma mensagem de sucesso com redirecionamento
            echo "<script>
                    alert('✅ Link de recuperação enviado para o número: $telefone');
                    window.location.href = 'recuperacao_sucesso.php';
                  </script>";
            exit; // Interrompe a execução do código
        } else {
            // Se o usuário não for encontrado, define uma variável de sessão para o erro
            $_SESSION['telefone_nao_encontrado'] = true;
            header('Location: ' . $_SERVER['PHP_SELF']); // Redireciona para a página atual
            exit; // Interrompe a execução do código
        }

    } catch (PDOException $e) {
        // Se ocorrer um erro na consulta SQL, exibe uma mensagem de erro
        echo "Erro no banco de dados: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Recuperar Senha</title>
    <!-- Inclusão dos arquivos de CSS do tema -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/main.css" />
    <style>
      body {
        background-color: #f9f9f9; /* Cor de fundo da página */
      }
      .auth-box {
        max-width: 420px;
        margin: 60px auto;
        padding: 40px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1); /* Estilo da caixa de formulário */
      }
      .auth-box .form-control {
        height: 45px; /* Altura dos campos de input */
      }
    </style>
  </head>
  <body>
    <div class="auth-box">
      <h3 class="text-center mb-4">Recuperar Senha</h3> <!-- Título da página -->
      <p class="text-center mb-4 text-muted">
        Digite seu Telefone para receber instruções de redefinição de senha.
      </p>
      <form method="POST" action="" class="needs-validation" id="formRecuperacao">
        <input type="text" name="fakeusernameremembered" style="display:none" autocomplete="off">
        <input type="password" name="fakepasswordremembered" style="display:none" autocomplete="new-password">
        <div class="mb-3">
          <label for="Telefone" class="form-label">Telefone</label>
          <!-- Campo de entrada para o telefone -->
          <input
            type="tel"
            name="Telefone"
            class="form-control"
            id="telefone"
            maxlength="19"
            placeholder="+55 (11) 99999-9999"
            required
          />
          <small class="text-muted">Insira seu Telefone de acesso</small>
        </div>
        <div class="d-grid">
          <button type="submit" class="btn btn-primary">Enviar link de recuperação</button> <!-- Botão de envio -->
        </div>
        <div class="text-center mt-3">
          <a href="Login.php" class="text-decoration-none">Voltar para login</a> <!-- Link para voltar ao login -->
        </div>
      </form>
    </div>

    <script src="https://demo.graygrids.com/themes/shopgrids/assets/js/bootstrap.min.js"></script>

    <?php if (isset($_SESSION['telefone_nao_encontrado'])): ?>
    <!-- Exibe uma mensagem de erro caso o número não tenha sido encontrado -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const alerta = document.createElement('div');
            alerta.style.position = 'fixed';
            alerta.style.bottom = '20px';
            alerta.style.right = '20px';
            alerta.style.backgroundColor = '#f44336';
            alerta.style.color = '#fff';
            alerta.style.padding = '12px 18px';
            alerta.style.borderRadius = '8px';
            alerta.style.boxShadow = '0 2px 10px rgba(0,0,0,0.15)';
            alerta.innerText = '⚠️ Número de telefone não encontrado!'; // Texto do alerta
            document.body.appendChild(alerta);
            setTimeout(() => alerta.remove(), 4000); // Remove o alerta após 4 segundos
        });
    </script>
    <?php unset($_SESSION['telefone_nao_encontrado']); endif; ?>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const telefoneInput = document.getElementById('telefone');
        
        telefoneInput.addEventListener('input', function () {
          let value = telefoneInput.value.replace(/\D/g, ''); // Remove caracteres não numéricos

          // Limita o número de caracteres para 13 (ex: +55 (11) 912345678)
          value = value.slice(0, 13);

          if (value.length > 0) value = '+' + value;
          if (value.length > 3) value = value.replace(/^(\+\d{2})(\d)/, '$1 ($2');
          if (value.length > 8) value = value.replace(/^(\+\d{2}) \((\d{2})(\d)/, '$1 ($2) $3');
          if (value.length > 15) value = value.replace(/^(\+\d{2}) \((\d{2})\) (\d{5})(\d)/, '$1 ($2) $3-$4');

          telefoneInput.value = value.slice(0, 19); // Máximo de 19 caracteres
        });

        // Validação visual do formulário
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
              form.submit(); // Submete o formulário se não houver campos inválidos
            } else {
              event.stopPropagation(); // Interrompe o envio do formulário se houver erro
            }
          });
        });
      });
    </script>

  </body>
</html>
