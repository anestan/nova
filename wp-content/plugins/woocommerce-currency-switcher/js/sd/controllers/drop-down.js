"use strict";
class WOOCS_SD_DD {
    constructor(demo_data, settings = {}) {
        this.container = document.getElementById('woocs-sd-dd-options');
        this.menu = JSON.parse(document.getElementById('woocs-sd-dd').getAttribute('data-menu'));
        this.create_nav();

        this.history = [];
        this.default_settings = {
            width: 500,
            img_pos: 1,
            max_open_height: 300,
            show_img: 1,
            width_p100: 0,
            scale: 100,
            description_font_size: 12,
            title_show: 1,
            title_value: '__CODE__',
            title_font: 'Arial',
            title_bold: 1,
            title_font_size: 14,
            title_color: '#000000',
            border_radius: 0,
            border_color: '#eeeeee',
            img_height: 38,
            img_vertival_pos: -3,
            show_description: 1,
            description_color: '#aaaaaa',
            description_font: 'Arial',
            background_color: '#fafafa',
            pointer_color: '#000000',
            divider_color: '#eeeeee',
            divider_size: 2,
            border_width: 1
        };

        this.settings = Object.assign(Object.assign({}, this.default_settings), settings);

        this.create_dd(demo_data);
        this.init_options();

        document.addEventListener('woocs-sd-ctrl-z', (ev) => {
            if (ev.detail.woocs_sd_dd === this) {
                //this.rollback();//TODO
            }
        });

        //need for correct visual in drop-down
        document.querySelector('.woocs_sd_title_value').dispatchEvent(new Event('change'));

        this.remember();
    }

    create_dd(demo_data) {
        let data = {
            options: demo_data,
            label: 'Select currency',
            selected: '',
            width: (this.settings.width ? this.settings.width : 500) + 'px',
            imgpos: this.settings.img_pos ? 'right' : 'left',
            //name: 'my_value', //hidden input name
            fusion: false, //use if wrap <select> to fuse titles by keys with options description here
            max_open_height: this.settings.max_open_height ? this.settings.max_open_height : 500, //max height (px) of opened drop-down when vertical scroll appears
        };

        this.selector = new Selectron23(document.querySelector('#selectron23-example'), data);
        this.selector.applyDesignSettings(this.settings);

        this.selector.onSelect = function () {
            console.log('drop down', this.value);
        };
    }

