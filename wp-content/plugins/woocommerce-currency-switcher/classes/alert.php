<?php

class WOOCS_ADV {

    public $notices_list = array();

    public function __construct($alert_list = array()) {

        //fix to avoid disabling of 'Upload Theme' button action on /wp-admin/theme-install.php?browse=featured
        if (isset($_SERVER['REQUEST_URI'])) {
            if (substr_count($_SERVER['REQUEST_URI'], 'theme-install.php')) {
                return;
            }
        } else {
            if (isset($_SERVER['PHP_SELF'])) {
                if (substr_count($_SERVER['PHP_SELF'], 'theme-install.php')) {
                    return;
                }
            }
        }

        //***

        $this->notices_list = array(
            // 'woocommerce_bulk_editor' => 'woo-bulk-editor',
            'weglot' => 'weglot',
            'woot_products_tables' => 'profit-products-tables-for-woocommerce',
        );
        $this->notices_list = array_merge($this->notices_list, $alert_list);
    }

    public function init() {
        if (is_admin()) {
            if (get_option('woocs_version') != WOOCS_VERSION) { // if update plugin
                update_option('woocs_version', WOOCS_VERSION);

                $alert = (array) get_option('woocs_alert', array());
                foreach ($this->notices_list as $key => $item) {
                    $alert[$key] = "";
                }

                add_option('woocs_alert', $alert, '', 'no');
                update_option('woocs_alert', $alert);
            }

            foreach ($this->notices_list as $key => $item) {
                if (file_exists(WP_PLUGIN_DIR . '/' . $item)) {
                    unset($this->notices_list[$key]);
                }
            }

            global $wp_version;
            if (version_compare($wp_version, '4.2', '>=') && current_user_can('install_plugins') && !empty($this->notices_list)) {
                $alert = (array) get_option('woocs_alert', array());
                foreach ($this->notices_list as $key => $item) {
                    if (empty($alert[$key]) AND method_exists($this, 'alert_' . $key)) {
                        add_action('admin_notices', array($this, 'alert_' . $key));
                        add_action('network_admin_notices', array($this, 'alert_' . $key));
                    }
                }
                add_action('wp_ajax_woocs_dismiss_alert_', array($this, 'woocs_dismiss_alert'));
                add_action('admin_enqueue_scripts', array($this, 'woocs_alert_scripts'));

                //enqueue admin/js/updates.js
            }
        }
    }

    public function woocs_dismiss_alert() {

        $alert = (array) get_option('woocs_alert', array());
        $alert[$_POST['alert']] = 1;


        add_option('woocs_alert', $alert, '', 'no');
        update_option('woocs_alert', $alert);

        exit;
    }

    public function woocs_alert_scripts() {
        wp_enqueue_script('plugin-install');
        add_thickbox();
        wp_enqueue_script('updates');
    }

    public function alert_woocommerce_bulk_editor() {
        if (isset($_GET['tab']) AND $_GET['tab'] === 'woocs') {
            $screen = get_current_screen();
            ?>
            <div class="notice notice-info is-dismissible" id="woocs_alert_woobe">
                <p class="plugin-card-woo-bulk-editor"<?php if ($screen->id != 'plugin-install') echo ' id="plugin-woobe"' ?>>
                    Try new plugin for managing and bulk edit WooCommerce Products data in robust and flexible way: <a href="<?php echo network_admin_url('plugin-install.php?tab=plugin-information') ?>&amp;plugin=woo-bulk-editor&amp;TB_iframe=true&amp;width=600&amp;height=550" class="thickbox open-plugin-details-modal" aria-label="WOOCS team recommends" data-title="WOOBE">WOOBE - WooCommerce Bulk Editor Professional</a>.
                    <a href="<?php echo network_admin_url('plugin-install.php?tab=plugin-information') ?>&amp;plugin=woo-bulk-editor&amp;TB_iframe=true&amp;width=600&amp;height=550" class="thickbox open-plugin-details-modal button" aria-label="More information about WOOBE" data-title="WOOBE" id="woocs_alert_install_button_woobe">Install</a>
                    <a class="install-now button" data-slug="woo-bulk-editor" href="<?php echo network_admin_url('update.php?action=install-plugin') ?>&amp;plugin=woo-bulk-editor&amp;_wpnonce=<?php echo wp_create_nonce('install-plugin-woo-bulk-editor') ?>" aria-label="Install woocommerce bulk editor now" data-name="Woocommerce bulkeditor" style="display:none">Install Now</a>
                </p>
            </div>

            <?php
            wp_add_inline_script('updates', $this->init_js(), 'after');
            wp_print_request_filesystem_credentials_modal();
        }
    }

