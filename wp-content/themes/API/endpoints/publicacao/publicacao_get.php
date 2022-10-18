<?php
// Definição do modelo de retorno para as publicações
function publicacao_scheme ($id) {
    $post = get_post($id);
    $post_meta = get_post_meta($id);
    $images = get_attached_media('image', $id);
    $images_array = null;

    if($images) {
        $images_array = array();
        foreach($images as $key => $value) {
            $images_array[] = array(
            'titulo' => $value->post_name,
            'src' => $value->guid,
            );
        }
    }

    $author = get_user_by('id', $post->post_author);
    $author_meta = get_user_meta($post->post_author);
    $author_response = array(
        'id' => $author->data->ID,
        'email' => $author->data->user_email,
        'nome' => $author->data->display_name,
        'username' => $author_meta['nickname'][0],
        'matricula' => $author_meta['matricula'][0],
        'foto' => $author_meta['foto'][0],
    );

    $tags_res = array();
    $tags_arr = explode("\"", $post_meta['tags'][0]);

    for ($i = 0; $i <= sizeof($tags_arr) - 1; $i++){
        if ($i % 2 === 1){
            array_push($tags_res, $tags_arr[$i]);
        }
    }

    $response = array(
        'id' => $id,
        'fotos' => $images_array,
        'titulo' => $post->post_title,
        'texto' => $post_meta['conteudo'][0],
        'resumo' => $post->post_excerpt,
        'status' => $post->post_status,
        'date' => $post_meta['date'][0],
        'tags' => $tags_res,
        'notas_ascom' => $post_meta['nota_edicao'][0],
        'editado_time' => $post_meta['editado_time'][0],
        'editado_por' => $post_meta['editado_por'][0],
        'autor' => $author_response,
    );
    
    return $response;
}

// Endpoint get all de publicações
function api_publicacao_get($request) {
    $q = sanitize_text_field($request['q']) ?: '';
    $_page = sanitize_text_field($request['_page']) ?: 0;
    $_limit = sanitize_text_field($request['_limit']) ?: 9;
    $_status = sanitize_text_field($request['_status']) ?: 'publish';

    $user = wp_get_current_user();
    $user_id = $user->ID;

    if ($user_id == 0){
        $response = new WP_Error('permissao', 'Usuário não possui permissão.', array('status' => 401));
        return rest_ensure_response($response);
    }

    $query = array(
        'post_type' => 'publicacao',
        'posts_per_page' => $_limit,
        'paged' => $_page,
        's' => $q,
        'post_status' => $_status,
    );

    $loop = new WP_Query($query);
    $posts = $loop->posts;

    $publicacoes = array();
    foreach($posts as $post) {
        $p = publicacao_scheme($post->ID);
        array_push($publicacoes, $p);
    }

    return rest_ensure_response($publicacoes);
}

function registrar_api_publicacao_get() {
  register_rest_route('api', '/publicacao', array(
    array(
      'methods' => WP_REST_Server::READABLE,
      'callback' => 'api_publicacao_get',
    ),
  ));
}

add_action('rest_api_init', 'registrar_api_publicacao_get');

//endpoint get by id

function api_publicacao_get_by_id ($request) {
    $slug = $request['slug'];

    $user = wp_get_current_user();
    $user_id = $user->ID;

    if ($user_id == 0){
        $response = new WP_Error('permissao', 'Usuário não possui permissão.', array('status' => 401));
        return rest_ensure_response($response);
    }

    $query = array(
        'post_type' => 'publicacao',
        'p' => $slug,
    );

    $loop = new WP_Query($query);
    $posts = $loop->posts;

    if (sizeof($posts) === 0){
        $response = new WP_Error('not found', 'Post não encontrado.', array('status' => 404));
        return rest_ensure_response($response);
    }

    $response = publicacao_scheme($posts[0]->ID);

    return rest_ensure_response($response);
}

function registrar_api_publicacao_get_by_id () {
    register_rest_route('api', '/publicacao/(?P<slug>[-\w]+)', array(
        array(
          'methods' => WP_REST_Server::READABLE,
          'callback' => 'api_publicacao_get_by_id',
        ),
      ));
}

add_action('rest_api_init', 'registrar_api_publicacao_get_by_id');
?>