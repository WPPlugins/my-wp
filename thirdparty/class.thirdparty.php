<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'MywpThirdparty' ) ) :

final class MywpThirdparty {

  private static $instance;

  private static $plugins = array();

  private static $is_plugin_activates = array();

  private function __construct() {}

  public static function get_instance() {

    if ( !isset( self::$instance ) ) {

      self::$instance = new self();

    }

    return self::$instance;

  }

  private function __clone() {}

  private function __wakeup() {}

  public static function get_thirdparties() {

    return apply_filters( 'mywp_thirdparties' , array() );

  }

  public static function set_plugin( $plugin_base_name = false , $plugin_name = false ) {

    if( empty( $plugin_base_name ) ) {

      $called_text = sprintf( '%s::%s( %s , %s )' , __CLASS__ , __FUNCTION__ , '$plugin_base_name' , '$plugin_name' );

      MywpHelper::error_require_message( '$plugin_base_name' , $called_text );

      return false;

    }

    $plugin_base_name = strip_tags( $plugin_base_name );

    if( ! empty( $plugin_name ) ) {

      $plugin_name = strip_tags( $plugin_name );

    }

    $is_activate = self::is_plugin_activate( $plugin_base_name );

    $plugin = array( 'plugin_name' => $plugin_name , 'plugin_base_name' => $plugin_base_name , 'activate' => $is_activate , 'plugin_data' => array() );

    self::$plugins[ $plugin_base_name ] = $plugin;

  }

  public static function get_plugins() {

    return self::$plugins;

  }

  public static function is_plugin_activate( $plugin_base_name = false ) {

    if( empty( $plugin_base_name ) ) {

      $called_text = sprintf( '%s::%s( %s )' , __CLASS__ , __FUNCTION__ , '$plugin_base_name' );

      MywpHelper::error_require_message( '$plugin_base_name' , $called_text );

      return false;

    }

    $plugin_base_name = strip_tags( $plugin_base_name );

    if( isset( self::$is_plugin_activates[ $plugin_base_name ] ) ) {

      return self::$is_plugin_activates[ $plugin_base_name ];

    }

    $is_plugin_activate = apply_filters( "mywp_thirdparty_pre_plugin_activate_{$plugin_base_name}" , false );

    if( ! $is_plugin_activate ) {

      if( ! function_exists( 'is_plugin_active' ) ) {

        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

      }

      $is_plugin_activate = is_plugin_active( $plugin_base_name );

    }

    self::$is_plugin_activates[ $plugin_base_name ] = $is_plugin_activate;

    return $is_plugin_activate;

  }

  public static function get_plugin_data_by_basename( $plugin_base_name = false ) {

    if( empty( $plugin_base_name ) ) {

      $called_text = sprintf( '%s::%s( %s )' , __CLASS__ , __FUNCTION__ , '$plugin_base_name' );

      MywpHelper::error_require_message( '$plugin_base_name' , $called_text );

      return false;

    }

    $plugin_base_name = strip_tags( $plugin_base_name );

    if( ! isset( self::$plugins[ $plugin_base_name ] ) ) {

      return false;

    }

    if( ! empty( self::$plugins[ $plugin_base_name ]['plugin_data'] ) ) {

      return self::$plugins[ $plugin_base_name ]['plugin_data'];

    }

    if( empty( self::$plugins[ $plugin_base_name ]['activate'] ) ) {

      return false;

    }

    self::$plugins[ $plugin_base_name ]['plugin_data'] = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_base_name );

    return self::$plugins[ $plugin_base_name ]['plugin_data'];

  }

  public static function get_plugin_data_by_name( $plugin_name = false ) {

    if( empty( $plugin_name ) ) {

      $called_text = sprintf( '%s::%s( %s )' , __CLASS__ , __FUNCTION__ , '$plugin_name' );

      MywpHelper::error_require_message( '$plugin_name' , $called_text );

      return false;

    }

    $plugin_name = strip_tags( $plugin_name );

    if( empty( self::$plugins ) ) {

      return false;

    }

    $plugin_data = false;

    foreach( self::$plugins as $plugin ) {

      if( $plugin['plugin_name'] != $plugin_name ) {

        continue;

      }

      $plugin_data = self::get_plugin_data_by_basename( $plugin['plugin_base_name'] );

      break;

    }

    return $plugin_data;

  }

}

endif;
