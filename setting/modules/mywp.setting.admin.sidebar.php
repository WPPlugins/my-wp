<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( ! class_exists( 'MywpAbstractSettingModule' ) ) {
  return false;
}

if ( ! class_exists( 'MywpSettingScreenAdminSidebar' ) ) :

final class MywpSettingScreenAdminSidebar extends MywpAbstractSettingModule {

  static protected $id = 'admin_sidebar';

  static protected $priority = 20;

  static private $menu = 'admin';

  static private $default_sidebar_items;

  static private $current_setting_sidebar_items;

  static private $find_parent_id;

  static private $user_roles;

  public static function mywp_setting_screens( $setting_screens ) {

    $setting_screens[ self::$id ] = array(
      'title' => __( 'Sidebar' , 'mywp' ),
      'menu' => self::$menu,
      'controller' => 'admin_sidebar',
      'use_advance' => true,
    );

    return $setting_screens;

  }

  public static function mywp_ajax() {

    add_action( 'wp_ajax_' . MywpSetting::get_ajax_action_name( self::$id , 'add_items' ) , array( __CLASS__ , 'ajax_add_items' ) );

    add_action( 'wp_ajax_' . MywpSetting::get_ajax_action_name( self::$id , 'remove_items' ) , array( __CLASS__ , 'ajax_remove_items' ) );

    add_action( 'wp_ajax_' . MywpSetting::get_ajax_action_name( self::$id , 'update_item' ) , array( __CLASS__ , 'ajax_update_item' ) );

    add_action( 'wp_ajax_' . MywpSetting::get_ajax_action_name( self::$id , 'update_item_order_and_parents' ) , array( __CLASS__ , 'ajax_update_item_order_and_parents' ) );

  }

  public static function ajax_add_items() {

    $action_name = MywpSetting::get_ajax_action_name( self::$id , 'add_items' );

    if( empty( $_POST[ $action_name ] ) ) {

      return false;

    }

    check_ajax_referer( $action_name , $action_name );

    if( empty( $_POST['add_items'] ) or ! is_array( $_POST['add_items'] ) ) {

      return false;

    }

    $add_items = array();

    foreach( $_POST['add_items'] as $key => $post_item ) {

      if( empty( $post_item['item_type'] ) ) {

        continue;

      }

      $add_item = array();

      $add_item['item_type'] = strip_tags( $post_item['item_type'] );

      $add_item['item_custom_html'] = false;

      if( !empty( $post_item['item_custom_html'] ) ) {

        $add_item['item_custom_html'] = wp_unslash( $post_item['item_custom_html'] );

      }

      $add_item['item_default_type'] = false;

      if( !empty( $post_item['item_default_type'] ) ) {

        $add_item['item_default_type'] = strip_tags( $post_item['item_default_type'] );

      }

      $add_item['item_default_id'] = false;

      if( !empty( $post_item['item_default_id'] ) ) {

        $add_item['item_default_id'] = strip_tags( $post_item['item_default_id'] );

      }

      $add_item['item_default_parent_id'] = false;

      if( !empty( $post_item['item_default_parent_id'] ) ) {

        $add_item['item_default_parent_id'] = strip_tags( $post_item['item_default_parent_id'] );

      }

      $add_item['item_link_title'] = false;

      if( !empty( $post_item['item_link_title'] ) ) {

        $add_item['item_link_title'] = wp_unslash( $post_item['item_link_title'] );

      }

      $add_item['item_link_url'] = false;

      if( !empty( $post_item['item_link_url'] ) ) {

        $add_item['item_link_url'] = strip_tags( $post_item['item_link_url'] );

      }

      $add_item['item_capability'] = false;

      if( !empty( $post_item['item_capability'] ) ) {

        $add_item['item_capability'] = strip_tags( $post_item['item_capability'] );

      }

      $add_items[] = $add_item;

    }

    if( empty( $add_items ) ) {

      return false;

    }

    $result_html = '';

    foreach( $add_items as $key => $add_item ) {

      $add_meta_data = array(
        'item_type' => $add_item['item_type'],
        'item_custom_html' => $add_item['item_custom_html'],
        'item_default_type' => $add_item['item_default_type'],
        'item_default_id' => $add_item['item_default_id'],
        'item_default_parent_id' => $add_item['item_default_parent_id'],
        'item_link_title' => $add_item['item_link_title'],
        'item_link_url' => $add_item['item_link_url'],
        'item_capability' => $add_item['item_capability'],
      );

      $parent_post_id = self::add_post( array( 'menu_order' => 1000 ) , $add_meta_data );

      if ( empty( $parent_post_id ) or is_wp_error( $parent_post_id ) ) {

        continue;

      }

      $parent_post = MywpPostType::get_post( $parent_post_id );

      ob_start();

      self::print_item( $parent_post );

      $result_html .= ob_get_contents();

      ob_end_clean();

    }

    wp_send_json_success( array( 'result_html' => $result_html ) );

  }

  public static function ajax_remove_items() {

    $action_name = MywpSetting::get_ajax_action_name( self::$id , 'remove_items' );

    if( empty( $_POST[ $action_name ] ) ) {

      return false;

    }

    check_ajax_referer( $action_name , $action_name );

    if( empty( $_POST['remove_items'] ) ) {

      return false;

    }

    $remove_items = array_map( 'intval' , $_POST['remove_items'] );

    foreach( $remove_items as $key => $post_id ) {

      $post = MywpPostType::get_post( $post_id );

      if( empty( $post )  or ! is_object( $post ) or $post->post_type != 'mywp_admin_sidebar' ) {

        continue;

      }

      wp_delete_post( $post_id , true );

    }

    wp_send_json_success();

  }

  public static function ajax_update_item() {

    $action_name = MywpSetting::get_ajax_action_name( self::$id , 'update_item' );

    if( empty( $_POST[ $action_name ] ) ) {

      return false;

    }

    check_ajax_referer( $action_name , $action_name );

    if( empty( $_POST['update_item'] ) or ! is_array( $_POST['update_item'] ) ) {

      return false;

    }

    $update_item = $_POST['update_item'];

    if( empty( $update_item['item_id'] ) ) {

      return false;

    }

    $post_id = intval( $update_item['item_id'] );

    unset( $update_item['item_id'] );

    $post = MywpPostType::get_post( $post_id );

    if( empty( $post )  or ! is_object( $post ) or $post->post_type != 'mywp_admin_sidebar' ) {

      return false;

    }

    foreach( $update_item as $meta_key => $meta_value ) {

      $meta_key = wp_unslash( strip_tags( $meta_key ) );

      if( in_array( $meta_key , array( 'item_li_class' , 'item_li_id' , 'item_capability' , 'item_link_class' , 'item_link_id' , 'item_link_url' , 'item_link_attr' , 'item_icon_class' , 'item_icon_id' , 'item_icon_img' , 'item_icon_style' ) ) ) {

        $meta_value = strip_tags( $meta_value );

      } elseif( in_array( $meta_key , array( 'item_link_title' , 'item_custom_html' , 'item_icon_title' ) ) ) {

        $meta_value = wp_unslash( $meta_value );

      } else {

        continue;

      }

      update_post_meta( $post_id , $meta_key , $meta_value );

    }

    wp_send_json_success();

  }

