<?php
/* 主题通用功能区
---------------------------------------------*/
require( dirname(__FILE__) . '/modules/base.php' );


/* 主题独有功能区
---------------------------------------------*/

/**
 * 添加底部小工具
 * 
 * @since ok 1.0.0
*/
function mooc_footerwidgets_init() {
	register_sidebar( array(
	'id' => 'site-footer',
	'name' => __('Footer', 'ok'),
	'before_widget' => '<aside id="%1$s" class="widget %2$s">',
	'after_widget' => '</aside>',
	'before_title' => '<h3 class="widget-title">',
	'after_title' => '</h3>',
	));
}
add_action( 'widgets_init', 'mooc_footerwidgets_init' );
function mooc_print_styles(){
	// 自动调整底部小工具宽度
	$sidebars_widgets = wp_get_sidebars_widgets();
	$count = isset($sidebars_widgets['site-footer']) ? count($sidebars_widgets['site-footer']) : 1;
	$count = max($count,1);
	// 自动调整单双栏
	$usesidebar1 = count($sidebars_widgets['sidebar-1']);
	$usesidebar2 = count($sidebars_widgets['sidebar-2']);
	?>
	<style type="text/css" media="screen">
	#footer-widgets .widget { width: <?php echo round(100/$count,3) . '%' ?>; }
	<?php 
	if(is_singular()){ 
		if(!$usesidebar2){echo '.posts { width: 100%; }';}
	}elseif(!$usesidebar1){
		echo '.posts { width: 100%; }';
	}?>
	@media screen and (max-width: 640px) {
		#footer-widgets .widget { width: auto; float: none; }
	}
	</style>
	<?php
}
add_action('wp_head', 'mooc_print_styles', 11);



?>