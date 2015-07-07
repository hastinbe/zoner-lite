<?php

global 	$inc_theme_url, 
		$admin_theme_url, 
		$prefix;
		
		$inc_theme_url   = get_template_directory_uri() . '/includes/theme/';
		$admin_theme_url = get_template_directory_uri() . '/includes/admin/';
		$prefix = '_zoner_';
		
add_theme_support( 'zoner' );
if ( ! isset( $content_width ) ) $content_width = 950;
		
if ( ! function_exists( 'zoner_setup' ) ) :
/**
 * Zoner Theme setup.
 * Set up theme defaults and registers support for various WordPress features.
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support post thumbnails.
 *
 * @since Zoner Theme 1.0
 */
function zoner_setup() {
	/*
	 * Make Zoner Theme available for translation.
	 *
	 * Translations can be added to the /languages/ directory.
	 * If you're building a theme based on Zoner Theme, use a find and
	 * replace to change 'zoner' to the name of your theme in all
	 * template files.
	 */
	 
	load_theme_textdomain( 'zoner', get_template_directory() . '/languages' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 750, 750, true );

	add_image_size( 'zoner-avatar-ceo', 190, 190, true );
	add_image_size( 'zoner-footer-thumbnails', 440, 330, true );
	add_image_size( 'zoner-home-slider', 1920, 780, true );
	
	register_nav_menus( array(
		'primary'   => __( 'Top primary menu', 'zoner' )
	) );

	add_theme_support( 'post-formats', array( 'aside', 'image', 'video', 'audio', 'quote', 'link', 'gallery', 'chat'));
}
endif; // zoner_setup


/*Walkers*/
class Zoner_Submenu_Class extends Walker_Nav_Menu {
	 function start_lvl(&$output, $depth = 0, $args = array()) {
		$classes 	 = array('sub-menu', 'list-unstyled', 'child-navigation');
		$class_names = implode( ' ', $classes );
		$output .= "\n" . '<ul class="' . $class_names . '">' . "\n";
	}
	
	function display_element( $element, &$children_elements, $max_depth, $depth=0, $args, &$output ) {
        $id_field = $this->db_fields['id'];
        if ( is_object( $args[0] ) )
        $args[0]->has_children = ! empty( $children_elements[$element->$id_field] );
        return parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
    }
	
	function start_el(&$output, $item, $depth = 0, $args = array(), $current_object_id = 0) {
		global $wp_query, $zoner_config; 
	   
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$class_names_arr = array();
		$class_names = $value = '';


		$classes = empty( $item->classes ) ? array() : (array) $item->classes;

		$class_names =  join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );
		$class_names_arr[] = esc_attr( $class_names );
	   
		if ( $args->has_children )
		$class_names_arr[] = 'has-child';

		$class_names = ' class="'. implode(' ', $class_names_arr) . '"';
		
		$output .= $indent . '<li id="menu-item-'. $item->ID . '"' . $value . $class_names .'>';

		$attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
		$attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
		$attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
		$attributes .= ! empty( $item->url ) ? ' href="' . $item->url .'"' : '';
		
		$item_output = $args->before;
		$item_output .= '<a'. $attributes .'>';
		$item_output .= $args->link_before .apply_filters( 'the_title', $item->title, $item->ID );
		$item_output .= $args->link_after;
		$item_output .= '</a>';
		$item_output .= $args->after;

		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}
}

if ( ! function_exists( 'zoner_add_parent_url_menu_class' ) ) {
	function zoner_add_parent_url_menu_class( $classes = array(), $item = false ) {
  
		$curr_url = zoner_curPageURL();
		$home_url = trailingslashit( home_url() );
	
		if( is_404() or $item->url == $home_url ) return $classes;
		
		return $classes;
	}
}	


if ( ! function_exists( 'zoner_add_page_parent_class' ) ) {
	function zoner_add_page_parent_class( $css_class, $page, $depth, $args ) {
		if ( ! empty( $args['has_children'] ) )
		$css_class[] = 'parent';
		
		return $css_class;
	}
}

class Zoner_Page_Walker extends Walker_page {
	function start_lvl( &$output, $depth = 0, $args = array() ) {
		$indent = str_repeat("\t", $depth);
			$output .= "\n$indent<ul class='sub-menu list-unstyled child-navigation'>\n";
	}
}
/*End custom walkers*/

/*Customize*/
if ( ! function_exists( 'zoner_customize_register' ) ) :
	function zoner_customize_register( $wp_customize ) {
		class Zoner_Theme_Options_Button_Control extends WP_Customize_Control {
			public $type = 'button_link_control';
	 
			public function render_content() {
				?>
					<label>
						<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
						<input class="button button-primary save link_to_options" type="button" value="<?php _e('Zoner Options', 'zoner'); ?>" onclick="javascript:location.href='<?php echo esc_url(admin_url('admin.php?page=zoner_options')); ?>'"/>
					</label>
				<?php
			}
		}
		
		$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
		$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
		$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
		
		
		$wp_customize->remove_section( 'colors');
		$wp_customize->remove_section( 'header_image');
		$wp_customize->remove_section( 'background_image');
		$wp_customize->add_section('zoner_themeoptions_link', array(
								   'title' => __('Zoner Options', 'zoner'),
								   'priority' => 10,
								));
		
		
		$wp_customize->add_setting( 'themeoptions_button_control', array('sanitize_callback' => 'themeoptions_button_control_sanitize_func',) );
	 
		$wp_customize->add_control(
			new Zoner_Theme_Options_Button_Control (
				$wp_customize,
				'button_link_control',
				array(
					'label' 	=> __('Advanced theme settings', 'zoner'),
					'section' 	=> 'zoner_themeoptions_link',
					'settings' 	=> 'themeoptions_button_control'
					)
				)
			);
	}
	function themeoptions_button_control_sanitize_func ( $value ) {
		return $value;
	}
endif; // zoner_customize_register

/**
 * Adjust content_width value for image attachment template.
 * @since Zoner Theme 1.0
 * @return void
 */
if ( ! function_exists( 'zoner_content_width' ) ) :
function zoner_content_width() {
	if ( is_attachment() && wp_attachment_is_image() ) {
		$GLOBALS['content_width'] = 950;
	}
}
endif; //zoner_content_width

/*Compress code*/
if ( ! function_exists( 'zoner_compress_code' ) ) {				
	function zoner_compress_code($code) {
		$code = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $code);
		$code = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $code);
    
		return $code;
	}
}  

/**
 * Enqueue scripts and styles for the front end.
 * @since Zoner Theme 1.0
 * @return void
 */
