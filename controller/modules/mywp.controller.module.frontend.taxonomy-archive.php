<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( ! class_exists( 'MywpControllerAbstractModule' ) ) {
  return false;
}

if ( ! class_exists( 'MywpControllerModuleFrontendTaxonomyArchive' ) ) :

final class MywpControllerModuleFrontendTaxonomyArchive extends MywpControllerAbstractModule {

  static protected $id = 'frontend_taxonomy_archive';

  static private $taxonomy = '';

  public static function mywp_controller_initial_data( $initial_data ) {

    $initial_data['disable_archive'] = '';

    return $initial_data;

  }

  public static function mywp_controller_default_data( $default_data ) {

    $default_data['disable_archive'] = false;

    return $default_data;

  }

  public static function mywp_get_option_key( $option_key ) {

    if( empty( self::$taxonomy ) ) {

      return $option_key;

    }

    $option_key .= '_' . self::$taxonomy;

    return $option_key;

  }

  public static function mywp_wp_loaded() {

    if( is_admin() ) {

      return false;

    }

    if( ! self::is_do_controller() ) {

      return false;

    }

    add_action( 'pre_get_posts' , array( __CLASS__ , 'pre_get_posts' ) );

  }

  public static function pre_get_posts( $wp_query ) {

    if( ! self::is_do_function( __FUNCTION__ ) ) {

      return false;

    }

    if( empty( $wp_query->is_category ) && empty( $wp_query->is_tag ) && empty( $wp_query->is_tax ) ) {

      return false;

    }

    if( empty( $wp_query->tax_query ) && empty( $wp_query->tax_query->queried_terms )) {

      return false;

    }

    self::$taxonomy = key( $wp_query->tax_query->queried_terms );

    add_filter( 'mywp_get_option_key_mywp_frontend_taxonomy_archive' , array( __CLASS__ , 'mywp_get_option_key' ) );

    $setting_data = self::get_setting_data();

    if( empty( $setting_data['disable_archive'] ) ) {

      return false;

    }

    $wp_query->set_404();

    self::after_do_function( __FUNCTION__ );

  }

}

MywpControllerModuleFrontendTaxonomyArchive::init();

endif;
