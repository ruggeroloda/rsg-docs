<?php
/*
 * Template Name: Single Trail Review 2022
 */
  

get_header();

if( have_posts() ) : while( have_posts() ) : the_post();


$descriptions['lugs'] = Array(
	'The {scarpa} does not always provide appropriate traction on wet and slippery surfaces',
	'The {scarpa} provides good traction on most types of terrains and surfaces',
	'The {scarpa} provides excellent grip and traction on all kind of terrains and surfaces'
);

$descriptions['rockplate'] = Array(
	'The {scarpa} does not have a rock plate.',
	'The rock plate in the {scarpa} does not adequately protect from sharp rocks on the trail',
	'The rock plate in the {scarpa} provides adequate protection from sharp rocks on the trail',
	'The rock plate in the {scarpa} provides great protection from sharp rocks on the trail'
);

$descriptions['water_resistance'] = Array(
	'The {scarpa} performs badly in wet weather conditions, getting wet rapidly and taking long time to dry',
	'The {scarpa} provides a decent protection from splashes and puddles',
	'The {scarpa} offers great water protection. Not completely waterproof but dries quickly',
	'The {scarpa} is made of a water-proof material, ideal for extremely wet conditions'
);

$descriptions['cushioning_type'] = Array(
	'The {scarpa} is extremely responsive: provides energy return and speed over softness and shock absorption',	
	'The  {scarpa} is more responsive than plush: provides energy return and some shock absorption capabilities',	
	'The {scarpa} is a perfect mix of responsive and plush: good shock absorption plus energy return and speed',	
	'The {scarpa} is more plush than responsive: provides shock absorption and some energy return',	
	'The {scarpa} is plush: provides shock absorption and comfort over energy return and speed'
);

$descriptions['cushioning_amount'] = Array(
	'The {scarpa} offers little cushioning',
	'The {scarpa} offers just the right amount of cushioning',
	'The {scarpa} is maximally cushioned'
);

$descriptions['flexibility'] = Array(
	'The {scarpa} is quite rigid and does not flex easily',
	'The {scarpa} has a good mix between flexibility and structure',
	'The {scarpa} is extremely flexible at the expense of some structure'
);

$descriptions['stability'] = Array(
	'The {scarpa} is not stable: not indicated for runners that need support',
	'The {scarpa} has some inherent stability and support',
	'The {scarpa} offers great stability for runners who needs the most support for over-pronation or flat-feet'
);

$descriptions['sizing'] = Array(
	'The {scarpa} fits large: buy a full size smaller than usual',
	'The {scarpa} fits a little large: buy half a size smaller than ususal',
	'The {scarpa} has average sizing: buy the usual size',
	'The {scarpa} fits a little small: buy half a size bigger than usual',
	'The {scarpa} fits small: buy a full size bigger than usual'
);


function rsg_truncate_read_more($text, $chars = 0) {
	$text = rsg_truncate($text, $chars);
	$html = $text[0];
	if(strlen($text[1])>1) {
		$html .= '<span data-toggle="next">... <a href="#" class="read-more">Read more</a></span>';
		$html .= '<span class="d-none"> '.$text[1].'</span>';
	}
	return $html;
}

?>

<style>

.review-sections h2 + div.spacer + .wp-caption{
	margin-top: 0px;
}

.icon-play-video:before {
	background-image: url('<?=get_template_directory_uri()?>/images/icons/video-play.svg');
	background-repeat: no-repeat;
    display: inline-block;
    position: absolute;
	height: 0.50em;
    background-size: 100% 100%;
    z-index: 10;
    top: 50%;
    left: 50%;
    margin-left: -0.25em;
    margin-top: -0.25em;
	font-size: 6rem;
	color:#cc2127;
	content: '';
	width: 0.50em;
    text-align: center;
    opacity: 1;
    font-variant: normal;
    text-transform: none;
    line-height: 1em;
	
}

.icon-play-video {
	position: relative;
}

.icon-play-video.video-360:before {
	background-image: url('<?=get_template_directory_uri()?>/images/icons/video-360.svg');
}

#extra-specs a.read-more, .panel-inverted a.read-more {
	color:#80d5ff;text-decoration:underline
}

ul.procons {
	list-style-type: none;
}

ul.procons li {
	margin-left: 1.5rem;
    position: relative;
}

ul.procons li::before {
	display: block;
	content: "";
	width: 1rem;
	height:1rem;
	top: 0.35rem;
	position:absolute;
	left:-1.5rem;
}

ul.procons .pro::before {
	background: url(<?=get_template_directory_uri()?>/images/icons.svg#icon-pluscircle-view);
}

ul.procons .con::before {
	background: url(<?=get_template_directory_uri()?>/images/icons.svg#icon-minuscircle-view);
}


.rating-big-stars {
	font-size: 1.4rem;line-height: 1.2;color: #ddd;white-space:nowrap;
}

.rating-big-stars span {
	display:inline-block;width: 0.64em;
}

.rating-big-stars.yellow {
	display:inline-block;position: absolute;top: 0;left: 0;
}

.rating-big-stars.yellow span {
	color: #f99d1c;
}			



.gallery-link {
	white-space: nowrap;
	width: 100%;
	text-align: center;
	overflow:auto;
	margin: -8px 0px;
    padding-left: 40px;
    padding-right: 40px;
	-webkit-overflow-scrolling: touch;
	scrollbar-width: none; /* Firefox 64 */
	-ms-overflow-style: none; /* Internet Explorer 11 */
}

.gallery-link::-webkit-scrollbar { /** WebKit */
  display: none;
  width: 0 !important;
  height: 0 !important;
}

.gallery-link .swipebox img {
	border-radius:14px;
}

.gallery-container {
	margin: 0 -47px;
}

.slider-controls a {
	margin:0 10px;width: 9px;height: 8.8px;background-color: #fff;border-radius: 50%;
}

.slider-controls a.active {
	width: 13px;height: 13px;border: solid 2.5px #ffffff;background-color: #5b8fbc;
}

.main-img-full-width h1.page-title {
	font-size:calc(50px + (96 - 50) * ((100vw - 320px) / (1200 - 320)));
	line-height:1;
}

.rangeslider,
.rangeslider__fill {
  display: block;
  -moz-box-shadow: inset 0px 1px 3px rgba(0, 0, 0, 0.3);
  -webkit-box-shadow: inset 0px 1px 3px rgba(0, 0, 0, 0.3);
  box-shadow: inset 0px 1px 3px rgba(0, 0, 0, 0.3);
  -moz-border-radius: 10px;
  -webkit-border-radius: 10px;
  border-radius: 10px;
}

.rangeslider {
  background: #e6e6e6;
  position: relative;
}

.rangeslider--horizontal {
  height: 0.5rem;
  width: 100%;
}


.rangeslider--disabled {
  opacity: 0.4;
}

.rangeslider__fill {
  background: #00ff00;
  position: absolute;
}
.rangeslider--horizontal .rangeslider__fill {
  top: 0;
  height: 100%;
}

.rangeslider__handle {
  background: white;
  border: 1px solid #ccc;
  cursor: pointer;
  display: inline-block;
  width: 64px;
  height: 40px;
  position: absolute;
  box-shadow: 0 0 8px rgba(0, 0, 0, 0.3);
  border-radius: 20%;
}
.rangeslider__handle div {
  content: "";
  display: block;
  width: 80%;
  height: 1rem;
  line-height:1rem;
  margin: auto;
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  border-left: 5px solid;
	border-right: 5px solid;
	border-style: double;
	border-top: 0px;
	border-bottom: 0px;
	border-color: #EAEAEA;
	font-weight:bold;
}
.rangeslider__handle:active, .rangeslider--active .rangeslider__handle {
  background-image: url('data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4gPHN2ZyB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PGxpbmVhckdyYWRpZW50IGlkPSJncmFkIiBncmFkaWVudFVuaXRzPSJvYmplY3RCb3VuZGluZ0JveCIgeDE9IjAuNSIgeTE9IjAuMCIgeDI9IjAuNSIgeTI9IjEuMCI+PHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iIzAwMDAwMCIgc3RvcC1vcGFjaXR5PSIwLjEiLz48c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiMwMDAwMDAiIHN0b3Atb3BhY2l0eT0iMC4xMiIvPjwvbGluZWFyR3JhZGllbnQ+PC9kZWZzPjxyZWN0IHg9IjAiIHk9IjAiIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIGZpbGw9InVybCgjZ3JhZCkiIC8+PC9zdmc+IA==');
  background-size: 100%;
  background-image: -webkit-gradient(linear, 50% 0%, 50% 100%, color-stop(0%, rgba(0, 0, 0, 0.1)), color-stop(100%, rgba(0, 0, 0, 0.12)));
  background-image: -moz-linear-gradient(rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.12));
  background-image: -webkit-linear-gradient(rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.12));
  background-image: linear-gradient(rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.12));
}
.rangeslider--horizontal .rangeslider__handle {
  top: -16px;
  text-align: center;
  touch-action: pan-y;
  -ms-touch-action: pan-y;
}
.rangeslider--vertical .rangeslider__handle {
  left: -10px;
  touch-action: pan-x;
  -ms-touch-action: pan-x;
}

input[type="range"]:focus + .rangeslider .rangeslider__handle {
  -moz-box-shadow: 0 0 8px rgba(255, 0, 255, 0.9);
  -webkit-box-shadow: 0 0 8px rgba(255, 0, 255, 0.9);
  box-shadow: 0 0 8px rgba(255, 0, 255, 0.9);
}


.comment-respond label {
	margin-bottom: 2rem;
}

.panel-heading.lowest-price {
	position:relative;
	color:#fff;
	background-image: url(<?=get_template_directory_uri()?>/images/specs-icons/lowest-price.svg);
    background-repeat: no-repeat;
    background-position: 30% 50%;
}

.panel-heading.lowest-price::before {
	content: "";
    width: 100%;
    height: 100%;
    display: block;
    background-image: url(<?=get_template_directory_uri()?>/images/specs-icons/lowest-price.svg);
    position: absolute;
    background-repeat: no-repeat;
    top: 0;
    left: 0;
    z-index: 0;
    background-position: 30% 50%;
    transform: scaleX(-1);
}


@media (min-width: 768px) {
	
	.review-sections{
		max-width: 92%;
	}

	.review-sections h2{
		margin-right: -8%;
	}

	.review-sections .wp-caption{
		max-width: 108% !important;
	}
	
	.gallery-link {
		position:absolute;
		bottom: 32px;
		padding:0;
	}
	
	.gallery-link .swipebox img {
		border: 2px solid white;
	}
	
	.gallery-container {
		margin: 0;
	}
	
	.gallery-link img {
		width: 49px;
	}
}

@media (min-width: 1200px) {
	.main-img-full-width h1.page-title {
		font-size: 96px;
	}
}
</style>

