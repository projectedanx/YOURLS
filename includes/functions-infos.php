<?php
/**
 * YOURLS Stats Functions
 *
 * This file contains functions that are used for generating statistical charts
 * and graphs. These functions are used to display information about the usage
 * of short URLs.
 *
 * @package YOURLS
 * @since 1.0
 */

/**
 * Echos a Google Charts map of countries.
 *
 * @since 1.0
 * @param array       $countries An array of 'country_code' => 'number of visits', sorted by number of visits DESC.
 * @param string|null $id        Optional. The HTML element ID. Default null.
 * @return void
 */
function yourls_stats_countries_map($countries, $id = null) {

    yourls_do_action( 'pre_stats_countries_map' );

    // if $id is null then assign a random string
    if( $id === null )
        $id = uniqid ( 'yourls_stats_map_' );

    $data = array_merge( array( 'Country' => 'Hits' ), $countries );
    $data = yourls_google_array_to_data_table( $data );

    $options = array(
        'backgroundColor' => "white",
        'colorAxis'       => "{colors:['A8D0ED','99C4E4','8AB8DB','7BACD2','6BA1C9','5C95C0','4D89B7','3E7DAE','2E72A5','1F669C']}",
        'width'           => "550",
        'height'          => "340",
        'theme'           => 'maximized'
    );
    $options = yourls_apply_filter( 'stats_countries_map_options', $options );

    $map = yourls_google_viz_code( 'GeoChart', $data, $options, $id );

    echo yourls_apply_filter( 'stats_countries_map', $map, $countries, $options, $id );
}


/**
 * Echos a Google Charts pie chart.
 *
 * @since 1.0
 * @param array       $data  An array of 'data' => 'value', sorted by value DESC.
 * @param int         $limit Optional. The number of items to show in the pie chart. Default 10.
 * @param string      $size  Optional. The size of the chart in pixels (e.g., '340x220'). Default '340x220'.
 * @param string|null $id    Optional. The HTML element ID. Default null.
 * @return void
 */
function yourls_stats_pie($data, $limit = 10, $size = '340x220', $id = null) {

    yourls_do_action( 'pre_stats_pie' );

    // if $id is null then assign a random string
    if( $id === null )
        $id = uniqid ( 'yourls_stats_pie_' );

    // Trim array: $limit first item + the sum of all others
    if ( count( $data ) > $limit ) {
        $i= 0;
        $trim_data = array( 'Others' => 0 );
        foreach( $data as $item=>$value ) {
            $i++;
            if( $i <= $limit ) {
                $trim_data[$item] = $value;
            } else {
                $trim_data['Others'] += $value;
            }
        }
        $data = $trim_data;
    }

    // Scale items
    $_data = yourls_scale_data( $data );

    list($width, $height) = explode( 'x', $size );

    $options = array(
        'theme'  => 'maximized',
        'width'   => $width,
        'height'   => $height,
        'colors'    => "['A8D0ED','99C4E4','8AB8DB','7BACD2','6BA1C9','5C95C0','4D89B7','3E7DAE','2E72A5','1F669C']",
        'legend'     => 'none',
        'chartArea'   => '{top: "5%", height: "90%"}',
        'pieSliceText' => 'label',
    );
    $options = yourls_apply_filter( 'stats_pie_options', $options );

    $script_data = array_merge( array( 'Country' => 'Value' ), $_data );
    $script_data = yourls_google_array_to_data_table( $script_data );

    $pie = yourls_google_viz_code( 'PieChart', $script_data, $options, $id );

    echo yourls_apply_filter( 'stats_pie', $pie, $data, $limit, $size, $options, $id );
}


/**
 * Builds a list of all daily values between two dates.
 *
 * @since 1.0
 * @param array $dates An array of dates and values.
 * @return array An array containing lists of days, months, and years with their corresponding values.
 */
