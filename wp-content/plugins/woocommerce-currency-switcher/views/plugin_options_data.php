<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');

$aggregators = array(
    'yahoo' => 'www.finance.yahoo.com',
    //'google' => 'www.google.com/finance',
    'ecb' => 'www.ecb.europa.eu',
    'free_ecb' => 'The Free Currency Converter by European Central Bank',
    'micro' => 'www.ratesapi.io - published by European Central Bank',
    'rf' => 'www.cbr.ru - russian centrobank',
    'privatbank' => 'api.privatbank.ua - ukrainian privatbank',
    'natbank' => 'Ukrainian national bank',
    'bank_polski' => 'Narodowy Bank Polsky',
    'free_converter' => 'The Free Currency Converter',
    'fixer' => 'Fixer',
    'cryptocompare' => 'CryptoCompare',
    'ron' => 'www.bnr.ro',
    'currencylayer' => 'Сurrencylayer',
    'openexchangerates' => 'Open exchange rates',
);

$aggregators = apply_filters('woocs_announce_aggregator', $aggregators);

//***

global $WOOCS;

$welcome_curr_options = array();
if (!empty($currencies) AND is_array($currencies)) {
    foreach ($currencies as $key => $currency) {
        $welcome_curr_options[$currency['name']] = $currency['name'];
    }
}

//+++

$pd = array();
$countries = array();
if (class_exists('WC_Geolocation')) {
    $c = new WC_Countries();
    $countries = $c->get_countries();
    $pd = WC_Geolocation::geolocate_ip();
}

//+++

$storage_options = array(
    'session' => esc_html__('PHP Session', 'woocommerce-currency-switcher'),
    'transient' => esc_html__('Transient', 'woocommerce-currency-switcher')
);

if (class_exists('Memcached')) {
    $storage_options['memcached'] = 'Memcached';
}

if (class_exists('Redis')) {
    $storage_options['redis'] = 'Redis';
}


$woocs_is_payments_rule_enable = get_option('woocs_payments_rule_enabled', 0);

//+++

function draw_switcher23($name, $is_checked, $event = '', $label_title = '', $inversion = false) {
    $id = uniqid();
    $checked = 'n';
    $selected_value = 0;

    if ($is_checked) {
        $checked = 'checked';
        $selected_value = 1;
    }

    if ($inversion) {
        if ($checked == 'checked') {
            $checked = 'n';
        } else {
            $checked = 'checked';
        }
    }


    return '<div>' . draw_html_item('input', array(
                'type' => 'hidden',
                'name' => $name,
                'value' => $selected_value
            )) . draw_html_item('input', array(
                'type' => 'checkbox',
                'id' => $id,
                'class' => 'switcher23',
                'value' => $selected_value,
                $checked => $checked,
                //'data-page-id' => $page_id,
                'data-event' => $event,
                'data-inversion' => intval($inversion)
            )) . draw_html_item('label', array(
                'for' => $id,
                'title' => $label_title,
                'class' => 'switcher23-toggle woocs-toggle-switcher'
                    ), '<span></span>') . '</div>';
}

function draw_html_item($type, $data, $content = '') {
    $item = '<' . $type;
    foreach ($data as $key => $value) {
        $item .= " {$key}='{$value}'";
    }

    if (!empty($content) OR in_array($type, array('textarea'))) {
        $item .= '>' . $content . "</{$type}>";
    } else {
        $item .= ' />';
    }

    return $item;
}

function draw_select($data, $selected = '', $name = '', $id = '') {
    ob_start();
    ?>
    <select class="woocs-form-select woocs-form-control" name="<?php echo esc_attr($name) ?>" <?php if (!empty($id)): ?>id="<?php echo esc_attr($id) ?>"<?php endif; ?>>
            <?php foreach ($data as $key => $value) : ?>
                <option value="<?php echo esc_attr($key) ?>" <?php if ($selected === $key): ?>selected=""<?php endif; ?>><?php echo esc_html($value) ?></option>
            <?php endforeach; ?>
    </select>
    <?php
    return ob_get_clean();
}
