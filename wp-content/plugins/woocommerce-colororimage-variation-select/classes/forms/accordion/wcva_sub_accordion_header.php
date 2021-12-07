<div class="accordion-header" data-action="accordion" style="position: relative;">	
    <div class="attribute-label">
		<?php echo rawurldecode($valuetitle); ?>
    </div>
				
    <div class="previewdiv">
		<?php 
	    if (isset($attrdisplaytype))  {

	    	    ?>

	    	    <div class="wcva_preview_color_<?php echo $attrnumber; ?>_<?php echo $attrsubnumber; ?>" style="<?php if (($attrdisplaytype == "Image") || ($attrdisplaytype == "textblock")) { echo 'display:none;'; } else {  echo 'display:block;';  } ?>"> 
            
	                <a class="colorpreviewdiv_<?php echo $attrnumber; ?>_<?php echo $attrsubnumber; ?> wcva-heading-color" style="<?php if ($displaytype == "round") { echo '-moz-border-radius: 99em; -webkit-border-radius: 99em;'; } ?> display: inline-block; background-color: <?php if (isset($attrcolor)) { echo  $attrcolor; } else { echo 'grey'; } ?>;height: 22px;width: 22px; border: solid 2px white; <?php if ($displaytype != "round") { echo 'outline: solid 1px #9C9999;'; } ?>"></a>  
	            </div>                                                            
	                                   
					   
	            <div class="wcva_preview_image_<?php echo $attrnumber; ?>_<?php echo $attrsubnumber; ?>" style="<?php if (($attrdisplaytype == "Image") ) { echo 'display:block;'; } else {  echo 'display:none;';  } ?>"> 
	                <a class="imagediv_<?php echo $attrnumber; ?>_<?php echo $attrsubnumber; ?> wcva-heading-image" style="<?php if ($displaytype == "round") { echo '-moz-border-radius: 99em; -webkit-border-radius: 99em; '; } ?>"><img src="<?php if (isset($swatchurl) && ($swatchurl != '')) { echo $swatchurl; } else { echo wedd_placeholder_img_src(); }  ?>" class="wcva_imgsrc_sub_header_<?php echo $attrnumber; ?>_<?php echo $attrsubnumber; ?>" width="22px" height="22px" style="<?php if ($displaytype == "round") { echo '-moz-border-radius: 99em; -webkit-border-radius: 99em;'; } ?> border: solid 2px white; <?php if ($displaytype != "round") { echo 'outline: solid 1px #9C9999;'; } ?>"></a>
	            </div>
	               
					   
			    
				<div class="wcva_preview_textblock wcva_preview_textblock_<?php echo $attrnumber; ?>_<?php echo $attrsubnumber; ?>" style="<?php if (($attrdisplaytype == "textblock") ) { echo 'display:inline-block;'; } else {  echo 'display:none;';  } ?>  <?php if ($displaytype != "round") { echo 'outline: solid 1px #9C9999;'; } ?>" ><?php echo $attrtextblock; ?>
				    	
				</div>
				<?php
		}
	    ?>
	</div>
</div>