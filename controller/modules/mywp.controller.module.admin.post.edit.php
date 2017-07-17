<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( ! class_exists( 'MywpControllerAbstractModule' ) ) {
  return false;
}

if ( ! class_exists( 'MywpControllerModuleAdminPostEdit' ) ) :

final class MywpControllerModuleAdminPostEdit extends MywpControllerAbstractModule {

  static protected $id = 'admin_post_edit';

  static private $post_type = '';

  public static function mywp_controller_initial_data( $initial_data ) {

    $initial_data['meta_boxes'] = array();

    $initial_data['hide_add_new'] = '';
    $initial_data['hide_title'] = '';
    $initial_data['hide_permalink'] = '';
    $initial_data['hide_change_permalink'] = '';
    $initial_data['hide_content'] = '';
    $initial_data['prevent_meta_box'] = '';

    return $initial_data;

  }

  public static function mywp_controller_default_data( $default_data ) {

    $default_data['meta_boxes'] = array();

    $initial_data['hide_add_new'] = false;
    $default_data['hide_title'] = false;
    $default_data['hide_permalink'] = false;
    $default_data['hide_change_permalink'] = false;
    $default_data['hide_content'] = false;
    $default_data['prevent_meta_box'] = false;

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

    add_action( 'load-post.php' , array( __CLASS__ , 'load_post' ) , 1000 );
    add_action( 'load-post-new.php' , array( __CLASS__ , 'load_post' ) , 1000 );

  }

  public static function mywp_get_option_key( $option_key ) {

    if( empty( self::$post_type ) ) {

      return $option_key;

    }

    $option_key .= '_' . self::$post_type;

    return $option_key;

  }

  public static function load_post() {

    global $typenow;

    if( empty( $typenow ) ) {

      return false;

    }

    self::$post_type = $typenow;

    add_filter( 'mywp_get_option_key_mywp_admin_post_edit' , array( __CLASS__ , 'mywp_get_option_key' ) );

    add_action( 'admin_print_styles' , array( __CLASS__ , 'hide_items' ) );

    add_action( 'admin_head' , array( __CLASS__ , 'hide_meta_box' ) );

    add_action( 'in_admin_header' , array( __CLASS__ , 'remove_meta_box' ) );

    add_action( 'in_admin_header' , array( __CLASS__ , 'change_title_meta_box' ) );

    add_action( 'admin_print_footer_scripts' , array( __CLASS__ , 'prevent_meta_box' ) );

  }

  public static function hide_items() {

    if( ! self::is_do_function( __FUNCTION__ ) ) {

      return false;

    }

    $setting_data = self::get_setting_data();

    echo '<style>';

    if( ! empty( $setting_data['hide_add_new'] ) ) {

      echo '.wrap h1 > a { display: none; }';

    }

    if( ! empty( $setting_data['hide_title'] ) ) {

      echo '#post-body-content #titlewrap { display: none; }';

    }

    if( ! empty( $setting_data['hide_permalink'] ) ) {

      echo '#post-body-content #titlediv .inside { display: none; }';

    }

    if( ! empty( $setting_data['hide_change_permalink'] ) ) {

      echo '#post-body-content #change-permalinks { display: none; }';

    }

    if( ! empty( $setting_data['hide_content'] ) ) {

      echo '#post-body-content #postdivrich { display: none; }';

    }

    echo '</style>';

    self::after_do_function( __FUNCTION__ );

  }

  public static function hide_meta_box() {

    if( ! self::is_do_function( __FUNCTION__ ) ) {

      return false;

    }

    $setting_data = self::get_setting_data();

    if( empty( $setting_data['meta_boxes'] ) ) {

      return false;

    }

    $hide_meta_boxes = array();

    foreach( $setting_data['meta_boxes'] as $meta_box_id => $meta_box_setting ) {

      if( $meta_box_setting['action'] != 'hide' ) {

        continue;

      }

      $hide_meta_boxes[] = $meta_box_id;

    }

    if( empty( $hide_meta_boxes ) ) {

      return false;

    }

    echo '<style>';

    foreach( $hide_meta_boxes as $meta_box_id ) {

      printf( '.postbox#%s { height: 0; overflow: hidden; margin: 0; box-shadow: none; border: 0 none; }' , $meta_box_id );

    }

    echo '</style>';

    echo '<script>jQuery(document).ready(function($){';

    foreach( $hide_meta_boxes as $meta_box_id ) {

      printf( '$("#screen-options-wrap .metabox-prefs label[for=%s-hide]").css("display", "none");' , $meta_box_id );

    }

    echo '});</script>';

    self::after_do_function( __FUNCTION__ );

  }

