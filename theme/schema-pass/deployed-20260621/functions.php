<?php
ini_set("mysql.trace_mode", "0");

ini_set("xdebug.var_display_max_children", '-1');
ini_set("xdebug.var_display_max_data", '-1');
ini_set("xdebug.var_display_max_depth", '-1');

remove_action('wp_head', 'wp_generator');

$GLOBALS['rsg-no-products-us-label'] = 'RunningWarehouse.Com';

$GLOBALS['rsg-no-products-us-men'] = 'https://www.runningwarehouse.com/?from=guru';

$GLOBALS['rsg-no-products-us-women'] = 'https://www.runningwarehouse.com/?from=guru';

add_rewrite_tag('%brands%','([^&]+)');

add_rewrite_tag('%shoes_type%','([^&]+)');

add_rewrite_tag('%both%','([^&]+)');
	
add_filter( 'wp_lazy_loading_enabled', '__return_false' );


add_action( 'rest_api_init', function () {
  register_rest_route( 'log_country_code/v1', '(?P<ip>[\d,\.]+)/(?P<country_code>\w+)/(?P<country_code_2>\w+)/', array(
    'methods' => 'GET',
    'callback' => 'log_country_code',
	'args' => array(
      'country_code' => array(
        'validate_callback' => 'is_numeric'
      ),
	  'country_code_2' => array(
        'validate_callback' => 'is_numeric'
      ),
    ),
	'permission_callback' => '__return_true',
  ) );
} );

function log_country_code( $params) {
	global $wpdb;
	$response = $wpdb->insert(
			'newguru_wp_log_country_code',
			[
				'ip'  => $params['ip'],
				'cloudfare_country' => $params['country_code'],
				'js_country'   => $params['country_code_2'],
				'tstamp' => current_time( 'mysql' ),
			],
			[ '%s', '%s', '%s', '%s' ]
		);
	
	$response = new WP_REST_Response($response.'--'.$params['ip'].'--'.$params['country_code'].'--'.$params['country_code_2']);
    $response->set_status(200);

    return $response;
}

add_filter('term_link', 'RSG_term_link_filter', 10, 3);
function RSG_term_link_filter( $url, $term, $taxonomy ) {
	if($taxonomy == 'brands')
		return $url . "all/";
	return $url;   
}


add_action( 'init', 'add_RSG_rewrite_rules' );

function add_RSG_rewrite_rules() {
	// 2022
	add_rewrite_rule('^reviews/road/([^/]+)/?$','index.php?post_type=reviews_road&name=$matches[1]','top');
	add_rewrite_rule('^reviews/training/([^/]+)/?$','index.php?post_type=reviews_training&name=$matches[1]','top');
	add_rewrite_rule('^reviews/trail/([^/]+)/?$','index.php?post_type=reviews_trail&name=$matches[1]','top');
	add_rewrite_rule('^reviews/gps/([^/]+)/?$','index.php?post_type=reviews_gps&name=$matches[1]','top');
	
	
	add_rewrite_rule('^reviews/page/([0-9]+)/?','index.php?page_id=589&paged=$matches[1]','top');
	add_rewrite_rule('^reviews/([^/]*)/([^/]*)/page/([0-9]+)/?','index.php?page_id=589&brands=$matches[1]&shoes_type=$matches[2]&paged=$matches[3]','top');
	add_rewrite_rule('^reviews/([^/]*)/([^/]*)/?','index.php?page_id=589&brands=$matches[1]&shoes_type=$matches[2]','top');
	add_rewrite_rule('^reviews/?$','index.php?page_id=589&paged=$matches[1]','top');
	add_rewrite_rule('^reviews/([^/]*)/?','index.php?page_id=589&both=$matches[1]','top');
	//add_rewrite_rule('^reviews/([^/]*)/page/([0-9]+)/?','index.php?page_id=589&brands=$matches[1]&paged=$matches[2]','top');
	
	
}

add_action( 'wp', 'rsg_remove_responsive_feed' );

function rsg_remove_responsive_feed() {
	global $wp_query;
	
}


remove_action( 'set_comment_cookies', 'wp_set_comment_cookies' );

//Remove taxonomies from WP sitemap
add_filter(
	'wp_sitemaps_taxonomies',
	function( $taxonomies ) {
		unset( $taxonomies['post_tag'] );
		return $taxonomies;
	}
);

function disable_feeds() {
	wp_redirect( home_url(), 301 );
	die;
}

add_action( 'do_feed_atom', 'disable_feeds', -1 );

// Disable comment feeds.
add_action( 'do_feed_rss2_comments', 'disable_feeds', -1 );
add_action( 'do_feed_atom_comments', 'disable_feeds', -1 );

add_filter( 'xmlrpc_enabled', '__return_false' );

function isPartUppercase($string) {
	return (bool) preg_match('/[A-Z]/', $string);
}
add_action('parse_request', 'parseUppercase');

function parseUppercase($wp) {
	// only process requests with "lid"
	$query = $wp->query_vars;
	$query_both = '';
	$query_brands = '';
	$query_shoes_type = '';
	$query_name = '';
	
	if(isset($query['name']))
		$query_name = $query['name'];
	if(isset($query['both']))
		$query_both = $query['both'];
	if(isset($query['brands']))
		$query_brands = $query['brands'];
	if(isset($query['shoes_type']))
		$query_shoes_type = $query['shoes_type'];
	//var_dump($query);
	//exit;
	if(isPartUppercase($query_name))	{
		$link = site_url().'/'.strtolower($query_name);
		wp_redirect( $link , 301 );
		exit;
	}
	if(isPartUppercase($query_both))	{
		$link = site_url().'/reviews/'.strtolower($query_both);
		wp_redirect( $link , 301 );
		exit;
	}
	if(isPartUppercase($query_brands) || isPartUppercase($query_shoes_type))	{
		$link = site_url().'/reviews/'.strtolower($query_brands).'/'.strtolower($query_shoes_type).'/';
		wp_redirect( $link , 301 );
		exit;
	}
}

//add_filter( 'page_link', 'rsg_reviews_post_type_link', 10, 2 );

/*
function rsg_reviews_post_type_link($url, $post) {
	if ( is_page_template( 'reviews_search.php' ) ) {
		return 'custom permalink';
	}
	else	{
		return $url.'none';
	}	
}
*/

/**
 * Deal with the custom RSS templates.
 */
add_action('init', 'RSGcustomRSS');
function RSGcustomRSS(){
        add_feed('tca', 'RSGcustomRSSFunc');
		add_feed('tca-best', 'RSGcustomRSSFunc');
}

function RSGcustomRSSFunc(){
        get_template_part('feed', 'tca');
}

add_filter( 'pre_get_posts', 'my_get_posts' );

function my_get_posts( $query ) {
	if ( !is_admin() && !is_single() && $query->is_main_query() ) {
		/*
		if( is_feed() )
			$query->set( 'post_type', array( 'post', 'content', 'buying_guide', 'gps' ) );
		elseif ( is_home() ) {
			$query->set( 'cat', '-11' );
			$query->set( 'post_type', array( 'post', 'content', 'buying_guide', 'gps' ) );
		}
		elseif ( is_search() ) {
			$query->set( 'post_type', array( 'post', 'page', 'content', 'buying_guide', 'affiliate_product', 'gps' ) );
		}
		else
			$query->set( 'post_type', array( 'post', 'page', 'content', 'buying_guide', 'gps' ) );
		*/
		if(is_feed('tca'))
			$query->set( 'cat', array( '11' ) );
		elseif(is_feed('tca-best'))
			$query->set( 'post_type', array( 'noindex'));
		elseif( is_feed() ) {
			$query->set( 'post_type', array( 'post', 'content', 'buying_guide', 'gps', 'review', 'reviews_road', 'reviews_trail', 'reviews_training', 'comparison' ) );
		}
		elseif ( is_home() ) {
			$query->set( 'cat', '-11' );
			$query->set( 'post_type', array( 'post', 'content', 'buying_guide', 'gps', 'review', 'reviews_road', 'reviews_trail', 'reviews_training'  ) );
		}
		elseif ( is_search() ) {
			$query->set( 'orderby', 'date' );
			$query->set( 'post_type', array( 'post', 'page', 'content', 'buying_guide', 'guide', 'affiliate_product', 'gps', 'review', 'reviews_road', 'reviews_trail', 'reviews_training'   ) );
		}
		elseif ( is_post_type_archive('') ) {
			
		}
		else
			$query->set( 'post_type', array( 'post', 'page', 'content', 'buying_guide', 'gps', 'review', 'reviews_road', 'reviews_trail', 'reviews_training'   ) );
		
	}
	
	if ( $query->is_author() ) {
		 $query->set( 'post_type', array( 'post', 'content', 'buying_guide', 'gps', 'review', 'reviews_road', 'reviews_trail', 'reviews_training' ) );
	}

	return $query;
}

remove_action( 'wp_head', 'rel_canonical' );
remove_action( 'wp_head', 'wp_shortlink_wp_head');
remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
remove_action( 'wp_head', 'wp_oembed_add_host_js' );

// replace the default WordPress canonical URL function with your own
add_action( 'wp_head', 'RSG_rel_canonical' );

function RSG_rel_canonical() {
	global $post;
    if ( !is_singular() )
        return;

    global $wp_the_query;
    if ( !$id = $wp_the_query->get_queried_object_id() )
        return;

    $link = get_permalink( $id );
	if(get_the_ID() == 23314) {
		echo "<link rel='canonical' href='https://www.runningshoesguru.com/guide/the-best-running-shoes-for-flat-feet/' />\n";
	}
	elseif(get_the_ID() == 24079 || get_the_ID() == 24077) {
		echo "<link rel='canonical' href='https://www.runningshoesguru.com/guide/best-running-shoes/' />\n";
	}
	elseif(get_the_ID() == 26342 || get_the_ID() == 26343) {
		echo "<link rel='canonical' href='https://www.runningshoesguru.com/2019/06/nike-zoom-pegasus-36-review/' />\n";
	}
	elseif(get_the_ID() == 23098) {
		echo "<link rel='canonical' href='https://www.runningshoesguru.com/free-running-training-plans/' />\n";
	}
	elseif ( is_page_template( 'reviews_search.php' ) ) {
		$brands_qv = get_query_var('brands');
		$type_qv   = get_query_var('shoes_type');
		$brands = ($brands_qv) ? $brands_qv.'/' : null;
		$shoes_type = ($type_qv) ? $type_qv.'/' : null;
		// Curated family pages (e.g. /reviews/nike/pegasus/) are self-canonical
		// so they can rank for "{brand} {family}" queries. shoes_family is the
		// editorial gate (only important model lines get a family term), so this
		// does not open thin facets. Brand+type and base behaviour are unchanged.
		$rsg_is_family = ( $brands_qv && $type_qv && 'all' !== $type_qv
			&& taxonomy_exists('shoes_family') && term_exists( $type_qv, 'shoes_family' ) );
		if ( $rsg_is_family )
			echo "<link rel='canonical' href='".$link.$brands.$shoes_type."' />\n";
		elseif(isset($brands) && $brands != 'all/')
			echo "<link rel='canonical' href='".$link.$brands."all/' />\n";
		elseif(isset($shoes_type) && $shoes_type != 'all/')
			echo "<link rel='canonical' href='".$link."all/".$shoes_type."' />\n";
		else
			echo "<link rel='canonical' href='".$link."' />\n";
	}
	elseif( is_singular('gps') && $post->post_parent > 0 ) {
		$link = get_permalink( $post->post_parent );
		echo "<link rel='canonical' href='$link' />\n";
	}
	else	{
		echo "<link rel='canonical' href='$link' />\n";
	}
}

