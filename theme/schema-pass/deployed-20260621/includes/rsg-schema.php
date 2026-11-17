<?php
/**
 * RSG review structured data (JSON-LD).
 *
 * Self-contained, frontend-invisible. Emits valid schema.org markup for the
 * four review post types (road / trail / gps / training) by building PHP
 * arrays and running them through wp_json_encode() — so the output is valid
 * JSON by construction (no hand-written braces/commas to break).
 *
 * Added 2026-06-21 — schema pass. Replaces the broken inline JSON-LD that
 * previously lived in header.php (road only, invalid JSON).
 *
 * Rollback: remove the require line in functions.php and delete this file.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'rsg_schema_brand_name' ) ) {
	function rsg_schema_brand_name( $brand ) {
		if ( is_object( $brand ) ) {
			if ( isset( $brand->name ) )       { return $brand->name; }        // WP_Term
			if ( isset( $brand->post_title ) ) { return $brand->post_title; }  // WP_Post
		}
		if ( is_array( $brand ) ) {
			if ( isset( $brand['name'] ) )       { return $brand['name']; }
			if ( isset( $brand['post_title'] ) ) { return $brand['post_title']; }
		}
		return is_string( $brand ) ? $brand : '';
	}
}

if ( ! function_exists( 'rsg_schema_image_url' ) ) {
	function rsg_schema_image_url( $img ) {
		if ( is_array( $img ) ) {
			if ( isset( $img['url'] ) ) { return $img['url']; }
			if ( isset( $img[0] ) )     { return $img[0]; }
		}
		return is_string( $img ) ? $img : '';
	}
}

if ( ! function_exists( 'rsg_schema_offer' ) ) {
	/**
	 * Best-effort Offer node from the DFS price feed. Never fatal: any missing
	 * data or error returns an empty array (offer simply omitted).
	 */
	function rsg_schema_offer( $pid ) {
		if ( ! function_exists( 'dstudio' ) || ! function_exists( 'rsg_build_datafeed_query' ) ) {
			return array();
		}
		try {
			$ds_query = get_field( 'ds_query', $pid );
			if ( empty( $ds_query ) ) { return array(); }
			$pricebox = rsg_build_datafeed_query( $ds_query );
			if ( empty( $pricebox ) ) { return array(); }
			$cc = function_exists( 'RSG_get_country_code' ) ? RSG_get_country_code() : 'US';
			$pj = dstudio( array( 'query' => $pricebox, 'pricebox' => 'newrsg-json', 'countrycode' => $cc ), null );
			if ( function_exists( 'rsg_datafeed_json' ) ) { $pj = rsg_datafeed_json( $pj ); }
			$item = isset( $pj->datafeed->items[0] ) ? $pj->datafeed->items[0] : null;
			if ( ! $item || ! isset( $item->DISPLAY_PRICE ) || floatval( $item->DISPLAY_PRICE ) <= 1 ) {
				return array();
			}
			return array(
				'@type'           => 'Offer',
				'price'           => (string) $item->DISPLAY_PRICE,
				'priceCurrency'   => isset( $item->CURRENCY_CODE ) ? $item->CURRENCY_CODE : 'USD',
				'availability'    => 'https://schema.org/InStock',
				'url'             => get_permalink( $pid ),
				'priceValidUntil' => gmdate( 'Y-m-d', strtotime( '+30 day' ) ),
			);
		} catch ( \Throwable $e ) {
			return array();
		}
	}
}

if ( ! function_exists( 'rsg_build_review_faq' ) ) {
	/**
	 * FAQPage from the "who should buy / who should not buy" ACF fields.
	 * Returns empty array if neither field is populated.
	 */
	function rsg_build_review_faq( $pid, $name ) {
		$buy = trim( (string) get_field( 'who_should_buy_it', $pid ) );
		$not = trim( (string) get_field( 'who_should_not_buy_it_', $pid ) );
		$qa  = array();
		if ( '' !== $buy ) {
			$qa[] = array(
				'@type'          => 'Question',
				'name'           => 'Who should buy the ' . $name . '?',
				'acceptedAnswer' => array( '@type' => 'Answer', 'text' => wp_strip_all_tags( $buy ) ),
			);
		}
		if ( '' !== $not ) {
			$qa[] = array(
				'@type'          => 'Question',
				'name'           => 'Who should not buy the ' . $name . '?',
				'acceptedAnswer' => array( '@type' => 'Answer', 'text' => wp_strip_all_tags( $not ) ),
			);
		}
		if ( empty( $qa ) ) { return array(); }
		return array( '@context' => 'https://schema.org/', '@type' => 'FAQPage', 'mainEntity' => $qa );
	}
}

