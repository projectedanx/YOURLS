<?php
/**
 * YOURLS HTML Functions
 *
 * This file contains functions that are used for generating HTML output. These
 * functions are used to display the header, footer, and other HTML elements.
 *
 * @package YOURLS
 * @since 1.0
 */

/**
 * Displays the YOURLS logo.
 *
 * @since 1.0
 * @return void
 */
function yourls_html_logo() {
    yourls_do_action( 'pre_html_logo' );
    ?>
    <header role="banner">
    <h1>
        <a href="<?php echo yourls_admin_url( 'index.php' ) ?>" title="YOURLS"><span>YOURLS</span>: <span>Y</span>our <span>O</span>wn <span>URL</span> <span>S</span>hortener<br/>
        <img src="<?php yourls_site_url(); ?>/images/yourls-logo.svg?v=<?php echo YOURLS_VERSION; ?>" id="yourls-logo" alt="YOURLS" title="YOURLS" /></a>
    </h1>
    </header>
    <?php
    yourls_do_action( 'html_logo' );
}

/**
 * Displays the HTML head and opening body tag.
 *
 * @since 1.0
 * @param string $context Optional. The context of the page (e.g., 'stats', 'index', 'infos'). Default 'index'.
 * @param string $title   Optional. The HTML title of the page.
 * @return void
 */
function yourls_html_head( $context = 'index', $title = '' ) {

    yourls_do_action( 'pre_html_head', $context, $title );

    // All components to false, except when specified true
    $share = $insert = $tablesorter = $tabs = $cal = $charts = false;

    // Load components as needed
    switch ( $context ) {
        case 'infos':
            $share = $tabs = $charts = true;
            break;

        case 'bookmark':
            $share = $insert = $tablesorter = true;
            break;

        case 'index':
            $insert = $tablesorter = $cal = $share = true;
            break;

        case 'plugins':
        case 'tools':
            $tablesorter = true;
            break;

        case 'login':
            $_title_page = 'Login';
            break;

        case 'install':
        case 'new':
        case 'upgrade':
            break;
    }

    // Force no cache for all admin pages
    if( yourls_is_admin() && !headers_sent() ) {
        yourls_no_cache_headers();
        yourls_no_frame_header();
        yourls_content_type_header( yourls_apply_filter( 'html_head_content-type', 'text/html' ) );
        yourls_do_action( 'admin_headers', $context, $title );
    }

    // Store page context
    yourls_set_html_context($context);

    // Body class
    $bodyclass = yourls_apply_filter( 'bodyclass', '' );
    $bodyclass .= ( yourls_is_mobile_device() ? 'mobile' : 'desktop' );

    // Page title
    $_title = 'YOURLS &mdash; Your Own URL Shortener | ' . yourls_link();
    $_title = empty($_title_page) ? $_title : $_title_page . ' &mdash; ' . $_title;
    $title = $title ? $title . " &laquo; " . $_title : $_title;
    $title = yourls_apply_filter( 'html_title', $title, $context );

    ?>
<!DOCTYPE html>
<html <?php yourls_html_language_attributes(); ?>>
<head>
    <title><?php echo $title ?></title>
    <meta http-equiv="Content-Type" content="<?php echo yourls_apply_filter( 'html_head_meta_content-type', 'text/html; charset=utf-8' ); ?>" />
    <meta name="generator" content="YOURLS <?php echo YOURLS_VERSION ?>" />
    <meta name="description" content="YOURLS &raquo; Your Own URL Shortener' | <?php yourls_site_url(); ?>" />
    <?php yourls_do_action('html_head_meta', $context); ?>
    <?php yourls_html_favicon(); ?>
    <script src="<?php yourls_site_url(); ?>/js/jquery-3.5.1.min.js?v=<?php echo YOURLS_VERSION; ?>" type="text/javascript"></script>
    <script src="<?php yourls_site_url(); ?>/js/common.js?v=<?php echo YOURLS_VERSION; ?>" type="text/javascript"></script>
    <script src="<?php yourls_site_url(); ?>/js/jquery.notifybar.js?v=<?php echo YOURLS_VERSION; ?>" type="text/javascript"></script>
    <link rel="stylesheet" href="<?php yourls_site_url(); ?>/css/style.css?v=<?php echo YOURLS_VERSION; ?>" type="text/css" media="screen" />
    <?php if ( $tabs ) { ?>
        <link rel="stylesheet" href="<?php yourls_site_url(); ?>/css/infos.css?v=<?php echo YOURLS_VERSION; ?>" type="text/css" media="screen" />
        <script src="<?php yourls_site_url(); ?>/js/infos.js?v=<?php echo YOURLS_VERSION; ?>" type="text/javascript"></script>
    <?php } ?>
    <?php if ( $tablesorter ) { ?>
        <link rel="stylesheet" href="<?php yourls_site_url(); ?>/css/tablesorter.css?v=<?php echo YOURLS_VERSION; ?>" type="text/css" media="screen" />
        <script src="<?php yourls_site_url(); ?>/js/jquery-3.tablesorter.min.js?v=<?php echo YOURLS_VERSION; ?>" type="text/javascript"></script>
        <script src="<?php yourls_site_url(); ?>/js/tablesorte.js?v=<?php echo YOURLS_VERSION; ?>" type="text/javascript"></script>
    <?php } ?>
    <?php if ( $insert ) { ?>
        <script src="<?php yourls_site_url(); ?>/js/insert.js?v=<?php echo YOURLS_VERSION; ?>" type="text/javascript"></script>
    <?php } ?>
    <?php if ( $share ) { ?>
        <link rel="stylesheet" href="<?php yourls_site_url(); ?>/css/share.css?v=<?php echo YOURLS_VERSION; ?>" type="text/css" media="screen" />
        <script src="<?php yourls_site_url(); ?>/js/share.js?v=<?php echo YOURLS_VERSION; ?>" type="text/javascript"></script>
        <script src="<?php yourls_site_url(); ?>/js/clipboard.min.js?v=<?php echo YOURLS_VERSION; ?>" type="text/javascript"></script>
    <?php } ?>
    <?php if ( $cal ) { ?>
        <link rel="stylesheet" href="<?php yourls_site_url(); ?>/css/cal.css?v=<?php echo YOURLS_VERSION; ?>" type="text/css" media="screen" />
        <?php yourls_l10n_calendar_strings(); ?>
        <script src="<?php yourls_site_url(); ?>/js/jquery.cal.js?v=<?php echo YOURLS_VERSION; ?>" type="text/javascript"></script>
    <?php } ?>
    <?php if ( $charts ) { ?>
            <script type="text/javascript" src="https://www.google.com/jsapi"></script>
            <script type="text/javascript">
                     google.load('visualization', '1.0', {'packages':['corechart', 'geochart']});
            </script>
    <?php } ?>
    <script type="text/javascript">
    //<![CDATA[
        var ajaxurl  = '<?php echo yourls_admin_url( 'admin-ajax.php' ); ?>';
    //]]>
    </script>
    <?php yourls_do_action( 'html_head', $context ); ?>
</head>
<body class="<?php echo $context; ?> <?php echo $bodyclass; ?>">
<div id="wrap">
    <?php
}

