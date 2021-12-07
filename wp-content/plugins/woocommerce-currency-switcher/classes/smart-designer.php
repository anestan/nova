<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WOOCS_SMART_DESIGNER {

    public function __construct() {
        add_action('wp_ajax_woocs_sd_create', array($this, 'create'));
        add_action('wp_ajax_woocs_sd_delete', function() {
            $id = intval($_REQUEST['id']);
            delete_option('woocs_sd_' . $id);
            $designs = $this->get_designs();
            if (($key = array_search($id, $designs, true)) !== false) {
                unset($designs[$key]);
            }
            update_option('woocs_sd', $designs);
        });
        add_action('wp_ajax_woocs_sd_save', array($this, 'save'));
        add_action('wp_ajax_woocs_sd_get', function() {
            die(json_encode($this->get(intval($_REQUEST['id']))));
        });

        add_action('admin_enqueue_scripts', function() {
            if (isset($_GET['tab']) AND $_GET['tab'] == 'woocs') {

                wp_enqueue_style('wp-color-picker');
                wp_enqueue_script('wp-color-picker');

                wp_enqueue_style('woocs-sd', WOOCS_LINK . 'css/sd/styles.css', [], WOOCS_VERSION);
                wp_enqueue_style('woocs-sd-selectron23', WOOCS_LINK . 'css/sd/selectron23.css', [], WOOCS_VERSION);
                wp_enqueue_style('woocs-sd-ranger-23', WOOCS_LINK . 'css/sd/ranger-23.css', [], WOOCS_VERSION);

                wp_enqueue_script('woocs-sd-selectron23', WOOCS_LINK . 'js/sd/selectron23.js', [], WOOCS_VERSION);
                wp_enqueue_script('woocs-sd-ranger-23', WOOCS_LINK . 'js/sd/ranger-23.js', [], WOOCS_VERSION);
                wp_enqueue_script('woocs-sd-switcher23', WOOCS_LINK . 'js/sd/switcher23.js', [], WOOCS_VERSION);
                wp_enqueue_script('woocs-sd-options', WOOCS_LINK . 'js/sd/options.js', [], WOOCS_VERSION);
                wp_enqueue_script('woocs-sd-drop-down', WOOCS_LINK . 'js/sd/controllers/drop-down.js', [], WOOCS_VERSION);

                wp_enqueue_script('woocs-sd', WOOCS_LINK . 'js/sd/smart-designer.js', ['woocs-sd-drop-down'], WOOCS_VERSION);

                wp_localize_script('woocs-sd', 'woocs_sd', [
                    'lang' => [
                        'loading' => esc_html__('Loading ...', 'woocommerce-currency-switcher'),
                        'loaded' => esc_html__('Loaded!', 'woocommerce-currency-switcher'),
                        'smth_wrong' => esc_html__('Something wrong!', 'woocommerce-currency-switcher'),
                        'saving' => esc_html__('saving ...', 'woocommerce-currency-switcher'),
                        'saved' => esc_html__('Saved!', 'woocommerce-currency-switcher'),
                        'are_you_sure' => esc_html__('Are you sure?', 'woocommerce-currency-switcher'),
                        'created' => esc_html__('Created!', 'woocommerce-currency-switcher'),
                        'creating' => esc_html__('Creating ...', 'woocommerce-currency-switcher'),
                        'select_currency' => esc_html__('Select currency', 'woocommerce-currency-switcher'),
                        'width' => esc_html__('Width', 'woocommerce-currency-switcher'),
                        'width100p' => esc_html__('Width 100%', 'woocommerce-currency-switcher'),
                        'scale' => esc_html__('Scale', 'woocommerce-currency-switcher'),
                        'description_font_size' => esc_html__('Description font size', 'woocommerce-currency-switcher'),
                        'title_show' => esc_html__('Show title', 'woocommerce-currency-switcher'),
                        'title_value' => esc_html__('Title value', 'woocommerce-currency-switcher'),
                        'title_font_size' => esc_html__('Title font size', 'woocommerce-currency-switcher'),
                        'title_color' => esc_html__('Title color', 'woocommerce-currency-switcher'),
                        'title_font' => esc_html__('Title font', 'woocommerce-currency-switcher'),
                        'border_radius' => esc_html__('Border radius', 'woocommerce-currency-switcher'),
                        'border_color' => esc_html__('Border color', 'woocommerce-currency-switcher'),
                        'show_flag' => esc_html__('Show flag', 'woocommerce-currency-switcher'),
                        'flag_pos' => esc_html__('Flag position', 'woocommerce-currency-switcher'),
                        'flag_height' => esc_html__('Flag height', 'woocommerce-currency-switcher'),
                        'flag_v_pos' => esc_html__('Flag vertical position', 'woocommerce-currency-switcher'),
                        'show_description' => esc_html__('Show description', 'woocommerce-currency-switcher'),
                        'description_color' => esc_html__('Description color', 'woocommerce-currency-switcher'),
                        'description_font' => esc_html__('Description font', 'woocommerce-currency-switcher'),
                        'bg_color' => esc_html__('Background color', 'woocommerce-currency-switcher'),
                        'pointer_color' => esc_html__('Pointer color', 'woocommerce-currency-switcher'),
                        'divider_color' => esc_html__('Options divider color', 'woocommerce-currency-switcher'),
                        'divider_size' => esc_html__('Options divider size', 'woocommerce-currency-switcher'),
                        'border_width' => esc_html__('Border width', 'woocommerce-currency-switcher'),
                        'max_opheight' => esc_html__('Max open height', 'woocommerce-currency-switcher'),
                        'title_bold' => esc_html__('Title Bold', 'woocommerce-currency-switcher'),
                        'deleting' => esc_html__('Deleting ...', 'woocommerce-currency-switcher'),
                        'deleted' => esc_html__('Deleted!', 'woocommerce-currency-switcher'),
                        'delete' => esc_html__('delete', 'woocommerce-currency-switcher'),
                        'edit' => esc_html__('edit', 'woocommerce-currency-switcher'),
                        'signs_using' => esc_html__('Use special keywords and their combination: __CODE__, __SIGN__, __DESCR__. Also you can use usual static text. Example: __CODE__ - __SIGN__', 'woocommerce-currency-switcher'),
                    ]]
                );
            }
        });
    }

    public function get_designs() {
        $res = get_option('woocs_sd', []);

        if (empty($res)) {
            $res = [];
        }

        return $res;
    }

    //ajax
    public function create() {
        $designs = $this->get_designs();

        if (empty($designs)) {
            $id = 1;
        } else {
            //$id = max($designs) + 1;
            $id = intval(get_option('woocs_sd_max')) + 1;
        }

        add_option('woocs_sd_' . $id, []);
        $designs[] = $id;
        update_option('woocs_sd', $designs);
        update_option('woocs_sd_max', $id);
        die("" . $id);
    }

    //ajax
    public function save() {
        $data = json_decode(stripslashes($_REQUEST['options']), true);
        update_option('woocs_sd_' . intval($_REQUEST['id']), $data);
    }

    public function get($id) {
        if (get_option('woocs_sd_' . $id, -1) === -1) {
            return -1;
        }

        return get_option('woocs_sd_' . $id, []);
    }

    public function get_currencies() {
        global $WOOCS;
        $all_currencies = apply_filters('woocs_currency_manipulation_before_show', $WOOCS->get_currencies());

        if (!empty($all_currencies)) {
            foreach ($all_currencies as $key => $currency) {
                if (isset($currency['hide_on_front']) AND $currency['hide_on_front']) {
                    unset($all_currencies[$key]);
                }
            }
        }

        return array_map(function($c) {
            $title = apply_filters('woocs_currname_in_option', $c['name']);
            return [
                'value' => $c['name'],
                'sign' => $c['symbol'],
                'title' => $title,
                'text' => $c['description'],
                'img' => $c['flag'],
                'title_attributes' => [
                    'data-sign' => $c['symbol'],
                    'data-name' => $title,
                    'data-desc' => $c['description']
                ]
            ];
        }, array_values($all_currencies));
    }

}

$GLOBALS['WOOCS_SD'] = new WOOCS_SMART_DESIGNER();