if ( ! function_exists( 'rsg_build_review_schema' ) ) {
	/**
	 * Builds an array of schema.org node arrays for the current review post.
	 * Returns array() if ACF or core data is unavailable.
	 */
	function rsg_build_review_schema() {
		global $post;
		if ( ! $post || ! function_exists( 'get_field' ) ) { return array(); }
		$pid = $post->ID;

		$brand_name = trim( (string) rsg_schema_brand_name( get_field( 'brand', $pid ) ) );
		$model      = trim( (string) get_field( 'model', $pid ) );
		$name       = trim( $brand_name . ' ' . $model );
		if ( '' === $name ) { $name = get_the_title( $pid ); }

		$verdict   = trim( (string) get_field( 'verdict', $pid ) );
		$vote      = get_field( 'vote', $pid );
		$image     = rsg_schema_image_url( get_field( 'professional_image', $pid ) );
		$permalink = get_permalink( $pid );
		$category  = ( 'reviews_gps' === get_post_type( $pid ) ) ? 'GPS Running Watch' : 'Running Shoes';

		// ---- Sub-criteria scores -> additionalProperty (skip blanks/zeros) ----
		$metrics = array(
			'comfort', 'build_quality', 'upper_rate', 'sole_unit_rate', 'landing',
			'transition', 'toe_off', 'traction', 'durability', 'value_price',
		);
		$additional = array();
		foreach ( $metrics as $m ) {
			$fo = get_field_object( $m, $pid );
			if ( ! $fo ) { continue; }
			$val = isset( $fo['value'] ) ? floatval( $fo['value'] ) : 0;
			if ( $val <= 0 ) { continue; } // e.g. GPS watches leave shoe-only metrics at 0
			$label        = ( ! empty( $fo['label'] ) ) ? $fo['label'] : ucwords( str_replace( '_', ' ', $m ) );
			$additional[] = array(
				'@type'    => 'PropertyValue',
				'name'     => $label,
				'value'    => $val,
				'minValue' => 0,
				'maxValue' => 10,
			);
		}
		// Specs.
		$weight = get_field( 'weight_oz', $pid );
		if ( '' !== $weight && null !== $weight && floatval( $weight ) > 0 ) {
			$additional[] = array( '@type' => 'PropertyValue', 'name' => 'Weight', 'value' => floatval( $weight ), 'unitText' => 'oz' );
		}
		$drop = get_field( 'heel_drop_mm', $pid );
		if ( '' !== $drop && null !== $drop && floatval( $drop ) > 0 ) {
			$additional[] = array( '@type' => 'PropertyValue', 'name' => 'Heel drop', 'value' => floatval( $drop ), 'unitText' => 'mm' );
		}

		// ---- Pros / cons ----
		$pos = array();
		$neg = array();
		if ( have_rows( 'pros', $pid ) ) {
			$i = 1;
			while ( have_rows( 'pros', $pid ) ) {
				the_row();
				$line = trim( (string) get_sub_field( 'pro_line' ) );
				if ( '' !== $line ) { $pos[] = array( '@type' => 'ListItem', 'position' => $i, 'name' => $line ); $i++; }
			}
		}
		if ( have_rows( 'cons', $pid ) ) {
			$i = 1;
			while ( have_rows( 'cons', $pid ) ) {
				the_row();
				$line = trim( (string) get_sub_field( 'cons_line' ) );
				if ( '' !== $line ) { $neg[] = array( '@type' => 'ListItem', 'position' => $i, 'name' => $line ); $i++; }
			}
		}

		// ---- Author / publisher ----
		$author_id = $post->post_author;
		$publisher = array(
			'@type'        => 'Organization',
			'name'         => 'Running Shoes Guru',
			'alternateName' => 'RSG',
			'legalName'    => 'RSGMEDIA',
			'url'          => 'https://www.runningshoesguru.com/',
			'foundingDate' => '2009-04-01',
			'email'        => 'guru@runningshoesguru.com',
			'logo'         => 'https://www.runningshoesguru.com/wp-content/themes/rsg_2022/images/logo2020.svg',
			'sameAs'       => array(
				'https://www.facebook.com/runningshoesguru/',
				'https://twitter.com/runningshoesgur/',
				'https://www.instagram.com/runningshoesguru/',
				'https://www.youtube.com/user/runningshoesguru',
			),
		);

		// ---- Review node ----
		$review = array(
			'@type'            => 'Review',
			'@id'              => $permalink . '#review',
			'mainEntityOfPage' => array( '@id' => $permalink ),
			'headline'         => $name . ' Review',
			'datePublished'    => get_the_date( 'c', $pid ),
			'dateModified'     => get_the_modified_date( 'c', $pid ),
			'author'           => array(
				'@type' => 'Person',
				'name'  => ucfirst( get_the_author_meta( 'display_name', $author_id ) ),
				'url'   => get_author_posts_url( $author_id ),
			),
			'publisher'        => $publisher,
		);
		if ( '' !== $verdict ) { $review['reviewBody'] = $verdict; }
		if ( ! empty( $pos ) ) { $review['positiveNotes'] = array( '@type' => 'ItemList', 'itemListElement' => $pos ); }
		if ( ! empty( $neg ) ) { $review['negativeNotes'] = array( '@type' => 'ItemList', 'itemListElement' => $neg ); }
		if ( '' !== $vote && null !== $vote && floatval( $vote ) > 0 ) {
			$review['reviewRating'] = array(
				'@type'       => 'Rating',
				'ratingValue' => number_format( floatval( $vote ), 1, '.', '' ),
				'bestRating'  => '10',
				'worstRating' => '1',
			);
		}

		// ---- Product node ----
		$product = array(
			'@context' => 'https://schema.org/',
			'@type'    => 'Product',
			'name'     => $name,
			'category' => $category,
			'sku'      => (string) $pid,
			'mpn'      => (string) $pid,
		);
		if ( '' !== $brand_name ) { $product['brand'] = array( '@type' => 'Brand', 'name' => $brand_name ); }
		if ( '' !== $image )      { $product['image'] = str_replace( ' ', '%20', $image ); }
		if ( '' !== $verdict )    { $product['description'] = $verdict; }
		if ( ! empty( $additional ) ) { $product['additionalProperty'] = $additional; }
		$product['review'] = array( $review );

		// aggregateRating from approved user reviews (meta_key 'rate').
		$rated = get_comments( array( 'post_id' => $pid, 'fields' => 'ids', 'meta_key' => 'rate', 'status' => 'approve' ) );
		$cnt   = count( $rated );
		if ( $cnt > 1 ) {
			$sum = 0;
			foreach ( $rated as $rc ) { $sum += floatval( get_comment_meta( $rc, 'rate', true ) ); }
			$avg = round( $sum / $cnt, 1);
			if ( $avg > 10 ) { $avg = 10; }
			if ( $avg > 0 ) {
				$product['aggregateRating'] = array(
					'@type'       => 'AggregateRating',
					'ratingValue' => number_format( $avg, 1, '.', '' ),
					'reviewCount' => (string) $cnt,
					'bestRating'  => '10',
					'worstRating' => '1',
				);
			}
		}

		$offer = rsg_schema_offer( $pid );
		if ( ! empty( $offer ) ) { $product['offers'] = $offer; }

		// ---- Breadcrumb node ----
		$breadcrumb = array(
			'@context'        => 'https://schema.org/',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array(
				array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => 'https://www.runningshoesguru.com/' ),
				array( '@type' => 'ListItem', 'position' => 2, 'name' => 'Reviews', 'item' => 'https://www.runningshoesguru.com/reviews/' ),
				array( '@type' => 'ListItem', 'position' => 3, 'name' => $name . ' Review', 'item' => $permalink ),
			),
		);

		$nodes = array( $product, $breadcrumb );

		$faq = rsg_build_review_faq( $pid, $name );
		if ( ! empty( $faq ) ) { $nodes[] = $faq; }

		return $nodes;
	}
}

