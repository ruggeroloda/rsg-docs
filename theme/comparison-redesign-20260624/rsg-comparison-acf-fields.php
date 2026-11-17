<?php
/**
 * RSG — Comparison "new design" ACF fields (local field group, code-registered).
 *
 * Makes the comparison-redesign controls editable in wp-admin without any DB change.
 * Field NAMES below are intentionally identical to the meta keys the redesign reads:
 *   - comparison_redesign  -> single-comparison.php  (get_post_meta truthy flag)
 *   - quick_verdict        -> comparison-redesign-block.php (rsg_cr_meta: get_field, meta fallback)
 *   - buy_if_1 / buy_if_2  -> comparison-redesign-block.php (rsg_cr_meta)
 *
 * Backward-compatible with the 3 WP-CLI pilots (28982/28311/28070): ACF resolves the
 * field by name and reads their existing post meta; values were never lost.
 *
 * @package rsg_2022
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'acf/init', 'rsg_register_comparison_redesign_fields' );
function rsg_register_comparison_redesign_fields() {

	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return; // ACF not loaded yet / disabled.
	}

	acf_add_local_field_group( array(
		'key'    => 'group_rsg_comparison_redesign',
		'title'  => 'Comparison — New Design',
		'show_in_rest' => 1,
		'fields' => array(

			array(
				'key'           => 'field_rsg_comparison_redesign',
				'label'         => 'Use new comparison design',
				'name'          => 'comparison_redesign',
				'type'          => 'true_false',
				'instructions'  => 'Turn ON to render this comparison with the redesigned layout (verdict, unified shoe module, score radar, decision aids, photo bands). Leave OFF for the classic table layout.',
				'required'      => 0,
				'message'       => 'Use the redesigned comparison layout',
				'default_value' => 0,
				'ui'            => 1,
				'ui_on_text'    => 'New design',
				'ui_off_text'   => 'Classic',
			),

			array(
				'key'               => 'field_rsg_quick_verdict',
				'label'             => 'The verdict, in short',
				'name'              => 'quick_verdict',
				'type'              => 'textarea',
				'instructions'      => 'Short TL;DR shown at the top of the redesigned page ("The verdict, in short"). 1–3 sentences. Only used when the new design is ON.',
				'required'          => 0,
				'rows'              => 4,
				'new_lines'         => 'wpautop',
				'conditional_logic' => array(
					array(
						array(
							'field'    => 'field_rsg_comparison_redesign',
							'operator' => '==',
							'value'    => '1',
						),
					),
				),
			),

			array(
				'key'               => 'field_rsg_buy_if_1',
				'label'             => 'Buy the FIRST shoe if…',
				'name'              => 'buy_if_1',
				'type'              => 'text',
				'instructions'      => 'One short line for the first shoe\'s card ("Buy if …"). E.g. "you want a softer, max-cushion daily trainer." Only used when the new design is ON.',
				'required'          => 0,
				'maxlength'         => 160,
				'conditional_logic' => array(
					array(
						array(
							'field'    => 'field_rsg_comparison_redesign',
							'operator' => '==',
							'value'    => '1',
						),
					),
				),
			),

			array(
				'key'               => 'field_rsg_buy_if_2',
				'label'             => 'Buy the SECOND shoe if…',
				'name'              => 'buy_if_2',
				'type'              => 'text',
				'instructions'      => 'One short line for the second shoe\'s card ("Buy if …"). Only used when the new design is ON.',
				'required'          => 0,
				'maxlength'         => 160,
				'conditional_logic' => array(
					array(
						array(
							'field'    => 'field_rsg_comparison_redesign',
							'operator' => '==',
							'value'    => '1',
						),
					),
				),
			),
		),
		'location' => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'comparison',
				),
			),
		),
		'menu_order'            => 0,
		'position'              => 'acf_after_title',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'label',
		'active'                => true,
		'description'           => 'Controls for the redesigned comparison layout (code-registered, rsg_2022 theme).',
	) );
}
