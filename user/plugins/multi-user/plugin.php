<?php
/*
Plugin Name: Multi-User Admin
Plugin URI: http://yourls.org/
Description: Adds a basic multi-user administrative frontend.
Version: 1.0
Author: YOURLS Contributors
Author URI: http://yourls.org/
*/

// No direct call
if( !defined( 'YOURLS_ABSPATH' ) ) die();

// Option name for storing users
define( 'YOURLS_MULTI_USER_OPTION', 'multi_user_passwords' );

// Hook into YOURLS authentication to merge our users
yourls_add_filter( 'shunt_is_valid_user', 'multi_user_authenticate' );

function multi_user_authenticate( $false_by_default ) {
    global $yourls_user_passwords;

    // Get stored users from DB options
    $stored_users = yourls_get_option( YOURLS_MULTI_USER_OPTION, array() );

    // Merge stored users into the global passwords array so the core auth works
    if ( is_array( $stored_users ) && !empty( $stored_users ) ) {
        $yourls_user_passwords = array_merge( $yourls_user_passwords, $stored_users );
    }

    // Return false to let the core authentication process continue with our merged array
    return false;
}

// Register a new admin page
yourls_add_action( 'plugins_loaded', 'multi_user_add_page' );

function multi_user_add_page() {
    yourls_register_plugin_page( 'multi-user', 'Multi-User Management', 'multi_user_display_page' );
}

yourls_add_action( 'load-multi-user', 'multi_user_process_actions' );

function multi_user_process_actions() {
    global $yourls_user_passwords;

    // Make sure we have the latest stored users merged
    $stored_users = yourls_get_option( YOURLS_MULTI_USER_OPTION, array() );

    // Process form submission for adding a user
    if ( isset( $_POST['action'] ) && $_POST['action'] == 'add_user' ) {
        if( yourls_verify_nonce( 'add_user' ) === false ) {
             yourls_die( 'Forbidden', 'CSRF validation failed', 403 );
        }
        $username = isset( $_POST['username'] ) ? multi_user_sanitize_text_field( $_POST['username'] ) : '';
        $password = isset( $_POST['password'] ) ? $_POST['password'] : '';

        if ( !empty($username) && !empty($password) ) {
            // Check if user already exists
            if ( isset( $yourls_user_passwords[$username] ) ) {
                 yourls_add_notice( 'User already exists.', 'notice notice-error' );
            } else {
                 // Save the user to our custom option array
                 $stored_users[$username] = yourls_hash_password($password);
                 yourls_update_option( YOURLS_MULTI_USER_OPTION, $stored_users );

                 // Update the global array for immediate display
                 $yourls_user_passwords[$username] = $stored_users[$username];

                 yourls_add_notice( 'User ' . multi_user_esc_html( $username ) . ' added successfully.', 'notice notice-success' );
            }
        } else {
             yourls_add_notice( 'Username and password are required.', 'notice notice-error' );
        }
    }

    // Process form submission for deleting a user
    if ( isset( $_POST['action'] ) && $_POST['action'] == 'delete_user' ) {
        if ( yourls_verify_nonce( 'delete_user' ) === false ) {
             yourls_die( 'Forbidden', 'CSRF validation failed', 403 );
        }
        $username = isset( $_POST['username'] ) ? multi_user_sanitize_text_field( $_POST['username'] ) : '';

        // Cannot delete config users
        $config_users = array();
        if ( file_exists( YOURLS_CONFIGFILE ) ) {
             // Basic check - realistically we just check if it's in our stored options
        }

        if ( isset( $stored_users[$username] ) ) {
             unset( $stored_users[$username] );
             yourls_update_option( YOURLS_MULTI_USER_OPTION, $stored_users );
             unset( $yourls_user_passwords[$username] );
             yourls_add_notice( 'User ' . multi_user_esc_html( $username ) . ' deleted.', 'notice notice-success' );
        } else if ( isset( $yourls_user_passwords[$username] ) ) {
             yourls_add_notice( 'Cannot delete a user defined in the configuration file.', 'notice notice-error' );
        }
    }
}

// Display the admin page
function multi_user_display_page() {
    global $yourls_user_passwords;

    // Make sure we have the latest stored users merged
    $stored_users = yourls_get_option( YOURLS_MULTI_USER_OPTION, array() );
    if ( is_array( $stored_users ) ) {
        $yourls_user_passwords = array_merge( $yourls_user_passwords, $stored_users );
    }

    echo '<h2>Multi-User Management</h2>';

    // Add User Form
    echo '<h3>Add New User</h3>';
    echo '<form method="post" action="">';
    echo '<input type="hidden" name="action" value="add_user" />';
    echo '<input type="hidden" name="nonce" value="' . yourls_create_nonce( 'add_user' ) . '" />';
    echo '<p><label for="username">Username:</label> <input type="text" name="username" id="username" class="text" required /></p>';
    echo '<p><label for="password">Password:</label> <input type="password" name="password" id="password" class="text" required /></p>';
    echo '<p><input type="submit" value="Add User" class="button button-primary" /></p>';
    echo '</form>';

    // Existing Users Table
    echo '<h3>Existing Users</h3>';
    echo '<table class="tblSorter" cellpadding="0" cellspacing="1">';
    echo '<thead><tr><th>Username</th><th>Type</th><th>Actions</th></tr></thead>';
    echo '<tbody>';

    foreach ( $yourls_user_passwords as $user => $pass ) {
        echo '<tr>';
        echo '<td>' . multi_user_esc_html( $user ) . '</td>';

        // Determine type
        $is_stored = isset( $stored_users[$user] );
        echo '<td>' . ( $is_stored ? 'Database' : 'Config File' ) . '</td>';

        echo '<td>';
        if ( $is_stored ) {
            echo '<form method="post" action="" style="display:inline;">';
            echo '<input type="hidden" name="action" value="delete_user" />';
            echo '<input type="hidden" name="nonce" value="' . yourls_create_nonce( 'delete_user' ) . '" />';
            echo '<input type="hidden" name="username" value="' . multi_user_esc_html( $user ) . '" />';
            echo '<input type="submit" value="Delete" class="button button-red" onclick="return confirm(\'Are you sure?\');" />';
            echo '</form>';
        } else {
            echo '<em>Managed in config.php</em>';
        }
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
}

if ( !function_exists('multi_user_sanitize_text_field') ) {
    function multi_user_sanitize_text_field( $str ) {
        return strip_tags( trim( $str ) );
    }
}

if ( !function_exists('multi_user_esc_html') ) {
    function multi_user_esc_html( $text ) {
        return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
    }
}
