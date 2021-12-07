/**
 * @summary     Selectron23
 * @description drop-down
 * @version     1.0.3
 * @file        selectron23
 * @author      realmag777
 * @contact     https://pluginus.net/contact-us/
 * @github      https://github.com/realmag777/selectron23
 * @copyright   Copyright 2021 Rostislav Sofronov
 *
 * This source file is free software, available under the following license:
 *   MIT license - https://en.wikipedia.org/wiki/MIT_License .Basically that
 * means you are free to use Selectron23 as long as this header is left intact.
 */

'use strict';

class Selectron23 {
    constructor(element, data = {}) {
        this.data = data;

        this.scale = 1;
        Selectron23.z_index = 9999;

        this.el = document.createElement('div');
        this.el.className = 'selectron23';
        this.element = element;
        if (this.element.tagName.toLowerCase() === 'select') {
            this.element.insertAdjacentElement('afterend', this.el);
        } else {
            this.element.insertAdjacentElement('afterbegin', this.el);
        }

        if (this.element.tagName.toLowerCase() === 'select') {
            //select replacement
            this.element.style.display = 'none';
            if (this.element.hasAttribute('style')) {
                if (this.element.style.width) {
                    this.data.width = this.element.style.width;
                }
            }

            let selected = null;
            let options = this.element.querySelectorAll('option');
            if (options.length > 0) {
                let fusion = Boolean(this.data.fusion);

                let opt = [];
                options.forEach((o) => {
                    let d = {
                        value: o.value,
                        title: o.textContent
                    };

                    if (o.hasAttribute('data-img') && o.getAttribute('data-img').length > 0) {
                        d.img = o.getAttribute('data-img');
                    }

                    if (o.hasAttribute('data-text') && o.getAttribute('data-text').length > 0) {
                        d.text = o.getAttribute('data-text');
                    }

                    opt.push(d);

                    if (o.hasAttribute('selected')) {
                        selected = o.value;
                    }
                });

                //data fusion
                if (fusion) {
                    this.data.options.forEach((o) => {
                        opt = opt.map((op) => {
                            if (op.value.toString() === o.value.toString()) {
                                op = o;
                            }

                            return op;
                        });
                    });
                }

                //+++

                this.data.options = opt;

                if (selected !== null) {
                    this.data.selected = selected;
                }
            }

            //+++

            if (Object.keys(this.element.dataset).length > 0) {
                Object.keys(this.element.dataset).forEach((key) => {
                    this.data[key] = this.element.dataset[key];
                });
            }

        }

        //+++

        this.container = null;
        this.value = null;
        this.input = null;

        this._draw();
        this.el.querySelector('*').addEventListener('click', ev => this._click(ev));
        document.addEventListener('click', ev => this.show(false));
        document.addEventListener('set_selectron23_value', ev => {
            let can = false;

            if (ev.detail.selects === 'all') {
                can = true;
            }

            if (can) {
                this.select(ev.detail.value, false);
            }

        });
        return this;
    }

    _draw() {

        if (this.data.options.length > 0) {

            this.container = document.createElement('div');
            this.container.className = 'selectron23-container';
            this.el.appendChild(this.container);
            this.container.setAttribute('data-opened', 0);

            this.data.options.forEach((o) => {
                this.append(o);
            });

            this.pointer = document.createElement('span');
            this.pointer.setAttribute('data-pointer', 1);
            this.container.appendChild(this.pointer);
        }

        if (typeof this.data.selected !== 'undefined') {
            this.select(this.data.selected);
        } else {
            if (typeof this.data.label !== 'undefined' && this.data.label.length > 0) {
                let option = document.createElement('div');
                option.setAttribute('data-label', 1);
                option.className = 'selectron23-option';
                option.innerHTML = `<div>${this.data.label}</div>`;
                this.container.insertAdjacentElement('afterbegin', option);
            } else {
                this.container.querySelector('div').setAttribute('data-selected', 1);
            }
        }

        if (typeof this.data.width !== 'undefined') {
            //this.container.style.width = this.data.width;
            this.el.style.flexBasis = this.el.style.width = this.data.width;
        }

        if (typeof this.data.name !== 'undefined') {
            this.input = document.createElement('input');
            this.input.setAttribute('type', 'hidden');
            this.input.setAttribute('name', this.data.name);
            this.input.setAttribute('value', '');
            if (this.container.querySelector('div').hasAttribute('data-value')) {
                this.input.setAttribute('value', this.container.querySelector('div').getAttribute('data-value'));
            }
            this.el.appendChild(this.input);
        }

        this._normalize_min_height();
    }

