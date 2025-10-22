<?php
/**
 * YOURLS Formatting Functions
 *
 * This file contains functions that are used for formatting, validating, and
 * sanitizing data. These functions are used throughout the application to
 * ensure that data is in the correct format and is safe to use.
 *
 * @package YOURLS
 * @since 1.0
 */

/**
 * Converts an integer to a string.
 *
 * @since 1.0
 * @param int    $num   The integer to convert.
 * @param string $chars The characters to use for the conversion.
 * @return string The converted string.
 */
function yourls_int2string($num, $chars = null) {
    if( $chars == null )
        $chars = yourls_get_shorturl_charset();
    $string = '';
    $len = strlen( $chars );
    while( $num >= $len ) {
        $mod = bcmod( (string)$num, (string)$len );
        $num = bcdiv( (string)$num, (string)$len );
        $string = $chars[ $mod ] . $string;
    }
    $string = $chars[ intval( $num ) ] . $string;

    return yourls_apply_filter( 'int2string', $string, $num, $chars );
}

/**
 * Converts a string to an integer.
 *
 * @since 1.0
 * @param string $string The string to convert.
 * @param string $chars  The characters used for the conversion.
 * @return int The converted integer.
 */
function yourls_string2int($string, $chars = null) {
    if( $chars == null )
        $chars = yourls_get_shorturl_charset();
    $integer = 0;
    $string = strrev( $string  );
    $baselen = strlen( $chars );
    $inputlen = strlen( $string );
    for ($i = 0; $i < $inputlen; $i++) {
        $index = strpos( $chars, $string[$i] );
        $integer = bcadd( (string)$integer, bcmul( (string)$index, bcpow( (string)$baselen, (string)$i ) ) );
    }

    return yourls_apply_filter( 'string2int', $integer, $string, $chars );
}

/**
 * Generates a unique HTML ID.
 *
 * @since 1.8.3
 * @param string $prefix      The prefix for the ID.
 * @param int    $initial_val The initial value for the counter.
 * @return string The unique ID.
 */
function yourls_unique_element_id($prefix = 'yid', $initial_val = 1) {
    static $id_counter = 1;
    if ($initial_val > 1) {
        $id_counter = (int) $initial_val;
    }
    return yourls_apply_filter( 'unique_element_id', $prefix . (string) $id_counter++ );
}

/**
 * Sanitizes a short URL keyword.
 *
 * @since 1.0
 * @param string $keyword                        The short URL keyword to sanitize.
 * @param bool   $restrict_to_shorturl_charset Whether to restrict the keyword to the short URL character set.
 * @return string The sanitized keyword.
 */
function yourls_sanitize_keyword( $keyword, $restrict_to_shorturl_charset = false ) {
    if( $restrict_to_shorturl_charset === true ) {
        // make a regexp pattern with the shorturl charset, and remove everything but this
        $pattern = yourls_make_regexp_pattern( yourls_get_shorturl_charset() );
        $valid = (string) substr( preg_replace( '![^'.$pattern.']!', '', $keyword ), 0, 199 );
    } else {
        $valid = yourls_sanitize_url( $keyword );
    }

    return yourls_apply_filter( 'sanitize_string', $valid, $keyword, $restrict_to_shorturl_charset );
}

/**
 * Sanitizes a page title.
 *
 * @since 1.5
 * @param string $unsafe_title The title to sanitize.
 * @param string $fallback     A fallback title to use if the sanitized title is empty.
 * @return string The sanitized title.
 */
function yourls_sanitize_title( $unsafe_title, $fallback = '' ) {
    $title = $unsafe_title;
    $title = strip_tags( $title );
    $title = preg_replace( "/\s+/", ' ', trim( $title ) );

    if ( '' === $title || false === $title ) {
        $title = $fallback;
    }

    return yourls_apply_filter( 'sanitize_title', $title, $unsafe_title, $fallback );
}

/**
 * Sanitizes a URL.
 *
 * @since 1.0
 * @param string $unsafe_url The URL to sanitize.
 * @param array  $protocols  An array of allowed protocols.
 * @return string The sanitized URL.
 */