if ( ! function_exists( 'zoner_scripts' ) ) {
	function zoner_scripts() {
		global 	$inc_theme_url, $zoner_config, $post, $prefix, $zoner;
		$is_rtl = 0;
		
		$is_mobile = false;
		if ( wp_is_mobile()) $is_mobile = true;
		
		if (is_rtl())
		$is_rtl = 1;
		
		if ( is_singular()) wp_enqueue_script( 'comment-reply' );
		
		
		wp_register_script( 'zoner-mainJs',	 $inc_theme_url . 'assets/js/custom.js',	 array( 'jquery' ), '20142807', true );
		wp_localize_script( 'zoner-mainJs', 'ZonerGlobal', 	array( 	'ajaxurl' 		=> admin_url( 'admin-ajax.php' ) ) );  
		
		/*Custom Css*/
		wp_enqueue_style( 'zoner-fontAwesom', 		$inc_theme_url . 'assets/fonts/font-awesome.min.css');
		wp_enqueue_style( 'zoner-fontElegantIcons', $inc_theme_url . 'assets/fonts/ElegantIcons.css');
		wp_enqueue_style( 'zoner-bootsrap', 	 	$inc_theme_url . 'assets/bootstrap/css/bootstrap.min.css');
		
		if (is_rtl())
		wp_enqueue_style( 'zoner-bootsrap-rtl', 	$inc_theme_url . 'assets/bootstrap/css/bootstrap-rtl.min.css');
		wp_enqueue_style( 'zoner-bootsrap-social', 	$inc_theme_url . 'assets/bootstrap/css/bootstrap-social-buttons.css');
		wp_enqueue_style( 'zoner-bootsrap-select', 	$inc_theme_url . 'assets/css/bootstrap-select.min.css');
		
		wp_enqueue_style( 'zoner-magnific-css', 	$inc_theme_url . 'assets/css/magnific-popup.css');
		
		
		wp_enqueue_style( 'zoner-style', get_stylesheet_uri() );
		
		/*Custom Js*/
		wp_enqueue_script( 'zoner-bootsrap', 		 $inc_theme_url . 'assets/bootstrap/js/bootstrap.min.js', array( 'jquery' ), '20142807', true );
		wp_enqueue_script( 'zoner-bootstrap-select', $inc_theme_url . 'assets/js/bootstrap-select.min.js',	  array( 'jquery' ), '20142807', true );
		wp_enqueue_script( 'zoner-bootsrap-holder',	 $inc_theme_url . 'assets/js/holder.js', array( 'jquery' ), '20142807', true ); 
		
		if (is_user_logged_in()) {
			wp_enqueue_script( 'zoner-bootsrap-filei',	$inc_theme_url . 'assets/bootstrap/js/bootstrap.file-input.js', array( 'jquery' ), '20142807', true );
		}
		
		wp_enqueue_script( 'zoner-ichek', $inc_theme_url . 'assets/js/icheck.min.js',	 array( 'jquery' ), '20142807', true );
		if (is_user_logged_in())
		wp_enqueue_script( 'zoner-bootsrap-filea',	$inc_theme_url . 'assets/js/fileinput.min.js', array( 'jquery' ), '20142807', true );
		if (!empty($zoner_config['smoothscroll']))
	    wp_enqueue_script( 'zoner-smoothscroll', 	$inc_theme_url . 'assets/js/smoothscroll.js', array( 'jquery' ), '20142807', true );
		
		wp_enqueue_script( 'zoner-validate', 	$inc_theme_url . 'assets/js/jquery.validate.min.js',	 array( 'jquery' ), '20142807', true );
		wp_enqueue_script( 'zoner-placeholder',	$inc_theme_url . 'assets/js/jquery.placeholder.js',	 array( 'jquery' ), '20142807', true );
		
		if (is_page() || is_single() || is_home() || is_author() || is_archive() || is_search())
			wp_enqueue_script( 'zoner-popup',$inc_theme_url	. 'assets/js/jquery.magnific-popup.min.js',	 array( 'jquery' ), '20142807', true );
		
		
		/*Custom scripts*/
		do_action('zoner_before_enqueue_script');
		
		wp_enqueue_script('zoner-mainJs');		
		
		do_action('zoner_after_enqueue_script');		
	}
}


/**
 * Extend the default WordPress body classes.
 *
 * Adds body classes to denote:
 * 1. Single or multiple authors.
 * 2. Presence of header image.
 * 3. Index views.
 * 4. Full-width content layout.
 * 5. Presence of footer widgets.
 * 6. Single views.
 * 7. Featured content layout.
 *
 * @since Zoner Theme 1.0
 *
 * @param array $classes A list of existing body class values.
 * @return array The filtered body class list.
 */
if ( ! function_exists( 'zoner_body_classes' ) ) :
function zoner_body_classes( $classes ) {
	global $prefix, $zoner, $zoner_config;
	$posts_page = get_option( 'page_for_posts' );	
	
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	if ( get_header_image() ) {
		$classes[] = 'header-image';
	} else {
		$classes[] = 'masthead-fixed';
	}

	if ( is_archive() || is_search() || is_home() || is_404() || is_tag() || is_category()) {
		$classes[] = 'list-view';
		$classes[] = 'page-sub-page';
		$classes[] = 'page-legal';
	}
	
	if ( is_active_sidebar( 'sidebar-3' ) ) {
		$classes[] = 'footer-widgets';
	}

	if ( is_singular() && !is_front_page() && !is_page()) {
		$classes[] = 'singular';
		$classes[] = 'page-sub-page';
		$classes[] = 'page-legal';
	}

	
	if ( is_front_page() || (is_home() && (empty($posts_page) && ($posts_page > 0)))) {
		$classes[] = 'page-homepage';
	} 
	
	return $classes;
}
endif; //zoner_body_classes


/**
 * Extend the default WordPress post classes.
 * Adds a post class to denote:
 * Non-password protected page with a post thumbnail.
 
 * @since Zoner Theme 1.0
 * @param array $classes A list of existing post class values.
 * @return array The filtered post class list.
 */
if ( ! function_exists( 'zoner_post_classes' ) ) :
function zoner_post_classes( $classes ) {
	if ( ! post_password_required() && has_post_thumbnail() ) {
		$classes[] = 'has-post-thumbnail';
	}
	return $classes;
}
endif; //zoner_post_classes


/**
 * Create a nicely formatted and more specific title element text for output
 * in head of document, based on current view.
 *
 * @since Zoner Theme 1.0
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 * @return string The filtered title.
 */
if ( ! function_exists( 'zoner_wp_title' ) ) :
function zoner_wp_title( $title, $sep ) {
	global $paged, $page;
	if ( is_feed() ) {
		return $title;
	}
	$title .= get_bloginfo( 'name' );
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) ) {
		$title = "$title $sep $site_description";
	}
	
	if ( $paged >= 2 || $page >= 2 ) {
		$title = "$title $sep " . sprintf( __( 'Page %s', 'zoner' ), max( $paged, $page ) );
	}

	return $title;
}
endif; //zoner_wp_title


if ( ! function_exists( 'zoner_list_authors' ) ) :
/**
 * Print a list of all site contributors who published at least one post.
 * @since Zoner Theme 1.0
 * @return void
 */
function zoner_list_authors() {
	$contributor_ids = get_users( array(
		'fields'  => 'ID',
		'orderby' => 'post_count',
		'order'   => 'DESC',
		'who'     => 'authors',
	) );

	foreach ( $contributor_ids as $contributor_id ) :
		$post_count = count_user_posts( $contributor_id );

		// Move on if user has not published a post (yet).
		if ( ! $post_count ) {
			continue;
		}
	?>

	<div class="contributor">
		<div class="contributor-info">
			<div class="contributor-avatar"><?php echo get_avatar( $contributor_id, 132 ); ?></div>
			<div class="contributor-summary">
				<h2 class="contributor-name"><?php echo get_the_author_meta( 'display_name', $contributor_id ); ?></h2>
				<p class="contributor-bio">
					<?php echo get_the_author_meta( 'description', $contributor_id ); ?>
				</p>
				<a class="contributor-posts-link" href="<?php echo esc_url( get_author_posts_url( $contributor_id ) ); ?>">
					<?php printf( _n( '%d Article', '%d Articles', $post_count, 'zoner' ), $post_count ); ?>
				</a>
			</div><!-- .contributor-summary -->
		</div><!-- .contributor-info -->
	</div><!-- .contributor -->

	<?php
	endforeach;
}
endif; //zoner_list_authors


/**
 * Getter function for Featured Content Plugin.
 * @since Zoner Theme 1.0
 * @return array An array of WP_Post objects.
 */
if ( ! function_exists( 'zoner_get_featured_posts' ) ) :
function zoner_get_featured_posts() {
	/**
	 * Filter the featured posts to return in Zoner Theme.
	 * @since Zoner Theme 1.0
	 * @param array|bool $posts Array of featured posts, otherwise false.
	 */
	return apply_filters( 'zoner_get_featured_posts', array() );
}
endif; //zoner_get_featured_posts

/**
 * A helper conditional function that returns a boolean value.
 * @since Zoner Theme 1.0
 * @return bool Whether there are featured posts.
 */
if ( ! function_exists( 'zoner_has_featured_posts' ) ) :
function zoner_has_featured_posts() {
	return ! is_paged() && (bool) zoner_get_featured_posts();
}
endif; //zoner_has_featured_posts

