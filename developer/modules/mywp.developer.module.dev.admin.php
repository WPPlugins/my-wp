<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( ! class_exists( 'MywpDeveloperAbstractModule' ) ) {
  return false;
}

if ( ! class_exists( 'MywpDeveloperModuleDevAdmin' ) ) :

final class MywpDeveloperModuleDevAdmin extends MywpDeveloperAbstractModule {

  static protected $id = 'dev_admin';

  static protected $priority = 25;

  public static function mywp_debug_renders( $debug_renders ) {

    if( ! is_admin() ) {

      return $debug_renders;

    }

    $debug_renders[ self::$id ] = array(
      'debug_type' => 'dev',
      'title' => __( 'Current Admin Information' , 'mywp'),
    );

    return $debug_renders;

  }

  protected static function get_debug_lists() {

    global $pagenow, $hook_suffix, $plugin_page, $page_hook, $typenow, $taxnow;
    global $post;
    global $user_id;

    $debug_lists = array(
      '$pagenow' => $pagenow,
      '$hook_suffix' => $hook_suffix,
      '$plugin_page' => $plugin_page,
      '$page_hook' => $page_hook,
      '$typenow' => $typenow,
      '$taxnow' => $taxnow,
    );

    if( in_array( $pagenow , array( 'post.php' , 'post-new.php' ) ) ) {

      $debug_lists['post_id'] = intval( $post->ID );
      $debug_lists['post'] = $post;
      $debug_lists['custom_fields'] = get_post_meta( $post->ID );

    } elseif( in_array( $pagenow , array( 'edit.php' ) ) ) {

      $debug_lists['post_type'] = $typenow;
      $debug_lists['counts'] = wp_count_posts( $typenow );

    } elseif( in_array( $pagenow , array( 'edit-tags.php' ) ) ) {

      $debug_lists['taxonomy'] = $taxnow;
      $debug_lists['count'] = wp_count_terms( $taxnow );

    } elseif( in_array( $pagenow , array( 'term.php' ) ) ) {

      $debug_lists['taxonomy'] = $taxnow;
      $debug_lists['post_type'] = $typenow;

      $term_id = absint( $_REQUEST['tag_ID'] );
      $deaub_lists['term_id'] = $term_id;
      $debug_lists['term'] = get_term( $term_id );
      $debug_lists['custom_meta'] = get_term_meta( $term_id );

    } elseif( in_array( $pagenow , array( 'users.php' ) ) ) {

      $debug_lists['count'] = count_users();

    } elseif( in_array( $pagenow , array( 'user-edit.php' , 'profile.php' ) ) ) {

      $debug_lists['user_id'] = $user_id;
      $debug_lists['get_userdata()'] = get_userdata( $user_id );

    }

    return $debug_lists;

  }

  protected static function mywp_developer_debug() {

    if( ! is_admin() ) {

      return false;

    }

    parent::mywp_developer_debug();

  }

  protected static function mywp_debug_render() {

    if( ! is_admin() ) {

      return false;

    }

    parent::mywp_debug_render();

  }

}

MywpDeveloperModuleDevAdmin::init();

endif;
