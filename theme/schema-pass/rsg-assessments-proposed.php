<?php
/**
 * PROPOSED ADDITION to rsg-schema.php — tester-assessment properties.
 *
 * Adds the editorial "Size/Fit", "Cushioning & ride" and "Usage" judgments to
 * the JSON-LD as PropertyValue entries placed INSIDE the Review node
 * (`$review['additionalProperty']`), so machines read them as RSG's tester
 * opinion (the Review carries author + publisher), not manufacturer spec.
 *
 * Verified 2026-06-22 against live posts via wp eval-file:
 *   reviews_road     -> 9 props   (sizing key resolved; usage = label strings)
 *   reviews_training -> 7 props   (blank cushioning_type / usage skipped)
 *   reviews_trail    -> 20 props  (adds lugs + usage_these_shoes_for + good_for_running,
 *                                  both stored as {value,label} arrays — normalized)
 *   reviews_gps      -> 0 props   (no block emitted; not applicable)
 *
 * Values are text only (per decision): the controlled-vocab label as stored,
 * ucfirst()'d. No numeric scale encoded.
 *
 * INTEGRATION: paste the four functions below into includes/rsg-schema.php, then
 * inside rsg_build_review_schema(), right after the $review array is assembled
 * (after the reviewRating block, before the Product node), add:
 *
 *     $assessments = rsg_build_review_assessments( $pid );
 *     if ( ! empty( $assessments ) ) { $review['additionalProperty'] = $assessments; }
 *
 * Frontend-invisible. Additive. Rollback = remove the four functions + that line.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'rsg_review_assessment_map' ) ) {
	/** Per-type field map. Empty array => no assessment block (e.g. GPS watches). */
	function rsg_review_assessment_map( $pt ) {
		$base_fit  = array( 'sizing', 'heel_fit', 'midfoot_fit', 'toebox_fit' );
		$base_ride = array( 'cushioning_type', 'cushioning_amount', 'stability', 'flexibility' );
		switch ( $pt ) {
			case 'reviews_road':
			case 'reviews_training':
				return array( 'fit' => $base_fit, 'ride' => $base_ride, 'usage' => array( 'usage' ) );
			case 'reviews_trail':
				return array(
					'fit'   => $base_fit,
					'ride'  => array_merge( $base_ride, array( 'lugs' ) ),
					'usage' => array( 'usage_these_shoes_for', 'good_for_running' ),
				);
			default:
				return array();
		}
	}
}

if ( ! function_exists( 'rsg_assessment_label' ) ) {
	/** Radio field -> human label. Resolves sizing's numeric key via choices. */
	function rsg_assessment_label( $pid, $field ) {
		$o = get_field_object( $field, $pid );
		if ( ! $o ) { return ''; }
		$val = isset( $o['value'] ) ? $o['value'] : '';
		if ( '' === $val || null === $val ) { return ''; }
		if ( ! empty( $o['choices'] ) && isset( $o['choices'][ $val ] ) ) {
			return (string) $o['choices'][ $val ];
		}
		return is_string( $val ) ? $val : '';
	}
}

if ( ! function_exists( 'rsg_assessment_usage_values' ) ) {
	/** Checkbox field -> flat array of selected labels. Handles both ACF storage
	 *  formats: plain label strings (road/training `usage`) and {value,label}
	 *  object arrays (trail `usage_these_shoes_for` / `good_for_running`). */
	function rsg_assessment_usage_values( $pid, $field ) {
		$raw = get_field( $field, $pid );
		if ( empty( $raw ) || ! is_array( $raw ) ) { return array(); }
		$out = array();
		foreach ( $raw as $item ) {
			if ( is_array( $item ) ) {
				$out[] = isset( $item['label'] ) ? $item['label'] : ( isset( $item['value'] ) ? $item['value'] : '' );
			} else {
				$out[] = (string) $item;
			}
		}
		return array_values( array_filter( array_map( 'trim', $out ), 'strlen' ) );
	}
}

if ( ! function_exists( 'rsg_build_review_assessments' ) ) {
	/** Returns an array of schema.org PropertyValue arrays for the post's tester
	 *  judgments, or array() when not applicable. */
	function rsg_build_review_assessments( $pid ) {
		$map = rsg_review_assessment_map( get_post_type( $pid ) );
		if ( empty( $map ) ) { return array(); }

		$props = array();
		$nice  = array(
			'sizing' => 'Sizing', 'heel_fit' => 'Heel fit', 'midfoot_fit' => 'Midfoot fit', 'toebox_fit' => 'Toebox fit',
			'cushioning_type' => 'Type of cushioning', 'cushioning_amount' => 'Amount of cushioning',
			'stability' => 'Stability', 'flexibility' => 'Flexibility', 'lugs' => 'Lugs',
		);

		foreach ( array( 'fit', 'ride' ) as $grp ) {
			if ( empty( $map[ $grp ] ) ) { continue; }
			foreach ( $map[ $grp ] as $f ) {
				$label = rsg_assessment_label( $pid, $f );
				if ( '' === $label ) { continue; }
				$props[] = array(
					'@type'      => 'PropertyValue',
					'propertyID' => ( 'fit' === $grp ? 'size-fit' : 'cushioning-ride' ),
					'name'       => isset( $nice[ $f ] ) ? $nice[ $f ] : ucwords( str_replace( '_', ' ', $f ) ),
					'value'      => ucfirst( $label ),
				);
			}
		}

		$usage_names = array(
			'usage'                 => 'Recommended usage',
			'usage_these_shoes_for' => 'Recommended usage',
			'good_for_running'      => 'Good for running',
		);
		if ( ! empty( $map['usage'] ) ) {
			foreach ( $map['usage'] as $uf ) {
				foreach ( rsg_assessment_usage_values( $pid, $uf ) as $v ) {
					$props[] = array(
						'@type'      => 'PropertyValue',
						'propertyID' => 'usage',
						'name'       => isset( $usage_names[ $uf ] ) ? $usage_names[ $uf ] : 'Recommended usage',
						'value'      => ucfirst( $v ),
					);
				}
			}
		}

		return $props;
	}
}
