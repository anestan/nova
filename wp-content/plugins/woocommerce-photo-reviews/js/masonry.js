jQuery(document).ready(function ($) {
    'use strict';
    let isSafari = false;
    if (/iPad/i.test(navigator.userAgent) || (/Safari/i.test(navigator.userAgent) && /Apple Computer/.test(navigator.vendor) && !/Mobi|Android/i.test(navigator.userAgent))) {
        isSafari = true;
    } else {
        $('.wcpr-enable-box-shadow').addClass('wcpr-fix-box-shadow');
    }
    let current = -1;
    let slides;
    let swipeBoxIndex = 0;


    function triggerReviewClick() {
        $(document).on('click', '.wcpr-grid-item', function () {
            slides = $('.wcpr-grid-item');
            let i = slides.index($(this));
            if (i >= 0) {
                showReview(i);
                wcpr_disable_scroll();
                // wcpr_helpful_button();
            }
        });
    }

    function wcpr_enable_scroll() {
        let scrollTop = parseInt($('html').css('top'));
        $('html').removeClass('wcpr-noscroll');
        $('html,body').scrollTop(-scrollTop);
    }

    function wcpr_disable_scroll() {
        if ($(document).height() > $(window).height()) {
            let scrollTop = ($('html').scrollTop()) ? $('html').scrollTop() : $('body').scrollTop(); // Works for Chrome, Firefox, IE...
            $('html').addClass('wcpr-noscroll').css('top', -scrollTop);
        }
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
        let $left_modal = $('#reviews-content-left-modal');
        let $left_main = $('#reviews-content-left-main');
        let $grid = $('.wcpr-grid');
        $left_modal.html('');
        $left_main.html('');
        if ($grid.find('.wcpr-grid-item').eq(current).find('.reviews-images-container').length === 0) {
            $('.wcpr-modal-light-box').addClass('wcpr-no-images');
        } else {
            $left_modal.html(($grid.find('.wcpr-grid-item').eq(current).find('.reviews-images-wrap-left').html()));
            let img_data = $grid.find('.wcpr-grid-item').eq(current).find('.reviews-images-wrap-right').html();
            if (img_data) {
                $('.wcpr-modal-light-box').removeClass('wcpr-no-images');
                $left_main.html(img_data);
            }
            $left_modal.find('.reviews-images').map(function () {
                let lazy_load_src = $(this).data('src');
                if (lazy_load_src) {
                    $(this).attr('src', lazy_load_src)
                }
            });
            $left_modal.find('.reviews-images').closest('a').on('click', function () {
                swipeBoxIndex = $(this).data('image_index');
                let current_image_src = $(this).attr('href');
                $left_main.find('source').attr('srcset', current_image_src);
                $left_main.find('.reviews-images').attr('src', current_image_src);
                $left_main.find('.wcpr-review-image-caption').html($(this).data('image_caption'));
                return false;
            });
        }
        $('#reviews-content-right .reviews-content-right-meta').html($grid.find('.wcpr-grid-item').eq(current).find('.review-content-container').html());
        $('.wcpr-modal-light-box').fadeIn(200);
    }

    switch (woocommerce_photo_reviews_params.masonry_popup) {
        case 'review':
            if ($('.wcpr-grid-item')) {
                slides = $('.wcpr-grid-item');
            }
            $('.wcpr-close').on('click', function () {
                wcpr_enable_scroll();
                $('.wcpr-modal-light-box').fadeOut(200);
                current = -1;

            });
            $('.wcpr-modal-light-box .wcpr-overlay').on('click', function () {
                wcpr_enable_scroll();
                $('.wcpr-modal-light-box').fadeOut(200);
                current = -1;
            });
            $('#reviews-content-left-main').on('click', '.reviews-images', function () {
                let this_image = $(this);
                let data = [];
                $('#reviews-content-left-modal').find('a').map(function () {
                    let current_image = $(this).find('.reviews-images');
                    let href = $(this).data('image_src') ? $(this).data('image_src') : current_image.attr('src');
                    let title = $(this).data('image_caption') ? $(this).data('image_caption') : ((parseInt($(this).data('image_index')) + 1) + '/' + $('#reviews-content-left-modal').find('a').length);
                    data.push({href: href, title: title});
                });
                if (data.length == 0) {
                    data.push({
                        href: this_image.data('original_src') ? this_image.data('original_src') : this_image.attr('src'),
                        title: this_image.parent().find('.wcpr-review-image-caption').html()
                    });
                }
                $.swipebox(data, {hideBarsDelay: 100000, initialIndexOnArray: swipeBoxIndex})
            });

            $(document).keydown(function (e) {
                if ($.swipebox.isOpen) {
                    return;
                }
                if ($('.wcpr-modal-light-box').css('display') == 'none') {
                    return;
                }
                if (e.keyCode == 27) {
                    wcpr_enable_scroll();
                    $('.wcpr-modal-light-box').fadeOut(200);
                    current = -1;
                }
                if (current != -1) {
                    if (e.keyCode == 37) {
                        showReview(current -= 1);
                    }
                    if (e.keyCode == 39) {
                        showReview(current += 1);
                    }
                }
            });
            triggerReviewClick();
            $('.wcpr-next').on('click', function () {
                showReview(current += 1);
            });
            $('.wcpr-prev').on('click', function () {
                showReview(current -= 1);
            });
            /*Mobile swipe support*/
            // let el = document.getElementById('wcpr-modal-wrap');
            // if (el !== null) {
            //     viSwipeDetect(el, function (swipedir) {
            //         switch (swipedir) {
            //             case 'left':
            //                 $('.wcpr-prev').click();
            //                 break;
            //             case 'right':
            //                 $('.wcpr-next').click();
            //                 break;
            //             case 'up':
            //             case 'down':
            //                 $('.wcpr-overlay').click();
            //                 break;
            //         }
            //     });
            //
            // }
            break;
        case 'image':
            $(document).on('click', '.wcpr-grid-item .reviews-images', function (e) {
                let this_image = $(this);
                e.stopPropagation();
                let $container = this_image.closest('.reviews-images-container');
                let data = [];
                $container.find('.reviews-images-wrap-left').find('a').map(function () {
                    let current_image = $(this).find('.reviews-images');
                    let href = $(this).data('image_src') ? $(this).data('image_src') : current_image.attr('src');
                    let title = $(this).data('image_caption') ? $(this).data('image_caption') : ((parseInt($(this).data('image_index')) + 1) + '/' + $container.find('.reviews-images-wrap-left').find('a').length);
                    data.push({href: href, title: title});
                });
                if (data.length == 0) {
                    data.push({
                        href: this_image.data('original_src') ? this_image.data('original_src') : this_image.attr('src'),
                        title: this_image.parent().find('.wcpr-review-image-caption').html()
                    });
                }
                $.swipebox(data, {hideBarsDelay: 100000, initialIndexOnArray: 0})
            });
            break;
        case 'off':
        default:
    }
    jQuery(document.body).on('wcpr_ajax_load_more_reviews_end wcpr_ajax_pagination_end', function () {
        setTimeout(function () {
            wcpr_resize_masonry_items();
        },100);
    });
    jQuery(document.body).on('click', '.wcpr-read-more', function (e) {
        e.stopPropagation();
        let $button = $(this);
        let $comment_content = $button.closest('.wcpr-review-content');
        let $comment_content_full = $comment_content.find('.wcpr-review-content-full');
        let comment_content_full = $comment_content_full.html();
        if (comment_content_full) {
            $comment_content.html(comment_content_full);
        }
        $comment_content.closest('.wcpr-grid-item').removeClass('wcpr-grid-item-init');
        wcpr_resize_masonry_items();
    });
    return false;
});
jQuery(window).on('load',function () {
    wcpr_resize_masonry_items();
}).on('resize', function () {
    wcpr_resize_masonry_items();
});

