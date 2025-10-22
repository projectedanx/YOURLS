<?php
/**
 * YOURLS Authentication Functions
 *
 * This file handles all aspects of user authentication, including cookie-based
 * sessions, secure API access, and nonce verification. It is responsible for
 * verifying user credentials, managing login and logout processes, and
 * protecting against unauthorized access.
 *
 * @package YOURLS
 * @since 1.0
 */


/**
 * Requires authentication if the installation is private.
 *
 * @since 1.0
 * @return void
 */
function yourls_maybe_require_auth() {
    if( yourls_is_private() ) {
        yourls_do_action( 'require_auth' );
        require_once( YOURLS_INC.'/auth.php' );
    } else {
        yourls_do_action( 'require_no_auth' );
    }
}

/**
 * Checks if the current user is valid.
 *
 * @since 1.0
 * @return bool|string True if the user is valid, an error message otherwise.
 */
function yourls_is_valid_user() {
    // Allow plugins to short-circuit the whole function
    $pre = yourls_apply_filter( 'shunt_is_valid_user', null );
    if ( null !== $pre ) {
        return $pre;
    }

    // $unfiltered_valid : are credentials valid? Boolean value. It's "unfiltered" to allow plugins to eventually filter it.
    $unfiltered_valid = false;

    // Logout request
    if( isset( $_GET['action'] ) && $_GET['action'] == 'logout' && isset( $_REQUEST['nonce'] ) ) {
        // The logout nonce is associated to fake user 'logout' since at this point we don't know the real user
        yourls_verify_nonce('admin_logout', $_REQUEST['nonce'], 'logout');
        yourls_do_action( 'logout' );
        yourls_store_cookie( '' );
        return yourls__( 'Logged out successfully' );
    }

    // Check cookies or login request. Login form has precedence.

    yourls_do_action( 'pre_login' );

    // Determine auth method and check credentials
    if
        // API only: Secure (no login or pwd) and time limited token
        // ?timestamp=12345678&signature=md5(totoblah12345678)
        ( yourls_is_API() &&
          isset( $_REQUEST['timestamp'] ) && !empty($_REQUEST['timestamp'] ) &&
          isset( $_REQUEST['signature'] ) && !empty($_REQUEST['signature'] )
        )
        {
            yourls_do_action( 'pre_login_signature_timestamp' );
            $unfiltered_valid = yourls_check_signature_timestamp();
        }

    elseif
        // API only: Secure (no login or pwd)
        // ?signature=md5(totoblah)
        ( yourls_is_API() &&
          !isset( $_REQUEST['timestamp'] ) &&
          isset( $_REQUEST['signature'] ) && !empty( $_REQUEST['signature'] )
        )
        {
            yourls_do_action( 'pre_login_signature' );
            $unfiltered_valid = yourls_check_signature();
        }

    elseif
        // API or normal: login with username & pwd
        ( isset( $_REQUEST['username'] ) && isset( $_REQUEST['password'] )
          && !empty( $_REQUEST['username'] ) && !empty( $_REQUEST['password']  ) )
        {
            yourls_do_action( 'pre_login_username_password' );
            $unfiltered_valid = yourls_check_username_password();
        }

    elseif
        // Normal only: cookies
        ( !yourls_is_API() &&
          isset( $_COOKIE[ yourls_cookie_name() ] ) )
        {
            yourls_do_action( 'pre_login_cookie' );
            $unfiltered_valid = yourls_check_auth_cookie();
        }

    // Regardless of validity, allow plugins to filter the boolean and have final word
    $valid = yourls_apply_filter( 'is_valid_user', $unfiltered_valid );

    // Login for the win!
    if ( $valid ) {
        yourls_do_action( 'login' );

        // (Re)store encrypted cookie if needed
        if ( !yourls_is_API() ) {
            yourls_store_cookie( YOURLS_USER );

            // Login form : redirect to requested URL to avoid re-submitting the login form on page reload
            if( isset( $_REQUEST['username'] ) && isset( $_REQUEST['password'] ) && isset( $_SERVER['REQUEST_URI'] ) ) {
                // The return makes sure we exit this function before waiting for redirection.
                // See #3189 and note in yourls_redirect()
                return yourls_redirect( yourls_sanitize_url_safe($_SERVER['REQUEST_URI']) );
            }
        }

        // Login successful
        return true;
    }

    // Login failed
    yourls_do_action( 'login_failed' );

    if ( isset( $_REQUEST['username'] ) || isset( $_REQUEST['password'] ) ) {
        return yourls__( 'Invalid username or password' );
    } else {
        return yourls__( 'Please log in' );
    }
}