if ( ! function_exists( 'rsg_emit_review_schema' ) ) {
	/**
	 * Echoes the JSON-LD block. Call from <head>. Guards on post type, so it is
	 * a no-op on non-review pages.
	 */
	function rsg_emit_review_schema() {
		if ( ! is_singular( array( 'reviews_road', 'reviews_trail', 'reviews_gps', 'reviews_training' ) ) ) {
			return;
		}
		$nodes = rsg_build_review_schema();
		if ( empty( $nodes ) ) { return; }
		$payload = ( count( $nodes ) === 1 ) ? $nodes[0] : $nodes;
		echo "\n<script type=\"application/ld+json\">\n";
		echo wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		echo "\n</script>\n";
	}
}

/* ---------------------------------------------------------------------------
 * Comparison structured data (JSON-LD).  Added 2026-06-21 — comparison schema
 * pass.  Frontend-invisible: emitted in <head>, mirrors the review pass.
 * Google has no "comparison" rich result, so we emit the two compared shoes as
 * two independent Product nodes + a BreadcrumbList.  Self-contained; reuses the
 * review pass's brand/image/offer helpers.  Never fatal (empty -> nothing).
 * Rollback: remove the rsg_emit_comparison_schema() call in header.php.
 * ------------------------------------------------------------------------- */

if ( ! function_exists( 'rsg_comparison_user_rating' ) ) {
	/**
	 * User rating for a compared shoe, normalised to a 0-5 star scale using the
	 * SAME math single-comparison.php uses for its "User score" bar:
	 *   percentage = avg(rate) * multiplier   (multiplier 10 for road, else 20)
	 * so road stores rate on 0-10 and other types on 0-5.  Returns
	 * array('value'=>stars,'count'=>n) or array() when no usable ratings.
	 */
	function rsg_comparison_user_rating( $pid ) {
		$multiplier = ( 'reviews_road' === get_post_type( $pid ) ) ? 10 : 20;
		$rated = get_comments( array( 'post_id' => $pid, 'fields' => 'ids', 'meta_key' => 'rate', 'status' => 'approve' ) );
		$cnt   = count( $rated );
		if ( $cnt < 1 ) { return array(); }
		$sum = 0;
		foreach ( $rated as $rc ) { $sum += floatval( get_comment_meta( $rc, 'rate', true ) ); }
		if ( $sum <= 0 ) { return array(); }
		$pct = ( $sum / $cnt ) * $multiplier; // mirrors page percentage
		if ( $pct > 100 ) { $pct = 100; }
		$stars = round( $pct / 20, 1 ); // 100% -> 5 stars
		if ( $stars <= 0 ) { return array(); }
		return array( 'value' => $stars, 'count' => $cnt );
	}
}

