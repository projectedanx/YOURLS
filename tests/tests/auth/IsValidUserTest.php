<?php

#[\PHPUnit\Framework\Attributes\Group('auth')]
#[\PHPUnit\Framework\Attributes\Group('is_valid_user')]
class IsValidUserTest extends PHPUnit\Framework\TestCase {

    protected $backup_get;
    protected $backup_request;
    protected $backup_cookie;
    protected $backup_server;
    protected $backup_yourls_actions;
    protected $backup_yourls_filters;

    protected function setUp(): void {
        global $yourls_actions, $yourls_filters;
        $this->backup_get     = $_GET;
        $this->backup_request = $_REQUEST;
        $this->backup_cookie  = $_COOKIE;
        $this->backup_server  = $_SERVER;

        $this->backup_yourls_filters = $yourls_filters;
    }

    protected function tearDown(): void {
        global $yourls_actions, $yourls_filters;
        $_GET     = $this->backup_get;
        $_REQUEST = $this->backup_request;
        $_COOKIE  = $this->backup_cookie;
        $_SERVER  = $this->backup_server;

        $yourls_filters = $this->backup_yourls_filters;
    }

    public function test_shunt_is_valid_user() {
        yourls_add_filter( 'shunt_is_valid_user', function() {
            return 'shunted';
        } );

        $this->assertSame( 'shunted', yourls_is_valid_user() );
    }

    public function test_logout_request() {
        $_GET['action'] = 'logout';
        $_REQUEST['nonce'] = yourls_create_nonce('admin_logout', 'logout');

        $this->assertSame( yourls__( 'Logged out successfully' ), yourls_is_valid_user() );
    }

    public function test_login_failed_no_credentials() {
        $_REQUEST = [];
        $_GET = [];
        $_COOKIE = [];

        $this->assertSame( yourls__( 'Please log in' ), yourls_is_valid_user() );
    }

    public function test_login_failed_with_credentials() {
        $_REQUEST = ['username' => 'invalid', 'password' => 'invalid'];
        $_GET = [];
        $_COOKIE = [];

        yourls_remove_all_actions('pre_yourls_die');
        // bypass die from verify_nonce
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('I have died');
        yourls_add_action( 'pre_yourls_die', function() { throw new Exception( 'I have died' ); } );

        yourls_is_valid_user();
    }

    public function test_login_failed_with_credentials_with_nonce() {
        $_REQUEST = ['username' => 'invalid', 'password' => 'invalid', 'nonce' => yourls_create_nonce('admin_login')];
        $_GET = [];
        $_COOKIE = [];

        $this->assertSame( yourls__( 'Invalid username or password' ), yourls_is_valid_user() );
    }

    public function test_login_success_api() {
        $_REQUEST = [];
        $_GET = [];
        $_COOKIE = [];

        yourls_add_filter( 'is_API', 'yourls_return_true' );
        yourls_add_filter( 'is_valid_user', 'yourls_return_true' );

        $this->assertTrue( yourls_is_valid_user() );
    }

    public function test_login_success_normal_with_redirect() {
        if (!defined('YOURLS_USER')) {
            define('YOURLS_USER', 'test');
        }

        $_REQUEST = [
            'username' => 'test',
            'password' => 'test',
            'nonce' => yourls_create_nonce('admin_login')
        ];
        $_SERVER['REQUEST_URI'] = '/admin/index.php';

        yourls_add_filter( 'is_API', 'yourls_return_false' );
        yourls_add_filter( 'is_valid_user', 'yourls_return_true' );

        $this->assertSame( 3, yourls_is_valid_user() ); // 3 indicates redirect
    }

    public function test_login_success_normal_no_redirect() {
        if (!defined('YOURLS_USER')) {
            define('YOURLS_USER', 'test');
        }

        $_REQUEST = [
            'username' => 'test',
            'password' => 'test',
            'nonce' => yourls_create_nonce('admin_login')
        ];
        unset($_SERVER['REQUEST_URI']);

        yourls_add_filter( 'is_API', 'yourls_return_false' );
        yourls_add_filter( 'is_valid_user', 'yourls_return_true' );

        $this->assertTrue( yourls_is_valid_user() );
    }

    public function test_api_secure_timestamp() {
        $_REQUEST = [
            'timestamp' => '12345678',
            'signature' => 'valid_sig'
        ];

        yourls_add_filter( 'is_API', 'yourls_return_true' );

        $called = false;
        yourls_add_action( 'pre_login_signature_timestamp', function() use (&$called) {
            $called = true;
        } );

        yourls_is_valid_user();

        $this->assertTrue( $called );
    }

    public function test_api_secure_signature() {
        $_REQUEST = [
            'signature' => 'valid_sig'
        ];

        yourls_add_filter( 'is_API', 'yourls_return_true' );

        $called = false;
        yourls_add_action( 'pre_login_signature', function() use (&$called) {
            $called = true;
        } );

        yourls_is_valid_user();

        $this->assertTrue( $called );
    }

    public function test_login_username_password() {
        $_REQUEST = [
            'username' => 'test',
            'password' => 'test',
            'nonce' => yourls_create_nonce('admin_login')
        ];

        $called = false;
        yourls_add_action( 'pre_login_username_password', function() use (&$called) {
            $called = true;
        } );

        // We need to bypass the actual login execution as we don't have valid user mocked unless we override the return
        yourls_add_filter( 'is_valid_user', 'yourls_return_false' );

        yourls_is_valid_user();

        $this->assertTrue( $called );
    }

    public function test_login_cookie() {
        $_REQUEST = [];
        $_COOKIE = [ yourls_cookie_name() => 'some_cookie' ];

        yourls_add_filter( 'is_API', 'yourls_return_false' );

        $called = false;
        yourls_add_action( 'pre_login_cookie', function() use (&$called) {
            $called = true;
        } );

        yourls_is_valid_user();

        $this->assertTrue( $called );
    }
}