/**
 * Checks if a username and password are valid.
 *
 * @since 1.0
 * @return bool True if the username and password are valid, false otherwise.
 */
function yourls_check_username_password() {
    global $yourls_user_passwords;

    // If login form (not API), check for nonce
    if(!yourls_is_API()) {
        yourls_verify_nonce('admin_login');
    }

    if( isset( $yourls_user_passwords[ $_REQUEST['username'] ] ) && yourls_check_password_hash( $_REQUEST['username'], $_REQUEST['password'] ) ) {
        yourls_set_user( $_REQUEST['username'] );
        return true;
    }
    return false;
}

/**
 * Checks if a password matches a stored hash.
 *
 * @since 1.0
 * @param string $user               The user's username.
 * @param string $submitted_password The password to check.
 * @return bool True if the password is correct, false otherwise.
 */
function yourls_check_password_hash($user, $submitted_password ) {
    global $yourls_user_passwords;

    if( !isset( $yourls_user_passwords[ $user ] ) )
        return false;

    if ( yourls_has_phpass_password( $user ) ) {
        // Stored password is hashed
        list( , $hash ) = explode( ':', $yourls_user_passwords[ $user ] );
        $hash = str_replace( '!', '$', $hash );
        return ( yourls_phpass_check( $submitted_password, $hash ) );
    } else if( yourls_has_md5_password( $user ) ) {
        // Stored password is a salted md5 hash: "md5:<$r = rand(10000,99999)>:<md5($r.'thepassword')>"
        list( , $salt, ) = explode( ':', $yourls_user_passwords[ $user ] );
        return( $yourls_user_passwords[ $user ] == 'md5:'.$salt.':'.md5( $salt . $submitted_password ) );
    } else {
        // Password stored in clear text
        return( $yourls_user_passwords[ $user ] === $submitted_password );
    }
}

/**
 * Hashes all plaintext passwords in the configuration file.
 *
 * @since 1.7
 * @param string $config_file The path to the configuration file.
 * @return bool|string True on success, an error message on failure.
 */
function yourls_hash_passwords_now( $config_file ) {
    if( !is_readable( $config_file ) ) {
        yourls_debug_log( 'Cannot hash passwords: cannot read file ' . $config_file );
        return 'cannot read file'; // not sure that can actually happen...
    }

    if( !is_writable( $config_file ) ) {
        yourls_debug_log( 'Cannot hash passwords: cannot write file ' . $config_file );
        return 'cannot write file';
    }

    $yourls_user_passwords = [];
    // Include file to read value of $yourls_user_passwords
    // Temporary suppress error reporting to avoid notices about redeclared constants
    $errlevel = error_reporting();
    error_reporting( 0 );
    require $config_file;
    error_reporting( $errlevel );

    $configdata = file_get_contents( $config_file );

    if( $configdata == false ) {
        yourls_debug_log('Cannot hash passwords: file_get_contents() false with ' . $config_file);
        return 'could not read file';
    }

    $to_hash = 0; // keep track of number of passwords that need hashing
    foreach ( $yourls_user_passwords as $user => $password ) {
        // avoid "deprecated" warning when password is null -- see test case in tests/data/auth/preg_replace_problem.php
        $password ??= '';
        if ( !yourls_has_phpass_password( $user ) && !yourls_has_md5_password( $user ) ) {
            $to_hash++;
            $hash = yourls_phpass_hash( $password );
            // PHP would interpret $ as a variable, so replace it in storage.
            $hash = str_replace( '$', '!', $hash );
            $quotes = "'" . '"';
            $pattern = "/[$quotes]" . preg_quote( $user, '/' ) . "[$quotes]\s*=>\s*[$quotes]" . preg_quote( $password, '/' ) . "[$quotes]/";
            $replace = "'$user' => 'phpass:$hash' /* Password encrypted by YOURLS */ ";
            $count = 0;
            $configdata = preg_replace( $pattern, $replace, $configdata, -1, $count );
            // There should be exactly one replacement. Otherwise, fast fail.
            if ( $count != 1 ) {
                yourls_debug_log( "Problem with preg_replace for password hash of user $user" );
                return 'preg_replace problem';
            }
        }
    }

    if( $to_hash == 0 ) {
        yourls_debug_log('Cannot hash passwords: no password found in ' . $config_file);
        return 'no password found';
    }

    $success = file_put_contents( $config_file, $configdata );
    if ( $success === FALSE ) {
        yourls_debug_log( 'Failed writing to ' . $config_file );
        return 'could not write file';
    }

    yourls_debug_log('Successfully encrypted passwords in ' . basename($config_file));
    return true;
}

