<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'MywpTaxonomyAbstractModule' ) ) :

abstract class MywpTaxonomyAbstractModule {

  private static $instance;

  protected static $id;

  private function __construct() {}

  public static function get_instance() {

    $class = get_called_class();

    if ( !isset( self::$instance[ $class ] ) ) {

      self::$instance[ $class ] = new static();

    }

    return self::$instance[ $class ];

  }

  private function __clone() {}

  private function __wakeup() {}

  public static function init() {

    $class = get_called_class();

    if( empty( static::$id ) ) {

      $called_text = sprintf( 'class %s' , $class );

      MywpHelper::error_require_message( '"static protected $id"' , $called_text );

      return false;

    }

    add_filter( 'mywp_taxonomy_types' , array( $class , 'mywp_taxonomy_types' ) );

    add_filter( "mywp_taxonomy_get_taxonomy_{$class::$id}" , array( $class , 'current_mywp_taxonomy_get_taxonomy' ) );

  }

  public static function mywp_taxonomy_types( $taxonomy_types ) {

    $class = get_called_class();

    $taxonomy_types[ static::$id ] = static::get_regist_taxonomy_type_args();

    return $taxonomy_types;

  }

  protected static function get_regist_taxonomy_type_args() {

    return array();

  }

  public static function current_mywp_taxonomy_get_taxonomy( $taxonomy ) {

    return $taxonomy;

  }

}

endif;
