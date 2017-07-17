<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( ! class_exists( 'MywpAbstractSettingModule' ) ) {
  return false;
}

if ( ! class_exists( 'MywpSettingScreenAdminGeneral' ) ) :

final class MywpSettingScreenAdminGeneral extends MywpAbstractSettingModule {

  static protected $id = 'admin_general';

  static private $menu = 'admin';

  protected static function after_init() {

    $screen_id = self::$id;

    add_action( "mywp_setting_screen_content_{$screen_id}" , array( __CLASS__ , 'mywp_setting_screen_content_20' ) , 20 );
    add_action( "mywp_setting_screen_content_{$screen_id}" , array( __CLASS__ , 'mywp_setting_screen_content_30' ) , 30 );

  }

  public static function mywp_setting_screens( $setting_screens ) {

    $setting_screens[ self::$id ] = array(
      'title' => __( 'General' ),
      'menu' => self::$menu,
      'controller' => 'admin_general',
      'use_advance' => true,
    );

    return $setting_screens;

  }

  public static function mywp_current_setting_screen_content() {

    $setting_data = self::get_setting_data();

    $fields = array(
      'core' => __( 'WordPress core updates' , 'mywp' ),
      'plugins' => __( 'Plugin updates' , 'mywp' ),
      'themes' => __( 'Theme updates' , 'mywp' ),
      'translations' => __( 'Translation updates' , 'mywp' ),
    );

    ?>
    <h3 class="mywp-setting-screen-subtitle"><?php _e( 'Notifications' , 'mywp' ); ?></h3>
    <table class="form-table">
      <tbody>
        <?php foreach( $fields as $field_name => $field_label ) : ?>
          <tr>
            <th><?php echo $field_label; ?></th>
            <td>
              <label>
                <input type="checkbox" name="mywp[data][hide_update_notice][<?php echo esc_attr( $field_name ); ?>]" class="hide_update_notice_<?php echo esc_attr( $field_name ); ?>" value="1" <?php checked( $setting_data['hide_update_notice'][$field_name] , true ); ?> />
                <?php _e( 'Hide notifications' , 'mywp' ); ?>
              </label>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p>&nbsp;</p>
    <?php

  }

  public static function mywp_setting_screen_content_20() {

    $setting_data = self::get_setting_data();

    $fields = array(
      'options' => __( 'Screen Options' ),
      'help' => __( 'Help' , 'mywp' ),
    );

    ?>
    <h3 class="mywp-setting-screen-subtitle"><?php _e( 'Screen Options and Help Tab' , 'mywp' ); ?></h3>
    <table class="form-table">
      <tbody>
        <?php foreach( $fields as $field_name => $field_label ) : ?>
          <tr>
            <th><?php echo $field_label; ?></th>
            <td>
              <label>
                <input type="checkbox" name="mywp[data][hide_screen_tabs][<?php echo esc_attr( $field_name ); ?>]" class="hide_screen_tabs_<?php echo esc_attr( $field_name ); ?>" value="1" <?php checked( $setting_data['hide_screen_tabs'][$field_name] , true ); ?> />
                <?php _e( 'Hide' ); ?>
              </label>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p>&nbsp;</p>
    <?php

  }

  public static function mywp_setting_screen_content_30() {

    $setting_data = self::get_setting_data();

    $fields = array(
      'left' => array(
        'label' => __( 'Left' , 'mywp' ),
        'code' => sprintf( __( 'Thank you for creating with <a href="%s">WordPress</a>.' ), __( 'https://wordpress.org/' ) ),
      ),
      'right' => array(
        'label' => __( 'Right' , 'mywp' ),
        'code' => core_update_footer(),
      ),
    );

    ?>
    <h3 class="mywp-setting-screen-subtitle"><?php _e( 'Footer Text' , 'mywp' ); ?></h3>
    <table class="form-table">
      <tbody>
        <?php foreach( $fields as $field_name => $field_label ) : ?>
          <tr>
            <th><?php echo $field_label['label']; ?></th>
            <td>
              <label>
                <input type="checkbox" name="mywp[data][hide_footer_text][<?php echo esc_attr( $field_name ); ?>]" class="hide_footer_text_<?php echo esc_attr( $field_name ); ?>" value="1" <?php checked( $setting_data['hide_footer_text'][$field_name] , true ); ?> />
                <?php _e( 'Hide' ); ?>
              </label>
              <code><?php echo $field_label['code']; ?></code>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <p>&nbsp;</p>
    <?php

  }