if ( ! function_exists( 'rsg_build_product_node' ) ) {
	/**
	 * Build a schema.org Product node array for any reviewed-shoe post ID.
	 * Returns array() if the post yields no usable name.  Self-contained and
	 * never fatal.  Shared design with the review pass's Product section.
	 */
	function rsg_build_product_node( $pid ) {
		if ( ! $pid || ! function_exists( 'get_field' ) ) { return array(); }
		$pid = (int) $pid;

		// Brand: ACF 'brand' first, then the 'brands' taxonomy term.
		$brand_name = trim( (string) rsg_schema_brand_name( get_field( 'brand', $pid ) ) );
		if ( '' === $brand_name ) {
			$terms = get_the_terms( $pid, 'brands' );
			if ( is_array( $terms ) && ! empty( $terms ) && isset( $terms[0]->name ) ) {
				$brand_name = trim( (string) $terms[0]->name );
			}
		}

		// Model name: shoe_name meta, then ACF model, then post title.
		$model = trim( (string) get_post_meta( $pid, 'shoe_name', true ) );
		if ( '' === $model ) { $model = trim( (string) get_field( 'model', $pid ) ); }
		$name = trim( $brand_name . ' ' . $model );
		if ( '' === $name ) { $name = trim( (string) get_the_title( $pid ) ); }
		if ( '' === $name ) { return array(); }

		$image     = rsg_schema_image_url( get_field( 'professional_image', $pid ) );
		$permalink = get_permalink( $pid );
		$ptype     = get_post_type( $pid );
		$category  = ( 'reviews_gps' === $ptype || 'gps' === $ptype ) ? 'GPS Running Watch' : 'Running Shoes';

		$product = array(
			'@context' => 'https://schema.org/',
			'@type'    => 'Product',
			'name'     => $name,
			'category' => $category,
			'sku'      => (string) $pid,
			'mpn'      => (string) $pid,
		);
		if ( '' !== $brand_name ) { $product['brand'] = array( '@type' => 'Brand', 'name' => $brand_name ); }
		if ( '' !== $image )      { $product['image'] = str_replace( ' ', '%20', $image ); }
		if ( $permalink )         { $product['url']   = $permalink; }

		// Specs -> additionalProperty (skip blanks/zeros).
		$additional = array();
		$weight = get_field( 'weight_oz', $pid );
		if ( null === $weight || '' === $weight || floatval( $weight ) <= 0 ) { $weight = get_post_meta( $pid, 'weight', true ); }
		if ( '' !== $weight && null !== $weight && floatval( $weight ) > 0 ) {
			$additional[] = array( '@type' => 'PropertyValue', 'name' => 'Weight', 'value' => floatval( $weight ), 'unitText' => 'oz' );
		}
		$drop = get_field( 'heel_drop_mm', $pid );
		if ( null === $drop || '' === $drop || floatval( $drop ) <= 0 ) { $drop = get_field( 'heeldrop_mm', $pid ); }
		if ( null === $drop || '' === $drop || floatval( $drop ) <= 0 ) { $drop = get_post_meta( $pid, 'heeldrop', true ); }
		if ( '' !== $drop && null !== $drop && floatval( $drop ) > 0 ) {
			$additional[] = array( '@type' => 'PropertyValue', 'name' => 'Heel drop', 'value' => floatval( $drop ), 'unitText' => 'mm' );
		}
		if ( ! empty( $additional ) ) { $product['additionalProperty'] = $additional; }

		// aggregateRating from approved user ratings (page's own normalised math).
		$ur = rsg_comparison_user_rating( $pid );
		if ( ! empty( $ur ) && $ur['count'] > 1 ) {
			$product['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => number_format( $ur['value'], 1, '.', '' ),
				'reviewCount' => (string) $ur['count'],
				'bestRating'  => '5',
				'worstRating' => '1',
			);
		}

		$offer = rsg_schema_offer( $pid );
		if ( ! empty( $offer ) ) { $product['offers'] = $offer; }

		return $product;
	}
}

