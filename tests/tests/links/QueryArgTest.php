<?php

/**
 * Links - Query arguments
 */
#[\PHPUnit\Framework\Attributes\Group('links')]
#[\PHPUnit\Framework\Attributes\Group('query_arg')]
class QueryArgTest extends PHPUnit\Framework\TestCase {

    protected $server_uri = '';

    protected function setUp(): void {
        parent::setUp();
        $this->server_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    }

    protected function tearDown(): void {
        $_SERVER['REQUEST_URI'] = $this->server_uri;
        parent::tearDown();
    }

    /**
     * Test yourls_add_query_arg()
     */
    public function test_yourls_add_query_arg() {
        // Single key/value
        $this->assertEquals( 'http://example.com/?hello=world', yourls_add_query_arg( 'hello', 'world', 'http://example.com/' ) );
        $this->assertEquals( 'http://example.com?hello=world', yourls_add_query_arg( 'hello', 'world', 'http://example.com' ) );

        // Single key/value with existing args
        $this->assertEquals( 'http://example.com/?foo=bar&hello=world', yourls_add_query_arg( 'hello', 'world', 'http://example.com/?foo=bar' ) );
        $this->assertEquals( 'http://example.com?foo=bar&hello=world', yourls_add_query_arg( 'hello', 'world', 'http://example.com?foo=bar' ) );

        // Single key/value replacing existing arg
        $this->assertEquals( 'http://example.com/?foo=world', yourls_add_query_arg( 'foo', 'world', 'http://example.com/?foo=bar' ) );

        // Multiple key/values
        $this->assertEquals( 'http://example.com/?foo=bar&hello=world', yourls_add_query_arg( ['foo' => 'bar', 'hello' => 'world'], 'http://example.com/' ) );

        // Multiple key/values with existing args
        $this->assertEquals( 'http://example.com/?existing=arg&foo=bar&hello=world', yourls_add_query_arg( ['foo' => 'bar', 'hello' => 'world'], 'http://example.com/?existing=arg' ) );

        // Multiple key/values replacing existing arg
        $this->assertEquals( 'http://example.com/?foo=newbar&hello=world', yourls_add_query_arg( ['foo' => 'newbar', 'hello' => 'world'], 'http://example.com/?foo=oldbar' ) );

        // Fragments
        $this->assertEquals( 'http://example.com/?hello=world#fragment', yourls_add_query_arg( 'hello', 'world', 'http://example.com/#fragment' ) );
        $this->assertEquals( 'http://example.com/?foo=bar&hello=world#fragment', yourls_add_query_arg( 'hello', 'world', 'http://example.com/?foo=bar#fragment' ) );

        // Encoding
        $this->assertEquals( 'http://example.com/?hello=world+hello', yourls_add_query_arg( 'hello', 'world hello', 'http://example.com/' ) );
    }

    /**
     * Test yourls_add_query_arg() with default REQUEST_URI
     */

    /**
     * Test yourls_add_query_arg() edge cases
     */
    public function test_yourls_add_query_arg_edge_cases() {
        // Handling of &amp;
        $this->assertEquals( 'http://example.com/?foo=bar&baz=qux&hello=world', yourls_add_query_arg( 'hello', 'world', 'http://example.com/?foo=bar&amp;baz=qux' ) );

        // Removal of arguments with false value
        $this->assertEquals( 'http://example.com/', yourls_add_query_arg( 'hello', false, 'http://example.com/?hello=world' ) );
        $this->assertEquals( 'http://example.com/?foo=bar', yourls_add_query_arg( 'hello', false, 'http://example.com/?foo=bar&hello=world' ) );

        // Empty string value handling (should not have '=' at the end)
        $this->assertEquals( 'http://example.com/?hello', yourls_add_query_arg( 'hello', '', 'http://example.com/' ) );
        $this->assertEquals( 'http://example.com/?foo=bar&hello', yourls_add_query_arg( 'hello', '', 'http://example.com/?foo=bar' ) );

        // URLs without '?' but with '='
        $this->assertEquals( 'foo=bar&hello=world', yourls_add_query_arg( 'hello', 'world', 'foo=bar' ) );
        $this->assertEquals( 'foo?hello=world', yourls_add_query_arg( 'hello', 'world', 'foo' ) );

        // URLs with protocol but no ? and with =
        $this->assertEquals( 'http://example.com/page=1?hello=world', yourls_add_query_arg( 'hello', 'world', 'http://example.com/page=1' ) );
    }
    public function test_yourls_add_query_arg_default() {
        $_SERVER['REQUEST_URI'] = '/test.php';

        $this->assertEquals( '/test.php?hello=world', yourls_add_query_arg( 'hello', 'world' ) );
        $this->assertEquals( '/test.php?hello=world', yourls_add_query_arg( ['hello' => 'world'] ) );

        $_SERVER['REQUEST_URI'] = '/test.php?foo=bar';
        $this->assertEquals( '/test.php?foo=bar&hello=world', yourls_add_query_arg( 'hello', 'world' ) );
    }

    /**
     * Test yourls_remove_query_arg()
     */
    public function test_yourls_remove_query_arg() {
        // Single arg
        $this->assertEquals( 'http://example.com/', yourls_remove_query_arg( 'hello', 'http://example.com/?hello=world' ) );
        $this->assertEquals( 'http://example.com', yourls_remove_query_arg( 'hello', 'http://example.com?hello=world' ) );

        // Single arg with multiple existing args
        $this->assertEquals( 'http://example.com/?foo=bar', yourls_remove_query_arg( 'hello', 'http://example.com/?foo=bar&hello=world' ) );
        $this->assertEquals( 'http://example.com/?hello=world', yourls_remove_query_arg( 'foo', 'http://example.com/?foo=bar&hello=world' ) );

        // Single arg not present
        $this->assertEquals( 'http://example.com/?foo=bar', yourls_remove_query_arg( 'hello', 'http://example.com/?foo=bar' ) );

        // Multiple args
        $this->assertEquals( 'http://example.com/', yourls_remove_query_arg( ['hello', 'foo'], 'http://example.com/?foo=bar&hello=world' ) );
        $this->assertEquals( 'http://example.com/?keep=me', yourls_remove_query_arg( ['hello', 'foo'], 'http://example.com/?foo=bar&keep=me&hello=world' ) );

        // Fragments
        $this->assertEquals( 'http://example.com/?#fragment', yourls_remove_query_arg( 'hello', 'http://example.com/?hello=world#fragment' ) );
        $this->assertEquals( 'http://example.com/?foo=bar#fragment', yourls_remove_query_arg( 'hello', 'http://example.com/?foo=bar&hello=world#fragment' ) );
    }

    /**
     * Test yourls_remove_query_arg() with default REQUEST_URI
     */
    public function test_yourls_remove_query_arg_default() {
        $_SERVER['REQUEST_URI'] = '/test.php?hello=world';
        $this->assertEquals( '/test.php', yourls_remove_query_arg( 'hello' ) );

        $_SERVER['REQUEST_URI'] = '/test.php?foo=bar&hello=world';
        $this->assertEquals( '/test.php?foo=bar', yourls_remove_query_arg( 'hello' ) );
        $this->assertEquals( '/test.php', yourls_remove_query_arg( ['foo', 'hello'] ) );
    }

}
