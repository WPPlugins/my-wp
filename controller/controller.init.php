<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'MywpControllerInit' ) ) :

final class MywpControllerInit {

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

    add_action( 'mywp_request_admin' , array( __CLASS__ , 'controller_cache' ) );
    add_action( 'mywp_request_frontend' , array( __CLASS__ , 'controller_cache' ) );

    add_filter( 'mywp_debug_renders' , array( __CLASS__ , 'mywp_debug_renders' ) , 100 );

    add_action( 'mywp_debug_render_controller' , array( __CLASS__ , 'mywp_debug_render_controller' ) );

  }

  public static function plugins_loaded_include_modules() {

    $dir = MYWP_PLUGIN_PATH . 'controller/modules/';

    $includes = array(
      'admin_dashboard'           => $dir . 'mywp.controller.module.admin.dashboard.php',
      'admin_general'             => $dir . 'mywp.controller.module.admin.general.php',
      'admin_nav_menu'            => $dir . 'mywp.controller.module.admin.nav-menu.php',
      'admin_post_edit'           => $dir . 'mywp.controller.module.admin.post.edit.php',
      'admin_posts'               => $dir . 'mywp.controller.module.admin.posts.php',
      'admin_regist_metaboxes'    => $dir . 'mywp.controller.module.admin.regist.metaboxes.php',
      'admin_sidebar'             => $dir . 'mywp.controller.module.admin.sidebar.php',
      'admin_user_edit'           => $dir . 'mywp.controller.module.admin.user-edit.php',
      'debug_general'             => $dir . 'mywp.controller.module.debug.general.php',
      'frontend_author_archive'   => $dir . 'mywp.controller.module.frontend.author-archive.php',
      'frontend_date_archive'     => $dir . 'mywp.controller.module.frontend.date-archive.php',
      'frontend_taxonomy_archive' => $dir . 'mywp.controller.module.frontend.taxonomy-archive.php',
      'frontend_general'          => $dir . 'mywp.controller.module.frontend.general.php',
      'main_general'              => $dir . 'mywp.controller.module.main.general.php',
      'site_general'              => $dir . 'mywp.controller.module.site.general.php',
    );

    $includes = apply_filters( 'mywp_controller_plugins_loaded_include_modules' , $includes );

    MywpApi::require_files( $includes );

  }

  public static function after_setup_theme_include_modules() {

    $includes = array();

    $includes = apply_filters( 'mywp_controller_after_setup_theme_include_modules' , $includes );

    MywpApi::require_files( $includes );

  }

  public static function controller_cache() {

    MywpController::set_controllers();

  }

  public static function mywp_debug_renders( $debug_renders ) {

    $debug_renders['controller'] = array(
      'debug_type' => 'mywp',
      'title' => __( 'Controller' , 'mywp' ),
    );

    return $debug_renders;

  }

  public static function mywp_debug_render_controller() {

    echo '<ul>';

    $controllers = MywpController::get_controllers();

    if( !empty( $controllers ) ) {

      foreach( $controllers as $controller_id => $controller ) {

        if( !empty( $controller['model'] ) && is_object( $controller['model'] ) ) {

          $controller['model']->get_data();

        }

        printf( '<li>%s <textarea readonly="readonly">%s</textarea></li>' , $controller_id , print_r( $controller , true ) );

      }

    }

    echo '</ul>';

  }

}

endif;
