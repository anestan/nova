jQuery(function ($) {

    'use strict';

    $(window).on('load', function(){
        
        $('.wpa-product-gallery .slick-track .slick-current > img:not(:first-child), .wpa-woocommerce-product-gallery .wpa-product-single-image > div img:not(:first-child)').remove();
        
        var wpawgZoom = $('.wpa-woocommerce-product-gallery').attr('data-zoom');
        if ($('.wpa-woocommerce-product-gallery').length > 0 && wpawgZoom == 'yes') {
            setTimeout(function(){
                WpaZoom();
            }, 100);
        }

        setTimeout(function(){
            var zoomThumb = $('.wpa-product-gallery .slick-track .slick-current img.zoomImg'),
            origZoomSrc = zoomThumb.attr('src');

            $( '.variations_form' ).on('found_variation', function( event, variation ) {
                if( variation.image.src ) {
                    zoomThumb.attr('src', variation.image.full_src);
                }
            });
        }, 500);
    });

    (function () {
        if ($('.wpa-product-gallery').length > 0) {

            var wpaVerticalLayout = Boolean($('.wpa-vertical-gallery').attr('data-vertical-layout')),
            wpaThumbnailShow = parseInt($('.wpa-woocommerce-product-gallery').attr('data-thumbnails')),
            wpaArrowShow = $('.wpa-woocommerce-product-gallery').attr('data-wpa-navigation'),
            wpaAutoPlay = $('.wpa-woocommerce-product-gallery').attr('data-autoplay'),
            wpaAutoPlaySpeed = $('.wpa-woocommerce-product-gallery').attr('data-autoplay-speed'),
            wpaCenterMode = $('.wpa-woocommerce-product-gallery').attr('data-centermode'),
            wpaNavColor = $('.wpa-woocommerce-product-gallery').attr('data-navigation-color'),
            wpaAdaptiveHeight = $('.wpa-woocommerce-product-gallery').attr('data-adaptive-height'),
            wpaRtl = false,
            wpaInfinite = false;

            // arrow visibility
            if (wpaArrowShow == 'yes') {
                wpaArrowShow = true;
            } else {
                wpaArrowShow = false;
            }

            // autoplay anable/diable
            if (wpaAutoPlay == 'yes') {
                wpaAutoPlay = true;
            } else {
                wpaAutoPlay = false;
            }

            // thumbnail center mode
            if (wpaCenterMode == 'yes') {
                wpaCenterMode = true;
                wpaInfinite = true;
            } else {
                wpaCenterMode = false;
            }

            // auto height mode
            if (wpaAdaptiveHeight == 'yes') {
                wpaAdaptiveHeight = true;
            } else {
                wpaAdaptiveHeight = false;
            }

            // nav color
            if (wpaNavColor) {
                wpaNavColor = 'color:' +wpaNavColor+ '';
            }

            //RTL
            if ($('body').hasClass("rtl")) {
               wpaRtl = true;
            }

            $('.wpa-product-gallery').slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: wpaArrowShow,
                autoplay: wpaAutoPlay,
                autoplaySpeed: wpaAutoPlaySpeed,
                adaptiveHeight: wpaAdaptiveHeight,
                infinite: false,
                touchMove: false,
                draggable: false,
                swipe: false,
                centerMode: false,
                fade: false,
                infinite: wpaInfinite,
                prevArrow: '<button class="wpawg-prev" style="'+wpaNavColor+'"><i class="flaticon-left-arrow-3"></i></button>',
                nextArrow: '<button class="wpawg-next" style="'+wpaNavColor+'"><i class="flaticon-right-arrow-2"></i></button>',
                asNavFor: '.wpa-product-gallery-thumbs',
                rtl: wpaRtl
            });

            $('.wpa-product-gallery-thumbs').slick({
                slidesToShow: parseInt(wpaThumbnailShow),
                slidesToScroll: 1,
                asNavFor: '.wpa-product-gallery',
                arrows: wpaArrowShow,
                autoplay: wpaAutoPlay,
                autoplaySpeed: wpaAutoPlaySpeed,
                centerMode: wpaCenterMode,
                focusOnSelect: true,
                infinite: wpaInfinite,
                prevArrow: '<button class="wpawg-prev" style="'+wpaNavColor+'"><i class="flaticon-left-arrow-2"></i></button>',
                nextArrow: '<button class="wpawg-next" style="'+wpaNavColor+'"><i class="flaticon-right-arrow-3"></i></button>',
                vertical: wpaVerticalLayout,
                rtl: wpaRtl
            });


            var firstImage = $('.wpa-product-gallery .slick-track .slick-current img');
            var firstThumb = $('.wpa-product-gallery-thumbs .slick-track .slick-current img');
            
            firstImage.removeAttr('srcset');
            firstThumb.removeAttr('srcset');
            
            var imagePopupSrc = $('.wpa-product-gallery .slick-track .slick-current .wpawg-image-popup');

            var origSrc = firstThumb.attr('src'),
                origSrcImg = firstImage.attr('src'),
                dataLargeImage = firstThumb.attr('data-large_image');

            var wpawgZoom = $('.wpa-woocommerce-product-gallery').attr('data-zoom');
            
            $( '.variations_form' ).on('found_variation', function( event, variation ) {
                $('.wpa-product-gallery').slick('slickGoTo', 0);

                if( variation.image.src ) {
                    firstImage.attr('src', variation.image.src);
                    firstThumb.attr('src', variation.image.gallery_thumbnail_src);
                }

                // console.log(variation.image.src);

                if( variation.image.src ) {
                    imagePopupSrc.attr('data-mfp-src', variation.image.full_src);
                }

                if ($('.wpa-woocommerce-product-gallery').length > 0 && wpawgZoom == 'yes') {
                    
                    $('.wpa-product-gallery .slick-track .slick-current img.zoomImg, .wpa-woocommerce-product-gallery .wpa-product-single-image > div img:not(:first-child)').remove();
                }
            });

            $( '.variations_form' ).on( 'reset_image', function() {
                firstImage.attr('src', origSrcImg);
                firstThumb.attr('src', origSrc);
                imagePopupSrc.attr('data-mfp-src', dataLargeImage);

                $('.wpa-product-gallery-thumbs').slick('slickGoTo', 0);
            });

            $( '.reset_variations' ).on( 'click', function() {
                if (wpawgZoom == 'yes') {
                    $('.wpa-product-gallery .slick-track .slick-current > img:not(:first-child), .wpa-woocommerce-product-gallery .wpa-product-single-image > div img:not(:first-child)').remove();

                    setTimeout(function(){
                        WpaZoom();
                    }, 100);
                }
            });

            // On before slide change
            $('.wpa-product-gallery').on('beforeChange', function(event, slick, currentSlide, nextSlide){
                $('.wpa-product-gallery .wpawg-video-popup, .wpa-product-gallery .wpawg-image-popup').css('display', 'none');
            });

            // On after slide change
            $('.wpa-product-gallery').on('afterChange', function(event, slick, currentSlide, nextSlide){
                $('.wpa-product-gallery .wpawg-video-popup, .wpa-product-gallery .wpawg-image-popup').css('display', 'block');
            });
        }

        $('.wpa-woocommerce-product-gallery').css({"opacity": "1"});
    }());



    /* ======= Magnific Popup for video ======= */
    (function(){
        if ($('.wpa-woocommerce-product-gallery').length > 0) {

            var wpaImageLightbox = $('.wpa-woocommerce-product-gallery').attr('data-image-lightbox');

            $('.wpa-product-gallery .slick-list, .wpa-product-single-image').magnificPopup({
                delegate: '.wpawg-image-popup',
                type: 'image',
                mainClass: 'mfp-with-fade',
                removalDelay: 300,
                fixedContentPos: true,
                image: {
                    verticalFit: true
                },
                gallery:{
                    enabled:true,
                    // navigateByImgClick: true
                },
                callbacks: {
                    open: function() {
                        var wpaMfp = $.magnificPopup.instance;
                        var wpaProto = $.magnificPopup.proto;
                        var wpaArrowRight = $('.mfp-arrow-right');
                        var wpaArrowLeft = $('.mfp-arrow-left');

                        // extend function that moves to next item
                        wpaMfp.next = function() {

                            // if index is not last, call parent method
                            if(wpaMfp.index < wpaMfp.items.length - 1) {
                                wpaProto.next.call(wpaMfp);
                                wpaArrowLeft.show();
                            } else {
                               // otherwise do whatever you want, e.g. hide "next" arrow
                               wpaArrowRight.hide();
                            }
                        };

                        // same with prev method
                        wpaMfp.prev = function() {
                            if(wpaMfp.index > 0) {
                                wpaProto.prev.call(wpaMfp);
                                wpaArrowRight.show();
                            } else {
                               // otherwise do whatever you want, e.g. hide "prev" arrow
                               wpaArrowLeft.hide();
                            }
                        };

                    }
                }
            });

            $(document).on('click', '.mfp-arrow-right', function(e){
              $('.wpawg-next').trigger( "click" );
            });
            $(document).on('click', '.mfp-arrow-left', function(e){
              $('.wpawg-prev').trigger( "click" );
            });
            
            
            /* ======= Magnific Popup for video ======= */
            $('.wpawg-video-popup').magnificPopup({
                disableOn: 200,
                type: 'iframe',
                mainClass: 'mfp-with-fade',
                removalDelay: 160,
                preloader: true,
                fixedContentPos: false,
                iframe: {
                patterns: {
                  dailymotion: {
                    index: 'dailymotion.com',
                    id: function(url) {
                        var m = url.match(/^.+dailymotion.com\/(video|hub)\/([^_]+)[^#]*(#video=([^_&]+))?/);
                        console.log(m);
                        if (m !== null) {
                            if(m[4] !== undefined) {
                                return m[4];
                            }
                            return m[2];
                        }
                        return null;
                    },
                    src: 'http://www.dailymotion.com/embed/video/%id%?autoplay=1'
                  },

                  facebook: {
                    index: 'facebook.com',
                    id: function(url) {
                        var videoId = url.match(/\d+[15]/g);
                        return videoId;
                    },
                    src: 'https://www.facebook.com/v2.5/plugins/video.php?href=https%3A%2F%2Fwww.facebook.com%2Fvideo.php%3Fv%3D%id%&autoplay=1'
                  },
                  srcAction: 'iframe_src'
                }
              }
            });
        }
    }());


    /* ======= Image Zoom ======= */
    var WpaZoom = function(){
        var wpawgZoom = $('.wpa-woocommerce-product-gallery').attr('data-zoom');
        if ($('.wpa-woocommerce-product-gallery').length > 0 && wpawgZoom == 'yes') {
            $('.wpa-product-gallery .slick-track .slick-slide, .wpa-woocommerce-product-gallery .wpa-product-single-image > div').zoom({
                on: 'mouseover', // other options: grab, click, toggle, mouseover
                duration: 500,
                magnify: 1
            });
        }
    }

    WpaZoom();
});