/**
 * Hashes a password using the phpass library.
 *
 * @since 1.7
 * @param string $password The password to hash.
 * @return string The hashed password.
 */
function yourls_phpass_hash( $password ) {
    /**
     * Filter for hashing algorithm. See https://www.php.net/manual/en/function.password-hash.php
     * Hashing algos are available if PHP was compiled with it.
     * PASSWORD_BCRYPT is always available.
     */
    $algo    = yourls_apply_filter('hash_algo', PASSWORD_BCRYPT);

    /**
     * Filter for hashing options. See https://www.php.net/manual/en/function.password-hash.php
     * A typical option for PASSWORD_BCRYPT would be ['cost' => <int in range 4-31> ]
     * We're leaving the options at default values, which means a cost of 10 for PASSWORD_BCRYPT.
     *
     * If willing to modify this, be warned about the computing time, as there is a 2^n factor.
     * See https://gist.github.com/ozh/65a75392b7cb254131cc55afd28de99b for examples.
     */
    $options = yourls_apply_filter('hash_options', [] );

    return password_hash($password, $algo, $options);
}

/**
 * Checks if a password matches a hash.
 *
 * @since 1.7
 * @param string $password The password to check.
 * @param string $hash     The hash to check against.
 * @return bool True if the password matches the hash, false otherwise.
 */
function yourls_phpass_check( $password, $hash ) {
    return password_verify($password, $hash);
}


/**
 * Checks if any passwords are stored in plaintext.
 *
 * @since 1.7
 * @return bool True if any passwords are in plaintext, false otherwise.
 */
function yourls_has_cleartext_passwords() {
    global $yourls_user_passwords;
    foreach ( $yourls_user_passwords as $user => $pwdata ) {
        if ( !yourls_has_md5_password( $user ) && !yourls_has_phpass_password( $user ) ) {
            return true;
        }
    }
    return false;
}

/**
 * Checks if a user's password is an MD5 hash.
 *
 * @since 1.7
 * @param string $user The user's username.
 * @return bool True if the password is an MD5 hash, false otherwise.
 */
function yourls_has_md5_password( $user ) {
    global $yourls_user_passwords;
    return(    isset( $yourls_user_passwords[ $user ] )
            && substr( $yourls_user_passwords[ $user ], 0, 4 ) == 'md5:'
            && strlen( $yourls_user_passwords[ $user ] ) == 42 // http://www.google.com/search?q=the+answer+to+life+the+universe+and+everything
           );
}

/**
 * Checks if a user's password is a phpass hash.
 *
 * @since 1.7
 * @param string $user The user's username.
 * @return bool True if the password is a phpass hash, false otherwise.
 */
function yourls_has_phpass_password( $user ) {
    global $yourls_user_passwords;
    return( isset( $yourls_user_passwords[ $user ] )
            && substr( $yourls_user_passwords[ $user ], 0, 7 ) == 'phpass:'
    );
}

/**
 * Checks if the authentication cookie is valid.
 *
 * @since 1.0
 * @return bool True if the cookie is valid, false otherwise.
 */
