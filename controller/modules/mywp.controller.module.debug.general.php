<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( ! class_exists( 'MywpControllerAbstractModule' ) ) {
  return false;
}

if ( ! class_exists( 'MywpControllerModuleDebugGeneral' ) ) :

final class MywpControllerModuleDebugGeneral extends MywpControllerAbstractModule {

  static protected $id = 'debug_general';

  static protected $model_use_filter = false;

  static protected $is_do_controller = true;

  public static function mywp_controller_initial_data( $initial_data ) {

    $initial_data['users'] = array();

    return $initial_data;

  }

  public static function mywp_controller_default_data( $default_data ) {

    $default_data['users'] = array();

    return $default_data;

  }

  public static function mywp_wp_loaded() {

    add_filter( 'mywp_is_debug' , array( __CLASS__ , 'mywp_is_debug' ) , 9 );

  }

  public static function mywp_is_debug( $is_debug ) {

    if( ! self::is_do_function( __FUNCTION__ ) ) {

      return false;

    }

    $setting_data = self::get_setting_data();

    if( empty( $setting_data['users'] ) ) {

      return false;

    }

    $user_id = get_current_user_id();

    if( empty( $user_id ) ) {

      return false;

    }

    if( in_array( $user_id , $setting_data['users'] ) ) {

      return true;

    }

    return false;

  }

}

MywpControllerModuleDebugGeneral::init();

endif;
