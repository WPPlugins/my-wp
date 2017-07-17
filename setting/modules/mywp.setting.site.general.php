<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( ! class_exists( 'MywpAbstractSettingModule' ) ) {
  return false;
}

if ( ! class_exists( 'MywpSettingScreenSiteGeneral' ) ) :

final class MywpSettingScreenSiteGeneral extends MywpAbstractSettingModule {

  static protected $id = 'site_general';

  static protected $priority = 90;

  static private $menu = 'site_general';

  public static function mywp_setting_menus( $setting_menus ) {

    if( is_multisite() ) {

      $setting_menus[ self::$menu ] = array(
        'network' => true,
        'main' => true,
        'menu_title' => __( 'My WP' , 'mywp' ),
        'multiple_screens' => false,
      );

    } else {

      $setting_menus[ self::$menu ] = array(
        'menu_title' => __( 'Website' , 'mywp' ),
        'multiple_screens' => false,
      );

    }

    return $setting_menus;

  }

  public static function mywp_setting_screens( $setting_screens ) {

    $setting_screens[ self::$id ] = array(
      'title' => __( 'Site General' , 'mywp' ),
      'menu' => self::$menu,
      'controller' => 'site_general',
    );

    return $setting_screens;

  }

  public static function mywp_current_setting_screen_content() {

    $setting_data = self::get_setting_data();

    ?>
    <input type="hidden" name="mywp[data][dummy]" value="1" />
    <table class="form-table">
      <tbody>
        <tr>
          <th><?php _e( 'Disable File Edit' , 'mywp' ); ?></th>
          <td>
            <label>
              <input type="checkbox" name="mywp[data][disable_file_edit]" class="disable_file_edit" value="1" <?php checked( $setting_data['disable_file_edit'] , true ); ?> />
              <?php _e( 'Disabled' , 'mywp' ); ?>
            </label>
          </td>
        </tr>
      </tbody>
    </table>
    <p>&nbsp;</p>
    <?php

  }

  public static function mywp_current_setting_post_data_format_update( $formatted_data ) {

    $mywp_model = self::get_model();

    if( empty( $mywp_model ) ) {

      return $formatted_data;

    }

    $new_formatted_data = $mywp_model->get_initial_data();

    $new_formatted_data['advance'] = $formatted_data['advance'];

    if( !empty( $formatted_data['disable_file_edit'] ) ) {

      $new_formatted_data['disable_file_edit'] = true;

    }

    return $new_formatted_data;

  }

}

MywpSettingScreenSiteGeneral::init();

endif;