/*Custom functions*/
if ( ! function_exists( 'zoner_get_logo' ) ) {
	function zoner_get_logo() {
		global $zoner_config;
		
		$original_logo = $retina_logo = '';
		$width 	= $zoner_config['logo-dimensions']['width'];
		$height = $zoner_config['logo-dimensions']['height'];
		
		if (!empty($zoner_config['logo']['url'])) { $original_logo = esc_url($zoner_config['logo']['url']); } else { $original_logo = ''; }
		if (!empty($zoner_config['logo-retina']['url'])) { $retina_logo 	 = esc_url($zoner_config['logo-retina']['url']);  } else {  $retina_logo   = ''; }
		
		/*Full Backend Options*/
		$description  = $name = '';
		$description  = esc_attr(get_bloginfo('description'));
		$name  		  = esc_attr(get_bloginfo('name'));
								
		if (!empty($original_logo) || !empty($retina_logo)) {
			if ($original_logo) echo '<a class="navbar-brand nav logo" href="' 			. esc_url( home_url( '/' ) ) . '" title="' . $description .'" rel="home"><img style="width:'.$width.'; height:'.$height.';" width="'.$width.'" height="'.$height.'" src="'. $original_logo  .'" alt="' . $description . '"/></a>';
			if ($retina_logo) 	echo '<a class="navbar-brand nav logo retina" href="' 	. esc_url( home_url( '/' ) ) . '" title="' . $description .'" rel="home"><img style="width:'.$width.'; height:'.$height.';" width="'.$width.'" height="'.$height.'" src="'. $retina_logo    .'" alt="' . $description . '"/></a>';
			
		} else {
			echo  '<a class="navbar-brand nav" href="' . esc_url( home_url( '/' ) ) . '" title="' . $description .'" rel="home"><h1 class="site-title">'. $name .'</h1><h2 class="site-description">'. $description .'</h2></a>';
		}	
	}
} //zoner_get_logo


if ( ! function_exists( 'zoner_get_main_nav' ) ) {
	function zoner_get_main_nav() {
		if ( has_nav_menu( 'primary' ) ) {
			 wp_nav_menu( array( 
							'theme_location' 	=> 'primary', 
							'menu_class' 	 	=> 'nav navbar-nav', 
							'container'		 	=> 'nav', 
							'container_class' 	=> 'collapse navbar-collapse bs-navbar-collapse navbar-right', 
							'walker' 			=> new zoner_submenu_class())); 
		} else {
			?>
				<nav class="collapse navbar-collapse bs-navbar-collapse navbar-right">
					<ul class="nav navbar-nav">
						<?php wp_list_pages(array('title_li' => '', 'sort_column' => 'ID', 'walker' => new Zoner_Page_Walker())); ?>	
					</ul>
				</nav>	
			<?php	
		}							  
	}
}		


/*Search form*/
if ( ! function_exists( 'zoner_search_form' ) ) {
	function zoner_search_form( $form ) {
		$form = '';
							
		$form .= '<form role="search" method="get" id="searchform" class="searchform" action="' . home_url( '/' ) . '" >';
			$form .= '<div class="input-group">';
				$form .= '<input type="search" class="form-control" value="' . get_search_query() . '" name="s" id="s" placeholder="'.__('Enter Keyword', 'zoner').'"/>';
				$form .= '<span class="input-group-btn"><button class="btn btn-default search" type="button"><i class="fa fa-search"></i></button></span>';
			$form .= '</div><!-- /input-group -->';
		$form .= '</form>';
		return $form;
	}
} //zoner_search_form

if ( ! function_exists( 'zoner_ExludeSearchFilter' ) ) {
	function zoner_ExludeSearchFilter($query) {
		if ( !$query->is_admin && $query->is_search) {
			$query->set('post_type', 'post');
		}
		return $query;
	}
}	

if ( ! function_exists( 'zoner_kses_data' ) ) {
	function zoner_kses_data($text = null) {
		$allowed_tags = wp_kses_allowed_html( 'post' );
		return wp_kses($text, $allowed_tags);
	}
}

if ( ! function_exists( 'zoner_change_excerpt_more' ) ) {
	function zoner_change_excerpt_more( $more ) {
		return '&#8230;';
	}
}

if ( ! function_exists( 'zoner_modify_read_more_link' ) ) {
	function zoner_modify_read_more_link() {
		return '<a class="link-arrow" href="' . get_permalink() . '">'.__('Read More', 'zoner').'</a>';
	}
}	

if ( ! function_exists( 'zoner_get_footer_area_sidebars' ) ) {
	function zoner_get_footer_area_sidebars() {
		global $zoner_config;
		
		$footer_dynamic_sidebar = '';
		$zoner_sidebars_class = array();
		$total_sidebars_count = 0;
		$total_sidebars_count = $zoner_config['footer-widget-areas'];
		
		
		if ($total_sidebars_count != 0) {
			
			if ($total_sidebars_count == 1) {
				$zoner_sidebars_class[] = 'col-md-12';
				$zoner_sidebars_class[] = 'col-sm-12';
			} else if ($total_sidebars_count == 2) {
				$zoner_sidebars_class[] = 'col-md-6';
				$zoner_sidebars_class[] = 'col-sm-6';
			} else if ($total_sidebars_count == 3) {
				$zoner_sidebars_class[] = 'col-md-4';
				$zoner_sidebars_class[] = 'col-sm-4';
			} else if ($total_sidebars_count == 4) {
				$zoner_sidebars_class[] = 'col-md-3';
				$zoner_sidebars_class[] = 'col-sm-3';
			} else {
				$zoner_sidebars_class[] = 'col-md-3';
				$zoner_sidebars_class[] = 'col-sm-3';
			}
		
		
			ob_start();
						
			for ( $i = 1; $i <= intval( $total_sidebars_count ); $i++ ) {
				
				if (zoner_active_sidebar('footer-'.$i)) {
					echo '<div class="'.implode(' ', $zoner_sidebars_class).'">';
						zoner_sidebar('footer-'.$i);
					echo '</div>';
				}
			} 
			
			$footer_dynamic_sidebar = ob_get_clean();
			if (!empty($footer_dynamic_sidebar)) {
			
			?>
			
				<section id="footer-main">
					<div class="container">
						<div class="row">		
							<?php echo $footer_dynamic_sidebar; ?>		
						</div>	
					</div>	
				</section>	
			<?php 
			
			}
		}
	}
}	


