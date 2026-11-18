<?php
/**
 * Plugin Name: RSG PHP error log
 * Description: Turns PHP error logging on (LiteSpeed ignores .user.ini, so nothing was logged), writes to ~/rsg-logs/ outside the web root, serves a token-protected summary for the rsg-site check and emails a weekly digest. Source: rsg project hosting/rocket/rsg-php-errors.php (12 Nov 2026).
 * Version: 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ---------- 1. Logging (runs on every request, before plugins and theme) ---------- */
function rsg_pe_env() { return ( strpos( ABSPATH, 'staging' ) !== false ) ? 'staging' : 'live'; }
function rsg_pe_dir() {
	return preg_match( '#^(/home/[^/]+)/#', ABSPATH, $m ) ? $m[1] . '/rsg-logs' : WP_CONTENT_DIR . '/../../rsg-logs';
}
function rsg_pe_file() { return rsg_pe_dir() . '/php-errors-' . rsg_pe_env() . '.log'; }
// WP-CLI runs (our own scripts, wp eval) log to their own file, so they never reach the summary, the rsg-php check
// or the weekly digest (18 Nov 2026: one wp eval typo counted as a site fatal on 15 Nov).
function rsg_pe_cli_file() { return rsg_pe_dir() . '/php-errors-' . rsg_pe_env() . '-cli.log'; }

( function () {
	$dir = rsg_pe_dir();
	if ( ! is_dir( $dir ) ) { @mkdir( $dir, 0700, true ); }
	if ( is_dir( $dir ) && is_writable( $dir ) ) {
		@ini_set( 'log_errors', '1' );
		@ini_set( 'log_errors_max_len', '2048' );
		@ini_set( 'error_log', PHP_SAPI === 'cli' ? rsg_pe_cli_file() : rsg_pe_file() );
		if ( PHP_SAPI !== 'cli' ) @ini_set( 'display_errors', '0' );
		// Fatal errors are logged by PHP itself, every time. Warnings/notices repeat on every render of the old
		// theme (~35 lines/s at first): log each distinct one (type + file + line) once per hour only. "Seen" marks
		// live in the object cache (Redis); if it is flushed, a warning is simply logged again.
		set_error_handler( 'rsg_pe_handler', E_WARNING | E_USER_WARNING | E_NOTICE | E_USER_NOTICE | E_DEPRECATED | E_USER_DEPRECATED );
	}
} )();

function rsg_pe_handler( $no, $str, $file = '', $line = 0 ) {
	if ( ! ( error_reporting() & $no ) ) return false;            // @-suppressed or not reported: PHP default (nothing)
	static $seen = array();
	$key = md5( $no . '|' . $file . '|' . $line );
	if ( isset( $seen[ $key ] ) ) return true;
	$seen[ $key ] = 1;
	$hour = gmdate( 'YmdH' );
	$first = function_exists( 'wp_cache_add' ) && wp_using_ext_object_cache()
		? wp_cache_add( 'pe:' . $hour . ':' . $key, 1, 'rsg_php_errors', 7200 )
		: true;
	if ( $first ) {
		$types = array( E_WARNING => 'Warning', E_USER_WARNING => 'Warning', E_NOTICE => 'Notice', E_USER_NOTICE => 'Notice', E_DEPRECATED => 'Deprecated', E_USER_DEPRECATED => 'Deprecated' );
		error_log( 'PHP ' . ( $types[ $no ] ?? 'Warning' ) . ':  ' . $str . ' in ' . $file . ' on line ' . $line );
	}
	return true;
}

