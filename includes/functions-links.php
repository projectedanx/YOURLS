<?php
/**
 * YOURLS Link Functions
 *
 * This file contains functions that are used for handling links. These functions
 * are used to add, remove, and modify query arguments in URLs, as well as to
 * generate short links and stat links.
 *
 * @package YOURLS
 * @since 1.5
 */

/**
 * Adds a query argument to a URL.
 *
 * @since 1.5
 * @param string|array $param1 A query key or an associative array of query keys and values.
 * @param string       $param2 The query value.
 * @param string       $param3 Optional. The URL to add the query argument to.
 * @return string The modified URL.
 */
function yourls_add_query_arg() {
    $ret = '';
    if ( is_array( func_get_arg(0) ) ) {
        if ( @func_num_args() < 2 || false === @func_get_arg( 1 ) )
            $uri = $_SERVER['REQUEST_URI'];
        else
            $uri = @func_get_arg( 1 );
    } else {
        if ( @func_num_args() < 3 || false === @func_get_arg( 2 ) )
            $uri = $_SERVER['REQUEST_URI'];
        else
            $uri = @func_get_arg( 2 );
    }

    $uri = str_replace( '&amp;', '&', $uri );


    if ( $frag = strstr( $uri, '#' ) )
        $uri = substr( $uri, 0, -strlen( $frag ) );
    else
        $frag = '';

    if ( preg_match( '|^https?://|i', $uri, $matches ) ) {
        $protocol = $matches[0];
        $uri = substr( $uri, strlen( $protocol ) );
    } else {
        $protocol = '';
    }

    if ( strpos( $uri, '?' ) !== false ) {
        $parts = explode( '?', $uri, 2 );
        if ( 1 == count( $parts ) ) {
            $base = '?';
            $query = $parts[0];
        } else {
            $base = $parts[0] . '?';
            $query = $parts[1];
        }
    } elseif ( !empty( $protocol ) || strpos( $uri, '=' ) === false ) {
        $base = $uri . '?';
        $query = '';
    } else {
        $base = '';
        $query = $uri;
    }

    parse_str( $query, $qs );
    $qs = yourls_urlencode_deep( $qs ); // this re-URL-encodes things that were already in the query string
    if ( is_array( func_get_arg( 0 ) ) ) {
        $kayvees = func_get_arg( 0 );
        $qs = array_merge( $qs, $kayvees );
    } else {
        $qs[func_get_arg( 0 )] = func_get_arg( 1 );
    }

    foreach ( (array) $qs as $k => $v ) {
        if ( $v === false )
            unset( $qs[$k] );
    }

    $ret = http_build_query( $qs );
    $ret = trim( $ret, '?' );
    $ret = preg_replace( '#=(&|$)#', '$1', $ret );
    $ret = $protocol . $base . $ret . $frag;
    $ret = rtrim( $ret, '?' );
    return $ret;
}

/**
 * URL-encodes a string or every value in an array.
 *
 * @since 1.5
 * @param array|string $value The array or string to be encoded.
 * @return array|string The encoded array or string.
 */
function yourls_urlencode_deep( $value ) {
    $value = is_array( $value ) ? array_map( 'yourls_urlencode_deep', $value ) : urlencode( $value );
    return $value;
}

/**
 * Removes a query argument from a URL.
 *
 * @since 1.5
 * @param string|array $key   The query key or keys to remove.
 * @param string|bool  $query Optional. The URL to remove the query argument from.
 *                            Default false (current URL).
 * @return string The modified URL.
 */
function yourls_remove_query_arg( $key, $query = false ) {
    if ( is_array( $key ) ) { // removing multiple keys
        foreach ( $key as $k )
            $query = yourls_add_query_arg( $k, false, $query );
        return $query;
    }
    return yourls_add_query_arg( $key, false, $query );
}

/**
 * Converts a keyword into a short link or a stat link.
 *
 * @since 1.0
 * @param string $keyword The short URL keyword.
 * @param bool   $stats   Optional. True to return a stat link (e.g., 'http://sho.rt/abc+').
 *                        Default false.
 * @return string The short URL or stat link.
 */