if ( ! function_exists( 'zoner_get_social' ) ) {
	function zoner_get_social() {
		global $zoner_config;
		$ftext = $fsocial = $out_ftext = ''; 
		$out_ = '';
		
		if (!empty($zoner_config['footer-text'])) {
			$ftext = zoner_kses_data(stripslashes($zoner_config['footer-text']));
			
			if (is_home() || is_front_page()) {
				$out_ftext .= $ftext;
			} else {
				$out_ftext .= '<nofollow>';
					$out_ftext .= $ftext;
				$out_ftext .= '</nofollow>';
				
			}
		}	
		
		if (!empty($zoner_config['footer-issocial'])) {
			if ($zoner_config['footer-issocial']) {
				$fsocial .= '<div class="social pull-right">';
					$fsocial .= '<div class="icons">';
					if (!empty($zoner_config['facebook-url'])) 	{ $fsocial .= '<a title="Facebook" 	href="'.esc_url($zoner_config['facebook-url']).'"><i class="icon social_facebook"></i></a>'; }	
					if (!empty($zoner_config['twitter-url'])) 	{ $fsocial .= '<a title="Twitter" 	href="'.esc_url($zoner_config['twitter-url']).'"><i class="icon social_twitter"></i></a>'; }	
					if (!empty($zoner_config['linkedin-url'])) 	{ $fsocial .= '<a title="Linked In" href="'.esc_url($zoner_config['linkedin-url']).'"><i class="icon social_linkedin"></i></a>'; }	
					if (!empty($zoner_config['myspace-url'])) 	{ $fsocial .= '<a title="My space" 	href="'.esc_url($zoner_config['myspace-url']).'"><i class="icon social_myspace"></i></a>'; }	
					if (!empty($zoner_config['gplus-url'])) 	{ $fsocial .= '<a title="Google+"	href="'.esc_url($zoner_config['gplus-url']).'"><i class="icon social_googleplus"></i></a>'; }	
					if (!empty($zoner_config['dribbble-url'])) 	{ $fsocial .= '<a title="Dribble" 	href="'.esc_url($zoner_config['dribbble-url']).'"><i class="icon social_dribbble"></i></a>';	}						
					if (!empty($zoner_config['flickr-url'])) 	{ $fsocial .= '<a title="Flickr" 	href="'.esc_url($zoner_config['flickr-url']).'"><i class="icon social_flickr"></i></a>'; }						
					if (!empty($zoner_config['youtube-url'])) 	{ $fsocial .= '<a title="YouTube" 	href="'.esc_url($zoner_config['youtube-url']).'"><i class="icon social_youtube"></i></a>'; }						
					if (!empty($zoner_config['delicious-url'])) 	{ $fsocial .= '<a title="Delicious" href="'.esc_url($zoner_config['delicious-url']).'"><i class="icon social_delicious"></i></a>'; }						
					if (!empty($zoner_config['deviantart-url']))	{ $fsocial .= '<a title="Deviantart" href="'.esc_url($zoner_config['deviantart-url']).'"><i class="icon social_deviantart"></i></a>'; }						
					if (!empty($zoner_config['rss-url'])) 			{ $fsocial .= '<a title="RSS" 		href="'.esc_url($zoner_config['rss-url']).'"><i class="icon social_rss"></i></a>'; }						
					if (!empty($zoner_config['instagram-url']))  { $fsocial .= '<a title="Instagram" href="'.esc_url($zoner_config['instagram-url']).'"><i class="icon social_instagram"></i></a>'; }						
					if (!empty($zoner_config['pinterest-url']))  { $fsocial .= '<a title="Pinterset" href="'.esc_url($zoner_config['pinterest-url']).'"><i class="icon social_pinterest"></i></a>'; }						
					if (!empty($zoner_config['vimeo-url'])) 		{ $fsocial .= '<a title="Vimeo" 	href="'.esc_url($zoner_config['vimeo-url']).'"><i class="icon social_vimeo"></i></a>'; }						
					if (!empty($zoner_config['picassa-url'])) 		{ $fsocial .= '<a title="Picassa" 	href="'.esc_url($zoner_config['picassa-url']).'"><i class="icon social_picassa"></i></a>'; }						
					if (!empty($zoner_config['social_tumblr']))		{ $fsocial .= '<a title="Tumblr" 	href="'.esc_url($zoner_config['social_tumblr']).'"><i class="icon social_tumblr"></i></a>'; }						
					if (!empty($zoner_config['email-address']))  	{ $fsocial .= '<a title="Email" 	href="mailto:'.esc_attr($zoner_config['email-address']).'"><i class="icon icon_mail_alt"></i></a>'; }						
					if (!empty($zoner_config['skype-username'])) 	{ $fsocial .= '<a title="Call to '.esc_attr($zoner_config['skype-username']).'" href="href="skype:'.esc_attr($zoner_config['skype-username']).'?call"><i class="icon social_skype"></i></a>'; }						
					$fsocial .= '</div><!-- /.icons -->';
				$fsocial .= '</div><!-- /.social -->';
			}
		}
		

		$out_ = '<section id="footer-copyright">';
			$out_ .= '<div class="container">';
				if (!empty($out_ftext)) {
					$out_ .= '<div class="copyright pull-left">'.$out_ftext.'</div><!-- /.copyright -->';
				} else {
					$out_ .= '<div class="copyright pull-left"><a title="'.get_bloginfo('name').'" href="'.site_url().'">'.$out_ftext.'</a></div><!-- /.copyright -->';
				}
				
				if ($fsocial != '') $out_ .= $fsocial;
				$out_ .='<span class="go-to-top pull-right"><a href="#page-top" class="roll">' . __('Go to top', 'zoner') . '</a></span>';
				
			$out_ .= '</div><!-- /.container -->';
		$out_ .= '</section>';
		
		echo $out_;
	}
}


if ( ! function_exists( 'zoner_visibilty_comments' ) ) {
	function zoner_visibilty_comments() {
		global $zoner_config, $post;
		
		if (!empty($zoner_config['pp-comments'])) {
			$is_comment = $zoner_config['pp-comments'];
			$post_type = get_post_type();
			if ( ( $is_comment == $post_type || $is_comment == 'both' ) && is_page() ) { 
				if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) { comments_template(); }
			}	
			
			if ( ( $is_comment == $post_type || $is_comment == 'both' ) && is_single() ) { 
				if ( ! post_password_required() && ( comments_open() || get_comments_number() ) ) { comments_template(); }
			}	
		}	
	}
}

