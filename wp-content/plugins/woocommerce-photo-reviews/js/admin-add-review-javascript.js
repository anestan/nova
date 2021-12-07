'use strict';
jQuery(document).ready(function ($) {
    /*validation*/
    $('input[name="wcpr_admin_add_review"]').on('click', function () {
        $('.vi-ui.warning.message').hide();
        if ($('.wcpr-product-search').val() == null) {
            $('.wcpr-product-search').focus();
            $('.wcpr-warning-message-product-id').show();
            return false;
        }
        if ($('.vi-wcpr-add-review-customer-name').val() == '') {
            $('.vi-wcpr-add-review-customer-name').focus();
            $('.wcpr-warning-message-customer-name').show();
            return false;
        }
        if ($(this).data('empty_content')){
            return  true;
        }
        let content = tinyMCE.get('vi_wcpr_add_review_content') ? tinyMCE.get('vi_wcpr_add_review_content').getContent() : $('#vi_wcpr_add_review_content').val();
        if (content == '') {
            tinyMCE.get('vi_wcpr_add_review_content').focus();
            $('.wcpr-warning-message-content').show();
            return false;
        }
    });
    $('.vi-ui.dropdown').dropdown();
    /*Color picker*/
    $('.color-picker').not('.verified-color').iris({
        change: function (event, ui) {
            $(this).parent().find('.color-picker').css({backgroundColor: ui.color.toString()});
            let ele = $(this).data('ele');
            if (ele == 'highlight') {
                $('#message-purchased').find('a').css({'color': ui.color.toString()});
            } else if (ele == 'textcolor') {
                $('#message-purchased').css({'color': ui.color.toString()});
            } else {
                $('#message-purchased').css({backgroundColor: ui.color.toString()});
            }
        },
        hide: true,
        border: true
    }).click(function () {
        $('.iris-picker').hide();
        $(this).closest('td').find('.iris-picker').show();
    });

    $('body').click(function () {
        $('.iris-picker').hide();
    });
    $('.color-picker').click(function (event) {
        event.stopPropagation();
    });
    $('.wcpr-product-search').select2({
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
    // Set all variables to be used in scope
    let frame,
        metaBox = $('#wcpr-comment-photos'), // Your meta box id here
        addImgLink = metaBox.find('.wcpr-upload-custom-img'),
        imgContainer = metaBox.find('#wcpr-new-image');

    // ADD IMAGE LINK
    addImgLink.on('click', function (event) {
        event.preventDefault();

        // If the media frame already exists, reopen it.
        if (frame) {
            frame.open();
            return;
        }

        // Create a new media frame
        frame = wp.media({
            title: 'Select or Upload Media Of Your Chosen Persuasion',
            button: {
                text: 'Use this media'
            },
            multiple: true  // Set to true to allow multiple files to be selected
        });


        // When an image is selected in the media frame...
        frame.on('select', function () {

            // Get media attachment details from the frame state
            let attachment = frame.state().get('selection').toJSON();
            let attachment_url;
            if(attachment.length>0){
                for(let i=0;i<attachment.length;i++){
                    if (attachment[i].sizes.thumbnail) {
                        attachment_url = attachment[i].sizes.thumbnail.url;
                    } else if (attachment[i].sizes.medium) {
                        attachment_url = attachment[i].sizes.medium.url;
                    } else if (attachment[i].sizes.large) {
                        attachment_url = attachment[i].sizes.large.url;
                    } else if (attachment[i].url) {
                        attachment_url = attachment[i].url;
                    }
                    // Send the attachment URL to our custom image input field.
                    imgContainer.append('<div class="wcpr-review-image-container"><img style="border: 1px solid;"class="review-images" src="' + attachment_url + '"/><input class="photo-reviews-id" name="vi_wcpr_add_review_images[]" type="hidden" value="' + attachment[i].id + '"/><span class="wcpr-remove-image">Remove</span></div>');
                }
            }
            

        });

        // Finally, open the modal on click
        frame.open();
    });
    // DELETE IMAGE LINK

    $('body').on('click', '.wcpr-remove-image', function (event) {
        event.preventDefault();
        $(this).parent().remove();
    })

});