add_filter( 'script_loader_tag', function ( $tag, $handle ) {
	$defered_scripts = array( 'rsg2018-ads' , 'rsg-collapse', 'rsg2022-single', 'stcr-plugin-js', 'rsg-facebook-pixel', 'eucookielaw-scripts', 'optinmonster-api-script', 'akismet-form', 'comment-reply', 'rsg2014-ajax-request', 'rsg2015-review-search', 'rsg2015-wizard','rsg2015-review-single','rsg2015-carousel','rsg2015-single', 'rsg2015-mega-menu', 'rsg2015-ads');
    
	if ( !in_array( $handle, $defered_scripts ) )
        return $tag;
	

    return str_replace( ' src', ' defer="defer" src', $tag );
}, 10, 2 );

function RSG_ajax_comments_scripts() {
	global $post, $page;
	
	if ( !is_admin() ) {
		wp_enqueue_style( 'rsg2015-bootstrap', get_template_directory_uri() . '/css/bootstrap.css' );
		wp_enqueue_style( 'rsg2015-base', get_template_directory_uri() . '/css/base.css' );
		//wp_enqueue_style( 'rsg2015-fontello', get_template_directory_uri() . '/css/fontello.css' );
		wp_enqueue_style( 'rsg2015-swipebox', get_template_directory_uri() . '/css/swipebox.css' );
		//wp_enqueue_style( 'rsg2015-fonts','//fonts.googleapis.com/css?family=Roboto+Slab:400,700&subset=latin');
	}
	if( is_single() && comments_open() ) :
		wp_enqueue_script( 'comment-reply', includes_url() . '/js/comment-reply.min.js', null, null, true );
		wp_enqueue_script( 'rsg2014-ajax-request', get_template_directory_uri() . '/js/ajax_comment.js', array( 'jquery' ), null, true );
	endif;
	if(is_page_template( 'reviews_search.php')):
		wp_enqueue_script( 'rsg2015-review-search', get_template_directory_uri() . '/js/reviews_search.js', array( 'jquery' ), null, true );
	endif;
	if(is_page_template( 'template-wizard.php')):
		wp_enqueue_script( 'rsg2015-wizard', get_template_directory_uri() . '/js/reviews_wizard.js', array( 'jquery' ), null, true );
	endif;
	/*
	if(is_page_template( 'template-wizard-2020.php')):
		wp_enqueue_script( 'rsg2015-wizard', get_template_directory_uri() . '/js/reviews_wizard2020.js', array( 'jquery' ), null, true );
	endif;
	*/
	if(is_review() === true && !is_singular( 'review' ) && !is_singular( 'reviews_road' ) && !is_singular( 'reviews_trail' ) && !is_singular( 'reviews_training' )):
		wp_enqueue_script( 'rsg2015-review-single', get_template_directory_uri() . '/js/review_single.js', array( 'jquery' ), null, true );
		wp_enqueue_script( 'rsg2015-carousel', get_template_directory_uri() . '/js/carousel.js', array( 'jquery' ), null, true );
	endif;
	
	if(is_singular( 'reviews_road' )):
		//wp_enqueue_script( 'rsg2022-review-single', get_template_directory_uri() . '/js/review_single.js', array( 'jquery' ), null, true );
	endif;
	
	if(is_single() === true || is_page() === true):
		if(!is_front_page())
			wp_enqueue_script( 'rsg2022-single', get_template_directory_uri() . '/js/single_2022.js', array( 'jquery' ), null, true );
	endif;
	wp_enqueue_script( 'rsg2015-mega-menu', get_template_directory_uri() . '/js/ajax_mega_menu.js', array( 'jquery' ), null, true );
	wp_enqueue_script( 'rsg-collapse', get_template_directory_uri() . '/js/collapse.js', array( 'jquery' ), null, true );
	if($_SERVER["HTTP_HOST"] != 'staging.runningshoesguru.com') { 
		//wp_enqueue_script( 'rsg2018-ads', get_template_directory_uri() . '/js/rsg_sortable.js', array( 'jquery' ), null, true );
		//wp_enqueue_script( 'rsg2018-ads', get_template_directory_uri() . '/js/rsg_freestar_4.js', array( 'jquery' ), null, true );
	}
	
	//wp_enqueue_script( 'rsg-facebook-pixel', get_template_directory_uri() . '/js/jquery.facebook.pixel.js', array( 'jquery' ), null, true );
}
add_action( 'wp_enqueue_scripts', 'RSG_ajax_comments_scripts' );



add_action( 'admin_enqueue_scripts', function(){ 
	wp_enqueue_script( 'RSG_admin_script', get_theme_file_uri('/js/admin_script.js') );
});

// The function that handles the AJAX request
function RSG_vote_comment_callback() {
	$nonce = $_REQUEST['rsg_ajax_security'];
	//check_ajax_referer( 'rsg-ajax-comment-voting-nonce', 'rsg_ajax_security' );
	if ( ! wp_verify_nonce( $nonce, 'rsg-ajax-comment-voting-nonce' ) ) {
     die( 'Security check'.$nonce );
	}
	else {
		// add vote up to comment
		$comment_id = explode('div-comment-vote-', $_REQUEST['comment_id']);
		$comment = get_comment($comment_id[1] , ARRAY_A );
		$vote = $comment['comment_karma'] +1;
		$comment['comment_karma'] = $vote;
		if( wp_update_comment( $comment ) )
			echo $vote;
		die(); // this is required to return a proper result
	}
}
add_action( 'wp_ajax_rsg_vote_comment', 'RSG_vote_comment_callback' );
add_action( 'wp_ajax_nopriv_rsg_vote_comment', 'RSG_vote_comment_callback' );


function RSG_redirect_search() {
	if(!empty($_GET['search'])) {
		wp_redirect(home_url(), 301);
		exit();
	}
	if (is_search() && !empty($_GET['s'])) {
		$terms_result = null;
		$terms = get_terms( array(
			'taxonomy' => 'brands',
			'hide_empty' => true,
			) );
		foreach ($terms as $term) {
			if( strtolower($_GET['s']) == strtolower($term->name) ) {
				$terms_result = $term->slug;
				break;
			}
			
			$term_arr = explode(' ', strtolower($term->name) );
			//if(strtolower($term->name) == strtolower($_GET['s'])) {
			if( in_array(strtolower($_GET['s']), $term_arr) ) {
				$terms_result = $term->slug;
				break;
			}
			
			
		}
		if($terms_result) {	
			wp_redirect(get_permalink(589).$terms_result.'/all/', 301);
			exit();
		}
		
		$terms = get_terms( array(
			'taxonomy' => 'shoes_family',
			'hide_empty' => true,
			) );
		foreach ($terms as $term) {
			//if(strtolower($term->name) == strtolower($_GET['s'])) {
			$term_arr = explode(' ', strtolower($term->name) );
			array_unshift($term_arr, strtolower($term->name));

			//if(strtolower($term->name) == strtolower($_GET['s'])) {
			if( in_array(strtolower($_GET['s']), $term_arr) ) {
				$terms_result = get_term_link($term);
				break;
			}
		}
		if($terms_result) {	
			wp_redirect($terms_result, 301);
			exit();
		}
		
	}
}

add_action('template_redirect', 'RSG_redirect_search' );

add_action('wp_enqueue_scripts', 'rsg_zarget');

function rsg_zarget() {
	global $post;
	if(is_array(get_field('load_zarget_js', 'option')) ) {
		//$Url = "http" . ($_SERVER['SERVER_PORT'] == 443 ? "s" : "") . "://cdn.zarget.com/133020/272727.js";
		$Url = get_field('zarget_script_url', 'option');
		if(in_array($post->ID, get_field('load_zarget_js', 'option'))) {
			wp_enqueue_script( 'zarget', $Url, array( 'jquery' ), null, false );
			wp_add_inline_script( 'zarget', '$ = jQuery;' );
		}
	}
}

add_action('wp_enqueue_scripts', 'rsg_jquery');
function rsg_jquery() {
	$jqueryUrl = "https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js";
	//$jqueryUrl = get_template_directory_uri().'/js/jquery.min.js';
	if(!is_admin()) {
		wp_deregister_script('jquery');
		wp_register_script('jquery', $jqueryUrl, false, null);
		wp_enqueue_script('jquery');
	}
}

if ( function_exists( 'register_nav_menus' ) ) {
  	register_nav_menus(
  		array(
  		  'header_menu' => 'Header Menu',
  		  'footer_menu_md' => 'Footer Menu Block Center',
		  'footer_menu_dx' => 'Footer Menu Block Right',
		  'footer_menu_legal' => 'Footer Menu Legal',
		  'most_popular_menu' => 'Most Popular Posts',
		  'homepage_menu_guide' => 'Homepage Guides',
  		)
  	);
}

function RSG_wp_title( $title, $sep ) {
	global $paged, $page;
	global $matching;
	
	$title = trim($title);
	
	
	$both = (get_query_var('both')) ? get_query_var('both') : null;
	$brands = (get_query_var('brands')) ? get_query_var('brands') : null;
	$shoes_type = (get_query_var('shoes_type')) ? get_query_var('shoes_type') : null;
	
	if(isset($both) && $both != null) {
		if(get_term_by('slug', $both, 'brands') != false)
			$brands = $both;
		elseif(get_term_by('slug', $both, 'shoes_type') != false)
			$shoes_type = $both;
	}
	
	if( (isset($brands) && $brands != null) || (isset($shoes_type) && $shoes_type != null) ) {
		$brands = ($brands!='all') ? $brands : null;
		$shoes_type = ($shoes_type!='all') ? $shoes_type : null;
		if($brands && !$shoes_type) {
			$brands_name = get_term_by('slug', $brands, 'brands');
			$title =  ucfirst($brands_name->name)." $title";
		}
		
		if(!$brands && $shoes_type) {
			$shoes_type_name = get_term_by('slug', $shoes_type, 'shoes_type');
			$title =  ucfirst($shoes_type_name->name)." $title";
		}
		
		if($brands && $shoes_type) {
			if($shoes_type != null) {
				if(!term_exists( $shoes_type, 'shoes_type')) {
					if(term_exists( $shoes_type, 'shoes_family')) {
						$term_name = 'shoes_family';
					}
				}
				else {
					$term_name = 'shoes_type';
				}
				//exit;
			}
			$brands_name = get_term_by('slug', $brands, 'brands');
			$shoes_type_name = get_term_by('slug', $shoes_type, $term_name);
			$title =  ucfirst($brands_name->name)." ".ucfirst($shoes_type_name->name)." $title";
		}
	}
	
	if(is_page_template( 'reviews_search.php' )) {
		$title =  $matching.' '.$title.date(' (F Y)');
	}

	
	if(is_tax('shoes_family')) {
		$title = "$title Explained";
	}
	
	if( is_singular( 'guide' ) ) {
		$title = "$title (".get_the_modified_time('F Y').")";
	}
	
	if( is_singular( 'top' ) ) {
		$title = "$title (".get_the_modified_time('F Y').")";
	}
		
	// Add a page number if necessary.
	if ( $paged >= 2 || $page >= 2 )
		$title = "$title $sep " . sprintf( 'Page %s', max( $paged, $page ) );
	
	return $title;
}

