"use strict";
var woocs_sd_current_edit_id = 0;
var woocs_sd_dd = null;

window.addEventListener('load', function () {
    if (document.getElementById('woocs-sd-create')) {
        document.getElementById('woocs-sd-create').addEventListener('click', function (e) {
            e.preventDefault();

            woocs_show_info_popup(woocs_sd.lang.creating, 7777);
            jQuery.ajax({
                method: "POST",
                url: ajaxurl,
                data: {
                    action: 'woocs_sd_create'
                },
                success: function (id) {
                    let tr = document.createElement('tr');
                    tr.setAttribute('id', `woocs-sd-dashboard-tr-${id}`);
                    let td1 = document.createElement('td');
                    td1.innerText = id;
                  
                    let td3 = document.createElement('td');
                    td3.innerHTML = `<input type="text" value="[woocs sd=${id}]" readonly="">`;
                    let td4 = document.createElement('td');
                    td4.innerText = '';

                    let btn_edit = document.createElement('a');
                    btn_edit.setAttribute('href', `javascript: woocs_sd_edit(${id});void(0);`);
                    btn_edit.className = 'woocs-panel-button woocs__button woocs__button-small woocs__button-outline-success dashicons-before dashicons-update';
                    btn_edit.innerText = woocs_sd.lang.edit;

                    let btn_delete = document.createElement('a');
                    btn_delete.setAttribute('href', `javascript: woocs_sd_delete(${id});void(0);`);
                    btn_delete.className = 'woocs-panel-button woocs__button woocs__button-small woocs__button-outline-warning dashicons-before dashicons-dismiss';
                    btn_delete.innerText = woocs_sd.lang.delete;

                    td4.appendChild(btn_edit);
                    td4.appendChild(btn_delete);

                    tr.appendChild(td1);
                    tr.appendChild(td3);
                    tr.appendChild(td4);
                    document.getElementById('woocs-sd-table').querySelector('tbody').prepend(tr);
                    woocs_show_info_popup(woocs_sd.lang.created, 111);
                },
                error: function () {
                    alert(woocs_sd.lang.smth_wrong);
                }
            });

            return false;
        });
    }

    window.addEventListener('scroll', function (e) {
        if (document.getElementById('selectron23-example-container')) {
            let distanceY = window.pageYOffset || document.documentElement.scrollTop;
            let container = document.getElementById('selectron23-example-container');

            if (distanceY > document.getElementById('selectron23-example-container').offsetHeight) {
                container.classList.add('selectron23-example-container-fixed');
                let padding = 2 * 11;
                container.style.width = `calc(100% - ${padding}px)`;
            } else {
                container.classList.remove('selectron23-example-container-fixed');
                container.removeAttribute('style');
            }
        }
    });


});


function woocs_sd_edit(id) {
    woocs_show_info_popup(woocs_sd.lang.loading, 7777);
    jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
            action: 'woocs_sd_get',
            id: id
        },
        success: function (options) {
            woocs_show_info_popup(woocs_sd.lang.loaded, 111);

            woocs_sd_current_edit_id = id;
            let area = document.getElementById('woocs-sd-work-area');
            area.innerHTML = document.getElementById('woocs-sd-work-area-tpl').innerHTML;
            document.getElementById('woocs-sd-manage-area').style.display = 'none';
            document.getElementById('woocs-sd-work-area').style.display = 'block';

            woocs_sd_dd = new WOOCS_SD_DD(JSON.parse(document.getElementById('selectron23-example').getAttribute('data-woocs-sd-currencies')), JSON.parse(options));
            document.addEventListener('keydown', function (event) {
                if (event.ctrlKey && event.key === 'z' && woocs_sd_current_edit_id > 0) {

                    document.dispatchEvent(new CustomEvent('woocs-sd-ctrl-z', {detail: {
                            id: woocs_sd_current_edit_id,
                            woocs_sd_dd: woocs_sd_dd
                        }}));

                }
            });

            woocs_sd_init_tabs();
        },
        error: function () {
            alert(woocs_sd.lang.smth_wrong);
        }
    });
}

function woocs_sd_delete(id) {
    if (confirm(woocs_sd.lang.are_you_sure)) {
        woocs_show_info_popup(woocs_sd.lang.deleting, 7777);
        document.getElementById(`woocs-sd-dashboard-tr-${id}`).remove();
        jQuery.ajax({
            method: "POST",
            url: ajaxurl,
            data: {
                action: 'woocs_sd_delete',
                id: id
            },
            success: function () {
                woocs_show_info_popup(woocs_sd.lang.deleted, 111);
            },
            error: function () {
                alert(woocs_sd.lang.smth_wrong);
            }
        });
    }
}

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

function woocs_sd_init_tabs() {
    document.querySelectorAll('#woocs-sd-tabs a').forEach(function (tab) {
        tab.addEventListener('click', function (e) {
            e.preventDefault();
            woocs_sd_click_tab(this);
            return false;
        });
    });
}

function woocs_sd_click_tab(_this) {
    document.querySelectorAll('#woocs-sd-tabs a').forEach(function (a) {
        a.classList.remove('woocs-panel-button-selected');
    });

    document.querySelectorAll('.woocs-sd-panel').forEach(function (tp) {
        tp.classList.remove('woocs-sd-panel-current');
    });


    _this.classList.add('woocs-panel-button-selected');
    document.querySelector(_this.getAttribute('href')).classList.add('woocs-sd-panel-current');
}


function woocs_sd_save() {
    woocs_show_info_popup(woocs_sd.lang.saving, 7777);
    jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
            action: 'woocs_sd_save',
            id: woocs_sd_current_edit_id,
            options: JSON.stringify(woocs_sd_dd.settings)
        },
        success: function () {
            woocs_show_info_popup(woocs_sd.lang.saved, 111);
        },
        error: function () {
            alert(woocs_sd.lang.smth_wrong);
        }
    });
}

function woocs_sd_save_exit() {
    woocs_sd_save();
    woocs_sd_exit();
}

function woocs_sd_exit_no_save() {
    if (confirm(woocs_sd.lang.are_you_sure)) {
        woocs_sd_exit();
    }
}

function woocs_sd_exit() {
    document.getElementById('woocs-sd-manage-area').style.display = 'block';
    document.getElementById('woocs-sd-work-area').style.display = 'none';
    document.getElementById('woocs-sd-work-area').innerHTML = '';
    woocs_sd_current_edit_id = 0;
}

function woocs_sd_reset() {
    if (confirm(woocs_sd.lang.are_you_sure)) {
        woocs_sd_dd.reset();
    }
}


