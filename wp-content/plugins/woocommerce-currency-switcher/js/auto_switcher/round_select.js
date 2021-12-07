"use strict";

jQuery(document).ready(function () {
    if (jQuery('.cd-stretchy-nav').length > 0) {
        var stretchyNavs = jQuery('.cd-stretchy-nav');

        stretchyNavs.each(function () {
            var stretchyNav = jQuery(this);
            var stretchyNavTrigger = stretchyNav.find('.cd-nav-trigger');

            stretchyNavTrigger.on('click', function (event) {
                event.preventDefault();
                stretchyNav.toggleClass('nav-is-visible');
            });

        });

        jQuery(document).on('click', function (event) {
            (!jQuery(event.target).is('.cd-nav-trigger') && !jQuery(event.target).is('.cd-nav-trigger span') && !jQuery(event.target).is('.woocs_current_text .flag_auto_switcher')) && stretchyNavs.removeClass('nav-is-visible');
        });
    }
});