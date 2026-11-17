<?php
/*
Template Name: Template Reviews Search
*/
?>
<?php
$main_page_url = get_permalink();
$both = (get_query_var('both')) ? get_query_var('both') : null;

if(isset($both) && $both != null) {
	if(get_term_by('slug', $both, 'brands') != false)
		$brands = $both;
	elseif(get_term_by('slug', $both, 'shoes_type') != false)
		$shoes_type = $both;
}
else {
	$brands = (get_query_var('brands')) ? get_query_var('brands') : null;
	$shoes_type = (get_query_var('shoes_type')) ? get_query_var('shoes_type') : null;
}

if(isset($brands) && $brands == 'all' && isset($shoes_type) && $shoes_type == 'all') {
	wp_redirect( $main_page_url , 301 );
}

$s_rev = get_query_var('s_rev', NULL);
if($s_rev  == "")
	$s_rev = NULL;
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$brands = ($brands!='all') ? $brands : null;
$shoes_type = ($shoes_type!='all') ? $shoes_type : null;
$reviews_the_query = NULL;
$args = array();
$args['meta_key'] = "review_post_type";
/*
$args['meta_query'] = array(
	array(
	'key' => 'review_post_type',
	'value' => '1'
	)
);
*/
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

$args['meta_value'] = '1';
$args['ignore_sticky_posts'] = 1;
$args['post_type'] = Array("post", "review", 'reviews_trail', 'reviews_training', 'reviews_road');
$args['showposts'] = 10;
$args['paged'] = $paged;
$args['brands'] = $brands;
$args[$term_name] = $shoes_type;
$args['s'] = $s_rev;
$brands_name = null;
$shoes_type_name = null;
if($args['brands']) {
	$brands_name = get_term_by('slug', $args['brands'], 'brands');
}
if($args[$term_name]) {
	$shoes_type_name = get_term_by('slug', $args[$term_name], $term_name);
}

if(isset($brands_name) && $brands_name == false) {
	if($shoes_type_name == false)
		wp_redirect( $main_page_url , 301 );
	else
		wp_redirect( $main_page_url .'all/'.$shoes_type_name->slug, 301 );
	exit;
}

if(isset($shoes_type_name) && $shoes_type_name == false) {
	if($brands_name == false)
		wp_redirect( $main_page_url , 301 );
	else
		wp_redirect( $main_page_url .$brands_name->slug.'/all/', 301 );
	exit;
}
	
$banner_position = 2;
global $ads_2013_position;
$ads_2013_position = 'search';


$reviews_the_query = new WP_Query($args);

global $matching;
$matching = $reviews_the_query->found_posts;

?>
<?php 
get_header();
?>

<style>
.list-group-checkbox a {
	display: block;
	color: inherit;
	font-weight: normal;
	line-height: 3rem;
}
.list-group-checkbox a:first-child {
	margin-top:-.9rem;
}
.list-group-checkbox a:before {
	content: '';
	display: inline-block;
	width: 20px;
	height: 20px;
	border-radius: 10px;
	border: solid 1px #bbd3f5;
	margin-right: 8px;
	vertical-align: text-top!important;
}

.list-group-checkbox a.active:before {
	background: var(--marigold);
	content: '✓';
	line-height: 20px;
	text-align: center;
	color: white;
	border: 0;
	font-size: 75%;
	padding-top: 1px;
	font-weight: bold;
}

.text-warning {
	color: var(--marigold);
}
</style>


<div class="spacer"></div>

