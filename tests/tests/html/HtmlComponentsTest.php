<?php

class HtmlComponentsTest extends PHPUnit\Framework\TestCase {
    public function test_get_html_components_infos() {
        $components = yourls_get_html_components('infos');
        $this->assertTrue($components['share']);
        $this->assertTrue($components['tabs']);
        $this->assertTrue($components['charts']);

        $this->assertFalse($components['insert']);
        $this->assertFalse($components['tablesorter']);
        $this->assertFalse($components['cal']);
    }

    public function test_get_html_components_bookmark() {
        $components = yourls_get_html_components('bookmark');
        $this->assertTrue($components['share']);
        $this->assertTrue($components['insert']);
        $this->assertTrue($components['tablesorter']);

        $this->assertFalse($components['tabs']);
        $this->assertFalse($components['cal']);
        $this->assertFalse($components['charts']);
    }

    public function test_get_html_components_index() {
        $components = yourls_get_html_components('index');
        $this->assertTrue($components['share']);
        $this->assertTrue($components['insert']);
        $this->assertTrue($components['tablesorter']);
        $this->assertTrue($components['cal']);

        $this->assertFalse($components['tabs']);
        $this->assertFalse($components['charts']);
    }

    public function test_get_html_components_plugins() {
        $components = yourls_get_html_components('plugins');
        $this->assertTrue($components['tablesorter']);

        $this->assertFalse($components['share']);
        $this->assertFalse($components['insert']);
        $this->assertFalse($components['tabs']);
        $this->assertFalse($components['cal']);
        $this->assertFalse($components['charts']);
    }

    public function test_get_html_components_tools() {
        $components = yourls_get_html_components('tools');
        $this->assertTrue($components['tablesorter']);

        $this->assertFalse($components['share']);
        $this->assertFalse($components['insert']);
        $this->assertFalse($components['tabs']);
        $this->assertFalse($components['cal']);
        $this->assertFalse($components['charts']);
    }

    public function test_get_html_components_unknown() {
        $components = yourls_get_html_components('unknown_context');
        $this->assertFalse($components['share']);
        $this->assertFalse($components['insert']);
        $this->assertFalse($components['tablesorter']);
        $this->assertFalse($components['tabs']);
        $this->assertFalse($components['cal']);
        $this->assertFalse($components['charts']);
    }
}
