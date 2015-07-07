<?php
	//add_action( 'init', 'zoner_prevent_admin_access', 0 );
	
	add_action( 'after_setup_theme',  'zoner_setup' );
	add_filter( 'nav_menu_css_class', 'zoner_nav_parent_class', 10, 2 );
	add_filter( 'nav_menu_css_class', 'zoner_add_parent_url_menu_class', 10, 3 );
	
	/*Remove Admin Bar*/
	add_action('init', 'zoner_options_admin_bar');
	
	/*Main Content Part*/
	add_action('zoner_before_content', 'zoner_before_content');
	add_action('zoner_after_content',  'zoner_after_content');
	add_action('the_main_content', 'zoner_the_main_content');
	
	add_filter( 'page_css_class', 'zoner_add_page_parent_class', 10, 4 );
	add_action( 'customize_register', 'zoner_customize_register' );
	add_action( 'template_redirect', 'zoner_content_width' );
	add_action( 'wp_enqueue_scripts', 'zoner_scripts', 10 );
	
	add_filter( 'body_class', 'zoner_body_classes' );
	add_filter( 'post_class', 'zoner_post_classes' );
	add_filter( 'wp_title', 'zoner_wp_title', 10, 2 );
	add_filter( 'get_search_form', 'zoner_search_form' );
	add_filter( 'excerpt_more', 'zoner_change_excerpt_more');
	add_filter( 'excerpt_length', 'zoner_set_excerpt_length', 999 );	
	add_filter( 'the_content_more_link', 'zoner_modify_read_more_link' );
	add_filter( 'edit_post_link', 'zoner_edit_post_link');
	
	add_action( 'zoner_comments_template', 'zoner_visibilty_comments');
	add_filter( 'the_password_form', 'zoner_password_protect_form' );
	add_filter( 'the_content', 'zoner_post_chat', 99);
	add_action( 'wp_head', 'zoner_add_google_analytics', 99);
	add_action( 'wp_head', 'zoner_add_favicon', 100);
	add_filter( 'img_caption_shortcode', 'zoner_img_caption', 10, 3 );
	
	add_filter('pre_get_posts','zoner_ExludeSearchFilter');
	
	
	/*Profile*/
	add_action('after_setup_theme', 'zoner_remove_admin_bar');
	add_action('wp', 'zoner_process_save_profile', 300);
	
	add_filter('query_vars', 'zoner_add_query_var');
	add_action('init', 'zoner_add_rewrite_rules', 10);
	
	
	/*Footer*/
	add_action('zoner_footer_elements', 'zoner_get_footer_area_sidebars', 1);
	add_action('zoner_footer_elements', 'zoner_get_social', 3);
	
	
	
	/*User password*/
	add_action( 'wp_ajax_zoner_check_user_password', 'zoner_check_user_password_act' );
	add_action( 'wp_ajax_nopriv_zoner_check_user_password', 'zoner_check_user_password_act' );
	
	add_action( 'wp', 'zoner_change_user_pass_act', 300);
	