<?php

/*
Plugin Name: ACF Link Field
Description: A simple link field, add text and a URL
Version: 0.0.1
Author: Seamus P. H. Leahy
Author URI: http://seamusleahy.com
License: MIT
*/
define('ACF_LINK_FIELD_FILE', __FILE__);


function register_acf_link_field($fields) {
  $fields[] = array('class' => 'ACF_Link_Field', 'url' => dirname(__FILE__).'/acf_link_field.class.php');
  
  return $fields;
}


add_filter('acf_register_field', 'register_acf_link_field');