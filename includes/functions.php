<?php
/**
 * YOURLS General Functions
 *
 * This file contains the core functions that power YOURLS. These functions are
 * used throughout the application for various tasks such as database
 * operations, URL handling, and user authentication.
 *
 * @package YOURLS
 * @since 1.0
 */

/**
 * Make an optimized regexp pattern from a string of characters.
 *
 * @since 1.0
 * @param string $string The string of characters to be escaped.
 * @return string The escaped string, suitable for use in a regular expression.
 */
function yourls_make_regexp_pattern( $string ) {
    // Simple benchmarks show that regexp with smarter sequences (0-9, a-z, A-Z...) are not faster or slower than 0123456789 etc...
    // add @ as an escaped character because @ is used as the regexp delimiter in yourls-loader.php
    return preg_quote( $string, '@' );
}

/**
 * Gets the IP address of the client.
 *
 * @since 1.0
 * @return string The IP address of the client.
 */
function yourls_get_IP() {
    $ip = '';

    // Precedence: if set, X-Forwarded-For > HTTP_X_FORWARDED_FOR > HTTP_CLIENT_IP > HTTP_VIA > REMOTE_ADDR
    $headers = [ 'X-Forwarded-For', 'HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'HTTP_VIA', 'REMOTE_ADDR' ];
    foreach( $headers as $header ) {
        if ( !empty( $_SERVER[ $header ] ) ) {
            $ip = $_SERVER[ $header ];
            break;
        }
    }

    // headers can contain multiple IPs (X-Forwarded-For = client, proxy1, proxy2). Take first one.
    if ( strpos( $ip, ',' ) !== false )
        $ip = substr( $ip, 0, strpos( $ip, ',' ) );

    return (string)yourls_apply_filter( 'get_IP', yourls_sanitize_ip( $ip ) );
}

/**
 * Gets the next decimal value to be used for a new short URL.
 *
 * @since 1.0
 * @return int The next decimal value.
 */
function yourls_get_next_decimal() {
    return (int)yourls_apply_filter( 'get_next_decimal', (int)yourls_get_option( 'next_id' ) );
}

/**
 * Updates the next decimal value to be used for a new short URL.
 *
 * @since 1.0
 * @param int $int The new decimal value.
 * @return bool True if the value was updated, false otherwise.
 */
function yourls_update_next_decimal( $int = 0 ) {
    $int = ( $int == 0 ) ? yourls_get_next_decimal() + 1 : (int)$int ;
    $update = yourls_update_option( 'next_id', $int );
    yourls_do_action( 'update_next_decimal', $int, $update );
    return $update;
}

/**
 * Encodes an array into an XML string.
 *
 * This function takes an associative array and converts it into a well-formed
 * XML string. It is useful for generating XML output for API responses or
 * other XML-based data exchange.
 *
 * @since 1.0
 * @param array $array The array to be encoded.
 * @return string The XML-encoded string.
 */
function yourls_xml_encode( $array ) {
    return (\Spatie\ArrayToXml\ArrayToXml::convert($array, '', true, 'UTF-8'));
}

/**
 * Updates the click count for a short URL.
 *
 * @since 1.0
 * @param string $keyword The short URL keyword.
 * @param int|false $clicks The new click count, or false to increment by 1.
 * @return int The number of affected rows.
 */
function yourls_update_clicks( $keyword, $clicks = false ) {
    // Allow plugins to short-circuit the whole function
    $pre = yourls_apply_filter( 'shunt_update_clicks', false, $keyword, $clicks );
    if ( false !== $pre ) {
        return $pre;
    }

    $keyword = yourls_sanitize_keyword( $keyword );
    $table = YOURLS_DB_TABLE_URL;
    if ( $clicks !== false && is_int( $clicks ) && $clicks >= 0 ) {
        $update = "UPDATE `$table` SET `clicks` = :clicks WHERE `keyword` = :keyword";
        $values = [ 'clicks' => $clicks, 'keyword' => $keyword ];
    } else {
        $update = "UPDATE `$table` SET `clicks` = clicks + 1 WHERE `keyword` = :keyword";
        $values = [ 'keyword' => $keyword ];
    }

    // Try and update click count. An error probably means a concurrency problem : just skip the update
    try {
        $result = yourls_get_db()->fetchAffected($update, $values);
    } catch (Exception $e) {
        $result = 0;
    }

    yourls_do_action( 'update_clicks', $keyword, $result, $clicks );

    return $result;
}


/**
 * Gets statistics for a range of links.
 *
 * @since 1.0
 * @param string $filter The filter to apply to the links. Can be 'top',
 *                       'bottom', 'rand', or 'last'.
 * @param int    $limit  The maximum number of links to return.
 * @param int    $start  The starting offset.
 * @return array An array of link statistics.
 */
function yourls_get_stats($filter = 'top', $limit = 10, $start = 0) {
    switch( $filter ) {
        case 'bottom':
            $sort_by    = '`clicks`';
            $sort_order = 'asc';
            break;
        case 'last':
            $sort_by    = '`timestamp`';
            $sort_order = 'desc';
            break;
        case 'rand':
        case 'random':
            $sort_by    = 'RAND()';
            $sort_order = '';
            break;
        case 'top':
        default:
            $sort_by    = '`clicks`';
            $sort_order = 'desc';
            break;
    }

    // Fetch links
    $limit = intval( $limit );
    $start = intval( $start );
    if ( $limit > 0 ) {

        $table_url = YOURLS_DB_TABLE_URL;
        $results = yourls_get_db()->fetchObjects( "SELECT * FROM `$table_url` WHERE 1=1 ORDER BY $sort_by $sort_order LIMIT $start, $limit;" );

        $return = [];
        $i = 1;

        foreach ( (array)$results as $res ) {
            $return['links']['link_'.$i++] = [
                'shorturl' => yourls_link($res->keyword),
                'url'      => $res->url,
                'title'    => $res->title,
                'timestamp'=> $res->timestamp,
                'ip'       => $res->ip,
                'clicks'   => $res->clicks,
            ];
        }
    }

    $return['stats'] = yourls_get_db_stats();

    $return['statusCode'] = '200';

    return yourls_apply_filter( 'get_stats', $return, $filter, $limit, $start );
}

/**
 * Gets the total number of links and clicks.
 *
 * @since 1.0
 * @param array $where An array of WHERE clauses to apply to the query.
 * @return array An array containing the total number of links and clicks.
 */
function yourls_get_db_stats( $where = [ 'sql' => '', 'binds' => [] ] ) {
    $table_url = YOURLS_DB_TABLE_URL;

    $totals = yourls_get_db()->fetchObject( "SELECT COUNT(keyword) as count, SUM(clicks) as sum FROM `$table_url` WHERE 1=1 " . $where['sql'] , $where['binds'] );
    $return = [ 'total_links' => $totals->count, 'total_clicks' => $totals->sum ];

    return yourls_apply_filter( 'get_db_stats', $return, $where );
}

/**
 * Gets the user agent string of the client.
 *
 * @since 1.0
 * @return string The user agent string.
 */
function yourls_get_user_agent() {
    $ua = '-';

    if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
        $ua = strip_tags( html_entity_decode( $_SERVER['HTTP_USER_AGENT'] ));
        $ua = preg_replace('![^0-9a-zA-Z\':., /{}\(\)\[\]\+@&\!\?;_\-=~\*\#]!', '', $ua );
    }

    return yourls_apply_filter( 'get_user_agent', substr( $ua, 0, 255 ) );
}

