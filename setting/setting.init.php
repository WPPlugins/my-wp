<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'MywpSettingInit' ) ) :

final class MywpSettingInit {

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

    add_filter( 'mywp_setting_menus' , array( __CLASS__ , 'add_setting_menu_admin' ) , 20 );
    add_filter( 'mywp_setting_menus' , array( __CLASS__ , 'add_setting_menu_frontend' ) , 30 );
    add_filter( 'mywp_setting_menus' , array( __CLASS__ , 'add_setting_menu_debug' ) , 100 );

    add_action( 'mywp_request_admin_manager' , array( __CLASS__ , 'mywp_request_admin_manager' ) );

    add_filter( 'mywp_debug_types' , array( __CLASS__ , 'add_debug_type' ) , 35 );
    add_filter( 'mywp_debug_renders' , array( __CLASS__ , 'mywp_debug_renders' ) , 110 );

    add_action( 'mywp_debug_render_current_setting' , array( __CLASS__ , 'mywp_debug_render_current_setting' ) );
    add_action( 'mywp_debug_render_settings' , array( __CLASS__ , 'mywp_debug_render_settings' ) );

  }

  public static function plugins_loaded_include_modules() {

    $dir = MYWP_PLUGIN_PATH . 'setting/modules/';

    $includes = array(
      'admin_dashboard'           => $dir . 'mywp.setting.admin.dashboard.php',
      'admin_general'             => $dir . 'mywp.setting.admin.general.php',
      'admin_nav_menu'            => $dir . 'mywp.setting.admin.nav-menu.php',
      'admin_post_edit'           => $dir . 'mywp.setting.admin.post-edit.php',
      'admin_posts'               => $dir . 'mywp.setting.admin.posts.php',
      'admin_sidebar'             => $dir . 'mywp.setting.admin.sidebar.php',
      'admin_user_edit'           => $dir . 'mywp.setting.admin.user-edit.php',
      'debug_defines'             => $dir . 'mywp.setting.debug.defines.php',
      'debug_general'             => $dir . 'mywp.setting.debug.general.php',
      'debug_post_types'          => $dir . 'mywp.setting.debug.post-types.php',
      'debug_taxonomies'          => $dir . 'mywp.setting.debug.taxonomies.php',
      'frontend_author_archive'   => $dir . 'mywp.setting.frontend.author-archive.php',
      'frontend_date_archive'     => $dir . 'mywp.setting.frontend.date-archive.php',
      'frontend_taxonomy_archive' => $dir . 'mywp.setting.frontend.taxonomy-archive.php',
      'frontend_general'          => $dir . 'mywp.setting.frontend.general.php',
      'main_general'              => $dir . 'mywp.setting.main.general.php',
      'site_general'              => $dir . 'mywp.setting.site.general.php',
    );

    $includes = apply_filters( 'mywp_setting_plugins_loaded_include_modules' , $includes );

    MywpApi::require_files( $includes );

  }

  public static function after_setup_theme_include_modules() {

    $includes = array();

    $includes = apply_filters( 'mywp_setting_after_setup_theme_include_modules' , $includes );

    MywpApi::require_files( $includes );

  }

  public static function add_setting_menu_admin( $setting_menus ) {

    $setting_menus['admin'] = array(
      'menu_title' => __( 'Admin' , 'mywp' ),
    );

    return $setting_menus;

  }

  public static function add_setting_menu_frontend( $setting_menus ) {

    $setting_menus['frontend'] = array(
      'menu_title' => __( 'Frontend' , 'mywp' ),
    );

    return $setting_menus;

  }

  public static function add_setting_menu_debug( $setting_menus ) {

    $setting_menus['debug'] = array(
      'menu_title' => __( 'Debug' , 'mywp' ),
    );

    return $setting_menus;

  }

  public static function mywp_request_admin_manager() {

    $setting_menus = MywpSettingMenu::get_setting_menus();

    if( empty( $setting_menus ) ) {

      return false;

    }

    $setting_screens = MywpSettingScreen::get_setting_screens();

    if( empty( $setting_screens ) ) {

      return false;

    }

    self::setting_data_update();

    add_action( 'admin_menu' , array( __CLASS__ , 'admin_menu' ) );

    add_action( 'network_admin_menu' , array( __CLASS__ , 'network_admin_menu' ) );

    add_action( 'admin_init' , array( __CLASS__ , 'admin_init' ) , 20 );

  }

  private static function setting_data_update() {

    if( empty( $_POST ) ) {

      return false;

    }

    if( ! MywpSetting::is_mywp_form_action( $_POST ) ) {

      return false;

    }

    $form = $_POST['mywp'];

    $setting_screen_id = strip_tags( $form['setting_screen'] );

    $action = strip_tags( $form['action'] );

    $nonce_key = MywpSetting::get_nonce_key( $setting_screen_id , $action );

    check_admin_referer( $nonce_key , $nonce_key );

    $mywp_notice = new MywpNotice();

    $formatted_data = MywpSetting::post_data_format( $setting_screen_id , $action , $form );

    $notice = $mywp_notice->get_notice();

    if( ! empty( $notice ) ) {

      return false;

    }

    $validated_data = MywpSetting::post_data_validate( $setting_screen_id , $action , $formatted_data );

    $notice = $mywp_notice->get_notice();

    if( ! empty( $notice ) ) {

      return false;

    }

    $is_redirect = MywpSetting::post_data_action( $setting_screen_id , $action , $validated_data );

    if( $is_redirect ) {

      wp_redirect( esc_url_raw( remove_query_arg( 'updated' , add_query_arg( 'updated' , true ) ) ) );
      exit;

    }

  }

  public static function admin_menu() {

    $setting_menus = MywpSettingMenu::get_setting_menus();

    if( empty( $setting_menus ) ) {

      return false;

    }

    foreach( $setting_menus as $setting_menu_id => $setting_menu ) {

      if( !empty( $setting_menu['network'] ) ) {

        continue;

      }

      MywpSettingMenu::add_menu( $setting_menu_id , $setting_menu );

    }

  }

  public static function network_admin_menu() {

    $setting_menus = MywpSettingMenu::get_setting_menus();

    if( empty( $setting_menus ) ) {

      return false;

    }

    foreach( $setting_menus as $setting_menu_id => $setting_menu ) {

      if( empty( $setting_menu['network'] ) ) {

        continue;

      }

      MywpSettingMenu::add_menu( $setting_menu_id , $setting_menu );

    }

  }

  public static function admin_init() {

    $menu_hook_names = MywpSettingMenu::get_menu_hook_names();

    if( empty( $menu_hook_names ) ) {

      return false;

    }

    foreach( $menu_hook_names as $setting_menu_id => $menu_hook_name ) {

      add_action( "load-{$menu_hook_name}" , array( __CLASS__ , 'load_setting_screen' ) );

    }

  }

  public static function load_setting_screen() {

    self::set_current_setting();

    $current_setting_menu_id = MywpSettingMenu::get_current_menu_id();

    if( empty( $current_setting_menu_id ) ) {

      return false;

    }

    $current_setting_screen_id = MywpSettingScreen::get_current_screen_id();

    if( empty( $current_setting_screen_id ) ) {

      return false;

    }

    add_action( 'admin_enqueue_scripts' , array( __CLASS__ , 'admin_enqueue_scripts' ) );

    add_action( 'admin_print_styles' , array( __CLASS__ , 'admin_print_styles' ) );

    add_action( 'admin_print_scripts' , array( __CLASS__ , 'admin_print_scripts' ) );

    add_action( 'admin_print_footer_scripts' , array( __CLASS__ , 'admin_print_footer_scripts' ) );

    add_action( 'admin_body_class' , array( __CLASS__ , 'admin_body_class' ) );

    do_action( "mywp_load_setting_screen_{$current_setting_menu_id}" );

    do_action( "mywp_load_setting_screen_{$current_setting_screen_id}" );

    do_action( "mywp_load_setting_screen_{$current_setting_screen_id}_{$current_setting_menu_id}" );

    do_action( 'mywp_load_setting_screen' , $current_setting_screen_id , $current_setting_menu_id );

    do_action( 'mywp_after_load_setting_screen' );

  }

  private static function set_current_setting() {

    global $page_hook;

    if( empty( $page_hook ) ) {

      return false;

    }

    MywpSettingMenu::set_current_menu_by_page_hook( $page_hook );

    if( ! empty( $_GET['setting_screen'] ) ) {

      MywpSettingScreen::set_current_screen_id( $_GET['setting_screen'] );

    } else {

      MywpSettingScreen::set_current_screen_by_menu_id( MywpSettingMenu::get_current_menu_id() );

    }

    if( ! empty( $_GET['setting_post_type'] ) ) {

      MywpSettingPostType::set_current_post_type_id( $_GET['setting_post_type'] );

    } else {

      MywpSettingPostType::set_current_post_type_to_default();

    }

    do_action( 'mywp_set_current_setting' );

  }

  public static function admin_enqueue_scripts() {

    $dir_css = MywpApi::get_plugin_url( 'css' );
    $dir_js = MywpApi::get_plugin_url( 'js' );

    wp_register_style( 'mywp_admin_setting' , $dir_css . 'admin-setting.css' , array() , MYWP_VERSION );
    wp_register_script( 'mywp_admin_setting' , $dir_js . 'admin-setting.js' , array( 'jquery' ) , MYWP_VERSION );

    $mywp_admin_setting = array(
      'error_try_again' => sprintf( 'ERROR: %s' , __( 'Please try again.' ) ),
    );

    wp_localize_script( 'mywp_admin_setting' , 'mywp_admin_setting' , $mywp_admin_setting );

    wp_enqueue_style( 'mywp_admin_setting' );
    wp_enqueue_script( 'mywp_admin_setting' );

    $current_setting_menu_id = MywpSettingMenu::get_current_menu_id();
    $current_setting_screen_id = MywpSettingScreen::get_current_screen_id();

    do_action( "mywp_admin_enqueue_scripts_{$current_setting_menu_id}" );

    do_action( "mywp_admin_enqueue_scripts_{$current_setting_screen_id}" );

    do_action( "mywp_admin_enqueue_scripts_{$current_setting_screen_id}_{$current_setting_menu_id}" );

    do_action( 'mywp_admin_enqueue_scripts' , $current_setting_screen_id , $current_setting_menu_id );

  }

  public static function admin_print_styles() {

    $current_setting_menu_id = MywpSettingMenu::get_current_menu_id();
    $current_setting_screen_id = MywpSettingScreen::get_current_screen_id();

    do_action( "mywp_admin_print_styles_{$current_setting_menu_id}" );

    do_action( "mywp_admin_print_styles_{$current_setting_screen_id}" );

    do_action( "mywp_admin_print_styles_{$current_setting_screen_id}_{$current_setting_menu_id}" );

    do_action( 'mywp_admin_print_styles' , $current_setting_screen_id , $current_setting_menu_id );

  }

  public static function admin_print_scripts() {

    $current_setting_menu_id = MywpSettingMenu::get_current_menu_id();
    $current_setting_screen_id = MywpSettingScreen::get_current_screen_id();

    do_action( "mywp_admin_print_scripts_{$current_setting_menu_id}" );

    do_action( "mywp_admin_print_scripts_{$current_setting_screen_id}" );

    do_action( "mywp_admin_print_scripts_{$current_setting_screen_id}_{$current_setting_menu_id}" );

    do_action( 'mywp_admin_print_scripts' , $current_setting_screen_id , $current_setting_menu_id );

  }

  public static function admin_print_footer_scripts() {

    $current_setting_menu_id = MywpSettingMenu::get_current_menu_id();
    $current_setting_screen_id = MywpSettingScreen::get_current_screen_id();

    do_action( "mywp_admin_print_footer_scripts_{$current_setting_menu_id}" );

    do_action( "mywp_admin_print_footer_scripts_{$current_setting_screen_id}" );

    do_action( "mywp_admin_print_footer_scripts_{$current_setting_screen_id}_{$current_setting_menu_id}" );

    do_action( 'mywp_admin_print_footer_scripts' , $current_setting_screen_id , $current_setting_menu_id );

  }

  public static function admin_body_class( $admin_body_class ) {

    $admin_body_class .= ' mywp-setting ';

    $current_setting_menu_id = MywpSettingMenu::get_current_menu_id();
    $current_setting_screen_id = MywpSettingScreen::get_current_screen_id();

    $admin_body_class .= "mywp-{$current_setting_menu_id} mywp-{$current_setting_screen_id}";

    return $admin_body_class;

  }

  public static function add_debug_type( $debug_types ) {

    if( ! MywpApi::is_manager() ) {

      return $debug_types;

    }

    if( is_admin() ) {

      $debug_types['setting'] = __( 'Settings' , 'mywp' );

    }

    return $debug_types;

  }

  public static function mywp_debug_renders( $debug_renders ) {

    $debug_renders['current_setting'] = array(
      'debug_type' => 'setting',
      'title' => __( 'Current Setting' , 'mywp' ),
    );

    $debug_renders['settings'] = array(
      'debug_type' => 'setting',
      'title' => __( 'Settings' , 'mywp' ),
    );

    return $debug_renders;

  }

  public static function mywp_debug_render_current_setting() {

    $current_setting_menu = MywpSettingMenu::get_current_menu();

    if( ! empty( $current_setting_menu ) ) {

      printf( '<p>Current Setting Menu = <textarea readonly="readonly">%s</textarea>' , print_r( $current_setting_menu , true ) );

    }

    $current_setting_screen = MywpSettingScreen::get_current_screen();

    if( ! empty( $current_setting_screen ) ) {

      printf( '<p>Current Setting Screen = <textarea readonly="readonly">%s</textarea>' , print_r( $current_setting_screen , true ) );

    }

    $current_setting_post_type = MywpSettingPostType::get_current_post_type();

    if( ! empty( $current_setting_post_type ) ) {

      printf( '<p>Current Setting Post Type = <textarea readonly="readonly">%s</textarea>' , print_r( $current_setting_post_type , true ) );

    }

    $current_setting_taxonomy = MywpSettingTaxonomy::get_current_taxonomy();

    if( ! empty( $current_setting_taxonomy ) ) {

      printf( '<p>Current Setting Taxonomy = <textarea readonly="readonly">%s</textarea>' , print_r( $current_setting_taxonomy , true ) );

    }

    $current_setting_screens = MywpSettingScreen::get_setting_screens_by_menu_id( MywpSettingMenu::get_current_menu_id() );

    if( ! empty( $current_setting_screens ) ) {

      printf( '<p>Current Setting Screens = <textarea readonly="readonly">%s</textarea></p>' , print_r( $current_setting_screens , true ) );

    }

    do_action( 'mywp_setting_debug_render_current_setting' );

  }

  public static function mywp_debug_render_settings() {

    $setting_menus = MywpSettingMenu::get_setting_menus();

    echo 'Setting Menus';

    if( !empty( $setting_menus ) ) {

      printf( '<textarea readonly="readonly">%s</textarea>' , print_r( $setting_menus , true ) );

    }

    $menu_hook_names = MywpSettingMenu::get_menu_hook_names();

    echo 'Menu Hook Names';

    if( !empty( $menu_hook_names ) ) {

      printf( '<textarea readonly="readonly">%s</textarea>' , print_r( $menu_hook_names , true ) );

    }

    $setting_screens = MywpSettingScreen::get_setting_screens();

    echo 'Setting Screens';

    if( !empty( $setting_screens ) ) {

      printf( '<textarea readonly="readonly">%s</textarea>' , print_r( $setting_screens , true ) );

    }

    $setting_post_types = MywpSettingPostType::get_setting_post_types();

    echo 'Setting Post Types';

    if( !empty( $setting_post_types ) ) {

      printf( '<textarea readonly="readonly">%s</textarea>' , print_r( $setting_post_types , true ) );

    }

    do_action( 'mywp_setting_mywp_debug_render_settings' );

    $setting_taxonomies = MywpSettingTaxonomy::get_setting_taxonomies();

    echo 'Setting Taxonomies';

    if( !empty( $setting_taxonomies ) ) {

      printf( '<textarea readonly="readonly">%s</textarea>' , print_r( $setting_taxonomies , true ) );

    }

  }

}

endif;
