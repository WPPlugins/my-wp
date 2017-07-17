<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'MywpModel' ) ) :

final class MywpModel {

  private $model_id = false;
  private $controller_id = false;
  private $use_filter = true;
  private $network = false;

  private $initial_data = false;
  private $default_data = false;

  private $option_key = false;

  private $pre_get_option = false;
  private $option = false;
  private $after_get_option = false;

  private $pre_setting_data = false;
  private $setting_data = false;
  private $after_setting_data = false;

  public function __construct( $controller_id = false , $use_filter = true , $network = false ) {

    if( empty( $controller_id ) ) {

      $called_text = sprintf( 'new %s( %s , %s )' , __CLASS__ , '$controller_id' , '$use_filter' );

      MywpHelper::error_require_message( '$controller_id' , $called_text );

      return false;

    }

    $this->controller_id = strip_tags( $controller_id );

    if( empty( $use_filter ) ) {

      $this->use_filter = false;

    }

    if( !empty( $network ) ) {

      $this->network = true;

    }

    $this->model_id = "mywp_{$this->controller_id}";

  }

  public function get_model_id() {

    return $this->model_id;

  }

  public function set_initial_data( $initial_data = false ) {

    $this->initial_data = $initial_data;

  }

  public function get_initial_data() {

    return $this->initial_data;

  }

  public function set_default_data( $default_data = false ) {

    $this->default_data = $default_data;

  }

  public function get_default_data() {

    return $this->default_data;

  }

  public function get_option_key() {

    $model_id = $this->get_model_id();

    if( empty( $model_id ) ) {

      return false;

    }

    $this->option_key = $model_id;

    if( $this->use_filter ) {

      $this->option_key = apply_filters( "mywp_get_option_key_{$model_id}" , $this->option_key );

    }

    return $this->option_key;

  }

  public function get_data() {

    $option_key = $this->get_option_key();

    if( empty( $option_key ) ) {

      $called_text = sprintf( '(object) %s->%s()' , __CLASS__ , __FUNCTION__ );

      MywpHelper::error_not_found_message( '$option_key' , $called_text );

      return false;

    }

    if( $this->use_filter ) {

      $this->pre_get_option = apply_filters( "mywp_pre_get_data_{$option_key}" , false );

      if( $this->pre_get_option !== false ) {

        return $this->pre_get_option;

      }

    }

    if( $this->network ) {

      $this->option = get_site_option( $option_key );

    } else {

      $this->option = get_option( $option_key );

    }

    if( $this->use_filter ) {

      $this->after_get_option = apply_filters( "mywp_after_get_data_{$option_key}" , $this->option );

      if( $this->after_get_option !== false ) {

        return $this->after_get_option;

      }

    }

    return $this->option;

  }

  public function update_data( $update_data ) {

    $option_key = $this->get_option_key();

    if( empty( $option_key ) ) {

      $called_text = sprintf( '(object) %s->%s()' , __CLASS__ , __FUNCTION__ );

      MywpHelper::error_not_found_message( '$option_key' , $called_text );

      return false;

    }

    if( $this->network ) {

      $return = update_site_option( $option_key , $update_data );

    } else {

      $return = update_option( $option_key , $update_data );

    }

    do_action( "mywp_model_update_data_{$option_key}" , $update_data , $return );

    return $return;

  }

  public function remove_data() {

    $option_key = $this->get_option_key();

    if( empty( $option_key ) ) {

      $called_text = sprintf( '(object) %s->%s()' , __CLASS__ , __FUNCTION__ );

      MywpHelper::error_not_found_message( '$option_key' , $called_text );

      return false;

    }

    if( $this->network ) {

      $return = delete_site_option( $option_key );

    } else {

      $return = delete_option( $option_key );

    }

    do_action( "mywp_model_remove_data_{$option_key}" , $return );

    return $return;

  }

  public function get_setting_data() {

    $option_key = $this->get_option_key();

    if( empty( $option_key ) ) {

      $called_text = sprintf( '(object) %s->%s()' , __CLASS__ , __FUNCTION__ );

      MywpHelper::error_not_found_message( '$option_key' , $called_text );

      return false;

    }

    if( $this->use_filter ) {

      $this->pre_setting_data = apply_filters( "mywp_pre_get_setting_data_{$option_key}" , false );

      if( $this->pre_setting_data !== false ) {

        return $this->pre_setting_data;

      }

    }

    $this->setting_data = $this->get_data();

    if( $this->setting_data === false ) {

      $this->setting_data = $this->get_default_data();

    }

    if( $this->use_filter ) {

      $this->after_setting_data = apply_filters( "mywp_after_get_setting_data_{$option_key}" , $this->setting_data );

      if( $this->after_setting_data !== false ) {

        $this->after_setting_data = wp_parse_args( $this->after_setting_data , $this->get_initial_data() );

        return $this->after_setting_data;

      }

    }

    $this->setting_data = wp_parse_args( $this->setting_data , $this->get_initial_data() );

    return $this->setting_data;

  }

}

endif;
