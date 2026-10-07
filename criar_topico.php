<?php
session_start();
require_once __DIR__ . '/funcoes.php';

if (!isset($_SESSION['usuario'])) {
    encerrarComErro('Você precisa estar logado para criar um tópico.', 403);
}

$titulo = '';
$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        encerrarComErro('Formulário expirado. Tente novamente.', 403);
    }
    $titulo = trim((string) ($_POST['titulo'] ?? ''));
    $mensagem = trim((string) ($_POST['mensagem'] ?? ''));
    if ($titulo === '' || $mensagem === '') {
        $erro = 'Informe o título e a mensagem.';
    } else {
        try {
            $topicos = carregarXml(ARQUIVO_TOPICOS, 'topicos');
            $novo = $topicos->addChild('topico');
            adicionarTextoXml($novo, 'autor', (string) $_SESSION['usuario']);
            adicionarTextoXml($novo, 'titulo', $titulo);
            adicionarTextoXml($novo, 'mensagem', $mensagem);
            $novo->addChild('comentarios');
            salvarXml($topicos, ARQUIVO_TOPICOS);

            $_SESSION['mensagem_sucesso'] = 'Tópico criado com sucesso!';
            header('Location: listar.php');
            exit;
        } catch (RuntimeException $excecao) {
            $erro = $excecao->getMessage();
        }
    }
}

renderHeader('Novo Tópico');
?>

<article>
    <h2>Criar novo tópico</h2>

    <?php if ($erro !== ''): ?>
        <div class="alert-error"><?= escapar($erro) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
        
        <label for="titulo">Título
            <input type="text" id="titulo" name="titulo" value="<?= escapar($titulo) ?>" placeholder="Assunto do tópico" required>
        </label>

        <label for="mensagem">Mensagem
            <textarea id="mensagem" name="mensagem" rows="5" placeholder="Escreva o conteúdo da sua publicação..." required><?= escapar($mensagem) ?></textarea>
        </label>

        <button type="submit">Publicar Tópico</button>
    </form>

    <footer>
        <small><a href="listar.php">Voltar aos tópicos</a></small>
    </footer>
</article>

<?php renderFooter(); ?>