/**
 * Displays the HTML footer.
 *
 * This function displays the HTML footer, including the closing body and html tags.
 * It also displays the number of SQL queries if debug mode is enabled.
 *
 * @since 1.0
 * @param bool $can_query Optional. If set to false, will not try to send another query to DB server. Default true.
 * @return void
 */
function yourls_html_footer($can_query = true) {
    if($can_query & yourls_get_debug_mode()) {
        $num_queries = yourls_get_num_queries();
        $num_queries = ' &ndash; '. sprintf( yourls_n( '1 query', '%s queries', $num_queries ), $num_queries );
    } else {
        $num_queries = '';
    }

    ?>
    </div><?php // wrap ?>
    <footer id="footer" role="contentinfo"><p>
        <?php
        $footer  = yourls_s( 'Powered by %s', '<a href="http://yourls.org/" title="YOURLS">YOURLS</a> v ' . YOURLS_VERSION );
        $footer .= $num_queries;
        echo yourls_apply_filter( 'html_footer_text', $footer );
        ?>
    </p></footer>
    <?php if( yourls_get_debug_mode() ) {
        echo '<div style="text-align:left"><pre>';
        echo join( "\n", yourls_get_debug_log() );
        echo '</pre></div>';
    } ?>
    <?php yourls_do_action( 'html_footer', yourls_get_html_context() ); ?>
    </body>
    </html>
    <?php
}

/**
 * Displays the "Add new URL" box.
 *
 * @since 1.0
 * @param string $url     Optional. URL to prefill the input with. Default ''.
 * @param string $keyword Optional. Keyword to prefill the input with. Default ''.
 * @return void
 */
function yourls_html_addnew( $url = '', $keyword = '' ) {
    $pre = yourls_apply_filter( 'shunt_html_addnew', false, $url, $keyword );
    if ( false !== $pre ) {
        return $pre;
    }
    ?>
    <main role="main">
    <div id="new_url">
        <div>
            <form id="new_url_form" action="" method="get">
                <div>
                    <label for="add-url"><strong><?php yourls_e( 'Enter the URL' ); ?></strong></label>:
                    <input type="text" id="add-url" name="url" value="<?php echo $url; ?>" class="text" size="80" placeholder="https://" />
                    <label for="add-keyword"><?php yourls_e( 'Optional '); ?> : <strong><?php yourls_e('Custom short URL'); ?></strong></label>:
                    <input type="text" id="add-keyword" name="keyword" value="<?php echo $keyword; ?>" class="text" size="8" />
                    <?php yourls_nonce_field( 'add_url', 'nonce-add' ); ?>
                    <input type="button" id="add-button" name="add-button" value="<?php yourls_e( 'Shorten The URL' ); ?>" class="button" onclick="add_link();" />
                </div>
            </form>
            <div id="feedback" style="display:none"></div>
        </div>
        <?php yourls_do_action( 'html_addnew' ); ?>
    </div>
    <?php
}

/**
 * Displays the main table's footer.
 *
 * This function displays the footer of the main table in the admin interface,
 * which includes filter and pagination controls.
 *
 * @since 1.0
 * @param array $params {
 *     Array of all required parameters.
 *
 *     @type string $search       The search query.
 *     @type string $search_text  The text being searched for.
 *     @type string $search_in    The field to search in (e.g., 'all', 'keyword', 'url').
 *     @type string $sort_by      The field to sort by (e.g., 'keyword', 'timestamp', 'clicks').
 *     @type string $sort_order   The sort order ('asc' or 'desc').
 *     @type int    $page         The current page number.
 *     @type int    $perpage      The number of items per page.
 *     @type string $click_filter Filter by number of clicks ('more' or 'less').
 *     @type int    $click_limit  The click limit for the filter.
 *     @type int    $total_pages  The total number of pages.
 *     @type string $date_filter  Filter by date ('before', 'after', 'between').
 *     @type string $date_first   The first date for the filter.
 *     @type string $date_second  The second date for the filter.
 * }
 * @return void
 */
