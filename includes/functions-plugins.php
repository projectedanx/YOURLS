<?php
/**
 * YOURLS Plugin API
 *
 * This file contains the functions that are used for managing plugins. These
 * functions are used to activate, deactivate, and load plugins, as well as to
 * provide a framework for plugins to interact with the core application.
 *
 * @package YOURLS
 * @since 1.5
 */
/**
 * The filter/plugin API is located in this file, which allows for creating filters
 * and hooking functions, and methods. The functions or methods will be run when
 * the filter is called.
 *
 * Any of the syntaxes explained in the PHP documentation for the
 * {@link https://www.php.net/manual/en/language.types.callable.php 'callback'}
 * type are valid.
 *
 * This API is heavily inspired by the one I implemented in Zenphoto 1.3, which was heavily inspired by the one used in WordPress.
 *
 * @author Ozh
 * @since 1.5
 */

/**
 * This global var will collect filters with the following structure:
 * $yourls_filters['hook']['array of priorities']['serialized function names']['array of ['array (functions, accepted_args, filter or action)]']
 *
 * Real life example :
 * print_r($yourls_filters) :
 * Array
 *  (
 *      [plugins_loaded] => Array
 *          (
 *              [10] => Array
 *                  (
 *                      [yourls_kses_init] => Array
 *                          (
 *                              [function] => yourls_kses_init
 *                              [accepted_args] => 1
 *                              [type] => action
 *                          )
 *                      [yourls_tzp_config] => Array
 *                          (
 *                              [function] => yourls_tzp_config
 *                              [accepted_args] => 1
 *                              [type] => action
 *                          )
 *                  )
 *          )
 *      [admin_menu] => Array
 *          (
 *              [10] => Array
 *                  (
 *                      [ozh_show_db] => Array
 *                          (
 *                              [function] => ozh_show_db
 *                              [accepted_args] =>
 *                              [type] => filter
 *                          )
 *                  )
 *          )
 *  )
 *
 * @var array $yourls_filters
 */
if ( !isset( $yourls_filters ) ) {
    $yourls_filters = [];
}

/**
 * This global var will collect 'done' actions with the following structure:
 * $yourls_actions['hook'] => number of time this action was done
 *
 * @var array $yourls_actions
 */
if ( !isset( $yourls_actions ) ) {
    $yourls_actions = [];
}

/**
 * Registers a filtering function.
 *
 * @since 1.5
 * @param string      $hook          The name of the filter hook.
 * @param callable    $function_name The name of the function to be called.
 * @param int         $priority      Optional. The priority of the function. Default 10.
 * @param int|null    $accepted_args Optional. The number of arguments the function accepts. Default null.
 * @param string      $type          Optional. The type of hook. Default 'filter'.
 * @return void
 */
function yourls_add_filter( $hook, $function_name, $priority = 10, $accepted_args = NULL, $type = 'filter' ) {
    global $yourls_filters;
    // At this point, we cannot check if the function exists, as it may well be defined later (which is OK)
    $id = yourls_filter_unique_id($function_name);

    $yourls_filters[ $hook ][ $priority ][ $id ] = [
        'function'      => $function_name,
        'accepted_args' => $accepted_args,
        'type'          => $type,
    ];
}

/**
 * Hooks a function on to a specific action.
 *
 * @since 1.5
 * @param string   $hook          The name of the action hook.
 * @param callable $function_name The name of the function to be called.
 * @param int      $priority      Optional. The priority of the function. Default 10.
 * @param int      $accepted_args Optional. The number of arguments the function accepts. Default 1.
 * @return void
 */
function yourls_add_action( $hook, $function_name, $priority = 10, $accepted_args = 1 ) {
    yourls_add_filter( $hook, $function_name, $priority, $accepted_args, 'action' );
}

/**
 * Builds a unique ID for a filter function.
 *
 * @since 1.5
 * @param callable $function The function to build the ID for.
 * @return string A unique ID for the function.
 */
function yourls_filter_unique_id($function) {
    // If given a string (function name)
    if ( is_string( $function ) ) {
        return $function;
    }

    if ( is_object( $function ) ) {
        // Closures are implemented as objects
        $function = [ $function, '' ];
    }
    else {
        $function = (array)$function;
    }

    // Object Class Calling
    if ( is_object( $function[0] ) ) {
        return spl_object_hash( $function[0] ).$function[1];
    }

    // Last case, static Calling : $function[0] is a string (Class Name) and $function[1] is a string (Method Name)
    return $function[0].'::'.$function[1];
}

