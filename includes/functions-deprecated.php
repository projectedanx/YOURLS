<?php
/**
 * Deprecated functions from past YOURLS versions. Don't use them, as they may be
 * removed in a later version. Use the newer alternatives instead.
 *
 * Note to devs: when deprecating a function, move it here. Then check all the places
 * in core that might be using it, including core plugins.
 *
 * Usage :  yourls_deprecated_function( 'function_name', 'version', 'replacement' );
 * Output:  "{function_name} is deprecated since version {version}! Use {replacement} instead."
 *
 * Usage :  yourls_deprecated_function( 'function_name', 'version' );
 * Output:  "{function_name} is deprecated since version {version} with no alternative available."
 *
 * @see yourls_deprecated_function()
 */

// @codeCoverageIgnoreStart

/**
 * Activates a plugin in a sandboxed environment.
 *
 * @since 1.8.3
 * @deprecated 1.9.2
 * @param string $pluginfile The path to the plugin file.
 * @return bool|string True on success, an error message on failure.
 */
function yourls_activate_plugin_sandbox( $pluginfile ) {
    yourls_deprecated_function( __FUNCTION__, '1.9.1', 'yourls_include_file_sandbox');
    return yourls_include_file_sandbox($pluginfile);
}

/**
 * Gets the current admin page.
 *
 * @since 1.6
 * @deprecated 1.9.1
 * @return string|null The current admin page, or null if not on an admin page.
 */
function yourls_current_admin_page() {
    yourls_deprecated_function( __FUNCTION__, '1.9.1' );
    if( yourls_is_admin() ) {
        $current = substr( yourls_get_request(), 6 );
        if( $current === false )
            $current = 'index.php'; // if current page is http://sho.rt/admin/ instead of http://sho.rt/admin/index.php

        return $current;
    }
    return null;
}

/**
 * Encodes a URI.
 *
 * @since 1.0
 * @deprecated 1.9.1
 * @param string $url The URI to encode.
 * @return string The encoded URI.
 */
function yourls_encodeURI($url) {
    yourls_deprecated_function( __FUNCTION__, '1.9.1', '' );
    // Decode URL all the way
    $result = yourls_rawurldecode_while_encoded( $url );
    // Encode once
    $result = strtr( rawurlencode( $result ), array (
        '%3B' => ';', '%2C' => ',', '%2F' => '/', '%3F' => '?', '%3A' => ':', '%40' => '@',
        '%26' => '&', '%3D' => '=', '%2B' => '+', '%24' => '$', '%21' => '!', '%2A' => '*',
        '%27' => '\'', '%28' => '(', '%29' => ')', '%23' => '#',
    ) );
    // @TODO:
    // Known limit: this will most likely break IDN URLs such as http://www.académie-française.fr/
    // To fully support IDN URLs, advocate use of a plugin.
    return yourls_apply_filter( 'encodeURI', $result, $url );
}

/**
 * Checks if a file is a plugin file.
 *
 * @since 1.5
 * @deprecated 1.8.3
 * @param string $file The path to the file.
 * @return bool True if the file is a plugin file, false otherwise.
 */
function yourls_validate_plugin_file( $file ) {
    yourls_deprecated_function( __FUNCTION__, '1.8.3', 'yourls_is_a_plugin_file' );
    return yourls_is_a_plugin_file($file);
}

/**
 * Converts a string to a valid HTML ID.
 *
 * @since 1.0
 * @deprecated 1.8.3
 * @param string $string The string to convert.
 * @return string The HTML ID.
 */
function yourls_string2htmlid( $string ) {
    yourls_deprecated_function( __FUNCTION__, '1.8.3', 'yourls_unique_element_id' );
    return yourls_apply_filter( 'string2htmlid', 'y'.abs( crc32( $string ) ) );
}

/**
 * Gets the search text from the query string.
 *
 * @since 1.7
 * @deprecated 1.8.2
 * @return string The search text.
 */
function yourls_get_search_text() {
    yourls_deprecated_function( __FUNCTION__, '1.8.2', 'YOURLS\Views\AdminParams::get_search' );
    $view_params = new YOURLS\Views\AdminParams();
    return $view_params->get_search();
}

