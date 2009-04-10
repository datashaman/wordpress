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
  $haml_template = basename($wordpress_template, '.php').'.haml';
  return $haml_template;
}

function haml_render_template($template_name, $variables = array())
{
  static $parser;
  empty($parser) and $parser = new HamlParser(TEMPLATEPATH.'/tpl', TEMPLATEPATH.'/tmp');
  $parser->removeBlank(true);
  $parser->clearCompiled();
  $parser->append($variables);
  $result = $parser->fetch($template_name);
  return trim($result);
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
    echo haml_render_template($template);
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
        echo haml_render_template($template);
        exit;
      }
    }

    if(file_exists(TEMPLATEPATH . "/tpl/index.haml") ) {
      echo haml_render_template('index.haml');
    }
  }

  exit;
}

function haml_get_language_attributes()
{
  $attributes = array();
  $output = '';

  if ( $dir = get_bloginfo('text_direction') )
          $attributes[] = "dir=\"$dir\"";

  if ( $lang = get_bloginfo('language') ) {
          if ( get_option('html_type') == 'text/html' || $doctype == 'html' )
                  $attributes[] = "lang=\"$lang\"";

          if ( get_option('html_type') != 'text/html' || $doctype == 'xhtml' )
                  $attributes[] = "xml:lang=\"$lang\"";
  }

  // TODO $output = implode(' ', $attributes);
  // TODO $output = apply_filters('language_attributes', $output);
  
  return $attributes;
}

function haml_get_footer()
{
  haml_render_template('footer.haml');
}

function haml_the_permalink()
{
  ob_start();
  the_permalink();
  $permalink = ob_get_clean();
  ob_end_flush();
  return $permalink;
}

function haml_wp_head()
{
  ob_start();
  wp_head();
  $head = ob_get_clean();
  ob_end_flush();
  return $head;
}

function haml_edit_post_link($link = 'Edit This', $before = '', $after = '')
{
  ob_start();
  edit_post_link($link, $before, $after);
  $link = ob_get_clean();
  ob_end_flush();
  return $link;
}

function haml_comments_popup_link( $zero = 'No Comments', $one = '1 Comment', $more = '% Comments', $css_class = '', $none = 'Comments Off' )
{
  ob_start();
  comments_popup_link($zero, $one, $more, $css_class, $none);
  $link = ob_get_clean();
  ob_end_flush();
  return $link;
}

function haml_the_post()
{
  ob_start();
  the_post();
  $post = ob_get_clean();
  ob_end_flush();
  return $post;
}

$theme_data = get_theme_data(get_template_directory().'/style.css');

if(in_array('haml', $theme_data['Tags'])) {
  add_action('template_redirect', 'haml_template_redirect');
}