/**
 * Gets the referrer string of the client.
 *
 * @since 1.0
 * @return string The referrer string.
 */
function yourls_get_referrer() {
    $referrer = isset( $_SERVER['HTTP_REFERER'] ) ? yourls_sanitize_url_safe( $_SERVER['HTTP_REFERER'] ) : 'direct';

    return yourls_apply_filter( 'get_referrer', substr( $referrer, 0, 200 ) );
}

/**
 * Redirects to another page.
 *
 * @since 1.4
 * @param string $location The URL to redirect to.
 * @param int    $code     The HTTP status code to use for the redirection.
 * @return int 1 for header redirection, 2 for JavaScript redirection, or 3 for CLI.
 */
function yourls_redirect( $location, $code = 301 ) {
    yourls_do_action( 'pre_redirect', $location, $code );
    $location = yourls_apply_filter( 'redirect_location', $location, $code );
    $code     = yourls_apply_filter( 'redirect_code', $code, $location );

    // Redirect, either properly if possible, or via Javascript otherwise
    if( !headers_sent() ) {
        yourls_status_header( $code );
        header( "Location: $location" );
        return 1;
    }

    // Headers sent : redirect with JS if not in CLI
    if( php_sapi_name() !== 'cli') {
        yourls_redirect_javascript( $location );
        return 2;
    }

    // We're in CLI
    return 3;
}

/**
 * Redirects to a short URL.
 *
 * @since 1.7.3
 * @param string $url The long URL to redirect to.
 * @param string $keyword The short URL keyword.
 * @return void
 */
function yourls_redirect_shorturl($url, $keyword) {
    yourls_do_action( 'redirect_shorturl', $url, $keyword );

    // Attempt to update click count in main table
    yourls_update_clicks( $keyword );

    // Update detailed log for stats
    yourls_log_redirect( $keyword );

    // Send an X-Robots-Tag header
    yourls_robots_tag_header();

    yourls_redirect( $url, 301 );
}

/**
 * Sends an X-Robots-Tag header.
 *
 * @since 1.9.2
 * @return void
 */
function yourls_robots_tag_header() {
    // Allow plugins to short-circuit the whole function
    $pre = yourls_apply_filter( 'shunt_robots_tag_header', false );
    if ( false !== $pre ) {
        return $pre;
    }

    // By default, we're sending a 'noindex' header
    $tag = yourls_apply_filter( 'robots_tag_header', 'noindex' );
    $replace = yourls_apply_filter( 'robots_tag_header_replace', true );
    if ( !headers_sent() ) {
        header( "X-Robots-Tag: $tag", $replace );
    }
}


/**
 * Sends headers to prevent caching.
 *
 * @since 1.7.10
 * @return void
 */
function yourls_no_cache_headers() {
    if( !headers_sent() ) {
        header( 'Expires: Thu, 23 Mar 1972 07:00:00 GMT' );
        header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
        header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
        header( 'Pragma: no-cache' );
    }
}

