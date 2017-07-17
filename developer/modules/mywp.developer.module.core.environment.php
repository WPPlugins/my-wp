<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( ! class_exists( 'MywpDeveloperAbstractModule' ) ) {
  return false;
}

if ( ! class_exists( 'MywpDeveloperModuleCoreEnvironment' ) ) :

final class MywpDeveloperModuleCoreEnvironment extends MywpDeveloperAbstractModule {

  static protected $id = 'core_environment';

  static protected $priority = 60;

  public static function mywp_debug_renders( $debug_renders ) {

    $debug_renders[ self::$id ] = array(
      'debug_type' => 'core',
      'title' => __( 'Environment' , 'mywp'),
    );

    return $debug_renders;

  }

  protected static function get_debug_lists() {

    global $wp_version;

    $debug_lists = array();

    $defines = array(
      'WP_DEBUG',
      'WP_DEBUG_LOG',
      'SCRIPT_DEBUG',
      'SAVEQUERIES',
      'WP_HOME',
      'WP_SITEURL',
      'WP_POST_REVISIONS',
    );

    foreach( $defines as $define ) {

      $debug_lists[ $define ] = false;

      if( defined( $define ) ) {

        $debug_lists[ $define ] = constant( $define );

      }

    }

    $debug_lists['$wp_version'] = $wp_version;
    $debug_lists['is_multisite()'] = is_multisite();
    $debug_lists['PHP_VERSION'] = PHP_VERSION;
    $debug_lists['max_input_vars'] = ini_get( 'max_input_vars' );

    return $debug_lists;

  }

}

MywpDeveloperModuleCoreEnvironment::init();

endif;
