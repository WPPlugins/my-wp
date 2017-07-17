<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'MywpPostTypeInit' ) ) :

final class MywpPostTypeInit {

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

    add_action( 'mywp_init' , array( __CLASS__ , 'regist_post_type' ) );

    add_filter( 'mywp_debug_renders' , array( __CLASS__ , 'mywp_debug_renders' ) , 115 );

    add_action( 'mywp_debug_render_post_type' , array( __CLASS__ , 'mywp_debug_render_post_type' ) );

  }

  public static function plugins_loaded_include_modules() {

    $dir = MYWP_PLUGIN_PATH . 'post-type/modules/';

    $includes = array(
      'admin_sidebar' => $dir . 'mywp.post-type.module.admin.sidebar.php',
    );

    $includes = apply_filters( 'mywp_post_type_plugins_loaded_include_modules' , $includes );

    MywpApi::require_files( $includes );

  }

  public static function after_setup_theme_include_modules() {

    $includes = array();

    $includes = apply_filters( 'mywp_post_type_after_setup_theme_include_modules' , $includes );

    MywpApi::require_files( $includes );

  }

  public static function regist_post_type() {

    $post_types = MywpPostType::get_post_types();

    if( empty( $post_types ) ) {

      return false;

    }

    foreach( $post_types as $post_type_name => $args ) {

      register_post_type( $post_type_name , $args );

    }

  }

  public static function mywp_debug_renders( $debug_renders ) {

    $debug_renders['post_type'] = array(
      'debug_type' => 'mywp',
      'title' => __( 'My WP Post_Type' , 'mywp' ),
    );

    return $debug_renders;

  }

  public static function mywp_debug_render_post_type() {

    echo '<ul>';

    $post_types = MywpPostType::get_post_types();

    if( !empty( $post_types ) ) {

      foreach( $post_types as $post_type_name => $args ) {

        printf( '<li>%s <textarea readonly="readonly">%s</textarea>' , $post_type_name , print_r( $args , true ) );

      }

    }

    echo '</ul>';

  }

}

endif;
