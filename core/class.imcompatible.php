<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'MywpIncompatible' ) ) :

final class MywpIncompatible {

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

    add_action( 'admin_notices' , array( __CLASS__ , 'admin_notices' ) );

  }

  public static function admin_notices() {

    if( ! MywpApi::is_manager() ) {

      return false;

    }

    echo '<div class="error">';

    echo '<p>';

    printf( __( 'Sorry, My WP is <strong>Incompatible</strong> with your version of WordPress. Require version  %s.' , 'mywp' ) , MYWP_REQUIRED_WP_VERSION );

    echo '</p>';

    echo '</div>';

  }

}

endif;