add_filter( 'wp_title', 'RSG_wp_title', 100000, 2 );

 //shorten post titles
function short_title($after = '', $length) {
	$mytitle = explode(' ', get_the_title(), $length);	 
	if (count($mytitle)>=$length) {
		array_pop($mytitle);
		$mytitle = implode(" ",$mytitle). $after;
	} else {
		$mytitle = implode(" ",$mytitle);
	}
	return $mytitle;
} 
// post thumbnails
if ( function_exists( 'add_theme_support' ) ) {
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' ); 
	//add_theme_support( 'html5', array( 'caption' ) ); 
	//set_post_thumbnail_size( 620, 150, true ); //the large feature size
	//add_image_size('blog-image', 640, 640, false);
	//add_image_size('the-reg-thumb', 112, 25, false);	
}

function rsg_feed_remove_srcset( $attr ) {
	if ( class_exists( 'BEA_Images' ) ) {
		return $attr;
	}
	if ( isset( $attr['sizes'] ) ) {
		unset( $attr['sizes'] );
	}
	if ( isset( $attr['srcset'] ) ) {
		unset( $attr['srcset'] );
	}
	return $attr;
}

//breadcrumbs
function dimox_breadcrumbs() {
 
  $delimiter = '<span class="delimeter-icon">&nbsp;</span>';
  $name = '<span class="home-icon">&nbsp;</span>'; //text for the 'Home' link
  $currentBefore = '<span class="current">';
  $currentAfter = '</span>';
  if ( !is_home() && !is_front_page() || is_paged() ) {
 
    echo '<div id="crumbs" class="condensed_font">';
 
    global $post;
    $home = get_bloginfo('url');
    echo '<a href="' . $home . '">' . $name . '</a> ' . $delimiter . ' ';
 
    if ( is_category() ) {
      global $wp_query;
      $cat_obj = $wp_query->get_queried_object();
      $thisCat = $cat_obj->term_id;
      $thisCat = get_category($thisCat);
      $parentCat = get_category($thisCat->parent);
      if ($thisCat->parent != 0) echo(get_category_parents($parentCat, TRUE, ' ' . $delimiter . ' '));
      echo $currentBefore . ' &#39;';
      single_cat_title();
      echo '&#39;' . $currentAfter;
 
    } elseif ( is_day() ) {
      echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
      echo '<a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a> ' . $delimiter . ' ';
      echo $currentBefore . get_the_time('d') . $currentAfter;
 
    } elseif ( is_month() ) {
      echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
      echo $currentBefore . get_the_time('F') . $currentAfter;
 
    } elseif ( is_year() ) {
      echo $currentBefore . get_the_time('Y') . $currentAfter;
 
    } elseif ( is_single() && !is_attachment() ) {
      $cat = get_the_category(); $cat = $cat[0];
      echo get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
      echo $currentBefore;
      the_title();
      echo $currentAfter;
 
    } elseif ( is_attachment() ) {
      $parent = get_post($post->post_parent);
      $cat = get_the_category($parent->ID); $cat = $cat[0];
      echo get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
      echo '<a href="' . get_permalink($parent) . '">' . $parent->post_title . '</a> ' . $delimiter . ' ';
      echo $currentBefore;
      the_title();
      echo $currentAfter;
 
    } elseif ( is_page() && !$post->post_parent ) {
      echo $currentBefore;
      the_title();
      echo $currentAfter;
 
    } elseif ( is_page() && $post->post_parent ) {
      $parent_id  = $post->post_parent;
      $breadcrumbs = array();
      while ($parent_id) {
        $page = get_page($parent_id);
        $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
        $parent_id  = $page->post_parent;
      }
      $breadcrumbs = array_reverse($breadcrumbs);
      foreach ($breadcrumbs as $crumb) echo $crumb . ' ' . $delimiter . ' ';
      echo $currentBefore;
      the_title();
      echo $currentAfter;
 
    } elseif ( is_search() ) {
      echo $currentBefore . 'Search results for &#39;' . get_search_query() . '&#39;' . $currentAfter;
 
    } elseif ( is_tag() ) {
      echo $currentBefore . 'Posts tagged &#39;';
      single_tag_title();
      echo '&#39;' . $currentAfter;
 
    } elseif ( is_author() ) {
       global $author;
      $userdata = get_userdata($author);
      echo $currentBefore . 'Articles posted by ' . $userdata->display_name . $currentAfter;
 
    } elseif ( is_404() ) {
      echo $currentBefore . 'Error 404' . $currentAfter;
    }
 
    if ( get_query_var('paged') && !is_page()) {
      if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ' (';
      echo __('Page') . ' ' . get_query_var('paged');
      if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ')';
    }
 
    echo '</div>';
 
  }
}

//short excerpt
function short_excerpt($limit) {
	global $id;
	if(rsg_get_subtitle()) {
		$excerpt = rsg_truncate(rsg_get_subtitle(),$limit);
		$excerpt = $excerpt[0];
	}
	else	{
		$excerpt = rsg_truncate(get_the_excerpt(),$limit);
		$excerpt = $excerpt[0];
	}
	/*
	if (count($excerpt)>=$limit) {
		array_pop($excerpt);
		$excerpt = implode(" ",$excerpt);
	} else {
		$excerpt =implode(" ",$excerpt);
	}	
	*/
	$excerpt = preg_replace('`\[[^\]]*\]`','',$excerpt);
	return $excerpt;
}

function short_excerpt_array($limit, $custom_id = NULL) {
	global $id, $post;
	
	if( !$custom_id)
		$custom_id = $id;
		
	
	if(rsg_get_subtitle($custom_id)) {
		$excerpt = rsg_truncate(rsg_get_subtitle($custom_id),$limit);
		$excerpt1 = $excerpt[0];
		$excerpt2 = $excerpt[1];
	}
	else	{
		$excerpt = rsg_truncate(get_the_excerpt($custom_id),$limit);
		$excerpt1 = $excerpt[0];
		$excerpt2 = $excerpt[1];
	}
	
		
	/*
	if (count($excerpt)>=$limit) {
		array_pop($excerpt);
		$excerpt = implode(" ",$excerpt);
	} else {
		$excerpt =implode(" ",$excerpt);
	}	
	*/
	$excerpt1 = preg_replace('`\[[^\]]*\]`','',$excerpt1);
	$excerpt2 = preg_replace('`\[[^\]]*\]`','',$excerpt2);
	$excerpt[0] = $excerpt1;
	$excerpt[1] = $excerpt2;
	return $excerpt;
}

function rsg_excerpt($limit) {
  global $id;
  $excerpt = explode(' ', get_the_excerpt(), $limit);
  
  $excerpt = preg_replace('`\[[^\]]*\]`','',implode(" ",$excerpt));
  return $excerpt.' ...';
}

function rsg_truncate($string, $limit) {
	if (strlen($string) > $limit) {
		$string = wordwrap($string, $limit);
		$string = explode("\n", $string);
		return $string;
	}
	else	{
		$string2 = Array();
		$string2[] = $string;
		$string2[] = '';
		return $string2;
	}
}

//trim_excerpt
function trim_excerpt($text, $custom_post) {
	if(strlen($text) < 10) {
		$custom = get_post_custom($custom_post->ID);
		$text = $custom["verdict"][0];
	}
  return rtrim($text,'[...]');
}

add_filter('get_the_excerpt', 'trim_excerpt', 10,  2);
//fix comment number
add_filter('get_comments_number', 'comment_count', 0);
function comment_count( $count ) {
	if ( ! is_admin() ) {
		global $id;
		$opts = get_comments('status=approve&post_id=' . $id);
		$comments_by_type = separate_comments($opts);
		return count($comments_by_type['comment']);
		} else {
			return $count;
		}
}
//author section
function ckIfAuthor(){
	$tempcontent = get_the_content();
    $tempseek = '<div id="post_author';
    $temppos = strpos($tempcontent,$tempseek);
    if($temppos === false) {
      return false;
    } else {
      return true;
    }
}
//yoast for authorship
function yoast_allow_rel() {
	global $allowedtags;
	$allowedtags['a']['rel'] = array ();
}
add_action( 'wp_loaded', 'yoast_allow_rel' );
function yoast_add_google_profile( $contactmethods ) {
	// Add Google Profiles
	$contactmethods['google_profile'] = 'Google Profile URL';
	return $contactmethods;
}
add_filter( 'user_contactmethods', 'yoast_add_google_profile', 10, 1);
/*---------- GIULIO ADD ONS -------------*/

//require_once('includes/theme_options.php');
//require_once('includes/theme_options_forms.php');
//require_once('includes/theme_options_deals_link.php');
require_once('includes/sidebars.php');
require_once('includes/reviews/functions.php');
require_once('includes/image_resize.php');
require_once('includes/subtitle.php');
require_once('includes/comments.php');
require_once('includes/category.php');
require_once('includes/author_image.php');
//require_once('includes/mobile_menu.php');
require_once('includes/share_count.php');
require_once('includes/related/related.php');
require_once('includes/widgets/widgets.php' );
//require_once('includes/widgets/popular-posts.php' );
require_once('includes/plugins/attention_grabber/attention_grabber.php');
//require_once('includes/plugins/seo.php');
//require_once('gxs-modules/init.php');
//require_once('includes/buying_guides_post_type.php');
//require_once('includes/gift_guides_post_type.php');
require_once('includes/mega_menu_post_type.php');
//require_once('includes/external_product_post_type.php');
require_once('includes/menu_walker.php');
// require_once('includes/plugins/rsg_aweber/rsg_aweber.php'); // AWeber removed 2026-06-21 (unused; migrated to MailerLite)
require_once('includes/content-post-type.php');
require_once('includes/noindex-post-type.php');
require_once('includes/content-length.php');
require_once('includes/shortcodes.php');
require_once('includes/amazon-api.php');
require_once('includes/gallery.php');
require_once('includes/progressive-image-in-content.php');
require_once('includes/gps_post_type.php');
//require_once('includes/trail_post_type.php');
require_once('includes/guide_post_type.php');
require_once('includes/top_post_type.php');
require_once('includes/review_post_type.php');
require_once('includes/comparison_post_type.php');
// 2022
require_once('includes/reviews_road_post_type.php');
require_once('includes/reviews_gps_post_type.php');
//2024
require_once('includes/search_stats.php');



