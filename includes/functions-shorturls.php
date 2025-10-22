<?php
/**
 * YOURLS Short URL Functions
 *
 * This file contains functions that are used for managing short URLs. These
 * functions are used to add, edit, and delete short URLs, as well as to
 * retrieve information about them.
 *
 * @package YOURLS
 * @since 1.0
 */


/**
 * Adds a new link to the database.
 *
 * @since 1.0
 * @param string $url     The URL to shorten.
 * @param string $keyword Optional. The custom keyword for the short URL. Default ''.
 * @param string $title   Optional. The title of the URL. Default ''.
 * @param int    $row_id  Optional. The row ID for the new link. Default 1.
 * @return array An array containing the result of the operation.
 */
function yourls_add_new_link( $url, $keyword = '', $title = '', $row_id = 1 ) {
    // Allow plugins to short-circuit the whole function
    $pre = yourls_apply_filter( 'shunt_add_new_link', false, $url, $keyword, $title );
    if ( false !== $pre ) {
        return $pre;
    }

    /**
     * The result array.
     */
    $return = [
        // Always present :
        'status' => '',
        'code'   => '',
        'message' => '',
        'errorCode' => '',
        'statusCode' => '',
    ];

    // Sanitize URL
    $url = yourls_sanitize_url( $url );
    if ( !$url || $url == 'http://' || $url == 'https://' ) {
        $return['status']    = 'fail';
        $return['code']      = 'error:nourl';
        $return['message']   = yourls__( 'Missing or malformed URL' );
        $return['errorCode'] = $return['statusCode'] = '400'; // 400 Bad Request

        return yourls_apply_filter( 'add_new_link_fail_nourl', $return, $url, $keyword, $title );
    }

    // Prevent DB flood
    $ip = yourls_get_IP();
    yourls_check_IP_flood( $ip );

    // Prevent internal redirection loops: cannot shorten a shortened URL
    if (yourls_is_shorturl($url)) {
        $return['status']    = 'fail';
        $return['code']      = 'error:noloop';
        $return['message']   = yourls__( 'URL is a short URL' );
        $return['errorCode'] = $return['statusCode'] = '400'; // 400 Bad Request
        return yourls_apply_filter( 'add_new_link_fail_noloop', $return, $url, $keyword, $title );
    }

    yourls_do_action( 'pre_add_new_link', $url, $keyword, $title );

    // Check if URL was already stored and we don't accept duplicates
    if ( !yourls_allow_duplicate_longurls() && ($url_exists = yourls_long_url_exists( $url )) ) {
        yourls_do_action( 'add_new_link_already_stored', $url, $keyword, $title );

        $return['status']   = 'fail';
        $return['code']     = 'error:url';
        $return['url']      = array( 'keyword' => $url_exists->keyword, 'url' => $url, 'title' => $url_exists->title, 'date' => $url_exists->timestamp, 'ip' => $url_exists->ip, 'clicks' => $url_exists->clicks );
        $return['message']  = /* //translators: eg "http://someurl/ already exists (short URL: sho.rt/abc)" */ yourls_s('%s already exists in database (short URL: %s)',
            yourls_trim_long_string($url), preg_replace('!https?://!', '',  yourls_get_yourls_site()) . '/'. $url_exists->keyword );
        $return['title']    = $url_exists->title;
        $return['shorturl'] = yourls_link($url_exists->keyword);
        $return['errorCode'] = $return['statusCode'] = '400'; // 400 Bad Request

        return yourls_apply_filter( 'add_new_link_already_stored_filter', $return, $url, $keyword, $title );
    }

    // Sanitize provided title, or fetch one
    if( isset( $title ) && !empty( $title ) ) {
        $title = yourls_sanitize_title( $title );
    } else {
        $title = yourls_get_remote_title( $url );
    }
    $title = yourls_apply_filter( 'add_new_title', $title, $url, $keyword );

    // Custom keyword provided : sanitize and make sure it's free
    if ($keyword) {
        yourls_do_action( 'add_new_link_custom_keyword', $url, $keyword, $title );

        $keyword = yourls_sanitize_keyword( $keyword, true );
        $keyword = yourls_apply_filter( 'custom_keyword', $keyword, $url, $title );

        if ( !yourls_keyword_is_free( $keyword ) ) {
            // This shorturl either reserved or taken already
            $return['status']  = 'fail';
            $return['code']    = 'error:keyword';
            $return['message'] = yourls_s( 'Short URL %s already exists in database or is reserved', $keyword );
            $return['errorCode'] = $return['statusCode'] = '400'; // 400 Bad Request

            return yourls_apply_filter( 'add_new_link_keyword_exists', $return, $url, $keyword, $title );
        }

        // Create random keyword
    } else {
        yourls_do_action( 'add_new_link_create_keyword', $url, $keyword, $title );

        $id = yourls_get_next_decimal();

        do {
            $keyword = yourls_int2string( $id );
            $keyword = yourls_apply_filter( 'random_keyword', $keyword, $url, $title );
            $id++;
        } while ( !yourls_keyword_is_free($keyword) );

        yourls_update_next_decimal($id);
    }

    // We should be all set now. Store the short URL !

    $timestamp = date( 'Y-m-d H:i:s' );

    try {
        if (yourls_insert_link_in_db( $url, $keyword, $title )){
            // everything ok, populate needed vars
            $return['url']      = array('keyword' => $keyword, 'url' => $url, 'title' => $title, 'date' => $timestamp, 'ip' => $ip );
            $return['status']   = 'success';
            $return['message']  = /* //translators: eg "http://someurl/ added to DB" */ yourls_s( '%s added to database', yourls_trim_long_string( $url ) );
            $return['title']    = $title;
            $return['html']     = yourls_table_add_row( $keyword, $url, $title, $ip, 0, time(), $row_id );
            $return['shorturl'] = yourls_link($keyword);
            $return['statusCode'] = '200'; // 200 OK
        } else {
            // unknown database error, couldn't store result
            $return['status']   = 'fail';
            $return['code']     = 'error:db';
            $return['message']  = yourls_s( 'Error saving url to database' );
            $return['errorCode'] = $return['statusCode'] = '500'; // 500 Internal Server Error
        }
    } catch (Exception $e) {
        // Keyword supposed to be free but the INSERT caused an exception: most likely we're facing a
        // concurrency problem. See Issue 2538.
        $return['status']  = 'fail';
        $return['code']    = 'error:concurrency';
        $return['message'] = $e->getMessage();
        $return['errorCode'] = $return['statusCode'] = '503'; // 503 Service Unavailable
    }

    yourls_do_action( 'post_add_new_link', $url, $keyword, $title, $return );

    return yourls_apply_filter( 'add_new_link', $return, $url, $keyword, $title );
}
/**
 * Returns the allowed character set for short URLs.
 *
 * @since 1.0
 * @return string The allowed character set.
 */