if ( ! function_exists( 'zoner_breadcrumbs_generate' ) ) {
	function zoner_breadcrumbs_generate($args = array()) {
		global $wp_query, 
			   $wp_rewrite;
		
		$breadcrumb = '';
		$trail = array();
		
		$path = '';
		$defaults = array(
			'separator' 	  => '',
			'before' 		  => false,
			'after'  		  => false,
			'front_page' 	  => true,
			'show_home' 	  => __( 'Home', 'zoner' ),
			'echo' 			  => true, 
			'show_posts_page' => true
		);


		if ( is_singular() )$defaults["singular_{$wp_query->post->post_type}_taxonomy"] = false;

		extract( wp_parse_args( $args, $defaults ) );

		if ( !is_front_page() && $show_home )
			$trail[] = '<li><a href="' . esc_url( home_url() ) . '" title="' . esc_attr( get_bloginfo( 'name' ) ) . '" rel="home" class="trail-begin">' . esc_html( $show_home ) . '</a></li>';

		/* If viewing the front page of the site. */
		if ( is_front_page() ) {
			if ( !$front_page )
				$trail = false;
			elseif ( $show_home )
				$trail['trail_end'] = "{$show_home}";
		}

		/* If viewing the "home"/posts page. */
		elseif ( is_home() ) {
			$home_page = get_page( $wp_query->get_queried_object_id() );
			$trail 	   = array_merge( $trail, zoner_breadcrumbs_get_parents( $home_page->post_parent, '' ) );
			$trail['trail_end'] = get_the_title( $home_page->ID );
		}

		/* If viewing a singular post (page, attachment, etc.). */
		elseif ( is_singular() ) {
			$post = $wp_query->get_queried_object();
			$post_id = absint( $wp_query->get_queried_object_id() );
			$post_type = $post->post_type;
			$parent = $post->post_parent;
			
			if ( 'page' !== $post_type && 'post' !== $post_type ) {
				
				$post_type_object = get_post_type_object( $post_type );
				if ( 'post' == $post_type || 'attachment' == $post_type || ( $post_type_object->rewrite['with_front'] && $wp_rewrite->front ) ) $path .= trailingslashit( $wp_rewrite->front );
				if ( !empty( $post_type_object->rewrite['slug'] ) ) $path .= $post_type_object->rewrite['slug'];
				if ( !empty( $path ) && '/' != $path ) $trail = array_merge( $trail, zoner_breadcrumbs_get_parents( '', $path ) );
				if ( !empty( $post_type_object->has_archive ) && function_exists( 'get_post_type_archive_link' ) ) $trail[] = '<li><a href="' . get_post_type_archive_link( $post_type ) . '" title="' . esc_attr( $post_type_object->labels->name ) . '">' . $post_type_object->labels->name . '</a></li>';
			}

			/* If the post type path returns nothing and there is a parent, get its parents. */
			if ( empty( $path ) && 0 !== $parent || 'attachment' == $post_type ) $trail = array_merge( $trail, zoner_breadcrumbs_get_parents( $parent, '' ) );

			/* Toggle the display of the posts page on single blog posts. */		
			if ( 'post' == $post_type && $show_posts_page == true && 'page' == get_option( 'show_on_front' ) ) {
				$posts_page = get_option( 'page_for_posts' );
				if ( $posts_page != '' && is_numeric( $posts_page ) ) {
					 $trail = array_merge( $trail, zoner_breadcrumbs_get_parents( $posts_page, '' ) );
				}
			}

			/* Display terms for specific post type taxonomy if requested. */
			if ( isset( $args["singular_{$post_type}_taxonomy"] ) && $terms = get_the_term_list( $post_id, $args["singular_{$post_type}_taxonomy"], '', ', ', '' ) ) $trail[] = $terms;

			/* End with the post title. */
			$post_title = get_the_title( $post_id ); // Force the post_id to make sure we get the correct page title.
			if ( !empty( $post_title ) ) $trail['trail_end'] = $post_title;
		}

		/* If we're viewing any type of archive. */
		elseif ( is_archive() ) {
			
			/* If viewing a taxonomy term archive. */
			if ( is_tax() || is_category() || is_tag() ) {

				/* Get some taxonomy and term variables. */
				$term = $wp_query->get_queried_object();
				$taxonomy = get_taxonomy( $term->taxonomy );

				/* Get the path to the term archive. Use this to determine if a page is present with it. */
				if ( is_category() )
					$path = get_option( 'category_base' );
				elseif ( is_tag() )
					$path = get_option( 'tag_base' );
				else {
					if ( $taxonomy->rewrite['with_front'] && $wp_rewrite->front )
						$path = trailingslashit( $wp_rewrite->front );
					$path .= $taxonomy->rewrite['slug'];
				}

				/* Get parent pages by path if they exist. */
				if ( $path )
					$trail = array_merge( $trail, zoner_breadcrumbs_get_parents( '', $path ) );

				/* If the taxonomy is hierarchical, list its parent terms. */
				if ( is_taxonomy_hierarchical( $term->taxonomy ) && $term->parent )
					$trail = array_merge( $trail, zoner_breadcrumbs_get_term_parents( $term->parent, $term->taxonomy ) );

				/* Add the term name to the trail end. */
				$trail['trail_end'] = $term->name;
			}

			/* If viewing a post type archive. */
			elseif ( function_exists( 'is_post_type_archive' ) && is_post_type_archive() ) {
				
				/* Get the post type object. */
				$post_type_object = get_post_type_object( get_query_var( 'post_type' ) );

				/* If $front has been set, add it to the $path. */
				if ( $post_type_object->rewrite['with_front'] && $wp_rewrite->front )
					$path .= trailingslashit( $wp_rewrite->front );

				/* If there's a slug, add it to the $path. */
				if ( !empty( $post_type_object->rewrite['archive'] ) )
					$path .= $post_type_object->rewrite['archive'];

				/* If there's a path, check for parents. */
				if ( !empty( $path ) && '/' != $path )
					$trail = array_merge( $trail, zoner_breadcrumbs_get_parents( '', $path ) );

				/* Add the post type [plural] name to the trail end. */
				$trail['trail_end'] = $post_type_object->labels->name;
			}

			/* If viewing an author archive. */
			elseif ( is_author() ) {

				/* If $front has been set, add it to $path. */
				if ( !empty( $wp_rewrite->front ) )
					$path .= trailingslashit( $wp_rewrite->front );

				/* If an $author_base exists, add it to $path. */
				if ( !empty( $wp_rewrite->author_base ) )
					$path .= $wp_rewrite->author_base;

				/* If $path exists, check for parent pages. */
				if ( !empty( $path ) )
					$trail = array_merge( $trail, zoner_breadcrumbs_get_parents( '', $path ) );

				/* Add the author's display name to the trail end. */
				$trail['trail_end'] = get_the_author_meta( 'display_name', get_query_var( 'author' ) );
			}

			/* If viewing a time-based archive. */
			elseif ( is_time() ) {

				if ( get_query_var( 'minute' ) && get_query_var( 'hour' ) )
					$trail['trail_end'] = get_the_time( __( 'g:i a', 'zoner' ) );

				elseif ( get_query_var( 'minute' ) )
					$trail['trail_end'] = sprintf( __( 'Minute %1$s', 'zoner' ), get_the_time( __( 'i', 'zoner' ) ) );

				elseif ( get_query_var( 'hour' ) )
					$trail['trail_end'] = get_the_time( __( 'g a', 'zoner' ) );
			}

			/* If viewing a date-based archive. */
			elseif ( is_date() ) {

				/* If $front has been set, check for parent pages. */
				if ( $wp_rewrite->front )
					$trail = array_merge( $trail, zoner_breadcrumbs_get_parents( '', $wp_rewrite->front ) );

				if ( is_day() ) {
					$trail[] = '<li><a href="' . get_year_link( get_the_time( 'Y' ) ) . '" title="' . get_the_time( esc_attr__( 'Y', 'zoner' ) ) . '">' . get_the_time( __( 'Y', 'zoner' ) ) . '</a></li>';
					$trail[] = '<li><a href="' . get_month_link( get_the_time( 'Y' ), get_the_time( 'm' ) ) . '" title="' . get_the_time( esc_attr__( 'F', 'zoner' ) ) . '">' . get_the_time( __( 'F', 'zoner' ) ) . '</a></li>';
					$trail['trail_end'] = get_the_time( __( 'j', 'zoner' ) ) ;
				}

				elseif ( get_query_var( 'w' ) ) {
					$trail[] = '<li><a href="' . get_year_link( get_the_time( 'Y' ) ) . '" title="' . get_the_time( esc_attr__( 'Y', 'zoner' ) ) . '">' . get_the_time( __( 'Y', 'zoner' ) ) . '</a></li>';
					$trail['trail_end'] = sprintf( __( 'Week %1$s', 'zoner' ), get_the_time( esc_attr__( 'W', 'zoner' ) ) );
				}

				elseif ( is_month() ) {
					$trail[] = '<li><a href="' . get_year_link( get_the_time( 'Y' ) ) . '" title="' . get_the_time( esc_attr__( 'Y', 'zoner' ) ) . '">' . get_the_time( __( 'Y', 'zoner' ) ) . '</a></li>';
					$trail['trail_end'] = get_the_time( __( 'F', 'zoner' ) );
				}

				elseif ( is_year() ) {
					$trail['trail_end'] = get_the_time( __( 'Y', 'zoner' ) ) ;
				}
			}
		}

		/* If viewing search results. */
		elseif ( is_search() )
			$trail['trail_end'] = '<li>' . sprintf( __( 'Search results for &quot;%1$s&quot;', 'zoner' ), esc_attr( get_search_query() ) ) . '</li>';

		/* If viewing a 404 error page. */
		elseif ( is_404() )
			$trail['trail_end'] =  __( '404 Not Found', 'zoner' );

		/* Connect the breadcrumb trail if there are items in the trail. */
		if ( is_array( $trail ) ) {

			/* If $before was set, wrap it in a container. */
			if ( !empty( $before ) )
				$breadcrumb .= '<span class="trail-before">' . wp_kses_post( $before ) . '</span> ';

			/* Wrap the $trail['trail_end'] value in a container. */
			if ( !empty( $trail['trail_end'] ) && !is_search() )
				$trail['trail_end'] = '<li class="active"><span class="trail-end">' . wp_kses_post( $trail['trail_end'] ) . '</span></li>';

			/* Format the separator. */
			if ( !empty( $separator ) )
				$separator = '<li><span class="sep">' . wp_kses_post( $separator ) . '</span></li>';

			/* Join the individual trail items into a single string. */
			$breadcrumb .= join( " {$separator} ", $trail );

			/* If $after was set, wrap it in a container. */
			if ( !empty( $after ) )
				$breadcrumb .= '<li><span class="trail-after">' . wp_kses_post( $after ) . '</span></li>';

			/* Close the breadcrumb trail containers. */
		}

		$breadcrumb = '<!-- Breadcrumb --><div class="container"><ol class="breadcrumb">' . $breadcrumb . '</ol></div>';

		/* Output the breadcrumb. */
		if ( $echo ) echo $breadcrumb; else return $breadcrumb;
	} 
}