    append(data) {
        if (!this.container.querySelector(`[data-value="${data.value}"]`)) {
            let option = this._create_option(data);
            this.container.appendChild(option);
            return true;
        }

        return false;
    }

    _create_option(data) {
        let option = document.createElement('div');
        option.setAttribute('data-value', data.value);
        option.className = 'selectron23-option';

        let float = 'left';
        if (typeof this.data.imgpos !== 'undefined') {
            if (this.data.imgpos === 'right') {
                float = 'right';
            }
        }

        let title = '';
        if (data.title) {
            title = `<div class="selectron23-option-title">${data.title}</div>`;
        }

        let text = '';
        if (data.text) {
            text = `<div class="selectron23-option-text">${data.text}</div>`;
        }

        let img = '';
        if (data.img) {
            let margin_top = 0;
            if (!text) {
                margin_top = 'margin-top: -6px;';
            }
            img = `<img src='${data.img}' alt='' loading='lazy' class='selectron23-img' style="float: ${float}; ${margin_top}" />`;
        }

        option.innerHTML = `${img}<div>${title}${text}</div>`;

        if (typeof data.title_attributes !== 'undefined' && Object.keys(data.title_attributes).length > 0) {
            for (const [kk, vv] of Object.entries(data.title_attributes)) {
                option.querySelector('.selectron23-option-title').setAttribute(kk, vv);
            }
        }

        if (img) {
            //option.querySelector('img').style.maxHeight = parseInt(option.offsetHeight - option.offsetHeight * 0.25) + 'px';
            option.querySelector('img').style.maxHeight = '38px';
        }

        return option;
    }

    select(value, call_event = true) {
        if (this.value !== value) {

            if (!this.container.querySelector(`[data-value="${value}"]`)) {
                return;
            }

            this.value = value;
            this._remove_label();

            let option = this.container.querySelector(`[data-value="${value}"]`);
            option.setAttribute('data-selected', 1);
            this.container.insertAdjacentElement('afterbegin', option);

            this.data.options.reverse().forEach((o) => {
                if (o.value !== value) {
                    let opt = this.container.querySelector(`[data-value="${o.value}"]`);
                    option.insertAdjacentElement('afterend', opt);
                    opt.removeAttribute('data-selected');
                }
            });

            this._normalize_min_height();
            if (this.input) {
                this.input.value = value;
            }

            if (call_event) {
                this.onSelect();
            }

            return true;
        }

        return false;
    }

    _remove_label() {
        if (this.container.querySelector(`[data-label="1"]`)) {
            this.container.querySelector(`[data-label="1"]`).remove();
        }
    }

    _click(ev) {

        let target = ev.target;

        if (!target.hasAttribute('data-value') && !target.hasAttribute('data-pointer')) {
            target = target.closest('.selectron23-option');
        }

        if (!target) {
            this.show(false);
            return;
        }

        if (target.hasAttribute('data-pointer') || target.hasAttribute('data-label')
                || target.hasAttribute('data-selected')) {

            if (parseInt(this.container.getAttribute('data-opened')) === 1) {
                this.show(false);
            } else {
                this.show(true);
            }
        } else {
            this.select(target.getAttribute('data-value'));
            this.show(false);
            if (this.element.tagName.toLowerCase() === 'select') {
                this.element.value = this.value;
                this.element.dispatchEvent(new Event("change"));
            }
        }

    }