function yourls_get_shorturl_charset() {
    if ( defined( 'YOURLS_URL_CONVERT' ) && in_array( YOURLS_URL_CONVERT, [ 62, 64 ] ) ) {
        $charset = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }
    else {
        // defined to 36, or wrongly defined
        $charset = '0123456789abcdefghijklmnopqrstuvwxyz';
    }

    return yourls_apply_filter( 'get_shorturl_charset', $charset );
}

/**
 * Checks if a URL is a short URL.
 *
 * This function accepts either a full short URL (e.g., 'http://sho.rt/abc') or a keyword (e.g., 'abc').
 *
 * @since 1.0
 * @param string $shorturl The URL or keyword to check.
 * @return bool True if the URL is a short URL, false otherwise.
 */
function yourls_is_shorturl( $shorturl ) {
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
}

/**
 * Returns the list of reserved keywords.
 *
 * @since 1.0
 * @return array An array of reserved keywords.
 */
function yourls_get_reserved_URL() {
    global $yourls_reserved_URL;
    if ( ! isset( $yourls_reserved_URL ) || ! is_array( $yourls_reserved_URL ) ) {
        return array();
    }

    return $yourls_reserved_URL;
}

/**
 * Checks if a keyword is reserved.
 *
 * A keyword is reserved if it is in the list of reserved keywords or if it
 * corresponds to an existing page.
 *
 * @since 1.0
 * @param string $keyword The keyword to check.
 * @return bool True if the keyword is reserved, false otherwise.
 */
