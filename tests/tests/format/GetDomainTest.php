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

        // Add additional edge cases
        yield array( 'http://192.168.1.1/foo', '192.168.1.1', 'http://192.168.1.1' ); // IPv4
        yield array( 'http://[2001:db8::1]/', '[2001:db8::1]', 'http://[2001:db8::1]' ); // IPv6
        yield array( '//example.com/foo', 'example.com', 'example.com' ); // Protocol-relative
        yield array( 'http://xn--bcher-kva.example/', 'xn--bcher-kva.example', 'http://xn--bcher-kva.example' ); // IDN domain
        yield array( 'http://example.com/path?foo=bar#baz', 'example.com', 'http://example.com' ); // Queries and fragments
        yield array( 'example.com:8080/foo', 'example.com', 'example.com' ); // Path-like string with port but no scheme
        yield array( 'http://user:pass@example.com/foo', 'example.com', 'http://example.com' ); // HTTP Basic Auth
        yield array( 'http://sub.sub.example.co.uk', 'sub.sub.example.co.uk', 'http://sub.sub.example.co.uk' ); // Subdomains
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
