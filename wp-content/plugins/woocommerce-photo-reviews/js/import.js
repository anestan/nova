'use strict';
jQuery(document).ready(function ($) {
    console.log(woocommerce_photo_reviews_import_params);
    $('.vi-ui.dropdown').dropdown({placeholder: 'Do not import'});
    if (woocommerce_photo_reviews_import_params.step === 'mapping') {
        jQuery(document).on('click','.instead_of_id_by_other_field',function () {
           if (jQuery(this).prop('checked')){
               jQuery('.instead_of_id_by_other_field').prop('checked', false);
               jQuery(this).prop('checked', true);
           }
        });
        let required_fields = woocommerce_photo_reviews_import_params.required_fields;
        $('input[name="woocommerce_photo_reviews_import"]').on('click', function (e) {
            if (($('#wcpr-comment_date').val() || $('#wcpr-comment_date_gmt').val()) && !$('#wcpr-import_csv_date_format').val()) {
                alert('Date format can not be empty if Comment date or Comment date gmt are mapped');
                e.preventDefault();
                return false;
            }
            let empty_required_fields = [];
            for (let field in required_fields) {
                if (required_fields.hasOwnProperty(field) && !$('#wcpr-' + field).val()) {
                    empty_required_fields.push(required_fields[field]);
                }
            }
            if (empty_required_fields.length > 0) {
                if (empty_required_fields.length === 1) {
                    alert(empty_required_fields[0] + ' is required to map')
                } else {
                    alert('These fields are required to map: ' + empty_required_fields.join());
                }
                e.preventDefault();
                return false;
            }
        })
    }
    let $progress = $('.wcpr-import-progress');
    let total = 0;
    let ftell = 0;
    let imported = 0;
    let start = parseInt(woocommerce_photo_reviews_import_params.custom_start) - 1;
    if (start === 0) {
        start = 1;
    }
    let reviews_per_request = parseInt(woocommerce_photo_reviews_import_params.reviews_per_request);
    let wcpr_index = woocommerce_photo_reviews_import_params.wcpr_index;
    let search_id_by_sku = woocommerce_photo_reviews_import_params.search_id_by_sku;
    let search_id_by_slug = woocommerce_photo_reviews_import_params.search_id_by_slug;
    let import_from_loox = woocommerce_photo_reviews_import_params.import_from_loox;
    let import_csv_date_format = woocommerce_photo_reviews_import_params.import_csv_date_format;

    if (woocommerce_photo_reviews_import_params.step === 'import') {
        $progress.progress('set label', 'Checking file...');
        $.ajax({
            url: woocommerce_photo_reviews_import_params.url,
            type: 'POST',
            dataType: 'JSON',
            data: {
                action: 'woocommerce_photo_reviews_import',
                nonce: woocommerce_photo_reviews_import_params.nonce,
                file_url: woocommerce_photo_reviews_import_params.file_url,
                wcpr_index: wcpr_index,
                step: 'check',
                reviews_per_request: reviews_per_request,
                search_id_by_sku: search_id_by_sku,
                search_id_by_slug: search_id_by_slug,
                import_csv_date_format: import_csv_date_format,
            },
            success: function (response) {
                console.log(response);
                if (response.status === 'success') {
                    total = parseFloat(response.total);
                    if (total > 0) {
                        if (total >= start) {
                            wcpr_import();
                            $progress.progress('set percent', 0);
                            $progress.progress('set label', 'Importing...');
                        } else {
                            $progress.progress('set error');
                            $progress.progress('set label', 'Error: The Start line must be smaller than ' + total + ' for this file');
                        }
                    } else {
                        $progress.progress('set error');
                        $progress.progress('set label', 'Error: No data');
                    }
                } else {
                    $progress.progress('set error');
                    if (response.hasOwnProperty('message')) {
                        $progress.progress('set label', 'Error: ' + response.message);
                    }
                }
            },
            error: function (err) {
                $progress.progress('set error');
                $progress.progress('set label', err.statusText);
            },
        });
    }

    function wcpr_import() {
        $.ajax({
            url: woocommerce_photo_reviews_import_params.url,
            type: 'POST',
            dataType: 'JSON',
            data: {
                action: 'woocommerce_photo_reviews_import',
                nonce: woocommerce_photo_reviews_import_params.nonce,
                file_url: woocommerce_photo_reviews_import_params.file_url,
                reviews_per_request: reviews_per_request,
                search_id_by_sku: search_id_by_sku,
                search_id_by_slug: search_id_by_slug,
                import_from_loox: import_from_loox,
                import_csv_date_format: import_csv_date_format,
                wcpr_index: wcpr_index,
                step: 'import',
                ftell: ftell,
                start: start,
                total: total,
                imported: imported,
            },
            success: function (response) {
                console.log(response);
                if (response.status === 'success') {
                    ftell = parseFloat(response.ftell);
                    start = parseInt(response.start);
                    imported = parseInt(response.imported);
                    let percent = response.percent;
                    $progress.progress('set percent', percent);
                    if (start === total) {
                        $progress.progress('complete');
                        let message = 'Imported ';
                        if (imported === 1) {
                            message += '1 review';
                        } else {
                            message += `${imported} reviews.`;
                        }
                        $progress.progress('set label', message);
                        alert(message);
                    } else {
                        wcpr_import();
                    }
                } else if (response.status === 'finish') {
                    imported = parseInt(response.imported);
                    $progress.progress('complete');
                    let message = 'Imported ';
                    if (imported === 1) {
                        message += '1 review';
                    } else {
                        message += `${imported} reviews.`;
                    }
                    $progress.progress('set label', message);
                    alert(message);
                } else {
                    $progress.progress('set error');
                    $progress.progress('set label', response.message);
                }
            },
            error: function (err) {
                console.log(err);
                $progress.progress('set error');
                $progress.progress('set label', 'Error');
            },
        });
    }

    $('.search-category').select2({
        closeOnSelect: false,
        placeholder: "Please fill in your category title",
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
        minimumInputLength: 2
    });
});