  public static function ajax_update_item_order_and_parents() {

    $action_name = MywpSetting::get_ajax_action_name( self::$id , 'update_item_order_and_parents' );

    if( empty( $_POST[ $action_name ] ) ) {

      return false;

    }

    check_ajax_referer( $action_name , $action_name );

    if( empty( $_POST['item_order_parents'] ) or ! is_array( $_POST['item_order_parents'] ) ) {

      return false;

    }

    $saved = false;

    foreach( $_POST['item_order_parents'] as $key => $post_item ) {

      if( !isset( $post_item['order'] ) or !isset( $post_item['parent_id'] ) or empty( $post_item['item_id'] ) ) {

        continue;

      }

      $post_id = intval( $post_item['item_id'] );

      $post = MywpPostType::get_post( $post_id );

      if( empty( $post )  or ! is_object( $post ) or $post->post_type != 'mywp_admin_sidebar' ) {

        continue;

      }

      $menu_order = intval( $post_item['order'] );

      $post_parent = intval( $post_item['parent_id'] );

      $post_data = array(
        'ID' => $post_id,
        'menu_order' => $menu_order,
        'post_parent' => $post_parent,
        'post_status' => 'publish',
      );

      wp_update_post( $post_data );

      $saved = true;

    }

    if( $saved ) {

      wp_send_json_success();

    }

  }

  public static function mywp_current_admin_enqueue_scripts() {

    $scripts = array( 'jquery-ui-sortable' );

    foreach( $scripts as $script ) {

      wp_enqueue_script( $script );

    }

  }

