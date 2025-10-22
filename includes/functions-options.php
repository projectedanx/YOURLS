<?php
/**
 * YOURLS Options Functions
 *
 * This file contains functions that are used for managing options. These
 * functions are used to get, set, and delete options from the database.
 *
 * @package YOURLS
 * @since 1.4
 */

/**
 * Retrieves an option value from the database.
 *
 * If the option does not exist, the function returns the default value.
 *
 * @since 1.4
 * @param string $option_name The name of the option to retrieve.
 * @param mixed  $default     Optional. The default value to return if the option does not exist.
 *                            Default false.
 * @return mixed The value of the option, or the default value if the option does not exist.
 */
function yourls_get_option( $option_name, $default = false ) {
    // Allow plugins to short-circuit options
    $pre = yourls_apply_filter( 'shunt_option_'.$option_name, false );
    if ( false !== $pre ) {
        return $pre;
    }

    $option = new \YOURLS\Database\Options(yourls_get_db());
    $value  = $option->get($option_name, $default);

    return yourls_apply_filter( 'get_option_'.$option_name, $value );
}

/**
 * Retrieves all options from the database.
 *
 * This function populates the options cache, which prevents the need for
 * subsequent SQL queries when retrieving individual options.
 *
 * @since 1.4
 * @return void
 */
function yourls_get_all_options() {
    // Allow plugins to short-circuit all options. (Note: regular plugins are loaded after all options)
    $pre = yourls_apply_filter( 'shunt_all_options', false );
    if ( false !== $pre ) {
        return $pre;
    }

    $options = new \YOURLS\Database\Options(yourls_get_db());

    if ($options->get_all_options() === false) {
        // Zero option found but no unexpected error so far: YOURLS isn't installed
        yourls_set_installed(false);
        return;
    }

    yourls_set_installed(true);
}

/**
 * Updates an option in the database.
 *
 * If the option does not exist, it will be added.
 *
 * @since 1.4
 * @param string $option_name The name of the option to update.
 * @param mixed  $newvalue    The new value for the option.
 * @return bool True if the value was updated, false otherwise.
 */
function yourls_update_option( $option_name, $newvalue ) {
    $option = new \YOURLS\Database\Options(yourls_get_db());
    $update = $option->update($option_name, $newvalue);

    return $update;
}

/**
 * Adds an option to the database.
 *
 * @since 1.4
 * @param string $name  The name of the option to add.
 * @param mixed  $value Optional. The value for the option. Default ''.
 * @return bool True if the option was added, false otherwise.
 */
function yourls_add_option( $name, $value = '' ) {
    $option = new \YOURLS\Database\Options(yourls_get_db());
    $add    = $option->add($name, $value);

    return $add;
}

/**
 * Deletes an option from the database.
 *
 * @since 1.4
 * @param string $name The name of the option to delete.
 * @return bool True if the option was deleted, false on failure.
 */
function yourls_delete_option( $name ) {
    $option = new \YOURLS\Database\Options(yourls_get_db());
    $delete = $option->delete($name);

    return $delete;
}

/**
 * Serializes data if needed.
 *
 * @since 1.4
 * @param mixed $data The data to serialize.
 * @return mixed The serialized data.
 */
function yourls_maybe_serialize( $data ) {
    if ( is_array( $data ) || is_object( $data ) )
        return serialize( $data );

    if ( yourls_is_serialized( $data, false ) )
        return serialize( $data );

    return $data;
}

/**
 * Unserializes a value if it was serialized.
 *
 * @since 1.4
 * @param string $original The value to unserialize.
 * @return mixed The unserialized value.
 */
function yourls_maybe_unserialize( $original ) {
    if ( yourls_is_serialized( $original ) ) // don't attempt to unserialize data that wasn't serialized going in
        return @unserialize( $original );
    return $original;
}

/**
 * Checks if a value is serialized.
 *
 * @since 1.4
 * @param mixed $data   The value to check.
 * @param bool  $strict Optional. Whether to be strict about the end of the string. Default true.
 * @return bool True if the value is serialized, false otherwise.
 */
function yourls_is_serialized( $data, $strict = true ) {
    // if it isn't a string, it isn't serialized
    if ( ! is_string( $data ) )
        return false;
    $data = trim( $data );
     if ( 'N;' == $data )
        return true;
    $length = strlen( $data );
    if ( $length < 4 )
        return false;
    if ( ':' !== $data[1] )
        return false;
    if ( $strict ) {
        $lastc = $data[ $length - 1 ];
        if ( ';' !== $lastc && '}' !== $lastc )
            return false;
    } else {
        $semicolon = strpos( $data, ';' );
        $brace     = strpos( $data, '}' );
        // Either ; or } must exist.
        if ( false === $semicolon && false === $brace )
            return false;
        // But neither must be in the first X characters.
        if ( false !== $semicolon && $semicolon < 3 )
            return false;
        if ( false !== $brace && $brace < 4 )
            return false;
    }
    $token = $data[0];
    switch ( $token ) {
        case 's' :
            if ( $strict ) {
                if ( '"' !== $data[ $length - 2 ] )
                    return false;
            } elseif ( false === strpos( $data, '"' ) ) {
                return false;
            }
            // or else fall through
        case 'a' :
        case 'O' :
            return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
        case 'b' :
        case 'i' :
        case 'd' :
            $end = $strict ? '$' : '';
            return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
    }
    return false;
}
