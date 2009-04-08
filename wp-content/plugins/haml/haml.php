<?php
/**
 * @package HAML Templates
 * @author Matt Mullenweg
 * @version 1.5
 */
/*
Plugin Name: HAML
Plugin URI: http://wordpress.org/#
Description: Allows template designers to use HAML/SASS for designing templates in Wordpress
Author: Marlin Forbes
Version: 0.1
Author URI: http://www.datashaman.com/
*/

require_once dirname(__FILE__).'/phphaml/includes/haml/HamlParser.class.php';

function haml_get_template($template_name)
{
  $wordpress_function = "get_{$template_name}_template";
  $wordpress_template = $wordpress_function();
  var_dump($wordpress_template);
  $haml_template = basename($wordpress_template, '.php').'.haml';
  return $haml_template;
}

function haml_render_template($template_name)
{
  static $parser;
  empty($parser) and $parser = new HamlParser(TEMPLATEPATH.'/tpl', TEMPLATEPATH.'/tmp');
  $parser->display($template_name);
}

function haml_template_redirect()
{
  if(is_robots()) {
    do_action('do_robots');
  } else if(is_feed() ) {
    do_feed();
  } else if(is_trackback() ) {
    include(ABSPATH . 'wp-trackback.php');
  } else if(is_attachment() && $template = haml_get_attachment_template() ) {
    remove_filter('the_content', 'prepend_attachment');
    haml_render_template($template);
  } else {
    $template_names = array(
      '404', 'search', 'tax',
      'tax', 'home', 'single', 'page',
      'category', 'tag', 'author', 'date',
      'archive', 'comments_popup', 'paged'
    );

    foreach($template_names as $template_name) {
      $check = "is_{$template_name}";
      if($check() && $template = haml_get_template($template_name)) {
        var_dump($template_name, $template);
        haml_render_template($template);
        exit;
      }
    }

    if(file_exists(TEMPLATEPATH . "/tpl/index.haml") ) {
      haml_render_template('index.haml');
    }
  }

  exit;
}

add_action('template_redirect', 'haml_template_redirect');