if ( ! function_exists( 'rsg_build_comparison_schema' ) ) {
	/**
	 * Two Product nodes (the compared shoes) + a BreadcrumbList for the current
	 * comparison post.  Returns array() when no usable products.
	 */
	function rsg_build_comparison_schema() {
		global $post;
		if ( ! $post || ! function_exists( 'get_field' ) ) { return array(); }

		$first  = get_field( 'first_shoe', $post->ID );
		$second = get_field( 'second_shoe', $post->ID );
		$first_id  = is_object( $first )  ? $first->ID  : (int) $first;
		$second_id = is_object( $second ) ? $second->ID : (int) $second;

		$nodes = array();
		if ( $first_id )  { $p1 = rsg_build_product_node( $first_id );  if ( ! empty( $p1 ) ) { $nodes[] = $p1; } }
		if ( $second_id ) { $p2 = rsg_build_product_node( $second_id ); if ( ! empty( $p2 ) ) { $nodes[] = $p2; } }
		if ( empty( $nodes ) ) { return array(); }

		$permalink = get_permalink( $post->ID );
		$title     = trim( (string) get_the_title( $post->ID ) );
		$breadcrumb = array(
			'@context'        => 'https://schema.org/',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array(
				array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => 'https://www.runningshoesguru.com/' ),
				array( '@type' => 'ListItem', 'position' => 2, 'name' => 'Comparisons', 'item' => 'https://www.runningshoesguru.com/comparison/' ),
				array( '@type' => 'ListItem', 'position' => 3, 'name' => $title, 'item' => $permalink ),
			),
		);
		$nodes[] = $breadcrumb;

		return $nodes;
	}
}

if ( ! function_exists( 'rsg_emit_comparison_schema' ) ) {
	/**
	 * Echoes the comparison JSON-LD block. Call from <head>. No-op unless on a
	 * singular comparison page.
	 */
	function rsg_emit_comparison_schema() {
		if ( ! is_singular( 'comparison' ) ) { return; }
		$nodes = rsg_build_comparison_schema();
		if ( empty( $nodes ) ) { return; }
		$payload = ( count( $nodes ) === 1 ) ? $nodes[0] : $nodes;
		echo "\n<script type=\"application/ld+json\">\n";
		echo wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		echo "\n</script>\n";
	}
}

/* ---------------------------------------------------------------------------
 * Guide (buying-guide roundup) structured data (JSON-LD).  Added 2026-06-21 —
 * guides schema pass.  Frontend-invisible: emitted in <head>, mirrors the
 * review + comparison passes.  Models the page as an authored, dated, published
 * editorial roundup (Article) whose mainEntity is a ranked ItemList of Product
 * picks (reusing rsg_build_product_node()), plus a BreadcrumbList and (when
 * present) a FAQPage built from the page's own ACF "faq" repeater.  The
 * publisher Organization carries RSG's independence / expertise USPs in
 * machine-readable form.  Self-contained; never fatal (empty -> nothing).
 * Guard: is_singular('guide').
 * Rollback: remove the rsg_emit_guide_schema() call in header.php.
 * ------------------------------------------------------------------------- */

if ( ! function_exists( 'rsg_publisher_org' ) ) {
	/**
	 * Shared RSG publisher Organization node.  Carries the brand's
	 * independence / expertise USPs (description, slogan, knowsAbout,
	 * foundingDate) so machines + LLMs read RSG as an established, independent
	 * authority.  Pass true to prepend @context for standalone top-level use.
	 */
	function rsg_publisher_org( $with_context = false ) {
		$org = array(
			'@type'         => 'Organization',
			'@id'           => 'https://www.runningshoesguru.com/#organization',
			'name'          => 'Running Shoes Guru',
			'alternateName' => 'RSG',
			'legalName'     => 'RSGMEDIA',
			'url'           => 'https://www.runningshoesguru.com/',
			'description'   => 'Founded in 2009, Running Shoes Guru is the place to go for independent, unbiased, expert reviews on running shoes and GPS running watches.',
			'slogan'        => 'Independent, unbiased, expert running shoe reviews since 2009.',
			'foundingDate'  => '2009-04-01',
			'email'         => 'guru@runningshoesguru.com',
			'knowsAbout'    => array( 'Running shoes', 'Running shoe reviews', 'GPS running watches', 'Running gear', 'Running' ),
			'contactPoint'  => array(
				'@type'       => 'ContactPoint',
				'url'         => 'https://www.runningshoesguru.com/contact-us/',
				'contactType' => 'customer service',
			),
			'logo'          => array(
				'@type' => 'ImageObject',
				'url'   => 'https://www.runningshoesguru.com/wp-content/themes/rsg_2022/images/logo2020.svg',
			),
			'sameAs'        => array(
				'https://www.facebook.com/runningshoesguru/',
				'https://twitter.com/runningshoesgur/',
				'https://www.instagram.com/runningshoesguru/',
				'https://www.youtube.com/user/runningshoesguru',
			),
		);
		if ( $with_context ) { $org = array( '@context' => 'https://schema.org/' ) + $org; }
		return $org;
	}
}

