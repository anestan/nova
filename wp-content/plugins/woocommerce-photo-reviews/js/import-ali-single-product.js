'use strict';
jQuery(document).ready(function ($) {
    let total;
    let product_id;
    let product_ids = [];
    let download_reviews_images = [];
    let download_reviews_images_product_ids = [];
    let product_sku;
    let ratings;
    let with_picture;
    let countries;
    let translate;
    let verified;
    let vote;
    let number_of_reviews = 0;
    let download_images;
    let review_status;
    $('.wcpr-import-ali-overlay').on('click', function () {
        $('.wcpr-import-ali-container-wrap').fadeOut(200);
    });
    $('.wcpr-import-ali-countries').select2({closeOnSelect: false});

    function bind_all() {
        unbind_all();
        $('.wcpr-import-ali-button-popup-single').on('click', function () {
            product_id = [];
            product_id.push($(this).find('.wcpr-import-ali-product-id').val());
            import_single_popup();
        });
        $('.wcpr-import-ali-container .wcpr-import-ali-check').on('click', function () {
            number_of_reviews = parseInt($('.wcpr-import-ali-container .wcpr-import-ali-number').val());
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
    }

    function unbind_all() {
        $('.wcpr-import-ali-button-popup-single').unbind();
        $('.wcpr-import-ali-container .wcpr-import-ali-check').unbind();
        $('th.column-wcpr_import_ali span').unbind();
        $('.wcpr-import-ali-import-all').unbind();
    }

    bind_all();

    function import_single_popup() {
        $('.wcpr-import-ali-container-wrap').css({'display': 'flex'});
    }

    $('.wcpr-import-ali-container .wcpr-import-ali-close-form').on('click', function () {
        $('.wcpr-import-ali-container-wrap').fadeOut(200);
    });

    function import_single() {
        unbind_all();
        $('.wcpr-import-ali-import-info-status-data').removeClass('wcpr-failed');
        $('.wcpr-import-ali-import-info-status-data').removeClass('wcpr-successful');
        download_reviews_images_product_ids = [];
        download_reviews_images = [];
        product_sku = $('.wcpr-import-ali-sku').val();
        if (!product_sku) {
            alert('Please enter AliExpress product ID');
            $('.wcpr-import-ali-sku').focus();
            return false;
        }
        countries = $('.wcpr-import-ali-countries').val();
        if ($('.wcpr-import-ali-container .wcpr-import-ali-with-picture').prop('checked') === true) {
            with_picture = 1;
        } else {
            with_picture = '';
        }
        if ($('.wcpr-import-ali-container .wcpr-import-ali-download-images').prop('checked') === true) {
            download_images = 1;
        } else {
            download_images = '';
        }
        if ($('.wcpr-import-ali-container .wcpr-import-ali-translate').prop('checked') === true) {
            translate = 1;
        } else {
            translate = '';
        }
        if ($('.wcpr-import-ali-container .wcpr-import-ali-verified').prop('checked') === true) {
            verified = 1;
        } else {
            verified = '';
        }
        if ($('.wcpr-import-ali-container .wcpr-import-ali-vote').prop('checked') === true) {
            vote = 1;
        } else {
            vote = '';
        }
        review_status = $('.wcpr-import-ali-review-status').val();
        $('.wcpr-import-ali-check').addClass('wcpr-importing');
        $('.wcpr-import-ali-import-info-status-data').addClass('wcpr-importing');
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: 'admin-ajax.php?action=wcpr_get_reviews_from_ali',
            data: {
                import_method: 'sku',
                product_id: product_id[0],
                product_sku: product_sku,
                countries: countries ? countries : [],
                ratings: ratings,
                with_picture: with_picture,
                review_status: review_status,
                translate: translate,
                verified: verified,
                vote: vote,
                number_of_reviews: number_of_reviews,
                download_images: download_images
            },
            success: function (response) {
                bind_all();
                $('.wcpr-import-ali-import-info-status-data').removeClass('wcpr-importing');
                $('.wcpr-import-ali-import-info-status-data').html(response.message);
                $('.wcpr-import-ali-import-info-time-data').html(response.time);
                $('.wcpr-import-ali-import-info-number-data').html(response.number);
                total = parseInt($('.wcpr-import-ali-import-info-total-data').html());
                total += parseInt(response.number);
                if (response.status === 'successful') {
                    $('.wcpr-import-ali-import-info-status-data').addClass('wcpr-successful');
                    $('.wcpr-import-ali-check').removeClass('wcpr-importing');

                    $('.wcpr-import-ali-import-info-total-data').html(total);
                    alert(response.number + ' reviews imported.');
                } else if (response.status === 'downloading') {
                    $('.wcpr-import-ali-import-info-total-data').html(total);
                    $('.wcpr-import-ali-import-info-status-data').addClass('wcpr-downloading');
                    download_reviews_images_product_ids.push(product_id[0]);
                    download_reviews_images.push(response.reviews[0]);
                    product_ids.push(product_id[0]);
                    downloadImages(download_reviews_images, 0, download_reviews_images_product_ids);
                } else {
                    $('.wcpr-import-ali-import-info-status-data').addClass('wcpr-failed');
                    $('.wcpr-import-ali-check').removeClass('wcpr-importing');

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
        let k = reviews_with_images.length - 1;
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
                    $('.wcpr-import-ali-import-info-status-data').removeClass('wcpr-downloading');
                    $('.wcpr-import-ali-import-info-status-data').addClass('wcpr-successful');
                    $('.wcpr-import-ali-check').removeClass('wcpr-importing');
                    $('.wcpr-import-ali-import-all').removeClass('wcpr-importing');
                    for (let j = 0; j < product_ids.length; j++) {
                        $('.wcpr-import-ali-import-info-status-data').html(response.message);
                        $('.wcpr-import-ali-import-info-status-data').removeClass('wcpr-importing');
                    }
                    bind_all();
                    alert('Import completed.')
                } else {
                    downloadImages(response.reviews, response.number, response.product_id);
                }
            },
            error: function (jqXHR, error, errorThrown) {
                bind_all();
                alert(jqXHR.responseText + jqXHR.status);
            }
        })
    }

});