/**
 * Applies a filter to a value.
 *
 * @since 1.5
 * @param string $hook      The name of the filter hook.
 * @param mixed  $value     The value to filter.
 * @param bool   $is_action Optional. Whether the function is called by yourls_do_action(). Default false.
 * @return mixed The filtered value.
 */
function yourls_apply_filter( $hook, $value = '', $is_action = false ) {
    global $yourls_filters;

    $args = func_get_args();

    // Do 'all' filters first. We check if $is_action to avoid calling `yourls_call_all_hooks()` twice
    if ( !$is_action && isset($yourls_filters['all']) ) {
        yourls_call_all_hooks('filter', $hook, $args);
    }

    // If we have no hook attached to that filter, just return unmodified $value
    if ( !isset( $yourls_filters[ $hook ] ) ) {
        return $value;
    }

    // Sort filters by priority
    ksort( $yourls_filters[ $hook ] );

    // Loops through each filter
    reset( $yourls_filters[ $hook ] );
    do {
        foreach ( (array)current( $yourls_filters[ $hook ] ) as $the_ ) {
            $_value = '';
            if ( !is_null($the_[ 'function' ]) ) {
                $args[ 1 ] = $value;
                $count = $the_[ 'accepted_args' ];
                if ( is_null( $count ) ) {
                    $_value = call_user_func_array( $the_[ 'function' ], array_slice( $args, 1 ) );
                }
                else {
                    $_value = call_user_func_array( $the_[ 'function' ], array_slice( $args, 1, (int)$count ) );
                }
            }
            if ( $the_[ 'type' ] == 'filter' ) {
                $value = $_value;
            }
        }
    } while ( next( $yourls_filters[ $hook ] ) !== false );

    // Return the value - this will be actually used only for filters, not for actions (see `yourls_do_action()`)
    return $value;
}

/**
 * Executes a function hooked to a specific action.
 *
 * @since 1.5
 * @param string $hook The name of the action hook.
 * @param mixed  ...$arg Optional. Arguments to pass to the hooked function.
 * @return void
 */
function yourls_do_action( $hook, $arg = '' ) {
    global $yourls_actions, $yourls_filters;

    // Keep track of actions that are "done"
    if ( !isset( $yourls_actions ) ) {
        $yourls_actions = [];
    }
    if ( !isset( $yourls_actions[ $hook ] ) ) {
        $yourls_actions[ $hook ] = 1;
    }
    else {
        ++$yourls_actions[ $hook ];
    }

    $args = [];
    if ( is_array( $arg ) && 1 == count( $arg ) && isset( $arg[ 0 ] ) && is_object( $arg[ 0 ] ) ) { // array(&$this)
        $args[] =& $arg[ 0 ];
    }
    else {
        $args[] = $arg;
    }

    for ( $a = 2 ; $a < func_num_args() ; $a++ ) {
        $args[] = func_get_arg( $a );
    }

    // Do 'all' actions first
    if ( isset($yourls_filters['all']) ) {
        yourls_call_all_hooks('action', $hook, $args);
    }

    yourls_apply_filter( $hook, $args, true );
}

/**
 * Retrieves the number of times an action has been fired.
 *
 * @since 1.5
 * @param string $hook The name of the action hook.
 * @return int The number of times the action has been fired.
 */
function yourls_did_action( $hook ) {
    global $yourls_actions;
    return empty( $yourls_actions[ $hook ] ) ? 0 : $yourls_actions[ $hook ];
}

/**
 * Executes the 'all' hook.
 *
 * This function is an internal function that is used by `yourls_do_action()` and
 * `yourls_apply_filter()` to execute the 'all' hook. It is not meant to be used
 * directly.
 *
 * @since 1.8.1
 * @param string $type The type of hook ('action' or 'filter').
 * @param string $hook The name of the hook.
 * @param mixed  ...$args The arguments passed to the hook.
 * @return void
 */