<script>
$(document).ready(function() {

	
	$("[data-toggle='next']").click(function(e){
		e.preventDefault();
		e.stopPropagation();
		$(this).hide();
		$(this).next('.d-none').removeClass('d-none').addClass('d-inline');
	});
	
	$( ".gallery-link" ).scroll(function() {
		scrollWidth = $(this)[0].scrollWidth;
		thumbnails = $(this).children('a');
		
		
		
		singleWidth = thumbnails.first().outerWidth( true );
		
		sliderControl = $(this).siblings('.slider-controls');
		
		
		var i;
		for (i = 0; i < thumbnails.length; i++) {
			//console.log($(this).scrollLeft() >= singleWidth * i && $(this).scrollLeft() < singleWidth * (i+1) );
			//console.log($(this).outerWidth( true )+$(this).scrollLeft()>singleWidth * (i+1));
			right  = $(this).outerWidth( true )+$(this).scrollLeft();
			
			//console.log(singleWidth * (i)+"--"+singleWidth * (i+1));
			//console.log($(this).scrollLeft() +"--"+ right);
			
			if( ( $(this).scrollLeft() < singleWidth * (i+1) - (singleWidth)/2 ) && right > singleWidth * (i+1)) {
				sliderControl.children('a').removeClass('active');
				sliderControl.children('a:eq('+i+')').addClass('active');
			}
			/*
			if($(this).scrollLeft() >= singleWidth * i && $(this).scrollLeft() < singleWidth * (i+1) )
				sliderControl.children('a:eq('+i+')').addClass('active');
			*/
		}
		
			
		//$(this).siblings('.slider-controls').hide();
	})
	
	
	$('.price-gender-switch a.btn').click(function(e){
		e.preventDefault();
		$(this).addClass('active').siblings().removeClass('active');
		var row_visible = $(this).closest('.panel-body').siblings('.row').find('.row:visible');
		$(this).closest('.panel-body').siblings('.row').find('.row:hidden').fadeIn(500);
		row_visible.hide();
		return false;
	});
	
	$("#toc-select").change(function(){
		window.location.hash = $(this).val();
	});
	
	if($( window ).width() > 992 ) {
		var observer_sections = new IntersectionObserver(observerSectionsCallback);
		
		function observerSectionsCallback(entries, observer) {
			entries.forEach(entry => {
				if(entry['isIntersecting'] === true ) {
						console.log(entry.target.id);
						//if(entries[0]['intersectionRatio'] > 0.5)
						$('#navbar-toc a').removeClass('btn');
						$('#navbar-toc a[href="#'+entry.target.id+'"]').addClass('btn');
						
					
				}
				else {
					
				}
			});
			
		};

		let target = 'div.review-sections > div.review-section';
		document.querySelectorAll(target).forEach((i) => {
			if (i) {
				observer_sections.observe(i);
			}
		});
		
		var observer_sections_parent = new IntersectionObserver(function(entries) { 
			entries.forEach(entry => {
			if(entry['isIntersecting'] === true) {
				$('#navbar-toc:hidden').show('slow');
			}
				else {
					$('#navbar-toc a').removeClass('btn');
					$('#navbar-toc:visible').hide('fast');
				}
			});
			
		});

		document.querySelectorAll('div.review-sections').forEach((i) => {
			if (i) {
				observer_sections_parent.observe(i);
			}
		});
	}
	
});


$(window).scroll(function(e){
	if (e.originalEvent && $( window ).width() < 992 ) {
					/*
					$('#nav_where_to_buy_bottom:visible').slideUp();
					
					clearTimeout( $.data( this, "scrollCheck" ) );
					if( isScrolledIntoView($('#main-row')) ) {
						$.data( this, "scrollCheck", setTimeout(function() {
							$('#nav_where_to_buy_bottom').slideDown();
						}, 1000) );
					}

					$('#nav_where_to_buy_bottom:visible').slideUp();
					clearTimeout( $.data( this, "scrollCheck" ) );
					
					if( isScrolledIntoView($('#sticky-container-2020')) ) {
						$.data( this, "scrollCheck", setTimeout(function() {
							$('#nav_where_to_buy_bottom:hidden').slideDown();
						}, 1000) );	
					}
					else {
						$('#nav_where_to_buy_bottom:visible').slideUp();
					}
					*/
					
					
					
						var scrollTop = $(window).scrollTop();
						if (scrollTop > 120) { 
							$('.navbar.navbar-default.navbar-fixed-top:visible').hide('slow');
							$('#best-price-mobile:hidden').show('slow');
						}
						else {
							$('.navbar.navbar-default.navbar-fixed-top:hidden').show('slow');
							$('#best-price-mobile:visible').hide('slow');
						}
					
				}
});





</script>

<?php


global $pricecomparison;
global $price_json;
global $price_json_women;

global $custom;
$custom = get_post_custom();

global $userviewed;
$userviewed = rand(15,39);


$weight = get_field('weight_oz');
$heeldrop = get_field('heel_drop_mm');



$verdict = get_field( "verdict" );

$reccomended_for = get_field( "who_is_it_for" );

$stars = get_field( "stars" );

$rrp = get_geo_rrp();

$shoe_name = get_field("model");

$brand = get_field('brand');

$battery_life_on_gps = get_field('battery_life_on_gps');

$multi_sport_field = get_field_object('multi-sport');

$multi_sport = $multi_sport_field['choices'][ get_field('multi-sport') ];

$water_resistance_field = get_field_object('water_resistance');

$water_resistance = $water_resistance_field['choices'][ get_field('water_resistance') ];

$music_field = get_field_object('music');

$music = $music_field['choices'][ get_field('music') ];

$uploadable_routes_field = get_field_object('uploadable_routes');

$uploadable_routes = $uploadable_routes_field['choices'][ get_field('uploadable_routes') ];

$hr_monitor_field = get_field_object('hr_monitor');

$hr_monitor = $hr_monitor_field['choices'][ get_field('hr_monitor') ];

$activity_tracking_field = get_field_object('24h_activity_tracking');

$activity_tracking = $activity_tracking_field['choices'][ get_field('24h_activity_tracking') ];

$vote = get_field('vote');

$sections_no_toc = Array('who_should_buy_it', 'who_should_not_buy_it_');

$sections = Array('introduction', 'impressions', 'comfort_and_fit', 'good_for_running', 'good_for_hiit__crossfit_', 'good_for_weight_lifting', 'good_for_dance_classes_', 'good_for_general_gym_use', 'good_for_other_types_of_training', 'conclusions');

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

if( is_array(get_the_terms($post->ID, 'shoes_type'))) {
	$shoes_types = array_values(get_the_terms($post->ID, 'shoes_type'));
	$shoes_type = $shoes_types[0];
}


array_walk_recursive(
    $descriptions,
    function (&$value) use ($shoe_name) {
        $value = str_replace('{scarpa}' , $shoe_name , $value);
    }
);





$pricebox = rsg_build_datafeed_query(get_field('ds_query'));
$pricebox_women = rsg_build_datafeed_query(get_field('ds_query'),null,true);


//echo $pricebox;

//$pricecomparison = html_entity_decode($custom["pricecomparison"][0]);

//$lowest_price_str = "";
//$lowest_price_str_shop_now = "";
$price_json = "";

/*TESTING-TO BE REMOVED*/
//$price_json = file_get_contents(get_template_directory_uri().'/json.js');

if (function_exists('dstudio')) {
	/*
	if(RSG_get_country_code() == 'US' || RSG_get_country_code() == 'NULL')
		$override_tag = 'rsg-overview-20';
	*/
	//$lowest_price_str = dstudio(array('query' => $pricebox , 'pricebox' => 'box-up-price1'),null);
	//$lowest_price_str_shop_now = dstudio(array('query' => $pricebox , 'pricebox' => 'box-up-label'),null);
	/*
	if($post->ID == 11064)
		$price_json = dstudio(array('query' => $pricebox , 'pricebox' => 'newrsg-json-rrs-2014'),null);
	else
	*/
		//$price_json = dstudio(array('query' => $pricebox , 'pricebox' => 'newrsg-json'),null);
		
		$price_json = dstudio(array('query' => $pricebox , 'pricebox' => 'newrsg-json', 'countrycode' => RSG_get_country_code()),null);
		$price_json = rsg_datafeed_json($price_json);
		
		$price_json_women = dstudio(array('query' => $pricebox_women , 'pricebox' => 'newrsg-json', 'countrycode' => RSG_get_country_code() ),null);
		$price_json_women = rsg_datafeed_json($price_json_women);
		
}



if ( has_post_thumbnail() )	{
	//$thumbnail_image = get_posts(array('p' => get_post_thumbnail_id(), 'post_type' => 'attachment'));
	$thumbnail_image = get_the_post_thumbnail_url($post->ID, 'full'); 
	
	$main_img_sm = rsg_image_resize($thumbnail_image,480,285);
	$main_img = rsg_image_resize($thumbnail_image,1280,760);
	//var_dump($thumbnail_image);

}


//$pros = preg_split('#(<br/>|<br>|<br />)#', $pros );
//$cons = preg_split('#(<br/>|<br>|<br />)#', $cons);

$keywords_i = stripcslashes(get_post_meta($post->ID, "keywords", true));
$keywords_i = str_replace('"', '', $keywords_i);


$attachment = $thumbnail_image;

/*
$amazon_item = amazon_item_lookup(get_field( "amazon_link" ) , "rsg-gps-single-20" );
$amazon_item = $amazon_item->Item;
$link = $amazon_item->DetailPageURL;
$price = amazon_item_getDisplayPrice($amazon_item);
$part_number = $amazon_item->ItemAttributes->PartNumber;
$ListPrice = $amazon_item->ItemAttributes->ListPrice->Amount / 100;	
$rrp = sprintf("%01.2f", $ListPrice);
*/
//var_dump($amazon_item);

$link_format = 'rel="nofollow sponsored" target="_blank" onclick="ga(\'send\', \'event\', \'Aff_Link\', \'Review-2019-In-Content\', \''.$shoe_name.'\');"';


$questions_args = array(
	'post_id' => $post->ID,   // Use post_id, not post_ID
	'type'   => 'question', // Return only the count
	'status'  => '1'
);
$questions = get_comments( $questions_args );