if ( ! function_exists( 'rsg_build_guide_faq' ) ) {
	/**
	 * FAQPage from the guide's own ACF "faq" repeater (question / answer).
	 * Fully consumes the have_rows() loop so ACF's row pointer resets cleanly
	 * before the template body re-iterates the same repeater.  Empty -> array().
	 */
	function rsg_build_guide_faq( $pid ) {
		if ( ! function_exists( 'have_rows' ) || ! have_rows( 'faq', $pid ) ) { return array(); }
		$qa = array();
		while ( have_rows( 'faq', $pid ) ) {
			the_row();
			$q = trim( wp_strip_all_tags( (string) get_sub_field( 'question' ) ) );
			$a = trim( wp_strip_all_tags( (string) get_sub_field( 'answer' ) ) );
			if ( '' === $q || '' === $a ) { continue; }
			$qa[] = array(
				'@type'          => 'Question',
				'name'           => $q,
				'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $a ),
			);
		}
		if ( empty( $qa ) ) { return array(); }
		return array( '@context' => 'https://schema.org/', '@type' => 'FAQPage', 'mainEntity' => $qa );
	}
}

if ( ! function_exists( 'rsg_build_editorial_review' ) ) {
	/**
	 * Editorial RSG Review node for a reviewed-shoe post, built from its
	 * editorial score ('vote', 0-10) + verdict + the reviewing author.  Gives
	 * each guide pick a first-party Review so the Product is rich-result valid
	 * even with no user ratings or live price, and surfaces RSG's testing in
	 * machine-readable form.  No @context (nested inside a Product).  Empty when
	 * there is no usable score.
	 */
	function rsg_build_editorial_review( $pid ) {
		$pid  = (int) $pid;
		$vote = get_field( 'vote', $pid );
		if ( '' === $vote || null === $vote || floatval( $vote ) <= 0 ) { return array(); }
		$author_id   = (int) get_post_field( 'post_author', $pid );
		$author_name = ucfirst( (string) get_the_author_meta( 'display_name', $author_id ) );
		$review = array(
			'@type'         => 'Review',
			'reviewRating'  => array(
				'@type'       => 'Rating',
				'ratingValue' => number_format( floatval( $vote ), 1, '.', '' ),
				'bestRating'  => '10',
				'worstRating' => '1',
			),
			'author'        => array(
				'@type' => 'Person',
				'name'  => ( '' !== $author_name ) ? $author_name : 'Running Shoes Guru',
				'url'   => get_author_posts_url( $author_id ),
			),
			'datePublished' => get_the_date( 'c', $pid ),
		);
		$verdict = trim( (string) get_field( 'verdict', $pid ) );
		if ( '' !== $verdict ) { $review['reviewBody'] = wp_strip_all_tags( $verdict ); }
		return $review;
	}
}

if ( ! function_exists( 'rsg_build_guide_schema' ) ) {
	/**
	 * Builds the guide node array: Article (with nested ranked ItemList of
	 * Product picks) + BreadcrumbList + optional FAQPage.  Returns array() when
	 * no pick resolves to a usable Product.
	 */
	function rsg_build_guide_schema() {
		global $post;
		if ( ! $post || ! function_exists( 'get_field' ) ) { return array(); }
		$pid       = $post->ID;
		$permalink = get_permalink( $pid );

		// Headline: ACF in_page_title falls back to post title (no date suffix).
		$headline = trim( (string) get_field( 'in_page_title', $pid ) );
		if ( '' === $headline ) { $headline = trim( (string) get_the_title( $pid ) ); }
		$subtitle = trim( wp_strip_all_tags( (string) get_field( 'in_page_subtitle', $pid ) ) );

		// ---- Ranked ItemList of picks from the review_sections_2023 repeater ----
		$list_items = array();
		$pos        = 1;
		if ( have_rows( 'review_sections_2023', $pid ) ) {
			while ( have_rows( 'review_sections_2023', $pid ) ) {
				the_row();
				$winner    = get_sub_field( 'winner' );
				$winner_id = is_object( $winner ) ? $winner->ID : (int) $winner;
				if ( ! $winner_id ) { continue; }                 // Amazon-only / empty row
				$product = rsg_build_product_node( $winner_id );
				if ( empty( $product ) ) { continue; }            // unresolved post -> skip
				unset( $product['@context'] );                    // nested; context on the node above
				$ed_review = rsg_build_editorial_review( $winner_id );
				if ( ! empty( $ed_review ) ) { $product['review'] = array( $ed_review ); }
				$award = trim( (string) get_sub_field( 'title' ) );
				if ( '' !== $award ) { $product['award'] = $award; }
				$list_items[] = array(
					'@type'    => 'ListItem',
					'position' => $pos,
					'name'     => isset( $product['name'] ) ? $product['name'] : ( 'Pick ' . $pos ),
					'item'     => $product,
				);
				$pos++;
			}
		}
		if ( empty( $list_items ) ) { return array(); }

		$item_list = array(
			'@type'           => 'ItemList',
			'@id'             => $permalink . '#itemlist',
			'itemListOrder'   => 'https://schema.org/ItemListOrderDescending',
			'numberOfItems'   => count( $list_items ),
			'itemListElement' => $list_items,
		);

		// ---- Author (Person) ----
		$author_id = $post->post_author;
		$author    = array(
			'@type' => 'Person',
			'@id'   => get_author_posts_url( $author_id ),
			'name'  => ucfirst( get_the_author_meta( 'display_name', $author_id ) ),
			'url'   => get_author_posts_url( $author_id ),
		);

		// ---- Article wrapper (machine-readable E-E-A-T: author, dates, publisher) ----
		$article = array(
			'@context'         => 'https://schema.org/',
			'@type'            => 'Article',
			'@id'              => $permalink . '#article',
			'mainEntityOfPage' => array( '@id' => $permalink ),
			'headline'         => $headline,
			'datePublished'    => get_the_date( 'c', $pid ),
			'dateModified'     => get_the_modified_date( 'c', $pid ),
			'author'           => $author,
			'publisher'        => rsg_publisher_org(),
			'mainEntity'       => $item_list,
		);
		if ( '' !== $subtitle ) { $article['description'] = $subtitle; }
		$img = get_the_post_thumbnail_url( $pid, 'large' );
		if ( $img ) { $article['image'] = $img; }

		// ---- Breadcrumb: Home -> Guides -> this guide ----
		$breadcrumb = array(
			'@context'        => 'https://schema.org/',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array(
				array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Home',   'item' => 'https://www.runningshoesguru.com/' ),
				array( '@type' => 'ListItem', 'position' => 2, 'name' => 'Guides', 'item' => 'https://www.runningshoesguru.com/guide/' ),
				array( '@type' => 'ListItem', 'position' => 3, 'name' => $headline, 'item' => $permalink ),
			),
		);

		$nodes = array( $article, $breadcrumb );

		$faq = rsg_build_guide_faq( $pid );
		if ( ! empty( $faq ) ) { $nodes[] = $faq; }

		return $nodes;
	}
}

