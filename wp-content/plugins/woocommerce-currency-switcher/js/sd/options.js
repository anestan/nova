"use strict";
class WOOCS_SD_OPT {
    constructor(options, container) {
        this.options = options;
        this.container = container;
    }

    draw(groups) {
        this.container.innerHTML = '';

        if (Object.keys(this.options).length > 0) {
            Object.entries(this.options).forEach(([key, data]) => {
                let wrap = document.createElement('div');
                wrap.className = 'woocs-sd-option woocs-sd-options-' + key;

                if (data.label) {
                    let label = document.createElement('div');
                    label.className = 'woocs-sd-label';
                    label.innerText = data.label + ':';

                    if (data.tip) {
                        let tip = document.createElement('span');
                        tip.className = 'woocs-sd-tooltip-toggle';
                        tip.setAttribute('aria-label', data.tip);
                        tip.setAttribute('tabindex', 0);
                        tip.innerHTML = this.get_tooltip_icon();
                        label.appendChild(tip);
                    }

                    wrap.appendChild(label);
                }

                switch (data.type) {
                    case 'slider':
                        var elem = document.createElement('div');
                        elem.className = 'ranger23-track woocs-sd-slider';

                        for (const [kk, vv] of Object.entries({
                            'data-key': key,
                            'data-min': data.min,
                            'data-max': data.max,
                            'data-selected-min': data.min,
                            'data-selected-max': (typeof data.value !== 'undefined' ? data.value : data.max)
                        })) {
                            elem.setAttribute(kk, vv);
                        }

                        var input = document.createElement('input');
                        input.setAttribute('type', 'number');
                        input.className = 'woocs-sd-slider-input';
                        input.value = typeof data.value !== 'undefined' ? data.value : data.max;
                        input.setAttribute('min', data.min);
                        input.setAttribute('max', data.max);

                        let slider = new Ranger23(elem, this.create_id('slider-'), 30, {
                            instant_cast: true,
                            disable_handler_left: true
                        });

                        document.addEventListener('ranger23-update', (e) => {
                            if (e.detail.cast_id === slider.cast_id) {
                                //let key = slider.track.getAttribute('data-key');
                                let from = parseInt(e.detail.from, 10);
                                let to = parseInt(e.detail.to, 10);

                                data.callback({from, to}, slider);
                                input.value = to;
                            }
                        });

                        wrap.appendChild(elem);

                        //+++

                        input.addEventListener('change', (e) => {
                            if (input.value < data.min) {
                                input.value = data.min;
                            }
                            if (input.value > data.max) {
                                input.value = data.max;
                            }
                            slider.set_right(input.value);
                        });

                        wrap.appendChild(input);

                        break;


                    case 'switcher':
                    case 'dirswitcher':
                        var switcher = new Switcher23(key, parseInt(data.state), '', data.type);
                        wrap.appendChild(switcher.container);
                        switcher.call = () => {
                            data.callback(switcher.value, switcher);
                        };
                        break;

                    case 'color':
                        var elem = document.createElement('input');
                        elem.setAttribute('type', 'color');
                        elem.className = 'color-picker';

                        var input = document.createElement('input');
                        input.setAttribute('type', 'text');
                        input.value = data.value;

                        elem.addEventListener('change', (e) => {
                            data.callback(elem.value, elem);
                            input.value = elem.value;
                        });

                        elem.addEventListener('input', (e) => {
                            data.callback(elem.value, elem);
                            input.value = elem.value;
                        });

                        input.addEventListener('keyup', (e) => {
                            data.callback(input.value, elem);
                            elem.value = input.value;
                        });

                        jQuery(elem).wpColorPicker();

                        wrap.appendChild(elem);
                        wrap.appendChild(input);
                        elem.value = data.value;
                        break;

                    case 'number':
                        var elem = document.createElement('input');
                        elem.setAttribute('type', 'number');
                        elem.value = data.value;

                        elem.addEventListener('change', (e) => {
                            data.callback(elem.value, elem);
                        });

                        elem.addEventListener('keyup', (e) => {
                            data.callback(elem.value, elem);
                        });

                        wrap.appendChild(elem);
                        break;


                    case 'textinput':
                        var elem = document.createElement('input');
                        elem.setAttribute('type', 'text');
                        elem.className = 'woocs-sd-text-input';
                        if (typeof data.class !== 'undefined') {
                            elem.classList.add(data.class);
                        }
                        elem.value = data.value;

                        elem.addEventListener('change', (e) => {
                            data.callback(elem.value, elem);
                        });

                        wrap.appendChild(elem);
                        break;

                    default:
                        console.log(key + ':', 'type is not supported');
                        break;
                }


                data.group.elements.push(wrap);

                //for example hide  
                if (data.aftersetup) {
                    data.aftersetup(wrap, elem);
            }
            });


            Object.keys(groups).forEach((key) => {
                let g = document.createElement('div');
                g.className = 'woocs-sd-group';
                g.setAttribute('id', `woocs-sd-section-${key}`);

                let header = document.createElement('header');
                header.innerText = groups[key].label;

                let curr_num = document.createElement('div');
                curr_num.innerText = '#' + woocs_sd_current_edit_id;
                curr_num.className = 'woocs-sd-section-header-num';
                header.appendChild(curr_num);

                let section = document.createElement('article');

                if (groups[key].elements.length > 0) {
                    groups[key].elements.forEach(function (elem) {
                        section.appendChild(elem);
                    });
                }

                g.appendChild(header);
                g.appendChild(section);
                this.container.appendChild(g);

            });

        }
    }

    create_id(prefix = '') {
        return prefix + Math.random().toString(36).substring(7);
    }

    get_tooltip_icon() {
        return '<svg viewBox="0 0 27 27" xmlns="http://www.w3.org/2000/svg"><g fill="#6495ed" fill-rule="evenodd"><path d="M13.5 27C20.956 27 27 20.956 27 13.5S20.956 0 13.5 0 0 6.044 0 13.5 6.044 27 13.5 27zm0-2C7.15 25 2 19.85 2 13.5S7.15 2 13.5 2 25 7.15 25 13.5 19.85 25 13.5 25z"/><path d="M12.05 7.64c0-.228.04-.423.12-.585.077-.163.185-.295.32-.397.138-.102.298-.177.48-.227.184-.048.383-.073.598-.073.203 0 .398.025.584.074.186.05.35.126.488.228.14.102.252.234.336.397.084.162.127.357.127.584 0 .22-.043.412-.127.574-.084.163-.196.297-.336.4-.14.106-.302.185-.488.237-.186.053-.38.08-.584.08-.215 0-.414-.027-.597-.08-.182-.05-.342-.13-.48-.235-.135-.104-.243-.238-.32-.4-.08-.163-.12-.355-.12-.576zm-1.02 11.517c.134 0 .275-.013.424-.04.148-.025.284-.08.41-.16.124-.082.23-.198.313-.35.085-.15.127-.354.127-.61v-5.423c0-.238-.042-.43-.127-.57-.084-.144-.19-.254-.318-.332-.13-.08-.267-.13-.415-.153-.148-.024-.286-.036-.414-.036h-.21v-.95h4.195v7.463c0 .256.043.46.127.61.084.152.19.268.314.35.125.08.263.135.414.16.15.027.29.04.418.04h.21v.95H10.82v-.95h.21z"/></g></svg>';
    }
}

