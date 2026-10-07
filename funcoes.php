<?php

const ARQUIVO_USUARIOS = __DIR__ . DIRECTORY_SEPARATOR . 'usuarios.xml';
const ARQUIVO_TOPICOS = __DIR__ . DIRECTORY_SEPARATOR . 'topicos.xml';

function carregarXml(string $arquivo, string $raiz): SimpleXMLElement
{
    $conteudo = is_file($arquivo) ? file_get_contents($arquivo) : false;
    if ($conteudo === false || trim($conteudo) === '') {
        return new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><' . $raiz . '/>');
    }

    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($conteudo, SimpleXMLElement::class, LIBXML_NONET);
    libxml_clear_errors();
    if ($xml === false || $xml->getName() !== $raiz) {
        throw new RuntimeException('O arquivo de dados está corrompido ou possui uma estrutura inválida.');
    }
    return $xml;
}

function salvarXml(SimpleXMLElement $xml, string $arquivo): void
{
    $conteudo = $xml->asXML();
    if ($conteudo === false || file_put_contents($arquivo, $conteudo, LOCK_EX) === false) {
        throw new RuntimeException('Não foi possível salvar os dados.');
    }
}

function adicionarTextoXml(SimpleXMLElement $pai, string $nome, string $valor): SimpleXMLElement
{
    $filho = $pai->addChild($nome);
    $filho[0] = $valor;
    return $filho;
}

function escapar(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function obterIndice(mixed $valor): ?int
{
    if (!is_string($valor) && !is_int($valor)) {
        return null;
    }
    $indice = filter_var($valor, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    return $indice === false ? null : $indice;
}

function tokenCsrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfValido(mixed $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

// Funções visuais para deixar todas as páginas com visual profissional
function renderHeader(string $titulo): void
{
    echo '<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>' . $titulo . '</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <style>
        body { padding-top: 2rem; background-color: #f8f9fa; }
        main.container { max-width: 800px; }
        .card-topic { margin-bottom: 2rem; padding: 1.5rem; background: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .comment-box { background: #f1f3f5; padding: 1rem; border-radius: 6px; margin-bottom: 0.75rem; border-left: 4px solid #0172ad; }
        .alert-error { background-color: #f8d7da; color: #842029; padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1rem; border: 1px solid #f5c2c7; }
        .alert-success { background-color: #d1e7dd; color: #0f5132; padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1rem; border: 1px solid #badbcc; }
        .actions-row { display: flex; gap: 0.5rem; align-items: center; justify-content: space-between; }
        .btn-inline { margin-bottom: 0; padding: 0.3rem 0.75rem; font-size: 0.85rem; }
    </style>
</head>
<body>
<main class="container">';
}

function renderFooter(): void
{
    echo '</main></body></html>';
}

function encerrarComErro(string $mensagem, int $status = 400): never
{
    http_response_code($status);
    renderHeader('Erro - Fórum');
    echo '<div class="alert-error">' . escapar($mensagem) . '</div>';
    echo '<p><a href="listar.php" role="button" class="outline">Voltar aos tópicos</a></p>';
    renderFooter();
    exit;
}