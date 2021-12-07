<?php
/**
 * Class VI_WooCommerce_Photo_Reviews_Frontend_Reviews
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Shortcode {
    protected  $settings, $is_ajax;
    protected  static $frontend;
    public function __construct() {
	    $this->settings = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_instance();
	    self::$frontend ='VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Frontend';
	    if ( 'on' === $this->settings->get_params( 'enable' ) ) {
		    add_action( 'viwcpr_shortcode_get_template_basic_html', array( $this, 'viwcpr_shortcode_get_template_basic_html' ),10,1 );
		    add_action( 'init', array( $this, 'shortcode_init' ) );
		    add_action( 'elementor/frontend/after_enqueue_scripts', array( $this, 'wp_enqueue_scripts_elementor' ) );
		    add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		    add_action( 'wp_ajax_woocommerce_photo_reviews_shortcode_ajax_get_reviews', array( $this, 'ajax_get_reviews' ) );
		    add_action( 'wp_ajax_nopriv_woocommerce_photo_reviews_shortcode_ajax_get_reviews', array( $this, 'ajax_get_reviews' ) );
	    }
    }
	public function ajax_get_reviews() {
		$this->is_ajax     = true;
		$reviews_shortcode = isset( $_REQUEST['reviews_shortcode'] ) ? json_decode( sanitize_text_field( stripslashes( $_REQUEST['reviews_shortcode'] ) ), true ) : '';
		$shortcode_attrs   = array();
		foreach ( $reviews_shortcode as $key => $value ) {
			$shortcode_attrs[] = "{$key}='{$value}'";
		}
		wp_send_json( array( 'html' => do_shortcode( '[wc_photo_reviews_shortcode ' . implode( ' ', $shortcode_attrs ) . ']' ) ) );
	}
	/**
	 * @param $atts
	 *
	 * @return string
	 */
	public function overall_rating_html($atts){
		$arr         = shortcode_atts( array(
			'product_id'   => '',
			'overall_rating_enable' => '',
			'rating_count_enable' => '',
			'is_shortcode' =>true,
		), $atts );
		if (empty($arr['product_id'])){
			global $product;
			$product_id = 0;
			if ( $product && is_a($product, 'WC_Product')) {
				$product_id = $product->get_id();
			}
			if (!$product_id){
				return '';
			}
			$arr['product_id']= $product_id;
		}
		if (strpos($arr['product_id'],',') > 0){
			$arr['product_id'] = explode(',',$arr['product_id']);
			//review count
			$reviews_count_args = array(
				'status'      => 'approve',
				'post_type'   => 'product',
				'post_status' => 'any',
				'number'      => 0,
				'count'       => true,
				'parent'      => 0,
				'post__in'    => $arr['product_id'],
			);
			$default_meta_query = array(
				'relation' => 'and'
			);
			$star_counts        = array();
			$total_rating       = 0;
			$total_rating_num   = 0;
			for ( $i = 1; $i < 6; $i ++ ) {
				$star_counts_args               = $reviews_count_args;
				$meta_query                     = $default_meta_query;
				$meta_query[]                   = array(
					'key'     => 'rating',
					'value'   => $i,
					'compare' => '=',
				);
				$star_counts_args['meta_query'] = $meta_query;
				$star_counts[ $i ]              = self::$frontend::get_comments( $star_counts_args );
				$total_rating                   += ( $star_counts[ $i ] * $i );
				$total_rating_num               += $star_counts[ $i ];
			}
			$average_rating = 0;
			if ( $total_rating_num ) {
				$average_rating = $total_rating / $total_rating_num;
			}
			$arr['count_reviews']=$total_rating_num;
			$arr['star_counts']=$star_counts;
			$arr['average_rating']=$average_rating;
		}else{
			$product = wc_get_product($arr['product_id']);
			$arr['star_counts'] = array();
			$arr['average_rating'] = $product->get_average_rating();
			$agrs          = array(
				'post_id'  => $arr['product_id'],
				'count'    => true,
				'meta_key' => 'rating',
				'status'   => 'approve'
			);
			remove_action( 'parse_comment_query', array( self::$frontend, 'filter_images_and_verified' ) );
			remove_action( 'parse_comment_query', array( self::$frontend, 'filter_review_rating' ) );
			$arr['count_reviews'] = self::$frontend::get_comments( $agrs );
			add_action( 'parse_comment_query', array( self::$frontend, 'filter_images_and_verified' ) );
			add_action( 'parse_comment_query', array( self::$frontend, 'filter_review_rating' ) );
		}
		if ( ! wp_style_is( 'wcpr-shortcode-all-reviews-style' ) ) {
			wp_enqueue_style( 'wcpr-shortcode-all-reviews-style', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'shortcode-style.css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
		}
		$arr = apply_filters('wc_photo_reviews_overall_rating_args', $arr);
		ob_start();
		remove_action( 'parse_comment_query', array( self::$frontend, 'filter_images_and_verified' ) );
		remove_action( 'parse_comment_query', array( self::$frontend, 'filter_review_rating' ) );
		do_action( 'viwcpr_get_overall_rating_html', $arr );
		add_action( 'parse_comment_query', array( self::$frontend, 'filter_images_and_verified' ) );
		add_action( 'parse_comment_query', array( self::$frontend, 'filter_review_rating' ) );
		$html =ob_get_clean();
		return $html;
	}
	public function rating_html( $atts ) {
		$arr         = shortcode_atts( array(
			'product_id'   => '',
			'rating'       => '',
			'review_count' => 'on',
		), $atts );
		$rating_html = '';
		$rating      = $arr['rating'];
		if ( function_exists( 'wc_get_rating_html' ) ) {
			if ( ! wp_style_is( 'woocommerce-photo-reviews-rating-html-shortcode' ) ) {
				wp_enqueue_style( 'woocommerce-photo-reviews-rating-html-shortcode', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'rating-html-shortcode.css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			}
			$product_id = '';
			if ( $arr['product_id'] ) {
				$product_id = $arr['product_id'];
			} else {
				global $product;
				if ( $product ) {
					$product_id = $product->get_id();
				}
			}
			$review_count = 0;
			if ( $product_id ) {
				$review_count = get_post_meta( $product_id, '_wc_review_count', true );
				$rating       = get_post_meta( $product_id, '_wc_average_rating', true );
			}
			$rating_html = wc_get_template_html('viwcpr-shortcode-reviews-rating-html.php',
				array(
					'rating'=> $rating,
					'review_count'=> $review_count,
					'review_count_enable'=>  $arr['review_count'],
				),
				'woocommerce-photo-reviews' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR,
				WOOCOMMERCE_PHOTO_REVIEWS_TEMPLATES );
		}
		return $rating_html;
	}
    public function viwcpr_shortcode_get_template_basic_html($arg){
	    if ( empty( $arg ) ) {
		    return;
	    }
	    wc_get_template( 'viwcpr-shortcode-template-basic-html.php', $arg,
		    'woocommerce-photo-reviews' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR,
		    WOOCOMMERCE_PHOTO_REVIEWS_TEMPLATES );
    }
	public function all_reviews_shortcode( $atts ) {
		global $wcpr_shortcode_id;
		if ( $wcpr_shortcode_id === null ) {
			$wcpr_shortcode_id = 1;
		} else {
			$wcpr_shortcode_id ++;
		}
		$shortcode_id = "woocommerce-photo-reviews-shortcode-{$wcpr_shortcode_id}";
		$arr          = shortcode_atts( array(
			'comments_per_page'       => 20,
			'cols'                    => $this->settings->get_params( 'photo', 'col_num' ),
			'cols_mobile'             => $this->settings->get_params( 'photo', 'col_num_mobile' ),
			'cols_gap'                => '',
			'use_single_product'      => '',
			'products'                => '',
			'products_status'         => 'publish',
			'grid_bg_color'           => '',
			'grid_item_bg_color'      => '',
			'grid_item_border_color'  => '',
			'text_color'              => '',
			'star_color'              => '',
			'product_cat'             => '',
			'order'                   => 'DESC',
			'orderby'                 => 'comment_date_gmt',
			'show_product'            => 'on',
			'filter'                  => 'on',
			'pagination'              => 'on',
			'pagination_ajax'         => $this->settings->get_params( 'pagination_ajax' ) ? 'on' : 'off',
			'pagination_next'         =>  '',
			'pagination_pre'         =>  '',
			'loadmore_button'         =>  '',
			'filter_default_image'    => $this->settings->get_params( 'filter_default_image' ) ? 'on' : 'off',
			'filter_default_verified' => $this->settings->get_params( 'filter_default_verified' ) ? 'on' : 'off',
			'filter_default_rating'   => $this->settings->get_params( 'filter_default_rating' ),
			'pagination_position'     => '',
			'conditional_tag'         => '',
			'custom_css'              => '',
			'masonry_popup'           => 'review',
			'image_popup'             => 'below_thumb',
			'ratings'                 => '',
			'mobile'                  => 'on',
			'style'                   => 'masonry',
			'enable_box_shadow'       => $this->settings->get_params( 'photo', 'enable_box_shadow' ) ? 'on' : 'off',
			'full_screen_mobile'      => $this->settings->get_params( 'photo', 'full_screen_mobile' ) ? 'on' : 'off',
			'style_mobile'            => '',
			'overall_rating'          => 'off',
			'rating_count'            => 'off',
			'only_images'             => 'off',
			'area_border_color'       => $this->settings->get_params( 'photo', 'filter' )['area_border_color'],
			'area_bg_color'           => $this->settings->get_params( 'photo', 'filter' )['area_bg_color'],
			'button_color'            => $this->settings->get_params( 'photo', 'filter' )['button_color'],
			'button_bg_color'         => $this->settings->get_params( 'photo', 'filter' )['button_bg_color'],
			'button_border_color'     => $this->settings->get_params( 'photo', 'filter' )['button_border_color'],
			'rating_count_bar_color'  => $this->settings->get_params( 'photo', 'rating_count_bar_color' ),
			'verified_color'          => $this->settings->get_params( 'photo', 'verified_color' ),
			'is_elementor'            => 'no',
			'wpml_all_languages'      => 'off',
		), $atts );
		global $wcpr_shortcode_count;
		$wcpr_shortcode_count = true;
		$custom_css           = stripslashes( $arr['custom_css'] );
		$arr                  = array_map( 'strtolower', $arr );
		if (empty($arr['masonry_popup'])){
			$arr['masonry_popup']='off';
		}
		if ( ! $this->is_ajax && $arr['conditional_tag'] ) {
			$logic_value = $arr['conditional_tag'];
			if ( stristr( $logic_value, "return" ) === false ) {
				$logic_value = "return (" . $logic_value . ");";
			}
			if ( ! eval( $logic_value ) ) {
				return '';
			}
		}
		$post_status = 'any';
		if ( $arr['products_status'] ) {
			$post_status = array_filter( explode( ',', ( $arr['products_status'] ) ), 'trim' );
		}
		global $wcpr_detect;
		if ( ! $wcpr_detect ) {
			$wcpr_detect = new VillaTheme_Mobile_Detect();
		}
		$is_mobile = $wcpr_detect->isMobile() && ! $wcpr_detect->isTablet();
		if ( $is_mobile && $arr['mobile'] !== 'on' ) {
			return '';
		}
		if ( ! $is_mobile || ! $arr['style_mobile'] ) {
			$frontend_style = $arr['style'];
		} else {
			$frontend_style = $arr['style_mobile'];
		}
		$shortcode_id_css = "#{$shortcode_id} ";
		$caption_enable   = $this->settings->get_params( 'image_caption_enable' );
		$modal_class      = ".{$shortcode_id}-modal.shortcode-wcpr-modal-light-box ";
		if ( ! $this->is_ajax ) {
			if ( ! wp_style_is( 'wcpr-verified-badge-icon' ) ) {
				wp_enqueue_style( 'wcpr-verified-badge-icon', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'woocommerce-photo-reviews-badge.css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			}
			if ( ! wp_style_is( 'wcpr-shortcode-all-reviews-style' ) ) {
				wp_enqueue_style( 'wcpr-shortcode-all-reviews-style', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'shortcode-style.css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			}
			if ( $this->settings->get_params( 'photo', 'helpful_button_enable' ) && ! wp_style_is( 'woocommerce-photo-reviews-vote-icons' ) ) {
				wp_enqueue_style( 'woocommerce-photo-reviews-vote-icons', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'woocommerce-photo-reviews-vote-icons.css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			}
			if ( ! wp_style_is( 'wcpr-swipebox-js' ) ) {
				wp_enqueue_script( 'wcpr-swipebox-js', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'jquery.swipebox.js', array( 'jquery' ) );
				wp_enqueue_style( 'wcpr-swipebox-css', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'swipebox.css' );
			}
			if ( ! wp_style_is( 'wcpr-shortcode-masonry-style' ) ) {
				wp_enqueue_style( 'wcpr-shortcode-masonry-style', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'shortcode-masonry.css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			}
			if ( ! wp_style_is( 'wcpr-rotate-font-style' ) ) {
				wp_enqueue_style( 'wcpr-rotate-font-style', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'rotate.css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			}
			if ( ! wp_style_is( 'wcpr-default-display-style' ) ) {
				wp_enqueue_style( 'wcpr-default-display-style', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'default-display-images.css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			}
			$css = $custom_css?:'';
			if ( $caption_enable ) {
				$css .= VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Frontend::add_inline_style(
					array( $shortcode_id_css.'.shortcode-reviews-images-wrap-right .shortcode-wcpr-review-image-caption',
						$shortcode_id_css.'#shortcode-reviews-content-left-main .shortcode-wcpr-review-image-container .shortcode-wcpr-review-image-caption',
						$shortcode_id_css.'.kt-reviews-image-container .big-review-images-content-container .wcpr-review-image-caption', ),
					array( 'background-color','color' ,'font-size'),
					array( $this->settings->get_params( 'image_caption_bg_color' ) ,$this->settings->get_params( 'image_caption_color' ),$this->settings->get_params( 'image_caption_font_size' )),
					array( '!important','!important' ,'px !important')
				);
			}

			if ( $arr['is_elementor'] !== 'yes' ) {
				if ( $frontend_style === 'masonry' ) {
					if ( $arr['cols'] ) {
						$css .= $shortcode_id_css . '.shortcode-wcpr-grid.wcpr-grid-loadmore{grid-template-columns: repeat(' . absint( $arr['cols'] ) . ', 1fr) !important;}';
						$css .= $shortcode_id_css . '.shortcode-wcpr-grid{column-count: ' . absint( $arr['cols'] ) . ' !important;}';
					}
					if ( $arr['cols_gap'] ) {
						$css .= $shortcode_id_css . '.shortcode-wcpr-grid.wcpr-grid-loadmore{grid-gap:' . absint( $arr['cols_gap'] ) . 'px !important;}';
						$css .= $shortcode_id_css . '.shortcode-wcpr-grid{column-gap:' . absint( $arr['cols_gap'] ) . 'px !important;}';
					}
					if ( $arr['grid_bg_color'] ) {
						$css .= $shortcode_id_css . '.shortcode-wcpr-grid{background-color:' . $arr['grid_bg_color'] . ' !important;}';
					}
					if ( $arr['grid_item_bg_color'] ) {
						$css .= $shortcode_id_css . '.shortcode-wcpr-grid .shortcode-wcpr-grid-item{background-color:' . $arr['grid_item_bg_color'] . ' !important;}';
						$css .= $modal_class . '.shortcode-wcpr-modal-light-box-wrapper .shortcode-wcpr-modal-wrap{background-color:' . $arr['grid_item_bg_color'] . ' !important;}';
					}
					if ( $arr['grid_item_border_color'] ) {
						$css .= $shortcode_id_css . '.shortcode-wcpr-grid .shortcode-wcpr-grid-item{border: 1px solid ' . $arr['grid_item_border_color'] . ' !important;}';
					}
					if ( $arr['text_color'] ) {
						$css .= $shortcode_id_css . '.shortcode-wcpr-grid .shortcode-wcpr-grid-item{color:' . $arr['text_color'] . ';}';
						$css .= $modal_class . '.shortcode-wcpr-modal-light-box-wrapper .shortcode-wcpr-modal-wrap>#shortcode-reviews-content-right{color:' . $arr['text_color'] . ' !important;}';
					}
					$css.='@media (max-width: 600px) {';
					$css .= VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Frontend::add_inline_style(
						array( $shortcode_id_css.'.shortcode-wcpr-grid', $shortcode_id_css.'.shortcode-wcpr-grid.shortcode-wcpr-masonry-2-col',
							$shortcode_id_css.'.shortcode-wcpr-grid.shortcode-wcpr-masonry-3-col', $shortcode_id_css.'.shortcode-wcpr-grid.shortcode-wcpr-masonry-4-col',
							$shortcode_id_css.'.shortcode-wcpr-grid.shortcode-wcpr-masonry-5-col',
						),
						array( 'column-count','grid-template-columns'),
						array($arr['cols_mobile'], 'repeat('.$arr['cols_mobile'].', 1fr)'),
						array( '!important', '!important')
					);
					$css .= '}';
				}

				if ( 'on' === $arr['filter'] ) {
					$css .= "{$shortcode_id_css} .shortcode-wcpr-filter-container{";
					if ( $arr['area_border_color'] ) {
						$css .= "border:1px solid " . $arr['area_border_color'] . ' !important';
					}
					if ( $arr['area_bg_color'] ) {
						$css .= 'background-color:' . $arr['area_bg_color'] . ' !important';
					}
					$css .= "}";
					$css .= "{$shortcode_id_css} .shortcode-wcpr-filter-container .shortcode-wcpr-filter-button{";

					if ( $arr['button_color'] ) {
						$css .= 'color:' . $arr['button_color'] . ' !important';
					}
					if ( $arr['button_bg_color'] ) {
						$css .= 'background-color:' . $arr['button_bg_color'] . ' !important';
					}
					if ( $arr['button_border_color'] ) {
						$css .= 'border:1px solid ' . $arr['button_border_color'] . ' !important';
					}
					$css .= "}";
				}
				if ( 'on' === $arr['rating_count'] && $arr['rating_count_bar_color'] ) {
					$css .= "{$shortcode_id_css}.rate-percent{background-color: {$arr['rating_count_bar_color']} !important;}";
				}
				if ( $arr['pagination_position'] ) {
					if ( in_array( $arr['pagination_position'], array( 'left', 'right', 'center' ) ) ) {
						$css .= "{$shortcode_id_css}.shortcode-wcpr-pagination{text-align: {$arr['pagination_position']} !important;}";
					}
				}
				if ( ( $arr['verified_color'] ) ) {
					$css .= "$shortcode_id_css.woocommerce-review__verified,$modal_class.woocommerce-review__verified{color: {$arr['verified_color']} !important;}";
				}
				if ( $arr['star_color'] ) {
					$css .= "{$shortcode_id_css}.shortcode-wcpr-reviews .shortcode-wcpr-comments .star-rating::before,
				{$shortcode_id_css}.shortcode-wcpr-reviews .shortcode-wcpr-comments .star-rating span::before,
				{$shortcode_id_css}.shortcode-review-content-container .star-rating span:before,
				{$shortcode_id_css}.shortcode-review-content-container .star-rating:before,
				.woocommerce-photo-reviews-rating-html-shortcode .star-rating::before,
				.woocommerce-photo-reviews-rating-html-shortcode .star-rating span::before,
				{$modal_class}.shortcode-wcpr-modal-light-box-wrapper .shortcode-wcpr-modal-wrap .star-rating span:before,
				{$modal_class}.shortcode-wcpr-modal-light-box-wrapper .shortcode-wcpr-modal-wrap .star-rating:before,
				.woocommerce-photo-reviews-form-container .star-rating span:before,
				.woocommerce-photo-reviews-form-container .star-rating:before,
				.woocommerce-photo-reviews-form-container .stars a:before,
				.woocommerce-photo-reviews-form-container .stars a:hover:after,
				.woocommerce-photo-reviews-form-container .stars a.active:after,
				{$shortcode_id_css}.shortcode-wcpr-overall-rating-right .shortcode-wcpr-overall-rating-right-star .star-rating:before,
				{$shortcode_id_css}.shortcode-wcpr-overall-rating-right .shortcode-wcpr-overall-rating-right-star .star-rating span:before,
				{$shortcode_id_css}.shortcode-wcpr-stars-count .shortcode-wcpr-row .shortcode-wcpr-col-star .star-rating:before,
				{$shortcode_id_css}.shortcode-wcpr-stars-count .shortcode-wcpr-row .shortcode-wcpr-col-star .star-rating span:before{color:{$arr['star_color']} !important;}";
				}
			} else {
				if ( ( $arr['verified_color'] ) ) {
					$css .= "$modal_class.woocommerce-review__verified{color: {$arr['verified_color']} !important;}";
				}
				if ( $arr['star_color'] ) {
					$css .= ".woocommerce-photo-reviews-rating-html-shortcode .star-rating::before,
				.woocommerce-photo-reviews-rating-html-shortcode .star-rating span::before,
				{$modal_class}.shortcode-wcpr-modal-light-box-wrapper .shortcode-wcpr-modal-wrap .star-rating span:before,
				{$modal_class}.shortcode-wcpr-modal-light-box-wrapper .shortcode-wcpr-modal-wrap .star-rating:before,
				.woocommerce-photo-reviews-form-container .star-rating span:before,
				.woocommerce-photo-reviews-form-container .star-rating:before,
				.woocommerce-photo-reviews-form-container .stars a:before,
				.woocommerce-photo-reviews-form-container .stars a:hover:after,
				.woocommerce-photo-reviews-form-container .stars a.active:after{color:{$arr['star_color']} !important;}";
				}
			}
			wp_add_inline_style( 'wcpr-shortcode-all-reviews-style', $css );
		}
		$comments_per_page = intval( $arr['comments_per_page'] );
		if ( ! $this->is_ajax && 'on' === $arr['pagination_ajax'] ) {
			$paged = 1;
		} else {
			$paged = isset( $_REQUEST['wcpr_page'] ) ? intval( $_REQUEST['wcpr_page'] ) : 1;
		}
		$offset       = ( $paged - 1 ) * $comments_per_page;
		$comment_args = array(
			'status'      => 'approve',
			'post_type'   => 'product',
			'post_status' => $post_status,
			'number'      => $comments_per_page,//comments per page
			'paged'       => $paged,// current page
			'offset'      => $offset,//start position=(paged-1)*number
			'parent'      => 0,
			'post__in'    => array(),
		);
		$comment_count_args = array(
			'status'      => 'approve',
			'post_type'   => 'product',
			'post_status' => $post_status,
			'number'      => 0,
			'count'       => true,
			'parent'      => 0,
			'post__in'    => array(),
		);
		if ( ! $this->is_ajax && is_product() && 'on' === $arr['use_single_product'] ) {
			global $product;
			if ( $product ) {
				$arr['product_cat'] = '';
				$comment_args['post_id']       = $product->get_id();
				$comment_count_args['post_id'] = $comment_args['post_id'];
				$arr['products']               = $comment_args['post_id'];
			}elseif ($product_id_t = get_the_ID()){
				$comment_args['post_id']       = $product_id_t;
				$comment_count_args['post_id'] = $product_id_t;
				$arr['products']               = $product_id_t;
			}
		}else{
			if ( $arr['products'] ) {
				$comment_args['post__in']       = array_filter( explode( ',', ( $arr['products'] ) ), 'trim' );
				$comment_count_args['post__in'] = $comment_args['post__in'];
			}
			if ( $arr['product_cat'] ) {
				$cats = array_filter( explode( ',', ( $arr['product_cat'] ) ), 'trim' );
				if ( count( $cats ) ) {
					$products  = array();
					$the_query = new WP_Query( array(
						'post_type'      => 'product',
						'posts_per_page' => - 1,
						'tax_query'      => array(
							'relation' => 'AND',
							array(
								'taxonomy' => 'product_cat',
								'field'    => 'ID',
								'terms'    => $cats,
								'operator' => 'IN'
							)
						)
					) );
					if ( $the_query->have_posts() ) {
						while ( $the_query->have_posts() ) {
							$the_query->the_post();
							$product_id = get_the_ID();
							if ( ! in_array( $product_id, $products ) ) {
								$products[] = $product_id;
							}
						}
						wp_reset_postdata();
					}
					$comment_args['post__in']       = array_merge( $products, $comment_args['post__in'] );
					$comment_count_args['post__in'] = array_merge( $products, $comment_count_args['post__in'] );
				}
			}
			$review_form_product = isset( $_GET['product_id'] ) ? wp_unslash( $_GET['product_id'] ) : '';
			if ( $review_form_product ) {
				$comment_args['post__in']       = array( $review_form_product );
				$comment_count_args['post__in'] = array( $review_form_product );
			}
		}
		$default_meta_query = array(
			'relation' => 'and'
		);
		if ( 'on' === $arr['only_images'] ) {
			$default_meta_query []            = array(
				'key'     => 'reviews-images',
				'compare' => 'EXISTS',
			);
			$comment_args['meta_query']       = $default_meta_query;
			$comment_count_args['meta_query'] = $default_meta_query;
		}
		if ( $arr['ratings'] ) {
			$include_rating        = array_filter( explode( ',', ( $arr['ratings'] ) ), 'trim' );
			$default_meta_query [] = array(
				'key'     => 'rating',
				'value'   => $include_rating,
				'compare' => 'IN',
			);
		}
		if ( ! $this->is_ajax && 'on' === $arr['pagination_ajax'] ) {
			$query_image    = $arr['filter_default_image'] === 'on' ? 1 : '';
			$query_verified = $arr['filter_default_verified'] === 'on' ? 1 : '';
			$query_rating   = $arr['filter_default_rating'];
		} else {
			$query_image    = isset( $_REQUEST['wcpr_image'] ) ? $_REQUEST['wcpr_image'] : '';
			$query_verified = isset( $_REQUEST['wcpr_verified'] ) ? $_REQUEST['wcpr_verified'] : '';
			$query_rating   = isset( $_REQUEST['wcpr_rating'] ) ? $_REQUEST['wcpr_rating'] : '';
		}
		//review count
		$reviews_count_args = $comment_count_args;
		$star_counts        = array();
		$total_rating       = 0;
		$total_rating_num   = 0;
		for ( $i = 1; $i < 6; $i ++ ) {
			$star_counts_args               = $reviews_count_args;
			$meta_query                     = $default_meta_query;
			$meta_query[]                   = array(
				'key'     => 'rating',
				'value'   => $i,
				'compare' => '=',
			);
			$star_counts_args['meta_query'] = $meta_query;
			$star_counts[ $i ]              = VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Frontend::get_comments( $star_counts_args );
			$total_rating                   += ( $star_counts[ $i ] * $i );
			$total_rating_num               += $star_counts[ $i ];
		}
		$average_rating = 0;
		if ( $total_rating_num ) {
			$average_rating = $total_rating / $total_rating_num;
		}
		$page_url = remove_query_arg( array( 'action', 'reviews_shortcode' ), $_SERVER['REQUEST_URI'] );
		if ($arr['filter']==='on'){
			$rating = 0;
			if ( isset( $query_rating ) ) {
				switch ( $query_rating ) {
					case 1:
					case 2:
					case 3:
					case 4:
					case 5:
						$rating = $query_rating;
						break;
					default:
						$rating = 0;
				}
			}
			$meta_query = $default_meta_query;
			if ( $query_verified == 1 ) {
				$meta_query[] = array(
					'key'   => 'verified',
					'value' => 1
				);
			}
			if ( $query_image == 1 ) {
				$meta_query[] = array(
					'key'     => 'reviews-images',
					'compare' => 'EXISTS'
				);
			}
			if ( $rating ) {
				$meta_query[] = array(
					'key'     => 'rating',
					'value'   => $rating,
					'compare' => '='
				);
			}
			$comment_args['meta_query']       = $meta_query;
			$comment_args                     = apply_filters( 'woocommerce_photo_reviews_shortcode_comment_args', $comment_args );
			$comment_count_args['meta_query'] = $meta_query;
			$comment_count_args               = apply_filters( 'woocommerce_photo_reviews_shortcode_comment_count_args', $comment_count_args );
		}
		if ( $arr['orderby'] ) {
			$comment_args['orderby'] = explode( ',', $arr['orderby'] );
		}
		if ( $arr['order'] ) {
			$comment_args['order'] = $arr['order'];
		}
		$my_comments = VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Frontend::get_comments( $comment_args );
		$all_comments_approved = VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Frontend::get_comments( $comment_count_args );
		if ( $comments_per_page > 0 ) {
			if ( $all_comments_approved % $comments_per_page > 0 ) {
				$max_num_pages = intval( $all_comments_approved / $comments_per_page ) + 1;
			} else {
				$max_num_pages = intval( $all_comments_approved / $comments_per_page );
			}
		} else {
			$max_num_pages = 1;
		}
		$loadmore_button = $arr['loadmore_button']==='on' && $arr['pagination_ajax']==='on';
		ob_start();
		if (!$this->is_ajax){
			echo sprintf('<div id="%s" class="%s" data-wcpr_image="%s" data-wcpr_verified="%s" data-wcpr_rating="%s" data-reviews_shortcode="%s">',
				$shortcode_id,esc_attr( 'woocommerce-photo-reviews-shortcode woocommerce-photo-reviews-shortcode-popup-' . $arr['masonry_popup'] ),
				$query_image,$query_verified,$query_rating,esc_attr( json_encode( $arr ) )
			);
		}
		do_action( 'viwcpr_get_overall_rating_html', array(
			'product_id'     => empty($comment_args['post__in']) ?($comment_args['post_id'] ?? ''):$comment_args['post__in'],
			'average_rating' => $average_rating,
			'count_reviews'  => $total_rating_num,
			'overall_rating_enable'  =>  $arr['overall_rating'] ,
			'rating_count_enable'  => $arr['rating_count'],
			'star_counts'  => $star_counts,
			'is_shortcode'   => true,
		) );
		if ($arr['filter']==='on'){
			$count_reviews = 0;
//			stars count
			for ( $k = 5; $k > 0; $k -- ) {
				$comment_count_args_k = $comment_count_args;
				$meta_query_k                = $default_meta_query;
				if ( $query_verified == 1 ) {
					$meta_query_k[] = array(
						'key'   => 'verified',
						'value' => 1
					);
				}
				if ( $query_image == 1 ) {
					$meta_query_k[] = array(
						'key'     => 'reviews-images',
						'compare' => 'EXISTS'
					);
				}
				$meta_query_k[]                            = array(
					'key'     => 'rating',
					'value'   => $k,
					'compare' => '='
				);
				$comment_count_args_k['meta_query'] = $meta_query_k;
				$star_counts[ $k ]              = VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Frontend::get_comments( $comment_count_args_k );
				$count_reviews += $star_counts[ $k ] ;
			}
//image count
			$comment_count_args_image          = $comment_count_args;
			$meta_query_image                = $default_meta_query;
			if ( $rating ) {
				$meta_query_image[] = array(
					'key'     => 'rating',
					'value'   => $rating,
					'compare' => '='
				);
			} else {
				$meta_query_image[] = array(
					'key'     => 'rating',
					'compare' => 'EXISTS'
				);
			}
			if ( $query_verified == 1 ) {
				$meta_query_image[] = array(
					'key'   => 'verified',
					'value' => 1
				);
			}
			$meta_query_image[]                            = array(
				'key'     => 'reviews-images',
				'compare' => 'EXISTS'
			);
			$comment_count_args_image['meta_query'] = $meta_query_image;
			$count_images                  = VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Frontend::get_comments( $comment_count_args_image );
//			verified count
			$comment_count_args_verified = $comment_count_args;
			$meta_query_verified                = $default_meta_query;
			if ( $query_image == 1 ) {
				$meta_query_verified[] = array(
					'key'     => 'reviews-images',
					'compare' => 'EXISTS'
				);
			}
			if ( $rating ) {
				$meta_query_verified[] = array(
					'key'     => 'rating',
					'value'   => $rating,
					'compare' => '='
				);
			} else {
				$meta_query_verified[] = array(
					'key'     => 'rating',
					'compare' => 'EXISTS'
				);
			}
			$meta_query_verified[]     = array(
				'key'   => 'verified',
				'value' => 1
			);
			$comment_count_args_verified['meta_query'] = $meta_query_verified;
			$count_verified = VI_WOOCOMMERCE_PHOTO_REVIEWS_Frontend_Frontend::get_comments( $comment_count_args_verified );
			do_action( 'viwcpr_get_filters_html', array(
				'settings'       => $this->settings,
				'product_id'     => empty($comment_args['post__in']) ?($comment_args['post_id'] ?? ''):$comment_args['post__in'],
				'count_reviews'  => $count_reviews,
				'star_counts'  => $star_counts,
				'count_images'   => $count_images,
				'count_verified' => $count_verified,
				'query_rating'   => $rating,
				'query_verified' => $query_verified,
				'query_image'    => $query_image,
				'product_link'   => $page_url,
				'is_shortcode'   => true,
			) );
		}
		if ( is_array( $my_comments ) && count( $my_comments ) ) {
			$pagination_html ='';
			if ( 'on' === $arr['pagination'] && $max_num_pages > 1 ) {
				if ($loadmore_button && $max_num_pages > $paged){
					ob_start();
					do_action( 'viwcpr_get_pagination_loadmore_html', array(
						'settings'     => $this->settings,
						'only_button' => true,
						'cpage'        => $paged + 1,
						'is_shortcode' => true,
					) );
					$pagination_html = ob_get_clean();
				}elseif (!$loadmore_button) {
					$pagination_html = wc_get_template_html(
						'viwcpr-pagination-basic-html.php',
						array(
							'max_num_pages' => $max_num_pages,
							'paged'         => $paged,
							'page_url'      => $page_url,
							'pre'      => $arr['pagination_pre'],
							'next'      => $arr['pagination_next'],
							'is_shortcode'  => true,
						),
						'woocommerce-photo-reviews' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR,
						WOOCOMMERCE_PHOTO_REVIEWS_TEMPLATES
					);
				}
			}
			echo $loadmore_button ? '': $pagination_html;
			if ($frontend_style ==='masonry') {
				do_action( 'viwcpr_get_template_masonry_html', array(
					'settings'          => $this->settings,
					'my_comments'       => $my_comments,
					'cols'              => $arr['cols'],
					'masonry_popup'     => $arr['masonry_popup'],
					'enable_box_shadow' => $arr['enable_box_shadow']==='on',
					'show_product' => $arr['show_product'],
					'loadmore_button' => $arr['loadmore_button'],
					'is_shortcode'      => true,
				) );
			}else{
				do_action( 'viwcpr_shortcode_get_template_basic_html', array(
					'settings'       => $this->settings,
					'my_comments'        => $my_comments,
					'image_popup_type'        => $arr['image_popup'],
					'caption_enable'        => $caption_enable,
				) );
			}
			echo $pagination_html;
		}
		echo sprintf('<div class="wcpr-shortcode-overlay"></div>');
		if (!$this->is_ajax){
			echo sprintf('</div>');
		}
		$return               = ob_get_clean();
		$wcpr_shortcode_count = false;
		return $return;
	}
	public function shortcode_init() {
		add_shortcode( 'wc_photo_reviews_shortcode', array( $this, 'all_reviews_shortcode' ) );
		add_shortcode( 'wc_photo_reviews_rating_html', array( $this, 'rating_html' ) );
		add_shortcode( 'wc_photo_reviews_overall_rating_html', array( $this, 'overall_rating_html' ) );
	}
	public function wp_enqueue_scripts_elementor() {
		$suffix = WP_DEBUG ? '' : 'min.';
		wp_enqueue_style( 'wcpr-verified-badge-icon', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'woocommerce-photo-reviews-badge.min.css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
		wp_enqueue_style( 'wcpr-shortcode-all-reviews-style', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'shortcode-style.'.$suffix.'css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
		wp_enqueue_style( 'woocommerce-photo-reviews-vote-icons', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'woocommerce-photo-reviews-vote-icons.min.css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
		wp_enqueue_script( 'wcpr-swipebox-js', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'jquery.swipebox.js', array( 'jquery' ) );
		wp_enqueue_style( 'wcpr-swipebox-css', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'swipebox.'.$suffix.'css' );
		wp_enqueue_style( 'wcpr-shortcode-masonry-style', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'shortcode-masonry.'.$suffix.'css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
		wp_enqueue_style( 'wcpr-rotate-font-style', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'rotate.min.css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
		wp_enqueue_style( 'wcpr-default-display-style', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'default-display-images.'.$suffix.'css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
		wp_enqueue_script( 'wcpr-default-display-script', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'default-display-images.'.$suffix.'js', array( 'jquery' ), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
		wp_enqueue_script( 'woocommerce-photo-reviews-script', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'script.'.$suffix.'js', array( 'jquery' ), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
		$grid_class = "wcpr-grid wcpr-masonry-{$this->settings->get_params( 'photo', 'col_num' )}-col wcpr-masonry-popup-{$this->settings->get_params( 'photo', 'masonry_popup' )}";
		if ( $this->settings->get_params( 'photo', 'enable_box_shadow' ) ) {
			$grid_class .= ' wcpr-enable-box-shadow';
		}
		wp_localize_script( 'woocommerce-photo-reviews-script', 'woocommerce_photo_reviews_params', array(
				'ajaxurl'               => admin_url( 'admin-ajax.php' ),
				'text_load_more'        => esc_html__( 'Load more', 'woocommerce-photo-reviews' ),
				'text_loading'          => esc_html__( 'Loading...', 'woocommerce-photo-reviews' ),
				'i18n_required_rating_text'  => esc_attr__( 'Please select a rating', 'woocommerce-photo-reviews' ),
				'i18n_required_comment_text' => esc_attr__( 'Please enter your comment', 'woocommerce-photo-reviews' ),
				'i18n_required_name_text'    => esc_attr__( 'Please enter your name', 'woocommerce-photo-reviews' ),
				'i18n_required_email_text'   => esc_attr__( 'Please enter your email', 'woocommerce-photo-reviews' ),
				'warning_gdpr'          => esc_html__( 'Please agree with our term and policy.', 'woocommerce-photo-reviews' ),
				'max_files'             => $this->settings->get_params( 'photo', 'maxfiles' ),
				'warning_max_files'     => sprintf( _n( 'You can only upload maximum of %s file', 'You can only upload maximum of %s files', $this->settings->get_params( 'photo', 'maxfiles' ), 'woocommerce-photo-reviews' ), $this->settings->get_params( 'photo', 'maxfiles' ) ),
				'default_comments_page' => get_option( 'default_comments_page' ),
				'sort'                  => $this->settings->get_params( 'photo', 'sort' )['time'],
				'display'               => $this->settings->get_params( 'photo', 'display' ),
				'masonry_popup'         => $this->settings->get_params( 'photo', 'masonry_popup' ),
				'pagination_ajax'       => $this->settings->get_params( 'pagination_ajax' ),
				'loadmore_button'            => $this->settings->get_params('loadmore_button')?:'',
				'container'             => ( $this->settings->get_params( 'photo', 'display' ) == 1 ? '.shortcode-wcpr-grid' : ( ( $this->settings->get_params( 'reviews_container' ) ) ? $this->settings->get_params( 'reviews_container' ) : '.commentlist' ) ),
				'comments_container_id' => apply_filters( 'woocommerce_photo_reviews_comments_wrap', 'comments' ),
				'nonce'                 => wp_create_nonce( 'woocommerce_photo_reviews_nonce' ),
				'grid_class'            => esc_attr( $grid_class ),
				'i18n_image_caption'    => esc_attr__( 'Caption for this image', 'woocommerce-photo-reviews' ),
				'image_caption_enable'  => $this->settings->get_params( 'image_caption_enable' ),
				'required_image'             => $this->settings->get_params( 'photo', 'required' ),
				'enable_photo'               => $this->settings->get_params( 'photo', 'enable' ),
				'warning_required_image'     => esc_html__( 'Please upload at least one image for your review!', 'woocommerce-photo-reviews' ),
				'allow_empty_comment'        => $this->settings->get_params( 'allow_empty_comment' ),
			)
		);
		wp_enqueue_script( 'woocommerce-photo-reviews-shortcode-script', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'shortcode-script.'.$suffix.'js', array( 'jquery' ), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
		wp_localize_script( 'woocommerce-photo-reviews-shortcode-script', 'woocommerce_photo_reviews_shortcode_params', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			)
		);
		wp_enqueue_style( 'woocommerce-photo-reviews-rating-html-shortcode', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'rating-html-shortcode.css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
	}
	public function quickview(){
		wc_get_template( 'viwcpr-quickview-template-html.php',
			array(
				'is_shortcode' => true
			),
			'woocommerce-photo-reviews' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR,
			WOOCOMMERCE_PHOTO_REVIEWS_TEMPLATES );
	}
	public function wp_enqueue_scripts() {
		$suffix = WP_DEBUG ? '' : 'min.';
		if ( ! wp_style_is( 'wcpr-verified-badge-icon', 'registered' ) ) {
			wp_register_style( 'wcpr-verified-badge-icon', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'woocommerce-photo-reviews-badge.min.css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
		}
		if ( ! wp_style_is( 'wcpr-shortcode-all-reviews-style', 'registered' ) ) {
			wp_register_style( 'wcpr-shortcode-all-reviews-style', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'shortcode-style.'.$suffix.'css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
		}
		if ( $this->settings->get_params( 'photo', 'helpful_button_enable' ) && ! wp_style_is( 'woocommerce-photo-reviews-vote-icons' ) ) {
			wp_register_style( 'woocommerce-photo-reviews-vote-icons', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'woocommerce-photo-reviews-vote-icons.min.css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
		}
		if ( ! wp_style_is( 'wcpr-swipebox-js', 'registered' ) ) {
			wp_register_script( 'wcpr-swipebox-js', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'jquery.swipebox.js', array( 'jquery' ) );
			wp_register_style( 'wcpr-swipebox-css', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'swipebox.'.$suffix.'css' );
		}
		if ( ! wp_style_is( 'wcpr-shortcode-masonry-style', 'registered' ) ) {
			wp_register_style( 'wcpr-shortcode-masonry-style', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'shortcode-masonry.'.$suffix.'css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
		}
		if ( ! wp_style_is( 'wcpr-rotate-font-style', 'registered' ) ) {
			wp_register_style( 'wcpr-rotate-font-style', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'rotate.min.css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
		}
		if ( ! wp_style_is( 'wcpr-default-display-style', 'registered' ) ) {
			wp_register_style( 'wcpr-default-display-style', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'default-display-images.'.$suffix.'css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
		}
		if ( ! wp_script_is( 'wcpr-default-display-script' ) ) {
			wp_enqueue_script( 'wcpr-default-display-script', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'default-display-images.'.$suffix.'js', array( 'jquery' ), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
		}
		if ( ! wp_script_is( 'woocommerce-photo-reviews-script' ) ) {
			wp_enqueue_script( 'woocommerce-photo-reviews-script', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'script.'.$suffix.'js', array( 'jquery' ), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			$grid_class = "wcpr-grid wcpr-masonry-{$this->settings->get_params( 'photo', 'col_num' )}-col wcpr-masonry-popup-{$this->settings->get_params( 'photo', 'masonry_popup' )}";
			if ( $this->settings->get_params( 'photo', 'enable_box_shadow' ) ) {
				$grid_class .= ' wcpr-enable-box-shadow';
			}
			wp_localize_script( 'woocommerce-photo-reviews-script', 'woocommerce_photo_reviews_params', array(
					'ajaxurl'               => admin_url( 'admin-ajax.php' ),
					'text_load_more'        => esc_html__( 'Load more', 'woocommerce-photo-reviews' ),
					'text_loading'          => esc_html__( 'Loading...', 'woocommerce-photo-reviews' ),
					'i18n_required_rating_text'  => esc_attr__( 'Please select a rating', 'woocommerce-photo-reviews' ),
					'i18n_required_comment_text' => esc_attr__( 'Please enter your comment', 'woocommerce-photo-reviews' ),
					'i18n_required_name_text'    => esc_attr__( 'Please enter your name', 'woocommerce-photo-reviews' ),
					'i18n_required_email_text'   => esc_attr__( 'Please enter your email', 'woocommerce-photo-reviews' ),
					'warning_gdpr'          => esc_html__( 'Please agree with our term and policy.', 'woocommerce-photo-reviews' ),
					'max_files'             => $this->settings->get_params( 'photo', 'maxfiles' ),
					'warning_max_files'     => sprintf( _n( 'You can only upload maximum of %s file', 'You can only upload maximum of %s files', $this->settings->get_params( 'photo', 'maxfiles' ), 'woocommerce-photo-reviews' ), $this->settings->get_params( 'photo', 'maxfiles' ) ),
					'default_comments_page' => get_option( 'default_comments_page' ),
					'sort'                  => $this->settings->get_params( 'photo', 'sort' )['time'],
					'display'               => $this->settings->get_params( 'photo', 'display' ),
					'masonry_popup'         => $this->settings->get_params( 'photo', 'masonry_popup' ),
					'pagination_ajax'       => $this->settings->get_params( 'pagination_ajax' ),
					'loadmore_button'            => $this->settings->get_params('loadmore_button')?:'',
					'container'             => ( $this->settings->get_params( 'photo', 'display' ) == 1 ? '.shortcode-wcpr-grid' : ( ( $this->settings->get_params( 'reviews_container' ) ) ? $this->settings->get_params( 'reviews_container' ) : '.commentlist' ) ),
					'comments_container_id' => apply_filters( 'woocommerce_photo_reviews_comments_wrap', 'comments' ),
					'nonce'                 => wp_create_nonce( 'woocommerce_photo_reviews_nonce' ),
					'grid_class'            => esc_attr( $grid_class ),
					'i18n_image_caption'    => esc_attr__( 'Caption for this image', 'woocommerce-photo-reviews' ),
					'image_caption_enable'  => $this->settings->get_params( 'image_caption_enable' ),
					'required_image'             => $this->settings->get_params( 'photo', 'required' ),
					'enable_photo'               => $this->settings->get_params( 'photo', 'enable' ),
					'warning_required_image'     => esc_html__( 'Please upload at least one image for your review!', 'woocommerce-photo-reviews' ),
					'allow_empty_comment'        => $this->settings->get_params( 'allow_empty_comment' ),
				)
			);
		}
		if ( ! wp_script_is( 'woocommerce-photo-reviews-shortcode-script' ) ) {
			add_action( 'wp_footer', array( $this, 'quickview' ) );
			wp_enqueue_script( 'woocommerce-photo-reviews-shortcode-script', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'shortcode-script.'.$suffix.'js', array( 'jquery' ), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
			wp_localize_script( 'woocommerce-photo-reviews-shortcode-script', 'woocommerce_photo_reviews_shortcode_params', array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
				)
			);
		}
		if ( ! wp_style_is( 'woocommerce-photo-reviews-rating-html-shortcode', 'registered' ) ) {
			wp_register_style( 'woocommerce-photo-reviews-rating-html-shortcode', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'rating-html-shortcode.css', array(), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
		}
	}
}