if($_SERVER["HTTP_HOST"] != 'staging.runningshoesguru.com') { 
	//require_once('includes/minit.php');
	require_once('includes/plugins/seo.php');
}

/*
if ( !function_exists( 'rsg_require_custom_widget_classes' ) ) {
    function rsg_require_custom_widget_classes() {

        require_once( 'includes/widgets/facebook_box.php' );
        register_widget( 'PW_Post_Loop_With_Thumbs' );

    }
}
add_action( 'widgets_init', 'rsg_require_custom_widget_classes' );
*/

function noindex_for_noindex() {
    if ( is_singular( 'noindex' ) ) {
        echo '<meta name="robots" content="noindex, follow">';
    }
}

add_action('wp_head', 'noindex_for_noindex');

function one_post_each_cat($cat_ids = Array()) {
	$timeout = 1; // 1 hours
	$transient_name = 'one_post_each_cat'.implode($cat_ids);
	$category_posts = get_transient($transient_name);
	if (!is_array($category_posts) || count($category_posts)==0) {
		$category_posts = array();
		$i = 0;
		foreach($cat_ids as $category) {
			$posts = get_posts("post_type=post&numberposts=1&cat={$category}");
			foreach($posts as $post) {
				$category_posts[$i] = $post;
				$i++;
			}
		}
		set_transient($transient_name,$category_posts,60*60*$timeout);
	}
	return $category_posts;
}

add_filter( 'query_vars', 'addnew_query_vars', 10, 1 );
function addnew_query_vars($vars)
{   
    //array_push($vars, 'test_new_ad_2014');
	array_push($vars, 's_rev');
    return $vars;
}

function the_category_filter($thelist,$separator=' ') {
	if(!defined('WP_ADMIN')) {
		//Category IDs to exclude

		$cats = explode($separator,$thelist);
		$newlist = array();
		
		$newlist[] = $cats[0];
		return implode($separator,$newlist);
	} else {
		return $thelist;
	}
}
add_filter('the_category','the_category_filter', 10, 2);

/*
function rsg_comment_form_field_html5($html) {
 return str_replace('size="30"', 'size="30" required', $html);
}
add_filter('comment_form_field_author','rsg_comment_form_field_html5');
add_filter('comment_form_field_email','rsg_comment_form_field_html5');
*/
add_filter( 'comment_form_default_fields', 'bootstrap4_comment_form_fields' );
function bootstrap4_comment_form_fields( $fields ) {
    $commenter = wp_get_current_commenter();
    
    $req      = get_option( 'require_name_email' );
    $aria_req = ( $req ? " aria-required='true' required='true' " : '' );
	$html5    = 1;
    
	/*
    $fields   =  array(
        'author' => '<div class="form-group"><div class="comment-form-author col-6"><div class="row"><label for="author" class="col-sm-4 control-label">' . __( 'Name' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
                    '<div class="col-sm-8"><input class="form-control" id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></div></div></div>',
        'email'  => '<div class="comment-form-email col-6"><div class="row"><label for="email" class="col-sm-4 control-label">' . __( 'Email' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
                    '<div class="col-sm-8"><input class="form-control" id="email" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></div></div></div>',
        'url'    => '</div><div class="form-group"><div class="comment-form-url col-6"><div class="row"><label for="url" class="col-sm-4 control-label">' . __( 'Website' ) . '</label> ' .
                    '<div class="col-sm-8"><input class="form-control" id="url" name="url" ' . ( $html5 ? 'type="url"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" /></div></div></div>',
    );
	*/
	
	global $wp_subscribe_reloaded;
	ob_start();
	if (isset($wp_subscribe_reloaded)){
		$wp_subscribe_reloaded->stcr->subscribe_reloaded_show();
	}
	$wp_subscribe_reloaded_html = ob_get_contents();
	ob_end_clean();
	
	if(is_review())	{
		$commenter = wp_get_current_commenter();
		//var_dump($default[ 'fields' ]);
		
			/*
			$default[ 'fields' ][ 'url' ] .= '<div class="comment-form-vote col-6"><div class="row">' .
				'<label for="rate" class="col-sm-4 control-label">'. __('My Rating') . '</label>
				<div class="col-sm-8">
				<select id="rate" name="rate" class="comment-form-input-fields form-control" tabindex="4"><option value="0">&nbsp;</option><option value="1">1 - Very bad</option><option value="2">2 - Bad</option><option value="3">3 - Ok</option><option value="4">4 - Good</option><option value="5">5 - Very good</option></select>
				</div></div></div></div>';
			
			$vote_html = '<div class="comment-form-vote col-6">' .
				'<select placeholder="My rating" id="rate" name="rate" class="comment-form-input-fields form-control" tabindex="4"><option value="">My rating</option><option value="1">1 - Very bad</option><option value="2">2 - Bad</option><option value="3">3 - Ok</option><option value="4">4 - Good</option><option value="5">5 - Very good</option></select>
				</div>';
			*/
			$vote_html = '<div class="comment-form-vote col-12">
							<fieldset class="animated-rating">
								<legend>Please rate:</legend>
								<input type="radio" id="star5" name="rate" value="5" /><label for="star5" title="Rocks!">5 stars</label>
								<input type="radio" id="star4" name="rate" value="4" /><label for="star4" title="Pretty good">4 stars</label>
								<input type="radio" id="star3" name="rate" value="3" /><label for="star3" title="Meh">3 stars</label>
								<input type="radio" id="star2" name="rate" value="2" /><label for="star2" title="Kinda bad">2 stars</label>
								<input type="radio" id="star1" name="rate" value="1" /><label for="star1" title="Sucks big time">1 star</label>
							</fieldset></div>';
	}
	/*
	$fields   =  array(
        'author' => '<div class="form-group"><div class="comment-form-author col-6">' .
                    '<input class="form-control" id="author" placeholder="Name *" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></div>',
        'email'  => '<div class="comment-form-email col-6">' .
                    '<input class="form-control" id="email" placeholder="@Email *" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></div>',
         'url'    => '</div><div class="form-group"><div class="comment-form-url col-6">' .
                    '<input class="form-control" id="url" placeholder="Website" name="url" ' . ( $html5 ? 'type="url"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" /></div>'.
					$vote_html.
					'</div><div class="form-group"><div class="comment-form-subscribe col-12">'.$wp_subscribe_reloaded_html.
					'</div></div>',
    );
	*/
	$fields   =  array(
        'author' => '<div class="form-group row"><div class="comment-form-author col-6">' .
                    '<input class="form-control" id="author" placeholder="Name *" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></div>',
        'email'  => '<div class="comment-form-email col-6">' .
                    '<input class="form-control" id="email" placeholder="@Email *" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></div>',
         'url'    => '</div><div class="form-group row"><div class="comment-form-subscribe col-12">'.$wp_subscribe_reloaded_html.
					'</div></div>',
    );
	
	if(is_singular( 'reviews_road' ) || is_singular( 'reviews_trail' ) || is_singular( 'reviews_training' )		)	{
		$fields   =  array(
			'author' => '<div class="form-group row"><div class="comment-form-author col-12">' .
						'<label for="author">3. Your name</label>'.
						'<input class="form-control" id="author" placeholder="" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30"' . $aria_req . ' /></div></div>',
						
			'email'  => '<div class="form-group row"><div class="comment-form-email col-12">' .
						'<label for="email">4. Your email</label>'.
						'<input class="form-control" id="email" placeholder="" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></div>',
			 'url'    => '</div><div class="form-group row"><div class="comment-form-subscribe col-12">'.$wp_subscribe_reloaded_html.
						'</div></div>',
		);
	}
	
    return $fields;
}
add_filter( 'comment_form_defaults', 'bootstrap3_comment_form' );
function bootstrap3_comment_form( $args ) {
	if(is_review() || is_singular( 'affiliate_product' ) )	{
		$commenter = wp_get_current_commenter();
		//var_dump($default[ 'fields' ]);
		
			/*
			$default[ 'fields' ][ 'url' ] .= '<div class="comment-form-vote col-6"><div class="row">' .
				'<label for="rate" class="col-sm-4 control-label">'. __('My Rating') . '</label>
				<div class="col-sm-8">
				<select id="rate" name="rate" class="comment-form-input-fields form-control" tabindex="4"><option value="0">&nbsp;</option><option value="1">1 - Very bad</option><option value="2">2 - Bad</option><option value="3">3 - Ok</option><option value="4">4 - Good</option><option value="5">5 - Very good</option></select>
				</div></div></div></div>';
			
			$vote_html = '<div class="comment-form-vote col-6">' .
				'<select placeholder="My rating" id="rate" name="rate" class="comment-form-input-fields form-control" tabindex="4"><option value="">My rating</option><option value="1">1 - Very bad</option><option value="2">2 - Bad</option><option value="3">3 - Ok</option><option value="4">4 - Good</option><option value="5">5 - Very good</option></select>
				</div>';
			*/
			$vote_html = '<div class="form-group row"><div class="comment-form-vote col-md-6 col-lg-5 clearfix">
							<fieldset class="animated-rating">
								<input type="radio" id="star5" name="rate" value="5" /><label for="star5" title="Very Good">5 stars</label>
								<input type="radio" id="star4" name="rate" value="4" /><label for="star4" title="Pretty good">4 stars</label>
								<input type="radio" id="star3" name="rate" value="3" /><label for="star3" title="OK">3 stars</label>
								<input type="radio" id="star2" name="rate" value="2" /><label for="star2" title="Kinda bad">2 stars</label>
								<input type="radio" id="star1" name="rate" value="1" /><label for="star1" title="Very Bad">1 star</label>
							</fieldset></div><div class="col-md-6 col-lg-7"><div id="animated-rating-label" class="text-muted h4"></div></div></div>';
	}
    /*
	$args['comment_field'] = '<div class="form-group"><div class="comment-form-comment col-xl-12"><div class="row">
            <label for="comment" class="col-sm-2 col-md-2 control-label">' . _x( 'Comment', 'noun' ) . '</label> 
            <div class="col-sm-10 col-xl-10"><textarea placeholder="Your comment..." class="form-control" id="comment" name="comment" rows="2" aria-required="true"></textarea></div></div></div>
        </div>';
	*/
	$args['comment_field'] = $vote_html.'<div class="form-group"><div class="row"><div class="comment-form-comment col-xl-12"> 
		<textarea placeholder="Your comment..." class="form-control" id="comment" name="comment" rows="2" aria-label="Your comment" aria-required="true" required="true"></textarea></div>
	</div></div>';
	
	if(is_singular( 'reviews_road' ) || is_singular( 'reviews_trail' ) || is_singular( 'reviews_training' )	 )	{
		$commenter = wp_get_current_commenter();
		
			$vote_html = '<div class="form-group row"><div class="comment-form-vote col-12">' .
				'<label for="range">1. Rating (optional)</label>
				<input type="range"  id="rate" name="rate" min="0" max="10" value="0">
				<output></output>
				</div></div>';
				
			$args['comment_field'] = $vote_html.'<div class="form-group"><div class="row"><div class="comment-form-comment col-xl-12"> 
				<label for="comment">2. Your comment</label>
				<textarea placeholder="" class="form-control" id="comment" name="comment" rows="2" aria-label="Your comment" aria-required="true" required="true"></textarea></div>
			</div></div>';
			}
    return $args;
}

