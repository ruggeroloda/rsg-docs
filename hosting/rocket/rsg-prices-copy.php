<?php
/**
 * Copy the DFS price meta (_rsg_prices) between the live and staging WordPress on the same server (15 Nov 2026).
 *   export: wp eval-file rsg-prices-copy.php export <file.json>   (read only)
 *   import: wp eval-file rsg-prices-copy.php import <file.json>   (writes _rsg_prices of the posts that exist here)
 * The receiver stores the JSON text as-is via update_post_meta; values are passed slashed so WordPress stores them unchanged.
 */
$mode = $args[0] ?? '';
$file = $args[1] ?? '';
global $wpdb;
if ( 'export' === $mode ) {
	$rows = $wpdb->get_results( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_rsg_prices'", ARRAY_A );
	$out  = array();
	foreach ( $rows as $r ) { $out[ (int) $r['post_id'] ] = $r['meta_value']; }
	file_put_contents( $file, wp_json_encode( $out ) );
	WP_CLI::success( count( $out ) . ' rows exported to ' . $file );
} elseif ( 'import' === $mode ) {
	$in = json_decode( (string) file_get_contents( $file ), true );
	if ( ! is_array( $in ) || count( $in ) < 100 ) { WP_CLI::error( 'refused: fewer than 100 rows in ' . $file ); }
	$n = 0; $same = 0; $missing = 0;
	foreach ( $in as $pid => $v ) {
		if ( ! get_post( (int) $pid ) ) { $missing++; continue; }
		if ( get_post_meta( (int) $pid, '_rsg_prices', true ) === $v ) { $same++; continue; }
		update_post_meta( (int) $pid, '_rsg_prices', wp_slash( $v ) );
		if ( get_post_meta( (int) $pid, '_rsg_prices', true ) !== $v ) { WP_CLI::warning( "post $pid: stored value differs" ); }
		$n++;
	}
	WP_CLI::success( "$n updated, $same already equal, $missing posts not on this site" );
} else {
	WP_CLI::error( 'usage: export|import <file.json>' );
}
