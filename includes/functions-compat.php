<?php
/**
 * YOURLS Compatibility Functions
 *
 * This file contains compatibility functions for older PHP versions that may
 * be missing certain features or functions. It ensures that YOURLS can run
 * on a wider range of server environments.
 *
 * @package YOURLS
 * @since 1.0
 */

// @codeCoverageIgnoreStart

/**
 * json_encode for PHP, should someone run a distro without php-json -- see http://askubuntu.com/questions/361424/
 *
 */
if( !function_exists( 'json_encode' ) ) {
    function json_encode( $array ) {
        return yourls_array_to_json( $array );
    }
}

/**
 * Converts an array to a JSON string.
 *
 * @since 1.0
 * @param array $array The array to convert.
 * @return string|false The JSON string, or false on failure.
 */
function yourls_array_to_json( $array ){

    if( !is_array( $array ) ){
        return false;
    }

    $associative = count( array_diff( array_keys($array), array_keys( array_keys( $array )) ));
    if( $associative ){

        $construct = array();
        foreach( $array as $key => $value ){

            // We first copy each key/value pair into a staging array,
            // formatting each key and value properly as we go.

            // Format the key:
            if( is_numeric( $key ) ){
                $key = "key_$key";
            }
            $key = '"'.addslashes( $key ).'"';

            // Format the value:
            if( is_array( $value )){
                $value = yourls_array_to_json( $value );
            } else if( !is_numeric( $value ) || is_string( $value ) ){
                $value = '"'.addslashes( $value ).'"';
            }

            // Add to staging array:
            $construct[] = "$key: $value";
        }

        // Then we collapse the staging array into the JSON form:
        $result = "{ " . implode( ", ", $construct ) . " }";

    } else { // If the array is a vector (not associative):

        $construct = array();
        foreach( $array as $value ){

            // Format the value:
            if( is_array( $value )){
                $value = yourls_array_to_json( $value );
            } else if( !is_numeric( $value ) || is_string( $value ) ){
                $value = '"'.addslashes($value).'"';
            }

            // Add to staging array:
            $construct[] = $value;
        }

        // Then we collapse the staging array into the JSON form:
        $result = "[ " . implode( ", ", $construct ) . " ]";
    }

    return $result;
}


/**
 * BC Math functions (assuming if one doesn't exist, none does)
 *
 */
if ( !function_exists( 'bcdiv' ) ) {
    function bcdiv( $dividend, $divisor ) {
        $quotient = floor( $dividend/$divisor );
        return $quotient;
    }
    function bcmod( $dividend, $modulo ) {
        $remainder = $dividend%$modulo;
        return $remainder;
    }
    function bcmul( $left, $right ) {
        return $left * $right;
    }
    function bcadd( $left, $right ) {
        return $left + $right;
    }
    function bcpow( $base, $power ) {
        return pow( $base, $power );
    }
}

// @codeCoverageIgnoreEnd
