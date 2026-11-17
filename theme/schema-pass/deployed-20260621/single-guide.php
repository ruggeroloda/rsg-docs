<?php
/*
Template Name: Single Buying Guide
*/

get_header();
//$content_before_reviews = get_field( "content_before_reviews" );
$not_found_link = get_not_found_link();
$content_after_reviews = get_field( "content_after_reviews" );
$featured = Array();
$featured[] = "best_choice";
$featured[] = "premium_pick";
$featured[] = "best_value";


$keywords_i = stripcslashes(get_post_meta($post->ID, "keywords", true));
$keywords_i = str_replace('"', '', $keywords_i);

get_template_part( 'include', 'single-fullwidth-css' );

$title = get_the_title(). ' ('. get_the_modified_time('F Y') .')';
if(get_field('in_page_title'))
	$title = get_field('in_page_title'). ' ('. get_the_modified_time('F Y') .')';

$sub_title = get_field('in_page_subtitle');

?>
<?php
if($_REQUEST['test'] == 1 || get_field('go_live') == true):
?>
<style>
ul.procons {
	list-style-type: none;
	line-height: 1.5;
}

ul.procons li {
	margin-bottom: 0.5rem;
	position: relative;
}

#why_you_can_trust_us {
	
}


@media (min-width: 1200px) {
  #why_you_can_trust_us {
	  border-bottom:none;
	border-right: 1px solid;
	}
}

</style>
<?php if( have_posts() ) : while( have_posts() ) : the_post(); 


$author_name = ucfirst(get_the_author_meta( 'display_name', $post->post_author ));
$author_first_name = ucfirst(get_the_author_meta( 'first_name', $post->post_author ));
$author_image = rsg_image_resize( the_author_image_url($post->post_author , 1 ) ,40,40,true,true,70 );
$author_image_big = rsg_image_resize( the_author_image_url($post->post_author , 1 ) ,80,80,true,true,70 );
$author_link = get_author_posts_url( $post->post_author );
$author_introduction = get_field('long_introduction', 'user_'.$post->post_author);
if(!$author_introduction)
	$author_introduction = get_field('introduction', 'user_'.$post->post_author);
if(!$author_introduction)
	$author_introduction = "Running Expert";
				
?>
<div class="container">
	<div class="row">
		<div class="col small">
			<div class="spacer"></div>
				<span>
				  <span class="d-none d-md-inline">
					
						<a  href="<?=  get_bloginfo('url') .'/' ?>">
							<span itemprop="name">Home</span>
							<meta itemprop="position" content="1" />
						</a>
						
				  </span>
				  <i class="arrow right d-none d-md-inline-block" style="margin-left:1em;margin-right: 1em;"></i>
				  <span>
						<a  href="<?= get_bloginfo('url') . '/guide/' ?>">
							<i class="arrow left d-md-none"></i>
							<span itemprop="name">Guides</span>
							<span itemprop="position" content="3" />
						</a>					
				  </span>
				  <i class="arrow right d-none d-md-inline-block" style="margin-left:1em;margin-right: 1em;"></i>
				  <span class="btn-link disabled d-none d-md-inline" ><?= $title; ?></span>
				  
				</span>
			<div class="spacer-sm"></div>
		</div>
	</div>
</div>