function yourls_build_list_of_days($dates) {
    /* Say we have an array like:
    $dates = array (
        2009 => array (
            '08' => array (
                29 => 15,
                30 => 5,
                ),
            '09' => array (
                '02' => 3,
                '03' => 5,
                '04' => 2,
                '05' => 99,
                ),
            ),
        )
    */

    if( !$dates )
        return array();

    // Get first & last years from our range. In our example: 2009 & 2009
    $first_year = key( $dates );
    $_keys      = array_keys( $dates );
    $last_year  = end( $_keys );
    reset( $dates );

    // Get first & last months from our range. In our example: 08 & 09
    $first_month = key( $dates[ $first_year ] );
    $_keys       = array_keys( $dates[ $last_year ] );
    $last_month  = end( $_keys );
    reset( $dates );

    // Get first & last days from our range. In our example: 29 & 05
    $first_day = key( $dates[ $first_year ][ $first_month ] );
    $_keys     = array_keys( $dates[ $last_year ][ $last_month ] );
    $last_day  = end( $_keys );

    unset( $_keys );

    // Extend to today
    $today = new DateTime();
    $today->setTime( 0, 0, 0 ); // Start of today
    $today_year = $today->format( 'Y' );
    $today_month = $today->format( 'm' );
    $today_day = $today->format( 'd' );

    // Now build a list of all years (2009), month (08 & 09) and days (all from 2009-08-29 to 2009-09-05)
    $list_of_years  = array();
    $list_of_months = array();
    $list_of_days   = array();
    for ( $year = $first_year; $year <= $today_year; $year++ ) {
        $_year = sprintf( '%04d', $year );
        $list_of_years[ $_year ] = $_year;
        $current_first_month = ( $year == $first_year ? $first_month : '01' );
        $current_last_month = ( $year == $today_year ? $today_month : '12' );
        for ( $month = $current_first_month; $month <= $current_last_month; $month++ ) {
            $_month = sprintf( '%02d', $month );
            $list_of_months[ $_month ] = $_month;
            $current_first_day = ( $year == $first_year && $month == $first_month ? $first_day : '01' );
            $current_last_day = ( $year == $today_year && $month == $today_month ? $today_day : yourls_days_in_month( $month, $year ) );
            for ( $day = $current_first_day; $day <= $current_last_day; $day++ ) {
                $day = sprintf( '%02d', $day );
                $key = date( 'M d, Y', mktime( 0, 0, 0, $_month, $day, $_year ) );
                $list_of_days[ $key ] = isset( $dates[$_year][$_month][$day] ) ? $dates[$_year][$_month][$day] : 0;
            }
        }
    }

    return array(
        'list_of_days'   => $list_of_days,
        'list_of_months' => $list_of_months,
        'list_of_years'  => $list_of_years,
    );
}


/**
 * Echos a Google Charts line graph.
 *
 * @since 1.0
 * @param array       $values An array of values (e.g., number of clicks).
 * @param string|null $id     Optional. The HTML element ID. Default null.
 * @return void
 */
function yourls_stats_line($values, $id = null) {

    yourls_do_action( 'pre_stats_line' );

    // if $id is null then assign a random string
    if( $id === null )
        $id = uniqid ( 'yourls_stats_line_' );

    // If we have only 1 day of data, prepend a fake day with 0 hits for a prettier graph
    if ( count( $values ) == 1 )
        array_unshift( $values, 0 );

    // Keep only a subset of values to keep graph smooth
    $values = yourls_array_granularity( $values, 30 );

    $data = array_merge( array( 'Time' => 'Hits' ), $values );
    $data = yourls_google_array_to_data_table( $data );

    $options = array(
        "legend"      => "none",
        "pointSize"   => "3",
        "theme"       => "maximized",
        "curveType"   => "function",
        "width"       => 430,
        "height"      => 220,
        "hAxis"       => "{minTextSpacing: 80, maxTextLines: 1, maxAlternation: 1}",
        "vAxis"       => "{minValue: 0, format: '#'}",
        "colors"      => "['#2a85b3']",
    );
    $options = yourls_apply_filter( 'stats_line_options', $options );

    $lineChart = yourls_google_viz_code( 'LineChart', $data, $options, $id );

    echo yourls_apply_filter( 'stats_line', $lineChart, $values, $options, $id );
}


/**
 * Returns the number of days in a month.
 *
 * @since 1.0
 * @param int $month The month (1-12).
 * @param int $year  The year.
 * @return int The number of days in the month.
 */
function yourls_days_in_month($month, $year) {
    // calculate number of days in a month
    return $month == 2 ? ( $year % 4 ? 28 : ( $year % 100 ? 29 : ( $year % 400 ? 28 : 29 ) ) ) : ( ( $month - 1 ) % 7 % 2 ? 30 : 31 );
}


/**
 * Gets the day with the highest value from a list of days.
 *
 * @since 1.0
 * @param array $list_of_days An array of 'date' => 'value'.
 * @return array An array containing the 'day' and 'max' value.
 */
function yourls_stats_get_best_day($list_of_days) {
    $max = max( $list_of_days );
    foreach( $list_of_days as $k=>$v ) {
        if ( $v == $max )
            return array( 'day' => $k, 'max' => $max );
    }
}

/**
 * Returns the domain of a URL.
 *
 * @since 1.0
 * @param string $url            The URL to parse.
 * @param bool   $include_scheme Optional. Whether to include the scheme (e.g., 'http://'). Default false.
 * @return string The domain of the URL.
 */
