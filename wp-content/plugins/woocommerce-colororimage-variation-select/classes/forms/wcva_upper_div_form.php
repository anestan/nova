<div class="wcva-upper-div">
    <table class="widefat" width="100%" style="border:none;">
		<tr>
		    <td width="70%"><?php echo esc_html__('Enable one attribute swatches On shop/archive pages','wcva'); ?></td>
	        <td width="30%"><input type="checkbox" id="wcva_shop_swatches" name="shop_swatches" value="yes" <?php if (isset($shop_swatches) && ($shop_swatches == "yes")) { echo 'checked'; } ?>></td>
	    </tr>

	    <tr id="wcva_shop_swatches_tr" style="<?php if (isset($shop_swatches) && ($shop_swatches == "yes")) { echo 'display:table-row;'; } else { echo 'display:none;'; } ?>">
	        <td width="70%">
	            <?php echo esc_html__('Select atrribute for shop swatches','wcva'); ?>
	        </td>
	        <td width="30%">
	            <?php if ((!empty($attributes)) && (sizeof($attributes) >0)) { ?>
	                <select name="shop_swatches_attribute" class="select">
	                    <?php foreach ($attributes as $key=>$values) { ?>
                            <option value="<?php echo $key; ?>" <?php if (isset($shop_swatches_attribute) && ($shop_swatches_attribute == $key)) { echo 'selected'; } ?>><?php echo $key; ?></option>     
	                    <?php } ?>
	            <?php } ?>
	                </select>

	       	        <img class="help_tip wcva_help_tip" data-tip='<?php echo esc_html__('Swatches of this attribute will be displayed on shop page.','wcva'); ?>' src="<?php echo $helpimg; ?>" height="16" width="16">
	        </td>
	    </tr>
    </table>
</div>