function yourls_html_tfooter( $params = array() ) {
    // Manually extract all parameters from the array. We prefer doing it this way, over using extract(),
    // to make things clearer and more explicit about what var is used.
    $search       = $params['search'];
    $search_text  = $params['search_text'];
    $search_in    = $params['search_in'];
    $sort_by      = $params['sort_by'];
    $sort_order   = $params['sort_order'];
    $page         = $params['page'];
    $perpage      = $params['perpage'];
    $click_filter = $params['click_filter'];
    $click_limit  = $params['click_limit'];
    $total_pages  = $params['total_pages'];
    $date_filter  = $params['date_filter'];
    $date_first   = $params['date_first'];
    $date_second  = $params['date_second'];

    ?>
    <tfoot>
        <tr>
            <th colspan="6">
            <div id="filter_form">
                <form action="" method="get">
                    <div id="filter_options">
                        <?php

                        // First search control: text to search
                        $_input = '<input aria-label="' .yourls__( 'Search for' ). '" type="text" name="search" class="text" size="12" value="' . yourls_esc_attr( $search_text ) . '" />';
                        $_options = array(
                            'all'     => yourls__( 'All fields' ),
                            'keyword' => yourls__( 'Short URL' ),
                            'url'     => yourls__( 'URL' ),
                            'title'   => yourls__( 'Title' ),
                            'ip'      => yourls__( 'IP' ),
                        );
                        $_select = yourls_html_select( 'search_in', $_options, $search_in, false, yourls__( 'Search in' ) );
                        /* //translators: "Search for <input field with text to search> in <select dropdown with URL, title...>" */
                        yourls_se( 'Search for %1$s in %2$s', $_input , $_select );
                        echo "&ndash;\n";

                        // Second search control: order by
                        $_options = array(
                            'keyword'      => yourls__( 'Short URL' ),
                            'url'          => yourls__( 'URL' ),
                            'title'        => yourls__( 'Title' ),
                            'timestamp'    => yourls__( 'Date' ),
                            'ip'           => yourls__( 'IP' ),
                            'clicks'       => yourls__( 'Clicks' ),
                        );
                        $_select = yourls_html_select( 'sort_by', $_options, $sort_by, false,  yourls__( 'Sort by' ) );
                        $sort_order = isset( $sort_order ) ? $sort_order : 'desc' ;
                        $_options = array(
                            'asc'  => yourls__( 'Ascending' ),
                            'desc' => yourls__( 'Descending' ),
                        );
                        $_select2 = yourls_html_select( 'sort_order', $_options, $sort_order, false,  yourls__( 'Sort order' ) );
                        /* //translators: "Order by <criteria dropdown (date, clicks...)> in <order dropdown (Descending or Ascending)>" */
                        yourls_se( 'Order by %1$s %2$s', $_select , $_select2 );
                        echo "&ndash;\n";

                        // Third search control: Show XX rows
                        /* //translators: "Show <text field> rows" */
                        $_input = '<input aria-label="' .yourls__( 'Number of rows to show' ). '" type="text" name="perpage" class="text" size="2" value="' . $perpage . '" />';
                        yourls_se( 'Show %s rows',  $_input );
                        echo "<br/>\n";

                        // Fourth search control: Show links with more than XX clicks
                        $_options = array(
                            'more' => yourls__( 'more' ),
                            'less' => yourls__( 'less' ),
                        );
                        $_select = yourls_html_select( 'click_filter', $_options, $click_filter, false, yourls__( 'Show links with' ) );
                        $_input  = '<input aria-label="' .yourls__( 'Number of clicks' ). '" type="text" name="click_limit" class="text" size="4" value="' . $click_limit . '" /> ';
                        /* //translators: "Show links with <more/less> than <text field> clicks" */
                        yourls_se( 'Show links with %1$s than %2$s clicks', $_select, $_input );
                        echo "<br/>\n";

                        // Fifth search control: Show links created before/after/between ...
                        $_options = array(
                            'before'  => yourls__('before'),
                            'after'   => yourls__('after'),
                            'between' => yourls__('between'),
                        );
                        $_select = yourls_html_select( 'date_filter', $_options, $date_filter, false, yourls__('Show links created') );
                        $_input  = '<input aria-label="' .yourls__('Select a date') . '" type="text" name="date_first" id="date_first" class="text" size="12" value="' . $date_first . '" />';
                        $_and    = '<span id="date_and"' . ( $date_filter === 'between' ? ' style="display:inline"' : '' ) . '> &amp; </span>';
                        $_input2 = '<input aria-label="' .yourls__('Select an end date') . '" type="text" name="date_second" id="date_second" class="text" size="12" value="' . $date_second . '"' . ( $date_filter === 'between' ? ' style="display:inline"' : '' ) . '/>';
                        /* //translators: "Show links created <before/after/between> <date input> <"and" if applicable> <date input if applicable>" */
                        yourls_se( 'Show links created %1$s %2$s %3$s %4$s', $_select, $_input, $_and, $_input2 );
                        ?>

                        <div id="filter_buttons">
                            <input type="submit" id="submit-sort" value="<?php yourls_e('Search'); ?>" class="button primary" />
                            &nbsp;
                            <input type="button" id="submit-clear-filter" value="<?php yourls_e('Clear'); ?>" class="button" onclick="window.parent.location.href = 'index.php'" />
                        </div>

                    </div>
                </form>
            </div>

            <?php
            // Remove empty keys from the $params array so it doesn't clutter the pagination links
            $params = array_filter( $params, function($val){ return $val !== '';} ); // remove keys with empty values

            if( isset( $search_text ) ) {
                $params['search'] = $search_text;
                unset( $params['search_text'] );
            }
            ?>

            <div id="pagination">
                <span class="navigation">
                <?php if( $total_pages > 1 ) { ?>
                    <span class="nav_total"><?php echo sprintf( yourls_n( '1 page', '%s pages', $total_pages ), $total_pages ); ?></span>
                    <?php
                    $base_page = yourls_admin_url( 'index.php' );
                    // Pagination offsets: min( max ( zomg! ) );
                    $p_start = max(  min( $total_pages - 4, $page - 2 ), 1 );
                    $p_end = min( max( 5, $page + 2 ), $total_pages );
                    if( $p_start >= 2 ) {
                        $link = yourls_add_query_arg( array_merge( $params, array( 'page' => 1 ) ), $base_page );
                        echo '<span class="nav_link nav_first"><a href="' . $link . '" title="' . yourls_esc_attr__('Go to First Page') . '">' . yourls__( '&laquo; First' ) . '</a></span>';
                        echo '<span class="nav_link nav_prev"></span>';
                    }
                    for( $i = $p_start ; $i <= $p_end; $i++ ) {
                        if( $i == $page ) {
                            echo "<span class='nav_link nav_current'>$i</span>";
                        } else {
                            $link = yourls_add_query_arg( array_merge( $params, array( 'page' => $i ) ), $base_page );
                            echo '<span class="nav_link nav_goto"><a href="' . $link . '" title="' . sprintf( yourls_esc_attr( 'Page %s' ), $i ) .'">'.$i.'</a></span>';
                        }
                    }
                    if( ( $p_end ) < $total_pages ) {
                        $link = yourls_add_query_arg( array_merge( $params, array( 'page' => $total_pages ) ), $base_page );
                        echo '<span class="nav_link nav_next"></span>';
                        echo '<span class="nav_link nav_last"><a href="' . $link . '" title="' . yourls_esc_attr__('Go to Last Page') . '">' . yourls__( 'Last &raquo;' ) . '</a></span>';
                    }
                    ?>
                <?php } ?>
                </span>
            </div>
            </th>
        </tr>
        <?php yourls_do_action( 'html_tfooter' ); ?>
    </tfoot>
    <?php
}