<div id="main" class="container">

	<div class="row">
		<div class="col-lg-8">
			<div class="spacer"></div>
			<div class="row">
				<div class="col-lg-3" id="why_you_can_trust_us">
					<div class="row no-gutters">
						<div class="col-1 col-lg-4">
							<img src="<?= get_theme_file_uri('/images/logo_round.gif'); ?>" class="img-circle avatar avatar-40" alt="" width="40" height="40" style="background-color:#d9e8fd">
						</div>
						<div class="col-11 col-lg-8 py-1 px-2 py-lg-0 px-lg-0">
							<h2 class="h6">Why you can trust us</h2>
						</div>
						<div class="col-12 d-lg-none">
							<div class="spacer-xs"></div>
						</div>
					</div>
				</div>
				<div class="col text-muted" style="font-size:0.75em">
					<div class="spacer-xs d-lg-none" style="border-top: 1px solid"></div>
					We don't accept free products or compensation in exchange for our reviews. We may earn an affiliate commissions when you buy through us. <a href="/about-2/" target="_blank" class="text-muted" style="color: var(--color-header);text-decoration: underline;">Learn more about how we evaluate products</a>.
				</div>
			</div>
			<div class="spacer"></div>
			<h1 itemprop="headline"><?= $title; ?></h1>
			<div class="spacer"></div>
			<div class="row no-gutters">
				<div class="col-2 col-md-1">
					<img src="<?=$author_image ?>" class="img-circle avatar avatar-40" alt="" width="40" height="40" style="background-color:#d9e8fd">
				</div>
				<div class="col col-md-9">							
					<div class="small" style="font-style:italic">
						<span class="text-muted">Written on <?=the_modified_time('dS F')?> by</span> <b><?= $author_name ?></b> <span class="text-muted"><?= $author_introduction ?></span>
						<br>
						<a href="<?=$author_link?>"><?= count_user_posts( $post->post_author, Array('post','review','reviews_road','reviews_trail','reviews_training'), true )?> other reviews</a>
					</div>
				</div>
			</div>
			<div class="spacer"></div>
			<?php
			the_post_thumbnail();
			?>
			<div class="spacer"></div>
			<?= get_field('introduction'); ?>
			

			<!--
			<div class="spacer"></div>
			-->
		</div>
		<div class="col-lg-4">
			<!--
			<div class="d-none d-lg-block" style="position:sticky; top: 60px">
				<div align="center" data-freestar-ad="__160x600" id="runningshoesguru_sidewall_left">
				  <script data-cfasync="false" type="text/javascript">
					freestar.config.enabled_slots.push({ placementName: "runningshoesguru_sidewall_left", slotId: "runningshoesguru_sidewall_left" });
				  </script>
				</div>
			</div>			<div class="d-lg-none">				<div align="center" data-freestar-ad="__336x280 __336x280" id="runningshoesguru_incontent_1">				  <script data-cfasync="false" type="text/javascript">					freestar.config.enabled_slots.push({ placementName: "runningshoesguru_incontent_1", slotId: "runningshoesguru_incontent_1" });				  </script>				</div>			</div>
			-->
		</div>
	</div>
	<div class="row">
		<div class="col">
			<div id="top" class="spacer-xxl"></div>
			<h2><? the_field('our_top_picks_title') ?></h2>
			
			<div class="spacer"></div>
			<div class="text-muted">Keep scrolling or click on a shoe to find out why we chose it and what other shoes you should consider.</div>
			<div class="spacer"></div>

			<div class="entries row justify-content-center d-flex flex-row">
				<?php
				if( have_rows('review_sections_2023') ):
					$ii = 0;
					// loop through the rows of data
					while ( have_rows('review_sections_2023') ) : the_row();
						
						$review_id = get_sub_field('winner');
						$sub_post = get_post( $review_id );
					
						//$thumbnail = get_field( 'professional_image', $sub_post->ID );
						
						if( (function_exists( 'has_post_thumbnail' ) ) && ( has_post_thumbnail( $sub_post->ID) ) ) :
							if ($thumbnail = wp_get_attachment_image_src ( get_post_thumbnail_id ( $sub_post->ID ), array(9999,9999)) )
								$thumbnail = $thumbnail[0];
						endif;
						
						$custom = get_post_custom($sub_post->ID);
						
						$shoe_name = $custom["shoe_name"][0];
						$current_post_brand = wp_get_post_terms($sub_post->ID, 'brands');
						
						if(!$shoe_name) {
							
							$shoe_name = get_field("model", $sub_post->ID);
						}
						
						$carbon_plate_value = get_field('carbon_plate', $sub_post->ID);

						$custom["pricebox"][0] = get_field('ds_query', $sub_post->ID);

					

						if($custom["pricebox"][0]) {

							

							$preferred_merchants = "40,41,49,70";

		

							$brands_meta =  get_option( "rsg_taxonomy_".$current_post_brand[0]->term_id );

							

							if(isset($brands_meta['custom_term_preferred_merchants']) && $brands_meta['custom_term_preferred_merchants'] != '')

								$preferred_merchants = $brands_meta['custom_term_preferred_merchants'];

									

							if($female == 1) {

								$pricebox = rsg_build_datafeed_query($custom["pricebox"][0],1,true, $sub_post->ID, $preferred_merchants);

							}

							else

								$pricebox = rsg_build_datafeed_query($custom["pricebox"][0],1,false, $sub_post->ID, $preferred_merchants);

							

							//var_dump($pricebox);

							

							if (function_exists('dstudio')) {

								

								$price_json = dstudio(array('query' => $pricebox , 'pricebox' => 'newrsg-json', 'countrycode' => RSG_get_country_code() , 'override_tag' => $override_tag ),null);

								//var_dump($price_json);

								$price_json = rsg_datafeed_json($price_json);

							}

							

							if(!isset($price_json->datafeed->items[0])) {

								if($female == 1)

									$pricebox = rsg_build_datafeed_query($custom["pricebox"][0],1,true, $sub_post->ID);

								else

									$pricebox = rsg_build_datafeed_query($custom["pricebox"][0],1,false, $sub_post->ID);

								

								if (function_exists('dstudio')) {

									$price_json = dstudio(array('query' => $pricebox , 'pricebox' => 'newrsg-json', 'countrycode' => RSG_get_country_code() , 'override_tag' => $override_tag ),null);

									$price_json = rsg_datafeed_json($price_json);

								}

							}

							

							

							if(isset($price_json->datafeed->items[0])) {
								
								$item = $price_json->datafeed->items[0];

								$price = $price_json->datafeed->items[0]->CURRENCY_PREFIX.$price_json->datafeed->items[0]->DISPLAY_PRICE;

								$merchant_name = $price_json->datafeed->items[0]->MERCHANT_NAME;

								$link = $price_json->datafeed->items[0]->DEEP_LINK;
								$rrp = get_geo_rrp($sub_post->ID);


							}

							else {

								

								$merchant_name = 'Not Found '.$not_found_link;

								$link = $not_found_link;

							}

						}

						elseif(get_field( "amazon_link", $sub_post->ID )) {

								//$amazon_item = amazon_item_lookup(get_field( "amazon_link", $sub_post->ID ) , "rsg-gps-guide-20" );

								$amazon_item = amazon_item_lookup(get_field( "amazon_link", $sub_post->ID ) , "rsg-gps-20" );

								$amazon_item = $amazon_item[0];

								$link = $amazon_item->DetailPageURL;

								$price = amazon_item_getDisplayPrice($amazon_item);

								$merchant_name = 'amazon';



								if( !isset($price) ) {

									$merchant_name = 'Not Found '.$not_found_link;

									$link = $not_found_link;

								}

						}

						else {

							$merchant_name = 'Not Found '.$not_found_link;

							$link = $not_found_link;

						}
						
						$args = array(
						  'post_id' => $sub_post->ID,
						  'fields' => 'ids',
						  'meta_key' => 'rate',
						  'status' => 'approve',
						);
						$rated_comments = get_comments($args);
						$rate = 0;
						foreach($rated_comments as $rated_comment) {
							$rate = $rate + get_comment_meta( $rated_comment, 'rate', true );
						}

						$vote_comments_nr = count($rated_comments);
						//echo $vote_comments_nr;
						$vote_comments_total = $rate;

						if($vote_comments_total >= 1 && $vote_comments_nr > 0)
							$user_vote_percentage = round($vote_comments_total/$vote_comments_nr, 1);
						else
							$user_vote_percentage = 0;
						if($user_vote_percentage > 10)
							$user_vote_percentage = 10;
							

						
				?>
						<div class="col-sm-10 col-md-6 col-lg-4 d-flex mb-5" onclick="gtag('event', 'top_pick_click', {'shoe_name': '<?=urlencode($current_post_brand[0]->name .'_'.$shoe_name);?>','screen_name': '<?=$title;?>'});">
								
								
								<article class="panel d-flex flex-column w-100" style="background:#F8F8F8;flex: 1 1 auto;">
										<figure class="entry-thumbnail">
											<a href="#<?=urlencode($current_post_brand[0]->name .'_'.$shoe_name);?>" aria-label="Best Running Shoes" rel="nofollow">
																
																									
												<?php
												build_progressive_picture($thumbnail, 420, 240, 'panel-img panel-img-all')
												?>					
																				
												
											</a>
										</figure>
										
											<h4 class="entry-title" style="margin:0px; padding: 16px;padding-bottom:0; margin-bottom: auto;">
													<a href="#<?=urlencode($current_post_brand[0]->name .'_'.$shoe_name);?>" style="color:var(--font-color-header);"><?php echo $current_post_brand[0]->name .' '.$shoe_name ?></a>
												</h4>
										
										<div class="media-body panel d-flex" style="margin-top:auto;box-shadow:none;background:#F8F8F8">
											<div class="panel-body" style="padding:16px;padding-top:8px;">
												
													<div class="spacer-xs"></div>
													<h5 class="" style="color: var(--water-blue);"><?=get_sub_field('title');?></h5>
													<div class="spacer-xs"></div>
													<div class="small">
														<b>Weight:</b> <?=the_field("weight_oz", $sub_post->ID);?>oz <b>Drop:</b> <?=the_field("heel_drop_mm", $sub_post->ID);?>mm <?= ($carbon_plate_value == 'no' ? '' : 'Carbon plate' )?>
													</div>
													<div class="spacer-xs"></div>
													<?php
													$field = get_field("vote", $sub_post->ID);
													$bg_color = 'green';
													if($field < 8)
														$bg_color = 'orange';
													if($field < 6)
														$bg_color = 'red';
													?>
													
													<div class="row">
														<div class="col">
															Our Rating
														</div>
														<div class="col text-right">
															<span class="h6" style="">
																<?php
																if($field < 10)
																	echo number_format($field, 1, '.', '');
																else
																	echo $field;
																?>
															</span>
														</div>
														<div class="col-12">
															<div style="background:#E6E7E9;height: 0.8rem;border-radius: 2rem;width: 100%;">
																<div style="background:<?=$bg_color?>;height: 100%;border-radius: 2rem;width: <?=$field*10?>%;"></div>
															</div>
														</div>
													</div>
													
													<div class="spacer-xs"></div>
													
													<?php
													$field = $user_vote_percentage;
													$bg_color = 'green';
													if($field < 8)
														$bg_color = 'orange';
													if($field < 6)
														$bg_color = 'red';
													?>
													<div class="row">
														<div class="col">
															Users Rating
														</div>
														<div class="col text-right">
															<span class="h6" style="">
																<?php
																if($field < 10)
																	echo number_format($field, 1, '.', '');
																else
																	echo $field;
																?>
															</span>
														</div>
														<div class="col-12">
															<div style="background:#E6E7E9;height: 0.8rem;border-radius: 2rem;width: 100%;">
																<div style="background:<?=$bg_color?>;height: 100%;border-radius: 2rem;width: <?=$field*10?>%;"></div>
															</div>
														</div>
													</div>
													
													
													
													
													<div class="spacer-xs"></div>
													<div class="small">
														<?php
														echo rsg_truncate(get_field("verdict", $sub_post->ID), 200, '-')[0];
														?>...
													</div>
												</h4>
											</div>
										</div>
										<div class="panel d-flex" style="flex: 1 1 auto;margin-top: auto;box-shadow:none;background:#F8F8F8">
											<div class="panel-body panel-body w-100 mt-auto" style="padding:16px;padding-top:0">
												<div class="row">
													<div class="col">
														<a rel="nofollow sponsored" target="_blank" onclick="" href="<?=$link?>" class="btn btn-block btn-warning"><span class="small text-muted" style="text-decoration:line-through">MSRP <?=$item->CURRENCY_PREFIX.$rrp?></span> <?=$price?> <span class="small">Best price today</span></a>
														<i class="small d-block text-center text-muted">this will redirect you to <?= $item->MERCHANT_NAME ?></i>
													</div>
													<!--
													<div class="col">
														<a href="#<?=urlencode($current_post_brand[0]->name .'_'.$shoe_name);?>" class="btn btn-outline-primary" rel="nofollow">Read why &nbsp;&nbsp;<span class="right-arrow"></span></a>
													</div>
													-->
												</div>
											</div>
										</div>
									</article>
						</div>
				<?php 
				endwhile;endif;
				?>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-8">
						
			<h2>Table of content</h2>
			<div class="spacer"></div>
			<div class="row no-gutters" style="line-height:2em">
				<div class="col-8" style="border-bottom:1px solid #ddd">
					<a href="#top" class="post-link-wrapper" rel="nofollow">&nbsp;</a>
					<a href="#top" class="h6">Our Top <?= count(get_field('review_sections_2023'));?> picks for all categories</a>
				</div>
				<div class="col-4 text-right" style="border-bottom:1px solid #ddd">
					<a href="#top" class="post-link-wrapper" rel="nofollow">&nbsp;</a>
					<span class="" style="transform: rotate(45deg);-webkit-transform: rotate(45deg);border: solid var(--font-color-header);border-width: 0 2px 2px 0;display: inline-block;padding: 0.25em;vertical-align: middle;margin-left:1em;margin-top:-0.5em"></span>
				</div>
				<div class="col-8" style="border-bottom:1px solid #ddd">
					<a href="#what_to_consider" class="post-link-wrapper" rel="nofollow">&nbsp;</a>
					<div class="spacer-xs"></div>
					<a href="#what_to_consider" class="h6">What to consider</a></div>
				<div class="col-4 text-right" style="border-bottom:1px solid #ddd"><a href="#what_to_consider" class="post-link-wrapper" rel="nofollow">&nbsp;</a><span class="" style="transform: rotate(45deg);-webkit-transform: rotate(45deg);border: solid var(--font-color-header);border-width: 0 2px 2px 0;display: inline-block;padding: 0.25em;vertical-align: middle;margin-left:1em;margin-top:-0.5em"></span></div>
				
				<?php
				if( have_rows('review_sections_2023') ):
					$ii = 0;
					// loop through the rows of data
					while ( have_rows('review_sections_2023') ) : the_row();
						?>
						<div class="col-8" style="border-bottom:1px solid #ddd">
							<a href="#<?=urlencode(get_sub_field('title'));?>" class="post-link-wrapper" rel="nofollow">&nbsp;</a>
							<div class="spacer-xs"></div>
							<a href="#<?=urlencode(get_sub_field('title'));?>" class="h6"><?=get_sub_field('title')?></a>
						</div>
						<div class="col-4 text-right" style="border-bottom:1px solid #ddd"><a href="#<?=urlencode(get_sub_field('title'));?>" class="post-link-wrapper" rel="nofollow">&nbsp;</a><span class="" style="transform: rotate(45deg);-webkit-transform: rotate(45deg);border: solid var(--font-color-header);border-width: 0 2px 2px 0;display: inline-block;padding: 0.25em;vertical-align: middle;margin-left:1em;margin-top:-0.5em"></span></div>
						
						<?php
					endwhile;
				endif;
				?>
				<div class="col-8" style="border-bottom:1px solid #ddd">
					<a href="#faq"  class="post-link-wrapper" rel="nofollow">&nbsp;</a>
					<div class="spacer-xs"></div>
					<a href="#faq" class="h6">Quick questions you might have</a>
				</div>
				<div class="col-4 text-right" style="border-bottom:1px solid #ddd"><a href="#faq"  class="post-link-wrapper" rel="nofollow">&nbsp;</a><span class="" style="transform: rotate(45deg);-webkit-transform: rotate(45deg);border: solid var(--font-color-header);border-width: 0 2px 2px 0;display: inline-block;padding: 0.25em;vertical-align: middle;margin-left:1em;margin-top:-0.5em"></span></div>
				
				<div class="col-8" style="border-bottom:1px solid #ddd">
					<a href="#comments"  class="post-link-wrapper" rel="nofollow">&nbsp;</a>
					<div class="spacer-xs"></div>
					<a href="#comments" class="h6">Comments</a>
				</div>
				<div class="col-4 text-right" style="border-bottom:1px solid #ddd"><a href="#comments"  class="post-link-wrapper" rel="nofollow">&nbsp;</a><span class="" style="transform: rotate(45deg);-webkit-transform: rotate(45deg);border: solid var(--font-color-header);border-width: 0 2px 2px 0;display: inline-block;padding: 0.25em;vertical-align: middle;margin-left:1em;margin-top:-0.5em"></span></div>
				
			</div>
			<div  id="what_to_consider" class="spacer-xxl"></div>
			<h2><? the_field('what_to_consider_title') ?></h2>
			<div class="spacer"></div>
			<?= get_field('what_to_consider'); ?>
			
			<div class="spacer-xxl"></div>
			
			<?php
			
			$shoe_sections = array(
				 array(
					 'slug' => 'winner',
					 'title' => 'The winner',
				 ),

				 array(
					 'slug' => 'runner_up',
					 'title' => 'Runner up',
				 ),

				 array(
					 'slug' => 'also_consider',
					 'title' => 'Also consider',
				 )
			);
			if( have_rows('review_sections_2023') ):
				$ii = 0;
				// loop through the rows of data
				while ( have_rows('review_sections_2023') ) : the_row();
					$ii++;
					$content_before_reviews = get_sub_field( "introduction_text" );
			?>

		
							<div class="row">
								<div class="col">
									<a name='<?=urlencode(get_sub_field('title'));?>'></a>
									<h2 >
										<?=get_sub_field('title');?>
									</h2>
									<div class="spacer"></div>
									
									<?=get_sub_field('introduction_text');?>
									
									<?php
									foreach( $shoe_sections as $shoe_section):
										
										if(!get_sub_field($shoe_section['slug']))
											continue;
										
										$review_id = get_sub_field($shoe_section['slug']);
										
										$sub_post = get_post( $review_id );
												
										$custom = get_post_custom($sub_post->ID);
										
										if(RSG_get_country_code() == 'US' || RSG_get_country_code() == 'NULL')
											$override_tag = 'rsg-bestnew-20';
										
										if(get_field('override_amazon_tag') && get_field('override_amazon_tag') != "")
											$override_tag = get_field('override_amazon_tag');
										
										/* TO BE REMOVED 
										if($_REQUEST['test_desktop'] == 1)
												$override_tag = "guide-test-mobile-btn-shop-20";
										elseif($_REQUEST['test_desktop'] == 2)
											$override_tag = "guide-test-mobile-btn-seedeal-20";
										else
											$override_tag = "guide-test-mobile-btn-buy-20";
										TO BE REMOVED */
										
										//var_dump($custom);
										$allowed_html = array(
										  'strong' => array(),
										  'b' => array(),
										  'i' => array(),
										);
										
										$verdict = empty( get_sub_field('custom_verdict') ) ? wp_kses(preg_replace("/<br\W*?\/>/", "&nbsp;", $custom["verdict"][0]), $allowed_html) : wp_kses( get_sub_field('custom_verdict') , $allowed_html);
										$verdict = rsg_truncate($verdict, 500, '-');
										if(strlen($verdict[1]) > 1)
											$verdict = $verdict[0].'...';
										else
											$verdict = $verdict[0];
										
										$shoe_name = $custom["shoe_name"][0];
										$current_post_brand = wp_get_post_terms($sub_post->ID, 'brands');
										
										if(!$shoe_name) {
											
											$shoe_name = get_field("model", $sub_post->ID);
										}
										
										$weight = $custom["weight"][0];
										if(!$weight) {
											
											$weight = get_field("weight", $sub_post->ID);
										}
										
										if(!$weight) {
											
											$weight = get_field("weight_oz", $sub_post->ID);
										}
										
										$heeldrop = $custom["heeldrop"][0];
										if(!$heeldrop) {
											$heeldrop = get_field('heel_drop_mm', $sub_post->ID);
										}
										
										if( is_array(get_the_terms($sub_post->ID, 'shoes_type'))) {
											$shoes_types = array_values(get_the_terms($sub_post->ID, 'shoes_type'));
											$shoes_type = $shoes_types[0];
											
											if($shoes_type->name == "Road")
												$shoes_type  = $shoes_types[1];
										}
										
										$author_name = ucfirst(get_the_author_meta( 'display_name', $sub_post->post_author ));
										$author_image = rsg_image_resize( the_author_image_url($sub_post->post_author , 1 ) ,40,40,true,true,70 );
										$author_introduction = get_field('introduction', 'user_'.$sub_post->post_author);
										if(!$author_introduction)
											$author_introduction = "Running Expert";
										
										
										if(!$current_post_brand) {
											$current_post_brand[0] = get_field('brand', $sub_post->ID);
										}
										
										if(isset($custom["stars"][0]) && $custom["stars"][0] > 0)
											$stars = $custom["stars"][0];
										else
											$stars = get_field('vote', $sub_post->ID);
										
										
										if($custom["pricebox"][0] == "" && get_field('ds_query', $sub_post->ID))
											$custom["pricebox"][0] = get_field('ds_query', $sub_post->ID);
										
										$vote_comments_nr = (isset($custom["vote_comments_nr"][0])) ? $custom["vote_comments_nr"][0] : null;
										//echo $vote_comments_nr;
										$vote_comments_total = (isset($custom["vote_comments_total"][0])) ? $custom["vote_comments_total"][0] : null;

										if($vote_comments_total > 1 && $vote_comments_nr > 0)
											$user_vote_percentage = $vote_comments_total/$vote_comments_nr*20;
										else
											$user_vote_percentage = 0;
										if($user_vote_percentage > 100)
											$user_vote_percentage = 100;
										
										$recommended_for = $custom["reccomended_for"][0];
										if(!$recommended_for)
											$recommended_for = get_field( "who_is_it_for" );
										
										$recommended_for = preg_replace('#(?:<br\s*/?>\s*?){2,}#', "<br />", $recommended_for);
										
										$recommended_for = rsg_truncate($recommended_for, 300);
										if(strlen($recommended_for[1]) > 1)
											$recommended_for = $recommended_for[0].'...';
										else
											$recommended_for = $recommended_for[0];
										
										$rrp = get_geo_rrp($sub_post->ID);
										
										/*
										if(get_sub_field('female_gender') == true)
											$pricebox = rsg_build_datafeed_query($custom["pricebox"][0],1,true, $sub_post->ID, '40,41,49');
										else
											$pricebox = rsg_build_datafeed_query($custom["pricebox"][0],1,false, $sub_post->ID, '40,41,49');
										
										if (function_exists('dstudio')) {
											$price_json = dstudio(array('query' => $pricebox , 'pricebox' => 'newrsg-json', 'countrycode' => RSG_get_country_code() , 'override_tag' => $override_tag ),null);
											$price_json = rsg_datafeed_json($price_json);
										}
										
										
											
										if(!isset($price_json->datafeed->items[0])) {
											if(get_sub_field('female_gender') == true)
												$pricebox = rsg_build_datafeed_query($custom["pricebox"][0],1,true, $sub_post->ID);
											else
												$pricebox = rsg_build_datafeed_query($custom["pricebox"][0],1,false, $sub_post->ID);
											
											if (function_exists('dstudio')) {
												$price_json = dstudio(array('query' => $pricebox , 'pricebox' => 'newrsg-json', 'countrycode' => RSG_get_country_code() , 'override_tag' => $override_tag ),null);
												$price_json = rsg_datafeed_json($price_json);
											}
										}
										*/
										if($custom["pricebox"][0]) {
											$preferred_merchants = "40,41,49,70";
				
											$brands_meta =  get_option( "rsg_taxonomy_".$current_post_brand[0]->term_id );
											
											if(isset($brands_meta['custom_term_preferred_merchants']) && $brands_meta['custom_term_preferred_merchants'] != '')
												$preferred_merchants = $brands_meta['custom_term_preferred_merchants'];
											
											$pricebox_female = rsg_build_datafeed_query($custom["pricebox"][0],1,true, $ID, $preferred_merchants);
											$pricebox_male = rsg_build_datafeed_query($custom["pricebox"][0],2,false, $ID, $preferred_merchants);
											
											//var_dump($pricebox_male);
				
											
											if (function_exists('dstudio')) {
												$price_json_male = dstudio(array('query' => $pricebox_male , 'pricebox' => 'newrsg-json', 'countrycode' => RSG_get_country_code() , 'override_tag' => $override_tag ),null);
												$price_json_male = rsg_datafeed_json($price_json_male);
												
												$price_json_female = dstudio(array('query' => $pricebox_female , 'pricebox' => 'newrsg-json', 'countrycode' => RSG_get_country_code() , 'override_tag' => $override_tag ),null);
												$price_json_female = rsg_datafeed_json($price_json_female);
											}
											
											if(!isset($price_json_male->datafeed->items[0])) {
												
												$pricebox_male = rsg_build_datafeed_query($custom["pricebox"][0],2,false, $sub_post->ID);
												
												if (function_exists('dstudio')) {
													$price_json_male = dstudio(array('query' => $pricebox_male , 'pricebox' => 'newrsg-json', 'countrycode' => RSG_get_country_code() , 'override_tag' => $override_tag ),null);
													$price_json_male = rsg_datafeed_json($price_json_male);
												}
											}
											
											if(!isset($price_json_female->datafeed->items[0])) {
												
												$pricebox_female = rsg_build_datafeed_query($custom["pricebox"][0],1,true, $sub_post->ID);
												
												if (function_exists('dstudio')) {
													$price_json_female = dstudio(array('query' => $pricebox_female , 'pricebox' => 'newrsg-json', 'countrycode' => RSG_get_country_code() , 'override_tag' => $override_tag ),null);
													$price_json_female = rsg_datafeed_json($price_json_female);
												}
											}
											
											if(isset($price_json_male->datafeed->items[0])) {
												$price_male = $price_json_male->datafeed->items[0]->CURRENCY_PREFIX.$price_json_male->datafeed->items[0]->DISPLAY_PRICE;
												$merchant_name_male = $price_json_male->datafeed->items[0]->MERCHANT_NAME;
												$link_male = $price_json_male->datafeed->items[0]->DEEP_LINK;
												$currency_prefix_1 = $price_json_male->datafeed->items[0]->CURRENCY_PREFIX;
												
												$price_male1 = null;
												
												if(isset($price_json_male->datafeed->items[1])) {
													$price_male1 = $price_json_male->datafeed->items[1]->CURRENCY_PREFIX.$price_json_male->datafeed->items[1]->DISPLAY_PRICE;
													$merchant_name_male1 = $price_json_male->datafeed->items[1]->MERCHANT_NAME;
													$link_male1 = $price_json_male->datafeed->items[1]->DEEP_LINK;
													$currency_prefix_11 = $price_json_male->datafeed->items[1]->CURRENCY_PREFIX;
												}

											}
											else {
												
												$merchant_name_male = 'Not Found '.$not_found_link;
												$link_male = $not_found_link;
											}
											
											if(isset($price_json_female->datafeed->items[0])) {
												$price_female = $price_json_female->datafeed->items[0]->CURRENCY_PREFIX.$price_json_female->datafeed->items[0]->DISPLAY_PRICE;
												$merchant_name_female = $price_json_female->datafeed->items[0]->MERCHANT_NAME;
												$link_female = $price_json_female->datafeed->items[0]->DEEP_LINK;
												$currency_prefix_1 = $price_json_female->datafeed->items[0]->CURRENCY_PREFIX;

											}
											else {
												
												$merchant_name_female = 'Not Found '.$not_found_link;
												$link_female = $not_found_link;
											}
										}
										elseif(get_field( "amazon_link", $sub_post->ID )) {
											//$amazon_item = amazon_item_lookup(get_field( "amazon_link", $sub_post->ID ) , "rsg-gps-guide-20" );
											$amazon_item = amazon_item_lookup(get_field( "amazon_link", $sub_post->ID ) , "rsg-gps-20" );
											$amazon_item = $amazon_item[0];
											$link_male = $amazon_item->DetailPageURL;
											$price_male = amazon_item_getDisplayPrice($amazon_item);
											$merchant_name_male = 'amazon';
											$ListPrice = $amazon_item->Offers->Listings[0]->SavingBasis->Amount;	
											
											if( !isset($price_male) ) {
												$merchant_name_male = 'Not Found '.$not_found_link;
												$link = $not_found_link;
											}
											$rrp = $ListPrice;

											$battery_life_on_gps = get_field('battery_life_on_gps', $sub_post->ID );
										}
										
										
										$permalink = get_permalink($review_id);
										
										if(empty($override_image)) {
											

											
											if( (function_exists( 'has_post_thumbnail' ) ) && ( has_post_thumbnail( $sub_post->ID) ) ) :
												if ($thumbnail = wp_get_attachment_image_src ( get_post_thumbnail_id ( $sub_post->ID ), array(9999,9999)) )
													$thumbnail = $thumbnail[0];
											endif;
										
										} else {
											$thumbnail = $override_image;
										}
										
										?>
										<a name='<?=urlencode($current_post_brand[0]->name .'_'.$shoe_name);?>'></a>
										<!--
										<h4 class="text-warning"><?=$shoe_section['title']?></h4>
										-->
										<div class="spacer-xs"></div>
										<div class="bg-default panel-body p-4 p-lg-5">
											<div class="row bg-default">
												<div class="col-lg-6">
													<a href="<?=$permalink?>">
														<?php
														if(!empty($thumbnail))
															build_progressive_picture($thumbnail, 370, 250, 'panel-img panel-img-all');
														?>
													</a>
												</div>
												<div class="col">
													<div class="spacer d-lg-none"></div>
													<h3><a href="<?=$permalink?>" style="color:var(--font-color-header);"><?php echo $current_post_brand[0]->name .' '.$shoe_name ?></a></h3>
													<div class="spacer"></div>
													
													<div class="spacer-xs d-none d-lg-block"></div>
													
													
													<div class="row justify-content-lg-center">
						<div class="col-12">
							<?php
									$i = 0;
									if(isset($price_json_male->datafeed->items[0])) {
										foreach ($price_json_male->datafeed->items as $item) {
											if($i<2) {
												echo '<div class="row align-items-center">';
												echo '<div class="col">';
												echo '<a class="" style="display: block;max-width: 8.5em;overflow: hidden; max-height: 2em;" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$price_event.'\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'"><img src="'.rsg_image_resize_ext($item->MERCHANT_LOGO,88,31,'/dfs-images-cache/').'" alt="merchant logo" style="display: inline-block;"></a>';
												echo '</div>';
												echo '<div class="col">';
												if(isset($rrp))
													echo '<a class="text-success" style="text-decoration: line-through;" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$price_event.'\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'">'.$item->CURRENCY_PREFIX.round((float)$rrp).'.00</a><br>';
												echo '<a class="text-success" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$price_event.'\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'"><b>'.$item->CURRENCY_PREFIX.$item->DISPLAY_PRICE.'.00</b></a>';
												echo '</div>';
												echo '<div class="col">';
												
												echo '<a class="btn btn-sm btn-warning" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$price_event.'\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'">See deal &nbsp;&nbsp;></a>';
												echo '</div>';
												if($i<1) {
													echo '<div class="col-12"><hr></div>';
												}
												echo '</div>';
												$i++;
											}
										}
									}
									else {
										echo '<div class="row"><div class="col" style="padding:0 10%;"><i>Sorry - Product not found. Please try <b>';
											if( RSG_get_country_code() == 'GB' )
												echo '<a rel="nofollow sponsored" href="'.get_field('geo_uk_link', 'option').'" target="_blank" onClick="ga(\'send\', \'event\', \'Aff_Link\', \'InReview-PriceComp-Sidebar-NoProduct\',\'Amazon UK\');">Amazon UK</a>';
													else
												echo '<a rel="nofollow sponsored" href="'.$GLOBALS['rsg-no-products-us-men'].'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \'InReview-PriceComp-Sidebar-NoProduct\', \''.$GLOBALS['rsg-no-products-us-label'].'\');">'.$GLOBALS['rsg-no-products-us-label'].'</a>';
										echo '</b> to check in their assortment.';
										echo '</i></div></div>';
									}
									
									
									?>
							
						</div>
					</div>
					<!--
													<a href="<?=$link_male?>" rel="nofollow sponsored" target="_blank" class="btn btn-outline-warning btn-block"><?=$price_male?> at <?=$merchant_name_male?> <span class="right-arrow"></span></a>
													<br>
													<?php
													if ( isset($price_male1) ):
													?>
														<a href="<?=$link_male1?>" rel="nofollow sponsored" target="_blank" class="btn btn-outline-warning btn-block"><?=$price_male1?> at <?=$merchant_name_male1?> <span class="right-arrow"></span></a>
													<?php
													endif;
													?>
													-->
												</div>
											</div>
											<div class="spacer"></div>
											<h4 class="h6">In short</h4>
											<div class="spacer"></div>
											<?= $verdict ?>
											<div class="spacer"></div>
											<div class="panel-body p-4 p-lg-5" style="background-color: #F2F8FF;">
												<div class="row">
													<div class="col-lg-6">
														<h4 class="h6">Pros</h4>
														<hr style="margin: 1rem 0;">
														
														<ul class="list-unstyled  procons">
															<?php
															if( have_rows('pros', $sub_post->ID) ):

																// loop through the rows of data
																while ( have_rows('pros', $sub_post->ID) ) : the_row();

																	// display a sub field value
																	echo '<li class="pro">'.get_sub_field('pro_line', $sub_post->ID).'</li>';

																endwhile;
															else:
																$pros = preg_split('#(<br/>|<br>|<br />)#', $custom["pros"][0] );
																$pros = array_slice( $pros , 0 , 4 ); 
																foreach($pros as $pro)
																	echo '<li class="pro">'.$pro.'</li>';
															endif;
															?>
														</ul>																												<div class="spacer d-lg-none"></div>
													</div>
													<div class="col-lg-6">
														<h4 class="h6">Cons</h4>
														<hr style="margin: 1rem 0;">
															<ul class="list-unstyled  procons">
															<?php
															if( have_rows('cons', $sub_post->ID) ):

																// loop through the rows of data
																while ( have_rows('cons', $sub_post->ID) ) : the_row();

																	// display a sub field value
																	echo '<li class="con">'.get_sub_field('cons_line', $sub_post->ID).'</li>';

																endwhile;
															else:
																$cons = preg_split('#(<br/>|<br>|<br />)#', $custom["cons"][0] );
																$cons = array_slice( $cons , 0 , 4 ); 
																foreach($cons as $con)
																	echo '<li class="con">'.$con.'</li>';
															endif;
															?>
														</ul>
													</div>
												</div>
												<div class="spacer"></div>
												<div class="row">
													<div class="col-lg-6">
														<h4 class="h6">Who should buy it</h4>
														<hr style="margin: 1rem 0;">
														<?php
														the_field('who_should_buy_it', $sub_post->ID);
														?>																												<div class="spacer d-lg-none"></div>
														
													</div>
													<div class="col-lg-6">
														<h4 class="h6">Who should not buy it</h4>
														<hr style="margin: 1rem 0;">
														<?php
														the_field('who_should_not_buy_it_', $sub_post->ID);
														?>
													</div>
												</div>
											</div>
											<div class="spacer"></div>
											<h4 class="h6">Main Specs</h4>
											<div class="spacer"></div>
											<?php
											
											$carbon_plate = get_field_object('carbon_plate', $sub_post->ID);
											$carbon_plate_value = get_field('carbon_plate', $sub_post->ID);
											
											$main_specs = array(
												array(
													 'slug' => 'type',
													 'title' => 'Type',
													 'value' => $shoes_type->name,
												 ),
												  array(
													 'slug' => 'weight_oz',
													 'title' => 'Weight',
													 'value' => $weight.' oz',
												 ),
												 array(
													 'slug' => 'heel_mm',
													 'title' => 'Heel',
													 'value' => get_field('heel_mm', $sub_post->ID).' mm',
												 ),

												
												
											);
											
											$main_specs2 = array(
												array(
													 'slug' => 'carbon_plate',
													 'title' => 'Carbon plate',
													 'value' => ucfirst($carbon_plate['choices'][ $carbon_plate_value ]),
												 ),
												  array(
													 'slug' => 'heel_drop_mm',
													 'title' => 'Heel drop',
													 'value' => $heeldrop.' mm',
												 ),
												  array(
													 'slug' => 'toe_mm',
													 'title' => 'Toe',
													 'value' => get_field('toe_mm', $sub_post->ID).' mm',
												 ),

												

												 

												 
											);
											?>
			<div class="row">				
				<div class="col-12 col-md-6">
					<?php
					foreach($main_specs as $main_spec):
						
						?>
						<div class="row" style="line-height:2em;">
							<div class="col-5"  style="background:#fff;border-right:1px solid #212529">
								<?= $main_spec['title']; ?>
							</div>
							<div class="col" style="background:#fff;color:var(--gray)">
								
									<?php
									//var_dump($field);
									
										echo $main_spec['value'];
									?>
								
							</div>
						</div>
						<div class="spacer-xs"></div>
						<?php
					endforeach;
					?>
				</div>
				<div class="col-12 col-md-6">
					<?php
					foreach($main_specs2 as $main_spec):
						
						?>
						<div class="row" style="line-height:2em;">
							<div class="col-5" style="background:#fff;border-right:1px solid #212529">
								<?= $main_spec['title']; ?>
							</div>
							<div class="col"  style="background:#fff;color:var(--gray)">
								
									<?php
									//var_dump($field);
									
										echo $main_spec['value'];
									?>
								
							</div>
						</div>
						<div class="spacer-xs"></div>
						<?php
					endforeach;
					?>
				</div>
				
			</div>											
														
											<div class="spacer"></div>
														<h4 class="h6">Rating breakdown</h4> 
			<div class="spacer"></div>
			<?php
			$rating_metrics_col1 = Array('comfort', 'build_quality', 'upper_rate', 'sole_unit_rate', 'landing');
			$rating_metrics_col2 = Array('transition', 'toe_off', 'traction', 'durability', 'value_price');
			?>
			<div class="row">				
				<div class="col-12 col-md-6">
					<?php
					foreach($rating_metrics_col1 as $rating_metric):
						$field = get_field_object($rating_metric, $sub_post->ID);
						?>
						<div class="row">
							<div class="col"  style="background:#fff">
								<?= $field['label']; ?>
							</div>
							<div class="col text-right" style="background:#fff">
								<span class="score-ticket-standalaone" style="display:inline-block;color:var(--water-blue);padding: 0.25rem 0.5rem;background-color: #2189E11A;font-weight: bold;">
									<?php
									if($field['value'] < 10)
										echo number_format($field['value'], 1, '.', '');
									else
										echo $field['value'];
									?>
								</span>
							</div>
						</div>
						<div class="spacer-xs"></div>
						<?php
					endforeach;
					?>
				</div>
				<div class="col-12 col-md-6">
					<?php
					foreach($rating_metrics_col2 as $rating_metric):
						$field = get_field_object($rating_metric, $sub_post->ID);
						?>
						<div class="row">
							<div class="col" style="background:#fff">
								<?= $field['label']; ?>
							</div>
							<div class="col text-right"  style="background:#fff">
								<span class="score-ticket-standalaone" style="display:inline-block;color:var(--water-blue);padding: 0.25rem 0.5rem;background-color: #2189E11A;font-weight: bold;">
									<?php
									if($field['value'] < 10)
										echo number_format($field['value'], 1, '.', '');
									else
										echo $field['value'];
									?>
								</span>
							</div>
						</div>
						<div class="spacer-xs"></div>
						<?php
					endforeach;
					?>
				</div>
			</div>
			<div class="spacer"></div>
											<h4 class="h6">Team review</h4>
											<div class="spacer"></div>
											<?= get_sub_field($shoe_section['slug'].'_custom_review') ?>
											<div class="spacer"></div>
											<a href="<?=$permalink?>" class="btn btn-outline-primary">Read full review</a>
										</div>
										<div class="spacer-xl"></div>
										<?php
										endforeach;
										?>
												
									</div>
								</div>
								<div class="spacer-xxl"></div>
								
			<?php
				endwhile;
			endif;
			?>
			
			<h2>Quick Questions you might have</h2>
			<div class="spacer"></div>
			<div id="faq"  >
				<?php
				if( have_rows('faq') ):
				
					// loop through the rows of data
					while ( have_rows('faq') ) : the_row();
						?>
						<div>
							<b itemprop="name"><?=get_sub_field('question')?></b>
							<div class="spacer"></div>
							<p>
								<span itemprop="text"><?=get_sub_field('answer')?></span>
							</p>
						</div>
						<?php
					endwhile;
				endif;
				?>
			</div>
			<div class="spacer-xxl"></div>
			<aside class="widget" style="margin-bottom:0px">
				<?php comments_template('/comments-2022.php', true); ?>
			</aside>
		</div>
		<div class="col-4">
			<!--
			<div style="position:sticky; top: 60px">
				<div align="center" data-freestar-ad="__160x600" id="runningshoesguru_sidewall_right">
				  <script data-cfasync="false" type="text/javascript">
					freestar.config.enabled_slots.push({ placementName: "runningshoesguru_sidewall_right", slotId: "runningshoesguru_sidewall_right" });
				  </script>
				</div>
			</div>
			-->
		</div>
	</div>
