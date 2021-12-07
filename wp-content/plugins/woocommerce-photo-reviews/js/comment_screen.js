'use strict';
jQuery(document).ready(function ($) {
    $('.ui-sortable').sortable({
        // update: function (event, ui) {
        //     indexChangeCal();
        // }
    });
    $('#postdiv').prepend($('#wcpr-review-title'));
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
        frame.on('open', function () {
            let selection = frame.state().get('selection');
            selection.reset([]);
        })
        // When an image is selected in the media frame...
        frame.on('select', function () {

            // Get media attachment details from the frame state
            let attachment = frame.state().get('selection').toJSON();
            let attachment_url;
            if (attachment.length > 0) {
                for (let i = 0; i < attachment.length; i++) {
                    if (attachment[i].sizes.thumbnail) {
                        attachment_url = attachment[i].sizes.thumbnail.url;
                    } else if (attachment[i].sizes.medium) {
                        attachment_url = attachment[i].sizes.medium.url;
                    } else if (attachment[i].sizes.large) {
                        attachment_url = attachment[i].sizes.large.url;
                    } else if (attachment[i].url) {
                        attachment_url = attachment[i].url;
                    }
                    // Send the attachment[i] URL to our custom image input field.
                    if (wcpr_admin_comment_params.image_caption_enable == 1) {
                        imgContainer.append('<div class="wcpr-review-image-container"><div class="wcpr-review-image-wrap">' +
                            '<div class="wcpr-review-image-caption">' + attachment[i].caption + '</div>' +
                            '<img style="border: 1px solid;" class="review-images" data-image_id="' + attachment[i].id + '" src="' + attachment_url + '"/><input class="photo-reviews-id" name="photo-reviews-id[]" type="hidden" value="' + attachment[i].id + '"/></div>' +
                            '<a href="#" class="wcpr-remove-image">Remove</a></div>');
                    } else {
                        imgContainer.append('<div class="wcpr-review-image-container"><div class="wcpr-review-image-wrap">' +
                            '<img style="border: 1px solid;" class="review-images" data-image_id="' + attachment[i].id + '" src="' + attachment_url + '"/><input class="photo-reviews-id" name="photo-reviews-id[]" type="hidden" value="' + attachment[i].id + '"/></div>' +
                            '<a href="#" class="wcpr-remove-image">Remove</a></div>');
                    }

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
    /*edit image*/
    let frame1;
    let image, image_container, selected;
    $('body').on('click', '.review-images', function (event) {
        image = $(this);
        image_container = image.parent().parent();
        selected = $(this).data('image_id');
        event.preventDefault();
        // If the media frame1 already exists, reopen it.
        if (frame1) {
            frame1.open();
            return;
        }

        // Create a new media frame1
        frame1 = wp.media({
            title: 'Select or Upload Media Of Your Chosen Persuasion',
            button: {
                text: 'Use this media'
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });
        frame1.on('select', function () {
            // Get media attachment details from the frame1 state
            let attachment = frame1.state().get('selection').toJSON();
            let attachment_url;
            if (attachment.length == 1) {
                if (attachment[0].sizes.thumbnail) {
                    attachment_url = attachment[0].sizes.thumbnail.url;
                } else if (attachment[0].sizes.medium) {
                    attachment_url = attachment[0].sizes.medium.url;
                } else if (attachment[0].sizes.large) {
                    attachment_url = attachment[0].sizes.large.url;
                } else if (attachment[0].url) {
                    attachment_url = attachment[0].url;
                }
                image.attr('src', attachment_url);
                image.data('image_id', attachment[0].id);
                image_container.find('.wcpr-review-image-caption').html(attachment[0].caption)
            }
        });
        // When an image is selected in the media frame1...
        frame1.on('open', function () {
            let selection = frame1.state().get('selection');
            selection.reset(selected ? [wp.media.attachment(selected)] : []);
        })
        frame1.open();
    })
});