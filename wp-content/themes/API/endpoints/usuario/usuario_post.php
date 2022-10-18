<?php

function api_usuario_post($request){

    $email = sanitize_email($request['email']);
    $nome = sanitize_text_field($request['nome']);
    $senha = $request['senha'];
    $matricula = sanitize_text_field($request['matricula']);
    $aniversario = sanitize_text_field($request['aniversario']);
    $files = $request->get_file_params();

    $user_exists = username_exists($email);
    $email_exists = email_exists($email);

    if ($email && $nome && $senha && !$user_exists && !$email_exists){
        $username = explode("@", $email);
        $user_id = wp_create_user($username[0], $senha, $email);

        wp_update_user(array(
            'ID' => $user_id,
            'display_name' => $nome
        ));

        if ($matricula){
            update_user_meta($user_id, 'matricula', $matricula);
        }

        if ($aniversario){
            update_user_meta($user_id, 'aniversario', $aniversario);
        }

        $first_name = explode(' ', $nome)[0];
        $last_name = end(explode(' ', $nome));

        update_user_meta( $user_id, 'first_name', $first_name);
        update_user_meta( $user_id, 'last_name', $last_name);
        if ($files) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';

            $photo_id = media_handle_upload('img', 0);
            $photo_url = wp_get_attachment_url($photo_id);

            update_user_meta($user_id, 'foto', $photo_url);
        }

        $response = array(
            'message' => 'Usuário Criado!'
        );
    } else {
        $response = new WP_Error('email', 'verifique preenchimento dos dados', array('status' => 403));
    }
    
    return rest_ensure_response($response);
}

function registrar_api_usuario_post(){
    register_rest_route('api', '/usuario', array(
        array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => 'api_usuario_post'
        ),
    ));
}

add_action('rest_api_init', 'registrar_api_usuario_post');

?>