</div>

<?php endwhile; endif;  ?>
<?php
else:
?>
<?php if( have_posts() ) : while( have_posts() ) : the_post(); ?>
<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="display: none;">
<defs>
<g id="icon-best_choice">
    <path id="path___0" data-name="path__0" d="M71.68,26.07H68.85V29.2h2.83a2.5,2.5,0,0,0,1.61-.44,1.39,1.39,0,0,0,.54-1.12,1.35,1.35,0,0,0-.54-1.17A2.74,2.74,0,0,0,71.68,26.07Zm87.79-5.27a4.09,4.09,0,0,0-4,4.1,4.05,4.05,0,0,0,1.22,3,3.83,3.83,0,0,0,2.78,1.22,3.63,3.63,0,0,0,2.78-1.22,4.24,4.24,0,0,0,1.12-3A4.07,4.07,0,0,0,162.26,22,3.7,3.7,0,0,0,159.47,20.8Zm-86,1.27A1.3,1.3,0,0,0,72.95,21a2,2,0,0,0-1.27-.39H68.85v2.93h2.83a2,2,0,0,0,1.27-.39A1.3,1.3,0,0,0,73.44,22.07Zm127.05,9.86H189.26V18h11V20.7h-7.42v2.83h6.64v2.73h-6.64V29.1h7.62v2.83Zm-18.65.2a7.21,7.21,0,0,1-5.32-2.1,7,7,0,0,1-2.1-5.13,6.74,6.74,0,0,1,2.15-5.08A7.77,7.77,0,0,1,185,18.46a8.65,8.65,0,0,1,2.59,1.76l-2.05,2.44a4.8,4.8,0,0,0-1.66-1.37A4.16,4.16,0,0,0,182,20.8,3.7,3.7,0,0,0,179.25,22a3.94,3.94,0,0,0-1.12,2.83,4.24,4.24,0,0,0,1.12,3A3.63,3.63,0,0,0,182,29a3.87,3.87,0,0,0,1.81-.49,8.83,8.83,0,0,0,1.71-1.17l2.05,2.15A8.31,8.31,0,0,1,185,31.4a7.31,7.31,0,0,1-3.12.73h0Zm-9.28-.2h-3.61V18h3.61v14Zm-13.09.2A7.5,7.5,0,0,1,154,30a6.9,6.9,0,0,1-2.15-5.13A6.74,6.74,0,0,1,154,19.82a8.25,8.25,0,0,1,10.89,0,6.67,6.67,0,0,1,2.2,5.08,6.82,6.82,0,0,1-2.2,5.13A7.54,7.54,0,0,1,159.47,32.13Zm-9.47-.2h-3.61V26.46h-5.86v5.47H137V18h3.52v5.76h5.86V18H150v14Zm-4.79,28.42-5.08,4.88a1.3,1.3,0,0,0-.29.44,0.52,0.52,0,0,0,0,.44l1.17,7a0.88,0.88,0,0,1-.39.93,1.15,1.15,0,0,1-1.07.15L133.3,70.9a1,1,0,0,0-.88,0l-6.35,3.32a1.15,1.15,0,0,1-1.07-.15,0.88,0.88,0,0,1-.39-0.93l1.27-7a2.26,2.26,0,0,0,0-.44,0.85,0.85,0,0,0-.24-0.44l-5.08-4.88a1,1,0,0,1-.29-1.07,0.77,0.77,0,0,1,.78-0.59l7-1.07a0.79,0.79,0,0,0,.49-0.15,1,1,0,0,0,.29-0.34l3.13-6.45a1,1,0,0,1,.93-0.49,0.8,0.8,0,0,1,.83.49l3.22,6.45a1,1,0,0,0,.29.34,0.65,0.65,0,0,0,.39.15l7,1.07a0.75,0.75,0,0,1,.83.59,1.18,1.18,0,0,1-.24,1.07h0ZM116.89,20.8h-4.3V31.93h-3.52V20.8h-4.2V17.87h12V20.8ZM98.54,32a10.62,10.62,0,0,1-3.47-.59,9.15,9.15,0,0,1-3-1.66l1.37-2.73A10,10,0,0,0,96,28.61a6.83,6.83,0,0,0,2.59.59,2.17,2.17,0,0,0,1.27-.34,1.08,1.08,0,0,0,.49-0.93q0-1.86-3.71-2.05T93,22a3.59,3.59,0,0,1,1.56-3.08,6.87,6.87,0,0,1,4.1-1.12,10.76,10.76,0,0,1,5.47,1.56l-1.37,2.73a15.42,15.42,0,0,0-2.34-1,6.79,6.79,0,0,0-2.15-.44,1.75,1.75,0,0,0-1.07.29,0.93,0.93,0,0,0-.39.78q0,1.76,3.71,2.05t3.71,4A3.58,3.58,0,0,1,102.54,31a7.21,7.21,0,0,1-4,1.07h0Zm-7.62-.1H79.69V18H90.63V20.7H83.2v2.83h6.74v2.73H83.2V29.1h7.71v2.83Zm-18.75,0H65.23V18h6.64a6.72,6.72,0,0,1,3.81.93,2.87,2.87,0,0,1,1.37,2.49,3.2,3.2,0,0,1-.63,2,3.11,3.11,0,0,1-1.71,1.12,3.68,3.68,0,0,1,2.05,1.22A3.44,3.44,0,0,1,77.54,28a3.35,3.35,0,0,1-1.46,2.83,6.46,6.46,0,0,1-3.91,1.07h0Zm57.52-14.16a7.41,7.41,0,0,1,3.08.68,8.65,8.65,0,0,1,2.59,1.76l-2.05,2.44a6.08,6.08,0,0,0-1.71-1.37,4,4,0,0,0-1.9-.49A3.7,3.7,0,0,0,126.9,22a3.94,3.94,0,0,0-1.12,2.83,4.24,4.24,0,0,0,1.12,3A3.63,3.63,0,0,0,129.69,29a4,4,0,0,0,1.9-.49,8.83,8.83,0,0,0,1.71-1.17l2.05,2.15a9.58,9.58,0,0,1-2.73,1.9,7.11,7.11,0,0,1-3,.73,7.21,7.21,0,0,1-5.32-2.1,7,7,0,0,1-2.1-5.13,6.74,6.74,0,0,1,2.15-5.08,7.47,7.47,0,0,1,5.37-2.05h0ZM260.06,1H5.66Q2.93,1,1.22,4.15a10.43,10.43,0,0,0-1,7L13.09,63.28A8.83,8.83,0,0,0,14.45,67a5.48,5.48,0,0,0,2.64,2.2L124,97.85A33.25,33.25,0,0,0,132.81,99a34.35,34.35,0,0,0,8.89-1.17L248.54,69.14A5.56,5.56,0,0,0,251.17,67a9.51,9.51,0,0,0,1.46-3.81l12.89-52.05a10.43,10.43,0,0,0-1-7Q262.79,1,260.06,1h0Z" transform="translate(0 -0.98)"/>