  public static function mywp_current_setting_screen_advance_content() {

    $setting_data = self::get_setting_data();

    ?>
    <table class="form-table">
      <tbody>
        <tr>
          <th><?php _e( 'Custom Footer Text' , 'mywp' ); ?></th>
          <td>
            <?php wp_editor( $setting_data['custom_footer_text'] , 'custom_footer_text' , array( 'textarea_name' => 'mywp[data][custom_footer_text]' , 'textarea_rows' => 5 ) ); ?>
          </td>
        </tr>
        <tr>
          <th><?php _e( 'Remove "Wordpress" from title tag' , 'mywp' ); ?></th>
          <td>
            <label>
              <input type="checkbox" name="mywp[data][hide_core_title_tag]" class="hide_core_title_tag" value="1" <?php checked( $setting_data['hide_core_title_tag'] , true ); ?> />
              <?php _e( 'Remove' ); ?>
            </label>
          </td>
        </tr>
        <tr>
          <th><?php _e( 'Include your CSS file' , 'mywp' ); ?></th>
          <td>
            <input type="text" name="mywp[data][include_css_file]" class="include_css_file large-text" value="<?php echo esc_attr( $setting_data['include_css_file'] ); ?>" placeholder="<?php echo esc_attr( 'http://example.com/admin.css' ); ?>" />
          </td>
        </tr>
        <tr>
          <th><?php _e( 'Include your JS file' , 'mywp' ); ?></th>
          <td>
            <input type="text" name="mywp[data][include_js_file]" class="include_js_file large-text" value="<?php echo esc_attr( $setting_data['include_js_file'] ); ?>" placeholder="<?php echo esc_attr( 'http://example.com/admin.js' ); ?>" />
          </td>
        </tr>
        <tr>
          <th><?php _e( 'Input CSS' , 'mywp' ); ?></th>
          <td>
            <textarea type="text" name="mywp[data][input_css]" class="input_css large-text" placeholder="<?php echo esc_attr( 'body.wp-admin{ color: brack; }' ); ?>"><?php echo esc_attr( $setting_data['input_css'] ); ?></textarea>
          </td>
        </tr>
        <tr>
          <th><?php _e( 'Max Post Revision' , 'mywp' ); ?></th>
          <td>
            <label>
              <input type="text" name="mywp[data][max_post_revision]" class="max_post_revision small-text" value="<?php echo esc_attr( $setting_data['max_post_revision'] ); ?>" placeholder="-1" />
            </label>
          </td>
        </tr>
        <tr>
          <th><?php _e( 'Not use Admin Panel' , 'mywp' ); ?></th>
          <td>
            <label>
              <input type="checkbox" name="mywp[data][not_use_admin]" class="not_use_admin" value="1" <?php checked( $setting_data['not_use_admin'] , true ); ?> />
            </label>
          </td>
        </tr>
      </tbody>
    </table>
    <p>&nbsp;</p>
    <?php

  }

  public static function mywp_current_admin_print_footer_scripts() {

?>
<style>
.input_css {
  height: 300px;
}
</style>
<?php

  }

  public static function mywp_current_setting_post_data_format_update( $formatted_data ) {

    $mywp_model = self::get_model();

    if( empty( $mywp_model ) ) {

      return $formatted_data;

    }

    $new_formatted_data = $mywp_model->get_initial_data();

    $new_formatted_data['advance'] = $formatted_data['advance'];

    if( !empty( $formatted_data['hide_update_notice']['core'] ) ) {

      $new_formatted_data['hide_update_notice']['core'] = true;

    }

    if( !empty( $formatted_data['hide_update_notice']['plugins'] ) ) {

      $new_formatted_data['hide_update_notice']['plugins'] = true;

    }

    if( !empty( $formatted_data['hide_update_notice']['themes'] ) ) {

      $new_formatted_data['hide_update_notice']['themes'] = true;

    }

    if( !empty( $formatted_data['hide_update_notice']['translations'] ) ) {

      $new_formatted_data['hide_update_notice']['translations'] = true;

    }

    if( !empty( $formatted_data['hide_screen_tabs']['options'] ) ) {

      $new_formatted_data['hide_screen_tabs']['options'] = true;

    }

    if( !empty( $formatted_data['hide_screen_tabs']['help'] ) ) {

      $new_formatted_data['hide_screen_tabs']['help'] = true;

    }

    if( !empty( $formatted_data['hide_footer_text']['left'] ) ) {

      $new_formatted_data['hide_footer_text']['left'] = true;

    }

    if( !empty( $formatted_data['hide_footer_text']['right'] ) ) {

      $new_formatted_data['hide_footer_text']['right'] = true;

    }

    if( !empty( $formatted_data['custom_footer_text'] ) ) {

      $new_formatted_data['custom_footer_text'] = wp_unslash( $formatted_data['custom_footer_text'] );

    }

    if( !empty( $formatted_data['hide_core_title_tag'] ) ) {

      $new_formatted_data['hide_core_title_tag'] = true;

    }

    if( !empty( $formatted_data['include_css_file'] ) ) {

      $new_formatted_data['include_css_file'] = wp_unslash( $formatted_data['include_css_file'] );

    }

    if( !empty( $formatted_data['include_js_file'] ) ) {

      $new_formatted_data['include_js_file'] = wp_unslash( $formatted_data['include_js_file'] );

    }

    if( !empty( $formatted_data['input_css'] ) ) {

      $new_formatted_data['input_css'] = wp_unslash( $formatted_data['input_css'] );

    }

    if( isset( $formatted_data['max_post_revision'] ) ) {

      if( $formatted_data['max_post_revision'] !== '' ) {

        $max_post_revision = '';

        if( strpos( $formatted_data['max_post_revision'] , '-' ) !== false ) {

          $max_post_revision = '-';

        }

        $max_post_revision .= absint( $formatted_data['max_post_revision'] );

        $new_formatted_data['max_post_revision'] = $max_post_revision;

      }

    }

    if( !empty( $formatted_data['not_use_admin'] ) ) {

      $new_formatted_data['not_use_admin'] = true;

    }

    return $new_formatted_data;

  }

}

MywpSettingScreenAdminGeneral::init();

endif;
