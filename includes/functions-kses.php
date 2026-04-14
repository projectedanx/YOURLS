<?php
/**
 * YOURLS KSES Functions
 *
 * This file is a modified subset of WordPress's KSES implementation. It is
 * used for filtering HTML and XHTML to prevent cross-site scripting (XSS)
 * attacks.
 *
 * @package YOURLS
 * @since 1.6
 */

/**
 * kses 0.2.2 - HTML/XHTML filter that only allows some elements and attributes
 * Copyright (C) 2002, 2003, 2005  Ulf Harnhammar
 *
 * This program is free software and open source software; you can redistribute
 * it and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin St, Fifth Floor, Boston, MA 02110-1301, USA
 * http://www.gnu.org/licenses/gpl.html
 *
 * [kses strips evil scripts!]
 *
 * @version 0.2.2
 * @copyright (C) 2002, 2003, 2005
 * @author Ulf Harnhammar <http://advogato.org/person/metaur/>
 *
 * @package External
 * @subpackage KSES
 *
 */

/* NOTE ABOUT GLOBALS
 * Two globals are defined: $yourls_allowedentitynames and $yourls_allowedprotocols
 * - $yourls_allowedentitynames is used internally in KSES functions to sanitize HTML entities
 * - $yourls_allowedprotocols is used in various parts of YOURLS, not just in KSES, albeit being defined here
 * Two globals are not defined and unused at this moment: $yourls_allowedtags_all and $yourls_allowedtags
 * The code for these vars is here and ready for any future use
 */

// Populate after plugins have loaded to allow user defined values
yourls_add_action( 'plugins_loaded', 'yourls_kses_init' );

/**
 * Initializes the KSES globals.
 *
 * @since 1.6
 * @return void
 */
function yourls_kses_init() {
    global $yourls_allowedentitynames, $yourls_allowedprotocols;

    if( ! $yourls_allowedentitynames ) {
        $yourls_allowedentitynames = yourls_apply_filter( 'kses_allowed_entities', yourls_kses_allowed_entities() );
    }

    if( ! $yourls_allowedprotocols ) {
        $yourls_allowedprotocols   = yourls_apply_filter( 'kses_allowed_protocols', yourls_kses_allowed_protocols() );
    }

    /** See NOTE ABOUT GLOBALS **

    if( ! $yourls_allowedtags_all ) {
        $yourls_allowedtags_all = yourls_kses_allowed_tags_all();
        $yourls_allowedtags_all = array_map( '_yourls_add_global_attributes', $yourls_allowedtags_all );
        $yourls_allowedtags_all = yourls_apply_filter( 'kses_allowed_tags_all', $yourls_allowedtags_all );
    } else {
        // User defined: let's sanitize
        $yourls_allowedtags_all = yourls_kses_array_lc( $yourls_allowedtags_all );
    }

    if( ! $yourls_allowedtags ) {
        $yourls_allowedtags = yourls_kses_allowed_tags();
        $yourls_allowedtags = array_map( '_yourls_add_global_attributes', $yourls_allowedtags );
        $yourls_allowedtags = yourls_apply_filter( 'kses_allowed_tags', $yourls_allowedtags );
    } else {
        // User defined: let's sanitize
        $yourls_allowedtags = yourls_kses_array_lc( $yourls_allowedtags );
    }

    /**/
}

/**
 * Returns a list of all allowed HTML tags.
 *
 * @since 1.6
 * @return array A list of all allowed HTML tags.
 */
function yourls_kses_allowed_tags_all() {
    return require __DIR__ . '/Config/KsesAllowedTagsAll.php';
}

/**
 * Returns a list of default allowed HTML tags.
 *
 * @since 1.6
 * @return array A list of default allowed HTML tags.
 */
function yourls_kses_allowed_tags() {
    return require __DIR__ . '/Config/KsesAllowedTags.php';
}

/**
 * Returns a list of allowed HTML entities.
 *
 * @since 1.6
 * @return array A list of allowed HTML entities.
 */
function yourls_kses_allowed_entities() {
    return require __DIR__ . '/Config/KsesAllowedEntities.php';
}

/**
 * Returns a list of allowed protocols.
 *
 * @since 1.6
 * @return array A list of allowed protocols.
 */
function yourls_kses_allowed_protocols() {
    // More or less common stuff in links. From http://en.wikipedia.org/wiki/URI_scheme
    return require __DIR__ . '/Config/KsesAllowedProtocols.php';
}


/**
 * Normalizes HTML entities.
 *
 * This function converts HTML entities to their correct format.
 *
 * @since 1.6
 * @param string $string The string to normalize.
 * @return string The normalized string.
 */
function yourls_kses_normalize_entities($string) {
    # Disarm all entities by converting & to &amp;

    $string = str_replace('&', '&amp;', $string);

    # Change back the allowed entities in our entity whitelist

    $string = preg_replace_callback('/&amp;([A-Za-z]{2,8});/', 'yourls_kses_named_entities', $string);
    $string = preg_replace_callback('/&amp;#(0*[0-9]{1,7});/', 'yourls_kses_normalize_entities2', $string);
    $string = preg_replace_callback('/&amp;#[Xx](0*[0-9A-Fa-f]{1,6});/', 'yourls_kses_normalize_entities3', $string);

    return $string;
}

