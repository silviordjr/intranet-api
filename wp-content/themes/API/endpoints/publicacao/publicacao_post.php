<?php

function api_publicacao_post($request) {
    $user = wp_get_current_user();
    $user_id = $user->ID;

    if ($user_id == 0){
        $response = new WP_Error('permissao', 'Usuário não possui permissão.', array('status' => 401));
        return rest_ensure_response($response);
    }

    $titulo = sanitize_text_field($request['titulo']);
    $texto = sanitize_textarea_field($request['texto']);
    $resumo = sanitize_textarea_field($request['resumo']);
    $files = $request->get_file_params();
    $tags = rest_sanitize_array($request['tags']);
    date_default_timezone_set('America/Maceio');
    $post_at = date("F j, Y, g:i a"); 
    

    $response = array(
        'post_author' => $user_id,
        'post_type' => 'publicacao',
        'post_title' => $titulo,
        'post_status' => 'pending',
        'post_excerpt' => $resumo,
        'meta_input' => array(
            'conteudo' => $texto,
            'tags' => $tags,
            'date' => $post_at,
            'nota_edicao' => '',
            'editado_time' => '',
            'editado_por' => '',
        ),
    );

    $publi_id = wp_insert_post($response);
    $response['id'] = get_post_field('post_name', $publi_id);

    if ($files){
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        media_handle_upload('img', $produto_id);
    }

    return rest_ensure_response($response);
}

function registrar_api_publicacao_post() {
  register_rest_route('api', '/publicacao', array(
    array(
      'methods' => WP_REST_Server::CREATABLE,
      'callback' => 'api_publicacao_post',
    ),
  ));
}

add_action('rest_api_init', 'registrar_api_publicacao_post');


?>