  public static function remove_meta_box() {

    global $wp_meta_boxes;

    if( ! self::is_do_function( __FUNCTION__ ) ) {

      return false;

    }

    $setting_data = self::get_setting_data();

    if( empty( $setting_data['meta_boxes'] ) ) {

      return false;

    }

    $remove_meta_boxes = array();

    foreach( $setting_data['meta_boxes'] as $meta_box_id => $meta_box_setting ) {

      if( $meta_box_setting['action'] != 'remove' ) {

        continue;

      }

      $remove_meta_boxes[] = $meta_box_id;

    }

    if( empty( $remove_meta_boxes ) ) {

      return false;

    }

    if( empty( $wp_meta_boxes[ self::$post_type ] ) ) {

      return false;

    }

    $current_meta_boxes = $wp_meta_boxes[ self::$post_type ];

    foreach( $current_meta_boxes as $context => $priority_meta_boxes ) {

      if( empty( $priority_meta_boxes ) or ! is_array( $priority_meta_boxes ) ) {

        continue;

      }

      foreach( $priority_meta_boxes as $priority => $meta_boxes ) {

        if( empty( $meta_boxes ) or ! is_array( $meta_boxes ) ) {

          continue;

        }

        foreach( $meta_boxes as $meta_box_id => $meta_box ) {

          if( empty( $meta_box ) or ! is_array( $meta_box ) ) {

            continue;

          }

          if( ! in_array( $meta_box_id , $remove_meta_boxes ) ) {

            continue;

          }

          remove_meta_box( $meta_box_id , self::$post_type , $context );

        }

      }

    }

    self::after_do_function( __FUNCTION__ );

  }

  public static function change_title_meta_box() {

    global $wp_meta_boxes;

    if( ! self::is_do_function( __FUNCTION__ ) ) {

      return false;

    }

    $setting_data = self::get_setting_data();

    if( empty( $setting_data['meta_boxes'] ) ) {

      return false;

    }

    $change_title_meta_boxes = array();

    foreach( $setting_data['meta_boxes'] as $meta_box_id => $meta_box_setting ) {

      if( !empty( $meta_box_setting['action'] ) ) {

        continue;

      }

      if( empty( $meta_box_setting['title'] ) ) {

        continue;

      }

      $change_title_meta_boxes[ $meta_box_id ] = $meta_box_setting['title'];

    }

    if( empty( $change_title_meta_boxes ) ) {

      return false;

    }

    if( empty( $wp_meta_boxes[ self::$post_type ] ) ) {

      return false;

    }

    $current_meta_boxes = $wp_meta_boxes[ self::$post_type ];

    foreach( $current_meta_boxes as $context => $priority_meta_boxes ) {

      if( empty( $priority_meta_boxes ) or ! is_array( $priority_meta_boxes ) ) {

        continue;

      }

      foreach( $priority_meta_boxes as $priority => $meta_boxes ) {

        if( empty( $meta_boxes ) or ! is_array( $meta_boxes ) ) {

          continue;

        }

        foreach( $meta_boxes as $meta_box_id => $meta_box ) {

          if( empty( $meta_box ) or ! is_array( $meta_box ) ) {

            continue;

          }

          if( empty( $change_title_meta_boxes[ $meta_box_id ] ) ) {

            continue;

          }

          $wp_meta_boxes[ self::$post_type ][ $context ][ $priority ][ $meta_box_id ]['title'] = do_shortcode( $change_title_meta_boxes[ $meta_box_id ] );

        }

      }

    }

    self::after_do_function( __FUNCTION__ );

  }

  public static function prevent_meta_box() {

    if( ! self::is_do_function( __FUNCTION__ ) ) {

      return false;

    }

    $setting_data = self::get_setting_data();

    if( ! empty( $setting_data['prevent_meta_box'] ) ) {

      echo '<script>jQuery(document).ready(function($){';

      echo '$(".meta-box-sortables").sortable("disable");';

      echo '});</script>';

      echo '<style>';

      echo '.js .postbox .hndle { cursor: pointer; }';

      echo '</style>';

    }

    self::after_do_function( __FUNCTION__ );

  }

}

MywpControllerModuleAdminPostEdit::init();

endif;
