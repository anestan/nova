'use strict';
jQuery(document).ready(function ($) {
    let product_ids = [];
    let download_reviews_images = [];
    let download_reviews_images_product_ids = [];
    let product_sku;
    let countries;
    let ratings;
    let with_picture = '';
    let translate = '';
    let number_of_reviews = 0;
    let download_images = '';
    let verified = '';
    let vote = '';
    let review_status = '';
    $('.wcpr-import-ali-overlay').on('click', function () {
        $('.wcpr-import-ali-container-wrap').fadeOut(200);
    });
    $('.wcpr-import-ali-countries').select2({closeOnSelect: false});

    function bind_all() {
        unbind_all();
        $('.wcpr-import-ali-button-popup').on('click', function () {
            product_ids = [];
            product_ids.push($(this).find('.wcpr-import-ali-product-id').val());

            $('.wcpr-import-ali-sku').val($(this).find('.wcpr-import-ali-product-sku').val());
            $('.wcpr-import-ali-product-url').val($(this).find('.wcpr-import-ali-product-ali-url').val());
            $('.wcpr-import-ali-current-product-title').html($(this).parent().parent().find('.row-title').html());
            import_single_popup();
        });
        $('.wcpr-import-ali-check').on('click', function () {
            number_of_reviews = parseInt($('.wcpr-import-ali-number').val());
            if (number_of_reviews <= 0) {
                alert("Please enter the number of reviews that you want to import.");
                $('.wcpr-import-ali-number').focus();
                return false;
            }
            ratings = [];
            $('.wcpr-import-ali-rating').map(function () {
                if ($(this).prop('checked')) {
                    ratings.push($(this).val());
                }
            });
            if (ratings.length === 0) {
                alert("Please select at least one rating to import");
                return false;
            }
            import_single();
        });
        $('#wcpr_import_ali span').on('click', function () {
            let pIDs = $('input[name="post[]"]').map(function () {
                if ($(this).prop('checked') == true) {
                    return $(this).val();
                }
            });
            if (pIDs.length == 0) {
                alert('Please check on products that you want to import reviews.');
                return false;
            }
            import_all_popup();
        });
        $('.wcpr-import-ali-import-all').on('click', function () {
            number_of_reviews = parseInt($('.wcpr-import-ali-number').val());
            if (number_of_reviews <= 0) {
                alert("Please enter the number of reviews that you want to import.");
                $('.wcpr-import-ali-number').focus();
                return false;
            }
            ratings = [];
            $('.wcpr-import-ali-rating').map(function () {
                if ($(this).prop('checked')) {
                    ratings.push($(this).val());
                }
            });
            import_all();
        });
    }

    function unbind_all() {
        $('.wcpr-import-ali-button-popup').unbind();
        $('.wcpr-import-ali-check').unbind();
        $('#wcpr_import_ali span').unbind();
        $('.wcpr-import-ali-import-all').unbind();
    }

    bind_all();

    function import_single_popup() {
        $('.wcpr-import-ali-import-all').hide();
        $('.wcpr-import-ali-check').show();
        $('.wcpr-import-ali-method-wrap').show();
        $('.wcpr-import-ali-method').val('sku');
        $('.wcpr-import-ali-sku-wrap').show();
        $('.wcpr-import-ali-product-url-wrap').hide();
        $('.wcpr-import-ali-container-wrap').css({'display': 'flex'});
    }

    $('.wcpr-import-ali-close-form').on('click', function () {
        $('.wcpr-import-ali-container-wrap').fadeOut(200);
    });

    function import_single() {
        unbind_all();
        $('#post-' + product_ids[0]).find('.wcpr-import-ali-import-info-status-data').removeClass('wcpr-failed');
        $('#post-' + product_ids[0]).find('.wcpr-import-ali-import-info-status-data').removeClass('wcpr-successful');
        download_reviews_images_product_ids = [];
        download_reviews_images = [];
        product_sku = $('.wcpr-import-ali-sku').val();
        countries = $('.wcpr-import-ali-countries').val();

        if ($('.wcpr-import-ali-with-picture').prop('checked')) {
            with_picture = 1;
        } else {
            with_picture = '';
        }
        if ($('.wcpr-import-ali-translate').prop('checked')) {
            translate = 1;
        } else {
            translate = '';
        }
        if ($('.wcpr-import-ali-download-images').prop('checked')) {
            download_images = 1;
        } else {
            download_images = '';
        }
        if ($('.wcpr-import-ali-verified').prop('checked')) {
            verified = 1;
        } else {
            verified = '';
        }
        if ($('.wcpr-import-ali-vote').prop('checked')) {
            vote = 1;
        } else {
            vote = '';
        }
        review_status=$('.wcpr-import-ali-review-status').val();
        if (!product_sku) {
            alert('Please enter AliExpress product ID');
            $('.wcpr-import-ali-sku').focus();
            return false;
        }
        $('.wcpr-import-ali-check').addClass('wcpr-importing');

        $('#post-' + product_ids[0]).find('.wcpr-import-ali-import-info-status-data').addClass('wcpr-importing');

        $.ajax({
            type: 'post',
            dataType: 'json',
            url: 'admin-ajax.php?action=wcpr_get_reviews_from_ali',
            data: {
                import_method: 'sku',
                product_id: product_ids[0],
                // product_url: product_url,
                product_sku: product_sku,
                countries: countries ? countries : [],
                ratings: ratings,
                with_picture: with_picture,
                number_of_reviews: number_of_reviews,
                translate: translate,
                verified: verified,
                vote: vote,
                download_images: download_images,
                review_status: review_status,
            },
            success: function (response) {
                let total = parseInt($('#post-' + product_ids[0]).find('.wcpr-import-ali-import-info-total-data').html());
                total += response.number;
                $('#post-' + product_ids[0]).find('.wcpr-import-ali-import-info-status-data').html(response.message);
                $('#post-' + product_ids[0]).find('.wcpr-import-ali-import-info-status-data').removeClass('wcpr-importing');

                if (response.status === 'successful') {
                    $('#post-' + product_ids[0]).find('.wcpr-import-ali-import-info-status-data').addClass('wcpr-successful');
                    bind_all();
                    $('.wcpr-import-ali-check').removeClass('wcpr-importing');
                    $('#post-' + product_ids[0]).find('.wcpr-import-ali-import-info-time-data').html(response.time);
                    $('#post-' + product_ids[0]).find('.wcpr-import-ali-import-info-number-data').html(response.number);

                    $('#post-' + product_ids[0]).find('.wcpr-import-ali-import-info-total-data').html(total);
                    alert(response.number + ' reviews imported.');
                } else if (response.status === 'downloading') {
                    $('#post-' + product_ids[0]).find('.wcpr-import-ali-import-info-status-data').addClass('wcpr-downloading');
                    $('#post-' + product_ids[0]).find('.wcpr-import-ali-import-info-total-data').html(total);
                    for (let j = 0; j < response.reviews.length; j++) {
                        download_reviews_images_product_ids.push(product_ids[0]);
                        download_reviews_images.push(response.reviews[j]);
                    }
                    downloadImages(download_reviews_images, 0, download_reviews_images_product_ids);
                } else {
                    bind_all();
                    $('#post-' + product_ids[0]).find('.wcpr-import-ali-import-info-status-data').addClass('wcpr-failed');
                    $('.wcpr-import-ali-check').removeClass('wcpr-importing');
                    $('#post-' + product_ids[0]).find('.wcpr-import-ali-import-info-time-data').html(response.time);
                    $('#post-' + product_ids[0]).find('.wcpr-import-ali-import-info-number-data').html(response.number);
                    alert(response.message);
                }
            },
            error: function (jqXHR, error, errorThrown) {
                bind_all();
                alert(jqXHR.responseText + jqXHR.status);
            }
        });
    }

    function downloadImages(reviews_with_images, n, ids) {
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: 'admin-ajax.php?action=wcpr_download_image_from_ali',
            data: {
                reviews_with_images: reviews_with_images,
                imported: n,
                product_ids: ids,
                wcpr_adjust_image_sizes: 1,
            },
            success: function (response) {
                if (response.status == 'downloaded') {
                    $('.wcpr-import-ali-check').removeClass('wcpr-importing');
                    $('.wcpr-import-ali-import-all').removeClass('wcpr-importing');
                    for (let j = 0; j < download_reviews_images_product_ids.length; j++) {
                        $('#post-' + download_reviews_images_product_ids[j]).find('.wcpr-import-ali-import-info-status-data').html(response.message);
                        $('#post-' + download_reviews_images_product_ids[j]).find('.wcpr-import-ali-import-info-status-data').removeClass('wcpr-importing');
                        $('#post-' + download_reviews_images_product_ids[j]).find('.wcpr-import-ali-import-info-status-data').addClass('wcpr-successful');
                        $('#post-' + download_reviews_images_product_ids[j]).find('.wcpr-import-ali-import-info-status-data').removeClass('wcpr-downloading');

                    }
                    bind_all();
                    alert('Import completed.')
                } else {
                    downloadImages(response.reviews, response.number, response.product_ids);
                }
            },
            error: function (jqXHR, error, errorThrown) {
                bind_all();
                alert(jqXHR.responseText + jqXHR.status);
            }
        })
    }

    function is_url(str) {
        regexp = /^(?:(?:https?|ftp):\/\/)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/\S*)?$/;
        return regexp.test(str);
    }

    function import_all_popup() {
        $('.wcpr-import-ali-current-product-title').html('Import reviews of selected products');
        $('.wcpr-import-ali-product-url-wrap').hide();
        $('.wcpr-import-ali-import-all').show();
        $('.wcpr-import-ali-check').hide();
        $('.wcpr-import-ali-method-wrap').hide();
        $('.wcpr-import-ali-sku-wrap').hide();
        $('.wcpr-import-ali-container-wrap').css({'display': 'flex'});
    }

    function import_all() {
        unbind_all();
        product_ids = [];
        download_reviews_images_product_ids = [];
        download_reviews_images = [];
        countries = $('.wcpr-import-ali-countries').val();
        if ($('.wcpr-import-ali-with-picture').prop('checked')) {
            with_picture = 1;
        } else {
            with_picture = '';
        }
        if ($('.wcpr-import-ali-translate').prop('checked')) {
            translate = 1;
        } else {
            translate = '';
        }
        if ($('.wcpr-import-ali-download-images').prop('checked')) {
            download_images = 1;
        } else {
            download_images = '';
        }
        if ($('.wcpr-import-ali-verified').prop('checked')) {
            verified = 1;
        } else {
            verified = '';
        }
        if ($('.wcpr-import-ali-vote').prop('checked')) {
            vote = 1;
        } else {
            vote = '';
        }
        review_status=$('.wcpr-import-ali-review-status').val();
        product_sku = $('.wcpr-import-ali-product-sku').map(function () {
            if ($(this).parent().parent().parent().find('input[name="post[]"]').prop('checked') == true) {
                return $(this).val();
            }
        });
        product_ids = $('input[name="post[]"]').map(function () {
            if ($(this).prop('checked') == true) {
                return $(this).val();
            }
        });
        let total = product_ids.length;
        if (total > 0) {
            for (let j = 0; j < total; j++) {
                $('#post-' + product_ids[j]).find('.wcpr-import-ali-import-info-status-data').addClass('wcpr-importing');
                $('#post-' + product_ids[j]).find('.wcpr-import-ali-import-info-status-data').removeClass('wcpr-failed');
                $('#post-' + product_ids[j]).find('.wcpr-import-ali-import-info-status-data').removeClass('wcpr-successful');
            }
            $('.wcpr-import-ali-import-all').addClass('wcpr-importing');
            import_reviews(total);
        } else {
            bind_all();
            alert('Please select products to import reviews from Aliexpress.');
        }
    }


    function import_reviews(n) {
        let i = n - 1;
        if (product_sku[i]) {
            $.ajax({
                type: 'post',
                dataType: 'json',
                url: 'admin-ajax.php?action=wcpr_get_reviews_from_ali',
                data: {
                    import_method: 'sku',
                    product_id: product_ids[i],
                    product_sku: product_sku[i],
                    countries: countries ? countries : [],
                    ratings: ratings,
                    with_picture: with_picture,
                    number_of_reviews: number_of_reviews,
                    translate: translate,
                    verified: verified,
                    vote: vote,
                    download_images: download_images,
                    review_status: review_status
                },
                success: function (response) {
                    $('#post-' + product_ids[i]).find('.wcpr-import-ali-import-info-status-data').removeClass('wcpr-importing');
                    $('#post-' + product_ids[i]).find('.wcpr-import-ali-import-info-status-data').html(response.message);
                    $('#post-' + product_ids[i]).find('.wcpr-import-ali-import-info-time-data').html(response.time);
                    $('#post-' + product_ids[i]).find('.wcpr-import-ali-import-info-number-data').html(response.number);
                    let total = parseInt($('#post-' + product_ids[i]).find('.wcpr-import-ali-import-info-total-data').html());
                    total += response.number;
                    $('#post-' + product_ids[i]).find('.wcpr-import-ali-import-info-total-data').html(total);
                    if (response.status === 'downloading') {
                        $('#post-' + product_ids[i]).find('.wcpr-import-ali-import-info-status-data').addClass('wcpr-downloading');

                        for (let j = 0; j < response.reviews.length; j++) {
                            download_reviews_images.push(response.reviews[j]);
                            download_reviews_images_product_ids.push(product_ids[i]);
                        }
                    } else if (response.status == 'successful') {
                        $('#post-' + product_ids[i]).find('.wcpr-import-ali-import-info-status-data').addClass('wcpr-successful');

                    } else {
                        $('#post-' + product_ids[i]).find('.wcpr-import-ali-import-info-status-data').addClass('wcpr-failed');

                    }
                    if (i > 0) {
                        import_reviews(i);
                    } else {
                        if (download_images && download_reviews_images.length > 0) {
                            downloadImages(download_reviews_images, 0, download_reviews_images_product_ids);
                        } else {
                            bind_all();
                            alert('Import completed.');
                            $('.wcpr-import-ali-import-all').removeClass('wcpr-importing');
                        }
                    }
                },
                error: function () {
                    $('#post-' + product_ids[i]).find('.wcpr-import-ali-import-info-status-data').removeClass('wcpr-importing');
                    if (i > 0) {
                        import_reviews(i);
                    } else {
                        bind_all();
                        alert('Import completed.');
                        $('.wcpr-import-ali-import-all').removeClass('wcpr-importing');
                    }
                }
            });
        } else {
            $('#post-' + product_ids[i]).find('.wcpr-import-ali-import-info-status-data').removeClass('wcpr-importing');
            if (i > 0) {
                import_reviews(i);
            } else {
                bind_all();
                alert('Import completed.');
                $('.wcpr-import-ali-import-all').removeClass('wcpr-importing');

            }
        }

    }
});