/**
 * Returns or displays a select dropdown field.
 *
 * @since 1.6
 * @param string  $name      The HTML 'name' and 'id' attribute for the select element.
 * @param array   $options   An associative array of options, where the key is the 'value' and the value is the text displayed.
 * @param string  $selected  Optional. The 'value' from the $options array that should be selected. Default ''.
 * @param bool    $display   Optional. False (default) to return the HTML, true to echo it. Default false.
 * @param string  $label     Optional. The ARIA label for the element. Default ''.
 * @return string The HTML content of the select element.
 */
function yourls_html_select( $name, $options, $selected = '', $display = false, $label = '' ) {
    // Allow plugins to filter the options -- see #3262
    $options = yourls_apply_filter( 'html_select_options', $options, $name, $selected, $display, $label );
    $html = "<select aria-label='$label' name='$name' id='$name' size='1'>\n";
    foreach( $options as $value => $text ) {
        $html .= "<option value='$value' ";
        $html .= $selected == $value ? ' selected="selected"' : '';
        $html .= ">$text</option>\n";
    }
    $html .= "</select>\n";
    $html  = yourls_apply_filter( 'html_select', $html, $name, $options, $selected, $display );
    if( $display )
        echo $html;
    return $html;
}


/**
 * Displays the Quick Share box.
 *
 * @since 1.0
 * @param string $longurl          The long URL.
 * @param string $shorturl         The short URL.
 * @param string $title            Optional. The title of the page. Default ''.
 * @param string $text             Optional. Text to display in the share box. Default ''.
 * @param string $shortlink_title  Optional. Replacement for 'Your short link'. Default ''.
 * @param string $share_title      Optional. Replacement for 'Quick Share'. Default ''.
 * @param bool   $hidden           Optional. True to hide the box by default. Default false.
 * @return void
 */
function yourls_share_box( $longurl, $shorturl, $title = '', $text='', $shortlink_title = '', $share_title = '', $hidden = false ) {
    if ( $shortlink_title == '' )
        $shortlink_title = '<h2>' . yourls__( 'Your short link' ) . '</h2>';
    if ( $share_title == '' )
        $share_title = '<h2>' . yourls__( 'Quick Share' ) . '</h2>';

    // Allow plugins to short-circuit the whole function
    $pre = yourls_apply_filter( 'shunt_share_box', false );
    if ( false !== $pre )
        return $pre;

    // Make sure IDN domains are in their UTF8 form
    $shorturl = yourls_normalize_uri($shorturl);

    $text   = ( $text ? '"'.$text.'" ' : '' );
    $title  = ( $title ? "$title " : '' );
    $share  = yourls_esc_textarea( $title.$text.$shorturl );
    $count  = 280 - strlen( $share );
    $hidden = ( $hidden ? 'style="display:none;"' : '' );

    // Allow plugins to filter all data
    $data = compact( 'longurl', 'shorturl', 'title', 'text', 'shortlink_title', 'share_title', 'share', 'count', 'hidden' );
    $data = yourls_apply_filter( 'share_box_data', $data );
    extract( $data );

    $_share = rawurlencode( $share );
    $_url   = rawurlencode( $shorturl );
    ?>

    <div id="shareboxes" <?php echo $hidden; ?>>

        <?php yourls_do_action( 'shareboxes_before', $longurl, $shorturl, $title, $text ); ?>

        <div id="copybox" class="share">
        <?php echo $shortlink_title; ?>
            <p><input id="copylink" class="text" size="32" value="<?php echo yourls_esc_url( $shorturl ); ?>" /></p>
            <p><small><?php yourls_e( 'Long link' ); ?>: <a id="origlink" href="<?php echo yourls_esc_url( $longurl ); ?>"><?php echo yourls_esc_url( $longurl ); ?></a></small>
            <?php if( yourls_do_log_redirect() ) { ?>
            <br/><small><?php yourls_e( 'Stats' ); ?>: <a id="statlink" href="<?php echo yourls_esc_url( $shorturl ); ?>+"><?php echo yourls_esc_url( $shorturl ); ?>+</a></small>
            <input type="hidden" id="titlelink" value="<?php echo yourls_esc_attr( $title ); ?>" />
            <?php } ?>
            </p>
        </div>

        <?php yourls_do_action( 'shareboxes_middle', $longurl, $shorturl, $title, $text ); ?>

        <div id="sharebox" class="share">
            <?php echo $share_title; ?>
            <div id="tweet">
                <span id="charcount" class="hide-if-no-js"><?php echo $count; ?></span>
                <textarea id="tweet_body"><?php echo $share; ?></textarea>
            </div>
            <p id="share_links"><?php yourls_e( 'Share with' ); ?>
                <a id="share_tw" href="https://twitter.com/intent/tweet?text=<?php echo $_share; ?>" title="<?php yourls_e( 'Tweet this!' ); ?>" onclick="share('tw');return false">Twitter</a>
                <a id="share_fb" href="https://www.facebook.com/share.php?u=<?php echo $_url; ?>" title="<?php yourls_e( 'Share on Facebook' ); ?>" onclick="share('fb');return false;">Facebook</a>
                <?php
                yourls_do_action( 'share_links', $longurl, $shorturl, $title, $text );
                // Note: on the main admin page, there are no parameters passed to the sharebox when it's drawn.
                ?>
            </p>
        </div>

        <?php yourls_do_action( 'shareboxes_after', $longurl, $shorturl, $title, $text ); ?>

    </div>

    <?php
}