function yourls_sanitize_url( $unsafe_url, $protocols = array() ) {
    $url = yourls_esc_url( $unsafe_url, 'redirection', $protocols );
    return yourls_apply_filter( 'sanitize_url', $url, $unsafe_url );
}

/**
 * Sanitizes a URL, removing characters that could be used in a CRLF injection attack.
 *
 * @since 1.7.2
 * @param string $unsafe_url The URL to sanitize.
 * @param array  $protocols  An array of allowed protocols.
 * @return string The sanitized URL.
 */
function yourls_sanitize_url_safe( $unsafe_url, $protocols = array() ) {
    $url = yourls_esc_url( $unsafe_url, 'safe', $protocols );
    return yourls_apply_filter( 'sanitize_url_safe', $url, $unsafe_url );
}

/**
 * Performs a deep replacement of a string.
 *
 * @since 1.0
 * @param string|array $search  The string or array of strings to search for.
 * @param string       $subject The string to search in.
 * @return string The modified string.
 */
function yourls_deep_replace($search, $subject ){
    $found = true;
    while($found) {
        $found = false;
        foreach( (array) $search as $val ) {
            while( strpos( $subject, $val ) !== false ) {
                $found = true;
                $subject = str_replace( $val, '', $subject );
            }
        }
    }

    return $subject;
}

/**
 * Sanitizes an integer.
 *
 * @since 1.0
 * @param int $int The integer to sanitize.
 * @return int The sanitized integer.
 */
function yourls_sanitize_int($int ) {
    return ( substr( preg_replace( '/[^0-9]/', '', strval( $int ) ), 0, 20 ) );
}

/**
 * Sanitizes an IP address.
 *
 * @since 1.0
 * @param string $ip The IP address to sanitize.
 * @return string The sanitized IP address.
 */
function yourls_sanitize_ip($ip ) {
    return preg_replace( '/[^0-9a-fA-F:., ]/', '', $ip );
}

/**
 * Sanitizes a date.
 *
 * @since 1.0
 * @param string $date The date to sanitize.
 * @return string|false The sanitized date, or false on failure.
 */
function yourls_sanitize_date($date ) {
    if( !preg_match( '!^\d{1,2}/\d{1,2}/\d{4}$!' , $date ) ) {
        return false;
    }
    return $date;
}

/**
 * Sanitizes a date for use in a SQL query.
 *
 * @since 1.0
 * @param string $date The date to sanitize.
 * @return string|false The sanitized date, or false on failure.
 */
function yourls_sanitize_date_for_sql($date) {
    if( !yourls_sanitize_date( $date ) )
        return false;
    return date( 'Y-m-d', strtotime( $date ) );
}

/**
 * Trims a string to a certain length.
 *
 * @since 1.0
 * @param string $string The string to trim.
 * @param int    $length The maximum length of the string.
 * @param string $append The string to append if the string is trimmed.
 * @return string The trimmed string.
 */
function yourls_trim_long_string($string, $length = 60, $append = '[...]') {
    $newstring = $string;
    if ( mb_strlen( $newstring ) > $length ) {
        $newstring = mb_substr( $newstring, 0, $length - mb_strlen( $append ), 'UTF-8' ) . $append;
    }
    return yourls_apply_filter( 'trim_long_string', $newstring, $string, $length, $append );
}

/**
 * Sanitizes a version number.
 *
 * @since 1.4.1
 * @param string $version The version number to sanitize.
 * @return string The sanitized version number.
 */
function yourls_sanitize_version( $version ) {
    preg_match( '/([0-9]+\.[0-9.]+).*$/', $version, $matches );
    $version = isset($matches[1]) ? trim($matches[1], '.') : '';

    return $version;
}

/**
 * Sanitizes a filename.
 *
 * @since 1.0
 * @param string $file The filename to sanitize.
 * @return string The sanitized filename.
 */
function yourls_sanitize_filename($file) {
    $file = str_replace( '\\', '/', $file ); // sanitize for Win32 installs
    $file = preg_replace( '|/+|' ,'/', $file ); // remove any duplicate slash
    return $file;
}

