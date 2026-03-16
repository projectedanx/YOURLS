<?php

/**
 * KSES functions. Most are not used in YOURLS, so there's few tests here.
 *
 * @since 0.1
 */
#[\PHPUnit\Framework\Attributes\Group('formatting')]
class KSESTest extends PHPUnit\Framework\TestCase {

    protected $entitynames, $protocols;

    protected function setUp(): void {
        global $yourls_allowedentitynames, $yourls_allowedprotocols;
        $this->entitynames = $yourls_allowedentitynames;
        $this->protocols   = $yourls_allowedprotocols;

        $yourls_allowedentitynames = $yourls_allowedprotocols = false;
    }

    protected function tearDown(): void {
        global $yourls_allowedentitynames, $yourls_allowedprotocols;
        $yourls_allowedentitynames = $this->entitynames;
        $yourls_allowedprotocols = $this->protocols;
    }

    /**
     * Meh
     *
     * @since 0.1
     */
    function test_sanitize_title() {
        global $yourls_allowedentitynames, $yourls_allowedprotocols;

        yourls_kses_init();

        // we should now have to populated arrays
        $this->assertTrue( is_array( $yourls_allowedentitynames ) && $yourls_allowedentitynames );
        $this->assertTrue( is_array( $yourls_allowedprotocols )   && $yourls_allowedprotocols );

        // currently unused in YOURLS, maybe in the future?
        $this->assertTrue( is_array( yourls_kses_allowed_tags() ) );
        $this->assertTrue( is_array( yourls_kses_allowed_tags_all() ) );
    }

    /**
     * Test yourls_kses_normalize_entities
     *
     * @since 1.6
     */
    public function test_normalize_entities() {
        yourls_kses_init();

        // The $yourls_allowedentitynames should now be populated
        global $yourls_allowedentitynames;
        $this->assertTrue( is_array( $yourls_allowedentitynames ) && !empty($yourls_allowedentitynames) );

        // Disarm all entities by converting & to &amp;
        $this->assertSame( '&amp;', yourls_kses_normalize_entities('&') );

        // Test allowed entities
        $this->assertSame( '&amp;', yourls_kses_normalize_entities('&amp;') );
        $this->assertSame( '&lt;', yourls_kses_normalize_entities('&lt;') );
        $this->assertSame( '&gt;', yourls_kses_normalize_entities('&gt;') );

        // Test unallowed entities
        $this->assertSame( '&amp;notanentity;', yourls_kses_normalize_entities('&notanentity;') );
        $this->assertSame( '&amp;nope;', yourls_kses_normalize_entities('&nope;') );

        // Test numeric entities (yourls_kses_normalize_entities2)
        // valid unicode numeric entity
        $this->assertSame( '&#038;', yourls_kses_normalize_entities('&#38;') );
        $this->assertSame( '&#038;', yourls_kses_normalize_entities('&#038;') );
        $this->assertSame( '&#038;', yourls_kses_normalize_entities('&amp;#038;') );

        // valid unicode hex entity (yourls_kses_normalize_entities3)
        $this->assertSame( '&#x26;', yourls_kses_normalize_entities('&#x26;') );
        $this->assertSame( '&#x26;', yourls_kses_normalize_entities('&amp;#x26;') );

        // invalid unicode numeric entity (0 is not valid unicode based on yourls_valid_unicode)
        // yourls_kses_normalize_entities2 and 3 return empty string if matches[1] is empty. Since `0` is treated as empty by `empty()`, it returns an empty string.
        // For &#0; -> empty('0') is true -> returns empty string
        $this->assertSame( '', yourls_kses_normalize_entities('&#0;') );
        $this->assertSame( '', yourls_kses_normalize_entities('&#x0;') );

        $this->assertSame( '&amp;#08;', yourls_kses_normalize_entities('&#08;') );
        $this->assertSame( '&amp;#x08;', yourls_kses_normalize_entities('&#x08;') );
    }
}
