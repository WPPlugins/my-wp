<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'MywpUser' ) ) :

final class MywpUser {

  private $user_id = false;
  private $user_data = false;
  private $user_role = false;
  private $user_roles = array();
  private $name = false;
  private $user_login = false;
  private $avatar_tag = false;

  public function __construct( $user_id = false ) {

    $this->set_user_id( $user_id );

  }

  private function set_user_id( $user_id = false ) {

    if( $this->user_id !== false ) {

      return false;

    }

    if( $user_id === false ) {

      $user_id = get_current_user_id();

    } else {

      $user_id = intval( $user_id );

    }

    if( empty( $user_id ) ) {

      $called_text = sprintf( '(object) %s->%s( %s )' , __CLASS__ , __FUNCTION__ , '$user_id' );

      MywpHelper::error_not_found_message( '$user_id' , $called_text );

      return false;

    }

    $this->user_id = $user_id;

  }

  public function get_user_id( $user_id =  false ) {

    return $this->user_id;

  }

  public function get_user_data() {

    if( ! empty( $this->user_data ) ) {

      return $this->user_data;

    }

    if( empty( $this->user_id ) ) {

      $called_text = sprintf( '(object) %s->%s()' , __CLASS__ , __FUNCTION__ );

      MywpHelper::error_not_found_message( 'user_id' , $called_text );

      return false;

    }

    $this->user_data = get_userdata( $this->user_id );

    return $this->user_data;

  }

  public function get_user_role() {

    if( ! empty( $this->user_role ) ) {

      return $this->user_role;

    }

    $user_roles = $this->get_user_roles();

    if( empty( $user_roles ) ) {

      return false;

    }

    $this->user_role = array_shift( $user_roles );

    return $this->user_role;

  }

  public function get_user_roles() {

    if( ! empty( $this->user_roles ) ) {

      return $this->user_roles;

    }

    $user_data = $this->get_user_data();

    if( empty( $user_data ) or empty( $user_data->roles ) ) {

      return false;

    }

    $this->user_roles = $user_data->roles;

    return $this->user_roles;

  }

  public function get_name() {

    if( ! empty( $this->name ) ) {

      return $this->name;

    }

    $user_data = $this->get_user_data();

    if( empty( $user_data ) or empty( $user_data->display_name ) ) {

      return false;

    }

    $this->name = $user_data->display_name;

    return $this->name;

  }

  public function get_user_login() {

    if( ! empty( $this->user_login ) ) {

      return $this->user_login;

    }

    $user_data = $this->get_user_data();

    if( empty( $user_data ) or empty( $user_data->user_login ) ) {

      $called_text = sprintf( '(object) %s->%s()' , __CLASS__ , __FUNCTION__ );

      MywpHelper::error_not_found_message( 'user_login' , $called_text );

      return false;

    }

    $this->user_login = $user_data->user_login;

    return $this->user_login;

  }

  public function get_avatar_tag( $size = false ) {

    if( $size === false ) {

      $size = 64;

    } else {

      $size = intval( $size );

    }

    $called_text = sprintf( '(object) %s->%s( %s )' , __CLASS__ , __FUNCTION__ , '$size' );

    if( empty( $size ) ) {

      MywpHelper::error_not_found_message( '$size' , $called_text );

      return false;

    }

    if( empty( $this->user_id ) ) {

      MywpHelper::error_not_found_message( 'user_id' , $called_text );

      return false;

    }

    $this->avatar_tag = get_avatar( $this->user_id , $size );

    return $this->avatar_tag;

  }

  public function get_posts( $args = array() ) {

    if( empty( $this->user_id ) ) {

      $called_text = sprintf( '(object) %s->%s( $args )' , __CLASS__ , __FUNCTION__ , '$args' );

      MywpHelper::error_not_found_message( 'user_id' , $called_text );

      return false;

    }

    $default = array(
      'author' => $this->user_id,
    );

    $args = wp_parse_args( $args , $default );

    return get_posts( $args );

  }

}

endif;
