"use strict";

jQuery(function ($) {
    $('#woocs_auto_switcher_skin').on("change", function () {
        var woocs_side_switcher_skin = $(this).val();
        if (woocs_side_switcher_skin == 'roll_blocks') {
            $('.woocs_roll_blocks_width').show(200);
        } else {
            $('.woocs_roll_blocks_width').hide(200);
        }
    });


    $('#woocs_is_multiple_allowed').on("change", function () {
        var woocs_is_multiple_allowed = parseInt($(this).val(), 10);
        var woocs_is_fixed_enabled = parseInt($('#woocs_is_fixed_enabled').val(), 10);
        //***
        if (woocs_is_multiple_allowed) {
            $('input[name=woocs_is_fixed_enabled]').parents('tr').show(200);
            $('input[name=woocs_is_fixed_coupon]').parents('tr').show(200);
            $('input[name=woocs_is_fixed_shipping]').parents('tr').show(200);
            if (woocs_is_fixed_enabled) {
                $('input[name=woocs_force_pay_bygeoip_rules]').parents('tr').show(200);
            }
        } else {
            $('input[name=woocs_is_fixed_enabled]').parents('tr').hide(200);
            $('input[name=woocs_is_fixed_coupon]').parents('tr').hide(200);
            $('input[name=woocs_is_fixed_shipping]').parents('tr').hide(200);
            $('input[name=woocs_force_pay_bygeoip_rules]').parents('tr').hide(200);
        }
    });



    //***

    init_switcher23();

    document.addEventListener('woocs_blind_option', function (e) {
        if (parseInt(e.detail.value, 10)) {
            alert(woocs_lang.blind_option);
        }

        //***

        if (e.detail.name === 'woocs_is_fixed_enabled') {
            if (parseInt(e.detail.value, 10)) {
                jQuery('input[name=woocs_force_pay_bygeoip_rules]').parents('tr').show(200);
            } else {
                jQuery('input[name=woocs_force_pay_bygeoip_rules]').parents('tr').hide(200);
            }
        }



    });


    document.addEventListener('woocs_is_auto_switcher', function (e) {
        if (parseInt(e.detail.value, 10)) {
            $('#tabs-6 .form-table tbody > tr').not(':first').show(200);
            $('#tabs-6 .form-table').not(':first').show(200);
            $('#tabs-6 .demo-img-1').show(200);
        } else {
            $('#tabs-6 .form-table tbody > tr').not(':first').hide(200);
            $('#tabs-6 .form-table').not(':first').hide(200);
            $('#tabs-6 .demo-img-1').hide(200);
        }
    });

    //***

    $('.woocs-select-all-in-select').on('click', function () {
        $(this).parents('td').find('select option').attr('selected', true);
        $(this).parents('td').find('select').trigger('change');
        return false;
    });

    $('.woocs-clear-all-in-select').on('click', function () {
        $(this).parents('td').find('select option').attr('selected', false);
        $(this).parents('td').find('select').trigger('change');
        return false;
    });

    //***
    //https://gromo.github.io/jquery.scrollbar/demo/advanced.html
    jQuery('.scrollbar-external').scrollbar({
        autoScrollSize: false,
        scrollx: $('.external-scroll_x'),
        scrolly: $('.external-scroll_y')
    });


});