/**
 * Gets the current time.
 *
 * @since 1.6
 * @deprecated 1.7.10
 * @param string   $type The type of time to retrieve. Can be 'mysql' or 'timestamp'.
 * @param int|bool $gmt  Whether to use GMT timezone.
 * @return string The current time.
 */
function yourls_current_time( $type, $gmt = 0 ) {
    yourls_deprecated_function( __FUNCTION__, '1.7.10', 'yourls_get_timestamp' );
    switch ( $type ) {
        case 'mysql':
            return ( $gmt ) ? gmdate( 'Y-m-d H:i:s' ) : gmdate( 'Y-m-d H:i:s', yourls_get_timestamp( time() ));
        case 'timestamp':
            return ( $gmt ) ? time() : yourls_get_timestamp( time() );
    }
}

/**
 * Lowercases the scheme and domain of a URI.
 *
 * @since 1.0
 * @deprecated 1.7.10
 * @param string $url The URL to modify.
 * @return string The modified URL.
 */
function yourls_lowercase_scheme_domain( $url ) {
    yourls_deprecated_function( __FUNCTION__, '1.7.10', 'yourls_normalize_uri' );
    return yourls_normalize_uri( $url );
}

/**
 * Sanitizes a string.
 *
 * @since 1.0
 * @deprecated 1.7.10
 * @param string $string The string to sanitize.
 * @param bool   $restrict_to_shorturl_charset Whether to restrict the string to the short URL character set.
 * @return string The sanitized string.
 */
function yourls_sanitize_string( $string, $restrict_to_shorturl_charset = false ) {
    yourls_deprecated_function( __FUNCTION__, '1.7.10', 'yourls_sanitize_keyword' );
    return yourls_sanitize_keyword( $string, $restrict_to_shorturl_charset );
}

/**
 * Gets the favicon URL.
 *
 * @since 1.0
 * @deprecated 1.7.10
 * @param bool $echo Whether to echo the URL.
 * @return string The favicon URL.
 */
function yourls_favicon( $echo = true ) {
    yourls_deprecated_function( __FUNCTION__, '1.7.10', 'yourls_get_yourls_favicon_url' );
    return yourls_get_yourls_favicon_url( $echo );
}

/**
 * Gets the stats for a link.
 *
 * @since 1.0
 * @deprecated 1.7.10
 * @param string $url The short URL keyword.
 * @return array An array of stats.
 */
function yourls_get_link_stats( $url ) {
    yourls_deprecated_function( __FUNCTION__, '1.7.10', 'yourls_get_keyword_stats' );
    return yourls_get_keyword_stats( $url );
}

/**
 * Checks if a long URL exists in the database.
 *
 * @since 1.5.1
 * @deprecated 1.7.10
 * @param string $url The long URL to check.
 * @return object|null An object with URL information if it exists, null otherwise.
 */
function yourls_url_exists( $url ) {
    yourls_deprecated_function( __FUNCTION__, '1.7.10', 'yourls_long_url_exists' );
    return yourls_long_url_exists( $url );
}

/**
 * Returns the singular or plural form of a word.
 *
 * @since 1.0
 * @deprecated 1.6
 * @param string $word  The word to pluralize.
 * @param int    $count The number of items.
 * @return string The pluralized word.
 */
function yourls_plural( $word, $count=1 ) {
    yourls_deprecated_function( __FUNCTION__, '1.6', 'yourls_n' );
    return $word . ($count > 1 ? 's' : '');
}

/**
 * Gets all short URLs associated with a long URL.
 *
 * @since 1.0
 * @deprecated 1.7
 * @param string $longurl The long URL.
 * @return array|null An array of short URL keywords, or null if duplicates are not allowed.
 */
function yourls_get_duplicate_keywords( $longurl ) {
    yourls_deprecated_function( __FUNCTION__, '1.7', 'yourls_get_longurl_keywords' );
    if( !yourls_allow_duplicate_longurls() )
        return NULL;
    return yourls_apply_filter( 'get_duplicate_keywords', yourls_get_longurl_keywords ( $longurl ), $longurl );
}

