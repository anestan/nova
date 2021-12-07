jQuery(document).ready(function ($) {
    'use strict';
    $('.vi-ui.tabular.menu .item').vi_tab({
        history: true,
        historyType: 'hash'
    });
    $('.vi-ui.accordion').vi_accordion('refresh');
    $('.dropdown').not('#verified-type').dropdown({placeholder: ''});
    /*Default filters*/
    $('#wcpr-pagination-ajax').on('change', function (e) {
        if ($(this).prop('checked')) {
            $('.wcpr-default-filters').removeClass('wcpr-hidden-items');
        } else {
            $('.wcpr-default-filters').addClass('wcpr-hidden-items');
        }
    });

    if ($('#kt_coupons_select').prop('disabled') === true) {
        $('.kt-custom-coupon').hide();
        $('.kt-existing-coupons').hide();
    } else {
        if ($('#kt_coupons_select').val() === 'kt_generate_coupon') {
            $('.kt-custom-coupon').show();
            $('.kt-existing-coupons').hide();
        } else {
            $('.kt-custom-coupon').hide();
            $('.kt-existing-coupons').show();
        }
    }

    $('#kt_coupons_select').on('change', function () {
        if ($('#kt_coupons_select').val() === 'kt_generate_coupon') {
            $('.kt-custom-coupon').show();
            $('.kt-existing-coupons').hide();
        } else {
            $('.kt-custom-coupon').hide();
            $('.kt-existing-coupons').show();
        }
    });
    $(".category-search").select2({
        closeOnSelect: false,
        placeholder: "Please enter category title",
        ajax: {
            url: "admin-ajax.php?action=wcpr_search_cate",
            dataType: 'json',
            type: "GET",
            quietMillis: 50,
            delay: 250,
            data: function (params) {
                return {
                    keyword: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) {
            return markup;
        }, // let our custom formatter work
        minimumInputLength: 1
    });
    $(".exclude-addresses").select2({
        closeOnSelect: true,
        tags: true,
        createTag: function (params) {
            // Don't offset to create a tag if there is no @ symbol
            if (params.term.indexOf('@') === -1) {
                // Return null to disable tag creation
                return null;
            }

            return {
                id: params.term,
                text: params.term
            }
        }
    });
    $(".product-search").select2({
        closeOnSelect: false,
        placeholder: "Please fill in your product title",
        ajax: {
            url: "admin-ajax.php?action=wcpr_search_parent_product",
            dataType: 'json',
            type: "GET",
            quietMillis: 50,
            delay: 250,
            data: function (params) {
                return {
                    keyword: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) {
            return markup;
        }, // let our custom formatter work
        minimumInputLength: 1
    });
    $(".wcpr-page-search").select2({
        allowClear: true,
        closeOnSelect: true,
        placeholder: "Please fill in your page title",
        ajax: {
            url: "admin-ajax.php?action=wcpr_search_page",
            dataType: 'json',
            type: "GET",
            quietMillis: 50,
            delay: 250,
            data: function (params) {
                return {
                    keyword: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) {
            return markup;
        }, // let our custom formatter work
        minimumInputLength: 1
    });
    $(".coupon-search").select2({
        placeholder: "Type coupon code here",
        ajax: {
            url: "admin-ajax.php?action=wcpr_search_coupon",
            dataType: 'json',
            type: "GET",
            quietMillis: 50,
            delay: 250,
            data: function (params) {
                return {
                    keyword: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) {
            return markup;
        }, // let our custom formatter work
        minimumInputLength: 1
    });
    /*Color picker*/
    $('.color-picker').not('.verified-color').wpColorPicker().click(function () {
        $('.iris-picker').hide();
        $(this).closest('td').find('.iris-picker').show();
    });

    $('body').click(function () {
        $('.wp-picker-active').click();
    });
    $('.color-picker').click(function (event) {
        event.stopPropagation();
    });
    $('#reviews_display1').on('click', function () {
        $('.masonry-options').show();
        $('.default-options').hide();
    });
    $('#reviews_display2').on('click', function () {
        $('.masonry-options').hide();
        $('.default-options').show();
    });
    $('.wcpr-verified-badge-wrap').on('click', function () {
        $('.wcpr-verified-badge-wrap').removeClass('wcpr-verified-active-badge');
        $(this).addClass('wcpr-verified-active-badge');
        $('input[name="verified_badge"]').val($(this).data('class_name'));
    });
    $('#verified-type').dropdown({
        onChange: function (val) {
            switch (val) {
                case 'text':
                    $('.wcpr-verified-badge-wrap-wrap').hide();
                    $('input[name="verified_text"]').show();
                    break;
                case 'badge':
                    $('.wcpr-verified-badge-wrap-wrap').show();
                    $('input[name="verified_text"]').hide();
                    break;
                default:
                    $('.wcpr-verified-badge-wrap-wrap').hide();
                    $('input[name="verified_text"]').hide();
            }
        }
    });
    $('.verified-color').iris({
        change: function (event, ui) {
            $(this).parent().find('.color-picker').css({backgroundColor: ui.color.toString()});
            $('.verified-text').css({'color': ui.color.toString()});
            $('.wcpr-verified-badge-wrap').css({'color': ui.color.toString()});
        },
        hide: true,
        border: true
    }).click(function () {
        $('.iris-picker').hide();
        $(this).closest('td').find('.iris-picker').show();
    });

    /*preview email*/
    $('.preview-emails-html-overlay').on('click', function () {
        $('.preview-emails-html-container').addClass('preview-html-hidden');
    })
    $('.coupon-preview-emails-button').on('click', function () {
        let old_preview_text = $(this).html();
        $(this).html(woo_photo_reviews_params_admin.text_please_wait);
        let language = $(this).data('wcpr_language');
        $.ajax({
            url: woo_photo_reviews_params_admin.url,
            type: 'GET',
            dataType: 'JSON',
            data: {
                action: 'wcpr_preview_emails',
                email_type: 'coupon',
                heading: $('#heading' + language).val(),
                content: tinyMCE.get('content' + language) ? tinyMCE.get('content' + language).getContent() : $('#content' + language).val(),
            },
            success: function (response) {
                $('.coupon-preview-emails-button[data-wcpr_language="' + language + '"]').html(old_preview_text);
                if (response) {
                    $('.preview-emails-html').html(response.html);
                    $('.preview-emails-html-container').removeClass('preview-html-hidden');
                }
            },
            error: function (err) {
                $('.preview-emails-button').html(old_preview_text);
            }
        })
    })
    $('.reminder-preview-emails-button').on('click', function () {
        let button = $(this);
        let old_preview_text = button.html();
        button.html(woo_photo_reviews_params_admin.text_please_wait);
        let language = button.data('wcpr_language');
        $.ajax({
            url: woo_photo_reviews_params_admin.url,
            type: 'GET',
            dataType: 'JSON',
            data: {
                action: 'wcpr_preview_emails',
                anchor: $('#wcpr-reviews-anchor-link').val(),
                email_type: 'reminder',
                heading: $('#follow_up_email_heading' + language).val(),
                review_button: $('#button-review-now' + language).val(),
                review_button_bg_color: $('#button-review-now-bg-color').val(),
                review_button_color: $('#button-review-now-color').val(),
                product_image_width: $('#product-image-width').val(),
                content: tinyMCE.get('follow_up_email_content' + language) ? tinyMCE.get('follow_up_email_content' + language).getContent() : $('#follow_up_email_content' + language).val(),
            },
            success: function (response) {
                button.html(old_preview_text);
                if (response) {
                    $('.preview-emails-html').html(response.html);
                    $('.preview-emails-html-container').removeClass('preview-html-hidden');
                }
            },
            error: function (err) {
                button.html(old_preview_text);
            }
        })
    });
    /**
     * Start Get download key
     */
    jQuery('.villatheme-get-key-button').one('click', function (e) {
        let v_button = jQuery(this);
        v_button.addClass('loading');
        let data = v_button.data();
        let item_id = data.id;
        let app_url = data.href;
        let main_domain = window.location.hostname;
        main_domain = main_domain.toLowerCase();
        let popup_frame;
        e.preventDefault();
        let download_url = v_button.attr('data-download');
        popup_frame = window.open(app_url, "myWindow", "width=380,height=600");
        window.addEventListener('message', function (event) {
            /*Callback when data send from child popup*/
            let obj = JSON.parse(event.data);
            let update_key = '';
            let message = obj.message;
            let support_until = '';
            let check_key = '';
            if (obj['data'].length > 0) {
                for (let i = 0; i < obj['data'].length; i++) {
                    if (obj['data'][i].id == item_id && (obj['data'][i].domain == main_domain || obj['data'][i].domain == '' || obj['data'][i].domain == null)) {
                        if (update_key == '') {
                            update_key = obj['data'][i].download_key;
                            support_until = obj['data'][i].support_until;
                        } else if (support_until < obj['data'][i].support_until) {
                            update_key = obj['data'][i].download_key;
                            support_until = obj['data'][i].support_until;
                        }
                        if (obj['data'][i].domain == main_domain) {
                            update_key = obj['data'][i].download_key;
                            break;
                        }
                    }
                }
                if (update_key) {
                    check_key = 1;
                    jQuery('.villatheme-autoupdate-key-field').val(update_key);
                }
            }
            v_button.removeClass('loading');
            if (check_key) {
                jQuery('<p><strong>' + message + '</strong></p>').insertAfter(".villatheme-autoupdate-key-field");
                jQuery(v_button).closest('form').submit();
            } else {
                jQuery('<p><strong> Your key is not found. Please contact support@villatheme.com </strong></p>').insertAfter(".villatheme-autoupdate-key-field");
            }
        });
    });
    /**
     * End get download key
     */

    /*Select all product*/
    $('.wcpr-clear-all-product').on('click', function () {
        let select_field = $(this).parent().find('.product-search');
        if (select_field.length) {
            select_field.val(null).trigger('change');
        }
    })
    $('.wcpr-select-all-product').on('click', function () {
        let button = $(this);
        if (button.hasClass('loading')) {
            return;
        }
        let select_field = button.parent().find('.product-search');
        button.addClass('loading');
        $.ajax({
            url: woo_photo_reviews_params_admin.url,
            type: 'GET',
            dataType: 'JSON',
            data: {
                action: 'wcpr_select_all_products',
            },
            success: function (response) {
                if (response.length > 0) {
                    select_field.val(null).trigger('change');
                    let ids = [];
                    for (let i in response) {
                        let product_id = response[i].id;
                        ids.push(product_id);
                        if (select_field.find("option[value='" + product_id + "']").length == 0) {
                            let data = {
                                id: product_id,
                                text: response[i].text
                            };
                            let newOption = new Option(data.text, data.id, false, false);
                            select_field.append(newOption).trigger('change');
                        }
                    }
                    select_field.val(ids).trigger('change')
                }
            },
            error: function (err) {
                console.log(err);
            },
            complete: function () {
                button.removeClass('loading')
            }
        })
    });
    /*add field*/
    $('.wcpr-add-custom-field').on('click', function () {
        $('.wcpr-add-custom-field-container').append(woo_photo_reviews_params_admin.add_field_html);
    });
    $('body').on('click', '.wcpr-remove-custom-field', function () {
        if (confirm('Remove this field?')) {
            $(this).closest('tr').remove();
        }
    });
    $('.vi-ui.form').on('submit', function () {
        let empty = false;
        $('input[name="wcpr_custom_fields[name][]"]').each(function () {
            if (!$(this).val()) {
                $(this).focus();
                alert('Name can not be empty');
                empty = true;
                return false;
            }
        });

        if (empty) {
            return false;
        }
    });
    /*Phrases filter*/
    $('.add-phrases-filter-rule').on('click', function () {
        let clone = `<tr class="clone-source">
                        <td>
                            <input type="text" name="phrases_filter[from_string][]">
                        </td>
                         <td>
                            <div class="phrases-filter-sensitive-container">
                            <input type="checkbox" value="1" class="phrases-filter-sensitive">                            
                            <input type="hidden" class="phrases-filter-sensitive-value" value="" name="phrases_filter[sensitive][]">
                            </div>
                        </td>
                        <td>
                            <input type="text" name="phrases_filter[to_string][]"  placeholder="Leave blank to delete matches">
                        </td>
                        <td>
                            <button type="button" class="vi-ui button negative delete-phrases-filter-rule">
                                <i class="dashicons dashicons-trash "></i>
                            </button>
                        </td>
                    </tr>`;

        $('.phrases-filter tbody').append(clone);
    });

    $('body').on('change', '.phrases-filter-sensitive', function () {
        let $container = $(this).closest('.phrases-filter-sensitive-container');
        let $sensitive_value = $container.find('.phrases-filter-sensitive-value');
        let sensitive_value = $(this).prop('checked') ? 1 : '';
        $sensitive_value.val(sensitive_value);
    });
    $('body').on('click', '.delete-phrases-filter-rule', function () {
        if (confirm('Remove this item?')) {
            $(this).closest('.clone-source').remove();
        }
    });
    /*Secret key for chrome extension*/
    $('.wcpr-generate-secretkey').on('click', function () {
        var a = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890".split(""), b = [];
        for (let i = 0; i < 32; i++) {
            var j = (Math.random() * (a.length - 1)).toFixed(0);
            b[i] = a[j];
        }

        $('.wcpr-secret-key').val(b.join(""));
    });

    $('.wcpr-copy-secretkey').on('click', function () {
        let $container = $(this).closest('td');
        $container.find('.wcpr-secret-key').select();
        $container.find('.wcpr-copy-secretkey-success').remove();
        document.execCommand('copy', true);
        let $result_icon = $('<span class="wcpr-copy-secretkey-success dashicons dashicons-yes" title="Copied to Clipboard"></span>');
        $container.append($result_icon);
        $result_icon.fadeOut(10000);
        setTimeout(function () {
            $result_icon.remove();
        }, 10000);
    });
    $('.wcpr-button-save-settings-container').closest('form').on('submit', function () {
        $('.wcpr-button-save-settings-container').find('button[type="submit"]').addClass('loading');
    });
    /*Share reviews*/
    $(document).on('click', '.wcpr-share-reviews-remove', function () {
        if (confirm('Do you really want to remove this row?')) {
            let $button = $(this), $container = $button.closest('table');
            let $rows = $container.find('.wcpr-share-reviews-row');
            if ($rows.length > 1) {
                $button.closest('.wcpr-share-reviews-row').remove();
                share_reviews_recalculate_index()
            } else if ($rows.length === 1) {
                $rows.eq(0).find('.wcpr-share-reviews-products').val('').trigger('change');
            }
        }
    });
    $(document).on('click', '.wcpr-share-reviews-add', function () {
        let $button = $(this), $container = $button.closest('table');
        let $row = $container.find('.wcpr-share-reviews-row').last().clone();
        $row.find('.select2-container').remove();
        $row.find('.product-search').select2({
            closeOnSelect: false,
            placeholder: "Please fill in your product title",
            ajax: {
                url: "admin-ajax.php?action=wcpr_search_parent_product",
                dataType: 'json',
                type: "GET",
                quietMillis: 50,
                delay: 250,
                data: function (params) {
                    return {
                        keyword: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                cache: true
            },
            escapeMarkup: function (markup) {
                return markup;
            }, // let our custom formatter work
            minimumInputLength: 1
        }).val('').trigger('change');
        $container.find('tbody').append($row);
        share_reviews_recalculate_index();
    });

    function share_reviews_recalculate_index() {
        let index = 0;
        $('.wcpr-share-reviews-row').map(function () {
            let $row = $(this);
            $row.find('.wcpr-share-reviews-products').attr('name', `share_reviews[${index}][]`);
            index++;
            $row.find('.wcpr-share-reviews-row-no').html(index);
        })
    }
});