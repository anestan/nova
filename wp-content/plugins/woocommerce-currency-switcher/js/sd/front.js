'use strict';
window.addEventListener('load', function () {

    let selectrons = document.querySelectorAll('div[data-woocs-sd]');

    if (selectrons.length > 0) {
        Array.from(selectrons).forEach(function (div) {
            div.innerHTML = '';
            let settings = JSON.parse(div.getAttribute('data-woocs-sd'));
            let options = JSON.parse(div.getAttribute('data-woocs-sd-currencies'));

            if (typeof settings.title_value !== 'undefined') {
                options.forEach(function (o, k) {
                    let title = settings.title_value;

                    if (title.length > 0) {
                        title = title.replace(/__CODE__/gi, o.title);
                        title = title.replace(/__SIGN__/gi, o.sign);
                        title = title.replace(/__DESCR__/gi, o.text);
                        options[k].title = title;
                    }
                });
            }

            settings.title_value = '';//!!important

            let data = {
                options: options,
                selected: woocs_current_currency.name,
                width: (settings.width ? settings.width : 500) + 'px',
                imgpos: typeof settings.img_pos !== 'undefined' ? (settings.img_pos ? 'right' : 'left') : 'right',
                max_open_height: settings.max_open_height ? settings.max_open_height : 300
            };

            let selectron = new Selectron23(div, data);
            selectron.applyDesignSettings(settings);

            selectron.onSelect = function () {
                woocs_redirect(this.value);
            };
        });
    }

});