function RSG_search_by_title_only( $search, $wp_query )
{
    global $wpdb;
    if ( empty( $search ) )
        return $search; // skip processing - no search term in query
    $q = $wp_query->query_vars;
    $n = ! empty( $q['exact'] ) ? '' : '%';
    $search = '';
    $searchand = '';
	if(array_key_exists('search_terms', $q)) {
		foreach ( (array) $q['search_terms'] as $term ) {
			$term = esc_sql( $wpdb->esc_like( $term ) );
			$search .= "{$searchand}($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
			$searchand = ' AND ';
		}
	}
    if ( ! empty( $search ) ) {
        $search = " AND ({$search}) ";
        if ( ! is_user_logged_in() )
            $search .= " AND ($wpdb->posts.post_password = '') ";
    }
    return $search;
}
add_filter( 'posts_search', 'RSG_search_by_title_only', 500, 2 );

function rsg_insert_ad_block( $text ) {

    if ( is_single()  ) :
		
		$new_text = '';
        if( is_review() ) {
			$insert_after = 2; //number of paragraphs
			$insert_after2 = 6; //number of paragraphs 2
			//$ads_text = '<div class="spacer clearfix"></div><div class="hidden-md hidden-lg hidden-xl center-block"><div class="adSlot" data-alias="RSG_SingleReview_Mobile_300x250_2_BTF" data-width="300" data-height="250"></div></div><div class="spacer"></div>';
			//$ads_text = '<div class="spacer clearfix"></div><div class="d-flex d-md-none"><div class="ad-tag-lazy" data-ad-name="RSG_SingleReview_320x50_2_BTF" data-ad-size="320x50" ></div></div><div class="spacer"></div>';
			//$ads_text2 = '<div class="center-block"><div class="adSlot" data-alias="RSG_SingleReview_728X90_2__BTF" data-width="728" data-height="90"></div></div>';
		}
		else {
			$insert_after = 2; //number of paragraphs
			$insert_after2 = 4; //number of paragraphs 2
			//$ads_text = '<div class="clearfix"></div><div class="hidden-md hidden-lg hidden-xl center-block"><div class="spacer"></div><div class="adSlot" data-alias="RSG_Article_Mobile_300x250_BTF" data-width="300" data-height="250"></div><div class="spacer"></div></div>';
			//$ads_text2 = '<div class="clearfix"></div><div class="visible-md visible-lg visible-xl center-block"><div class="spacer"></div><div class="adSlot" data-alias="RSG_Article_728x90_BTF" data-width="728" data-height="90"></div><div class="spacer"></div></div>';
			//$ads_text2 .= '<div class="hidden-md hidden-lg hidden-xl center-block"><div class="adSlot" data-alias="RSG_Article_Mobile_300x250_2_BTF" data-width="300" data-height="250"></div><div class="spacer"></div><div class="spacer"></div></div>';	
			
			if(is_singular('post')) {
				/*
				$ads_text = '<div class="spacer clearfix"></div><div class="d-flex d-md-none">
				<div align="center" data-freestar-ad="__336x280 __336x280" id="runningshoesguru_incontent_1">
				  <script data-cfasync="false" type="text/javascript">
					freestar.config.enabled_slots.push({ placementName: "runningshoesguru_incontent_1", slotId: "runningshoesguru_incontent_1" });
				  </script>
				</div>
				</div><div class="spacer clearfix"></div>';
				$ads_text2 .= '<div class="spacer clearfix"></div><div class="d-flex d-md-none"><div class="spacer"></div>
				<div align="center" data-freestar-ad="__336x280 __336x280" id="runningshoesguru_incontent_2">
				  <script data-cfasync="false" type="text/javascript">
					freestar.config.enabled_slots.push({ placementName: "runningshoesguru_incontent_2", slotId: "runningshoesguru_incontent_2" });
				  </script>
				</div>
				</div><div class="spacer clearfix"></div>';
				*/
			}
			else {
				/*
				$ads_text = '<div class="spacer clearfix"></div><div class="d-flex d-md-none">
				<div align="center" data-freestar-ad="__336x280 __336x280" id="runningshoesguru_incontent_1">
				  <script data-cfasync="false" type="text/javascript">
					freestar.config.enabled_slots.push({ placementName: "runningshoesguru_incontent_1", slotId: "runningshoesguru_incontent_1" });
				  </script>
				</div>
				</div><div class="spacer clearfix"></div>';
				$ads_text2 .= '<div class="spacer clearfix"></div><div class="d-flex"><div class="spacer"></div>
				<div align="center" data-freestar-ad="__336x280 __336x280" id="runningshoesguru_incontent_2">
				  <script data-cfasync="false" type="text/javascript">
					freestar.config.enabled_slots.push({ placementName: "runningshoesguru_incontent_2", slotId: "runningshoesguru_incontent_2" });
				  </script>
				</div>
				</div><div class="spacer clearfix"></div>';
				*/
			}
		}
		
        $split_by = "</p>";
        

        // make array of paragraphs
        $paragraphs = explode( $split_by, $text);

        // if array elements are less than $insert_after set the insert point at the end
        $len = count( $paragraphs );
		
		$i = 1;
        // loop through array and build string for output
        foreach( $paragraphs as $paragraph ) {
            $new_text .= $paragraph.'</p>';
			
			if (  $i == $insert_after && isset($ads_text) )
				$new_text .= $ads_text;
			
			if (  $i == $insert_after2 && isset($ads_text2) ) {
				//echo $first_text_length;
				$first_text_length = rsg_get_content_length($new_text);
				if($first_text_length < 1950) {
					//$new_text .= $first_text_length.'after'.$insert_after2;
					$insert_after2 = $insert_after2+1;
					$i++;
					continue;
				}
				$new_text .= $ads_text2;
			}
			$i++;
        }

        return $new_text;

    endif;

    return $text;

}

function rsg_insert_ad_block_h2( $text ) {
	

    if ( is_single()  ) :
		
		
		$new_text = '';
        if( is_review() ) {
			$insert_after = 2; //number of paragraphs
			$insert_after2 = 6; //number of paragraphs 2
			$ads_text = '';
		}
		else {
			$insert_after = 2; //number of paragraphs
			$insert_after2 = 3; //number of paragraphs 2
			$insert_after3 = 4; //number of paragraphs 2
			$insert_after4 = 6; //number of paragraphs 2
			//$ads_text = '<div class="clearfix"></div><div class="hidden-md hidden-lg hidden-xl center-block"><div class="spacer"></div><div class="adSlot" data-alias="RSG_Article_Mobile_300x250_BTF" data-width="300" data-height="250"></div><div class="spacer"></div></div>';
			//$ads_text2 = '<div class="clearfix"></div><div class="visible-md visible-lg visible-xl center-block"><div class="spacer"></div><div class="adSlot" data-alias="RSG_Article_728x90_BTF" data-width="728" data-height="90"></div><div class="spacer"></div></div>';
			//$ads_text2 .= '<div class="hidden-md hidden-lg hidden-xl center-block"><div class="adSlot" data-alias="RSG_Article_Mobile_300x250_2_BTF" data-width="300" data-height="250"></div><div class="spacer"></div><div class="spacer"></div></div>';	
			
			if(is_singular('post') || is_singular('content') ) {
				/*
				$ads_text = '<div class="spacer clearfix"></div><div class="d-flex">
				<div align="center" data-freestar-ad="__336x280 __336x280" id="runningshoesguru_incontent_1">
				  <script data-cfasync="false" type="text/javascript">
					freestar.config.enabled_slots.push({ placementName: "runningshoesguru_incontent_1", slotId: "runningshoesguru_incontent_1" });
				  </script>
				</div>
				</div><div class="spacer clearfix"></div>';
				$ads_text2 .= '<div class="spacer clearfix"></div><div class="d-flex"><div class="spacer"></div>
				<div align="center" data-freestar-ad="__336x280 __336x280" id="runningshoesguru_incontent_2">
				  <script data-cfasync="false" type="text/javascript">
					freestar.config.enabled_slots.push({ placementName: "runningshoesguru_incontent_2", slotId: "runningshoesguru_incontent_2" });
				  </script>
				</div>
				</div><div class="spacer clearfix"></div>';
				$ads_text3 .= '<div class="spacer clearfix"></div><div class="d-flex"><div class="spacer"></div>
				<div align="center" data-freestar-ad="__336x280 __336x280" id="runningshoesguru_incontent_3">
				  <script data-cfasync="false" type="text/javascript">
					freestar.config.enabled_slots.push({ placementName: "runningshoesguru_incontent_3", slotId: "runningshoesguru_incontent_3" });
				  </script>
				</div>
				</div><div class="spacer clearfix"></div>';
				$ads_text4 .= '<div class="spacer clearfix"></div><div class="d-flex"><div class="spacer"></div>
				<div align="center" data-freestar-ad="__336x280 __336x280" id="runningshoesguru_incontent_4">
				  <script data-cfasync="false" type="text/javascript">
					freestar.config.enabled_slots.push({ placementName: "runningshoesguru_incontent_4", slotId: "runningshoesguru_incontent_4" });
				  </script>
				</div>
				</div><div class="spacer clearfix"></div>';
				*/
			}
			else {
				/*
				$ads_text = '<div class="spacer clearfix"></div><div class="d-flex d-md-none">
				<div align="center" data-freestar-ad="__336x280 __336x280" id="runningshoesguru_incontent_1">
				  <script data-cfasync="false" type="text/javascript">
					freestar.config.enabled_slots.push({ placementName: "runningshoesguru_incontent_1", slotId: "runningshoesguru_incontent_1" });
				  </script>
				</div>
				</div><div class="spacer clearfix"></div>';
				$ads_text2 .= '<div class="spacer clearfix"></div><div class="d-flex"><div class="spacer"></div>
				<div align="center" data-freestar-ad="__336x280 __336x280" id="runningshoesguru_incontent_2">
				  <script data-cfasync="false" type="text/javascript">
					freestar.config.enabled_slots.push({ placementName: "runningshoesguru_incontent_2", slotId: "runningshoesguru_incontent_2" });
				  </script>
				</div>
				</div><div class="spacer clearfix"></div>';
				*/
			}
		}
		
		$split_by = "<h2";
        
		if(substr($text, 0, 3) == "<h2")
			$start_with_h2 = true;
			
        // make array of paragraphs
        $paragraphs = explode( $split_by, $text);

        // if array elements are less than $insert_after set the insert point at the end
        $len = count( $paragraphs );
		
		if( $insert_after4  > $len )
			$insert_after4 = $len;
		
		$i = 1;
        // loop through array and build string for output
        foreach( $paragraphs as $paragraph ) {
			
			if($paragraph == "")
				continue;
			
			if($i > 1) {
				$new_text .= '<h2'.$paragraph;
			}
			else {
				if($start_with_h2 == true)
					$new_text .= '<h2'.$paragraph;
				else
					$new_text .= $paragraph;
			}
			
			
			
			if (  $i == $insert_after && isset($ads_text) ) {
				$new_text .= $ads_text;
			}
			
			if (  $i == $insert_after2 && isset($ads_text2) ) {
				
				$new_text .= $ads_text2;
			}
			
			if (  $i == $insert_after3 && isset($ads_text3) ) {
				
				$new_text .= $ads_text3;
			}
			
			if (  $i == $insert_after4 && isset($ads_text4) ) {
				
				$new_text .= $ads_text4;
			}
			
			
			
			$i++;
        }
		

        return $new_text;

    endif;

    return $text;

}