/**
 * Kills YOURLS execution and displays an HTML page with an error message.
 *
 * This function is used to stop YOURLS execution and display a custom error page.
 * It is a YOURLS-flavored version of `wp_die()`.
 *
 * @since 1.0
 * @see https://www.youtube.com/watch?v=zSiKETBjARk
 * @param string $message     Optional. The error message. Default ''.
 * @param string $title       Optional. The page title. Default ''.
 * @param int    $header_code Optional. The HTTP status code to send. Default 200.
 * @return void
 */
function yourls_die( $message = '', $title = '', $header_code = 200 ) {
    yourls_do_action( 'pre_yourls_die', $message, $title, $header_code );

    yourls_status_header( $header_code );

    if( !yourls_did_action( 'html_head' ) ) {
        yourls_html_head();
        yourls_html_logo();
    }
    echo yourls_apply_filter( 'die_title', "<h2>$title</h2>" );
    echo yourls_apply_filter( 'die_message', "<p>$message</p>" );
    // Hook into 'yourls_die' to add more elements or messages to that page
    yourls_do_action( 'yourls_die' );
    if( !yourls_did_action( 'html_footer' ) ) {
        yourls_html_footer(false);
    }

    // die with a value in case we're running tests, so PHPUnit doesn't exit with 0 as if success
    die(1);
}

/**
 * Returns an "Edit" row for the main table.
 *
 * This function generates the HTML for the "Edit" row that appears when a user
 * clicks the "Edit" button for a particular short URL.
 *
 * @since 1.0
 * @param string $keyword The keyword to edit.
 * @param string $id      The ID of the row.
 * @return string The HTML of the edit row.
 */
function yourls_table_edit_row( $keyword, $id ) {
    $keyword = yourls_sanitize_keyword($keyword);
    $url = yourls_get_keyword_longurl( $keyword );
    $title = htmlspecialchars( yourls_get_keyword_title( $keyword ) );
    $safe_url = yourls_esc_attr( $url );
    $safe_title = yourls_esc_attr( $title );
    $safe_keyword = yourls_esc_attr( $keyword );

    // Make strings sprintf() safe: '%' -> '%%'
    $safe_url = str_replace( '%', '%%', $safe_url );
    $safe_title = str_replace( '%', '%%', $safe_title );

    $www = yourls_link();

    $nonce = yourls_create_nonce( 'edit-save_'.$id );

    if( $url ) {
        $return = <<<RETURN
<tr id="edit-$id" class="edit-row"><td colspan="5" class="edit-row"><strong>%s</strong>:<input type="text" id="edit-url-$id" name="edit-url-$id" value="$safe_url" class="text" size="70" /><br/><strong>%s</strong>: $www<input type="text" id="edit-keyword-$id" name="edit-keyword-$id" value="$safe_keyword" class="text" size="10" /><br/><strong>%s</strong>: <input type="text" id="edit-title-$id" name="edit-title-$id" value="$safe_title" class="text" size="60" /></td><td colspan="1"><input type="button" id="edit-submit-$id" name="edit-submit-$id" value="%s" title="%s" class="button" onclick="edit_link_save('$id');" />&nbsp;<input type="button" id="edit-close-$id" name="edit-close-$id" value="%s" title="%s" class="button" onclick="edit_link_hide('$id');" /><input type="hidden" id="old_keyword_$id" value="$safe_keyword"/><input type="hidden" id="nonce_$id" value="$nonce"/></td></tr>
RETURN;
        $return = sprintf( $return, yourls__( 'Long URL' ), yourls__( 'Short URL' ), yourls__( 'Title' ), yourls__( 'Save' ), yourls__( 'Save new values' ), yourls__( 'Cancel' ), yourls__( 'Cancel editing' ) );
    } else {
        $return = '<tr class="edit-row notfound"><td colspan="6" class="edit-row notfound">' . yourls__( 'Error, URL not found' ) . '</td></tr>';
    }

    $return = yourls_apply_filter( 'table_edit_row', $return, $keyword, $url, $title );

    return $return;
}

/**
 * Returns an "Add" row for the main table.
 *
 * @since 1.0
 * @param string     $keyword   The short URL keyword.
 * @param string     $url       The long URL.
 * @param string     $title     The title of the URL.
 * @param string     $ip        The IP address of the creator.
 * @param string|int $clicks    The number of clicks.
 * @param string     $timestamp The timestamp of creation.
 * @param int        $row_id    Optional. A numeric value used to form row IDs. Defaults to 1.
 * @return string The HTML of the row.
 */