$args = array(
  'post_id' => $post->ID,
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





/*comparison */

$first_shoe_ID = get_field('first_shoe');; 
$second_shoe_ID = $post->ID;
$third_shoe_ID = get_field('third_shoe');;

$custom_1 = get_post_custom($first_shoe_ID);
$custom_2 = get_post_custom($second_shoe_ID);
$custom_3 = get_post_custom($second_shoe_ID);

$thumbnail_1[0] = get_field( 'professional_image', $first_shoe_ID );
$thumbnail_1_resized = rsg_image_resize($thumbnail_1[0],200,90);

$thumbnail_2[0] = get_field( 'professional_image', $second_shoe_ID );
$thumbnail_2_resized = rsg_image_resize($thumbnail_2[0],200,90);

$thumbnail_3[0] = get_field( 'professional_image', $third_shoe_ID );
$thumbnail_3_resized = rsg_image_resize($thumbnail_3[0],200,90);


$shoes_type_1 = get_the_terms($first_shoe_ID, 'shoes_type');
$shoes_type_2 = get_the_terms($second_shoe_ID, 'shoes_type');
$shoes_type_3 = get_the_terms($third_shoe_ID, 'shoes_type');

$brand_1 = get_the_terms($first_shoe_ID, 'brands');
$brand_2 = get_the_terms($second_shoe_ID, 'brands');
$brand_3 = get_the_terms($third_shoe_ID, 'brands');

$permalink_1 = get_permalink($first_shoe_ID);
$permalink_2 = get_permalink($second_shoe_ID);
$permalink_3 = get_permalink($third_shoe_ID);


$args = array(
  'post_id' => $first_shoe_ID,
  'fields' => 'ids',
  'meta_key' => 'rate',
  'status' => 'approve',
);
$rated_comments = get_comments($args);
$rate = 0;
foreach($rated_comments as $rated_comment) {
	$rate = $rate + get_comment_meta( $rated_comment, 'rate', true );
}

$vote_comments_nr_1 = count($rated_comments);
//echo $vote_comments_nr;
$vote_comments_total = $rate;

if($vote_comments_total >= 1 && $vote_comments_nr_1 > 0)
	$user_vote_percentage_1 = round($vote_comments_total/$vote_comments_nr_1, 1);
else
	$user_vote_percentage_1 = 0;
if($user_vote_percentage_1 > 10)
	$user_vote_percentage_1 = 10;


$args = array(
  'post_id' => $second_shoe_ID,
  'fields' => 'ids',
  'meta_key' => 'rate',
  'status' => 'approve',
);
$rated_comments = get_comments($args);
$rate = 0;
foreach($rated_comments as $rated_comment) {
	$rate = $rate + get_comment_meta( $rated_comment, 'rate', true );
}

$vote_comments_nr_2 = count($rated_comments);
//echo $vote_comments_nr;
$vote_comments_total = $rate;


if($vote_comments_total >= 1 && $vote_comments_nr_2 > 0)
	$user_vote_percentage_2 = round($vote_comments_total/$vote_comments_nr_2, 1);
else
	$user_vote_percentage_2 = 0;
if($user_vote_percentage_2 > 10)
	$user_vote_percentage_2 = 10;

$args = array(
  'post_id' => $third_shoe_ID,
  'fields' => 'ids',
  'meta_key' => 'rate',
  'status' => 'approve',
);
$rated_comments = get_comments($args);
$rate = 0;
foreach($rated_comments as $rated_comment) {
	$rate = $rate + get_comment_meta( $rated_comment, 'rate', true );
}

$vote_comments_nr_3 = count($rated_comments);
//echo $vote_comments_nr;
$vote_comments_total = $rate;


if($vote_comments_total >= 1 && $vote_comments_nr_3 > 0)
	$user_vote_percentage_3 = round($vote_comments_total/$vote_comments_nr_3, 1);
else
	$user_vote_percentage_3 = 0;
if($user_vote_percentage_3 > 10)
	$user_vote_percentage_3 = 10;

$pricebox = rsg_build_datafeed_query(get_field('ds_query', $first_shoe_ID),1,false,$first_shoe_ID);
$price_json_1 = NULL;
if (function_exists('dstudio')) {
	$price_json_1 = dstudio(array('query' => $pricebox , 'pricebox' => 'newrsg-json', 'countrycode' => RSG_get_country_code()),null);
	$price_json_1 = rsg_datafeed_json($price_json_1);
}

if(isset($price_json_1->datafeed->items[0])) {
	$item = $price_json_1->datafeed->items[0];
	$merchant_name_1 = $item->MERCHANT_NAME;
	$deep_link_1 = $item->DEEP_LINK;
	$currency_prefix_1 = $item->CURRENCY_PREFIX;
	$display_price_1 = $item->DISPLAY_PRICE;
}


if(isset($item->MERCHANT_LOGO) && $item->MERCHANT_LOGO != '') {
	$logo_html_1 = '<img src="'.rsg_image_resize_ext($item->MERCHANT_LOGO,88,26,'/dfs-images-cache/').'" />';
}

if(isset($deep_link_1))
	$link_html_1 = 'rel="nofollow sponsored" target="_blank" onClick="ga(\'send\', \'event\', \'Aff_Link\', \'Comparison-Page\', window.location.href)" href="'.$deep_link_1.'"';

$pricebox = rsg_build_datafeed_query(get_field('ds_query', $second_shoe_ID),1,false,$second_shoe_ID);
$price_json_2 = NULL;
if (function_exists('dstudio')) {
	$price_json_2 = dstudio(array('query' => $pricebox , 'pricebox' => 'newrsg-json', 'countrycode' => RSG_get_country_code() ),null);
	$price_json_2 = rsg_datafeed_json($price_json_2);
}

if(isset($price_json_2->datafeed->items[0])) {
	$item = $price_json_2->datafeed->items[0];
	$merchant_name_2 = $item->MERCHANT_NAME;
	$deep_link_2 = $item->DEEP_LINK;
	$currency_prefix_2 = $item->CURRENCY_PREFIX;
	$display_price_2 = $item->DISPLAY_PRICE;
}

if(isset($deep_link_2))
	$link_html_2 = 'rel="nofollow sponsored" target="_blank" onClick="ga(\'send\', \'event\', \'Aff_Link\', \'Comparison-Page\', window.location.href)" href="'.$deep_link_2.'"';

$pricebox = rsg_build_datafeed_query(get_field('ds_query', $third_shoe_ID),1,false,$third_shoe_ID);
$price_json_3 = NULL;
if (function_exists('dstudio')) {
	$price_json_3 = dstudio(array('query' => $pricebox , 'pricebox' => 'newrsg-json', 'countrycode' => RSG_get_country_code() ),null);
	$price_json_3 = rsg_datafeed_json($price_json_3);
}

if(isset($price_json_3->datafeed->items[0])) {
	$item = $price_json_3->datafeed->items[0];
	$merchant_name_3 = $item->MERCHANT_NAME;
	$deep_link_3 = $item->DEEP_LINK;
	$currency_prefix_3 = $item->CURRENCY_PREFIX;
	$display_price_3 = $item->DISPLAY_PRICE;
}


if(isset($item->MERCHANT_LOGO) && $item->MERCHANT_LOGO != '') {
	$logo_html_3 = '<img src="'.rsg_image_resize_ext($item->MERCHANT_LOGO,88,26,'/dfs-images-cache/').'" />';
}

if(isset($deep_link_3))
	$link_html_3 = 'rel="nofollow sponsored" target="_blank" onClick="ga(\'send\', \'event\', \'Aff_Link\', \'Comparison-Page\', window.location.href)" href="'.$deep_link_3.'"';
?>

<style>
.table-comparison {
	font-weight: 500;
}

.table-comparison > .row > .col, .table-comparison > .row > [class*="col-"] {
	padding-top: 24px;
	padding-bottom: 24px;
}

.table-comparison > .row > div:first-of-type {
	font-weight: 600;
	font-family: var(--font-family-header);
	background: var(--pale-grey);
	text-align: center;
	font-size: 80%;
}

.table-comparison > .row {
	margin-right: 0;
}

.table-comparison > .row:not(:first-of-type) > div:not(:first-of-type) {
	border-top: 1px solid var(--light-blue-grey);
	text-align: center;
}

.table-comparison > .row:not(:first-of-type) > div:nth-child(2) {
    border-left: 1px solid var(--light-blue-grey);
}

.table-comparison > .row:not(:first-of-type) > div:nth-child(4) {
    border-right: 1px solid var(--light-blue-grey);
}

.table-comparison > .row:nth-child(2) > div:nth-child(2) {
	border-top-left-radius: 1rem;
}

.table-comparison > .row:nth-child(2) > div:last-of-type {
	border-top-right-radius: 1rem;
}

.table-comparison > .row:last-of-type > div:not(:first-of-type) {
	border-bottom: 1px solid var(--light-blue-grey);
}

.table-comparison > .row:last-of-type > div:nth-child(2) {
	border-bottom-left-radius: 1rem;
}

.table-comparison > .row:last-of-type > div:last-of-type {
	border-bottom-right-radius: 1rem;
}

.table-comparison > .row > div:nth-child(3) {
	background-color: rgba(15, 112, 196, 0.05);
}

.table-comparison > .row:first-of-type > div:nth-child(3) {
	border-radius: 1rem 1rem 0% 0%;
}

@media (min-width: 768px) {
	.table-comparison > .row > div:first-of-type {
		background: none;
		text-align: left;
	}
}
</style>
				
				
<div class="navbar navbar-fixed-top" id="navbar-toc" style="background:#fff;border-bottom: 1px solid #CED4DA;display:none">		
	<div class="container">	
		<div class="row">
			<div class="col small">
				<ul class="nav navbar-nav">
					<?php

					/*
					if( have_rows('sections') ):

						// Loop through rows.
						while( have_rows('sections') ) : the_row();

							?>
							<li><a href="#<?= strtolower(urlencode(get_sub_field('section_toc_title'))) ?>" style="font-family:var(--font-family-body);color:var(--font-color-body)"><?= get_sub_field('section_toc_title') ?></a><li>
							
							<?php
						endwhile;

					endif;
					*/
					foreach ($sections as $section):
						if(get_field($section)):
							$field_object = get_field_object($section);
							?>
							<li><a href="#<?= $field_object['name'] ?>" style="font-family:var(--font-family-body);color:var(--font-color-body)"><?= $field_object['label'] ?></a><li>
							
							<?php
						endif;
					endforeach;
					?>
				</ul>
			</div>
		</div>
	</div>
</div>
				
				


<div class="container">
	<div class="row">
		<div class="col small">
			<div class="spacer"></div>
				<span >
				  <span  class="d-none d-md-inline">
					
						<a  href="<?=  get_bloginfo('url') .'/' ?>">
							<span itemprop="name">Home</span>
							<meta itemprop="position" content="1" />
						</a>
						
				  </span>
				  <i class="arrow right d-none d-md-inline-block" style="margin-left:1em;margin-right: 1em;"></i>
				  <i class="arrow left d-inline-block d-md-none" style="margin-left:1em;margin-right: 1em;"></i>
				  <span  class="d-inline-block d-md-none">
					
						<a href="<?=  get_bloginfo('url') . '/reviews/' ?>">
							All running shoes <span itemprop="name">Reviews</span>
							<meta itemprop="position" content="2" />
						</a>
				  </span>
				  <i class="arrow right d-none" style="margin-left:1em;margin-right: 1em;"></i>
				  <span  class="d-none">
						<a  href="<?= get_bloginfo('url') . '/reviews/all/'. $shoes_type->slug . '/' ?>">
							<i class="arrow left d-md-none"></i>
							<span itemprop="name"><?= $shoes_type->name ?> shoe reviews</span>
							<span itemprop="position" content="3" />
						</a>					
				  </span>
				  <span class="btn-link disabled d-none d-md-inline">...</span>
				  <i class="arrow right d-none d-md-inline-block" style="margin-left:1em;margin-right: 1em;"></i>
				  <span class="btn-link disabled d-none d-md-inline" ><?= $brand->name.' '.$shoe_name ?> expert review</span>
				  
				</span>
			<div class="spacer-sm"></div>
		</div>
	</div>
</div>

<?php
$images = get_field('gallery');
$shoe_video = get_field('video');
$shoe_360 = get_field('360');

$starting_image_index = 1;
$images_count = count($images);
if($shoe_video) {
}
?>
<div class="container">
	<div class="row">
		<div class="col-12 order-2 order-lg-1">
			<h1 class="" style="">
				<?= $brand->name.' '.$shoe_name ?> review
			</h1>
			<div class="spacer"></div>
			<div class="d-flex flex-wrap flex-lg-nowrap" style="gap:1rem">
				<div class="panel small flex-fill" style="color:#2189E1;background:#2189E114;padding:0.35rem 1rem;font-weight:bold;"><span class="big"><?= $vote ?></span> <u style="vertical-align:0.20rem">expert score</u></div>
				<div class="panel small flex-fill" style="background:#0000000d;padding:0.35rem 1rem;font-weight:bold;"><span class="big"><?= $user_vote_percentage ?></span> <u style="vertical-align:0.20rem">user's score</u></div>
				<span class="d-lg-flex align-items-center text-center small text-muted" style="border-radius: 0.625rem;border:1px solid #EAEAEA;padding:0.2rem 1rem;font-style:italic">As editorial policy, we do not accept free samples. We purchased this pair at&nbsp;<a href="<? the_field('purchased_from_url')?>" target="_blank"><? the_field('purchased_from_name') ?></a>&nbsp;with our own money.</span>
			</div>
			<div class="spacer"></div>
		</div>
		<div class="col-12 order-1 order-lg-2">
			<div class="row" style="margin-right:-8px;margin-left:-8px">
				<div class="col-md-6" style="padding-right:8px;padding-left:8px">
					<a href="<?php echo rsg_image_resize($attachment,900,620); ?>" rel="review_box_imgs" class="swipebox" title="Photo 1 / <?=count($images)?>">
						<picture id="header-img" style="border-radius: 0.625rem;">
							<img class="full-image" srcset="<?php echo rsg_image_resize($attachment,500,320); ?>   500w,
							<?php echo rsg_image_resize($attachment,560,410); ?> 560w"
							src="<?php echo rsg_image_resize($attachment,560,410); ?>" sizes=" (min-width: 481px) 560px, 500px" class="" alt="<?= get_post_meta(get_post_thumbnail_id($post_id), '_wp_attachment_image_alt', true); ?>">
						</picture>
					</a>
					<div class="spacer-sm d-md-none"></div>
				</div>
				<div class="col-4 col-md-3" style="padding-right:8px;padding-left:8px">
					
						<?php
						for($i = $starting_image_index; $i < $starting_image_index+2; ++$i):
							if($i == $starting_image_index && $shoe_video) {
								$linked = $shoe_video;
								$extra_class = "icon-play-video";
							}
							else {
								$linked = rsg_image_resize($images[$i]['url'],900,620);
								$extra_class = "";
							}
						?>
							
								<a href="<?php echo $linked; ?>" rel="review_box_imgs" class="swipebox <?=$extra_class?>" title="Photo <?=$i + 1?> / <?=$images_count?>">
								<picture class="<?= ($i > $starting_image_index) ? 'd-none d-md-block' : '' ?>" style="border-radius: 0.625rem;">
												
									
									<img class="full-image" srcset="<?php echo rsg_image_resize($images[$i]['url'],500,320); ?>   500w,
									<?php echo rsg_image_resize($images[$i]['url'],560,405); ?> 560w"
									src="<?php echo rsg_image_resize($images[$i]['url'],560,405); ?>" sizes=" (min-width: 481px) 560px, 500px" class="" alt="<?= get_post_meta(get_post_thumbnail_id($post_id), '_wp_attachment_image_alt', true); ?>">
									
									
								</picture>
								</a>
								<div class="spacer-sm"></div>
							
						<?php
						endfor;
						?>
					
					
					
				</div>
				<div class="col-8 col-md-3" style="padding-right:8px;padding-left:8px">
					<div class="row" style="margin-right:-8px;margin-left:-8px">
					<?php
					for($i = $starting_image_index +2 ; $i < $starting_image_index + 4; ++$i):
						if($i == $starting_image_index+2 && $shoe_360) {
							$linked = $shoe_360;
							$extra_class = "icon-play-video video-360";
						}
						else {
							$linked = rsg_image_resize($images[$i]['url'],900,620);
							$extra_class = "";
						}
					?>
						<div class="col-6 col-md-12" style="padding-right:8px;padding-left:8px">
							<a href="<?php echo $linked; ?>" rel="review_box_imgs" class="swipebox <?=$extra_class?>" title="Photo <?=$i + 1?> / <?=$images_count?>">
							<picture style="border-radius: 0.625rem;">
											
								
								<img class="full-image" srcset="<?php echo rsg_image_resize($images[$i]['url'],500,320); ?>   500w,
								<?php echo rsg_image_resize($images[$i]['url'],560,405); ?> 560w"
								src="<?php echo rsg_image_resize($images[$i]['url'],560,405); ?>" sizes=" (min-width: 481px) 560px, 500px" class="" alt="<?= get_post_meta(get_post_thumbnail_id($post_id), '_wp_attachment_image_alt', true); ?>">
								
								<?php
								if($i == $starting_image_index + 2):
								?>
									
								<?php
								endif;
								?>
								<?php
								if($i == $starting_image_index + 3):
								?>
									<div class="btn btn-info d-md-none" style="position:absolute;bottom:0rem;right:0rem;transform: translate(-50%, -50%);padding:0.5rem 0.75rem">1/<?=count($images)?></div>
									<div class="btn btn-info d-none d-md-inline-block" style="position:absolute;bottom:1rem;right:1rem"><span class="d-none d-lg-inline">See all </span><?=count($images)?> photos</div>
								<?php
								endif;
								?>
							</picture>
							</a>
							<div class="spacer-sm"></div>
						</div>
					<?php
					endfor;
					?>
					</div>
				</div>
				<?php
				for($i = $starting_image_index + 4; $i < count($images); ++$i):
				?>
					<a href="<?php echo rsg_image_resize($images[$i]['url'],900,620); ?>" rel="review_box_imgs" class="swipebox d-none" title="Photo <?=$i + 1?> / <?=$images_count?>"></a>
					
				<?php
				endfor;
				?>
			</div>	
		</div>
		<!--
		<div class="col-12 order-1 order-lg-2">
			<div class="row" style="margin-right:-8px;margin-left:-8px">
				<div class="col-md-6" style="padding-right:8px;padding-left:8px">
					<a href="<?php echo rsg_image_resize($attachment,900,620); ?>" rel="review_box_imgs" class="swipebox" title="Photo 1 / <?=count($images)?>">
						<picture id="header-img" class="progressive-picture" style="border-radius: 0.625rem;">
											
							<?php //echo base64_encode(file_get_contents(rsg_image_resize($image['sizes']['large'],23,14,true,true,20 ))) ?>
							<img src="<?php echo rsg_image_resize($attachment,56,41,true,true,20 ); ?>" class="" alt="" style="width:100%;-webkit-filter: blur(1px);filter: blur(1px);">
							
							<img class="full-image" data-srcset="<?php echo rsg_image_resize($attachment,500,320); ?>   500w,
							<?php echo rsg_image_resize($attachment,560,410); ?> 560w"
							data-src="<?php echo rsg_image_resize($attachment,560,410); ?>" sizes=" (min-width: 481px) 560px, 500px" class="" alt="<?= get_post_meta(get_post_thumbnail_id($post_id), '_wp_attachment_image_alt', true); ?>">
							
							<noscript>
							  <img itemprop="image" src="<?php echo rsg_image_resize($attachment,500,320); ?>" class="" alt="<?= get_post_meta(get_post_thumbnail_id($post_id), '_wp_attachment_image_alt', true); ?>">
							</noscript>
						</picture>
					</a>
					<div class="spacer-sm d-md-none"></div>
				</div>
				<div class="col-4 col-md-3" style="padding-right:8px;padding-left:8px">
					
						<?php
						for($i = $starting_image_index; $i < $starting_image_index+2; ++$i):
							if($i == $starting_image_index && $shoe_video) {
								$linked = $shoe_video;
								$extra_class = "icon-play-video";
							}
							else {
								$linked = rsg_image_resize($images[$i]['url'],900,620);
								$extra_class = "";
							}
						?>
							
								<a href="<?php echo $linked; ?>" rel="review_box_imgs" class="swipebox <?=$extra_class?>" title="Photo <?=$i + 1?> / <?=$images_count?>">
								<picture class="progressive-picture <?= ($i > $starting_image_index) ? 'd-none d-md-block' : '' ?>" style="border-radius: 0.625rem;">
												
									<?php //echo base64_encode(file_get_contents(rsg_image_resize($image['sizes']['large'],23,14,true,true,20 ))) ?>
									<img src="<?php echo rsg_image_resize($images[$i]['url'],56,40.5,true,true,20 ); ?>" class="" alt="" style="width:100%;-webkit-filter: blur(1px);filter: blur(1px);aspect-ratio: 56 / 40.5;">
									
									<img class="full-image" data-srcset="<?php echo rsg_image_resize($images[$i]['url'],500,320); ?>   500w,
									<?php echo rsg_image_resize($images[$i]['url'],560,405); ?> 560w"
									data-src="<?php echo rsg_image_resize($images[$i]['url'],560,405); ?>" sizes=" (min-width: 481px) 560px, 500px" class="" alt="<?= get_post_meta(get_post_thumbnail_id($post_id), '_wp_attachment_image_alt', true); ?>">
									
									<noscript>
									  <img itemprop="image" src="<?php echo rsg_image_resize($images[$i]['url'],500,320); ?>" class="" alt="<?= get_post_meta(get_post_thumbnail_id($post_id), '_wp_attachment_image_alt', true); ?>">
									</noscript>
								</picture>
								</a>
								<div class="spacer-sm"></div>
							
						<?php
						endfor;
						?>
					
					
					
				</div>
				<div class="col-8 col-md-3" style="padding-right:8px;padding-left:8px">
					<div class="row" style="margin-right:-8px;margin-left:-8px">
					<?php
					for($i = $starting_image_index +2 ; $i < $starting_image_index + 4; ++$i):
						if($i == $starting_image_index+2 && $shoe_360) {
							$linked = $shoe_360;
							$extra_class = "icon-play-video video-360";
						}
						else {
							$linked = rsg_image_resize($images[$i]['url'],900,620);
							$extra_class = "";
						}
					?>
						<div class="col-6 col-md-12" style="padding-right:8px;padding-left:8px">
							<a href="<?php echo $linked; ?>" rel="review_box_imgs" class="swipebox <?=$extra_class?>" title="Photo <?=$i + 1?> / <?=$images_count?>">
							<picture class="progressive-picture" style="border-radius: 0.625rem;">
											
								<?php //echo base64_encode(file_get_contents(rsg_image_resize($image['sizes']['large'],23,14,true,true,20 ))) ?>
								<img src="<?php echo rsg_image_resize($images[$i]['url'],56,40.5,true,true,20 ); ?>" class="" alt="" style="width:100%;-webkit-filter: blur(1px);filter: blur(1px);aspect-ratio: 56 / 40.5;">
								
								<img class="full-image" data-srcset="<?php echo rsg_image_resize($images[$i]['url'],500,320); ?>   500w,
								<?php echo rsg_image_resize($images[$i]['url'],560,405); ?> 560w"
								data-src="<?php echo rsg_image_resize($images[$i]['url'],560,405); ?>" sizes=" (min-width: 481px) 560px, 500px" class="" alt="<?= get_post_meta(get_post_thumbnail_id($post_id), '_wp_attachment_image_alt', true); ?>">
								
								<noscript>
								  <img itemprop="image" src="<?php echo rsg_image_resize($images[$i]['url'],500,320); ?>" class="" alt="<?= get_post_meta(get_post_thumbnail_id($post_id), '_wp_attachment_image_alt', true); ?>">
								</noscript>
								<?php
								if($i == $starting_image_index + 2):
								?>
									
								<?php
								endif;
								?>
								<?php
								if($i == $starting_image_index + 3):
								?>
									<div class="btn btn-info d-md-none" style="position:absolute;bottom:0rem;right:0rem;transform: translate(-50%, -50%);padding:0.5rem 0.75rem">1/<?=count($images)?></div>
									<div class="btn btn-info d-none d-md-inline-block" style="position:absolute;bottom:1rem;right:1rem"><span class="d-none d-lg-inline">See all </span><?=count($images)?> photos</div>
								<?php
								endif;
								?>
							</picture>
							</a>
							<div class="spacer-sm"></div>
						</div>
					<?php
					endfor;
					?>
					</div>
				</div>
				<?php
				for($i = $starting_image_index + 4; $i < count($images); ++$i):
				?>
					<a href="<?php echo rsg_image_resize($images[$i]['url'],900,620); ?>" rel="review_box_imgs" class="swipebox d-none" title="Photo <?=$i + 1?> / <?=$images_count?>"></a>
					
				<?php
				endfor;
				?>
			</div>	
		</div>
		-->
	</div>
	<div class="row">
		<div class="col-lg-8">
			<div class="row">
				<div class="col-lg-11">
					<div class="row no-gutters">
						<div class="col-2 col-md-1">
							<img src="<?=$author_image ?>" class="img-circle avatar avatar-40" alt="" width="40" height="40" style="background-color:#d9e8fd">
						</div>
						<div class="col col-md-9">							
							<div class="small" style="font-style:italic">
								<span class="text-muted">Review written on <?=the_modified_time('dS F')?> by</span> <b><?= $author_name ?></b> <span class="text-muted"><?= $author_introduction ?></span>
								<br>
								<a href="<?=$author_link?>"><?= count_user_posts( $post->post_author, Array('post','review','reviews_road','reviews_trail','reviews_training'), true )?> other reviews</a>
							</div>
						</div>
					</div>
					<div class="spacer"></div>
					<h2><?= $brand->name.' '.$shoe_name ?> Verdict</h2>
					<div class="spacer"></div>
					<div class="big"><?= $verdict ?></div>
				</div>
			</div>
			<hr>
			<div class="row">
				<div class="col-12 col-md-6">
					<h3>The pros</h3>
					<div class="spacer"></div>
					
					<ul class="procons list-unstyled big">
						<?php
						
						if( have_rows('pros') ):

							// loop through the rows of data
							while ( have_rows('pros') ) : the_row();

								// display a sub field value
								echo '<li class="pro">'.get_sub_field('pro_line').'<div class="spacer-sm d-md-none"></div><div class="spacer d-none d-md-block"></div></li>';

							endwhile;

						endif;
						?>
					</ul>
								
				</div>
				<div class="col-12 col-md-6">
					<h3>The cons</h3>
					<div class="spacer"></div>
					
					<ul class="procons list-unstyled big">
						<?php
						
						if( have_rows('cons') ):

							// loop through the rows of data
							while ( have_rows('cons') ) : the_row();

								// display a sub field value
								echo '<li class="con">'.get_sub_field('cons_line').'<div class="spacer-sm d-md-none"></div><div class="spacer d-none d-md-block"></div></li>';

							endwhile;

						endif;
						?>
					</ul>
				</div>
			</div>
			<hr>
			<!--
			<div class="d-none d-lg-block" align="center" data-freestar-ad="__336x280 __336x280" id="runningshoesguru_incontent_1">
			  <script data-cfasync="false" type="text/javascript">
				freestar.config.enabled_slots.push({ placementName: "runningshoesguru_incontent_1", slotId: "runningshoesguru_incontent_1" });
			  </script>
			</div>
			-->
			
			<div class="d-lg-none">
				
				<div class="panel-body" style="padding:0">
					<div class="row">
						<div class="col-12">
							<h2>Where to buy</h2>
							<div class="spacer-sm"></div>
							<div class="text-muted">
								Best offers today in <b><?=$item->COUNTRY_NAME?></b>, all prices in <b><?=$item->CURRENCY_CODE?></b>
							</div>
							<div class="spacer-sm"></div>
						</div>
						<div class="col-8 offset-2">
							<div class="price-gender-switch btn-group d-flex">
								<a href="#" class="btn w-100 active" style="padding: 0.5rem;">Men</a>
								<a href="#" class="btn w-100" onclick="ga('send', 'event', 'Gender_Switch', 'InReview-PriceComp-Sidebar','Women');" style="padding: 0.5rem;">Women</a>
							</div>
						</div>
					</div>
				</div>
				<div class="spacer"></div>
				<div class="row">
					<div class="col-12">
						<?php
									$i = 0;
									if(isset($price_json->datafeed->items[0])) {
										foreach ($price_json->datafeed->items as $item) {
											if($i<4) {
												echo '<div class="row align-items-center">';
												
												echo '<div class="col-5">';
												echo '<a class="" style="display: block;max-width: 8.5em;overflow: hidden; max-height: 2.5em;" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$price_event.'\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'"><img src="'.rsg_image_resize_ext($item->MERCHANT_LOGO,114,40,'/dfs-images-cache/').'" alt="merchant logo" style="display: inline-block;"></a>';
												echo '</div>';
												
												echo '<div class="col-3">';
												if(isset($rrp))
													echo '<a class="text-success d-md-none" style="text-decoration: line-through;" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$price_event.'\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'">'.$item->CURRENCY_PREFIX.round((float)$rrp).'.00</a><br class="text-success d-md-none">';
												echo '<a class="text-success" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$price_event.'\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'"><b>'.$item->CURRENCY_PREFIX.$item->DISPLAY_PRICE.'.00</b></a>';
												echo '</div>';
												echo '<div class="col-3">';
												
												echo '<a class="btn btn-sm btn-warning" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$price_event.'\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'">See deal &nbsp;&nbsp;></a>';
												echo '</div>';
												echo '<div class="col-12"><div class="spacer"></div></div>';
												echo '</div>';
												$i++;
											}
										}
									}
									else {
										echo '<div class="row"><div class="col" style="padding:10%;"><i>Sorry - Product not found. Please try <b>';
											if( RSG_get_country_code() == 'GB' )
												echo '<a rel="nofollow sponsored" href="'.get_field('geo_uk_link', 'option').'" target="_blank" onClick="ga(\'send\', \'event\', \'Aff_Link\', \'InReview-PriceComp-Sidebar-NoProduct\',\'Amazon UK\');">Amazon UK</a>';
													else
												echo '<a rel="nofollow sponsored" href="'.$GLOBALS['rsg-no-products-us-men'].'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \'InReview-PriceComp-Sidebar-NoProduct\', \''.$GLOBALS['rsg-no-products-us-label'].'\');">'.$GLOBALS['rsg-no-products-us-label'].'</a>';
										echo '</b> to check in their assortment.';
										echo '</i></div></div>';
									}
									
									$i = 0;
									if(isset($price_json_women->datafeed->items[0])) {
										foreach ($price_json_women->datafeed->items as $item) {
											if($i<4) {
												echo '<div class="women row align-items-center" style="display:none">';
												
												echo '<div class="col-5">';
												echo '<a class="" style="display: block;max-width: 8.5em;overflow: hidden; max-height: 2.5em;" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$price_event.'\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'"><img src="'.rsg_image_resize_ext($item->MERCHANT_LOGO,114,40,'/dfs-images-cache/').'" alt="merchant logo" style="display: inline-block;"></a>';
												echo '</div>';
												
												echo '<div class="col-3">';
												if(isset($rrp))
													echo '<a class="text-success d-md-none" style="text-decoration: line-through;" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$price_event.'\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'">'.$item->CURRENCY_PREFIX.round((float)$rrp).'.00</a><br class="text-success d-md-none">';
												echo '<a class="text-success" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$price_event.'\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'"><b>'.$item->CURRENCY_PREFIX.$item->DISPLAY_PRICE.'.00</b></a>';
												echo '</div>';
												echo '<div class="col-3">';
												
												echo '<a class="btn btn-sm btn-warning" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$price_event.'\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'">See deal &nbsp;&nbsp;></a>';
												echo '</div>';
												echo '<div class="col-12"><div class="spacer"></div></div>';
												echo '</div>';
												$i++;
											}
										}
									}
									else {
										echo '<div class="row women" style="display:none"><div class="col" style="padding:10%;"><i>Sorry - Product (women) not found. Please try <b>';
											if( RSG_get_country_code() == 'GB' )
												echo '<a rel="nofollow sponsored" href="'.get_field('geo_uk_link', 'option').'" target="_blank" onClick="ga(\'send\', \'event\', \'Aff_Link\', \'InReview-PriceComp-Sidebar-NoProduct\',\'Amazon UK\');">Amazon UK</a>';
													else
												echo '<a rel="nofollow sponsored" href="'.$GLOBALS['rsg-no-products-us-men'].'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \'InReview-PriceComp-Sidebar-NoProduct\', \''.$GLOBALS['rsg-no-products-us-label'].'\');">'.$GLOBALS['rsg-no-products-us-label'].'</a>';
										echo '</b> to check in their assortment.';
										echo '</i></div></div>';
									}
									?>
									
						<div class="spacer-sm"></div>
						
						<div class="text-center"><i class="small text-muted">This page contains affiliate links <a target="_blank" href="/about-2/">Read more</a></i></div>

						
					</div>
				</div>
			</div>
			
			<hr>
			
			<h3>Rating breakdown</h3> 
			<div class="spacer"></div>
			<?php
			$rating_metrics_col1 = Array('comfort', 'build_quality', 'upper_rate', 'sole_unit_rate', 'landing');
			$rating_metrics_col2 = Array('transition', 'toe_off', 'traction', 'durability', 'value_price');
			?>
			<div class="row">				
				<div class="col-12 col-md-6">
					<?php
					foreach($rating_metrics_col1 as $rating_metric):
						$field = get_field_object($rating_metric);
						?>
						<div class="row">
							<div class="col big">
								<?= $field['label']; ?>
							</div>
							<div class="col text-right">
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
						$field = get_field_object($rating_metric);
						?>
						<div class="row">
							<div class="col big">
								<?= $field['label']; ?>
							</div>
							<div class="col text-right">
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
			<?php include get_template_directory() . '/includes/review-assessment-block.php'; ?>
			<div class="spacer-xxl"></div>
			<div class="review_sections-no-toc">
			<?php
				foreach ($sections_no_toc as $section):
					if($section_body = get_field($section)):
						$field_object = get_field_object($section);
						?>
						<div class="review-section" id="<?= $field_object['name'] ?>">
							
							<h2><?= $field_object['label'] ?> <?= $brand->name.' '.$shoe_name ?> ?</h2>
							<div class="spacer"></div>
							<?= $section_body ?>
						</div>
						<?php
						$index++;
					endif;
				endforeach;
			?>
			</div>
			<!--
			<div align="center" data-freestar-ad="__336x280 __336x280" id="runningshoesguru_incontent_2">
							  <script data-cfasync="false" type="text/javascript">
								freestar.config.enabled_slots.push({ placementName: "runningshoesguru_incontent_2", slotId: "runningshoesguru_incontent_2" });
							  </script>
			</div>
			-->
			<div class="spacer"></div>
			<div class="review-sections">
				<?php

				/*
				if( have_rows('sections') ):

					// Loop through rows.
					while( have_rows('sections') ) : the_row();

						?>
						<div class="review-section" id="<?= strtolower(urlencode(get_sub_field('section_toc_title'))) ?>">
							<h2><?= get_sub_field('section_title') ?></h2>
							<div class="spacer"></div>
							<?= get_sub_field('section_body') ?>
						</div>
						<?php
					endwhile;

				endif;
				*/
				
				
				$index = 1;
				foreach ($sections as $section):
					if($section_body = get_field($section)):
						$field_object = get_field_object($section);
						?>
						<div class="review-section" id="<?= $field_object['name'] ?>">
							<?php
							if(stripos( $field_object['label'] , 'good for') !== false):
							?>
								<h2>Is the <?= $brand->name.' '.$shoe_name ?> <?= lcfirst($field_object['label']) ?>?</h2>
							<?php
							else:
							?>
								<h2><?= $brand->name.' '.$shoe_name ?> <?= $field_object['label'] ?></h2>
							<?php
							endif;
							?>
							<div class="spacer"></div>
							<?= $section_body ?>
						</div>
						<?php
						if($index == 2):
						?>
						<!--
							<div align="center" data-freestar-ad="__336x280 __336x280" id="runningshoesguru_incontent_3">
							  <script data-cfasync="false" type="text/javascript">
								freestar.config.enabled_slots.push({ placementName: "runningshoesguru_incontent_3", slotId: "runningshoesguru_incontent_3" });
							  </script>
							</div>
							<div class="spacer"></div>
						-->
						<?php
						endif;
						if($index == 4):
						?>
						<!--
							<div align="center" data-freestar-ad="__336x280 __336x280" id="runningshoesguru_incontent_5">
							  <script data-cfasync="false" type="text/javascript">
								freestar.config.enabled_slots.push({ placementName: "runningshoesguru_incontent_5", slotId: "runningshoesguru_incontent_5" });
							  </script>
							</div>
							<div class="spacer"></div>
						-->
						<?php
						endif;
						$index++;
					endif;
				endforeach;
						
				?>
			</div>
			
			<div>
				<div class="spacer-xxl"></div>
			<?php
			if($first_shoe_ID & $third_shoe_ID):
			?>
				
				
				<h2>How does the <?=$shoe_name?> compare?</h2>
				<div class="spacer-xxl"></div>
				<div class="table-comparison">
					<div class="row">
						<div class="col-md-2 my-auto"></div>
						<div class="col d-flex flex-column">
							<h6><?=$brand_1[0]->name?> <?=get_field("model", $first_shoe_ID)?></h6>
							<div class="spacer-xl mt-auto"></div>
							<figure class="entry-thumbnail">
								<a href="<?php echo $permalink_1; ?>" rel="nofollow" style="display:block;position:relative;">
									<picture>
														
										<img class="full-image" srcset="<?php echo $thumbnail_1_resized; ?> 250w" src="<?php echo $thumbnail_1_resized; ?>" sizes="250px" class="" alt="">
														
									</picture>
								</a>
							</figure>
							
						</div>
						<div class="col d-flex flex-column">
							<h6><?=$brand_2[0]->name?> <?=get_field("model", $second_shoe_ID)?></h6>
							<div class="spacer-xl mt-auto"></div>
							<figure class="entry-thumbnail">
								<a href="<?php echo $permalink_2; ?>" rel="nofollow" style="display:block;position:relative;">
									<picture>
														
										<img class="full-image" srcset="<?php echo $thumbnail_2_resized; ?> 250w" src="<?php echo $thumbnail_2_resized; ?>" sizes="250px" class="" alt="">
														
									</picture>
								</a>
							</figure>
							
						</div>
						<div class="col d-flex flex-column">
							<h6><?=$brand_3[0]->name?> <?=get_field("model", $third_shoe_ID)?></h6>
							<div class="spacer-xl mt-auto"></div>
							<figure class="entry-thumbnail">
								<a href="<?php echo $permalink_3; ?>" rel="nofollow" style="display:block;position:relative;">
									<picture>
														
										<img class="full-image" srcset="<?php echo $thumbnail_3_resized; ?> 250w" src="<?php echo $thumbnail_3_resized; ?>" sizes="250px" class="" alt="">
														
									</picture>
								</a>
							</figure>
							
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 my-auto">Expert score</div>
						<div class="col"><span class="score-ticket-standalaone" style="display:inline-block;color:var(--water-blue);padding: 0.25rem 0.5rem;background-color: #2189E11A;font-weight: bold;"><?=get_field('vote', $first_shoe_ID)?></span></div>
						<div class="col"><span class="score-ticket-standalaone" style="display:inline-block;color:var(--water-blue);padding: 0.25rem 0.5rem;background-color: #2189E11A;font-weight: bold;"><?=get_field('vote', $second_shoe_ID)?></span></div>
						<div class="col"><span class="score-ticket-standalaone" style="display:inline-block;color:var(--water-blue);padding: 0.25rem 0.5rem;background-color: #2189E11A;font-weight: bold;"><?=get_field('vote', $third_shoe_ID)?></span></div>
					</div>
					<div class="row">
						<div class="col-md-2 my-auto">User score</div>
						<div class="col">
							
							<a class="" href="<?=$permalink_1?>#comments"><span class="d-none d-md-inline">&nbsp;&nbsp;</span><?=$user_vote_percentage_1?></a>
						</div>
						<div class="col">
							
							<a class="" href="<?=$permalink_2?>#comments"><span class="d-none d-md-inline">&nbsp;&nbsp;</span><?=$user_vote_percentage_2?></a>
						</div>
						<div class="col">
							
							<a class="" href="<?=$permalink_3?>#comments"><span class="d-none d-md-inline">&nbsp;&nbsp;</span><?=$user_vote_percentage_3?></a>
						</div>
					</div>
					
					<div class="row">
						<div class="col-md-2 my-auto">Best price</div>
						<div class="col">
							
							<a <?=$link_html_1?> class="btn btn-warning" rel="nofollow sponsored"><?=$currency_prefix_1.$display_price_1?>&nbsp;&nbsp;<span class="right-arrow"></span></a>
								
						</div>
						<div class="col">
							
							<a <?=$link_html_2?> class="btn btn-warning" rel="nofollow sponsored"><?=$currency_prefix_2.$display_price_2?>&nbsp;&nbsp;<span class="right-arrow"></span></a>
								
						</div>
						<div class="col">
							
							<a <?=$link_html_3?> class="btn btn-warning" rel="nofollow sponsored"><?=$currency_prefix_3.$display_price_3?>&nbsp;&nbsp;<span class="right-arrow"></span></a>
								
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 my-auto">Retail price</div>
						<div class="col">US$<?= get_field('rrp_us', $first_shoe_ID) ?></div>
						<div class="col">US$<?= get_field('rrp_us', $second_shoe_ID) ?></div>
						<div class="col">US$<?= get_field('rrp_us', $third_shoe_ID) ?></div>
					</div>
					<div class="row">
						<div class="col-md-2 my-auto">Brand</div>
						<div class="col">
							<a href="<?=get_bloginfo('url')?>/reviews/<?=$brand_1[0]->slug?>/all/" rel="tag"><?=$brand_1[0]->name?></a>
						</div>
						<div class="col">
							<a href="<?=get_bloginfo('url')?>/reviews/<?=$brand_2[0]->slug?>/all/" rel="tag"><?=$brand_2[0]->name?></a>
						</div>
						<div class="col">
							<a href="<?=get_bloginfo('url')?>/reviews/<?=$brand_3[0]->slug?>/all/" rel="tag"><?=$brand_3[0]->name?></a>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 my-auto">Review</div>
						<div class="col">
							<a href="<?=$permalink_1?>" rel="tag"><?=get_field('model', $first_shoe_ID)?> review</a>
						</div>
						<div class="col">
							<a href="<?=$permalink_2?>" rel="tag"><?=get_field('model', $second_shoe_ID)?> review</a>
						</div>
						<div class="col">
							<a href="<?=$permalink_3?>" rel="tag"><?=get_field('model', $third_shoe_ID)?> review</a>
						</div>
					</div>
					<div class="row">
						<div class="col-md-2 my-auto">Shoe type</div>
						<div class="col">
							<a href="<?=get_bloginfo('url')?>/reviews/all/<?=$shoes_type_1[0]->slug?>" rel="tag"><?=$shoes_type_1[0]->name?></a>
						</div>
						<div class="col">
							<a href="<?=get_bloginfo('url')?>/reviews/all/<?=$shoes_type_2[0]->slug?>" rel="tag"><?=$shoes_type_2[0]->name?></a>
						</div>
						<div class="col">
							<a href="<?=get_bloginfo('url')?>/reviews/all/<?=$shoes_type_3[0]->slug?>" rel="tag"><?=$shoes_type_3[0]->name?></a>
						</div>
					</div>
					
					<div class="row">
						<div class="col-md-2 my-auto">Weight</div>
						<div class="col"><?= get_field('weight_oz', $first_shoe_ID)?> oz</div>
						<div class="col"><?= get_field('weight_oz', $second_shoe_ID)?> oz</div>
						<div class="col"><?= get_field('weight_oz', $third_shoe_ID)?> oz</div>
					</div>
					<div class="row">
						<div class="col-md-2 my-auto">Heel Drop</div>
						<div class="col"><?= get_field('heel_drop_mm', $first_shoe_ID)?> mm</div>
						<div class="col"><?= get_field('heel_drop_mm', $second_shoe_ID)?> mm</div>
						<div class="col"><?= get_field('heel_drop_mm', $third_shoe_ID)?> mm</div>
					</div>
					<div class="row">
						<div class="col-md-2 my-auto">Recommended for</div>
						<div class="col"><?= ucfirst(implode(', ', (array) get_field('usage', $first_shoe_ID))); ?></div>
						<div class="col"><?= ucfirst( implode(', ', (array) get_field('usage', $second_shoe_ID))); ?></div>
						<div class="col"><?= ucfirst( implode(', ', (array) get_field('usage', $third_shoe_ID))); ?></div>
					</div>
					<div class="row">
						<div class="col-md-2 my-auto">Cushioning type</div>
						<div class="col"><?= get_field('cushioning_type', $first_shoe_ID); ?></div>
						<div class="col"><?= get_field('cushioning_type', $second_shoe_ID); ?></div>
						<div class="col"><?= get_field('cushioning_type', $third_shoe_ID); ?></div>
					</div>
					<div class="row">
						<div class="col-md-2 my-auto">Cushioning amount</div>
						<div class="col"><?= get_field('cushioning_amount', $first_shoe_ID); ?></div>
						<div class="col"><?= get_field('cushioning_amount', $second_shoe_ID); ?></div>
						<div class="col"><?= get_field('cushioning_amount', $third_shoe_ID); ?></div>
					</div>
					<div class="row">
						<div class="col-md-2 my-auto">Flexibility</div>
						<div class="col"><?= get_field('flexibility', $first_shoe_ID); ?></div>
						<div class="col"><?= get_field('flexibility', $second_shoe_ID); ?></div>
						<div class="col"><?= get_field('flexibility', $third_shoe_ID); ?></div>
					</div>
					<div class="row">
						<div class="col-md-2 my-auto">Stability</div>
						<div class="col"><?= get_field('stability', $first_shoe_ID); ?></div>
						<div class="col"><?= get_field('stability', $second_shoe_ID); ?></div>
						<div class="col"><?= get_field('stability', $third_shoe_ID); ?></div>
					</div>
					<div class="row">
						<div class="col-md-2 my-auto">Sizing </div>
						<div class="col"><?= get_field_object('sizing' , $first_shoe_ID)['choices'][get_field('sizing', $first_shoe_ID)]; ?></div>
						<div class="col"><?= get_field_object('sizing' , $second_shoe_ID)['choices'][get_field('sizing', $second_shoe_ID)]; ?></div>
						<div class="col"><?= get_field_object('sizing' , $third_shoe_ID)['choices'][get_field('sizing', $third_shoe_ID)]; ?></div>
					</div>
					
				</div>
				
				
				
				
				<div class="spacer"></div>
				
				<?php
				endif;
				?>
				
				<div class="panel text-center" style="box-shadow:none; border:1px solid #EAEAEA">
					<div class="panel-body" style="padding-left:5vw;padding-right:5vw">
						<h4>Why you can trust us</h4>
						<div class="spacer-sm"></div>
						<i class="text-muted small">
						As editorial policy, we do not accept free samples from companies.<br>
						We purchased this pair of <?=$brand->name ?> at&nbsp;<a href="<? the_field('purchased_from_url')?>" target="_blank"><? the_field('purchased_from_name') ?></a>&nbsp; with our own money.<br><br>
						This page contains affiliate links. As an Amazon Associate we earn from qualifying purchases. Read more about our policy.
						</i>
					</div>
				</div>
				<div class="spacer"></div>
			</div>
			
			
			<div class="panel panel-primary" id="full-price-comparison">
				<div class="panel-heading inverted-block text-center lowest-price" style="color:#fff;">
					<div class="spacer-sm"></div>
						<div class="small">Lowest price:</div>
						<h4 style="color:#fff">
							<?php
										
								if(isset($price_json->datafeed->items[0])):
									$item = $price_json->datafeed->items[0];
									echo '<a class="text-success h4" style="color:#fff" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$ga_event.'-Top-Red-Price\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'">'.$item->CURRENCY_PREFIX.$item->DISPLAY_PRICE.'</a>';
								else:
									if( RSG_get_country_code() == 'GB' )
										echo '<a class="text-success h4" style="color:#fff" rel="nofollow sponsored" href="'.get_field('geo_uk_link', 'option').'" target="_blank" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$ga_event.'-Top-Red-Price-NoProduct\',\'Amazon UK\');">$'.$rrp.'</a>';
									else
										echo '<a class="text-success h4" style="color:#fff" rel="nofollow sponsored"  href="'.$GLOBALS['rsg-no-products-us-men'].'" target="_blank" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$ga_event.'-Top-Red-Price-NoProduct\',\''.$GLOBALS['rsg-no-products-us-label'].'\');">$'.$rrp.'</a>';
								endif;
								?>
						</h4>
						<div class="spacer-sm">
					</div>
				</div>
				<div class="panel-body">
					<div class="row justify-content-center">
						<div class="col-12 text-center">
							<h2><?= $brand->name.' '.$shoe_name ?> price comparison</h2>
							<div class="spacer-sm"></div>
							<div class="text-muted small">
									Best offers today in <b><?=$item->COUNTRY_NAME?></b>, all prices in <b><?=$item->CURRENCY_CODE?></b>
							</div>
							<div class="spacer-sm"></div>
							
						</div>
						<div class="col-8 col-lg-5">
							<div class="price-gender-switch btn-group d-flex">
								<a href="#" class="btn w-100 active" style="padding: 0.5rem;">Men</a>
								<a href="#" class="btn w-100" onclick="ga('send', 'event', 'Gender_Switch', 'InReview-PriceComp-Sidebar','Women');" style="padding: 0.5rem;">Women</a>
							</div>
						</div>
					</div>
				</div>
				<div class="row justify-content-center">
					<div class="col-10">
						<?php
									$i = 0;
									if(isset($price_json->datafeed->items[0])) {
										foreach ($price_json->datafeed->items as $item) {
											if($i<4) {
												echo '<div class="row align-items-center">';
												echo '<div class="col d-none d-md-block">';
												if(isset($item->IMAGE_URL) && $item->IMAGE_URL != '') {
													echo '<img src="'.rsg_image_resize_ext($item->IMAGE_URL,56,45,'/dfs-images-cache/').'" width="56" />';
												} else {
													echo '<span class="wp-post-image-placeholder">&nbsp;</span>';
												}
														
												echo '</div>';
												echo '<div class="col">';
												echo '<a class="" style="display: block;max-width: 8.5em;overflow: hidden; max-height: 2em;" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$price_event.'\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'"><img src="'.rsg_image_resize_ext($item->MERCHANT_LOGO,88,31,'/dfs-images-cache/').'" alt="merchant logo" style="display: inline-block;"></a>';
												echo '</div>';
												echo '<div class="col d-none d-md-block">';
												if(isset($rrp))
													echo '<a class="text-success" style="text-decoration: line-through;" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$price_event.'\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'">'.$item->CURRENCY_PREFIX.round((float)$rrp).'.00</a><br>';
												echo '</div>';
												echo '<div class="col">';
												if(isset($rrp))
													echo '<a class="text-success d-md-none" style="text-decoration: line-through;" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$price_event.'\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'">'.$item->CURRENCY_PREFIX.round((float)$rrp).'.00</a><br class="text-success d-md-none">';
												echo '<a class="text-success" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$price_event.'\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'"><b>'.$item->CURRENCY_PREFIX.$item->DISPLAY_PRICE.'.00</b></a>';
												echo '</div>';
												echo '<div class="col">';
												
												echo '<a class="btn btn-sm btn-warning" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$price_event.'\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'">See deal &nbsp;&nbsp;></a>';
												echo '</div>';
												echo '<div class="col-12"><hr></div>';
												echo '</div>';
												$i++;
											}
										}
									}
									else {
										echo '<div class="row"><div class="col" style="padding:10%;"><i>Sorry - Product not found. Please try <b>';
											if( RSG_get_country_code() == 'GB' )
												echo '<a rel="nofollow sponsored" href="'.get_field('geo_uk_link', 'option').'" target="_blank" onClick="ga(\'send\', \'event\', \'Aff_Link\', \'InReview-PriceComp-Sidebar-NoProduct\',\'Amazon UK\');">Amazon UK</a>';
													else
												echo '<a rel="nofollow sponsored" href="'.$GLOBALS['rsg-no-products-us-men'].'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \'InReview-PriceComp-Sidebar-NoProduct\', \''.$GLOBALS['rsg-no-products-us-label'].'\');">'.$GLOBALS['rsg-no-products-us-label'].'</a>';
										echo '</b> to check in their assortment.';
										echo '</i></div></div>';
									}
									
									$i = 0;
									if(isset($price_json_women->datafeed->items[0])) {
										foreach ($price_json_women->datafeed->items as $item) {
											if($i<4) {
												echo '<div class="women row align-items-center" style="display:none">';
												echo '<div class="col d-none d-md-block">';
												if(isset($item->IMAGE_URL) && $item->IMAGE_URL != '') {
													echo '<img src="'.rsg_image_resize_ext($item->IMAGE_URL,56,45,'/dfs-images-cache/').'" width="56" />';
												} else {
													echo '<span class="wp-post-image-placeholder">&nbsp;</span>';
												}
														
												echo '</div>';
												echo '<div class="col">';
												echo '<a class="" style="display: block;max-width: 8.5em;overflow: hidden; max-height: 2em;" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$price_event.'\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'"><img src="'.rsg_image_resize_ext($item->MERCHANT_LOGO,88,31,'/dfs-images-cache/').'" alt="merchant logo" style="display: inline-block;"></a>';
												echo '</div>';
												echo '<div class="col d-none d-md-block">';
												if(isset($rrp))
													echo '<a class="text-success" style="text-decoration: line-through;" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$price_event.'\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'">'.$item->CURRENCY_PREFIX.round((float)$rrp).'.00</a><br>';
												echo '</div>';
												echo '<div class="col">';
												if(isset($rrp))
													echo '<a class="text-success d-md-none" style="text-decoration: line-through;" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$price_event.'\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'">'.$item->CURRENCY_PREFIX.round((float)$rrp).'.00</a><br class="text-success d-md-none">';
												echo '<a class="text-success" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$price_event.'\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'"><b>'.$item->CURRENCY_PREFIX.$item->DISPLAY_PRICE.'.00</b></a>';
												echo '</div>';
												echo '<div class="col">';
												
												echo '<a class="btn btn-sm btn-warning" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$price_event.'\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'">See deal &nbsp;&nbsp;></a>';
												echo '</div>';
												echo '<div class="col-12"><hr></div>';
												echo '</div>';
												$i++;
											}
										}
									}
									else {
										echo '<div class="row women" style="display:none"><div class="col" style="padding:10%;"><i>Sorry - Product (women) not found. Please try <b>';
											if( RSG_get_country_code() == 'GB' )
												echo '<a rel="nofollow sponsored" href="'.get_field('geo_uk_link', 'option').'" target="_blank" onClick="ga(\'send\', \'event\', \'Aff_Link\', \'InReview-PriceComp-Sidebar-NoProduct\',\'Amazon UK\');">Amazon UK</a>';
													else
												echo '<a rel="nofollow sponsored" href="'.$GLOBALS['rsg-no-products-us-men'].'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \'InReview-PriceComp-Sidebar-NoProduct\', \''.$GLOBALS['rsg-no-products-us-label'].'\');">'.$GLOBALS['rsg-no-products-us-label'].'</a>';
										echo '</b> to check in their assortment.';
										echo '</i></div></div>';
									}
									?>
						<div class="spacer-sm"></div>
						<div class="text-center"><i class="small text-muted">This page contains affiliate links <a target="_blank" href="/about-2/">Read more</a></i></div>

						<div class="spacer-sm"></div>
					</div>
				</div>				
			</div>
			<div class="d-lg-none">
				<?php
				$args = array(
					'posts_per_page'   => 1,
					'post_type'     => 'guide',
					'meta_query'    => array(
						'relation'      => 'OR',
						array(
							'key'       => 'flexible_header_0_linked_post',
							'compare'   => '=',
							'value'     => $post->ID,
						),
						array(
							'key'       => 'flexible_header_1_linked_post',
							'compare'   => '=',
							'value'     => $post->ID,
						),
						array(
							'key'       => 'flexible_header_2_linked_post',
							'compare'   => '=',
							'value'     => $post->ID,
						),
						array(
							'key'       => 'flexible_header_3_linked_post',
							'compare'   => '=',
							'value'     => $post->ID,
						),
						array(
							'key'       => 'flexible_header_4_linked_post',
							'compare'   => '=',
							'value'     => $post->ID,
						),
					)
				);


				// query
				$the_query = new WP_Query( $args );
				?>
				<?php if( $the_query->have_posts() ): ?>
					<div class="spacer"></div>
					<div class="small" style="border-radius: 0.625rem;border:1px solid #EAEAEA;padding:0.6rem 2rem;">
						<div class="row" style="margin: 0;">
								<div class="col-1 px-0">
									<img src="<?=get_template_directory_uri()?>/images/specs-icons/top_5.svg">
								</div>
								<div class="col-8 pr-0">
									<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
											<b>top 5</b> in <a href="<?php the_permalink(); ?>"><?php echo strtolower(get_the_title()); ?></a>
									<?php endwhile; ?>
								</div>
								<div class="col px-0">
									<img src="<?= $thumbnail_2_resized; ?>">
								</div>
						</div>
					</div>
				<?php endif; ?>

				<?php wp_reset_query();   // Restore global post data stomped by the_post(). ?>
			</div>
			<div class="spacer-xxl"></div>
			<div class="panel" style="background-color: rgba(15, 112, 196, 0.05);box-shadow:none;">
				<div class="panel-body">
					<div class="row">
						<div class="col-md-8">
							<h2>Reviewed by <?=$author_first_name?></h2>
						</div>
						<div class="col text-right d-none d-md-block">
							<a class="btn btn-default" href="<?=$author_link?>">All reviews by <?=$author_first_name?></a>
						</div>
					</div>
					<div class="spacer"></div>
					<div class="row align-items-center">
						<div class="col-3">
							<img src="<?=$author_image_big ?>" class="img-circle avatar avatar-80" alt="" width="80" height="80" style="background-color:#d9e8fd">
						</div>
						<div class="col-9 d-md-none">
							<a class="btn btn-default" href="<?=$author_link?>">All reviews ></a>
						</div>
						<div class="col-lg-8 text-muted">
							<div class="spacer d-md-none"></div>
							This review was written by <?=$author_name?> on <?=the_modified_time('dS F')?>.<br>
							<?php
							echo get_the_author_meta( 'description' );
							?>
						</div>
					</div>
				</div>
			</div>
			<div class="spacer-xxl"></div>
			
			<div id="comments"></div>

			<?php comments_template('/comments-2022.php', true); ?>
					
					
					
					
					
						
						
					
					<div class="spacer-xxl"></div>
					<div class="spacer-xl"></div>
					
			
		
		</div>
		<div class="col-4 d-none d-lg-block">
			<div style="position: -webkit-sticky;position: sticky; top: 80px">
				<div class="panel panel-primary">
					<div class="panel-heading inverted-block text-center" style="color:#fff">
						<div class="spacer-sm"></div>
							<div class="small">Lowest price:</div>
								<?php
										
								if(isset($price_json->datafeed->items[0])):
									$item = $price_json->datafeed->items[0];
									echo '<a class="text-success h4" style="color:#fff" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$ga_event.'-Top-Red-Price\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'">'.$item->CURRENCY_PREFIX.$item->DISPLAY_PRICE.'</a>';
								else:
									if( RSG_get_country_code() == 'GB' )
										echo '<a class="text-success h4" style="color:#fff" rel="nofollow sponsored" href="'.get_field('geo_uk_link', 'option').'" target="_blank" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$ga_event.'-Top-Red-Price-NoProduct\',\'Amazon UK\');">$'.$rrp.'</a>';
									else
										echo '<a class="text-success h4" style="color:#fff" rel="nofollow sponsored"  href="'.$GLOBALS['rsg-no-products-us-men'].'" target="_blank" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$ga_event.'-Top-Red-Price-NoProduct\',\''.$GLOBALS['rsg-no-products-us-label'].'\');">$'.$rrp.'</a>';
								endif;
								?>
							<div class="spacer-sm">
						</div>
					</div>
					<div class="panel-body">
						<div class="row justify-content-lg-center">
							<div class="col-12 text-center">
								<h2>Where to buy</h2>
								<div class="spacer-sm"></div>
								<div class="text-muted small">
									Best offers today in <b><?=$item->COUNTRY_NAME?></b>, all prices in <b><?=$item->CURRENCY_CODE?></b>
								</div>
								<div class="spacer-sm"></div>
							</div>
							<div class="col-lg-10">
								<div class="price-gender-switch btn-group d-flex">
									<a href="#" class="btn w-100 active" style="padding: 0.5rem;">Men</a>
									<a href="#" class="btn w-100" onclick="ga('send', 'event', 'Gender_Switch', 'InReview-PriceComp-Sidebar','Women');" style="padding: 0.5rem;">Women</a>
								</div>
							</div>
						</div>
					</div>
					<div class="row justify-content-lg-center">
						<div class="col-10">
							<?php
									$i = 0;
									if(isset($price_json->datafeed->items[0])) {
										foreach ($price_json->datafeed->items as $item) {
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
												echo '<div class="col-12"><hr></div>';
												echo '</div>';
												$i++;
											}
										}
									}
									else {
										echo '<div class="row"><div class="col" style="padding:10%;"><i>Sorry - Product not found. Please try <b>';
											if( RSG_get_country_code() == 'GB' )
												echo '<a rel="nofollow sponsored" href="'.get_field('geo_uk_link', 'option').'" target="_blank" onClick="ga(\'send\', \'event\', \'Aff_Link\', \'InReview-PriceComp-Sidebar-NoProduct\',\'Amazon UK\');">Amazon UK</a>';
													else
												echo '<a rel="nofollow sponsored" href="'.$GLOBALS['rsg-no-products-us-men'].'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \'InReview-PriceComp-Sidebar-NoProduct\', \''.$GLOBALS['rsg-no-products-us-label'].'\');">'.$GLOBALS['rsg-no-products-us-label'].'</a>';
										echo '</b> to check in their assortment.';
										echo '</i></div></div>';
									}
									
									$i = 0;
									if(isset($price_json_women->datafeed->items[0])) {
										foreach ($price_json_women->datafeed->items as $item) {
											if($i<2) {
												echo '<div class="women row align-items-center" style="display:none">';
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
												echo '<div class="col-12"><hr></div>';
												echo '</div>';
												$i++;
											}
										}
									}
									else {
										echo '<div class="row women" style="display:none"><div class="col" style="padding:10%;"><i>Sorry - Product (women) not found. Please try <b>';
											if( RSG_get_country_code() == 'GB' )
												echo '<a rel="nofollow sponsored" href="'.get_field('geo_uk_link', 'option').'" target="_blank" onClick="ga(\'send\', \'event\', \'Aff_Link\', \'InReview-PriceComp-Sidebar-NoProduct\',\'Amazon UK\');">Amazon UK</a>';
													else
												echo '<a rel="nofollow sponsored" href="'.$GLOBALS['rsg-no-products-us-men'].'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \'InReview-PriceComp-Sidebar-NoProduct\', \''.$GLOBALS['rsg-no-products-us-label'].'\');">'.$GLOBALS['rsg-no-products-us-label'].'</a>';
										echo '</b> to check in their assortment.';
										echo '</i></div></div>';
									}
									?>
							<div class="text-center"><i class="small text-muted">This page contains affiliate links <a target="_blank" href="/about-2/">Read more</a></i></div>
							<div class="spacer-sm"></div>
						</div>
					</div>
					
				</div>
				<?php
				$args = array(
					'posts_per_page'   => 1,
					'post_type'     => 'guide',
					'meta_query'    => array(
						'relation'      => 'OR',
						array(
							'key'       => 'flexible_header_0_linked_post',
							'compare'   => '=',
							'value'     => $post->ID,
						),
						array(
							'key'       => 'flexible_header_1_linked_post',
							'compare'   => '=',
							'value'     => $post->ID,
						),
						array(
							'key'       => 'flexible_header_2_linked_post',
							'compare'   => '=',
							'value'     => $post->ID,
						),
						array(
							'key'       => 'flexible_header_3_linked_post',
							'compare'   => '=',
							'value'     => $post->ID,
						),
						array(
							'key'       => 'flexible_header_4_linked_post',
							'compare'   => '=',
							'value'     => $post->ID,
						),
					)
				);


				// query
				$the_query = new WP_Query( $args );
				?>
				<?php if( $the_query->have_posts() ): ?>
					<div class="spacer"></div>
					<div class="small" style="border-radius: 0.625rem;border:1px solid #EAEAEA;padding:0.6rem 2rem;">
						<div class="row" style="margin: 0;">
								<div class="col-1 px-0">
									<img src="<?=get_template_directory_uri()?>/images/specs-icons/top_5.svg">
								</div>
								<div class="col-8 pr-0">
									<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
											<b>top 5</b> in <a href="<?php the_permalink(); ?>"><?php echo strtolower(get_the_title()); ?></a>
									<?php endwhile; ?>
								</div>
								<div class="col px-0">
									<img src="<?= $thumbnail_2_resized; ?>">
								</div>
						</div>
					</div>
				<?php endif; ?>

				<?php wp_reset_query();   // Restore global post data stomped by the_post(). ?>
				<div class="spacer-xxl"></div>
			</div>
		</div>
	</div>