function yourls_check_auth_cookie() {
    global $yourls_user_passwords;
    foreach( $yourls_user_passwords as $valid_user => $valid_password ) {
        if ( yourls_cookie_value( $valid_user ) === $_COOKIE[ yourls_cookie_name() ] ) {
            yourls_set_user( $valid_user );
            return true;
        }
    }
    return false;
}

/**
 * Checks if a signature and timestamp are valid.
 *
 * @since 1.4.1
 * @return bool True if the signature and timestamp are valid, false otherwise.
 */
function yourls_check_signature_timestamp() {
    if(   !isset( $_REQUEST['signature'] ) OR empty( $_REQUEST['signature'] )
       OR !isset( $_REQUEST['timestamp'] ) OR empty( $_REQUEST['timestamp'] )
    ) {
        return false;
    }

    // Exit if the timestamp argument is outdated or invalid
    if( !yourls_check_timestamp( $_REQUEST['timestamp'] )) {
        return false;
    }

    // if there is a hash argument, make sure it's part of the availables algos
    $hash_function = isset($_REQUEST['hash']) ? (string)$_REQUEST['hash'] : 'md5';
    if( !in_array($hash_function, hash_algos()) ) {
        return false;
    }

    // Check signature & timestamp against all possible users
    global $yourls_user_passwords;
    foreach( $yourls_user_passwords as $valid_user => $valid_password ) {
        if (
            hash( $hash_function, $_REQUEST['timestamp'].yourls_auth_signature( $valid_user ) ) === $_REQUEST['signature']
            or
            hash( $hash_function, yourls_auth_signature( $valid_user ).$_REQUEST['timestamp'] ) === $_REQUEST['signature']
            ) {
            yourls_set_user( $valid_user );
            return true;
        }
    }

    // Signature doesn't match known user
    return false;
}

/**
 * Checks if a signature is valid.
 *
 * @since 1.4.1
 * @return bool True if the signature is valid, false otherwise.
 */
function yourls_check_signature() {
    if( !isset( $_REQUEST['signature'] ) OR empty( $_REQUEST['signature'] ) )
        return false;

    // Check signature against all possible users
    global $yourls_user_passwords;
    foreach( $yourls_user_passwords as $valid_user => $valid_password ) {
        if ( yourls_auth_signature( $valid_user ) === $_REQUEST['signature'] ) {
            yourls_set_user( $valid_user );
            return true;
        }
    }

    // Signature doesn't match known user
    return false;
}

/**
 * Generates a secret signature hash.
 *
 * @since 1.0
 * @param string|false $username The username to generate the signature for.
 * @return string The secret signature hash.
 */
function yourls_auth_signature( $username = false ) {
    if( !$username && defined('YOURLS_USER') ) {
        $username = YOURLS_USER;
    }
    return ( $username ? substr( yourls_salt( $username ), 0, 10 ) : 'Cannot generate auth signature: no username' );
}

/**
 * Checks if a timestamp is valid.
 *
 * @since 1.0
 * @param int $time The timestamp to check.
 * @return bool True if the timestamp is valid, false otherwise.
 */
function yourls_check_timestamp( $time ) {
    $now = time();
    // Allow timestamp to be a little in the future or the past -- see Issue 766
    return yourls_apply_filter( 'check_timestamp', abs( $now - (int)$time ) < yourls_get_nonce_life(), $time );
}

/**
 * Stores a cookie.
 *
 * @since 1.0
 * @param string $user The user to store the cookie for.
 * @return void
 */
