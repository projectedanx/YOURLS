<?php

#[\PHPUnit\Framework\Attributes\Group('links')]
class FaviconTest extends PHPUnit\Framework\TestCase {

    private $testFile = null;

    protected function tearDown(): void {
        if ($this->testFile && file_exists($this->testFile)) {
            unlink($this->testFile);
        }
        yourls_remove_all_filters('get_favicon_url');
        parent::tearDown();
    }

    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_default_favicon() {
        $expected = yourls_site_url(false) . '/images/favicon.svg';

        $this->expectOutputString($expected);
        $url = yourls_get_yourls_favicon_url();
        $this->assertEquals($expected, $url);
    }

    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_default_favicon_no_echo() {
        $expected = yourls_site_url(false) . '/images/favicon.svg';

        $this->expectOutputString('');
        $url = yourls_get_yourls_favicon_url(false);
        $this->assertEquals($expected, $url);
    }

    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_custom_favicon_gif() {
        $ext = 'gif';
        $this->testFile = YOURLS_USERDIR . '/favicon.' . $ext;
        touch($this->testFile);

        $expected = yourls_site_url(false, YOURLS_USERURL . '/favicon.' . $ext);

        $this->expectOutputString($expected);
        $url = yourls_get_yourls_favicon_url();
        $this->assertEquals($expected, $url);
    }

    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_custom_favicon_ico() {
        $ext = 'ico';
        $this->testFile = YOURLS_USERDIR . '/favicon.' . $ext;
        touch($this->testFile);

        $expected = yourls_site_url(false, YOURLS_USERURL . '/favicon.' . $ext);

        $this->expectOutputString($expected);
        $url = yourls_get_yourls_favicon_url();
        $this->assertEquals($expected, $url);
    }

    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_custom_favicon_png() {
        $ext = 'png';
        $this->testFile = YOURLS_USERDIR . '/favicon.' . $ext;
        touch($this->testFile);

        $expected = yourls_site_url(false, YOURLS_USERURL . '/favicon.' . $ext);

        $this->expectOutputString($expected);
        $url = yourls_get_yourls_favicon_url();
        $this->assertEquals($expected, $url);
    }

    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_custom_favicon_jpg() {
        $ext = 'jpg';
        $this->testFile = YOURLS_USERDIR . '/favicon.' . $ext;
        touch($this->testFile);

        $expected = yourls_site_url(false, YOURLS_USERURL . '/favicon.' . $ext);

        $this->expectOutputString($expected);
        $url = yourls_get_yourls_favicon_url();
        $this->assertEquals($expected, $url);
    }

    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_custom_favicon_svg() {
        $ext = 'svg';
        $this->testFile = YOURLS_USERDIR . '/favicon.' . $ext;
        touch($this->testFile);

        $expected = yourls_site_url(false, YOURLS_USERURL . '/favicon.' . $ext);

        $this->expectOutputString($expected);
        $url = yourls_get_yourls_favicon_url();
        $this->assertEquals($expected, $url);
    }

    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_static_cache_with_echo() {
        $expected = yourls_site_url(false) . '/images/favicon.svg';

        // First call caches it and echoes
        $this->expectOutputString($expected . $expected);
        $url1 = yourls_get_yourls_favicon_url(true);
        $this->assertEquals($expected, $url1);

        // Second call uses cache and echoes again
        $url2 = yourls_get_yourls_favicon_url(true);
        $this->assertEquals($expected, $url2);
    }

    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_static_cache_no_echo() {
        $expected = yourls_site_url(false) . '/images/favicon.svg';

        // First call caches it, no echo
        $this->expectOutputString('');
        $url1 = yourls_get_yourls_favicon_url(false);
        $this->assertEquals($expected, $url1);

        // Second call uses cache, no echo
        $url2 = yourls_get_yourls_favicon_url(false);
        $this->assertEquals($expected, $url2);
    }

    #[\PHPUnit\Framework\Attributes\RunInSeparateProcess]
    #[\PHPUnit\Framework\Attributes\PreserveGlobalState(false)]
    public function test_favicon_filter() {
        $filtered = 'http://example.com/filtered.png';

        yourls_add_filter('get_favicon_url', function() use ($filtered) {
            return $filtered;
        });

        $this->expectOutputString($filtered);
        $url = yourls_get_yourls_favicon_url();
        $this->assertEquals($filtered, $url);
    }
}