function yourls_keyword_is_reserved( $keyword ) {
    $keyword = yourls_sanitize_keyword( $keyword );
    $reserved = false;

    if ( in_array( $keyword, yourls_get_reserved_URL() )
        or yourls_is_page($keyword)
        or is_dir( YOURLS_ABSPATH ."/$keyword" )
    )
        $reserved = true;

    return yourls_apply_filter( 'keyword_is_reserved', $reserved, $keyword );
}

/**
 * Deletes a link from the database.
 *
 * @since 1.0
 * @param string $keyword The keyword of the link to delete.
 * @return int|null The number of deleted links, or null if the operation is short-circuited.
 */
function yourls_delete_link_by_keyword( $keyword ) {
    // Allow plugins to short-circuit the whole function
    $pre = yourls_apply_filter( 'shunt_delete_link_by_keyword', null, $keyword );
    if ( null !== $pre ) {
        return $pre;
    }

    $table = YOURLS_DB_TABLE_URL;
    $keyword = yourls_sanitize_keyword($keyword);
    $delete = yourls_get_db()->fetchAffected("DELETE FROM `$table` WHERE `keyword` = :keyword", array('keyword' => $keyword));
    yourls_do_action( 'delete_link', $keyword, $delete );
    return $delete;
}

/**
 * Inserts a new link into the database.
 *
 * @since 1.0
 * @param string $url     The long URL.
 * @param string $keyword The short URL keyword.
 * @param string $title   Optional. The title of the URL. Default ''.
 * @return bool True on success, false on failure.
 */
function yourls_insert_link_in_db($url, $keyword, $title = '' ) {
    $url       = yourls_sanitize_url($url);
    $keyword   = yourls_sanitize_keyword($keyword);
    $title     = yourls_sanitize_title($title);
    $timestamp = date('Y-m-d H:i:s');
    $ip        = yourls_get_IP();

    $table = YOURLS_DB_TABLE_URL;
    $binds = array(
        'keyword'   => $keyword,
        'url'       => $url,
        'title'     => $title,
        'timestamp' => $timestamp,
        'ip'        => $ip,
    );
    $insert = yourls_get_db()->fetchAffected("INSERT INTO `$table` (`keyword`, `url`, `title`, `timestamp`, `ip`, `clicks`) VALUES(:keyword, :url, :title, :timestamp, :ip, 0);", $binds);

    yourls_do_action( 'insert_link', (bool)$insert, $url, $keyword, $title, $timestamp, $ip );

    return (bool)$insert;
}

/**
 * Checks if a long URL already exists in the database.
 *
 * @since 1.7.10
 * @param string $url The URL to check.
 * @return object|null An object with URL information if the URL exists, otherwise null.
 */
function yourls_long_url_exists( $url ) {
    // Allow plugins to short-circuit the whole function
    $pre = yourls_apply_filter( 'shunt_url_exists', false, $url );
    if ( false !== $pre ) {
        return $pre;
    }

    $table = YOURLS_DB_TABLE_URL;
    $url   = yourls_sanitize_url($url);
    $url_exists = yourls_get_db()->fetchObject("SELECT * FROM `$table` WHERE `url` = :url", array('url'=>$url));

    if ($url_exists === false) {
        $url_exists = NULL;
    }

    return yourls_apply_filter( 'url_exists', $url_exists, $url );
}

/**
 * Edits a link in the database.
 *
 * @since 1.0
 * @param string $url        The new long URL.
 * @param string $keyword    The short URL keyword.
 * @param string $newkeyword Optional. The new short URL keyword. Default ''.
 * @param string $title      Optional. The new title. Default ''.
 * @return array An array containing the result of the operation.
 */