/**
 * Sanitizes an integer.
 *
 * @since 1.0
 * @deprecated 1.7
 * @param int $int The integer to sanitize.
 * @return int The sanitized integer.
 */
function yourls_intval( $int ) {
    yourls_deprecated_function( __FUNCTION__, '1.7', 'yourls_sanitize_int' );
    return yourls_escape( $int );
}

/**
 * Gets the content of a remote URL.
 *
 * @since 1.0
 * @deprecated 1.7
 * @param string $url     The URL to get the content from.
 * @param int    $maxlen  The maximum length of the content to get.
 * @param int    $timeout The timeout for the request.
 * @return string The content of the remote URL.
 */
function yourls_get_remote_content( $url,  $maxlen = 4096, $timeout = 5 ) {
    yourls_deprecated_function( __FUNCTION__, '1.7', 'yourls_http_get_body' );
    return yourls_http_get_body( $url );
}

/**
 * Applies a filter to a value.
 *
 * @since 1.6
 * @deprecated 1.7.1
 * @param string $hook  The name of the filter to apply.
 * @param mixed  $value The value to filter.
 * @return mixed The filtered value.
 */
function yourls_apply_filters( $hook, $value = '' ) {
    yourls_deprecated_function( __FUNCTION__, '1.7.1', 'yourls_apply_filter' );
    return yourls_apply_filter( $hook, $value );
}

/**
 * Checks if the interface should be displayed.
 *
 * @since 1.0
 * @deprecated 1.7.1
 * @return bool True if the interface should be displayed, false otherwise.
 */
function yourls_has_interface() {
    yourls_deprecated_function( __FUNCTION__, '1.7.1' );
    if( yourls_is_API() or yourls_is_GO() )
        return false;
    return true;
}

/**
 * Checks if a proxy is defined.
 *
 * @since 1.7
 * @deprecated 1.7.1
 * @return bool True if a proxy is defined, false otherwise.
 */
function yourls_http_proxy_is_defined() {
    yourls_deprecated_function( __FUNCTION__, '1.7.1', 'yourls_http_get_proxy' );
    return yourls_apply_filter( 'http_proxy_is_defined', defined( 'YOURLS_PROXY' ) );
}

/**
 * Displays a translated string with context.
 *
 * @since 1.6
 * @deprecated 1.7.1
 * @param string $text    The text to translate.
 * @param string $context The context of the text.
 * @param string $domain  The domain to retrieve the translated text from.
 * @return void
 */
function yourls_ex( $text, $context, $domain = 'default' ) {
    yourls_deprecated_function( __FUNCTION__, '1.7.1', 'yourls_xe' );
    echo yourls_xe( $text, $context, $domain );
}

/**
 * Escapes a string or an array of strings for use in a database query.
 *
 * @since 1.0
 * @deprecated 1.7.3
 * @param string|array $data The string or array of strings to escape.
 * @return string|array The escaped string or array of strings.
 */
function yourls_escape( $data ) {
    yourls_deprecated_function( __FUNCTION__, '1.7.3', 'PDO' );
    if( is_array( $data ) ) {
        foreach( $data as $k => $v ) {
            if( is_array( $v ) ) {
                $data[ $k ] = yourls_escape( $v );
            } else {
                $data[ $k ] = yourls_escape_real( $v );
            }
        }
    } else {
        $data = yourls_escape_real( $data );
    }

    return $data;
}

/**
 * Escapes a string for use in a database query.
 *
 * @since 1.7
 * @deprecated 1.7.3
 * @param string $string The string to escape.
 * @return string The escaped string.
 */
function yourls_escape_real( $string ) {
    yourls_deprecated_function( __FUNCTION__, '1.7.3', 'PDO' );
    global $ydb;
    if( isset( $ydb ) && ( $ydb instanceof \YOURLS\Database\YDB ) )
        return $ydb->escape( $string );

    // YOURLS DB classes have been bypassed by a custom DB engine or a custom cache layer
    return yourls_apply_filter( 'custom_escape_real', addslashes( $string ), $string );
}

// @codeCoverageIgnoreEnd