function yourls_link( $keyword = '', $stats = false ) {
    $keyword = yourls_sanitize_keyword($keyword);
    if( $stats  === true ) {
        $keyword = $keyword . '+';
    }
    $link    = yourls_normalize_uri( yourls_get_yourls_site() . '/' . $keyword );

    if( yourls_is_ssl() ) {
        $link = yourls_set_url_scheme( $link, 'https' );
    }

    return yourls_apply_filter( 'yourls_link', $link, $keyword );
}

/**
 * Converts a keyword into a stat link.
 *
 * @since 1.0
 * @param string $keyword The short URL keyword.
 * @return string The stat link.
 */
function yourls_statlink( $keyword = '' ) {
    $link = yourls_link( $keyword, true );
    return yourls_apply_filter( 'yourls_statlink', $link, $keyword );
}

/**
 * Returns an admin URL.
 *
 * @since 1.0
 * @param string $page Optional. The admin page to link to. Default ''.
 * @return string The admin URL.
 */
function yourls_admin_url( $page = '' ) {
    $admin = yourls_get_yourls_site() . '/admin/' . $page;
    if( yourls_is_ssl() or yourls_needs_ssl() ) {
        $admin = yourls_set_url_scheme( $admin, 'https' );
    }
    return yourls_apply_filter( 'admin_url', $admin, $page );
}

/**
 * Returns the site URL.
 *
 * @since 1.0
 * @param bool   $echo Optional. Whether to echo the URL. Default true.
 * @param string $url  Optional. A path to append to the site URL. Default ''.
 * @return string The site URL.
 */
function yourls_site_url($echo = true, $url = '' ) {
    $url = yourls_get_relative_url( $url );
    $url = trim( yourls_get_yourls_site() . '/' . $url, '/' );

    // Do not enforce (checking yourls_need_ssl() ) but check current usage so it won't force SSL on non-admin pages
    if( yourls_is_ssl() ) {
        $url = yourls_set_url_scheme( $url, 'https' );
    }
    $url = yourls_apply_filter( 'site_url', $url );
    if( $echo ) {
        echo $url;
    }
    return $url;
}

/**
 * Returns the YOURLS site URL.
 *
 * @since 1.7.7
 * @return string The YOURLS site URL, trimmed and filtered.
 */
function yourls_get_yourls_site() {
    return yourls_apply_filter('get_yourls_site', trim(YOURLS_SITE, '/'));
}

/**
 * Changes the protocol of a URL to HTTPS if the current page is served over HTTPS.
 *
 * @since 1.5.1
 * @param string $url    The URL to modify.
 * @param string $normal Optional. The standard scheme. Default 'http://'.
 * @param string $ssl    Optional. The SSL scheme. Default 'https://'.
 * @return string The modified URL.
 */
function yourls_match_current_protocol( $url, $normal = 'http://', $ssl = 'https://' ) {
    // we're only doing something if we're currently serving through SSL and the input URL begins with 'http://' or 'https://'
    if( yourls_is_ssl() && in_array( yourls_get_protocol($url), array('http://', 'https://') ) ) {
        $url = str_replace( $normal, $ssl, $url );
    }

    return yourls_apply_filter( 'match_current_protocol', $url );
}

/**
 * Returns the URL of the favicon.
 *
 * This function auto-detects a custom favicon in the /user directory, and falls
 * back to the default YOURLS favicon if one is not found.
 *
 * @since 1.7.10
 * @param bool $echo Optional. Whether to echo the URL. Default true.
 * @return string The favicon URL.
 */
function yourls_get_yourls_favicon_url( $echo = true ) {
    static $favicon = null;

    if( $favicon !== null ) {
        if( $echo ) {
            echo $favicon;
        }
        return $favicon;
    }

    $custom = null;
    // search for favicon.(gif|ico|png|jpg|svg)
    foreach( array( 'gif', 'ico', 'png', 'jpg', 'svg' ) as $ext ) {
        if( file_exists( YOURLS_USERDIR. '/favicon.' . $ext ) ) {
            $custom = 'favicon.' . $ext;
            break;
        }
    }

    if( $custom ) {
        $favicon = yourls_site_url( false, YOURLS_USERURL . '/' . $custom );
    } else {
        $favicon = yourls_site_url( false ) . '/images/favicon.svg';
    }

    $favicon = yourls_apply_filter('get_favicon_url', $favicon);

    if( $echo ) {
        echo $favicon;
    }
    return $favicon;
}
