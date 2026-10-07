<?php
session_start();
require_once __DIR__ . '/funcoes.php';

$erro = '';
$mensagemSucesso = (string) ($_SESSION['mensagem_sucesso'] ?? '');
unset($_SESSION['mensagem_sucesso']);

try {
    $topicos = carregarXml(ARQUIVO_TOPICOS, 'topicos');
} catch (RuntimeException $excecao) {
    $erro = $excecao->getMessage();
    $topicos = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><topicos/>');
}

renderHeader('Fórum de Discussão');
?>

<header style="margin-bottom: 2rem;">
    <nav class="actions-row">
        <div>
            <h1 style="margin-bottom: 0;color: black">Fórum</h1>
        </div>
        <div>
            <?php if (isset($_SESSION['usuario'])): ?>
                <small>Conectado como <strong><?= escapar($_SESSION['usuario']) ?></strong></small> 
                <a href="criar_topico.php" role="button" class="btn-inline">Novo Tópico</a>
            <?php else: ?>
                <a href="login.php" role="button" class="outline btn-inline">Entrar</a>
                <a href="cadastro.php" role="button" class="btn-inline">Cadastrar</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<?php if ($mensagemSucesso !== ''): ?>
    <div class="alert-success"><?= escapar($mensagemSucesso) ?></div>
<?php endif; ?>

<?php if ($erro !== ''): ?>
    <div class="alert-error"><?= escapar($erro) ?></div>
<?php elseif (count($topicos->topico) === 0): ?>
    <article><p>Nenhum tópico foi criado ainda. Seja o primeiro a publicar!</p></article>
<?php endif; ?>

<?php $id = 0; ?>
<?php foreach ($topicos->topico as $topico): ?>
    <article class="card-topic">
        <header class="actions-row">
            <h3 style="margin: 0;"><?= escapar($topico->titulo) ?></h3>
            <small>Por: <strong><?= escapar($topico->autor) ?></strong></small>
        </header>

        <p><?= nl2br(escapar($topico->mensagem)) ?></p>

        <hr>

        <h4>Comentários</h4>

        <?php if (count($topico->comentarios->comentario) === 0): ?>
            <p><small>Nenhum comentário ainda.</small></p>
        <?php endif; ?>

        <?php $comentarioId = 0; ?>
        <?php foreach ($topico->comentarios->comentario as $comentario): ?>
            <div class="comment-box">
                <div class="actions-row">
                    <strong><?= escapar($comentario->nome) ?></strong>
                    
                    <?php if (isset($_SESSION['usuario']) && (string) $_SESSION['usuario'] === (string) $topico->autor): ?>
                        <form method="post" action="excluir.php" style="margin: 0;">
                            <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
                            <input type="hidden" name="id" value="<?= escapar($id) ?>">
                            <input type="hidden" name="comentario" value="<?= escapar($comentarioId) ?>">
                            <button type="submit" class="outline secondary btn-inline" style="padding: 2px 8px; font-size: 0.75rem;">Excluir</button>
                        </form>
                    <?php endif; ?>
                </div>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.95rem;"><?= nl2br(escapar($comentario->mensagem)) ?></p>
            </div>
            <?php $comentarioId++; ?>
        <?php endforeach; ?>

        <details style="margin-top: 1rem;">
            <summary>Deixar um comentário</summary>
            <form method="post" action="comentar.php" style="margin-top: 1rem;">
                <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
                <input type="hidden" name="id" value="<?= escapar($id) ?>">
                
                <label for="nome_<?= $id ?>">Seu Nome
                    <input type="text" id="nome_<?= $id ?>" name="nome" placeholder="Como deseja se identificar" required>
                </label>
                
                <label for="mensagem_<?= $id ?>">Comentário
                    <textarea id="mensagem_<?= $id ?>" name="mensagem" rows="2" placeholder="Digite seu comentário..." required></textarea>
                </label>
                
                <button type="submit" class="btn-inline">Enviar Comentário</button>
            </form>
        </details>
    </article>
    <?php $id++; ?>
<?php endforeach; ?>

<?php renderFooter(); ?>