/**
 * Sends an X-Frame-Options header to prevent clickjacking.
 *
 * @since 1.8.1
 * @return void
 */
function yourls_no_frame_header() {
    // Allow plugins to short-circuit the whole function
    $pre = yourls_apply_filter( 'shunt_no_frame_header', false );
    if ( false !== $pre ) {
        return $pre;
    }

    if( !headers_sent() ) {
        header( 'X-Frame-Options: SAMEORIGIN' );
    }
}

/**
 * Sends a Content-Type header.
 *
 * @since 1.7
 * @param string $type The content type to send.
 * @return bool True if the header was sent, false otherwise.
 */
function yourls_content_type_header( $type ) {
    yourls_do_action( 'content_type_header', $type );
    if( !headers_sent() ) {
        $charset = yourls_apply_filter( 'content_type_header_charset', 'utf-8' );
        header( "Content-Type: $type; charset=$charset" );
        return true;
    }
    return false;
}

/**
 * Sends an HTTP status header.
 *
 * @since 1.4
 * @param int $code The HTTP status code to send.
 * @return bool True if the header was sent, false otherwise.
 */
function yourls_status_header( $code = 200 ) {
    yourls_do_action( 'status_header', $code );

    if( headers_sent() )
        return false;

    $protocol = $_SERVER['SERVER_PROTOCOL'];
    if ( 'HTTP/1.1' != $protocol && 'HTTP/1.0' != $protocol )
        $protocol = 'HTTP/1.0';

    $code = intval( $code );
    $desc = yourls_get_HTTP_status( $code );

    @header ("$protocol $code $desc"); // This causes problems on IIS and some FastCGI setups

    return true;
}

/**
 * Redirects to another page using JavaScript.
 *
 * @since 1.0
 * @param string $location The URL to redirect to.
 * @param bool   $dontwait If true, the redirection will be immediate.
 *                         If false, the user will be prompted to click a link.
 * @return void
 */
function yourls_redirect_javascript( $location, $dontwait = true ) {
    yourls_do_action( 'pre_redirect_javascript', $location, $dontwait );
    $location = yourls_apply_filter( 'redirect_javascript', $location, $dontwait );
    if ( $dontwait ) {
        $message = yourls_s( 'if you are not redirected after 10 seconds, please <a href="%s">click here</a>', $location );
        echo <<<REDIR
        <script type="text/javascript">
        window.location="$location";
        </script>
        <small>($message)</small>
REDIR;
    }
    else {
        echo '<p>'.yourls_s( 'Please <a href="%s">click here</a>', $location ).'</p>';
    }
    yourls_do_action( 'post_redirect_javascript', $location );
}

/**
 * Gets the description for an HTTP status code.
 *
 * @since 1.0
 * @param int $code The HTTP status code.
 * @return string The description of the HTTP status code.
 */
function yourls_get_HTTP_status( $code ) {
    $code = intval( $code );
    $headers_desc = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',

        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        226 => 'IM Used',

        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',

        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',

        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        510 => 'Not Extended'
    ];

    return $headers_desc[$code] ?? '';
}

/**
 * Logs a redirection.
 *
 * @since 1.4
 * @param string $keyword The short URL keyword.
 * @return int The number of affected rows.
 */
function yourls_log_redirect( $keyword ) {
    // Allow plugins to short-circuit the whole function
    $pre = yourls_apply_filter( 'shunt_log_redirect', false, $keyword );
    if ( false !== $pre ) {
        return $pre;
    }

    if (!yourls_do_log_redirect()) {
        return true;
    }

    $table = YOURLS_DB_TABLE_LOG;
    $ip = yourls_get_IP();
    $binds = [
        'now' => date( 'Y-m-d H:i:s' ),
        'keyword'  => yourls_sanitize_keyword($keyword),
        'referrer' => substr( yourls_get_referrer(), 0, 200 ),
        'ua'       => substr(yourls_get_user_agent(), 0, 255),
        'ip'       => $ip,
        'location' => yourls_geo_ip_to_countrycode($ip),
    ];

    // Try and log. An error probably means a concurrency problem : just skip the logging
    try {
        $result = yourls_get_db()->fetchAffected("INSERT INTO `$table` (click_time, shorturl, referrer, user_agent, ip_address, country_code) VALUES (:now, :keyword, :referrer, :ua, :ip, :location)", $binds );
    } catch (Exception $e) {
        $result = 0;
    }

    return $result;
}

/**
 * Checks if redirection logging is enabled.
 *
 * @since 1.0
 * @return bool True if redirection logging is enabled, false otherwise.
 */
function yourls_do_log_redirect() {
    return ( !defined( 'YOURLS_NOSTATS' ) || YOURLS_NOSTATS != true );
}

/**
 * Checks if an upgrade is needed.
 *
 * @since 1.0
 * @return bool True if an upgrade is needed, false otherwise.
 */
function yourls_upgrade_is_needed() {
    // check YOURLS_DB_VERSION exist && match values stored in YOURLS_DB_TABLE_OPTIONS
    list( $currentver, $currentsql ) = yourls_get_current_version_from_sql();
    if ( $currentsql < YOURLS_DB_VERSION ) {
        return true;
    }

    // Check if YOURLS_VERSION exist && match value stored in YOURLS_DB_TABLE_OPTIONS, update DB if required
    if ( $currentver < YOURLS_VERSION ) {
        yourls_update_option( 'version', YOURLS_VERSION );
    }

    return false;
}