function yourls_table_add_row( $keyword, $url, $title, $ip, $clicks, $timestamp, $row_id = 1 ) {
    $keyword  = yourls_sanitize_keyword($keyword);
    $id       = yourls_unique_element_id('yid', $row_id);
    $shorturl = yourls_link( $keyword );

    $statlink = yourls_statlink( $keyword );

    $delete_link = yourls_nonce_url( 'delete-link_'.$id,
        yourls_add_query_arg( array( 'id' => $id, 'action' => 'delete', 'keyword' => $keyword ), yourls_admin_url( 'admin-ajax.php' ) )
    );

    $edit_link = yourls_nonce_url( 'edit-link_'.$id,
        yourls_add_query_arg( array( 'id' => $id, 'action' => 'edit', 'keyword' => $keyword ), yourls_admin_url( 'admin-ajax.php' ) )
    );

    // Action link buttons: the array
    $actions = array(
        'stats' => array(
            'href'    => $statlink,
            'id'      => "statlink-$id",
            'title'   => yourls_esc_attr__( 'Stats' ),
            'anchor'  => yourls__( 'Stats' ),
        ),
        'share' => array(
            'href'    => '',
            'id'      => "share-button-$id",
            'title'   => yourls_esc_attr__( 'Share' ),
            'anchor'  => yourls__( 'Share' ),
            'onclick' => "toggle_share('$id');return false;",
        ),
        'edit' => array(
            'href'    => $edit_link,
            'id'      => "edit-button-$id",
            'title'   => yourls_esc_attr__( 'Edit' ),
            'anchor'  => yourls__( 'Edit' ),
            'onclick' => "edit_link_display('$id');return false;",
        ),
        'delete' => array(
            'href'    => $delete_link,
            'id'      => "delete-button-$id",
            'title'   => yourls_esc_attr__( 'Delete' ),
            'anchor'  => yourls__( 'Delete' ),
            'onclick' => "remove_link('$id');return false;",
        )
    );
    $actions = yourls_apply_filter( 'table_add_row_action_array', $actions, $keyword );

    // Action link buttons: the HTML
    $action_links = '';
    foreach( $actions as $key => $action ) {
        $onclick = isset( $action['onclick'] ) ? 'onclick="' . $action['onclick'] . '"' : '' ;
        $action_links .= sprintf( '<a href="%s" id="%s" title="%s" class="%s" %s>%s</a>',
            $action['href'], $action['id'], $action['title'], 'button button_'.$key, $onclick, $action['anchor']
        );
    }
    $action_links = yourls_apply_filter( 'action_links', $action_links, $keyword, $url, $ip, $clicks, $timestamp );

    if( ! $title )
        $title = $url;

    $protocol_warning = '';
    if( ! in_array( yourls_get_protocol( $url ) , array( 'http://', 'https://' ) ) )
        $protocol_warning = yourls_apply_filter( 'add_row_protocol_warning', '<span class="warning" title="' . yourls__( 'Not a common link' ) . '">&#9733;</span>' );

    // Row cells: the array
    $cells = array(
        'keyword' => array(
            'template'      => '<a href="%shorturl%">%keyword_html%</a>',
            'shorturl'      => yourls_esc_url( $shorturl ),
            'keyword_html'  => yourls_esc_html( $keyword ),
        ),
        'url' => array(
            'template'      => '<a href="%long_url%" title="%title_attr%">%title_html%</a><br/><small>%warning%<a href="%long_url%">%long_url_html%</a></small>',
            'long_url'      => yourls_esc_url( $url ),
            'title_attr'    => yourls_esc_attr( $title ),
            'title_html'    => yourls_esc_html( yourls_trim_long_string( $title ) ),
            'long_url_html' => yourls_esc_html( yourls_trim_long_string( urldecode( $url ) ) ),
            'warning'       => $protocol_warning,
        ),
        'timestamp' => array(
            'template' => '<span class="timestamp" aria-hidden="true">%timestamp%</span> %date%',
            'timestamp' => $timestamp,
            'date'     => yourls_date_i18n( yourls_get_datetime_format('M d, Y H:i'), yourls_get_timestamp( $timestamp )),
        ),
        'ip' => array(
            'template' => '%ip%',
            'ip'       => $ip,
        ),
        'clicks' => array(
            'template' => '%clicks%',
            'clicks'   => yourls_number_format_i18n( $clicks, 0 ),
        ),
        'actions' => array(
            'template' => '%actions% <input type="hidden" id="keyword_%id%" value="%keyword%"/>',
            'actions'  => $action_links,
            'id'       => $id,
            'keyword'  => $keyword,
        ),
    );
    $cells = yourls_apply_filter( 'table_add_row_cell_array', $cells, $keyword, $url, $title, $ip, $clicks, $timestamp );

    // Row cells: the HTML. Replace every %stuff% in 'template' with 'stuff' value.
    $row = "<tr id=\"id-$id\">";
    foreach( $cells as $cell_id => $elements ) {
        $row .= sprintf( '<td class="%s" id="%s">', $cell_id, $cell_id . '-' . $id );
        $row .= preg_replace_callback( '/%([^%]+)?%/', function( $match ) use ( $elements ) { return $elements[ $match[1] ]; }, $elements['template'] );
        $row .= '</td>';
    }
    $row .= "</tr>";
    $row  = yourls_apply_filter( 'table_add_row', $row, $keyword, $url, $title, $ip, $clicks, $timestamp );

    return $row;
}

/**
 * Echos the main table head.
 *
 * @since 1.0
 * @return void
 */
function yourls_table_head() {
    $start = '<table id="main_table" class="tblSorter" cellpadding="0" cellspacing="1"><thead><tr>'."\n";
    echo yourls_apply_filter( 'table_head_start', $start );

    $cells = yourls_apply_filter( 'table_head_cells', array(
        'shorturl' => yourls__( 'Short URL' ),
        'longurl'  => yourls__( 'Original URL' ),
        'date'     => yourls__( 'Date' ),
        'ip'       => yourls__( 'IP' ),
        'clicks'   => yourls__( 'Clicks' ),
        'actions'  => yourls__( 'Actions' )
    ) );
    foreach( $cells as $k => $v ) {
        echo "<th id='main_table_head_$k'><span>$v</span></th>\n";
    }

    $end = "</tr></thead>\n";
    echo yourls_apply_filter( 'table_head_end', $end );
}

/**
 * Echos the tbody start tag.
 *
 * @since 1.0
 * @return void
 */
function yourls_table_tbody_start() {
    echo yourls_apply_filter( 'table_tbody_start', '<tbody>' );
}

/**
 * Echos the tbody end tag.
 *
 * @since 1.0
 * @return void
 */
function yourls_table_tbody_end() {
    echo yourls_apply_filter( 'table_tbody_end', '</tbody>' );
}

/**
 * Echos the table end tag.
 *
 * @since 1.0
 * @return void
 */
function yourls_table_end() {
    echo yourls_apply_filter( 'table_end', '</table></main>' );
}



/**
 * Echos an HTML link.
 *
 * @since 1.0
 * @param string $href    The URL to link to.
 * @param string $anchor  Optional. The anchor text. Default ''.
 * @param string $element Optional. The element ID. Default ''.
 * @return void
 */