function yourls_call_all_hooks($type, $hook, ...$args) {
    global $yourls_filters;

    // Loops through each filter or action hooked with the 'all' hook
    reset( $yourls_filters['all'] );
    do {
        foreach ( (array) current($yourls_filters['all']) as $the_ )
            // Call the hooked function only if it's hooked to the current type of hook (eg 'filter' or 'action')
            if ( $the_['type'] == $type && !is_null($the_['function']) ) {
                call_user_func_array( $the_['function'], array($type, $hook, $args) );
                /**
                 * Note that we don't return a value here, regardless of $type being an action (obviously) but also
                 * a filter. Indeed it would not make sense to actually "filter" and return values when we're
                 * feeding the same function every single hook in YOURLS, no matter their parameters.
                 */
            }

    } while ( next($yourls_filters['all']) !== false );

}

/**
 * Removes a function from a filter hook.
 *
 * @since 1.5
 * @param string   $hook               The filter hook to which the function is hooked.
 * @param callable $function_to_remove The name of the function to remove.
 * @param int      $priority           Optional. The priority of the function. Default 10.
 * @return bool True if the function was removed, false otherwise.
 */
function yourls_remove_filter( $hook, $function_to_remove, $priority = 10 ) {
    global $yourls_filters;

    $function_to_remove = yourls_filter_unique_id($function_to_remove);

    $remove = isset( $yourls_filters[ $hook ][ $priority ][ $function_to_remove ] );

    if ( $remove === true ) {
        unset ( $yourls_filters[ $hook ][ $priority ][ $function_to_remove ] );
        if ( empty( $yourls_filters[ $hook ][ $priority ] ) ) {
            unset( $yourls_filters[ $hook ] );
        }
    }
    return $remove;
}

/**
 * Removes a function from an action hook.
 *
 * @since 1.7.1
 * @param string   $hook               The action hook to which the function is hooked.
 * @param callable $function_to_remove The name of the function to remove.
 * @param int      $priority           Optional. The priority of the function. Default 10.
 * @return bool True if the function was removed, false otherwise.
 */
function yourls_remove_action( $hook, $function_to_remove, $priority = 10 ) {
    return yourls_remove_filter( $hook, $function_to_remove, $priority );
}

/**
 * Removes all functions from an action hook.
 *
 * @since 1.7.1
 * @param string    $hook     The action hook to remove functions from.
 * @param int|false $priority Optional. The priority of the functions to remove. Default false.
 * @return bool True when finished.
 */
function yourls_remove_all_actions( $hook, $priority = false ) {
    return yourls_remove_all_filters( $hook, $priority );
}

/**
 * Removes all functions from a filter hook.
 *
 * @since 1.7.1
 * @param string    $hook     The filter hook to remove functions from.
 * @param int|false $priority Optional. The priority of the functions to remove. Default false.
 * @return bool True when finished.
 */
function yourls_remove_all_filters( $hook, $priority = false ) {
    global $yourls_filters;

    if ( isset( $yourls_filters[ $hook ] ) ) {
        if ( $priority === false ) {
            unset( $yourls_filters[ $hook ] );
        }
        elseif ( isset( $yourls_filters[ $hook ][ $priority ] ) ) {
            unset( $yourls_filters[ $hook ][ $priority ] );
        }
    }

    return true;
}

/**
 * Returns the filters for a specific hook.
 *
 * @since 1.8.3
 * @param string $hook The hook to retrieve filters for.
 * @return array An array of filters for the specified hook.
 */
function yourls_get_filters($hook) {
    global $yourls_filters;
    return $yourls_filters[$hook] ?? array();
}

/**
 * Returns the actions for a specific hook.
 *
 * @since 1.8.3
 * @param string $hook The hook to retrieve actions for.
 * @return array An array of actions for the specified hook.
 */
function yourls_get_actions($hook) {
    return yourls_get_filters($hook);
}
/**
 * Checks if any filter has been registered for a hook.
 *
 * @since 1.5
 * @param string         $hook              The name of the filter hook.
 * @param callable|false $function_to_check Optional. The function to check for. Default false.
 * @return bool|int True if the hook has a filter, false otherwise. If $function_to_check is specified,
 *                  returns the priority of that function on the hook, or false if it's not attached.
 */
function yourls_has_filter( $hook, $function_to_check = false ) {
    global $yourls_filters;

    $has = !empty( $yourls_filters[ $hook ] );
    if ( false === $function_to_check || false === $has ) {
        return $has;
    }

    if ( !$idx = yourls_filter_unique_id($function_to_check) ) {
        return false;
    }

    foreach ( array_keys( $yourls_filters[ $hook ] ) as $priority ) {
        if ( isset( $yourls_filters[ $hook ][ $priority ][ $idx ] ) ) {
            return $priority;
        }
    }
    return false;
}


