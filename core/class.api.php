<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'MywpApi' ) ) :

final class MywpApi {

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

  public static function get_manager_capability() {

    $capability = 'manage_options';

    if( is_multisite() ) {

      $capability = 'manage_network';

    }

    return apply_filters( 'mywp_manager_capability' , $capability );

  }

  public static function is_manager() {

    $capability = self::get_manager_capability();

    if( current_user_can( $capability ) ) {

      return true;

    }

    return false;

  }

  public static function plugin_info() {

    $plugin_info = array(
      'forum_url' => 'https://wordpress.org/support/plugin/my-wp/',
      'review_url' => 'https://wordpress.org/support/plugin/my-wp/reviews/',
      'admin_url' => admin_url( 'admin.php?page=mywp' ),
      'website_url' => 'https://mywpcustomize.com/',
    );

    $plugin_info = apply_filters( 'mywp_plugin_info' , $plugin_info );

    return $plugin_info;

  }

  public static function get_plugin_url( $path = 'assets' ) {

    $path = strip_tags( $path );

    if( $path == 'assets' ) {

      return MYWP_PLUGIN_URL . 'assets/';

    } elseif( $path == 'css' ) {

        return MYWP_PLUGIN_URL . 'assets/css/';

    } elseif( $path == 'js' ) {

        return MYWP_PLUGIN_URL . 'assets/js/';

    } elseif( $path == 'img' ) {

        return MYWP_PLUGIN_URL . 'assets/img/';

    } else {

      $called_text = sprintf( '%s::%s()' , __CLASS__ , __FUNCTION__ );

      MywpHelper::error_not_found_message( $path , $called_text );

    }

  }

  public static function include_files( $files = array() ) {

    if( empty( $files ) ) {

      return false;

    }

    foreach( $files as $file ) {

      self::include_file( $file );

    }

  }

  public static function include_file( $file = false ) {

    if( $file === false ) {

      return false;

    }

    if ( file_exists( $file ) ) {

      $mywp_cache = new MywpCache( 'include_file' );

      $mywp_cache->add_cache( $file );

      include_once( $file );

    } else {

      $error_msg = sprintf( __( "There doesn't seem to be a %s file." , 'mywp' ) , $file );

      return self::add_error( $error_msg );

    }

  }

  public static function require_files( $files = array() ) {

    if( empty( $files ) ) {

      return false;

    }

    foreach( $files as $file ) {

      self::require_file( $file );

    }

  }

  public static function require_file( $file = false ) {

    if( $file === false ) {

      return false;

    }

    if ( file_exists( $file ) ) {

      $mywp_cache = new MywpCache( 'require_file' );

      $mywp_cache->add_cache( $file );

      require_once( $file );

    } else {

      $error_msg = sprintf( __( "There doesn't seem to be a %s file." , 'mywp' ) , $file );

      return self::add_error( $error_msg );

    }

  }

  public static function add_error( $message = false ) {

    $mywp_cache = new MywpCache( 'error' );

    return $mywp_cache->add_cache( $message );

  }

  public static function get_error() {

    $errors = self::get_errors();

    if( empty( $errors ) ) {

      return false;

    }

    return array_shift( $errors );

  }

  public static function get_errors() {

    $mywp_cache = new MywpCache( 'error' );

    return $mywp_cache->get_cache();

  }

  public static function get_all_user_roles() {

    $editable_roles = get_editable_roles();

    if( empty( $editable_roles ) ) {

      $called_text = sprintf( '%s::%s()' , __CLASS__ , __FUNCTION__ );

      MywpHelper::error_not_found_message( '$editable_roles' , $called_text );

      return false;

    }

    $all_user_roles = array();

    foreach( $editable_roles as $role_group_name => $role_details ) {

      $role_group = $role_details;
      $role_group['label'] = translate_user_role( $role_details['name'] );

      $all_user_roles[ $role_group_name ] = $role_group;

    }

    return apply_filters( 'mywp_all_user_roles' , $all_user_roles );
  }

}

endif;
