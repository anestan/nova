jQuery(window).on('elementor/frontend/init', () => {
    'use strict';
    elementorFrontend.hooks.addAction('frontend/element_ready/woocommerce-photo-reviews.default', function ($scope) {
        if (!window.elementor) {
            return;
        }
        wcpr_helpful_button();
    })
});
jQuery(document).ready(function ($) {
    'use strict';
    let image_caption_enable = woocommerce_photo_reviews_params.image_caption_enable == 1;
    let i18n_image_caption = woocommerce_photo_reviews_params.i18n_image_caption;

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

    /*helpful button*/
    wcpr_helpful_button();
    let max_files = woocommerce_photo_reviews_params.max_files;
    $('#commentform').on('change', '.wcpr_image_upload', function (e) {
        $(this).parent().find('.wcpr-selected-image-container').html('');
        if (this.files.length > max_files) {
            alert(woocommerce_photo_reviews_params.warning_max_files);
            $(this).val('');
            return false;
        } else if (this.files.length > 0) {
            readURL(this);
        }
    });

    $('#commentform').find('input[type="submit"]').on('click', function (e) {
        let $container = $(this).closest('form');
        let $content = $container.find('textarea[id="comment"]')|| $container.find('textarea[name="comment"]');
        let $name = $container.find('input[name="author"]');
        let $email = $container.find('input[name="email"]');
        if ($content.length > 0 && !$content.val() && woocommerce_photo_reviews_params.allow_empty_comment != 1) {
            alert(woocommerce_photo_reviews_params.i18n_required_comment_text);
            e.preventDefault();
            $content.focus();
            return false;
        }
        if ('on' === woocommerce_photo_reviews_params.enable_photo) {
            let $fileUpload = $container.find('.wcpr_image_upload');
            if ($fileUpload.length > 0) {
                let imagesCount = parseInt($fileUpload.get(0).files.length);
                if ('on' === woocommerce_photo_reviews_params.required_image && imagesCount === 0) {
                    alert(woocommerce_photo_reviews_params.warning_required_image);
                    e.preventDefault();
                    return false;
                }
                if (imagesCount > max_files) {
                    alert(woocommerce_photo_reviews_params.warning_max_files);
                    e.preventDefault();
                    return false;
                }
            } else if ('on' === woocommerce_photo_reviews_params.required_image) {
                alert(woocommerce_photo_reviews_params.warning_required_image);
                e.preventDefault();
                return false;
            }
        }
        if ($name.length > 0 && $name.attr('required') && !$name.val()) {
            alert(woocommerce_photo_reviews_params.i18n_required_name_text);
            e.preventDefault();
            $name.focus();
            return false;
        }
        if ($email.length > 0 && $email.attr('required') && !$email.val()) {
            alert(woocommerce_photo_reviews_params.i18n_required_email_text);
            e.preventDefault();
            $email.focus();
            return false;
        }
        if ($container.find('input[name="wcpr_gdpr_checkbox"]').prop('checked') === false) {
            alert(woocommerce_photo_reviews_params.warning_gdpr);
            e.preventDefault();
            return false;
        }
    });
    let comments = woocommerce_photo_reviews_params.hasOwnProperty('comments_container_id') ? woocommerce_photo_reviews_params.comments_container_id : 'comments';
    let $comments = $('#' + comments);
    $comments.prepend($('.wcpr-filter-container')).prepend($('.wcpr-overall-rating-and-rating-count')).prepend($('.woocommerce-Reviews-title').eq(0));
    /*Ajax pagination*/
    if (woocommerce_photo_reviews_params.pagination_ajax && $comments.length) {
        let $pagination_container = $comments.find('.woocommerce-pagination');
        if (woocommerce_photo_reviews_params.loadmore_button) {
            $pagination_container.html(jQuery('.wcpr-load-more-reviews-button-modal').html());
            wcpr_pagination_loadmore($comments, $comments.find('.woocommerce-pagination'));
        }else {
            wcpr_pagination_basic($comments, $comments.find('.woocommerce-pagination'));
        }
    }
    $(document).on('click', '.reviews_tab', function () {
        $comments = $('#' + comments);
        if (($('.wcpr-filter-container').length > 0 && $comments.find('.wcpr-filter-container').length === 0) || ($('.wcpr-overall-rating-and-rating-count').length > 0 && $comments.find('.wcpr-overall-rating-and-rating-count').length === 0)) {
            $comments.prepend($('.wcpr-filter-container')).prepend($('.wcpr-overall-rating-and-rating-count')).prepend($('.woocommerce-Reviews-title').eq(0));
        }
    })
});
function wcpr_pagination_basic($comments,$pagination_container) {
    $comments = jQuery($comments);
    $pagination_container = jQuery($pagination_container);
    let ajax_pagination_running = false;
    jQuery(document).on('click','.woocommerce-pagination a',function (e) {
        if (ajax_pagination_running ) {
            return false;
        }
        e.preventDefault();
        e.stopPropagation();
        let $container = woocommerce_photo_reviews_params.display ==='1'? $comments.find('.wcpr-grid') : $comments.find(woocommerce_photo_reviews_params.container);
        let scrollTop = parseInt($container.offset().top) - 200;
        window.scrollTo({top: scrollTop, behavior: 'smooth'});
        ajax_pagination_running = true;
        let url = jQuery(this).attr('href');
        if (!$container.find('.wcpr-grid-overlay').length){
            $container.append('<div class="wcpr-grid-overlay wcpr-hidden"></div>');
        }
        let $overlay = $container.find('.wcpr-grid-overlay');
        $overlay.removeClass('wcpr-hidden');
        jQuery.ajax({
            url: url,
            type: 'get',
            success: function (response) {
                if (response) {
                    response = response.replace(/(\r\n\t|\n|\r\t)/gm, "");
                    let $reg;
                    if (woocommerce_photo_reviews_params.display ==='1'){
                        $reg = new RegExp('<div class="' + woocommerce_photo_reviews_params.grid_class + '" data-wcpr_columns="'+$container.data('wcpr_columns')+'">([^]+?)<div class="wcpr-grid-overlay', 'gm');
                    }else {
                        let first_char = woocommerce_photo_reviews_params.container.substr(0, 1),
                            $container_tag = $container.get()[0].localName;
                        if (first_char === '.') {
                            $reg =new RegExp('<'+$container_tag+' class="' +woocommerce_photo_reviews_params.container.substr(1) + '">([^]+?)</'+$container_tag+'>', 'gm');
                        } else {
                            $reg = new RegExp('<'+$container_tag+' id="' +woocommerce_photo_reviews_params.container.substr(1) + '">([^]+?)</'+$container_tag+'>', 'gm');
                        }
                    }
                    let match = $reg.exec(response);
                    if (match != null) {
                        // console.log(match[1].substr(0, match[1].length - 6) )
                        $container.html(match[1]+ '<div class="wcpr-grid-overlay wcpr-hidden"></div>');
                        // wcpr_helpful_button();
                    }
                    $reg = /class="woocommerce-pagination">([^]+?)<\/nav>/gm;
                    match = $reg.exec(response);
                    if (match != null) {
                        $pagination_container.html(match[1])
                    }
                    jQuery(document.body).trigger('wcpr_ajax_pagination_end');
                }
                $overlay.addClass('wcpr-hidden');
                ajax_pagination_running = false;
            },
            error: function (err) {
                ajax_pagination_running = false;
                $overlay.addClass('wcpr-hidden');
            }
        });
    });
    let $filters = $comments.find('.wcpr-filter-container');
    if ($filters.length ) {
        jQuery(document).on('click', 'a.wcpr-filter-button', function (e) {
            let $button = jQuery(this);
            if (ajax_pagination_running || (parseInt($button.find('.wcpr-filter-button-count').html()) === 0 && !$button.hasClass('wcpr-active'))) {
                return false;
            }
            ajax_pagination_running = true;
            e.preventDefault();
            let url = jQuery(this).attr('href');
            let $container = woocommerce_photo_reviews_params.display ==='1'? $comments.find('.wcpr-grid') : $comments.find(woocommerce_photo_reviews_params.container);
            if ($container.length === 0) {
                if (woocommerce_photo_reviews_params.display ==='1'){
                    $comments.append('<div class="' + woocommerce_photo_reviews_params.grid_class + '"><div class="wcpr-grid-overlay"></div></div>');
                    $container = $comments.find('.wcpr-grid');
                }else {
                    let first_char = woocommerce_photo_reviews_params.container.substr(0, 1);
                    if (first_char === '.') {
                        $comments.append('<ol class="' + woocommerce_photo_reviews_params.container.substr(1)  + '"></ol>');
                    } else {
                        $comments.append('<ol id="' + woocommerce_photo_reviews_params.container.substr(1)  + '"></ol>');
                    }
                    $container = $comments.find(woocommerce_photo_reviews_params.container);
                }
                $comments.find('.woocommerce-noreviews').hide();
            }
            if (!$container.find('.wcpr-grid-overlay').length){
                $container.append('<div class="wcpr-grid-overlay wcpr-hidden"></div>');
            }
            let $overlay = $container.find('.wcpr-grid-overlay');
            $overlay.removeClass('wcpr-hidden');
            $filters.addClass('wcpr-filter-loading');
            jQuery.ajax({
                url: url,
                type: 'get',
                data: {
                    'wcpr_is_ajax': 1
                },
                success: function (response) {
                    if (response) {
                        response = response.replace(/(\r\n\t|\n|\r\t)/gm, "");
                        let $reg;
                        if (woocommerce_photo_reviews_params.display ==='1'){
                            $reg = new RegExp('<div class="' + woocommerce_photo_reviews_params.grid_class + '" data-wcpr_columns="'+$container.data('wcpr_columns')+'">([^]+?)<div class="wcpr-grid-overlay', 'gm');
                        }else {
                            let first_char = woocommerce_photo_reviews_params.container.substr(0, 1),
                                $container_tag = $container.get()[0].localName;
                            if (first_char === '.') {
                                $reg =new RegExp('<'+$container_tag+' class="' +woocommerce_photo_reviews_params.container.substr(1) + '">([^]+?)</'+$container_tag+'>', 'gm');
                            } else {
                                $reg = new RegExp('<'+$container_tag+' id="' +woocommerce_photo_reviews_params.container.substr(1) + '">([^]+?)</'+$container_tag+'>', 'gm');
                            }
                        }
                        let match = $reg.exec(response);
                        if (match != null) {
                            $container.html(match[1] + '<div class="wcpr-grid-overlay wcpr-hidden"></div>');
                            // triggerReadMoreButton();
                            // wcpr_helpful_button();
                        } else {
                            $container.html('<div class="wcpr-grid-overlay wcpr-hidden"></div>');
                        }
                        $reg = /class="woocommerce-pagination">([^]+?)<\/nav>/gm;
                        match = $reg.exec(response);
                        if (match != null) {
                            if ($pagination_container && $pagination_container.length ) {
                                $pagination_container.html(match[1]);
                            } else {
                                $comments.append('<nav class="woocommerce-pagination">' + match[1] + '</nav>');
                                $pagination_container = $comments.find('.woocommerce-pagination');
                            }
                        } else {
                            if ($pagination_container.length > 0) {
                                $pagination_container.remove();
                                $pagination_container = null;
                            }
                        }
                        $reg = /<div class="wcpr-filter-container" style="display: none;">([^]+?)<\/div>/gm;
                        match = $reg.exec(response);
                        if (match != null) {
                            $filters.html(match[1]);
                        }
                        jQuery(document.body).trigger('wcpr_ajax_pagination_end');
                    }
                },
                error: function (err) {
                    console.log(err);
                },
                complete: function () {
                    ajax_pagination_running = false;
                    $overlay.addClass('wcpr-hidden');
                    $filters.removeClass('wcpr-filter-loading');
                }
            });
        });
    }
}
function wcpr_pagination_loadmore($comments,$pagination_container) {
    $comments = jQuery($comments);
    $pagination_container = jQuery($pagination_container);
    let  $filters = $comments.find('.wcpr-filter-container'),
        cpage = jQuery('.wcpr-load-more-reviews-cpage').val(),
        parent_post_id = jQuery('.wcpr-load-more-reviews-product-id').val(),
        rating = jQuery('.wcpr-load-more-reviews-rating').val(),
        verified = jQuery('.wcpr-load-more-reviews-verified').val(),
        image = jQuery('.wcpr-load-more-reviews-image').val(),
        $no_review = $comments.find('.woocommerce-noreviews'),
        $container = woocommerce_photo_reviews_params.display ==='1'? $comments.find('.wcpr-grid') : $comments.find(woocommerce_photo_reviews_params.container);
    function handle_missing_container() {
        if ($container.length === 0) {
            let first_char = woocommerce_photo_reviews_params.display ==='1'? '.': woocommerce_photo_reviews_params.container.substr(0, 1);
            if (first_char === '.') {
                $container =woocommerce_photo_reviews_params.display ==='1'? jQuery('<div class="' + woocommerce_photo_reviews_params.grid_class + '"></div>') :jQuery('<ol class="' + woocommerce_photo_reviews_params.container.substr(1) + '"></ol>');
            } else {
                $container = jQuery('<ol id="' + woocommerce_photo_reviews_params.container.substr(1) + '"></ol>');
            }
            $comments.append($container);
        }
    }

    function handle_missing_pagination() {
        if ($pagination_container.length === 0) {
            $comments.append('<nav class="woocommerce-pagination"></nav>');
            $pagination_container = $comments.find('.woocommerce-pagination');
        }
    }

    if (cpage && parent_post_id ) {
        if (cpage > 1) {
            let $button = $comments.find('.wcpr-load-more-reviews-button');
            jQuery.ajax({
                url: woocommerce_photo_reviews_params.ajaxurl, // AJAX handler, declared before
                data: {
                    'action': 'wcpr_ajax_load_more_reviews', // wp_ajax_cloadmore
                    'post_id': parent_post_id, // the current post
                    'cpage': cpage, // current comment page
                    'rating': rating,
                    'verified': verified,
                    'image': image,
                    'is_shortcode': '',
                    'frontend_style': woocommerce_photo_reviews_params.display,
                },
                type: 'POST',
                beforeSend:function(){
                    $button.addClass('wcpr-loading');
                },
                success: function (response) {
                    if (response.html) {
                        handle_missing_container();
                        $container.html(response.html);
                    }
                    jQuery(document.body).trigger('wcpr_ajax_load_more_reviews_end');
                    // wcpr_helpful_button();
                },
                complete: function () {
                    $button.removeClass('wcpr-loading');
                }
            });
        }
        jQuery(document).on('click', '.wcpr-load-more-reviews-button:not(.shortcode-wcpr-load-more-reviews-button):not(.wcpr-loading)', function () {
            let $button = jQuery(this);
            if (woocommerce_photo_reviews_params.sort == 2) {
                cpage++;
                if (cpage == 1) {
                    cpage++;
                }
            } else {
                cpage--;
                if (cpage == 1) {
                    cpage--;
                }
            }
            if (cpage > -1) {
                jQuery.ajax({
                    url: woocommerce_photo_reviews_params.ajaxurl, // AJAX handler, declared before
                    data: {
                        'action': 'wcpr_ajax_load_more_reviews', // wp_ajax_cloadmore
                        'post_id': parent_post_id, // the current post
                        'cpage': cpage, // current comment page
                        'rating': rating,
                        'verified': verified,
                        'image': image,
                        'is_shortcode': '',
                        'frontend_style': woocommerce_photo_reviews_params.display,
                    },
                    type: 'POST',
                    beforeSend: function (xhr) {
                        $button.addClass('wcpr-loading');
                    },
                    success: function (response) {
                        if (response.html) {
                            $container.append(response.html);
                            // if the last page, remove the button
                            if (cpage == 0)
                                $button.parent().remove();
                        } else {
                            $button.parent().remove();
                        }
                        jQuery(document.body).trigger('wcpr_ajax_load_more_reviews_end');
                        // wcpr_helpful_button();
                    },
                    complete: function () {
                        $button.removeClass('wcpr-loading');
                    }
                });
            }
            return false;
        });
        let ajax_pagination_running = false;
        if ($filters.length ) {
            let $filters_rating = $filters.find('.wcpr-filter-button-ul');
            jQuery(document).on('click', 'a.wcpr-filter-button', function (e) {
                let $button = jQuery(this);
                if (ajax_pagination_running || (parseInt($button.find('.wcpr-filter-button-count').html()) === 0 && !$button.hasClass('wcpr-active'))) {
                    return false;
                }
                cpage = 0;
                let filter_type = $button.data('filter_type');
                switch (filter_type) {
                    case 'all':
                        if ($button.hasClass('wcpr-active')) {
                            return false;
                        } else {
                            rating = '';
                        }
                        break;
                    case 'image':
                        if ($button.hasClass('wcpr-active')) {
                            image = '';
                        } else {
                            image = 1;
                        }

                        break;
                    case 'verified':
                        if ($button.hasClass('wcpr-active')) {
                            verified = '';
                        } else {
                            verified = 1;
                        }

                        break;
                    default:
                        if ($button.hasClass('wcpr-active')) {
                            return false;
                        } else {
                            rating = parseInt(filter_type);
                        }
                }
                ajax_pagination_running = true;
                e.preventDefault();
                $filters.addClass('wcpr-filter-loading');
                jQuery.ajax({
                    url: woocommerce_photo_reviews_params.ajaxurl, // AJAX handler, declared before
                    type: 'POST',
                    data: {
                        'action': 'wcpr_ajax_load_more_reviews', // wp_ajax_cloadmore
                        'post_id': parent_post_id, // the current post
                        'cpage': cpage, // current comment page
                        'rating': rating,
                        'verified': verified,
                        'image': image,
                        'filter_type': filter_type,
                        'is_shortcode': '',
                        'frontend_style': woocommerce_photo_reviews_params.display,
                    },
                    success: function (response) {
                        if (woocommerce_photo_reviews_params.sort == 2) {
                            cpage++;
                        } else {
                            cpage--;
                        }
                        if (response.html) {
                            $no_review.hide();
                            handle_missing_container();
                        }
                        $container.html(response.html);
                        cpage = parseInt(response.cpage);
                        handle_missing_pagination();
                        $pagination_container.html(response.load_more_html);
                        // wcpr_helpful_button();
                        let update_count = response.update_count;
                        if (update_count) {
                            for (let i in update_count) {
                                if (update_count.hasOwnProperty(i)) {
                                    $filters.find('.wcpr-filter-button[data-filter_type="' + i + '"]').find('.wcpr-filter-button-count').html(update_count[i]);
                                }
                            }
                        }
                        $filters.find('.wcpr-filter-rating-placeholder .wcpr-filter-button-count').html(update_count.all);
                        switch (filter_type) {
                            case 'all':
                                $filters_rating.find('.wcpr-filter-button').removeClass('wcpr-active');
                                $button.addClass('wcpr-active');
                                $filters.find('.wcpr-filter-rating-placeholder').html($button.html());
                                break;
                            case 'image':
                            case 'verified':
                                if ($button.hasClass('wcpr-active')) {
                                    $button.removeClass('wcpr-active');
                                } else {
                                    $button.addClass('wcpr-active');
                                }
                                break;
                            default:
                                $filters_rating.find('.wcpr-filter-button').removeClass('wcpr-active');
                                $button.addClass('wcpr-active');
                            // $filters.find('.wcpr-filter-rating-placeholder').html($button.html());
                        }
                        jQuery(document.body).trigger('wcpr_ajax_load_more_reviews_end');
                    },
                    error: function (err) {
                        console.log(err);
                    },
                    complete: function () {
                        ajax_pagination_running = false;
                        $filters.removeClass('wcpr-filter-loading');
                    }
                });
            });
        }
    }
}

