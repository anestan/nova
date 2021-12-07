<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');

require_once 'plugin_options_data.php';

$options = array(
    array(
        'name' => '',
        'type' => 'title',
        'desc' => '',
        'id' => 'woocs_general_settings'
    ),
    array(
        'name' => esc_html__('Welcome currency', 'woocommerce-currency-switcher'),
        'desc' => esc_html__('In wich currency show prices for first visit of your customer on your site. Do not do it private to avoid logic mess!', 'woocommerce-currency-switcher'),
        'id' => 'woocs_welcome_currency',
        'type' => 'select',
        'class' => 'chosen_select',
        'css' => 'min-width:300px;',
        'options' => $welcome_curr_options,
        'desc_tip' => true
    ),
    array(
        'name' => esc_html__('Currency aggregator', 'woocommerce-currency-switcher'),
        'desc' => esc_html__('Currency aggregators. Note: If you know aggregator which not is represented in WOOCS write request on support please with suggestion to add it!', 'woocommerce-currency-switcher'),
        'id' => 'woocs_currencies_aggregator',
        'type' => 'select',
        'class' => 'chosen_select',
        'css' => 'min-width:300px;',
        'options' => $aggregators,
        'desc_tip' => true
    ),
    array(
        'name' => esc_html__('Aggregator API key', 'woocommerce-currency-switcher'),
        'desc' => esc_html__('Some aggregators require an API key. See the hint below how to get it!', 'woocommerce-currency-switcher'),
        'id' => 'woocs_aggregator_key',
        'type' => 'text',
        'default' => '',
        'css' => 'min-width:300px;',
        'desc_tip' => true
    ),
    array(
        'name' => esc_html__('Currency storage', 'woocommerce-currency-switcher'),
        'desc' => esc_html__('In some servers there is troubles with sessions, and after currency selecting its reset to welcome currency or geo ip currency. In such case use transient. If it is possible on your hosting use Memcached or Redis!', 'woocommerce-currency-switcher'),
        'id' => 'woocs_storage',
        'type' => 'select',
        'class' => 'chosen_select',
        'css' => 'min-width:300px;',
        'options' => $storage_options,
        'desc_tip' => true
    ),
    array(
        'name' => esc_html__('Storage server', 'woocommerce-currency-switcher'),
        'desc' => esc_html__('Server or socket for: memcached or redis. Usually is localhost. Read your hosting documentation about what host to use for memcached or redis.', 'woocommerce-currency-switcher'),
        'id' => 'woocs_storage_server',
        'type' => 'text',
        'default' => '',
        'css' => 'min-width:300px;',
        'desc_tip' => true
    ),
    array(
        'name' => esc_html__('Storage port', 'woocommerce-currency-switcher'),
        'desc' => esc_html__('Port for: memcached or redis. Usually for memcached port is 11211, for redis port is 6379. Read your hosting documentation about what port to use for memcached or redis.', 'woocommerce-currency-switcher'),
        'id' => 'woocs_storage_port',
        'type' => 'text',
        'default' => '',
        'css' => 'min-width:300px;',
        'desc_tip' => true
    ),
    array(
        'name' => esc_html__('Rate auto update', 'woocommerce-currency-switcher'),
        'desc' => esc_html__('Currencies rate auto update by WordPress cron.', 'woocommerce-currency-switcher'),
        'id' => 'woocs_currencies_rate_auto_update',
        'type' => 'select',
        'class' => 'chosen_select',
        'css' => 'min-width:300px;',
        'options' => array(
            'no' => esc_html__('no auto update', 'woocommerce-currency-switcher'),
            'hourly' => esc_html__('hourly', 'woocommerce-currency-switcher'),
            'twicedaily' => esc_html__('twicedaily', 'woocommerce-currency-switcher'),
            'daily' => esc_html__('daily', 'woocommerce-currency-switcher'),
            'week' => esc_html__('weekly', 'woocommerce-currency-switcher'),
            'month' => esc_html__('monthly', 'woocommerce-currency-switcher'),
            'min1' => esc_html__('special: each minute', 'woocommerce-currency-switcher'), //for tests
            'min5' => esc_html__('special: each 5 minutes', 'woocommerce-currency-switcher'), //for tests
            'min15' => esc_html__('special: each 15 minutes', 'woocommerce-currency-switcher'), //for tests
            'min30' => esc_html__('special: each 30 minutes', 'woocommerce-currency-switcher'), //for tests
            'min45' => esc_html__('special: each 45 minutes', 'woocommerce-currency-switcher'), //for tests
        ),
        'desc_tip' => true
    ),
    array(
        'name' => esc_html__('Custom money signs', 'woocommerce-currency-switcher'),
        'desc' => esc_html__('Add your money symbols in your shop. Example: $USD,AAA,AUD$,DDD - separated by commas', 'woocommerce-currency-switcher'),
        'id' => 'woocs_customer_signs',
        'type' => 'textarea',
        'default' => '',
        'css' => 'min-width:400px;',
        'desc_tip' => true
    ),
    array(
        'name' => esc_html__('Custom price format', 'woocommerce-currency-switcher'),
        'desc' => esc_html__('Set your format how to display price on front. Use keys: __CODE__,__PRICE__. Leave it empty to use default format. Example: __PRICE__ (__CODE__)', 'woocommerce-currency-switcher'),
        'id' => 'woocs_customer_price_format',
        'type' => 'text',
        'default' => '',
        'css' => 'min-width:400px;',
        'desc_tip' => true
    ),
    array(
        'name' => esc_html__('Prices without cents', 'woocommerce-currency-switcher'),
        'desc' => esc_html__('Recount prices without cents everywhere like in JPY and TWD which by its nature have not cents. Use comma. Example: UAH,RUB. Test it for checkout after setup!', 'woocommerce-currency-switcher'),
        'id' => 'woocs_no_cents',
        'type' => 'text',
        'default' => '',
        'css' => 'min-width:400px;',
        'desc_tip' => true
    ),
    array('type' => 'sectionend', 'id' => 'woocs_general_settings')
);
?>

<div class="woocs-admin-preloader">
    <div class="cssload-loader">
        <div class="cssload-inner cssload-one"></div>
        <div class="cssload-inner cssload-two"></div>
        <div class="cssload-inner cssload-three"></div>
    </div>
</div>


