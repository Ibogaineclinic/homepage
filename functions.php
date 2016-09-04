<?php
/**
* Enqueues child theme stylesheet, loading first the parent theme stylesheet.
*/
function themify_custom_enqueue_child_theme_styles() {
wp_enqueue_style( 'parent-theme-css', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'themify_custom_enqueue_child_theme_styles' );

/**
 * Register our sidebars and widgetized areas.
 *
// */
//function arphabet_widgets_init() {
//    
//	register_sidebar( array(
//		'name'          => 'Home Bottom 1',
//		'id'            => 'Home_Bottom_1',
//		'before_widget' => '<div class="bottom_1">',
//		'after_widget'  => '</div>',
//		'before_title'  => '<h4">',
//		'after_title'   => '</h4>',
//	) );
//
//	register_sidebar( array(
//		'name'          => 'Home Bottom 2',
//		'id'            => 'Home_Bottom_2',
//		'before_widget' => '<div class="bottom_2">',
//		'after_widget'  => '</div>',
//		'before_title'  => '<h4">',
//		'after_title'   => '</h4>',
//	) );
//
//	register_sidebar( array(
//		'name'          => 'Home Bottom 3',
//		'id'            => 'Home_Bottom_3',
//		'before_widget' => '<div class="bottom_3">',
//		'after_widget'  => '</div>',
//		'before_title'  => '<h4">',
//		'after_title'   => '</h4>',
//	) );
//
//}
//add_action( 'widgets_init', 'arphabet_widgets_init' );
