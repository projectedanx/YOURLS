<?php

#[\PHPUnit\Framework\Attributes\Group('links')]
#[\PHPUnit\Framework\Attributes\Group('admin')]
class AdminUrlTest extends PHPUnit\Framework\TestCase {

    protected function tearDown(): void {
        yourls_remove_all_filters('is_ssl');
        yourls_remove_all_filters('needs_ssl');
        yourls_remove_all_filters('admin_url');
        yourls_remove_all_filters('get_yourls_site');
    }

    public function test_yourls_admin_url_base() {
        $this->assertEquals( YOURLS_SITE . '/admin/', yourls_admin_url() );
    }

    public function test_yourls_admin_url_with_page() {
        $this->assertEquals( YOURLS_SITE . '/admin/index.php', yourls_admin_url('index.php') );
        $this->assertEquals( YOURLS_SITE . '/admin/tools.php', yourls_admin_url('tools.php') );
    }

    public function test_yourls_admin_url_with_ssl() {
        yourls_add_filter( 'is_ssl', 'yourls_return_true' );

        $expected = str_replace( 'http://', 'https://', YOURLS_SITE ) . '/admin/';
        $this->assertEquals( $expected, yourls_admin_url() );

        $expected_page = str_replace( 'http://', 'https://', YOURLS_SITE ) . '/admin/plugins.php';
        $this->assertEquals( $expected_page, yourls_admin_url('plugins.php') );
    }

    public function test_yourls_admin_url_with_needs_ssl() {
        yourls_add_filter( 'needs_ssl', 'yourls_return_true' );

        $expected = str_replace( 'http://', 'https://', YOURLS_SITE ) . '/admin/';
        $this->assertEquals( $expected, yourls_admin_url() );
    }

    public function test_yourls_admin_url_filter() {
        yourls_add_filter( 'admin_url', function($url, $page) {
            return $url . '?filtered=true';
        } );

        $this->assertEquals( YOURLS_SITE . '/admin/?filtered=true', yourls_admin_url() );
    }

    public function test_yourls_admin_url_with_different_site_url() {
        yourls_add_filter( 'get_yourls_site', function() {
            return 'http://different.com';
        } );

        $this->assertEquals( 'http://different.com/admin/', yourls_admin_url() );
    }
}