/**
 * Checks if a string is UTF-8.
 *
 * @since 1.0
 * @param string $str The string to check.
 * @return bool True if the string is UTF-8, false otherwise.
 */
function yourls_seems_utf8($str) {
    $length = strlen( $str );
    for ( $i=0; $i < $length; $i++ ) {
        $c = ord( $str[ $i ] );
        if ( $c < 0x80 ) $n = 0; # 0bbbbbbb
        elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb
        elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb
        elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb
        elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb
        elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b
        else return false; # Does not match any model
        for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
            if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
                return false;
        }
    }
    return true;
}


/**
 * Checks if PCRE was compiled with UTF-8 support.
 *
 * @since 1.7.1
 * @return bool True if PCRE was compiled with UTF-8 support, false otherwise.
 */
function yourls_supports_pcre_u() {
    static $utf8_pcre;
    if( !isset( $utf8_pcre ) ) {
        $utf8_pcre = (bool) @preg_match( '/^./u', 'a' );
    }
    return $utf8_pcre;
}

/**
 * Checks for invalid UTF-8 in a string.
 *
 * @since 1.6
 * @param string $string The string to check.
 * @param bool   $strip  Whether to strip invalid UTF-8.
 * @return string The sanitized string.
 */
function yourls_check_invalid_utf8( $string, $strip = false ) {
    $string = (string) $string;

    if ( 0 === strlen( $string ) ) {
        return '';
    }

    // We can't demand utf8 in the PCRE installation, so just return the string in those cases
    if ( ! yourls_supports_pcre_u() ) {
        return $string;
    }

    // preg_match fails when it encounters invalid UTF8 in $string
    if ( 1 === @preg_match( '/^./us', $string ) ) {
        return $string;
    }

    // Attempt to strip the bad chars if requested (not recommended)
    if ( $strip && function_exists( 'iconv' ) ) {
        return iconv( 'utf-8', 'utf-8', $string );
    }

    return '';
}

/**
 * Converts a number of special characters to their HTML entities.
 *
 * @since 1.6
 * @param string  $string        The string to convert.
 * @param int     $quote_style   The quote style.
 * @param bool    $double_encode Whether to double encode.
 * @return string The converted string.
 */
function yourls_specialchars( $string, $quote_style = ENT_NOQUOTES, $double_encode = false ) {
    $string = (string) $string;

    if ( 0 === strlen( $string ) )
        return '';

    // Don't bother if there are no specialchars - saves some processing
    if ( ! preg_match( '/[&<>"\']/', $string ) )
        return $string;

    // Account for the previous behaviour of the function when the $quote_style is not an accepted value
    if ( empty( $quote_style ) )
        $quote_style = ENT_NOQUOTES;
    elseif ( ! in_array( $quote_style, array( 0, 2, 3, 'single', 'double' ), true ) )
        $quote_style = ENT_QUOTES;

    $charset = 'UTF-8';

    $_quote_style = $quote_style;

    if ( $quote_style === 'double' ) {
        $quote_style = ENT_COMPAT;
        $_quote_style = ENT_COMPAT;
    } elseif ( $quote_style === 'single' ) {
        $quote_style = ENT_NOQUOTES;
    }

    // Handle double encoding ourselves
    if ( $double_encode ) {
        $string = @htmlspecialchars( $string, $quote_style, $charset );
    } else {
        // Decode &amp; into &
        $string = yourls_specialchars_decode( $string, $_quote_style );

        // Guarantee every &entity; is valid or re-encode the &
        $string = yourls_kses_normalize_entities( $string );

        // Now re-encode everything except &entity;
        $string = preg_split( '/(&#?x?[0-9a-z]+;)/i', $string, -1, PREG_SPLIT_DELIM_CAPTURE );

        for ( $i = 0; $i < count( $string ); $i += 2 )
            $string[$i] = @htmlspecialchars( $string[$i], $quote_style, $charset );

        $string = implode( '', $string );
    }

    // Backwards compatibility
    if ( 'single' === $_quote_style )
        $string = str_replace( "'", '&#039;', $string );

    return $string;
}

