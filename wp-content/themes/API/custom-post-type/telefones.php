<?php
function registrar_cpt_telefones() {
  register_post_type('telefones', array(
    'label' => 'Telefones',
    'description' => 'Telefones dos usuarios na intranet',
    'public' => true,
    'show_ui' => true,
    'capability_type' => 'post',
    'rewrite' => array('slug' => 'telefones', 'with_front' => true),
    'query_var' => true,
    'supports' => array('title', 'custom-fields'),
    'publicly_queryable' => true
  ));
}
add_action('init', 'registrar_cpt_telefones');

?>