</div>


<div class="d-md-none" id="best-price-mobile" style="display:none;position: fixed; top: 16px;width:100%">
	<div class="container">	
		<div class="row">
			<div class="col">
				<div class="navbar-default" style="cursor:pointer;color:#fff;padding:0.5rem 1rem;border-radius: 0.75rem;">
					<?php
					if(isset($price_json->datafeed->items[0])) {
						$item = $price_json->datafeed->items[0];
						echo '<div class="row align-items-center">';
						echo '<div class="col-3">';
						if(isset($item->IMAGE_URL) && $item->IMAGE_URL != '') {
							echo '<img src="'.rsg_image_resize_ext($item->IMAGE_URL,59,40,'/dfs-images-cache/').'" width="59" style="border-radius: 0.625rem;" />';
						} else {
							echo '<span class="wp-post-image-placeholder">&nbsp;</span>';
						}
								
						echo '</div>';
						echo '<div class="col" style="line-height:1.3em">';
						if(isset($rrp))
							echo '<a class="text-muted small d-md-none" style="color:#FFF;text-decoration: line-through;" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$price_event.'\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'">'.$item->CURRENCY_PREFIX.round((float)$rrp).'.00</a><br>';
						echo '<a style="color:#FFF;" rel="nofollow sponsored" target="_'.$item->MYKEY.'" onClick="ga(\'send\', \'event\', \'Aff_Link\', \''.$price_event.'\', window.location.href);" href="'.str_replace("rsg-road-bottom-20", $override_tag, $item->DEEP_LINK).'"><b>'.$item->CURRENCY_PREFIX.$item->DISPLAY_PRICE.'.00</b></a>';
						echo '</div>';
						echo '<div class="col">';
						
						echo '<a class="btn btn-sm btn-warning"  href="#full-price-comparison">See all deals &nbsp;&nbsp;></a>';
						echo '</div>';
						echo '</div>';			
					}
					?>
				</div>
				
			</div>
		</div>
	</div>
