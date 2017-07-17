<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( ! class_exists( 'MywpAbstractSettingModule' ) ) {
  return false;
}

if ( ! class_exists( 'MywpSettingScreenAdminPostEdit' ) ) :

final class MywpSettingScreenAdminPostEdit extends MywpAbstractSettingModule {

  static protected $id = 'admin_post_edit';

  static protected $priority = 50;

  static private $menu = 'admin';

  static private $post_type = '';

  public static function mywp_setting_screens( $setting_screens ) {

    $setting_screens[ self::$id ] = array(
      'title' => __( 'Post Edit' ),
      'menu' => self::$menu,
      'controller' => 'admin_post_edit',
      'use_advance' => true,
    );

    return $setting_screens;

  }

  public static function mywp_current_load_setting_screen() {

    $current_setting_post_type_name = MywpSettingPostType::get_current_post_type_id();

    if( !empty( $current_setting_post_type_name ) ) {

      self::$post_type = $current_setting_post_type_name;

      add_filter( 'mywp_get_option_key_mywp_admin_post_edit' , array( __CLASS__ , 'mywp_get_option_key' ) );

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

  public static function mywp_current_setting_screen_content() {

    $setting_data = self::get_setting_data();

    $current_setting_post_type_id = MywpSettingPostType::get_current_post_type_id();
    $current_setting_post_type = MywpSettingPostType::get_current_post_type();

    if( empty( $current_setting_post_type ) ) {

      printf( __( '%1$s: %2$s is not found.' , 'mywp' ) , __( 'Invalid Post Type' , 'mywp' ) , $current_setting_post_type_id );

      return false;

    }

    $meta_boxes_setting = array();

    if( !empty( $setting_data['meta_boxes'] ) ) {

      $meta_boxes_setting = $setting_data['meta_boxes'];

    }

    $one_post_link = MywpSettingPostType::get_one_post_link_edit( $current_setting_post_type_id );

    MywpSettingMetaBox::set_current_meta_box_screen_id( $current_setting_post_type_id );
    MywpSettingMetaBox::set_current_meta_box_screen_url( $one_post_link );
    MywpSettingMetaBox::set_current_meta_box_setting_data( $meta_boxes_setting );

    ?>
    <h3 class="mywp-setting-screen-subtitle"><?php _e( 'Management of meta boxes' , 'mywp' ); ?></h3>

    <?php MywpApi::include_file( MYWP_PLUGIN_PATH . 'views/elements/setting-screen-management-meta-boxes.php' ); ?>

    <p>&nbsp;</p>
    <?php

  }

  public static function mywp_current_setting_screen_advance_content() {

    $setting_data = self::get_setting_data();

    $current_setting_post_type_id = MywpSettingPostType::get_current_post_type_id();
    $current_setting_post_type = MywpSettingPostType::get_current_post_type();

    if( empty( $current_setting_post_type ) ) {

      printf( __( '%1$s: %2$s is not found.' , 'mywp' ) , __( 'Invalid Post Type' , 'mywp' ) , $current_setting_post_type_id );

      return false;

    }

    ?>
    <h3 class="mywp-setting-screen-subtitle"><?php echo $current_setting_post_type->labels->edit_item; ?></h3>
    <table class="form-table">
      <tbody>
        <tr>
          <th><?php echo _x( 'Add New' , 'post' ); ?></th>
          <td>
            <label>
              <input type="checkbox" name="mywp[data][hide_add_new]" class="hide_add_new" value="1" <?php checked( $setting_data['hide_add_new'] , true ); ?> />
              <?php _e( 'Hide' ); ?>
            </label>
          </td>
        </tr>
        <tr>
          <th><?php _e( 'Title' ); ?></th>
          <td>
            <label>
              <input type="checkbox" name="mywp[data][hide_title]" class="hide_title" value="1" <?php checked( $setting_data['hide_title'] , true ); ?> />
              <?php _e( 'Hide' ); ?>
            </label>
          </td>
        </tr>
        <tr>
          <th><?php _e( 'Permalinks' ); ?></th>
          <td>
            <label>
              <input type="checkbox" name="mywp[data][hide_permalink]" class="hide_permalink" value="1" <?php checked( $setting_data['hide_permalink'] , true ); ?> />
              <?php _e( 'Hide' ); ?>
            </label>
          </td>
        </tr>
        <tr>
          <th><?php _e( 'Change Permalinks' ); ?></th>
          <td>
            <label>
              <input type="checkbox" name="mywp[data][hide_change_permalink]" class="hide_change_permalink" value="1" <?php checked( $setting_data['hide_change_permalink'] , true ); ?> />
              <?php _e( 'Hide' ); ?>
            </label>
          </td>
        </tr>
        <tr>
          <th><?php _e( 'Content' ); ?></th>
          <td>
            <label>
              <input type="checkbox" name="mywp[data][hide_content]" class="hide_content" value="1" <?php checked( $setting_data['hide_content'] , true ); ?> />
              <?php _e( 'Hide' ); ?>
            </label>
          </td>
        </tr>
        <tr>
          <th><?php _e( 'Re-arrange meta boxes' , 'mywp' ); ?></th>
          <td>
            <label>
              <input type="checkbox" name="mywp[data][prevent_meta_box]" class="prevent_meta_box" value="1" <?php checked( $setting_data['prevent_meta_box'] , true ); ?> />
              <?php _e( 'Prevent' , 'mywp' ); ?>
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

    if( !empty( $formatted_data['meta_boxes'] ) ) {

      foreach( $formatted_data['meta_boxes'] as $meta_box_id => $meta_box_setting ) {

        $meta_box_id = strip_tags( $meta_box_id );

        $new_meta_box_setting = array( 'action' => '' , 'title' => '' );

        $new_meta_box_setting['action'] = strip_tags( $meta_box_setting['action'] );

        if( !empty( $meta_box_setting['title'] ) ) {

          $new_meta_box_setting['title'] = wp_unslash( $meta_box_setting['title'] );

        }

        $new_formatted_data['meta_boxes'][ $meta_box_id ] = $new_meta_box_setting;

      }

    }

    if( !empty( $formatted_data['hide_add_new'] ) ) {

      $new_formatted_data['hide_add_new'] = true;

    }

    if( !empty( $formatted_data['hide_title'] ) ) {

      $new_formatted_data['hide_title'] = true;

    }

    if( !empty( $formatted_data['hide_permalink'] ) ) {

      $new_formatted_data['hide_permalink'] = true;

    }

    if( !empty( $formatted_data['hide_change_permalink'] ) ) {

      $new_formatted_data['hide_change_permalink'] = true;

    }

    if( !empty( $formatted_data['hide_content'] ) ) {

      $new_formatted_data['hide_content'] = true;

    }

    if( !empty( $formatted_data['prevent_meta_box'] ) ) {

      $new_formatted_data['prevent_meta_box'] = true;

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

      add_filter( 'mywp_get_option_key_mywp_admin_post_edit' , array( __CLASS__ , 'mywp_get_option_key' ) );

    }

  }

  public static function mywp_current_setting_before_post_data_action_remove( $validated_data ) {

    if( !empty( $validated_data['post_type'] ) ) {

      self::$post_type = $validated_data['post_type'];

      add_filter( 'mywp_get_option_key_mywp_admin_post_edit' , array( __CLASS__ , 'mywp_get_option_key' ) );

    }

  }

}

MywpSettingScreenAdminPostEdit::init();

endif;
