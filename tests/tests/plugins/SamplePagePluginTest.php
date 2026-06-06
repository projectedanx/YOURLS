<?php

/**
 * Test for Sample Page Plugin
 */
#[\PHPUnit\Framework\Attributes\Group('plugins')]
class SamplePagePluginTest extends PHPUnit\Framework\TestCase {

    protected function setUp(): void {
        parent::setUp();
        require_once YOURLS_ABSPATH . '/user/plugins/sample-page/plugin.php';
    }

    protected function tearDown(): void {
        yourls_get_db()->set_plugin_pages(array());
        parent::tearDown();
    }

    public function test_add_page() {
        $ydb = yourls_get_db();

        ozh_yourls_samplepage_add_page();

        $pages = $ydb->get_plugin_pages();
        $this->assertArrayHasKey( 'sample_page', $pages );
        $this->assertSame( 'Sample Admin Page', $pages['sample_page']['title'] );
        $this->assertSame( 'ozh_yourls_samplepage_do_page', $pages['sample_page']['function'] );
        $this->assertSame( 'sample_page', $pages['sample_page']['slug'] );
    }

    public function test_update_option() {
        $action = 'sample_page';
        $_REQUEST['nonce'] = yourls_create_nonce($action);

        $_POST['test_option'] = '123';
        ozh_yourls_samplepage_update_option();
        $this->assertSame( 123, yourls_get_option( 'test_option' ) );

        $_POST['test_option'] = 'abc';
        ozh_yourls_samplepage_update_option();
        $this->assertSame( 0, yourls_get_option( 'test_option' ) );

        unset($_POST['test_option']);
        unset($_REQUEST['nonce']);
    }

    public function test_do_page() {
        ob_start();
        ozh_yourls_samplepage_do_page();
        $output = ob_get_clean();

        $this->assertStringContainsString('<h2>Sample Plugin Administration Page</h2>', $output);
        $this->assertStringContainsString('<input type="text" id="test_option" name="test_option" value="0" />', $output);
    }
}
