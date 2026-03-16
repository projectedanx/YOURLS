<?php

/**
 * Links
 */
#[\PHPUnit\Framework\Attributes\Group('links')]
class QueryArgTest extends PHPUnit\Framework\TestCase {

    public function test_add_query_arg_simple() {
        $url = 'http://example.com';
        $this->assertEquals( 'http://example.com?key=value', yourls_add_query_arg( 'key', 'value', $url ) );
    }

    public function test_add_query_arg_appended() {
        $url = 'http://example.com?existing=1';
        $this->assertEquals( 'http://example.com?existing=1&key=value', yourls_add_query_arg( 'key', 'value', $url ) );
    }

    public function test_add_query_arg_overwrite() {
        $url = 'http://example.com?key=oldvalue';
        $this->assertEquals( 'http://example.com?key=value', yourls_add_query_arg( 'key', 'value', $url ) );
    }

    public function test_add_query_arg_array() {
        $url = 'http://example.com';
        $args = array( 'key1' => 'value1', 'key2' => 'value2' );
        $this->assertEquals( 'http://example.com?key1=value1&key2=value2', yourls_add_query_arg( $args, $url ) );
    }

    public function test_add_query_arg_remove() {
        $url = 'http://example.com?key=value&keep=this';
        $this->assertEquals( 'http://example.com?keep=this', yourls_add_query_arg( 'key', false, $url ) );
    }

    public function test_add_query_arg_fragment() {
        $url = 'http://example.com?existing=1#frag';
        $this->assertEquals( 'http://example.com?existing=1&key=value#frag', yourls_add_query_arg( 'key', 'value', $url ) );
    }

    public function test_add_query_arg_protocol_less() {
        $url = 'example.com';
        $this->assertEquals( 'example.com?key=value', yourls_add_query_arg( 'key', 'value', $url ) );
    }

    public function test_add_query_arg_request_uri() {
        $old_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;
        $_SERVER['REQUEST_URI'] = '/path?existing=1';
        $this->assertEquals( '/path?existing=1&key=value', yourls_add_query_arg( 'key', 'value' ) );
        if ($old_uri !== null) {
            $_SERVER['REQUEST_URI'] = $old_uri;
        } else {
            unset($_SERVER['REQUEST_URI']);
        }
    }

    public function test_add_query_arg_request_uri_array() {
        $old_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;
        $_SERVER['REQUEST_URI'] = '/path?existing=1';
        $args = array( 'key1' => 'value1', 'key2' => 'value2' );
        $this->assertEquals( '/path?existing=1&key1=value1&key2=value2', yourls_add_query_arg( $args ) );
        if ($old_uri !== null) {
            $_SERVER['REQUEST_URI'] = $old_uri;
        } else {
            unset($_SERVER['REQUEST_URI']);
        }
    }

    public function test_add_query_arg_multiple_remove() {
        $url = 'http://example.com?key1=1&key2=2&key3=3';
        $args = array( 'key1' => false, 'key2' => false );
        $this->assertEquals( 'http://example.com?key3=3', yourls_add_query_arg( $args, $url ) );
    }

    public function test_add_query_arg_url_encode() {
        $url = 'http://example.com';
        $this->assertEquals( 'http://example.com?key=value+space', yourls_add_query_arg( 'key', 'value space', $url ) );
    }
}