function yourls_store_cookie( $user = '' ) {

    // No user will delete the cookie with a cookie time from the past
    if( !$user ) {
        $time = time() - 3600;
    } else {
        $time = time() + yourls_get_cookie_life();
    }

    $path     = yourls_apply_filter( 'setcookie_path',     '/' );
    $domain   = yourls_apply_filter( 'setcookie_domain',   parse_url( yourls_get_yourls_site(), PHP_URL_HOST ) );
    $secure   = yourls_apply_filter( 'setcookie_secure',   yourls_is_ssl() );
    $httponly = yourls_apply_filter( 'setcookie_httponly', true );

    // Some browsers refuse to store localhost cookie
    if ( $domain == 'localhost' )
        $domain = '';

    yourls_do_action( 'pre_setcookie', $user, $time, $path, $domain, $secure, $httponly );

    if ( !headers_sent( $filename, $linenum ) ) {
        yourls_setcookie( yourls_cookie_name(), yourls_cookie_value( $user ), $time, $path, $domain, $secure, $httponly );
    } else {
        // For some reason cookies were not stored: action to be able to debug that
        yourls_do_action( 'setcookie_failed', $user );
        yourls_debug_log( "Could not store cookie: headers already sent in $filename on line $linenum" );
    }
}

/**
 * Sets a cookie.
 *
 * @since 1.7.7
 * @param string $name     The name of the cookie.
 * @param string $value    The value of the cookie.
 * @param int    $expire   The time the cookie expires.
 * @param string $path     The path on the server in which the cookie will be available on.
 * @param string $domain   The (sub)domain that the cookie is available to.
 * @param bool   $secure   Whether the cookie should only be transmitted over a secure HTTPS connection.
 * @param bool   $httponly Whether the cookie will be made accessible only through the HTTP protocol.
 * @return bool True on success, false on failure.
 */
function yourls_setcookie($name, $value, $expire, $path, $domain, $secure, $httponly) {
    $samesite = yourls_apply_filter('setcookie_samesite', 'Lax' );

    return(setcookie($name, $value, array(
        'expires'  => $expire,
        'path'     => $path,
        'domain'   => $domain,
        'samesite' => $samesite,
        'secure'   => $secure,
        'httponly' => $httponly,
    )));
}

/**
 * Sets the current user.
 *
 * @since 1.0
 * @param string $user The username of the user.
 * @return void
 */
function yourls_set_user( $user ) {
    if( !defined( 'YOURLS_USER' ) )
        define( 'YOURLS_USER', $user );
}

/**
 * Gets the cookie life.
 *
 * @since 1.7.7
 * @return int The cookie life in seconds.
 */
function yourls_get_cookie_life() {
    return yourls_apply_filter( 'get_cookie_life', YOURLS_COOKIE_LIFE );
}

/**
 * Gets the nonce life.
 *
 * @since 1.7.7
 * @return int The nonce life in seconds.
 */
function yourls_get_nonce_life() {
    return yourls_apply_filter( 'get_nonce_life', YOURLS_NONCE_LIFE );
}

/**
 * Gets the cookie name.
 *
 * @since 1.7.1
 * @return string The cookie name.
 */
function yourls_cookie_name() {
    return yourls_apply_filter( 'cookie_name', 'yourls_' . yourls_salt( yourls_get_yourls_site() ) );
}

/**
 * Gets the cookie value.
 *
 * @since 1.7.7
 * @param string $user The username.
 * @return string The cookie value.
 */
function yourls_cookie_value( $user ) {
    return yourls_apply_filter( 'set_cookie_value', yourls_salt( $user ?? '' ), $user );
}

/**
 * Returns a nonce tick.
 *
 * @since 1.0
 * @return float The nonce tick.
 */
function yourls_tick() {
    return ceil( time() / yourls_get_nonce_life() );
}

/**
 * Hashes a string.
 *
 * @since 1.4.1
 * @param string $string The string to hash.
 * @return string The hashed string.
 */
function yourls_salt( $string ) {
    $salt = defined('YOURLS_COOKIEKEY') ? YOURLS_COOKIEKEY : md5(__FILE__) ;
    return yourls_apply_filter( 'yourls_salt', hash_hmac( yourls_hmac_algo(), $string,  $salt), $string );
}

/**
 * Gets the HMAC algorithm.
 *
 * @since 1.8.3
 * @return string The HMAC algorithm.
 */
function yourls_hmac_algo() {
    $algo = yourls_apply_filter( 'hmac_algo', 'sha256' );
    if( !in_array( $algo, hash_hmac_algos() ) ) {
        $algo = 'sha256';
    }
    return $algo;
}

