<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');

require_once 'plugin_options_data.php';
global $WOOCS;
?>

<div class="woocs-admin-preloader">
    <div class="cssload-loader">
        <div class="cssload-inner cssload-one"></div>
        <div class="cssload-inner cssload-two"></div>
        <div class="cssload-inner cssload-three"></div>
    </div>
</div>


<div class="subsubsub_section woocs_subsubsub_section">

    <div class="section">

        <div class="woocs__section-title woocs-mb-4">
            <?php echo draw_switcher23('woocs_admin_theme_id', get_option('woocs_admin_theme_id', 0), 'woocs_admin_theme_id', esc_html__('Obsolete WOOCS admin panel', 'woocommerce-currency-switcher')); ?>
            <h3 class="woocs_settings_version">WOOCS - <?php printf(esc_html__('WooCommerce Currency Switcher %s', 'woocommerce-currency-switcher'), '<span class="woocs__text-success">v.' . WOOCS_VERSION . '</span>') ?></h3>
            <i><?php printf(esc_html__('Actualized for WooCommerce v.%s.x', 'woocommerce-currency-switcher'), $this->actualized_for) ?></i>
        </div>

        <div id="tabs" class="woocs__tabs">

            <?php if (version_compare(WOOCOMMERCE_VERSION, WOOCS_MIN_WOOCOMMERCE, '<')): ?>
                <b class="woocs_settings_version" ><?php printf(esc_html__("Your version of WooCommerce plugin is too obsolete. Update minimum to %s version to avoid malfunctionality!", 'woocommerce-currency-switcher'), WOOCS_MIN_WOOCOMMERCE) ?></b>
            <?php endif; ?>

            <input type="hidden" name="woocs_woo_version" value="<?php echo WOOCOMMERCE_VERSION ?>" />

            <nav class="woocs__tabs-nav">
                <ul id="woocs-pills-tab">
                    <li>
                        <a href="#woocs-tabs-1" class="woocs-active">
                            <i class="uil uil-dollar-alt"></i>
                            <?php esc_html_e("Currencies", 'woocommerce-currency-switcher') ?>
                        </a>
                    </li>
                    <li>
                        <a href="#woocs-tabs-2">
                            <i class="uil uil-setting"></i>
                            <?php esc_html_e("Options", 'woocommerce-currency-switcher') ?>
                        </a>
                    </li>
                    <li>
                        <a href="#woocs-tabs-3">
                            <i class="uil uil-external-link-alt"></i>
                            <?php esc_html_e("Advanced", 'woocommerce-currency-switcher') ?>
                        </a>
                    </li>



                    <?php if ($woocs_is_payments_rule_enable): ?>
                        <li>
                            <a href="#woocs-tabs-5">
                                <i class="uil uil-master-card"></i>
                                <?php esc_html_e("Payments", 'woocommerce-currency-switcher') ?>
                            </a>
                        </li>
                    <?php endif; ?>



                    <?php if ($this->is_use_geo_rules()): ?>
                        <li>
                            <a href="#woocs-tabs-6">
                                <i class="uil uil-adjust"></i>
                                <?php esc_html_e("GeoIP", 'woocommerce-currency-switcher') ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($this->statistic AND $this->statistic->can_collect()): ?>
                        <li>
                            <a href="#woocs-tabs-7" onclick="return woocs_stat_activate_graph();">
                                <i class="uil uil-graph-bar"></i>
                                <?php esc_html_e("Statistic", 'woocommerce-currency-switcher') ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <li>
                        <a href="#tab-smart-designer">
                            <i class="uil uil-cube"></i>
                            <?php esc_html_e("Designer", 'woocommerce-currency-switcher') ?>
                        </a>
                    </li>


                    <li>
                        <a href="#woocs-tabs-4">
                            <i class="uil uil-toggle-off"></i>
                            <?php esc_html_e("Side", 'woocommerce-currency-switcher') ?>
                        </a>
                    </li>

                    <li>
                        <a href="#woocs-tabs-8">
                            <i class="uil uil-question-circle"></i>
                            <?php esc_html_e("Help", 'woocommerce-currency-switcher') ?>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="woocs__tab-content" id="woocs-pills-tab-content">


                <div id="woocs-tabs-1" class="woocs__tab-pane woocs-show woocs-active">
                    <div class="woocs__tab-content">

                        <div class="woocs__tools-panel">
                            <div class="woocs__options">
                                <a href="javascript: void(0)" id="woocs_add_currency" data-action="prepend" class="woocs__button"><i class="woocs-uil uil-plus uil"></i><?php esc_html_e("Prepend Currency", 'woocommerce-currency-switcher') ?></a>
                                <a href="javascript: woocs_update_all_rates(); void(0);" class="woocs__button"><i class="woocs-uil uil-refresh uil"></i><?php esc_html_e("Update all rates", 'woocommerce-currency-switcher') ?></a>
                                <a href="javascript: woocs_add_money_sign2(); void(0);" class="woocs__button"><i class="woocs-uil uil-plus uil"></i><?php esc_html_e("Add custom currency symbols", 'woocommerce-currency-switcher') ?></a>
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

                                <label for="woocs_drop_down_view" class="woocs-options-valign-top"><?php woocs_draw_tooltip(esc_html__('How to display currency switcher (by default) on the site front. (NEW) Make your attention on skins with numbers - you can use them on the same page with different designs in shortcode [woocs] described in its attribute style and style number (see Codex page in Info Help tab)!', 'woocommerce-currency-switcher')) ?></label>

                                <select name="woocs_drop_down_view" id="woocs_drop_down_view" class="chosen_select woocs-options-fix1">
                                    <?php foreach ($opts as $key => $value) : ?>
                                        <option value="<?= $key ?>" <?php selected($key === $selected) ?>><?= $value ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
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

                        <div class="scrollbar-external_wrapper">
                            <div class="scrollbar-external">
                                <div id="woocs-currencies-options">
                                    <table class="woocs__table woocs__data-table">
                                        <thead>
                                            <tr>
                                                <th><?php echo esc_html__('Basic', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Move', 'woocommerce-currency-switcher') ?></th>

                                                <th><?php echo esc_html__('Flag', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Currency', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Symbol', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Position', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Rate+%', 'woocommerce-currency-switcher') ?></th>
                                                <th class="woocs_align_left"><?php echo esc_html__('Decimal', 'woocommerce-currency-switcher') ?></th>

                                                <th class="woocs_align_left"><?php echo esc_html__('Separators', 'woocommerce-currency-switcher') ?></th>

                                                <th><?php echo esc_html__('Visible', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Cents', 'woocommerce-currency-switcher') ?></th>
                                                <th><?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?></th>
                                                <th>X</th>
                                            </tr>
                                        </thead>
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

                        <div class="woocs_settings_codes woocs__options">
                            <a href="javascript: void(0)" id="woocs_add_currency2" data-action="append" class="woocs__button"><i class="woocs-uil uil-plus uil"></i><?php esc_html_e("Append Currency", 'woocommerce-currency-switcher') ?></a>
                            &nbsp;<a href="http://en.wikipedia.org/wiki/ISO_4217#Active_codes" target="_blank" class="woocs__button">
                                <?php esc_html_e("Read wiki about Currency Active codes  <-  Get right currencies codes here if you are not sure about it!", 'woocommerce-currency-switcher') ?>
                            </a>
                        </div>

                    </div>

                </div>
                <div id="woocs-tabs-2" class="woocs__tab-pane">
                    <div class="woocs__tab-content">

                        <table class="woocs__table">
                            <thead>
                                <tr>
                                    <th scope="col" class="woocs__table-option">Option</th>
                                    <th scope="col" class="woocs__table-status">Status</th>
                                    <th scope="col" class="woocs__table-description">Description</th>
                                </tr>
                            </thead>
                            <tbody>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php echo esc_html__('Welcome currency', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php echo draw_select($welcome_curr_options, get_option('woocs_welcome_currency', ''), 'woocs_welcome_currency') ?>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__('In which currency to show prices for first visit of your customer on your site. Do not do it by private currency to avoid logic mess!', 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php echo esc_html__('Currency aggregator', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php echo draw_select($aggregators, get_option('woocs_currencies_aggregator', ''), 'woocs_currencies_aggregator') ?>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__('Currency aggregators. Note: If you know aggregator which not is represented in WOOCS write request on support please with suggestion to add it!', 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php echo esc_html__('Aggregator API key', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <input type="text" name="woocs_aggregator_key" value="<?php echo get_option('woocs_aggregator_key', '') ?>">
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__('Some aggregators are required an API key.', 'woocommerce-currency-switcher') ?>. <?php esc_html_e('Get free API keys:', 'woocommerce-currency-switcher'); ?> <a href="https://free.currencyconverterapi.com/free-api-key"  target="_blank"><?php esc_html_e('The Free Currency Converter', 'woocommerce-currency-switcher'); ?></a>,
                                                <a href="https://fixer.io/signup/free" target="_blank">Fixer</a>,
                                                <a href="https://openexchangerates.org/signup"  target="_blank">Open exchange rates</a>,
                                                <a href="https://currencylayer.com/product"  target="_blank">Currencylayer</a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php echo esc_html__('Currency storage', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php echo draw_select($storage_options, get_option('woocs_storage', ''), 'woocs_storage', 'woocs_storage') ?>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__('In some servers there are troubles with sessions, and after currency selecting it reset to welcome currency or geo ip currency. In such cases use transient storage. If it is possible on your hosting use Memcached or Redis!', 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php echo esc_html__('Storage server', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <input type="text" name="woocs_storage_server" id="woocs_storage_server" value="<?php echo get_option('woocs_storage_server', '') ?>">
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__('Server or socket for: memcached or redis. Usually is localhost. Read your hosting documentation about what host to use for memcached or redis.', 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php echo esc_html__('Storage port', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <input type="text" name="woocs_storage_port" id="woocs_storage_port" value="<?php echo get_option('woocs_storage_port', '') ?>">
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__('Port for: memcached or redis. Usually for memcached port is 11211, for redis port is 6379. Read your hosting documentation about what port to use for memcached or redis.', 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php echo esc_html__('Rate auto update', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php
                                            echo draw_select(array(
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
                                                    ), get_option('woocs_currencies_rate_auto_update', ''), 'woocs_currencies_rate_auto_update')
                                            ?>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__('Currencies rate auto update by WordPress cron.', 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php echo esc_html__('Custom currency symbols', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <textarea id="woocs_customer_signs" name="woocs_customer_signs"><?php echo get_option('woocs_customer_signs', '') ?></textarea>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__('Add your custom currencies symbols in your shop. Example: $USD,AAA,AUD$,DDD,X - separated by commas', 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php echo esc_html__('Custom price format', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <input type="text" name="woocs_customer_price_format" value="<?php echo get_option('woocs_customer_price_format', '') ?>">
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__('Set your format how to display price on front. Use keys: __CODE__,__PRICE__. Leave it empty to use default format. Example: __PRICE__ (__CODE__)', 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php echo esc_html__('Prices without cents', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <input type="text" name="woocs_no_cents" value="<?php echo get_option('woocs_no_cents', '') ?>">
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__('Recounts prices without cents everywhere like in JPY and TWD which by its nature have not cents. Use comma. Example: UAH,RUB. Test it for checkout after setup!', 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php echo esc_html__('Show flags by default', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php echo draw_switcher23('woocs_show_flags', get_option('woocs_show_flags', 1)); ?>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__("Show / Hide flags on the front drop-down", 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php echo esc_html__('No GET data in link', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php echo draw_switcher23('woocs_special_ajax_mode', get_option('woocs_special_ajax_mode', 0)); ?>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__("Switches currency without GET parameters (?currency=USD) in the link", 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php echo esc_html__('Show currency symbols', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php echo draw_switcher23('woocs_show_money_signs', get_option('woocs_show_money_signs', 1)); ?>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__("Show or hide money symbols on the site front drop-down", 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php echo esc_html__('Show price info icon', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php echo draw_switcher23('woocs_price_info', get_option('woocs_price_info', 0)); ?>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__("Show info icon near the price of the product which while its under hover shows prices of products in all currencies", 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php echo esc_html__('Email notice about "Rate auto update" results', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php echo draw_switcher23('woocs_rate_auto_update_email', get_option('woocs_rate_auto_update_email', 0)); ?>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__("After cron done its job - new currency rates will be sent on the site admin email. ATTENTION: if you not got emails - it is mean that PHP function mail() doesnt work on your server or sending emails by this function is locked.", 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php echo esc_html__('Hide switcher on checkout page', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php echo draw_switcher23('woocs_restrike_on_checkout_page', get_option('woocs_restrike_on_checkout_page', 0)); ?>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__("Hide switcher on the checkout page, if it is necessary.", 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>



                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php echo esc_html__('Show approximate amount', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php echo draw_switcher23('woocs_show_approximate_amount', get_option('woocs_show_approximate_amount', 0)); ?>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__("Show approximate amount on the checkout page and the cart page with currency of user defined by IP in the GeoIp rules tab. Works only with the currencies rates data and NOT with: fixed prices rules, geo rules.", 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php echo esc_html__('Show approx. price', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php echo draw_switcher23('woocs_show_approximate_price', get_option('woocs_show_approximate_price', 0)); ?>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__("Show approximate price on the shop page and the product single page with currency of user defined by IP in the GeoIp rules tab. Works only with currencies rates data and NOT with: fixed prices rules, geo rules.", 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php echo esc_html__('I am using cache plugin on my site', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php echo draw_switcher23('woocs_shop_is_cached', get_option('woocs_shop_is_cached', 0)); ?>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__('Set Yes here ONLY if you are REALLY use cache plugin for your site, for example like Super cache or Hiper cache (doesn matter). + Set "Custom price format", for example: __PRICE__ (__CODE__). After enabling this feature - clean your cache to make it works. It will allow show prices in selected currency on all pages of site. Fee for this feature - additional AJAX queries for products prices redrawing.', 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr <?php echo (!get_option('woocs_shop_is_cached', 0)) ? "style='display:none'" : "" ?>>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php echo esc_html__('Prices preloader', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php echo draw_switcher23('woocs_shop_is_cached_preloader', get_option('woocs_shop_is_cached_preloader', 0)); ?>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__('For ajax  redraw.', 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php echo esc_html__('Show options button on top admin bar.', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php echo draw_switcher23('woocs_show_top_button', get_option('woocs_show_top_button', 0)); ?>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__('Show WOOCS options button on top admin bar for quick access. Very handy for active work. Visible for site administrators only!', 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php esc_html_e('Disable on pages', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php
                                            $opts = array(
                                                0 => esc_html__('Direct use', 'woocommerce-currency-switcher'),
                                                1 => esc_html__('Reverse use', 'woocommerce-currency-switcher')
                                            );
                                            $woocs_activate_page_list_reverse = get_option('woocs_activate_page_list_reverse', 1);
                                            $woocs_activate_page_list = get_option('woocs_activate_page_list', '');
                                            ?>
                                            <div class="woocs__table-card-flex woocs-flex-column woocs-flex-grow">
                                                <input type="text" name="woocs_activate_page_list" id="woocs_activate_page_list" class="woocs_settings_dd" value="<?php echo esc_attr($woocs_activate_page_list) ?>">
                                                <select name="woocs_activate_page_list_reverse" class="chosen_select enhanced" tabindex="-1" title="<?php esc_html_e('Reverse this option', 'woocommerce-currency-switcher') ?>">
                                                    <?php foreach ($opts as $val => $title): ?>
                                                        <option value="<?php echo esc_attr($val) ?>" <?php echo selected($woocs_activate_page_list_reverse, $val) ?>><?php echo esc_html($title) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php esc_html_e('Disabling or Enabling WOOCS on the described pages only. Use comma and pages slugs. Example: blog,account', 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                            </tbody>
                        </table>

                    </div>
                </div>

                <div id="woocs-tabs-3" class="woocs__tab-pane">
                    <div class="woocs__tab-content">
                        <table class="woocs__table">
                            <thead>
                                <tr>
                                    <th scope="col" class="woocs__table-option"><?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?></th>
                                    <th scope="col" class="woocs__table-status"><?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?></th>
                                    <th scope="col" class="woocs__table-description"><?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?></th>
                                </tr>
                            </thead>
                            <tbody>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php esc_html_e('Is multiple allowed', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php
                                            $opts = array(
                                                0 => esc_html__('No', 'woocommerce-currency-switcher'),
                                                1 => esc_html__('Yes', 'woocommerce-currency-switcher')
                                            );
                                            $woocs_is_multiple_allowed = get_option('woocs_is_multiple_allowed', 0);
                                            ?>
                                            <select name="woocs_is_multiple_allowed" id="woocs_is_multiple_allowed" class="chosen_select enhanced woocs_settings_dd" tabindex="-1" title="<?php esc_html_e('Is multiple allowed', 'woocommerce-currency-switcher') ?>">
                                                <?php foreach ($opts as $val => $title): ?>
                                                    <option value="<?php echo esc_attr($val) ?>" <?php echo selected($woocs_is_multiple_allowed, $val) ?>><?php echo esc_html($title) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php esc_html_e('Customer will pay using selected currency (Yes) or using default currency (No).', 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="<?php if (!$woocs_is_multiple_allowed): ?>woocs_settings_hide<?php endif; ?>">
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>" data-key="woocs_is_fixed_enabled">
                                        <?php esc_html_e('Individual fixed prices rules for each product', 'woocommerce-currency-switcher') ?>(*)
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card woocs__table-card-with-el">
                                            <div class="woocs-d-flex woocs-align-items-center woocs-flex-with-margin">
                                                <div class="woocs__item">
                                                    <?php
                                                    $woocs_is_fixed_enabled = get_option('woocs_is_fixed_enabled', 0);
                                                    echo draw_switcher23('woocs_is_fixed_enabled', $woocs_is_fixed_enabled, 'woocs_blind_option');
                                                    ?>
                                                </div>
                                                <div class="woocs__item">
                                                    <a href="https://currency-switcher.com/video-tutorials#video_YHDQZG8GS6w" target="_blank"
                                                       class="woocs-btn woocs-btn-icon" title="<?php esc_html_e('Watch video instructions', 'woocommerce-currency-switcher') ?>">
                                                        <i class="uil uil-video"></i>
                                                    </a>
                                                </div>
                                            </div>


                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php esc_html_e("You will be able to set FIXED prices for simple and variable products. ATTENTION: 'Is multiple allowed' should be enabled!", 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="<?php if (!$woocs_is_fixed_enabled): ?>woocs_settings_hide<?php endif; ?>">
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>" data-key="woocs_force_pay_bygeoip_rules">
                                        <?php esc_html_e('Checkout by GeoIP rules', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php echo draw_switcher23('woocs_force_pay_bygeoip_rules', get_option('woocs_force_pay_bygeoip_rules', 0)); ?>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__("Forces the customers to pay on checkout page by the rules defined in [GeoIP rules] tab. ATTENTION: this feature has logical sense if you enabled [Enable fixed pricing] and also set fixed prices rules to the products in different currencies!", 'woocommerce-currency-switcher') ?>
                                                <?php
                                                if (!empty($pd) AND!empty($countries) AND isset($countries[$pd['country']])) {
                                                    echo '<i class="woocs_settings_i1" >' . sprintf(esc_html__('Your country is: %s', 'woocommerce-currency-switcher'), $countries[$pd['country']]) . '</i>';
                                                } else {
                                                    echo '<i class="woocs_settings_i2" >' . esc_html__('Your country is not defined! Troubles with internet connection or GeoIp service.', 'woocommerce-currency-switcher') . '</i>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>


                                <tr class="<?php if (!$woocs_is_multiple_allowed): ?>woocs_settings_hide<?php endif; ?>">
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>" data-key="woocs_is_fixed_coupon">
                                        <?php esc_html_e('Individual fixed amount for coupon', 'woocommerce-currency-switcher') ?>(*)
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php echo draw_switcher23('woocs_is_fixed_coupon', get_option('woocs_is_fixed_coupon', 0)); ?>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php esc_html_e("You will be able to set FIXED amount for coupon for each currency. ATTENTION: 'Is multiple allowed' should be enabled!", 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="<?php if (!$woocs_is_multiple_allowed): ?>woocs_settings_hide<?php endif; ?>">
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>" data-key="woocs_is_fixed_shipping">
                                        <?php esc_html_e('Individual fixed amount for shipping', 'woocommerce-currency-switcher') ?>(*)
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php echo draw_switcher23('woocs_is_fixed_shipping', get_option('woocs_is_fixed_shipping', 0)); ?>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php esc_html_e("You will be able to set FIXED amount for each currency for free and all another shipping ways. ATTENTION: 'Is multiple allowed' should be enabled!", 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>" data-key="woocs_is_fixed_user_role">
                                        <?php esc_html_e('Individual prices based on user role', 'woocommerce-currency-switcher') ?>(*)
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php echo draw_switcher23('woocs_is_fixed_user_role', get_option('woocs_is_fixed_user_role', 0)); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php esc_html_e('Gives ability to set different prices for each user role', 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>



                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>" data-key="woocs_is_geoip_manipulation">
                                        <?php esc_html_e('Individual GeoIP rules for each product', 'woocommerce-currency-switcher') ?>(*)
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card woocs__table-card-with-el">
                                            <div class="woocs-d-flex woocs-align-items-center woocs-flex-with-margin">
                                                <div class="woocs__item">
                                                    <?php echo draw_switcher23('woocs_is_geoip_manipulation', get_option('woocs_is_geoip_manipulation', 0), 'woocs_blind_option'); ?>
                                                </div>
                                                <div class="woocs__item">
                                                    <a href="https://currency-switcher.com/video-tutorials#video_PZugTH80-Eo" target="_blank"
                                                       class="woocs-btn woocs-btn-icon" title="<?php esc_html_e('Watch video instructions', 'woocommerce-currency-switcher') ?>">
                                                        <i class="uil uil-video"></i>
                                                    </a>
                                                </div>
                                                <div class="woocs__item">
                                                    <a href="https://currency-switcher.com/video-tutorials#video_zh_LVqKADBU" target="_blank"
                                                       class="woocs-btn woocs-btn-icon" title="<?php esc_html_e('a hint', 'woocommerce-currency-switcher') ?>">
                                                        <i class="uil uil-video"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php esc_html_e("You will be able to set different prices for each product (in BASIC currency) for different countries", 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>" data-key="woocs_collect_statistic">
                                        <?php esc_html_e('Statistic', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php
                                            $enable_stat = array(
                                                0 => esc_html__('No', 'woocommerce-currency-switcher'),
                                                1 => esc_html__('Yes', 'woocommerce-currency-switcher')
                                            );
                                            $collect_statistic = get_option('woocs_collect_statistic', 0);
                                            ?>
                                            <select name="woocs_collect_statistic" id="woocs_collect_statistic" class="chosen_select enhanced woocs_settings_dd" tabindex="-1" title="<?php esc_html_e('Statistic', 'woocommerce-currency-switcher') ?>">
                                                <?php foreach ($enable_stat as $val => $title): ?>
                                                    <option value="<?php echo esc_attr($val) ?>" <?php echo selected($collect_statistic, $val) ?>><?php echo esc_html($title) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php esc_html_e('Collect currencies switching statistic for business purposes. No any private data of customers collects, only currency, country and time of switching. Also statistic for order currencies is there.', 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php echo esc_html__('Payments rules', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php echo draw_switcher23('woocs_payments_rule_enabled', get_option('woocs_payments_rule_enabled', 0)); ?>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__("Hide or show payments systems on checkout page depending of the current currency", 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php esc_html_e('Notes*', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">&nbsp;</div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <i><?php esc_html_e('Native WooCommerce price filter is blind for all data generated by marked features', 'woocommerce-currency-switcher') ?></i>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>




                <div id="woocs-tabs-4" class="woocs__tab-pane woocs_settings_section">
                    <div class="woocs__tab-content">


                        <div class="woocs__tools-panel">
                            <h5><?php echo esc_html__('Side switcher', 'woocommerce-currency-switcher') ?></h5>
                        </div>

                        <table class="woocs__table">
                            <thead>
                                <tr>
                                    <th scope="col" class="woocs__table-option">Option</th>
                                    <th scope="col" class="woocs__table-status">Status</th>
                                    <th scope="col" class="woocs__table-description">Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php esc_html_e('Enable / Disable', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card woocs__table-card-with-el">
                                            <?php
                                            $woocs_is_auto_switcher = get_option('woocs_is_auto_switcher', 0);
                                            echo draw_switcher23('woocs_is_auto_switcher', $woocs_is_auto_switcher, 'woocs_is_auto_switcher');
                                            ?><br>
                                            <a id="woocs-modal-link" href="<?php echo WOOCS_LINK ?>img/side-switcher.png" class="woocs__link woocs__link-img <?php if (!$woocs_is_auto_switcher): ?>woocs_settings_hide<?php endif; ?>">
                                                <img id="woocs-my-img" class="woocs__img" src="<?php echo WOOCS_LINK ?>img/side-switcher.png" />
                                            </a>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php esc_html_e('Enable / Disable the side currency switcher on your site', 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="<?php if (!$woocs_is_auto_switcher): ?>woocs_settings_hide<?php endif; ?>">
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php esc_html_e('Skin', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php
                                            $opts = array(
                                                'classic_blocks' => esc_html__('Classic blocks', 'woocommerce-currency-switcher'),
                                                'roll_blocks' => esc_html__('Roll blocks', 'woocommerce-currency-switcher'),
                                                'round_select' => esc_html__('Round select', 'woocommerce-currency-switcher'),
                                            );
                                            $woocs_auto_switcher_skin = get_option('woocs_auto_switcher_skin', 'classic_blocks');
                                            ?>
                                            <div class="woocs__table-card-flex ">
                                                <select name="woocs_auto_switcher_skin" id="woocs_auto_switcher_skin"  class="chosen_select enhanced woocs_settings_dd" tabindex="-1" title="<?php esc_html_e('Choice skin', 'woocommerce-currency-switcher') ?>">
                                                    <?php foreach ($opts as $val => $title): ?>
                                                        <option value="<?php echo esc_attr($val) ?>" <?php echo selected($woocs_auto_switcher_skin, $val) ?>><?php echo esc_html($title) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="woocs_roll_blocks_width" class="<?php if ($woocs_auto_switcher_skin != 'roll_blocks'): ?>woocs_settings_hide<?php endif; ?>">
                                                    <input type="text" name="woocs_auto_switcher_roll_px" id="woocs_auto_switcher_roll_px" placeholder="<?php esc_html_e('enter roll width', 'woocommerce-currency-switcher') ?>"  value="<?php echo get_option('woocs_auto_switcher_roll_px', 90) ?>" />
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php esc_html_e("Style of the switcher on the site front (px)", 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="<?php if (!$woocs_is_auto_switcher): ?>woocs_settings_hide<?php endif; ?>">
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php esc_html_e('Side', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php
                                            $opts = array(
                                                'left' => esc_html__('Left', 'woocommerce-currency-switcher'),
                                                'right' => esc_html__('Right', 'woocommerce-currency-switcher'),
                                            );
                                            $woocs_auto_switcher_side = get_option('woocs_auto_switcher_side', 'left');
                                            ?>
                                            <select name="woocs_auto_switcher_side" class="" tabindex="-1" title="<?php esc_html_e('Choice side', 'woocommerce-currency-switcher') ?>">
                                                <?php foreach ($opts as $val => $title): ?>
                                                    <option value="<?php echo esc_attr($val) ?>" <?php echo selected($woocs_auto_switcher_side, $val) ?>><?php echo esc_html($title) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php esc_html_e("The side where the switcher is be placed", 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="<?php if (!$woocs_is_auto_switcher): ?>woocs_settings_hide<?php endif; ?>">
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php esc_html_e('Top margin', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php
                                            $woocs_auto_switcher_top_margin = get_option('woocs_auto_switcher_top_margin', '100px');
                                            ?>
                                            <input type="text" name="woocs_auto_switcher_top_margin" id="woocs_auto_switcher_top_margin" class="woocs_settings_dd" value="<?php echo esc_attr($woocs_auto_switcher_top_margin) ?>" >
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php esc_html_e("Distance from the top of the screen to the switcher html block. You can set in px or in %. Example 1: 100px. Example 2: 10%.", 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="<?php if (!$woocs_is_auto_switcher): ?>woocs_settings_hide<?php endif; ?>">
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php esc_html_e('Basic field(s)', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php
                                            $woocs_auto_switcher_basic_field = get_option('woocs_auto_switcher_basic_field', '__CODE__ __SIGN__');
                                            ?>
                                            <input type="text" name="woocs_auto_switcher_basic_field" id="woocs_auto_switcher_basic_field" class="woocs_settings_dd" value="<?php echo esc_attr($woocs_auto_switcher_basic_field) ?>" >
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php esc_html_e("What content to show in the switcher after the site page loading. Variants:  __CODE__ __FLAG___ __SIGN__ __DESCR__", 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="<?php if (!$woocs_is_auto_switcher): ?>woocs_settings_hide<?php endif; ?>">
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php esc_html_e('Hover field(s)', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php
                                            $woocs_auto_switcher_additional_field = get_option('woocs_auto_switcher_additional_field', '__DESCR__');
                                            ?>
                                            <input type="text" name="woocs_auto_switcher_additional_field" id="woocs_auto_switcher_additional_field" class="woocs_settings_dd" value="<?php echo esc_attr($woocs_auto_switcher_additional_field) ?>" >
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php esc_html_e("What content to show in the switcher after mouse hover on any currency there. Variants:  __CODE__ __FLAG___ __SIGN__ __DESCR__", 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="<?php if (!$woocs_is_auto_switcher): ?>woocs_settings_hide<?php endif; ?>">
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php esc_html_e('Show on the pages', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php
                                            $woocs_auto_switcher_show_page = get_option('woocs_auto_switcher_show_page', '');
                                            ?>
                                            <input type="text" name="woocs_auto_switcher_show_page" id="woocs_auto_switcher_show_page" class="woocs_settings_dd" value="<?php echo esc_attr($woocs_auto_switcher_show_page) ?>" >
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php esc_html_e("Where on the site the switcher should be visible. If any value is presented here switcher will be hidden on all another pages which not presented in this field. You can use pages IDs using comma, example: 28,34,232. Also you can use special words as: product, shop, checkout, front_page, woocommerce", 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="<?php if (!$woocs_is_auto_switcher): ?>woocs_settings_hide<?php endif; ?>">
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php esc_html_e('Hide on the pages', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
                                            <?php
                                            $woocs_auto_switcher_hide_page = get_option('woocs_auto_switcher_hide_page', '');
                                            ?>
                                            <input type="text" name="woocs_auto_switcher_hide_page" id="woocs_auto_switcher_hide_page" class="woocs_settings_dd" value="<?php echo esc_attr($woocs_auto_switcher_hide_page) ?>" >
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php esc_html_e("Where on the site the switcher should be hidden. If any value is presented here switcher will be hidden on that pages and visible on all another ones. You can use pages IDs using comma, example: 28,34,232. Also you can use special words as: product, shop, checkout, front_page, woocommerce", 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="<?php if (!$woocs_is_auto_switcher): ?>woocs_settings_hide<?php endif; ?>">
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php esc_html_e('Behavior for devices', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card">
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
                                                    <option value="<?php echo esc_attr($val) ?>" <?php echo selected($woocs_auto_switcher_mobile_show, $val) ?>><?php echo esc_attr($title) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php esc_html_e("Show / Hide on mobile device (highest priority)", 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="<?php if (!$woocs_is_auto_switcher): ?>woocs_settings_hide<?php endif; ?>">
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php echo esc_html__('Main color', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card woocs-justify-start">
                                            <input class="woocs-color-picker" data-default-color="#222222" name="woocs_auto_switcher_color" type="text" value="<?php echo get_option('woocs_auto_switcher_color', '') ?>" />
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__('Main color which coloring the switcher elements', 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                <tr class="<?php if (!$woocs_is_auto_switcher): ?>woocs_settings_hide<?php endif; ?>">
                                    <td data-title="<?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?>">
                                        <?php echo esc_html__('Hover color', 'woocommerce-currency-switcher') ?>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-card woocs-justify-start">
                                            <input class="woocs-color-picker" data-default-color="#3b5998" name="woocs_auto_switcher_hover_color" type="text" value="<?php echo get_option('woocs_auto_switcher_hover_color', '') ?>" />
                                        </div>
                                    </td>
                                    <td data-title="<?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?>">
                                        <div class="woocs__table-desc">
                                            <div class="woocs__table-desc-body">
                                                <?php echo esc_html__('The switcher color when mouse hovering', 'woocommerce-currency-switcher') ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                            </tbody>
                        </table>

                    </div>
                </div>

                <?php if ($woocs_is_payments_rule_enable): ?>
                    <div id="woocs-tabs-5" class="woocs__tab-pane woocs_settings_section">
                        <div class="woocs__tab-content">

                            <div class="woocs__tools-panel">
                                <h5><?php echo esc_html__('Payments rules', 'woocommerce-currency-switcher') ?></h5>
                            </div>

                            <table class="woocs__table woocs__middle-size">
                                <thead>
                                    <tr>
                                        <th scope="col" class="woocs__table-option"><?php echo esc_html__('Option', 'woocommerce-currency-switcher') ?></th>
                                        <th scope="col" class="woocs__table-status"><?php echo esc_html__('Status', 'woocommerce-currency-switcher') ?></th>
                                        <th scope="col" class="woocs__table-description"><?php echo esc_html__('Description', 'woocommerce-currency-switcher') ?></th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <tr>
                                        <td>
                                            <?php esc_html_e('Payments behavior', 'woocommerce-currency-switcher') ?>
                                        </td>
                                        <td>
                                            <div class="woocs__table-card">
                                                <?php
                                                $opts = array(
                                                    0 => esc_html__('Is hidden', 'woocommerce-currency-switcher'),
                                                    1 => esc_html__('Is shown', 'woocommerce-currency-switcher')
                                                );
                                                $woocs_payment_control = get_option('woocs_payment_control', 0);
                                                ?>
                                                <select name="woocs_payment_control" id="woocs_payment_control" class="chosen_select enhanced woocs_settings_dd" tabindex="-1" title="<?php esc_html_e('Behavior', 'woocommerce-currency-switcher') ?>">
                                                    <?php foreach ($opts as $val => $title): ?>
                                                        <option value="<?php echo esc_attr($val) ?>" <?php echo selected($woocs_payment_control, $val) ?>><?php echo esc_html($title) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="woocs__table-desc">
                                                <div class="woocs__table-desc-body">
                                                    <?php esc_html_e('Should the payment systems be hidden for selected currencies or vice versa shown!', 'woocommerce-currency-switcher') ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    <?php
                                    $payments = WC()->payment_gateways->payment_gateways();
                                    $woocs_payments_rules = get_option('woocs_payments_rules', array());
                                    foreach ($payments as $key => $payment) {

                                        if ($payment->enabled == "yes"):
                                            ?>
                                            <tr>
                                                <td>
                                                    <?php echo esc_html($payment->title) ?>
                                                </td>
                                                <td colspan="2">
                                                    <div class="woocs__table-card woocs-justify-start">
                                                        <div class="woocs__table-card-flex woocs-flex-column woocs-flex-grow">
                                                            <select name="woocs_payments_rules[<?php echo esc_attr($key) ?>][]" multiple=""  class="chosen_select woocs_settings_dd"  title="<?php esc_html_e('Choice currencies', 'woocommerce-currency-switcher') ?>">
                                                                <?php
                                                                $payment_rules = array();
                                                                if (isset($woocs_payments_rules[$key])) {
                                                                    $payment_rules = $woocs_payments_rules[$key];
                                                                }
                                                                if (!empty($currencies) AND is_array($currencies)) {
                                                                    foreach ($currencies as $key_curr => $currency) {
                                                                        ?>
                                                                        <option value="<?php echo esc_attr($key_curr) ?>" <?php echo(in_array($key_curr, $payment_rules) ? 'selected=""' : '') ?>><?php echo esc_html($key_curr) ?></option>
                                                                        <?php
                                                                    }
                                                                }
                                                                ?>
                                                            </select>
                                                            <div class="woocs__buttons-group woocs-flr-clr">
                                                                <a href="javascript: void(0)" class="woocs__button woocs__button-small woocs-select-all-in-select"><?php echo esc_html__('Select all', 'woocommerce-currency-switcher') ?></a>
                                                                <a href="javascript: void(0)" class="woocs__button woocs__button-small woocs__button-outline-warning woocs-clear-all-in-select"><?php echo esc_html__('Clear all', 'woocommerce-currency-switcher') ?></a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>


                                            </tr>
                                            <?php
                                        endif;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>


                <?php if ($this->is_use_geo_rules()): ?>
                    <div id="woocs-tabs-6" class="woocs__tab-pane">
                        <div class="woocs__tab-content">

                            <div class="woocs__tools-panel">
                                <h5><?php esc_html_e('GeoIP rules', 'woocommerce-currency-switcher') ?></h5>

                                <?php if (empty($pd)): ?>
                                    <b class="woocs_hint"><?php esc_html_e("WooCommerce GeoIP functionality doesn't work on your site", 'woocommerce-currency-switcher'); ?>:&nbsp;<a href='https://wordpress.org/support/topic/geolocation-not-working-1/?replies=10' target='_blank'><?php esc_html_e("read this please", 'woocommerce-currency-switcher'); ?></a></b>
                                <?php endif; ?>
                            </div>


                            <ul class="woocs__tab-content-list">
                                <?php
                                if (!empty($currencies) AND is_array($currencies)) {
                                    foreach ($currencies as $key => $currency) {
                                        $rules = array();
                                        if (isset($geo_rules[$key])) {
                                            $rules = $geo_rules[$key];
                                        }
                                        ?>
                                        <li class="woocs__tab-content-list-item">
                                            <table class="woocs__settings-geo-table woocs__table-fullwidth">
                                                <tbody>
                                                    <tr>
                                                        <td class="woocs__settings-geo-table-title">
                                                            <div class="<?php if ($currency['is_etalon']): ?>woocs_hint<?php endif; ?>"><strong><?php echo esc_html($key) ?>:</strong></div>
                                                        </td>
                                                        <td class="woocs__settings-geo-table-td">
                                                            <div class="woocs-d-flex woocs-flex-column">
                                                                <select name="woocs_geo_rules[<?php echo esc_attr($currency['name']) ?>][]" multiple class="chosen_select">
                                                                    <?php foreach ($countries as $key => $value): ?>
                                                                        <option <?php echo(in_array($key, $rules) ? 'selected=""' : '') ?> value="<?php echo esc_attr($key) ?>"><?php echo esc_html($value) ?></option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                                <div class="woocs__buttons-group woocs-align-self-end woocs-flr-clr">
                                                                    <a href="javascript: void(0)" class="woocs__button woocs__button-small woocs-select-all-in-select"><?php echo esc_html__('Select all', 'woocommerce-currency-switcher') ?></a>
                                                                    <a href="javascript: void(0)" class="woocs__button woocs__button-small woocs__button-outline-warning woocs-clear-all-in-select"><?php echo esc_html__('Clear all', 'woocommerce-currency-switcher') ?></a>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </li>
                                        <?php
                                    }
                                }
                                ?>
                                <li class="woocs__tab-content-list-item-profile">

                                    <div class="woocs__tools-panel">
                                        <h5><?php esc_html_e("GeoIP profiles", 'woocommerce-currency-switcher'); ?></h5>
                                    </div>

                                    <div class="woocs-p-3 woocs-border-bottom">

                                        <div class="woocs__alert woocs__alert-info" role="alert">
                                            <ul class="woocs-list-unstyled">
                                                <li><?php printf(__("Here you can create set of countries profiles to apply it then in the products by your business logic. %s!", 'woocommerce-currency-switcher'), "<a href='" . WOOCS_LINK . "img/woocs-options-1.png' class='woocs-text-decoration-underline2' target='_blank'>" . esc_html__('This make work on rules settings for each product more fast', 'woocommerce-currency-switcher') . "</a>"); ?></li>
                                            </ul>
                                        </div>

                                        <div class="woocs-d-flex22">

                                            <div class="woocs_fields woocs-align-items-center woocs-field-col-5" style="display: block;">
                                                <input type="hidden" name="woocs_geo_rules_profile_key" value="">
                                                <input type="text" name="woocs_geo_rules_profile_title" value="" class="woocs-w-100p" placeholder="<?php esc_html_e("Set title", 'woocommerce-currency-switcher'); ?>" />
                                                <select name="woocs_geo_rules_profile_countries[]" multiple="" size="1" style="width: 100%" id="woocs_geo_rules_profile_countries" class="chosen_select">
                                                    <?php foreach ($countries as $key => $value): ?>
                                                        <option  value="<?php echo esc_attr($key) ?>"><?php echo esc_html($value) ?></option>
                                                    <?php endforeach; ?>
                                                </select>

                                                <a href="javascript:woocs_add_geoip_profile()" class="woocs__button woocs_add_geoip_profile"><?php esc_html_e("Add new profile", 'woocommerce-currency-switcher'); ?></a>
                                                <a href="javascript:woocs_update_geoip_profile()" class="woocs__button woocs_update_geoip_profile"><?php esc_html_e("Update profile", 'woocommerce-currency-switcher'); ?></a>
                                                <a href="javascript:woocs_cancel_geoip_profile()" class="woocs__button woocs_update_cancel_geoip_profile"><?php esc_html_e("Cancel", 'woocommerce-currency-switcher'); ?></a>
                                            </div>

                                        </div>

                                    </div>

                                    <div class="woocs__tools-panel">
                                        <h5><?php esc_html_e("Profiles", 'woocommerce-currency-switcher'); ?></h5>
                                        <div class="woocs_geoip_profile_info woocs-notice" style="display: none;"></div>
                                    </div>

                                    <div class="woocs-p-3">

                                        <div class="woocs-d-flex">

                                            <?php
                                            global $WOOCS;
                                            $geoIP_profiles = $WOOCS->geoip_profiles->get_data();
                                            ?>

                                            <div class="woocs_fields woocs-field-col-4">
                                                <select class="chosen_select woocs_geoip_profile_countries">
                                                    <?php
                                                    foreach ($geoIP_profiles as $key => $item) {
                                                        ?>
                                                        <option data-key="<?php echo esc_attr($key) ?>" value='<?php echo json_encode($item['data']) ?>'><?php echo esc_html($item['name']) ?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>

                                                <a href="javascript:woocs_edit_geoip_profile()" class="woocs__button woocs_edit_geoip_profile"><?php esc_html_e("Edit profile", 'woocommerce-currency-switcher'); ?></a>
                                                <a href="javascript:woocs_delete_geoip_profile()" class="woocs__button woocs_delete_geoip_profile"><?php esc_html_e("Delete profile", 'woocommerce-currency-switcher'); ?></a>

                                                <div class="woocs__profiles-add-to-rules" >
                                                    <a href="javascript:woocs_geoip_profile_to_rules()" class="woocs__button woocs_profile_geoip_rules"><?php esc_html_e("Add to:", 'woocommerce-currency-switcher'); ?></a>
                                                    <?php
                                                    if (!empty($currencies) AND is_array($currencies)) {
                                                        ?><select class="chosen_select woocs_profile_geoip_currency">
                                                            <?php esc_html_e("Select currency...", 'woocommerce-currency-switcher'); ?></option>
                                                            <?php
                                                            foreach ($currencies as $key => $currency) {
                                                                ?><option value="<?php echo esc_attr($key) ?>" ><?php echo esc_html($key) ?></option><?php
                                                            }
                                                            ?></select><?php
                                                    }
                                                    ?>
                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                </li>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="woocs_geo_rules" value="" />
                <?php endif; ?>

                <?php if ($this->statistic AND $this->statistic->can_collect()): ?>
                    <?php $this->statistic->install_table(); ?>

                    <div id="woocs-tabs-7" class="woocs__tab-pane">
                        <div class="woocs__tab-content">

                            <div class="woocs__tools-panel">
                                <h5><?php echo esc_html__('Statistic', 'woocommerce-currency-switcher') ?></h5>
                            </div>


                            <div class="woocs-p-3">
                                <div class="woocs__stat-holder">


                                    <div class="woocs__stat-col" style="flex-basis: 75%;">
                                        <select id="woocs-stat-chart-type" class="woocs__max-width">
                                            <option value="pie"><?php echo esc_html__('Chart type: pie', 'woocommerce-currency-switcher') ?></option>
                                            <option value="bar"><?php echo esc_html__('Chart type: bar', 'woocommerce-currency-switcher') ?></option>
                                        </select>

                                        <div id="woocs-stat-chart" class="woocs__stat-chart"></div>

                                    </div>


                                    <div class="woocs__stat-col" style="flex-basis: 25%;">
                                        <ul class="woocs__list">
                                            <li>
                                                <select id="woocs-stat-type" class="woocs__max-width">
                                                    <option value="currency"><?php echo esc_html__('Currency', 'woocommerce-currency-switcher') ?></option>
                                                    <option value="order"><?php echo esc_html__('Orders (completed)', 'woocommerce-currency-switcher') ?></option>
                                                </select>
                                            </li>
                                            <li>
                                                <?php
                                                $format = 'dd-mm-yy';
                                                $min_date = $this->statistic->get_min_date();
                                                $first_this_m = new DateTime('first day of this month');
                                                ?>

                                                <input type="hidden" id="woocs-stat-calendar-format" value="<?php echo esc_attr($format) ?>" />
                                                <input type="hidden" id="woocs-stat-calendar-min-y" value="<?php echo date('Y', $min_date) ?>" />
                                                <input type="hidden" id="woocs-stat-calendar-min-m" value="<?php echo (intval(date('m', $min_date)) - 1) ?>" />
                                                <input type="hidden" id="woocs-stat-calendar-min-d" value="<?php echo date('d', $min_date) ?>" />

                                                <input type="hidden" id="woocs-stat-from" value="<?php echo mktime(0, 0, 0, $first_this_m->format('m'), $first_this_m->format('d'), $first_this_m->format('Y')) ?>" />
                                                <input type="text" readonly="" value="<?php echo $first_this_m->format('d-m-Y'); ?>" class="woocs_stat_calendar woocs__max-width woocs_stat_calendar_from" placeholder="<?php echo esc_html__('from', 'woocommerce-currency-switcher') ?>" />
                                                <input type="hidden" id="woocs-stat-to" value="0" />
                                                <input type="text" readonly="" value="" class="woocs_stat_calendar woocs__max-width woocs_stat_calendar_to" placeholder="<?php echo esc_html__('to', 'woocommerce-currency-switcher') ?>" />

                                            </li>
                                            <li class="woocs-d-flex woocs-align-items-center">
                                                <a href="javascript: woocs_stat_redraw(1); void(0);" id="woocs_stat_redraw_1" class="woocs_stat_redraw_btn woocs__button">
                                                    <i class="uil uil-refresh"></i>&nbsp;<?php echo $this->statistic->get_label(1) ?>
                                                </a>&nbsp;
                                                <label class="woocs-options-valign-top">
                                                    <?php woocs_draw_tooltip(esc_html__('For currencies - aggregated data about selected currencies on the site front. For orders - count of orders made in the selected currencies.', 'woocommerce-currency-switcher')) ?>
                                                </label>
                                            </li>
                                            <li class="woocs-d-flex woocs-align-items-center">
                                                <a href="javascript: woocs_stat_redraw(2); void(0);" id="woocs_stat_redraw_2" class="woocs_stat_redraw_btn woocs__button">
                                                    <i class="uil uil-refresh"></i>&nbsp;<?php echo $this->statistic->get_label(2) ?>
                                                </a>&nbsp;
                                                <label class="woocs-options-valign-top"><?php woocs_draw_tooltip(esc_html__('For currencies - aggregated data about count of countries which users selected currencies on the site front. For orders - count of orders made from countries, detected by selected country in the billing address.', 'woocommerce-currency-switcher')) ?></label>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="woocs__list woocs-p-10">

                                <div class="woocs__alert woocs__alert-info" role="alert">
                                    <p><?php printf(__("If you have ideas about scenarios of the statistic please share and discuss them on %s", 'woocommerce-currency-switcher'), '<a href="https://pluginus.net/support/forum/woocs-woocommerce-currency-switcher-multi-currency-and-multi-pay-for-woocommerce/" target="_blank">' . esc_html__('the plugin forum', 'woocommerce-currency-switcher') . '</a>') ?></p>
                                </div>


                                <div class="woocs__alert woocs__alert-warning" role="alert">
                                    <h4 class="woocs__alert-heading"><?php esc_html_e('Note', 'woocommerce-currency-switcher') ?></h4>
                                    <p><?php esc_html_e('If in tab Options activated option I am using cache plugin on my site - to avoid double data for statistical data registration in tab Options activate option No GET data in link!', 'woocommerce-currency-switcher') ?></p>
                                </div>


                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div id="woocs-tabs-8" class="woocs__tab-pane">
                    <div class="woocs__tab-content">

                        <div class="woocs-p-4">

                            <div class="woocs-card-holder woocs__col-2">

                                <div class="woocs-card-item">

                                    <div class="woocs-card woocs-transition woocs-text-center woocs-rounded">
                                        <div class="woocs-card-body">
                                            <img src="<?php echo WOOCS_LINK ?>img/icon/site-structure-optimization.svg" class="woocs-avatar woocs-avatar-small woocs-mb-3" alt="">
                                            <h5 class="woocs-h5"><a href="https://pluginus.net/support/forum/woocs-woocommerce-currency-switcher-multi-currency-and-multi-pay-for-woocommerce/" class="woocs-text-dark" target="_blank"><?php esc_html_e('Support', 'woocommerce-currency-switcher'); ?></a></h5>
                                            <p><?php esc_html_e('If you have questions about plugin functionality or found bug write us please', 'woocommerce-currency-switcher'); ?></p>
                                        </div>
                                    </div>

                                </div>

                                <div class="woocs-card-item">

                                    <div class="woocs-card woocs-transition woocs-text-center woocs-rounded">
                                        <div class="woocs-card-body">
                                            <img src="<?php echo WOOCS_LINK ?>img/icon/features.svg" class="woocs-avatar woocs-avatar-small woocs-mb-3" alt="">
                                            <h5 class="woocs-h5"><a href="https://currency-switcher.com/faq" class="woocs-text-dark" target="_blank"><?php esc_html_e('FAQ', 'woocommerce-currency-switcher'); ?></a></h5>
                                            <p><?php esc_html_e('Check please already prepared answers', 'woocommerce-currency-switcher'); ?></p>
                                        </div>
                                    </div>

                                </div>

                                <div class="woocs-card-item">

                                    <div class="woocs-card woocs-transition woocs-text-center woocs-rounded">
                                        <div class="woocs-card-body">
                                            <img src="<?php echo WOOCS_LINK ?>img/icon/bookmarking.svg" class="woocs-avatar woocs-avatar-small woocs-mb-3" alt="">
                                            <h5 class="woocs-h5"><a href="https://currency-switcher.com/codex/" class="woocs-text-dark" target="_blank"><?php esc_html_e('Codex', 'woocommerce-currency-switcher'); ?></a></h5>
                                            <p><?php esc_html_e('WOOCS has power bunch of functionality', 'woocommerce-currency-switcher'); ?></p>
                                        </div>
                                    </div>

                                </div>

                                <div class="woocs-card-item">

                                    <div class="woocs-card woocs-transition woocs-text-center woocs-rounded">
                                        <div class="woocs-card-body">
                                            <img src="<?php echo WOOCS_LINK ?>img/icon/clean-code.svg" class="woocs-avatar woocs-avatar-small woocs-mb-3" alt="">
                                            <h5 class="woocs-h5"><a href="https://currency-switcher.com/woocs-labs/" class="woocs-text-dark" target="_blank"><?php esc_html_e('Labs', 'woocommerce-currency-switcher'); ?></a></h5>
                                            <p><?php esc_html_e('Found incompatibility? We can help!', 'woocommerce-currency-switcher'); ?></p>
                                        </div>
                                    </div>

                                </div>

                            </div>

                            <div class="woocs-row">

                                <div class="woocs-col-lg-6 woocs-mt-4">

                                    <div class="woocs-d-flex woocs-p-4 woocs-shadow woocs-align-items-center woocs-features woocs-rounded">
                                        <div class="woocs-icons woocs-text-primary woocs-text-center">
                                            <i class="uil uil-info-circle woocs-d-block woocs-rounded"></i>
                                        </div>
                                        <div class="woocs-ms-4">
                                            <h5 class="woocs-h5 woocs-mb-1">
                                                <a class="woocs-text-dark" href="https://www.youtube.com/watch?v=wUoM9EHjnYs" target="_blank">
                                                    <?php esc_html_e("Quick Introduction", 'woocommerce-currency-switcher') ?>
                                                </a>
                                            </h5>
                                        </div>
                                    </div>

                                </div>

                                <div class="woocs-col-lg-6 woocs-mt-4">
                                    <div class="woocs-d-flex woocs-p-4 woocs-shadow woocs-align-items-center woocs-features woocs-rounded">
                                        <div class="woocs-icons woocs-text-primary woocs-text-center">
                                            <i class="uil uil-favorite woocs-d-block woocs-rounded"></i>
                                        </div>
                                        <div class="woocs-ms-4">
                                            <h5 class="woocs-h5 woocs-mb-1">
                                                <a class="woocs-text-dark" href="https://pluginus.net/support/forum/woocs-woocommerce-currency-switcher-multi-currency-and-multi-pay-for-woocommerce/" target="_blank"><?php echo esc_html__('WooCommerce Currency Switcher Support', 'woocommerce-currency-switcher') ?></a>
                                            </h5>
                                        </div>
                                    </div>
                                </div>

                                <div class="woocs-col-lg-6 woocs-mt-4">
                                    <div class="woocs-d-flex woocs-align-items-center woocs-p-4 woocs-shadow woocs-features woocs-rounded">
                                        <div class="woocs-icons woocs-text-primary woocs-text-center">
                                            <i class="uil uil-language woocs-d-block woocs-rounded"></i>
                                        </div>
                                        <div class="woocs-ms-4">
                                            <h5 class="woocs-h5 woocs-mb-1">
                                                <a class="woocs-text-dark" href="https://currency-switcher.com/translations/" target="_blank"><?php echo esc_html__('Translations', 'woocommerce-currency-switcher') ?></a>
                                            </h5>
                                        </div>
                                    </div>
                                </div>

                                <div class="woocs-col-lg-6 woocs-mt-4">
                                    <div class="woocs-d-flex woocs-align-items-center woocs-p-4 woocs-shadow woocs-features woocs-rounded">
                                        <div class="woocs-icons woocs-text-primary woocs-text-center">
                                            <i class="uil uil-dollar-alt woocs-d-block woocs-rounded"></i>
                                        </div>
                                        <div class="woocs-ms-4">
                                            <h5 class="woocs-h5 woocs-mb-1">
                                                <a class="woocs-text-dark" href="http://en.wikipedia.org/wiki/ISO_4217#Active_codes" target="_blank"><?php echo esc_html__('Read wiki about Currency Active codes', 'woocommerce-currency-switcher') ?></a>
                                            </h5>
                                        </div>
                                    </div>
                                </div>

                                <div class="woocs-col-lg-6 woocs-mt-4">
                                    <div class="woocs-d-flex woocs-align-items-center woocs-p-4 woocs-shadow woocs-features woocs-rounded">
                                        <div class="woocs-icons woocs-text-primary woocs-text-center">
                                            <i class="uil uil-globe woocs-d-block woocs-rounded"></i>
                                        </div>
                                        <div class="woocs-ms-4">
                                            <h5 class="woocs-h5 woocs-mb-1">
                                                <a class="woocs-text-dark" href="https://currency-switcher.com/get-flags-free/" target="_blank"><?php echo esc_html__('Free flags images', 'woocommerce-currency-switcher') ?></a>
                                            </h5>
                                            <p><?php esc_html_e('Find and use free flags to your taste according to your business preferences', 'woocommerce-currency-switcher') ?></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="woocs-col-lg-6 woocs-mt-4">
                                    <div class="woocs-d-flex woocs-align-items-center woocs-p-4 woocs-shadow woocs-features woocs-rounded">
                                        <div class="woocs-icons woocs-text-primary woocs-text-center">
                                            <i class="uil uil-video woocs-d-block woocs-rounded"></i>
                                        </div>
                                        <div class="woocs-ms-4">
                                            <h5 class="woocs-h5 woocs-mb-1">
                                                <a class="woocs-text-dark" href="https://currency-switcher.com/video-tutorials/" target="_blank"><?php echo esc_html__('Video tutorials', 'woocommerce-currency-switcher') ?></a>
                                            </h5>
                                            <p><?php esc_html_e('Watch video tutorials about WOOCS features to use its functionality in full force', 'woocommerce-currency-switcher') ?></p>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <?php
                            $rate_url = 'https://codecanyon.net/downloads#item-8085217';
                            if ($WOOCS->notes_for_free) {
                                $rate_url = 'https://wordpress.org/support/plugin/woocommerce-currency-switcher/reviews/#new-post';
                            }
                            ?>

                            <div class="woocs__alert woocs__alert-info" role="alert">
                                <h5 class="woocs__alert-heading">
                                    <?php esc_html_e('Some questions', 'woocommerce-currency-switcher') ?>:
                                </h5>
                                <ul class="woocs-list-unstyled woocs-text-muted woocs-border-top woocs-mb-0 woocs-pt-3">
                                    <li><a href="https://currency-switcher.com/i-cant-add-flags-what-to-do/" target="_blank" class="woocs-text-decoration-underline"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="woocs-fea woocs-icon-sm woocs-me-2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 16 16 12 12 8"></polyline><line x1="8" y1="12" x2="16" y2="12"></line></svg><?php esc_html_e("I cant add flags! What to do?", 'woocommerce-currency-switcher') ?></a></li>
                                    <li><a href="https://currency-switcher.com/using-geolocation-causes-problems-doesnt-seem-to-work-for-me/" target="_blank" class="woocs-text-decoration-underline"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="woocs-fea woocs-icon-sm woocs-me-2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 16 16 12 12 8"></polyline><line x1="8" y1="12" x2="16" y2="12"></line></svg><?php esc_html_e("Using Geolocation causes problems, doesn’t seem to work for me", 'woocommerce-currency-switcher') ?></a></li>
                                    <li><a href="https://currency-switcher.com/how-to-insert-currency-switcher-into-site-menu/" target="_blank" class="woocs-text-decoration-underline"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="woocs-fea woocs-icon-sm woocs-me-2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 16 16 12 12 8"></polyline><line x1="8" y1="12" x2="16" y2="12"></line></svg><?php esc_html_e("How to insert currency switcher into site menu?", 'woocommerce-currency-switcher') ?></a></li>
                                </ul>
                            </div>

                            <div class="woocs__section-title woocs-mb-3">
                                <h5><?php esc_html_e("More power for your shop", 'woocommerce-currency-switcher') ?></h5>
                            </div>

                            <ul class="woocs__features-gallery woocs__col-6">
                                <li><a target="_blank" href="https://pluginus.net/affiliate/woocommerce-products-filter"><img class="woocs-rounded" width="300" src="<?php echo WOOCS_LINK ?>img/woof_banner.png" /></a></li>
                                <li><a target="_blank" href="https://pluginus.net/affiliate/woocommerce-bulk-editor"><img class="woocs-rounded" width="300" src="<?php echo WOOCS_LINK ?>img/woobe_banner.png" /></a></li>
                                <li><a target="_blank" href="https://pluginus.net/affiliate/woot-woocommerce-products-tables"><img class="woocs-rounded" width="300" src="<?php echo WOOCS_LINK ?>img/woot_banner.png" /></a></li>
                            </ul>


                        </div>

                    </div>
                </div>

                <div id="tab-smart-designer" class="woocs__tab-pane woocs_settings_section">
                    <div class="woocs__tab-content">

                        <div id="woocs-sd-manage-area">

                            <div class="woocs__tools-panel">
                                <h5><?php esc_html_e("Smart Designer", 'woocommerce-currency-switcher') ?></h5>
                            </div>


                            <div style="padding: 10px;">
                                <div class="woocs__alert woocs__alert-success"><?php esc_html_e("In this section you can create your own view of currency drop-down switcher", 'woocommerce-currency-switcher'); ?></div>


                                <a href="#" id="woocs-sd-create" class="woocs__button dashicons-before dashicons-plus"><?php esc_html_e("Create", 'woocommerce-currency-switcher') ?></a><br />


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
                                                            <div class="woocs__buttons-group woocs-align-self-end woocs-flr-clr">
                                                                <a href="javascript: woocs_sd_edit(<?php echo $design_id ?>);void(0);" class="woocs__button woocs__button-small woocs__button-outline-success dashicons-before dashicons-update"><?php esc_html_e('edit', 'woocommerce-currency-switcher') ?></a><a href="javascript: woocs_sd_delete(<?php echo $design_id ?>);void(0);" class="woocs__button woocs__button-small woocs__button-outline-warning dashicons-before dashicons-dismiss"><?php esc_html_e('delete', 'woocommerce-currency-switcher') ?></a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>

                                    </table>

                                </div>


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
                                        <?php global $WOOCS_SD; ?>
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


                    </div>
                </div>


            </div>
        </div>

        <div class="woocs_settings_powered woocs-mb-3">
            <h5><a href="https://pluginus.net/" target="_blank" >Powered by PluginUs.NET</a></h5>
        </div>

    </div>

    <div class="woocs__alert woocs__alert-info" role="alert">
        <ul class="woocs-list-unstyled">
            <li><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="woocs-fea woocs-icon-sm woocs-me-2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 16 16 12 12 8"></polyline><line x1="8" y1="12" x2="16" y2="12"></line></svg><span><?php esc_html_e('Hint', 'woocommerce-currency-switcher'); ?>:</span>&nbsp;<?php esc_html_e('If you want let your customers pay in their selected currency in tab Advanced set the option Is multiple allowed to Yes.', 'woocommerce-currency-switcher'); ?></li>
            <li><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="woocs-fea woocs-icon-sm woocs-me-2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 16 16 12 12 8"></polyline><line x1="8" y1="12" x2="16" y2="12"></line></svg><span><?php esc_html_e('Note', 'woocommerce-currency-switcher'); ?>:</span>&nbsp;<?php esc_html_e("If WOOCS settings panel looks incorrect or you have JavaScript errors (after update) - firstly", 'woocommerce-currency-switcher') ?> <a href="https://pluginus.net/how-to-reset-page-cache-in-the-browser/" target="_blank"><?php esc_html_e("reset the browser cache", 'woocommerce-currency-switcher') ?></a></li>
        </ul>
    </div>

    <?php if ($WOOCS->notes_for_free): ?>

        <div class="woocs__alert woocs__alert-warning woocs_free_warning"><?php printf(__('In the free version of the plugin %s you can operate with 2 ANY currencies only%s! If you want to use more currencies %s you can make upgrade to the premium version%s of the plugin', 'woocommerce-currency-switcher'), '<b class="woocs_red">', '</b>', '<a href="https://pluginus.net/affiliate/woocommerce-currency-switcher" target="_blank">', '</a>') ?></div>

        <div class="woocs__alert woocs__alert-success">
        <table class="woocs_settings_promotion" >
            <tr>
                <td>
                    <h6 class="woocs_red"><?php esc_html_e("UPGRADE to Full version", 'woocommerce-currency-switcher') ?>:</h6>
                    <a href="https://pluginus.net/affiliate/woocommerce-currency-switcher" target="_blank"><img src="<?php echo WOOCS_LINK ?>img/woocs_banner.png" alt="<?php esc_html_e("full version of the plugin", 'woocommerce-currency-switcher'); ?>" /></a>
                </td>

                <td>
                    <h6><?php esc_html_e("WOOF - WooCommerce Products Filter", 'woocommerce-currency-switcher') ?>:</h6>
                    <a href="https://pluginus.net/affiliate/woocommerce-products-filter" target="_blank"><img src="<?php echo WOOCS_LINK ?>img/woof_banner.png" alt="<?php esc_html_e("WOOF - WooCommerce Products Filter", 'woocommerce-currency-switcher'); ?>" /></a>
                </td>

                <td>
                    <h6><?php esc_html_e("WOOBE - WooCommerce Bulk Editor Professional", 'woocommerce-currency-switcher') ?>:</h6>
                    <a href="https://pluginus.net/affiliate/woocommerce-bulk-editor" target="_blank"><img src="<?php echo WOOCS_LINK ?>img/woobe_banner.png" alt="<?php esc_html_e("WOOBE - WooCommerce Bulk Editor Professional", 'woocommerce-currency-switcher'); ?>" /></a>
                </td>

                <td>
                    <h6><?php esc_html_e("WOOT - WooCommerce Active Products Tables", 'woocommerce-currency-switcher') ?>:</h6>
                    <a href="https://pluginus.net/affiliate/woot-woocommerce-products-tables" target="_blank"><img src="<?php echo WOOCS_LINK ?>img/woot_banner.png" alt="<?php esc_html_e("WOOT - WooCommerce Active Products Tables", 'woocommerce-currency-switcher'); ?>" /></a>
                </td>

            </tr>
        </table>
        </div>
        
    <?php endif; ?>


    <div class="info_popup woocs_settings_hide" ></div>

</div>


<?php

function woocs_print_currency($_this, $currency) {
    global $WOOCS;
    $is_etalon = intval($currency['is_etalon']);
    ?>
    <tr data-etalon="<?php echo $is_etalon ?>">
        <td data-title="<?php esc_html_e('Basic', 'woocommerce-currency-switcher') ?>">
            <label class="woocs-checkmark-label">
                <input class="woocs_is_etalon" type="radio" <?php checked(1, $is_etalon) ?> title="<?php esc_html_e("Set etalon main currency. This should be the currency in which the price of goods exhibited!", 'woocommerce-currency-switcher') ?>" />
                <input name="woocs_is_etalon[]" type="hidden" value="<?php echo $is_etalon; ?>" />
                <span class="woocs-checkmark"></span>
            </label>
        </td>

        <td data-title="<?php esc_html_e("Drag and Drop", 'woocommerce-currency-switcher') ?>">
            <a href="#" class="woocs__size-icon-large woocs_settings_move" title="<?php esc_html_e("Drag and Drop", 'woocommerce-currency-switcher'); ?>">
                <i class="uil uil-arrow-break"></i>
            </a>
        </td>

        <td data-title="<?php esc_html_e("Flag", 'woocommerce-currency-switcher'); ?>">
            <?php
            $flag = WOOCS_LINK . 'img/no_flag.png';
            if (isset($currency['flag']) AND!empty($currency['flag'])) {
                $flag = $currency['flag'];
            }
            ?>
            <input type="hidden" value="<?php echo esc_attr($flag) ?>" class="woocs_flag_input" name="woocs_flag[]" />
            <a href="#" class="woocs_flag woocs_settings_flag_tip" data-tip="<?php esc_html_e("Click to select the flag", 'woocommerce-currency-switcher'); ?>">
                <img src="<?php echo esc_attr($flag) ?>" alt="<?php esc_html_e("Flag", 'woocommerce-currency-switcher'); ?>" />
            </a>
        </td>

        <td data-title="<?php esc_html_e("Currency", 'woocommerce-currency-switcher') ?>" style="position: relative;">
            <input type="text" value="<?php echo esc_attr($currency['name']) ?>" name="woocs_name[]" class="woocs-name-input" placeholder="<?php esc_html_e("Code", 'woocommerce-currency-switcher') ?>" />
        </td>

        <td data-title="<?php esc_html_e("Symbol", 'woocommerce-currency-switcher') ?>">
            <select name="woocs_symbol[]" title="<?php esc_html_e("Currency symbols", 'woocommerce-currency-switcher') ?>">
                <?php foreach ($_this->currency_symbols as $symbol) : ?>
                    <option value="<?php echo md5($symbol) ?>" <?php selected(md5($currency['symbol']), md5($symbol)) ?>><?php echo esc_html($symbol); ?></option>
    <?php endforeach; ?>
            </select>
        </td>

        <td data-title="<?php esc_html_e("Position", 'woocommerce-currency-switcher') ?>">
            <select name="woocs_position[]" title="<?php esc_html_e('Select symbol position', 'woocommerce-currency-switcher') ?>">
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
                    <option value="<?php echo esc_attr($position) ?>" <?php selected($currency['position'], $position) ?>><?php echo esc_html($position_desc_sign) ?></option>
    <?php endforeach; ?>
            </select>
        </td>


        <td data-title="<?php esc_html_e("Rate+%", 'woocommerce-currency-switcher') ?>">
            <div class="woocs__table-card">
                <div class="woocs__table-card-flex">
                    <div class="woocs__table-card-rate">
                        <input type="text" <?php if ($is_etalon): ?>readonly=""<?php endif; ?> value="<?php echo esc_attr($currency['rate']) ?>" name="woocs_rate[]" placeholder="<?php esc_html_e("Exchange rate", 'woocommerce-currency-switcher') ?>" />
                        <span>&nbsp;+&nbsp;</span>
                        <input type="text" value="<?php echo (isset($currency['rate_plus']) ? ($currency['rate_plus'] > 0 ? $currency['rate_plus'] : '') : '') ?>" name="woocs_rate_plus[]" class="woocs-text woocs-rate-plus" placeholder="<?php esc_html_e('interest', 'woocommerce-currency-switcher') ?>" title="<?php esc_html_e("+ to your interest in the rate, example values: 0.15, 20%", 'woocommerce-currency-switcher') ?>" />
                    </div>
                    <button class="woocs__button woocs__size-icon-large woocs_get_fresh_rate" title="<?php esc_html_e("Press the button if you want to update currency rate!", 'woocommerce-currency-switcher') ?>">
                        <i class="uil uil-rotate-360"></i>
                    </button>
                </div>
            </div>
        </td>

        <td data-title="<?php esc_html_e("Decimal", 'woocommerce-currency-switcher') ?>">

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


        <td data-title="<?php esc_html_e("Visible", 'woocommerce-currency-switcher') ?>">
    <?php echo draw_switcher23('woocs_hide_on_front[]', boolval(isset($currency['hide_on_front']) ? $currency['hide_on_front'] : 0), '', '', true); ?>            
        </td>


        <td data-title="<?php esc_html_e("Cents", 'woocommerce-currency-switcher') ?>">
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


        <td data-title="<?php esc_html_e("Description", 'woocommerce-currency-switcher') ?>">
            <input type="text" value="<?php echo esc_attr($currency['description']) ?>" name="woocs_description[]" placeholder="<?php esc_html_e("description", 'woocommerce-currency-switcher') ?>" />
        </td>

        <td data-title="<?php esc_html_e("Remove", 'woocommerce-currency-switcher') ?>">
            <a href="#" class="woocs__size-icon-large woocs_del_currency" title="<?php esc_html_e("Remove", 'woocommerce-currency-switcher'); ?>">
                <i class="uil uil-trash"></i>
            </a>
        </td>


    </tr>

    <?php
}

function woocs_get_tooltip_icon() {
    return '<svg viewBox="0 0 27 27" xmlns="http://www.w3.org/2000/svg"><g fill="#6495ed" fill-rule="evenodd"><path d="M13.5 27C20.956 27 27 20.956 27 13.5S20.956 0 13.5 0 0 6.044 0 13.5 6.044 27 13.5 27zm0-2C7.15 25 2 19.85 2 13.5S7.15 2 13.5 2 25 7.15 25 13.5 19.85 25 13.5 25z"/><path d="M12.05 7.64c0-.228.04-.423.12-.585.077-.163.185-.295.32-.397.138-.102.298-.177.48-.227.184-.048.383-.073.598-.073.203 0 .398.025.584.074.186.05.35.126.488.228.14.102.252.234.336.397.084.162.127.357.127.584 0 .22-.043.412-.127.574-.084.163-.196.297-.336.4-.14.106-.302.185-.488.237-.186.053-.38.08-.584.08-.215 0-.414-.027-.597-.08-.182-.05-.342-.13-.48-.235-.135-.104-.243-.238-.32-.4-.08-.163-.12-.355-.12-.576zm-1.02 11.517c.134 0 .275-.013.424-.04.148-.025.284-.08.41-.16.124-.082.23-.198.313-.35.085-.15.127-.354.127-.61v-5.423c0-.238-.042-.43-.127-.57-.084-.144-.19-.254-.318-.332-.13-.08-.267-.13-.415-.153-.148-.024-.286-.036-.414-.036h-.21v-.95h4.195v7.463c0 .256.043.46.127.61.084.152.19.268.314.35.125.08.263.135.414.16.15.027.29.04.418.04h.21v.95H10.82v-.95h.21z"/></g></svg>';
}

function woocs_draw_tooltip($text) {
    echo '<span class="woocs-sd-tooltip-toggle" aria-label="' . $text . '">' . woocs_get_tooltip_icon() . '</span>';
}
