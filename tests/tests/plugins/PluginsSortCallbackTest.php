<?php

#[\PHPUnit\Framework\Attributes\Group('plugins')]
class PluginsSortCallbackTest extends yut_TestCase {

    public function test_plugins_sort_callback_asc() {
        $plugin_a = ['Plugin Name' => 'AAA'];
        $plugin_b = ['Plugin Name' => 'BBB'];

        $this->assertSame( -1, yourls_plugins_sort_callback( $plugin_a, $plugin_b ) );
        $this->assertSame( 1, yourls_plugins_sort_callback( $plugin_b, $plugin_a ) );
        $this->assertSame( 0, yourls_plugins_sort_callback( $plugin_a, $plugin_a ) );
    }

    public function test_plugins_sort_callback_desc() {
        yourls_add_filter('plugins_sort_callback', function($in) {
            if ($in === 'ASC') {
                return 'DESC';
            }
            return $in;
        });

        $plugin_a = ['Plugin Name' => 'AAA'];
        $plugin_b = ['Plugin Name' => 'BBB'];

        $this->assertSame( 1, yourls_plugins_sort_callback( $plugin_a, $plugin_b ) );
        $this->assertSame( -1, yourls_plugins_sort_callback( $plugin_b, $plugin_a ) );
        $this->assertSame( 0, yourls_plugins_sort_callback( $plugin_a, $plugin_a ) );

        // Remove the filter for subsequent tests
        yourls_remove_all_filters('plugins_sort_callback');
    }

    public function test_plugins_sort_callback_missing_key() {
        $plugin_a = ['Plugin Name' => 'AAA'];
        $plugin_b = []; // Missing 'Plugin Name'

        $this->assertSame( 1, yourls_plugins_sort_callback( $plugin_a, $plugin_b ) ); // 'AAA' > ''
        $this->assertSame( -1, yourls_plugins_sort_callback( $plugin_b, $plugin_a ) ); // '' < 'AAA'

        $plugin_c = [];
        $this->assertSame( 0, yourls_plugins_sort_callback( $plugin_b, $plugin_c ) ); // '' == ''
    }

    public function test_plugins_sort_callback_custom_orderby() {
        yourls_add_filter('plugins_sort_callback', function($in) {
            if ($in === 'Plugin Name') {
                return 'Custom Key';
            }
            return $in;
        });

        $plugin_a = ['Plugin Name' => 'ZZZ', 'Custom Key' => 'AAA'];
        $plugin_b = ['Plugin Name' => 'AAA', 'Custom Key' => 'BBB'];

        // Should sort by 'Custom Key' ascending, so $plugin_a < $plugin_b
        $this->assertSame( -1, yourls_plugins_sort_callback( $plugin_a, $plugin_b ) );
        $this->assertSame( 1, yourls_plugins_sort_callback( $plugin_b, $plugin_a ) );

        yourls_remove_all_filters('plugins_sort_callback');
    }
}