/* ---------- 2. Reading the log ---------- */
/** Parse the last $bytes of the log into entries: time (unix), type, msg, where. Multi-line stack traces are folded. */
function rsg_pe_entries( $since, $bytes = 2097152 ) {
	$out = array();
	foreach ( array( rsg_pe_file() . '.1', rsg_pe_file() ) as $f ) {
		if ( ! is_readable( $f ) ) continue;
		$size = filesize( $f );
		$h = fopen( $f, 'r' );
		if ( $size > $bytes ) { fseek( $h, $size - $bytes ); fgets( $h ); }
		while ( ( $line = fgets( $h ) ) !== false ) {
			if ( ! preg_match( '/^\[(\d\d-\w\w\w-\d{4} \d\d:\d\d:\d\d)( [^\]]+)?\] PHP ([A-Za-z ]+?):\s+(.*)$/', $line, $m ) ) continue;
			$t = strtotime( $m[1] . ( empty( $m[2] ) ? ' UTC' : $m[2] ) );
			if ( $t < $since ) continue;
			$msg = trim( $m[4] );
			$where = '';
			if ( preg_match( '/ in (\/\S+?)(?: on line |:)(\d+)/', $msg, $w ) ) $where = str_replace( ABSPATH, '', $w[1] ) . ':' . $w[2];
			$out[] = array( 'time' => $t, 'type' => $m[3], 'msg' => $msg, 'where' => $where );
		}
		fclose( $h );
	}
	return $out;
}
function rsg_pe_is_fatal( $type ) { return (bool) preg_match( '/Fatal|Parse|Recoverable|Core error|Compile error/i', $type ); }
function rsg_pe_signature( $e ) {
	$m = preg_replace( array( '/ in \/\S+.*$/s', '/Stack trace:.*$/s', '/\d+/' ), array( '', '', 'N' ), $e['msg'] );
	return $e['type'] . ' | ' . trim( mb_substr( $m, 0, 160 ) ) . ( $e['where'] ? ' @ ' . $e['where'] : '' );
}

/* ---------- 3. Summary endpoint for the rsg-site check (rsgmedia) ---------- */
add_action( 'rest_api_init', function () {
	register_rest_route( 'rsg/v1', '/php-errors', array(
		'methods'             => 'GET',
		'permission_callback' => function ( $req ) {
			$tok = (string) get_option( 'rsg_php_errors_token', '' );
			return strlen( $tok ) >= 32 && hash_equals( $tok, (string) $req->get_header( 'x_rsg_check' ) );
		},
		'callback'            => function () {
			$now = time(); $fatal = array(); $warn = 0;
			foreach ( rsg_pe_entries( $now - 86400 ) as $e ) {
				if ( rsg_pe_is_fatal( $e['type'] ) ) { if ( $e['time'] >= $now - 3600 ) $fatal[] = $e; }
				else $warn++;
			}
			$last = $fatal ? end( $fatal ) : null;
			$res = new WP_REST_Response( array(
				'env'         => rsg_pe_env(),
				'logging'     => ini_get( 'log_errors' ) === '1' && ini_get( 'error_log' ) === rsg_pe_file(),
				'fatal_60m'   => count( $fatal ),
				'last_fatal'  => $last ? mb_substr( strtok( $last['msg'], "\n" ), 0, 300 ) : null,
				'warnings_24h'=> $warn,                                   // distinct warning-hours, not occurrences
				'log_bytes'   => is_readable( rsg_pe_file() ) ? filesize( rsg_pe_file() ) : 0,
			) );
			$res->header( 'Cache-Control', 'no-store' );
			return $res;
		},
	) );
} );

/* ---------- 4. Daily rotation + weekly digest (WP-Cron) ---------- */
add_action( 'init', function () {
	if ( ! wp_next_scheduled( 'rsg_pe_daily' ) ) wp_schedule_event( strtotime( 'tomorrow 05:20 UTC' ), 'daily', 'rsg_pe_daily' );
} );
add_action( 'rsg_pe_daily', function () {
	$f = rsg_pe_file();
	// Weekly digest on Mondays (live only), before rotating.
	if ( gmdate( 'N' ) === '1' && rsg_pe_env() === 'live' ) rsg_pe_send_digest();
	// Rotate when over 5 MB: keep one older file (.1).
	foreach ( array( $f, rsg_pe_cli_file() ) as $f ) {
		if ( is_readable( $f ) && filesize( $f ) > 5 * 1048576 ) { @rename( $f, $f . '.1' ); }
	}
} );

