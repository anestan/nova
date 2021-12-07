<div class="wcvametaupperdiv" style="<?php if (isset($_coloredvariables[$key]['display_type']) && ($_coloredvariables[$key]['display_type'] == 'none')) { echo 'display:none;'; } else { echo 'display:;'; } ?>">
		 
    <p class="form-field">
		<label for="_display_size">
		        <span class="wcvaformfield"><?php echo esc_html__('Display Size','wcva'); ?></span>
		</label>
			
		<select class="wcva_display_size_select" kyvalue="<?php echo $key; ?>" name="coloredvariables[<?php echo $key; ?>][size]">
	        <option value="small"  <?php if (isset($_coloredvariables[$key]['size']) && ($_coloredvariables[$key]['size'] == 'small')) { echo 'selected'; }?>><span class="wcvaformfield"><?php echo esc_html__('Small (32px * 32px)','wcva'); ?></span></option>
		    <option value="extrasmall" <?php if (isset($_coloredvariables[$key]['size']) && ($_coloredvariables[$key]['size'] == 'extrasmall')) { echo 'selected'; }?>><span class="wcvaformfield"><?php echo esc_html__('Extra Small (22px * 22px)','wcva'); ?></span></option>
		    <option value="medium" <?php if (isset($_coloredvariables[$key]['size']) && ($_coloredvariables[$key]['size'] == 'medium')) { echo 'selected'; }?>><span class="wcvaformfield"><?php echo esc_html__('Middle (40px * 40px)','wcva'); ?></span></option>
		    <option value="big" <?php if (isset($_coloredvariables[$key]['size']) && ($_coloredvariables[$key]['size'] == 'big')) { echo 'selected'; }?>><span class="wcvaformfield"><?php echo esc_html__('Big (60px * 60px)','wcva'); ?></span></option>
		    <option value="extrabig" <?php if (isset($_coloredvariables[$key]['size']) && ($_coloredvariables[$key]['size'] == 'extrabig')) { echo 'selected'; }?>><span class="wcvaformfield"><?php echo esc_html__('Extra Big (90px * 90px)','wcva'); ?></span></option>
			<option value="custom" <?php if (isset($_coloredvariables[$key]['size']) && ($_coloredvariables[$key]['size'] == 'custom')) { echo 'selected'; }?>><span class="wcvaformfield"><?php echo esc_html__('Custom','wcva'); ?></span></option>
		        <?php echo apply_filters('wcva_custom_attribute_display_size', $key ); ?>
	    </select>
			
		<select name="coloredvariables[<?php echo $key; ?>][displaytype]">
	        <option value="square" <?php if (isset($_coloredvariables[$key]['displaytype']) && ($_coloredvariables[$key]['displaytype'] == 'square')) { echo 'selected'; }?>><span class="wcvaformfield"><?php echo esc_html__('Square','wcva'); ?></span></option>
		    <option value="round" <?php if (isset($_coloredvariables[$key]['displaytype']) && ($_coloredvariables[$key]['displaytype'] == 'round')) { echo 'selected'; }?>><span class="wcvaformfield"><?php echo esc_html__('Round','wcva'); ?></span></option>
		    <?php echo apply_filters('wcva_custom_attribute_display_displaytype', $key ); ?>
	    </select>

		<img class="help_tip wcva_help_tip" data-tip='<?php echo esc_html__('These fields does not apply to shop/archive swatches.To change swatch size on archive/shop pages visit WooCommerce/settings/WooSwatches tab.','wcva'); ?>' src="<?php echo $helpimg; ?>" height="16" width="16">
	</p>

	<p class="form-field custom_display_size_row custom_display_size_row_<?php echo $key; ?>" style="<?php if (isset($_coloredvariables[$key]['size']) && ($_coloredvariables[$key]['size'] == 'custom')) { echo 'display:;'; } else { echo 'display:none;'; } ?>">
		<label for="custom_display_size">
		        <span class="wcvaformfield"><?php echo esc_html__('Custom Size','wcva'); ?></span>
		</label>

		<?php echo esc_html__('Height','wcva'); ?>:
		<input type="number" name="coloredvariables[<?php echo $key; ?>][customheight]" size="12" value="<?php if (isset($_coloredvariables[$key]['customheight']) && ($_coloredvariables[$key]['customheight'] != '')) { echo $_coloredvariables[$key]['customheight']; } else { echo '100'; } ?>" />px

		<?php echo esc_html__('Width','wcva'); ?>:
		<input type="number" name="coloredvariables[<?php echo $key; ?>][customwidth]" size="12" value="<?php if (isset($_coloredvariables[$key]['customwidth']) && ($_coloredvariables[$key]['customwidth'] != '')) { echo $_coloredvariables[$key]['customwidth']; } else { echo '100'; } ?>" />px

	</p>
		 
	<p class="form-field">
		
		    <label for="_show_name">
		        <span class="wcvaformfield"><?php echo esc_html__('Show Attribute Name','wcva'); ?></span>
            </label>
	        
	        <select name="coloredvariables[<?php echo $key; ?>][show_name]" class="wcvadisplaytype">
	            <option value="no" <?php if (isset($_coloredvariables[$key]['show_name']) && ($_coloredvariables[$key]['show_name'] == 'no')) { echo 'selected'; }?>><span class="wcvaformfield"><?php echo esc_html__('No','wcva'); ?></span></option>
		        <option value="yes" <?php if (isset($_coloredvariables[$key]['show_name']) && ($_coloredvariables[$key]['show_name'] == 'yes')) { echo 'selected'; }?>><span class="wcvaformfield"><?php echo esc_html__('Yes','wcva'); ?></span></option>
		    </select>
            <img class="help_tip wcva_help_tip" data-tip='<?php echo esc_html__('This field does not apply to shop/archive swatches.','wcva'); ?>' src="<?php echo $helpimg; ?>" height="16" width="16">
	</p>
</div>