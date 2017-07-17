<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( ! class_exists( 'MywpAbstractSettingModule' ) ) {
  return false;
}

if ( ! class_exists( 'MywpSettingScreeMainGeneral' ) ) :

final class MywpSettingScreeMainGeneral extends MywpAbstractSettingModule {

  static protected $id = 'main_general';

  static private $menu = 'main';

  static protected $priority = 1;

  public static function mywp_setting_menus( $setting_menus ) {

    $setting_menus[ self::$menu ] = array(
      'menu_title' => __( 'My WP' , 'mywp' ),
      'slug' => 'mywp',
      'main' => true,
      'multiple_screens' => false,
    );

    return $setting_menus;

  }

  public static function mywp_setting_screens( $setting_screens ) {

    $setting_screens[ self::$id ] = array(
      'title' => __( 'My WP' , 'mywp' ),
      'menu' => self::$menu,
      'use_form' => false,
    );

    return $setting_screens;

  }

  public static function mywp_current_setting_screen_content() {

    $plugin_info = MywpApi::plugin_info();

    ?>

    <p>
      <?php _e( 'I have planning to add more features and if you have problems, bugs, please contact me. Thank you. ;)' ); ?>
    </p>

    <p>
      <a href="<?php echo esc_url( $plugin_info['website_url'] ); ?>document_category/shortcode/" target="_blank" class="button"><span class="dashicons dashicons-external"></span> <?php _e( 'See shortcodes documents' , 'mywp' ); ?></a>
    </p>

    <?php

  }

}

MywpSettingScreeMainGeneral::init();

endif;
