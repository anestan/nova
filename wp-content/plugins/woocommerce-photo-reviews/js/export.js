'use strict';
jQuery(document).ready(function ($) {
    /*validation*/
    $('input[name="wcpr_admin_add_review"]').on('click', function () {
        $('.vi-ui.warning.message').hide();
        if ($('.wcpr-product-search').val() == null) {
            $('.wcpr-product-search').focus();
            $('.wcpr-warning-message-product-id').show();
            return false;
        }
        if ($('.vi-wcpr-add-review-customer-name').val() == '') {
            $('.vi-wcpr-add-review-customer-name').focus();
            $('.wcpr-warning-message-customer-name').show();
            return false;
        }
        let content = tinyMCE.get('vi_wcpr_add_review_content') ? tinyMCE.get('vi_wcpr_add_review_content').getContent() : $('#vi_wcpr_add_review_content').val();
        if (content == '') {
            tinyMCE.get('vi_wcpr_add_review_content').focus();
            $('.wcpr-warning-message-content').show();
            return false;
        }
    });
    $('.vi-ui.dropdown').dropdown();

    $('.wcpr-product-search').select2({
        closeOnSelect: false,
        allowClear: true,
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
    $('.wcpr-category-search').select2({
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
    })
});