    public function init_js() {
        $screen = get_current_screen();
        ob_start();
        ?>
        jQuery( document ).ready(function() {
        jQuery('#woocs_alert_woobe .open-plugin-details-modal').on('click', function () {
        jQuery('#woocs_alert_install_button_woobe').hide().next().show();
        return true;
        });
        jQuery(function ($) {
        var alert_w = $('#woocs_alert_woobe');
        alert_w.on('click', '.notice-dismiss', function (e) {
        //e.preventDefault
        $.post(ajaxurl, {
        action: 'woocs_dismiss_alert_',
        alert: 'woocommerce_bulk_editor',
        sec: <?php echo json_encode(wp_create_nonce('woocs_dissmiss_alert_')) ?>
        }).done(function (w) {

        });
        });

        <?php if ($screen->id == 'plugin-install'): ?>
            $('#plugin-woobe').prepend(alert_w.css('margin-bottom', '10px').addClass('inline'));
        <?php endif ?>

        $(document).on('tb_unload', function () {
        if (jQuery('#woocs_alert_install_button_woobe').next().hasClass('updating-message'))
        return;

        jQuery('#woocs_alert_install_button_woobe').show().next().hide();
        });
        $(document).on('credential-modal-cancel', function () {
        jQuery('#woocs_alert_install_button_woobe').show().next().hide();
        });
        });
        });
        <?php
        return ob_get_clean();
    }

    public function alert_woot_products_tables() {
        if (isset($_GET['tab']) AND $_GET['tab'] === 'woocs') {
            $screen = get_current_screen();
            ?>
            <div class="notice notice-info is-dismissible" id="woocs_alert_woot">
                <p class="plugin-card-profit-products-tables-for-woocommerce"<?php if ($screen->id != 'plugin-install') echo ' id="plugin-woot"' ?>>
                    Try new WOOCS compatible plugin for displaying WooCommerce shop products in table format: <a href="<?php echo network_admin_url('plugin-install.php?tab=plugin-information') ?>&amp;plugin=profit-products-tables-for-woocommerce&amp;TB_iframe=true&amp;width=600&amp;height=550" class="thickbox open-plugin-details-modal" aria-label="WOOCS team recommends" data-title="WOOT">WOOT - WooCommerce Active Products Tables</a>.
                    <a href="<?php echo network_admin_url('plugin-install.php?tab=plugin-information') ?>&amp;plugin=profit-products-tables-for-woocommerce&amp;TB_iframe=true&amp;width=600&amp;height=550" class="thickbox open-plugin-details-modal button" aria-label="More information about WOOT" data-title="WOOT" id="woocs_alert_install_button_woot">Install</a>
                    <a class="install-now button" data-slug="profit-products-tables-for-woocommerce" href="<?php echo network_admin_url('update.php?action=install-plugin') ?>&amp;plugin=profit-products-tables-for-woocommerce&amp;_wpnonce=<?php echo wp_create_nonce('install-plugin-profit-products-tables-for-woocommerce') ?>" aria-label="Install woot now" data-name="Woocommerce woot" style="display:none">Install Now</a>
                </p>
            </div>

            <?php
            wp_add_inline_script('updates', $this->woot_init_js(), 'after');
            wp_print_request_filesystem_credentials_modal();
        }
    }