    show(is, dir = 'down') {
        if (is) {
            if (parseInt(this.container.getAttribute('data-opened'))) {
                return;
            }

            this.container.style.zIndex = ++Selectron23.z_index;//to avoid css issue with anothers selectron23 drop-downs

            //animation
            let counter = 1;
            let timer = setInterval(() => {
                let max_height = 0;

                if (typeof this.data.max_open_height !== 'undefined' && this.data.max_open_height > 0) {
                    max_height = parseInt(this.data.max_open_height);
                } else {
                    this.container.querySelectorAll('.selectron23-option').forEach(function (item) {
                        max_height += item.offsetHeight;
                    });
                }


                //growing
                this.container.style.maxHeight = parseFloat(0.05 * counter) * max_height + 'px';

                if (parseInt(this.container.style.maxHeight) >= max_height) {
                    clearInterval(timer);
                    if (typeof this.data.max_open_height !== 'undefined') {
                        this.container.style.maxHeight = max_height + 'px';
                        this.container.style.overflow = 'auto';
                    } else {
                        this.container.style.maxHeight = '100vh';
                    }
                    this.container.setAttribute('data-opened', 1);
                    this.pointer.style.top = (parseInt(this.pointer.style.top) + 1) + 'px';
                }
                counter++;
            }, 10);
        } else {
            if (!parseInt(this.container.getAttribute('data-opened'))) {
                return;
            }

            this.container.setAttribute('data-opened', 0);
            this.pointer.style.top = (parseInt(this.pointer.style.top) - 1) + 'px';

            this._normalize_min_height();
            this.container.style.maxHeight = this.container.style.minHeight;
            if (typeof this.container.style.overflow !== 'undefined') {
                this.container.scrollTop = 0;//!!
                this.container.style.overflow = null;
            }
    }
    }

    _normalize_min_height() {
        this.container.style.minHeight = this.container.querySelector('div').clientHeight + 'px';
        this.el.style.height = (parseInt(this.container.style.minHeight) + 1) + 'px';//!!
        this.pointer.style.top = (parseInt(this.container.style.minHeight) / 2) - 2 + 'px';
        //this.el.style.maxHeight = window.getComputedStyle(this.el, null).getPropertyValue('min-height');
    }

    setWidth(width) {
        if (width === '100p') {
            this.el.style.flexBasis = this.el.style.width = this.element.style.width = '100%';
        } else {
            this.el.style.flexBasis = this.el.style.width = this.element.style.width = width + 'px';
        }
    }

    setScale(height) {
        let scale = parseFloat(height / 100);
        this.el.style.transform = `scale(${scale})`;
    }

    setDescriptionTextSize(size) {
        this.el.querySelectorAll('.selectron23-option-text').forEach((option) => {
            option.style.fontSize = size + 'px';
        });

        this._normalize_min_height();
    }

    setTitleFontSize(size) {
        this.el.querySelectorAll('.selectron23-option-title').forEach((option) => {
            option.style.fontSize = size + 'px';
        });

        this._normalize_min_height();
    }

    setTitleValue(value) {
        this.el.querySelectorAll('.selectron23-option-title').forEach((option) => {
            if (value.length > 0) {
                option.innerText = value;
            }
        });
    }

    setTitleFont(font) {
        this.el.querySelectorAll('.selectron23-option-title').forEach((option) => {
            option.style.fontFamily = font;
        });

        this._normalize_min_height();
    }

    setDescriptionFont(font) {
        this.el.querySelectorAll('.selectron23-option-text').forEach((option) => {
            option.style.fontFamily = font;
        });

        this._normalize_min_height();
    }

    setBorderRadius(radius) {
        let scale = parseFloat(radius / 100);
        this.container.style.borderRadius = (parseInt(this.container.style.minHeight) / 2 * scale) + 'px';
    }

    showImg(is) {
        this.el.querySelectorAll('.selectron23-img').forEach((option) => {
            option.style.display = is ? 'inline-block' : 'none';
        });

        this._normalize_min_height();
    }

    setImgVPosition(margin) {
        this.el.querySelectorAll('.selectron23-img').forEach((option) => {
            option.style.marginTop = margin + 'px';
        });

        this._normalize_min_height();
    }