/**
 * Checks if any action has been registered for a hook.
 *
 * @since 1.5
 * @param string         $hook              The name of the action hook.
 * @param callable|false $function_to_check Optional. The function to check for. Default false.
 * @return bool|int True if the hook has an action, false otherwise. If $function_to_check is specified,
 *                  returns the priority of that function on the hook, or false if it's not attached.
 */
function yourls_has_action( $hook, $function_to_check = false ) {
    return yourls_has_filter( $hook, $function_to_check );
}

/**
 * Returns the number of active plugins.
 *
 * @since 1.5
 * @return int The number of active plugins.
 */
function yourls_has_active_plugins() {
    return count( yourls_get_db()->get_plugins() );
}

/**
 * Lists the plugins in the user/plugins directory.
 *
 * @since 1.5
 * @return array An array of plugins, with the plugin file as the key and an array of plugin data as the value.
 */
function yourls_get_plugins() {
    $plugins = (array)glob( YOURLS_PLUGINDIR.'/*/plugin.php' );

    if ( is_array( $plugins ) ) {
        foreach ( $plugins as $key => $plugin ) {
            $plugins[ yourls_plugin_basename( $plugin ) ] = yourls_get_plugin_data( $plugin );
            unset( $plugins[ $key ] );
        }
    }

    return empty( $plugins ) ? [] : $plugins;
}

/**
 * Checks if a plugin is active.
 *
 * @since 1.5
 * @param string $plugin The path to the plugin file, relative to the plugins directory.
 * @return bool True if the plugin is active, false otherwise.
 */
function yourls_is_active_plugin( $plugin ) {
    return yourls_has_active_plugins() > 0 ?
        in_array( yourls_plugin_basename( $plugin ), yourls_get_db()->get_plugins() )
        : false;
}

/**
 * Parses a plugin's header.
 *
 * @since 1.5
 * @param string $file The path to the plugin file.
 * @return array An array of plugin data.
 */
function yourls_get_plugin_data( $file ) {
    $fp = fopen( $file, 'r' ); // assuming $file is readable, since yourls_load_plugins() filters this
    $data = fread( $fp, 8192 ); // get first 8kb
    fclose( $fp );

    // Capture all the header within first comment block
    if ( !preg_match( '!.*?/\*(.*?)\*/!ms', $data, $matches ) ) {
        return [];
    }

    // Capture each line with "Something: some text"
    unset( $data );
    $lines = preg_split( "[\n|\r]", $matches[ 1 ] );
    unset( $matches );

    $plugin_data = [];
    foreach ( $lines as $line ) {
        if ( !preg_match( '!(\s*)?\*?(\s*)?(.*?):\s+(.*)!', $line, $matches ) ) {
            continue;
        }

        $plugin_data[ trim($matches[3]) ] = yourls_esc_html(trim($matches[4]));
    }

    return $plugin_data;
}

/**
 * Includes active plugins.
 *
 * @since 1.5
 * @return array An array containing information about the loaded plugins.
 */
function yourls_load_plugins() {
    // Don't load plugins when installing or updating
    if ( yourls_is_installing() OR yourls_is_upgrading() OR !yourls_is_installed() ) {
        return [
            'loaded' => false,
            'info'   => 'install/upgrade'
        ];
    }

    $active_plugins = (array)yourls_get_option( 'active_plugins' );
    if ( empty( $active_plugins ) ) {
        return [
            'loaded' => false,
            'info'   => 'no active plugin'
        ];
    }

    $plugins = [];
    foreach ( $active_plugins as $key => $plugin ) {
        $file = YOURLS_PLUGINDIR . '/' . $plugin;
        if ( yourls_is_a_plugin_file($file) && yourls_include_file_sandbox( $file ) === true ) {
            $plugins[] = $plugin;
            unset( $active_plugins[ $key ] );
        }
    }

    // Replace active plugin list with list of plugins we just activated
    yourls_get_db()->set_plugins( $plugins );
    $info = count( $plugins ).' activated';

    // $active_plugins should be empty now, if not, a plugin could not be found, or is erroneous : remove it
    $missing_count = count( $active_plugins );
    if ( $missing_count > 0 ) {
        yourls_update_option( 'active_plugins', $plugins );
        $message = yourls_n( 'Could not find and deactivate plugin :', 'Could not find and deactivate plugins :', $missing_count );
        $missing = '<strong>'.implode( '</strong>, <strong>', $active_plugins ).'</strong>';
        yourls_add_notice( $message.' '.$missing );
        $info .= ', '.$missing_count.' removed';
    }

    return [
        'loaded' => true,
        'info'   => $info
    ];
}