function yourls_html_link( $href, $anchor = '', $element = '' ) {
    if( !$anchor )
        $anchor = $href;
    if( $element )
        $element = sprintf( 'id="%s"', yourls_esc_attr( $element ) );
    $link = sprintf( '<a href="%s" %s>%s</a>', yourls_esc_url( $href ), $element, yourls_esc_html( $anchor ) );
    echo yourls_apply_filter( 'html_link', $link );
}

/**
 * Displays the login screen.
 *
 * This function displays the login screen and exits.
 *
 * @since 1.0
 * @param string $error_msg Optional. An error message to display. Default ''.
 * @return void
 */
function yourls_login_screen( $error_msg = '' ) {
    yourls_html_head( 'login' );

    $action = ( isset( $_GET['action'] ) && $_GET['action'] == 'logout' ? '?' : '' );

    yourls_html_logo();
    ?>
    <main role="main">
        <div id="login">
            <form method="post" action="<?php echo $action; ?>"> <?php // reset any QUERY parameters ?>
                <?php
                    if( !empty( $error_msg ) ) {
                        echo '<p id="error-message" class="error">'.$error_msg.'</p>';
                    }
                    yourls_do_action( 'login_form_top' );
                ?>
                <p>
                    <label for="username"><?php yourls_e( 'Username' ); ?></label><br />
                    <input type="text" id="username" aria-describedby="error-message" name="username" class="text" autocomplete="username" />
                </p>
                <p>
                    <label for="password"><?php yourls_e( 'Password' ); ?></label><br />
                    <input type="password" id="password" name="password" class="text" autocomplete="current-password" />
                </p>
                <?php
                    yourls_do_action( 'login_form_bottom' );
                ?>
                <p style="text-align: right;">
                    <?php yourls_nonce_field('admin_login'); ?>
                    <input type="submit" id="submit" name="submit" value="<?php yourls_e( 'Login' ); ?>" class="button" />
                </p>
                <?php
                    yourls_do_action( 'login_form_end' );
                ?>
            </form>
            <script type="text/javascript">$('#username').focus();</script>
        </div>
    </main>
    <?php
    yourls_html_footer();
    die();
}


/**
 * Displays the admin menu.
 *
 * @since 1.0
 * @return void
 */
function yourls_html_menu() {
    // Build menu links
    if( defined( 'YOURLS_USER' ) ) {
        // Create a logout link with a nonce associated to fake user 'logout' : the user is not yet defined
        // when the logout check is done -- see yourls_is_valid_user()
        $logout_url = yourls_nonce_url( 'admin_logout',
        yourls_add_query_arg(['action' => 'logout'], yourls_admin_url('index.php')), 'nonce', 'logout');
        $logout_link = yourls_apply_filter('logout_link', sprintf( yourls__('Hello <strong>%s</strong>'), YOURLS_USER ) . ' (<a href="' . $logout_url . '" title="' . yourls_esc_attr__( 'Logout' ) . '">' . yourls__( 'Logout' ) . '</a>)' );
    } else {
        $logout_link = yourls_apply_filter( 'logout_link', '' );
    }
    $help_link   = yourls_apply_filter( 'help_link',   '<a href="' . yourls_site_url( false ) .'/readme.html">' . yourls__( 'Help' ) . '</a>' );

    $admin_links    = array();
    $admin_sublinks = array();

    $admin_links['admin'] = array(
        'url'    => yourls_admin_url( 'index.php' ),
        'title'  => yourls__( 'Go to the admin interface' ),
        'anchor' => yourls__( 'Admin interface' )
    );

    if( yourls_is_admin() ) {
        $admin_links['tools'] = array(
            'url'    => yourls_admin_url( 'tools.php' ),
            'anchor' => yourls__( 'Tools' )
        );
        $admin_links['plugins'] = array(
            'url'    => yourls_admin_url( 'plugins.php' ),
            'anchor' => yourls__( 'Manage Plugins' )
        );
        $admin_sublinks['plugins'] = yourls_list_plugin_admin_pages();
    }

    $admin_links    = yourls_apply_filter( 'admin_links',    $admin_links );
    $admin_sublinks = yourls_apply_filter( 'admin_sublinks', $admin_sublinks );

    // Now output menu
    echo '<nav role="navigation"><ul id="admin_menu">'."\n";
    if ( yourls_is_private() && !empty( $logout_link ) )
        echo '<li id="admin_menu_logout_link">' . $logout_link .'</li>';

    foreach( (array)$admin_links as $link => $ar ) {
        if( isset( $ar['url'] ) ) {
            $anchor = isset( $ar['anchor'] ) ? $ar['anchor'] : $link;
            $title  = isset( $ar['title'] ) ? 'title="' . $ar['title'] . '"' : '';
            printf( '<li id="admin_menu_%s_link" class="admin_menu_toplevel"><a href="%s" %s>%s</a>', $link, $ar['url'], $title, $anchor );
        }
        // Output submenu if any. TODO: clean up, too many code duplicated here
        if( isset( $admin_sublinks[$link] ) ) {
            echo "<ul>\n";
            foreach( $admin_sublinks[$link] as $link => $ar ) {
                if( isset( $ar['url'] ) ) {
                    $anchor = isset( $ar['anchor'] ) ? $ar['anchor'] : $link;
                    $title  = isset( $ar['title'] ) ? 'title="' . $ar['title'] . '"' : '';
                    printf( '<li id="admin_menu_%s_link" class="admin_menu_sublevel admin_menu_sublevel_%s"><a href="%s" %s>%s</a>', $link, $link, $ar['url'], $title, $anchor );
                }
            }
            echo "</ul>\n";
        }
    }

    if ( isset( $help_link ) )
        echo '<li id="admin_menu_help_link">' . $help_link .'</li>';

    yourls_do_action( 'admin_menu' );
    echo "</ul></nav>\n";
    yourls_do_action( 'admin_notices' );
    yourls_do_action( 'admin_notice' ); // because I never remember if it's 'notices' or 'notice'
    /*
    To display a notice:
    $message = "<div>OMG, dude, I mean!</div>" );
    yourls_add_action( 'admin_notices', function() use ( $message ) { echo (string) $message; } );
    */
}