    setImgHeight(height) {
        this.el.querySelectorAll('.selectron23-img').forEach((option) => {
            option.style.height = height + 'px';
            option.style.maxHeight = height + 'px';
        });

        this._normalize_min_height();
    }

    setImgSide(side) {
        this.el.querySelectorAll('.selectron23-img').forEach((option) => {
            option.style.float = side ? 'right' : 'left';
        });
    }

    showDescription(is) {
        this.el.querySelectorAll('.selectron23-option-text').forEach((option) => {
            if (is) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });

        this._normalize_min_height();
    }

    showTitle(is) {
        this.el.querySelectorAll('.selectron23-option-title').forEach((option) => {
            if (is) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });

        this._normalize_min_height();
    }

    setBorderColor(value) {
        this.container.style.borderColor = value;
    }

    setDescriptionColor(value) {
        this.el.querySelectorAll('.selectron23-option-text').forEach((option) => {
            option.style.color = value;
        });
    }

    setTitleColor(value) {
        this.el.querySelectorAll('.selectron23-option-title').forEach((option) => {
            option.style.color = value;
        });
    }

    setTitleBold(value) {
        this.el.querySelectorAll('.selectron23-option-title').forEach((option) => {
            option.style.fontWeight = value ? 'bold' : 'normal';
        });

        this._normalize_min_height();
    }

    setBackgroundColor(value) {
        this.el.querySelectorAll('.selectron23-option').forEach((option) => {
            option.style.background = value;
        });
    }

    setPointerColor(value) {
        //this.pointer.style.borderColor = value;
        this.pointer.style.color = value;
    }

    setContainerBgColor(value) {
        this.container.style.background = value;
    }

    setDividerSize(value) {
        this.el.querySelectorAll('.selectron23-option').forEach((option) => {
            option.style.marginBottom = value + 'px';
        });

        this._normalize_min_height();
    }

    setBorderWidth(value) {
        this.container.style.borderWidth = value + 'px';
    }

    setMaxOpenHeight(value) {
        this.data.max_open_height = value;
        this._normalize_min_height();
    }

    applyDesignSettings(settings) {
        for (const [key, value] of Object.entries(settings)) {
            switch (key) {
                case 'width':
                    this.setWidth(value);
                    if (settings.width_p100) {
                        this.setWidth('100p');
                    }
                    break;

                case 'img_pos':
                    this.setImgSide(value);
                    break;

                case 'max_open_height':
                    this.setMaxOpenHeight(value);
                    break;

                case 'show_img':
                    this.showImg(value);
                    break;

                case 'scale':
                    this.setScale(value);
                    break;

                case 'description_font_size':
                    this.setDescriptionTextSize(value);
                    break;

                case 'title_show':
                    this.showTitle(value);
                    break;

                case 'title_font_size':
                    this.setTitleFontSize(value);
                    break;

                case 'title_color':
                    this.setTitleColor(value);
                    break;

                case 'title_bold':
                    this.setTitleBold(value);
                    break;

                case 'title_font':
                    this.setTitleFont(value);
                    break;

                case 'title_value':
                    this.setTitleValue(value);
                    break;

                case 'border_radius':
                    this.setBorderRadius(value);
                    break;

                case 'border_color':
                    this.setBorderColor(value);
                    break;

                case 'img_height':
                    this.setImgHeight(value);
                    break;

                case 'img_vertival_pos':
                    this.setImgVPosition(value);
                    break;

                case 'show_description':
                    this.showDescription(value);
                    break;

                case 'description_color':
                    this.setDescriptionColor(value);
                    break;

                case 'description_font':
                    this.setDescriptionFont(value);
                    break;


                case 'background_color':
                    this.setBackgroundColor(value);
                    break;

                case 'pointer_color':
                    this.setPointerColor(value);
                    break;

                case 'divider_color':
                    this.setContainerBgColor(value);
                    break;

                case 'divider_size':
                    this.setDividerSize(value);
                    break;

                case 'border_width':
                    this.setBorderWidth(value);
                    break;
            }
        }

        this._normalize_min_height();
    }

    onSelect() {
        //for API
    }

}

