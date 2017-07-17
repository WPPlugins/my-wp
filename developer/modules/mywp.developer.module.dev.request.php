<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( ! class_exists( 'MywpDeveloperAbstractModule' ) ) {
  return false;
}

if ( ! class_exists( 'MywpDeveloperModuleDevRequest' ) ) :

final class MywpDeveloperModuleDevRequest extends MywpDeveloperAbstractModule {

  static protected $id = 'dev_request';

  static protected $priority = 70;

  public static function mywp_debug_renders( $debug_renders ) {

    $debug_renders[ self::$id ] = array(
      'debug_type' => 'dev',
      'title' => __( 'Request' , 'mywp'),
    );

    return $debug_renders;

  }

  protected static function get_debug_lists() {

    $debug_lists = array(
      'DOING_AJAX' => defined( 'DOING_AJAX' ),
      'DOING_CRON' => defined( 'DOING_CRON' ),
      'XMLRPC_REQUEST' => defined( 'XMLRPC_REQUEST' ),
      'is_ssl()' => is_ssl(),
      'is_admin()' => is_admin(),
      'is_network_admin()' => is_network_admin(),
      '$_POST' => $_POST,
      '$_GET' => $_GET,
    );

    return $debug_lists;

  }

  protected static function mywp_debug_render() {

    $debug_lists = self::get_debug_lists();

    if( empty( $debug_lists ) ) {

      return false;

    }

    echo '<table class="debug-table">';

    foreach( $debug_lists as $key => $val ) {

      echo '<tr>';

      printf( '<th>%s</th>' , $key );

      echo '<td>';

      if( in_array( $key , array( '$_POST' , '$_GET' ) ) ) {

        printf( '<textarea readonly="readonly">%s</textarea>' , print_r( MywpHelper::deep_esc_html( $val ) , true ) );

      } else {

        echo $val;

      }

      echo '</td>';

      echo '</tr>';

    }

    echo '</table>';

  }

}

MywpDeveloperModuleDevRequest::init();

endif;