  public static function mywp_current_admin_print_styles() {

?>
<style>
#setting-screen-sidebar-available-item #add-items {
  height: 300px;
}
#setting-screen-sidebar-available-item .spinner {
  float: right;
}
#setting-screen-sidebar-available-item #available-items {
  display: none;
}
#setting-screen-sidebar-items {
  padding: 30px 0;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item {
  border: 1px solid #ddd;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item.active {
  background: #FFF4FF;
  border-color: #D0AAC9;
}
#setting-screen-sidebar-items .sortable-placeholder,
#setting-screen-sidebar-items .setting-screen-sidebar-item .item-header {
  height: 38px;
}
#setting-screen-sidebar-items .sortable-placeholder {
  margin: 0;
  background: #ccc;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item .item-header {
  cursor: move;
  background: #fafafa;
  line-height: 38px;
  overflow:  hidden;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item.active > .item-header {
  border-bottom: 1px solid #D0AAC9;
  background: #E6D2E2;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item .item-header .item-active-toggle {
  float: right;
  display: inline-block;
  width: 38px;
  height: 38px;
  text-decoration: none;
  color: #72777c;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item .item-header .item-active-toggle:hover {
  color: #23282d;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item .item-header .item-active-toggle:focus {
  box-shadow: none;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item .item-header .item-active-toggle:before {
  font: 400 20px/1 dashicons;
  content: "\f140";
  display: block;
  text-align: center;
  padding-top: 10px;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item.active > .item-header .item-active-toggle:before {
  content: "\f142";
}
#setting-screen-sidebar-items .setting-screen-sidebar-item .item-header .item-title-wrap {
  margin-left: 10px;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item .item-header .item-title-wrap .dashicons {
  margin: 8px 4px 0 0;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item .item-header .item-title-wrap .dashicons.svg {
  background-repeat: no-repeat;
  background-position: center;
  background-size: 20px auto;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item .item-header .item-title-wrap .item-title {}
#setting-screen-sidebar-items .setting-screen-sidebar-item .item-header .item-title-wrap .item-default-title {
  color: #999;
  font-style: italic;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item .item-content {
  display: none;
  padding: 20px;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item.active > .item-content {
  display: block;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item .item-content .item-update {
  float: right;
  margin: 12px 0 6px 0;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item .item-content .item-remove {
  margin: 12px 0 6px 0;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item .item-content .form-table th,
#setting-screen-sidebar-items .setting-screen-sidebar-item .item-content .form-table td {
  background: #fff;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item .item-content .form-table th {
  width: 120px;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item .item-content .item-content-hidden-fields {
  display: none;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item .item-content .custom-html {
  height: 300px;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item .item-content ul.capability-roles {
  margin: 8px 0 0 0;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item .item-content ul.capability-roles li {
  color: #ccc;
  font-size: 12px;
  line-height: 1.2;
  display: inline-block;
  margin: 4px;
  padding: 2px 4px;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item .item-content ul.capability-roles li.role-can {
  color: #000;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item .item-content .child-menu-title {
  font-weight: 400;
}
#setting-screen-sidebar-items .setting-screen-sidebar-item .item-content .child-menus {
  margin: 10px 0;
  padding: 10px;
  border: 1px solid #aaa;
  background: #fff;
}
</style>
<?php

  }

  public static function mywp_current_admin_print_footer_scripts() {

?>
<script>
jQuery(document).ready(function($){

  $('.sortable-items').sortable({
    placeholder: 'sortable-placeholder',
    handle: '.item-header',
    connectWith: '.sortable-items',
    distance: 2,
    stop: function( ev , ui ) {

      var $sorted_item = ui.item;

      $sorted_item.children().find('> .item-title-wrap .spinner').css('visibility', 'visible');

      var item_order_parents = [];

      $(document).find('#setting-screen-sidebar-items .setting-screen-sidebar-item').each( function( index , el ) {

        var $item = $(el)

        var post_id = $item.find('> .item-content .id').val();

        var parent_id = 0;

        if( $item.parent().hasClass('child-menus') ) {

          parent_id = $item.parent().parent().find('> .item-content-fields .id').val();

        }

        var item_order_parent = { item_id: post_id, order: index, parent_id: parent_id };

        item_order_parents.push( item_order_parent );

      });

      if( item_order_parents.length == 0 ) {

        return false;

      }

      PostData = {
        action: '<?php echo MywpSetting::get_ajax_action_name( self::$id , 'update_item_order_and_parents' ); ?>',
        <?php echo MywpSetting::get_ajax_action_name( self::$id , 'update_item_order_and_parents' ); ?>: '<?php echo wp_create_nonce( MywpSetting::get_ajax_action_name( self::$id , 'update_item_order_and_parents' ) ); ?>',
        item_order_parents: item_order_parents
      };

      $.ajax({
        type: 'post',
        url: ajaxurl,
        data: PostData
      }).done( function( xhr ) {

        if( typeof xhr !== 'object' || xhr.success === undefined ) {

          alert( '<?php _e( 'An error has occurred. Please reload the page and try again.' ); ?>' );

          return false;

        }

        $sorted_item.children().find('> .item-title-wrap .spinner').css('visibility', 'hidden');

        return true;

      }).fail( function( xhr ) {

        alert( '<?php _e( 'An error has occurred. Please reload the page and try again.' ); ?>' );

        return false;

      });

    }
  });

  $('#sidebar-available-item-add-button').on('click', function() {

    var $available_item = $(this).parent();

    var add_item_keys = $available_item.find('#add-items').val();

    if( add_item_keys == null ) {

      return false;

    }

    var add_items = [];

    $.each( add_item_keys , function( i , add_item_key ) {

      var $available_item = $('#available-items').find('.available-item.item-key-'+add_item_key);

      if( ! $available_item.size() ) {

        return true;

      }

      var add_item = {
        item_type: $available_item.find('.item_type').val(),
        item_custom_html: $available_item.find('.item_custom_html').val(),
        item_default_type: $available_item.find('.item_default_type').val(),
        item_default_id: $available_item.find('.item_default_id').val(),
        item_default_parent_id: $available_item.find('.item_default_parent_id').val(),
        item_link_title: $available_item.find('.item_link_title').val(),
        item_link_url: $available_item.find('.item_link_url').val(),
        item_capability: $available_item.find('.item_capability').val()
      };

      add_items.push( add_item );

    });

    if( ! add_items[0] ) {

      return false;

    }

    PostData = {
      action: '<?php echo MywpSetting::get_ajax_action_name( self::$id , 'add_items' ); ?>',
      <?php echo MywpSetting::get_ajax_action_name( self::$id , 'add_items' ); ?>: '<?php echo wp_create_nonce( MywpSetting::get_ajax_action_name( self::$id , 'add_items' ) ); ?>',
      add_items: add_items
    };

    $available_item.find('.spinner').css('visibility', 'visible');

    $.ajax({
      type: 'post',
      url: ajaxurl,
      data: PostData
    }).done( function( xhr ) {

      if( typeof xhr !== 'object' || xhr.success === undefined ) {

        alert( '<?php _e( 'An error has occurred. Please reload the page and try again.' ); ?>' );

        return false;

      }

      if( xhr.data.result_html === undefined ) {

        alert( '<?php _e( 'An error has occurred. Please reload the page and try again.' ); ?>' );

        return false;

      }

      $(document).find('#setting-screen-sidebar-items').append( xhr.data.result_html );

      $(document).find('.sortable-items').sortable({
        connectWith: '.sortable-items'
      });

      $available_item.find('.spinner').css('visibility', 'hidden');

      var scroll_position = $(document).find('#setting-screen-sidebar-items .setting-screen-sidebar-item:last').offset().top;

      $( 'html,body' ).animate({
        scrollTop: scroll_position
      });

    }).fail( function( xhr ) {

      alert( '<?php _e( 'An error has occurred. Please reload the page and try again.' ); ?>' );

    });

  });

  $(document).on('click', '#setting-screen-sidebar-items .item-active-toggle', function() {

    $(this).parent().parent().toggleClass('active');

  });

  $(document).on('click', '#setting-screen-sidebar-items .item-remove', function() {

    var $item = $(this).parent().parent().parent();

    $item.find('.spinner').css('visibility', 'visible');

    var remove_items = [];

    $item.find('.id').each( function( i , el ){
      remove_items.push( $(el).val() );
    });

    PostData = {
      action: '<?php echo MywpSetting::get_ajax_action_name( self::$id , 'remove_items' ); ?>',
      <?php echo MywpSetting::get_ajax_action_name( self::$id , 'remove_items' ); ?>: '<?php echo wp_create_nonce( MywpSetting::get_ajax_action_name( self::$id , 'remove_items' ) ); ?>',
      remove_items: remove_items
    };

    $.ajax({
      type: 'post',
      url: ajaxurl,
      data: PostData
    }).done( function( xhr ) {

      if( typeof xhr !== 'object' || xhr.success === undefined ) {

        alert( '<?php _e( 'An error has occurred. Please reload the page and try again.' ); ?>' );

        return false;

      }

      $item.slideUp( 'normal' , function() {

        $item.remove();

      });

    }).fail( function( xhr ) {

      alert( '<?php _e( 'An error has occurred. Please reload the page and try again.' ); ?>' );

    });

  });

  $(document).on('click', '#setting-screen-sidebar-items .item-update', function() {

    var $item = $(this).parent().parent().parent();
    var $item_content_field = $(this).parent();

    $item_content_field.find('.spinner').css('visibility', 'visible');

    var update_item = {
      item_id: $item_content_field.find('.id').val(),
      item_type: $item_content_field.find('.item_type').val(),
      item_link_title: $item_content_field.find('.item_link_title').val(),
      item_li_class: $item_content_field.find('.item_li_class').val(),
      item_li_id: $item_content_field.find('.item_li_id').val(),
      item_custom_html: $item_content_field.find('.item_custom_html').val(),
      item_capability: $item_content_field.find('.item_capability').val(),
      item_link_class: $item_content_field.find('.item_link_class').val(),
      item_link_id: $item_content_field.find('.item_link_id').val(),
      item_link_url: $item_content_field.find('.item_link_url').val(),
      item_link_attr: $item_content_field.find('.item_link_attr').val(),
      item_icon_class: $item_content_field.find('.item_icon_class').val(),
      item_icon_id: $item_content_field.find('.item_icon_id').val(),
      item_icon_img: $item_content_field.find('.item_icon_img').val(),
      item_icon_style: $item_content_field.find('.item_icon_style').val(),
      item_icon_title: $item_content_field.find('.item_icon_title').val(),
    };

    PostData = {
      action: '<?php echo MywpSetting::get_ajax_action_name( self::$id , 'update_item' ); ?>',
      <?php echo MywpSetting::get_ajax_action_name( self::$id , 'update_item' ); ?>: '<?php echo wp_create_nonce( MywpSetting::get_ajax_action_name( self::$id , 'update_item' ) ); ?>',
      update_item: update_item
    };

    $.ajax({
      type: 'post',
      url: ajaxurl,
      data: PostData
    }).done( function( xhr ) {

      if( typeof xhr !== 'object' || xhr.success === undefined ) {

        alert( '<?php _e( 'An error has occurred. Please reload the page and try again.' ); ?>' );

        return false;

      }

      $item_content_field.find('.spinner').css('visibility', 'hidden');

    }).fail( function( xhr ) {

      alert( '<?php _e( 'An error has occurred. Please reload the page and try again.' ); ?>' );

    });

  });

});
</script>
<?php

  }

  public static function mywp_current_setting_screen_header() {

    $available_sidebar_items = self::get_available_sidebar_items();

    if( empty( $available_sidebar_items ) ) {

      return false;

    }

    ?>

    <div id="setting-screen-sidebar-available-item">

      <select id="add-items" multiple="multiple">

        <?php foreach( $available_sidebar_items as $key => $available_item ) : ?>

          <option value="<?php echo esc_attr( $key ); ?>" class="available-item"><?php echo esc_attr( strip_shortcodes( $available_item['title'] ) ); ?></option>

        <?php endforeach; ?>

      </select>

      <a href="javascript:void(0);" id="sidebar-available-item-add-button" class="button button-secondary"><span class="dashicons dashicons-plus"></span> <?php _e( 'Add Item' , 'mywp' ); ?></a>

      <span class="spinner"></span>

      <div id="available-items">

        <?php foreach( $available_sidebar_items as $key => $available_item ) : ?>

          <?php if( empty( $available_item['item_type'] ) ) : ?>

            <?php continue; ?>

          <?php endif; ?>

          <div class="available-item item-key-<?php echo esc_attr( $key ); ?>">

            <input type="text" class="item_type" value="<?php echo esc_attr( $available_item['item_type'] ); ?>" />

            <?php if( !empty( $available_item['item_custom_html'] ) ) : ?>

              <input type="text" class="item_custom_html" value="<?php echo esc_attr( $available_item['item_custom_html'] ); ?>" />

            <?php endif; ?>

            <?php if( !empty( $available_item['item_default_type'] ) ) : ?>

              <input type="text" class="item_default_type" value="<?php echo esc_attr( $available_item['item_default_type'] ); ?>" />

            <?php endif; ?>

            <?php if( !empty( $available_item['item_default_id'] ) ) : ?>

              <input type="text" class="item_default_id" value="<?php echo esc_attr( $available_item['item_default_id'] ); ?>" />

            <?php endif; ?>

            <?php if( !empty( $available_item['item_default_parent_id'] ) ) : ?>

              <input type="text" class="item_default_parent_id" value="<?php echo esc_attr( $available_item['item_default_parent_id'] ); ?>" />

            <?php endif; ?>

            <?php if( !empty( $available_item['item_link_title'] ) ) : ?>

              <input type="text" class="item_link_title" value="<?php echo esc_attr( $available_item['item_link_title'] ); ?>" />

            <?php endif; ?>

            <?php if( !empty( $available_item['item_link_url'] ) ) : ?>

              <input type="text" class="item_link_url" value="<?php echo esc_attr( $available_item['item_link_url'] ); ?>" />

            <?php endif; ?>

            <?php if( !empty( $available_item['item_capability'] ) ) : ?>

              <input type="text" class="item_capability" value="<?php echo esc_attr( $available_item['item_capability'] ); ?>" />

            <?php endif; ?>

          </div>

        <?php endforeach; ?>

      </div>

    </div>

    <p>&nbsp;</p>

    <?php

  }

  public static function mywp_current_setting_screen_content() {

    $parent_sidebar_items = self::find_items_to_parent_id();

    if( empty( $parent_sidebar_items ) ) {

      return false;

    }

    ?>
    <h3 class="mywp-setting-screen-subtitle"><?php _e( 'Current Sidebar' , 'mywp' ); ?></h3>

    <p><?php _e( 'Drag menu items to edit and reorder menus.' , 'mywp' ); ?></p>

    <div id="setting-screen-sidebar">

      <div id="setting-screen-sidebar-items" class="sortable-items">

        <?php if( !empty( $parent_sidebar_items ) ) : ?>

          <?php foreach( $parent_sidebar_items as $key => $item ) : ?>

            <?php self::print_item( $item ); ?>

          <?php endforeach; ?>

        <?php endif; ?>

      </div>

    </div>

    <?php

  }

  public static function mywp_current_setting_screen_advance_content() {

    $setting_data = self::get_setting_data();

    ?>
    <table class="form-table">
      <tbody>
        <tr>
          <th><?php _e( 'Custom Menu UI' , 'mywp' ); ?></th>
          <td>
            <label>
              <input type="checkbox" name="mywp[data][custom_menu_ui]" class="custom_menu_ui" value="1" <?php checked( $setting_data['custom_menu_ui'] , true ); ?> />
              <?php _e( 'Enable' , 'mywp' ); ?>
            </label>
          </td>
        </tr>
      </tbody>
    </table>
    <p>&nbsp;</p>
    <?php

  }

  private static function get_default_sidebar_items() {

    if( !empty( self::$default_sidebar ) ) {

      return self::$default_sidebar;

    }

    $default_sidebar = MywpAdminSidebar::get_default_sidebar();

    if( empty( $default_sidebar['menu'] ) or empty( $default_sidebar['submenu'] ) ) {

      return false;

    }

    foreach( $default_sidebar['menu'] as $key => $menu ) {

      $menu_id = $menu[2];

      if( $menu_id == 'edit-comments.php' ) {

        $default_sidebar['menu'][ $key ][0] = sprintf( '%s %s' , __( 'Comments' ) , '[mywp_comment_count tag="1"]' );

      } elseif( $menu_id == 'themes.php' ) {

        $default_sidebar['menu'][ $key ][0] = sprintf( '%s %s' , __( 'Appearance' ) , '[mywp_update_count type="themes" tag="1"]' );

      } elseif( $menu_id == 'plugins.php' ) {

        $default_sidebar['menu'][ $key ][0] = sprintf( '%s %s' , __( 'Plugins' ) , '[mywp_update_count type="plugins" tag="1"]' );

      }

    }

    foreach( $default_sidebar['submenu'] as $parent_id => $submenus ) {

      foreach( $submenus as $key => $submenu ) {

        $menu_id = $submenu[2];

        if( $parent_id == 'index.php' && $menu_id == 'update-core.php' ) {

          $default_sidebar['submenu'][ $parent_id ][ $key ][0] = sprintf( '%s %s' , __( 'Update' ) , '[mywp_update_count type="total" tag="1"]' );

        }

      }

    }

    $default_sidebar = apply_filters( 'mywp_setting_admin_sidebar_get_default_sidebar_items' , $default_sidebar );

    if( !empty( $default_sidebar['menu'] ) ) {

      ksort( $default_sidebar['menu'] );

    }

    if( !empty( $default_sidebar['submenu'] ) ) {

      foreach( $default_sidebar['submenu'] as $parent_id => $submenus ) {

        ksort( $default_sidebar['submenu'][ $parent_id ] );

      }

    }

    self::$default_sidebar_items = $default_sidebar;

    return self::$default_sidebar_items;

  }

  private static function get_available_sidebar_items() {

    $default_sidebar_items = self::get_default_sidebar_items();

    if( empty( $default_sidebar_items['menu'] ) ) {

      return false;

    }

    $available_sidebar_items = array(
      array(
        'title' => __( 'Custom Html' , 'mywp' ),
        'item_type' => 'custom',
      ),
      array(
        'title' => __( 'Custom Link' ),
        'item_type' => 'link',
      ),
      array(
        'title' => __( 'Separator' ),
        'item_type' => 'custom',
        'item_custom_html' => '<div class="separator"></div>',
      ),
      array(
        'title' => '----------------',
        'item_type' => '',
      ),
    );

    foreach( $default_sidebar_items['menu'] as $menu ) {

      if( strpos( $menu[4] , 'separator' ) !== false ) {

        continue;

      }

      $parent_menu_id = $menu[2];

      $available_sidebar_items[] = array(
        'title' => strip_tags( strip_shortcodes( $menu[0] ) ),
        'item_type' => 'default',
        'item_default_type' => 'menu',
        'item_default_id' => $parent_menu_id,
        'item_default_parent_id' => '',
        'item_link_title' => $menu[0],
        'item_link_url' => $menu[2],
        'item_capability' => $menu[1],
      );

      if( !empty( $default_sidebar_items['submenu'][ $parent_menu_id ] ) ) {

        foreach( $default_sidebar_items['submenu'][ $parent_menu_id ] as $submenu ) {

          $child_menu_id = $submenu[2];

          $available_sidebar_items[] = array(
            'title' => '&#160;&#160;-&#160;&#160;' . strip_tags( strip_shortcodes( $submenu[0] ) ),
            'item_type' => 'default',
            'item_default_type' => 'submenu',
            'item_default_id' => $child_menu_id,
            'item_default_parent_id' => $parent_menu_id,
            'item_link_title' => $submenu[0],
            'item_link_url' => $submenu[2],
            'item_capability' => $submenu[1],
          );

        }

      }

    }

    return apply_filters( 'mywp_setting_admin_sidebar_available_sidebar_items' , $available_sidebar_items );

  }

  private static function get_current_setting_sidebar_item_posts() {

    $args = array( 'post_status' => array( 'publish' , 'draft' ) , 'post_type' => 'mywp_admin_sidebar' , 'order' => 'ASC' , 'orderby' => 'menu_order' , 'posts_per_page' => -1 );

    $args = apply_filters( 'mywp_setting_admin_sidebar_get_current_setting_sidebar_item_posts_args' , $args );

    $current_setting_sidebar_item_posts = MywpPostType::get_posts( $args );

    return $current_setting_sidebar_item_posts;

  }

  private static function get_current_setting_sidebar_items() {

    if( !empty( self::$current_setting_sidebar_items ) ) {

      return self::$current_setting_sidebar_items;

    }

    $current_setting_sidebar_items = self::get_current_setting_sidebar_item_posts();

    if( empty( $current_setting_sidebar_items ) ) {

      self::regist_default_sidebar_items();

      $current_setting_sidebar_items = self::get_current_setting_sidebar_item_posts();

    }

    if( empty( $current_setting_sidebar_items ) ) {

      return false;

    }

    foreach( $current_setting_sidebar_items as $key => $sidebar_item ) {

      if( $sidebar_item->item_type == 'default') {

        $sidebar_item = MywpAdminSidebar::default_item_convert( $sidebar_item );

        if( !empty( $sidebar_item ) ) {

          $current_setting_sidebar_items[ $key ] = $sidebar_item;

        } else {

          unset( $current_setting_sidebar_items[ $key ] );

        }

      }

    }

    self::$current_setting_sidebar_items = apply_filters( 'mywp_setting_admin_sidebar_get_current_setting_sidebar_items' , $current_setting_sidebar_items );

    return $current_setting_sidebar_items;

  }

  private static function find_items_to_parent_id( $parent_id = 0 ) {

    $current_setting_sidebar_items = self::get_current_setting_sidebar_items();

    if( empty( $current_setting_sidebar_items ) ) {

      return false;

    }

    $parent_id = intval( $parent_id );

    if( !empty( self::$find_parent_id[ $parent_id ] ) ) {

      return self::$find_parent_id[ $parent_id ];

    }

    $find_items = array();

    foreach( $current_setting_sidebar_items as $item ) {

      if( $item->item_parent != $parent_id ) {

        continue;

      }

      $find_items[] = $item;

    }

    if( empty( $find_items ) ) {

      return false;

    }

    self::$find_parent_id[ $parent_id ] = $find_items;

    return $find_items;

  }

  private static function regist_default_sidebar_items() {

    $default_sidebar_item = self::get_default_sidebar_items();

    if( empty( $default_sidebar_item['menu'] ) ) {

      return false;

    }

    @set_time_limit( 300 );

    $menu_order = 0;

    $called_text = sprintf( '%s::%s()' , __CLASS__ , __FUNCTION__ );

    foreach( $default_sidebar_item['menu'] as $parent_menu ) {

      $parent_item_id = $parent_menu[2];

      $add_meta_data = array(
        'item_type' => 'default',
        'item_default_type' => 'menu',
        'item_default_id' => $parent_item_id,
        'item_default_parent_id' => '',
        'item_link_title' => $parent_menu[0],
      );

      $parent_post_id = self::add_post( array( 'post_status' => 'draft' , 'menu_order' => $menu_order ) , $add_meta_data );

      $menu_order++;

      if ( empty( $parent_post_id ) ) {

        MywpHelper::error_not_found_message( '$parent_post_id' , $called_text );

        continue;

      } elseif( is_wp_error( $parent_post_id ) ) {

        MywpHelper::error_message( $parent_post_id->get_error_message() , $called_text );

        continue;

      }

      if( !empty( $default_sidebar_item['submenu'][ $parent_item_id ] ) ) {

        foreach( $default_sidebar_item['submenu'][ $parent_item_id ] as $child_menu ) {

          $child_item_id = $child_menu[2];

          $add_meta_data = array(
            'item_type' => 'default',
            'item_default_type' => 'submenu',
            'item_default_id' => $child_item_id,
            'item_default_parent_id' => $parent_item_id,
            'item_link_title' => $child_menu[0],
          );

          $child_post_id = self::add_post( array( 'post_status' => 'draft' , 'menu_order' => $menu_order , 'post_parent' => $parent_post_id ) , $add_meta_data );

          $menu_order++;

          if ( empty( $child_post_id ) ) {

            MywpHelper::error_not_found_message( '$child_post_id' , $called_text );

            continue;

          } elseif( is_wp_error( $child_post_id ) ) {

            MywpHelper::error_message( $child_post_id->get_error_message() , $called_text );

            continue;

          }

        }

      }

    }

  }

  private static function print_item( $item = false , $find_parent = 0 ) {

    if( empty( $item ) or ! is_object( $item ) ) {

      return false;

    }

    $find_parent = intval( $find_parent );

    if( $find_parent != $item->item_parent ) {

      return false;

    }

    ?>

    <div class="setting-screen-sidebar-item item-id-<?php echo esc_attr( $item->ID ); ?>">

      <?php self::print_item_header( $item ); ?>

      <?php self::print_item_content( $item ); ?>

      <?php do_action( 'mywp_setting_admin_sidebar_print_item' , $item , $find_parent ); ?>

    </div>

    <?php

  }

  private static function print_item_header( $item ) {

    $pre_title = apply_filters( 'mywp_setting_admin_sidebar_print_item_header_pre_title' , '' , $item );

    ?>

    <div class="item-header">

      <a href="javascript:void(0);" class="item-active-toggle">&nbsp;</a>

      <div class="item-title-wrap">

        <?php if( MywpDeveloper::is_debug() ) : ?>

          [<?php echo $item->ID; ?>]

        <?php endif; ?>

        <?php if( !empty( $pre_title ) ) : ?>

          <?php echo $pre_title; ?>

        <?php else : ?>

          <?php if( !empty( $item->item_icon_img ) ) : ?>

            <img src="<?php echo esc_attr( $item->item_icon_img ); ?>" />

          <?php elseif( !empty( $item->item_icon_class ) or !empty( $item->item_icon_style ) ) : ?>

            <?php $dashicon_class = ''; ?>

            <?php if( strpos( $item->item_icon_class , 'dashicons-' ) !== false ) : ?>

              <?php $dashicon_class = 'dashicons'; ?>

            <?php endif; ?>

            <span class="<?php echo $dashicon_class; ?> <?php echo esc_attr( $item->item_icon_class ); ?>" style="<?php echo esc_attr( $item->item_icon_style ); ?>"></span>

          <?php endif; ?>

          <?php if( in_array( $item->item_type , array( 'default' , 'link' ) ) ) : ?>

            <span class="item-title"><?php echo strip_tags( strip_shortcodes( $item->item_link_title ) ); ?></span>

          <?php endif; ?>

          <?php if( $item->item_type == 'custom' ) : ?>

            <span class="item-title"><?php echo wp_html_excerpt( $item->item_custom_html , 20 ); ?></span>
            <span class="item-default-title"><?php _e( 'Custom Html' , 'mywp' ); ?></span>

          <?php elseif( $item->item_type == 'link' ) : ?>

            <span class="item-default-title"><?php _e( 'Custom Link' ); ?></span>

          <?php elseif( !empty( $item->item_default_title ) ) : ?>

            <span class="item-default-title"><?php echo $item->item_default_title; ?></span>

          <?php endif; ?>

        <?php endif; ?>

        <span class="spinner"></span>

      </div>

    </div>

    <?php

  }

  private static function print_item_content( $item ) {

    $item_type = $item->item_type;

    ?>

    <div class="item-content item-type-<?php echo esc_attr( $item_type ); ?>">

      <div class="item-content-fields">

        <?php self::print_item_content_field( 'id' , $item->ID , $item ); ?>
        <?php self::print_item_content_field( 'item_type' , $item->item_type , $item ); ?>
        <?php self::print_item_content_field( 'menu_order' , $item->menu_order , $item ); ?>
        <?php self::print_item_content_field( 'item_parent' , $item->item_parent , $item ); ?>
        <?php self::print_item_content_field( 'item_default_type' , $item->item_default_type , $item ); ?>
        <?php self::print_item_content_field( 'item_default_id' , $item->item_default_id , $item ); ?>
        <?php self::print_item_content_field( 'item_default_parent_id' , $item->item_default_parent_id , $item ); ?>

        <?php do_action( 'mywp_setting_admin_sidebar_print_item_content' , $item ); ?>

        <?php if( $item_type == 'default' ) : ?>

          <div class="item-content-hidden-fields">

            <?php self::print_item_content_field( 'item_capability' , '' , $item ); ?>
            <?php self::print_item_content_field( 'item_custom_html' , '' , $item ); ?>

            <?php self::print_item_content_field( 'item_li_class' , '' , $item ); ?>
            <?php self::print_item_content_field( 'item_li_id' , '' , $item ); ?>

            <?php self::print_item_content_field( 'item_link_class' , '' , $item ); ?>
            <?php self::print_item_content_field( 'item_link_id' , '' , $item ); ?>
            <?php self::print_item_content_field( 'item_link_url' , '' , $item ); ?>
            <?php self::print_item_content_field( 'item_link_attr' , '' , $item ); ?>

            <?php self::print_item_content_field( 'item_icon_class' , '' , $item ); ?>
            <?php self::print_item_content_field( 'item_icon_id' , '' , $item ); ?>
            <?php self::print_item_content_field( 'item_icon_title' , '' , $item ); ?>
            <?php self::print_item_content_field( 'item_icon_style' , '' , $item ); ?>
            <?php self::print_item_content_field( 'item_icon_img' , '' , $item ); ?>

          </div>

          <table class="form-table">
            <tbody>
              <tr>
                <th><?php _e( 'Menu Title' ); ?></th>
                <td>
                  <?php self::print_item_content_field( 'item_link_title' , $item->item_link_title , $item ); ?>
                </td>
              </tr>
            </tbody>
          </table>
          <table class="form-table">
            <tbody>
              <tr>
                <th><?php _e( 'Capability' , 'mywp' ); ?></th>
                <td>
                  <code><?php echo $item->item_capability; ?></code>
                  <?php self::print_item_content_field_user_role_group( $item->item_capability ); ?>
                </td>
              </tr>
              <tr>
                <th><?php _e( 'Link URL' , 'mywp' ); ?></th>
                <td>
                  <a href="<?php echo esc_url( $item->item_link_url ); ?>"><?php echo $item->item_link_url; ?></a>
                </td>
              </tr>
              <tr>
                <th><?php _e( 'LI class' , 'mywp' ); ?></th>
                <td>
                  <?php echo $item->item_li_class; ?>
                </td>
              </tr>
              <tr>
                <th><?php _e( 'LI id' , 'mywp' ); ?></th>
                <td>
                  <?php echo $item->item_li_id; ?>
                </td>
              </tr>
              <tr>
                <th><?php _e( 'Link class' , 'mywp' ); ?></th>
                <td>
                  <?php echo $item->item_link_class; ?>
                </td>
              </tr>
              <tr>
                <th><?php _e( 'Link id' , 'mywp' ); ?></th>
                <td>
                  <?php echo $item->item_link_id; ?>
                </td>
              </tr>
              <tr>
                <th><?php _e( 'Icon class' , 'mywp' ); ?></th>
                <td>
                  <?php echo $item->item_icon_class; ?>
                </td>
              </tr>
            </tbody>
          </table>

        <?php elseif( $item_type == 'link' ) : ?>

          <div class="item-content-hidden-fields">

            <?php self::print_item_content_field( 'item_custom_html' , '' , $item ); ?>

          </div>

          <table class="form-table">
            <tbody>
              <tr>
                <th><?php _e( 'Menu Title' ); ?></th>
                <td>
                  <?php self::print_item_content_field( 'item_link_title' , $item->item_link_title , $item ); ?>
                </td>
              </tr>
              <tr>
                <th><?php _e( 'Link URL' , 'mywp' ); ?></th>
                <td>
                  <?php self::print_item_content_field( 'item_link_url' , $item->item_link_url , $item ); ?>
                </td>
              </tr>
              <tr>
                <th><?php _e( 'Link Attributes' , 'mywp' ); ?></th>
                <td>
                  <?php self::print_item_content_field( 'item_link_attr' , $item->item_link_attr , $item ); ?>
                </td>
              </tr>
              <tr>
                <th><?php _e( 'Capability' , 'mywp' ); ?></th>
                <td>
                  <?php self::print_item_content_field( 'item_capability' , $item->item_capability , $item ); ?>
                </td>
              </tr>
            </tbody>
          </table>
          <table class="form-table">
            <tbody>
              <tr>
                <th><?php _e( 'LI class' , 'mywp' ); ?></th>
                <td>
                  <?php self::print_item_content_field( 'item_li_class' , $item->item_li_class , $item ); ?>
                </td>
              </tr>
              <tr>
                <th><?php _e( 'LI id' , 'mywp' ); ?></th>
                <td>
                  <?php self::print_item_content_field( 'item_li_id' , $item->item_li_id , $item ); ?>
                </td>
              </tr>
              <tr>
                <th><?php _e( 'Link class' , 'mywp' ); ?></th>
                <td>
                  <?php self::print_item_content_field( 'item_link_class' , $item->item_link_class , $item ); ?>
                </td>
              </tr>
              <tr>
                <th><?php _e( 'Link id' , 'mywp' ); ?></th>
                <td>
                  <?php self::print_item_content_field( 'item_link_id' , $item->item_link_id , $item ); ?>
                </td>
              </tr>
              <tr>
                <th><?php _e( 'Icon class' , 'mywp' ); ?></th>
                <td>
                  <?php self::print_item_content_field( 'item_icon_class' , $item->item_icon_class , $item ); ?>
                </td>
              </tr>
              <tr>
                <th><?php _e( 'Icon id' , 'mywp' ); ?></th>
                <td>
                  <?php self::print_item_content_field( 'item_icon_id' , $item->item_icon_id , $item ); ?>
                </td>
              </tr>
              <tr>
                <th><?php _e( 'Icon img' , 'mywp' ); ?></th>
                <td>
                  <?php self::print_item_content_field( 'item_icon_img' , $item->item_icon_img , $item ); ?>
                </td>
              </tr>
              <tr>
                <th><?php _e( 'Icon style' , 'mywp' ); ?></th>
                <td>
                  <?php self::print_item_content_field( 'item_icon_style' , $item->item_icon_style , $item ); ?>
                </td>
              </tr>
              <tr>
                <th><?php _e( 'Icon html' , 'mywp' ); ?></th>
                <td>
                  <?php self::print_item_content_field( 'item_icon_title' , $item->item_icon_title , $item ); ?>
                </td>
              </tr>
            </tbody>
          </table>

        <?php elseif( $item_type == 'custom' ) : ?>

          <div class="item-content-hidden-fields">

            <?php self::print_item_content_field( 'item_link_class' , '' , $item ); ?>
            <?php self::print_item_content_field( 'item_link_id' , '' , $item ); ?>
            <?php self::print_item_content_field( 'item_link_title' , '' , $item ); ?>
            <?php self::print_item_content_field( 'item_link_url' , '' , $item ); ?>
            <?php self::print_item_content_field( 'item_link_attr' , '' , $item ); ?>

            <?php self::print_item_content_field( 'item_icon_class' , '' , $item ); ?>
            <?php self::print_item_content_field( 'item_icon_id' , '' , $item ); ?>
            <?php self::print_item_content_field( 'item_icon_title' , '' , $item ); ?>
            <?php self::print_item_content_field( 'item_icon_style' , '' , $item ); ?>
            <?php self::print_item_content_field( 'item_icon_img' , '' , $item ); ?>

          </div>

          <table class="form-table">
            <tbody>
              <tr>
                <th><?php _e( 'Custom Html' , 'mywp' ); ?></th>
                <td>
                  <?php self::print_item_content_field( 'item_custom_html' , $item->item_custom_html , $item ); ?>
                </td>
              </tr>
            </tbody>
          </table>
          <table class="form-table">
            <tbody>
              <tr>
                <th><?php _e( 'Capability' , 'mywp' ); ?></th>
                <td>
                  <?php self::print_item_content_field( 'item_capability' , $item->item_capability , $item ); ?>
                </td>
              </tr>
              <tr>
                <th><?php _e( 'LI class' , 'mywp' ); ?></th>
                <td>
                  <?php self::print_item_content_field( 'item_li_class' , $item->item_li_class , $item ); ?>
                </td>
              </tr>
              <tr>
                <th><?php _e( 'LI id' , 'mywp' ); ?></th>
                <td>
                  <?php self::print_item_content_field( 'item_li_id' , $item->item_li_id , $item ); ?>
                </td>
              </tr>
            </tbody>
          </table>

        <?php else : ?>

          <?php do_action( 'mywp_setting_admin_sidebar_print_item_content_item_type' , $item , $item_type ); ?>

        <?php endif; ?>

        <a href="javascript:void(0);" class="item-update button button-primary"><?php _e( 'Update' ); ?></a>

        <a href="javascript:void(0);" class="item-remove button button-secondary button-caution"><span class="dashicons dashicons-no-alt"></span> <?php _e( 'Remove' ); ?></a>

        <span class="spinner"></span>

      </div>

      <p class="child-menu-title"><?php _e( 'Child Menus' , 'mywp' ); ?></p>

      <div class="child-menus sortable-items">

        <?php $child_sidebar_items = self::find_items_to_parent_id( $item->ID ); ?>

        <?php if( !empty( $child_sidebar_items ) ) : ?>

          <?php foreach( $child_sidebar_items as $key => $sub_item ) : ?>

            <?php self::print_item( $sub_item , $sub_item->item_parent ); ?>

          <?php endforeach; ?>

        <?php endif; ?>

      </div>

    </div>

    <?php

  }

  private static function get_user_roles() {

    if( !empty( self::$user_roles ) ) {

      return self::$user_roles;

    }

    self::$user_roles = MywpApi::get_all_user_roles();

    return self::$user_roles;

  }

  private static function print_item_content_field( $field_name = false , $value = '' , $item = false , $args = false ) {

    if( empty( $field_name ) or ! is_object( $item ) ) {

      return false;

    }

    $field_name = strip_tags( $field_name );

    if( $field_name == 'id' ) {

      printf( '<input type="hidden" class="id" value="%s" />' , esc_attr( $value ) );

    } elseif( $field_name == 'item_type' ) {

      printf( '<input type="hidden" class="item_type" value="%s" placeholder="default" />' , esc_attr( $value ) );

    } elseif( $field_name == 'menu_order' ) {

      printf( '<input type="hidden" class="menu_order" value="%d" placeholder="0" />' , esc_attr( $value ) );

    } elseif( $field_name == 'item_parent' ) {

      printf( '<input type="hidden" class="post_parent" value="%d" placeholder="0" />' , esc_attr( $value ) );

    } elseif( $field_name == 'item_default_type' ) {

      printf( '<input type="hidden" class="item_default_type" value="%s" placeholder="menu" />' , esc_attr( $value ) );

    } elseif( $field_name == 'item_default_id' ) {

      printf( '<input type="hidden" class="item_default_id" value="%s" placeholder="%s" />' , esc_attr( $value ) , esc_attr( 'index.php' ) );

    } elseif( $field_name == 'item_default_parent_id' ) {

      printf( '<input type="hidden" class="item_default_parent_id" value="%s" placeholder="%s" />' , esc_attr( $value ) , esc_attr( 'index.php' ) );

    } elseif( $field_name == 'item_capability' ) {

      printf( '<input type="text" class="item_capability large-text" value="%s" placeholder="%s" />' ,  esc_attr( $value ) , esc_attr( 'read' ) );

      self::print_item_content_field_user_role_group( $value );

    } elseif( $field_name == 'item_custom_html' ) {

      printf( '<textarea class="item_custom_html large-text" placeholder="%s">%s</textarea>' , esc_attr( '<div class="" style="">Custom Html</div>...' ) , $value );

    } elseif( $field_name == 'item_li_class' ) {

      printf( '<input type="text" class="item_li_class large-text" value="%s" placeholder="%s" />' , esc_attr( $value ) , esc_attr( 'sidebar-item-li-class' ) );

    } elseif( $field_name == 'item_li_id' ) {

      printf( '<input type="text" class="item_li_id large-text" value="%s" placeholder="%s" />' , esc_attr( $value ) , esc_attr( 'sidebar-item-li-id' ) );

    } elseif( $field_name == 'item_link_class' ) {

      printf( '<input type="text" class="item_link_class large-text" value="%s" placeholder="%s" />' , esc_attr( $value ) , esc_attr( 'sidebar-item-link-class' ) );

    } elseif( $field_name == 'item_link_id' ) {

      printf( '<input type="text" class="item_link_id large-text" value="%s" placeholder="%s" />' , esc_attr( $value ) , esc_attr( 'sidebar-item-link-id' ) );

    } elseif( $field_name == 'item_link_url' ) {

      printf( '<input type="text" class="item_link_url large-text" value="%s" placeholder="%s" />' , esc_attr( $value ) , esc_url( do_shortcode( '[mywp_url]' ) ) );

    } elseif( $field_name == 'item_link_title' ) {

      printf( '<input type="text" class="item_link_title large-text" value="%s" placeholder="%s" />' , esc_attr( $value ) , esc_attr( $item->item_default_title ) );

    } elseif( $field_name == 'item_link_attr' ) {

      printf( '<input type="text" class="item_link_attr large-text" value="%s" placeholder="%s" />' , esc_attr( $value ) , esc_attr( ' target="_blank"' ) );

    } elseif( $field_name == 'item_icon_class' ) {

      printf( '<input type="text" class="item_icon_class large-text" value="%s" placeholder="%s" />' , esc_attr( $value ) , esc_attr( 'sidebar-item-icon-class' ) );

    } elseif( $field_name == 'item_icon_id' ) {

      printf( '<input type="text" class="item_icon_id large-text" value="%s" placeholder="%s" />' , esc_attr( $value ) , esc_attr( 'sidebar-item-icon-id' ) );

    } elseif( $field_name == 'item_icon_img' ) {

      printf( '<input type="text" class="item_icon_img large-text" value="%s" placeholder="%s" />' , esc_attr( $value ) , esc_attr( esc_url( do_shortcode( '[mywp_url]' ) . '/icon.png' ) ) );

    } elseif( $field_name == 'item_icon_style' ) {

      printf( '<input type="text" class="item_icon_style large-text" value="%s" placeholder="%s" />' , esc_attr( $value ) , esc_attr( 'background: #000; color: #fff;' ) );

    } elseif( $field_name == 'item_icon_title' ) {

      printf( '<input type="text" class="item_icon_title large-text" value="%s" placeholder="%s" />' , esc_attr( $value ) , esc_attr( 'Icon Html' ) );

    } else {

      do_action( 'mywp_setting_admin_sidebar_print_item_content_field' , $field_name , $value , $item );

    }

  }

  private static function print_item_content_field_user_role_group( $capability ) {

    echo '<ul class="capability-roles">';

    $user_roles = self::get_user_roles();

    foreach( $user_roles as $role_group_name => $role ) {

      $role_has_class = '';

      if( empty( $capability ) or !empty( $role['capabilities'][ $capability ] ) ) {

        $role_has_class = ' role-can';

      }

      printf( '<li class="%s %s">%s</li>' , esc_attr( $role_group_name ) , esc_attr( $role_has_class ) , esc_attr( $role['label'] ) );

    }

    echo '</ul>';

  }

  private static function add_post( $args = array() , $post_metas = array() ) {

    global $wpdb;

    $default_args = array(
      'post_type' => 'mywp_admin_sidebar',
      'post_status' => 'publish',
      'post_parent' => 0,
    );

    $post = wp_parse_args( $args , $default_args );

    $parent_post_id = wp_insert_post( $post );

    if ( empty( $parent_post_id ) or is_wp_error( $parent_post_id ) ) {

      return $parent_post_id;

    }

    if( !empty( $post_metas ) ) {

      $add_meta_data = array();

      foreach( $post_metas as $meta_key => $meta_value ) {

        $meta_key = strip_tags( $meta_key );

        $add_meta_data[] = $wpdb->prepare( "(NULL, %d, %s, %s)" , intval( $parent_post_id ) , esc_sql( wp_unslash( $meta_key ) ) , maybe_serialize( wp_unslash( $meta_value ) ) );

      }

      $query = "INSERT INTO $wpdb->postmeta (meta_id, post_id, meta_key, meta_value) VALUES " . join( ',' , $add_meta_data );

      $wpdb->query( $query );

    }

    return $parent_post_id;

  }

  public static function mywp_current_setting_post_data_format_update( $formatted_data ) {

    $mywp_model = self::get_model();

    if( empty( $mywp_model ) ) {

      return $formatted_data;

    }

    $new_formatted_data = $mywp_model->get_initial_data();

    $new_formatted_data['advance'] = $formatted_data['advance'];

    if( !empty( $formatted_data['custom_menu_ui'] ) ) {

      $new_formatted_data['custom_menu_ui'] = true;

    }

    $current_setting_sidebar_items = self::get_current_setting_sidebar_item_posts();

    if( empty( $current_setting_sidebar_items ) ) {

      return false;

    }

    foreach( $current_setting_sidebar_items as $key => $current_setting_sidebar_item ) {

      $post_id = $current_setting_sidebar_item->ID;

      $post = MywpPostType::get_post( $post_id );

      if( empty( $post )  or ! is_object( $post ) or $post->post_type != 'mywp_admin_sidebar' ) {

        continue;

      }

      $post = array(
        'ID' => $post_id,
        'post_status' => 'publish',
      );

      wp_update_post( $post );

    }

    return $new_formatted_data;

  }

  public static function mywp_current_setting_before_post_data_action_remove( $validated_data ) {

    if( empty( $validated_data['remove'] ) ) {

      return false;

    }

    $current_setting_sidebar_items = self::get_current_setting_sidebar_item_posts();

    if( empty( $current_setting_sidebar_items ) ) {

      return false;

    }

    foreach( $current_setting_sidebar_items as $key => $current_setting_sidebar_item ) {

      $post_id = $current_setting_sidebar_item->ID;

      $post = MywpPostType::get_post( $post_id );

      if( empty( $post )  or ! is_object( $post ) or $post->post_type != 'mywp_admin_sidebar' ) {

        continue;

      }

      wp_delete_post( $post_id );

    }

  }

}

MywpSettingScreenAdminSidebar::init();

endif;