if ( ! function_exists( 'rsg_emit_guide_schema' ) ) {
	/**
	 * Echoes the guide JSON-LD block. Call from <head>. No-op unless on a
	 * singular guide page.
	 */
	function rsg_emit_guide_schema() {
		if ( ! is_singular( 'guide' ) ) { return; }
		$nodes = rsg_build_guide_schema();
		if ( empty( $nodes ) ) { return; }
		$payload = ( count( $nodes ) === 1 ) ? $nodes[0] : $nodes;
		echo "\n<script type=\"application/ld+json\">\n";
		echo wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		echo "\n</script>\n";
	}
}

/* ---------------------------------------------------------------------------
 * Reviews hub / faceted directory structured data (JSON-LD).  Added 2026-06-21
 * — reviews-hub pass.  Frontend-invisible.  The reviews_search.php template
 * powers /reviews/ and its faceted brand / type / family views.  Schema is
 * emitted ONLY on the self-canonical (indexable) views — base, /{brand}/all/,
 * /all/{type}/, and the curated /{brand}/{family}/ hubs — mirroring
 * RSG_rel_canonical(), so we never put rich data on a consolidated duplicate.
 * Emits a CollectionPage -> ItemList of the listed reviews + a BreadcrumbList.
 * Guard: is_page_template('reviews_search.php').
 * Rollback: remove the rsg_emit_reviews_schema() call in header.php.
 * ------------------------------------------------------------------------- */

if ( ! function_exists( 'rsg_reviews_facet' ) ) {
	/**
	 * Resolve the current facet + whether this URL is the self-canonical
	 * (indexable) view, mirroring RSG_rel_canonical().  Returns array() off the
	 * template, else brand/family/type terms + canonical URL + self flag.
	 */
	function rsg_reviews_facet() {
		if ( ! is_page_template( 'reviews_search.php' ) ) { return array(); }
		$brand_slug = get_query_var( 'brands' );
		$type_slug  = get_query_var( 'shoes_type' );

		$brand_term  = ( $brand_slug && 'all' !== $brand_slug ) ? get_term_by( 'slug', $brand_slug, 'brands' ) : null;
		$is_family   = ( $type_slug && 'all' !== $type_slug && taxonomy_exists( 'shoes_family' ) && term_exists( $type_slug, 'shoes_family' ) );
		$family_term = $is_family ? get_term_by( 'slug', $type_slug, 'shoes_family' ) : null;
		$type_term   = ( ! $is_family && $type_slug && 'all' !== $type_slug ) ? get_term_by( 'slug', $type_slug, 'shoes_type' ) : null;

		$base = trailingslashit( get_permalink( get_queried_object_id() ) );
		if ( $brand_term && $family_term ) {
			$canonical = $base . $brand_term->slug . '/' . $family_term->slug . '/';
			$self      = true;
		} elseif ( $brand_term ) {
			$canonical = $base . $brand_term->slug . '/all/';
			$self      = ( ! $type_slug || 'all' === $type_slug );
		} elseif ( $type_term ) {
			$canonical = $base . 'all/' . $type_term->slug . '/';
			$self      = true;
		} else {
			$canonical = $base;
			$self      = ( ! $brand_slug || 'all' === $brand_slug ) && ( ! $type_slug || 'all' === $type_slug );
		}
		return array( 'brand' => $brand_term, 'family' => $family_term, 'type' => $type_term, 'canonical' => $canonical, 'self' => $self );
	}
}