jQuery(function ($) {

    woocs_init_tabs();

    jQuery("#woocs_list").sortable({
        handle: '.woocs_settings_move img'
    });

    jQuery('#woocs_add_currency').on('click', function () {
        jQuery('#woocs_list').append(jQuery('#woocs_item_tpl').html());
        return false;
    });

    jQuery('body').on('click', '.woocs_del_currency', function () {
        jQuery(this).parents('tr').hide(220, function () {
            jQuery(this).remove();
        });
        return false;
    });

    jQuery('body').on('click', '.woocs_is_etalon', function () {
        jQuery('.woocs_is_etalon').next('input[type=hidden]').val(0);
        jQuery('.woocs_is_etalon').prop('checked', 0);
        jQuery(this).next('input[type=hidden]').val(1);
        jQuery(this).prop('checked', 1);
        jQuery(this).parents('tr').find("input[name='woocs_rate[]']").val(1);
        jQuery(this).parents('tr').find("input[name='woocs_rate_plus[]']").val('');
        //instant save
        var currency_name = jQuery(this).parents('tr').find('input[name="woocs_name[]"]').val();
        if (currency_name.length) {
            woocs_show_stat_info_popup(woocs_lang.loading + ' ...');
            var data = {
                action: "woocs_save_etalon",
                currency_name: currency_name
            };
            jQuery.post(ajaxurl, data, function (request) {
                try {
                    request = JSON.parse(request);

                    jQuery.each(request, function (index, value) {
                        var elem = jQuery('input[name="woocs_name[]"]').filter(function () {
                            return this.value.toUpperCase() == index;
                        });

                        if (elem) {
                            jQuery(elem).parents('tr').find('input[name="woocs_rate[]"]').val(value);
                            jQuery(elem).parents('tr').find('input[name="woocs_rate[]"]').text(value);
                        }
                    });

                    woocs_hide_stat_info_popup();
                    woocs_show_info_popup(woocs_lang.save_changes, 1999);
                } catch (e) {
                    woocs_hide_stat_info_popup();
                    alert('Request error! Try later or another agregator!');
                }
            });
        }

        return true;
    });


    jQuery('body').on('change', '.woocs_flag_input', function ()
    {
        jQuery(this).next('a.woocs_flag').find('img').attr('src', jQuery(this).val());
    });

    jQuery('body').on('click', '.woocs_flag', function ()
    {
        var input_object = jQuery(this).prev('input[type=hidden]');
        window.send_to_editor = function (html)
        {
            woocs_insert_html_in_buffer(html);
            var imgurl = jQuery('#woocs_buffer').find('a').eq(0).attr('href');
            woocs_insert_html_in_buffer("");
            jQuery(input_object).val(imgurl);
            jQuery(input_object).trigger('change');
            tb_remove();
        };
        tb_show('', 'media-upload.php?post_id=0&type=image&TB_iframe=true');

        return false;
    });

    jQuery('body').on('click', '.woocs_get_fresh_rate', function () {
        var currency_name = jQuery(this).parents('tr').find('input[name="woocs_name[]"]').val();
        var _this = this;
        jQuery(_this).parent().find('input[name="woocs_rate[]"]').val(woocs_lang.loading.toLowerCase() + ' ...');
        var data = {
            action: "woocs_get_rate",
            currency_name: currency_name
        };
        jQuery.post(ajaxurl, data, function (value) {
            jQuery(_this).parent().find('input[name="woocs_rate[]"]').val(value);
        });

        return false;
    });

    //***

    $('body').on('click', '.label.container', function () {
        $(this).find('input[type=radio]').trigger('click');
        return true;
    });

    //loader
    jQuery(".woocs-admin-preloader").fadeOut("slow");

});


//*********************

function woocs_init_tabs() {
    document.querySelectorAll('#tabs nav ul li a').forEach(function (tab) {
        tab.addEventListener('click', function (e) {
            e.preventDefault();
            woocs_click_tab(this);
            return false;
        });
    });
}

function woocs_click_tab(_this) {
    document.querySelectorAll('#tabs nav ul li').forEach(function (a) {
        a.classList.remove('tab-current');
    });

    document.querySelectorAll('#tabs .content-wrap section').forEach(function (tp) {
        tp.classList.remove('content-current');
    });


    _this.closest('li').classList.add('tab-current');
    document.querySelector(_this.getAttribute('href')).classList.add('content-current');
}

function woocs_update_all_rates() {
    jQuery('.woocs_is_etalon:checked').trigger('click');
}

function woocs_add_money_sign2() {
    woocs_click_tab(document.querySelector('#tabs nav a[href="#tabs-2"]'));
    //document.querySelector('#tabs nav a[href="#tabs-2"]').onclick();
    jQuery('#tabs-2').find('#woocs_customer_signs').focus();
    jQuery('#woocs_customer_signs').scroll();
}

