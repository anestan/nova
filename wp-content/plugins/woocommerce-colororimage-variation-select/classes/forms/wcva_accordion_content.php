<div class="accordion-content">
    <?php include('accordion/wcva_display_type_dropdown.php'); ?>
    <?php include('accordion/wcva_upper_meta_div.php'); ?>

	<div class="wcvaimageorcolordiv" style="<?php if (isset($_coloredvariables[$key]['display_type']) && ($_coloredvariables[$key]['display_type'] == 'colororimage')) { echo 'display:;'; } else { echo 'display:none;'; } ?>">
		 
	    <?php 

	        $attrsubnumber=0; 
                
            foreach ($values as $value) {
		     
		        if (isset($_coloredvariables[$key]['displaytype'])) { 
			    
				    $displaytype   = $_coloredvariables[$key]['displaytype']; 
				
				} else {

				    $displaytype=''; 

				}
				   
		        $valuetitle            = $value;
               
		    
		 
		
                if (isset($terms)) {

				    foreach ( $terms as $term ) {
									   
                        if ( $term->slug != $value  ) continue; { 

							$valuetitle                 = $term->name;
							$globalthumbnail_id 	    = absint( get_term_meta( $term->term_id, 'thumbnail_id', true ) );
						    $hoverglobalimage 	        = absint( get_term_meta( $term->term_id, 'hoverimage', true ) );
		                    $globaldisplay_type 	    = get_term_meta($term->term_id, 'display_type', true );
		                    $globalcolor 	            = get_term_meta($term->term_id, 'color', true );
							$globaltextblock 	        = get_term_meta($term->term_id, 'textblock', true );
						}
			        }					   
		        }			  
		    
		  
		  
		  
		        if ((isset($_coloredvariables[$key]['values'])) && (isset($_coloredvariables[$key]['values'][$value]['image']))){
	            
				    $swatchimage = $_coloredvariables[$key]['values'][$value]['image']; 
		  
		        } elseif (isset($globalthumbnail_id)) {
		    
			        $swatchimage =$globalthumbnail_id; 
		        
		        } 

			
			    if ((isset($_coloredvariables[$key]['values'])) && (isset($_coloredvariables[$key]['values'][$value]['hoverimage']))) {
	            
				    $hoverthumb_id = $_coloredvariables[$key]['values'][$value]['hoverimage']; 
		  
		        } elseif (isset($hoverglobalimage)) {
		    
			        $hoverthumb_id = $hoverglobalimage; 
		        } 


		    
                 
		   
		        if ((isset($_coloredvariables[$key]['values'])) && (isset($_coloredvariables[$key]['values'][$value]['type']))) {
	      
		            $attrdisplaytype = $_coloredvariables[$key]['values'][$value]['type'];
		  
		        } elseif (isset($globaldisplay_type)) {
		  
		            $attrdisplaytype = $globaldisplay_type;
		        }
		  
		  
		        if ((isset($_coloredvariables[$key]['values'])) && (isset($_coloredvariables[$key]['values'][$value]['color']))) {
	          
			        $attrcolor = $_coloredvariables[$key]['values'][$value]['color'];
		  
		        } elseif (isset($globalcolor)) {
		        
				    $attrcolor = $globalcolor;
		        }
			   
			    if ((isset($_coloredvariables[$key]['values'])) && (isset($_coloredvariables[$key]['values'][$value]['textblock']))) {
	          
			        $attrtextblock = $_coloredvariables[$key]['values'][$value]['textblock'];
		  
		        } elseif (isset($globaltextblock)) {
		        
				    $attrtextblock = $globaltextblock;
		        }
			   
			    if (isset($swatchimage)) {
				    $swatchurl     = wp_get_attachment_thumb_url( $swatchimage );
			    } 
			  
			    if (isset($hoverthumb_id)) {
				    $hoverurl      = wp_get_attachment_thumb_url( $hoverthumb_id ); 
			    } 
		?>
	        
                <div class="accordion-container">
                    <div class="accordion collapsed">
                        <?php include('accordion/wcva_sub_accordion_header.php'); ?>
		                <?php include('accordion/wcva_sub_accordion_content.php'); ?>
		            </div>
		        </div>
		        <?php 
		            $attrsubnumber++;
		            $globalthumbnail_id = ''; 
			        $globaldisplay_type = 'Color';
			        $globalcolor        =  'grey';
		    } 

		?>
	 
    </div>
</div>