/**
 * Converts a number of HTML entities to their special characters.
 *
 * @since 1.6
 * @param string $string      The string to convert.
 * @param int    $quote_style The quote style.
 * @return string The converted string.
 */
function yourls_specialchars_decode( $string, $quote_style = ENT_NOQUOTES ) {
    $string = (string) $string;

    if ( 0 === strlen( $string ) ) {
        return '';
    }

    // Don't bother if there are no entities - saves a lot of processing
    if ( strpos( $string, '&' ) === false ) {
        return $string;
    }

    // Match the previous behaviour of _wp_specialchars() when the $quote_style is not an accepted value
    if ( empty( $quote_style ) ) {
        $quote_style = ENT_NOQUOTES;
    } elseif ( !in_array( $quote_style, array( 0, 2, 3, 'single', 'double' ), true ) ) {
        $quote_style = ENT_QUOTES;
    }

    // More complete than get_html_translation_table( HTML_SPECIALCHARS )
    $single = array( '&#039;'  => '\'', '&#x27;' => '\'' );
    $single_preg = array( '/&#0*39;/'  => '&#039;', '/&#x0*27;/i' => '&#x27;' );
    $double = array( '&quot;' => '"', '&#034;'  => '"', '&#x22;' => '"' );
    $double_preg = array( '/&#0*34;/'  => '&#034;', '/&#x0*22;/i' => '&#x22;' );
    $others = array( '&lt;'   => '<', '&#060;'  => '<', '&gt;'   => '>', '&#062;'  => '>', '&amp;'  => '&', '&#038;'  => '&', '&#x26;' => '&' );
    $others_preg = array( '/&#0*60;/'  => '&#060;', '/&#0*62;/'  => '&#062;', '/&#0*38;/'  => '&#038;', '/&#x0*26;/i' => '&#x26;' );

    $translation = $translation_preg = [];

    if ( $quote_style === ENT_QUOTES ) {
        $translation = array_merge( $single, $double, $others );
        $translation_preg = array_merge( $single_preg, $double_preg, $others_preg );
    } elseif ( $quote_style === ENT_COMPAT || $quote_style === 'double' ) {
        $translation = array_merge( $double, $others );
        $translation_preg = array_merge( $double_preg, $others_preg );
    } elseif ( $quote_style === 'single' ) {
        $translation = array_merge( $single, $others );
        $translation_preg = array_merge( $single_preg, $others_preg );
    } elseif ( $quote_style === ENT_NOQUOTES ) {
        $translation = $others;
        $translation_preg = $others_preg;
    }

    // Remove zero padding on numeric entities
    $string = preg_replace( array_keys( $translation_preg ), array_values( $translation_preg ), $string );

    // Replace characters according to translation table
    return strtr( $string, $translation );
}


/**
 * Escapes a string for use in HTML.
 *
 * @since 1.6
 * @param string $text The string to escape.
 * @return string The escaped string.
 */
function yourls_esc_html( $text ) {
    $safe_text = yourls_check_invalid_utf8( $text );
    $safe_text = yourls_specialchars( $safe_text, ENT_QUOTES );
    return yourls_apply_filter( 'esc_html', $safe_text, $text );
}

/**
 * Escapes a string for use in an HTML attribute.
 *
 * @since 1.6
 * @param string $text The string to escape.
 * @return string The escaped string.
 */
function yourls_esc_attr( $text ) {
    $safe_text = yourls_check_invalid_utf8( $text );
    $safe_text = yourls_specialchars( $safe_text, ENT_QUOTES );
    return yourls_apply_filter( 'esc_attr', $safe_text, $text );
}

/**
 * Escapes a URL for use in HTML.
 *
 * @since 1.6
 * @param string $url       The URL to escape.
 * @param string $context   The context in which the URL is being used.
 * @param array  $protocols An array of allowed protocols.
 * @return string The escaped URL.
 */
