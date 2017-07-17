<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( ! class_exists( 'MywpControllerAbstractModule' ) ) {
  return false;
}

if ( ! class_exists( 'MywpControllerModuleFrontendGeneral' ) ) :

final class MywpControllerModuleFrontendGeneral extends MywpControllerAbstractModule {

  static protected $id = 'frontend_general';

  public static function mywp_controller_initial_data( $initial_data ) {

    $initial_data['admin_bar'] = '';
    $initial_data['hide_wp_generator'] = '';
    $initial_data['hide_wlwmanifest_link'] = '';
    $initial_data['hide_rsd_link'] = '';
    $initial_data['hide_feed_links'] = '';
    $initial_data['hide_feed_links_extra'] = '';
    $initial_data['custom_header_meta'] = '';

    return $initial_data;

  }

  public static function mywp_controller_default_data( $default_data ) {

    $initial_data['admin_bar'] = '';
    $default_data['hide_wp_generator'] = false;
    $default_data['hide_wlwmanifest_link'] = false;
    $default_data['hide_rsd_link'] = false;
    $default_data['hide_feed_links'] = false;
    $default_data['hide_feed_links_extra'] = false;
    $default_data['custom_header_meta'] = '';

    return $default_data;

  }

  public static function mywp_wp_loaded() {

    if( is_admin() ) {

      return false;

    }

    if( ! self::is_do_controller() ) {

      return false;

    }

    add_action( 'wp' , array( __CLASS__ , 'wp' ) );

    add_action( 'wp_head' , array( __CLASS__ , 'wp_head' ) );

  }

  public static function wp() {

    if( ! self::is_do_function( __FUNCTION__ ) ) {

      return false;

    }

    $setting_data = self::get_setting_data();

    if( ! empty( $setting_data['admin_bar'] ) ) {

      if( $setting_data['admin_bar'] == 'hide' ) {

        show_admin_bar( false );

      } elseif( $setting_data['admin_bar'] == 'show' ) {

        show_admin_bar( true );

      }

    }

    if( ! empty( $setting_data['hide_wp_generator'] ) ) {

      remove_action( 'wp_head' , 'wp_generator' );

    }

    if( ! empty( $setting_data['hide_wlwmanifest_link'] ) ) {

      remove_action( 'wp_head' , 'wlwmanifest_link' );

    }

    if( ! empty( $setting_data['hide_rsd_link'] ) ) {

      remove_action( 'wp_head' , 'rsd_link' );

    }

    if( ! empty( $setting_data['hide_feed_links'] ) ) {

      remove_action( 'wp_head' , 'feed_links' , 2 );

    }

    if( ! empty( $setting_data['hide_feed_links_extra'] ) ) {

      remove_action( 'wp_head' , 'feed_links_extra' , 3 );

    }

    self::after_do_function( __FUNCTION__ );

  }

  public static function wp_head() {

    if( ! self::is_do_function( __FUNCTION__ ) ) {

      return false;

    }

    $setting_data = self::get_setting_data();

    if( empty( $setting_data['custom_header_meta'] ) ) {

      return false;

    }

    $custom_header_meta = do_shortcode( $setting_data['custom_header_meta'] );

    echo $custom_header_meta;

    self::after_do_function( __FUNCTION__ );

  }

}

MywpControllerModuleFrontendGeneral::init();

endif;