</g>
<g id="icon-premium_pick">
    <path d="M76.171875 19.824218999999999 C75.48828125 19.824218999999971 74.12109375 19.824218999999971 73.4375 19.824218999999971 C73.4375 20.947265749999985 73.4375 23.193359250000015 73.4375 24.316406000000029 C74.12109375 24.316406000000029 75.48828125 24.316406000000029 76.171875 24.316406000000029 Q77.34375 24.316406000000029 77.978515499999958 23.730468500000029 Q78.613281000000029 23.144531000000029 78.613281000000029 21.972656000000029 Q78.613281000000029 20.898437999999999 77.978515499999958 20.361328500000013 Q77.34375 19.824218999999971 76.171875 19.824218999999971 zM62.109375 19.824218999999999 C61.5234375 19.824218999999971 60.3515625 19.824218999999971 59.765625 19.824218999999971 C59.765625 20.947265749999985 59.765625 23.193359250000015 59.765625 24.316406000000029 C60.3515625 24.316406000000029 61.5234375 24.316406000000029 62.109375 24.316406000000029 Q63.28125 24.316406000000029 63.964843999999971 23.730468500000029 Q64.648437999999942 23.144531000000029 64.648437999999942 21.972656000000029 Q64.648437999999942 20.898437999999999 63.964843999999971 20.361328500000013 Q63.28125 19.824218999999971 62.109375 19.824218999999971 C62.109375 19.824218999999971 62.109375 19.824218999999971 62.109375 19.824218999999971 zM166.699218999999999 19.824218999999999 C166.113281499999971 19.824218999999971 164.941406499999971 19.824218999999971 164.355468999999971 19.824218999999971 C164.355468999999971 20.947265749999985 164.355468999999971 23.193359250000015 164.355468999999971 24.316406000000029 C164.941406499999971 24.316406000000029 166.113281499999971 24.316406000000029 166.699218999999971 24.316406000000029 Q167.871093999999971 24.316406000000029 168.505859500000042 23.730468500000029 Q169.140625 23.144531000000029 169.140625 21.972656000000029 Q169.140625 20.898437999999999 168.505859500000042 20.361328500000013 Q167.871093999999971 19.824218999999971 166.699218999999971 19.824218999999971 zM203.808593999999999 31.152343999999999 C202.880859500000042 29.760742499999992 201.025390499999958 26.977539500000034 200.097656000000029 25.585937999999999 C199.633788999999979 26.098633250000034 198.706054999999992 27.124023749999992 198.242187999999942 27.636718999999971 C198.242187999999942 28.515625249999971 198.242187999999942 30.273437749999971 198.242187999999942 31.152343999999971 C197.363281499999971 31.152343999999971 195.605468500000029 31.152343999999971 194.726562000000058 31.152343999999971 C194.726562000000058 27.612304999999992 194.726562000000058 20.532227000000034 194.726562000000058 16.992187999999999 C195.605468500000029 16.992187999999999 197.363281499999971 16.992187999999999 198.242187999999942 16.992187999999999 C198.242187999999942 18.505859750000013 198.242187999999942 21.533203249999985 198.242187999999942 23.046875 C199.584961249999992 21.533203249999985 202.270507749999979 18.505859750000013 203.613281000000029 16.992187999999999 C204.663085749999937 16.992187999999999 206.762695249999979 16.992187999999999 207.8125 16.992187999999999 C206.494140499999958 18.457031499999971 203.857421499999987 21.386718500000029 202.539062000000058 22.851562000000001 C203.930663750000008 24.926757500000008 206.713867250000021 29.077148500000021 208.105468999999971 31.152343999999971 C207.031250249999971 31.152343999999971 204.882812749999971 31.152343999999971 203.808593999999971 31.152343999999971 zM187.304687999999999 31.25 Q184.082031000000029 31.25 181.982421499999987 29.199218999999971 Q179.882812000000058 27.148437999999999 179.882812000000058 24.023437999999999 Q179.882812000000058 20.898437999999999 182.03125 18.847656499999971 Q184.179687999999942 16.796875 187.402343999999971 16.796875 Q188.964843999999971 16.796875 190.478516000000013 17.480468500000029 Q191.992187999999942 18.164062000000001 193.066406000000029 19.238281000000029 C192.382812333333277 20.052083333333314 191.528320249999979 21.069336249999992 191.015625 21.679687999999999 Q190.332031000000029 20.800781000000029 189.355468500000029 20.3125 Q188.378906000000029 19.824218999999971 187.402343999999971 19.824218999999971 Q185.742187999999942 19.824218999999971 184.619141000000013 20.996093999999971 Q183.496093999999971 22.167968999999971 183.496093999999971 23.925781000000029 Q183.496093999999971 25.78125 184.619141000000013 26.953125 Q185.742187999999942 28.125 187.402343999999971 28.125 Q188.378906000000029 28.125 189.306640499999958 27.685546999999985 Q190.234375 27.246093999999971 191.015625 26.464843999999971 C191.699218666666638 27.213542000000018 192.553710749999937 28.149414500000034 193.066406000000029 28.710937999999999 Q191.894531000000029 29.882812000000001 190.380859500000042 30.566406000000029 Q188.867187999999942 31.25 187.304687999999942 31.25 C187.304687999999942 31.25 187.304687999999942 31.25 187.304687999999942 31.25 zM178.027343999999999 31.152343999999999 C177.124023500000021 31.152343999999971 175.317382500000008 31.152343999999971 174.414062000000058 31.152343999999971 C174.414062000000058 27.612304999999992 174.414062000000058 20.532227000000034 174.414062000000058 16.992187999999999 C175.317382500000008 16.992187999999999 177.124023500000021 16.992187999999999 178.027343999999971 16.992187999999999 C178.027343999999971 20.532227000000034 178.027343999999971 27.612304999999992 178.027343999999971 31.152343999999971 zM166.894531000000001 27.148437999999999 C166.259765499999958 27.148437999999999 164.990234500000042 27.148437999999999 164.355468999999971 27.148437999999999 C164.355468999999971 28.149414500000034 164.355468999999971 30.151367499999992 164.355468999999971 31.152343999999971 C163.452148749999992 31.152343999999971 161.645508250000034 31.152343999999971 160.742187999999942 31.152343999999971 C160.742187999999942 27.612304999999992 160.742187999999942 20.532227000000034 160.742187999999942 16.992187999999999 C162.280273749999992 16.992187999999999 165.356445249999979 16.992187999999999 166.894531000000029 16.992187999999999 Q169.628906000000029 16.992187999999999 171.09375 18.261718999999971 Q172.558593999999971 19.53125 172.558593999999971 21.875 Q172.558593999999971 24.414062000000001 171.09375 25.78125 Q169.628906000000029 27.148437999999999 166.894531000000029 27.148437999999999 C166.894531000000029 27.148437999999999 166.894531000000029 27.148437999999999 166.894531000000029 27.148437999999999 zM153.222656000000001 31.152343999999999 C152.441406000000029 31.152343999999971 150.878906000000029 31.152343999999971 150.097656000000029 31.152343999999971 C150.097656000000029 28.833008000000007 150.097656000000029 24.194336000000021 150.097656000000029 21.875 C149.243163999999979 23.828125 147.534179999999992 27.734375 146.679687999999942 29.6875 C146.118164500000034 29.6875 144.995117499999992 29.6875 144.433593999999971 29.6875 C143.579101750000063 27.734375 141.870117250000021 23.828125 141.015625 21.875 C141.015625 24.194336000000021 141.015625 28.833008000000007 141.015625 31.152343999999971 C140.209961000000021 31.152343999999971 138.598633000000063 31.152343999999971 137.792968999999971 31.152343999999971 C137.792968999999971 27.587890749999985 137.792968999999971 20.458984250000015 137.792968999999971 16.894531000000029 C138.769531499999971 16.894531000000029 140.722656499999971 16.894531000000029 141.699218999999971 16.894531000000029 C142.675781499999971 19.018554499999993 144.628906499999971 23.266601499999979 145.605468999999971 25.390625 C146.557617250000021 23.266601499999979 148.461913750000008 19.018554499999993 149.414062000000058 16.894531000000029 C150.366210499999966 16.894531000000029 152.270507500000008 16.894531000000029 153.222656000000029 16.894531000000029 C153.222656000000029 20.458984250000015 153.222656000000029 27.587890749999985 153.222656000000029 31.152343999999971 zM148.144531000000001 53.320312000000001 Q148.144531000000029 53.320312000000001 148.144531000000029 53.369140500000015 Q148.144531000000029 53.417968999999971 148.144531000000029 53.417968999999971 Q147.851562000000058 53.808593999999971 146.972656000000029 55.615234499999985 Q146.09375 57.421875 145.507812000000058 60.253906000000029 Q145.214843999999971 61.523437999999999 145.019531499999971 62.988281499999971 Q144.824218999999971 64.453125 144.824218999999971 66.015625 Q144.824218999999971 66.113281000000029 144.824218999999971 66.2109375 Q144.824218999999971 66.308593999999971 144.824218999999971 66.40625 C144.824218999999971 66.40625 144.824218999999971 66.40625 144.824218999999971 66.40625 C144.824218999999971 67.358398500000021 144.824218999999971 69.262695500000007 144.824218999999971 70.214843999999971 C138.964843999999971 70.214843999999971 127.246093999999971 70.214843999999971 121.386718999999971 70.214843999999971 C121.386718999999971 69.262695500000007 121.386718999999971 67.358398500000021 121.386718999999971 66.40625 C121.362304750000021 66.40625 121.313476250000008 66.40625 121.289062000000058 66.40625 Q121.386718999999971 64.648437999999999 121.191406499999971 63.134766000000013 Q120.996093999999971 61.621093999999971 120.703125 60.253906000000029 Q120.019531000000029 57.226562000000001 119.091796499999987 55.371093500000029 Q118.164062000000058 53.515625 117.96875 53.320312000000001 Q117.96875 53.320312000000001 117.96875 53.320312000000001 Q117.96875 53.320312000000001 117.96875 53.320312000000001 C117.96875 53.320312000000001 117.96875 53.320312000000001 117.96875 53.320312000000001 Q117.96875 53.320312000000001 118.115234500000042 53.320312000000001 Q118.261718999999971 53.320312000000001 118.554687999999942 53.417968999999971 Q119.433593999999971 53.613281000000029 121.386718999999971 54.4921875 Q123.339843999999971 55.371093999999971 124.707031000000029 57.8125 Q124.316406000000029 58.105468999999971 124.072265499999958 58.59375 Q123.828125 59.082031000000029 123.828125 59.570312000000001 Q123.828125 59.765625 123.828125 59.863281499999971 Q123.828125 59.960937999999999 123.828125 60.15625 Q123.828125 61.132812000000001 124.511718500000029 61.914062000000001 Q125.195312000000058 62.695312000000001 126.171875 62.695312000000001 Q126.171875 62.695312000000001 126.220702999999958 62.646483999999987 Q126.269531000000029 62.597656000000029 126.367187999999942 62.597656000000029 C126.367187999999942 62.597656000000029 126.367187999999942 62.597656000000029 126.367187999999942 62.597656000000029 Q127.246093999999971 62.5 127.832031499999971 61.816406000000029 Q128.417968999999971 61.132812000000001 128.417968999999971 60.15625 Q128.515625 59.960937999999999 128.515625 59.863281499999971 Q128.515625 59.765625 128.417968999999971 59.570312000000001 Q128.417968999999971 58.984375 128.076172000000042 58.447265500000015 Q127.734375 57.910156000000029 127.34375 57.617187999999999 Q128.027343999999971 56.542968999999971 129.1015625 55.175781499999971 Q130.175781000000029 53.808593999999971 131.738281000000029 52.636718999999971 Q132.03125 52.34375 132.373047000000042 52.099609499999985 Q132.714843999999971 51.855468999999971 133.105468999999971 51.5625 Q133.203125 51.660156000000029 133.251952999999958 51.708983999999987 Q133.300781000000029 51.757812000000001 133.300781000000029 51.757812000000001 Q133.59375 51.953125 133.886718999999971 52.1484375 Q134.179687999999942 52.34375 134.472656000000029 52.636718999999971 Q136.035156000000029 53.808593999999971 137.109375 55.175781499999971 Q138.183593999999971 56.542968999999971 138.867187999999942 57.617187999999999 Q138.378906000000029 57.910156000000029 138.0859375 58.447265500000015 Q137.792968999999971 58.984375 137.792968999999971 59.570312000000001 Q137.695312000000058 59.765625 137.695312000000058 59.863281499999971 Q137.695312000000058 59.960937999999999 137.695312000000058 60.15625 Q137.792968999999971 61.132812000000001 138.427734500000042 61.914062000000001 Q139.0625 62.695312000000001 140.039062000000058 62.695312000000001 Q140.136718999999971 62.695312000000001 140.185547000000042 62.646483999999987 Q140.234375 62.597656000000029 140.332031000000029 62.597656000000029 C140.332031000000029 62.597656000000029 140.332031000000029 62.597656000000029 140.332031000000029 62.597656000000029 Q141.210937999999942 62.5 141.796875 61.816406000000029 Q142.382812000000058 61.132812000000001 142.382812000000058 60.15625 Q142.382812000000058 59.960937999999999 142.382812000000058 59.863281499999971 Q142.382812000000058 59.765625 142.382812000000058 59.570312000000001 Q142.285156000000029 59.082031000000029 142.089843500000029 58.59375 Q141.894531000000029 58.105468999999971 141.503906000000029 57.8125 Q142.871093999999971 54.980468999999971 145.5078125 54.150390500000015 Q148.144531000000029 53.320312000000001 148.144531000000029 53.320312000000001 C148.177083333333371 53.320312000000001 148.217773749999992 53.320312000000001 148.242187999999942 53.320312000000001 Q148.242187999999942 53.320312000000001 148.242187999999942 53.320312000000001 Q148.242187999999942 53.320312000000001 148.144531000000029 53.320312000000001 C148.144531000000029 53.320312000000001 148.144531000000029 53.320312000000001 148.144531000000029 53.320312000000001 zM116.210937999999999 16.992187999999999 C117.114258250000034 16.992187999999999 118.920898749999992 16.992187999999999 119.824218999999971 16.992187999999999 C119.824218999999971 20.532227000000034 119.824218999999971 27.612304999999992 119.824218999999971 31.152343999999971 C118.920898749999992 31.152343999999971 117.114258250000034 31.152343999999971 116.210937999999942 31.152343999999971 C116.210937999999942 27.612304999999992 116.210937999999942 20.532227000000034 116.210937999999942 16.992187999999999 zM113.378906000000001 31.152343999999999 C112.597656000000029 31.152343999999971 111.035156000000029 31.152343999999971 110.253906000000029 31.152343999999971 C110.253906000000029 28.833008000000007 110.253906000000029 24.194336000000021 110.253906000000029 21.875 C109.399413999999979 23.828125 107.690429999999992 27.734375 106.835937999999942 29.6875 C106.250000499999942 29.6875 105.078125499999942 29.6875 104.492187999999942 29.6875 C103.662109750000013 27.734375 102.001953250000042 23.828125 101.171875 21.875 C101.171875 24.194336000000021 101.171875 28.833008000000007 101.171875 31.152343999999971 C100.366211000000021 31.152343999999971 98.754883000000063 31.152343999999971 97.949218999999971 31.152343999999971 C97.949218999999971 27.587890749999985 97.949218999999971 20.458984250000015 97.949218999999971 16.894531000000029 C98.925781499999971 16.894531000000029 100.878906499999971 16.894531000000029 101.855468999999971 16.894531000000029 C102.807617250000021 19.018554499999993 104.711913750000008 23.266601499999979 105.664062000000058 25.390625 C106.616210499999966 23.266601499999979 108.520507500000008 19.018554499999993 109.472656000000029 16.894531000000029 C110.449218500000029 16.894531000000029 112.402343500000029 16.894531000000029 113.378906000000029 16.894531000000029 C113.378906000000029 20.458984250000015 113.378906000000029 27.587890749999985 113.378906000000029 31.152343999999971 zM95.605468999999999 31.152343999999999 C92.797851750000063 31.152343999999971 87.182617250000021 31.152343999999971 84.375 31.152343999999971 C84.375 27.612304999999992 84.375 20.532227000000034 84.375 16.992187999999999 C87.133788999999979 16.992187999999999 92.651366999999937 16.992187999999999 95.410156000000029 16.992187999999999 C95.410156000000029 17.700195750000034 95.410156000000029 19.116211249999992 95.410156000000029 19.824218999999971 C93.554687250000029 19.824218999999971 89.843749750000029 19.824218999999971 87.988281000000029 19.824218999999971 C87.988281000000029 20.507812749999971 87.988281000000029 21.875000249999971 87.988281000000029 22.558593999999971 C89.672851250000008 22.558593999999971 93.041991749999966 22.558593999999971 94.726562000000058 22.558593999999971 C94.726562000000058 23.266601750000007 94.726562000000058 24.682617250000021 94.726562000000058 25.390625 C93.041991749999966 25.390625 89.672851250000008 25.390625 87.988281000000029 25.390625 C87.988281000000029 26.123046750000015 87.988281000000029 27.587890249999987 87.988281000000029 28.320312000000001 C89.892577999999958 28.320312000000001 93.701172000000042 28.320312000000001 95.605468999999971 28.320312000000001 C95.605468999999971 29.028320000000008 95.605468999999971 30.444336000000021 95.605468999999971 31.152343999999971 zM78.320312000000001 31.152343999999999 C77.807616749999966 30.151367499999992 76.782226250000008 28.149414500000034 76.269531000000029 27.148437999999999 C75.561523249999937 27.148437999999999 74.145507749999979 27.148437999999999 73.4375 27.148437999999999 C73.4375 28.149414500000034 73.4375 30.151367499999992 73.4375 31.152343999999971 C72.534179750000021 31.152343999999971 70.727539250000063 31.152343999999971 69.824218999999971 31.152343999999971 C69.824218999999971 27.612304999999992 69.824218999999971 20.532227000000034 69.824218999999971 16.992187999999999 C71.411133000000063 16.992187999999999 74.584961000000021 16.992187999999999 76.171875 16.992187999999999 Q78.90625 16.992187999999999 80.46875 18.261718999999971 Q82.03125 19.53125 82.03125 21.875 Q82.03125 23.535156000000029 81.347656000000029 24.658203000000015 Q80.664062000000058 25.78125 79.492187999999942 26.464843999999971 C80.468750333333332 28.027343999999971 81.689453250000042 29.980468999999971 82.421875 31.152343999999971 C81.396484249999958 31.152343999999971 79.345702749999987 31.152343999999971 78.320312000000058 31.152343999999971 zM62.304687999999999 27.148437999999999 C61.669922250000013 27.148437999999999 60.400390750000042 27.148437999999999 59.765625 27.148437999999999 C59.765625 28.149414500000034 59.765625 30.151367499999992 59.765625 31.152343999999971 C58.88671875 31.152343999999971 57.12890625 31.152343999999971 56.25 31.152343999999971 C56.25 27.612304999999992 56.25 20.532227000000034 56.25 16.992187999999999 C57.763672000000042 16.992187999999999 60.791016000000013 16.992187999999999 62.304687999999942 16.992187999999999 Q65.039062000000058 16.992187999999999 66.503906000000029 18.261718999999971 Q67.96875 19.53125 67.96875 21.875 Q67.96875 24.414062000000001 66.503906000000029 25.78125 Q65.039062000000058 27.148437999999999 62.304687999999942 27.148437999999999 C62.304687999999942 27.148437999999999 62.304687999999942 27.148437999999999 62.304687999999942 27.148437999999999 zM122.363281000000001 16.992187999999999 C123.266601250000008 16.992187999999999 125.073241749999966 16.992187999999999 125.976562000000058 16.992187999999999 C125.976562000000058 19.042968999999971 125.976562000000058 23.144531000000029 125.976562000000058 25.195312000000001 Q125.976562000000058 26.660156000000029 126.806640499999958 27.441406000000029 Q127.636718999999971 28.222656000000029 128.90625 28.222656000000029 Q130.175781000000029 28.222656000000029 130.957031000000029 27.441406000000029 Q131.738281000000029 26.660156000000029 131.738281000000029 25.195312000000001 C131.738281000000029 22.460937333333334 131.738281000000029 19.042968999999971 131.738281000000029 16.992187999999999 C132.617187250000029 16.992187999999999 134.374999750000029 16.992187999999999 135.253906000000029 16.992187999999999 C135.253906000000029 19.042968999999971 135.253906000000029 23.144531000000029 135.253906000000029 25.195312000000001 Q135.253906000000029 28.027343999999971 133.544922000000042 29.638671999999985 Q131.835937999999942 31.25 128.808593999999971 31.25 Q125.878906000000029 31.25 124.121093500000029 29.638671999999985 Q122.363281000000029 28.027343999999971 122.363281000000029 25.195312000000001 C122.363281000000029 22.460937333333334 122.363281000000029 19.042968999999971 122.363281000000029 16.992187999999999 zM260.058594000000028 0.976562000000001 C196.459961000000021 0.976562000000001 69.262695000000008 0.976562000000001 5.664061999999944 0.976562000000001 Q2.929688000000056 0.976562000000001 1.220703500000013 4.150390500000015 Q-0.488281000000029 7.324218999999971 0.195311999999944 11.132812000000001 C4.492187333333391 28.515624666666668 9.863281499999971 50.244140500000015 13.085938000000056 63.28125 Q13.378906000000029 65.429687999999999 14.453125 67.041016000000013 Q15.527343999999971 68.652343999999971 17.089843999999971 69.238281000000029 C52.734375333333332 78.776041333333353 97.290039500000034 90.698241750000022 124.023437999999942 97.851562000000001 Q128.320312000000058 99.023437999999999 132.8125 99.023437999999999 Q137.304687999999942 99.023437999999999 141.699218999999971 97.851562000000001 C177.31119799999999 88.281249666666668 221.826171749999958 76.318359250000015 248.535156000000029 69.140625 Q250.097656000000029 68.554687999999999 251.171875 66.992187999999999 Q252.246093999999971 65.429687999999999 252.636718999999971 63.183593999999971 C256.933594000000085 45.833333333333314 262.304687750000085 24.145507500000008 265.527344000000085 11.132812000000001 Q266.210937999999942 7.324218999999971 264.501953500000013 4.150390500000015 Q262.792969000000085 0.976562000000001 260.058594000000085 0.976562000000001 C260.058594000000085 0.976562000000001 260.058594000000085 0.976562000000001 260.058594000000085 0.976562000000001 z" />
