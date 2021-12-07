<div id="colored_variable_tab_data" class="panel woocommerce_options_panel hidden wc-metaboxes-wrapper">
	    
    <?php 
         
		$product            = wc_get_product($post->ID);
		$product_type       =  $product->get_type();
		
	    if ( $product_type == 'variable' )  {
	        $product = new WC_Product_Variable( $post->ID ); 
	        $attributes = $product->get_variation_attributes();
	    }
         
        $global_activation    = get_option("wcva_woocommerce_global_activation"); 
	    
		if ((!empty($attributes)) && (sizeof($attributes) >0)) { 
		
            include('wcva_upper_div_form.php');

        } else {

        	include('wcva_configure_variation_message.php');
        }
    
    ?>
		
		
	<div class="colororimagediv">
	      
	     
        <?php

		    $attrnumber=0; 
		    $displaytypenumber=0; 
        
            if ((!empty($attributes)) && (sizeof($attributes) >0)) { 
	         
	            foreach ($attributes as $key=>$values) { 
	    
		
	                if ( taxonomy_exists( $key  ) ) {
			  
			            $terms = get_terms( $key, array('menu_order' => 'ASC') );
			        } 
			  
                    if (isset($_coloredvariables[$key]['display_type'])) {

			            $selected_type = $_coloredvariables[$key]['display_type'];

		            } elseif (( taxonomy_exists( $key  ) ) && (isset($global_activation)) && ($global_activation == "yes")) {
			            $selected_type = "global";
		            } 
		  
		?>
	
	                <div class="main-content">
	                    <div class="accordion-container">
	                        <div class="accordion collapsed">
	                            <?php include('wcva_accordion_header.php'); ?>
	                            <?php include('wcva_accordion_content.php'); ?>
	                        </div>
	                    </div>			
    
	                    <?php $attrnumber++; ?>
	                </div>
	
	            <?php 
	            } 
	        }
   	    ?>
    </div>
</div>