function yourls_edit_link($url, $keyword, $newkeyword='', $title='' ) {
    // Allow plugins to short-circuit the whole function
    $pre = yourls_apply_filter( 'shunt_edit_link', null, $keyword, $url, $keyword, $newkeyword, $title );
    if ( null !== $pre )
        return $pre;

    $ydb = yourls_get_db();

    $table = YOURLS_DB_TABLE_URL;
    $url = yourls_sanitize_url($url);
    $keyword = yourls_sanitize_keyword($keyword);
    $title = yourls_sanitize_title($title);
    $newkeyword = yourls_sanitize_keyword($newkeyword, true);

    if(!$url OR !$newkeyword) {
        $return['status']  = 'fail';
        $return['message'] = yourls__( 'Long URL or Short URL cannot be blank' );
        return yourls_apply_filter( 'edit_link', $return, $url, $keyword, $newkeyword, $title );
    }

    $old_url = $ydb->fetchValue("SELECT `url` FROM `$table` WHERE `keyword` = :keyword", array('keyword' => $keyword));

    // Check if new URL is not here already
    if ( $old_url != $url && !yourls_allow_duplicate_longurls() ) {
        $new_url_already_there = intval($ydb->fetchValue("SELECT COUNT(keyword) FROM `$table` WHERE `url` = :url;", array('url' => $url)));
    } else {
        $new_url_already_there = false;
    }

    // Check if the new keyword is not here already
    if ( $newkeyword != $keyword ) {
        $keyword_is_ok = yourls_keyword_is_free( $newkeyword );
    } else {
        $keyword_is_ok = true;
    }

    yourls_do_action( 'pre_edit_link', $url, $keyword, $newkeyword, $new_url_already_there, $keyword_is_ok );

    // All clear, update
    if ( ( !$new_url_already_there || yourls_allow_duplicate_longurls() ) && $keyword_is_ok ) {
            $sql   = "UPDATE `$table` SET `url` = :url, `keyword` = :newkeyword, `title` = :title WHERE `keyword` = :keyword";
            $binds = array('url' => $url, 'newkeyword' => $newkeyword, 'title' => $title, 'keyword' => $keyword);
            $update_url = $ydb->fetchAffected($sql, $binds);
        if( $update_url ) {
            $return['url']     = array( 'keyword'       => $newkeyword,
                                        'shorturl'      => yourls_link($newkeyword),
                                        'url'           => yourls_esc_url($url),
                                        'display_url'   => yourls_esc_html(yourls_trim_long_string($url)),
                                        'title'         => yourls_esc_attr($title),
                                        'display_title' => yourls_esc_html(yourls_trim_long_string( $title ))
                                );
            $return['status']  = 'success';
            $return['message'] = yourls__( 'Link updated in database' );
        } else {
            $return['status']  = 'fail';
            $return['message'] = /* //translators: "Error updating http://someurl/ (Shorturl: http://sho.rt/blah)" */ yourls_s( 'Error updating %s (Short URL: %s)', yourls_esc_html(yourls_trim_long_string($url)), $keyword ) ;
        }

    // Nope
    } else {
        $return['status']  = 'fail';
        $return['message'] = yourls__( 'URL or keyword already exists in database' );
    }

    return yourls_apply_filter( 'edit_link', $return, $url, $keyword, $newkeyword, $title, $new_url_already_there, $keyword_is_ok );
}

/**
 * Updates the title of a link.
 *
 * @since 1.0
 * @param string $keyword The short URL keyword.
 * @param string $title   The new title.
 * @return int|null The number of updated rows, or null if the operation is short-circuited.
 */
function yourls_edit_link_title( $keyword, $title ) {
    // Allow plugins to short-circuit the whole function
    $pre = yourls_apply_filter( 'shunt_edit_link_title', null, $keyword, $title );
    if ( null !== $pre ) {
        return $pre;
    }

    $keyword = yourls_sanitize_keyword( $keyword );
    $title = yourls_sanitize_title( $title );

    $table = YOURLS_DB_TABLE_URL;
    $update = yourls_get_db()->fetchAffected("UPDATE `$table` SET `title` = :title WHERE `keyword` = :keyword;", array('title' => $title, 'keyword' => $keyword));

    return $update;
}