function wcpr_helpful_button() {
    'use strict';
    jQuery(document).on('click','.wcpr-comment-helpful-button', function (e) {
        e.stopPropagation();
        let button = jQuery(this);
        let vote = button.hasClass('wcpr-comment-helpful-button-up-vote') ? 'up' : 'down';
        let container = button.closest('.wcpr-comment-helpful-button-container');
        let comment_id = container.data('comment_id');
        if (vote === 'up' && container.hasClass('wcpr-comment-helpful-button-voted-up')) {
            return;
        }
        if (vote === 'down' && container.hasClass('wcpr-comment-helpful-button-voted-down')) {
            return;
        }
        if (container.hasClass('wcpr-comment-helpful-button-voting')) {
            return;
        }
        container.addClass('wcpr-comment-helpful-button-voting');
        jQuery.ajax({
            url: woocommerce_photo_reviews_params.ajaxurl, // AJAX handler, declared before
            data: {
                'action': 'wcpr_helpful_button_handle',
                'vote': vote,
                'comment_id': comment_id,
                // 'nonce': woocommerce_photo_reviews_params.nonce,
            },
            type: 'POST',
            success: function (response) {
                if (response.status === 'success') {
                    container.find('.wcpr-comment-helpful-button-up-vote-count').html(parseInt(response.up));
                    container.find('.wcpr-comment-helpful-button-down-vote-count').html(parseInt(response.down));
                    if (vote === 'up') {
                        container.removeClass('wcpr-comment-helpful-button-voted-down').addClass('wcpr-comment-helpful-button-voted-up');
                    } else {
                        container.removeClass('wcpr-comment-helpful-button-voted-up').addClass('wcpr-comment-helpful-button-voted-down');
                    }
                    if (container.parent().hasClass('reviews-content-right-meta') || container.parent().hasClass('shortcode-reviews-content-right-meta')) {
                        let comment_container = jQuery('.wcpr-comment-helpful-button-container[data-comment_id="' + comment_id + '"]');
                        comment_container.find('.wcpr-comment-helpful-button-up-vote-count').html(parseInt(response.up));
                        comment_container.find('.wcpr-comment-helpful-button-down-vote-count').html(parseInt(response.down));
                        if (vote === 'up') {
                            comment_container.removeClass('wcpr-comment-helpful-button-voted-down').addClass('wcpr-comment-helpful-button-voted-up');
                        } else {
                            comment_container.removeClass('wcpr-comment-helpful-button-voted-up').addClass('wcpr-comment-helpful-button-voted-down');
                        }
                    }
                }
            },
            error: function () {

            },
            complete: function () {
                container.removeClass('wcpr-comment-helpful-button-voting');
            }
        });
    });
}

