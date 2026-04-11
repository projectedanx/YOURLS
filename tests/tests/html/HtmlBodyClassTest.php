<?php

class HtmlBodyClassTest extends PHPUnit\Framework\TestCase {

    protected function setUp(): void {
        parent::setUp();
        // Backup the current user agent
        $this->original_user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }

    protected function tearDown(): void {
        // Restore the original user agent
        $_SERVER['HTTP_USER_AGENT'] = $this->original_user_agent;
        yourls_remove_all_filters('bodyclass');
        parent::tearDown();
    }

    public function test_get_html_bodyclass_mobile() {
        $_SERVER['HTTP_USER_AGENT'] = 'iphone';
        $bodyclass = yourls_get_html_bodyclass();
        $this->assertSame('mobile', $bodyclass);
    }

    public function test_get_html_bodyclass_desktop() {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36';
        $bodyclass = yourls_get_html_bodyclass();
        $this->assertSame('desktop', $bodyclass);
    }

    public function test_get_html_bodyclass_filter() {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36';
        yourls_add_filter('bodyclass', function($class) {
            return 'custom-class ' . $class;
        });

        $bodyclass = yourls_get_html_bodyclass();
        $this->assertSame('custom-class desktop', $bodyclass);
    }
}
