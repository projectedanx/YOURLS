<?php
/**
 * View for YOURLS Stats
 */

yourls_html_head( 'infos', yourls_s( 'Statistics for %s', YOURLS_SITE.'/'.$keyword ) );
yourls_html_logo();
yourls_html_menu();
?>

<h2 id="informations"><?php echo yourls_esc_html( $title ); ?></h2>

<h3><span class="label"><?php yourls_e( 'Short URL'); ?>:</span> <img src="<?php yourls_get_yourls_favicon_url() ?>"/>
<?php if( $aggregate ) {
    $i = 0;
    foreach( $keyword_list as $k ) {
        $i++;
        if ( $i == 1 ) {
            yourls_html_link( yourls_link($k) );
        } else {
            yourls_html_link( yourls_link($k), "/$k" );
        }
        if ( $i < count( $keyword_list ) )
            echo ' + ';
    }
} else {
    yourls_html_link( yourls_link($keyword) );
    if( isset( $keyword_list ) && count( $keyword_list ) > 1 )
        echo ' <a href="'. yourls_link($keyword).'+all" title="' . yourls_esc_attr__( 'Aggregate stats for duplicate short URLs' ) . '"><img src="' . yourls_match_current_protocol( YOURLS_SITE ) . '/images/chart_bar_add.svg" border="0" /></a>';
} ?></h3>
<h3 id="longurl"><span class="label"><?php yourls_e( 'Long URL'); ?>:</span> <img class="fix_images" src="<?php echo yourls_get_favicon_url( $longurl );?>" /> <?php yourls_html_link( $longurl, yourls_trim_long_string( $longurl ), 'longurl' ); ?></h3>

<div id="tabs">
    <div class="wrap_unfloat">
    <ul id="headers" class="toggle_display stat_tab">
        <?php if( yourls_do_log_redirect() ) { ?>
        <li class="selected"><a href="#stat_tab_stats"><h2><?php yourls_e( 'Traffic statistics'); ?></h2></a></li>
        <li><a href="#stat_tab_location"><h2><?php yourls_e( 'Traffic location'); ?></h2></a></li>
        <li><a href="#stat_tab_sources"><h2><?php yourls_e( 'Traffic sources'); ?></h2></a></li>
        <?php } ?>
        <li><a href="#stat_tab_share"><h2><?php yourls_e( 'Share'); ?></h2></a></li>
    </ul>
    </div>