if ( ! function_exists( 'zoner_breadcrumbs_get_parents' ) ) {
	function zoner_breadcrumbs_get_parents( $post_id = '', $path = '' ) {
		$trail = array();

		if ( empty( $post_id ) && empty( $path ) ) return $trail;
		if ( empty( $post_id ) ) {
			$parent_page = get_page_by_path( $path );
			if( empty( $parent_page ) ) $parent_page = get_page_by_title ( $path );
			if( empty( $parent_page ) ) $parent_page = get_page_by_title ( str_replace( array('-', '_'), ' ', $path ) );
			if ( !empty( $parent_page ) ) $post_id = $parent_page->ID;
		}

		if ( $post_id == 0 && !empty( $path ) ) {
			$path = trim( $path, '/' );
			preg_match_all( "/\/.*?\z/", $path, $matches );
			if ( isset( $matches ) ) {
				$matches = array_reverse( $matches );
				foreach ( $matches as $match ) {
					if ( isset( $match[0] ) ) {
						$path = str_replace( $match[0], '', $path );
						$parent_page = get_page_by_path( trim( $path, '/' ) );
						if ( !empty( $parent_page ) && $parent_page->ID > 0 ) {
							$post_id = $parent_page->ID;
							break;
						}
					}
				}
			}
		}
	
		while ( $post_id ) {
				$page = get_page( $post_id );
				$parents[]  = '<li><a href="' . get_permalink( $post_id ) . '" title="' . esc_attr( get_the_title( $post_id ) ) . '">' . esc_html( get_the_title( $post_id ) ) . '</a></li>';
				$post_id = $page->post_parent;
		}

		if ( isset( $parents ) ) $trail = array_reverse( $parents );
		return $trail;
	} 
}

if ( ! function_exists( 'zoner_breadcrumbs_get_term_parents' ) ) {
	function zoner_breadcrumbs_get_term_parents( $parent_id = '', $taxonomy = '' ) {
		$trail = array();
		$parents = array();

		if ( empty( $parent_id ) || empty( $taxonomy ) ) return $trail;
		while ( $parent_id ) {
			$parent = get_term( $parent_id, $taxonomy );
			$parents[] = '<li><a href="' . get_term_link( $parent, $taxonomy ) . '" title="' . esc_attr( $parent->name ) . '">' . $parent->name . '</a></li>';
			$parent_id = $parent->parent;
		}

		if ( !empty( $parents ) ) $trail = array_reverse( $parents );
		return $trail;
	} 
}

if ( ! function_exists( 'zoner_add_breadcrumbs' ) ) {
	function zoner_add_breadcrumbs() {
		if (class_exists('ReduxFramework')) {
			global $zoner_config;
			if (!empty($zoner_config['pp-breadcrumbs'])) {
				if ($zoner_config['pp-breadcrumbs']) {
					if (!is_front_page()) zoner_breadcrumbs_generate();
				}
			}
		} else {
			if (!is_front_page()) zoner_breadcrumbs_generate();
		}
	}
}

if ( ! function_exists( 'zoner_seconadry_navigation' ) ) {
	function zoner_seconadry_navigation() {
		global $zoner_config, $current_user, $wp_users, $zoner;
		
		get_currentuserinfo();
		
		$site_url = site_url('');
		if (function_exists('icl_get_home_url'))
		$site_url = icl_get_home_url();
		?>
		<div class="secondary-navigation">
			<div class="container">
				<div class="contact">
					<?php if (!empty($zoner_config['header-phone'])) { ?>	
						<figure><strong><?php _e('Phone', 'zoner'); ?>:</strong><?php echo $zoner_config['header-phone']; ?></figure>
					<?php } ?>
					<?php if (!empty($zoner_config['header-email'])) { ?>	
						<figure><strong><?php _e('Email', 'zoner'); ?>:</strong><a href="mailto:<?php echo $zoner_config['header-email']; ?>"><?php echo $zoner_config['header-email']; ?></a></figure>
					<?php } ?>
				</div>
				<div class="user-area">
					<div class="actions">
						<?php if ( is_user_logged_in() ) { ?>
							<a class="promoted" href="<?php echo add_query_arg(array('profile-page' => 'my_profile'), get_author_posts_url($current_user->ID)); ?>"><i class="fa fa-user"></i> <strong><?php echo $current_user->display_name; ?></strong></a>
							<a class="promoted logout" href="<?php echo wp_logout_url(esc_url($site_url)); ?>" title="<?php _e('Sign Out', 'zoner'); ?>"><?php _e('Sign Out', 'zoner'); ?></a>
						<?php } ?>
					</div>
					<?php 
						if (isset($zoner_config['wmpl-flags-box'])) {
							if ( function_exists( 'icl_get_languages' ) ) {
								$languages = icl_get_languages('skip_missing=0&orderby=code');
									if(!empty($languages)) {
					?>
										<div class="language-bar">
											<?php 
									
												foreach($languages as $l) { 
													if($l['country_flag_url']){
														if(!$l['active']) { echo '<a href="'.$l['url'].'">'; } else { echo '<a class="active" href="'.$l['url'].'">'; };
															echo '<img src="'.$l['country_flag_url'].'" height="11" alt="'.$l['language_code'].'" width="16" />';
														if(!$l['active']) echo '</a>';
													}
									
												}
											?>
										</div>
					<?php 
								}
							}
						}
					?>	
				</div>
				
				
			</div>
		</div>	
		
		<?php
	}
}
			

if ( ! function_exists( 'zoner_before_content' ) ) {
	function zoner_before_content() {
		$elem_class = array();
		$elem_class[] = 'wpb_row';
		$elem_class[] = 'vc_row-fluid';
		if (is_front_page()) $elem_class[] = 'block';
	?>
		<section class="<?php echo implode(' ', $elem_class); ?>">
			<div class="container">
				<div class="row">	
	<?php	        
		
	}
}


if ( ! function_exists( 'zoner_after_content' ) ) {
	function zoner_after_content () {
	
	?>
				</div>
			</div>
		</section>		
	<?php	        
		
	}
}

if ( ! function_exists( 'zoner_zoner_get_sidebar_part' ) ) {
	function zoner_get_sidebar_part($sidebar) {
		global $zoner_config, $zoner, $prefix;
	?>
		<div id="sidebar" class="sidebar">
			<?php if (zoner_active_sidebar($sidebar)) zoner_sidebar($sidebar); ?>	
		</div>
	<?php
	}
}		
	
if ( ! function_exists( 'zoner_get_content_part' ) ) {
	function zoner_get_content_part($type_in) {
		$title = ''; 
		if ( have_posts() ) {
			
			$page_for_posts = get_option( 'page_for_posts' ); 
			$page_on_front  = get_option('page_on_front');
			
			if (is_home() && !empty($page_for_posts)) { 
				echo '<header><h1>'.get_the_title($page_for_posts).'</h1></header>'; 
			}  elseif (is_front_page() && empty($page_for_posts) && empty($page_on_front)) {
				echo '<header><h1>'.__('Latest posts', 'zoner').'</h1></header>'; 
			}
			
			if (is_archive()) {
				if ( is_day() ) :
					$title = sprintf( __( 'Daily Archives: %s', 'zoner' ),   get_the_date() );
				elseif ( is_month() ) :
					$title = sprintf( __( 'Monthly Archives: %s', 'zoner' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'zoner' ) ) );
				elseif ( is_year() ) :
					$title = sprintf( __( 'Yearly Archives: %s', 'zoner' ),  get_the_date( _x( 'Y', 'yearly archives date format', 'zoner' ) ) );
				else :
					$title = __( 'Archives', 'zoner' );
				endif;
			}
			
			
			
			if (is_category()) $title = sprintf( __( 'Category: %s', 'zoner' ), single_cat_title( '', false ) );
			if (is_search()) $title = sprintf( __( 'Search Results for: %s', 'zoner' ), get_search_query() );
			if (is_tag()) $title = sprintf( __( 'Tag Archives: %s', 'zoner' ), single_tag_title( '', false ) );
			
			if ($title != '') echo '<header><h1>'.$title.'</h1></header>';
			
				while ( have_posts() ) : the_post();
					if ($type_in == 'page') {
						get_template_part( 'content', 'page' );
					} elseif ($type_in == 'front-page') {
						the_content();
					} else {
						get_template_part( 'content', get_post_format() );
					}						
				endwhile;
				
			if ($type_in == 'post') zoner_paging_nav(); 
		} else {
			echo '<header><h1>'. __('Nothing Found', 'zoner').'</h1></header>';
			get_template_part( 'content', 'none' );
		}
	}
}	
	