<div class="">
	<div class="container">
		<div class="row">
			<div class="col-lg-3 d-none d-lg-block">
				<div style="border-top:1px solid var(--light-blue-grey);">
					<div style="padding:24px">
						<b style="color:var(--water-blue);">
							Search by name
						</b>
						<div class="spacer-sm"></div>
						
						<div class="search-option" style="border: 1px solid var(--light-blue-grey);padding: 8px;border-radius: 3px;margin: 0;margin-bottom: 0px;width: 100%;background: #fff;line-height: 1rem;">
							<form action="<? the_permalink(); ?>">
								<input type="text" name="s_rev" placeholder="Search by name..." aria-label="Type a keyword" value="" class="" onclick="ga('send', 'event', 'Reviews Page Heatmap', 'Search Filter Click', '')" style="border: none;outline: 0;width: calc(100% - 23px);float: left;padding: 0;box-shadow: none;font-size: 85%;line-height: 1;">
								<button type="submit" aria-label="search" style="min-width: auto;padding: 0;border: 0;background: #fff;box-shadow: none;margin: 0;font-size: 85%;color: var(--marigold);">
									<svg style="width: 1em;height: 1em;"><use xlink:href="<?=get_template_directory_uri()?>/images/icons.svg#icon-search"></use></svg>
								</button>
							</form>
						</div>
						<!--
						<form action="<? the_permalink(); ?>">
						<input type="text" name="s_rev" placeholder="Search by name..." value="<?=$s_rev?>" class="form-control" onclick="ga('send', 'event', 'Reviews Page Heatmap', 'Search Filter Click', '')" style="">
						
						<div class="clearfix"></div>
						</form>
						-->
					</div>
				</div>
				
				<?php
				if(!$args['shoes_type'] && $brands_name->term_id > 0):
					$args_family = array(
						'taxonomy' => 'shoes_family',
						'hierarchical' => false,

						'meta_query' => array(
							array(
								'key'     => "brand",
								'value'   => $brands_name->term_id,
								'compare' => "="
							)
						)
					);

					$terms = get_terms( $args_family );
					
				if(count($terms) > 0):
				?>
				<div style="border-top:1px solid var(--light-blue-grey);">
					<div style="padding:24px">
						<b style="color:var(--water-blue);">
							<?=$brands_name->name?> Families
						</b>
						<div class="spacer-sm"></div>
						
						<?php
						

						$terms_shoestype_arr = $terms;
						
						?>
						
						<div class="list-group-checkbox">
						<?php
						
						foreach ($terms_shoestype_arr as $term) {
							if($args['brands'])	{
								$found_posts = $term->count;
								$url_str = $args['brands'];
							}
							else	{
								$found_posts = $term->count;
								$url_str = 'all';
							}
							
							$url = $main_page_url. $url_str .'/'.$term->slug;
							if(isset($s_rev))
								$url = add_query_arg( 's_rev', $s_rev, $url );

							if($args['shoes_family'] == $term->slug) {
								$active_shoe_family = $term->name;
								echo '<a  class="active">'.$term->name.' ('.$found_posts.')</a>';
							}
							elseif( $found_posts > 0 )
								echo '<a href="'.$url.'" class="" onclick="ga(\'send\', \'event\', \'Reviews Page Heatmap\', \'Shoe Types Filter Click\', \''.$term->name.'\')">'.$term->name.' ('.$found_posts.')</a>';
								//echo '<li><a href="'.add_query_arg(array ( $term->taxonomy=>$term->slug, 'paged' => '1' )).'">'.$term->name.'  ('.$found_posts.')</a></li>';
						}
						?>
						</div>
						
						
					</div>
				</div>
				<?php
				endif;
				endif;
				?>
				
				<?php
				if(!$args['shoes_family']):
				?>
				<div style="border-top:1px solid var(--light-blue-grey);">
					<div style="padding:24px">
						<b style="color:var(--water-blue);">
							Shoe Types
						</b>
						<div class="spacer-sm"></div>
						
						<?php 
						$terms = get_terms( 'shoes_type', null );
						$terms_shoestype_arr = Array();
						foreach ($terms as $term) {
							if(isset($s_rev)) {
								if($args['brands'])	{
									$brand_filter = array(
										'fields' => 'ids',
										'posts_per_page' => '-1',
										'post_type' => $args['post_type'],
										'meta_key' => "review_post_type",
										'meta_value' => '1',
										's' => $s_rev,
										'tax_query' => array(
											'relation' => 'AND',
											array(
												'taxonomy' => 'shoes_type',
												'field' => 'slug',
												'terms' => array($term->slug),
											),
											array(
												'taxonomy' => 'brands',
												'field' => 'slug',
												'terms' => array($args['brands']),
											)
										)
									);
								}
								else {
									$brand_filter = array(
										'fields' => 'ids',
										'posts_per_page' => '-1',
										'post_type' => $args['post_type'],
										'meta_key' => "review_post_type",
										'meta_value' => '1',
										's' => $s_rev,
										'tax_query' => array(
											'relation' => 'AND',
											array(
												'taxonomy' => 'shoes_type',
												'field' => 'slug',
												'terms' => array($term->slug),
											),
										)
									);
								}
								$tax_query = new WP_Query($brand_filter);
								$term->count = $tax_query->found_posts;
							}
							elseif($args['brands'])	{
								$brand_filter = array(
									'fields' => 'ids',
									'posts_per_page' => '-1',
									'post_type' => $args['post_type'],
									'meta_key' => "review_post_type",
									'meta_value' => '1',
									'tax_query' => array(
										'relation' => 'AND',
										array(
											'taxonomy' => 'shoes_type',
											'field' => 'slug',
											'terms' => array($term->slug),
										),
										array(
											'taxonomy' => 'brands',
											'field' => 'slug',
											'terms' => array($args['brands']),
										)
									)
								);
								$tax_query = new WP_Query($brand_filter);
								$term->count = $tax_query->found_posts;
							}
							else {
								$brand_filter = array(
									'fields' => 'ids',
									'posts_per_page' => '-1',
									'post_type' => $args['post_type'],
									'meta_key' => "review_post_type",
									'meta_value' => '1',
									'tax_query' => array(
										'relation' => 'AND',
										array(
											'taxonomy' => 'shoes_type',
											'field' => 'slug',
											'terms' => array($term->slug),
										)
									)
								);
								$tax_query = new WP_Query($brand_filter);
								$term->count = $tax_query->found_posts;
							}
							
							
							if($term->count >0)
								$terms_shoestype_arr[] = $term;
							
						}
						?>
						
						<div class="list-group-checkbox">
						<?php
						foreach ($terms_shoestype_arr as $term) {
							if($args['brands'])	{
								$found_posts = $term->count;
								$url_str = $args['brands'];
							}
							else	{
								$found_posts = $term->count;
								$url_str = 'all';
							}
							
							$url = $main_page_url. $url_str .'/'.$term->slug;
							if(isset($s_rev))
								$url = add_query_arg( 's_rev', $s_rev, $url );

							if($args['shoes_type'] == $term->slug) {
								$active_shoe_type = $term->name;
								echo '<a  class="active">'.$term->name.' ('.$found_posts.')</a>';
							}
							elseif( $found_posts > 0 )
								echo '<a href="'.$url.'" class="" onclick="ga(\'send\', \'event\', \'Reviews Page Heatmap\', \'Shoe Types Filter Click\', \''.$term->name.'\')">'.$term->name.' ('.$found_posts.')</a>';
								//echo '<li><a href="'.add_query_arg(array ( $term->taxonomy=>$term->slug, 'paged' => '1' )).'">'.$term->name.'  ('.$found_posts.')</a></li>';
						}
						?>
						</div>
						
						
					</div>
				</div>
				<?php
				endif;
				?>
				
				<div style="border-top:1px solid var(--light-blue-grey);">
					<div style="padding:24px">
						<b style="color:var(--water-blue);">
							Brands
						</b>
						<div class="spacer-sm"></div>
						
						<?php
						$terms = get_terms( 'brands', null );
						$terms_arr = Array();
						$terms_arr_extra = Array();
						foreach ($terms as $term) {
							
							if(isset($s_rev)) {
								if(!empty($args['shoes_type']))	{
									$brand_filter = array(
										'fields' => 'ids',
										'posts_per_page' => '-1',
										'post_type' => $args['post_type'],
										'meta_key' => "review_post_type",
										'meta_value' => '1',
										's' => $s_rev,
										'tax_query' => array(
											'relation' => 'AND',
											array(
												'taxonomy' => 'shoes_type',
												'field' => 'slug',
												'terms' => array($args['shoes_type']),
											),
											array(
												'taxonomy' => 'brands',
												'field' => 'slug',
												'terms' => array($term->slug),
											)
										)
									);
								}
								else {
									$brand_filter = array(
										'fields' => 'ids',
										'posts_per_page' => '-1',
										'post_type' => $args['post_type'],
										'meta_key' => "review_post_type",
										'meta_value' => '1',
										's' => $s_rev,
										'tax_query' => array(
											'relation' => 'AND',
											array(
												'taxonomy' => 'brands',
												'field' => 'slug',
												'terms' => array($term->slug),
											)
										)
									);
								}
								$tax_query = new WP_Query($brand_filter);
								$term->count = $tax_query->found_posts;
							}
							elseif(!empty($args['shoes_type']))	{
								$brand_filter = array(
								'fields' => 'ids',
								'posts_per_page' => '-1',
								'post_type' => $args['post_type'],
								'meta_key' => "review_post_type",
								'meta_value' => '1',
								'tax_query' => array(
									'relation' => 'AND',
									array(
										'taxonomy' => 'shoes_type',
										'field' => 'slug',
										'terms' => array($args['shoes_type']),
									),
									array(
										'taxonomy' => 'brands',
										'field' => 'slug',
										'terms' => array($term->slug),
									)
								)
								);
								$tax_query = new WP_Query($brand_filter);
								$term->count = $tax_query->found_posts;
							}
							else	{
								$brand_filter = array(
								'fields' => 'ids',
								'posts_per_page' => '-1',
								'post_type' => $args['post_type'],
								'meta_key' => "review_post_type",
								'meta_value' => '1',
								'tax_query' => array(
									'relation' => 'AND',
									array(
										'taxonomy' => 'brands',
										'field' => 'slug',
										'terms' => array($term->slug),
									)
								)
								);
								$tax_query = new WP_Query($brand_filter);
								$term->count = $tax_query->found_posts;
							}
							
							
							$term_meta = get_option( 'rsg_taxonomy_'.$term->term_id );
							if(isset($term_meta['custom_term_checkbox']) && $term_meta['custom_term_checkbox'] == 1 && $term->count >0)
								$terms_arr[] = $term;
							elseif($term->count > 0)
								$terms_arr_extra[] = $term;
						}
						if( (count($terms_arr) + count($terms_arr_extra)) < 12 ) {
							$terms_arr = array_merge($terms_arr, $terms_arr_extra);
							$terms_arr_extra = null;
						}
						?>
						<div class="list-group-checkbox">
						<?php
						foreach ($terms_arr as $term) {
							if(!empty($args['shoes_type']))	{
								
								$found_posts = $term->count;
								$url_str = $args['shoes_type'].'/';
							}
							else	{
								$found_posts = $term->count;
								$url_str = 'all/';
							}
							
							$url = $main_page_url.$term->slug.'/'. $url_str;
							if(isset($s_rev))
								$url = add_query_arg( 's_rev', $s_rev, $url );
							
							if($args['brands'] == $term->slug) {
								$active_brand = $term->name;
								echo '<a class="active">'.$term->name.' ('.$found_posts.')</a>';
							}
							elseif( $found_posts > 0 )
								echo '<a href="'.$url .'" class="" onclick="ga(\'send\', \'event\', \'Reviews Page Heatmap\', \'Brands Filter Click\', \''.$term->name.'\')">'.$term->name.' ('.$found_posts.')</a>';
								//echo '<li><a href="'.add_query_arg(array ( $term->taxonomy=>$term->slug, 'paged' => '1' )).'">'.$term->name.'  ('.$found_posts.')</a></li>';
						}
						?>
						</div>
						<div class="list-group-checkbox list-more-brands" style="display:none;">
						<div></div>
						<?php
						if( is_array($terms_arr_extra) && count($terms_arr_extra) > 0 ) {
							foreach ($terms_arr_extra as $term) {
								if(!empty($args['shoes_type']))	{
									
									$found_posts = $term->count;
									$url_str = $args['shoes_type'].'/';
								}
								else	{
									$found_posts = $term->count;
									$url_str = 'all/';
								}
								
								$url = $main_page_url.$term->slug.'/'. $url_str;
								if(isset($s_rev))
									$url = add_query_arg( 's_rev', $s_rev, $url );
								
								if($args['brands'] == $term->slug) {
									$active_brand = $term->name;
									echo '<a class="active">'.$term->name.' ('.$found_posts.')</a>';
								}
								elseif( $found_posts > 0 )
									echo '<a href="'.$url .'" class="" onclick="ga(\'send\', \'event\', \'Reviews Page Heatmap\', \'Brands Filter Click\', \''.$term->name.'\')">'.$term->name.' ('.$found_posts.')</a>';
									//echo '<li><a href="'.add_query_arg(array ( $term->taxonomy=>$term->slug, 'paged' => '1' )).'">'.$term->name.'  ('.$found_posts.')</a></li>';
							}
						}
						?>
						</div>
						<div class="spacer-xs"></div>
						<?php
						if( isset($terms_arr_extra) ):
						?>
							<div class=""><a id="btn-more-brands" href="#" class="text-warning"><span style="transform: rotate(90deg);display: inline-block;font-size: 1.2em;line-height: 1em;padding: 0px .2em;">&raquo;</span> More Brands</a></div>
						<?php
						endif;
						?>
						
						<div class="spacer"></div>
						
						
					</div>
				</div>
				<!--
				<div style="position:sticky; top: 60px">
					<div align="center" data-freestar-ad="__160x600" id="runningshoesguru_sidewall_left">
					  <script data-cfasync="false" type="text/javascript">
						freestar.config.enabled_slots.push({ placementName: "runningshoesguru_sidewall_left", slotId: "runningshoesguru_sidewall_left" });
					  </script>
					</div>
				</div>
				-->
			</div>
			<div class="col-lg-9">
				<h1 class="page-title" itemprop="headline">
						<span itemprop="name">
						<?php
						/*
						if($brands && !$shoes_type)
							echo $brands_name->name.' ';
						if(!$brands && $shoes_type)
							echo $shoes_type_name->name.' ';
						*/
						if($brands && !isset($s_rev))
							echo $brands_name->name.' ';
						if($shoes_type && !isset($s_rev))
							echo $shoes_type_name->name.' ';
						the_title();
						?>
						</span>
				</h1><!--post-title-->
				<div class="spacer-sm"></div>
				
					
				<?php
					if(isset($s_rev)):
						//echo '<div class="hidden-xs hidden-ms"><p itemprop="description" style="margin-bottom:0">Matching: '.$s_rev.'</p></div>';
					elseif(isset($brands_name) && !$shoes_type && $brands_name->description):
						echo '<div class=""><p itemprop="description" style="margin-bottom:0">'.$brands_name->description.'</p></div>';
					elseif(isset($shoes_type_name) && !$brands_name && $shoes_type_name->description):
						echo '<div class=""><p itemprop="description" style="margin-bottom:0">'.$shoes_type_name->description.'</p></div>';
					elseif(isset($shoes_type_name) && $term_name == 'shoes_family' && $shoes_type_name->description):
						echo '<div class=""><p itemprop="description" style="margin-bottom:0">'.$shoes_type_name->description.'</p></div>';
					
					else:
					?>
					<div class="">
							<p itemprop="description" class="" style="margin-bottom:0">
							The <?php if($brands) echo 'best '.$brands_name->name.' '.get_the_title(); elseif($shoes_type) echo 'best '.$shoes_type_name->name.' '.get_the_title(); else echo '<a href="/guide/best-running-shoes-2024/" style="font-weight:400">best Running Shoes</a> reviews'; ?> on the internet! Our testers run and analyze all the latest shoes - and you can read hundreds of feedback from our readers!
							</p>
					</div>
					<?php
					endif;

				?>
					<div class="spacer-xl d-none d-md-block"></div>
					
					<div class="row">
						<div class="col-md-6 order-md-2 text-right">
							<div class="spacer d-md-none"></div>
					
								<?php
								global $query_brand;
								$brands_meta = null;
								if(isset($brands_name))
									$brands_meta =  get_option( "rsg_taxonomy_".$brands_name->term_id );
								if(isset($brands_meta['custom_term_meta']) && $brands_meta['custom_term_meta'] != '' && !$shoes_type && $paged == 1):
									
									$query_brand = new WP_Query( array('p' => $brands_meta['custom_term_meta'], 'post_type' => 'any' ) );
									//var_dump($query_brand);
									$query_brand->posts[0]->featured = 1;
									//echo 'p='.$brands_meta['custom_term_meta'];

									// The Loop
									while ( $query_brand->have_posts() ) :
									
									$query_brand->the_post();
										if( (function_exists( 'has_post_thumbnail' ) ) && ( has_post_thumbnail() ) ) :
											if ($thumbnail = wp_get_attachment_image_src ( get_post_thumbnail_id ( $post->ID ), array(9999,9999)) ):
											?>
											
											
											<a href="<?php the_permalink(); ?>#top" rel="bookmark" class="btn btn-block btn-warning" onclick="gtag('event', 'reviews_best_click', {'link': '<?php the_title(); ?>','screen_name': '<?=$title;?>'});">
												<span style="display:inline-block;font-size:1.8em;color:#FFF;height: 1.2rem;overflow: hidden;line-height: 1.2rem;margin-bottom: -0.3rem;">★</span> <span style="text-decoration:underline"><?php the_title(); ?></span>
											</a>
											<div class="spacer d-md-none"></div>
										<?php
										endif;
										endif;
									endwhile;	
									wp_reset_postdata();
									//var_dump($wp_query->posts);
									//if(isset($query_brand))
										//$reviews_the_query->posts = array_merge($query_brand->posts,$reviews_the_query->posts);
									//wp_reset_postdata();
								endif;
								?>
						</div>
						<div class="col order-md-1">
							<h4>
								<?php echo $reviews_the_query->found_posts ?> matching reviews
							
							
								<?php
								if($args['brands'] || $args['shoes_type'] || isset($s_rev)):
								?>
										<span class="text-center d-none d-lg-inline-block">
											&nbsp;
												<?php
												
												echo ' <a href="'.$main_page_url.'" class="btn btn btn-outline-warning" style="background-color:#fff2ee;padding-left:1em;padding-right:0.5em">Clear Filters <span class="fontello-icon-cancel-1 text-muted"></span></a>';
												
												?>
											
										</span>
								<?php
								endif;
								?>
							</h4>
						</div>
						
					</div>
					
					
					<div class="row d-lg-none">
						<div class="col-12 col-sm-4">
							<h4 style="margin-top:0" class="d-none d-sm-block block-title">Search</h4>
							<div class="spacer-xs "></div>
							<div class="search-option" style="border: 1px solid #ccc;padding: 5px;border-radius: 3px;margin: 0;margin-bottom: 0px;width: 100%;background: #fff;">
								<form action="">
								<input type="text" name="s_rev" placeholder="Search within..." value="<?=$s_rev?>" class="form-control"  onclick="ga('send', 'event', 'Reviews Page Heatmap', 'Search Filter Click', '')" style="border: none;outline: 0;width: calc(100% - 23px);float: left;padding: 0;box-shadow: none;">
								<button type="submit" style="min-width: auto;padding: 0;line-height: 1em;font-size: 1em;border: 0;background: #fff;box-shadow: none;">
								<i class="fontello-icon-search" style="color: #333;"></i>
								</button>
								<div class="clearfix"></div>
								</form>
							</div>
							<div class="spacer d-sm-none"></div>
						</div>
						<div class="col-12 col-sm-8">
							<div class="row">
								<div class="col-12">
									<h4 style="margin-top:0" class="block-title">Filter</h4>
									<div class="spacer-xs "></div>
								</div>
								<div class="col-6">
									<select id="frm_terms_shoestype_arr" class="comment-form-input-fields form-control" onchange="ga('send', 'event', 'Reviews Page Heatmap', 'Shoe Types Filter Click', this.options[this.selectedIndex].text)">
										<option value="all">All Types</option>
										<?php
										
										foreach ($terms_shoestype_arr as $term) {
											if($args['shoes_type'] == $term->slug)
												echo '<option class="disabled" selected value="'.$term->slug.'">'.$term->name.'</option>';
											else
												echo '<option value="'.$term->slug.'">'.$term->name.'</option>';
										}
										?>
									</select>
								</div>
								<div class="col-6">
									<select id="frm_terms_brands_arr" class="comment-form-input-fields form-control" onchange="ga('send', 'event', 'Reviews Page Heatmap', 'Brands Filter Click', this.options[this.selectedIndex].text)">
										<option value="all">All Brands</option>
										<?php
										foreach ($terms_arr as $term) {
											if($args['brands'] == $term->slug)
												echo '<option class="disabled" selected value="'.$term->slug.'">'.$term->name.'</option>';
											else
												echo '<option value="'.$term->slug.'">'.$term->name.'</option>';
										}
										?>
									</select>
								</div>
							</div>
						</div>
					</div>
					
					<div class="spacer"></div>
					
					
					
							

						<?php 
						$wp_query = $reviews_the_query;
						
						if( have_posts() ) : 

						
						get_template_part( 'include', 'reviewslist-fullwidth-2020' );
						?>
						<?php else : ?>
						<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<div class="spacer-l"></div>
<div class="spacer-xxl"></div>


<script>
var tmpCurrentUrl = '<?=$main_page_url?>';
var tmpCurrentSearch = '<?=$s_rev?>';
$(document).ready(function(){
	$('#frm_terms_shoestype_arr, #frm_terms_brands_arr').change(function(){
		var newURL = tmpCurrentUrl+$('#frm_terms_brands_arr').val()+'/'+$('#frm_terms_shoestype_arr').val()+'/';
		if(tmpCurrentSearch.length > 0)
			newURL = newURL+'?s_rev='+tmpCurrentSearch;
		if( document.URL == newURL) {
			
		}
		else {
			window.location = newURL;
		}
		
		return false;
	});
});
</script>

<?php get_footer();?>