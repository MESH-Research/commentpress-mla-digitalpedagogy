<?php /*
===============================================================
Commentpress Child Theme Functions
===============================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
---------------------------------------------------------------
NOTES

Example theme amendments and overrides.

---------------------------------------------------------------
*/


/** 
 * @description: augment the CommentPress Default Theme setup function
 * @todo: 
 *
 */
function cpchild_setup( 
	
) { //-->

	/** 
	 * Make theme available for translation.
	 * Translations can be added to the /languages/ directory of the child theme.
	 */
	load_theme_textdomain( 
	
		'commentpress-child-theme', 
		get_stylesheet_directory() . '/languages' 
		
	);

}

// add after theme setup hook
add_action( 'after_setup_theme', 'cpchild_setup' );






/** 
 * @description: override styles by enqueueing as late as we can
 * @todo:
 *
 */
function cpchild_enqueue_styles() {

	// init
	$dev = '';
	
	// check for dev
	if ( defined( 'SCRIPT_DEBUG' ) AND SCRIPT_DEBUG === true ) {
		$dev = '.dev';
	}
	
	// add child theme's css file
	wp_enqueue_style( 
	
		'cpchild_css', 
		get_stylesheet_directory_uri() . '/assets/css/style-overrides'.$dev.'.css',
		array( 'cp_reset_css' ),
		'1.0', // version
		'all' // media
	
	);

}

// add a filter for the above
add_filter( 'wp_enqueue_scripts', 'cpchild_enqueue_styles', 110 );



// adding clickable MLA logo to header
function my_child_template_header_body($path) {
	$path = get_stylesheet_directory() . '/assets/templates/header_body.php';
	return $path;
}
add_filter(	'cp_template_header_body', 'my_child_template_header_body' );


add_filter('show_admin_bar', '__return_false');  


/* We roll our own CommentpressCoreDisplay::list_pages() and wp_list_pages() 
 * here so that we can display a custom TOC that includes authors' names. 
 */  
function mla_list_pages( $exclude_pages = array() ) {

	global $commentpress_core;

	// get welcome page ID
	$welcome_id = $commentpress_core->db->option_get( 'cp_welcome_page' );

	// get front page
	$page_on_front = $commentpress_core->db->option_wp_get( 'page_on_front' );

	// print link to title page, if we have one and it's the front page
	if ( $welcome_id !== false AND $page_on_front == $welcome_id ) {

		// define title page
		$title_page_title = get_the_title( $welcome_id );

		// allow overrides
		$title_page_title = apply_filters( 'cp_title_page_title', $title_page_title );

		// echo list item
		echo '<li class="page_item page-item-'.$welcome_id.'"><a href="'.get_permalink( $welcome_id ).'">'.$title_page_title.'</a></li>';

	}

	// ALWAYS write subpages into page, even if they aren't displayed
	$depth = 0;

	// get pages to exclude
	$exclude = $commentpress_core->db->option_get( 'cp_special_pages' );

	// do we have any?
	if ( !$exclude ) { $exclude = array(); }

	// exclude title page, if we have one
	if ( $welcome_id !== false ) { $exclude[] = $welcome_id; }

	// did we get any passed to us?
	if ( !empty( $exclude_pages ) ) {

		// merge arrays
		$exclude = array_merge( $exclude, $exclude_pages );

	}

	$defaults = array( 
		'sort_order' => 'ASC',
		'sort_column' => 'menu_order, post_title',
		'hierarchical' => 1,
		'exclude' => implode( ',', $exclude ),
		'include' => '',
		'meta_key' => '',
		'meta_value' => '',
		'authors' => '',
		'child_of' => 0,
		'parent' => -1,
		'exclude_tree' => '',
		'number' => '',
		'offset' => 0,
		'post_type' => 'page',
		'post_status' => 'publish' 
	); 

	// use Wordpress function to echo
	$pages = get_pages( $defaults );

	$out = ''; 
	foreach ( $pages as $page ) { 
		if ( $page->post_parent ) { 
			$author = get_post_meta( $page->ID, 'author', true ); 
			$out .= sprintf( 
				'<li class="child-page"><a href="%s">%s (%s)</a></li>', 
				get_page_link( $page->ID ),
				$page->post_title, 
				$author
			); 
		} else { 
		$out .= sprintf( 
			'<li class="page"><a href="%s">%s</a></li>', 
			get_page_link( $page->ID ),
			$page->post_title
		); 
		} 
	} 
	echo $out; 
}
