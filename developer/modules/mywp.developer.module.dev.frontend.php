<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( ! class_exists( 'MywpDeveloperAbstractModule' ) ) {
  return false;
}

if ( ! class_exists( 'MywpDeveloperModuleDevFrontend' ) ) :

final class MywpDeveloperModuleDevFrontend extends MywpDeveloperAbstractModule {

  static protected $id = 'dev_frontend';

  static protected $priority = 25;

  public static function mywp_debug_renders( $debug_renders ) {

    if( is_admin() ) {

      return $debug_renders;

    }

    $debug_renders[ self::$id ] = array(
      'debug_type' => 'dev',
      'title' => __( 'Current Frontend Information' , 'mywp'),
    );

    return $debug_renders;

  }

  protected static function get_debug_lists() {

    global $post;
    global $template;

    $debug_lists = array(
      'is_front_page()' => is_front_page(),
      'is_home()' => is_home(),
      'is_single()' => is_single(),
      'is_singular()' => is_singular(),
      'is_page()' => is_page(),
      'is_category()' => is_category(),
      'is_tag()' => is_tag(),
      'is_tax()' => is_tax(),
      'is_author()' => is_author(),
      'is_date()' => is_date(),
      'is_archive()' => is_archive(),
      'is_search()' => is_search(),
      'is_404()' => is_404(),
      'is_attachment()' => is_attachment(),
      'is_paged()' => is_paged(),
      'is_post_type_archive()' => is_post_type_archive(),
      'is_page_template()' => is_page_template(),
      'is_main_query()' => is_main_query(),
      'is_preview()' => is_preview(),
      'current_theme_template' => basename( $template ),
    );

    if( is_singular() ) {

      $debug_lists['post_id'] = intval( $post->ID );
      $debug_lists['post'] = $post;
      $debug_lists['custom_fields'] = get_post_meta( $post->ID );

    } elseif( is_archive() ) {

      if( is_category() or is_tag() or is_tax() ) {

        $term = get_queried_object();

        $debug_lists['taxonomy'] = $term->taxonomy;
        $debug_lists['term_id'] = $term->term_id;
        $debug_lists['term'] = $term;
        $debug_lists['custom_meta'] = get_term_meta( $term->term_id );

      } elseif( is_date() ) {

        $debug_lists['get_query_var("year")'] = get_query_var("year");
        $debug_lists['get_query_var("m")'] = get_query_var("m");
        $debug_lists['get_query_var("monthnum")'] = get_query_var("monthnum");
        $debug_lists['get_query_var("day")'] = get_query_var("day");

      }

    } elseif( is_search() ) {

      $debug_lists['get_search_query()'] = get_search_query();

    }

    return $debug_lists;

  }

  protected static function mywp_developer_debug() {

    if( is_admin() ) {

      return false;

    }

    parent::mywp_developer_debug();

  }

  protected static function mywp_debug_render() {

    if( is_admin() ) {

      return false;

    }

    parent::mywp_debug_render();

  }

}

MywpDeveloperModuleDevFrontend::init();

endif;
