<?php

#[\PHPUnit\Framework\Attributes\Group('plugins')]
class MultiUserPluginTest extends PHPUnit\Framework\TestCase {

    protected function setUp(): void {
        parent::setUp();
        require_once dirname( dirname( dirname( __DIR__ ) ) ) . '/user/plugins/multi-user/plugin.php';
    }

    protected function tearDown(): void {
        parent::tearDown();
        // unregister plugin pages
        yourls_get_db()->set_plugin_pages(array());
    }

    public function test_multi_user_add_page() {
        $ydb = yourls_get_db();

        // no plugin page registered initially
        $this->assertEmpty($ydb->get_plugin_pages());

        // call the function to register the page
        multi_user_add_page();

        // verify the page is registered
        $pages = $ydb->get_plugin_pages();
        $this->assertCount(1, $pages);
        $this->assertArrayHasKey('multi-user', $pages);

        $expected = array(
            'slug' => 'multi-user',
            'title' => 'Multi-User Management',
            'function' => 'multi_user_display_page',
        );
        $this->assertSame($expected, $pages['multi-user']);
    }
}
