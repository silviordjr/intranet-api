<?php

function api_usuario_put($request){
    $current = wp_get_current_user();
    $current_id = $current->ID;
    $is_current_admin = $current->edit_users;

    $user_id = $request['slug'];
    $user = get_user_by('ID', $user_id);

    if (!$user_id) {
        $response = new WP_Error('usuario', 'indique o usuário a ser modificado.', array('status' => 422));
        return rest_ensure_response($response);
    }

    if ($current_id == 0){
        $response = new WP_Error('permissao', 'Usuário não possui permissão.', array('status' => 401));
        return rest_ensure_response($response);
    }

    if ($user_id != $current_id && !$is_current_admin) {
        $response = new WP_Error('permissao', 'Sem permissão para esta operação.', array('status' => 403));
        return rest_ensure_response($response);
    }

    $email = sanitize_email($request['email']);
    $matricula = sanitize_text_field($request['matricula']);
    $aniversario = sanitize_text_field($request['aniversario']);
    $nome = sanitize_text_field($request['nome']);
    $files = $request->get_file_params();

    $email_exists = email_exists($email);

    if (!$email_exists || $email_exists === $user_id || !$email){
        $email && wp_update_user(array(
            'ID' => $user_id,
            'user_email' => $email,
        ));

        if ($nome) {
            $first_name = explode(' ', $nome)[0];
            $last_name = end(explode(' ', $nome));

            wp_update_user(array(
                'ID' => $user_id,
                'display_name' => $nome,
            ));

            update_user_meta( $user_id, 'first_name', $first_name);
            update_user_meta( $user_id, 'last_name', $last_name);
        }

        if ($files) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';

            $photo_id = media_handle_upload('img', 0);
            $photo_url = wp_get_attachment_url($photo_id);

            update_user_meta($user_id, 'foto', $photo_url);
        }

        $aniversario && update_user_meta( $user_id, 'aniversario', $aniversario );
        $matricula && update_user_meta( $user_id, 'matricula', $matricula );

        $response = array(
            'message' => 'Usuário modificado.'
        );
    }

    return rest_ensure_response($response);
}

function registrar_api_usuario_put(){
    register_rest_route('api', '/usuario/(?P<slug>[-\w]+)', array(
        array(
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => 'api_usuario_put'
        ),
    ));
}

add_action('rest_api_init', 'registrar_api_usuario_put');

?>