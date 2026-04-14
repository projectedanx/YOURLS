<?php

class HtmlTitleTest extends PHPUnit\Framework\TestCase {

    protected function tearDown(): void {
        yourls_remove_all_filters('html_title');
        parent::tearDown();
    }

    public function test_get_html_title_default() {
        $title = yourls_get_html_title('index', '');
        $expected = 'YOURLS &mdash; Your Own URL Shortener | ' . yourls_link();
        $this->assertSame($expected, $title);
    }

    public function test_get_html_title_login() {
        $title = yourls_get_html_title('login', '');
        $expected = 'Login &mdash; YOURLS &mdash; Your Own URL Shortener | ' . yourls_link();
        $this->assertSame($expected, $title);
    }

    public function test_get_html_title_with_title_in() {
        $title = yourls_get_html_title('index', 'My Title');
        $expected = 'My Title &laquo; YOURLS &mdash; Your Own URL Shortener | ' . yourls_link();
        $this->assertSame($expected, $title);
    }

    public function test_get_html_title_login_with_title_in() {
        $title = yourls_get_html_title('login', 'My Title');
        $expected = 'My Title &laquo; Login &mdash; YOURLS &mdash; Your Own URL Shortener | ' . yourls_link();
        $this->assertSame($expected, $title);
    }

    public function test_get_html_title_filter() {
        yourls_add_filter('html_title', function($title, $context) {
            return 'Custom Title ' . $context;
        });

        $title = yourls_get_html_title('index', '');
        $this->assertSame('Custom Title index', $title);
    }
}
