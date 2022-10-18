<?php

function api_usuario_delete($request){
    $slug = $request['slug'];
    $user = wp_get_current_user();
    $user_id = $user->ID;
    $is_current_admin = $user->delete_users;

    if (!$is_current_admin){
        $response = new WP_Error('permissao', 'Sem permissão para esta operação.', array('status' => 403));
        return rest_ensure_response($response);
    }

    wp_delete_user($user_id);

    return rest_ensure_response(array(
        'message' => 'Usuário deletado.'
    ));
}

function registrar_api_usuario_delete(){
    register_rest_route('api', '/usuario/(?P<slug>[-\w]+)', array(
        array(
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => 'api_usuario_delete'
        ),
    ));
}

add_action('rest_api_init', 'registrar_api_usuario_delete');

?>