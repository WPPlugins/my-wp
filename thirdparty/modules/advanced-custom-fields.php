<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( ! class_exists( 'MywpThirdpartyAbstractModule' ) ) {
  return false;
}

if ( ! class_exists( 'MywpThirdpartyModuleAdvancedCustomFields' ) ) :

final class MywpThirdpartyModuleAdvancedCustomFields extends MywpThirdpartyAbstractModule {

  protected static $id = 'advanced_custom_fields';

  protected static $thirdparty_base_name = 'advanced-custom-fields/acf.php';

  protected static $thirdparty_name = 'Advanced Custom Fields';

  protected static function after_init() {

    add_filter( 'mywp_setting_post_types' , array( __CLASS__ , 'mywp_setting_post_types' ) );

    add_filter( 'mywp_controller_admin_sidebar_get_sidebar_item_added_classes_found_current_item_ids' , array( __CLASS__ , 'mywp_controller_admin_sidebar_get_sidebar_item_added_classes_found_current_item_ids' ) , 10 , 5 );

  }

  public static function current_pre_plugin_activate( $is_plugin_activate ) {

    if( class_exists( 'acf' ) ) {

      return true;

    }

    return $is_plugin_activate;

  }

  public static function mywp_setting_post_types( $post_types ) {

    if( !empty( $post_types['acf'] ) ) {

      unset( $post_types['acf'] );

    }

    return $post_types;

  }

  public static function mywp_controller_admin_sidebar_get_sidebar_item_added_classes_found_current_item_ids( $found_current_item_ids , $sidebar_items , $current_url , $current_url_parse , $current_url_query ) {

    if( ! self::is_current_plugin_activate() ) {

      return $found_current_item_ids;

    }

    if( !empty( $found_current_item_ids ) ) {

      return $found_current_item_ids;

    }

    if( empty( $current_url_query['post_type'] ) or $current_url_query['post_type'] != 'acf' ) {

      return $found_current_item_ids;

    }

    if(
      strpos( $current_url_parse['path'] , 'post-new.php' ) === false
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

      if( $sidebar_item->item_link_url_parse_query !== array( 'post_type' => 'acf' ) ) {

        continue;

      }

      $found_current_item_ids[] = $sidebar_item->ID;

    }

    return $found_current_item_ids;

  }

}

MywpThirdpartyModuleAdvancedCustomFields::init();

endif;
