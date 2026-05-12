<?php
/**
 * YOURLS Stats
 *
 * This file handles the display of the statistics for a short URL. It is
 * responsible for gathering the data and displaying it in a user-friendly
 * format.
 *
 * @package YOURLS
 * @since 1.0
 */

define( 'YOURLS_INFOS', true );
require_once( dirname( __FILE__ ).'/includes/load-yourls.php' );
yourls_maybe_require_auth();

// Variables should be defined in yourls-loader.php
if ( !isset( $keyword ) ) {
    yourls_do_action( 'infos_no_keyword' );
    yourls_redirect( YOURLS_SITE, 302 );
    exit;
}

// Get basic infos for this shortened URL
$keyword = yourls_sanitize_keyword( $keyword );
$longurl = yourls_get_keyword_longurl( $keyword );
$clicks = yourls_get_keyword_clicks( $keyword );
$timestamp = yourls_get_keyword_timestamp( $keyword );
$title = yourls_get_keyword_title( $keyword );

// Update title if it hasn't been stored yet
if( $title == '' ) {
    $title = yourls_get_remote_title( $longurl );
    yourls_edit_link_title( $keyword, $title );
}

if ( $longurl === false ) {
    yourls_do_action( 'infos_keyword_not_found' );
    yourls_redirect( YOURLS_SITE, 302 );
    exit;
}

yourls_do_action( 'pre_yourls_infos', $keyword );