/**
 * Checks if a file is a plugin file.
 *
 * This function checks if a file is a valid plugin file, but does not check if it
 * is a valid PHP file.
 *
 * @since 1.5
 * @param string $file The full path to the file.
 * @return bool True if the file is a plugin file, false otherwise.
 */
function yourls_is_a_plugin_file($file) {
    return false === strpos( $file, '..' )
           && false === strpos( $file, './' )
           && 'plugin.php' === substr( $file, -10 )
           && is_readable( $file );
}

/**
 * Activates a plugin.
 *
 * @since 1.5
 * @param string $plugin The path to the plugin file, relative to the plugins directory.
 * @return string|true True on success, or an error string on failure.
 */
function yourls_activate_plugin( $plugin ) {
    // validate file
    $plugin = yourls_plugin_basename( $plugin );
    $plugindir = yourls_sanitize_filename( YOURLS_PLUGINDIR );
    if ( !yourls_is_a_plugin_file($plugindir . '/' . $plugin ) ) {
        return yourls__( 'Not a valid plugin file' );
    }

    // check not activated already
    $ydb = yourls_get_db();
    if ( yourls_is_active_plugin( $plugin ) ) {
        return yourls__( 'Plugin already activated' );
    }

    // attempt activation.
    $attempt = yourls_include_file_sandbox( $plugindir.'/'.$plugin );
    if( $attempt !== true ) {
        return yourls_s( 'Plugin generated unexpected output. Error was: <br/><pre>%s</pre>', $attempt );
    }

    // so far, so good: update active plugin list
    $ydb->add_plugin( $plugin );
    yourls_update_option( 'active_plugins', $ydb->get_plugins() );
    yourls_do_action( 'activated_plugin', $plugin );
    yourls_do_action( 'activated_'.$plugin );

    return true;
}

/**
 * Deactivates a plugin.
 *
 * @since 1.5
 * @param string $plugin The path to the plugin file, relative to the plugins directory.
 * @return string|true True on success, or an error string on failure.
 */
function yourls_deactivate_plugin( $plugin ) {
    $plugin = yourls_plugin_basename( $plugin );

    // Check plugin is active
    if ( !yourls_is_active_plugin( $plugin ) ) {
        return yourls__( 'Plugin not active' );
    }

    // Check if we have an uninstall file - load if so
    $uninst_file = YOURLS_PLUGINDIR . '/' . dirname($plugin) . '/uninstall.php';
    $attempt = yourls_include_file_sandbox( $uninst_file );

    // Check if we have an error to display
    if ( is_string( $attempt ) ) {
        $message = yourls_s( 'Loading %s generated unexpected output. Error was: <br/><pre>%s</pre>', $uninst_file, $attempt );
        return( $message );
    }

    if ( $attempt === true ) {
        define('YOURLS_UNINSTALL_PLUGIN', true);
    }

    // Deactivate the plugin
    $ydb = yourls_get_db();
    $plugins = $ydb->get_plugins();
    $key = array_search( $plugin, $plugins );
    if ( $key !== false ) {
        array_splice( $plugins, $key, 1 );
    }

    $ydb->set_plugins( $plugins );
    yourls_update_option( 'active_plugins', $plugins );
    yourls_do_action( 'deactivated_plugin', $plugin );
    yourls_do_action( 'deactivated_'.$plugin );

    return true;
}

/**
 * Returns the path of a plugin file, relative to the plugins directory.
 *
 * @since 1.5
 * @param string $file The path to the plugin file.
 * @return string The relative path to the plugin file.
 */
function yourls_plugin_basename( $file ) {
    return trim( str_replace( yourls_sanitize_filename( YOURLS_PLUGINDIR ), '', yourls_sanitize_filename( $file ) ), '/' );
}

/**
 * Returns the URL of a plugin's directory.
 *
 * @since 1.5
 * @param string $file The path to the plugin file.
 * @return string The URL of the plugin's directory.
 */
function yourls_plugin_url( $file ) {
    $url = YOURLS_PLUGINURL.'/'.yourls_plugin_basename( $file );
    if ( yourls_is_ssl() or yourls_needs_ssl() ) {
        $url = str_replace( 'http://', 'https://', $url );
    }
    return (string)yourls_apply_filter( 'plugin_url', $url, $file );
}

