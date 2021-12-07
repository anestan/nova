var $naz = jQuery.noConflict();

(function( $naz ) {
    'use strict';
    //click event

       	 $naz(document).on( 'click', '.wcvaswatchinput',
       	 	function( e ){
              var hoverimage    = $naz(this).attr('data-o-src');
              var parent        = $naz(this).closest('li');
              var parentdiv     = $naz(this).closest('div.shopswatchinput');
              var productimage  = $naz(this).closest('.product').find("img").attr("src"); 
             
			        $naz( this ).closest('.shopswatchinput').find('div.selectedswatch').removeClass('selectedswatch').addClass('wcvashopswatchlabel');
			        $naz( this ).closest('.wcvaswatchinput').find('div.wcvashopswatchlabel').removeClass('wcvashopswatchlabel').addClass( 'selectedswatch' );
			 
               if (hoverimage) {
                 $naz(this).closest('.product').find("img.attachment-woocommerce_thumbnail").attr("src",hoverimage);
				         $naz(this).closest('.product').find("img.attachment-woocommerce_thumbnail").attr("srcset",hoverimage);
                 $naz(parentdiv).attr("prod-img",productimage);
               }
             }
			 

         );


        var slider_count = parseInt(wcva_shop.slider_no);

        jQuery(document).ready(function($naz) {

          if (wcva_shop.enable_slider == "yes") {

            

             $naz('.wcva-multiple-items').each(function(){

            

              var swatch_count = $naz(this).attr("swatch-count");
              
              
              if (swatch_count > slider_count) {
                jQuery(this).slick({
                
                  slidesToShow: slider_count,
                  slidesToScroll: slider_count,
                  nextArrow: '<img src="'+wcva_shop.right_icon+'" class="nextArrowBtn">',
                  prevArrow: '<img src="'+wcva_shop.left_icon+'" class="nextArrowBtn">',
              
                }); 
              }
               
            });

            $naz('.wcva-multiple-items').show();

          }

        });
})(jQuery);