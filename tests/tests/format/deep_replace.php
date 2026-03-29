<?php

/**
 * Formatting functions
 *
 * @since 0.1
 */
class Format_Deep_Replace extends PHPUnit\Framework\TestCase {

    public function test_yourls_deep_replace() {
        $search = array('%0d', '%0a', '%0D', '%0A');

        // Test cases from WP's _deep_replace
        $this->assertEquals( 'testtest', yourls_deep_replace( $search, 'test%%0d0dtest' ) );
        $this->assertEquals( 'testtest', yourls_deep_replace( $search, 'test%%0a0atest' ) );
        $this->assertEquals( 'testtest', yourls_deep_replace( $search, 'test%%0D0Dtest' ) );
        $this->assertEquals( 'testtest', yourls_deep_replace( $search, 'test%%0A0Atest' ) );

        $this->assertEquals( 'testtest', yourls_deep_replace( $search, 'test%0dtest' ) );
        $this->assertEquals( 'testtest', yourls_deep_replace( $search, 'test%0atest' ) );
        $this->assertEquals( 'testtest', yourls_deep_replace( $search, 'test%0Dtest' ) );
        $this->assertEquals( 'testtest', yourls_deep_replace( $search, 'test%0Atest' ) );

        $this->assertEquals( 'testtest', yourls_deep_replace( $search, 'test%0d%0atest' ) );
        $this->assertEquals( 'testtest', yourls_deep_replace( $search, 'test%0d%0a%0D%0Atest' ) );

        // Other tests
        $this->assertEquals( 'testtest', yourls_deep_replace( 'a', 'testaatest' ) );
        $this->assertEquals( 'testtest', yourls_deep_replace( array('a', 'b'), 'testbaatest' ) );
        $this->assertEquals( 'testtest', yourls_deep_replace( array('ab', 'cd'), 'testacbdtest' ) );
    }

}
