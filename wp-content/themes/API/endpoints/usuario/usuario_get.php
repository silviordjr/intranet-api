<?php
// endpoint de get usuario atual, através do JWT passado no authorization
function api_usuario_get_current($request){

    $user = wp_get_current_user();
    $user_id = $user->ID;

    if ($user_id > 0){
        $user_meta = get_user_meta($user_id);
    }

    $user_response = array(
        'user' => $user,
        'meta' => $user_meta
    );

    return rest_ensure_response($user_response);
}

function registrar_api_usuario_get_current(){
    register_rest_route('api', '/usuario_atual', array(
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'api_usuario_get_current'
        ),
    ));
}

add_action('rest_api_init', 'registrar_api_usuario_get_current');

// definicao do modelo de retorno do usuario

function user_scheme ($user) {
    $user_id = $user->ID;
    $user_meta = get_user_meta( $user_id );

    $response = array(
        'id' => $user->ID,
        'email' => $user->data->user_email,
        'roles' => $user->roles,
        'nome' => $user->data->display_name,
        'username' => $user_meta['nickname'][0],
        'matricula' => $user_meta['matricula'][0],
        'foto' => $user_meta['foto'][0],
    );

    return $response;
}

//definicao do retorno dos posts do usuario

function publicacao_user_scheme ($post) {
    $post_meta = get_post_meta($post->ID);
    $images = get_attached_media('image', $post->ID);
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

    $tags_res = array();
    $tags_arr = explode("\"", $post_meta['tags'][0]);

    for ($i = 0; $i <= sizeof($tags_arr) - 1; $i++){
        if ($i % 2 === 1){
            array_push($tags_res, $tags_arr[$i]);
        }
    }

    $response = array(
        'id' => $post->ID,
        'fotos' => $images_array,
        'titulo' => $post->post_title,
        'texto' => $post_meta['conteudo'][0],
        'resumo' => $post->post_excerpt,
        'status' => $post->post_status,
        'date' => $post_meta['date'][0],
        'tags' => $tags_res,
    );
    
    return $response;
}

// endpoint get users

function api_usuario_get($request){
    $q = sanitize_text_field($request['q']) ?: '';
    $_page = sanitize_text_field($request['_page']) ?: 1;
    $_limit = sanitize_text_field($request['_limit']) ?: 9;

    $user = wp_get_current_user();
    $user_id = $user->ID;

    if ($user_id == 0){
        $response = new WP_Error('permissao', 'Usuário não possui permissão.', array('status' => 401));
        return rest_ensure_response($response);
    }

    $args = array(
        'number' => $_limit,
        'paged' => $_page,
        'search' => "*{$q}*",
        'search-columns' => array(
            'user_login',
            'user_email',
            'user_nicename',
            'display_name',
        ),
        'meta_query' => array(
            'relation' => 'OR',
            array(
                'key'     => 'first_name',
                'value'   => $q,
                'compare' => 'LIKE'
            ),
            array(
                'key'     => 'last_name',
                'value'   => $q,
                'compare' => 'LIKE'
            )
            ),
    );

    $user_search = new WP_User_Query( $args );
    $users = $user_search->get_results();
    $users_array = array();

    foreach($users as $u){
        $formated_u = user_scheme($u);
        array_push($users_array, $formated_u);
    }

    return rest_ensure_response($users_array);
}

function registrar_api_usuario_get (){
    register_rest_route('api', '/usuario', array(
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'api_usuario_get'
        ),
    ));
}

add_action('rest_api_init', 'registrar_api_usuario_get');

//endpoint get user by id

function api_usuario_get_by_id ($request) {
    $current = wp_get_current_user();
    $current_id = $current->ID;

    if ($current_id == 0){
        $response = new WP_Error('permissao', 'Usuário não possui permissão.', array('status' => 401));
        return rest_ensure_response($response);
    }

    $id = $request['slug'];

    $user = get_user_by('ID', $id);

    $user_response = user_scheme($user);

    $query = array(
        'post_type' => 'publicacao',
        'posts_per_page' => 9,
        'paged' => 0,
        'author' => $id,
    );

    $loop = new WP_Query($query);
    $posts = $loop->posts;

    $user_posts = array();

    foreach ($posts as $post) {
        $p = publicacao_user_scheme($post);
        array_push($user_posts, $p);
    }


    $response = array(
        'user' => $user_response,
        'posts' => $user_posts,
    );

    return rest_ensure_response($response);
}

function registrar_api_usuario_get_by_id () {
    register_rest_route('api', '/usuario/(?P<slug>[-\w]+)', array(
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'api_usuario_get_by_id'
        ),
    ));
}

add_action('rest_api_init', 'registrar_api_usuario_get_by_id');

?>