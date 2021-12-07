
jQuery(function($){
    
    $(".coupon.button").click(function(){

        var code = $(this).siblings('input').val()
        var data = {
			security: wc_checkout_params.apply_coupon_nonce,
			coupon_code: code
		}
		$("ul").remove('.woocommerce-error')
        if (code) {
            $.post(wc_checkout_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'apply_coupon' ), data, function(html){
                
                $(document.body).trigger('applied_coupon_in_checkout', [data.coupon_code]);
				$(document.body).trigger('update_checkout', {update_shipping_method: false});
				$('.coupon.container').prepend(html)
            })
        }
    })
})