'use strict';
jQuery(window).on('elementor/frontend/init', () => {
    elementorFrontend.hooks.addAction('frontend/element_ready/woocommerce-photo-reviews-form.default', function ($scope) {
        if (!window.elementor) {
            return;
        }
        let $shortcode_container = $scope.find('.woocommerce-photo-reviews-form-container');
        $shortcode_container.find('.wcpr-rating')
            .hide()
            .before(
                '<p class="stars">\
                    <span>\
                        <a class="star-1" href="#">1</a>\
                        <a class="star-2" href="#">2</a>\
                        <a class="star-3" href="#">3</a>\
                        <a class="star-4" href="#">4</a>\
                        <a class="star-5" href="#">5</a>\
                    </span>\
                </p>'
            );
    })
});
jQuery(document).ready(function ($) {
    $('body')
    // Star ratings for comments
        .on('init', '.wcpr-rating', function () {
            $('.wcpr-rating')
                .hide()
                .before(
                    '<p class="stars">\
                        <span>\
                            <a class="star-1" href="#">1</a>\
                            <a class="star-2" href="#">2</a>\
                            <a class="star-3" href="#">3</a>\
                            <a class="star-4" href="#">4</a>\
                            <a class="star-5" href="#">5</a>\
                        </span>\
                    </p>'
                );
        })
        .on('click', '#respond p.stars a', function () {
            var $star = $(this),
                $rating = $(this).closest('#respond').find('#wcpr-rating'),
                $container = $(this).closest('.stars');

            $rating.val($star.text());
            $star.siblings('a').removeClass('active');
            $star.addClass('active');
            $container.addClass('selected');

            return false;
        })
        .on('click', '#respond #submit', function () {
            var $rating = $(this).closest('#respond').find('#wcpr-rating'),
                rating = $rating.val();
            if ($rating.length > 0 && !rating && woocommerce_photo_reviews_form_params.review_rating_required === 'yes') {
                window.alert(woocommerce_photo_reviews_form_params.i18n_required_rating_text);

                return false;
            }
        });
    $('#wcpr-rating').trigger('init');
    let max_files = woocommerce_photo_reviews_form_params.max_files;
    $('.wcpr-comment-form').on('change', '.wcpr_image_upload', function (e) {
        $(this).parent().find('.wcpr-selected-image-container').html('');
        if (this.files.length > max_files) {
            alert(woocommerce_photo_reviews_form_params.warning_max_files);
            $(this).val('');
            return false;
        } else if (this.files.length > 0) {
            readURL(this);
        }
    }).find('.star-5').click();
    let image_caption_enable = woocommerce_photo_reviews_form_params.image_caption_enable == 1;
    let i18n_image_caption = woocommerce_photo_reviews_form_params.i18n_image_caption;

    function getSelectedImageHtml(src, name) {
        let selectImageHtml;
        if (image_caption_enable) {
            selectImageHtml = `<div class="wcpr-selected-image"><img title="${name}" src="${src}" class="wcpr-selected-image-preview"><div class="wcpr-selected-image-info"><div class="wcpr-selected-image-name" title="${name}">${name}</div><input class="wcpr-selected-image-caption" type="text" name="wcpr_image_caption[]" placeholder="${i18n_image_caption}"></div></div>`;
        } else {
            selectImageHtml = `<div class="wcpr-selected-image"><img title="${name}" src="${src}" class="wcpr-selected-image-preview"><div class="wcpr-selected-image-info"><div class="wcpr-selected-image-name" title="${name}">${name}</div></div></div>`;
        }
        return selectImageHtml;
    }

    function readURL(input) {
        for (let i = 0; i < input.files.length; i++) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $(input).parent().find('.wcpr-selected-image-container').append(getSelectedImageHtml(e.target.result, input.files[i].name))
            };

            reader.readAsDataURL(input.files[i]); // convert to base64 string
        }
    }

    $('.woocommerce-photo-reviews-form-container').find('input[type="submit"]').on('click', function (e) {
        let $button = $(this);
        let $container = $button.closest('.woocommerce-photo-reviews-form-container');
        let $content = $container.find('textarea[name="comment"]');
        let $name = $container.find('input[name="author"]');
        let $email = $container.find('input[name="email"]');
        if ($content.length > 0 && !$content.val() && woocommerce_photo_reviews_form_params.allow_empty_comment != 1) {
            alert(woocommerce_photo_reviews_form_params.i18n_required_comment_text);
            e.preventDefault();
            $content.focus();
            return false;
        }
        if ('on' === woocommerce_photo_reviews_form_params.enable_photo) {
            let $fileUpload = $container.find('.wcpr_image_upload');
            if ($fileUpload.length > 0) {
                let imagesCount = parseInt($fileUpload.get(0).files.length);
                if ('on' === woocommerce_photo_reviews_form_params.required_image && imagesCount === 0) {
                    alert(woocommerce_photo_reviews_form_params.warning_required_image);
                    return false;
                }
                if (imagesCount > woocommerce_photo_reviews_form_params.max_files) {
                    alert(woocommerce_photo_reviews_form_params.warning_max_files);
                    e.preventDefault();
                    return false;
                }
            } else if ('on' === woocommerce_photo_reviews_form_params.required_image) {
                alert(woocommerce_photo_reviews_form_params.warning_required_image);
                return false;
            }
        }

        if ($name.length > 0 && $name.attr('required') && !$name.val()) {
            alert(woocommerce_photo_reviews_form_params.i18n_required_name_text);
            e.preventDefault();
            $name.focus();
            return false;
        }
        if ($email.length > 0 && $email.attr('required') && !$email.val()) {
            alert(woocommerce_photo_reviews_form_params.i18n_required_email_text);
            e.preventDefault();
            $email.focus();
            return false;
        }
        if ($container.find('input[name="wcpr_gdpr_checkbox"]').prop('checked') === false) {
            alert(woocommerce_photo_reviews_form_params.warning_gdpr);
            e.preventDefault();
            return false;
        }
    });
    $(document).on('click', '.woocommerce-photo-reviews-form-main,.woocommerce-photo-reviews-form-main-close', function () {
        let $container = $(this).closest('.woocommerce-photo-reviews-form-container');
        $container.addClass('woocommerce-photo-reviews-form-popup-hide')
    });
    $(document).on('click', '.woocommerce-photo-reviews-form-button-add-review', function () {
        let $button = $(this);
        let $container = $button.closest('.woocommerce-photo-reviews-form-container');
        $container.removeClass('woocommerce-photo-reviews-form-popup-hide');
    });
    $(document).on('click', '.woocommerce-photo-reviews-form-main-inner', function (e) {
        e.stopPropagation();
    });
});