/**
 * Gets the current YOURLS version from the database.
 *
 * @since 1.4
 * @return array An array containing the current version and database version.
 */
function yourls_get_current_version_from_sql() {
    $currentver = yourls_get_option( 'version' );
    $currentsql = yourls_get_option( 'db_version' );

    // Values if version is 1.3
    if ( !$currentver ) {
        $currentver = '1.3';
    }
    if ( !$currentsql ) {
        $currentsql = '100';
    }

    return [ $currentver, $currentsql ];
}

/**
 * Checks if the current installation is private.
 *
 * @since 1.0
 * @return bool True if the installation is private, false otherwise.
 */
function yourls_is_private() {
    $private = defined( 'YOURLS_PRIVATE' ) && YOURLS_PRIVATE;

    if ( $private ) {

        // Allow overruling for particular pages:

        // API
        if ( yourls_is_API() && defined( 'YOURLS_PRIVATE_API' ) ) {
            $private = YOURLS_PRIVATE_API;
        }
        // Stat pages
        elseif ( yourls_is_infos() && defined( 'YOURLS_PRIVATE_INFOS' ) ) {
            $private = YOURLS_PRIVATE_INFOS;
        }
        // Others future cases ?
    }

    return yourls_apply_filter( 'is_private', $private );
}

/**
 * Checks if duplicate long URLs are allowed.
 *
 * @since 1.0
 * @return bool True if duplicate long URLs are allowed, false otherwise.
 */
function yourls_allow_duplicate_longurls() {
    // special treatment if API to check for WordPress plugin requests
    if ( yourls_is_API() && isset( $_REQUEST[ 'source' ] ) && $_REQUEST[ 'source' ] == 'plugin' ) {
            return false;
    }

    return yourls_apply_filter('allow_duplicate_longurls', defined('YOURLS_UNIQUE_URLS') && !YOURLS_UNIQUE_URLS);
}

/**
 * Checks if an IP address is flooding the database.
 *
 * @since 1.0
 * @param string $ip The IP address to check.
 * @return bool True if the IP address is not flooding, otherwise the function will die.
 */
function yourls_check_IP_flood( $ip = '' ) {

    // Allow plugins to short-circuit the whole function
    $pre = yourls_apply_filter( 'shunt_check_IP_flood', false, $ip );
    if ( false !== $pre )
        return $pre;

    yourls_do_action( 'pre_check_ip_flood', $ip ); // at this point $ip can be '', check it if your plugin hooks in here

    // Raise white flag if installing or if no flood delay defined
    if(
        ( defined('YOURLS_FLOOD_DELAY_SECONDS') && YOURLS_FLOOD_DELAY_SECONDS === 0 ) ||
        !defined('YOURLS_FLOOD_DELAY_SECONDS') ||
        yourls_is_installing()
    )
        return true;

    // Don't throttle logged in users
    if( yourls_is_private() ) {
         if( yourls_is_valid_user() === true )
            return true;
    }

    // Don't throttle whitelist IPs
    if( defined( 'YOURLS_FLOOD_IP_WHITELIST' ) && YOURLS_FLOOD_IP_WHITELIST ) {
        $whitelist_ips = explode( ',', YOURLS_FLOOD_IP_WHITELIST );
        foreach( (array)$whitelist_ips as $whitelist_ip ) {
            $whitelist_ip = trim( $whitelist_ip );
            if ( $whitelist_ip == $ip )
                return true;
        }
    }

    $ip = ( $ip ? yourls_sanitize_ip( $ip ) : yourls_get_IP() );

    yourls_do_action( 'check_ip_flood', $ip );

    $table = YOURLS_DB_TABLE_URL;
    $lasttime = yourls_get_db()->fetchValue( "SELECT `timestamp` FROM $table WHERE `ip` = :ip ORDER BY `timestamp` DESC LIMIT 1", [ 'ip' => $ip ] );
    if( $lasttime ) {
        $now = date( 'U' );
        $then = date( 'U', strtotime( $lasttime ) );
        if( ( $now - $then ) <= YOURLS_FLOOD_DELAY_SECONDS ) {
            // Flood!
            yourls_do_action( 'ip_flood', $ip, $now - $then );
            yourls_die( yourls__( 'Too many URLs added too fast. Slow down please.' ), yourls__( 'Too Many Requests' ), 429 );
        }
    }

    return true;
}

/**
 * Checks if YOURLS is currently being installed.
 *
 * @since 1.6
 * @return bool True if YOURLS is being installed, false otherwise.
 */
function yourls_is_installing() {
    return (bool)yourls_apply_filter( 'is_installing', defined( 'YOURLS_INSTALLING' ) && YOURLS_INSTALLING );
}

/**
 * Checks if YOURLS is currently being upgraded.
 *
 * @since 1.6
 * @return bool True if YOURLS is being upgraded, false otherwise.
 */
function yourls_is_upgrading() {
    return (bool)yourls_apply_filter( 'is_upgrading', defined( 'YOURLS_UPGRADING' ) && YOURLS_UPGRADING );
}

