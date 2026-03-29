<?php

/**
 * URL encode deep tests
 *
 * @group links
 */
#[\PHPUnit\Framework\Attributes\Group('links')]
class UrlencodeDeepTest extends PHPUnit\Framework\TestCase {

    /**
     * Test yourls_urlencode_deep with string
     */
    public function test_urlencode_deep_string() {
        $this->assertSame( 'hello', yourls_urlencode_deep( 'hello' ) );
        $this->assertSame( 'hello+world', yourls_urlencode_deep( 'hello world' ) );
        $this->assertSame( 'hello%21%40%23%24%25%5E%26%2A%28%29_%2B', yourls_urlencode_deep( 'hello!@#$%^&*()_+' ) );
    }

    /**
     * Test yourls_urlencode_deep with array
     */
    public function test_urlencode_deep_array() {
        $input = array( 'hello world', 'foo bar' );
        $expected = array( 'hello+world', 'foo+bar' );
        $this->assertSame( $expected, yourls_urlencode_deep( $input ) );
    }

    /**
     * Test yourls_urlencode_deep with nested array
     */
    public function test_urlencode_deep_nested_array() {
        $input = array( 'hello world', array( 'nested string', 'another nested' ) );
        $expected = array( 'hello+world', array( 'nested+string', 'another+nested' ) );
        $this->assertSame( $expected, yourls_urlencode_deep( $input ) );
    }

    /**
     * Test yourls_urlencode_deep with deeply nested array
     */
    public function test_urlencode_deep_deeply_nested_array() {
        $input = array( 'a' => array( 'b' => array( 'c' => 'd e' ) ) );
        $expected = array( 'a' => array( 'b' => array( 'c' => 'd+e' ) ) );
        $this->assertSame( $expected, yourls_urlencode_deep( $input ) );
    }

}