add_filter('the_content', 'rsg_insert_ad_block_h2');

function rsg_order_by_date( $a, $b )
{
	return strcmp( $b->post_date, $a->post_date );
}

function rsg_paginate_links( $args = '' ) {
	$defaults = array(
		'base' => '%_%', // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
		'format' => '?page=%#%', // ?page=%#% : %#% is replaced by the page number
		'total' => 1,
		'current' => 0,
		'show_all' => false,
		'prev_next' => true,
		//'prev_text' => __('&laquo;'),
		//'next_text' => __('&raquo;'),
		'prev_text' => '',
		'next_text' => '',
		'end_size' => 1,
		'mid_size' => 2,
		'type' => 'list',
		'add_args' => false, // array of query args to add
		'add_fragment' => ''
	);

	$args = wp_parse_args( $args, $defaults );
	extract($args, EXTR_SKIP);

	// Who knows what else people pass in $args
	$total = (int) $total;
	if ( $total < 2 )
		return;
	$current  = (int) $current;
	$end_size = 0  < (int) $end_size ? (int) $end_size : 1; // Out of bounds?  Make it the default.
	$mid_size = 0 <= (int) $mid_size ? (int) $mid_size : 2;
	$add_args = is_array($add_args) ? $add_args : false;
	$r = '';
	$page_links = array();
	$n = 0;
	$dots = false;

	if ( $prev_next && $current && 1 < $current ) :
		$link = str_replace('%_%', 2 == $current ? '' : $format, $base);
		$link = str_replace('%#%', $current - 1, $link);
		if ( $add_args )
			$link = add_query_arg( $add_args, $link );
		$link .= $add_fragment;
		$page_links[] = '<li class="page-item"><a class="page-link" href="' . esc_url( apply_filters( 'paginate_links', $link ) ) . '"><span class="fontello-icon-left-open">' . $prev_text . '</span></a></li>';
	endif;
	for ( $n = 1; $n <= $total; $n++ ) :
		$n_display = number_format_i18n($n);
		if ( $n == $current ) :
			$page_links[] = "<li class='page-item active'><span class='page-link'>$n_display</span><li>";
			$dots = true;
		else :
			if ( $show_all || ( $n <= $end_size || ( $current && $n >= $current - $mid_size && $n <= $current + $mid_size ) || $n > $total - $end_size ) ) :
				$link = str_replace('%_%', 1 == $n ? '' : $format, $base);
				$link = str_replace('%#%', $n, $link);
				if ( $add_args )
					$link = add_query_arg( $add_args, $link );
				$link .= $add_fragment;
				$page_links[] = "<li class='page-item'><a class='page-link' href='" . esc_url( apply_filters( 'paginate_links', $link ) ) . "'>$n_display</a></li>";
				$dots = true;
			elseif ( $dots && !$show_all ) :
				$page_links[] = '<li class="page-item"><span class="page-link">' . __( '&hellip;' ) . '</span></li>';
				$dots = false;
			endif;
		endif;
	endfor;
	if ( $prev_next && $current && ( $current < $total || -1 == $total ) ) :
		$link = str_replace('%_%', $format, $base);
		$link = str_replace('%#%', $current + 1, $link);
		if ( $add_args )
			$link = add_query_arg( $add_args, $link );
		$link .= $add_fragment;
		$page_links[] = '<li class="page-item"><a class="page-link" href="' . esc_url( apply_filters( 'paginate_links', $link ) ) . '"><span class="fontello-icon-right-open">' . $next_text . '</span></a></li>';
	endif;
	switch ( $type ) :
		case 'array' :
			return $page_links;
			break;
		case 'list' :
			/*
			$r .= "<ul class='pagination'>\n\t<li>";
			$r .= join("</li>\n\t<li>", $page_links);
			$r .= "</li>\n</ul>\n";
			*/
			$r .= "<ul class='pagination justify-content-center'>".join("\n", $page_links)."</ul>";
			break;
		default :
			$r = join("\n", $page_links);
			break;
	endswitch;
	return $r;
}

/*----------------- ACF ----------------------- */

// 1. customize ACF path
add_filter('acf/settings/path', 'my_acf_settings_path');
 
function my_acf_settings_path( $path ) {
 
    // update path
    $path = get_template_directory() . '/includes/plugins/acf3/';
    
    // return
    return $path;
    
}
 

// 2. customize ACF dir
add_filter('acf/settings/dir', 'my_acf_settings_dir');
 
function my_acf_settings_dir( $dir ) {
 
    // update path
    $dir = get_template_directory_uri() . '/includes/plugins/acf3/';
    
    // return
    return $dir;
    
}
 

// 3. Hide ACF field group menu item
//add_filter('acf/settings/show_admin', '__return_false');


// 4. Include ACF
include_once( get_template_directory() . '/includes/plugins/acf3/acf.php' );
include_once( get_template_directory() . '/includes/plugins/acf3/addon/amazon/acf-acf_amazon.php' );
include_once( get_template_directory() . '/includes/plugins/acf3/addon/amazon_image/acf-acf_amazon_image.php' );

if( function_exists('acf_add_options_page') ) {
	
	acf_add_options_page(array(
		'page_title' 	=> 'Theme General Settings',
		'menu_title'	=> 'Theme Settings',
		'menu_slug' 	=> 'theme-general-settings',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));
	
	/*
	acf_add_options_sub_page(array(
		'page_title' 	=> 'Theme Header Settings',
		'menu_title'	=> 'Header',
		'parent_slug'	=> 'theme-general-settings',
	));
	
	acf_add_options_sub_page(array(
		'page_title' 	=> 'Theme Footer Settings',
		'menu_title'	=> 'Footer',
		'parent_slug'	=> 'theme-general-settings',
	));
	*/
}

function my_acf_admin_head() {
	 echo '<style type="text/css">
           h3.hndle.ui-sortable-handle a.acf-hndle-cog { display: none; visibility: hidden }
         </style>';
}

add_action('acf/input/admin_head', 'my_acf_admin_head');


add_action('acf/save_post', function( $post_id ) {
	global $post;
	if($post->post_type == 'review') {
		$fields = $_POST['acf'];
		$message = "";
		foreach($fields as $key=>$field) {
			$object = get_field_object($key);
			if($object['type'] == 'wysiwyg') {
				preg_match('/(<img .*?>)/', $field, $matches);
				if(count($matches) < 1) {
					$message .= 'MISSING IMAGE WARNING: the field <b>'.$object['label'].'</b> is missing an image. Every section should include an image to complement the text.<br>';
				}
			}
		}
		if($message != "")
			RSG_set_admin_notice(get_current_user_id().$post_id, $message, 'error');
	}
	return;
	//exit;
	
}, 5);

// Include ACF to REST API
include_once( get_template_directory() . '/includes/plugins/acf-to-rest-api/class-acf-to-rest-api.php' );

require_once('includes/remove_bg.php');


function RSG_set_admin_notice($id, $message, $status = 'success') {
    set_transient('wpse152033' . '_' . $id, [
        'message' => $message,
        'status' => $status
    ], 30);
}

function RSG_get_admin_notice($id) {
    $transient = get_transient( 'wpse152033' . '_' . $id );
    if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] && $transient ) {
        delete_transient( 'wpse152033' . '_' . $id );
    }
    return $transient;
}

function RSG_admin_notice_error() {
	global $post;
	$notice = RSG_get_admin_notice(get_current_user_id().$post->ID);
	if (!empty($notice) && is_array($notice)) {
		echo '<div class="notice notice-error is-dismissible"><p>'.$notice['message'].'</p></div>';
	}
}
add_action( 'admin_notices', 'RSG_admin_notice_error' );


function disable_wp_emojicons() {

  // all actions related to emojis
  remove_action( 'admin_print_styles', 'print_emoji_styles' );
  remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
  remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
  remove_action( 'wp_print_styles', 'print_emoji_styles' );
  remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
  remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
  remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
  add_filter( 'emoji_svg_url', '__return_false' );
  // filter to remove TinyMCE emojis
  add_filter( 'tiny_mce_plugins', 'disable_emojicons_tinymce' );
}
function disable_emojicons_tinymce( $plugins ) {
  if ( is_array( $plugins ) ) {
    return array_diff( $plugins, array( 'wpemoji' ) );
  } else {
    return array();
  }
}

add_action( 'init', 'disable_wp_emojicons' );


function RSG_disable_embeds_code_init() {
	
	global $wp;

	// Remove the embed query var.
	$wp->public_query_vars = array_diff( $wp->public_query_vars, array(
		'embed',
	) );

	// Remove the REST API endpoint.
	remove_action( 'rest_api_init', 'wp_oembed_register_route' );

	// Turn off oEmbed auto discovery.
	add_filter( 'embed_oembed_discover', '__return_false' );

	// Don't filter oEmbed results.
	remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );

	// Remove oEmbed discovery links.
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

	// Remove oEmbed-specific JavaScript from the front-end and back-end.
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
	add_filter( 'tiny_mce_plugins', 'RSG_disable_embeds_tiny_mce_plugin' );

	// Remove all embeds rewrite rules.
	add_filter( 'rewrite_rules_array', 'RSG_disable_embeds_rewrites' );

	// Remove filter of the oEmbed result before any HTTP requests are made.
	remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );
}