/**
 * Checks if YOURLS is installed.
 *
 * @since 1.0
 * @return bool True if YOURLS is installed, false otherwise.
 */
function yourls_is_installed() {
    return (bool)yourls_apply_filter( 'is_installed', yourls_get_db()->is_installed() );
}

/**
 * Sets the installed state of YOURLS.
 *
 * @since 1.7.3
 * @param bool $bool True if YOURLS is installed, false otherwise.
 * @return void
 */
function yourls_set_installed( $bool ) {
    yourls_get_db()->set_installed( $bool );
}

/**
 * Generates a random string of a given length and type.
 *
 * @since 1.0
 * @param int    $length   The length of the random string.
 * @param int    $type     The type of characters to use.
 * @param string $charlist A custom character list to use.
 * @return string The random string.
 */
function yourls_rnd_string ( $length = 5, $type = 0, $charlist = '' ) {
    $length = intval( $length );

    // define possible characters
    switch ( $type ) {

        // no vowels to make no offending word, no 0/1/o/l to avoid confusion between letters & digits. Perfect for passwords.
        case '1':
            $possible = "23456789bcdfghjkmnpqrstvwxyz";
            break;

        // Same, with lower + upper
        case '2':
            $possible = "23456789bcdfghjkmnpqrstvwxyzBCDFGHJKMNPQRSTVWXYZ";
            break;

        // all letters, lowercase
        case '3':
            $possible = "abcdefghijklmnopqrstuvwxyz";
            break;

        // all letters, lowercase + uppercase
        case '4':
            $possible = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
            break;

        // all digits & letters lowercase
        case '5':
            $possible = "0123456789abcdefghijklmnopqrstuvwxyz";
            break;

        // all digits & letters lowercase + uppercase
        case '6':
            $possible = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
            break;

        // custom char list, or comply to charset as defined in config
        default:
        case '0':
            $possible = $charlist ? $charlist : yourls_get_shorturl_charset();
            break;
    }

    $str = substr( str_shuffle( $possible ), 0, $length );
    return yourls_apply_filter( 'rnd_string', $str, $length, $type, $charlist );
}

/**
 * Checks if the current request is an API request.
 *
 * @since 1.0
 * @return bool True if the current request is an API request, false otherwise.
 */
function yourls_is_API() {
    return (bool)yourls_apply_filter( 'is_API', defined( 'YOURLS_API' ) && YOURLS_API );
}

/**
 * Checks if the current request is an AJAX request.
 *
 * @since 1.0
 * @return bool True if the current request is an AJAX request, false otherwise.
 */
function yourls_is_Ajax() {
    return (bool)yourls_apply_filter( 'is_Ajax', defined( 'YOURLS_AJAX' ) && YOURLS_AJAX );
}

/**
 * Checks if the current request is a redirection.
 *
 * @since 1.0
 * @return bool True if the current request is a redirection, false otherwise.
 */
function yourls_is_GO() {
    return (bool)yourls_apply_filter( 'is_GO', defined( 'YOURLS_GO' ) && YOURLS_GO );
}

/**
 * Checks if the current request is for stats.
 *
 * @since 1.0
 * @return bool True if the current request is for stats, false otherwise.
 */
function yourls_is_infos() {
    return (bool)yourls_apply_filter( 'is_infos', defined( 'YOURLS_INFOS' ) && YOURLS_INFOS );
}

/**
 * Checks if the current request is for the admin area.
 *
 * @since 1.0
 * @return bool True if the current request is for the admin area, false otherwise.
 */
function yourls_is_admin() {
    return (bool)yourls_apply_filter( 'is_admin', defined( 'YOURLS_ADMIN' ) && YOURLS_ADMIN );
}

/**
 * Checks if the server is running on Windows.
 *
 * @since 1.0
 * @return bool True if the server is running on Windows, false otherwise.
 */
function yourls_is_windows() {
    return defined( 'DIRECTORY_SEPARATOR' ) && DIRECTORY_SEPARATOR == '\\';
}

/**
 * Checks if SSL is required for the admin area.
 *
 * @since 1.0
 * @return bool True if SSL is required, false otherwise.
 */
function yourls_needs_ssl() {
    return (bool)yourls_apply_filter( 'needs_ssl', defined( 'YOURLS_ADMIN_SSL' ) && YOURLS_ADMIN_SSL );
}

/**
 * Checks if the current request is over SSL.
 *
 * @since 1.0
 * @return bool True if the current request is over SSL, false otherwise.
 */
function yourls_is_ssl() {
    $is_ssl = false;
    if ( isset( $_SERVER[ 'HTTPS' ] ) ) {
        if ( 'on' == strtolower( $_SERVER[ 'HTTPS' ] ) ) {
            $is_ssl = true;
        }
        if ( '1' == $_SERVER[ 'HTTPS' ] ) {
            $is_ssl = true;
        }
    }
    elseif ( isset( $_SERVER[ 'HTTP_X_FORWARDED_PROTO' ] ) ) {
        if ( 'https' == strtolower( $_SERVER[ 'HTTP_X_FORWARDED_PROTO' ] ) ) {
            $is_ssl = true;
        }
    }
    elseif ( isset( $_SERVER[ 'SERVER_PORT' ] ) && ( '443' == $_SERVER[ 'SERVER_PORT' ] ) ) {
        $is_ssl = true;
    }
    return (bool)yourls_apply_filter( 'is_ssl', $is_ssl );
}