function yourls_esc_url( $url, $context = 'display', $protocols = array() ) {
    // trim first -- see #1931
    $url = trim( $url );

    // make sure there's only one 'http://' at the beginning (prevents pasting a URL right after the default 'http://')
    $url = str_replace(
        array( 'http://http://', 'http://https://' ),
        array( 'http://',        'https://'        ),
        $url
    );

    if ( '' == $url )
        return $url;

    $original_url = $url;

    // force scheme and domain to lowercase - see issues 591 and 1630
    $url = yourls_normalize_uri( $url );

    $url = preg_replace( '|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\[\]\\x80-\\xff]|i', '', $url );
    // Previous regexp in YOURLS was '|[^a-z0-9-~+_.?\[\]\^#=!&;,/:%@$\|*`\'<>"()\\x80-\\xff\{\}]|i'
    // TODO: check if that was it too destructive

    // If $context is 'safe', an extra step is taken to make sure no CRLF injection is possible.
    // To be used when $url can be forged by evil user (eg it's from a $_SERVER variable, a query string, etc..)
    if ( 'safe' == $context ) {
        $strip = array( '%0d', '%0a', '%0D', '%0A' );
        $url = yourls_deep_replace( $strip, $url );
    }

    // Replace ampersands and single quotes only when displaying.
    if ( 'display' == $context ) {
        $url = yourls_kses_normalize_entities( $url );
        $url = str_replace( '&amp;', '&#038;', $url );
        $url = str_replace( "'", '&#039;', $url );
    }

    // If there's a protocol, make sure it's OK
    if( yourls_get_protocol($url) !== '' ) {
        if ( ! is_array( $protocols ) or ! $protocols ) {
            global $yourls_allowedprotocols;
            $protocols = yourls_apply_filter( 'esc_url_protocols', $yourls_allowedprotocols );
            // Note: $yourls_allowedprotocols is also globally filterable in functions-kses.php/yourls_kses_init()
        }

        if ( !yourls_is_allowed_protocol( $url, $protocols ) )
            return '';

        // I didn't use KSES function kses_bad_protocol() because it doesn't work the way I liked (returns //blah from illegal://blah)
    }

    return yourls_apply_filter( 'esc_url', $url, $original_url, $context );
}


/**
 * Normalizes a URI.
 *
 * @since 1.7.1
 * @param string $url The URI to normalize.
 * @return string The normalized URI.
 */
