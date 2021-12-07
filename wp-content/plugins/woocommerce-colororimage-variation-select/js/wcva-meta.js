var $gaz = jQuery.noConflict();
(function( $gaz ) {
	'use strict';


	$gaz('.wcvaheader').click(function(){

		$gaz(this).nextUntil('tr.wcvaheader').slideToggle(100, function(){
		});
	});

	$gaz('.subcollapsetr').click(function(){

		$gaz(this).nextUntil('tr.subcollapsetr').slideToggle(100, function(){
		});
	});


	$gaz(function() {
		$gaz('.wcvadisplaytype').on('change',function(){

			var zvalue= $gaz(this).val();

			if (zvalue == "colororimage") {

				$gaz(this).closest("div").find(".wcvametaupperdiv").show();
				$gaz(this).closest("div").find(".wcvaimageorcolordiv").show();

			} else if (zvalue == "variationimage") {

				$gaz(this).closest("div").find(".wcvametaupperdiv").show();
				$gaz(this).closest("div").find(".wcvaimageorcolordiv").hide();

			} else{

				$gaz(this).closest("div").find(".wcvametaupperdiv").hide();
				$gaz(this).closest("div").find(".wcvaimageorcolordiv").hide();
			}

		});
	});


	$gaz(function() {

		$gaz('.wcva_display_size_select').on('change',function(){
			var nvalue = $gaz(this).val();

			var kyvalue = $gaz(this).attr("kyvalue");

			if (nvalue == "custom") {

				$gaz(".custom_display_size_row_"+kyvalue+"").show();

			} else {

				$gaz(".custom_display_size_row_"+kyvalue+"").hide();
			}
		});
	});







	    /**
	     * hide/show shop swatches select on checkbox change
	     */
	     $gaz(function() {

	     	$gaz("#wcva_shop_swatches").click(function() {
	     		if($gaz(this).is(":checked")) {
	     			$gaz("#wcva_shop_swatches_tr").show(300);
	     			$gaz(".wcvahoverimagediv").show(200);

	     		} else {
	     			$gaz("#wcva_shop_swatches_tr").hide(200);
	     			$gaz(".wcvahoverimagediv").hide(100);
	     		}
	     	});

	     });


	     $gaz('.wcvacolororimage').on('change',function(){

	     	var parentId = $gaz(this).attr('idval');



	     	if (this.value == "Image") {

	     		$gaz(this).closest("div").find(".wcvacolordiv").hide();
	     		$gaz(this).closest("div").find(".wcvatextblockdiv").hide();
	     		$gaz(".wcva_preview_color_"+parentId+"").hide();
	     		$gaz(".wcva_preview_textblock_"+parentId+"").hide();
	     		$gaz(this).closest("div").find(".wcvaimagediv").show();
	     		$gaz(".wcva_preview_image_"+parentId+"").show();


	     	} else if (this.value == "Color") {

	     		$gaz(this).closest("div").find(".wcvaimagediv").hide();
	     		$gaz(this).closest("div").find(".wcvatextblockdiv").hide();
	     		$gaz(".wcva_preview_image_"+parentId+"").hide();
	     		$gaz(".wcva_preview_textblock_"+parentId+"").hide();
	     		$gaz(this).closest("div").find(".wcvacolordiv").show();
	     		$gaz(".wcva_preview_color_"+parentId+"").show();

	     	} else if (this.value == "textblock") {

	     		$gaz(this).closest("div").find(".wcvaimagediv").hide();
	     		$gaz(this).closest("div").find(".wcvacolordiv").hide();
	     		$gaz(".wcva_preview_image_"+parentId+"").hide();
	     		$gaz(".wcva_preview_color_"+parentId+"").hide();
	     		$gaz(this).closest("div").find(".wcvatextblockdiv").show();
	     		$gaz(".wcva_preview_textblock_"+parentId+"").show();
	     	}

	     });


	     $gaz(".wcvacolordiv").each(function(){
	     	$gaz('.wcvaattributecolorselect').iris({
	     		hide: true,
	     		palettes: true,
	     		change: function(event, ui) {


	     			var parentId = $gaz(this).attr('idval');


	     			$gaz(".colorpreviewdiv_"+parentId+"").css("background-color", ui.color.toString());
	     		}
	     	});


	     	$gaz('.iris-picker').click(function() {
	     		$gaz(this).hide();
	     	});

	     	$gaz('.wcvaattributecolorselect').click(function() {
	     		$gaz(this).next(".iris-picker").show();
	     	});



	     });



	     $gaz(".wcvaattributetextblock").on("change paste keyup", function() {

	     	var parentId = $gaz(this).attr('idval');

	     	$gaz(".wcva_preview_textblock_"+parentId+"").text($gaz(this).val());

	     });




        //loads Media upload for each media upload input
        $gaz(".image-upload-div").each(function(){
        	var parentId = $gaz(this).closest('div').attr('idval');
		 		 // Only show the "remove image" button when needed
		 		 var srcvalue    = $gaz('.facility_thumbnail_id_' + parentId + '').val();

		 		 if ( !srcvalue ){
		 		 	jQuery('.remove_image_button_' + parentId + ' ').hide();
		 		 }  
				// Uploading files
				var file_frame;

				jQuery(document).on( 'click', '.upload_image_button_' + parentId + ' ', function( event ){


					event.preventDefault();

					// If the media frame already exists, reopen it.
					if ( file_frame ) {
						file_frame.open();
						return;
					}

					// Create the media frame.
					file_frame = wp.media.frames.downloadable_file = wp.media({
						title: wcvameta.uploadimage,
						button: {
							text: wcvameta.useimage,
						},
						multiple: false
					});

					// When an image is selected, run a callback.
					file_frame.on( 'select', function() {
						var attachment = file_frame.state().get('selection').first().toJSON();

						jQuery('.facility_thumbnail_id_' + parentId + '').val( attachment.id );
						jQuery('#facility_thumbnail_' + parentId + ' img').attr('src', attachment.url );
						jQuery('.imagediv_' + parentId + ' img').attr('src', attachment.url );
						jQuery('.remove_image_button_' + parentId + '').show();
						jQuery('.wcva_imgsrc_sub_header_' + parentId + '').attr('src', attachment.url );
					});

					// Finally, open the modal.
					file_frame.open();
				});

				jQuery(document).on( 'click', '.remove_image_button_' + parentId + '', function( event ){

					jQuery('#facility_thumbnail_' + parentId + ' img').attr('src', wcvameta.placeholder );
					jQuery('.imagediv_' + parentId + ' img').attr('src', '');
					jQuery('.facility_thumbnail_id_' + parentId + '').val('');
					jQuery('.remove_image_button_' + parentId + '').hide();
					jQuery('.wcva_imgsrc_sub_header_' + parentId + '').attr('src', wcvameta.placeholder );
					return false;
				});

			});				


     //loads Media upload for each media upload input
     $gaz(".hover-image-upload-div").each(function(){
     	var parentId2 = $gaz(this).closest('div').attr('idval');
		 		 // Only show the "remove image" button when needed

		 		 var srcvalue2    = $gaz('.hover_facility_thumbnail_id_' + parentId2 + '').val();


		 		 if ( !srcvalue2 ){
		 		 	jQuery('.hover_remove_image_button_' + parentId2 + '').hide();
		 		 }  
				// Uploading files
				var file_frame;

				jQuery(document).on( 'click', '.hover_upload_image_button_' + parentId2 + ' ', function( event ){


					event.preventDefault();

					// If the media frame already exists, reopen it.
					if ( file_frame ) {
						file_frame.open();
						return;
					}

					// Create the media frame.
					file_frame = wp.media.frames.downloadable_file = wp.media({
						title: wcvameta.uploadimage,
						button: {
							text: wcvameta.useimage,
						},
						multiple: false
					});

					// When an image is selected, run a callback.
					file_frame.on( 'select', function() {
						var attachment = file_frame.state().get('selection').first().toJSON();

						jQuery('.hover_facility_thumbnail_id_' + parentId2 + '').val( attachment.id );
						jQuery('#hover_facility_thumbnail_' + parentId2 + ' img').attr('src', attachment.url );
						jQuery('.hover_remove_image_button_' + parentId2 + '').show();
					});

					// Finally, open the modal.
					file_frame.open();
				});

				jQuery(document).on( 'click', '.hover_remove_image_button_' + parentId2 + '', function( event ){

					jQuery('#hover_facility_thumbnail_' + parentId2 + ' img').attr('src', wcvameta.placeholder );
					jQuery('.hover_facility_thumbnail_id_' + parentId2 + '').val('');
					jQuery('.hover_remove_image_button_' + parentId2 + '').hide();
					return false;
				});

			});				



 })( jQuery );