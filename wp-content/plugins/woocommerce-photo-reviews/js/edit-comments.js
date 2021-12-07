'use strict';
jQuery(document).ready(function ($) {
    jQuery('.wcpr-product-search').select2({
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
    })

});