<?php 
$template_diretorio = get_template_directory();

// chamada dos endpoints do usuário
require_once($template_diretorio . '/endpoints/usuario/usuario_get.php');
require_once($template_diretorio . '/endpoints/usuario/usuario_post.php');
require_once($template_diretorio . '/endpoints/usuario/usuario_put.php');
require_once($template_diretorio . '/endpoints/usuario/usuario_delete.php');

// chamada dos endpoints das publicacoes
require_once($template_diretorio . '/endpoints/publicacao/publicacao_get.php');
require_once($template_diretorio . '/endpoints/publicacao/publicacao_post.php');
require_once($template_diretorio . '/endpoints/publicacao/publicacao_put.php');
require_once($template_diretorio . '/endpoints/publicacao/publicacao_delete.php');

// criacao dos post types
require_once($template_diretorio . '/custom-post-type/publicacao.php');
require_once($template_diretorio . '/custom-post-type/telefones.php');
?>