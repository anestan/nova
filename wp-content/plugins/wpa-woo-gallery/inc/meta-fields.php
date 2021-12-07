<?php defined('ABSPATH') || die('Direct access is not allow'); ?>

<select id="wpa_gallery_layout" name="wpawg[wpa_gallery_layout]">
    <option value="wpa_gallery_default_settings"><?php esc_html_e( 'Inherit Global Settings', 'wpa-gallery' )?></option>
    <option value="wpa_gallery_default" <?php if(!empty($wpa_gallery_layout) && $wpa_gallery_layout == "wpa_gallery_default"){ echo "selected";}?>><?php esc_html_e( 'Horizontal (Default)', 'wpa-gallery' )?></option>
    <option value="wpa_gallery_position_left" <?php if(!empty($wpa_gallery_layout) && $wpa_gallery_layout == "wpa_gallery_position_left"){ echo "selected";}?>><?php esc_html_e( 'Vertical (Gallery thumbnail left)', 'wpa-gallery' )?></option>
    <option value="wpa_gallery_position_right" <?php if(!empty($wpa_gallery_layout) && $wpa_gallery_layout == "wpa_gallery_position_right"){ echo "selected";}?>><?php esc_html_e( 'Vertical (Gallery thumbnail right)', 'wpa-gallery' )?></option>
</select>