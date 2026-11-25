<?php
/**
 * Shoe categories: set shoes_type terms from category-mapping-v2.csv.
 * Usage (from a webroot):  wp eval-file terms.php <csv> [apply]
 * Without "apply" it changes nothing and prints what it would do.
 *
 * - creates daily-trainer, max-cushion, performance-trainer, trail-max-cushion, trail-race
 * - renames the labels of racing (Race) and training (Gym & training)
 * - per review: surface (road / trail / training) + category + stability, exactly as the CSV says;
 *   removes cushioning and any other managed term the CSV does not give it
 * - leaves lightweight, minimalist, motion-control (hidden old tags) as they are
 * - non-review posts (NON_REVIEWS) lose every shoes_type term
 * Direct SQL on term_relationships only: no post is saved, so post dates and
 * post hooks (cache purges) are untouched. Terms are recounted at the end.
 */
global $wpdb;
$csv   = $args[0] ?? '';
$apply = ( $args[1] ?? '' ) === 'apply';
$tax   = 'shoes_type';
const NON_REVIEWS = [ 11782, 11853, 12421, 26389 ]; // Ruggero 25 Nov: lose their type tag
const OLD_TAG_FIXES = [ [ 49537, 'minimalist' ], [ 34281, 'minimalist' ] ]; // PUMA Velocity Nitro 4 (2025) + ASICS Noosa Tri 13 (2021): wrongly tagged, they topped the Minimalist listing (look pass, 26 Nov)

$new    = [ 'daily-trainer' => 'Daily trainer', 'max-cushion' => 'Max cushion', 'performance-trainer' => 'Performance trainer', 'trail-max-cushion' => 'Trail – Max cushion', 'trail-race' => 'Trail – Race' ];
$rename = [ 'racing' => 'Race', 'training' => 'Gym & training' ];
$surf   = [ 'Road' => 'road', 'Trail' => 'trail', 'Gym' => 'training' ];
$cat    = [
	'Road'  => [ 'Daily trainer' => 'daily-trainer', 'Max cushion' => 'max-cushion', 'Performance trainer' => 'performance-trainer', 'Race' => 'racing' ],
	'Trail' => [ 'Daily trainer' => null, 'Max cushion' => 'trail-max-cushion', 'Race' => 'trail-race' ],
	'Gym'   => [ '' => null ],
];
$managed = array_merge( [ 'cushioning', 'racing', 'road', 'trail', 'training', 'stability' ], array_keys( $new ) );
$all_slugs = array_merge( $managed, [ 'lightweight', 'minimalist', 'motion-control' ] );

if ( ! is_readable( $csv ) ) { WP_CLI::error( "CSV not readable: $csv" ); }
$fh = fopen( $csv, 'r' ); $head = fgetcsv( $fh ); $rows = [];
while ( ( $r = fgetcsv( $fh ) ) !== false ) { $rows[] = array_combine( $head, $r ); }
WP_CLI::log( 'CSV rows: ' . count( $rows ) . ( $apply ? '  MODE: APPLY' : '  MODE: dry run' ) );

// 1. terms
foreach ( $new as $slug => $name ) {
	if ( ! term_exists( $slug, $tax ) ) {
		WP_CLI::log( "create $slug ($name)" );
		if ( $apply ) { $t = wp_insert_term( $name, $tax, [ 'slug' => $slug ] ); if ( is_wp_error( $t ) ) WP_CLI::error( $t->get_error_message() ); }
	}
}
foreach ( $rename as $slug => $name ) {
	$t = get_term_by( 'slug', $slug, $tax );
	if ( $t && $t->name !== $name ) {
		WP_CLI::log( "rename $slug: {$t->name} -> $name" );
		if ( $apply ) { $wpdb->update( $wpdb->terms, [ 'name' => $name ], [ 'term_id' => $t->term_id ] ); clean_term_cache( $t->term_id, $tax ); }
	}
}
$tt = []; // slug => term_taxonomy_id
foreach ( $all_slugs as $slug ) {
	$t = get_term_by( 'slug', $slug, $tax );
	if ( $t ) { $tt[ $slug ] = (int) $t->term_taxonomy_id; }
	elseif ( $apply ) { WP_CLI::error( "missing term $slug" ); }
	else { $tt[ $slug ] = -1 * ( array_search( $slug, $all_slugs ) + 1 ); } // placeholder in dry run
}
$by_tt = array_flip( $tt );

