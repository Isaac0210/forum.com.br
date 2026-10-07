<?php
session_start();
require_once __DIR__ . '/funcoes.php';

$email = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $senha = (string) ($_POST['senha'] ?? '');
    try {
        $usuarios = carregarXml(ARQUIVO_USUARIOS, 'usuarios');
        foreach ($usuarios->usuario as $usuario) {
            $hash = (string) $usuario->senha;
            $senhaCorreta = password_verify($senha, $hash)
                || (preg_match('/^[a-f0-9]{32}$/i', $hash) === 1 && hash_equals(strtolower($hash), md5($senha)));
            if (strcasecmp((string) $usuario->email, $email) === 0 && $senhaCorreta) {
                session_regenerate_id(true);
                $_SESSION['usuario'] = (string) $usuario->email;
                if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                    $usuario->senha = password_hash($senha, PASSWORD_DEFAULT);
                    salvarXml($usuarios, ARQUIVO_USUARIOS);
                }
                header('Location: listar.php');
                exit;
            }
        }
        $erro = 'Login ou senha inválidos.';
    } catch (RuntimeException $excecao) {
        $erro = $excecao->getMessage();
    }
}

renderHeader('Entrar');
?>

<article>
    <h2>Acessar a conta</h2>

    <?php if ($erro !== ''): ?>
        <div class="alert-error"><?= escapar($erro) ?></div>
    <?php endif; ?>

    <form method="post">
        <label for="email">E-mail
            <input type="email" id="email" name="email" value="<?= escapar($email) ?>" placeholder="seu@email.com" required>
        </label>

        <label for="senha">Senha
            <input type="password" id="senha" name="senha" required>
        </label>

        <button type="submit">Entrar</button>
    </form>

    <footer>
        <small><a href="cadastro.php">Criar cadastro</a> | <a href="listar.php">Ver tópicos</a></small>
    </footer>
</article>

<?php renderFooter(); ?>