function yourls_get_domain($url, $include_scheme = false) {
    $parse = @parse_url( $url ); // Hiding ugly stuff coming from malformed referrer URLs

    // Get host & scheme. Fall back to path if not found.
    $host = isset( $parse['host'] ) ? $parse['host'] : '';
    $scheme = isset( $parse['scheme'] ) ? $parse['scheme'] : '';
    $path = isset( $parse['path'] ) ? $parse['path'] : '';
    if( !$host )
        $host = $path;

    if ( $include_scheme && $scheme )
        $host = $scheme.'://'.$host;

    return $host;
}


/**
 * Returns the favicon URL for a given URL.
 *
 * @since 1.0
 * @param string $url The URL to get the favicon for.
 * @return string The favicon URL.
 */
function yourls_get_favicon_url($url) {
    return yourls_match_current_protocol( '//www.google.com/s2/favicons?domain=' . yourls_get_domain( $url, false ) );
}

/**
 * Scales an array of data to a maximum of 100.
 *
 * @since 1.0
 * @param array $data The array of data to scale.
 * @return array The scaled array.
 */
function yourls_scale_data($data ) {
    $max = max( $data );
    if( $max > 100 ) {
        foreach( $data as $k=>$v ) {
            $data[$k] = intval( $v / $max * 100 );
        }
    }
    return $data;
}


/**
 * Tweaks the granularity of an array to keep a certain number of values.
 *
 * This function reduces the number of data points in an array to make graphs
 * less cluttered.
 *
 * @since 1.0
 * @param array $array        The array to process.
 * @param int   $grain        The number of values to keep. Default 100.
 * @param bool  $preserve_max Optional. Whether to preserve the maximum value. Default true.
 * @return array The array with adjusted granularity.
 */
function yourls_array_granularity($array, $grain = 100, $preserve_max = true) {
    if ( count( $array ) > $grain ) {
        $max = max( $array );
        $step = intval( count( $array ) / $grain );
        $i = 0;
        // Loop through each item and unset except every $step (optional preserve the max value)
        foreach( $array as $k=>$v ) {
            $i++;
            if ( $i % $step != 0 ) {
                if ( $preserve_max == false ) {
                    unset( $array[$k] );
                } else {
                    if ( $v < $max )
                        unset( $array[$k] );
                }
            }
        }
    }
    return $array;
}

/**
 * Transforms a data array into a Google Charts data table.
 *
 * @since 1.0
 * @param array $data The data array to transform.
 * @return string The Javascript code for the data table.
 */
function yourls_google_array_to_data_table($data){
    $str  = "var data = google.visualization.arrayToDataTable([\n";
    foreach( $data as $label => $values ){
        if( !is_array( $values ) ) {
            $values = array( $values );
        }
        $str .= "\t['$label',";
        foreach( $values as $value ){
            if( !is_numeric( $value ) && strpos( $value, '[' ) !== 0 && strpos( $value, '{' ) !== 0 ) {
                $value = "'$value'";
            }
            $str .= "$value";
        }
        $str .= "],\n";
    }
    $str = substr( $str, 0, -2 ) . "\n"; // remove the trailing comma/return, reappend the return
    $str .= "]);\n"; // wrap it up
    return $str;
}

/**
 * Returns the Javascript code to display a Google Chart.
 *
 * @since 1.0
 * @param string $graph_type The type of graph (e.g., 'PieChart', 'LineChart').
 * @param string $data       The data for the graph, formatted as a Javascript data table.
 * @param array  $options    An array of options for the graph.
 * @param string $id         The HTML element ID for the graph.
 * @return string The Javascript code for the Google Chart.
 */
function yourls_google_viz_code($graph_type, $data, $options, $id ) {
    $function_name = 'yourls_graph' . $id;
    $code  = "\n<script id=\"$function_name\" type=\"text/javascript\">\n";
    $code .= "function $function_name() { \n";

    $code .= "$data\n";

    $code .= "var options = {\n";
    foreach( $options as $field => $value ) {
        if( !is_numeric( $value ) && strpos( $value, '[' ) !== 0 && strpos( $value, '{' ) !== 0 ) {
            $value = "\"$value\"";
        }
        $code .= "\t'$field': $value,\n";
    }
    $code  = substr( $code, 0, -2 ) . "\n"; // remove the trailing comma/return, reappend the return
    $code .= "\t}\n";

    $code .= "new google.visualization.$graph_type( document.getElementById('visualization_$id') ).draw( data, options );";
    $code .= "}\n";
    $code .= "google.setOnLoadCallback( $function_name );\n";
    $code .= "</script>\n";
    $code .= "<div id=\"visualization_$id\"></div>\n";

    return $code;
}
