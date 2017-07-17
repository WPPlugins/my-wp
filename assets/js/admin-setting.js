jQuery(document).ready(function($){

  $('.mywp_form').on('submit', function() {

    $(this).find('.submit .spinner').css('visibility', 'visible');

  });

  $('#select-advance-setting-toggle').on('click', function() {

    var $setting_screen_advance = $('#setting-screen-advance');
    var $setting_screen_advance_check = $setting_screen_advance.find('#select-advance-setting-check');
    var toggle = parseInt( $setting_screen_advance_check.val() );

    if( toggle ) {

      $setting_screen_advance_check.val( '0' );
      $setting_screen_advance.removeClass( 'active' );

    } else {

      $setting_screen_advance_check.val( '1' );
      $setting_screen_advance.addClass( 'active' );

    }

    return false;

  });

  $('#select-advance-setting-check').on('change', function() {

    var $setting_screen_advance = $('#setting-screen-advance');

    if( $(this).prop('checked') ) {

      $setting_screen_advance.addClass( 'active' );

    } else {

      $setting_screen_advance.removeClass( 'active' );

    }

  });

  $('#setting-screen-select-post-type').on('change', function() {

    $(this).parent().find('.spinner').css('visibility', 'visible');

    var $selected = $(this).find('option:selected');

    var url = $selected.data('post_type_url');

    if( ! url ) {

      return false;

    }

    $(location).attr('href', url);

  });

  $('#setting-screen-select-taxonomy').on('change', function() {

    $(this).parent().find('.spinner').css('visibility', 'visible');

    var $selected = $(this).find('option:selected');

    var url = $selected.data('taxonomy_url');

    if( ! url ) {

      return false;

    }

    $(location).attr('href', url);

  });

  $('#meta-box-screen-refresh-button').on('click', function() {

    var $button = $(this);
    var url = $button.prop('href');

    $.ajax({
      url: url,
      beforeSend: function( xhr ) {
        $button.parent().find('.dashicons-update').addClass('spin');
      }
    }).done( function( xhr ) {

      location.reload();

    }).fail( function( xhr ) {

      load_meta_box_error();

    });

    return false;

  });

  function load_meta_box_error() {

    $('#meta-box-screen-refresh .dashicons-update').removeClass('spin');

    alert( mywp_admin_setting.error_try_again );

  }

  function render_meta_box_management() {

    $('#meta-boxes-table tbody tr').each( function ( index , el ) {

      var $tr = $(el);
      var action = $tr.find('.meta-box-action-select').val();
      var disabled = false;

      if( action == 'remove' || action == 'hide' ) {

        disabled = true;

      }

      $tr.find('.meta-box-change-title').prop('disabled', disabled);

      if( disabled ) {

        $tr.find('.meta-box-change-title').addClass('disabled');

      } else {

        $tr.find('.meta-box-change-title').removeClass('disabled');

      }

    });

  }

  render_meta_box_management();

  $('#meta-boxes-table .meta-box-action-select').on('change', function() {

    render_meta_box_management();

  });

  function meta_box_bulk_action( action = false ) {

    var defined_action = false;

    if( action == 'remove' || action == 'hide' || action == '' ) {

      defined_action = true;

    }

    if( ! defined_action ) {

      return false;

    }

    $('#meta-boxes-table tbody tr').each( function ( index , el ) {

      var $tr = $(el);

      $tr.find('.meta-box-action-select').val( action );

    });

    render_meta_box_management();

  }

  $('#meta-box-bulk-actions #meta-box-bulk-action-publish').on('click', function() {

    meta_box_bulk_action( '' );

  });

  $('#meta-box-bulk-actions #meta-box-bulk-action-remove').on('click', function() {

    meta_box_bulk_action( 'remove' );

  });

  $('#meta-box-bulk-actions #meta-box-bulk-action-hide').on('click', function() {

    meta_box_bulk_action( 'hide' );

  });

});
