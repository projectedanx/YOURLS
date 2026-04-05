<?php

/**
 * Deep functions
 */
#[\PHPUnit\Framework\Attributes\Group('links')]
class DeepTest extends PHPUnit\Framework\TestCase {

    /**
     * Test yourls_urlencode_deep
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('deep_data')]
    public function test_urlencode_deep( $data, $expected ) {
        $this->assertSame( $expected, yourls_urlencode_deep( $data ) );
    }

    public static function deep_data(): \Iterator
    {
        yield array( 'hello world', 'hello+world' );
        yield array( 'hello world!', 'hello+world%21' );
        yield array(
            array( 'hello world', 'hello world!' ),
            array( 'hello+world', 'hello+world%21' )
        );
        yield array(
            array( 'a' => 'b c', 'd' => array( 'e' => 'f g' ) ),
            array( 'a' => 'b+c', 'd' => array( 'e' => 'f+g' ) )
        );
        // Test with empty array
        yield array( array(), array() );
        // Test with nested empty array
        yield array( array( 'a' => array() ), array( 'a' => array() ) );
        // Test with mix of empty and non-empty
        yield array( array( 'a' => '', 'b' => ' ' ), array( 'a' => '', 'b' => '+' ) );
    }

    /**
     * Test yourls_add_query_arg
     */
    public function test_add_query_arg() {
        $url = 'http://example.com/test.php?a=b';
        $this->assertEquals( 'http://example.com/test.php?a=b&c=d', yourls_add_query_arg( 'c', 'd', $url ) );
        $this->assertEquals( 'http://example.com/test.php?a=b&c=d&e=f', yourls_add_query_arg( array( 'c' => 'd', 'e' => 'f' ), $url ) );

        // Test with special characters
        $this->assertEquals( 'http://example.com/test.php?a=b&c=d+e', yourls_add_query_arg( 'c', 'd e', $url ) );

        // Test with already encoded parameters in the URL
        $url2 = 'http://example.com/test.php?a=b+c';
        // yourls_add_query_arg will re-encode existing params using yourls_urlencode_deep
        // parse_str('a=b+c') results in $qs['a'] = 'b c'
        // yourls_urlencode_deep($qs) results in $qs['a'] = 'b+c'
        $this->assertEquals( 'http://example.com/test.php?a=b%2Bc&d=e', yourls_add_query_arg( 'd', 'e', $url2 ) );
    }

    /**
     * Test yourls_remove_query_arg
     */
    public function test_remove_query_arg() {
        $url = 'http://example.com/test.php?a=b&c=d&e=f';
        $this->assertEquals( 'http://example.com/test.php?c=d&e=f', yourls_remove_query_arg( 'a', $url ) );
        $this->assertEquals( 'http://example.com/test.php?e=f', yourls_remove_query_arg( array( 'a', 'c' ), $url ) );
    }
}
