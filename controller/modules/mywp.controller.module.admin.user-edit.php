<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( ! class_exists( 'MywpControllerAbstractModule' ) ) {
  return false;
}

if ( ! class_exists( 'MywpControllerModuleAdminUserEdit' ) ) :

final class MywpControllerModuleAdminUserEdit extends MywpControllerAbstractModule {

  static protected $id = 'admin_user_edit';

  public static function mywp_controller_initial_data( $initial_data ) {

    $initial_data['hide_rich_editing'] = '';
    $initial_data['hide_admin_color'] = '';
    $initial_data['hide_comment_shortcuts'] = '';
    $initial_data['hide_toolbar'] = '';
    $initial_data['hide_language'] = '';
    $initial_data['hide_url'] = '';
    $initial_data['hide_description'] = '';
    $initial_data['hide_picture'] = '';
    $initial_data['hide_session'] = '';

    $initial_data['hide_contact_fields'] = '';

    return $initial_data;

  }

  public static function mywp_controller_default_data( $default_data ) {

    $default_data['hide_rich_editing'] = false;
    $default_data['hide_admin_color'] = false;
    $default_data['hide_comment_shortcuts'] = false;
    $default_data['hide_toolbar'] = false;
    $initial_data['hide_language'] = false;
    $default_data['hide_url'] = false;
    $default_data['hide_description'] = false;
    $initial_data['hide_picture'] = false;
    $default_data['hide_session'] = false;

    $default_data['hide_contact_fields'] = '';

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

    add_action( 'load-profile.php' , array( __CLASS__ , 'load_user_edit' ) , 1000 );
    add_action( 'load-user-edit.php' , array( __CLASS__ , 'load_user_edit' ) , 1000 );

  }

  public static function load_user_edit() {

    add_action( 'admin_print_styles' , array( __CLASS__ , 'hide_items' ) );

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

    if( !empty( $setting_data['hide_rich_editing'] ) ) {

      echo 'body.wp-admin .user-rich-editing-wrap { display: none; }';

    }

    if( !empty( $setting_data['hide_admin_color'] ) ) {

      echo 'body.wp-admin .user-admin-color-wrap { display: none; }';

    }

    if( !empty( $setting_data['hide_comment_shortcuts'] ) ) {

      echo 'body.wp-admin .user-comment-shortcuts-wrap { display: none; }';

    }

    if( !empty( $setting_data['hide_toolbar'] ) ) {

      echo 'body.wp-admin .user-admin-bar-front-wrap { display: none; }';

    }

    if( !empty( $setting_data['hide_language'] ) ) {

      echo 'body.wp-admin .user-language-wrap { display: none; }';

    }

    if( !empty( $setting_data['hide_url'] ) ) {

      echo 'body.wp-admin .user-url-wrap { display: none; }';

    }

    if( !empty( $setting_data['hide_description'] ) ) {

      echo 'body.wp-admin .user-description-wrap { display: none; }';

    }

    if( !empty( $setting_data['hide_picture'] ) ) {

      echo 'body.wp-admin .user-profile-picture { display: none; }';

    }

    if( !empty( $setting_data['hide_session'] ) ) {

      echo 'body.wp-admin .user-sessions-wrap { display: none; }';

    }

    if( !empty( $setting_data['hide_contact_fields'] ) ) {

      foreach( $setting_data['hide_contact_fields'] as $field_name => $v ) {

        $field_name = strip_tags( $field_name );

        echo "body.wp-admin .user-{$field_name}-wrap { display: none; }";

      }

    }

    echo '</style>';

    self::after_do_function( __FUNCTION__ );

  }

}

MywpControllerModuleAdminUserEdit::init();

endif;