/**
 * Callback function for `yourls_kses_normalize_entities()` to handle named entities.
 *
 * @since 1.6
 * @param array $matches An array of matches from `preg_replace_callback()`.
 * @return string The correctly encoded entity.
 */
function yourls_kses_named_entities($matches) {
    global $yourls_allowedentitynames;

    if ( empty($matches[1]) )
        return '';

    $i = $matches[1];
    return ( ( ! in_array($i, $yourls_allowedentitynames) ) ? "&amp;$i;" : "&$i;" );
}

/**
 * Callback function for `yourls_kses_normalize_entities()` to handle numeric entities.
 *
 * @since 1.6
 * @param array $matches An array of matches from `preg_replace_callback()`.
 * @return string The correctly encoded entity.
 */
function yourls_kses_normalize_entities2($matches) {
    if ( empty($matches[1]) )
        return '';

    $i = $matches[1];
    if (yourls_valid_unicode($i)) {
        $i = str_pad(ltrim($i,'0'), 3, '0', STR_PAD_LEFT);
        $i = "&#$i;";
    } else {
        $i = "&amp;#$i;";
    }

    return $i;
}

/**
 * Callback function for `yourls_kses_normalize_entities()` to handle hex entities.
 *
 * @since 1.6
 * @param array $matches An array of matches from `preg_replace_callback()`.
 * @return string The correctly encoded entity.
 */
function yourls_kses_normalize_entities3($matches) {
    if ( empty($matches[1]) )
        return '';

    $hexchars = $matches[1];
    return ( ( ! yourls_valid_unicode(hexdec($hexchars)) ) ? "&amp;#x$hexchars;" : '&#x'.ltrim($hexchars,'0').';' );
}

/**
 * Adds global attributes to a tag in the allowed HTML list.
 *
 * @since 1.6
 * @param array|true $value An array of attributes, or true if the tag has no attributes.
 * @return array The array of attributes with global attributes added.
 */
function _yourls_add_global_attributes( $value ) {
    $global_attributes = array(
        'class' => true,
        'id' => true,
        'style' => true,
        'title' => true,
    );

    if ( true === $value )
        $value = array();

    if ( is_array( $value ) )
        return array_merge( $value, $global_attributes );

    return $value;
}

/**
 * Checks if a Unicode value is valid.
 *
 * @since 1.6
 * @param int $i The Unicode value.
 * @return bool True if the value is a valid Unicode number, false otherwise.
 */
function yourls_valid_unicode($i) {
    return ( $i == 0x9 || $i == 0xa || $i == 0xd ||
            ($i >= 0x20 && $i <= 0xd7ff) ||
            ($i >= 0xe000 && $i <= 0xfffd) ||
            ($i >= 0x10000 && $i <= 0x10ffff) );
}

/**
 * Converts the keys of an array to lowercase.
 *
 * @since 1.6
 * @param array $inarray The input array.
 * @return array The array with all keys in lowercase.
 */
function yourls_kses_array_lc($inarray) {
    $outarray = array ();

    foreach ( (array) $inarray as $inkey => $inval) {
        $outkey = strtolower($inkey);
        $outarray[$outkey] = array ();

        foreach ( (array) $inval as $inkey2 => $inval2) {
            $outkey2 = strtolower($inkey2);
            $outarray[$outkey][$outkey2] = $inval2;
        } # foreach $inval
    } # foreach $inarray

    return $outarray;
}

/**
 * Decodes numeric HTML entities.
 *
 * @since 1.6
 * @param string $string The string to decode.
 * @return string The decoded string.
 */
function yourls_kses_decode_entities($string) {
    $string = preg_replace_callback('/&#([0-9]+);/', '_yourls_kses_decode_entities_chr', $string);
    $string = preg_replace_callback('/&#[Xx]([0-9A-Fa-f]+);/', '_yourls_kses_decode_entities_chr_hexdec', $string);

    return $string;
}

/**
 * Callback function for `yourls_kses_decode_entities()` to handle numeric entities.
 *
 * @since 1.6
 * @param array $match An array of matches from `preg_replace_callback()`.
 * @return string The decoded character.
 */
function _yourls_kses_decode_entities_chr( $match ) {
    return chr( $match[1] );
}

/**
 * Callback function for `yourls_kses_decode_entities()` to handle hex entities.
 *
 * @since 1.6
 * @param array $match An array of matches from `preg_replace_callback()`.
 * @return string The decoded character.
 */
function _yourls_kses_decode_entities_chr_hexdec( $match ) {
    return chr( hexdec( $match[1] ) );
}

/**
 * Removes any null characters from a string.
 *
 * @since 1.6
 * @param string $string The string to clean.
 * @return string The cleaned string.
 */
function yourls_kses_no_null($string) {
    $string = preg_replace( '/\0+/', '', $string );
    $string = preg_replace( '/(\\\\0)+/', '', $string );

    return $string;
}
