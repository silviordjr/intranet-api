<?php

function api_publicacao_delete($request){
    $id = $request['slug'];

    $current = wp_get_current_user();
    $current_id = $current->ID;

    $post = get_post($id);

    if (!in_array("administrator", $current->roles) && $current_id !== $post->ID){
        $response = new WP_Error('permissao', 'Usuário não possui permissão.', array('status' => 401));
        return rest_ensure_response($response);
    }

    wp_delete_attachment($id, true);
    wp_delete_post( $id, true );

    return rest_ensure_response(array(
        'message' => 'Usuário deletado.'
    ));
}

function registrar_api_publicacao_delete(){
    register_rest_route('api', '/publicacao/(?P<slug>[-\w]+)', array(
        array(
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => 'api_publicacao_delete'
        ),
    ));
}

add_action('rest_api_init', 'registrar_api_publicacao_delete');

?>