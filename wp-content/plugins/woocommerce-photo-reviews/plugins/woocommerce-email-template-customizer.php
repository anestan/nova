<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * WooCommerce Order Status & Actions Manager plugin
 */
if ( ! class_exists( 'VI_WOOCOMMERCE_PHOTO_REVIEWS_Plugins_WooCommerce_Email_Template_Customizer' ) ) {
	class VI_WOOCOMMERCE_PHOTO_REVIEWS_Plugins_WooCommerce_Email_Template_Customizer {
		protected $settings;

		public function __construct() {
			$this->settings = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_instance();
			add_action( 'admin_enqueue_scripts', array(
				$this,
				'admin_enqueue_scripts'
			) );
			add_filter( 'viwec_register_email_type', array( $this, 'register_email_type' ) );
			add_filter( 'viwec_sample_subjects', array( $this, 'register_email_sample_subject' ) );
			add_filter( 'viwec_sample_templates', array( $this, 'register_email_sample_template' ) );
			add_filter( 'viwec_live_edit_shortcodes', array( $this, 'register_render_preview_shortcode' ) );
			add_filter( 'viwec_register_preview_shortcode', array( $this, 'register_render_preview_shortcode' ) );
			add_action( 'viwec_render_content', array( $this, 'render_review_reminder' ), 10, 3 );
		}

		public function register_email_type( $emails ) {
			$emails['wcpr_coupon_email']    = array(
				'name'       => esc_html__( 'WooCommerce Photo Reviews - Coupon For Review', 'woocommerce-photo-reviews' ),
				'hide_rules' => array( 'country', 'category', 'min_order', 'max_order' ),
			);
			$emails['wcpr_review_reminder'] = array(
				'name'            => esc_html__( 'WooCommerce Photo Reviews - Review Reminder', 'woocommerce-photo-reviews' ),
				'hide_rules'      => array( 'country', 'category', 'min_order', 'max_order' ),
				'accept_elements' => array(
					'wcpr_products_to_review',
					'html/order_detail',
					'html/order_subtotal',
					'html/order_total',
					'html/shipping_method',
					'html/payment_method',
					'html/order_note',
					'html/billing_address',
					'html/shipping_address',
				),
			);

			return $emails;
		}

		public function register_email_sample_subject( $subjects ) {
			$subjects['wcpr_coupon_email']    = 'Coupon gift for your review!';
			$subjects['wcpr_review_reminder'] = 'Thank you for shopping with us!';

			return $subjects;
		}

		public function register_email_sample_template( $samples ) {
			$samples['wcpr_coupon_email']    = [
				'basic' => [
					'name' => esc_html__( 'Basic', 'woocommerce-photo-reviews' ),
					'data' => '{"style_container":{"background-color":"transparent","background-image":"none"},"rows":{"0":{"props":{"style_outer":{"padding":"15px 35px","background-image":"none","background-color":"#162447","border-color":"transparent","border-style":"solid","border-width":"0px","border-radius":"0px"},"type":"layout/grid1cols","dataCols":"1"},"cols":{"0":{"props":{"style":{"padding":"0px","background-image":"none","background-color":"transparent","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"}},"elements":{"0":{"type":"html/text","style":{"line-height":"30px","background-image":"none","background-color":"transparent","padding":"0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p style=\"text-align: center;\"><span style=\"color: #ffffff;\">{site_title}</span></p>"},"attrs":{},"childStyle":{}}}}}},"1":{"props":{"style_outer":{"padding":"25px","background-image":"none","background-color":"#f9f9f9","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"type":"layout/grid1cols","dataCols":"1"},"cols":{"0":{"props":{"style":{"padding":"0px","background-image":"none","background-color":"transparent","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"}},"elements":{"0":{"type":"html/text","style":{"line-height":"28px","background-image":"none","background-color":"transparent","padding":"0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p style=\"text-align: center;\"><span style=\"font-size: 24px; color: #444444;\">Thank you for shopping with us!</span></p>"},"attrs":{},"childStyle":{}}}}}},"2":{"props":{"style_outer":{"padding":"10px 35px","background-image":"none","background-color":"#ffffff","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"type":"layout/grid1cols","dataCols":"1"},"cols":{"0":{"props":{"style":{"padding":"0px","background-image":"none","background-color":"transparent","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"}},"elements":{"0":{"type":"html/text","style":{"line-height":"22px","background-image":"none","background-color":"transparent","padding":"0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p>Dear {customer_name},</p>"},"attrs":{},"childStyle":{}},"1":{"type":"html/spacer","style":{},"content":{},"attrs":{},"childStyle":{".viwec-spacer":{"padding":"10px 0px 0px"}}},"2":{"type":"html/text","style":{"line-height":"22px","background-image":"none","background-color":"transparent","padding":"0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p>Thank you so much for leaving review on my website!</p>\n<p>We’d like to offer you this discount coupon as our thankfulness to you.</p>"},"attrs":{},"childStyle":{}},"3":{"type":"html/button","style":{"font-size":"15px","font-weight":"400","color":"#1de712","line-height":"22px","text-align":"center","padding":"20px 0px 20px 1px"},"content":{"text":"{wcpr_coupon_code}"},"attrs":{"href":"{shop_url}"},"childStyle":{"a":{"border-width":"2px","border-radius":"0px","border-color":"#162447","border-style":"dashed","background-color":"#ffffff","width":"141px","padding":"10px 20px"}}},"4":{"type":"html/text","style":{"line-height":"22px","background-image":"none","background-color":"transparent","padding":"0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p>This coupon will expire on {wcpr_date_expires}.</p>"},"attrs":{},"childStyle":{}},"5":{"type":"html/spacer","style":{},"content":{},"attrs":{},"childStyle":{".viwec-spacer":{"padding":"10px 0px 0px"}}},"6":{"type":"html/text","style":{"line-height":"22px","background-image":"none","background-color":"transparent","padding":"0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p>Yours sincerely!</p>\n<p>{site_title}</p>"},"attrs":{},"childStyle":{}}}}}},"3":{"props":{"style_outer":{"padding":"15px 35px","background-image":"none","background-color":"#ffffff","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"type":"layout/grid1cols","dataCols":"1"},"cols":{"0":{"props":{"style":{"padding":"0px","background-image":"none","background-color":"transparent","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"}},"elements":{"0":{"type":"html/text","style":{"line-height":"22px","background-image":"none","background-color":"transparent","padding":"0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p>You might want to take a look at our latest products:</p>"},"attrs":{},"childStyle":{}},"1":{"type":"html/suggest_product","style":{"padding":"0px","background-image":"none","background-color":"transparent"},"content":{},"attrs":{"data-product_type":"newest","data-max_row":"1","data-column":"4","character-limit":"30"},"childStyle":{".viwec-suggest-product":{"width":"530px"},".viwec-product-name":{"font-size":"15px","font-weight":"400","color":"#444444","line-height":"22px"},".viwec-product-price":{"font-size":"15px","font-weight":"400","color":"#444444","line-height":"22px"},".viwec-product-distance":{"padding":"0px 0px 0px 10px"},".viwec-product-h-distance":{}}}}}}},"4":{"props":{"style_outer":{"padding":"25px 35px","background-image":"none","background-color":"#162447","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"type":"layout/grid1cols","dataCols":"1"},"cols":{"0":{"props":{"style":{"padding":"0px","background-image":"none","background-color":"transparent","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"}},"elements":{"0":{"type":"html/text","style":{"line-height":"22px","background-image":"none","background-color":"transparent","padding":"0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p style=\"text-align: center;\"><span style=\"color: #f5f5f5; font-size: 20px;\">Get in Touch</span></p>"},"attrs":{},"childStyle":{}},"1":{"type":"html/social","style":{"text-align":"center","padding":"20px 0px 0px","background-image":"none","background-color":"transparent"},"content":{},"attrs":{"facebook":"' . VIWEC_IMAGES . 'fb-blue-white.png","facebook_url":"#","twitter":"' . VIWEC_IMAGES . 'twi-cyan-white.png","twitter_url":"#","instagram":"' . VIWEC_IMAGES . 'ins-white-color.png","instagram_url":"#","direction":""},"childStyle":{}},"2":{"type":"html/text","style":{"line-height":"22px","background-image":"none","background-color":"transparent","padding":"20px 0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p style=\"text-align: center;\"><span style=\"color: #f5f5f5; font-size: 12px;\">This email was sent by : <span style=\"color: #ffffff;\"><a style=\"color: #ffffff;\" href=\"{admin_email}\">{admin_email}</a></span></span></p>\n<p style=\"text-align: center;\"><span style=\"color: #f5f5f5; font-size: 12px;\">For any questions please send an email to <span style=\"color: #ffffff;\"><a style=\"color: #ffffff;\" href=\"{admin_email}\">{admin_email}</a></span></span></p>"},"attrs":{},"childStyle":{}},"3":{"type":"html/text","style":{"line-height":"22px","background-image":"none","background-color":"transparent","padding":"0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p style=\"text-align: center;\"><span style=\"color: #f5f5f5;\"><span style=\"color: #f5f5f5;\"><span style=\"font-size: 12px;\"><a style=\"color: #f5f5f5;\" href=\"#\">Privacy Policy</a>&nbsp; |&nbsp; <a style=\"color: #f5f5f5;\" href=\"#\">Help Center</a></span></span></span></p>"},"attrs":{},"childStyle":{}}}}}}}}'
				]
			];
			$samples['wcpr_review_reminder'] = [
				'basic' => [
					'name' => esc_html__( 'Basic', 'woocommerce-photo-reviews' ),
					'data' => '{"style_container":{"background-color":"transparent","background-image":"none"},"rows":{"0":{"props":{"style_outer":{"padding":"15px 35px","background-image":"none","background-color":"#162447","border-color":"transparent","border-style":"solid","border-width":"0px","border-radius":"0px"},"type":"layout/grid1cols","dataCols":"1"},"cols":{"0":{"props":{"style":{"padding":"0px","background-image":"none","background-color":"transparent","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"}},"elements":{"0":{"type":"html/text","style":{"line-height":"30px","background-image":"none","background-color":"transparent","padding":"0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p style=\"text-align: center;\"><span style=\"color: #ffffff;\">{site_title}</span></p>"},"attrs":{},"childStyle":{}}}}}},"1":{"props":{"style_outer":{"padding":"25px","background-image":"none","background-color":"#f9f9f9","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"type":"layout/grid1cols","dataCols":"1"},"cols":{"0":{"props":{"style":{"padding":"0px","background-image":"none","background-color":"transparent","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"}},"elements":{"0":{"type":"html/text","style":{"line-height":"28px","background-image":"none","background-color":"transparent","padding":"0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p style=\"text-align: center;\"><span style=\"font-size: 24px; color: #444444;\">Thank you for shopping with us!</span></p>"},"attrs":{},"childStyle":{}}}}}},"2":{"props":{"style_outer":{"padding":"25px 35px 0px","background-image":"none","background-color":"#ffffff","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"type":"layout/grid1cols","dataCols":"1"},"cols":{"0":{"props":{"style":{"padding":"0px","background-image":"none","background-color":"transparent","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"}},"elements":{"0":{"type":"html/text","style":{"line-height":"22px","background-image":"none","background-color":"transparent","padding":"0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p>Dear {wcpr_customer_name},<br>Thank you for your recent purchase from our company.</p>"},"attrs":{},"childStyle":{}},"1":{"type":"html/spacer","style":{},"content":{},"attrs":{},"childStyle":{".viwec-spacer":{"padding":"10px 0px 0px"}}},"2":{"type":"html/text","style":{"line-height":"22px","background-image":"none","background-color":"transparent","padding":"0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p>We’re excited to count you as a customer. Our goal is always to provide our very best product so that our customers are happy. It’s also our goal to continue improving. That’s why we value your feedback.</p>"},"attrs":{},"childStyle":{}},"3":{"type":"html/spacer","style":{},"content":{},"attrs":{},"childStyle":{".viwec-spacer":{"padding":"10px 0px 0px"}}},"4":{"type":"html/text","style":{"line-height":"22px","background-image":"none","background-color":"transparent","padding":"0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p>Thank you so much for taking the time to provide us feedback and review. This feedback is appreciated and very helpful to us.</p>"},"attrs":{},"childStyle":{}},"5":{"type":"html/spacer","style":{},"content":{},"attrs":{},"childStyle":{".viwec-spacer":{"padding":"10px 0px 0px"}}},"6":{"type":"html/text","style":{"line-height":"22px","background-image":"none","background-color":"transparent","padding":"0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p>Best regards!</p>\n<p>{site_title}</p>"},"attrs":{},"childStyle":{}},"7":{"type":"html/spacer","style":{},"content":{},"attrs":{},"childStyle":{".viwec-spacer":{"padding":"18px 0px 0px"}}}}}}},"3":{"props":{"style_outer":{"padding":"15px 35px","background-image":"none","background-color":"#ffffff","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"type":"layout/grid1cols","dataCols":"1"},"cols":{"0":{"props":{"style":{"padding":"0px","background-image":"none","background-color":"transparent","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"}},"elements":{"0":{"type":"wcpr_products_to_review","style":{},"content":{"review_button_title":"Write Your Review"},"attrs":{},"childStyle":{".viwec-item-row":{"background-color":"transparent","border-width":"0px","border-color":"#808080"},".viwec-product-img":{"width":"150px"},".viwec-product-distance":{"padding":"10px 0px 0px"},".viwec-product-name":{"font-size":"15px","font-weight":"400","color":"#444444","line-height":"21px"},".viwec-product-review-button":{"padding":"10px 20px","border-radius":"3px","line-height":"22px","font-size":"15px","font-weight":"400","color":"#ffffff","background-color":"#00c1a7"},".viwec-product-price":{"font-size":"15px","font-weight":"400","color":"#444444","line-height":"22px"}}}}}}},"4":{"props":{"style_outer":{"padding":"25px 35px","background-image":"none","background-color":"#162447","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"type":"layout/grid1cols","dataCols":"1"},"cols":{"0":{"props":{"style":{"padding":"0px","background-image":"none","background-color":"transparent","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"}},"elements":{"0":{"type":"html/text","style":{"line-height":"22px","background-image":"none","background-color":"transparent","padding":"0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p style=\"text-align: center;\"><span style=\"color: #f5f5f5; font-size: 20px;\">Get in Touch</span></p>"},"attrs":{},"childStyle":{}},"1":{"type":"html/social","style":{"text-align":"center","padding":"20px 0px 0px","background-image":"none","background-color":"transparent"},"content":{},"attrs":{"facebook":"' . VIWEC_IMAGES . 'fb-blue-white.png","facebook_url":"#","twitter":"' . VIWEC_IMAGES . 'twi-cyan-white.png","twitter_url":"#","instagram":"' . VIWEC_IMAGES . 'ins-white-color.png","instagram_url":"#","direction":""},"childStyle":{}},"2":{"type":"html/text","style":{"line-height":"22px","background-image":"none","background-color":"transparent","padding":"20px 0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p style=\"text-align: center;\"><span style=\"color: #f5f5f5; font-size: 12px;\">This email was sent by : <span style=\"color: #ffffff;\"><a style=\"color: #ffffff;\" href=\"{admin_email}\">{admin_email}</a></span></span></p>\n<p style=\"text-align: center;\"><span style=\"color: #f5f5f5; font-size: 12px;\">For any questions please send an email to <span style=\"color: #ffffff;\"><a style=\"color: #ffffff;\" href=\"{admin_email}\">{admin_email}</a></span></span></p>"},"attrs":{},"childStyle":{}},"3":{"type":"html/text","style":{"line-height":"22px","background-image":"none","background-color":"transparent","padding":"0px","border-color":"#444444","border-style":"solid","border-width":"0px","border-radius":"0px"},"content":{"text":"<p style=\"text-align: center;\"><span style=\"color: #f5f5f5;\"><span style=\"color: #f5f5f5;\"><span style=\"font-size: 12px;\"><a style=\"color: #f5f5f5;\" href=\"#\">Privacy Policy</a>&nbsp; |&nbsp; <a style=\"color: #f5f5f5;\" href=\"#\">Help Center</a></span></span></span></p>"},"attrs":{},"childStyle":{}}}}}}}}'
				],
			];

			return $samples;
		}

		public function register_render_preview_shortcode( $sc ) {
			$sc['wcpr_coupon_email']    = self::coupon_email_shortcodes();
			$sc['wcpr_review_reminder'] = self::review_reminder_shortcodes();

			return $sc;
		}

		public static function coupon_email_shortcodes() {
			$date_format = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_date_format();

			return array(
				'{wcpr_coupon_code}'   => 'HAPPY',
				'{wcpr_customer_name}' => 'John',
				'{wcpr_date_expires}'  => date_i18n( $date_format, ( current_time( 'timestamp' ) + 20 * DAY_IN_SECONDS ) ),
			);
		}

		public static function review_reminder_shortcodes() {
			$date_format = VI_WOOCOMMERCE_PHOTO_REVIEWS_DATA::get_date_format();

			return array(
				'{wcpr_order_id}'            => '9999',
				'{wcpr_customer_name}'       => 'John',
				'{wcpr_order_date_create}'   => date_i18n( $date_format, ( current_time( 'timestamp' ) - 3 * DAY_IN_SECONDS ) ),
				'{wcpr_order_date_complete}' => date_i18n( $date_format, ( current_time( 'timestamp' ) - 1 * DAY_IN_SECONDS ) ),
			);
		}

		public function admin_enqueue_scripts() {
			global $pagenow, $post_type, $viwec_params;
			if ( ( $pagenow === 'post.php' || $pagenow === 'post-new.php' ) && $post_type === 'viwec_template' && $viwec_params !== null ) {
				wp_enqueue_script( 'woocommerce-photo-reviews-email-template-customizer', VI_WOOCOMMERCE_PHOTO_REVIEWS_JS . 'woocommerce-email-template-customizer.js', array(
					'jquery',
					'woocommerce-email-template-customizer-components'
				), VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
				wp_enqueue_style( 'woocommerce-photo-reviews-email-template-customizer-style', VI_WOOCOMMERCE_PHOTO_REVIEWS_CSS . 'woocommerce-email-template-customizer.css', '', VI_WOOCOMMERCE_PHOTO_REVIEWS_VERSION );
				wp_localize_script( 'woocommerce-photo-reviews-email-template-customizer', 'viwec_woocommerce_photo_reviews', array(
					'review_reminder' => array(
						'category' => 'woocommerce_photo_reviews_review_reminder',
						'type'     => 'wcpr_products_to_review',
						'name'     => esc_html__( 'Products to Review', 'woocommerce-photo-reviews' ),
						'icon'     => 'woocommerce-photo-reviews-review-reminder',
						'html'     => $this->get_items_html(),
					),
				) );
			}
		}

		public function get_items_html() {
			global $viwec_params;
			ob_start();
			?>
            <div class="viwec-woocommerce-photo-reviews-coupon">
                <table class="viwec-item-row" border="0" cellpadding="0" cellspacing="0"
                       style="border-collapse: collapse; width: 100%; border-style: solid;">
                    <tr>
                        <td class="viwec-product-img" style="width: 150px;"><a href="" target="_blank"
                                                                               style="display: inline-block;text-decoration: none;"><img
                                        style="width: 100%"
                                        src="<?php echo esc_url( $viwec_params['product'] ) ?>"></a>
                        </td>
                        <td style="width:10px;"></td>
                        <td class="viwec-product-detail" valign="middle">
                            <p class="viwec-product-name"><?php esc_html_e( 'Product title', 'woocommerce-photo-reviews' ) ?></p>
                            <p><a href="" target="_blank" class="viwec-product-price"
                                  style="display: inline-block;text-decoration: none;"><?php echo $viwec_params['suggestProductPrice'] ?></a>
                            </p>
                            <p class="viwec-product-review"><a href="" target="_blank"
                                                               class="viwec-product-review-button"
                                                               style="display: inline-block;text-decoration: none;padding-top: 10px;padding-left: 20px;padding-bottom: 10px;padding-right: 20px;background-color: #00c1a7;color: #ffffff;border-radius: 3px;">Review</a>
                            </p>
                        </td>
                    </tr>
                </table>
                <div class="viwec-product-distance" style="padding: 10px 0px 0px;"></div>
                <table class="viwec-item-row" border="0" cellpadding="0" cellspacing="0"
                       style="border-collapse: collapse; width: 100%; border-style: solid;">
                    <tr>
                        <td class="viwec-product-img" style="width: 150px;"><a href="" target="_blank"
                                                                               style="display: inline-block;text-decoration: none;"><img
                                        style="width: 100%"
                                        src="<?php echo esc_url( $viwec_params['product'] ) ?>"></a>
                        </td>
                        <td style="width:10px;"></td>
                        <td class="viwec-product-detail" valign="middle">
                            <p class="viwec-product-name"><?php esc_html_e( 'Product title', 'woocommerce-photo-reviews' ) ?></p>
                            <p><a href="" target="_blank" class="viwec-product-price"
                                  style="display: inline-block;text-decoration: none;"><?php echo $viwec_params['suggestProductPrice'] ?></a>
                            </p>
                            <p class="viwec-product-review"><a href="" target="_blank"
                                                               class="viwec-product-review-button"
                                                               style="display: inline-block;text-decoration: none;padding-top: 10px;padding-left: 20px;padding-bottom: 10px;padding-right: 20px;background-color: #00c1a7;color: #ffffff;border-radius: 3px;">Review</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </div>
			<?php
			return ob_get_clean();
		}

		public function render_review_reminder( $type, $props, $render ) {
			global $wcpr_products_to_review;
			if ( $type === 'wcpr_products_to_review' ) {
				$item_distance = ! empty( $props['childStyle']['.viwec-product-distance'] ) ? viwec_parse_styles( $props['childStyle']['.viwec-product-distance'] ) : '';
				$tmpl          = self::html_format_item( $props );
				if ( $render->preview ) {
					$product_ids = array();
					if ( ! empty( $render->order ) ) {
						$order = $render->order;
						$items = $order->get_items();
						foreach ( $items as $item_id => $item ) {
							$product_id = $item->get_product_id();
							if ( $product_id ) {
								$product_ids[] = $product_id;
							}
						}
					}
					if ( $count = count( $product_ids ) ) {
						$product_ids = array_unique( $product_ids );
						foreach ( $product_ids as $i => $product_id ) {
							$product    = wc_get_product( $product_id );
							$review_url = $product->get_permalink() . '#' . $this->settings->get_params( 'reviews_anchor_link' );
							$image      = $product->get_image_id() ? wp_get_attachment_image_url( $product->get_image_id(), 'woocommerce_thumbnail' ) : VIWEC_IMAGES . 'product.png';
							$title      = $product->get_title();
							$price      = $product->get_price_html();
							echo sprintf( $tmpl, esc_url( $review_url ), esc_url( $image ), esc_url( $review_url ), esc_html( $title ), $price, esc_url( $review_url ) );
							if ( $i != $count ) {
								echo sprintf( "<table width='100%%' border='0' cellpadding='0' cellspacing='0'><tr><td style='%s'></td></tr></table>", esc_attr( $item_distance ) );
							}
						}
					} else {
						echo sprintf( $tmpl, '', esc_url( VIWEC_IMAGES . 'product.png' ), '', esc_html( 'Product title' ), wc_price( 20 ), '' );
						echo sprintf( "<div style='%s'></div>", esc_attr( $item_distance ) );
						echo sprintf( $tmpl, '', esc_url( VIWEC_IMAGES . 'product.png' ), '', esc_html( 'Product title' ), wc_price( 20 ), '' );
					}
				} else {
					if ( is_array( $wcpr_products_to_review ) && count( $wcpr_products_to_review ) ) {
						$count = count( $wcpr_products_to_review ) - 1;
						foreach ( $wcpr_products_to_review as $i => $item ) {
							echo sprintf( $tmpl, esc_url( $item['review_url'] ), esc_url( $item['image'] ), esc_url( $item['review_url'] ), esc_html( $item['name'] ), $item['price'], esc_url( $item['review_url'] ) );
							if ( $i != $count ) {
								echo sprintf( "<table width='100%%' border='0' cellpadding='0' cellspacing='0'><tr><td style='%s'></td></tr></table>", esc_attr( $item_distance ) );
							}
						}
					}
				}
			}
		}

		public static function html_format_item( $props ) {
			ob_start();
			$row_style           = ! empty( $props['childStyle']['.viwec-item-row'] ) ? viwec_parse_styles( $props['childStyle']['.viwec-item-row'] ) : '';
			$img_style           = ! empty( $props['childStyle']['.viwec-product-img'] ) ? viwec_parse_styles( $props['childStyle']['.viwec-product-img'] ) : array();
			$pdetail_style       = ! empty( $props['childStyle']['.viwec-product-detail'] ) ? viwec_parse_styles( $props['childStyle']['.viwec-product-detail'] ) : '';
			$pname_style         = ! empty( $props['childStyle']['.viwec-product-name'] ) ? viwec_parse_styles( $props['childStyle']['.viwec-product-name'] ) : '';
			$pprice_style        = ! empty( $props['childStyle']['.viwec-product-price'] ) ? viwec_parse_styles( $props['childStyle']['.viwec-product-price'] ) : '';
			$button_review_style = ! empty( $props['childStyle']['.viwec-product-review-button'] ) ? viwec_parse_styles( $props['childStyle']['.viwec-product-review-button'] ) : '';
			$review_button_title = ! empty( $props['content']['review_button_title'] ) ? $props['content']['review_button_title'] : '';
			$width               = 0;
			if ( isset( $props['childStyle']['.viwec-product-img']['width'], $props['style']['width'] ) ) {
				$width = absint( $props['style']['width'] ) - absint( $props['childStyle']['.viwec-product-img']['width'] ) - 2;
			}
			?>
            <table width='100%%' border='0' cellpadding='0' cellspacing='0' align='center'
                   style=' border-collapse:separate;font-size: 0;'>
                <tr>
                    <td valign='middle' style='<?php echo esc_attr( $row_style ) ?>'>
                        <!--[if mso | IE]>
                        <table width="100%%" role="presentation" border="0" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style='<?php echo $img_style ?>'>
                        <![endif]-->
                        <div class='viwec-responsive viwec-fix-outlook'
                             style='vertical-align:middle;display:inline-block;<?php echo $img_style ?>'>
                            <table align="left" width="100%%" border='0' cellpadding='0' cellspacing='0'>
                                <tr>
                                    <td>
                                        <a href="%s"
                                           style="display: inline-block;text-decoration: none;" target="_blank">
                                            <img width='100%%'
                                                 src='%s'
                                                 style='vertical-align: middle'>
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <!--[if mso | IE]></td>
                        <td style="vertical-align:top;">
                        <![endif]-->
                        <div class='viwec-responsive viwec-fix-outlook'
                             style='vertical-align:middle;display:inline-block;line-height: 150%%;<?php if ( $width > 0 )
							     echo esc_attr( "max-width:{$width}px;" ) ?><?php echo esc_attr( $pdetail_style ) ?>'>
                            <table align="left" width="100%%" border='0' cellpadding='0' cellspacing='0'>
                                <tr>
                                    <td class="viwec-mobile-hidden" style="padding: 0;width: 10px;"></td>
                                    <td style="padding: 5px 0;" class="viwec-responsive-center">
                                        <p><a href="%s" target="_blank"
                                              style="<?php echo esc_attr( $pname_style ) ?>;display: inline-block;text-decoration: none;">%s</a>
                                        </p>
                                        <p style="<?php echo esc_attr( $pprice_style ) ?>">%s</p>
                                        <p><a href="%s" target="_blank"
                                              style="<?php echo esc_attr( $button_review_style ) ?>;display: inline-block;text-decoration: none;"><?php echo esc_html( $review_button_title ); ?></a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <!--[if mso | IE]></td></tr></table><![endif]-->
                    </td>
                </tr>
            </table>
			<?php

			return ob_get_clean();
		}
	}
}