/**
 * Creates a nonce.
 *
 * @since 1.0
 * @param string $action The action to create the nonce for.
 * @param string|false $user   The user to create the nonce for.
 * @return string The nonce.
 */
function yourls_create_nonce($action, $user = false ) {
    if( false === $user ) {
        $user = defined('YOURLS_USER') ? YOURLS_USER : '-1';
    }
    $tick = yourls_tick();
    $nonce = substr( yourls_salt($tick . $action . $user), 0, 10 );
    // Allow plugins to alter the nonce
    return yourls_apply_filter( 'create_nonce', $nonce, $action, $user );
}

/**
 * Creates a nonce field.
 *
 * @since 1.0
 * @param string       $action The action to create the nonce for.
 * @param string       $name   The name of the nonce field.
 * @param string|false $user   The user to create the nonce for.
 * @param bool         $echo   Whether to echo the nonce field.
 * @return string The nonce field.
 */
function yourls_nonce_field($action, $name = 'nonce', $user = false, $echo = true ) {
    $field = '<input type="hidden" id="'.$name.'" name="'.$name.'" value="'.yourls_create_nonce( $action, $user ).'" />';
    if( $echo )
        echo $field."\n";
    return $field;
}

/**
 * Adds a nonce to a URL.
 *
 * @since 1.0
 * @param string       $action The action to create the nonce for.
 * @param string|false $url    The URL to add the nonce to.
 * @param string       $name   The name of the nonce field.
 * @param string|false $user   The user to create the nonce for.
 * @return string The URL with the nonce added.
 */
function yourls_nonce_url($action, $url = false, $name = 'nonce', $user = false ) {
    $nonce = yourls_create_nonce( $action, $user );
    return yourls_add_query_arg( $name, $nonce, $url );
}

/**
 * Verifies a nonce.
 *
 * @since 1.0
 * @param string       $action The action the nonce was created for.
 * @param string|false $nonce  The nonce to verify.
 * @param string|false $user   The user the nonce was created for.
 * @param string       $return An error message to display if the nonce is invalid.
 * @return bool|void True if the nonce is valid, otherwise the function will die.
 */
function yourls_verify_nonce($action, $nonce = false, $user = false, $return = '' ) {
    // Get user
    if( false === $user ) {
        $user = defined('YOURLS_USER') ? YOURLS_USER : '-1';
    }

    // Get nonce value from $_REQUEST if not specified
    if( false === $nonce && isset( $_REQUEST['nonce'] ) ) {
        $nonce = $_REQUEST['nonce'];
    }

    // Allow plugins to short-circuit the rest of the function
    if (yourls_apply_filter( 'verify_nonce', false, $action, $nonce, $user, $return ) === true) {
        return true;
    }

    // What nonce should be
    $valid = yourls_create_nonce( $action, $user );

    if( $nonce === $valid ) {
        return true;
    } else {
        if( $return )
            die( $return );
        yourls_die( yourls__( 'Unauthorized action or expired link' ), yourls__( 'Error' ), 403 );
    }
}

/**
 * Checks if the user is authenticated via environment variables.
 *
 * @since 1.8.2
 * @return bool True if the user is authenticated via environment variables, false otherwise.
 */
function yourls_is_user_from_env() {
    return yourls_apply_filter('is_user_from_env', getenv('YOURLS_USER') && getenv('YOURLS_PASSWORD'));

}

/**
 * Checks if passwords should be hashed.
 *
 * @since 1.8.2
 * @return bool True if passwords should be hashed, false otherwise.
 */
function yourls_maybe_hash_passwords() {
    $hash = true;

    if ( !yourls_has_cleartext_passwords()
         OR (yourls_skip_password_hashing())
         OR (yourls_is_user_from_env())
    ) {
        $hash = false;
    }

    return yourls_apply_filter('maybe_hash_password', $hash );
}

/**
 * Checks if password hashing is skipped.
 *
 * @since 1.8.2
 * @return bool True if password hashing is skipped, false otherwise.
 */
function yourls_skip_password_hashing() {
    return yourls_apply_filter('skip_password_hashing', defined('YOURLS_NO_HASH_PASSWORD') && YOURLS_NO_HASH_PASSWORD);
}
