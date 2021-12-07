<?php

// 谷歌标签管理器Head
function wp_head_add_google_tag_manager_code(){
?>

<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-NXN6S7X');</script>
<!-- End Google Tag Manager -->

<?php
}
add_action('wp_head', 'wp_head_add_google_tag_manager_code');


// 谷歌标签管理器Body
function wp_body_add_google_tag_manager_code(){
?>

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NXN6S7X"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<?php
}
add_action('wp_body_open', 'wp_body_add_google_tag_manager_code');