<div class="subsubsub_section woocs_subsubsub_section">
    <br class="clear" />

    <div class="section">

        <div class="woocs__section-title">
            <?php echo draw_switcher23('woocs_admin_theme_id', get_option('woocs_admin_theme_id', 0), 'woocs_admin_theme_id', esc_html__('Modern WOOCS admin theme', 'woocommerce-currency-switcher')); ?>
            <h3 class="woocs_settings_version">WOOCS - <?php printf(esc_html__('WooCommerce Currency Switcher v.%s', 'woocommerce-currency-switcher'), WOOCS_VERSION) ?></h3>
            <i><?php printf(esc_html__('Actualized for WooCommerce v.%s.x', 'woocommerce-currency-switcher'), $this->actualized_for) ?></i><br />
        </div>
        <br />

        <div id="tabs" class="wfc-tabs wfc-tabs-style-shape" >

            <?php if (version_compare(WOOCOMMERCE_VERSION, WOOCS_MIN_WOOCOMMERCE, '<')): ?>

                <b class="woocs_settings_version" ><?php printf(esc_html__("Your version of WooCommerce plugin is too obsolete. Update minimum to %s version to avoid malfunctionality!", 'woocommerce-currency-switcher'), WOOCS_MIN_WOOCOMMERCE) ?></b><br />

            <?php endif; ?>

            <input type="hidden" name="woocs_woo_version" value="<?php echo WOOCOMMERCE_VERSION ?>" />

            <svg class="hidden">
            <defs>
            <path id="tabshape" d="M80,60C34,53.5,64.417,0,0,0v60H80z"/>
            </defs>
            </svg><nav>
                <ul>
                    <li class="tab-current">
                        <a href="#tabs-1">
                            <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                            <span><?php esc_html_e("Currencies", 'woocommerce-currency-switcher') ?></span>
                        </a>
                    </li><li>
                        <a href="#tabs-2">
                            <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                            <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                            <span><?php esc_html_e("Options", 'woocommerce-currency-switcher') ?></span>
                        </a>
                    </li><li><a href="#tabs-3">
                            <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                            <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                            <span><?php esc_html_e("Advanced", 'woocommerce-currency-switcher') ?></span>
                        </a></li>

                    <?php if ($woocs_is_payments_rule_enable): ?>
                        <li>
                            <a href="#tabs-7">
                                <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                                <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                                <span><?php esc_html_e("Payments", 'woocommerce-currency-switcher') ?></span>
                            </a>
                        </li>
                    <?php endif; ?>


                    <?php if ($this->is_use_geo_rules()): ?>
                        <li>
                            <a href="#tabs-4">
                                <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                                <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                                <span><?php esc_html_e("GeoIP", 'woocommerce-currency-switcher') ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($this->statistic AND $this->statistic->can_collect()): ?>
                        <li>
                            <a href="#tabs-stat" onclick="return woocs_stat_activate_graph();">
                                <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                                <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                                <span><?php esc_html_e("Statistic", 'woocommerce-currency-switcher') ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <li>
                        <a href="#tab-smart-designer">
                            <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                            <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                            <span><?php esc_html_e("Designer", 'woocommerce-currency-switcher') ?></span>
                        </a>
                    </li>

                    <li>
                        <a href="#tabs-6">
                            <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                            <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                            <span><?php esc_html_e("Side switcher", 'woocommerce-currency-switcher') ?></span>
                        </a>
                    </li>


                    <li>
                        <a href="#tabs-5">
                            <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                            <svg viewBox="0 0 80 60" preserveAspectRatio="none"><use xlink:href="#tabshape"></use></svg>
                            <span><?php esc_html_e("Help", 'woocommerce-currency-switcher') ?></span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="content-wrap">
                <section id="tabs-1" class="content-current">
                    <div class="wcf-control-section">

                        <div id="woocs_tools_panel">

                            <div class="woocs-options-float-left">

                                <a href="#" id="woocs_add_currency" class="woocs-panel-button dashicons-before dashicons-plus"><?php esc_html_e("Add Currency", 'woocommerce-currency-switcher') ?></a>
                                <a href="javascript: woocs_update_all_rates(); void(0);" class="woocs-panel-button dashicons-before dashicons-update"><?php esc_html_e("Update all rates", 'woocommerce-currency-switcher') ?></a>
                                <a href="javascript: woocs_add_money_sign2(); void(0);" class="woocs-panel-button dashicons-before dashicons-plus"><?php esc_html_e("Add custom money sign", 'woocommerce-currency-switcher') ?></a>

                            </div>
                            <div class="woocs_drop_down_view_panel">

                                <?php
                                $opts = array(
                                    'no' => esc_html__('Not styled drop-down', 'woocommerce-currency-switcher'),
                                    'style-1' => esc_html__('Style #1', 'woocommerce-currency-switcher'),
                                    'style-2' => esc_html__('Style #2', 'woocommerce-currency-switcher'),
                                    'style-3' => esc_html__('Style #3', 'woocommerce-currency-switcher'),
                                    'flags' => esc_html__('Flags (as images)', 'woocommerce-currency-switcher'),
                                    //+++
                                    'ddslick' => esc_html__('ddslick drop-down', 'woocommerce-currency-switcher'),
                                    'chosen' => esc_html__('Chosen drop-down', 'woocommerce-currency-switcher'),
                                    'chosen_dark' => esc_html__('Chosen dark drop-down', 'woocommerce-currency-switcher'),
                                    'wselect' => esc_html__('wSelect drop-down', 'woocommerce-currency-switcher')
                                );

                                $selected = trim(get_option('woocs_drop_down_view', 'ddslick'));
                                ?>

                                <label for="woocs_drop_down_view" class="woocs-options-valign-top"><span class="woocommerce-help-tip" data-tip="<?php echo esc_html__('How to display currency switcher (by default) on the site front. (NEW) Make your attention on skins with numbers - you can use them on the same page with different designs in shortcode [woocs] described in its attribute style and style number (see Codex page in Info Help tab)!', 'woocommerce-currency-switcher') ?>"></span></label>
                                &nbsp;<select name="woocs_drop_down_view" id="woocs_drop_down_view" class="chosen_select woocs-options-fix1">

                                    <?php foreach ($opts as $key => $value) : ?>
                                        <option value="<?= $key ?>" <?php selected($key === $selected) ?>><?= $value ?></option>
                                    <?php endforeach; ?>

                                </select>

                            </div>
                            <div class="clear"></div>



                        </div>

                        <div class="woocs_settings_hide">
                            <template id="woocs_item_tpl"><?php
                                $empty = array(
                                    'name' => '',
                                    'rate' => 0,
                                    'symbol' => '',
                                    'position' => '',
                                    'is_etalon' => 0,
                                    'description' => '',
                                    'hide_cents' => 0
                                );
                                woocs_print_currency($this, $empty);
                                ?>
                            </template>
                        </div>

                        <div class="woocs-data-table" id="woocs-currencies-options">

                            <div class="scrollbar-external_wrapper">
                                <div class="scrollbar-external">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th><?php echo esc_html__('Basic', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Currency', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Symbol', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Position', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Decimal', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Separators', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Rate and Interest', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Cents', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Visibility', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Flag', 'woocommerce-currency-switcher') ?></th>
                                                <th>A</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th><?php echo esc_html__('Basic', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Currency', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Symbol', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Position', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Decimal', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Separators', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Rate and Interest', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Cents', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Visibility', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Flag', 'woocommerce-currency-switcher') ?></th>
                                                <th>A</th>
                                            </tr>
                                        </tfoot>
                                        <tbody id="woocs_list">
                                            <?php
                                            if (!empty($currencies) AND is_array($currencies)) {
                                                foreach ($currencies as $key => $currency) {
                                                    woocs_print_currency($this, $currency);
                                                }
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>


                            <div class="external-scroll_wrapper">
                                <div class="external-scroll_x">
                                    <div class="scroll-element_outer">
                                        <div class="scroll-element_size"></div>
                                        <div class="scroll-element_track"></div>
                                        <div class="scroll-bar"></div>
                                    </div>
                                </div>
                            </div>



                        </div>

                        <br />


                        <div class="woocs_settings_codes">
                            <a href="https://en.wikipedia.org/wiki/ISO_4217#Active_codes" target="_blank" class="button button-primary button-large dashicons-before dashicons-book woocs-panel-button"><?php esc_html_e("Read wiki about Currency Active codes  <-  Get right currencies codes here if you are not sure about it!", 'woocommerce-currency-switcher') ?></a>
                        </div>

                        <div class="woocs_settings_clear"></div>

                    </div>
                </section>
                <section id="tabs-2">
                    <div class="wfc-control-section-xxx">

                        <?php woocommerce_admin_fields($options); ?>

                        <table class="form-table">
                            <tbody>
                                <tr valign="top">
                                    <th scope="row" class="titledesc">
                                        <label for="woocs_show_flags"><?php echo esc_html__('Show flags by default', 'woocommerce-currency-switcher') ?></label>
                                        <span class="woocommerce-help-tip woocs_settings_tip"  data-tip="<?php echo esc_html__("Show/hide flags on the front drop-down", 'woocommerce-currency-switcher') ?>"></span>
                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php echo draw_switcher23('woocs_show_flags', get_option('woocs_show_flags', 1)); ?>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row" class="titledesc">
                                        <label for="woocs_special_ajax_mode"><?php echo esc_html__('No GET data in link', 'woocommerce-currency-switcher') ?></label>
                                        <span class="woocommerce-help-tip woocs_settings_tip"  data-tip="<?php echo esc_html__("Switches currency without GET properties (?currency=USD) in the link.", 'woocommerce-currency-switcher') ?>"></span>
                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php echo draw_switcher23('woocs_special_ajax_mode', get_option('woocs_special_ajax_mode', 0)); ?>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row" class="titledesc">
                                        <label for="woocs_show_money_signs"><?php echo esc_html__('Show money signs', 'woocommerce-currency-switcher') ?></label>
                                        <span class="woocommerce-help-tip woocs_settings_tip"  data-tip="<?php echo esc_html__("Show/hide money signs on the front drop-down", 'woocommerce-currency-switcher') ?>"></span>
                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php echo draw_switcher23('woocs_show_money_signs', get_option('woocs_show_money_signs', 1)); ?>
                                    </td>
                                </tr>


                                <tr valign="top">
                                    <th scope="row" class="titledesc">
                                        <label for="woocs_price_info"><?php echo esc_html__('Show price info icon', 'woocommerce-currency-switcher') ?></label>
                                        <span class="woocommerce-help-tip woocs_settings_tip"  data-tip="<?php echo esc_html__("Show info icon near the price of the product which while its under hover shows prices of products in all currencies", 'woocommerce-currency-switcher') ?>"></span>
                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php echo draw_switcher23('woocs_price_info', get_option('woocs_price_info', 0)); ?>
                                    </td>
                                </tr>


                                <tr valign="top">
                                    <th scope="row" class="titledesc">
                                        <label for="woocs_rate_auto_update_email"><?php echo esc_html__('Email notice about "Rate auto update" results', 'woocommerce-currency-switcher') ?></label>
                                        <span class="woocommerce-help-tip woocs_settings_tip"  data-tip="<?php echo esc_html__("After cron done - new currency rates will be sent on the site admin email. ATTENTION: if you not got emails - it is mean that PHP function mail() doesnt work on your server or sending emails by this function is locked.", 'woocommerce-currency-switcher') ?>"></span>
                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php echo draw_switcher23('woocs_rate_auto_update_email', get_option('woocs_rate_auto_update_email', 0)); ?>
                                    </td>
                                </tr>


                                <tr valign="top">
                                    <th scope="row" class="titledesc">
                                        <label for="woocs_restrike_on_checkout_page"><?php echo esc_html__('Hide switcher on checkout page', 'woocommerce-currency-switcher') ?></label>
                                        <span class="woocommerce-help-tip woocs_settings_tip"  data-tip="<?php echo esc_html__("Hide switcher on checkout page for any of your reason. Better restrike for users change currency on checkout page in multiple mode.", 'woocommerce-currency-switcher') ?>"></span>
                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php echo draw_switcher23('woocs_restrike_on_checkout_page', get_option('woocs_restrike_on_checkout_page', 0)); ?>
                                    </td>
                                </tr>


                                <tr valign="top">
                                    <th scope="row" class="titledesc">
                                        <label for="woocs_payments_rule_enabled"><?php echo esc_html__('Payments rules', 'woocommerce-currency-switcher') ?></label>
                                        <span class="woocommerce-help-tip woocs_settings_tip"  data-tip="<?php echo esc_html__("Hide/Show payments systems on checkout page depending on the current currency", 'woocommerce-currency-switcher') ?>"></span>
                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php echo draw_switcher23('woocs_payments_rule_enabled', get_option('woocs_payments_rule_enabled', 0)); ?>
                                    </td>
                                </tr>


                                <tr valign="top">
                                    <th scope="row" class="titledesc">
                                        <label for="woocs_show_approximate_amount"><?php echo esc_html__('Show approx. amount', 'woocommerce-currency-switcher') ?></label>
                                        <span class="woocommerce-help-tip woocs_settings_tip"  data-tip="<?php echo esc_html__("Show approximate amount on the checkout and the cart page with currency of user defined by IP in the GeoIp rules tab. Works only with currencies rates data and NOT with fixed prices rules and geo rules.", 'woocommerce-currency-switcher') ?>"></span>
                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php echo draw_switcher23('woocs_show_approximate_amount', get_option('woocs_show_approximate_amount', 0)); ?>
                                    </td>
                                </tr>


                                <tr valign="top">
                                    <th scope="row" class="titledesc">
                                        <label for="woocs_show_approximate_price"><?php echo esc_html__('Show approx. price', 'woocommerce-currency-switcher') ?></label>
                                        <span class="woocommerce-help-tip woocs_settings_tip"  data-tip="<?php echo esc_html__("Show approximate price on the shop and the single product page with currency of user defined by IP in the GeoIp rules tab. Works only with currencies rates data and NOT with fixed prices rules and geo rules.", 'woocommerce-currency-switcher') ?>"></span>
                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php echo draw_switcher23('woocs_show_approximate_price', get_option('woocs_show_approximate_price', 0)); ?>
                                    </td>
                                </tr>




                                <tr valign="top">
                                    <th scope="row" class="titledesc">
                                        <label for="woocs_shop_is_cached"><?php echo esc_html__('I am using cache plugin on my site', 'woocommerce-currency-switcher') ?></label>
                                        <span class="woocommerce-help-tip woocs_settings_tip"  data-tip="<?php echo esc_html__('Set Yes here ONLY if you are REALLY use cache plugin for your site, for example like Super cache or Hiper cache (doesn matter). + Set "Custom price format", for example: __PRICE__ (__CODE__). After enabling this feature - clean your cache to make it works. It will allow show prices in selected currency on all pages of site. Fee for this feature - additional AJAX queries for products prices redrawing.', 'woocommerce-currency-switcher') ?>"></span>
                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php echo draw_switcher23('woocs_shop_is_cached', get_option('woocs_shop_is_cached', 0)); ?>
                                    </td>
                                </tr>

                                <tr valign="top" <?php echo (!get_option('woocs_shop_is_cached', 0)) ? "style='display:none'" : "" ?>>
                                    <th scope="row" class="titledesc">
                                        <label for="woocs_shop_is_cached"><?php echo esc_html__('Prices preloader', 'woocommerce-currency-switcher') ?></label>
                                        <span class="woocommerce-help-tip woocs_settings_tip"  data-tip="<?php echo esc_html__('For ajax  redraw.', 'woocommerce-currency-switcher') ?>"></span>
                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php echo draw_switcher23('woocs_shop_is_cached_preloader', get_option('woocs_shop_is_cached_preloader', 0)); ?>
                                    </td>
                                </tr>


                                <tr valign="top">
                                    <th scope="row" class="titledesc">
                                        <label for="woocs_show_top_button"><?php echo esc_html__('Show options button on top admin bar.', 'woocommerce-currency-switcher') ?></label>
                                        <span class="woocommerce-help-tip woocs_settings_tip"  data-tip="<?php echo esc_html__('Show WOOCS options button on top admin bar for quick access. Very handy for active work. Visible for site administrators only!', 'woocommerce-currency-switcher') ?>"></span>
                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php echo draw_switcher23('woocs_show_top_button', get_option('woocs_show_top_button', 0)); ?>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th scope="row" class="titledesc">
                                        <label for="woocs_activate_page_list"><?php esc_html_e('Disable on pages', 'woocommerce-currency-switcher') ?></label>
                                        <span class="woocommerce-help-tip" data-tip="<?php esc_html_e('Disabling or Enabling WOOCS on the described pages only. Use comma and pages slugs. Example: blog,account', 'woocommerce-currency-switcher') ?>"></span>
                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php
                                        $opts = array(
                                            0 => esc_html__('Direct use', 'woocommerce-currency-switcher'),
                                            1 => esc_html__('Reverse use', 'woocommerce-currency-switcher')
                                        );
                                        $woocs_activate_page_list_reverse = get_option('woocs_activate_page_list_reverse', 1);

                                        $woocs_activate_page_list = get_option('woocs_activate_page_list', '');
                                        ?>
                                        <input type="text" name="woocs_activate_page_list" id="woocs_activate_page_list" class="woocs_settings_dd" value="<?php echo $woocs_activate_page_list ?>">
                                        <select name="woocs_activate_page_list_reverse" id="woocs_activate_page_list_reverse" class="chosen_select enhanced" tabindex="-1" title="<?php esc_html_e('Reverse this option', 'woocommerce-currency-switcher') ?>">

                                            <?php foreach ($opts as $val => $title): ?>
                                                <option value="<?php echo $val ?>" <?php echo selected($woocs_activate_page_list_reverse, $val) ?>><?php echo $title ?></option>
                                            <?php endforeach; ?>

                                        </select>
                                    </td>
                                </tr>

                            </tbody>
                        </table>

                    </div>
                </section>

                <section id="tabs-3">

                    <table class="form-table">
                        <tbody>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label for="woocs_is_multiple_allowed"><?php esc_html_e('Is multiple allowed', 'woocommerce-currency-switcher') ?></label>
                                    <span class="woocommerce-help-tip" data-tip="<?php esc_html_e('Customer will pay with selected currency (Yes) or with default currency (No).', 'woocommerce-currency-switcher') ?>"></span>
                                </th>
                                <td class="forminp forminp-select">
                                    <?php
                                    $opts = array(
                                        0 => esc_html__('No', 'woocommerce-currency-switcher'),
                                        1 => esc_html__('Yes', 'woocommerce-currency-switcher')
                                    );
                                    $woocs_is_multiple_allowed = get_option('woocs_is_multiple_allowed', 0);
                                    ?>
                                    <select name="woocs_is_multiple_allowed" id="woocs_is_multiple_allowed" class="chosen_select enhanced woocs_settings_dd" tabindex="-1" title="<?php esc_html_e('Is multiple allowed', 'woocommerce-currency-switcher') ?>">

                                        <?php foreach ($opts as $val => $title): ?>
                                            <option value="<?php echo $val ?>" <?php echo selected($woocs_is_multiple_allowed, $val) ?>><?php echo $title ?></option>
                                        <?php endforeach; ?>

                                    </select>
                                </td>
                            </tr>

                            <tr valign="top" class="<?php if (!$woocs_is_multiple_allowed): ?>woocs_settings_hide<?php endif; ?>" >
                                <th scope="row" class="titledesc">
                                    <label for="woocs_is_fixed_enabled"><?php esc_html_e('Individual fixed prices rules for each product', 'woocommerce-currency-switcher') ?>(*)</label>
                                    <span class="woocommerce-help-tip woocs_settings_tip"  data-tip="<?php esc_html_e("You will be able to set FIXED prices for simple and variable products. ATTENTION: 'Is multiple allowed' should be enabled!", 'woocommerce-currency-switcher') ?>"></span>
                                </th>
                                <td class="forminp forminp-select">

                                    <?php
                                    $woocs_is_fixed_enabled = get_option('woocs_is_fixed_enabled', 0);
                                    echo draw_switcher23('woocs_is_fixed_enabled', $woocs_is_fixed_enabled, 'woocs_blind_option');
                                    ?>
                                    <br />
                                    &nbsp;<a href="https://currency-switcher.com/video-tutorials#video_YHDQZG8GS6w" target="_blank" class="button"><?php esc_html_e('Watch video instructions', 'woocommerce-currency-switcher') ?></a>
                                </td>
                            </tr>


                            <tr valign="top" class="<?php if (!$woocs_is_fixed_enabled): ?>woocs_settings_hide<?php endif; ?>" >
                                <th scope="row" class="titledesc">
                                    <label for="woocs_force_pay_bygeoip_rules"><?php esc_html_e('Checkout by GeoIP rules', 'woocommerce-currency-switcher') ?></label>
                                    <span class="woocommerce-help-tip woocs_settings_geo_tip" data-tip="<?php esc_html_e("Force the customers to pay on checkout page by rules defined in 'GeoIP rules' tab. ATTENTION: this feature has logical sense if you enabled 'Enable fixed pricing' and also installed fixed prices rules in the products for different currencies!", 'woocommerce-currency-switcher') ?>"></span>
                                    <?php
                                    if (!empty($pd) AND!empty($countries) AND isset($countries[$pd['country']])) {
                                        echo '<i class="woocs_settings_i1" >' . sprintf(esc_html__('Your country is: %s', 'woocommerce-currency-switcher'), $countries[$pd['country']]) . '</i>';
                                    } else {
                                        echo '<i class="woocs_settings_i2" >' . esc_html__('Your country is not defined! Troubles with internet connection or GeoIp service.', 'woocommerce-currency-switcher') . '</i>';
                                    }
                                    ?>

                                </th>
                                <td class="forminp forminp-select">
                                    <?php echo draw_switcher23('woocs_force_pay_bygeoip_rules', get_option('woocs_force_pay_bygeoip_rules', 0)); ?>
                                </td>
                            </tr>

                            <tr valign="top" class="<?php if (!$woocs_is_multiple_allowed): ?>woocs_settings_hide<?php endif; ?>" >
                                <th scope="row" class="titledesc">
                                    <label for="woocs_is_fixed_coupon"><?php esc_html_e('Individual fixed amount for coupon', 'woocommerce-currency-switcher') ?>(*)</label>
                                    <span class="woocommerce-help-tip woocs_settings_tip"  data-tip="<?php esc_html_e("You will be able to set FIXED amount for coupon for each currency. ATTENTION: 'Is multiple allowed' should be enabled!", 'woocommerce-currency-switcher') ?>"></span>
                                </th>
                                <td class="forminp forminp-select">
                                    <?php echo draw_switcher23('woocs_is_fixed_coupon', get_option('woocs_is_fixed_coupon', 0)); ?>
                                </td>
                            </tr>

                            <tr valign="top" class="<?php if (!$woocs_is_multiple_allowed): ?>woocs_settings_hide<?php endif; ?>">
                                <th scope="row" class="titledesc">
                                    <label for="woocs_is_fixed_shipping"><?php esc_html_e('Individual fixed amount for shipping', 'woocommerce-currency-switcher') ?>(*)</label>
                                    <span class="woocommerce-help-tip woocs_settings_tip"  data-tip="<?php esc_html_e("You will be able to set FIXED amount for each currency for free and all another shipping ways. ATTENTION: 'Is multiple allowed' should be enabled!", 'woocommerce-currency-switcher') ?>"></span>
                                </th>
                                <td class="forminp forminp-select">
                                    <?php echo draw_switcher23('woocs_is_fixed_shipping', get_option('woocs_is_fixed_shipping', 0)); ?>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label for="woocs_is_fixed_user_role"><?php esc_html_e('Individual prices based on user role', 'woocommerce-currency-switcher') ?>(*)</label>
                                    <span class="woocommerce-help-tip woocs_settings_user_tip"  data-tip="<?php esc_html_e('Gives ability to set different prices for each user role', 'woocommerce-currency-switcher') ?>"></span>
                                </th>
                                <td class="forminp forminp-select">
                                    <?php echo draw_switcher23('woocs_is_fixed_user_role', get_option('woocs_is_fixed_user_role', 0)); ?>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label for="woocs_is_geoip_manipulation"><?php esc_html_e('Individual GeoIP rules for each product', 'woocommerce-currency-switcher') ?>(*)</label>
                                    <span class="woocommerce-help-tip woocs_settings_user_tip" data-tip="<?php esc_html_e("You will be able to set different prices for each product (in BASIC currency) for different countries", 'woocommerce-currency-switcher') ?>"></span>
                                </th>
                                <td class="forminp forminp-select">

                                    <?php echo draw_switcher23('woocs_is_geoip_manipulation', get_option('woocs_is_geoip_manipulation', 0), 'woocs_blind_option'); ?>
                                    <br />
                                    &nbsp;<a href="https://currency-switcher.com/video-tutorials#video_PZugTH80-Eo" target="_blank" class="button"><?php esc_html_e('Watch video instructions', 'woocommerce-currency-switcher') ?></a>
                                    &nbsp;<a href="https://currency-switcher.com/video-tutorials#video_zh_LVqKADBU" target="_blank" class="button"><?php esc_html_e('a hint', 'woocommerce-currency-switcher') ?></a>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label for="woocs_collect_statistic" class="woocs-red"><?php esc_html_e('Statistic', 'woocommerce-currency-switcher') ?></label>
                                    <span class="woocommerce-help-tip" data-tip="<?php esc_html_e('Collect currencies switching statistic for business purposes. No any private data of customers collects, only currency, country and time of switching. Also statistic for order currencies is there.', 'woocommerce-currency-switcher') ?>"></span>
                                </th>
                                <td class="forminp forminp-select">
                                    <?php
                                    $enable_stat = array(
                                        0 => esc_html__('No', 'woocommerce-currency-switcher'),
                                        1 => esc_html__('Yes', 'woocommerce-currency-switcher')
                                    );
                                    $collect_statistic = get_option('woocs_collect_statistic', 0);
                                    ?>
                                    <select name="woocs_collect_statistic" id="woocs_collect_statistic" class="chosen_select enhanced woocs_settings_dd" tabindex="-1" title="<?php esc_html_e('Statistic', 'woocommerce-currency-switcher') ?>">

                                        <?php foreach ($enable_stat as $val => $title): ?>
                                            <option value="<?php echo $val ?>" <?php echo selected($collect_statistic, $val) ?>><?php echo $title ?></option>
                                        <?php endforeach; ?>

                                    </select>
                                </td>
                            </tr>


                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label><?php esc_html_e('Notes*', 'woocommerce-currency-switcher') ?></label>
                                </th>
                                <td class="forminp forminp-select">


                                    <i><?php esc_html_e('Native WooCommerce price filter is blind for all data generated by marked features', 'woocommerce-currency-switcher') ?></i>

                                </td>
                            </tr>


                        </tbody>
                    </table>




                </section>

                <section id="tabs-6" class="woocs_settings_section" >

                    <table class="form-table">
                        <tbody>
                            <tr valign="top">
                                <th scope="row" class="titledesc">
                                    <label for="woocs_is_auto_switcher"><?php esc_html_e('Enable/Disable', 'woocommerce-currency-switcher') ?></label>
                                    <span class="woocommerce-help-tip" data-tip="<?php esc_html_e('Enable/Disable the side currency switcher on your page', 'woocommerce-currency-switcher') ?>"></span>
                                </th>
                                <td class="forminp forminp-select">
                                    <?php
                                    $woocs_is_auto_switcher = get_option('woocs_is_auto_switcher', 0);
                                    echo draw_switcher23('woocs_is_auto_switcher', $woocs_is_auto_switcher, 'woocs_is_auto_switcher');
                                    ?>

                                    <a href="<?php echo WOOCS_LINK ?>img/side-switcher.png" class="demo-img-1 <?php if (!$woocs_is_auto_switcher): ?>woocs_settings_hide<?php endif; ?> woocs-options-fix2" target="_blank"><img width="200" src="<?php echo WOOCS_LINK ?>img/side-switcher.png" /></a>
                                </td>
                            </tr>

                            <tr valign="top" class="<?php if (!$woocs_is_auto_switcher): ?>woocs_settings_hide<?php endif; ?>">
                                <th scope="row" class="titledesc">
                                    <label for="woocs_auto_switcher_skin"><?php esc_html_e('Skin', 'woocommerce-currency-switcher') ?></label>
                                    <span class="woocommerce-help-tip woocs_settings_tip"  data-tip="<?php esc_html_e("Style of the switcher on the site front", 'woocommerce-currency-switcher') ?>"></span>
                                </th>
                                <td class="forminp forminp-select">
                                    <?php
                                    $opts = array(
                                        'classic_blocks' => esc_html__('Classic blocks', 'woocommerce-currency-switcher'),
                                        'roll_blocks' => esc_html__('Roll blocks', 'woocommerce-currency-switcher'),
                                        // 'round_chain' => esc_html__('Round chain', 'woocommerce-currency-switcher'),
                                        'round_select' => esc_html__('Round select', 'woocommerce-currency-switcher'),
                                    );
                                    $woocs_auto_switcher_skin = get_option('woocs_auto_switcher_skin', 'classic_blocks');
                                    ?>
                                    <select name="woocs_auto_switcher_skin" id="woocs_auto_switcher_skin"  class="chosen_select enhanced woocs_settings_dd" tabindex="-1" title="<?php esc_html_e('Choice skin', 'woocommerce-currency-switcher') ?>">

                                        <?php foreach ($opts as $val => $title): ?>
                                            <option value="<?php echo $val ?>" <?php echo selected($woocs_auto_switcher_skin, $val) ?>><?php echo $title ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="woocs_roll_blocks_width" class="<?php if ($woocs_auto_switcher_skin != 'roll_blocks'): ?>woocs_settings_hide<?php endif; ?>">
                                        <input type="text" name="woocs_auto_switcher_roll_px" id="woocs_auto_switcher_roll_px" placeholder="<?php esc_html_e('enter roll width', 'woocommerce-currency-switcher') ?>"  value="<?php echo get_option('woocs_auto_switcher_roll_px', 90) ?>" />.px<br />
                                    </div>
                                </td>

                            </tr>
                            <tr valign="top" class="<?php if (!$woocs_is_auto_switcher): ?>woocs_settings_hide<?php endif; ?>">
                                <th scope="row" class="titledesc">
                                    <label for="woocs_auto_switcher_side"><?php esc_html_e('Side', 'woocommerce-currency-switcher') ?></label>
                                    <span class="woocommerce-help-tip woocs_settings_tip"  data-tip="<?php esc_html_e("The side where the switcher is be placed", 'woocommerce-currency-switcher') ?>"></span>
                                </th>
                                <td class="forminp forminp-select">
                                    <?php
                                    $opts = array(
                                        'left' => esc_html__('Left', 'woocommerce-currency-switcher'),
                                        'right' => esc_html__('Right', 'woocommerce-currency-switcher'),
                                    );
                                    $woocs_auto_switcher_side = get_option('woocs_auto_switcher_side', 'left');
                                    ?>
                                    <select name="woocs_auto_switcher_side" id="woocs_auto_switcher_side"  class="chosen_select enhanced woocs_settings_dd" tabindex="-1" title="<?php esc_html_e('Choice side', 'woocommerce-currency-switcher') ?>">
                                        <?php foreach ($opts as $val => $title): ?>
                                            <option value="<?php echo $val ?>" <?php echo selected($woocs_auto_switcher_side, $val) ?>><?php echo $title ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr valign="top" class="<?php if (!$woocs_is_auto_switcher): ?>woocs_settings_hide<?php endif; ?>">
                                <th scope="row" class="titledesc">
                                    <label for="woocs_auto_switcher_top_margin"><?php esc_html_e('Top margin', 'woocommerce-currency-switcher') ?></label>
                                    <span class="woocommerce-help-tip woocs_settings_tip"  data-tip="<?php esc_html_e("Distance from the top of the screen to the switcher html block. You can set in px or in %. Example 1: 100px. Example 2: 10%.", 'woocommerce-currency-switcher') ?>"></span>
                                </th>
                                <td class="forminp forminp-select">
                                    <?php
                                    $woocs_auto_switcher_top_margin = get_option('woocs_auto_switcher_top_margin', '100px');
                                    ?>
                                    <input type="text" name="woocs_auto_switcher_top_margin" id="woocs_auto_switcher_top_margin" class="woocs_settings_dd" value="<?php echo $woocs_auto_switcher_top_margin ?>" >
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <table class="form-table <?php if (!$woocs_is_auto_switcher): ?>woocs_settings_hide<?php endif; ?>">
                        <tbody>
                            <?php
                            $color = array(
                                array(
                                    'name' => esc_html__('Main color', 'woocommerce-currency-switcher'),
                                    'desc' => esc_html__('Main color which coloring the switcher elements', 'woocommerce-currency-switcher'),
                                    'id' => 'woocs_auto_switcher_color',
                                    'type' => 'color',
                                    'std' => '', // WooCommerce < 2.0
                                    'default' => '#222222', // WooCommerce >= 2.0
                                    'css' => 'min-width:500px;',
                                    'desc_tip' => true
                                ),
                                array(
                                    'name' => esc_html__('Hover color', 'woocommerce-currency-switcher'),
                                    'desc' => esc_html__('The switcher color when mouse hovering', 'woocommerce-currency-switcher'),
                                    'id' => 'woocs_auto_switcher_hover_color',
                                    'type' => 'color',
                                    'std' => '', // WooCommerce < 2.0
                                    'default' => '#3b5998', // WooCommerce >= 2.0
                                    'css' => 'min-width:500px;',
                                    'desc_tip' => true
                                ),
                                    // array('type' => 'sectionend', 'id' => 'woocs_color')
                            );

                            woocommerce_admin_fields($color);
                            ?>

                        </tbody>
                    </table>

                    <table class="form-table">
                        <tbody>

                            <tr valign="top" class="<?php if (!$woocs_is_auto_switcher): ?>woocs_settings_hide<?php endif; ?>">
                                <th scope="row" class="titledesc">
                                    <label for="woocs_auto_switcher_basic_field"><?php esc_html_e('Basic field(s)', 'woocommerce-currency-switcher') ?></label>
                                    <span class="woocommerce-help-tip woocs_settings_tip"  data-tip="<?php esc_html_e("What content to show in the switcher after the site page loading. Variants:  __CODE__ __FLAG___ __SIGN__ __DESCR__", 'woocommerce-currency-switcher') ?>"></span>
                                </th>
                                <td class="forminp forminp-select">
                                    <?php
                                    $woocs_auto_switcher_basic_field = get_option('woocs_auto_switcher_basic_field', '__CODE__ __SIGN__');
                                    ?>
                                    <input type="text" name="woocs_auto_switcher_basic_field" id="woocs_auto_switcher_basic_field" class="woocs_settings_dd" value="<?php echo $woocs_auto_switcher_basic_field ?>" >
                                </td>
                            </tr>
                            <tr valign="top" class="<?php if (!$woocs_is_auto_switcher): ?>woocs_settings_hide<?php endif; ?>">
                                <th scope="row" class="titledesc">
                                    <label for="woocs_auto_switcher_additional_field"><?php esc_html_e('Hover field(s)', 'woocommerce-currency-switcher') ?></label>
                                    <span class="woocommerce-help-tip woocs_settings_tip"  data-tip="<?php esc_html_e("What content to show in the switcher after mouse hover on any currency there. Variants:  __CODE__ __FLAG___ __SIGN__ __DESCR__", 'woocommerce-currency-switcher') ?>"></span>
                                </th>
                                <td class="forminp forminp-select">
                                    <?php
                                    $woocs_auto_switcher_additional_field = get_option('woocs_auto_switcher_additional_field', '__DESCR__');
                                    ?>
                                    <input type="text" name="woocs_auto_switcher_additional_field" id="woocs_auto_switcher_additional_field" class="woocs_settings_dd" value="<?php echo $woocs_auto_switcher_additional_field ?>" >
                                </td>
                            </tr>
                            <tr valign="top" class="<?php if (!$woocs_is_auto_switcher): ?>woocs_settings_hide<?php endif; ?>">
                                <th scope="row" class="titledesc">
                                    <label for="woocs_auto_switcher_show_page"><?php esc_html_e('Show on the pages', 'woocommerce-currency-switcher') ?></label>
                                    <span class="woocommerce-help-tip woocs_settings_tip"  data-tip="<?php esc_html_e("Where on the site the switcher should be visible. If any value is presented here switcher will be hidden on all another pages which not presented in this field. You can use pages IDs using comma, example: 28,34,232. Also you can use special words as: product, shop, checkout, front_page, woocommerce", 'woocommerce-currency-switcher') ?>"></span>
                                </th>
                                <td class="forminp forminp-select">
                                    <?php
                                    $woocs_auto_switcher_show_page = get_option('woocs_auto_switcher_show_page', '');
                                    ?>
                                    <input type="text" name="woocs_auto_switcher_show_page" id="woocs_auto_switcher_show_page" class="woocs_settings_dd" value="<?php echo $woocs_auto_switcher_show_page ?>" >
                                </td>
                            </tr>
                            <tr valign="top" class="<?php if (!$woocs_is_auto_switcher): ?>woocs_settings_hide<?php endif; ?>">
                                <th scope="row" class="titledesc">
                                    <label for="woocs_auto_switcher_hide_page"><?php esc_html_e('Hide on the pages', 'woocommerce-currency-switcher') ?></label>
                                    <span class="woocommerce-help-tip woocs_settings_tip"  data-tip="<?php esc_html_e("Where on the site the switcher should be hidden. If any value is presented here switcher will be hidden on that pages and visible on all another ones. You can use pages IDs using comma, example: 28,34,232. Also you can use special words as: product, shop, checkout, front_page, woocommerce", 'woocommerce-currency-switcher') ?>"></span>
                                </th>
                                <td class="forminp forminp-select">
                                    <?php
                                    $woocs_auto_switcher_hide_page = get_option('woocs_auto_switcher_hide_page', '');
                                    ?>
                                    <input type="text" name="woocs_auto_switcher_hide_page" id="woocs_auto_switcher_hide_page" class="woocs_settings_dd" value="<?php echo $woocs_auto_switcher_hide_page ?>" >
                                </td>
                            </tr>
                            <tr valign="top" class="<?php if (!$woocs_is_auto_switcher): ?>woocs_settings_hide<?php endif; ?>">
                                <th scope="row" class="titledesc">
                                    <label for="woocs_auto_switcher_mobile_show"><?php esc_html_e('Behavior for devices', 'woocommerce-currency-switcher') ?></label>
                                    <span class="woocommerce-help-tip woocs_settings_tip" data-tip="<?php esc_html_e("Show/Hide on mobile device (highest priority)", 'woocommerce-currency-switcher') ?>"></span>
                                </th>
                                <td class="forminp forminp-select">
                                    <?php
                                    $mobile = array(
                                        0 => esc_html__('Show on all devices', 'woocommerce-currency-switcher'),
                                        '1' => esc_html__('Show on mobile devices only', 'woocommerce-currency-switcher'),
                                        '2' => esc_html__('Hide on mobile devices', 'woocommerce-currency-switcher'),
                                    );
                                    $woocs_auto_switcher_mobile_show = get_option('woocs_auto_switcher_mobile_show', 'left');
                                    ?>
                                    <select name="woocs_auto_switcher_mobile_show" id="woocs_auto_switcher_mobile_show" class="chosen_select enhanced woocs_settings_dd" tabindex="-1" title="<?php esc_html_e('Choice behaviour', 'woocommerce-currency-switcher') ?>">

                                        <?php foreach ($mobile as $val => $title): ?>
                                            <option value="<?php echo $val ?>" <?php echo selected($woocs_auto_switcher_mobile_show, $val) ?>><?php echo $title ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <?php if ($woocs_is_payments_rule_enable): ?>
                    <section id="tabs-7" class="woocs_settings_section" >

                        <h3><?php esc_html_e('Payments rules', 'woocommerce-currency-switcher') ?></h3>

                        <table class="form-table">
                            <tbody>
                                <tr valign="top">
                                    <th scope="row" class="titledesc">
                                        <label for="woocs_payment_control"><?php esc_html_e('Payments behavior', 'woocommerce-currency-switcher') ?></label>
                                        <span class="woocommerce-help-tip" data-tip="<?php esc_html_e('Should be payments system be hidden for selected currencies or vice versa shown!', 'woocommerce-currency-switcher') ?>"></span>
                                    </th>
                                    <td class="forminp forminp-select">
                                        <?php
                                        $opts = array(
                                            0 => esc_html__('Is hidden', 'woocommerce-currency-switcher'),
                                            1 => esc_html__('Is shown', 'woocommerce-currency-switcher')
                                        );
                                        $woocs_payment_control = get_option('woocs_payment_control', 0);
                                        ?>
                                        <select name="woocs_payment_control" id="woocs_payment_control" class="chosen_select enhanced woocs_settings_dd" tabindex="-1" title="<?php esc_html_e('Behavior', 'woocommerce-currency-switcher') ?>">

                                            <?php foreach ($opts as $val => $title): ?>
                                                <option value="<?php echo $val ?>" <?php echo selected($woocs_payment_control, $val) ?>><?php echo $title ?></option>
                                            <?php endforeach; ?>

                                        </select>
                                    </td>
                                </tr>

                                <?php
                                $payments = WC()->payment_gateways->payment_gateways();
                                $woocs_payments_rules = get_option('woocs_payments_rules', array());

                                foreach ($payments as $key => $payment) {
                                    if ($payment->enabled == "yes"):
                                        ?>
                                        <tr valign="top">
                                            <th scope="row" class="titledesc">
                                                <label for="woocs_payment_rule"><?php echo $payment->title ?></label>
                                            </th>
                                            <td class="forminp forminp-select">
                                                <select name="woocs_payments_rules[<?php echo $key ?>][]" multiple=""  class="chosen_select woocs_settings_dd"  title="<?php esc_html_e('Choice currencies', 'woocommerce-currency-switcher') ?>">
                                                    <?php
                                                    $payment_rules = array();
                                                    if (isset($woocs_payments_rules[$key])) {
                                                        $payment_rules = $woocs_payments_rules[$key];
                                                    }
                                                    if (!empty($currencies) AND is_array($currencies)) {
                                                        foreach ($currencies as $key_curr => $currency) {
                                                            ?>
                                                            <option value="<?php echo $key_curr ?>" <?php echo(in_array($key_curr, $payment_rules) ? 'selected=""' : '') ?>><?php echo $key_curr ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select><br />

                                                <div class="woocs-flr-clr">
                                                    <a href="#" class="woocs-select-all-in-select"><?php echo esc_html__('select all', 'woocommerce-currency-switcher') ?></a>&nbsp;|&nbsp;<a href="#" class="woocs-clear-all-in-select"><?php echo esc_html__('clear all', 'woocommerce-currency-switcher') ?></a><br />
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    endif;
                                }
                                ?>
                            </tbody>
                        </table>
                    </section>
                <?php endif; ?>


                <?php if ($this->is_use_geo_rules()): ?>
                    <section id="tabs-4">


                        <h3><?php esc_html_e('GeoIP rules', 'woocommerce-currency-switcher') ?></h3>


                        <?php if (version_compare(WOOCOMMERCE_VERSION, '2.3', '<')): ?>

                            <b class="woocs_hint"><?php esc_html_e("GeoIP works from v.2.3 of the WooCommerce plugin and no with minor versions of WooCommerce!!", 'woocommerce-currency-switcher'); ?></b><br />

                        <?php endif; ?>

                        <?php if (empty($pd)): ?>

                            <b class="woocs_hint"><?php esc_html_e("WooCommerce GeoIP functionality doesn't work on your site", 'woocommerce-currency-switcher'); ?>:&nbsp;<a href='https://wordpress.org/support/topic/geolocation-not-working-1/?replies=10' target='_blank'><?php esc_html_e("read this please", 'woocommerce-currency-switcher'); ?></a></b><br />

                        <?php endif; ?>
                        <ul>
                            <?php
                            if (!empty($currencies) AND is_array($currencies)) {
                                foreach ($currencies as $key => $currency) {
                                    $rules = array();
                                    if (isset($geo_rules[$key])) {
                                        $rules = $geo_rules[$key];
                                    }
                                    ?>
                                    <li>
                                        <table class="woocs_settings_geo_table" >
                                            <tr>
                                                <td class="woocs_settings_geo_table_title" >
                                                    <div class="<?php if ($currency['is_etalon']): ?>woocs_hint<?php endif; ?>"><strong class="woocs-options-fix3"><?php echo $key ?>:</strong></div>
                                                </td>
                                                <td class="woocs_settings_geo_table_td">
                                                    <select name="woocs_geo_rules[<?php echo $currency['name'] ?>][]" multiple="" size="1"  class="chosen_select">
                                                        <?php foreach ($countries as $key => $value): ?>
                                                            <option <?php echo(in_array($key, $rules) ? 'selected=""' : '') ?> value="<?php echo $key ?>"><?php echo $value ?></option>
                                                        <?php endforeach; ?>
                                                    </select><br />
                                                    <div class="woocs-flr-clr">
                                                        <a href="#" class="woocs-select-all-in-select"><?php echo esc_html__('select all', 'woocommerce-currency-switcher') ?></a>&nbsp;|&nbsp;<a href="#" class="woocs-clear-all-in-select"><?php echo esc_html__('clear all', 'woocommerce-currency-switcher') ?></a><br />
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </li>
                                    <?php
                                }
                            }
                            ?>
                            <li>
                                <hr/>
                                <h2><?php esc_html_e("GeoIP profiles", 'woocommerce-currency-switcher'); ?>:</h2>

                                <div class="woocs-notice"><?php printf(__("Here you can create set of countries profiles to apply it then in the products by your business logic. %s!", 'woocommerce-currency-switcher'), "<a href='" . WOOCS_LINK . "img/woocs-options-1.png' target='_blank'>" . esc_html__('This make work on rules settings for each product more fast', 'woocommerce-currency-switcher') . "</a>"); ?></div>

                                <input type="hidden" name="woocs_geo_rules_profile_key" value="" >
                                <input type="text" name="woocs_geo_rules_profile_title" value="" class="woocs-options-fix4" placeholder="<?php esc_html_e("Set title", 'woocommerce-currency-switcher'); ?>" /><br />
                                <select name="woocs_geo_rules_profile_countries[]" multiple="" size="1" id="woocs_geo_rules_profile_countries" class="chosen_select">
                                    <?php foreach ($countries as $key => $value): ?>
                                        <option  value="<?php echo $key ?>"><?php echo $value ?></option>
                                    <?php endforeach; ?>
                                </select>

                                <div class="woocs-fll">
                                    <a href="javascript:woocs_add_geoip_profile()" class="button woocs_add_geoip_profile"><?php esc_html_e("Add new profile", 'woocommerce-currency-switcher'); ?></a>
                                    <a href="javascript:woocs_update_geoip_profile()" class="button woocs_update_geoip_profile"><?php esc_html_e("Update profile", 'woocommerce-currency-switcher'); ?></a>
                                </div>
                                <div class="woocs-flr">
                                    <a href="javascript:woocs_cancel_geoip_profile()" class="button woocs_update_cancel_geoip_profile"><?php esc_html_e("Cancel", 'woocommerce-currency-switcher'); ?></a>
                                </div>
                                <div class="clearfix clear"></div>



                                <br /><br />
                                <h4 class="woocs_geoip_profile_title"><?php esc_html_e("Profiles", 'woocommerce-currency-switcher'); ?>:</h4>
                                <div class="woocs_geoip_profile_info woocs-notice" style="display: none;"></div>
                                <?php
                                global $WOOCS;
                                $geoIP_profiles = $WOOCS->geoip_profiles->get_data();
                                ?>
                                <select class="chosen_select woocs_geoip_profile_countries">
                                    <?php
                                    foreach ($geoIP_profiles as $key => $item) {
                                        ?>
                                        <option data-key="<?php echo $key ?>" value='<?php echo json_encode($item['data']) ?>'><?php echo $item['name'] ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>

                                <div class="woocs-fll">

                                    <a href="javascript:woocs_edit_geoip_profile()" class="button woocs_edit_geoip_profile"><?php esc_html_e("Edit profile", 'woocommerce-currency-switcher'); ?></a>
                                    <a href="javascript:woocs_delete_geoip_profile()" class="button woocs_delete_geoip_profile"><?php esc_html_e("Delete profile", 'woocommerce-currency-switcher'); ?></a>


                                </div>
                                <div class="woocs-flr">

                                    <div class="woocs_profiles_add_to_rules" >
                                        <a href="javascript:woocs_geoip_profile_to_rules()" class="button woocs_profile_geoip_rules"><?php esc_html_e("Add to:", 'woocommerce-currency-switcher'); ?></a>
                                        <?php
                                        if (!empty($currencies) AND is_array($currencies)) {
                                            ?><select class="chosen_select woocs_profile_geoip_currency">
                                                                                                                                                                                                                <!--<option value="-1" ><?php esc_html_e("Select currency...", 'woocommerce-currency-switcher'); ?></option> -->
                                                <?php
                                                foreach ($currencies as $key => $currency) {
                                                    ?><option value="<?php echo $key ?>" ><?php echo $key ?></option><?php
                                                }
                                                ?></select><?php
                                        }
                                        ?>
                                    </div>

                                </div>
                                <div class="clearfix clear"></div>


                            </li>
                        </ul>
                    </section>
                <?php else: ?>
                    <input type="hidden" name="woocs_geo_rules" value="" />
                <?php endif; ?>



                <?php if ($this->statistic AND $this->statistic->can_collect()): ?>

                    <?php $this->statistic->install_table(); ?>

                    <section id="tabs-stat">

                        <table class="woocs-w-100p">
                            <tr>
                                <td class="forminp forminp-select woocs-stat-forminp">

                                    <select id="woocs-stat-chart-type">
                                        <option value="pie"><?php echo esc_html__('Chart type: pie', 'woocommerce-currency-switcher') ?></option>
                                        <option value="bar"><?php echo esc_html__('Chart type: bar', 'woocommerce-currency-switcher') ?></option>
                                    </select><br />

                                    <div id="woocs-stat-chart"></div>


                                    <div class="woocs-options-fix5">
                                        <p><?php printf(__("If you have ideas about scenarios of the statistic please share and discuss them on %s", 'woocommerce-currency-switcher'), '<a href="https://pluginus.net/support/forum/woocs-woocommerce-currency-switcher-multi-currency-and-multi-pay-for-woocommerce/" target="_blank">' . esc_html__('the plugin forum', 'woocommerce-currency-switcher') . '</a>') ?></p>
                                        <p>
                                            <b class="woocs-red"><?php esc_html_e('Note', 'woocommerce-currency-switcher') ?></b>:&nbsp;<i><?php esc_html_e('If in tab Options activated option I am using cache plugin on my site - to avoid double data for statistical data registration in tab Options activate option No GET data in link!', 'woocommerce-currency-switcher') ?></i>
                                        </p>
                                    </div>

                                </td>
                                <td class="woocs-options-fix6">

                                    <ul class="woocs-ml-15">
                                        <li class="forminp forminp-select">
                                            <select id="woocs-stat-type">
                                                <option value="currency"><?php echo esc_html__('currency', 'woocommerce-currency-switcher') ?></option>
                                                <option value="order"><?php echo esc_html__('orders (completed)', 'woocommerce-currency-switcher') ?></option>
                                            </select>
                                        </li>
                                        <li class="forminp forminp-text">
                                            <?php
                                            $format = 'dd-mm-yy';
                                            $min_date = $this->statistic->get_min_date();
                                            $first_this_m = new DateTime('first day of this month');
                                            ?>

                                            <input type="hidden" id="woocs-stat-calendar-format" value="<?php echo $format ?>" />
                                            <input type="hidden" id="woocs-stat-calendar-min-y" value="<?php echo date('Y', $min_date) ?>" />
                                            <input type="hidden" id="woocs-stat-calendar-min-m" value="<?php echo (intval(date('m', $min_date)) - 1) ?>" />
                                            <input type="hidden" id="woocs-stat-calendar-min-d" value="<?php echo date('d', $min_date) ?>" />

                                            <input type="hidden" id="woocs-stat-from" value="<?php echo mktime(0, 0, 0, $first_this_m->format('m'), $first_this_m->format('d'), $first_this_m->format('Y')) ?>" />
                                            <input type="text" readonly="" value="<?php echo $first_this_m->format('d-m-Y'); ?>" class="woocs_stat_calendar woocs_stat_calendar_from" placeholder="<?php echo esc_html__('from', 'woocommerce-currency-switcher') ?>" />

                                            <div class="woocs-h7"></div>

                                            <input type="hidden" id="woocs-stat-to" value="0" />
                                            <input type="text" readonly="" value="" class="woocs_stat_calendar woocs_stat_calendar_to" placeholder="<?php echo esc_html__('to', 'woocommerce-currency-switcher') ?>" />
                                        </li>
                                        <li><a href="javascript: woocs_stat_redraw(1); void(0);" id="woocs_stat_redraw_1" class="woocs_stat_redraw_btn woocs-panel-button dashicons-before dashicons-update"><?php echo $this->statistic->get_label(1) ?></a>&nbsp;<label class="woocs-options-valign-top"><span class="woocommerce-help-tip" data-tip="<?php echo esc_html__('For currencies - aggregated data about selected currencies on the site front. For orders - count of orders made in the selected currencies.', 'woocommerce-currency-switcher') ?>"></span></label></li>
                                        <li><a href="javascript: woocs_stat_redraw(2); void(0);" id="woocs_stat_redraw_2" class="woocs_stat_redraw_btn woocs-panel-button dashicons-before dashicons-update"><?php echo $this->statistic->get_label(2) ?></a>&nbsp;<label class="woocs-options-valign-top"><span class="woocommerce-help-tip" data-tip="<?php echo esc_html__('For currencies - aggregated data about count of countries which users selected currencies on the site front. For orders - count of orders made from countries, detected by selected country in the billing address.', 'woocommerce-currency-switcher') ?>"></span></label></li>
                                    </ul>
                                </td>
                            </tr>
                        </table>

                        <br />

                    </section>
                <?php endif; ?>


                <section id="tab-smart-designer">

                    <div id="woocs-sd-manage-area">

                        <h3><?php esc_html_e("Smart Designer", 'woocommerce-currency-switcher') ?></h3>

                        <div class="woocs-notice"><?php esc_html_e("In this section you can create your own view of currency drop-down switcher", 'woocommerce-currency-switcher'); ?></div>


                        <a href="#" id="woocs-sd-create" class="woocs-panel-button dashicons-before dashicons-plus"><?php esc_html_e("Create", 'woocommerce-currency-switcher') ?></a><br />
                        <br />
                        <?php
                        global $WOOCS_SD;
                        $designs = array_reverse($WOOCS_SD->get_designs());
                        ?>
                        <div class="woocs-data-table">
                            <table id="woocs-sd-table">

                                <tbody>
                                    <?php if (!empty($designs)): ?>
                                        <?php foreach ($designs as $design_id) : ?>
                                            <tr id="woocs-sd-dashboard-tr-<?php echo $design_id ?>">
                                                <td>
                                                    <?php echo $design_id ?>
                                                </td>

                                                <td>
                                                    <input type="text" value="[woocs sd=<?php echo $design_id ?>]" readonly="" />
                                                </td>
                                                <td>
                                                    <a href="javascript: woocs_sd_edit(<?php echo $design_id ?>);void(0);" class="woocs-panel-button dashicons-before dashicons-update"><?php esc_html_e('edit', 'woocommerce-currency-switcher') ?></a><a href="javascript: woocs_sd_delete(<?php echo $design_id ?>);void(0);" class="woocs-panel-button dashicons-before dashicons-dismiss"><?php esc_html_e('delete', 'woocommerce-currency-switcher') ?></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>

                            </table>

                        </div>

                    </div>

                    <div id="woocs-sd-work-area" style="display: none;"></div>

                    <template id="woocs-sd-work-area-tpl">
                        <div class="woocs-sd-main-wrapper">

                            <?php
                            $menu = [
                                'general' => esc_html__("General", 'woocommerce-currency-switcher'),
                                'title' => esc_html__("Title", 'woocommerce-currency-switcher'),
                                'description' => esc_html__("Description", 'woocommerce-currency-switcher'),
                                'image' => esc_html__("Flag", 'woocommerce-currency-switcher')
                            ];
                            ?>

                            <div id="woocs-sd-dd" class="woocs-sd-panel woocs-sd-panel-current" data-menu='<?php echo json_encode($menu) ?>'>

                                <div id="selectron23-example-container">
                                    <?php
                                    global $WOOCS_SD;
                                    ?>
                                    <div id="selectron23-example" data-woocs-sd-currencies='<?php echo json_encode($WOOCS_SD->get_currencies()) ?>'></div>

                                </div>

                                <div id="woocs-sd-dd-options" class="woocs-sd-panel-options"></div>

                                <div class="woocs-sd-missing-options"><a href="https://pluginus.net/support/forum/woocs-woocommerce-currency-switcher-multi-currency-and-multi-pay-for-woocommerce/" target="_blank"><?php esc_html_e("Missing options? Describe your proposal please on the support forum.", 'woocommerce-currency-switcher') ?></a></div>

                            </div>

                            <div id="woocs-sd-pp" class="woocs-sd-panel">
                                popup
                            </div>

                            <div id="woocs-sd-ss" class="woocs-sd-panel">
                                side switcher
                            </div>

                            <div id="woocs-sd-c" class="woocs-sd-panel">
                                custom
                            </div>

                        </div>

                        <div id="woocs-sd-main-buttons">
                            <div><a href="javascript: woocs_sd_save();void(0);" class="woocs-panel-button dashicons-before dashicons-cloud-saved"><?php esc_html_e("Save changes", 'woocommerce-currency-switcher') ?></a>&nbsp;</div>
                            <div><a href="javascript: woocs_sd_save_exit();void(0);" class="woocs-panel-button dashicons-before dashicons-cloud-saved dashicons-exit"><?php esc_html_e("Save and exit", 'woocommerce-currency-switcher') ?></a>&nbsp;</div>
                            <div><a href="javascript: woocs_sd_exit_no_save();void(0);" class="woocs-panel-button dashicons-before dashicons-exit"><?php esc_html_e("Exit without save", 'woocommerce-currency-switcher') ?></a>&nbsp;</div>
                            <div><a href="javascript: woocs_sd_reset();void(0);" class="woocs-panel-button dashicons-before dashicons-dismiss"><?php esc_html_e("Reset to default", 'woocommerce-currency-switcher') ?></a>&nbsp;</div>
                        </div>
                    </template>


                </section>


                <section id="tabs-5">

                    <a href="https://pluginus.net/support/forum/woocs-woocommerce-currency-switcher-multi-currency-and-multi-pay-for-woocommerce/" target="_blank" class="woocs-panel-button dashicons-before dashicons-hammer"><?php echo esc_html__('WooCommerce Currency Switcher SUPPORT', 'woocommerce-currency-switcher') ?></a>
                    &nbsp;<a href="https://currency-switcher.com/translations/" target="_blank" class="woocs-panel-button dashicons-before dashicons-admin-site-alt3"><span class="dashicons-before dashicons-universal-access user-on-the-planet"></span></a>

                    <br /><br />

                    <ol class="woocs-info">
                        <li><?php echo esc_html__("If you have an idea about css+js template - send request on support please where describe your vision.", 'woocommerce-currency-switcher') ?></li>

                        <li><a href="https://currency-switcher.com/get-flags-free/" target="_blank"><?php esc_html_e("Free flags images", 'woocommerce-currency-switcher') ?></a></li>

                        <li><a href="https://currency-switcher.com/category/faq/" target="_blank"><?php esc_html_e("FAQ", 'woocommerce-currency-switcher') ?></a></li>

                        <li><a href="https://currency-switcher.com/codex/" target="_blank"><?php esc_html_e("Codex", 'woocommerce-currency-switcher') ?></a></li>

                        <li><a href="https://currency-switcher.com/video-tutorials/" target="_blank"><?php esc_html_e("Video tutorials", 'woocommerce-currency-switcher') ?></a></li>

                        <li><a href="https://en.wikipedia.org/wiki/ISO_4217#Active_codes" target="_blank"><?php esc_html_e("Read wiki about Currency Active codes", 'woocommerce-currency-switcher') ?></a></li>

                        <li><?php esc_html_e("If WOOCS settings panel looks incorrect or you have JavaScript errors (after update) - firstly", 'woocommerce-currency-switcher') ?> <a href="https://pluginus.net/how-to-reset-page-cache-in-the-browser/" target="_blank" class="woocs-red"><?php esc_html_e("reset the browser cache", 'woocommerce-currency-switcher') ?></a></li>
                    </ol>

                    <hr />

                    <?php
                    $rate_url = 'https://codecanyon.net/downloads#item-8085217';
                    if ($WOOCS->notes_for_free) {
                        $rate_url = 'https://wordpress.org/support/plugin/woocommerce-currency-switcher/reviews/#new-post';
                    }
                    ?>

                    <b><?php printf(__("We work hard to make this plugin more effective tool for your e-shops, and ready to %s hear your review, suggestions and opinions %s, please share it with us!", 'woocommerce-currency-switcher'), '<a href="' . $rate_url . '" target="_blank" class="woocs-orange">', '</a>') ?></b>

                    <hr />
                    <ol class="woocs-info">
                        <li><a href="https://currency-switcher.com/i-cant-add-flags-what-to-do/" target="_blank"><?php esc_html_e("I cant add flags! What to do?", 'woocommerce-currency-switcher') ?></a></li>
                        <li><a href="https://currency-switcher.com/using-geolocation-causes-problems-doesnt-seem-to-work-for-me/" target="_blank"><?php esc_html_e("Using Geolocation causes problems, doesn’t seem to work for me", 'woocommerce-currency-switcher') ?></a></li>
                    </ol>

                    <hr />

                    <h3><?php esc_html_e("Quick introduction", 'woocommerce-currency-switcher') ?></h3>

                    <iframe width="560" height="315" src="https://www.youtube.com/embed/wUoM9EHjnYs" frameborder="0" allowfullscreen></iframe>

                    <hr />

                    <h3><?php esc_html_e("More power for your shop", 'woocommerce-currency-switcher') ?></h3>


                    <a href="https://pluginus.net/affiliate/woocommerce-products-filter" target="_blank"><img width="300" src="<?php echo WOOCS_LINK ?>img/woof_banner.png" /></a>&nbsp;
                    <a href="https://pluginus.net/affiliate/woocommerce-bulk-editor" target="_blank"><img width="300" src="<?php echo WOOCS_LINK ?>img/woobe_banner.png" /></a>
                    <a href="https://pluginus.net/affiliate/woot-woocommerce-products-tables" target="_blank"><img width="300" src="<?php echo WOOCS_LINK ?>img/woot_banner.png" /></a>



                    <?php if (!$WOOCS->notes_for_free): ?>
                        <hr />
                        <div id="plugin_warning" style="display: none;">
                            <div class="plugin_warning_head"><strong class="woocs_settings_red" >IF YOU BOUGHT WOOCS ON CODECANYON - DO NOT READ AND IGNORE NEXT MESSAGE!<BR />ATTENTION MESSAGE FROM THE PLUGIN AUTHOR TO ALL USERS WHO USES PIRATE VERSION OF THE PLUGIN!</strong>!<br></div>
                            <br />
                            GET YOUR COPY OF THE PLUGIN <em> <span class="woocs_settings_underline"><span class="woocs_settings_ff"><strong>ONLY</strong></span></span></em> FROM <a href="https://pluginus.net/affiliate/woocommerce-currency-switcher" target="_blank"><span class="woocs_settings_green"><strong>CODECANYON.NET</strong></span></a> OR <span class="woocs_settings_green"><strong><a href="https://wordpress.org/plugins/woocommerce-currency-switcher/" target="_blank">WORDPRESS.ORG</a></strong></span> IF YOU DO NOT WANT TO BE AN AFFILIATE OF ANY VIRUS SITE.<br>
                            <br>
                            <strong>DID YOU CATCH A VIRUS DOWNLOADING THE PLUGIN FROM ANOTHER (PIRATE) SITES<span class="woocs_settings_ff">?</span></strong> THIS IS YOUR TROUBLES AND <em>DO NOT WRITE TO SUPPORT THAT GOOGLE DOWN YOUR SITE TO ZERO BECAUSE OF &nbsp;ANY VIRUS</em>!!<br>
                            <br>
                            <strong><span  class="woocs_settings_ff" >REMEMBER</span></strong> - if somebody suggesting YOU premium version of the plugin for free - think twenty times before installing it ON YOUR SITE, as it can be trap for it! <strong>DOWNLOAD THE PLUGIN ONLY FROM OFFICIAL SITES TO AVOID THE COLLAPSE OF YOUR BUSINESS</strong>.<br>
                            <br>
                            <strong class="woocs_settings_ff">Miser pays twice</strong>!<br>
                            <br>
                            P.S. Reason of this warning text - emails from the users! Be care!!
                        </div>

                    <?php endif; ?>


                </section>



            </div>
        </div>



        <div class="woocs_settings_powered">
            <a href="https://pluginus.net/" target="_blank" >Powered by PluginUs.NET</a>
        </div>


    </div>
    <br />


    <b class="woocs_hint" ><?php esc_html_e('Hint', 'woocommerce-currency-switcher'); ?>:</b>&nbsp;<?php esc_html_e('If you want let your customers pay in their selected currency in tab Advanced set the option Is multiple allowed to Yes.', 'woocommerce-currency-switcher'); ?><br />
    <b class="woocs_hint" ><?php esc_html_e('Note', 'woocommerce-currency-switcher'); ?>:</b>&nbsp;<?php esc_html_e('Get free API keys:', 'woocommerce-currency-switcher'); ?>
    &nbsp;<a href="https://free.currencyconverterapi.com/free-api-key"  target="_blank"><?php esc_html_e('The Free Currency Converter', 'woocommerce-currency-switcher'); ?></a>,
    &nbsp;<a href="https://fixer.io/signup/free" target="_blank"><?php esc_html_e('Fixer', 'woocommerce-currency-switcher'); ?></a>,
    &nbsp;<a href="https://openexchangerates.org/signup"  target="_blank"><?php esc_html_e('Open exchange rates', 'woocommerce-currency-switcher'); ?></a>,
    &nbsp;<a href="https://currencylayer.com/product"  target="_blank"><?php esc_html_e('Currencylayer', 'woocommerce-currency-switcher'); ?></a>
    <br />

    <b class="woocs_hint" ><?php esc_html_e('Note', 'woocommerce-currency-switcher'); ?>:</b>&nbsp;<?php esc_html_e("If WOOCS settings panel looks incorrect or you have JavaScript errors (after update) - firstly", 'woocommerce-currency-switcher') ?> <a href="https://pluginus.net/how-to-reset-page-cache-in-the-browser/" target="_blank" class="woocs-red"><?php esc_html_e("reset the browser cache", 'woocommerce-currency-switcher') ?></a><br />

    <?php if ($WOOCS->notes_for_free): ?>
        <hr />

        <div class="woocs-notice"><i><?php printf(__('In the free version of the plugin %s you can operate with 2 ANY currencies only%s! If you want to use more currencies %s you can make upgrade to the premium version%s of the plugin', 'woocommerce-currency-switcher'), '<b class="woocs_settings_red">', '</b>', '<a href="https://pluginus.net/affiliate/woocommerce-currency-switcher" target="_blank">', '</a>') ?></i></div><br />

        <table class="woocs_settings_promotion" >
            <tr>
                <td >
                    <h3 class="woocs_settings_red"><?php esc_html_e("UPGRADE to Full version", 'woocommerce-currency-switcher') ?>:</h3>
                    <a href="https://pluginus.net/affiliate/woocommerce-currency-switcher" target="_blank"><img width="240" src="<?php echo WOOCS_LINK ?>img/woocs_banner.png" alt="<?php esc_html_e("full version of the plugin", 'woocommerce-currency-switcher'); ?>" /></a>
                </td>

                <td >
                    <h3><?php esc_html_e("WOOF - WooCommerce Products Filter", 'woocommerce-currency-switcher') ?>:</h3>
                    <a href="https://pluginus.net/affiliate/woocommerce-products-filter" target="_blank"><img width="240" src="<?php echo WOOCS_LINK ?>img/woof_banner.png" alt="<?php esc_html_e("WOOF - WooCommerce Products Filter", 'woocommerce-currency-switcher'); ?>" /></a>
                </td>

                <td >
                    <h3><?php esc_html_e("WOOBE - WooCommerce Bulk Editor Professional", 'woocommerce-currency-switcher') ?>:</h3>
                    <a href="https://pluginus.net/affiliate/woocommerce-bulk-editor" target="_blank"><img src="<?php echo WOOCS_LINK ?>img/woobe_banner.png" width="240" alt="<?php esc_html_e("WOOBE - WooCommerce Bulk Editor Professional", 'woocommerce-currency-switcher'); ?>" /></a>
                </td>

                <td >
                    <h3><?php esc_html_e("WOOT - WooCommerce Active Products Tables", 'woocommerce-currency-switcher') ?>:</h3>
                    <a href="https://pluginus.net/affiliate/woot-woocommerce-products-tables" target="_blank"><img src="<?php echo WOOCS_LINK ?>img/woot_banner.png" width="240" alt="<?php esc_html_e("WOOT - WooCommerce Active Products Tables", 'woocommerce-currency-switcher'); ?>" /></a>
                </td>

            </tr>
        </table>
    <?php endif; ?>


    <div class="info_popup woocs_settings_hide" ></div>

</div>


<?php

function woocs_print_currency($_this, $currency) {
    global $WOOCS;
    ?>
    <tr>
        <td><label class="container">
                <input class="help_tip woocs_is_etalon" data-tip="<?php esc_html_e("Set etalon main currency. This should be the currency in which the price of goods exhibited!", 'woocommerce-currency-switcher') ?>" type="radio" <?php checked(1, $currency['is_etalon']) ?> />
                <input type="hidden" name="woocs_is_etalon[]" value="<?php echo $currency['is_etalon'] ?>" />
                <span class="checkmark"></span>
            </label></td>

        <td><input type="text" value="<?php echo $currency['name'] ?>" name="woocs_name[]" class="woocs-mw50 woocs-text woocs-currency" placeholder="<?php esc_html_e("Exmpl.: USD,EUR", 'woocommerce-currency-switcher') ?>" /></td>


        <td>
            <select class="woocs-drop-down woocs-symbol woocs-mw60" name="woocs_symbol[]" title="<?php esc_html_e("Money signs", 'woocommerce-currency-switcher') ?>">
                <?php foreach ($_this->currency_symbols as $symbol) : ?>
                    <option value="<?php echo md5($symbol) ?>" <?php selected(md5($currency['symbol']), md5($symbol)) ?>><?php echo $symbol; ?></option>
                <?php endforeach; ?>
            </select>

        </td>

        <td>
            <select class="woocs-drop-down woocs-position woocs-mw130" name="woocs_position[]" title="<?php echo esc_html__('Select symbol position', 'woocommerce-currency-switcher') ?>">
                <?php
                foreach ($_this->currency_positions as $position) :

                    switch ($position) {
                        case 'right':
                            $position_desc_sign = esc_html__('P$ - right', 'woocommerce-currency-switcher');
                            break;

                        case 'right_space':
                            $position_desc_sign = esc_html__('P $ - right space', 'woocommerce-currency-switcher');
                            break;

                        case 'left_space':
                            $position_desc_sign = esc_html__('$ P - left space', 'woocommerce-currency-switcher');
                            break;

                        default:
                            $position_desc_sign = esc_html__('$P - left', 'woocommerce-currency-switcher');
                            break;
                    }
                    ?>
                    <option value="<?php echo $position ?>" <?php selected($currency['position'], $position) ?>><?php echo $position_desc_sign ?></option>
                <?php endforeach; ?>
            </select>
        </td>

        <td>
            <select name="woocs_decimals[]" class="woocs-drop-down woocs-decimals" title="<?php echo esc_html__('Decimals', 'woocommerce-currency-switcher') ?>">
                <?php
                $woocs_decimals = range(0, 8);
                if (!isset($currency['decimals'])) {
                    $currency['decimals'] = 2;
                }
                ?>
                <?php foreach ($woocs_decimals as $v => $n): ?>
                    <option <?php if ($currency['decimals'] == $v): ?>selected=""<?php endif; ?> value="<?php echo $v ?>"><?php echo $n ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        
        <td data-title="<?php esc_html_e("Separators", 'woocommerce-currency-switcher') ?>">

            <select name="woocs_separators[]" class="woocs-drop-down" title="<?php echo esc_html__('Separators', 'woocommerce-currency-switcher') ?>">
                <?php
                $separators = [
                    0 => '10,000.00', //default
                    1 => '10.000,00',
                    2 => '10 000.00',
                    3 => '10 000,00',
                    4 => '10000.00',
                    5 => '10000,00',
                ];
                foreach ($separators as $k => $v):
                    if (!isset($currency['separators'])) {
                        if (get_option('woocommerce_price_thousand_sep', '.') === '.') {
                            $currency['separators'] = 1;
                        } else {
                            $currency['separators'] = 0;
                        }
                    }
                    ?>
                    <option <?php if (isset($currency['separators']) AND $currency['separators'] == $k): ?>selected=""<?php endif; ?> value="<?php echo $k ?>"><?php echo $v ?></option>
                <?php endforeach; ?>
            </select>

        </td>

        <td class="woocs-mw150">
            <input type="text" value="<?php echo $currency['rate'] ?>" name="woocs_rate[]" class="woocs-text woocs-rate" placeholder="<?php esc_html_e("exchange rate", 'woocommerce-currency-switcher') ?>" /><span class="woocs-bold-orangered">&nbsp;+&nbsp;</span><input type="text" value="<?php echo (isset($currency['rate_plus']) ? ($currency['rate_plus'] > 0 ? $currency['rate_plus'] : '') : '') ?>" name="woocs_rate_plus[]" class="woocs-text woocs-rate-plus" placeholder="<?php esc_html_e('interest', 'woocommerce-currency-switcher') ?>" title="<?php esc_html_e("+ to your interest in the rate, for example: 0.15", 'woocommerce-currency-switcher') ?>" />
            <button class="button woocs_finance_btn woocs_get_fresh_rate help_tip" data-tip="<?php esc_html_e("Press this button if you want get currency rate from the selected aggregator above!", 'woocommerce-currency-switcher') ?>"><span class="woocs-btn-update-rate dashicons-before dashicons-update"></span></button>
        </td>


        <td>
            <select name="woocs_hide_cents[]" class="woocs-mw105 woocs-drop-down" <?php if (in_array($currency['name'], $WOOCS->no_cents)): ?>disabled=""<?php endif; ?>>
                <?php
                $woocs_hide_cents = array(
                    0 => esc_html__("Show cents", 'woocommerce-currency-switcher'),
                    1 => esc_html__("Hide cents", 'woocommerce-currency-switcher')
                );
                if (in_array($currency['name'], $WOOCS->no_cents)) {
                    $currency['hide_cents'] = 1;
                }
                $hide_cents = 0;
                if (isset($currency['hide_cents'])) {
                    $hide_cents = $currency['hide_cents'];
                }
                ?>
                <?php foreach ($woocs_hide_cents as $v => $n): ?>
                    <option <?php if ($hide_cents == $v): ?>selected=""<?php endif; ?> value="<?php echo $v ?>"><?php echo $n ?></option>
                <?php endforeach; ?>
            </select>

        </td>

        <td>

            <?php
            $woocs_hide_on_front = array(
                0 => esc_html__("Public", 'woocommerce-currency-switcher'),
                1 => esc_html__("Private", 'woocommerce-currency-switcher')
            );

            $hide_on_front = 0;
            if (isset($currency['hide_on_front'])) {
                $hide_on_front = $currency['hide_on_front'];
            }
            ?>
            <select name="woocs_hide_on_front[]" class="woocs-mw75 <?php if ($hide_on_front): ?>woocs-red<?php endif; ?> woocs-drop-down woocs_hide_on_front_select" title="<?php echo esc_html__('Show currency for all on the site front or Hide for private moments in wp admin panel using only.', 'woocommerce-currency-switcher') ?>">

                <?php foreach ($woocs_hide_on_front as $v => $n): ?>
                    <option <?php if ($hide_on_front == $v): ?>selected=""<?php endif; ?> value="<?php echo $v ?>"><?php echo $n ?></option>
                <?php endforeach; ?>
            </select>
        </td>

        <td>
            <input type="text" value="<?php echo $currency['description'] ?>" name="woocs_description[]"  class="woocs-text woocs_curr_description" placeholder="<?php esc_html_e("description", 'woocommerce-currency-switcher') ?>" />
        </td>

        <td>

            <?php
            $flag = WOOCS_LINK . 'img/no_flag.png';
            if (isset($currency['flag']) AND!empty($currency['flag'])) {
                $flag = $currency['flag'];
            }
            ?>
            <input type="hidden" value="<?php echo $flag ?>" class="woocs_flag_input" name="woocs_flag[]" />
            <a href="#" class="woocs_flag help_tip woocs_settings_flag_tip" data-tip="<?php esc_html_e("Click to select the flag", 'woocommerce-currency-switcher'); ?>" ><img src="<?php echo $flag ?>"  alt="<?php esc_html_e("Flag", 'woocommerce-currency-switcher'); ?>" /></a>
        </td>
        <td class="woocs-padd-0">
            <a href="#" class="woocs_del_currency help_tip" data-tip="<?php esc_html_e("remove", 'woocommerce-currency-switcher'); ?>" ><span class="woocs-btn-delete dashicons-before dashicons-dismiss"></span></a><br />
            <div class="woocs-h11"></div>
            <a href="#" class="help_tip woocs_settings_move" data-tip="<?php esc_html_e("drag and drop", 'woocommerce-currency-switcher'); ?>"><img  src="<?php echo WOOCS_LINK ?>img/move.png" alt="<?php esc_html_e("move", 'woocommerce-currency-switcher'); ?>" /></a>
        </td>
    </tr>

    <?php
}