if ( ! function_exists( 'zoner_the_main_content' ) ) {
	function zoner_the_main_content () {
		global $zoner_config, $prefix, $post;
		$layout = 3;
		$sidebar = 'secondary';
		$add_wrapper = true;
		
		$type   = get_post_type( $post );
		
		if ($type == 'page') {
				$page_layout = get_post_meta($post->ID, $prefix.'pages_layout', true);
			if ($page_layout) $layout = $page_layout;
			$sidebar = 'secondary';
		} else {
			if (!empty($zoner_config['pp-post'])) $layout = (int)$zoner_config['pp-post'];
			$sidebar = 'primary';
		}						
		
		$page_on_front = get_option('page_on_front');
		if (is_front_page() && !empty($page_on_front)) {
			$page_layout = get_post_meta($post->ID, $prefix.'pages_layout', true);
			
			if ($page_layout) $layout  = $page_layout;
			
			$type 	 = 'front-page';
			$sidebar = 'primary';
			
			$front_page_content = $post->post_content;
			if (strpos($front_page_content, 'vc_row') !== false) $add_wrapper = false;
				
		}
		
		
		if ($layout == -1) {
			zoner_get_content_part($type);
		} elseif ($layout == 1) {
	?>
		<?php if ($add_wrapper) { ?>
			<div class="col-md-12 col-sm-12">
		<?php } ?>
			<?php zoner_get_content_part($type);?> 
		<?php if ($add_wrapper) { ?>
			</div>
		<?php } ?>	
	<?php 
		} else if ($layout == 2) { 
	?>	
		<div class="col-md-3 col-sm-3">
			<?php zoner_get_sidebar_part($sidebar); ?>
		</div>
		<div class="col-md-9 col-sm-9">
			<?php zoner_get_content_part($type); ?>
		</div>
			
	<?php 
		} else if ($layout == 3) {
	?>
		<div class="col-md-9 col-sm-9">
			<?php zoner_get_content_part($type); ?>
		</div>
		<div class="col-md-3 col-sm-3">
			<?php zoner_get_sidebar_part($sidebar); ?>
		</div>
		
			
	<?php	
		}
	}	
}

if ( ! function_exists( 'zoner_nav_parent_class' ) ) {			
	function zoner_nav_parent_class( $classes, $item ) {
		global $wpdb, $zoner, $zoner_config, $prefix;
		$cpt_name = array('');
		if ( in_array(get_post_type(), $cpt_name) && ! is_admin() ) {

			$classes = str_replace( 'current_page_parent', '', $classes );
			$page    = get_page_by_title( $item->title, OBJECT, 'page' );
			if (!empty($page->post_name)) {
				if($page->post_name === get_post_type())  $classes[] = 'current_page_parent';
			}	
		}
		
		return $classes;
	} 
}

if ( ! function_exists( 'zoner_set_excerpt_length' ) ) {				
	function zoner_set_excerpt_length( $length ) {
		return 75;
	}
}

/*Post password protected*/
if ( ! function_exists( 'zoner_password_protect_form' ) ) {				
	function zoner_password_protect_form() {
		global $post;
		$out = '';
		$label = 'pwbox-'.( empty( $post->ID ) ? rand() : $post->ID );
		
		$out .= '<form role="form" class="protected-form" action="' . esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) ) . '" method="post">';
			
			$out .= '<div class="panel panel-default">';
				$out .= '<div class="panel-heading">' . __('This is password protected post', 'zoner') . '</div>';
				$out .= '<div class="panel-body">'  . __("This content is password protected. To view it please enter your password below:", 'zoner' ) . '</div>';
				$out .= '<div class="panel-body">';
					$out .= '<input name="post_password" id="'. $label .'" type="password" size="20" maxlength="20" placeholder="'.__('Password', 'zoner').'"/>';
				$out .= '</div>';
	  
				$out .= '<div class="form-group clearfix">';
					$out .= '<div class="col-md-12">';
						$out .= '<input type="submit" name="Submit" class="btn pull-right btn-default" value="' . esc_attr__( "Submit", 'zoner' ) . '" />';
					$out .= '</div>';
				$out .= '</div>';	
			$out .= '</div>';

		$out .= '</form>';
		
		return $out;
	}
}


if ( ! function_exists( 'zoner_post_chat' ) ) {				
	function zoner_post_chat($content = null) {
		global $post;
		$format = null;
		if (isset($post)) $format = get_post_format( $post->ID );
		$cnt = 0;
		
		if ($format == 'chat') {
			if (($post->post_type == 'post') && ($format == 'chat')) {
					remove_filter ('the_content',  'wpautop');
					$chatoutput = "<dl class=\"chat\">\n";
					$split = preg_split("/(\r?\n)+|(<br\s*\/?>\s*)+/", $content);
						foreach($split as $haystack) {
							if (strpos($haystack, ":")) {
								$string 	= explode(":", trim($haystack), 2);
								$who 		= strip_tags(trim($string[0]));
								$what 		= strip_tags(trim($string[1]));
								$chatoutput = $chatoutput . "<dt><i class='fa fa-weixin'></i><span class='chat-author'><strong>$who:</strong></span></dt><dd>$what</dd>\n";
							}
							else {
								$chatoutput = $chatoutput . $haystack . "\n";
							}
							$cnt++;
							
							if (!is_single()) {
								if ($cnt > 2) break;
							}	
						}
						$content = $chatoutput . "</dl>\n";
						return $content;
			}
		} else {
			return $content;
		}
	}
}

if ( ! function_exists( 'zoner_add_google_analytics' ) ) {				
	function zoner_add_google_analytics() {
		global $zoner_config, $prefix;
		
		if (!empty($zoner_config['tracking-code'])) {
		?>

		<script type="text/javascript">
			var _gaq = _gaq || [];
			_gaq.push(['_setAccount', '<?php echo esc_js($zoner_config['tracking-code']); ?>']);
			_gaq.push(['_trackPageview']);
			(function() {
				var ga = document.createElement('script'); 
					ga.type = 'text/javascript'; 
					ga.async = true;
					ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
				var s = document.getElementsByTagName('script')[0];
					s.parentNode.insertBefore(ga, s);
			})();
		</script>

		<?php
		}
	}
}

if ( ! function_exists( 'zoner_get_delay_interval' ) ) {				
	function zoner_get_delay_interval($interval = 0) {
		$time_class = '';		
		if ($interval > 0) {
			$time_class = 'after '.$interval.'s';
		}
		return $time_class;		
	}
}

if ( ! function_exists( 'zoner_add_favicon' ) ) {				
	function zoner_add_favicon() {
		global $zoner_config, $prefix;
		
		if( !empty($zoner_config['favicon'])) 				echo '<link rel="shortcut icon" href="' .  	esc_url($zoner_config['favicon']['url'])  . '"/>' . "\n";
		if( !empty($zoner_config['favicon-iphone'])) 		echo '<link rel="apple-touch-icon" href="'. esc_url($zoner_config['favicon-iphone']['url']) .'"> '. "\n"; 
		if( !empty($zoner_config['favicon-iphone-retina'])) 	echo '<link rel="apple-touch-icon" sizes="114x114" 	href="'.  esc_url($zoner_config['favicon-iphone-retina']['url']) .' "> '. "\n"; 
		if( !empty($zoner_config['favicon-ipad'])) 			echo '<link rel="apple-touch-icon" sizes="72x72" 	href="'. esc_url($zoner_config['favicon-ipad']['url']) .'"> '. "\n"; 
		if( !empty($zoner_config['favicon-ipad-retina']))	echo '<link rel="apple-touch-icon" sizes="144x144" 	href="'. esc_url($zoner_config['favicon-ipad-retina']['url'])  .'"> '. "\n";  
	 
	}
}

if ( ! function_exists( 'zoner_img_caption' ) ) {				
	function zoner_img_caption( $empty_string, $attributes, $content ){
		extract(shortcode_atts(array(
			'id' 		=> '',
			'align' 	=> 'alignnone',
			'width' 	=> '',
			'caption' 	=> ''
		), $attributes));
  
		if ( empty($caption) ) return $content;
		if ($id ) $id = 'id="' . esc_attr($id) . '" ';
		return '<div ' . $id . 'class="wp-caption ' . esc_attr($align) . '" style="width:'.$width.'px;">' . do_shortcode( $content ) . '<p class="wp-caption-text">' . $caption . '</p></div>';
	}
}