// 2. per review
$add = $del = []; $problems = []; $changed_posts = 0;
foreach ( $rows as $r ) {
	$id = (int) $r['id'];
	$p  = get_post( $id );
	if ( ! $p ) { $problems[] = "$id missing"; continue; }
	if ( $p->post_status !== 'publish' ) { $problems[] = "$id status {$p->post_status}"; }
	$want = [];
	if ( ! in_array( $id, NON_REVIEWS, true ) ) {
		$s = $r['surface'];
		if ( ! isset( $surf[ $s ] ) || ! array_key_exists( $r['category'], $cat[ $s ] ) ) { $problems[] = "$id bad surface/category '$s' / '{$r['category']}'"; continue; }
		$pt = [ 'reviews_road' => 'Road', 'reviews_trail' => 'Trail', 'reviews_training' => 'Gym' ];
		if ( isset( $pt[ $p->post_type ] ) && $pt[ $p->post_type ] !== $s ) { $problems[] = "$id post type {$p->post_type} vs surface $s"; }
		$want[] = $surf[ $s ];
		if ( $cat[ $s ][ $r['category'] ] ) { $want[] = $cat[ $s ][ $r['category'] ]; }
		if ( $r['stability'] === 'Stability' ) { $want[] = 'stability'; }
	}
	$have = $wpdb->get_col( $wpdb->prepare( "SELECT r.term_taxonomy_id FROM {$wpdb->term_relationships} r JOIN {$wpdb->term_taxonomy} x ON x.term_taxonomy_id=r.term_taxonomy_id AND x.taxonomy=%s WHERE r.object_id=%d", $tax, $id ) );
	$have_slugs = [];
	foreach ( $have as $h ) { $have_slugs[] = $by_tt[ (int) $h ] ?? ( 'tt' . $h ); }
	if ( in_array( 'motion-control', $have_slugs, true ) && ! in_array( 'stability', $want, true ) && ! in_array( $id, NON_REVIEWS, true ) ) { $problems[] = "$id motion-control without stability"; }
	$keep_old = in_array( $id, NON_REVIEWS, true ) ? [] : [ 'lightweight', 'minimalist', 'motion-control' ];
	foreach ( OLD_TAG_FIXES as $fx ) { if ( $fx[0] === $id ) { $keep_old = array_values( array_diff( $keep_old, [ $fx[1] ] ) ); } }
	$target = array_unique( array_merge( $want, array_values( array_intersect( $have_slugs, $keep_old ) ) ) );
	$to_add = array_diff( $target, $have_slugs ); $to_del = array_diff( $have_slugs, $target );
	if ( $to_add || $to_del ) { $changed_posts++; }
	foreach ( $to_add as $s ) { $add[] = [ $id, $s ]; }
	foreach ( $to_del as $s ) { $del[] = [ $id, $s ]; }
}
$c = function ( $list ) { $o = []; foreach ( $list as $x ) { $o[ $x[1] ] = ( $o[ $x[1] ] ?? 0 ) + 1; } ksort( $o ); return $o; };
WP_CLI::log( "posts changing: $changed_posts" );
WP_CLI::log( 'add: ' . json_encode( $c( $add ) ) );
WP_CLI::log( 'remove: ' . json_encode( $c( $del ) ) );
WP_CLI::log( 'problems (' . count( $problems ) . '): ' . implode( '; ', array_slice( $problems, 0, 40 ) ) );
if ( ! $apply ) { return; }
if ( $problems ) { WP_CLI::error( 'problems found, nothing applied' ); }

// 3. apply in one transaction
$wpdb->query( 'START TRANSACTION' );
foreach ( $del as $x ) {
	if ( ! isset( $tt[ $x[1] ] ) ) { $wpdb->query( 'ROLLBACK' ); WP_CLI::error( "unknown term {$x[1]} on {$x[0]}" ); }
	$wpdb->delete( $wpdb->term_relationships, [ 'object_id' => $x[0], 'term_taxonomy_id' => $tt[ $x[1] ] ] );
}
foreach ( $add as $x ) {
	$wpdb->query( $wpdb->prepare( "INSERT IGNORE INTO {$wpdb->term_relationships} (object_id, term_taxonomy_id, term_order) VALUES (%d, %d, 0)", $x[0], $tt[ $x[1] ] ) );
}
$wpdb->query( 'COMMIT' );
foreach ( array_unique( array_merge( array_column( $add, 0 ), array_column( $del, 0 ) ) ) as $id ) { wp_cache_delete( $id, $tax . '_relationships' ); }
$all_tt = $wpdb->get_col( $wpdb->prepare( "SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE taxonomy=%s", $tax ) );
wp_update_term_count_now( array_map( 'intval', $all_tt ), $tax );
clean_term_cache( array_map( 'intval', $all_tt ), $tax );
wp_cache_set_terms_last_changed();
WP_CLI::success( 'applied: ' . count( $add ) . ' added, ' . count( $del ) . ' removed; terms recounted' );