/**
 * Builds a list of links to plugin admin pages.
 *
 * @since 1.5
 * @return array An array of links to plugin admin pages.
 */
function yourls_list_plugin_admin_pages() {
    $plugin_links = [];
    foreach ( yourls_get_db()->get_plugin_pages() as $plugin => $page ) {
        $plugin_links[ $plugin ] = [
            'url'    => yourls_admin_url( 'plugins.php?page='.$page[ 'slug' ] ),
            'anchor' => $page[ 'title' ],
        ];
    }
    return $plugin_links;
}

/**
 * Registers a plugin administration page.
 *
 * @since 1.5
 * @param string   $slug     The slug for the admin page.
 * @param string   $title    The title of the admin page.
 * @param callable $function The function that displays the admin page.
 * @return void
 */
function yourls_register_plugin_page( $slug, $title, $function ) {
    yourls_get_db()->add_plugin_page( $slug, $title, $function );
}

/**
 * Handles the display of a plugin administration page.
 *
 * @since 1.5
 * @param string $plugin_page The slug of the plugin page to display.
 * @return void
 */
function yourls_plugin_admin_page( $plugin_page ) {
    // Check the plugin page is actually registered
    $pages = yourls_get_db()->get_plugin_pages();
    if ( !isset( $pages[ $plugin_page ] ) ) {
        yourls_die( yourls__( 'This page does not exist. Maybe a plugin you thought was activated is inactive?' ), yourls__( 'Invalid link' ) );
    }

    // Check the plugin page function is actually callable
    $page_function = $pages[ $plugin_page ][ 'function' ];
    if (!is_callable($page_function)) {
        yourls_die( yourls__( 'This page cannot be displayed because the displaying function is not callable.' ), yourls__( 'Invalid code' ) );
    }

    // Draw the page itself
    yourls_do_action( 'load-'.$plugin_page );
    yourls_html_head( 'plugin_page_'.$plugin_page, $pages[ $plugin_page ][ 'title' ] );
    yourls_html_logo();
    yourls_html_menu();

    $page_function( );

    yourls_html_footer();
}

/**
 * Callback function for sorting plugins.
 *
 * @since 1.5
 * @param array $plugin_a The first plugin to compare.
 * @param array $plugin_b The second plugin to compare.
 * @return int 0 if the plugins are equal, 1 if $plugin_a is greater, -1 if $plugin_b is greater.
 */
function yourls_plugins_sort_callback( $plugin_a, $plugin_b ) {
    $orderby = yourls_apply_filter( 'plugins_sort_callback', 'Plugin Name' );
    $order = yourls_apply_filter( 'plugins_sort_callback', 'ASC' );

    $a = isset( $plugin_a[ $orderby ] ) ? $plugin_a[ $orderby ] : '';
    $b = isset( $plugin_b[ $orderby ] ) ? $plugin_b[ $orderby ] : '';

    if ( $a == $b ) {
        return 0;
    }

    if ( 'DESC' == $order ) {
        return ( $a < $b ) ? 1 : -1;
    }
    else {
        return ( $a < $b ) ? -1 : 1;
    }
}

/**
 * Executes the 'shutdown' action.
 *
 * This function is registered as a shutdown function and is executed when the
 * script has finished executing.
 *
 * @since 1.5.1
 * @return void
 */
function yourls_shutdown() {
    yourls_do_action( 'shutdown' );
}

/**
 * Returns true.
 *
 * @since 1.7.1
 * @return bool True.
 */
function yourls_return_true() {
    return true;
}

/**
 * Returns false.
 *
 * @since 1.7.1
 * @return bool False.
 */
function yourls_return_false() {
    return false;
}

/**
 * Returns 0.
 *
 * @since 1.7.1
 * @return int 0.
 */
function yourls_return_zero() {
    return 0;
}

/**
 * Returns an empty array.
 *
 * @since 1.7.1
 * @return array Empty array.
 */
function yourls_return_empty_array() {
    return [];
}

/**
 * Returns null.
 *
 * @since 1.7.1
 * @return null Null value.
 */
function yourls_return_null() {
    return null;
}

/**
 * Returns an empty string.
 *
 * @since 1.7.1
 * @return string Empty string.
 */
function yourls_return_empty_string() {
    return '';
}
