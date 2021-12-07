<?php
// 移除 WordPress 版本号
remove_action('wp_head', 'wp_generator');
// 移除 wlwmanifest
remove_action('wp_head', 'wlwmanifest_link');
// 移除离线编辑器开放接口 EditURI
remove_action('wp_head', 'rsd_link');
// 移除文章和评论 feed
remove_action('wp_head', 'feed_links', 2 );
// 移除分类等 feed
remove_action('wp_head', 'feed_links_extra', 3);
// 移除 wp-json 链接
remove_action('wp_head', 'rest_output_link_wp_head', 10);
// 移除 oEmbed 发现链接
remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
// 移除Emoji表情包
remove_action('wp_head', 'print_emoji_detection_script', 7);
// 移除Emoji表情样式
remove_action('wp_print_styles', 'print_emoji_styles');
// 禁止Javascript的调用
// remove_action( 'wp_head', 'wp_enqueue_scripts', 1 ); 

/**
 * 移除WordPress加载的JS和CSS链接中的版本号 并设置成相对路径
 */
function remove_cssjs_ver($src) {
    if(strpos( $src, 'ver=' )){
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}
add_filter('style_loader_src',  'remove_cssjs_ver', 999);
add_filter('script_loader_src', 'remove_cssjs_ver', 999);


/**
 * 自定义结算完成订单详情页标题
 */
function order_received_title($title){
	if(is_order_received_page()){
		$title['title']='Oredr Checkout Detail';
		return $title;
	}
	return $title;
}
add_filter('document_title_parts', 'order_received_title', 10, 1);