</g>
<g id="icon-best_value">
	<path id="path___0" data-name="path__0" d="M75.88,26H72.95V29.1h2.93a2.78,2.78,0,0,0,1.56-.39A1.31,1.31,0,0,0,78,27.54a1.35,1.35,0,0,0-.59-1.12A2.54,2.54,0,0,0,75.88,26Zm68.55,0.59h4.1l-2.05-5.18ZM77.54,22.07A1.36,1.36,0,0,0,77.1,21a1.77,1.77,0,0,0-1.22-.39H72.95v2.83h2.93A1.77,1.77,0,0,0,77.1,23a1.25,1.25,0,0,0,.44-1h0Zm115.53,9.77H181.93v-14h10.84V20.7h-7.32v2.73h6.64v2.83h-6.64V29h7.62v2.83ZM179.3,26a5.85,5.85,0,0,1-1.71,4.39A6.37,6.37,0,0,1,172.95,32a6.55,6.55,0,0,1-4.69-1.66A5.76,5.76,0,0,1,166.5,26V17.87h3.61V26a3,3,0,0,0,.78,2.25,3.08,3.08,0,0,0,4.1,0,3,3,0,0,0,.78-2.25V17.87h3.52V26ZM165,31.84h-9.47v-14h3.61v11H165v2.93Zm-14.55,0-1-2.54h-6.05l-1.07,2.54h-3.61l6.05-14h3.61l5.86,14h-3.81ZM137.3,71.78a6.08,6.08,0,0,1-2.15,1.37,12.11,12.11,0,0,1-2.93.68l-0.29.1v2l-0.34.15a2.4,2.4,0,0,1-.93.15,2.12,2.12,0,0,1-1,0L129.2,76V73.83h-0.1a8.19,8.19,0,0,1-2.29-.49,9.21,9.21,0,0,1-2-1.07,4.13,4.13,0,0,1-1.46-1.9,8,8,0,0,1-.49-3V66.6l0.83-.29a7.54,7.54,0,0,1,2.39-.29,8.49,8.49,0,0,1,2.29.24l0.83,0.24v1.07q0,1.56,0,2.1t0,0.73a1,1,0,0,0,.34.49,1.57,1.57,0,0,0,.93.2,2.16,2.16,0,0,0,.68-0.1,0.94,0.94,0,0,0,.49-0.39,1.22,1.22,0,0,0,.29-0.54,2.66,2.66,0,0,0,.1-0.73q0-1,0-1.66a2.91,2.91,0,0,0-.24-1.07,2.25,2.25,0,0,0-.78-0.93,10.4,10.4,0,0,0-1.56-.93L127,63.43a18.46,18.46,0,0,1-1.61-.93,5.13,5.13,0,0,1-1-.78,5.51,5.51,0,0,1-.88-1.17,10.08,10.08,0,0,1-.54-1.32,4.76,4.76,0,0,1-.24-1.51,5.37,5.37,0,0,1,.39-2.1A4.38,4.38,0,0,1,124.32,54a5.63,5.63,0,0,1,2-1.07,14.36,14.36,0,0,1,2.83-.59V50.59l0.34-.2a2.39,2.39,0,0,1,1.12-.2,2.35,2.35,0,0,1,1,.15l0.29,0.15v1.76h0.39a14.35,14.35,0,0,1,2.64.59,5.51,5.51,0,0,1,1.86,1,4,4,0,0,1,1.12,1.56,5.64,5.64,0,0,1,.34,2.05,1.34,1.34,0,0,1,0,.29,1.61,1.61,0,0,0,0,.39l-0.78.29a7.94,7.94,0,0,1-2.15.39,8.12,8.12,0,0,1-2.39-.29l-0.93-.29V56.35a2.54,2.54,0,0,0-.1-0.78,1.41,1.41,0,0,0-.44-0.44,1.45,1.45,0,0,0-.73-0.15,1.51,1.51,0,0,0-.68.15,1.86,1.86,0,0,0-.49.34,2.25,2.25,0,0,0-.29.63,2.66,2.66,0,0,0-.1.73,3.56,3.56,0,0,0,.2,1.22,1.75,1.75,0,0,0,.59.83q0.29,0.2,1.22.73t2.39,1.22q1.27,0.59,2.15,1.07a12,12,0,0,1,1.27.78,3,3,0,0,1,.83.73,6.14,6.14,0,0,1,.63,1,3.08,3.08,0,0,1,.49,1.32,12.24,12.24,0,0,1,.1,1.61,5.65,5.65,0,0,1-.34,2.49,5.47,5.47,0,0,1-1.22,1.9h0Zm-16.6-51h-4.2v11h-3.61v-11h-4.1V17.87H120.7V20.8ZM102.44,31.93a10.34,10.34,0,0,1-3.37-.59,9.15,9.15,0,0,1-3-1.66L97.46,27A8.33,8.33,0,0,0,100,28.52a7,7,0,0,0,2.54.59,2.44,2.44,0,0,0,1.27-.29,1,1,0,0,0,.49-1q0-1.86-3.66-2T97,22a3.62,3.62,0,0,1,1.51-3.08,6.65,6.65,0,0,1,4.05-1.12A10.76,10.76,0,0,1,108,19.34l-1.37,2.73A9.6,9.6,0,0,0,104.3,21a7.36,7.36,0,0,0-2.05-.39,2.3,2.3,0,0,0-1.12.24,0.79,0.79,0,0,0-.44.73q0,1.86,3.71,2.1t3.71,4.05a3.49,3.49,0,0,1-1.66,3.13,7.21,7.21,0,0,1-4,1.07h0Zm-7.62-.1H83.69v-14H94.63V20.7H87.3v2.73h6.64v2.83H87.3V29h7.52v2.83Zm-18.55,0H69.43v-14h6.64a6.27,6.27,0,0,1,3.71,1,3,3,0,0,1,1.37,2.54,3.05,3.05,0,0,1-.63,1.9,3.11,3.11,0,0,1-1.71,1.12,3.57,3.57,0,0,1,2.05,1.27,3.49,3.49,0,0,1,.78,2.25,3.35,3.35,0,0,1-1.42,2.88,6.64,6.64,0,0,1-4,1h0Zm52.93-14,3.52,10.25,3.61-10.25h3.61l-5.47,14h-3.61l-5.47-14h3.81ZM260.06,1H5.66Q2.93,1,1.22,4.15a10.43,10.43,0,0,0-1,7L13.09,63.28A8.83,8.83,0,0,0,14.45,67a5.48,5.48,0,0,0,2.64,2.2L124,97.85A33.25,33.25,0,0,0,132.81,99a34.35,34.35,0,0,0,8.89-1.17L248.54,69.14A5.56,5.56,0,0,0,251.17,67a9.51,9.51,0,0,0,1.46-3.81l12.89-52.05a10.43,10.43,0,0,0-1-7Q262.79,1,260.06,1h0Z" transform="translate(0 -0.98)"/>
</g>
<g id="icon-top-blank">
	<path id="path___0" d="M71.7,25.1h-2.8v3.1h2.8c0.6,0,1.1-0.1,1.6-0.4c0.3-0.3,0.5-0.7,0.5-1.1c0-0.5-0.2-0.9-0.5-1.2
		C72.8,25.2,72.2,25.1,71.7,25.1z M159.5,19.8c-2.2,0-4,1.9-4,4.1c0,1.1,0.4,2.2,1.2,3c0.7,0.8,1.7,1.2,2.8,1.2
		c1.1,0,2.1-0.4,2.8-1.2c0.8-0.8,1.2-1.9,1.1-3c0-1.1-0.4-2.1-1.1-2.9C161.5,20.2,160.5,19.8,159.5,19.8z M73.5,21.1
		c0-0.4-0.2-0.8-0.5-1.1c-0.4-0.3-0.8-0.4-1.3-0.4h-2.8v2.9h2.8c0.5,0,0.9-0.1,1.3-0.4C73.3,21.9,73.5,21.5,73.5,21.1L73.5,21.1z
		 M260.1,0H5.7c-1.8,0-3.3,1-4.4,3.2c-1.1,2.1-1.5,4.6-1,7l12.9,52.1c0.2,1.3,0.6,2.6,1.4,3.7c0.6,1,1.5,1.8,2.6,2.2L124,96.9
		c2.9,0.8,5.8,1.2,8.8,1.2c3,0,6-0.4,8.9-1.2l106.8-28.7c1.1-0.4,2-1.2,2.6-2.1c0.8-1.1,1.3-2.4,1.5-3.8l12.9-52
		c0.5-2.4,0.1-4.9-1-7C263.4,1.1,261.9,0,260.1,0L260.1,0z"/>
