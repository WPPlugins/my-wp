<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'MywpThirdpartyInit' ) ) :

final class MywpThirdpartyInit {

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

    add_action( 'mywp_after_setup_theme' , array( __CLASS__ , 'mywp_after_setup_theme' ) , 100 );

    add_filter( 'mywp_debug_renders' , array( __CLASS__ , 'mywp_debug_renders' ) , 200 );

    add_action( 'mywp_debug_render_thirdparty' , array( __CLASS__ , 'mywp_debug_render_thirdparty' ) );

  }

  public static function plugins_loaded_include_modules() {

    $dir = MYWP_PLUGIN_PATH . 'thirdparty/modules/';

    $includes = array(
      'acf'         => $dir . 'advanced-custom-fields.php',
      'woocommerce' => $dir . 'woocommerce.php',
    );

    $includes = apply_filters( 'mywp_thirdparty_plugins_loaded_include_modules' , $includes );

    MywpApi::require_files( $includes );

  }

  public static function after_setup_theme_include_modules() {

    $includes = array();

    $includes = apply_filters( 'mywp_thirdparty_after_setup_theme_include_modules' , $includes );

    MywpApi::require_files( $includes );

  }

  public static function mywp_after_setup_theme() {

    $thirdparties = MywpThirdparty::get_thirdparties();

    if( empty( $thirdparties ) ) {

      return false;

    }

    foreach( $thirdparties as $plugin_name => $plugin_base_name ) {

      MywpThirdparty::set_plugin( $plugin_base_name , $plugin_name );

    }

  }

  public static function mywp_debug_renders( $debug_renders ) {

    $debug_renders['thirdparty'] = array(
      'debug_type' => 'mywp',
      'title' => __( 'Thirdparty' , 'mywp' ),
    );

    return $debug_renders;

  }

  public static function mywp_debug_render_thirdparty() {

    $thirdparties = MywpThirdparty::get_plugins();

    if( !empty( $thirdparties ) ) {

      echo '<table class="debug-table">';

      foreach( $thirdparties as $plugin_base_name => $plugin ) {

        echo '<tr>';

        printf( '<th>%s</th>' , $plugin['plugin_name'] );

        echo '<td>';

        if( $plugin['activate']  ) {

          _e( 'Activated' , 'mywp' );

        } else {

          _e( 'Not Activated' , 'mywp' );

        }

        echo '<br />';

        printf( __( 'plugin base name: %s' , 'mywp' ) . '<br />' , $plugin['plugin_base_name'] );

        echo '</td>';

        echo '</tr>';

      }

      echo '</table>';

    }

  }

}

endif;
