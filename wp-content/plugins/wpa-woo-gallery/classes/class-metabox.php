<?php
if ( ! defined( 'ABSPATH' ) ) :
    exit; // Exit if accessed directly
endif;

/**
 * metabox class by @Aazztech
 */
if (! class_exists('WPAWG_Meta_Box')) {

    class WPAWG_Meta_Box {

        public function __construct () {
            if (is_admin()) {
                add_action('add_meta_boxes_product', array($this, 'wpawg_meta_box'));
                add_action('edit_post', array($this, 'wpawg_update_post'));
            }
        }

        public function wpawg_meta_box () {
            add_meta_box('wpawg_meta_box',
                esc_html__('Gallery Layout', 'wpa-gallery'),
                array($this, 'wpawg_meta_box_input'),
                'product',
                'side',
                'low'
            );
        }

        public function wpawg_meta_box_input ($post) {
            // Add a nonce field so we can check for it later.
            wp_nonce_field('wpawg_action', 'wpawg_nonce');

            $lcg_svalue = get_post_meta($post->ID, 'wpawg', true);
            $s_value = WPA_Woo_Gallery::unserialize_and_decode24($lcg_svalue);
            $value = is_array($s_value) ? $s_value : array();
            extract($value);

            include_once WPA_WG_PATH . 'inc/meta-fields.php';
        }

        public function wpawg_update_post ($post_id) {
            // vail if the security check fails
            if (!$this->wpawg_security_check('wpawg_nonce', 'wpawg_action', $post_id))
                return;

            // save the meta data if it is our post type lcg_mainpost post type
            if (!empty($_POST['post_type']) && ('product' == $_POST['post_type'])) {

                $wpawg = !empty($_POST['wpawg']) ? WPA_Woo_Gallery::serialize_and_encode24($_POST['wpawg']) : WPA_Woo_Gallery::serialize_and_encode24(array());

                //save the meta value
                update_post_meta($post_id, "wpawg", $wpawg);

            }
        }

        //security check
        private function wpawg_security_check($nonce_name, $action, $post_id){
            // checks are divided into 3 parts for readability.
            if ( !empty( $_POST[$nonce_name] ) && wp_verify_nonce( $_POST[$nonce_name], $action ) ) {
                return true;
            }
            // If this is an autosave, our form has not been submitted, so we don't want to do anything. returns false
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return false;
            }
            // Check the user's permissions.
            if ( current_user_can( 'edit_post', $post_id ) ) {
                return true;
            }
            return false;
        }
    }

    new WPAWG_Meta_Box();
}