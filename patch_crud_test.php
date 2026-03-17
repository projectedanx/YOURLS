<?php
$content = file_get_contents('tests/tests/shorturl/CRUDTest.php');

$search = <<<'SEARCH'
    #[\PHPUnit\Framework\Attributes\Depends('test_add_url')]
    public function test_is_shorturl( $keyword ) {
        $this->assertFalse( yourls_is_shorturl( rand_str() ) );
        $this->assertTrue( yourls_is_shorturl( $keyword ) );
        $this->assertTrue( yourls_is_shorturl( yourls_link( $keyword ) ) );
    }
SEARCH;

$replace = <<<'REPLACE'
    #[\PHPUnit\Framework\Attributes\Depends('test_add_url')]
    public function test_is_shorturl( $keyword ) {
        $this->assertFalse( yourls_is_shorturl( rand_str() ) );
        $this->assertTrue( yourls_is_shorturl( $keyword ) );
        $this->assertTrue( yourls_is_shorturl( yourls_link( $keyword ) ) );

        // stats suffixes
        $this->assertTrue( yourls_is_shorturl( $keyword . '+' ) );
        $this->assertTrue( yourls_is_shorturl( $keyword . '+all' ) );
        $this->assertTrue( yourls_is_shorturl( yourls_link( $keyword ) . '+' ) );
        $this->assertTrue( yourls_is_shorturl( yourls_link( $keyword ) . '+all' ) );

        // query strings
        $this->assertTrue( yourls_is_shorturl( $keyword . '?foo=bar' ) );
        $this->assertTrue( yourls_is_shorturl( yourls_link( $keyword ) . '?foo=bar' ) );
    }
REPLACE;

$new_content = str_replace($search, $replace, $content);

file_put_contents('tests/tests/shorturl/CRUDTest.php', $new_content);
