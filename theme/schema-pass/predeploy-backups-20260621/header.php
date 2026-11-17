<?php
if(array_key_exists('newsletter_thankyou',$_GET) && $_GET['newsletter_thankyou'] && !$_COOKIE["newsletter_thankyou"])
	setcookie("newsletter_thankyou", 1);
?><!DOCTYPE html>
<html lang="en">
<head>
	<?php if(is_singular('gps')) { ?>
		
	<?php } ?>
	
	<?php if ( is_page(21177) ) { ?>
	
	<?php } ?>
	
	<?php if(get_the_ID() == 23875) { ?>
		
	
	<?php } ?>
	

	
	
	<!-- Google Analytics Universal 
	<script type="text/javascript">
		
		(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		})(window,document,'script','//www.google-analytics.com/analytics.js','ga');
		ga('set', 'anonymizeIp', true);
		ga('create', 'UA-8203420-1', 'auto');
		ga('set', 'forceSSL', true);
		ga('require', 'displayfeatures');
		
		ga('send', 'pageview');

	</script>
	< / Google Analytics -->
	
	<!-- Global site tag (gtag.js) - Google Analytics -->
	
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-X59H6B5DK0"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());

	  gtag('config', 'G-X59H6B5DK0', {

		'content_group':'<?=get_post_type()?>'

	  });
	</script>
	
	<!--
	<script type="text/javascript">
	(function(c,l,a,r,i,t,y){
	c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
	t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
	y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
	})(window, document, "clarity", "script", "469c46nhc8");
	</script>
	-->
	
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1 , maximum-scale=1.0, user-scalable=no">
	<title><?php wp_title(''); ?> | <?php bloginfo('name'); ?></title>
	
	<link rel="dns-prefetch" href="https://typekit.com" />

	<link rel="preconnect" href="https://typekit.com" crossorigin />
	<link rel="preconnect" href="https://use.typekit.net" crossorigin />
	<link rel="preconnect" href="https://p.typekit.net" crossorigin />
	
	<meta name="p:domain_verify" content="03f5dd8e490c2e8123ee1fd454bf2b08">
	<!-- Chrome, Firefox OS and Opera -->
	<meta name="theme-color" content="#025FB0">
	<!-- Windows Phone -->
	<meta name="msapplication-navbutton-color" content="#025FB0">
	<!-- iOS Safari -->
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
	<!-- Facebook
	================================================== -->
	<?php
	$short_excerpt = ( get_post_meta( get_the_ID(), 'description', true )) ? get_post_meta( get_the_ID(), 'description', true ) : short_excerpt(120);
	//if(is_single() || is_page() || is_front_page()) {
	if(is_single() || is_page() ) {
		//echo '<meta property="fb:app_id" content="141348465888050" />';
		echo '<meta property="og:type" content="website" />';
		echo '<meta property="og:url" content="'.get_permalink().'" />';
		echo '<meta property="og:title" content="'.get_the_title().'" />';
		echo '<meta property="og:description" content="'.$short_excerpt.'" />';
		
		if ( function_exists( 'add_theme_support' ) && has_post_thumbnail()){
			//$thumbnail = wp_get_attachment_image_src ( get_post_thumbnail_id ( $post->ID ), array(9999,9999));
			if ( function_exists( 'social_image_generator' ) && 'gift-guide' != get_post_type() ) {
				$thumbnail = social_image_generator(get_the_ID());
			}
			else {
				$thumbnail = wp_get_attachment_image_src ( get_post_thumbnail_id ( $post->ID ), array(9999,9999));
				$thumbnail = $thumbnail[0];
			}
			if(get_the_ID() == 20940)
				$thumbnail = get_template_directory_uri().'/images/survey2018/facebook3.jpg';
			echo '<meta property="og:image" content="'.$thumbnail.'" />';
		}
	}
	
	?>
	<!-- Twitter
	================================================== -->
	<?php
	//if(is_single() || is_page() || is_front_page()) {
	if(is_single() || is_page() ) {
		//echo( (isset( get_post_meta( get_the_ID(), 'description', true ) ? 'set' || 'not set'));
		echo '<meta name="twitter:card" content="summary_large_image" />';
		echo '<meta name="twitter:title" content="'.get_the_title().'" />';
		echo '<meta name="twitter:description" content="'.$short_excerpt.'" />';
		
		if ( function_exists( 'add_theme_support' ) && has_post_thumbnail()){
			//$thumbnail = wp_get_attachment_image_src ( get_post_thumbnail_id ( $post->ID ), array(9999,9999));
			if ( function_exists( 'social_image_generator' ) && 'gift-guide' != get_post_type() ) {
				$thumbnail = social_image_generator(get_the_ID());
			}
			else {
				$thumbnail = wp_get_attachment_image_src ( get_post_thumbnail_id ( $post->ID ), array(9999,9999));
				$thumbnail = $thumbnail[0];
			}
			if(get_the_ID() == 20940)
				$thumbnail = get_template_directory_uri().'/images/survey2018/twitter3.jpg';
			echo '<meta name="twitter:image" content="'.$thumbnail.'" />';
		}
	}
	?>

	
	<?php if ( get_post_type() == 'mega_menu' || is_404() ) { ?>
		<meta name="robots" content="noindex,follow" />
	<?php } ?>
	
	<!-- Raptive Head Tag Manual -->
	<script data-no-optimize="1" data-cfasync="false">
	(function(w, d) {
		w.adthrive = w.adthrive || {};
		w.adthrive.cmd = w.
		adthrive.cmd || [];
		w.adthrive.plugin = 'adthrive-ads-manual';
		w.adthrive.host = 'ads.adthrive.com';var s = d.createElement('script');
		s.async = true;
		s.referrerpolicy='no-referrer-when-downgrade';
		s.src = 'https://' + w.adthrive.host + '/sites/56f01024a1cbb74635a25c23/ads.min.js?referrer=' + w.encodeURIComponent(w.location.href) + '&cb=' + (Math.floor(Math.random() * 100) + 1);
		var n = d.getElementsByTagName('script')[0];
		n.parentNode.insertBefore(s, n);
	})(window, document);
	</script>

	
	<?php wp_head(); ?>
	
	<style>
		#header-banner .ad-tag {
			min-height: 50px;
			max-height: 50px;
		}
		
		@media (min-width: 992px) {
			#header-banner .ad-tag {
				min-height: 90px;
				max-height: 90px;
			}
		}
		
		.fontello-icon-right-open { 
			width: 0.7em;transform: rotate(315deg);-webkit-transform: rotate(315deg);border: solid currentColor;border-width: 0 3.5px 3.5px 0;display: inline-block;padding: 0px;height: 0.7em;vertical-align: middle;margin-right: 0.3em;margin-top: -0.3em
		}
		
		.fontello-icon-up-open { 
			width: 0.7em;transform: rotate(225deg);-webkit-transform: rotate(225deg);border: solid currentColor;border-width: 0 3.5px 3.5px 0;display: inline-block;padding: 0px;height: 0.7em;vertical-align: middle;margin: 0 0.15em;
		}
		.fontello-icon-left-open {
			width: 0.7em;transform: rotate(135deg);-webkit-transform: rotate(135deg);border: solid currentColor;border-width: 0 3.5px 3.5px 0;display: inline-block;padding: 0px;height: 0.7em;vertical-align: middle;margin-left: 0.3em;margin-top: -0.3em
		}
		
		.fontello-icon-search::before {
			content: "";
			display: inline-block;
			position: absolute;
			right: -0.25em;
			bottom: -0.1em;
			border-width: 0;
			background: currentColor;
			width: 0.35em;
			height: 0.08em;
			-webkit-transform: rotate(45deg);
			-moz-transform: rotate(45deg);
			-ms-transform: rotate(45deg);
			-o-transform: rotate(45deg);
		}

		.fontello-icon-search {
			font-size: 1em;
			display: inline-block;
			width: 0.4em;
			height: 0.4em;
			border: 0.1em solid currentColor;
			position: relative;
			border-radius: 0.35em;
		}

	</style>
	
	<script>
	function throttle(callback, delay) {
		var timeoutHandler = null;
		if (timeoutHandler == null) {
			timeoutHandler = setTimeout(function () {
				callback();
				clearInterval(timeoutHandler);
				timeoutHandler = null;
			}, delay);
		}	
	}
	
	;( function( $ ) {

		jQuery.cachedScript = function(url, options) {
		  // allow user to set any option except for dataType, cache, and url
		  options = $.extend(options || {}, {
			dataType: "script",
			cache: true,
			async: true,
			url: url
		  });
		  return jQuery.ajax(options);
		};

	} )( jQuery );
	
	var RSG_Ajax = { ajaxurl: '<?=admin_url( 'admin-ajax.php' )?>', rsg_ajax_security: '<?=wp_create_nonce( 'rsg-ajax-comment-voting-nonce' )?>' };
	
	(function(d) {
		var config = {
		  kitId: 'wiu6byt',
		  scriptTimeout: 3000,
		  async: true
		},
		h=d.documentElement,t=setTimeout(function(){h.className=h.className.replace(/\bwf-loading\b/g,"")+" wf-inactive";},config.scriptTimeout),tk=d.createElement("script"),f=false,s=d.getElementsByTagName("script")[0],a;h.className+=" wf-loading";tk.src='https://use.typekit.net/'+config.kitId+'.js';tk.async=true;tk.onload=tk.onreadystatechange=function(){a=this.readyState;if(f||a&&a!="complete"&&a!="loaded")return;f=true;clearTimeout(t);try{Typekit.load(config)}catch(e){}};s.parentNode.insertBefore(tk,s)
	  })(document);
	
	/*
	function isScrolledIntoViewImg(el) {
		// Special bonus for those using jQuery
		if (typeof jQuery === "function" && el instanceof jQuery) {
			el = el[0];
		}

		var rect = el.getBoundingClientRect();

		
		return (rect.top >= 0 && rect.left >= 0 && rect.top <= (window.innerHeight || document.documentElement.clientHeight));
		
		
		
		
	}
	
	*/
	
	function isScrolledIntoView(elem) {
		
		var docViewTop = $(window).scrollTop();
		var docViewBottom = docViewTop + $(window).height();

		var elemTop = $(elem).offset().top;
		var elemBottom = elemTop + $(elem).height();

		return ( (docViewBottom >= elemTop) && (elemBottom >= docViewBottom));
		//(docViewBottom >= elemTop) && 
	}
	</script>
	
	
	<?php
	// Review structured data (JSON-LD) - see includes/rsg-schema.php (schema pass 2026-06-21)
	if ( function_exists('rsg_emit_review_schema') ) { rsg_emit_review_schema(); }
	if ( function_exists('rsg_emit_comparison_schema') ) { rsg_emit_comparison_schema(); }
	?>
	
	<?php
	if(is_singular('top')):
		?>
												
		
		<script type="application/ld+json">
		{
  "@context": "http://schema.org",
  "@type": "Article",
  "headline": "<?php the_title(); ?>",
  "description": "<?= $post->description; ?>",
  "url": "<?php the_permalink(); ?>",
  "publisher": {
				"@type": "Organization",
				"name": "Running Shoes Guru",
				"alternateName" : "RSG",
				"legalName": "RSGMEDIA",
				"url": "https://www.runningshoesguru.com/",
				"description": "Founded in 2009, Running Shoes Guru is the place to go for independent, unbiased, expert reviews on running shoes and GPS running watches.",
				"foundingDate": "2009-04-01",
				"email" : "guru@runningshoesguru.com",
				"contactPoint": {
					"@type": "ContactPoint",
					"url": "https://www.runningshoesguru.com/contact-us/",
					"contactType": "customer service"
				},
				"logo": "https://www.runningshoesguru.com/wp-content/themes/rsg_2019/images/logo2020.svg",
				"sameAs": [
					"https://www.facebook.com/runningshoesguru/",
					"https://twitter.com/runningshoesgur/",
					"https://www.instagram.com/runningshoesguru/",
					"https://www.youtube.com/user/runningshoesguru"
				]
		  },
  "author": {
			  "@type": "Person",
			  "@id": "<?php echo get_author_posts_url( $post->post_author ); ?>",
			  "name": "<?php echo ucfirst(get_the_author_meta( 'display_name', $post->post_author )); ?>",
			  "url": "<?php echo get_author_posts_url( $post->post_author ); ?>",
			  "sameAs": [
				"<?php echo get_author_posts_url( $post->post_author ); ?>"
			  ]
			},
  "datePublished": "<?php the_time('c'); ?>",
  "dateModified": "<?php the_modified_time('c'); ?>",
  "image": {
    "@type": "ImageObject",
    "url": "<?php the_post_thumbnail_url('large'); ?>", 
    "width": 633, 
    "height": 356 
  },
  "mainEntity": {
    "@type": "ItemList",
 
   "itemListElement": [
   <?php
   if( have_rows('review_sections_2023') ):
		// loop through the rows of data
		$i = 1;
		while ( have_rows('review_sections_2023') ) : the_row();
			
			$award = get_sub_field('title');
			$review_id = get_sub_field('winner');
			$sub_post = get_post( $review_id );
			$verdict = get_field( "verdict" , $sub_post->ID );
		
			$shoe_name = get_field("model", $sub_post->ID );

			$brand = get_field('brand', $sub_post->ID );
			
			$vote = get_field('vote', $sub_post->ID );
			
			$thumbnail_2[0] = get_field( 'professional_image', $sub_post->ID );
			if($i>1)
				echo ',';
			?>
			{
				"@type": "ListItem",
				"position": <?=$i?>,
				"item": {
				  "@type": "Product",
				  "brand": {
					"@type": "Brand",
					"name": "<?= $brand->name ?>"
				  },
				  "description": <?php echo json_encode(trim($verdict)); ?>,
				  "sku": "<?=$sub_post->ID?>",
				  "mpn": "<?=$sub_post->ID?>",
				  "image": "<?=str_replace(' ', '%20',$thumbnail_2[0])?>",
				  "category": "Running Shoes",
				  "name": "<?= $brand->name.' '.$shoe_name ?>",
				  "award": "<?=$award?>",
				  "review": {
					  "@type": "Review",
					  "name": "<?= $brand->name.' '.$shoe_name ?> Review",
					  "url": "<? the_permalink($sub_post->ID); ?>",
					  "author": {
								  "@type": "Person",
								  "@id": "<?php echo get_author_posts_url( $sub_post->post_author ); ?>",
								  "name": "<?php echo ucfirst(get_the_author_meta( 'display_name', $sub_post->post_author )); ?>",
								  "url": "<?php echo get_author_posts_url( $sub_post->post_author ); ?>",
								  "sameAs": [
									"<?php echo get_author_posts_url( $sub_post->post_author ); ?>"
								  ]
								},
					  "datePublished": "<?php echo get_the_time('c', $sub_post->ID); ?>",
					  "reviewRating": {
						"@type": "Rating",
						"ratingValue": "<?php echo number_format($vote, 1, '.', '') ?>",
						"bestRating": "10",
						"worstRating": "1"
					  },
					  "description": <?php echo json_encode(trim($verdict)); ?>
					},
						
				  "offers": {
					"@type": "Offer",
					"url": "https://www.runningshoesguru.com/go/index.php?id=40_b0dyphb654&clickref=&override_tag=&referer=https://www.runningshoesguru.com/top/best-running-shoes-right-now/&site_id=", 
					"priceCurrency": "USD",
					"price": "149.99", 
					"itemCondition": "http://schema.org/NewCondition",
					"availability": "http://schema.org/InStock" 
				  }
				}
			}
			<?php
			$i++;
		endwhile;
	endif;
	?>
    ]
  }
}
		
		</script>
		
		
	<?php
	endif;
	?>
	
	<!-- Below is a recommended list of pre-connections, which allow the network to establish each connection quicker, speeding up response times and improving ad performance. -->
	<link rel="preconnect" href="https://a.pub.network/" crossorigin />
	<link rel="preconnect" href="https://b.pub.network/" crossorigin />
	<link rel="preconnect" href="https://c.pub.network/" crossorigin />
	<link rel="preconnect" href="https://d.pub.network/" crossorigin />
	<link rel="preconnect" href="https://c.amazon-adsystem.com" crossorigin />
	<link rel="preconnect" href="https://s.amazon-adsystem.com" crossorigin />
	<link rel="preconnect" href="https://secure.quantserve.com/" crossorigin />
	<link rel="preconnect" href="https://rules.quantcount.com/" crossorigin />
	<link rel="preconnect" href="https://pixel.quantserve.com/" crossorigin />
	<link rel="preconnect" href="https://cmp.quantcast.com/" crossorigin />
	<link rel="preconnect" href="https://btloader.com/" crossorigin />
	<link rel="preconnect" href="https://api.btloader.com/" crossorigin />
	<link rel="preconnect" href="https://confiant-integrations.global.ssl.fastly.net" crossorigin />		
	
	<!--
	<link rel="preload" href="https://a.pub.network/core/pubfig/cls.css" as="style" onload="this.onload=null;this.rel='stylesheet'">	<noscript><link rel="stylesheet" href="https://a.pub.network/core/pubfig/cls.css"></noscript>
	<script data-cfasync="false" type="text/javascript">
	  var freestar = freestar || {};
	  freestar.queue = freestar.queue || [];
	  freestar.config = freestar.config || {};
	  freestar.config.enabled_slots = [];
	  freestar.initCallback = function () { (freestar.config.enabled_slots.length === 0) ? freestar.initCallbackCalled = false : freestar.newAdSlots(freestar.config.enabled_slots) }
	</script>
	-->