/**
 * Checks if a keyword is available.
 *
 * A keyword is available if it is not reserved and not already taken.
 *
 * @since 1.0
 * @param string $keyword The keyword to check.
 * @return bool True if the keyword is available, false otherwise.
 */
function yourls_keyword_is_free( $keyword  ) {
    $free = true;
    if ( yourls_keyword_is_reserved( $keyword ) or yourls_keyword_is_taken( $keyword, false ) ) {
        $free = false;
    }

    return yourls_apply_filter( 'keyword_is_free', $free, $keyword );
}

/**
 * Checks if a keyword corresponds to a page.
 *
 * @since 1.7.10
 * @see https://docs.yourls.org/guide/extend/pages.html
 * @param string $keyword The keyword to check.
 * @return bool True if the keyword corresponds to a page, false otherwise.
 */
function yourls_is_page($keyword) {
    return yourls_apply_filter( 'is_page', file_exists( YOURLS_PAGEDIR . "/$keyword.php" ) );
}

/**
 * Checks if a keyword is taken.
 *
 * @since 1.0
 * @param string $keyword   The keyword to check.
 * @param bool   $use_cache Optional. Whether to use the cache. Default true.
 * @return bool True if the keyword is taken, false otherwise.
 */
function yourls_keyword_is_taken( $keyword, $use_cache = true ) {
    // Allow plugins to short-circuit the whole function
    $pre = yourls_apply_filter( 'shunt_keyword_is_taken', false, $keyword );
    if ( false !== $pre ) {
        return $pre;
    }

    $taken = false;
    // To check if a keyword is already associated with a short URL, we fetch all info matching that keyword. This
    // will save a query in case of a redirection in yourls-go.php because info will be cached
    if ( yourls_get_keyword_infos($keyword, $use_cache) ) {
        $taken = true;
    }

    return yourls_apply_filter( 'keyword_is_taken', $taken, $keyword );
}

/**
 * Returns all information associated with a keyword.
 *
 * @since 1.4
 * @param string $keyword   The short URL keyword.
 * @param bool   $use_cache Optional. Whether to use the cache. Default true.
 * @return array|false An array of information, or false if the keyword is not found.
 */
function yourls_get_keyword_infos( $keyword, $use_cache = true ) {
    $ydb = yourls_get_db();
    $keyword = yourls_sanitize_keyword( $keyword );

    yourls_do_action( 'pre_get_keyword', $keyword, $use_cache );

    if( $ydb->has_infos($keyword) && $use_cache === true ) {
        return yourls_apply_filter( 'get_keyword_infos', $ydb->get_infos($keyword), $keyword );
    }

    yourls_do_action( 'get_keyword_not_cached', $keyword );

    $table = YOURLS_DB_TABLE_URL;
    $infos = $ydb->fetchObject("SELECT * FROM `$table` WHERE `keyword` = :keyword", array('keyword' => $keyword));

    if( $infos ) {
        $infos = (array)$infos;
        $ydb->set_infos($keyword, $infos);
    } else {
        // is NULL if not found
        $infos = false;
        $ydb->set_infos($keyword, false);
    }

    return yourls_apply_filter( 'get_keyword_infos', $infos, $keyword );
}

/**
 * Returns a specific piece of information about a keyword.
 *
 * @since 1.0
 * @param string      $keyword  The short URL keyword.
 * @param string      $field    The field to return (e.g., 'url', 'title', 'ip', 'clicks', 'timestamp').
 * @param false|string $notfound Optional. The value to return if the keyword is not found. Default false.
 * @return mixed The value of the field, or the value of $notfound if the keyword is not found.
 */
function yourls_get_keyword_info($keyword, $field, $notfound = false ) {

    // Allow plugins to short-circuit the whole function
    $pre = yourls_apply_filter( 'shunt_get_keyword_info', false, $keyword, $field, $notfound );
    if ( false !== $pre )
        return $pre;

    $keyword = yourls_sanitize_keyword( $keyword );
    $infos = yourls_get_keyword_infos( $keyword );

    $return = $notfound;
    if ( isset( $infos[ $field ] ) && $infos[ $field ] !== false )
        $return = $infos[ $field ];

    return yourls_apply_filter( 'get_keyword_info', $return, $keyword, $field, $notfound );
}

