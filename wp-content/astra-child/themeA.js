jQuery(function($) {
    //页面加载完成执行
	$(document).ready(function(){			
		qtyChange()	
	})
	//AJAX完成后执行
	$(document).ajaxComplete(function () {
		qtyChange()
	})
	//更改数量函数
	function qtyChange() {
	    
		$(document).off("click", ".btnQ").on( "click", ".btnQ", function() {
            // 数量输入框
            var qty = $(this).siblings( ".qty" ) 
			
            var val = parseFloat(qty.val())
            var min = parseFloat(qty.attr("min"))
            var max = parseFloat(qty.attr("max"))
            var step= parseFloat(qty.attr("step"))

            if ($(this).hasClass("qty-after-btn")) {
			    
                if (val === max) return false
			    
                if ( isNaN(val) ){ 
                    qty.val(step)
                }else if(val+step > max){
                    qty.val(max)
                }else{
                    qty.val(val+step)
                }
            } else {
			    if (val === min) return false
			    
			    if ( isNaN(val) ){ 
			        qty.val(min)
			    }else if(val-step < min){
			        qty.val(min)
			    }else{
			        qty.val(val-step)
			    }
			    
			}
			qty.trigger("change")
		})
	}	
 })