</g>
</defs>
</svg>
<style>
svg {
  fill: currentColor;
}

.text-warning {
    color: var(--marigold);
}

.bg-item-number {
    position: relative;
    display: inline-block;
    margin-right: 20px;
    width: 1.5em;
    min-width: 1.5em;
    height: 1.5em;
    text-align: center;
    line-height: 1.5em;
    font-size: 1em;
}

.bg-item-number:before {
    content: "";
    display: block;
    width: 1.5em;
    height: 1.5em;
    background: #4693aa;
    border-radius: 5px;
    position: absolute;
    -webkit-transform: rotate(-45deg);
    transform: rotate(-45deg);
}
.bg-item-number span {
    position: relative;
    display: inline-block;
    font-size: .9em;
    z-index: 2;
    color: #fff;
}

a.list-group-item:hover, a.list-group-item:focus {
    color: #333;
    text-decoration: none;
	background: #FFF;
}

.panel.triangle:after,
.panel.triangle:before {
	content: '';
    display: block;
    position: absolute;
    left: 100%;
    width: 0;
    height: 0;
    border-style: solid;
}

/* this border color controlls the outside, thin border */
.panel.triangle:before {
    top: 28px;
    border-color: transparent transparent transparent #bbd3f5;
    border-width: 17px;
}


/* this border color controlls the color of the triangle (what looks like the fill of the triangle) */
.panel.triangle:after {
    top: 29px;
    border-color: transparent transparent transparent white;
    border-width: 16px;
}


.panel.triangle.bottom:after {
    bottom: -34px;
    left: 70px;
    border-color: white transparent transparent transparent;
    border-width: 17px;
	top: auto;
}

.panel.triangle.bottom:before {
    bottom: -39px;
    left: 68px;
    border-color: #2189e1 transparent transparent transparent;
    border-width: 19px;
	top: auto;
}

.position-ticket:before {
    display: block;
    content: ' ';
    position: absolute;
    left: 2.8px;
    bottom: -5.8px;
    width: 0px;
    height: 0px;
    border-top: 0;
    border-bottom: 6px solid transparent;
    border-right: 9px solid #17328B;
}

ul.procons {
	list-style-type: none;
	line-height: 1.5;
}

ul.procons li {
	margin-left: 1.5rem;
	margin-bottom: 0.5rem;
	position: relative;
}

ul.procons li::before {
	display: block;
	content: "";
	width: 1rem;
	height:1rem;
	top:0.2rem;
	position:absolute;
	left:-1.5rem;
}