    public function woot_init_js() {
        $screen = get_current_screen();
        ob_start();
        ?>
        jQuery( document ).ready(function() {
        jQuery('#woocs_alert_woot .open-plugin-details-modal').on('click', function () {
        jQuery('#woocs_alert_install_button_woot').hide().next().show();
        return true;
        });
        jQuery(function ($) {
        var alert_w = $('#woocs_alert_woot');
        alert_w.on('click', '.notice-dismiss', function (e) {
        //e.preventDefault
        $.post(ajaxurl, {
        action: 'woocs_dismiss_alert_',
        alert: 'woot_products_tables',
        sec: <?php echo json_encode(wp_create_nonce('woocs_dissmiss_alert_')) ?>
        }).done(function (w) {

        });
        });

        <?php if ($screen->id == 'plugin-install'): ?>
            $('#plugin-woot').prepend(alert_w.css('margin-bottom', '10px').addClass('inline'));
        <?php endif ?>

        $(document).on('tb_unload', function () {
        if (jQuery('#woocs_alert_install_button_woot').next().hasClass('updating-message'))
        return;

        jQuery('#woocs_alert_install_button_woot').show().next().hide();
        });
        $(document).on('credential-modal-cancel', function () {
        jQuery('#woocs_alert_install_button_woot').show().next().hide();
        });
        });
        });
        <?php
        return ob_get_clean();
    }

    public function alert_weglot() {
        global $WOOCS;
        if ($WOOCS->notes_for_free) {
            if (isset($_GET['tab']) AND $_GET['tab'] === 'woocs') {
                $screen = get_current_screen();
                ?>
                <div class="notice notice-info is-dismissible" id="woocs_alert_weglot">
                    <p class="plugin-card-weglot"<?php if ($screen->id != 'plugin-install') echo ' id="weglot"' ?>>
                        Try <a href="https://weglot.pluginus.net/" target="_blank" title="Weglot">Weglot</a>, WOOCS compatible plugin, which allows to translate your site on the fly. Install, try and get unique 20% <strong>discount</strong> by secret coupon: <span class="woocs-red">WOOCS</span>.
                        <a href="<?php echo network_admin_url('plugin-install.php?tab=plugin-information') ?>&amp;plugin=weglot&amp;TB_iframe=true&amp;width=600&amp;height=550" class="thickbox open-plugin-details-modal button" aria-label="More information about Weglot" data-title="Weglot" id="woocs_alert_install_button_weglot">Install</a>
                        <a class="install-now button" data-slug="weglot" href="<?php echo network_admin_url('update.php?action=install-plugin') ?>&amp;plugin=weglot&amp;_wpnonce=<?php echo wp_create_nonce('install-plugin-weglot') ?>" aria-label="Install Weglot now" data-name="Weglot" style="display:none">Install Now</a>
                    </p>
                </div>

                <?php
                wp_add_inline_script('updates', $this->weglot_init_js(), 'after');
                wp_print_request_filesystem_credentials_modal();
            }
        }
    }

    public function weglot_init_js() {
        $screen = get_current_screen();
        ob_start();
        ?>
        jQuery( document ).ready(function() {
        jQuery('#woocs_alert_weglot .open-plugin-details-modal').on('click', function () {
        jQuery('#woocs_alert_install_button_weglot').hide().next().show();
        return true;
        });
        jQuery(function ($) {
        var alert_w = $('#woocs_alert_weglot');
        alert_w.on('click', '.notice-dismiss', function (e) {
        //e.preventDefault
        $.post(ajaxurl, {
        action: 'woocs_dismiss_alert_',
        alert: 'weglot',
        sec: <?php echo json_encode(wp_create_nonce('woocs_dissmiss_alert_')) ?>
        }).done(function (w) {

        });
        });

        <?php if ($screen->id == 'plugin-install'): ?>
            $('#plugin-weglot').prepend(alert_w.css('margin-bottom', '10px').addClass('inline'));
        <?php endif ?>

        $(document).on('tb_unload', function () {
        if (jQuery('#woocs_alert_install_button_weglot').next().hasClass('updating-message'))
        return;

        jQuery('#woocs_alert_install_button_weglot').show().next().hide();
        });
        $(document).on('credential-modal-cancel', function () {
        jQuery('#woocs_alert_install_button_weglot').show().next().hide();
        });
        });
        });
        <?php
        return ob_get_clean();
    }

}