</div>

<div class="d-md-none" style="position: fixed; bottom: 16px;width:100%">
	<div class="container">	
		<div class="row">
			<div class="col">
				<div id="toc-mobile" class="navbar-default" style="cursor:pointer;color:#fff;padding:0.5rem 1rem;border-radius: 0.75rem;">
					Table of contents
					
					<button type="button" class="navbar-toggle" style="border:0">
						<span class="icon-bar" style="margin:3.5px 0px"></span>
						<span class="icon-bar" style="margin:3.5px 0px"></span>
						<span class="icon-bar" style="margin:3.5px 0px"></span>
					</button>
				</div>
				<select id="toc-select" style="opacity: 0;width: 100%;position: absolute;top: 0;">
					<?php

					/*
					if( have_rows('sections') ):

						// Loop through rows.
						while( have_rows('sections') ) : the_row();

							?>
							<option value="#<?= strtolower(urlencode(get_sub_field('section_toc_title'))) ?>"><?= get_sub_field('section_toc_title') ?></option>
							
							<?php
						endwhile;

					endif;
					*/
					
					foreach ($sections as $section):
						if(get_field($section)):
							$field_object = get_field_object($section);
							?>
							<option value="#<?= $field_object['name'] ?>"><?= $field_object['label'] ?></option>
							
							<?php
						endif;
					endforeach;
					?>
				</select>
			</div>
		</div>
	</div>