    init_options() {

        let groups = {};

        for (const [key, title] of Object.entries(this.menu)) {
            groups[key] = {
                label: title,
                elements: []
            };
        }

        let options = {
            width: {
                label: woocs_sd.lang.width,
                type: 'slider',
                min: 80,
                value: this.settings.width,
                max: 900,
                group: groups.general,
                callback: (data, element) => {
                    let val = data.to;
                    this.settings.width = val;

                    if (val >= document.getElementById('selectron23-example-container').offsetWidth - 2 * 11) {
                        this.selector.setWidth('100p');//to avoid layout break
                    } else {
                        this.selector.setWidth(val);
                    }

                    this.remember(element);
                },
                aftersetup: (wrap, elem) => {
                    if (this.settings.width_p100) {
                        wrap.style.display = 'none';
                    }
                }
            },
            width_p100: {
                label: woocs_sd.lang.width100p,
                type: 'switcher',
                state: this.settings.width_p100,
                group: groups.general,
                callback: (is, element) => {
                    this.settings.width_p100 = is;
                    if (is) {
                        this.selector.setWidth('100p');
                        document.querySelector('.woocs-sd-options-width').style.display = 'none';
                    } else {
                        this.selector.setWidth(this.settings.width);
                        document.querySelector('.woocs-sd-options-width').style.display = 'block';
                        window.dispatchEvent(new Event('resize'));
                    }

                    this.remember(element);
                }
            },
            scale: {
                label: woocs_sd.lang.scale,
                type: 'slider',
                min: 20,
                max: 100,
                value: this.settings.scale,
                group: groups.general,
                callback: (data, element) => {
                    this.settings.scale = data.to;
                    this.selector.setScale(data.to);
                    this.remember(element);
                }
            },
            show_title: {
                label: woocs_sd.lang.title_show,
                type: 'switcher',
                state: this.settings.title_show,
                group: groups.title,
                callback: (is, element) => {
                    this.settings.title_show = is;
                    this.selector.showTitle(is);

                    let hide_elements = ['title_value', 'title_font_size', 'title_bold', 'title_color', 'title_font'];

                    hide_elements.forEach((val) => {
                        document.querySelector(`.woocs-sd-options-${val}`).style.display = is ? 'block' : 'none';
                    });

                    if (is) {
                        window.dispatchEvent(new Event('resize'));
                    }

                    this.remember(element);
                }
            },
            title_value: {
                label: woocs_sd.lang.title_value,
                type: 'textinput',
                value: this.settings.title_value,
                group: groups.title,
                class: 'woocs_sd_title_value',
                callback: (value, element) => {
                    this.settings.title_value = value;

                    if (value.length === 0) {
                        value = '__CODE__';
                    }

                    this.selector.container.querySelectorAll('.selectron23-option-title').forEach((o) => {
                        let val = value;
                        val = val.replace(/__CODE__/gi, o.getAttribute('data-name'));
                        val = val.replace(/__SIGN__/gi, o.getAttribute('data-sign'));
                        val = val.replace(/__DESCR__/gi, o.getAttribute('data-desc'));
                        o.innerText = val;
                    });

                    //this.selector.setTitleValue(value);
                    this.remember(element);
                },
                aftersetup: (wrap, elem) => {
                    if (!this.settings.title_show) {
                        wrap.style.display = 'none';
                    }
                },
                tip: woocs_sd.lang.signs_using
            },
            title_font_size: {
                label: woocs_sd.lang.title_font_size,
                type: 'slider',
                min: 8,
                max: 48,
                value: this.settings.title_font_size,
                group: groups.title,
                callback: (data, element) => {
                    this.settings.title_font_size = data.to;
                    this.selector.setTitleFontSize(data.to);
                    this.remember(element);
                },
                aftersetup: (wrap, elem) => {
                    if (!this.settings.title_show) {
                        wrap.style.display = 'none';
                    }
                }
            },
            title_bold: {
                label: woocs_sd.lang.title_bold,
                type: 'switcher',
                state: this.settings.title_bold,
                group: groups.title,
                callback: (is, element) => {
                    this.settings.title_bold = is;
                    this.selector.setTitleBold(is);
                    this.remember(element);
                },
                aftersetup: (wrap, elem) => {
                    if (!this.settings.title_show) {
                        wrap.style.display = 'none';
                    }
                }
            },
            title_color: {
                label: woocs_sd.lang.title_color,
                type: 'color',
                value: this.settings.title_color,
                group: groups.title,
                callback: (color, element) => {
                    this.settings.title_color = color;
                    this.selector.setTitleColor(color);
                    this.remember(element);
                },
                aftersetup: (wrap, elem) => {
                    if (!this.settings.title_show) {
                        wrap.style.display = 'none';
                    }
                }
            },
            title_font: {
                label: woocs_sd.lang.title_font,
                type: 'textinput',
                value: this.settings.title_font,
                group: groups.title,
                callback: (font, element) => {
                    this.settings.title_font = font;
                    this.selector.setTitleFont(font);
                    this.remember(element);
                },
                aftersetup: (wrap, elem) => {
                    if (!this.settings.title_show) {
                        wrap.style.display = 'none';
                    }
                }
            },
            border_width: {
                label: woocs_sd.lang.border_width,
                type: 'slider',
                min: 0,
                max: 10,
                value: this.settings.border_width,
                group: groups.general,
                callback: (data, element) => {
                    this.settings.border_width = data.to;
                    this.selector.setBorderWidth(data.to);
                    this.remember(element);
                }
            },
            border_radius: {
                label: woocs_sd.lang.border_radius,
                type: 'slider',
                min: 0,
                max: 100,
                value: this.settings.border_radius,
                group: groups.general,
                callback: (data, element) => {
                    this.settings.border_radius = data.to;
                    this.selector.setBorderRadius(data.to);
                    this.remember(element);
                }
            },
            border_color: {
                label: woocs_sd.lang.border_color,
                type: 'color',
                value: this.settings.border_color,
                group: groups.general,
                callback: (value, element) => {
                    this.settings.border_color = value;
                    this.selector.setBorderColor(value);
                    this.remember(element);
                }
            },
            show_img: {
                label: woocs_sd.lang.show_flag,
                type: 'switcher',
                state: this.settings.show_img,
                group: groups.image,
                callback: (is, element) => {
                    this.settings.show_img = is;
                    this.selector.showImg(is);

                    if (is) {
                        document.querySelector('.woocs-sd-options-img_pos').style.display = 'block';
                        document.querySelector('.woocs-sd-options-img_height').style.display = 'block';
                        document.querySelector('.woocs-sd-options-img_vertival_pos').style.display = 'block';
                        window.dispatchEvent(new Event('resize'));
                    } else {
                        document.querySelector('.woocs-sd-options-img_pos').style.display = 'none';
                        document.querySelector('.woocs-sd-options-img_height').style.display = 'none';
                        document.querySelector('.woocs-sd-options-img_vertival_pos').style.display = 'none';
                    }

                    this.remember(element);
                }
            },
            img_pos: {
                label: woocs_sd.lang.flag_pos,
                type: 'dirswitcher',
                state: this.settings.img_pos,
                group: groups.image,
                callback: (is, element) => {
                    this.settings.img_pos = is;
                    this.selector.setImgSide(is);
                    this.remember(element);
                }, aftersetup: (wrap, elem) => {
                    if (!this.settings.show_img) {
                        wrap.style.display = 'none';
                    }
                }
            },
            img_height: {
                label: woocs_sd.lang.flag_height,
                type: 'slider',
                min: 5,
                max: 100,
                value: this.settings.img_height,
                group: groups.image,
                callback: (data, element) => {
                    this.settings.img_height = data.to;
                    this.selector.setImgHeight(data.to);
                    this.remember(element);
                }, aftersetup: (wrap, elem) => {
                    if (!this.settings.show_img) {
                        wrap.style.display = 'none';
                    }
                }
            },
            img_vertival_pos: {
                label: woocs_sd.lang.flag_v_pos,
                type: 'slider',
                min: -20,
                max: 30,
                value: this.settings.img_vertival_pos,
                group: groups.image,
                callback: (data, element) => {
                    this.settings.img_vertival_pos = data.to;
                    this.selector.setImgVPosition(data.to);
                    this.remember(element);
                }, aftersetup: (wrap, elem) => {
                    if (!this.settings.show_img) {
                        wrap.style.display = 'none';
                    }
                }
            },
            show_description: {
                label: woocs_sd.lang.show_description,
                type: 'switcher',
                state: this.settings.show_description,
                group: groups.description,
                callback: (is, element) => {
                    this.settings.show_description = is;
                    this.selector.showDescription(is);

                    if (is) {
                        document.querySelector('.woocs-sd-options-description_font_size').style.display = 'block';
                        document.querySelector('.woocs-sd-options-description_color').style.display = 'block';
                        document.querySelector('.woocs-sd-options-description_font').style.display = 'block';
                        window.dispatchEvent(new Event('resize'));
                    } else {
                        document.querySelector('.woocs-sd-options-description_font_size').style.display = 'none';
                        document.querySelector('.woocs-sd-options-description_color').style.display = 'none';
                        document.querySelector('.woocs-sd-options-description_font').style.display = 'none';
                    }

                    this.remember(element);
                }
            },
            description_font_size: {
                label: woocs_sd.lang.description_font_size,
                type: 'slider',
                min: 8,
                max: 48,
                value: this.settings.description_font_size,
                group: groups.description,
                callback: (data, element) => {
                    this.settings.description_font_size = data.to;
                    this.selector.setDescriptionTextSize(data.to);
                    this.remember(element);
                },
                aftersetup: (wrap, elem) => {
                    if (!this.settings.show_description) {
                        wrap.style.display = 'none';
                    }
                }
            },
            description_color: {
                label: woocs_sd.lang.description_color,
                type: 'color',
                value: this.settings.description_color,
                group: groups.description,
                callback: (color, element) => {
                    this.settings.description_color = color;
                    this.selector.setDescriptionColor(color);
                    this.remember(element);
                },
                aftersetup: (wrap, elem) => {
                    if (!this.settings.show_description) {
                        wrap.style.display = 'none';
                    }
                }
            },
            description_font: {
                label: woocs_sd.lang.description_font,
                type: 'textinput',
                value: this.settings.description_font,
                group: groups.description,
                callback: (font, element) => {
                    this.settings.description_font = font;
                    this.selector.setDescriptionFont(font);
                    this.remember(element);
                },
                aftersetup: (wrap, elem) => {
                    if (!this.settings.show_description) {
                        wrap.style.display = 'none';
                    }
                }
            },
            background_color: {
                label: woocs_sd.lang.bg_color,
                type: 'color',
                value: this.settings.background_color,
                group: groups.general,
                callback: (color, element) => {
                    this.settings.background_color = color;
                    this.selector.setBackgroundColor(color);
                    this.remember(element);
                }
            },
            pointer_color: {
                label: woocs_sd.lang.pointer_color,
                type: 'color',
                value: this.settings.pointer_color,
                group: groups.general,
                callback: (color, element) => {
                    this.settings.pointer_color = color;
                    this.selector.setPointerColor(color);
                    this.remember(element);
                }
            },
            divider_color: {
                label: woocs_sd.lang.divider_color,
                type: 'color',
                value: this.settings.divider_color,
                group: groups.general,
                callback: (color, element) => {
                    this.settings.divider_color = color;
                    this.selector.setContainerBgColor(color);
                    this.remember(element);
                }
            },
            divider_size: {
                label: woocs_sd.lang.divider_size,
                type: 'slider',
                min: 0,
                max: 10,
                value: this.settings.divider_size,
                group: groups.general,
                callback: (data, element) => {
                    this.settings.divider_size = data.to;
                    this.selector.setDividerSize(data.to);
                    this.remember(element);
                }
            },
            max_open_height: {
                label: woocs_sd.lang.max_opheight,
                type: 'slider',
                min: 50,
                max: 1000,
                value: this.settings.max_open_height,
                group: groups.general,
                callback: (data, element) => {
                    this.settings.max_open_height = data.to;
                    this.selector.setMaxOpenHeight(data.to);
                    this.remember(element);
                }
            }
        };
        this.options = new WOOCS_SD_OPT(options, this.container);
        this.options.draw(groups);
    }