</head>
<?php
if( is_page_template( 'reviews_search.php' ) || is_review() || is_singular('gps') ):
?>
<body>
	<div style="position:relative"><!-- OPEN CONTAINING DIV -->
	<div id="scroll-pixel" style="position:absolute;width:1px;height:1px;left:0;top:40%"></div>
	
	<div>
		<!-- Fixed navbar -->
		<div class="navbar navbar-default navbar-fixed-top" role="navigation">
<?php
else:
?>
<body>
	<div style="position:relative"><!-- OPEN CONTAINING DIV -->
	<div id="scroll-pixel" style="position:absolute;width:1px;height:1px;left:0;top:40%"></div>
	
	<div>
		<!-- Fixed navbar -->
		<div class="navbar navbar-default navbar-fixed-top" role="navigation">
<?php
endif;
?>
		  <div class="container">
			<div class="clearfix">
				<div class="navbar-header">
				  <button type="button" id="btn-burger" aria-label="menu" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				  </button>
				  
				  <button class="navbar-toggle collapsed btn btn-default pull-right" aria-label="search" data-toggle="rsg_collapse" data-target=".navbar-search" onclick="ga('send', 'event', 'Header Menu', 'Search-Icon', window.location.href)" style="background: white;border: none;color: #212529;padding: 0 8px;border-radius: 50%;line-height: 1.8;font-size: 1.2rem;margin-top: 8px;">
					<svg style="width: 1em;height: 1em;"><use xlink:href="<?=get_template_directory_uri()?>/images/icons.svg#icon-search"></use></svg>
				  </button>
				  
				  <a class="navbar-brand" href="<?=home_url( '/' )?>"><img src="<?=get_template_directory_uri()?>/images/logo2020.svg"  alt="Running Shoes Guru" sizes="170px" style="max-height:100%" alt=""></a>
		
				</div>
				<div style="float:right">
					<div class="navbar-nav navbar-collapse collapse small">
						<!--
						style="max-width: 590px;overflow: hidden;max-height: 40px;"
						-->
						<div class="spacer-xxl d-lg-none"></div>
						<?php wp_nav_menu( array( 'container_class' => 'menu-main-menu-bar clearfix', 'menu_class' => 'nav navbar-nav' , 'theme_location' => 'header_menu', 'walker' => new RSG_2015_Walker_Header_Menu() ) ); ?>
					</div>        <!--/.nav-collapse -->
					<div class="nav navbar-nav navbar-right d-none  d-lg-block" style="margin-top: 9px;">
						<div class="search-option" style="border: 1px solid #ccc;padding: 6px;border-radius: 3px;margin: 0;margin-bottom: 0px;width: 100%;background: #fff;line-height: 1rem;">
							<form action="/">
								<input type="text" name="s" placeholder="Type a keyword" aria-label="Type a keyword" value="" class="" onclick="ga('send', 'event', 'Reviews Page Heatmap', 'Search Filter Click', '')" style="border: none;outline: 0;width: calc(100% - 23px);float: left;padding: 0;box-shadow: none;font-size: 85%;line-height: 1;">
								<button type="submit" aria-label="search" style="min-width: auto;padding: 0;border: 0;background: #fff;box-shadow: none;margin: 0;font-size: 85%;color:#212529">
									<svg style="width: 1em;height: 1em;"><use xlink:href="<?=get_template_directory_uri()?>/images/icons.svg#icon-search"></use></svg>
								</button>
							</form>
						</div>
					</div>
				</div>
			</div>
				<form role="search" method="get" action="<?php bloginfo('url'); ?>/" class="navbar-search">
					
					<div class="input-group mb-3">
						<input type="text" name="s" placeholder="Search" aria-label="Search keywords" class="form-control" minlength='3'>
						<div class="input-group-append">
						 
							<button class="btn btn-outline-secondary" aria-label="search button" type="submit">
								<svg style="width: 1em;height: 1em;"><use xlink:href="<?=get_template_directory_uri()?>/images/icons.svg#icon-search"></use></svg>
							</button>
						</div>
					  </div>
				</form>
				<?php
				if( !is_review() && !is_page_template( 'reviews_search.php' )):
				?>
				<!-- GIFT GUIDE BUTTON -->
				<!--
				<div style="position:relative" class="visible-lg visible-xl">
					<div style="position: absolute;right: -2px;bottom: -58px;box-shadow: 0 5px 10px rgba(0,0,0,0.2);background-color: #B52684;padding: 10px 20px;color: #fff;text-transform: uppercase;font-size: 11px;border-radius: 3px;z-index: 499999;"><a style="color: #fff;" href="http://www.runningshoesguru.com/gift-guide/gift-guide-for-runners-holidays-2016/"><span style="display: block;font-size: 19.5px;line-height: 20px;">RUNNERS</span>Gift Guide 2016</a></div>
				</div>
				-->
				<?php
				endif;
				?>
			</div>
		</div>
		
		
			<div id="header-banner" class="container">
				<?php
				global $template;
				if( is_review() && !is_singular( 'review' )  ):
				?>
					<!--
					<div class="visible-xs visible-ms visible-sm d-flex">
						<div class="adSlot" data-alias="RSG_SingleReview_Mobile_320x50_ATF" data-width="320" data-height="50"></div>
					</div>
					
					<div class="d-none  d-sm-none d-flex">
						<div class="adSlot" data-alias="RSG_Single_Review_728x90_ATF" data-width="728" data-height="90"></div>
					</div>
					-->
					
					<!--
					<div class="spacer"></div>
					<div align="center" data-freestar-ad="__320x50 __728x90" id="runningshoesguru_leaderboard_atf">
						<script data-cfasync="false" type="text/javascript">
						freestar.config.enabled_slots.push({ placementName: "runningshoesguru_leaderboard_atf", slotId: "runningshoesguru_leaderboard_atf" });
						</script>
					</div>
					-->
				<?php
				elseif( is_front_page() ):
				?>
				<!--
				<div class="d-none  d-sm-none hidden-md d-flex">
					<div class="ad-tag" data-ad-name="homepage-desktop-728x90-ATF-" data-ad-size="728x90" ></div>
				</div>
				-->
				<?php
				elseif( 'gift-guide' == get_post_type() || is_tax('shoes_family') ):
				
				?>		
				
				<?php
				elseif( is_page_template( 'reviews_search.php' ) ):
				?>	
					<!--
					<div class="d-none  d-flex">
						<div class="adSlot" data-alias="RSG_Review_728x90_ATF" data-width="728" data-height="90"></div>
					</div>
					-->
					
					<!--
					<div class="spacer"></div>
					<div align="center" data-freestar-ad="__320x50 __728x90" id="runningshoesguru_leaderboard_atf">
						<script data-cfasync="false" type="text/javascript">
						freestar.config.enabled_slots.push({ placementName: "runningshoesguru_leaderboard_atf", slotId: "runningshoesguru_leaderboard_atf" });
						</script>
					</div>
					-->
				<?php
				elseif( is_category() || is_home() || is_author() || is_search()  ):
				?>
					<!--
					<div class="d-none  d-flex">
						<div class="adSlot" data-alias="RSG_Category_728x90_ATF" data-width="728" data-height="90"></div>
					</div>
					-->
					
					<!--
					<div class="spacer"></div>
					<div class="d-none d-sm-flex ">
						<div align="center" data-freestar-ad="__320x50 __728x90" id="runningshoesguru_leaderboard_atf">
							<script data-cfasync="false" type="text/javascript">
							freestar.config.enabled_slots.push({ placementName: "runningshoesguru_leaderboard_atf", slotId: "runningshoesguru_leaderboard_atf" });
							</script>
						</div>
					</div>
					-->
				<?php
				elseif(is_singular( 'content' ) ):
				?>
					
				<?php
				elseif(	 basename($template) == 'single-guide.php' ):
				?>
					
					
				<?php
				elseif(is_singular( 'comparison' )):
				
				elseif(is_archive( 'comparison' )):
				
				elseif(	 basename($template) == 'single-guide-alt.php' ):
				
				elseif(	 basename($template) == 'single-guide-alt-2.php' ):
				
				elseif( is_page_template( 'template-clean.php' ) || is_page_template( 'template-home-2018.php' ) || is_page_template( 'template-wizard.php' ) || is_page_template( 'template-wizard-2024.php' ) || is_page_template( 'template-wizard-2020.php' ) || is_singular( 'affiliate_product' ) || is_page_template( 'template-plans.php' ) || is_page_template( 'template-plans-2.php' ) || is_page_template( 'template-plans-2-alt.php' ) || is_singular( 'gps' )|| is_singular( 'review' )  ):
				?>	
				
				<?php
				else:
				?>
					
					<?php if( (function_exists( 'has_post_thumbnail' ) ) && ( has_post_thumbnail() ) && get_post_thumbnail_id($post->ID) ): ?>
						<!--
						<div class="visible-xs visible-ms d-flex">
							<div class="adSlot" data-alias="RSG_Article_Mobile_320x50_ATF" data-width="320" data-height="50"></div>
						</div>
						
						<div class="d-none  d-flex">
							<div class="adSlot" data-alias="RSG_Article_728x90_ATF" data-width="728" data-height="90"></div>
						</div>
						-->
						
						<!--
						<div class="spacer"></div>
						<div class="d-flex">
							<div align="center" data-freestar-ad="__320x50 __728x90" id="runningshoesguru_leaderboard_atf">
								<script data-cfasync="false" type="text/javascript">
								freestar.config.enabled_slots.push({ placementName: "runningshoesguru_leaderboard_atf", slotId: "runningshoesguru_leaderboard_atf" });
								</script>
							</div>
						</div>
						-->
					<?php
					endif;
					?>
				<?php
				endif;
				?>
			</div>
	</div>