</div>



	
	
	<?php

	$brands = '';
	$shoes_types = '';
	if( is_array(get_the_terms($post->ID, 'brands')) )
		$brands = array_values(get_the_terms($post->ID, 'brands'));
	if( is_array(get_the_terms($post->ID, 'shoes_type')) )
		$shoes_types = array_values(get_the_terms($post->ID, 'shoes_type'));


	$args = array(
		'fields' => 'ids',
		'post_type' => array('post', 'review', 'reviews_road'),
		'date_query' => array( array( 'after' => '-1 year' ) ),  
		'showposts' => 3,
		
		'post__not_in' => array($post->ID),
		'tax_query' => array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'brands',
				'field'    => 'slug',
				'terms'    => array( $brands[0]->slug ),
			),
			array(
				'taxonomy' => 'shoes_type',
				'field'    => 'slug',
				'terms'    => array( 'road' ),
			),
		),
	);

	$related_posts_brand = new WP_Query($args);

	$args = array(
		'fields' => 'ids',
		'post_type' => array('post', 'review', 'reviews_road'),
		'date_query' => array( array( 'after' => '-1 year' ) ),  
		'showposts' => 3,
		'meta_key' => 'review_post_type',
		'meta_value' => '1',
		'post__not_in' => array($post->ID),
		'orderby'        		=> 'comment_count',
	);


	$related_posts = new WP_Query($args);

	$related_posts = array_merge($related_posts_brand->posts, $related_posts->posts);
	if(count($related_posts_brand->posts) > 3)
		rsort($related_posts);
	$related_posts = array_slice($related_posts, 0, 3);
	$wp_query->related_posts = $related_posts;
	get_template_part( 'include', 'related-reviews-fullwidth' );
	?>



<?php
//rsg_single_review_sticky_bottom_2020('nav_where_to_buy_bottom', 'single-trail-mobile-bottom');
?>

<?php endwhile; else : ?>

<?php endif; ?>

<?php get_footer(); ?>


