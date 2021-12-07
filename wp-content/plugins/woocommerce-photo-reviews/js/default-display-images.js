jQuery(document).ready(function ($) {
    'use strict';
    let $cur, $n, parent;
    $(document).on('click', '.reviews-images-item', function () {
        let $item = $(this);
        let $container = $item.closest('.kt-reviews-image-container');
        if ($container.hasClass('kt-reviews-image-container-image-popup-below_thumb')) {
            let currentRotate, rotateItem;
            parent = $(this).parent().parent();
            currentRotate = parseInt(parent.find('.wcpr-rotate-value').val());
            let $big_review_images = parent.find('.big-review-images');
            if ($(this).hasClass('active-image')) {
                $big_review_images.hide();
                $(this).removeClass('active-image');
            } else {
                $cur = $(this).data('index');
                $n = $(this).parent().find('.reviews-images-item').length;
                $(this).parent().find('.reviews-images-item').removeClass('active-image');
                $(this).addClass('active-image');
                parent.find('.big-review-images-content').html('');
                // $big_review_images.hide();
                $big_review_images.find('.big-review-images-content').append('<img class="big-review-images-content-img" style="float:left;display: block;border-radius: 3px;" src="' + $(this).data('image_src') + '">');
                $big_review_images.css({'display': 'table'});
                parent.find('.wcpr-review-image-caption').html($(this).data('image_caption'));
            }
            if (currentRotate) {
                rotateItem = parent.find('.big-review-images-content-container');
                rotateItem.css({'transform': 'rotate(' + currentRotate + 'deg)'});
            }

        } else {
            let this_image = $item.find('.review-images');
            let $image_container = this_image.closest('.kt-wc-reviews-images-wrap-wrap');
            let data = [];
            $image_container.find('.reviews-images-item').map(function () {
                let current_image = $(this).find('.review-images');
                let href = $(this).data('image_src') ? $(this).data('image_src') : current_image.attr('src');
                let title = $(this).data('image_caption') ? $(this).data('image_caption') : ((parseInt($(this).data('index')) + 1) + '/' + $image_container.find('.reviews-images-item').length);
                data.push({href: href, title: title});
            });
            $.swipebox(data, {hideBarsDelay: 100000, initialIndexOnArray: $item.data('index')})
        }
    });

    $(document).on('click', '.big-review-images-content-img', function () {
        let $image_container = $(this).closest('.kt-reviews-image-container').find('.kt-wc-reviews-images-wrap-wrap');
        let data = [];
        $image_container.find('.reviews-images-item').map(function () {
            let current_image = $(this).find('.review-images');
            let href = $(this).data('image_src') ? $(this).data('image_src') : current_image.attr('src');
            let title = $(this).data('image_caption') ? $(this).data('image_caption') : ((parseInt($(this).data('index')) + 1) + '/' + $image_container.find('.reviews-images-item').length);
            data.push({href: href, title: title});
        });
        $.swipebox(data, {
            hideBarsDelay: 100000,
            initialIndexOnArray: $image_container.find('.active-image').data('index')
        })
    });
    $(document).on('click', '.wcpr-next-normal', function () {
        let currentRotate, rotateItem;
        parent = $(this).parent().parent();
        currentRotate = parseInt(parent.find('.wcpr-rotate-value').val());
        $cur = parent.find('.active-image').data('index');
        $n = parent.find('.reviews-images-item').length;
        parent.find('.reviews-images-item').removeClass('active-image');
        if ($cur < $n - 1) {

            $cur++;

        } else {
            $cur = 0;
        }
        parent.find('.reviews-images-item').eq($cur).addClass('active-image');
        parent.find('.big-review-images-content').html('');
        let $big_review_images = parent.find('.big-review-images');
        // $big_review_images.hide();
        $big_review_images.find('.big-review-images-content').append('<img class="big-review-images-content-img" style="float:left;display: block;border-radius: 3px;" src="' + parent.find('.reviews-images-item').eq($cur).data('image_src') + '">')
        $big_review_images.css({'display': 'table'});
        parent.find('.wcpr-review-image-caption').html(parent.find('.reviews-images-item').eq($cur).data('image_caption'));
        if (currentRotate) {
            rotateItem = parent.find('.big-review-images-content-container');
            rotateItem.css({'transform': 'rotate(' + currentRotate + 'deg)'});
        }
    });
    $(document).on('click', '.wcpr-prev-normal', function () {
        let currentRotate, rotateItem;
        parent = $(this).parent().parent();
        currentRotate = parseInt(parent.find('.wcpr-rotate-value').val());
        $cur = parent.find('.active-image').data('index');
        $n = parent.find('.reviews-images-item').length;
        parent.find('.reviews-images-item').removeClass('active-image');
        if ($cur > 0) {

            $cur--;

        } else {
            $cur = $n - 1;
        }
        parent.find('.reviews-images-item').eq($cur).addClass('active-image');
        parent.find('.big-review-images-content').html('');
        let $big_review_images = parent.find('.big-review-images');
        // $big_review_images.hide();
        $big_review_images.find('.big-review-images-content').append('<img class="big-review-images-content-img" style="float:left;display: block;border-radius: 4px;" src="' + parent.find('.reviews-images-item').eq($cur).data('image_src') + '">')
        $big_review_images.css({'display': 'table'});
        parent.find('.wcpr-review-image-caption').html(parent.find('.reviews-images-item').eq($cur).data('image_caption'));
        if (currentRotate) {
            rotateItem = parent.find('.big-review-images-content-container');
            rotateItem.css({'transform': 'rotate(' + currentRotate + 'deg)'});
        }
    });

    $(document).on('click', '.wcpr-close-normal', function () {
        parent = $(this).parent().parent();
        $(this).parent().hide();
        parent.find('.kt-wc-reviews-images-wrap-wrap').find('.active-image').removeClass('active-image');
    });
    $(document).on('click', '.wcpr-rotate-left', function () {
        let currentRotate, rotateItem;
        parent = $(this).parent().parent();
        currentRotate = parseInt(parent.find('.wcpr-rotate-value').val());
        rotateItem = parent.find('.big-review-images-content-container');
        currentRotate += -90;
        parent.find('.wcpr-rotate-value').val(currentRotate);
        rotateItem.css({'transform': 'rotate(' + currentRotate + 'deg)'});
    });
    $(document).on('click', '.wcpr-rotate-right', function () {
        let currentRotate, rotateItem;
        parent = $(this).parent().parent();
        currentRotate = parseInt(parent.find('.wcpr-rotate-value').val());
        rotateItem = parent.find('.big-review-images-content-container');
        currentRotate += 90;
        parent.find('.wcpr-rotate-value').val(currentRotate);
        rotateItem.css({'transform': 'rotate(' + currentRotate + 'deg)'});
    });
    /*Ajax load more and filters for normal style*/
    let comments = woocommerce_photo_reviews_params.hasOwnProperty('comments_container_id') ? woocommerce_photo_reviews_params.comments_container_id : 'comments';
    let $filters = $('.wcpr-filter-container');
    let $comments = $('#' + comments);
    $comments.prepend($filters).prepend($('.wcpr-overall-rating-and-rating-count')).prepend($('.woocommerce-Reviews-title').eq(0));
});