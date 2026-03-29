<?php

/**
 * Links
 */
#[\PHPUnit\Framework\Attributes\Group('links')]
#[\PHPUnit\Framework\Attributes\Group('idn')]
class LinkTest extends PHPUnit\Framework\TestCase {

    protected function tearDown(): void {
        yourls_remove_all_filters( 'get_yourls_site' );
        yourls_remove_all_filters( 'is_ssl' );
        yourls_remove_all_filters( 'site_url' );
    }

    /**
     * Check yourls_get_yourls_site() returns a string
     */
    public function test_yourls_site() {
        $this->assertIsString(yourls_get_yourls_site());

        $scheme = yourls_get_protocol( yourls_get_yourls_site() );
        $this->assertContains( $scheme, array( 'http://', 'https://' ), "yourls_get_yourls_site() isn't http(s)://" );
    }

    /**
     * Check yourls_link() gives a link
     */
    public function test_yourls_link() {
        $this->assertEquals( yourls_link('bonjour'), YOURLS_SITE . '/bonjour' );
    }

    /**
     * Check yourls_statlink() gives a link
     */
    public function test_yourls_statlink() {
        $this->assertEquals( yourls_statlink('hello'), YOURLS_SITE . '/hello+' );
    }

    /**
     * Check yourls_link() gives an IDN utf8 link
     */
    public function test_yourls_link_IDN() {
        yourls_add_filter( 'get_yourls_site', function() {return 'http://xn--hh-bjab.com';} );
        $this->assertEquals( 'http://héhé.com/suicidal', yourls_link('suicidal') );
        $this->assertEquals( 'http://héhé.com/angels+', yourls_statlink('angels') );
    }



    /**
     * Check yourls_site_url() gives a link
     */
    public function test_yourls_site_url() {
        // no echo, no path
        $this->assertEquals( YOURLS_SITE, yourls_site_url(false) );

        // no echo, path
        // yourls_site_url calls yourls_get_relative_url($url) which strips YOURLS_SITE if it matches
        $this->assertEquals( YOURLS_SITE . '/bonjour', yourls_site_url(false, YOURLS_SITE . '/bonjour') );

        // if strict is used (default), and URL does not have site root, it strips nothing. So 'bonjour' returns 'bonjour'.
        // actually yourls_get_relative_url with strict returns empty string if the URL is not matching YOURLS_SITE.
        // so yourls_site_url(false, 'bonjour') returns YOURLS_SITE.
        $this->assertEquals( YOURLS_SITE, yourls_site_url(false, 'bonjour') );
    }

    /**
     * Check yourls_site_url() with echo
     */
    public function test_yourls_site_url_echo() {
        // echo, no path
        ob_start();
        yourls_site_url(true);
        $actual = ob_get_clean();
        $this->assertEquals( YOURLS_SITE, $actual );

        // echo, path
        ob_start();
        yourls_site_url(true, YOURLS_SITE . '/bonjour');
        $actual = ob_get_clean();
        $this->assertEquals( YOURLS_SITE . '/bonjour', $actual );
    }

    /**
     * Check yourls_site_url() with SSL
     */
    public function test_yourls_site_url_ssl() {
        yourls_add_filter('is_ssl', 'yourls_return_true');

        $site = yourls_get_yourls_site();
        $expected = yourls_set_url_scheme($site, 'https');
        $this->assertEquals( $expected, yourls_site_url(false) );

        $expected_with_path = yourls_set_url_scheme($site . '/bonjour', 'https');
        $this->assertEquals( $expected_with_path, yourls_site_url(false, YOURLS_SITE . '/bonjour') );
    }

    /**
     * Check yourls_site_url() with filter
     */
    public function test_yourls_site_url_filter() {
        yourls_add_filter('site_url', function($url) { return $url . '/filtered'; });
        $this->assertEquals( YOURLS_SITE . '/filtered', yourls_site_url(false) );
    }

}