if ( ! function_exists( 'zoner_curPageURL' ) ) {				
	function zoner_curPageURL() {
		$pageURL = 'http';
		if (isset($_SERVER["HTTPS"])) {$pageURL .= "s";}
			$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
	 return $pageURL;
	}
}

if ( ! function_exists( 'zoner_add_query_var' ) ) {				
	function zoner_add_query_var($public_query_vars) {
		$public_query_vars[] = 'profile-page';
		
		$public_query_vars[] = 'invitehash';
		
		$public_query_vars[] = 'created_user';
		
		return apply_filters('zoner_query_vars', $public_query_vars);
	}
}	

if ( ! function_exists( 'zoner_add_rewrite_rules' ) ) {				
	function zoner_add_rewrite_rules() {
		 add_rewrite_tag ('%invitehash%', '([^/]*)/?');
		 add_rewrite_rule('^invitehash/([^/]*)/?', 'index.php?invitehash=$matches[1]', 'top' );
		 
		 add_rewrite_tag ('%created_user%', '([^/]*)/?');
		 add_rewrite_rule('^created_user/([^/]*)/?', 'index.php?created_user=$matches[1]', 'top' );
		
		 flush_rewrite_rules();
	}
}

if ( ! function_exists( 'zoner_insert_attachment' ) ) {				
	function zoner_insert_attachment($file_handler, $post_id, $setthumb = false) {
		if ($_FILES[$file_handler]['error'] !== UPLOAD_ERR_OK) __return_false();
 
		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		require_once(ABSPATH . "wp-admin" . '/includes/file.php');
		require_once(ABSPATH . "wp-admin" . '/includes/media.php');
 
		$attach_id = media_handle_upload( $file_handler, $post_id );
		if ($setthumb) set_post_thumbnail($post_id, $attach_id);
		return $attach_id;
	}
	
}

if ( ! function_exists( 'zoner_get_author_information' ) ) {				
	function zoner_get_author_information() {	
		global $zoner_config, $prefix, $zoner;
		?>
		<?php if ( have_posts() ) : ?>
			<?php the_post(); ?>
			<section id="author-detail" class="author-detail">
				<header><h1><?php printf( __( 'All posts by "%s"', 'zoner' ), get_the_author()); ?></h1></header>
			</section>	
				
			<?php if ( get_the_author_meta( 'description' ) ) : ?>
				<div class="author-description"><?php the_author_meta( 'description' ); ?></div>
			<?php endif; ?>

			<?php
				rewind_posts();

				while ( have_posts() ) : the_post();
					get_template_part( 'content', get_post_format() );
				endwhile;
				zoner_paging_nav();
				else :
					get_template_part( 'content', 'none' );
				endif;
	}
}	


/*Blog*/

/*Get Post Thumbnail*/
if ( ! function_exists( 'zoner_get_post_thumbnail' ) ) {				
	function zoner_get_post_thumbnail() {
		global $zoner_config, $prefix, $zoner, $post;
		
		if ( has_post_thumbnail() && ($zoner_config['pp-thumbnail'])) {
			$attachment_id = get_post_thumbnail_id( $post->ID );
			$post_thumbnail = wp_get_attachment_image_src( $attachment_id, 'full');
		?> 
			<?php if (!is_single()) { ?>
				<a href="<?php the_permalink();?>">	 
			<?php } ?>
				<img src="<?php echo $post_thumbnail[0]; ?>" alt="" />
			<?php if (!is_single()) { ?>
				</a> 
			<?php } ?>
		<?php	
		} 
	}
}	

/*Get title*/
if ( ! function_exists( 'zoner_get_post_title' ) ) {				
	function zoner_get_post_title() {
		global $zoner_config, $prefix, $zoner;
		
		$sticky_icon = '';
		
		if (is_sticky()) $sticky_icon = '<span class="sticky-wrapper"><i class="fa fa-paperclip"></i></span>';
		
		if ( is_single() ) :
			the_title( '<header><h1 class="entry-title">' . $sticky_icon, '</h1></header>' );
		else :
			the_title( '<header><a href="' . esc_url( get_permalink() ) . '"><h2>'. $sticky_icon, '</h2></a></header>' );
		endif;
	}
}	

/*Meta*/
if ( ! function_exists( 'zoner_get_post_meta' ) ) {				
	function zoner_get_post_meta() {
		global $zoner_config, $prefix, $zoner, $post;
		
			$archive_year  = get_the_time('Y'); 
			$archive_month = get_the_time('m'); 
					
		?>
			<figure class="meta">
				<?php if ($zoner_config['pp-authors']) { ?>
					<a class="link-icon" href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' )); ?>">
						<i class="fa fa-user"></i>
						<?php the_author(); ?>
					</a>
				<?php } ?>
				
				<?php if ($zoner_config['pp-date']) { ?>
					<a class="link-icon" href="<?php echo get_month_link( $archive_year, $archive_month ); ?>">
						<i class="fa fa-calendar"></i>
						<?php the_time('d/m/Y'); ?>
					</a>
				<?php } ?>
				<?php edit_post_link( '<i title="' . __("Edit", 'zoner') . '" class="fa fa-pencil-square-o"></i>'.__("Edit", 'zoner'), '', '' ); ?>
				<?php 
					 $tags = wp_get_post_tags( $post->ID);
					 if (!empty($tags) && ($zoner_config['pp-tags'])) {
				?>
					<div class="tags">
						<?php foreach($tags as $tag) {  ?>
							<a class="tag article" href="<?php echo get_tag_link($tag->term_id)?>"><?php echo $tag->name; ?></a>
						<?php } ?>
					</div>
				
				<?php } ?>

			</figure>
		<?php
	}
}	

/*Content none*/
if ( ! function_exists( 'zoner_get_post_none_content' ) ) {				
	function zoner_get_post_none_content() {
	?>
		<?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>
				<p><?php printf( __( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'zoner' ), admin_url( 'post-new.php' ) ); ?></p>
			<?php elseif ( is_search() ) : ?>
				<p><?php _e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'zoner' ); ?></p>
			<?php get_search_form(); ?>
			<?php else : ?>
				<p><?php _e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'zoner' ); ?></p>
			<?php get_search_form(); ?>
		<?php endif; ?>
	<?php	
	}
}	

/*Single About The Author*/	
if ( ! function_exists( 'zoner_get_post_about_author' ) ) {				
	function zoner_get_post_about_author() {
		global $zoner_config, $prefix, $zoner, $post;
	
	?>
		
		<section id="about-author">
			<header><h3><?php _e('About the Author', 'zoner'); ?></h3></header>
			<div class="post-author">
				<?php echo zoner_get_profile_avartar(get_the_author_meta( 'ID')); ?>
				<div class="wrapper">
					<header><?php the_author(); ?></header>
					<?php the_author_meta( 'description'); ?>
				</div>
			</div>
		</section>	
		
	<?php 
	}
}	

/*Read More*/
if ( ! function_exists( 'zoner_get_readmore_link' ) ) {				
	function zoner_get_readmore_link() {
		global $zoner_config, $prefix, $zoner, $post;
		?> 
			<a class="link-arrow" href="<?php the_permalink();?>"><?php _e('Read More', 'zoner'); ?></a>
		<?php	
	}
}

if ( ! function_exists( 'zoner_edit_post_link' ) ) {				
	function zoner_edit_post_link($output) {
		$output = str_replace('class="post-edit-link"', 'class="link-icon"', $output);
		return $output;
	}
}	

/*Remove admin bar*/
if ( ! function_exists( 'zoner_options_admin_bar' ) ) {				
	function zoner_options_admin_bar() {
		global $zoner_config, $prefix, $zoner;
		$vBarOptions = 1;
	
		if (!empty($zoner_config['adminbar-displayed'])) {
			$vBarOptions = (int) esc_attr($zoner_config['adminbar-displayed']);
		} 
		
		if ($vBarOptions == 2) {
			if (!is_admin() && !is_super_admin())
			add_filter('show_admin_bar', '__return_false');  																	 
		
		} elseif ($vBarOptions == 3) {
			add_filter('show_admin_bar', '__return_false');  																	 
		}
	}
}