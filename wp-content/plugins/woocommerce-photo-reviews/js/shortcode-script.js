let slides;
let current_shortcode_id;
jQuery(window).on('elementor/frontend/init', () => {
    'use strict';
    elementorFrontend.hooks.addAction('frontend/element_ready/woocommerce-photo-reviews.default', function ($scope) {
        if (!window.elementor) {
            return;
        }
        let $shortcode_container = $scope.find('.woocommerce-photo-reviews-shortcode');
        let current = -1;
        let swipeBoxIndex = 0;
        let isSafari = false;
        if (/iPad/i.test(navigator.userAgent) || (/Safari/i.test(navigator.userAgent) && /Apple Computer/.test(navigator.vendor) && !/Mobi|Android/i.test(navigator.userAgent))) {
            isSafari = true;
        }
        triggerReviewImageClick();
        fixBoxShadow();

        function fixBoxShadow() {
            if (!isSafari) {
                $shortcode_container.find('.shortcode-wcpr-enable-box-shadow').addClass('shortcode-wcpr-fix-box-shadow');
            }
        }

        function triggerReviewImageClick() {
            $shortcode_container.find('.shortcode-wcpr-grid-item .shortcode-reviews-images').unbind().on('click', function (e) {
                let this_image = jQuery(this);
                let reviews_shortcode = $shortcode_container.data('reviews_shortcode');
                if (reviews_shortcode.hasOwnProperty('masonry_popup') && reviews_shortcode.masonry_popup === 'image') {
                    e.stopPropagation();
                    let $container = this_image.closest('.shortcode-reviews-images-container');
                    let data = [];
                    $container.find('.shortcode-reviews-images-wrap-left').find('a').map(function () {
                        let current_image = jQuery(this).find('.shortcode-reviews-images');
                        let href = jQuery(this).data('image_src') ? jQuery(this).data('image_src') : current_image.attr('src');
                        let title = jQuery(this).data('image_caption') ? jQuery(this).data('image_caption') : ((parseInt(jQuery(this).data('image_index')) + 1) + '/' + $container.find('.shortcode-reviews-images-wrap-left').find('a').length);
                        data.push({href: href, title: title});
                    });
                    if (data.length === 0) {
                        data.push({
                            href: this_image.data('original_src') ? this_image.data('original_src') : this_image.attr('src'),
                            title: this_image.parent().find('.shortcode-wcpr-review-image-caption').html()
                        });
                    }
                    jQuery.swipebox(data, {hideBarsDelay: 100000, initialIndexOnArray: 0})
                }
            });
        }

        function showReviewElementor(n) {
            swipeBoxIndex = 0;
            current = n;
            if (n >= slides.length) {
                current = 0
            }
            if (n < 0) {
                current = slides.length - 1
            }
            jQuery('#shortcode-reviews-content-left-modal').html('');
            jQuery('#shortcode-reviews-content-left-main').html('');
            let $current = jQuery(slides[current]);
            if ($current.find('.shortcode-reviews-images-container').length == 0) {
                jQuery('.shortcode-wcpr-modal-light-box').addClass('shortcode-wcpr-no-images');
            } else {
                jQuery('#shortcode-reviews-content-left-modal').html(($current.find('.shortcode-reviews-images-wrap-left').html()));
                let img_data = $current.find('.shortcode-reviews-images-wrap-right').html();
                if (img_data) {
                    jQuery('.shortcode-wcpr-modal-light-box').removeClass('shortcode-wcpr-no-images');
                    jQuery('#shortcode-reviews-content-left-main').html(img_data);
                }
                jQuery('#shortcode-reviews-content-left-modal').find('.shortcode-reviews-images').parent().on('click', function () {
                    swipeBoxIndex = jQuery(this).data('image_index');
                    jQuery('#shortcode-reviews-content-left-main').find('.shortcode-reviews-images').attr('src', jQuery(this).attr('href'));
                    jQuery('#shortcode-reviews-content-left-main').find('.shortcode-wcpr-review-image-caption').html(jQuery(this).data('image_caption'));
                    return false;
                });
            }
            jQuery('#shortcode-reviews-content-right .shortcode-reviews-content-right-meta').html($current.find('.shortcode-review-content-container').html());
            jQuery('#shortcode-reviews-content-right .shortcode-wcpr-single-product-summary').html($current.find('.shortcode-wcpr-single-product-summary-content-wrapper').html());
            jQuery('.shortcode-wcpr-modal-light-box').fadeIn(200);
        }

        jQuery('.shortcode-wcpr-modal-light-box').keydown(function (e) {
            if (jQuery.swipebox.isOpen) {
                return;
            }
            if (jQuery('.shortcode-wcpr-modal-light-box').css('display') == 'none') {
                return;
            }
            if (e.keyCode == 27) {
                jQuery('.shortcode-wcpr-modal-light-box').fadeOut(200);
                jQuery('.shortcode-wcpr-modal-light-box').removeClass(current_shortcode_id + '-modal');
                current = -1;

            }
            if (current != -1) {
                if (e.keyCode == 37) {
                    showReviewElementor(current -= 1);
                }

                if (e.keyCode == 39) {
                    showReviewElementor(current += 1);
                }
            }
        });
        triggerReviewClickElementor();

        function triggerReviewClickElementor() {
            $shortcode_container.find('.shortcode-wcpr-grid-item').unbind().on('click', function () {
                let reviews_shortcode = $shortcode_container.data('reviews_shortcode');

                if (reviews_shortcode.hasOwnProperty('masonry_popup') && reviews_shortcode.masonry_popup === 'review') {
                    if (reviews_shortcode.hasOwnProperty('full_screen_mobile') && reviews_shortcode.full_screen_mobile === 'on') {
                        jQuery('.shortcode-wcpr-modal-light-box').addClass('shortcode-wcpr-full-screen-mobile');
                    } else {
                        jQuery('.shortcode-wcpr-modal-light-box').removeClass('shortcode-wcpr-full-screen-mobile');
                    }
                    slides = $shortcode_container.find('.shortcode-wcpr-grid-item');
                    let i = slides.index(jQuery(this));
                    if (i >= 0) {
                        jQuery('.shortcode-wcpr-modal-light-box').removeClass(current_shortcode_id + '-modal');
                        current_shortcode_id = $shortcode_container.attr('id');
                        jQuery('.shortcode-wcpr-modal-light-box').addClass(current_shortcode_id + '-modal');
                        showReviewElementor(i);
                        wcpr_disable_scroll();
                        // wcpr_helpful_button();
                    }
                }
            });
            $shortcode_container.find('.shortcode-wcpr-read-more').unbind().on('click', function (e) {
                e.stopPropagation();
                let $button = jQuery(this);
                let $comment_content = $button.closest('.shortcode-wcpr-review-content');
                let $comment_content_full = $comment_content.find('.shortcode-wcpr-review-content-full');
                let comment_content_full = $comment_content_full.html();
                if (comment_content_full) {
                    $comment_content.html(comment_content_full);
                }
                // $comment_content.closest('.shortcode-wcpr-grid').find('.shortcode-wcpr-grid-item').removeClass('wcpr-grid-item-init');
                $comment_content.closest('.shortcode-wcpr-grid-item').removeClass('wcpr-grid-item-init');
                shortcode_wcpr_resize_masonry_items();
            })
        }
        shortcode_wcpr_resize_masonry_items();
    });
});
jQuery(document).ready(function ($) {
    'use strict';
    let isSafari = false;
    if (/iPad/i.test(navigator.userAgent) || (/Safari/i.test(navigator.userAgent) && /Apple Computer/.test(navigator.vendor) && !/Mobi|Android/i.test(navigator.userAgent))) {
        isSafari = true;
    }
    fixBoxShadow();

    function fixBoxShadow() {
        if (!isSafari) {
            $('.shortcode-wcpr-enable-box-shadow').addClass('shortcode-wcpr-fix-box-shadow');
        }
    }

    /*Masonry*/
    let current = -1;
    let swipeBoxIndex = 0;
    $('.shortcode-wcpr-close').on('click', function () {
        closeReviewPopUp();

    });
    $( '.shortcode-wcpr-single-product-summary-meta-shop .variations_form' ).each( function() {
        $( this ).addClass('shortcode-wcpr-variations_form').removeClass('variations_form');
    });
    $('.shortcode-wcpr-modal-light-box .shortcode-wcpr-overlay').on('click', function () {
        closeReviewPopUp();
    });
    $('#shortcode-reviews-content-left-main').on('click', '.shortcode-reviews-images', function () {
        let this_image = $(this);
        let data = [];
        $('#shortcode-reviews-content-left-modal').find('a').map(function () {
            let current_image = $(this).find('.shortcode-reviews-images');
            let href = $(this).data('image_src') ? $(this).data('image_src') : current_image.attr('src');
            let title = $(this).data('image_caption') ? $(this).data('image_caption') : ((parseInt($(this).data('image_index')) + 1) + '/' + $('#shortcode-reviews-content-left-modal').find('a').length);
            data.push({href: href, title: title});
        });
        if (data.length === 0) {
            data.push({
                href: this_image.data('original_src') ? this_image.data('original_src') : this_image.attr('src'),
                title: this_image.parent().find('.shortcode-wcpr-review-image-caption').html()
            });
        }
        $.swipebox(data, {hideBarsDelay: 100000, initialIndexOnArray: swipeBoxIndex})
    });
    triggerReviewImageClick();

    function closeReviewPopUp() {
        wcpr_enable_scroll();
        $('.shortcode-wcpr-modal-light-box').fadeOut(200);
        current = -1;
    }

    function triggerReviewImageClick() {
        // $('.shortcode-wcpr-grid-item .shortcode-reviews-images').unbind().on('click', function (e) {
        //     let this_image = $(this);
        //     let $shortcode_container = this_image.closest('.woocommerce-photo-reviews-shortcode');
        //     let reviews_shortcode = $shortcode_container.data('reviews_shortcode');
        //     if (reviews_shortcode.hasOwnProperty('masonry_popup') && reviews_shortcode.masonry_popup === 'image') {
        //         e.stopPropagation();
        //         let $container = this_image.closest('.shortcode-reviews-images-container');
        //         let data = [];
        //         $container.find('.shortcode-reviews-images-wrap-left').find('a').map(function () {
        //             let current_image = $(this).find('.shortcode-reviews-images');
        //             let href = $(this).data('image_src') ? $(this).data('image_src') : current_image.attr('src');
        //             let title = $(this).data('image_caption') ? $(this).data('image_caption') : ((parseInt($(this).data('image_index')) + 1) + '/' + $container.find('.shortcode-reviews-images-wrap-left').find('a').length);
        //             data.push({href: href, title: title});
        //         });
        //         if (data.length === 0) {
        //             data.push({
        //                 href: this_image.data('original_src') ? this_image.data('original_src') : this_image.attr('src'),
        //                 title: this_image.parent().find('.shortcode-wcpr-review-image-caption').html()
        //             });
        //         }
        //         $.swipebox(data, {hideBarsDelay: 100000, initialIndexOnArray: 0})
        //     }
        // });
    }

    function showReview(n) {
        swipeBoxIndex = 0;
        current = n;
        if (n >= slides.length) {
            current = 0
        }
        if (n < 0) {
            current = slides.length - 1
        }
        let $left_modal = $('#shortcode-reviews-content-left-modal');
        let $left_main = $('#shortcode-reviews-content-left-main');
        $left_modal.html('');
        $left_main.html('');
        let $current = $(slides[current]);
        if ($current.find('.shortcode-reviews-images-container').length === 0) {
            $('.shortcode-wcpr-modal-light-box').addClass('shortcode-wcpr-no-images');
        } else {
            $left_modal.html(($current.find('.shortcode-reviews-images-wrap-left').html()));
            let img_data = $current.find('.shortcode-reviews-images-wrap-right').html();
            if (img_data) {
                $('.shortcode-wcpr-modal-light-box').removeClass('shortcode-wcpr-no-images');
                $left_main.html(img_data);
            }
            $left_modal.find('.shortcode-reviews-images').map(function () {
                let lazy_load_src = $(this).data('src');
                if (lazy_load_src) {
                    $(this).attr('src', lazy_load_src)
                }
            });
            $left_modal.find('.shortcode-reviews-images').parent().on('click', function () {
                swipeBoxIndex = $(this).data('image_index');
                $left_main.find('.shortcode-reviews-images').attr('src', $(this).attr('href'));
                $left_main.find('.shortcode-wcpr-review-image-caption').html($(this).data('image_caption'));
                return false;
            });
        }
        $('#shortcode-reviews-content-right .shortcode-reviews-content-right-meta').html($current.find('.shortcode-review-content-container').html());
        $('#shortcode-reviews-content-right .shortcode-wcpr-single-product-summary').html($current.find('.shortcode-wcpr-single-product-summary-content-wrapper').html());
        $('.shortcode-wcpr-modal-light-box').fadeIn(200);
        wcpr_variation_form($current);
    }

    $(document).on('keydown', function (e) {
        let $modal = $('.shortcode-wcpr-modal-light-box');
        if ($('.woocommerce-photo-reviews-shortcode').length === 0) {
            return;
        }
        if ($.swipebox.isOpen) {
            return;
        }
        if ($modal.css('display') === 'none') {
            return;
        }
        if (e.keyCode === 27) {
            closeReviewPopUp();
        }
        if (current !== -1) {
            if (e.keyCode === 37) {
                showReview(current -= 1);
            }

            if (e.keyCode === 39) {
                showReview(current += 1);
            }
        }
    });
    triggerReviewClick();

    function triggerReviewClick() {
        $(document).on('click', '.shortcode-wcpr-grid-item', function () {
            let $shortcode_container = $(this).closest('.woocommerce-photo-reviews-shortcode');
            let reviews_shortcode = $shortcode_container.data('reviews_shortcode');
            if (reviews_shortcode.hasOwnProperty('masonry_popup') && reviews_shortcode.masonry_popup === 'review') {
                if (reviews_shortcode.hasOwnProperty('full_screen_mobile') && reviews_shortcode.full_screen_mobile === 'on') {
                    $('.shortcode-wcpr-modal-light-box').addClass('shortcode-wcpr-full-screen-mobile');
                } else {
                    $('.shortcode-wcpr-modal-light-box').removeClass('shortcode-wcpr-full-screen-mobile');
                }
                slides = $shortcode_container.find('.shortcode-wcpr-grid-item');
                let i = slides.index($(this));
                if (i >= 0) {
                    $('.shortcode-wcpr-modal-light-box').removeClass(current_shortcode_id + '-modal');
                    current_shortcode_id = $shortcode_container.attr('id');
                    $('.shortcode-wcpr-modal-light-box').addClass(current_shortcode_id + '-modal');
                    showReview(i);
                    wcpr_disable_scroll();
                    // wcpr_helpful_button();
                }
            }
        });
        $(document).on('click', '.shortcode-wcpr-read-more', function (e) {
            e.stopPropagation();
            let $button = $(this);
            let $comment_content = $button.closest('.shortcode-wcpr-review-content');
            let $comment_content_full = $comment_content.find('.shortcode-wcpr-review-content-full');
            let comment_content_full = $comment_content_full.html();
            if (comment_content_full) {
                $comment_content.html(comment_content_full);
            }
            // $comment_content.closest('.shortcode-wcpr-grid').find('.shortcode-wcpr-grid-item').removeClass('wcpr-grid-item-init');
            $comment_content.closest('.shortcode-wcpr-grid-item').removeClass('wcpr-grid-item-init');
            shortcode_wcpr_resize_masonry_items();
        });
        $(document).on('click', '.shortcode-wcpr-grid-item .shortcode-reviews-images', function (e) {
            let this_image = $(this);
            let $shortcode_container = this_image.closest('.woocommerce-photo-reviews-shortcode');
            let reviews_shortcode = $shortcode_container.data('reviews_shortcode');
            if (reviews_shortcode.hasOwnProperty('masonry_popup') && reviews_shortcode.masonry_popup === 'image') {
                e.stopPropagation();
                let $container = this_image.closest('.shortcode-reviews-images-container');
                let data = [];
                $container.find('.shortcode-reviews-images-wrap-left').find('a').map(function () {
                    let current_image = $(this).find('.shortcode-reviews-images');
                    let href = $(this).data('image_src') ? $(this).data('image_src') : current_image.attr('src');
                    let title = $(this).data('image_caption') ? $(this).data('image_caption') : ((parseInt($(this).data('image_index')) + 1) + '/' + $container.find('.shortcode-reviews-images-wrap-left').find('a').length);
                    data.push({href: href, title: title});
                });
                if (data.length === 0) {
                    data.push({
                        href: this_image.data('original_src') ? this_image.data('original_src') : this_image.attr('src'),
                        title: this_image.parent().find('.shortcode-wcpr-review-image-caption').html()
                    });
                }
                $.swipebox(data, {hideBarsDelay: 100000, initialIndexOnArray: 0})
            }
        });
    }

    $('body').on('click', '.shortcode-wcpr-next', function () {
        showReview(current += 1);
    });
    $('body').on('click', '.shortcode-wcpr-prev', function () {
        showReview(current -= 1);
    });

    /*Mobile swipe support*/
    // let el = document.getElementById('shortcode-wcpr-modal-wrap');
    // if (el !== null) {
    //     viSwipeDetect(el, function (swipedir) {
    //         switch (swipedir) {
    //             case 'left':
    //                 $('.shortcode-wcpr-prev').click();
    //                 break;
    //             case 'right':
    //                 $('.shortcode-wcpr-next').click();
    //                 break;
    //             case 'up':
    //             case 'down':
    //                 $('.shortcode-wcpr-overlay').click();
    //                 break;
    //         }
    //     });
    // }
    jQuery(document.body).on('woocommerce_photo_reviews_shortcode_ajax_get_reviews', function () {
        setTimeout(function () {
            shortcode_wcpr_resize_masonry_items();
        },100);
    });
    /*Ajax pagination*/
    let ajax_pagination_running = false;
    let wcpr_image = '', wcpr_verified = '', wcpr_rating = '';
    $(document).on('click', '.shortcode-wcpr-load-more-reviews-button:not(.wcpr-loading)', function (e) {
        let $button = $(this);
        let $container = $button.closest('.woocommerce-photo-reviews-shortcode');
        wcpr_image = $container.data('wcpr_image');
        wcpr_verified = $container.data('wcpr_verified');
        wcpr_rating = $container.data('wcpr_rating');
        let reviews_shortcode = $container.data('reviews_shortcode');
        if (ajax_pagination_running) {
            return false;
        }
        ajax_pagination_running = true;
        e.preventDefault();
        $container.addClass('woocommerce-photo-reviews-shortcode-loading');
        $button.addClass('wcpr-loading');
        $.ajax({
            url: woocommerce_photo_reviews_shortcode_params.ajaxurl,
            type: 'get',
            data: {
                action: 'woocommerce_photo_reviews_shortcode_ajax_get_reviews',
                reviews_shortcode: JSON.stringify(reviews_shortcode),
                wcpr_page: $button.data('cpage'),
                wcpr_image: wcpr_image,
                wcpr_verified: wcpr_verified,
                wcpr_rating: wcpr_rating,
            },
            success: function (response) {
                let $html = jQuery('<div class="woocommerce_photo_reviews_shortcode_ajax_get_reviews"></div>');
                $html.css('display','none').html(response.html);
                if (reviews_shortcode.hasOwnProperty('style') && reviews_shortcode.style === 'masonry'){
                    $container.find('.shortcode-wcpr-grid').append($html.find('.shortcode-wcpr-grid').html());
                }else {
                    $container.find('.commentlist').append($html.find('.commentlist').html());
                }
                $container.find('.wcpr-load-more-reviews-button-container').replaceWith($html.find('.wcpr-load-more-reviews-button-container'));
                $container.data('wcpr_image', wcpr_image);
                $container.data('wcpr_verified', wcpr_verified);
                $container.data('wcpr_rating', wcpr_rating);
            },
            complete: function () {
                if (reviews_shortcode.hasOwnProperty('style') && reviews_shortcode.style === 'masonry') {
                    // triggerReviewClick();
                    if (reviews_shortcode.hasOwnProperty('masonry_popup') && reviews_shortcode.masonry_popup === 'image') {
                        triggerReviewImageClick()
                    }
                }
                // wcpr_helpful_button();
                fixBoxShadow();
                jQuery(document.body).trigger('woocommerce_photo_reviews_shortcode_ajax_get_reviews');
                ajax_pagination_running = false;
                $button.removeClass('wcpr-loading');
                $container.removeClass('woocommerce-photo-reviews-shortcode-loading');
            }
        });
    });
    $(document).on('click', 'a.wcpr-page-numbers.wcpr-page-numbers-nav', function (e) {
        let $container = jQuery(this).closest('.woocommerce-photo-reviews-shortcode');
        let reviews_shortcode = $container.data('reviews_shortcode');
        if (!reviews_shortcode.hasOwnProperty('pagination_ajax') || reviews_shortcode.pagination_ajax !== 'on') {
            return true;
        }
        e.preventDefault();
        e.stopPropagation();
        let wrap =jQuery(this).closest('.shortcode-wcpr-pagination');
        let i = wrap.find('.wcpr-page-numbers').index(wrap.find('.wcpr-page-numbers.wcpr-current'));
        if (jQuery(this).hasClass('wcpr-page-numbers-next')){
            i++;
        }else {
            i--;
        }
        if (i ===0 || i === wrap.find('.wcpr-page-numbers:not(.wcpr-page-numbers-nav)').length){
            return false;
        }
        wrap.find('.wcpr-page-numbers').eq(i).trigger('click');
    });
    $(document).on('click', 'a.wcpr-page-numbers:not(.wcpr-page-numbers-nav)', function (e) {
        let $button = $(this);
        let $container = $button.closest('.woocommerce-photo-reviews-shortcode');
        wcpr_image = $container.data('wcpr_image');
        wcpr_verified = $container.data('wcpr_verified');
        wcpr_rating = $container.data('wcpr_rating');
        let reviews_shortcode = $container.data('reviews_shortcode');
        if (!reviews_shortcode.hasOwnProperty('pagination_ajax') || reviews_shortcode.pagination_ajax !== 'on') {
            return true;
        }
        if (ajax_pagination_running) {
            return false;
        }
        let scrollTop = parseInt($container.offset().top);
        window.scrollTo({top: scrollTop, behavior: 'smooth'});
        ajax_pagination_running = true;
        e.preventDefault();
        $container.addClass('woocommerce-photo-reviews-shortcode-loading');
        $.ajax({
            url: woocommerce_photo_reviews_shortcode_params.ajaxurl,
            type: 'get',
            data: {
                action: 'woocommerce_photo_reviews_shortcode_ajax_get_reviews',
                reviews_shortcode: JSON.stringify(reviews_shortcode),
                wcpr_page: parseInt($button.html()),
                wcpr_image: wcpr_image,
                wcpr_verified: wcpr_verified,
                wcpr_rating: wcpr_rating,
            },
            success: function (response) {
                $container.html(response.html);
                $container.data('wcpr_image', wcpr_image);
                $container.data('wcpr_verified', wcpr_verified);
                $container.data('wcpr_rating', wcpr_rating);
            },
            error: function (err) {

            },
            complete: function () {
                if (reviews_shortcode.hasOwnProperty('style') && reviews_shortcode.style === 'masonry') {
                    // triggerReviewClick();
                    if (reviews_shortcode.hasOwnProperty('masonry_popup') && reviews_shortcode.masonry_popup === 'image') {
                        triggerReviewImageClick()
                    }
                }
                // wcpr_helpful_button();
                fixBoxShadow();
                jQuery(document.body).trigger('woocommerce_photo_reviews_shortcode_ajax_get_reviews');
                ajax_pagination_running = false;
                $container.removeClass('woocommerce-photo-reviews-shortcode-loading');
            }
        });
    });
    $(document).on('click', 'a.shortcode-wcpr-filter-button', function (e) {
        let $button = $(this);
        let $container = $button.closest('.woocommerce-photo-reviews-shortcode');
        let reviews_shortcode = $container.data('reviews_shortcode');
        if (!reviews_shortcode.hasOwnProperty('pagination_ajax') || reviews_shortcode.pagination_ajax !== 'on') {
            return;
        }
        if (ajax_pagination_running || (parseInt($button.find('.shortcode-wcpr-filter-button-count').html()) === 0 && !$button.hasClass('shortcode-wcpr-active'))) {
            return false;
        }
        wcpr_image = $container.data('wcpr_image');
        wcpr_verified = $container.data('wcpr_verified');
        wcpr_rating = $container.data('wcpr_rating');
        let filter_type = $button.data('filter_type');
        switch (filter_type) {
            case 'all':
                if ($button.hasClass('shortcode-wcpr-active')) {
                    return false;
                } else {
                    wcpr_rating = '';
                }
                break;
            case 'image':
                if ($button.hasClass('shortcode-wcpr-active')) {
                    wcpr_image = '';
                } else {
                    wcpr_image = 1;
                }

                break;
            case 'verified':
                if ($button.hasClass('shortcode-wcpr-active')) {
                    wcpr_verified = '';
                } else {
                    wcpr_verified = 1;
                }
                break;
            default:
                if ($button.hasClass('shortcode-wcpr-active')) {
                    return false;
                } else {
                    wcpr_rating = filter_type;
                }
        }
        let scrollTop = parseInt($container.offset().top);
        window.scrollTo({top: scrollTop, behavior: 'smooth'});
        ajax_pagination_running = true;
        e.preventDefault();
        $container.addClass('woocommerce-photo-reviews-shortcode-loading');
        $.ajax({
            url: woocommerce_photo_reviews_shortcode_params.ajaxurl,
            type: 'get',
            data: {
                action: 'woocommerce_photo_reviews_shortcode_ajax_get_reviews',
                reviews_shortcode: JSON.stringify(reviews_shortcode),
                wcpr_image: wcpr_image,
                wcpr_verified: wcpr_verified,
                wcpr_rating: wcpr_rating,
            },
            success: function (response) {
                $container.html(response.html);
                $container.data('wcpr_image', wcpr_image);
                $container.data('wcpr_verified', wcpr_verified);
                $container.data('wcpr_rating', wcpr_rating);
            },
            error: function (err) {

            },
            complete: function () {
                if (reviews_shortcode.hasOwnProperty('style') && reviews_shortcode.style === 'masonry') {
                    // triggerReviewClick();
                    if (reviews_shortcode.hasOwnProperty('masonry_popup') && reviews_shortcode.masonry_popup === 'image') {
                        triggerReviewImageClick()
                    }
                }
                // wcpr_helpful_button();
                fixBoxShadow();
                jQuery(document.body).trigger('woocommerce_photo_reviews_shortcode_ajax_get_reviews');
                ajax_pagination_running = false;
                $container.removeClass('woocommerce-photo-reviews-shortcode-loading');
            }
        });
    });

    function wcpr_variation_form($current) {
        let $product_summary = $('.shortcode-wcpr-single-product-summary'),
            $form_variation = $product_summary.find('.shortcode-wcpr-variations_form');
        $form_variation.each(function () {
            $(this).addClass('variations_form vi_wpvs_variation_form');
            $(this).find('select').each(function (k, v) {
                $(this).val(jQuery($current).find('select').eq(k).val()).trigger('change')
            });
            $(this).wc_variation_form();
            // WooCommerce Product Variations Swatches plugin of VillaTheme
            $(document.body).trigger('vi_wpvs_variation_form');
            // WooCommerce Price Based on Country (Basic) plugin of Oscar Gare v:2.0.15
            $(document.body).trigger('wc_price_based_country_ajax_geolocation');
        })
    }
});
jQuery(window).on('load',function () {
    shortcode_wcpr_resize_masonry_items();
}).on('resize', function () {
    shortcode_wcpr_resize_masonry_items();
});
function shortcode_wcpr_resize_masonry_items(){
    let row_height = 1,
        reviews_shortcode = jQuery('.woocommerce-photo-reviews-shortcode').data('reviews_shortcode');
    let row_gap =reviews_shortcode ?  parseInt(reviews_shortcode.cols_gap||0) :0;
    if (!row_gap) {
        row_gap = 20;
    }
    jQuery('.wcpr-grid-loadmore .shortcode-wcpr-grid-item:not(.wcpr-grid-item-init)').each(function () {
        shortcode_wcpr_resize_masonry_item(jQuery(this),row_height, row_gap );
    });
}
function shortcode_wcpr_resize_masonry_item(item,row_height, row_gap ) {
    item = jQuery(item);
    let item_img, img_height = 0;
    if (item.find('.shortcode-reviews-images-wrap-right .shortcode-reviews-images').length) {
        item_img = item.find('.shortcode-reviews-images-wrap-right .shortcode-reviews-images');
        img_height = item_img.outerHeight();
        if (img_height === 0) {
            let item_width = item.find('.shortcode-wcpr-content').outerWidth(),
                img_width = item_img.attr('width') || 0,
                img_height_t = item_img.attr('height') || 0;
            img_height = img_height_t !== 0 ? Math.round((item_width / img_width) * img_height_t) : item_width;
        }
    }
    let item_height = item.find('.shortcode-wcpr-content').outerHeight(),
        item_content_height = item.find('.shortcode-review-content-container').outerHeight();
    if (item_height < (item_content_height + img_height)) {
        item_height = item_content_height + img_height;
    }
    let row_item = Math.ceil((item_height + row_gap) / (row_height + row_gap));
    item.addClass('wcpr-grid-item-init').css('grid-row-end', 'span ' + row_item);
}
function wcpr_enable_scroll() {
    'use strict';
    let scrollTop = parseInt(jQuery('html').css('top'));
    jQuery('html').removeClass('shortcode-wcpr-noscroll');
    jQuery('html,body').scrollTop(-scrollTop);
}

function wcpr_disable_scroll() {
    'use strict';
    if (jQuery(document).height() > jQuery(window).height()) {
        let scrollTop = (jQuery('html').scrollTop()) ? jQuery('html').scrollTop() : jQuery('body').scrollTop(); // Works for Chrome, Firefox, IE...
        jQuery('html').addClass('shortcode-wcpr-noscroll').css('top', -scrollTop);
    }
}