ul.procons .pro::before {
	background: url(https://www.runningshoesguru.com/wp-content/themes/rsg_2019/images/icons.svg#icon-pluscircle-view);
}

.top-ticket {
	top: 24px;
}

@media (min-width: 992px) {
.top-ticket {
	top: 56px;
}
}
</style>
<div>


<div class="container">
	<div class="row">
		<div class="col small">
			<div class="spacer"></div>
				<span>
				  <span class="d-none d-md-inline">
					
						<a   href="<?=  get_bloginfo('url') .'/' ?>">
							<span itemprop="name">Home</span>
							<meta itemprop="position" content="1" />
						</a>
						
				  </span>
				  <i class="arrow right d-none d-md-inline-block" style="margin-left:1em;margin-right: 1em;"></i>
				  <span>
						<a  href="<?= get_bloginfo('url') . '/guide/' ?>">
							<i class="arrow left d-md-none"></i>
							<span itemprop="name">Guides</span>
							<span itemprop="position" content="3" />
						</a>					
				  </span>
				  <i class="arrow right d-none d-md-inline-block" style="margin-left:1em;margin-right: 1em;"></i>
				  <span class="btn-link disabled d-none d-md-inline" ><?php the_title(); ?></span>
				  
				</span>
			<div class="spacer-sm"></div>
		</div>
	</div>
</div>


<div id="main" class="container" >
	<div id="content" class="content bg-base section">
		
				<div class="row justify-content-center">
					<div class="col-lg-10 text-center">
						<header class="page-header">

							<h1 class="page-title full-page-title" style="text-align:center">
								<span itemprop="headline"><?= $title; ?></span>
							</h1>
							<div class="spacer-sm"></div>
							<h2 class="subtitle" style="font-weight: 400 !important;">
								<?= $sub_title ?>
							</h2>

						</header>
						<div class="spacer-xs"></div>
						<small itemprop="dateModified" content="<?php the_modified_time('c') ?>" style="font-size: 1.1rem;color: #868e96">Updated: <?php the_modified_time('F jS, Y');?></small>
						<div class="spacer-l"></div>
						<div class="small text-muted">
							<code style="font-size:100%;font-family:var(--font-family-body)">We don't accept free products or compensation in exchange for our reviews.</code><br>
							We are reader supported, and earn affiliate commissions when you buy through us.
						</div>
						<div class="spacer-l"></div>
					</div>
				</div>
				
	</div>
</div>

<?php
if($_REQUEST['test'] == 1) {
	?>
	<div id="main" class="container" >
	<div id="content" class="content bg-base section">
	<div class="row justify-content-center">
	<div class="col-lg-11 text-center">
	<?php
	if( have_rows('flexible_header') ):

				while( have_rows('flexible_header') ): the_row(); 

					// vars
					$not_found_link = get_not_found_link();
					$title = get_sub_field('box_title');
					$linked_post_ID = get_sub_field('linked_post');

					print_review_block_2020($linked_post_ID, $title, 'rsg-test-best-new-20');
					?>
					<div class="spacer-l"></div>
					<?php
				endwhile;

			endif;
	?>
	</div>
	</div>
	</div>
	</div>
	<?php
}
else
	get_template_part( 'template-parts/guide', 'boxes' );
?>
<!--
<div style="">
	<div class="spacer"></div>
	<div class="container">		
		<div class="row">
			<div class="col-md-10 col-md-offset-1">
				<div class="row">
					<div class="col-xl-10 col-xl-offset-1">
						<div class="" style="display: flex;align-items: center;">
							
								<figure class="entry-author-avatar media-object avatar" style="margin-bottom:5px;display:inline-block;margin-right:1rem">
																	<a rel="nofollow sponsored" href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>">
																		<?php
																		the_author_image_size(50, 50, get_the_author_meta('ID') );
																		?>
																	</a>															
								</figure>

								<div>
									<h6 style="margin:0px;text-transform:uppercase" class="entry-author-name">By <a rel="nofollow sponsored" href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>"><?php echo (get_the_author_meta( 'display_name' )); ?></a></h6>
									<div class="text-muted small">Published <?php the_modified_time('F jS, Y');?></div>
								</div>
							
						</div>
					</div>
				</div>
				
			
		</div>
	</div>
	</div>
	<div class="spacer"></div>
</div>
-->

<div class="spacer"></div>
<div class="spacer"></div>


<div style="">
	<div class="spacer"></div>
	<div class="spacer"></div>
	<div class="container">		
		<div class="row justify-content-md-center">
			<div class="col-md-10">
				<div class="row justify-content-md-center">
					<div class="col-xl-12 text-center">
						<span itemprop="description" class="easy-reading" style="font-size:24px"><?php echo get_field( "content_before_reviews" ); ?></span>	
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!--
<div class="center-block">
	<div align="center" data-freestar-ad="__336x280 __336x280" id="runningshoesguru_incontent_1">
			  <script data-cfasync="false" type="text/javascript">
				freestar.config.enabled_slots.push({ placementName: "runningshoesguru_incontent_1", slotId: "runningshoesguru_incontent_1" });
			  </script>
			</div>
</div>
-->

<?php
if( have_rows('review_sections') ):
	$ii = 0;
	// loop through the rows of data
	while ( have_rows('review_sections') ) : the_row();
		$ii++;
		$content_before_reviews = get_sub_field( "introduction_text" );
?>

		<div style="">
			<?php
			if( $ii > 1 ):
			?>
			<!--
			<div class="spacer"></div>
			-->
			<?php
			endif;
			?>
			<div class="container">		
				<div class="row justify-content-md-center">
					<div class="col-md-10">
						<div class="row justify-content-md-center">
							<div class="col-xl-8">
								<div class="spacer"></div>
								<h2 class="h1 text-center">
									<?=get_sub_field('title');?>
								</h2>
								<!--
								<span itemprop="description" class="easy-reading"><?php echo ($content_before_reviews); ?></span>
								-->
								<div class="spacer-l"></div>
								<span class="text-center"><?php echo ($content_before_reviews); ?></span>		
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div style="">
			<div class="container">
				
				<div class="spacer"></div>
				<div class="row justify-content-md-center" style="line-height:2">
					<?php

					// check if the repeater field has rows of data
					if( have_rows('rows') ):
						$i = 0;
						// loop through the rows of data
						while ( have_rows('rows') ) : the_row();
							$i++;
							$review_id = get_sub_field('review');
							$sub_post = get_post( $review_id );
									
							$custom = get_post_custom($sub_post->ID);
							
							if(RSG_get_country_code() == 'US' || RSG_get_country_code() == 'NULL')
								$override_tag = 'rsg-bestnew-20';
							
							if(get_field('override_amazon_tag') && get_field('override_amazon_tag') != "")
								$override_tag = get_field('override_amazon_tag');
							
							/* TO BE REMOVED 
							if($_REQUEST['test_desktop'] == 1)
									$override_tag = "guide-test-mobile-btn-shop-20";
							elseif($_REQUEST['test_desktop'] == 2)
								$override_tag = "guide-test-mobile-btn-seedeal-20";
							else
								$override_tag = "guide-test-mobile-btn-buy-20";
							TO BE REMOVED */
							
							//var_dump($custom);
							$allowed_html = array(
							  'strong' => array(),
							  'b' => array(),
							  'i' => array(),
							);
							
							$verdict = empty( get_sub_field('custom_verdict') ) ? wp_kses(preg_replace("/<br\W*?\/>/", "&nbsp;", $custom["verdict"][0]), $allowed_html) : wp_kses( get_sub_field('custom_verdict') , $allowed_html);
							$verdict = rsg_truncate($verdict, 500, '-');
							if(strlen($verdict[1]) > 1)
								$verdict = $verdict[0].'...';
							else
								$verdict = $verdict[0];
							
							$shoe_name = $custom["shoe_name"][0];
							$current_post_brand = wp_get_post_terms($sub_post->ID, 'brands');
							
							if(!$shoe_name) {
								
								$shoe_name = get_field("model", $sub_post->ID);
							}
							
							$weight = $custom["weight"][0];
							if(!$weight) {
								
								$weight = get_field("weight", $sub_post->ID);
							}
							
							if(!$weight) {
								
								$weight = get_field("weight_oz", $sub_post->ID);
							}
							
							$heeldrop = $custom["heeldrop"][0];
							if(!$heeldrop) {
								$heeldrop = get_field('heel_drop_mm', $sub_post->ID);
							}
							
							if( is_array(get_the_terms($sub_post->ID, 'shoes_type'))) {
								$shoes_types = array_values(get_the_terms($sub_post->ID, 'shoes_type'));
								$shoes_type = $shoes_types[0];
								
								if($shoes_type->name == "Road")
									$shoes_type  = $shoes_types[1];
							}
							
							$author_name = ucfirst(get_the_author_meta( 'display_name', $sub_post->post_author ));
							$author_image = rsg_image_resize( the_author_image_url($sub_post->post_author , 1 ) ,40,40,true,true,70 );
							$author_introduction = get_field('introduction', 'user_'.$sub_post->post_author);
							if(!$author_introduction)
								$author_introduction = "Running Expert";
							
							
							if(!$current_post_brand) {
								$current_post_brand[0] = get_field('brand', $sub_post->ID);
							}
							
							if(isset($custom["stars"][0]) && $custom["stars"][0] > 0)
								$stars = $custom["stars"][0];
							else
								$stars = get_field('vote', $sub_post->ID);
							
							
							if($custom["pricebox"][0] == "" && get_field('ds_query', $sub_post->ID))
								$custom["pricebox"][0] = get_field('ds_query', $sub_post->ID);
							
							$vote_comments_nr = (isset($custom["vote_comments_nr"][0])) ? $custom["vote_comments_nr"][0] : null;
							//echo $vote_comments_nr;
							$vote_comments_total = (isset($custom["vote_comments_total"][0])) ? $custom["vote_comments_total"][0] : null;

							if($vote_comments_total > 1 && $vote_comments_nr > 0)
								$user_vote_percentage = $vote_comments_total/$vote_comments_nr*20;
							else
								$user_vote_percentage = 0;
							if($user_vote_percentage > 100)
								$user_vote_percentage = 100;
							
							$recommended_for = $custom["reccomended_for"][0];
							if(!$recommended_for)
								$recommended_for = get_field( "who_is_it_for" );
							
							$recommended_for = preg_replace('#(?:<br\s*/?>\s*?){2,}#', "<br />", $recommended_for);
							
							$recommended_for = rsg_truncate($recommended_for, 300);
							if(strlen($recommended_for[1]) > 1)
								$recommended_for = $recommended_for[0].'...';
							else
								$recommended_for = $recommended_for[0];
							
							/*
							if(get_sub_field('female_gender') == true)
								$pricebox = rsg_build_datafeed_query($custom["pricebox"][0],1,true, $sub_post->ID, '40,41,49');
							else
								$pricebox = rsg_build_datafeed_query($custom["pricebox"][0],1,false, $sub_post->ID, '40,41,49');
							
							if (function_exists('dstudio')) {
								$price_json = dstudio(array('query' => $pricebox , 'pricebox' => 'newrsg-json', 'countrycode' => RSG_get_country_code() , 'override_tag' => $override_tag ),null);
								$price_json = rsg_datafeed_json($price_json);
							}
							
							
								
							if(!isset($price_json->datafeed->items[0])) {
								if(get_sub_field('female_gender') == true)
									$pricebox = rsg_build_datafeed_query($custom["pricebox"][0],1,true, $sub_post->ID);
								else
									$pricebox = rsg_build_datafeed_query($custom["pricebox"][0],1,false, $sub_post->ID);
								
								if (function_exists('dstudio')) {
									$price_json = dstudio(array('query' => $pricebox , 'pricebox' => 'newrsg-json', 'countrycode' => RSG_get_country_code() , 'override_tag' => $override_tag ),null);
									$price_json = rsg_datafeed_json($price_json);
								}
							}
							*/
							if($custom["pricebox"][0]) {
								$preferred_merchants = "40,41,49,70";
	
								$brands_meta =  get_option( "rsg_taxonomy_".$current_post_brand[0]->term_id );
								
								if(isset($brands_meta['custom_term_preferred_merchants']) && $brands_meta['custom_term_preferred_merchants'] != '')
									$preferred_merchants = $brands_meta['custom_term_preferred_merchants'];
								
								$pricebox_female = rsg_build_datafeed_query($custom["pricebox"][0],1,true, $ID, $preferred_merchants);
								$pricebox_male = rsg_build_datafeed_query($custom["pricebox"][0],1,false, $ID, $preferred_merchants);
								
								
	
								
								if (function_exists('dstudio')) {
									$price_json_male = dstudio(array('query' => $pricebox_male , 'pricebox' => 'newrsg-json', 'countrycode' => RSG_get_country_code() , 'override_tag' => $override_tag ),null);
									$price_json_male = rsg_datafeed_json($price_json_male);
									
									$price_json_female = dstudio(array('query' => $pricebox_female , 'pricebox' => 'newrsg-json', 'countrycode' => RSG_get_country_code() , 'override_tag' => $override_tag ),null);
									$price_json_female = rsg_datafeed_json($price_json_female);
								}
								
								if(!isset($price_json_male->datafeed->items[0])) {
									
									$pricebox_male = rsg_build_datafeed_query($custom["pricebox"][0],1,false, $sub_post->ID);
									
									if (function_exists('dstudio')) {
										$price_json_male = dstudio(array('query' => $pricebox_male , 'pricebox' => 'newrsg-json', 'countrycode' => RSG_get_country_code() , 'override_tag' => $override_tag ),null);
										$price_json_male = rsg_datafeed_json($price_json_male);
									}
								}
								
								if(!isset($price_json_female->datafeed->items[0])) {
									
									$pricebox_female = rsg_build_datafeed_query($custom["pricebox"][0],1,true, $sub_post->ID);
									
									if (function_exists('dstudio')) {
										$price_json_female = dstudio(array('query' => $pricebox_female , 'pricebox' => 'newrsg-json', 'countrycode' => RSG_get_country_code() , 'override_tag' => $override_tag ),null);
										$price_json_female = rsg_datafeed_json($price_json_female);
									}
								}
								
								if(isset($price_json_male->datafeed->items[0])) {
									$price_male = $price_json_male->datafeed->items[0]->CURRENCY_PREFIX.$price_json_male->datafeed->items[0]->DISPLAY_PRICE;
									$merchant_name_male = $price_json_male->datafeed->items[0]->MERCHANT_NAME;
									$link_male = $price_json_male->datafeed->items[0]->DEEP_LINK;
									$currency_prefix_1 = $price_json_male->datafeed->items[0]->CURRENCY_PREFIX;
									
									$price_male1 = null;
									if(isset($price_json_male->datafeed->items[1])) {
										$price_male1 = $price_json_male->datafeed->items[1]->CURRENCY_PREFIX.$price_json_male->datafeed->items[1]->DISPLAY_PRICE;
										$merchant_name_male1 = $price_json_male->datafeed->items[1]->MERCHANT_NAME;
										$link_male1 = $price_json_male->datafeed->items[1]->DEEP_LINK;
										$currency_prefix_11 = $price_json_male->datafeed->items[1]->CURRENCY_PREFIX;
									}

								}
								else {
									
									$merchant_name_male = 'Not Found '.$not_found_link;
									$link_male = $not_found_link;
								}
								
								if(isset($price_json_female->datafeed->items[0])) {
									$price_female = $price_json_female->datafeed->items[0]->CURRENCY_PREFIX.$price_json_female->datafeed->items[0]->DISPLAY_PRICE;
									$merchant_name_female = $price_json_female->datafeed->items[0]->MERCHANT_NAME;
									$link_female = $price_json_female->datafeed->items[0]->DEEP_LINK;
									$currency_prefix_1 = $price_json_female->datafeed->items[0]->CURRENCY_PREFIX;

								}
								else {
									
									$merchant_name_female = 'Not Found '.$not_found_link;
									$link_female = $not_found_link;
								}
								
							}
							elseif(get_field( "amazon_link", $sub_post->ID )) {
								//$amazon_item = amazon_item_lookup(get_field( "amazon_link", $sub_post->ID ) , "rsg-gps-guide-20" );
								$amazon_item = amazon_item_lookup(get_field( "amazon_link", $sub_post->ID ) , "rsg-gps-20" );
								$amazon_item = $amazon_item[0];
								$link_male = $amazon_item->DetailPageURL;
								$price_male = amazon_item_getDisplayPrice($amazon_item);
								$merchant_name_male = 'amazon';
								$ListPrice = $amazon_item->Offers->Listings[0]->SavingBasis->Amount;	
								
								if( !isset($price_male) ) {
									$merchant_name_male = 'Not Found '.$not_found_link;
									$link = $not_found_link;
								}
								$rrp = $ListPrice;

								$battery_life_on_gps = get_field('battery_life_on_gps', $sub_post->ID );
							}
							
							$on_click_event_male = "ga('send', 'event', 'Aff_Link', 'Guide-Bottom-Box', '".$merchant_name_male."');";
							$on_click_event_female = "ga('send', 'event', 'Aff_Link', 'Guide-Bottom-Box', '".$merchant_name_female."');";
							$on_click_event_readmore = "ga('send', 'event', 'Aff_Link', 'Guide-Bottom-Box-Readmore', '".$merchant_name_male."');";
							
							$permalink = get_permalink($review_id);
							
							if(empty($override_image)) {
								
								if( 'affiliate_product' == get_post_type( $sub_post ) ) {
									if( (function_exists( 'has_post_thumbnail' ) ) && ( has_post_thumbnail( $sub_post->ID) ) ) :
										if ($thumbnail = wp_get_attachment_image_src ( get_post_thumbnail_id ( $sub_post->ID ), array(9999,9999)) )
											$thumbnail = $thumbnail[0];
									endif;
								}
								else {						
									$thumbnail = get_field( 'professional_image', $sub_post->ID );
								}
								/*
								if( (function_exists( 'has_post_thumbnail' ) ) && ( has_post_thumbnail( $sub_post->ID) ) ) :
									if ($thumbnail = wp_get_attachment_image_src ( get_post_thumbnail_id ( $sub_post->ID ), array(9999,9999)) )
										$thumbnail = $thumbnail[0];
								endif;
								*/
							} else {
								$thumbnail = $override_image;
							}
							
							/*
							if($_REQUEST['pro'] == 1) {
								if(get_field( 'professional_image', $sub_post->ID ))
									$thumbnail = get_field( 'professional_image', $sub_post->ID );
							}
							*/
							
							
							?>
							
							<?php
							if($_REQUEST['test_desktop'] == '1old'):
							?>
								<div class="col-lg-5" style="">
									<div class="panel w-100 text-center triangle" style="position:relative;border: 1px solid #bbd3f5;box-shadow: none;">
										<div class="top-ticket text-center position-ticket" style="background-color: #2189E1;text-transform: none;left: -.75rem;padding: .9em 1em;border-radius: 5px;box-shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.1)">
											<?= ($i == 1 ? 'First choice' : 'Also consider') ?>
										</div>
										<div class="panel-body">
											<?php
											if(!empty($thumbnail))
												build_progressive_picture($thumbnail, 370, 250, '');
											?>
											<div class="spacer"></div>
											<div class="row text-left">
												<div class="col-3 offset-lg-1">
													<h6 class="mb-3" style="color: inherit;">Weight</h6>
													<?=$weight?> oz.
												</div>
												<div class="col-3">
													<h6 class="mb-3" style="color: inherit;">Drop</h6>
													<?=$heeldrop?> mm.
												</div>
												<div class="col-3">
													<h6 class="mb-3" style="color: inherit;">Category</h6>
													<?= $shoes_type->name ?>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-7">
									<div class="review_box_stars_small"><div style="width:<?php echo $stars*10; ?>%"></div></div>
									<?php
									echo '<h4 class="mb-4 mt-3" style="">'. $current_post_brand[0]->name .' '.$shoe_name.'</h4>';
									?>
									<a rel="nofollow sponsored" target="_blank" onclick="<?=$on_click_event_male?>" href="<?=$link_male?>" class="btn btn-warning">Buy Men's&nbsp;&nbsp;<span class="right-arrow"></span></a>
									&nbsp;&nbsp;
									<a rel="nofollow sponsored" target="_blank" onclick="<?=$on_click_event_female?>" href="<?=$link_female?>" class="btn btn-warning">Buy Women's&nbsp;&nbsp;<span class="right-arrow"></span></a>
									<div class="spacer"></div>
									<h6 class="mb-3" style="color: inherit;">
									Reasons to buy
									</h6>
									<!--
									<ul class="list-unstyled  green-check">
										<?php
										if( have_rows('pros', $sub_post->ID) ):

											// loop through the rows of data
											while ( have_rows('pros', $sub_post->ID) ) : the_row();

												// display a sub field value
												echo '<li style="margin-bottom:0.5rem">'.get_sub_field('pro_line', $sub_post->ID).'</li>';

											endwhile;
										else:
											$pros = preg_split('#(<br/>|<br>|<br />)#', $custom["pros"][0] );
											
											foreach($pros as $pro)
												echo '<li style="margin-bottom:0.5rem">'.$pro.'</li>';
										endif;
										?>
									</ul>
									-->
									<ul class="list-unstyled  procons">
										<?php
										if( have_rows('pros', $sub_post->ID) ):

											// loop through the rows of data
											while ( have_rows('pros', $sub_post->ID) ) : the_row();

												// display a sub field value
												echo '<li class="pro">'.get_sub_field('pro_line', $sub_post->ID).'</li>';

											endwhile;
										else:
											$pros = preg_split('#(<br/>|<br>|<br />)#', $custom["pros"][0] );
											$pros = array_slice( $pros , 0 , 4 ); 
											foreach($pros as $pro)
												echo '<li class="pro">'.$pro.'</li>';
										endif;
										?>
									</ul>
									<div class="spacer-xs"></div>
									“<?php
									echo $verdict;
									?>”
									
									<div class="spacer"></div>
									<h6 class="mb-3" style="color: inherit;">
									Recommended for
									</h6>
									<?= $recommended_for ?>
									<div class="spacer"></div>
									<div class="row">
										<div class="col-6">
											<div class="row no-gutters">
												<div class="col-2">
													<img src="<?=$author_image ?>" class="img-circle avatar avatar-40" alt="" width="40" height="40">
												</div>
												<div class="col-9">
													<?= $author_name ?>
													<div class="small text-muted">
														<?= $author_introduction ?>
													</div>
												</div>
											</div>
										</div>
										<div class="col-6 text-right">
											<h6 style="color: inherit;"><a href="<?=$permalink?>" onclick="<?=$on_click_event_readmore?>">Read the full review</a> of <?=$shoe_name?> or buy them here: <a onclick="<?=$on_click_event_male?>" href="<?=$link_male?>" target="_blank">Men</a> / <a onclick="<?=$on_click_event_female?>" href="<?=$link_female?>" target="_blank">Women</a></h6>
										</div>
									</div>
									
									
								</div>
								<div class="col-12 spacer-xxl"></div>
								<div class="col-12 spacer-sm"></div>
								
							<?php
							elseif($_REQUEST['test_desktop'] == '1'):
							?>
								<div class="col-lg-11" style="">
									<div class="panel w-100 text-center triangle bottom" style="position:relative;border: 2px solid #2189e1;box-shadow: none;">
										<div class="top-ticket text-center position-ticket" style="background-color: #2189E1;text-transform: none;left: -.75rem;padding: .9em 1em;border-radius: 5px;box-shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.1)">
											<?= ($i == 1 ? 'First choice' : 'Also consider') ?>
										</div>
										<div class="panel-body">
											<div class="row">
												<div class="col">
													<?php
													if(!empty($thumbnail))
														build_progressive_picture($thumbnail, 370, 250, '');
													?>
													<div class="d-lg-none text-left">
														<div class="review_box_stars_small"><div style="width:<?php echo $stars*10; ?>%"></div></div>
														<?php
														echo '<h4 class="mt-2" style="">'. $current_post_brand[0]->name .' '.$shoe_name.'</h4>';
														?>
													</div>
													<div class="spacer"></div>
													
													<div class="row no-gutters text-left">
														<div class="col-4 col-lg-3 px-lg-2 offset-lg-2">
															<h6 style="color: inherit;">Weight</h6>
															<div class="spacer-sm d-none d-lg-block"></div>
															<div class="spacer-xs d-lg-none"></div>
															<?=$weight?> oz.
														</div>
														<div class="col-4 col-lg-3 px-lg-2">
															<h6 style="color: inherit;">Drop</h6>
															<div class="spacer-sm d-none d-lg-block"></div>
															<div class="spacer-xs d-lg-none"></div>
															<?=$heeldrop?> mm.
														</div>
														<div class="col-4 col-lg-3 px-lg-2">
															<h6 style="color: inherit;">Category</h6>
															<div class="spacer-sm d-none d-lg-block"></div>
															<div class="spacer-xs d-lg-none"></div>
															<?= $shoes_type->name ?>
														</div>
													</div>
													<div class="spacer d-lg-none"></div>
												</div>
												<div class="col-12 col-lg-6 text-left">
													<div class="d-none d-lg-block">
														<div class="spacer"></div>
														<div class="review_box_stars_small"><div style="width:<?php echo $stars*10; ?>%"></div></div>
														<?php
														echo '<h4 class="mb-4 mt-3" style="">'. $current_post_brand[0]->name .' '.$shoe_name.'</h4>';
														?>
													</div>
													<div class="d-none d-lg-block">
														<a rel="nofollow sponsored" target="_blank" onclick="<?=$on_click_event_male?>" href="<?=$link_male?>" class="btn btn-warning">Shop Men's&nbsp;&nbsp;<span class="right-arrow"></span></a>
														&nbsp;&nbsp;
														<a rel="nofollow sponsored" target="_blank" onclick="<?=$on_click_event_female?>" href="<?=$link_female?>" class="btn btn-warning">Shop Women's&nbsp;&nbsp;<span class="right-arrow"></span></a>
													</div>
													<div class="d-lg-none">
														<a rel="nofollow sponsored" target="_blank" onclick="<?=$on_click_event_male?>" href="<?=$link_male?>" class="btn btn-warning btn-sm" style="font-size:1rem">Shop Men's</a>
														&nbsp;&nbsp;
														<a rel="nofollow sponsored" target="_blank" onclick="<?=$on_click_event_female?>" href="<?=$link_female?>" class="btn btn-warning btn-sm" style="font-size:1rem">Shop Women's</a>
													</div>
													<div class="spacer"></div>
													<h6 class="mb-3" style="color: inherit;">
													Reasons to buy
													</h6>
													<!--
													<ul class="list-unstyled  green-check">
														<?php
														if( have_rows('pros', $sub_post->ID) ):

															// loop through the rows of data
															while ( have_rows('pros', $sub_post->ID) ) : the_row();

																// display a sub field value
																echo '<li style="margin-bottom:0.5rem">'.get_sub_field('pro_line', $sub_post->ID).'</li>';

															endwhile;
														else:
															$pros = preg_split('#(<br/>|<br>|<br />)#', $custom["pros"][0] );
															
															foreach($pros as $pro)
																echo '<li style="margin-bottom:0.5rem">'.$pro.'</li>';
														endif;
														?>
													</ul>
													-->
													<ul class="list-unstyled  procons">
														<?php
														if( have_rows('pros', $sub_post->ID) ):

															// loop through the rows of data
															while ( have_rows('pros', $sub_post->ID) ) : the_row();

																// display a sub field value
																echo '<li class="pro">'.get_sub_field('pro_line', $sub_post->ID).'</li>';

															endwhile;
														else:
															$pros = preg_split('#(<br/>|<br>|<br />)#', $custom["pros"][0] );
															$pros = array_slice( $pros , 0 , 4 ); 
															foreach($pros as $pro)
																echo '<li class="pro">'.$pro.'</li>';
														endif;
														?>
													</ul>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-10">
									<div class="spacer"></div>
									<div class="spacer-xs"></div>
									“<?php
									echo $verdict;
									?>”
									
									<div class="spacer"></div>
									<div class="d-none d-lg-block">
										<h6 class="mb-3" style="color: inherit;">
										Recommended for
										</h6>
										<?= $recommended_for ?>
										<div class="spacer"></div>
									</div>
									<div class="row">
										<div class="col-6">
											<div class="row no-gutters">
												<div class="col-2">
													<img src="<?=$author_image ?>" class="img-circle avatar avatar-40" alt="" width="40" height="40">
												</div>
												<div class="col-9">
													<?= $author_name ?>
													<div class="small text-muted">
														<?= $author_introduction ?>
													</div>
												</div>
											</div>
										</div>
										<div class="col-6 text-right">
											<h6 style="color: inherit;"><a href="<?=$permalink?>" onclick="<?=$on_click_event_readmore?>">Read the full review</a> of <?=$shoe_name?><br>or buy them here: <a onclick="<?=$on_click_event_male?>" href="<?=$link_male?>" target="_blank">Men</a> / <a onclick="<?=$on_click_event_female?>" href="<?=$link_female?>" target="_blank">Women</a></h6>
										</div>
									</div>
									
									
								</div>
								<div class="col-12 spacer-xxl"></div>
								<div class="col-12 spacer-xs"></div>
							<?php
							elseif($_REQUEST['test_desktop'] == '2'):
							?>
								<div class="col-lg-11" style="">
									<div class="panel w-100 text-center triangle bottom" style="position:relative;border: 2px solid #2189e1;box-shadow: none;">
										<div class="top-ticket text-center position-ticket" style="background-color: #2189E1;text-transform: none;left: -.75rem;padding: .9em 1em;border-radius: 5px;box-shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.1)">
											<?= ($i == 1 ? 'First choice' : 'Also consider') ?>
										</div>
										<div class="panel-body">
											<div class="row">
												<div class="col">
													<?php
													if(!empty($thumbnail))
														build_progressive_picture($thumbnail, 370, 250, '');
													?>
													<div class="d-lg-none text-left">
														<div class="review_box_stars_small"><div style="width:<?php echo $stars*10; ?>%"></div></div>
														<?php
														echo '<h4 class="mt-2" style="">'. $current_post_brand[0]->name .' '.$shoe_name.'</h4>';
														?>
													</div>
													<div class="spacer"></div>
													
													<div class="row no-gutters text-left">
														<div class="col-4 col-lg-3 px-lg-2 offset-lg-2">
															<h6 style="color: inherit;">Weight</h6>
															<div class="spacer-sm d-none d-lg-block"></div>
															<div class="spacer-xs d-lg-none"></div>
															<?=$weight?> oz.
														</div>
														<div class="col-4 col-lg-3 px-lg-2">
															<h6 style="color: inherit;">Drop</h6>
															<div class="spacer-sm d-none d-lg-block"></div>
															<div class="spacer-xs d-lg-none"></div>
															<?=$heeldrop?> mm.
														</div>
														<div class="col-4 col-lg-3 px-lg-2">
															<h6 style="color: inherit;">Category</h6>
															<div class="spacer-sm d-none d-lg-block"></div>
															<div class="spacer-xs d-lg-none"></div>
															<?= $shoes_type->name ?>
														</div>
													</div>
													<div class="spacer d-lg-none"></div>
												</div>
												<div class="col-12 col-lg-6 text-left">
													<div class="d-none d-lg-block">
														<div class="spacer"></div>
														<div class="review_box_stars_small"><div style="width:<?php echo $stars*10; ?>%"></div></div>
														<?php
														echo '<h4 class="mb-4 mt-3" style="">'. $current_post_brand[0]->name .' '.$shoe_name.'</h4>';
														?>
													</div>
													<div class="d-none d-lg-block">
														<a rel="nofollow sponsored" target="_blank" onclick="<?=$on_click_event_male?>" href="<?=$link_male?>" class="btn btn-warning">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;See Deal&nbsp;&nbsp;<span class="right-arrow"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>
													</div>
													<div class="d-lg-none text-center">
														<a rel="nofollow sponsored" target="_blank" onclick="<?=$on_click_event_male?>" href="<?=$link_male?>" class="btn btn-warning">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;See Deal&nbsp;&nbsp;<span class="right-arrow"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>
													</div>
													<div class="spacer"></div>
													<h6 class="mb-3" style="color: inherit;">
													Reasons to buy
													</h6>
													<!--
													<ul class="list-unstyled  green-check">
														<?php
														if( have_rows('pros', $sub_post->ID) ):

															// loop through the rows of data
															while ( have_rows('pros', $sub_post->ID) ) : the_row();

																// display a sub field value
																echo '<li style="margin-bottom:0.5rem">'.get_sub_field('pro_line', $sub_post->ID).'</li>';

															endwhile;
														else:
															$pros = preg_split('#(<br/>|<br>|<br />)#', $custom["pros"][0] );
															
															foreach($pros as $pro)
																echo '<li style="margin-bottom:0.5rem">'.$pro.'</li>';
														endif;
														?>
													</ul>
													-->
													<ul class="list-unstyled  procons">
														<?php
														if( have_rows('pros', $sub_post->ID) ):

															// loop through the rows of data
															while ( have_rows('pros', $sub_post->ID) ) : the_row();

																// display a sub field value
																echo '<li class="pro">'.get_sub_field('pro_line', $sub_post->ID).'</li>';

															endwhile;
														else:
															$pros = preg_split('#(<br/>|<br>|<br />)#', $custom["pros"][0] );
															$pros = array_slice( $pros , 0 , 4 ); 
															foreach($pros as $pro)
																echo '<li class="pro">'.$pro.'</li>';
														endif;
														?>
													</ul>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-10">
									<div class="spacer"></div>
									<div class="spacer-xs"></div>
									“<?php
									echo $verdict;
									?>”
									
									<div class="spacer"></div>
									<div class="d-none d-lg-block">
										<h6 class="mb-3" style="color: inherit;">
										Recommended for
										</h6>
										<?= $recommended_for ?>
										<div class="spacer"></div>
									</div>
									<div class="row">
										<div class="col-6">
											<div class="row no-gutters">
												<div class="col-2">
													<img src="<?=$author_image ?>" class="img-circle avatar avatar-40" alt="" width="40" height="40">
												</div>
												<div class="col-9">
													<?= $author_name ?>
													<div class="small text-muted">
														<?= $author_introduction ?>
													</div>
												</div>
											</div>
										</div>
										<div class="col-6 text-right">
											<h6 style="color: inherit;"><a href="<?=$permalink?>" onclick="<?=$on_click_event_readmore?>">Read the full review</a> of <?=$shoe_name?><br>or buy them here: <a onclick="<?=$on_click_event_male?>" href="<?=$link_male?>" target="_blank">Men</a> / <a onclick="<?=$on_click_event_female?>" href="<?=$link_female?>" target="_blank">Women</a></h6>
										</div>
									</div>
									
									
								</div>
								<div class="col-12 spacer-xxl"></div>
								<div class="col-12 spacer-xs"></div>
							<?php
							else:
							?>
								<div class="col-lg-11" style="">
									<div class="panel w-100 text-center triangle bottom" style="position:relative;border: 2px solid #2189e1;box-shadow: none;">
										<div class="top-ticket text-center position-ticket" style="background-color: #2189E1;text-transform: none;left: -.75rem;padding: .9em 1em;border-radius: 5px;box-shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.1)">
											<?= ($i == 1 ? 'First choice' : 'Also consider') ?>
										</div>
										<div class="panel-body">
											<div class="row">
												<div class="col">
													<a href="<?=$permalink?>" onclick="<?=$on_click_event_readmore?>" rel="nofollow">
													<?php
													if(!empty($thumbnail))
														build_progressive_picture($thumbnail, 370, 250, '');
													?>
													</a>
													<div class="d-lg-none text-left">
														<div class="review_box_stars_small"><div style="width:<?php echo $stars*10; ?>%"></div></div>
														<a href="<?=$permalink?>" onclick="<?=$on_click_event_readmore?>">
														<?php
														echo '<h4 class="mt-2" style="">'. $current_post_brand[0]->name .' '.$shoe_name.'</h4>';
														?>
														</a>
													</div>
													<div class="spacer"></div>
													
													<?php
													if(isset($weight)):
													?>
														<div class="row no-gutters text-left">
															<div class="col-4 col-lg-3 px-lg-2 offset-lg-2">
																<h6 style="color: inherit;">Weight</h6>
																<div class="spacer-sm d-none d-lg-block"></div>
																<div class="spacer-xs d-lg-none"></div>
																<?=$weight?> oz.
															</div>
															<div class="col-4 col-lg-3 px-lg-2">
																<h6 style="color: inherit;">Drop</h6>
																<div class="spacer-sm d-none d-lg-block"></div>
																<div class="spacer-xs d-lg-none"></div>
																<?=$heeldrop?> mm.
															</div>
															<div class="col-4 col-lg-3 px-lg-2">
																<h6 style="color: inherit;">Category</h6>
																<div class="spacer-sm d-none d-lg-block"></div>
																<div class="spacer-xs d-lg-none"></div>
																<?= $shoes_type->name ?>
															</div>
														</div>
													<?php
													else:
													?>
														<div class="row no-gutters text-left">
															<div class="col-6 col-lg-4 px-lg-2 offset-lg-3">
																<h6 style="color: inherit;">Battery life</h6>
																<div class="spacer-sm d-none d-lg-block"></div>
																<div class="spacer-xs d-lg-none"></div>
																<?=$battery_life_on_gps?> hrs.
															</div>
															<div class="col-6 col-lg-4 px-lg-2">
																<h6 style="color: inherit;">List price</h6>
																<div class="spacer-sm d-none d-lg-block"></div>
																<div class="spacer-xs d-lg-none"></div>
																<?= $rrp ?> US$
															</div>
														</div>
														
													<?php
													endif;
													?>
													<div class="spacer d-lg-none"></div>
												</div>
												<div class="col-12 col-lg-6 text-left">
													<div class="d-none d-lg-block">
														<div class="spacer"></div>
														<div class="review_box_stars_small"><div style="width:<?php echo $stars*10; ?>%"></div></div>
														<a href="<?=$permalink?>" onclick="<?=$on_click_event_readmore?>">
														<?php
														echo '<h4 class="mb-4 mt-3" style="">'. $current_post_brand[0]->name .' '.$shoe_name.'</h4>';
														?>
														</a>
													</div>
													<?php
													if(isset($link_female)):
													?>
														<div class="d-none d-md-block">
															<a rel="nofollow sponsored" target="_blank" onclick="<?=$on_click_event_male?>" href="<?=$link_male?>" class="btn btn-warning">Shop Men&nbsp;&nbsp;<span class="right-arrow"></span></a>
															&nbsp;&nbsp;
															<a rel="nofollow sponsored" target="_blank" onclick="<?=$on_click_event_female?>" href="<?=$link_female?>" class="btn btn-warning">Shop Women&nbsp;&nbsp;<span class="right-arrow"></span></a>
														</div>
														<div class="d-md-none">
															<a rel="nofollow sponsored" target="_blank" onclick="<?=$on_click_event_male?>" href="<?=$link_male?>" class="btn btn-warning btn-sm" style="font-size:1rem">&nbsp;Shop Men&nbsp;</a>
															&nbsp;&nbsp;
															<a rel="nofollow sponsored" target="_blank" onclick="<?=$on_click_event_female?>" href="<?=$link_female?>" class="btn btn-warning btn-sm" style="font-size:1rem">&nbsp;Shop Women&nbsp;</a>
														</div>
													<?php
													else:
													?>
														<a rel="nofollow sponsored" target="_blank" onclick="<?=$on_click_event_male?>" href="<?=$link_male?>" class="btn btn-warning">Check on Amazon&nbsp;&nbsp;<span class="right-arrow"></span></a>
													<?php
													endif;
													?>
													<div class="spacer"></div>
													<h6 class="mb-3" style="color: inherit;">
													Reasons to buy
													</h6>
													<!--
													<ul class="list-unstyled  green-check">
														<?php
														if( have_rows('pros', $sub_post->ID) ):

															// loop through the rows of data
															while ( have_rows('pros', $sub_post->ID) ) : the_row();

																// display a sub field value
																echo '<li style="margin-bottom:0.5rem">'.get_sub_field('pro_line', $sub_post->ID).'</li>';

															endwhile;
														else:
															$pros = preg_split('#(<br/>|<br>|<br />)#', $custom["pros"][0] );
															
															foreach($pros as $pro)
																echo '<li style="margin-bottom:0.5rem">'.$pro.'</li>';
														endif;
														?>
													</ul>
													-->
													<ul class="list-unstyled  procons">
														<?php
														if( have_rows('pros', $sub_post->ID) ):

															// loop through the rows of data
															while ( have_rows('pros', $sub_post->ID) ) : the_row();

																// display a sub field value
																echo '<li class="pro">'.get_sub_field('pro_line', $sub_post->ID).'</li>';

															endwhile;
														else:
															$pros = preg_split('#(<br/>|<br>|<br />)#', $custom["pros"][0] );
															$pros = array_slice( $pros , 0 , 4 ); 
															foreach($pros as $pro)
																echo '<li class="pro">'.$pro.'</li>';
														endif;
														?>
													</ul>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-10">
									<div class="spacer"></div>
									<div class="spacer-xs"></div>
									“<?php
									echo $verdict;
									?>”
									
									<div class="spacer"></div>
									<?
									if(isset($recommended_for) && $recommended_for != ''):
									?>
										<div class="d-none d-lg-block">
											<h6 class="mb-3" style="color: inherit;">
											Recommended for
											</h6>
											<?= $recommended_for ?>
											<div class="spacer"></div>
										</div>
									<?
									endif;
									?>
									<div class="row">
										<div class="col-6">
											<div class="row no-gutters">
												<div class="col-2">
													<img src="<?=$author_image ?>" class="img-circle avatar avatar-40" alt="" width="40" height="40">
												</div>
												<div class="col-9">
													<?= $author_name ?>
													<div class="small text-muted">
														<?= $author_introduction ?>
													</div>
												</div>
											</div>
										</div>
										<div class="col-6 text-right">
											<h6 style="color: inherit;"><a href="<?=$permalink?>" onclick="<?=$on_click_event_readmore?>">Read the full review</a> of <?=$shoe_name?><br>or buy them here: 
											<?php
											if(isset($link_female)):
											?>
												<a onclick="<?=$on_click_event_male?>" href="<?=$link_male?>" target="_blank">Men</a> / <a onclick="<?=$on_click_event_female?>" href="<?=$link_female?>" target="_blank">Women</a>
											<?php
											else:
											?>
												<a onclick="<?=$on_click_event_male?>" href="<?=$link_male?>" target="_blank">Amazon</a>
											<?php
											endif;
											?>
											</h6>

											</div>
									</div>
									
									
								</div>
								<div class="col-12 spacer-xxl"></div>
								<div class="col-12 spacer-xs"></div>
							<?php
							endif;
							
							endwhile;

						endif;

						?>
				</div>
			</div>
		</div>
<?php
	if($ii % 2) {
		?>
		<!--
		<div class="clearfix"></div>
		<div align="center" data-freestar-ad="__336x280 __336x280" id="runningshoesguru_incontent_2">
			  <script data-cfasync="false" type="text/javascript">
				freestar.config.enabled_slots.push({ placementName: "runningshoesguru_incontent_2", slotId: "runningshoesguru_incontent_2" });
			  </script>
			</div>
		-->
		<?php
	}
	endwhile;
endif;
?>



<div style="">
	<div class="spacer"></div>
	<div class="spacer"></div>
	<div class="container">		
		<div class="row justify-content-md-center" id="main-row">
			<div class="col-md-8 col-lg-8">
					
				<span itemprop="articleBody" class="easy-reading"><?php echo ($content_after_reviews); ?></span>		
				
		
				<div class="spacer-l"></div>
				<div class="small text-center text-muted">
					As an Amazon Associate we earn from qualifying purchases
				</div>
				<div class="spacer-l"></div>
				
				<?php
				get_template_part( 'include', 'form-plans' );
				?>
							
					
			</div>
		</div>
		<div class="spacer"></div>
	</div>
</div>

<div class="spacer-xxl"></div>

<div class="container">
					<div class="row">
						<div class="col-xl-12">
								
								
								
								
								
								<aside class="widget widget-author media row">
									
									<figure class="entry-author-avatar media-object avatar col-3 col-md-2" style="margin-top:10px">
										<a rel="nofollow" href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>">
											<?php
											the_author_image_size(100, 100, get_the_author_meta('ID') );
											?>
										</a>					
									</figure>
									<div class="media-body col-9 col-md-10">
										<h4 class="entry-author-name">By <a rel="nofollow" href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>">
										<span class="author">
											<span itemprop="name"><?php echo ucfirst(get_the_author_meta( 'display_name' )); ?></span>
											<meta itemprop="url" content="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>">
										</span></a>
										</h4>

										<p>
											<?php
											echo get_the_author_meta( 'description' );
											?>
										</p>

									</div>

								</aside>
								<div class="spacer"></div>
								
							</div>
						</div>
</div>


<aside class="widget" style="margin-bottom:0px" itemprop="comment">
	<?php comments_template('/comments-full-width.php', true); ?>
</aside>
<?php endwhile; endif ; ?>	

</div>

<div class="after-entry">
				<div class="container">
					<div class="row">
						<div class="col-xl-12">								
								<?php
								get_template_part( 'include', 'amazon-native' );
								?>
							</div>
						</div>
					</div>
								

					
					
					
					
					
					<div class="">
						

							
					
						
								
					
							
					

								<aside class="widget">	
									<div class="widget-title-bg">		
										<div class="container">
													

														<h3 class="widget-title ribbon">
															Latest Guides
														</h3>
										</div>
									</div>
										<div class="container">
											<div class="row">
												<div class="col-xl-12">
																
																							<?php
										if(get_post_type() == 'guide') {
											$args = array(
											'post__not_in' => array($post->ID),
											'post_type' => 'guide',
											'posts_per_page' => 8
											);
											$related_posts = new WP_Query($args);
											
											if(isset($related_posts) && $related_posts != null) {
												$related_posts = wp_list_pluck( $related_posts->posts, 'ID' );
												$wp_query->related_posts = $related_posts;
												get_template_part( 'include', 'related-posts-fullwidth' );
											}
											
										}
															
															?>

													
												</div>
											</div>
										</div>
								</aside>
								
								
	</div>
</div>

<?php
endif;
?>

<div class="spacer-xxl"></div>
<div class="spacer-xl"></div>		


<?php get_footer(); ?>