if ( ! function_exists( 'rsg_build_reviews_schema' ) ) {
	/**
	 * CollectionPage -> ItemList of the listed reviews + BreadcrumbList for the
	 * current self-canonical reviews view.  Returns array() on non-canonical
	 * views or when no items are listed.
	 */
	function rsg_build_reviews_schema() {
		$f = rsg_reviews_facet();
		if ( empty( $f ) || empty( $f['self'] ) ) { return array(); }
		$canonical = $f['canonical'];

		$label_parts = array();
		if ( $f['brand'] )  { $label_parts[] = ucfirst( $f['brand']->name ); }
		if ( $f['family'] ) { $label_parts[] = ucfirst( $f['family']->name ); }
		if ( $f['type'] )   { $label_parts[] = ucfirst( $f['type']->name ); }
		$label = trim( implode( ' ', $label_parts ) );
		$name  = ( '' !== $label ? $label . ' ' : '' ) . 'Running Shoes Reviews';

		// ItemList of the reviews shown on this page (lightweight: position/url/name).
		$items = array();
		$q     = isset( $GLOBALS['rsg_reviews_query'] ) ? $GLOBALS['rsg_reviews_query'] : null;
		if ( $q && ! empty( $q->posts ) ) {
			$pos = 1;
			foreach ( $q->posts as $rp ) {
				$rid = is_object( $rp ) ? $rp->ID : (int) $rp;
				$url = get_permalink( $rid );
				if ( ! $url ) { continue; }
				$brand_name = trim( (string) rsg_schema_brand_name( get_field( 'brand', $rid ) ) );
				if ( '' === $brand_name ) {
					$terms = get_the_terms( $rid, 'brands' );
					if ( is_array( $terms ) && ! empty( $terms ) && isset( $terms[0]->name ) ) { $brand_name = trim( (string) $terms[0]->name ); }
				}
				$model = trim( (string) get_post_meta( $rid, 'shoe_name', true ) );
				if ( '' === $model ) { $model = trim( (string) get_field( 'model', $rid ) ); }
				$iname = trim( $brand_name . ' ' . $model );
				if ( '' === $iname ) { $iname = trim( (string) get_the_title( $rid ) ); }
				$items[] = array( '@type' => 'ListItem', 'position' => $pos, 'url' => $url, 'name' => $iname );
				$pos++;
			}
		}
		if ( empty( $items ) ) { return array(); }

		$collection = array(
			'@context'   => 'https://schema.org/',
			'@type'      => 'CollectionPage',
			'@id'        => $canonical . '#collection',
			'url'        => $canonical,
			'name'       => $name,
			'isPartOf'   => array( '@type' => 'WebSite', 'name' => 'Running Shoes Guru', 'url' => 'https://www.runningshoesguru.com/' ),
			'publisher'  => rsg_publisher_org(),
			'mainEntity' => array(
				'@type'           => 'ItemList',
				'@id'             => $canonical . '#itemlist',
				'numberOfItems'   => count( $items ),
				'itemListElement' => $items,
			),
		);

		$crumbs = array(
			array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Home',    'item' => 'https://www.runningshoesguru.com/' ),
			array( '@type' => 'ListItem', 'position' => 2, 'name' => 'Reviews', 'item' => 'https://www.runningshoesguru.com/reviews/' ),
		);
		$p = 3;
		if ( $f['brand'] ) {
			$crumbs[] = array( '@type' => 'ListItem', 'position' => $p++, 'name' => ucfirst( $f['brand']->name ), 'item' => 'https://www.runningshoesguru.com/reviews/' . $f['brand']->slug . '/all/' );
		}
		if ( $f['family'] ) {
			$crumbs[] = array( '@type' => 'ListItem', 'position' => $p++, 'name' => ucfirst( $f['family']->name ), 'item' => $canonical );
		} elseif ( $f['type'] ) {
			$crumbs[] = array( '@type' => 'ListItem', 'position' => $p++, 'name' => ucfirst( $f['type']->name ), 'item' => $canonical );
		}
		$breadcrumb = array(
			'@context'        => 'https://schema.org/',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $crumbs,
		);

		return array( $collection, $breadcrumb );
	}
}

if ( ! function_exists( 'rsg_emit_reviews_schema' ) ) {
	/**
	 * Echoes the reviews-hub JSON-LD block. Call from <head>. No-op unless on a
	 * self-canonical reviews_search.php view.
	 */
	function rsg_emit_reviews_schema() {
		if ( ! is_page_template( 'reviews_search.php' ) ) { return; }
		$nodes = rsg_build_reviews_schema();
		if ( empty( $nodes ) ) { return; }
		$payload = ( count( $nodes ) === 1 ) ? $nodes[0] : $nodes;
		echo "\n<script type=\"application/ld+json\">\n";
		echo wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
		echo "\n</script>\n";
	}
}