<?php if( yourls_do_log_redirect() ) { ?>
    <div id="stat_tab_stats" class="tab">
        <h2><?php yourls_e( 'Traffic statistics'); ?></h2>

        <?php yourls_do_action( 'pre_yourls_info_stats', $keyword ); ?>

        <?php if ( $list_of_days ) { ?>

            <?php
            $graphs = array(
                '24' => yourls__( 'Last 24 hours' ),
                '7'  => yourls__( 'Last 7 days' ),
                '30' => yourls__( 'Last 30 days' ),
                'all'=> yourls__( 'All time' ),
            );

            // Which graph to generate ?
            $do_all = $do_30 = $do_7 = $do_24 = false;
            $hits_all = array_sum( $list_of_days );
            $hits_30  = array_sum( array_slice( $list_of_days, -30 ) );
            $hits_7   = array_sum( array_slice( $list_of_days, -7 ) );
            $hits_24  = array_sum( $last_24h );
            if( $hits_all > 0 )
                $do_all = true; // graph for all days range
            if( $hits_30 > 0 && count( array_slice( $list_of_days, -30 ) ) == 30 )
                $do_30 = true; // graph for last 30 days
            if( $hits_7 > 0 && count( array_slice( $list_of_days, -7 ) ) == 7 )
                $do_7 = true; // graph for last 7 days
            if( $hits_24 > 0 )
                $do_24 = true; // graph for last 24 hours

            // Which graph to display ?
            $display_all = $display_30 = $display_7 = $display_24 = false;
            if( $do_24 ) {
                $display_24 = true;
            } elseif ( $do_7 ) {
                $display_7 = true;
            } elseif ( $do_30 ) {
                $display_30 = true;
            } elseif ( $do_all ) {
                $display_all = true;
            }
            ?>

            <table border="0" cellspacing="2">
            <tr>
                <td valign="top">
                <ul id="stats_lines" class="toggle_display stat_line">
                <?php
                if( $do_24 == true )
                    echo '<li><a href="#stat_line_24">' . yourls__( 'Last 24 hours' ) . '</a>';
                if( $do_7 == true )
                    echo '<li><a href="#stat_line_7">' . yourls__( 'Last 7 days' ) . '</a>';
                if( $do_30 == true )
                    echo '<li><a href="#stat_line_30">' . yourls__( 'Last 30 days' ) . '</a>';
                if( $do_all == true )
                    echo '<li><a href="#stat_line_all">' . yourls__( 'All time' ) . '</a>';
                ?>
                </ul>
                <?php
                // Generate, and display if applicable, each needed graph
                foreach( $graphs as $graph => $graphtitle ) {
                    if( ${'do_'.$graph} == true ) {
                        $display = ( ${'display_'.$graph} === true ? 'display:block' : 'display:none' );
                        echo "<div id='stat_line_$graph' class='stats_line line' style='$display'>";
                        echo '<h3>' . yourls_s( 'Number of hits : %s' , $graphtitle ) . '</h3>';
                        switch( $graph ) {
                            case '24':
                                yourls_stats_line( $last_24h, "stat_line_$graph" );
                                break;

                            case '7':
                            case '30':
                                $slice = array_slice( $list_of_days, intval( $graph ) * -1 );
                                yourls_stats_line( $slice, "stat_line_$graph" );
                                unset( $slice );
                                break;

                            case 'all':
                                yourls_stats_line( $list_of_days, "stat_line_$graph" );
                                break;
                        }
                        echo "</div>\n";
                    }
                } ?>

                </td>
                <td valign="top">
                <h3><?php yourls_e( 'Historical click count' ); ?></h3>
                <?php
                $timestamp = strtotime( $timestamp );
                $ago = round( (date('U') - $timestamp) / (24* 60 * 60 ) );
                if( $ago <= 1 ) {
                    $daysago = '';
                } else {
                    $daysago = ' (' . sprintf( yourls_n( 'about 1 day ago', 'about %s days ago', $ago ), $ago ) . ')';
                }
                ?>
                <p><?php echo /* //translators: eg Short URL created on March 23rd 1972 */ yourls_s( 'Short URL created on %s', yourls_date_i18n( yourls_get_datetime_format("F j, Y @ g:i a"), yourls_get_timestamp( $timestamp ) ) ) . $daysago; ?></p>
                <div class="wrap_unfloat">
                    <ul class="no_bullet toggle_display stat_line" id="historical_clicks">
                    <?php
                    foreach( $graphs as $graph => $graphtitle ) {
                        if ( ${'do_'.$graph} ) {
                            $link = "<a href='#stat_line_$graph'>$graphtitle</a>";
                        } else {
                            $link = $graphtitle;
                        }
                        $stat = '';
                        if( ${'do_'.$graph} ) {
                            switch( $graph ) {
                                case '7':
                                case '30':
                                    $stat = yourls_s( '%s per day', round( ( ${'hits_'.$graph} / intval( $graph ) ) * 100 ) / 100 );
                                    break;
                                case '24':
                                    $stat = yourls_s( '%s per hour', round( ( ${'hits_'.$graph} / 24 ) * 100 ) / 100 );
                                    break;
                                case 'all':
                                    if( $ago > 0 )
                                        $stat = yourls_s( '%s per day', round( ( ${'hits_'.$graph} / $ago ) * 100 ) / 100 );
                            }
                        }
                        $hits = sprintf( yourls_n( '%s hit', '%s hits', ${'hits_'.$graph} ), ${'hits_'.$graph} );
                        echo "<li><span class='historical_link'>$link</span> <span class='historical_count'>$hits</span> $stat</li>\n";
                    }
                    ?>
                    </ul>
                </div>

                <h3><?php yourls_e( 'Best day' ); ?></h3>
                <?php
                $best = yourls_stats_get_best_day( $list_of_days );
                $best_time['day']   = date( "d", strtotime( $best['day'] ) );
                $best_time['month'] = date( "m", strtotime( $best['day'] ) );
                $best_time['year']  = date( "Y", strtotime( $best['day'] ) );
                ?>
                <p><?php echo sprintf( /* //translators: eg. 43 hits on January 1, 1970 */ yourls_n( '<strong>%1$s</strong> hit on %2$s', '<strong>%1$s</strong> hits on %2$s', $best['max'] ), $best['max'],  yourls_date_i18n( yourls_get_date_format("F j, Y"), strtotime( $best['day'] ) ) ); ?>.
                <a href="" class='details hide-if-no-js' id="more_clicks"><?php yourls_e( 'Click for more details' ); ?></a></p>
                <ul id="details_clicks" style="display:none">
                    <?php
                    foreach( $dates as $year=>$months ) {
                        $css_year = ( $year == $best_time['year'] ? 'best_year' : '' );
                        if( count( $list_of_years ) > 1 ) {
                            $li = "<a href='' class='details' id='more_year$year'>" . yourls_s( 'Year %s', $year ) . '</a>';
                            $display = 'none';
                        } else {
                            $li = yourls_s( 'Year %s', $year );
                            $display = 'block';
                        }
                        echo "<li><span class='$css_year'>$li</span>";
                        echo "<ul style='display:$display' id='details_year$year'>";
                        foreach( $months as $month=>$days ) {
                            $css_month = ( ( $month == $best_time['month'] && ( $css_year == 'best_year' ) ) ? 'best_month' : '' );
                            $monthname = yourls_date_i18n( "F", mktime( 0, 0, 0, $month, 1 ) );
                            if( count( $list_of_months ) > 1 ) {
                                $li = "<a href='' class='details' id='more_month$year$month'>$monthname</a>";
                                $display = 'none';
                            } else {
                                $li = "$monthname";
                                $display = 'block';
                            }
                            echo "<li><span class='$css_month'>$li</span>";
                            echo "<ul style='display:$display' id='details_month$year$month'>";
                                foreach( $days as $day=>$hits ) {
                                    $class = ( $hits == $best['max'] ? 'class="bestday"' : '' );
                                    echo "<li $class>$day: " . sprintf( yourls_n( '1 hit', '%s hits', $hits ), $hits ) ."</li>\n";
                                }
                            echo "</ul>\n";
                        }
                        echo "</ul>\n";
                    }
                    ?>
                </ul>

                </td>

            </tr>
            </table>

        <?php yourls_do_action( 'post_yourls_info_stats', $keyword ); ?>

        <?php } else {
            echo '<p>' . yourls__( 'No traffic yet. Get some clicks first!' ) . '</p>';
        } ?>
    </div>


    <div id="stat_tab_location" class="tab">
        <h2><?php yourls_e( 'Traffic location' ); ?></h2>

        <?php yourls_do_action( 'pre_yourls_info_location', $keyword ); ?>

        <?php if ( $countries ) { ?>

            <table border="0" cellspacing="2">
            <tr>
                <td valign="top">
                    <h3><?php yourls_e( 'Top 5 countries' ); ?></h3>
                    <?php yourls_stats_pie( $countries, 5, '340x220', 'stat_tab_location_pie' ); ?>
                    <p><a href="" class='details hide-if-no-js' id="more_countries"><?php yourls_e( 'Click for more details' ); ?></a></p>
                    <ul id="details_countries" style="display:none" class="no_bullet">
                    <?php
                    foreach( $countries as $code=>$count ) {
                        echo "<li><img src='".yourls_geo_get_flag( $code )."' /> $code (".yourls_geo_countrycode_to_countryname( $code ) . ') : ' . sprintf( yourls_n( '1 hit', '%s hits', $count ), $count ) . "</li>\n";
                    }
                    ?>
                    </ul>

                </td>
                <td valign="top">
                    <h3><?php yourls_e( 'Overall traffic' ); ?></h3>
                    <?php yourls_stats_countries_map( $countries, 'stat_tab_location_map' ); ?>
                </td>
            </tr>
            </table>

        <?php yourls_do_action( 'post_yourls_info_location', $keyword ); ?>

        <?php } else {
            echo '<p>' . yourls__( 'No country data.' ) . '</p>';
        } ?>
    </div>


    <div id="stat_tab_sources" class="tab">
        <h2><?php yourls_e( 'Traffic sources' ); ?></h2>

        <?php yourls_do_action( 'pre_yourls_info_sources', $keyword ); ?>

        <?php if ( $referrers ) { ?>

            <table border="0" cellspacing="2">
            <tr>
                <td valign="top">
                    <h3><?php yourls_e( 'Referrer shares' ); ?></h3>
                    <?php
                    if ( $number_of_sites > 1 )
                        $referrer_sort[ yourls__( 'Others' ) ] = count( $referrers );
                    yourls_stats_pie( $referrer_sort, 5, '440x220', 'stat_tab_source_ref' );
                    unset( $referrer_sort[yourls__('Others')] );
                    ?>
                    <h3><?php yourls_e( 'Referrers' ); ?></h3>
                    <ul class="no_bullet">
                        <?php
                        $i = 0;
                        foreach( $referrer_sort as $site => $count ) {
                            $i++;
                            $favicon = yourls_get_favicon_url( $site );
                            echo "<li class='sites_list'><img src='$favicon' class='fix_images'/> $site: <strong>$count</strong> <a href='' class='details hide-if-no-js' id='more_url$i'>" . yourls__( '(details)' ) . "</a></li>\n";
                            echo "<ul id='details_url$i' style='display:none'>";
                            foreach( $referrers[$site] as $url => $count ) {
                                echo "<li>"; yourls_html_link($url); echo ": <strong>$count</strong></li>\n";
                            }
                            echo "</ul>\n";
                            unset( $referrers[$site] );
                        }
                        // Any referrer left? Group in "various"
                        if ( $referrers ) {
                            echo "<li id='sites_various'>" . yourls__( 'Various:' ) . " <strong>". count( $referrers ). "</strong> <a href='' class='details hide-if-no-js' id='more_various'>" . yourls__( '(details)' ) . "</a></li>\n";
                            echo "<ul id='details_various' style='display:none'>";
                            foreach( $referrers as $url ) {
                                echo "<li>"; yourls_html_link(key($url)); echo ": 1</li>\n";
                            }
                            echo "</ul>\n";
                        }
                        ?>

                    </ul>

                </td>

                <td valign="top">
                    <h3><?php yourls_e( 'Direct vs Referrer Traffic' ); ?></h3>
                    <?php
                    yourls_stats_pie( array( yourls__( 'Direct' ) => $direct, yourls__( 'Referrers' ) => $notdirect ), 5, '440x220', 'stat_tab_source_direct' );
                    ?>
                    <p><?php yourls_e( 'Direct traffic:' ); echo ' ' . sprintf( yourls_n( '<strong>%s</strong> hit', '<strong>%s</strong> hits', $direct ), $direct ); ?> </p>
                    <p><?php yourls_e( 'Referrer traffic:' ); echo ' ' . sprintf( yourls_n( '<strong>%s</strong> hit', '<strong>%s</strong> hits', $notdirect ), $notdirect ); ?> </p>

                </td>
            </tr>
            </table>

        <?php yourls_do_action( 'post_yourls_info_sources', $keyword ); ?>

        <?php } else {
            echo '<p>' . yourls__( 'No referrer data.' ) . '</p>';
        } ?>

    </div>

<?php } // endif do log redirect ?>


    <div id="stat_tab_share" class="tab">
        <h2><?php yourls_e( 'Share' ); ?></h2>

        <?php yourls_share_box( $longurl, yourls_link($keyword), $title, '', '<h3>' . yourls__( 'Short link' ) . '</h3>', '<h3>' . yourls__( 'Quick Share' ) . '</h3>'); ?>

    </div>

</div>


<?php yourls_html_footer(); ?>