function viSwipeDetect(el, callback) {
    var touchsurface = el,
        swipedir,
        startX,
        startY,
        distX,
        distY,
        threshold = 150, //required min distance traveled to be considered swipe
        restraint = 100, // maximum distance allowed at the same time in perpendicular direction
        allowedTime = 300, // maximum time allowed to travel that distance
        elapsedTime,
        startTime,
        handleswipe = callback || function (swipedir) {
        };

    touchsurface.addEventListener(
        "touchstart",
        function (e) {
            var touchobj = e.changedTouches[0];
            swipedir = "none";
            startX = touchobj.pageX;
            startY = touchobj.pageY;
            startTime = new Date().getTime(); // record time when finger first makes contact with surface
        },
        false
    );

    touchsurface.addEventListener(
        "touchmove",
        function (e) {
            e.preventDefault(); // prevent scrolling when inside DIV
        },
        false
    );

    touchsurface.addEventListener(
        "touchend",
        function (e) {
            var touchobj = e.changedTouches[0];
            distX = touchobj.pageX - startX; // get horizontal dist traveled by finger while in contact with surface
            distY = touchobj.pageY - startY; // get vertical dist traveled by finger while in contact with surface
            elapsedTime = new Date().getTime() - startTime; // get time elapsed
            if (elapsedTime <= allowedTime) {
                // first condition for awipe met
                if (Math.abs(distX) >= threshold && Math.abs(distY) <= restraint) {
                    // 2nd condition for horizontal swipe met
                    swipedir = distX < 0 ? "left" : "right"; // if dist traveled is negative, it indicates left swipe
                } else if (
                    Math.abs(distY) >= threshold &&
                    Math.abs(distX) <= restraint
                ) {
                    // 2nd condition for vertical swipe met
                    swipedir = distY < 0 ? "up" : "down"; // if dist traveled is negative, it indicates up swipe
                }
            }
            handleswipe(swipedir);
        },
        false
    );
}
