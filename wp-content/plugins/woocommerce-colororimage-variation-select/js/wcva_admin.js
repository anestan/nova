var $guz = jQuery.noConflict();
(function( $guz ) {
    'use strict';

    $guz(function() {

        $guz('#wcva_woocommerce_global_activation').on('change',function(){

            if ($guz(this).prop('checked')) {
               $guz(this).closest("tr").next().show(200);
           } else {
               $guz(this).closest("tr").next().hide(200);
           }
       });

        $guz('#woocommerce_wcva_swatch_tooltip:not(:checked)').closest('tr').next().hide();
        //hide show "disable tooltip on iphone devices checkbox"
        $guz('#woocommerce_wcva_swatch_tooltip').change(function() {

            var wcvarow = $guz(this).closest('tr').next();

            if( $guz(this).is(':checked')) {
                wcvarow.show();
            } else {
                wcvarow.hide();
            }
        }); 


        $guz('#woocommerce_enable_shop_slider:not(:checked)').closest('tr').next().hide();
        //hide show "disable tooltip on iphone devices checkbox"
        $guz('#woocommerce_enable_shop_slider').change(function() {

            var wcvarow2 = $guz(this).closest('tr').next();

            if( $guz(this).is(':checked')) {
                wcvarow2.show();
            } else {
                wcvarow2.hide();
            }
        });

    
        var next_tr_to_shop_more         = $guz('#woocommerce_enable_shop_show_more').closest('tr').next();
    
        var second_next_to_shop_more     = $guz('#woocommerce_enable_shop_show_more').closest('tr').next().next();


        next_tr_to_shop_more.hide();

        second_next_to_shop_more.hide();

        
    
        $guz('#woocommerce_enable_shop_show_more').on('change',function() {

            if( $guz(this).is(':checked')) {
                next_tr_to_shop_more.show();
                second_next_to_shop_more.show();
            } else {
                next_tr_to_shop_more.hide();
                second_next_to_shop_more.hide();
            }
        }); 

    

        $guz(window).load(function() {

            if( $guz('#woocommerce_enable_shop_show_more').is(':checked')) {
                next_tr_to_shop_more.show();
                second_next_to_shop_more.show();
            } else {
                next_tr_to_shop_more.hide();
                second_next_to_shop_more.hide();
            }
        });


});


}) ( jQuery );