function yourls_normalize_uri( $url ) {
    $scheme = yourls_get_protocol( $url );

    if ('' == $scheme) {
        // Scheme not found, malformed URL? Something else? Not sure.
        return $url;
    }

    /**
     * Case 1 : scheme like "stuff:", as opposed to "stuff://"
     * Examples: "mailto:joe@joe.com" or "bitcoin:15p1o8vnWqNkJBJGgwafNgR1GCCd6EGtQR?amount=1&label=Ozh"
     * In this case, we only lowercase the scheme, because depending on it, things after should or should not be lowercased
     */
    if (substr($scheme, -2, 2) != '//') {
        $url = str_replace( $scheme, strtolower( $scheme ), $url );
        return $url;
    }

    /**
     * Case 2 : scheme like "stuff://" (eg "http://example.com/" or "ssh://joe@joe.com")
     * Here we lowercase the scheme and domain parts
     */
    $parts = parse_url($url);

    // Most likely malformed stuff, could not parse : we'll just lowercase the scheme and leave the rest untouched
    if (false == $parts) {
        $url = str_replace( $scheme, strtolower( $scheme ), $url );
        return $url;
    }

    // URL seems parsable, let's do the best we can
    $lower = array();
    $lower['scheme'] = strtolower( $parts['scheme'] );
    if( isset( $parts['host'] ) ) {
        // Convert domain to lowercase, with mb_ to preserve UTF8
        $lower['host'] = mb_strtolower($parts['host']);
        /**
         * Convert IDN domains to their UTF8 form so that طارق.net and xn--mgbuq0c.net
         * are considered the same. Explicitly mention option and variant to avoid notice
         * on PHP 7.2 and 7.3
         */
         $lower['host'] = idn_to_utf8($lower['host'], IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
    }

    $url = http_build_url($url, $lower);

    return $url;
}


/**
 * Escapes a string for use in JavaScript.
 *
 * @since 1.6
 * @param string $text The string to escape.
 * @return string The escaped string.
 */
function yourls_esc_js( $text ) {
    $safe_text = yourls_check_invalid_utf8( $text );
    $safe_text = yourls_specialchars( $safe_text, ENT_COMPAT );
    $safe_text = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", stripslashes( $safe_text ) );
    $safe_text = str_replace( "\r", '', $safe_text );
    $safe_text = str_replace( "\n", '\\n', addslashes( $safe_text ) );
    return yourls_apply_filter( 'esc_js', $safe_text, $text );
}

/**
 * Escapes a string for use in a textarea.
 *
 * @since 1.6
 * @param string $text The string to escape.
 * @return string The escaped string.
 */
function yourls_esc_textarea( $text ) {
    $safe_text = htmlspecialchars( $text, ENT_QUOTES );
    return yourls_apply_filter( 'esc_textarea', $safe_text, $text );
}

/**
 * Adds backslashes to a string.
 *
 * @since 1.6
 * @param string $string The string to add backslashes to.
 * @return string The string with backslashes.
 */
function yourls_backslashit($string) {
    $string = preg_replace('/^([0-9])/', '\\\\\\\\\1', (string)$string);
    $string = preg_replace('/([a-z])/i', '\\\\\1', (string)$string);
    return $string;
}

/**
 * Checks if a string is raw URL encoded.
 *
 * @since 1.7
 * @param string $string The string to check.
 * @return bool True if the string is raw URL encoded, false otherwise.
 */
function yourls_is_rawurlencoded( $string ) {
    return rawurldecode( $string ) != $string;
}

/**
 * Raw URL decodes a string until it is no longer encoded.
 *
 * @since 1.7
 * @param string $string The string to decode.
 * @return string The decoded string.
 */
function yourls_rawurldecode_while_encoded( $string ) {
    $string = rawurldecode( $string );
    if( yourls_is_rawurlencoded( $string ) ) {
        $string = yourls_rawurldecode_while_encoded( $string );
    }
    return $string;
}

/**
 * Creates a bookmarklet from JavaScript code.
 *
 * @since 1.7.1
 * @param string $code The JavaScript code.
 * @return string The bookmarklet.
 */
function yourls_make_bookmarklet( $code ) {
    $book = new \Ozh\Bookmarkletgen\Bookmarkletgen;
    return $book->crunch( $code );
}

/**
 * Gets a timestamp, adjusted for the time offset.
 *
 * @since 1.7.10
 * @param int $timestamp The timestamp to adjust.
 * @return int The adjusted timestamp.
 */
function yourls_get_timestamp( $timestamp ) {
    $offset = yourls_get_time_offset();
    $timestamp_offset = (int)$timestamp + ($offset * 3600);

    return yourls_apply_filter( 'get_timestamp', $timestamp_offset, $timestamp, $offset );
}

/**
 * Gets the time offset.
 *
 * @since 1.7.10
 * @return int The time offset.
 */
function yourls_get_time_offset() {
    $offset = defined('YOURLS_HOURS_OFFSET') ? (int)YOURLS_HOURS_OFFSET : 0;
    return yourls_apply_filter( 'get_time_offset', $offset );
}

/**
 * Gets the date and time format.
 *
 * @since 1.7.10
 * @param string $format The date and time format.
 * @return string The date and time format.
 */
function yourls_get_datetime_format( $format ) {
    return yourls_apply_filter( 'get_datetime_format', (string)$format );
}

/**
 * Gets the date format.
 *
 * @since 1.7.10
 * @param string $format The date format.
 * @return string The date format.
 */
function yourls_get_date_format( $format ) {
    return yourls_apply_filter( 'get_date_format', (string)$format );
}

/**
 * Gets the time format.
 *
 * @since 1.7.10
 * @param string $format The time format.
 * @return string The time format.
 */
function yourls_get_time_format( $format ) {
    return yourls_apply_filter( 'get_time_format', (string)$format );
}
