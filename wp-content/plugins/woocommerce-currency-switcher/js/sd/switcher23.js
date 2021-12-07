/**
 * @summary     Switcher23
 * @description switcher
 * @version     1.0.0
 * @file        switcher23
 * @author      realmag777
 * @contact     https://pluginus.net/contact-us/
 * @github      https://github.com/realmag777/switcher23
 * @copyright   Copyright 2021 Rostislav Sofronov
 *
 * This source file is free software, available under the following license:
 *   MIT license - https://en.wikipedia.org/wiki/MIT_License .Basically that
 * means you are free to use Selectron23 as long as this header is left intact.
 */

'use strict';

class Switcher23 {
    constructor(name, value, event, type = 'switcher') {
        this.value = parseInt(value);
        this.name = name;
        this.event = event;
        this.type = type;
        this.draw();
        this.init_action();
    }

    init_action() {
        this.checkbox.addEventListener('change', (e) => {
            e.stopPropagation();
            this.value = this.value ? 0 : 1;
            this.checkbox.setAttribute('checked', Boolean(this.value));
            this.checkbox.value = this.value;
            this.hidden.setAttribute('value', this.value);
            this.call();
            return false;
        });
    }

    draw() {
        let id = this.create_id('sw');
        this.container = document.createElement('div');
        this.container.classList.add('switcher23-container');

        this.hidden = document.createElement('input');
        this.hidden.setAttribute('type', 'hidden');
        this.hidden.setAttribute('value', this.value);

        this.checkbox = document.createElement('input');

        for (const [key, value] of Object.entries({
            type: 'checkbox',
            id: id,
            class: 'switcher23',
            value: this.value,
            name: this.name
        })) {
            this.checkbox.setAttribute(key, value);
        }


        if (Boolean(this.value)) {
            this.checkbox.setAttribute('checked', true);
        }

        if (this.event) {
            this.checkbox.setAttribute('data-event', this.event);
        }

        let label = document.createElement('label');
        label.setAttribute('for', id);
        label.setAttribute('class', 'switcher23-toggle');
        if (this.type === 'dirswitcher') {
            label.classList.add('switcher23-toggle-dir');
        }
        label.innerHTML = '<span></span>';

        this.container.appendChild(this.hidden);
        this.container.appendChild(this.checkbox);
        this.container.appendChild(label);

    }

    create_id(prefix = '') {
        return prefix + Math.random().toString(36).substring(7);
    }

    call() {
        //API
    }
}
