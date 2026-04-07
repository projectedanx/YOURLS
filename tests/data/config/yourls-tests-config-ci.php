<?php
/**
 * YOURLS Config for CI
 */

// Polyfill environment variables for PHPUnit 10+ child processes
$env_file = __DIR__ . '/.env.test.php';
if (getenv('GITHUB_WORKSPACE') !== false) {
    file_put_contents($env_file, '<?php return ' . var_export([
        'GITHUB_WORKSPACE' => getenv('GITHUB_WORKSPACE'),
        'DB_PORT'          => getenv('DB_PORT'),
        'YOURLS_DB_USER'   => getenv('YOURLS_DB_USER'),
        'YOURLS_DB_PASS'   => getenv('YOURLS_DB_PASS'),
        'YOURLS_DB_NAME'   => getenv('YOURLS_DB_NAME'),
        'YOURLS_DB_HOST'   => getenv('YOURLS_DB_HOST'),
    ], true) . ';');
} elseif (file_exists($env_file)) {
    $env = require $env_file;
    foreach ((array)$env as $k => $v) {
        if ($v !== false) putenv("$k=$v");
    }
}

define('YOURLS_TESTS_CI', getenv('CI') || false);
define('YOURLS_ABSPATH', getenv('GITHUB_WORKSPACE'));

define( 'YOURLS_SITE', 'http://localhost/YOURLS' );

/*** MySQL settings */
define( 'YOURLS_DB_USER', getenv('YOURLS_DB_USER') !== false ? getenv('YOURLS_DB_USER') : 'root' );
define( 'YOURLS_DB_PASS', getenv('YOURLS_DB_PASS') !== false ? getenv('YOURLS_DB_PASS') : '' );
define( 'YOURLS_DB_NAME', getenv('YOURLS_DB_NAME') !== false ? getenv('YOURLS_DB_NAME') : 'yourls_tests' );

$yourls_db_host = getenv('YOURLS_DB_HOST') !== false ? getenv('YOURLS_DB_HOST') : '127.0.0.1';
$yourls_db_port = getenv('DB_PORT');
if ( $yourls_db_port && strpos($yourls_db_host, ':') === false ) {
    $yourls_db_host .= ':' . $yourls_db_port;
}
define( 'YOURLS_DB_HOST', $yourls_db_host );

/*** Site options */
define( 'YOURLS_PHP_BIN', 'php' );

/*** Standard YOURLS config. */

define('YOURLS_HOURS_OFFSET', 5);
define('YOURLS_UNIQUE_URLS',  true);
define('YOURLS_PRIVATE',  true);
define('YOURLS_COOKIEKEY',  'I &hearts; unit tests');
define('YOURLS_URL_CONVERT',  62);
define('YOURLS_DB_PREFIX',  'yourls_');
define('YOURLS_FLOOD_DELAY_SECONDS',  0);
define('YOURLS_FLOOD_IP_WHITELIST',  '');
define('YOURLS_LANG',  'fr_FR'); // locale of a sample translation file in the data dir
define('YOURLS_DEBUG', true);

$yourls_reserved_URL = array(
    'porn', 'sex', 'nigger', 'fuck', 'cunt', 'dick',
);

$yourls_user_passwords = array(
    'yourls'  => 'secret-ci-test',
    'clear'   => 'somepassword',
    'md5'     => 'md5:12373:e52e4488f79a740bd341f229e3c163c8',                          // password: '3cd6944201fa7bbc5e0fe852e36b1096' with md5 and salt
    'phpass'  => 'phpass:!2a!08!T1ptMlBSxu7g3odpbUXgd.9wbKvg8k7cJt.HbwSqUNrlLPudWnf/6', // password: '3cd6944201fa7bbc5e0fe852e36b1096' with old PHPass library
    'phpass2' => 'phpass:$2a$08$gt2bnpfUyuCX3hrp0RPOieFR1RwBnLsMzpq/NvPXwCdV3LqI3RGYi', // password: also '3cd6944201fa7bbc5e0fe852e36b1096' with old PHPass lib but without YOURLS internal char substitution
    'phpass3' => 'phpass:!2y!10!.FjK.vQR0JVivkMwckiiIesFUFhtMxX/f9pes.i/ccp/W0IuUSxPW', // password: also '3cd6944201fa7bbc5e0fe852e36b1096' hashed with password_hash
    'phpass4' => 'phpass:$2y$10$KPP/sv7pv0JL2GwcixNBfuXRPElC4KxQUgetqBfCboB.q30yKwKG6', // password: also '3cd6944201fa7bbc5e0fe852e36b1096' hashed with password_hash but without YOURLS internal char substitution
    '1994'    => '@$*',
    'special' => 'lol .\+*?[^]$(){}=!<>|:-/',
    'quote1'  => '"ahah"',
    'quote2'  => "'ahah'",
    'utf8fun' => 'أنا أحب النقانق',
);