/**
 * Returns the title of a short URL.
 *
 * @since 1.0
 * @param string      $keyword  The short URL keyword.
 * @param false|string $notfound Optional. The value to return if the keyword is not found. Default false.
 * @return string The title of the short URL.
 */
function yourls_get_keyword_title( $keyword, $notfound = false ) {
    return yourls_get_keyword_info( $keyword, 'title', $notfound );
}

/**
 * Returns the long URL of a short URL.
 *
 * @since 1.0
 * @param string      $keyword  The short URL keyword.
 * @param false|string $notfound Optional. The value to return if the keyword is not found. Default false.
 * @return string The long URL.
 */
function yourls_get_keyword_longurl( $keyword, $notfound = false ) {
    return yourls_get_keyword_info( $keyword, 'url', $notfound );
}

/**
 * Returns the number of clicks for a short URL.
 *
 * @since 1.0
 * @param string      $keyword  The short URL keyword.
 * @param false|string $notfound Optional. The value to return if the keyword is not found. Default false.
 * @return int The number of clicks.
 */
function yourls_get_keyword_clicks( $keyword, $notfound = false ) {
    return yourls_get_keyword_info( $keyword, 'clicks', $notfound );
}

/**
 * Returns the IP address that created a short URL.
 *
 * @since 1.0
 * @param string      $keyword  The short URL keyword.
 * @param false|string $notfound Optional. The value to return if the keyword is not found. Default false.
 * @return string The IP address.
 */
function yourls_get_keyword_IP( $keyword, $notfound = false ) {
    return yourls_get_keyword_info( $keyword, 'ip', $notfound );
}

/**
 * Returns the timestamp of a short URL.
 *
 * @since 1.0
 * @param string      $keyword  The short URL keyword.
 * @param false|string $notfound Optional. The value to return if the keyword is not found. Default false.
 * @return string The timestamp.
 */
function yourls_get_keyword_timestamp( $keyword, $notfound = false ) {
    return yourls_get_keyword_info( $keyword, 'timestamp', $notfound );
}

/**
 * Returns an array of stats for a given keyword.
 *
 * @since 1.7.10
 * @param string $shorturl The short URL keyword.
 * @return array An array of stats.
 */
function yourls_get_keyword_stats( $shorturl ) {
    $table_url = YOURLS_DB_TABLE_URL;
    $shorturl  = yourls_sanitize_keyword( $shorturl );

    $res = yourls_get_db()->fetchObject("SELECT * FROM `$table_url` WHERE `keyword` = :keyword", array('keyword' => $shorturl));

    if( !$res ) {
        // non existent link
        $return = array(
            'statusCode' => '404',
            'message'    => 'Error: short URL not found',
        );
    } else {
        $return = array(
            'statusCode' => '200',
            'message'    => 'success',
            'link'       => array(
                'shorturl' => yourls_link($res->keyword),
                'url'      => $res->url,
                'title'    => $res->title,
                'timestamp'=> $res->timestamp,
                'ip'       => $res->ip,
                'clicks'   => $res->clicks,
            )
        );
    }

    return yourls_apply_filter( 'get_link_stats', $return, $shorturl );
}

/**
 * Returns an array of keywords that redirect to the submitted long URL.
 *
 * @since 1.7
 * @param string $longurl The long URL.
 * @param string $order   Optional. The sort order ('ASC' or 'DESC'). Default 'ASC'.
 * @return array An array of keywords.
 */
function yourls_get_longurl_keywords( $longurl, $order = 'ASC' ) {
    $longurl = yourls_sanitize_url($longurl);
    $table   = YOURLS_DB_TABLE_URL;
    $sql     = "SELECT `keyword` FROM `$table` WHERE `url` = :url";

    if (in_array($order, array('ASC','DESC'))) {
        $sql .= " ORDER BY `keyword` ".$order;
    }

    return yourls_apply_filter( 'get_longurl_keywords', yourls_get_db()->fetchCol($sql, array('url'=>$longurl)), $longurl );
}