    reset() {
        this.settings = Object.assign({}, this.default_settings);
        this.selector.applyDesignSettings(this.settings);
        this.init_options();
        //need for correct visual in drop-down
        document.querySelector('.woocs_sd_title_value').dispatchEvent(new Event('change'));

        this.history = [];
    }

    remember(element = null) {
        console.log('remember');

        if (element /* && element instanceof Ranger23 */) {
            if (element.history_timer) {
                clearTimeout(element.history_timer);
            }

            element.history_timer = setTimeout(() => this.remember(), 777);
            return;
        }

        this.history.push(Object.assign({}, this.settings));
    }

    rollback() {
        console.log('rollback');

        if (this.history.length === 1) {
            const compareObj = (objects) => {
                const res = objects.map((item) => {
                    return Object.entries(item).flat().join();
                });
                return res.every((a) => {
                    return a === res[0];
                });
            };


            if (!compareObj([this.settings, this.history[0]])) {
                this.settings = Object.assign({}, this.history[0]);
            } else {
                return;
            }
        }

        if (this.history.length === 2) {
            this.history = this.history.slice(0, 1);
            this.settings = Object.assign({}, this.history[0]);
        }

        if (this.history.length > 2) {
            let tmp = this.history.reverse();

            this.settings = Object.assign({}, tmp[1]);
            this.history = tmp.slice(1).reverse();
        }

        this.selector.applyDesignSettings(this.settings);
        this.init_options();
    }

    create_nav() {
        let nav = document.getElementById('woocs-sd-nav');
        if (!nav) {
            nav = document.createElement('div');
            nav.setAttribute('id', 'woocs-sd-nav');

            for (const [key, title] of Object.entries(this.menu)) {
                let div = document.createElement('div');
                let a = document.createElement('a');
                a.setAttribute('href', '#');
                a.innerText = title;
                a.addEventListener('click', (e) => {
                    e.preventDefault();
                    document.querySelector(`#woocs-sd-section-${key}`).scrollIntoView({behavior: 'smooth'});
                    return false;
                });

                div.appendChild(a);
                nav.appendChild(div);
            }


            document.getElementById('selectron23-example-container').appendChild(nav);

        }
    }
}

