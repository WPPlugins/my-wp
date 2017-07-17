<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( ! class_exists( 'MywpShortcodeAbstractModule' ) ) {
  return false;
}

if ( ! class_exists( 'MywpShortcodeModuleUrl' ) ) :

final class MywpShortcodeModuleUrl extends MywpShortcodeAbstractModule {

  protected static $id = 'mywp_url';

  public static function do_shortcode( $atts , $content = false , $tag ) {

    if( empty( $atts['blog_id'] ) ) {

      $blog_id = get_current_blog_id();

    } else {

      $blog_id = intval( $atts['blog_id'] );

    }

    if( get_current_blog_id() == $blog_id ) {

      $blog_id = false;

    }

    if( !empty( $atts['admin'] ) ) {

      $url = get_admin_url( $blog_id );

    } elseif( !empty( $atts['site'] ) ) {

      $url = get_site_url( $blog_id );

    } elseif( !empty( $atts['login'] ) ) {

      $url = wp_login_url();

    } elseif( !empty( $atts['logout'] ) ) {

      $url = wp_logout_url();

    } elseif( !empty( $atts['lost_password'] ) ) {

      $url = wp_lostpassword_url();

    } elseif( !empty( $atts['current'] ) ) {

      if( is_ssl() ) {

        $scheme = 'https';

      } else {

        $scheme = 'http';

      }

      $url = $scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    } else {

      $url = get_home_url( $blog_id );

    }

    if( !empty( $atts['esc_url'] ) ) {

      $url = esc_url( $url );

    }

    $content = apply_filters( 'mywp_shortcode_url' , $url , $atts );

    return $content;

  }

}

MywpShortcodeModuleUrl::init();

endif;