function init_switcher23(container = '') {
    Array.from(document.querySelectorAll(container + ' .switcher23')).forEach((button) => {
        button.addEventListener('click', function () {

            if (this.value > 0) {
                this.value = 0;
                this.previousSibling.value = 0;
                this.removeAttribute('checked');
            } else {
                this.value = 1;
                this.previousSibling.value = 1;
                this.setAttribute('checked', 'checked');
            }

            if (this.previousSibling.getAttribute('name') === 'woocs_shop_is_cached') {
                if (parseInt(this.value, 10)) {
                    jQuery('input[name=woocs_shop_is_cached_preloader]').parents('tr').show(200);
                } else {
                    jQuery('input[name=woocs_shop_is_cached_preloader]').parents('tr').hide(200);
                }
            }

            //Trigger the event
            if (this.getAttribute('data-event').length > 0) {
                document.dispatchEvent(new CustomEvent(this.getAttribute('data-event'), {detail: {
                        name: this.previousSibling.getAttribute('name'),

                        value: parseInt(this.value, 10)
                    }}));
            }


            return true;
        });
    });
}
function woocs_update_profiles_data(key, title, countries) {

    var data = {
        action: "woocs_update_profiles_data",
        countries: countries,
        key: key,
        title: title
    };
    jQuery.post(ajaxurl, data, function (value) {
        value = JSON.parse(value);
        var info = jQuery('.woocs_geoip_profile_info');
        if (value.info) {
            jQuery(info).text(value.info);
            jQuery(info).show();
            setTimeout(function () {
                jQuery(info).text("");
                jQuery(info).hide();
            }, 3000);
        }
        var select = jQuery('select.woocs_geoip_profile_countries');
        var option = jQuery(select).find('option[data-key="' + value.key + '"]');
        if (jQuery(option).length) {
            jQuery(option).replaceWith(value.option);
        } else {
            jQuery(select).append(value.option);
        }
        jQuery(select).trigger('change');

        document.querySelector('select.woocs_geoip_profile_countries option:last-child').setAttribute('selected', '');
    });

}
function woocs_add_geoip_profile() {
    var countries = jQuery('#woocs_geo_rules_profile_countries').val();
    var title = jQuery('input[name="woocs_geo_rules_profile_title"]').val();
    if (!title) {
        alert(woocs_lang.set_title);
    }
    if (title && countries) {
        woocs_update_profiles_data(0, title, countries);
    }
}
function woocs_update_geoip_profile() {
    var countries = jQuery('#woocs_geo_rules_profile_countries').val();
    var title = jQuery('input[name="woocs_geo_rules_profile_title"]').val();
    var key = jQuery('input[name="woocs_geo_rules_profile_key"]').val();
    if (!title) {
        alert(woocs_lang.set_title);
    }
    if (title && countries && key) {
        woocs_update_profiles_data(key, title, countries);
    }
}
function woocs_edit_geoip_profile() {
    var selected = jQuery('select.woocs_geoip_profile_countries option:selected');
    var key = selected.data('key');
    var value = selected.val();
    var title = selected.text();

    jQuery('input[name="woocs_geo_rules_profile_title"]').val(title);
    jQuery('input[name="woocs_geo_rules_profile_key"]').val(key);
    if (value = JSON.parse(value)) {
        jQuery('#woocs_geo_rules_profile_countries').val(value);
        jQuery('.woocs_update_geoip_profile').show();
        jQuery('#woocs_geo_rules_profile_countries').trigger('change');
        jQuery('#woocs_geo_rules_profile_countries').trigger("chosen:updated");
    }
}

function woocs_delete_geoip_profile() {
    var selected = jQuery('select.woocs_geoip_profile_countries option:selected');
    var key = selected.data('key');
    var data = {
        action: "woocs_delete_profiles_data",
        key: key
    };
    if (confirm("Sure?")) {
        jQuery.post(ajaxurl, data, function (value) {
            var info = jQuery('.woocs_geoip_profile_info');
            if (value = JSON.parse(value)) {
                if (value.info) {
                    jQuery(info).text(value.info);
                    jQuery(info).show();
                    setTimeout(function () {
                        jQuery(info).text("");
                        jQuery(info).hide();
                    }, 3000);
                }

            }
            jQuery(selected).remove();
        });
    }
}
function woocs_cancel_geoip_profile() {
    jQuery('#woocs_geo_rules_profile_countries option').removeAttr('selected');
    jQuery('input[name="woocs_geo_rules_profile_title"]').val("");
    jQuery('input[name="woocs_geo_rules_profile_key"]').val("");
    jQuery('.woocs_update_geoip_profile').hide();
    jQuery('#woocs_geo_rules_profile_countries').trigger('change');
    jQuery('#woocs_geo_rules_profile_countries').trigger("chosen:updated");

}

function woocs_geoip_profile_to_rules() {
    var currency = jQuery('.woocs_profile_geoip_currency').val();
    var selected = jQuery('select.woocs_geoip_profile_countries option:selected');
    var value = selected.val();
    if (value = JSON.parse(value)) {
        jQuery('select[name="woocs_geo_rules[' + currency + '][]"]').val(value);
        jQuery('select[name="woocs_geo_rules[' + currency + '][]"]').trigger('change');
        jQuery('select[name="woocs_geo_rules[' + currency + '][]"]').trigger("chosen:updated");
        return false;
    }
}