if( yourls_do_log_redirect() ) {

    $table = YOURLS_DB_TABLE_LOG;
    $referrers = array();
    $direct = $notdirect = 0;
    $countries = array();
    $dates = array();
    $list_of_days = array();
    $list_of_months = array();
    $list_of_years = array();
    $last_24h = array();

    if( yourls_allow_duplicate_longurls() )
        $keyword_list = yourls_get_longurl_keywords( $longurl );

    $offset = (int)yourls_get_time_offset();

    // Define keyword query range : either a single keyword or a list of keywords
    if( isset($aggregate) && $aggregate ) {
        $keyword_range = 'IN ( :list )';
        $keyword_binds = array('list' => $keyword_list, 'offset' => $offset);
    } else {
        $aggregate = false;
        $keyword_range = '= :keyword';
        $keyword_binds = array('keyword' => $keyword, 'offset' => $offset);
    }


    // *** Referrers ***
    $sql = "SELECT `referrer`, COUNT(*) AS `count` FROM `$table` WHERE `shorturl` $keyword_range GROUP BY `referrer`;";
    $sql = yourls_apply_filter('stat_query_referrer', $sql);
    $rows = $ydb->fetchObjects($sql, $keyword_binds);

    // Loop through all results and build list of referrers, countries and hits per day
    foreach( (array)$rows as $row ) {
        if ( $row->referrer == 'direct' ) {
            $direct = $row->count;
            continue;
        }

        $host = yourls_get_domain( $row->referrer );
        if( !isset( $referrers[$host] ) )
            $referrers[$host] = array( );
        if( !isset( $referrers[$host][$row->referrer] ) ) {
            $referrers[$host][$row->referrer] = $row->count;
            $notdirect += $row->count;
        } else {
            $referrers[$host][$row->referrer] += $row->count;
            $notdirect += $row->count;
        }
    }

    // Sort referrers. $referrer_sort is a array of most frequent domains
    arsort( $referrers );
    $referrer_sort = array();
    $number_of_sites = count( array_keys( $referrers ) );
    foreach( $referrers as $site => $urls ) {
        if( count($urls) > 1 || $number_of_sites == 1 )
            $referrer_sort[$site] = array_sum( $urls );
    }
    arsort($referrer_sort);


    // *** Countries ***
    $sql = "SELECT `country_code`, COUNT(*) AS `count` FROM `$table` WHERE `shorturl` $keyword_range GROUP BY `country_code`;";
    $sql = yourls_apply_filter('stat_query_country', $sql);
    $rows = $ydb->fetchObjects($sql, $keyword_binds);

    // Loop through all results and build list of countries and hits
    foreach( (array)$rows as $row ) {
        if ("$row->country_code")
            $countries["$row->country_code"] = $row->count;
    }

    // Sort countries, most frequent first
    if ( $countries )
        arsort( $countries );


    // *** Dates : array of $dates[$year][$month][$day] = number of clicks ***
    $sql = "SELECT
        DATE_FORMAT(`click_time`, '%Y') AS `year`,
        DATE_FORMAT(`click_time`, '%m') AS `month`,
        DATE_FORMAT(`click_time`, '%d') AS `day`,
        COUNT(*) AS `count`
    FROM `$table`
    WHERE `shorturl` $keyword_range
    GROUP BY `year`, `month`, `day`;";
    $sql = yourls_apply_filter('stat_query_dates', $sql);
    $rows = $ydb->fetchObjects($sql, $keyword_binds);

    // Loop through all results and fill blanks
    foreach( (array)$rows as $row ) {
        if( !isset( $dates[$row->year] ) )
            $dates[$row->year] = array();
        if( !isset( $dates[$row->year][$row->month] ) )
            $dates[$row->year][$row->month] = array();
        if( !isset( $dates[$row->year][$row->month][$row->day] ) )
            $dates[$row->year][$row->month][$row->day] = $row->count;
        else
            $dates[$row->year][$row->month][$row->day] += $row->count;
    }

    // Sort dates, chronologically from [2007][12][24] to [2009][02][19]
    ksort( $dates );
    foreach( $dates as $year=>$months ) {
        ksort( $dates[$year] );
        foreach( $months as $month=>$day ) {
            ksort( $dates[$year][$month] );
        }
    }

    // Get $list_of_days, $list_of_months, $list_of_years
    reset( $dates );
    if( $dates ) {
        $_lists = yourls_build_list_of_days( $dates );
        $list_of_days   = $_lists['list_of_days'];
        $list_of_months = $_lists['list_of_months'];
        $list_of_years  = $_lists['list_of_years'];
        unset($_lists);
    }

    // *** Last 24 hours : array of $last_24h[ $hour ] = number of click ***
    $sql = "SELECT
        DATE_FORMAT(DATE_ADD(`click_time`, INTERVAL :offset HOUR), '%H %p') AS `time`,
        COUNT(*) AS `count`
    FROM `$table`
    WHERE `shorturl` $keyword_range AND DATE_ADD(`click_time`, INTERVAL :offset HOUR) > (DATE_ADD(CURRENT_TIMESTAMP, INTERVAL :offset HOUR) - INTERVAL 1 DAY)
    GROUP BY `time`;";
    $sql = yourls_apply_filter('stat_query_last24h', $sql);
    $rows = $ydb->fetchObjects($sql, $keyword_binds);

    $_last_24h = array_column((array)$rows, 'count', 'time');

    $now          = time();
    $current_hour = (int)date('G', $now + ($offset * 3600));
    for ($i = 23; $i >= 0; $i--) {
        $hour = ($current_hour - $i + 24) % 24;
        $h    = sprintf('%02d %s', $hour, $hour < 12 ? 'AM' : 'PM');
        // If the $last_24h doesn't have all the hours, insert missing hours with value 0
        $last_24h[ $h ] = $_last_24h[ $h ] ?? 0;
    }
    unset( $_last_24h );

    // *** Queries all done, phew ***

    // Filter all this junk if applicable. Be warned, some are possibly huge datasets.
    $referrers      = yourls_apply_filter( 'pre_yourls_info_referrers', $referrers );
    $referrer_sort  = yourls_apply_filter( 'pre_yourls_info_referrer_sort', $referrer_sort );
    $direct         = yourls_apply_filter( 'pre_yourls_info_direct', $direct );
    $notdirect      = yourls_apply_filter( 'pre_yourls_info_notdirect', $notdirect );
    $dates          = yourls_apply_filter( 'pre_yourls_info_dates', $dates );
    $list_of_days   = yourls_apply_filter( 'pre_yourls_info_list_of_days', $list_of_days );
    $list_of_months = yourls_apply_filter( 'pre_yourls_info_list_of_months', $list_of_months );
    $list_of_years  = yourls_apply_filter( 'pre_yourls_info_list_of_years', $list_of_years );
    $last_24h       = yourls_apply_filter( 'pre_yourls_info_last_24h', $last_24h );
    $countries      = yourls_apply_filter( 'pre_yourls_info_countries', $countries );

}

require_once dirname( __FILE__ ) . '/includes/views/yourls-infos.php';
