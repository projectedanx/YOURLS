<?php

/**
 * Format domain function tests.
 *
 * @since 0.1
 */
#[\PHPUnit\Framework\Attributes\Group('formatting')]
class GetDomainTest extends PHPUnit\Framework\TestCase {

    /**
     * List of URLs and their expected domains
     */
    public static function list_of_urls(): \Iterator
    {
        // format: input URL, expected without scheme, expected with scheme
        yield array( 'http://example.com', 'example.com', 'http://example.com' );
        yield array( 'https://example.com/path?foo=bar', 'example.com', 'https://example.com' );
        yield array( 'example.com', 'example.com', 'example.com' );
        yield array( 'example.com/path', 'example.com/path', 'example.com/path' );
        yield array( 'http://www.example.com:8080/foo', 'www.example.com', 'http://www.example.com' );
        yield array( 'mailto:user@example.com', 'user@example.com', 'mailto://user@example.com' );
        yield array( 'ftp://ftp.example.com/file', 'ftp.example.com', 'ftp://ftp.example.com' );
        yield array( 'http:///example.com', '', '' ); // parse_url returns false or empty for this
        yield array( '', '', '' );
        yield array( '://', '://', '://' ); // malformed parse_url fallback to path
    }

    /**
     * Test yourls_get_domain without scheme
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('list_of_urls')]
    public function test_get_domain_without_scheme( $url, $expected_domain, $expected_with_scheme ) {
        $this->assertSame( $expected_domain, yourls_get_domain( $url, false ) );
    }

    /**
     * Test yourls_get_domain with scheme
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('list_of_urls')]
    public function test_get_domain_with_scheme( $url, $expected_domain, $expected_with_scheme ) {
        $this->assertSame( $expected_with_scheme, yourls_get_domain( $url, true ) );
    }
}