/**
 * Displays a notice in the admin interface.
 *
 * @since 1.0
 * @param string $message The message to display.
 * @param string $style   Optional. The style of the notice (e.g., 'notice', 'error', 'success'). Default 'notice'.
 * @return void
 */
function yourls_add_notice( $message, $style = 'notice' ) {
    // Escape single quotes in $message to avoid breaking the anonymous function
    $message = yourls_notice_box( strtr( $message, array( "'" => "\'" ) ), $style );
    yourls_add_action( 'admin_notices', function() use ( $message ) { echo (string) $message; } );
}

/**
 * Returns a formatted notice.
 *
 * @since 1.0
 * @param string $message The message to display.
 * @param string $style   Optional. The CSS class to use for the notice. Default 'notice'.
 * @return string The HTML of the notice.
 */
function yourls_notice_box( $message, $style = 'notice' ) {
    return <<<HTML
    <div class="$style">
    <p>$message</p>
    </div>
HTML;
}

/**
 * Displays a page from the YOURLS page directory.
 *
 * This function includes and displays a PHP file from the YOURLS_PAGEDIR
 * directory, as if it were a standard short URL (e.g., http://sho.rt/$page).
 *
 * @since 1.0
 * @param string $page The PHP file to display (without the .php extension).
 * @return void
 */
function yourls_page( $page ) {
    if( !yourls_is_page($page)) {
        yourls_die( yourls_s('Page "%1$s" not found', $page), yourls__('Not found'), 404 );
    }

    yourls_do_action( 'pre_page', $page );
    $load = yourls_include_file_sandbox(YOURLS_PAGEDIR . "/$page.php");
    if (is_string($load)) {
        yourls_die( $load, yourls__('Not found'), 404 );
    }
    yourls_do_action( 'post_page', $page );
}

/**
 * Displays the language attributes for the HTML tag.
 *
 * Builds up a set of HTML attributes containing the text direction and language
 * information for the page.
 *
 * @since 1.6
 * @return void
 */
function yourls_html_language_attributes() {
    $attributes = array();
    $output = '';

    $attributes[] = ( yourls_is_rtl() ? 'dir="rtl"' : 'dir="ltr"' );

    $doctype = yourls_apply_filter( 'html_language_attributes_doctype', 'html' );
    // Experimental: get HTML lang from locale. Should work. Convert fr_FR -> fr-FR
    if ( $lang = str_replace( '_', '-', yourls_get_locale() ) ) {
        if( $doctype == 'xhtml' ) {
            $attributes[] = "xml:lang=\"$lang\"";
        } else {
            $attributes[] = "lang=\"$lang\"";
        }
    }

    $output = implode( ' ', $attributes );
    $output = yourls_apply_filter( 'html_language_attributes', $output );
    echo $output;
}

/**
 * Outputs translated strings used by the Javascript calendar.
 *
 * @since 1.6
 * @return void
 */
function yourls_l10n_calendar_strings() {
    echo "\n<script>\n";
    echo "var l10n_cal_month = " . json_encode( array_values( yourls_l10n_months() ) ) . ";\n";
    echo "var l10n_cal_days = " . json_encode( array_values( yourls_l10n_weekday_initial() ) ) . ";\n";
    echo "var l10n_cal_today = \"" . yourls_esc_js( yourls__( 'Today' ) ) . "\";\n";
    echo "var l10n_cal_close = \"" . yourls_esc_js( yourls__( 'Close' ) ) . "\";\n";
    echo "</script>\n";

    // Dummy returns, to initialize l10n strings used in the calendar
    yourls__( 'Today' );
    yourls__( 'Close' );
}


/**
 * Displays a notice if a newer version of YOURLS is available.
 *
 * @since 1.7
 * @param string|null $compare_with Optional. The YOURLS version to compare to. Defaults to the current version.
 * @return void
 */
function yourls_new_core_version_notice($compare_with = null) {
    $compare_with = $compare_with ?: YOURLS_VERSION;

    $checks = yourls_get_option( 'core_version_checks' );
    $latest = isset($checks->last_result->latest) ? yourls_sanitize_version($checks->last_result->latest) : false;

    if( $latest AND version_compare( $latest, $compare_with, '>' ) ) {
        yourls_do_action('new_core_version_notice', $latest);
        $msg = yourls_s( '<a href="%s">YOURLS version %s</a> is available. Please update!', 'http://yourls.org/download', $latest );
        yourls_add_notice( $msg );
    }
}

/**
 * Displays or returns the HTML for a bookmarklet link.
 *
 * @since 1.7.1
 * @param string $href   The bookmarklet link (a minified "javascript:" URI).
 * @param string $anchor The link anchor text.
 * @param bool   $echo   Optional. True to display, false to return the HTML. Default true.
 * @return string The HTML for the bookmarklet link.
 */
function yourls_bookmarklet_link( $href, $anchor, $echo = true ) {
    $alert = yourls_esc_attr__( 'Drag to your toolbar!' );
    $link = <<<LINK
    <a href="$href" class="bookmarklet" onclick="alert('$alert');return false;">$anchor</a>
LINK;

    if( $echo )
        echo $link;
    return $link;
}

/**
 * Sets the HTML context.
 *
 * @since 1.7.3
 * @param string $context The HTML context (e.g., 'stats', 'index', 'infos').
 * @return void
 */
function yourls_set_html_context($context) {
    yourls_get_db()->set_html_context($context);
}

/**
 * Gets the HTML context.
 *
 * @since 1.7.3
 * @return string The HTML context.
 */
function yourls_get_html_context() {
    return yourls_get_db()->get_html_context();
}

/**
 * Print HTML link for favicon
 *
 * @since 1.7.10
 * @return mixed|void
 */
function yourls_html_favicon() {
    // Allow plugins to short-circuit the whole function
    $pre = yourls_apply_filter( 'shunt_html_favicon', false );
    if ( false !== $pre ) {
        return $pre;
    }

    printf( '<link rel="shortcut icon" href="%s" />', yourls_get_yourls_favicon_url(false) );
}
