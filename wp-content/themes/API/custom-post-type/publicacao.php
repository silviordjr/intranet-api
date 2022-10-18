<?php
function registrar_cpt_publicacao() {
  register_post_type('publicacao', array(
    'label' => 'Publicacao',
    'description' => 'Publicacao dos usuarios na intranet',
    'public' => true,
    'show_ui' => true,
    'capability_type' => 'post',
    'rewrite' => array('slug' => 'publicacao', 'with_front' => true),
    'query_var' => true,
    'supports' => array('author', 'title', 'excerpt', 'custom-fields'),
    'publicly_queryable' => true
  ));
}
add_action('init', 'registrar_cpt_publicacao');

?>