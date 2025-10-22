<?php
/**
 * YOURLS Redirection
 *
 * This file handles the redirection of short URLs. It is the entry point for
 * all short URLs and is responsible for redirecting them to the corresponding
 * long URL.
 *
 * @package YOURLS
 * @since 1.0
 */

define( 'YOURLS_GO', true );
require_once( dirname( __FILE__ ) . '/includes/load-yourls.php' );

// Variables should be defined in yourls-loader.php
if( !isset( $keyword ) ) {
    yourls_do_action( 'redirect_no_keyword' );
    yourls_redirect( YOURLS_SITE, 301 );
}

$keyword = yourls_sanitize_keyword($keyword);

// if we have a page, display and exit
if( yourls_is_page($keyword) ) {
    yourls_page( $keyword );
    return;
}

// if we can get a long URL from the DB, redirect
if( $url = yourls_get_keyword_longurl( $keyword ) ) {
    yourls_redirect_shorturl($url, $keyword);
    return;
}

// Either reserved keyword, or no such keyword
yourls_do_action( 'redirect_keyword_not_found', $keyword );
yourls_redirect( YOURLS_SITE, 302 ); // no 404 to tell browser this might change, and also to not pollute logs
exit();
