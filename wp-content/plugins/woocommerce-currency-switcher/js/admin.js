"use strict";

(function ($, window) {

    jQuery(function () {
        jQuery('.form-table .forminp #woocommerce_currency').parents('tr').remove();
        jQuery('.form-table .forminp #woocommerce_currency_pos').parents('tr').remove();

    });


    document.addEventListener('woocs_admin_theme_id', function (e) {
        jQuery.post(ajaxurl, {
            action: "woocs_admin_theme_id",
            theme_id: parseInt(e.detail.value, 10)
        }, function () {
            window.location.reload();
        });
    });


})(jQuery, window);

jQuery(function ($) {

    jQuery('body').append('<div id="woocs_buffer" style="display: none;"></div>');

});


function woocs_insert_html_in_buffer(html) {
    jQuery('#woocs_buffer').html(html);
}
function woocs_get_html_from_buffer() {
    return jQuery('#woocs_buffer').html();
}

function woocs_show_info_popup(text, delay) {
    jQuery(".info_popup").text(text);
    jQuery(".info_popup").fadeTo(400, 0.9);
    window.setTimeout(function () {
        jQuery(".info_popup").fadeOut(400);
    }, delay);
}

function woocs_show_stat_info_popup(text) {
    jQuery(".info_popup").text(text);
    jQuery(".info_popup").fadeTo(400, 0.9);
}


function woocs_hide_stat_info_popup() {
    window.setTimeout(function () {
        jQuery(".info_popup").fadeOut(400);
    }, 500);
}
function woocs_auto_hide_color() {
    if (jQuery('#woocs_is_auto_switcher').val() == 0) {
        jQuery('#woocs_auto_switcher_color').parents('tr').hide();
        jQuery('#woocs_auto_switcher_hover_color').parents('tr').hide();
    }
}
woocs_auto_hide_color();

function woocs_check_api_key_field() {
    var aggregator = jQuery("select[name=woocs_currencies_aggregator]").val();
    var is_api = ['free_converter', 'fixer', 'currencylayer', 'openexchangerates'];
    let tr = document.querySelector('input[name=woocs_aggregator_key]').closest('tr');
    if (jQuery.inArray(aggregator, is_api) != -1) {
        jQuery(tr).show();
    } else {
        jQuery(tr).hide();
    }
}


woocs_check_api_key_field();
jQuery("select[name=woocs_currencies_aggregator]").change(function () {
    woocs_check_api_key_field();
});



function woocs_check_storage_type() {
    var storage = jQuery("#woocs_storage").val();

    if (jQuery.inArray(storage, ['memcached', 'redis']) !== -1) {
        jQuery("#woocs_storage_server").parents("tr").show();
        jQuery("#woocs_storage_port").parents("tr").show();
    } else {
        jQuery("#woocs_storage_server").parents("tr").hide();
        jQuery("#woocs_storage_port").parents("tr").hide();
    }
}

woocs_check_storage_type();
jQuery("#woocs_storage").change(function () {
    woocs_check_storage_type();
});



function woocs_set_cookie(name, value, days = 365) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

function woocs_get_cookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ')
            c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0)
            return c.substring(nameEQ.length, c.length);
    }
    return null;
}

