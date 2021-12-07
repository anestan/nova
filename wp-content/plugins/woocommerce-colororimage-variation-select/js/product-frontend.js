var $laz = jQuery.noConflict();
(function( $laz ) {
    'use strict';
        
	    var wcva_attribute_number = $laz('.attribute-swatch').length;
        var chosenarray           = {};
        var chosenlength          = 0;
        var chosenthreshold       = wcva_attribute_number - 1;
        

        

	    if (wcva.tooltip == "yes") {
		    $laz('.swatchinput label').powerTip();
	    }
      
	    $laz('form.variations_form').on( 'change', '.wcva-standard-select', function() {
			var selectedtext         = $laz(this).val();
			
			if (wcva.show_attribute == "yes") {
			   $laz( this ).closest('tr').prev().find('.wcva_selected_attribute').text(selectedtext);
		    }
		});



	



        
     
	    $laz('form.variations_form').on( 'click', '.swatchinput label', function() {
		    var selectid           = $laz(this).attr("selectid");
            var dataoption         = $laz(this).attr("data-option");
			var selectedtext       = $laz(this).attr("selectedtext");
		    var attributeindex     = $laz(this).closest('.attribute-swatch').attr('attribute-index');
	        

		    


		    if  (wcva.cross_outofstock != "yes") {
			    if ($laz(this).hasClass('selectedswatch')) {
				
				$laz(this).removeClass('selectedswatch').addClass('wcvaswatchlabel');
				
				var currentoptionToSelect = parent.jQuery("form.variations_form #" + selectid + "").children("[value='']");

               //mark the option as selected
                currentoptionToSelect.prop("selected", "selected").change();
				
				$laz( this ).closest('tr').prev().find('.wcva_selected_attribute').text("");
				
				return;
			   }
		    }

		    
		   if (wcva.show_attribute == "yes") {
			   $laz( this ).closest('tr').prev().find('.wcva_selected_attribute').text(selectedtext);
		   }
	      
		   
		   $laz( this ).closest('.attribute-swatch').find('.selectedswatch').removeClass('selectedswatch').addClass('wcvaswatchlabel');
	       $laz( this ).removeClass('wcvaswatchlabel').addClass( 'selectedswatch' );
		   
		  
		  
           //find the option to select
           var optionToSelect = parent.jQuery("form.variations_form #" + selectid + "").children("[value='" + dataoption + "']");

           //mark the option as selected
           optionToSelect.prop("selected", "selected").change();
		 
					    
		});	   
		
		
		if (wcva.disable_options == "yes") {
			 		
			$laz('form.variations_form').on( 'click', '.swatchinput label', function( event ) {

				 var selectid           = $laz(this).attr("selectid");
                 var dataoption         = $laz(this).attr("data-option");

                 wcva_disable_swatches_as_dropdown();
                 

		    });
			
			$laz('form.variations_form').on( 'click', '.wcva-standard-select', function(event) {

				 
			
			     wcva_disable_swatches_as_dropdown();
			     
		    });

		
		}


		

        if (wcva.cross_outofstock == "yes") {

		    $laz('form.variations_form').on( 'click', '.swatchinput label', function( event ) {

				 var selectid           = $laz(this).attr("selectid");
                 var dataoption         = $laz(this).attr("data-option");
                 

                 
                 wcva_disable_outofstock_options(selectid,dataoption,chosenarray);

		    });


		    $laz('form.variations_form').on( 'change', '.wcva-standard-select', function(event) {

		    	var selectid           = $laz(this).attr("id");
                var dataoption         = $laz(this).val();
                
               
                //intiate adding of outofstock class

                wcva_disable_outofstock_options(selectid,dataoption,chosenarray);


		    });

		}
		
	   
	   
	    
        $laz( window ).load(function() {
           	
           	wcva_disable_swatches_as_dropdown();
               

            $laz('.attribute-swatch').each(function(){

                var show_number       = $laz(this).find('.wcva_show_more_link').attr("show-number");
                var swatch_count      = $laz(this).find('.swatchinput').length;
                 
                var show_more_link    = $laz(this).find('.wcva_show_more_link');

                var swatch_hide_count = swatch_count - show_number;
                var swatch_hide_count = Math.abs(swatch_hide_count) * -1;

                

                if (swatch_count > show_number) {
                 	
                    $laz(this).find('.swatchinput').slice(swatch_hide_count).addClass("hidden_swatchinput");
                    $laz(this).find('.swatchinput').slice(swatch_hide_count).hide();

                 	show_more_link.show();


                 	$laz(show_more_link).on( 'click', function(event) {
                    
                        event.preventDefault();
                	    
                	    $laz(this).parents('.attribute-swatch').find('.swatchinput').slice(swatch_hide_count).show();
                        $laz(this).parents('.attribute-swatch').find('.swatchinput').slice(swatch_hide_count).removeClass("hidden_swatchinput");
                	    
                	    $laz(this).hide();
                	    
                	    return false;

                    });

                }
            });
           	   
        });
      
       
        $laz('form.variations_form').on( 'click', '.reset_variations', function() {
			
			$laz('form.variations_form').find('.selectedswatch').removeClass('selectedswatch').addClass('wcvaswatchlabel');
			$laz('form.variations_form').find('.wcva_selected_attribute').text("");
            
            //remove wcvaoutofstock class upon reset variation
            
            if (wcva_attribute_number != 1) {
			    $laz('form.variations_form' ).find('.wcvaoutofstock').removeClass('wcvaoutofstock');
			}
			
			
		
		if (wcva.disable_options == "yes") {
			
			$laz('form.variations_form' ).find('.wcvadisabled').removeClass('wcvadisabled');
			
			jQuery('.swatchinput').removeClass('wcvadisabled');

			if (wcva.enable_click == "02") {
                jQuery('.swatchinput').removeClass('clickenabled');
                $laz('form.variations_form' ).find('.clickenabled').removeClass('clickenabled');
		    }
            
            if (wcva.hide_options == "yes") {
				if (!jQuery(this).parent().hasClass("hidden_swatchinput")) {
						jQuery(this).parent().show();
                }
			}

            if (wcva_attribute_number == 1) {

               wcva_disable_swatches_as_dropdown();
              
            }

		}


		if (wcva.cross_outofstock == "yes") {
            
            chosenarray           = {};

            chosenlength          = 0;
			
			
			
			
			
		}



			
		});



		function wcva_disable_swatches_as_dropdown() {

            var availableoptions = [];
			
			jQuery('form.variations_form').find( '.variations select' ).each( function( i, e ) {
				
				var eachselect = jQuery( e );
				
				
				
				jQuery(e).trigger('focusin');
				
				jQuery(eachselect).find('option').each(function(index,element){
					
					    
					    availableoptions.push(element.value);
                
                });


				
				var wcvalabel = jQuery(this).closest('td').find('.swatchinput label');
				
				jQuery(wcvalabel).each(function(){

					var dataoption = jQuery(this).attr("data-option");
					
					if(jQuery.inArray( dataoption, availableoptions ) < 0){
						
						if ($laz(this).hasClass('selectedswatch')) {
						   jQuery(this).removeClass('selectedswatch').addClass('wcvaswatchlabel');
		                   
		                   jQuery(this).addClass('wcvadisabled');

		                   if (wcva.enable_click == "02") {
                                jQuery(this).addClass('clickenabled');
		                   }
						   
                           if (wcva.hide_options == "yes") {
						     jQuery(this).parent().hide();
                           }
						 
						} else {
						   jQuery(this).addClass('wcvadisabled');
						   
						   if (wcva.hide_options == "yes") {
						    jQuery(this).parent().hide();
						   }

						   if (wcva.enable_click == "02") {
                                jQuery(this).addClass('clickenabled');
		                   }


						 
						}
						
					}else{
						
						jQuery(this).removeClass('wcvadisabled');
						
						if (wcva.hide_options == "yes") {
                            if (!jQuery(this).parent().hasClass("hidden_swatchinput")) {
						        jQuery(this).parent().show();
                            }
						}

						if (wcva.enable_click == "02") {
                                jQuery(this).removeClass('clickenabled');
		                }
					}
				});
			});
		}
        
        function wcva_get_common_match(tempoutofstock_array,chosenarray) {
            var filterdarray = [];

            var chosenstring = '';
                     

            $laz.each(chosenarray, function( stkey, stvalue ) {

                chosenstring += ''+stkey+'_'+stvalue+',';

                        
            });

            chosenstring  = chosenstring.slice(0, -1);



            
            var matchindex = 0;

            $laz.each(tempoutofstock_array, function( ltkey, ltvalue ) {

            	var originalstring = '';
                  

                $laz.each(ltvalue, function( ntkey, ntvalue ) {
                    
                    originalstring += ''+ntkey+'_'+ntvalue+',';

                });

                if (originalstring.includes(chosenstring)) {
                	filterdarray[matchindex] = ltvalue;
                	matchindex++;
                }

            });

            
            
        	return filterdarray;
        }
        
		function wcva_disable_outofstock_options(selectid,dataoption,chosenarray) {

			var outofarray = [];

			var outattribute     = 'attribute_'+selectid+'';
			var outvalue         = dataoption;
            chosenarray[""+outattribute+""]        = outvalue;

            chosenlength = Object.keys(chosenarray).length;
            
            if (chosenlength < chosenthreshold) {
            	return;
            }
            
            
            
            var tempoutofstock_array = outofstock_array;

            if ((wcva_attribute_number > 2) && (chosenlength >= 2 )) {
            	var common_match = wcva_get_common_match(tempoutofstock_array,chosenarray);
            } else {
            	var common_match = tempoutofstock_array;
            }

            

            
			
            
            $laz.each(common_match, function( fkey, fvalue ) {
                 
                 
                 var outselectval = fvalue[outattribute];


                 
                 if ((outselectval == outvalue)) {

                 	fvalue == null;
                 	
                 	outofarray.push(fvalue);

                 	
                 }
            });

            
            
            
            
            if (wcva_attribute_number != 1) {
                $laz('form.variations_form' ).find('.wcvaoutofstock').removeClass('wcvaoutofstock');
            }


            
            

            

            //Main function for crossing out outofstock options

           

            if (wcva_attribute_number != 1) {

			    jQuery('form.variations_form').find( '.variations select' ).each( function( index, event ) {
				
				    var eachselect = jQuery( event );


				    var wcvalabel = jQuery(this).closest('td').find('.swatchinput label');
				
				    jQuery(wcvalabel).each(function(){

					    var selectid2           = $laz(this).attr("selectid");
                        var dataoption2         = $laz(this).attr("data-option");
                        var crossedarray        = [];

                    
                    
					
					    $laz.each(outofarray, function( fkey2, fvalue2 ) {
                         
                            

                            $laz.each(fvalue2, function( fkey3, fvalue3 ) {



                        	    
                                crossedarray.push('.'+fkey3+'_'+dataoption2+'');




                                    if  (fvalue3 == dataoption2) {

                           	  
                                        if ((outattribute != fkey3)) {

                                        	$laz.each(crossedarray, function( fkey4, fvalue4 ) {

                                        		var option_to_disable = $laz('.'+fkey3+'_'+dataoption2+'');
                                                

                                        		
                                        		if (!$laz(option_to_disable).hasClass("selectedswatch")) {
                                        			if (wcva_attribute_number != 1) {
                                        				option_to_disable.addClass('wcvaoutofstock');
                                        			}
                                        		} else {

                                        			option_to_disable.removeClass('wcvaoutofstock');

                                        		} 

                                        	});

                                        }
                           	  

                                    } 
                           
                            });

                         
                        });

				    });
                });
            
            }
            
		}

})( jQuery );