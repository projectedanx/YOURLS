<?php
/**
 * YOURLS Debug Functions
 *
 * This file contains functions that are used for debugging purposes. These
 * functions are only active when the `YOURLS_DEBUG` constant is set to `true`.
 *
 * @package YOURLS
 * @since 1.7
 */

/**
 * Adds a message to the debug log.
 *
 * @since 1.7
 * @param string $msg The message to add to the debug log.
 * @return string The message itself.
 */
function yourls_debug_log( $msg ) {
    yourls_do_action( 'debug_log', $msg );
    // Get the DB object ($ydb), get its profiler (\Aura\Sql\Profiler\Profiler), its logger (\Aura\Sql\Profiler\MemoryLogger) and
    // pass it a unused argument (loglevel) and the message
    // Check if function exists to allow usage of the function in very early stages
    if(function_exists('yourls_debug_log')) {
        yourls_get_db()->getProfiler()->getLogger()->log( 'debug', $msg);
    }
    return $msg;
}

/**
 * Gets the debug log.
 *
 * @since 1.7.3
 * @return array The debug log.
 */
function yourls_get_debug_log() {
    return yourls_get_db()->getProfiler()->getLogger()->getMessages();
}

/**
 * Gets the number of SQL queries performed.
 *
 * @since 1.0
 * @return int The number of SQL queries performed.
 */
function yourls_get_num_queries() {
    return yourls_apply_filter( 'get_num_queries', yourls_get_db()->get_num_queries() );
}

/**
 * Sets the debug mode.
 *
 * @since 1.7.3
 * @param bool $bool True to enable debug mode, false to disable.
 * @return void
 */
function yourls_debug_mode( $bool ) {
    // log queries if true
    yourls_get_db()->getProfiler()->setActive( (bool)$bool );

    // report notices if true
    $level = $bool ? -1 : ( E_ERROR | E_PARSE );
    error_reporting( $level );
}

/**
 * Gets the debug mode.
 *
 * @since 1.7.7
 * @return bool True if debug mode is enabled, false otherwise.
 */
function yourls_get_debug_mode() {
    return defined( 'YOURLS_DEBUG' ) && YOURLS_DEBUG;
}
