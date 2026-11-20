<?php
/* Trust details, 20 Nov 2026 (theme/BRIEF-TRUST-DETAILS-2026-11-20.md). Database part, same script on staging and live:
 *   wp eval-file apply-2026-11-20.php <file with the new About content (from update-2026-11-20.py)>
 * 1. About page 116: new content, slug about-2 -> about, 301 /about-2/ -> /about/ (Simple 301 Redirects), footer menu link.
 * 2. Typos in listing descriptions (brands + old categories): Healty, Kirachiro, Bowermann, lightweigth.
 * 3. Review count cache cleared (theme now counts shoe reviews only).
 * Backups are taken before this runs (~/rsg_deploy_backups/trust-details-20261120/). */
$file = $args[0] ?? '';
$new  = is_readable( $file ) ? file_get_contents( $file ) : '';
if ( '' === trim( $new ) ) { WP_CLI::error( 'no content file' ); }

$r = wp_update_post( array( 'ID' => 116, 'post_content' => $new, 'post_name' => 'about' ), true );
if ( is_wp_error( $r ) ) { WP_CLI::error( $r->get_error_message() ); }
WP_CLI::log( 'about: ' . get_permalink( 116 ) );

$red = get_option( '301_redirects', array() );
$red['/about-2/'] = home_url( '/about/' );
update_option( '301_redirects', $red );

global $wpdb;
foreach ( $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_menu_item_url' AND meta_value LIKE '%/about-2/'" ) as $mid ) {
	update_post_meta( (int) $mid, '_menu_item_url', home_url( '/about/' ) );
	WP_CLI::log( "menu link $mid" );
}

$fix = array( 'Healty' => 'Healthy', 'Kirachiro' => 'Kihachiro', 'Bowermann' => 'Bowerman', 'lightweigth' => 'lightweight' );
foreach ( $wpdb->get_results( "SELECT term_id, taxonomy, description FROM {$wpdb->term_taxonomy} WHERE description REGEXP 'Healty|Kirachiro|Bowermann|lightweigth'" ) as $t ) {
	$d = str_replace( array_keys( $fix ), array_values( $fix ), $t->description );
	$wpdb->update( $wpdb->term_taxonomy, array( 'description' => $d ), array( 'term_id' => $t->term_id, 'taxonomy' => $t->taxonomy ) );
	clean_term_cache( (int) $t->term_id, $t->taxonomy );
	WP_CLI::log( "term {$t->term_id} {$t->taxonomy} fixed" );
}
delete_transient( 'rsg_home_counts' );
WP_CLI::success( 'done' );
