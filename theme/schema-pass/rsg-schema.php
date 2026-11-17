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
