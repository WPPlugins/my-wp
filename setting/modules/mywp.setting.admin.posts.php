<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( ! class_exists( 'MywpAbstractSettingModule' ) ) {
  return false;
}

if ( ! class_exists( 'MywpSettingScreenAdminPosts' ) ) :

final class MywpSettingScreenAdminPosts extends MywpAbstractSettingModule {

  static protected $id = 'admin_posts';

  static protected $priority = 40;

  static private $menu = 'admin';

  static private $post_type = '';

  public static function mywp_setting_screens( $setting_screens ) {

    $setting_screens[ self::$id ] = array(
      'title' => __( 'All Posts' ),
      'menu' => self::$menu,
      'controller' => 'admin_posts',
      'use_advance' => true,
    );

    return $setting_screens;

  }

  public static function mywp_current_load_setting_screen() {

    $current_setting_post_type_name = MywpSettingPostType::get_current_post_type_id();

    if( !empty( $current_setting_post_type_name ) ) {

      self::$post_type = $current_setting_post_type_name;

      add_filter( 'mywp_get_option_key_mywp_admin_posts' , array( __CLASS__ , 'mywp_get_option_key' ) );

    }

  }

  public static function mywp_get_option_key( $option_key ) {

    if( empty( self::$post_type ) ) {

      return $option_key;

    }

    $option_key .= '_' . self::$post_type;

    return $option_key;

  }

  public static function mywp_current_setting_screen_header() {

    MywpApi::include_file( MYWP_PLUGIN_PATH . 'views/elements/setting-screen-select-post-type.php' );

  }

  public static function mywp_current_setting_screen_advance_content() {

    $setting_data = self::get_setting_data();

    $current_setting_post_type_id = MywpSettingPostType::get_current_post_type_id();
    $current_setting_post_type = MywpSettingPostType::get_current_post_type();

    if( empty( $current_setting_post_type ) ) {

      printf( __( '%1$: %2$s is not found.' , 'mywp' ) , __( 'Invalid Post Type' , 'mywp' ) , $current_setting_post_type_id );

      return false;

    }

    ?>
    <h3 class="mywp-setting-screen-subtitle"><?php echo $current_setting_post_type->labels->name; ?></h3>
    <table class="form-table">
      <tbody>
        <tr>
          <th><?php _e( 'Number of items per page:' ); ?></th>
          <td>
            <label>
              <input type="number" name="mywp[data][per_page_num]" class="per_page_num small-text" value="<?php echo esc_attr( $setting_data['per_page_num'] ); ?>" />
            </label>
          </td>
        </tr>
        <tr>
          <th><?php echo $current_setting_post_type->labels->add_new; ?></th>
          <td>
            <label>
              <input type="checkbox" name="mywp[data][hide_add_new]" class="hide_add_new" value="1" <?php checked( $setting_data['hide_add_new'] , true ); ?> />
              <?php _e( 'Hide' ); ?>
            </label>
          </td>
        </tr>
        <tr>
          <th><?php echo $current_setting_post_type->labels->search_items; ?></th>
          <td>
            <label>
              <input type="checkbox" name="mywp[data][hide_search_box]" class="hide_search_box" value="1" <?php checked( $setting_data['hide_search_box'] , true ); ?> />
              <?php _e( 'Hide' ); ?>
            </label>
          </td>
        </tr>
      </tbody>
    </table>
    <p>&nbsp;</p>
    <?php

  }

  public static function mywp_current_setting_screen_remove_form() {

    $current_setting_post_type_id = MywpSettingPostType::get_current_post_type_id();

    if( empty( $current_setting_post_type_id ) ) {

      return false;

    }

    ?>

    <input type="hidden" name="mywp[data][post_type]" value="<?php echo esc_attr( $current_setting_post_type_id ); ?>" />

    <?php

  }

  public static function mywp_current_setting_post_data_format_update( $formatted_data ) {

    $mywp_model = self::get_model();

    if( empty( $mywp_model ) ) {

      return $formatted_data;

    }

    $new_formatted_data = $mywp_model->get_initial_data();

    $new_formatted_data['advance'] = $formatted_data['advance'];

    if( !empty( $formatted_data['post_type'] ) ) {

      $new_formatted_data['post_type'] = strip_tags( $formatted_data['post_type'] );

    }

    if( !empty( $formatted_data['per_page_num'] ) ) {

      $new_formatted_data['per_page_num'] = intval( $formatted_data['per_page_num'] );

    }

    if( !empty( $formatted_data['hide_add_new'] ) ) {

      $new_formatted_data['hide_add_new'] = true;

    }

    if( !empty( $formatted_data['hide_search_box'] ) ) {

      $new_formatted_data['hide_search_box'] = true;

    }

    return $new_formatted_data;

  }

  public static function mywp_current_setting_post_data_format_remove( $formatted_data ) {

    if( !empty( $formatted_data['post_type'] ) ) {

      $formatted_data['post_type'] = strip_tags( $formatted_data['post_type'] );

    }

    return $formatted_data;

  }

  public static function mywp_current_setting_post_data_validate_update( $validated_data ) {

    $mywp_notice = new MywpNotice();

    if( empty( $validated_data['post_type'] ) ) {

      $mywp_notice->add_notice_error( sprintf( __( 'The %s is not found data.' ) , 'post_type' ) );

    }

    return $validated_data;

  }

  public static function mywp_current_setting_post_data_validate_remove( $validated_data ) {

    $mywp_notice = new MywpNotice();

    if( empty( $validated_data['post_type'] ) ) {

      $mywp_notice->add_notice_error( sprintf( __( 'The %s is not found data.' ) , 'post_type' ) );

    }

    return $validated_data;

  }

  public static function mywp_current_setting_before_post_data_action_update( $validated_data ) {

    if( !empty( $validated_data['post_type'] ) ) {

      self::$post_type = $validated_data['post_type'];

      add_filter( 'mywp_get_option_key_mywp_admin_posts' , array( __CLASS__ , 'mywp_get_option_key' ) );

    }

  }

  public static function mywp_current_setting_before_post_data_action_remove( $validated_data ) {

    if( !empty( $validated_data['post_type'] ) ) {

      self::$post_type = $validated_data['post_type'];

      add_filter( 'mywp_get_option_key_mywp_admin_posts' , array( __CLASS__ , 'mywp_get_option_key' ) );

    }

  }

}

MywpSettingScreenAdminPosts::init();

endif;