function wcpr_resize_masonry_items() {
    jQuery('.wcpr-grid-loadmore .wcpr-grid-item:not(.wcpr-grid-item-init)').each(function () {
        wcpr_resize_masonry_item(jQuery(this));
    });
}

function wcpr_resize_masonry_item(item) {
    item = jQuery(item);
    let item_img, img_height = 0, item_width, colums= parseInt(jQuery('.wcpr-grid').data('wcpr_columns') || 3);
    if (item.find('.reviews-images-wrap-right .reviews-images').length) {
        item_img = item.find('.reviews-images-wrap-right .reviews-images');
        img_height = item_img.outerHeight();
        if (img_height === 0) {
            item_width = item.find('.wcpr-content').outerWidth() ;
                let img_width = parseFloat(item_img.attr('width') || 0),
                img_height_t = parseFloat(item_img.attr('height') || 0);
            if (item_width===0){
                item_width= ((jQuery('.wcpr-grid').outerWidth() ?jQuery('.wcpr-grid').outerWidth():(jQuery('#reviews').outerWidth()?jQuery('#reviews').outerWidth(): (jQuery('.woocommerce-Tabs-panel').outerWidth()?jQuery('.woocommerce-Tabs-panel').outerWidth(): 200)) )- ((colums - 1)*20) )/colums;
            }
            img_height = img_height_t !== 0 ? Math.round((item_width / img_width) * img_height_t) : item_width;
        }
    }
    let row_height = 1,// parseInt(jQuery('.wcpr-grid').css('grid-auto-rows')),
        row_gap = 20;//parseInt(jQuery('.wcpr-grid').css('grid-row-gap'));
    let item_height = item.find('.wcpr-content').outerHeight(),
        item_content_height = item.find('.review-content-container').outerHeight();
    if (item_content_height ===0 ){
        if (!item_width){
            item_width = item.find('.wcpr-content').outerWidth() ;
            if (item_width===0){
                item_width= ((jQuery('.wcpr-grid').outerWidth() ?jQuery('.wcpr-grid').outerWidth():(jQuery('#reviews').outerWidth()?jQuery('#reviews').outerWidth(): (jQuery('.woocommerce-Tabs-panel').outerWidth()?jQuery('.woocommerce-Tabs-panel').outerWidth(): 200)) )- ((colums - 1)*20) )/colums;
            }
        }
       jQuery('body').append('<div class="review-content-container-temp" style="width: '+item_width+'px; visibility: hidden; ">'+item.find('.review-content-container').html()+'</div>');
        let temp = jQuery('.review-content-container-temp');
        temp.find('.wcpr-review-content-full').remove();
        item_content_height = temp.outerHeight();
        temp.remove();
    }
    if (item_height < (item_content_height + img_height)) {
        item_height = item_content_height + img_height;
    }
    let row_item = Math.ceil((item_height + row_gap) / (row_height + row_gap));
    item.addClass('wcpr-grid-item-init').css('grid-row-end', 'span ' + row_item);
}