function rsg_pe_digest_text( $days = 7 ) {
	$since = time() - $days * 86400; $sig = array(); $n = array( 'fatal' => 0, 'other' => 0 );
	foreach ( rsg_pe_entries( $since, 8388608 ) as $e ) {
		$k = rsg_pe_signature( $e );
		if ( ! isset( $sig[ $k ] ) ) $sig[ $k ] = array( 'n' => 0, 'last' => 0, 'where' => $e['where'], 'fatal' => rsg_pe_is_fatal( $e['type'] ) );
		$sig[ $k ]['n']++; $sig[ $k ]['last'] = max( $sig[ $k ]['last'], $e['time'] );
		$n[ rsg_pe_is_fatal( $e['type'] ) ? 'fatal' : 'other' ]++;
	}
	if ( ! $sig ) return null;
	uasort( $sig, function ( $a, $b ) { return ( $b['fatal'] <=> $a['fatal'] ) ?: ( $b['n'] <=> $a['n'] ); } );
	$lines = array( sprintf( "PHP errors on www.runningshoesguru.com, last %d days: %d fatal, %d distinct warnings/notices.\nFatal: count of occurrences. Warnings: number of hours in which each was seen (logged once per hour).\n", $days, $n['fatal'], count( $sig ) - count( array_filter( $sig, function ( $x ) { return $x['fatal']; } ) ) ) );
	$i = 0;
	foreach ( $sig as $k => $s ) {
		if ( ++$i > 20 ) { $lines[] = '… and ' . ( count( $sig ) - 20 ) . ' more.'; break; }
		$lines[] = sprintf( '%5d x  %s  (last %s UTC)', $s['n'], $k, gmdate( 'd M H:i', $s['last'] ) );
	}
	$lines[] = "\nFull log on Rocket.net: ~/rsg-logs/php-errors-live.log (SSH). Fatal errors also fail the rsg-site healthcheck for an hour. Source: rsg project hosting/rocket/rsg-php-errors.php";
	return implode( "\n", $lines );
}
function rsg_pe_send_digest() {
	$to = (string) get_option( 'rsg_php_errors_to', '' );
	$text = rsg_pe_digest_text( 7 );
	if ( ! $to || $text === null || ! defined( 'RSG_MAILERSEND_TOKEN' ) || ! RSG_MAILERSEND_TOKEN ) return 'skipped';
	$resp = wp_remote_post( 'https://api.mailersend.com/v1/email', array(
		'timeout' => 15,
		'headers' => array( 'Authorization' => 'Bearer ' . RSG_MAILERSEND_TOKEN, 'Content-Type' => 'application/json' ),
		'body'    => wp_json_encode( array(
			'from'    => array( 'email' => 'newsletter@runningshoesguru.com', 'name' => 'RSG site' ),
			'to'      => array( array( 'email' => $to ) ),
			'subject' => 'RSG weekly PHP errors: ' . strtok( $text, "\n" ),
			'text'    => $text,
		) ),
	) );
	return is_wp_error( $resp ) ? $resp->get_error_message() : (string) wp_remote_retrieve_response_code( $resp );
}

/* ---------- 5. WP-CLI: wp rsg-php-errors summary|digest|send-digest ---------- */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'rsg-php-errors', function ( $args ) {
		$cmd = $args[0] ?? 'summary';
		if ( $cmd === 'send-digest' ) { WP_CLI::log( rsg_pe_send_digest() ); return; }
		$t = rsg_pe_digest_text( $cmd === 'digest' ? 7 : 1 );
		WP_CLI::log( $t ?? 'No PHP errors logged in that period. Log: ' . rsg_pe_file() );
	} );
}
