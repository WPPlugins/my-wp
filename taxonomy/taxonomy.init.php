<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'MywpTaxonomyInit' ) ) :

final class MywpTaxonomyInit {

  private static $instance;

  private function __construct() {}

  public static function get_instance() {

    if ( !isset( self::$instance ) ) {

      self::$instance = new self();

    }

    return self::$instance;

  }

  private function __clone() {}

  private function __wakeup() {}

  public static function init() {

    add_action( 'mywp_plugins_loaded' , array( __CLASS__ , 'plugins_loaded_include_modules' ) , 20 );
    add_action( 'mywp_after_setup_theme' , array( __CLASS__ , 'after_setup_theme_include_modules' ) , 20 );

    add_action( 'mywp_init' , array( __CLASS__ , 'regist_taxonomy' ) );

    add_filter( 'mywp_debug_renders' , array( __CLASS__ , 'mywp_debug_renders' ) , 115 );

    add_action( 'mywp_debug_render_taxonomy' , array( __CLASS__ , 'mywp_debug_render_taxonomy' ) );

  }

  public static function plugins_loaded_include_modules() {

    $dir = MYWP_PLUGIN_PATH . 'taxonomy/modules/';

    $includes = array(
    );

    $includes = apply_filters( 'mywp_taxonomy_plugins_loaded_include_modules' , $includes );

    MywpApi::require_files( $includes );

  }

  public static function after_setup_theme_include_modules() {

    $includes = array();

    $includes = apply_filters( 'mywp_taxonomy_after_setup_theme_include_modules' , $includes );

    MywpApi::require_files( $includes );

  }

  public static function regist_taxonomy() {

    $taxonomy_types = MywpTaxonomy::get_taxonomy_types();

    if( empty( $taxonomy_types ) ) {

      return false;

    }

    foreach( $taxonomy_types as $taxonomy_name => $args ) {

      if( empty( $args['post_type'] ) ) {

        continue;

      }

      $post_types = false;

      if( !empty( $args['post_type'] ) ) {

        $post_types = $args['post_type'];
        unset( $args['post_type'] );

      }

      register_taxonomy( $taxonomy_name , $post_types , $args );

    }

  }

  public static function mywp_debug_renders( $debug_renders ) {

    $debug_renders['taxonomy'] = array(
      'debug_type' => 'mywp',
      'title' => __( 'My WP Taxonomy' , 'mywp' ),
    );

    return $debug_renders;

  }

  public static function mywp_debug_render_taxonomy() {

    echo '<ul>';

    $taxonomy_types = MywpTaxonomy::get_taxonomy_types();

    if( !empty( $taxonomy_types ) ) {

      foreach( $taxonomy_types as $taxonomy_name => $args ) {

        printf( '<li>%s <textarea readonly="readonly">%s</textarea>' , $taxonomy_name , print_r( $args , true ) );

      }

    }

    echo '</ul>';

  }

}

endif;
