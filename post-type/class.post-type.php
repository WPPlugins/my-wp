<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if ( ! class_exists( 'MywpPostType' ) ) :

final class MywpPostType {

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

  public static function get_post_types() {

    $pre_post_types = apply_filters( 'mywp_post_types' , array() );

    if( empty( $pre_post_types ) ) {

      return false;

    }

    $default = array(
      'public' => false,
      'hierarchical' => false,
      'exclude_from_search' => true,
      'show_ui' => false,
      'query_var' => false,
      'delete_with_user' => false,
      'rewrite' => false,
      'supports' => false,
    );

    $post_types = array();

    $is_debug = MywpDeveloper::is_debug();

    if( $is_debug ) {

      $default['show_ui'] = true;
      $default['delete_with_user'] = true;

    }

    foreach( $pre_post_types as $post_type_name => $args ) {

      if( empty( $args['labels'] ) ) {

        if( !empty( $args['label'] ) ) {

          $label = $args['label'];

          $labels_args = array(
            'name' => $label,
            'singular_name' => $label,
          );

          $default['labels'] = $labels_args;

        }

      }

      $post_types[ $post_type_name ] = wp_parse_args( $args , $default );

    }

    return $post_types;

  }

  public static function get_posts( $args = array() ) {

    $posts = get_posts( $args );

    if( empty( $posts ) ) {

      return false;

    }

    foreach( $posts as $key => $post ) {

      $post = apply_filters( 'mywp_post_type_get_post' , $post , $post->post_type );
      $posts[ $key ] = apply_filters( "mywp_post_type_get_post_{$post->post_type}" , $post );

    }

    return $posts;

  }

  public static function get_post( $post_id = false ) {

    $mywp_cache = new MywpCache( "MywpPostType_get_post_{$post_id}" );

    $cache = $mywp_cache->get_cache();

    if( !empty( $cache ) ) {

      return $cache;

    }

    $post = get_post( $post_id );

    if( empty( $post ) ) {

      return false;

    }

    $post = apply_filters( 'mywp_post_type_get_post' , $post , $post->post_type , $post_id );
    $post = apply_filters( "mywp_post_type_get_post_{$post->post_type}" , $post , $post_id );

    $mywp_cache->update_cache( $post );

    return $post;

  }

  public static function get_post_custom( $post_id = false ) {

    if( empty( $post_id ) ) {

      return false;

    }

    $post_id = intval( $post_id );

    $mywp_cache = new MywpCache( "MywpPostType_get_post_custom_{$post_id}" );

    $cache = $mywp_cache->get_cache();

    if( !empty( $cache ) ) {

      return $cache;

    }

    $all_custom_fields = get_post_custom( $post_id );

    $all_custom_fields = apply_filters( 'mywp_post_type_get_post_custom' , $all_custom_fields , $post_id );
    $all_custom_fields = apply_filters( "mywp_post_type_get_post_custom_{$post_id}" , $all_custom_fields );

    $mywp_cache->update_cache( $all_custom_fields );

    return $all_custom_fields;

  }

  public static function get_post_meta( $post_id = false , $find_meta_key = false ) {

    if( empty( $post_id ) or empty( $find_meta_key ) ) {

      return false;

    }

    $post_id = intval( $post_id );

    $find_meta_key = strip_tags( $find_meta_key );

    $mywp_cache = new MywpCache( "MywpPostType_get_post_meta_{$post_id}_{$find_meta_key}" );

    $cache = $mywp_cache->get_cache();

    if( !empty( $cache ) ) {

      return $cache;

    }

    $all_custom_fields = self::get_post_custom( $post_id );

    if( empty( $all_custom_fields ) or ! is_array( $all_custom_fields ) ) {

      return false;

    }

    $post_meta = false;

    foreach( $all_custom_fields as $custom_meta_key => $custom_meta_value ) {

      if( $find_meta_key != $custom_meta_key ) {

        continue;

      }

      $post_meta = $custom_meta_value[0];

      break;

    }

    $post_meta = apply_filters( 'mywp_post_type_get_post_meta' , $post_meta , $post_id , $find_meta_key );
    $post_meta = apply_filters( "mywp_post_type_get_post_meta_{$find_meta_key}" , $post_meta );

    $mywp_cache->update_cache( $post_meta );

    return $post_meta;

  }


}

endif;