/**
 * Gets the title of a remote page.
 *
 * @since 1.0
 * @param string $url The URL of the remote page.
 * @return string The title of the remote page, or the URL if the title cannot be found.
 */
function yourls_get_remote_title( $url ) {
    // Allow plugins to short-circuit the whole function
    $pre = yourls_apply_filter( 'shunt_get_remote_title', false, $url );
    if ( false !== $pre ) {
        return $pre;
    }

    $url = yourls_sanitize_url( $url );

    // Only deal with http(s)://
    if ( !in_array( yourls_get_protocol( $url ), [ 'http://', 'https://' ] ) ) {
        return $url;
    }

    $title = $charset = false;

    $max_bytes = yourls_apply_filter( 'get_remote_title_max_byte', 32768 ); // limit data fetching to 32K in order to find a <title> tag

    $response = yourls_http_get( $url, [], [], [ 'max_bytes' => $max_bytes ] ); // can be a Request object or an error string
    if ( is_string( $response ) ) {
        return $url;
    }

    // Page content. No content? Return the URL
    $content = $response->body;
    if ( !$content ) {
        return $url;
    }

    // look for <title>. No title found? Return the URL
    if ( preg_match( '/<title>(.*?)<\/title>/is', $content, $found ) ) {
        $title = $found[ 1 ];
        unset( $found );
    }
    if ( !$title ) {
        return $url;
    }

    // Now we have a title. We'll try to get proper utf8 from it.

    // Get charset as (and if) defined by the HTML meta tag. We should match
    // <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    // or <meta charset='utf-8'> and all possible variations: see https://gist.github.com/ozh/7951236
    if ( preg_match( '/<meta[^>]*charset\s*=["\' ]*([a-zA-Z0-9\-_]+)/is', $content, $found ) ) {
        $charset = $found[ 1 ];
        unset( $found );
    }
    else {
        // No charset found in HTML. Get charset as (and if) defined by the server response
        $_charset = current( $response->headers->getValues( 'content-type' ) );
        if ( preg_match( '/charset=(\S+)/', $_charset, $found ) ) {
            $charset = trim( $found[ 1 ], ';' );
            unset( $found );
        }
    }

    // Conversion to utf-8 if what we have is not utf8 already
    if ( strtolower( $charset ) != 'utf-8' && function_exists( 'mb_convert_encoding' ) ) {
        // We use @ to remove warnings because mb_ functions are easily bitching about illegal chars
        if ( $charset ) {
            $title = @mb_convert_encoding( $title, 'UTF-8', $charset );
        }
        else {
            $title = @mb_convert_encoding( $title, 'UTF-8' );
        }
    }

    // Remove HTML entities
    $title = html_entity_decode( $title, ENT_QUOTES, 'UTF-8' );

    // Strip out evil things
    $title = yourls_sanitize_title( $title, $url );

    return (string)yourls_apply_filter( 'get_remote_title', $title, $url );
}

/**
 * Checks if the client is a mobile device.
 *
 * @since 1.0
 * @return bool True if the client is a mobile device, false otherwise.
 */
function yourls_is_mobile_device() {
    // Strings searched
    $mobiles = [
        'android', 'blackberry', 'blazer',
        'compal', 'elaine', 'fennec', 'hiptop',
        'iemobile', 'iphone', 'ipod', 'ipad',
        'iris', 'kindle', 'opera mobi', 'opera mini',
        'palm', 'phone', 'pocket', 'psp', 'symbian',
        'treo', 'wap', 'windows ce', 'windows phone'
    ];

    // Current user-agent
    $current = strtolower( $_SERVER['HTTP_USER_AGENT'] );

    // Check and return
    $is_mobile = ( str_replace( $mobiles, '', $current ) != $current );
    return (bool)yourls_apply_filter( 'is_mobile_device', $is_mobile );
}

/**
 * Gets the request URI relative to the YOURLS installation.
 *
 * @since 1.5
 * @param string $yourls_site The YOURLS installation URL.
 * @param string $uri         The request URI.
 * @return string The request URI relative to the YOURLS installation.
 */