add_action( 'init', 'RSG_disable_embeds_code_init', 9999 );

function RSG_disable_embeds_tiny_mce_plugin($plugins) {
    return array_diff($plugins, array('wpembed'));
}

function RSG_disable_embeds_rewrites($rules) {
    foreach($rules as $rule => $rewrite) {
        if(false !== strpos($rewrite, 'embed=true')) {
            unset($rules[$rule]);
        }
    }
    return $rules;
}

function RSG_get_country_code() {
	if(isset($_SERVER['HTTP_X_COUNTRY_CODE'])){
		//var_dump($_SERVER['HTTP_X_COUNTRY_CODE']);
	    return $_SERVER['HTTP_X_COUNTRY_CODE'];
    }	if(isset($_SERVER['HTTP_CF_IPCOUNTRY'])) {		if(!in_array($_SERVER['HTTP_CF_IPCOUNTRY'],  array(	'US', 'CA', 'GB', 'DE', 'FR', 'ES', 'IT', 'GR', 'SE', 'NO', 'IE', 'AU', 'NZ', 'MY', 'ID', 'SG', 'HK') ) ) {			return "US";		}					return $_SERVER['HTTP_CF_IPCOUNTRY'];		}
	// ip2nation/ip2location geo block removed 2026-06-21 (dead code; plaintext DB creds stripped)
}

function get_not_found_link() {
	$not_found_link = null;
	$not_found_link_us = null;
	if( have_rows('not_found_links', 'option') ):
		// loop through the rows of data
		while ( have_rows('not_found_links', 'option') ) :
			the_row();
			if( RSG_get_country_code() === get_sub_field('country_code') ) {
				$not_found_link = get_sub_field('url');
			}
			elseif( get_sub_field('country_code') == 'US' ) {
				$not_found_link_us = get_sub_field('url');
			}
		endwhile;
	endif;
	
	if($not_found_link)
		return $not_found_link;
	else
		return $not_found_link_us;
}

// Function to convert IP address (xxx.xxx.xxx.xxx) to IP number (0 to 256^4-1)
function Dot2LongIP ($IPaddr) 
{
	if ($IPaddr == "")
	{
	return 0;
	}	 
	else {
	$ip = explode(".", $IPaddr);
	return ($ip[3] + $ip[2] * 256 + $ip[1] * 256 * 256 + $ip[0] * 256 * 256 * 256);
	}
}


function rsg_set_country_cookie() {
	$sql = 'SELECT country FROM ip2nation WHERE ip < INET_ATON("'.$_SERVER['REMOTE_ADDR'].'") ORDER BY ip DESC LIMIT 0,1';

	$code = $wpdb->get_results($sql, OBJECT);

	$code = $code[0]->country;
			
	//var_dump(mysql_query($sql));
	var_dump($code);
	//list($code) = mysql_fetch_row(mysql_query($sql));
	//$code = 'US';
	//var_dump($_SERVER['REMOTE_ADDR']);
	//$wp_query->user_country_code = $code;
	setcookie( 'country_code'.$_SERVER['REMOTE_ADDR'], $code, 90 * DAYS_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
}

function featuredtoRSS($content) {
	global $post;
	
	if(!is_feed('tca') && !is_feed('tca-best')) {
		if ( has_post_thumbnail( $post->ID ) ){
			$content = '<div>' . get_the_post_thumbnail( $post->ID, 'large', array( 'style' => 'margin-bottom: 15px;' ) ) . '</div>' . $content;
		}
	}
	return $content;
}
 
add_filter('the_excerpt_rss', 'featuredtoRSS');
add_filter('the_content_feed', 'featuredtoRSS');

/*
add_action( 'wp_feed_options', function ( $feed ) {
	$feed->enable_cache( false );
});
*/

function add_featured_image_in_rss() {
		if ( function_exists( 'social_image_generator' ) && has_post_thumbnail() && 'gift-guide' != get_post_type() ) {
			$featured_image[0] = social_image_generator(get_the_ID());
		} elseif ( function_exists( 'get_the_image' ) && ( $featured_image = get_the_image('format=array&echo=0') ) ) {
			$featured_image[0] = $featured_image['url'];
		} elseif ( function_exists( 'has_post_thumbnail' ) and has_post_thumbnail() ) {
			$featured_image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'post-thumbnail' );
		} elseif ( function_exists( 'get_post_thumbnail_src' ) ) {
			$featured_image = get_post_thumbnail_src();
			if ( preg_match( '|^<img src="([^"]+)"|', $featured_image[0], $m ) )
				$featured_image[0] = $m[1];
		} else {
			$featured_image = false;
		}
		if ( ! empty( $featured_image ) ) {
			if(is_feed('tca') || is_feed('tca-best')) {
				echo "\t" . '<media:content url="' . $featured_image[0] . '" type="image/jpeg">' . "\n";
				echo "\t" . '<media:description>'. get_the_post_thumbnail_caption() .'</media:description>' . "\n";
				echo "\t" . '<media:credit>Running Shoes Guru</media:credit>' . "\n";
				echo "\t" . '</media:content>' . "\n";
			}
			else
				echo "\t" . '<enclosure url="' . $featured_image[0] . '" type="image/jpeg" />' . "\n";
		}
    
}
add_action( 'rss2_item', 'add_featured_image_in_rss' );
 
function RSG_content_image_sizes_attr( $sizes, $size ){
	$width = $size[0];
	
	768 <= $width && $sizes = '(max-width: 767px) 95vw, 768px';
	
	return $sizes;
}
add_filter( 'wp_calculate_image_sizes', 'RSG_content_image_sizes_attr', 10 , 2 );

//add_action('wp', 'RSG_genius_ale', 99999);

function RSG_genius_ale() {
	if (!is_admin()) {
		remove_action('wp_head', 'genius_ale', 9999);
	
  
  add_action( 'wp_enqueue_scripts', function() {
	  wp_enqueue_script( 'cdn.geni.us', '//cdn.geni.us/snippet.min.js', false, null, true );
	  wp_register_script( 'jquery.cdn.geni.us', get_template_directory_uri() . '/js/jquery.geni.us.5.js' );
		if (get_option('genius_ale_tsid') == '') {
			$gr_use_tsid = 4632;
		  } else {
			$gr_use_tsid = get_option('genius_ale_tsid');
		  }

		  if (get_option('genius_ale_domain') != 'geni.us' && get_option('genius_ale_domain') != '') {
			$gr_use_domain = ", 'http://" . get_option("genius_ale_domain")."'" ;
		  } else {
			$gr_use_domain = '';
		  }
		 
			// come back to this
		  $preserve_tracking = 'false';
		  if (get_option('genius_ale_preserve_tracking') == 'yes') {
			$preserve_tracking = 'true';
		  }
				// Localize the script with new data
		$rsg_array = array(
			'ale_get_on_click_checkbox_state' => ale_get_on_click_checkbox_state(),
			'gr_use_tsid' => $gr_use_tsid,
			'preserve_tracking' => $preserve_tracking,
			'gr_use_domain' => $gr_use_domain
		);
		
		wp_localize_script( 'jquery.cdn.geni.us', 'object_js', $rsg_array );

		// Enqueued script with localized data.
		wp_enqueue_script( 'jquery.cdn.geni.us');
	  //wp_enqueue_script( 'jquery.cdn.geni.us', get_template_directory_uri() . '/js/jquery.geni.us.js', array( 'jquery', 'cdn.geni.us' ), null, true );
  } );
  
	}
	
}

function RSGgetFavicon($url){
	
	// Get it from Google instead of doing all the work ourselves
	$components = parse_url($url);
	
	$hostnameComponents = explode('.', $components['host']);
	
	if (count($hostnameComponents) >= 2){
		$domain = $hostnameComponents[count($hostnameComponents)-2].'.'.$hostnameComponents[count($hostnameComponents)-1];
	} else {
		$domain = $string;
	}
	
	//$faviconURL = 'http://g.etfv.co/http://www.'.$domain.'?defaulticon=none';
	//$faviconURL = 'http://www.google.com/s2/favicons?domain=www.'.$domain.'';
	
	$faviconURL = 'https://api.faviconkit.com/www.'.$domain.'/144';
	
	$faviconURL = rsg_image_resize_ext($faviconURL,0,0,'/amazon-api-cache/', $quality = 80, $hostnameComponents[count($hostnameComponents)-2].'-icon2018.jpg');
	
	return $faviconURL;
}

if (!function_exists('array_key_first')) {
    /**
     * Gets the first key of an array
     *
     * @param array $array
     * @return mixed
     */
    function array_key_first(array $array)
    {
        if (count($array)) {
            reset($array);
            return key($array);
        }

        return null;
    }
}

if (! function_exists("array_key_last")) {
    function array_key_last($array) {
        if (!is_array($array) || empty($array)) {
            return NULL;
        }
        
        return array_keys($array)[count($array)-1];
    }
}

function get_geo_rrp($ID = null) {
	global $post;
	if(!$ID) {
		$ID = $post->ID;
	}
	$rrp = NULL;
	if(RSG_get_country_code() != "GB" && RSG_get_country_code() != "CA" && RSG_get_country_code() != "AU")
		$rrp = get_field('rrp_us', $ID);
	else {
		$rrp = get_field('rrp_'.strtolower(RSG_get_country_code()), $ID);
	}	
	return $rrp;
}

add_filter('posts_where', function ($where) {
	global $wpdb;
	//$search = "( newguru_wp_postmeta.meta_key = 'review_post_type' AND newguru_wp_postmeta.meta_value = '1' )";
	$search = "$wpdb->postmeta.meta_key = 'review_post_type' AND $wpdb->postmeta.meta_value = '1'";
	if(stripos($where, $search))
		$where = str_ireplace($search, "(( $wpdb->postmeta.meta_key = 'review_post_type' AND $wpdb->postmeta.meta_value = '1' ) OR $wpdb->posts.post_type IN ('review', 'reviews_trail', 'reviews_training', 'reviews_road') )", $where);
	return $where;
});

