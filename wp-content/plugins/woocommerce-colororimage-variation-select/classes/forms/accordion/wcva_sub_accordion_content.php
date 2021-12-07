<div class="accordion-content">
    <table class="widefat" width="100" style="border:none;">
        <tr>
            <td width="30%"><span class="wcvaformfield"><?php echo esc_html__('Display Type','wcva'); ?></span></td>
            <td width="70%">
             	<select idval="<?php echo $attrnumber; ?>_<?php echo $attrsubnumber; ?>" name="coloredvariables[<?php echo $key; ?>][values][<?php echo $value; ?>][type]" id="coloredvariables-<?php echo $attrnumber; ?>-values-<?php echo $attrsubnumber; ?>-type" class="wcvacolororimage">
	                <option value="Color" <?php if ((isset($attrdisplaytype))  && $attrdisplaytype == "Color" ) { echo 'selected'; } ?>><?php echo esc_html__('Color','wcva'); ?></option>
		            <option value="Image" <?php if ((isset($attrdisplaytype)) && $attrdisplaytype == "Image" ) { echo 'selected'; } ?>><?php echo esc_html__('Image','wcva'); ?></option>
				    <option value="textblock" <?php if ((isset($attrdisplaytype)) && $attrdisplaytype == "textblock" ) { echo 'selected'; } ?>><?php echo esc_html__('Text block','wcva'); ?></option>
		        </select>
            </td>
        </tr>

        <tr class="wcvacolordiv" id="coloredvariables-<?php echo $attrnumber; ?>-values-<?php echo $attrsubnumber; ?>-color"  style="<?php if ((isset($attrdisplaytype)) && (($attrdisplaytype == "Image") || ($attrdisplaytype == "textblock")) ) { echo 'display:none;'; } else { echo 'display:;'; } ?>">
            <td width="30%"> <span class="wcvaformfield"><?php echo esc_html__('Swatch Color','wcva'); ?></span></td>
            <td width="70%">
                  <input idval="<?php echo $attrnumber; ?>_<?php echo $attrsubnumber; ?>" name="coloredvariables[<?php echo $key; ?>][values][<?php echo $value; ?>][color]" type="text" class="wcvaattributecolorselect" value="<?php if (isset($attrcolor)) { echo  $attrcolor; } else { echo '#ffffff'; }  ?>" data-default-color="#ffffff"> 
            </td>
        </tr>
         
		
		<tr class="wcvaimagediv" id="coloredvariables-image" style="<?php if ((isset($attrdisplaytype))  && ($attrdisplaytype == "Image")) { echo 'display:;'; } else { echo 'display:none;'; } ?>">
		    <td width="30%"> <span class="wcvaformfield"><?php echo esc_html__('Swatch Image','wcva'); ?></span></td>
	        <td width="70%">  
	            <div class="facility_thumbnail" id="facility_thumbnail_<?php echo $attrnumber; ?>_<?php echo $attrsubnumber; ?>" style="float:left;">
	                <img src="<?php if (isset($swatchurl) && ($swatchurl != '')) { echo $swatchurl; } else { echo wedd_placeholder_img_src(); }  ?>" width="60px" height="60px" />
	                    <div  class="image-upload-div" idval="<?php echo $attrnumber; ?>_<?php echo $attrsubnumber; ?>" >
					        <input type="hidden" class="facility_thumbnail_id_<?php echo $attrnumber; ?>_<?php echo $attrsubnumber; ?>" name="coloredvariables[<?php echo $key; ?>][values][<?php echo $value; ?>][image]" value="<?php if (isset($swatchimage)) { echo $swatchimage; } ?>"/>
					        <button type="submit" class="upload_image_button_<?php echo $attrnumber; ?>_<?php echo $attrsubnumber; ?> button"><?php echo esc_html__( 'Upload/Add image', 'wcva' ); ?></button>
					        <button type="submit" class="remove_image_button_<?php echo $attrnumber; ?>_<?php echo $attrsubnumber; ?> button"><?php echo esc_html__( 'Remove image', 'wcva' ); ?></button>
				        </div>
				</div>
	        </td>
	    </tr>
			
	    <tr class="wcvatextblockdiv" id="coloredvariables-textblock" style="<?php if ((isset($attrdisplaytype))  && ($attrdisplaytype == "textblock")) { echo 'display:;'; } else { echo 'display:none;'; } ?>">
		    <td width="30%"> <span class="wcvaformfield"><?php echo esc_html__('Text','wcva'); ?></span></td>
	        <td width="70%">  
	              
				<input idval="<?php echo $attrnumber; ?>_<?php echo $attrsubnumber; ?>" name="coloredvariables[<?php echo $key; ?>][values][<?php echo $value; ?>][textblock]" type="text" class="wcvaattributetextblock" value="<?php if (isset($attrtextblock) && (!empty($attrtextblock))) { echo  $attrtextblock; } else { echo $value; }  ?>" > 
	              
	        </td>
	    </tr>
		  
		<tr class="wcvahoverimagediv" id="wcvahoverimagediv" style="<?php if (isset($shop_swatches) && ($shop_swatches == "yes")) { echo 'display:;'; } else { echo 'display:none;'; } ?>">
		    <td width="30%"><span class="wcvaformfield"><?php echo esc_html__('Hover Image','wcva'); ?> <img class="help_tip" data-tip='<?php echo esc_html__('This image will replace the product image on swatch hover on shop/archive page.','wcva'); ?>' src="<?php echo $helpimg; ?>" height="16" width="16"></span></td>
            <td width="70%">
                <div class="hover_facility_thumbnail" id="hover_facility_thumbnail_<?php echo $attrnumber; ?>_<?php echo $attrsubnumber; ?>" style="float:left;">
                    <img src="<?php if (isset($hoverurl) && ($hoverurl != '')) { echo $hoverurl; } else { echo wedd_placeholder_img_src(); } ?>" width="60px" height="60px" />
				        <div  class="hover-image-upload-div" idval="<?php echo $attrnumber; ?>_<?php echo $attrsubnumber; ?>" >
					        <input type="hidden" class="hover_facility_thumbnail_id_<?php echo $attrnumber; ?>_<?php echo $attrsubnumber; ?>" name="coloredvariables[<?php echo $key; ?>][values][<?php echo $value; ?>][hoverimage]" value="<?php if (isset($hoverthumb_id)) { echo $hoverthumb_id; } ?>"/>
					        <button type="submit" class="hover_upload_image_button_<?php echo $attrnumber; ?>_<?php echo $attrsubnumber; ?> button"><?php echo esc_html__( 'Upload/Add image', 'wcva' ); ?></button>
					        <button type="submit" class="hover_remove_image_button_<?php echo $attrnumber; ?>_<?php echo $attrsubnumber; ?> button"><?php echo esc_html__( 'Remove image', 'wcva' ); ?></button>
			            </div>
			    </div>
		     </td>
		</tr>
    </table>
</div>