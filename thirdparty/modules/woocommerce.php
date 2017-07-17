<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( ! class_exists( 'MywpThirdpartyAbstractModule' ) ) {
  return false;
}

if ( ! class_exists( 'MywpThirdpartyModuleWoocommerce' ) ) :

final class MywpThirdpartyModuleWoocommerce extends MywpThirdpartyAbstractModule {

  protected static $id = 'woocommerce';

  protected static $thirdparty_base_name = 'woocommerce/woocommerce.php';

  protected static $thirdparty_name = 'WooCommerce';

  protected static function after_init() {

    add_filter( 'mywp_shortcode' , array( __CLASS__ , 'mywp_shortcode' ) );

    add_filter( 'mywp_nav_menu_meta_boxes_items' , array( __CLASS__ , 'mywp_nav_menu_meta_boxes_items' ) );

    add_filter( 'mywp_setting_get_latest_post_args_shop_order' , array( __CLASS__ , 'mywp_setting_get_latest_post_args_shop_order' ) );

    add_filter( 'mywp_setting_admin_sidebar_get_default_sidebar_items' , array( __CLASS__ , 'mywp_setting_admin_sidebar_get_default_sidebar_items' ) , 10 , 2 );

    add_filter( 'mywp_controller_admin_sidebar_get_sidebar_item_added_classes_found_current_item_ids' , array( __CLASS__ , 'mywp_controller_admin_sidebar_get_sidebar_item_added_classes_found_current_item_ids' ) , 10 , 5 );

  }

  public static function current_pre_plugin_activate( $is_plugin_activate ) {

    if( class_exists( 'WooCommerce' ) ) {

      return true;

    }

    return $is_plugin_activate;

  }

  public static function mywp_shortcode( $shortcodes ) {

    $class = get_called_class();

    $shortcodes['mywp_woocommerce_order_count'] = array( __CLASS__ , 'do_shortcode_order_count' );

    return $shortcodes;

  }

  public static function do_shortcode_order_count( $atts , $content = false , $tag ) {

    if( ! current_user_can( 'manage_woocommerce' ) ) {

      return false;

    }

    $status = 'processing';

    if( !empty( $atts['status'] ) ) {

      $status = strip_tags( $atts['status'] );

    }

    $count = wc_orders_count( $status );

    if( empty( $count ) ) {

      return $content;

    }

    if( !empty( $atts['tag'] ) ) {

      $content = sprintf(
        '<span class="update-plugins count-%d"><span class="%s-count">%d</span></span>',
        $count,
        $status,
        number_format_i18n( $count )
      );

    } else {

      $content = $count;

    }

    return $content;

  }

  public static function mywp_nav_menu_meta_boxes_items( $menu_meta_boxes_items ) {

    if( ! self::is_current_plugin_activate() ) {

      return $menu_meta_boxes_items;

    }

    $menu_meta_boxes_items[] = array( 'id' => 'woocommerce_endpoints_nav_link' , 'title' => __( 'WooCommerce Endpoints', 'woocommerce' ) );

    return $menu_meta_boxes_items;

  }

  public static function mywp_setting_get_latest_post_args_shop_order( $args ) {

    $args['post_status'] = array( 'wc-processing', 'wc-completed' );

    return $args;

  }

  public static function mywp_setting_admin_sidebar_get_default_sidebar_items( $default_sidebar ) {

    if( ! self::is_current_plugin_activate() ) {

      return $default_sidebar;

    }

    if( empty( $default_sidebar ) ) {

      return $default_sidebar;

    }

    if( !empty( $default_sidebar['submenu'][ self::$id ] ) ) {

      foreach( $default_sidebar['submenu'][ self::$id ] as $key => $submenu ) {

        if( $submenu[2] == 'edit.php?post_type=shop_order' ) {

          $default_sidebar['submenu'][ self::$id ][ $key ][0] = sprintf( '%s %s' , _x( 'Orders', 'Admin menu name', 'woocommerce' ) , '[mywp_woocommerce_order_count status="processing" tag="1"]' );

          break;

        }

      }

    }

    return $default_sidebar;

  }

  public static function mywp_controller_admin_sidebar_get_sidebar_item_added_classes_found_current_item_ids( $found_current_item_ids , $sidebar_items , $current_url , $current_url_parse , $current_url_query ) {

    if( ! self::is_current_plugin_activate() ) {

      return $found_current_item_ids;

    }

    if( !empty( $found_current_item_ids ) ) {

      return $found_current_item_ids;

    }

    if( empty( $current_url_query['post_type'] ) or empty( $current_url_query['taxonomy'] ) or $current_url_query['post_type'] != 'product' ) {

      return $found_current_item_ids;

    }

    if(
      strpos( $current_url_parse['path'] , 'edit-tags.php' ) === false &&
      strpos( $current_url_parse['path'] , 'term.php' ) === false
    ) {

      return $found_current_item_ids;

    }

    foreach( $sidebar_items as $key => $sidebar_item ) {

      if( ! is_object( $sidebar_item ) ) {

        continue;

      }

      if( empty( $sidebar_item->item_link_url_parse['host'] ) or empty( $sidebar_item->item_link_url_parse['path'] ) ) {

        continue;

      }

      if(
        $current_url_parse['scheme'] !== $sidebar_item->item_link_url_parse['scheme'] or
        $current_url_parse['host'] !== $sidebar_item->item_link_url_parse['host']
      ) {

        continue;

      }

      if( empty( $sidebar_item->item_link_url_parse_query['post_type'] ) or $sidebar_item->item_link_url_parse_query['post_type'] !== 'product' ) {

        continue;

      }

      if( empty( $sidebar_item->item_link_url_parse_query['page'] ) or $sidebar_item->item_link_url_parse_query['page'] !== 'product_attributes' ) {

        continue;

      }

      $found_current_item_ids[] = $sidebar_item->ID;

    }


    return $found_current_item_ids;

  }

}

MywpThirdpartyModuleWoocommerce::init();

endif;
