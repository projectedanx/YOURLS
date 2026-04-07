<?php

/**
 * SSL Utility functions
 *
 * @since 0.1
 */
#[\PHPUnit\Framework\Attributes\Group('utilities')]
class NeedsSSLTest extends PHPUnit\Framework\TestCase {

    protected function tearDown(): void {
        yourls_remove_all_filters( 'needs_ssl' );
    }

    /**
     * Check that yourls_needs_ssl() behaves as expected
     *
     * @since 0.1
     */
    //#[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    //#[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_yourls_needs_ssl() {
        if (!defined('YOURLS_ADMIN_SSL')) {
            define('YOURLS_ADMIN_SSL', false);
        }
        $this->assertFalse( yourls_needs_ssl() );

        yourls_add_filter( 'needs_ssl', 'yourls_return_true' );
        $this->assertTrue( yourls_needs_ssl() );

        yourls_add_filter( 'needs_ssl', 'yourls_return_false' );
        $this->assertFalse( yourls_needs_ssl() );
    }
}