function build_progressive_picture ($url, $width, $height, $extra_class = "", $check_db = true, $force_max_width = false) {
	$percentage = $height * 100 / $width;
	$vw = $width*100/1200;
	$vw_xs = $width*100/576;
	$vw_md = $width*100/768;
	$extra_style = '';
	if($force_max_width)
		$extra_style = 'max-width:'.$width.'px';
	?>
	<!--
	<picture class="progressive-picture" style="padding-bottom: <?= $percentage ?>%;">
	-->
	<picture class="progressive-picture" style="<?=$extra_style?>">					
							<?php //echo base64_encode(file_get_contents(rsg_image_resize($thumbnail[0],23,14,true,true,20 ))) ?>
							<img src="<?php echo rsg_image_resize($url,$width/10,$height/10,true,$check_db,20 ); ?>" class="<?= $extra_class ?>" alt="" width="<?= $width ?>" height="<?= $height ?>" style="-webkit-filter: blur(1px);filter: blur(1px);">
							
							<img class="full-image <?= $extra_class ?>" 
							data-srcset="<?php echo rsg_image_resize($url,round($width/2),round($height/2), true, $check_db); ?>   <?= round($width/2) ?>w, <?php echo rsg_image_resize($url,$width,$height,true,$check_db); ?> <?= $width ?>w" 
							data-src="<?php echo rsg_image_resize($url,$width,$height, true, $check_db); ?>" 
							class="" alt="" width="<?= $width ?>" height="<?= $height ?>">
							<!--
							sizes="(max-width: 576px) <?= $vw_xs ?>vw, (max-width: 768px) <?= $vw_md ?>vw, <?= $vw ?>vw" 
							class="" alt="" style="max-width:<?=$width?>px">
							-->
							
							<noscript>
							  <img src="<?php echo rsg_image_resize($url,$width,$height,true,$check_db); ?>" class="" alt="">
							</noscript>
	</picture>
	<?php
}

add_filter('admin_comment_types_dropdown', function($array) {
	return $array + array('review'=>'Review','question'=>'Question');
	//return $array;
});


add_action('load-edit-comments.php', function() {
    $screen = get_current_screen();

    add_filter("manage_{$screen->id}_columns", 'RSG_comments_add_columns');
	add_action(
        "manage_{$screen->post_type}_posts_custom_column",
        'RSG_comments_type_colum',
        10, 2
    );
});

add_action('manage_comments_custom_column', 'RSG_comments_type_colum', 10, 2);


function RSG_comments_add_columns($cols) {
    //$cols['type'] = 'Type';
	$my_columns = array(
		'type' => 'Type'
	);
	$cols = array_slice( $cols, 0, 2, true ) + $my_columns + array_slice( $cols, 2, NULL, true );
	
    return $cols;
}

function RSG_comments_type_colum($col, $post_id) {
	global $comment;
	//var_dump($comment);
	switch($col)
    {
        case 'type':
			echo '<strong>';
			if($comment->comment_type != "")
				echo ucfirst($comment->comment_type);
			else
				echo "Default";
			echo '</strong>';
            break;
    }
}

add_action( 'admin_head', function (){
	echo '<style>.comments th#type {width: 80px;}</style>';
});

add_action( 'add_meta_boxes_comment', function($comment) {
		add_meta_box( 'pmg-comment-title', ucfirst($comment->comment_type).' Specific Settings', 'RSG_comment_meta_box_cb', 'comment', 'normal', 'high' );
});

function RSG_comment_meta_box_cb( $comment ){
    $answer = get_comment_meta( $comment->comment_ID, 'answer', true );
	$types = Array('Default'=>'', 'Review'=>'review', 'Question'=>'question');
    wp_nonce_field( 'rsg_comment_update', 'rsg_comment_update', false );
    ?>
	<p>
		<span style="color: #999;">Type</span> 
		<select name="rsg_comment_type">
		<?php
		foreach($types as $key => $value) {
			$checked = '';
			if($comment->comment_type == $value)
				$checked = 'selected';
			echo '<option value="'.$value.'" '.$checked.'>'.$key.'</option>';
		}
		?>
		</select>
    </p>
	<?php
	if($comment->comment_type == "question"):
	?>
    <p>
        <label for="rsg_comment_answer"><?php _e( 'Answer' ); ?></label>
        <textarea name="rsg_comment_answer" class="widefat"><?php echo esc_attr( $answer ); ?></textarea>
    </p>
    <?php
	endif;
	
	if($comment->comment_type == "review"):
		/*
		$review_args = array(
			'fields' => 'ids',
			'meta_key' => 'rate',
			'status' => 'approve',
			'post_type' => 'review'
		);
		$reviews = get_comments( $review_args );
		
		foreach($reviews as $rated_comment) {
			//var_dump($rated_comment);
			//update_comment_meta( $rated_comment, 'meta_rate', esc_attr( get_comment_meta( $rated_comment, 'rate', true ) ) );
			
			//update_comment_meta( $rated_comment, 'meta_distance', esc_attr( '20 - 40' ) );
			
			//update_comment_meta( $rated_comment, 'meta_activity', esc_attr( 'running' ) );	 
			
		}
		*/
		
		$comment_metas = (get_comment_meta($comment->comment_ID));
		
		foreach($comment_metas as $key=>$value) {
			if(substr( $key, 0, 5 ) === "meta_") {
				echo '<p><span style="color: #999;">'.$key.'</span> - ';
				echo $value[0].'</p>';
			}
		}
		
	endif;
}

add_action( 'edit_comment', function ( $comment_id ) {
    if( ! isset( $_POST['rsg_comment_update'] ) || ! wp_verify_nonce( $_POST['rsg_comment_update'], 'rsg_comment_update' ) )
        return;
    if( isset( $_POST['rsg_comment_answer'] ) )
        update_comment_meta( $comment_id, 'answer', esc_attr( $_POST['rsg_comment_answer'] ) );
	return;
});


function rsg_preprocess_comment( $commentdata ) {
	if( ( isset( $_POST['rsg_comment_type'] ) ) ) {
		if($_POST['rsg_comment_type'] == '')
			$commentdata['comment_type'] = "";
		else
			$commentdata['comment_type'] = wp_filter_nohtml_kses( $_POST['rsg_comment_type'] );
    }
    return $commentdata;
}

add_filter( 'wp_update_comment_data' , 'rsg_preprocess_comment' );

add_action( 'current_screen', function ( $screen ) {
    if ( $screen->id != 'edit-comments' )
        return;

    // Check if our Query Var is defined    
    if( isset( $_GET['filter-by-post-id'] ) )
        add_action( 'pre_get_comments', 'wpse_63422_list_comments_from_specific_post', 10, 1 );

    
}, 10, 2 );



/**
 * Only display comments of specific post_id
 */ 
function wpse_63422_list_comments_from_specific_post( $clauses )
{
    $clauses->query_vars['post_id'] = $_GET['filter-by-post-id'];
}

add_action( 'restrict_manage_comments', function(){
	echo '<label class="screen-reader-text" for="filter-by-post-id">' . __( 'Filter by post type' ) . '</label>';
	echo '<input class="search-box" type="search" id="filter-by-post-id" name="filter-by-post-id" value="'.$_GET['filter-by-post-id'].'" placeholder="filter by post ID">';
} );


// The function that handles the AJAX request
function RSG_post_user_review_callback() {
	global $post;
	
	$nonce = $_REQUEST['rsg_ajax_security'];
	//check_ajax_referer( 'rsg-ajax-comment-voting-nonce', 'rsg_ajax_security' );
	if ( ! wp_verify_nonce( $nonce, 'rsg-ajax-comment-voting-nonce' ) ) {
		die( 'Security check'.$nonce );
	}
	else {
		$comment_meta = Array();
		foreach($_REQUEST as $key=>$value) {
			if(substr( $key, 0, 5 ) === "meta_")
				$comment_meta[$key] = $value;
				
		}
		$commentdata = array(
			'comment_post_ID'      => $_REQUEST['postid'],             // To which post the comment will show up.
			'comment_author'       => $_REQUEST['author'],     // Fixed value - can be dynamic.
			'comment_author_email' => $_REQUEST['author_email'], // Fixed value - can be dynamic.
			'comment_content'      => $_REQUEST['content'], // Fixed value - can be dynamic.
			'comment_type'         => $_REQUEST['type'],                    // Empty for regular comments, 'pingback' for pingbacks, 'trackback' for trackbacks.
			'comment_parent'       => 0,                     // 0 if it's not a reply to another comment; if it's a reply, mention the parent comment ID here.
			'comment_meta'	       => $comment_meta
		);
		 
		// Insert new comment and get the comment ID.
		$comment_id = wp_new_comment( $commentdata );
		echo $comment_id;
		die(); // this is required to return a proper result
	}
}
add_action( 'wp_ajax_rsg_post_user_review', 'RSG_post_user_review_callback' );
add_action( 'wp_ajax_nopriv_rsg_post_user_review', 'RSG_post_user_review_callback' );


function RSG_expert_score_ticket($stars, $extra_class = "") {
?>
	<div class="top-ticket icon-ticket score-ticket text-center <?=$extra_class?>" style="<?= ($stars < 7 ? 'background-color:#f8c427' : '') ?>">
			<div style="position: absolute;top: 8px;right: 8px;line-height: 0;">
				<!--
				<a href="#expert-modal" aria-label="Expert bio" data-toggle="modal">
					<svg style="width: 0.9rem;height: 0.9rem;">
						<use xlink:href="<?=get_template_directory_uri()?>/images/icons.svg#icon-question"></use>
					</svg>
				</a>
				-->
			</div>
			<span class="small">Expert score</span>
			<div class="h4" style="letter-spacing: 0.05em;min-width: 4em;color:#fff">
				<svg style="width: 1.5rem;height: 1.5rem;margin-top: -0.2rem;">
					<use xlink:href="<?=get_template_directory_uri()?>/images/icons.svg#icon-rating-whole-inverted"></use>
				</svg>
				<!--
				<svg style="width: 1.5rem;height: 1.5rem;margin-top: -0.2rem;">
					<use xlink:href="<?=get_template_directory_uri()?>/images/icons.svg#icon-smiley<?= ($stars < 7 ? '-meh' : '') ?>"></use>
				</svg>
				-->
				<?=$stars?><span style="font-weight: normal;opacity: .7;">/10</small>
			</div>
	</div>
<?php
}

// Disable WordPress sanitization to allow more than just $allowedtags from /wp-includes/kses.php.
remove_filter( 'pre_user_description', 'wp_filter_kses' );
// Add sanitization for WordPress posts.
add_filter( 'pre_user_description', 'wp_filter_post_kses' );

add_filter( 'get_the_author_description', function( $value ){
	if(!is_author()){
		//$value = preg_replace("/<br\W*?\/>/", " ", $value);
		$value =  str_replace("<br>", " ", $value);
		$value =  str_replace("<br/>", " ", $value);
		$allowed_html = array(
			'a' => array(
				'href' => array(),
				'title' => array()
			),
			'em' => array(),
			'strong' => array(),
		);
		$value = wp_kses( $value, $allowed_html );
		//$value = rsg_truncate($value, 500);
		//$value = $value[0];
	}
	$value = rsg_truncate($value, 500);
	$value = $value[0];
	return $value;
});



/* Review structured data (JSON-LD) - schema pass 2026-06-21 */
require_once get_template_directory() . '/includes/rsg-schema.php';
