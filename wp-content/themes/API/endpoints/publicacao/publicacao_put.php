<?php
function api_publicacao_put($request) {
    $current = wp_get_current_user();
    $current_id = $current->ID;

    $id = $request['slug'];

    $post = get_post($id);

    if ($current_id == 0){
        $response = new WP_Error('permissao', 'Usuário não possui permissão.', array('status' => 401));
        return rest_ensure_response($response);
    }

    $nota_edicao = sanitize_textarea_field($request['nota-edicao']);
    $aprovacao = sanitize_text_field($request['aprovacao']);
    $titulo = sanitize_text_field($request['titulo']);
    $texto = sanitize_textarea_field($request['texto']);
    $resumo = sanitize_textarea_field($request['resumo']);

    if ($nota_edicao || $aprovacao){
        if (!in_array("administrator", $current->roles)){
            $response = new WP_Error('permissao', 'Usuário não possui permissão.', array('status' => 401));
            return rest_ensure_response($response);
        }

        if ($nota_edicao){
            update_post_meta($id, 'nota_edicao', $nota_edicao);
        }

        if ($aprovacao) {
            $post->post_status = 'publish';
            wp_update_post($post);
        }
    }

    if ($titulo || $texto || $resumo) {
        if (!in_array("administrator", $current->roles) && $current_id !== $post->post_author){
            $response = new WP_Error('permissao', 'Usuário não possui permissão.', array('status' => 401));
            return rest_ensure_response($response);
        }

        if ($titulo) {
            $post->post_title = $titulo;
            wp_update_post($post);

            date_default_timezone_set('America/Maceio');
            $edited_at = date("F j, Y, g:i a"); 

            update_post_meta($id, 'editado_time', $edited_at);
            update_post_meta($id, 'editado_por', $current->data->user_login);
        }

        if ($texto) {
            update_post_meta($id, 'conteudo', $texto);

            date_default_timezone_set('America/Maceio');
            $edited_at = date("F j, Y, g:i a"); 

            update_post_meta($id, 'editado_time', $edited_at);
            update_post_meta($id, 'editado_por', $current->data->user_login);
        }

        if ($resumo) {
            $post->post_excerpt = $resumo;
            wp_update_post($post);

            date_default_timezone_set('America/Maceio');
            $edited_at = date("F j, Y, g:i a"); 

            update_post_meta($id, 'editado_time', $edited_at);
            update_post_meta($id, 'editado_por', $current->data->user_login);
        }
    }

    return rest_ensure_response(array(
        'message' => 'Publicação Atualizada.'
    ));
}

function registrar_api_publicacao_put() {
  register_rest_route('api', '/publicacao/(?P<slug>[-\w]+)', array(
    array(
      'methods' => WP_REST_Server::EDITABLE,
      'callback' => 'api_publicacao_put',
    ),
  ));
}

add_action('rest_api_init', 'registrar_api_publicacao_put');

?>