function yourls_get_request($yourls_site = '', $uri = '') {
    // Allow plugins to short-circuit the whole function
    $pre = yourls_apply_filter( 'shunt_get_request', false );
    if ( false !== $pre ) {
        return $pre;
    }

    yourls_do_action( 'pre_get_request', $yourls_site, $uri );

    // Default values
    if ( '' === $yourls_site ) {
        $yourls_site = yourls_get_yourls_site();
    }
    if ( '' === $uri ) {
        $uri = $_SERVER[ 'REQUEST_URI' ];
    }

    // Even though the config sample states YOURLS_SITE should be set without trailing slash...
    $yourls_site = rtrim( $yourls_site, '/' );

    // Now strip the YOURLS_SITE path part out of the requested URI, and get the request relative to YOURLS base
    // +---------------------------+-------------------------+---------------------+--------------+
    // |       if we request       | and YOURLS is hosted on | YOURLS path part is | "request" is |
    // +---------------------------+-------------------------+---------------------+--------------+
    // | http://sho.rt/abc         | http://sho.rt           | /                   | abc          |
    // | https://SHO.rt/subdir/abc | https://shor.rt/subdir/ | /subdir/            | abc          |
    // +---------------------------+-------------------------+---------------------+--------------+
    // and so on. You can find various test cases in /tests/tests/utilities/get_request.php

    // Take only the URL_PATH part of YOURLS_SITE (ie "https://sho.rt:1337/path/to/yourls" -> "/path/to/yourls")
    $yourls_site = parse_url( $yourls_site, PHP_URL_PATH ).'/';

    // Strip path part from request if exists
    $request = $uri;
    if ( substr( $uri, 0, strlen( $yourls_site ) ) == $yourls_site ) {
        $request = ltrim( substr( $uri, strlen( $yourls_site ) ), '/' );
    }

    // Unless request looks like a full URL (ie request is a simple keyword) strip query string
    if ( !preg_match( "@^[a-zA-Z]+://.+@", $request ) ) {
        $request = current( explode( '?', $request ) );
    }

    $request = yourls_sanitize_url( $request );

    return (string)yourls_apply_filter( 'get_request', $request );
}

/**
 * Fixes the `$_SERVER['REQUEST_URI']` variable for various server configurations.
 *
 * @since 1.5.1
 * @return void
 */
function yourls_fix_request_uri() {

    $default_server_values = [
        'SERVER_SOFTWARE' => '',
        'REQUEST_URI'     => '',
    ];
    $_SERVER = array_merge( $default_server_values, $_SERVER );

    // Make $_REQUEST with only $_GET and $_POST, not $_COOKIE. See #3383.
    $_REQUEST = array_merge( $_GET, $_POST );

    // Fix for IIS when running with PHP ISAPI
    if ( empty( $_SERVER[ 'REQUEST_URI' ] ) || ( php_sapi_name() != 'cgi-fcgi' && preg_match( '/^Microsoft-IIS\//', $_SERVER[ 'SERVER_SOFTWARE' ] ) ) ) {

        // IIS Mod-Rewrite
        if ( isset( $_SERVER[ 'HTTP_X_ORIGINAL_URL' ] ) ) {
            $_SERVER[ 'REQUEST_URI' ] = $_SERVER[ 'HTTP_X_ORIGINAL_URL' ];
        }
        // IIS Isapi_Rewrite
        elseif ( isset( $_SERVER[ 'HTTP_X_REWRITE_URL' ] ) ) {
            $_SERVER[ 'REQUEST_URI' ] = $_SERVER[ 'HTTP_X_REWRITE_URL' ];
        }
        else {
            // Use ORIG_PATH_INFO if there is no PATH_INFO
            if ( !isset( $_SERVER[ 'PATH_INFO' ] ) && isset( $_SERVER[ 'ORIG_PATH_INFO' ] ) ) {
                $_SERVER[ 'PATH_INFO' ] = $_SERVER[ 'ORIG_PATH_INFO' ];
            }

            // Some IIS + PHP configurations puts the script-name in the path-info (No need to append it twice)
            if ( isset( $_SERVER[ 'PATH_INFO' ] ) ) {
                if ( $_SERVER[ 'PATH_INFO' ] == $_SERVER[ 'SCRIPT_NAME' ] ) {
                    $_SERVER[ 'REQUEST_URI' ] = $_SERVER[ 'PATH_INFO' ];
                }
                else {
                    $_SERVER[ 'REQUEST_URI' ] = $_SERVER[ 'SCRIPT_NAME' ].$_SERVER[ 'PATH_INFO' ];
                }
            }

            // Append the query string if it exists and isn't null
            if ( !empty( $_SERVER[ 'QUERY_STRING' ] ) ) {
                $_SERVER[ 'REQUEST_URI' ] .= '?'.$_SERVER[ 'QUERY_STRING' ];
            }
        }
    }
}

/**
 * Checks if the site is in maintenance mode.
 *
 * @since 1.0
 * @return void
 */
function yourls_check_maintenance_mode() {
    $dot_file = YOURLS_ABSPATH . '/.maintenance' ;

    if ( !file_exists( $dot_file ) || yourls_is_upgrading() || yourls_is_installing() ) {
        return;
    }

    global $maintenance_start;
    yourls_include_file_sandbox( $dot_file );
    // If the $maintenance_start timestamp is older than 10 minutes, don't die.
    if ( ( time() - $maintenance_start ) >= 600 ) {
        return;
    }

    // Use any /user/maintenance.php file
    $file = YOURLS_USERDIR . '/maintenance.php';
    if(file_exists($file)) {
        if(yourls_include_file_sandbox( $file ) == true) {
            die();
        }
    }

    // Or use the default messages
    $title = yourls__('Service temporarily unavailable');
    $message = yourls__('Our service is currently undergoing scheduled maintenance.') . "</p>\n<p>" .
        yourls__('Things should not last very long, thank you for your patience and please excuse the inconvenience');
    yourls_die( $message, $title, 503 );
}

/**
 * Checks if a URL protocol is allowed.
 *
 * @since 1.6
 * @param string $url       The URL to check.
 * @param array  $protocols An array of allowed protocols.
 * @return bool True if the protocol is allowed, false otherwise.
 */
