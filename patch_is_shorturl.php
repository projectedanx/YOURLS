<?php
$content = file_get_contents('includes/functions-shorturls.php');

$search = <<<'SEARCH'
    // TODO: make sure this function evolves with the feature set.

    $is_short = false;

    // Is $shorturl a URL (http://sho.rt/abc) or a keyword (abc) ?
    if( yourls_get_protocol( $shorturl ) ) {
        $keyword = yourls_get_relative_url( $shorturl );
    } else {
        $keyword = $shorturl;
    }

    // Check if it's a valid && used keyword
    if( $keyword && $keyword == yourls_sanitize_keyword( $keyword ) && yourls_keyword_is_taken( $keyword ) ) {
        $is_short = true;
    }

    return yourls_apply_filter( 'is_shorturl', $is_short, $shorturl );
SEARCH;

$replace = <<<'REPLACE'
    $is_short = false;

    // Is $shorturl a URL (http://sho.rt/abc) or a keyword (abc) ?
    if( yourls_get_protocol( $shorturl ) ) {
        $keyword = yourls_get_relative_url( $shorturl );
    } else {
        $keyword = $shorturl;
    }

    // Unless request looks like a full URL (ie request is a simple keyword) strip query string
    if ( !preg_match( "@^[a-zA-Z]+://.+@", $keyword ) ) {
        $keyword = current( explode( '?', $keyword ) );
    }

    // Let's look at the request : what we want to catch here is "anything", or "anything+" / "anything+all" (stat page)
    preg_match( "@^(.+?)(\+(all)?)?/?$@", $keyword, $matches );
    $keyword = isset($matches[1]) ? $matches[1] : '';

    // Check if it's a valid && used keyword
    if( $keyword && $keyword == yourls_sanitize_keyword( $keyword ) && yourls_keyword_is_taken( $keyword ) ) {
        $is_short = true;
    }

    return yourls_apply_filter( 'is_shorturl', $is_short, $shorturl );
REPLACE;

$new_content = str_replace($search, $replace, $content);

file_put_contents('includes/functions-shorturls.php', $new_content);
