'use strict';

window.addEventListener('load', function () {

    jQuery.post(woocs_ajaxurl, {
        action: "woocs_get_products_price_html",
        products_ids: ''
    }, function (data) {
        data=JSON.parse(data);
        document.dispatchEvent(new CustomEvent('set_selectron23_value', {detail: {
                value: data.current_currency,
                selects: 'all'
            }}));
    });

});



