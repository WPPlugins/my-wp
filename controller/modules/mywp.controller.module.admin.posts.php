<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( ! class_exists( 'MywpControllerAbstractModule' ) ) {
  return false;
}

if ( ! class_exists( 'MywpControllerModuleAdminPosts' ) ) :

final class MywpControllerModuleAdminPosts extends MywpControllerAbstractModule {

  static protected $id = 'admin_posts';

  static private $post_type = '';

  public static function mywp_controller_initial_data( $initial_data ) {

    $initial_data['hide_add_new'] = '';
    $initial_data['hide_search_box'] = '';
    $initial_data['per_page_num'] = '';

    return $initial_data;

  }

  public static function mywp_controller_default_data( $default_data ) {

    $default_data['hide_add_new'] = false;
    $default_data['hide_search_box'] = false;
    $default_data['per_page_num'] = 20;

    return $default_data;

  }

  public static function mywp_wp_loaded() {

    if( ! is_admin() ) {

      return false;

    }

    if( is_network_admin() ) {

      return false;

    }

    if( ! self::is_do_controller() ) {

      return false;

    }

    add_action( 'mywp_ajax' , array( __CLASS__ , 'mywp_ajax' ) , 1000 );

    add_action( 'load-edit.php' , array( __CLASS__ , 'load_edit' ) , 1000 );

  }

  public static function mywp_get_option_key( $option_key ) {

    if( empty( self::$post_type ) ) {

      return $option_key;

    }

    $option_key .= '_' . self::$post_type;

    return $option_key;

  }

  public static function mywp_ajax() {

    if( empty( $_POST['action'] ) or $_POST['action'] != 'inline-save' ) {

      return false;

    }

    if( empty( $_POST['screen'] ) or $_POST['screen'] != 'edit-post' ) {

      return false;

    }

    if( empty( $_POST['post_type'] ) ) {

      return false;

    }

    self::$post_type = strip_tags( $_POST['post_type'] );

    add_filter( 'mywp_get_option_key_mywp_admin_posts' , array( __CLASS__ , 'mywp_get_option_key' ) );

  }

  public static function load_edit() {

    global $typenow;

    if( empty( $typenow ) ) {

      return false;

    }

    self::$post_type = $typenow;

    add_filter( 'mywp_get_option_key_mywp_admin_posts' , array( __CLASS__ , 'mywp_get_option_key' ) );

    add_action( 'admin_print_styles-edit.php' , array( __CLASS__ , 'hide_items' ) );

    add_filter( "edit_{$typenow}_per_page" , array( __CLASS__ , 'edit_per_page' ) );

  }

  public static function hide_items() {

    if( ! self::is_do_function( __FUNCTION__ ) ) {

      return false;

    }

    $setting_data = self::get_setting_data();

    if( empty( $setting_data ) ) {

      return false;

    }

    echo '<style>';

    if( !empty( $setting_data['hide_add_new'] ) ) {

      echo 'body.wp-admin .wrap h1 a { display: none; }';
      echo 'body.wp-admin .wrap .page-title-action { display: none; }';

    }

    if( !empty( $setting_data['hide_search_box'] ) ) {

      echo 'body.wp-admin #posts-filter .search-box { display: none; }';

    }

    echo '</style>';

    self::after_do_function( __FUNCTION__ );

  }

  public static function edit_per_page( $per_page ) {

    if( ! self::is_do_function( __FUNCTION__ ) ) {

      return false;

    }

    $setting_data = self::get_setting_data();

    if( empty( $setting_data ) ) {

      return false;

    }

    if( empty( $setting_data['per_page_num'] ) ) {

      return $per_page;

    }

    self::after_do_function( __FUNCTION__ );

    return $setting_data['per_page_num'];

  }

}

MywpControllerModuleAdminPosts::init();

endif;
