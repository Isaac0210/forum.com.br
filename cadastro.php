<?php
require_once __DIR__ . '/funcoes.php';

$nome = '';
$celular = '';
$email = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim((string) ($_POST['nome'] ?? ''));
    $celular = trim((string) ($_POST['celular'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $senha = (string) ($_POST['senha'] ?? '');

    if ($nome === '' || $celular === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Preencha nome, celular e um e-mail válido.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve possuir pelo menos 6 caracteres.';
    } else {
        try {
            $usuarios = carregarXml(ARQUIVO_USUARIOS, 'usuarios');
            foreach ($usuarios->usuario as $usuario) {
                if (strcasecmp((string) $usuario->email, $email) === 0) {
                    $erro = 'Este e-mail já está cadastrado.';
                    break;
                }
            }
            if ($erro === '') {
                $novo = $usuarios->addChild('usuario');
                adicionarTextoXml($novo, 'nome', $nome);
                adicionarTextoXml($novo, 'celular', $celular);
                adicionarTextoXml($novo, 'email', $email);
                adicionarTextoXml($novo, 'senha', password_hash($senha, PASSWORD_DEFAULT));
                salvarXml($usuarios, ARQUIVO_USUARIOS);

                renderHeader('Cadastro realizado');
                echo '<div class="alert-success">Usuário cadastrado com sucesso! <a href="login.php">Fazer login</a></div>';
                renderFooter();
                exit;
            }
        } catch (RuntimeException $excecao) {
            $erro = $excecao->getMessage();
        }
    }
}

renderHeader('Criar Conta');
?>

<article>
    <h2>Criar nova conta</h2>
    
    <?php if ($erro !== ''): ?>
        <div class="alert-error"><?= escapar($erro) ?></div>
    <?php endif; ?>

    <form method="post">
        <label for="nome">Nome
            <input type="text" id="nome" name="nome" value="<?= escapar($nome) ?>" placeholder="Seu nome completo" required>
        </label>
        
        <label for="celular">Celular
            <input type="tel" id="celular" name="celular" value="<?= escapar($celular) ?>" placeholder="(00) 00000-0000" required>
        </label>
        
        <label for="email">E-mail
            <input type="email" id="email" name="email" value="<?= escapar($email) ?>" placeholder="seu@email.com" required>
        </label>
        
        <label for="senha">Senha
            <input type="password" id="senha" name="senha" minlength="6" placeholder="Mínimo 6 caracteres" required>
        </label>

        <button type="submit">Cadastrar</button>
    </form>
    
    <footer>
        <small><a href="login.php">Já tenho cadastro</a></small>
    </footer>
</article>

<?php renderFooter(); ?>