function yourls_is_allowed_protocol( $url, $protocols = [] ) {
    if ( empty( $protocols ) ) {
        global $yourls_allowedprotocols;
        $protocols = $yourls_allowedprotocols;
    }

    return yourls_apply_filter( 'is_allowed_protocol', in_array( yourls_get_protocol( $url ), $protocols ), $url, $protocols );
}

/**
 * Gets the protocol of a URL.
 *
 * @since 1.6
 * @param string $url The URL to check.
 * @return string The protocol of the URL.
 */
function yourls_get_protocol( $url ) {
    /*
    http://en.wikipedia.org/wiki/URI_scheme#Generic_syntax
    The scheme name consists of a sequence of characters beginning with a letter and followed by any
    combination of letters, digits, plus ("+"), period ("."), or hyphen ("-"). Although schemes are
    case-insensitive, the canonical form is lowercase and documents that specify schemes must do so
    with lowercase letters. It is followed by a colon (":").
    */
    preg_match( '!^[a-zA-Z][a-zA-Z0-9+.-]+:(//)?!', $url, $matches );
    return (string)yourls_apply_filter( 'get_protocol', isset( $matches[0] ) ? $matches[0] : '', $url );
}

/**
 * Gets the relative URL of a URL.
 *
 * @since 1.6
 * @param string $url    The URL to get the relative URL from.
 * @param bool   $strict If true, an empty string will be returned if the URL is not relative.
 * @return string The relative URL.
 */
function yourls_get_relative_url( $url, $strict = true ) {
    $url = yourls_sanitize_url( $url );

    // Remove protocols to make it easier
    $noproto_url = str_replace( 'https:', 'http:', $url );
    $noproto_site = str_replace( 'https:', 'http:', yourls_get_yourls_site() );

    // Trim URL from YOURLS root URL : if no modification made, URL wasn't relative
    $_url = str_replace( $noproto_site.'/', '', $noproto_url );
    if ( $_url == $noproto_url ) {
        $_url = ( $strict ? '' : $url );
    }
    return yourls_apply_filter( 'get_relative_url', $_url, $url );
}

/**
 * Marks a function as deprecated.
 *
 * @since 1.6
 * @param string $function    The function that was called.
 * @param string $version     The version of YOURLS that deprecated the function.
 * @param string $replacement The function that should have been called instead.
 * @return void
 */
function yourls_deprecated_function( $function, $version, $replacement = null ) {

    yourls_do_action( 'deprecated_function', $function, $replacement, $version );

    // Allow plugin to filter the output error trigger
    if ( yourls_get_debug_mode() && yourls_apply_filter( 'deprecated_function_trigger_error', true ) ) {
        if ( ! is_null( $replacement ) )
            trigger_error( sprintf( yourls__('%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.'), $function, $version, $replacement ) );
        else
            trigger_error( sprintf( yourls__('%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.'), $function, $version ) );
    }
}

/**
 * Splits a URL into its protocol, slashes, and the rest.
 *
 * @since 1.7
 * @param string $url   The URL to split.
 * @param array  $array An array of keys to use for the returned array.
 * @return array|false An array containing the protocol, slashes, and the rest of the URL, or false if the URL is invalid.
 */
function yourls_get_protocol_slashes_and_rest( $url, $array = [ 'protocol', 'slashes', 'rest' ] ) {
    $proto = yourls_get_protocol( $url );

    if ( !$proto or count( $array ) != 3 ) {
        return false;
    }

    list( $null, $rest ) = explode( $proto, $url, 2 );

    list( $proto, $slashes ) = explode( ':', $proto );

    return [
        $array[ 0 ] => $proto.':',
        $array[ 1 ] => $slashes,
        $array[ 2 ] => $rest
    ];
}

/**
 * Sets the scheme of a URL.
 *
 * @since 1.7.1
 * @param string $url    The URL to modify.
 * @param string $scheme The new scheme.
 * @return string The modified URL.
 */
function yourls_set_url_scheme( $url, $scheme = '' ) {
    if ( in_array( $scheme, [ 'http', 'https' ] ) ) {
        $url = preg_replace( '!^[a-zA-Z0-9+.-]+://!', $scheme.'://', $url );
    }
    return $url;
}

/**
 * Checks for a new version of YOURLS and displays a notice if one is available.
 *
 * @since 1.7.3
 * @return void
 */
function yourls_tell_if_new_version() {
    yourls_debug_log( 'Check for new version: '.( yourls_maybe_check_core_version() ? 'yes' : 'no' ) );
    yourls_new_core_version_notice(YOURLS_VERSION);
}

/**
 * Includes a file in a sandboxed environment.
 *
 * @since 1.9.2
 * @param string $file The file to include.
 * @return bool|string True on success, an error message on failure.
 */
function yourls_include_file_sandbox($file) {
    try {
        if (is_readable( $file )) {
            require_once $file;
            yourls_debug_log("loaded $file");
            return true;
        }
    } catch ( \Throwable $e ) {
        yourls_debug_log("could not load $file");
        return sprintf("%s (%s : %s)", $e->getMessage() , $e->getFile() , $e->getLine() );
    }
}
