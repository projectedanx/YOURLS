<?php

/**
 * yourls_get_db_stats() function tests
 *
 * @group stats
 */
class GetDBStatsTest extends PHPUnit\Framework\TestCase {

    protected function tearDown(): void {
        global $yourls_filters;
        unset( $yourls_filters['get_db_stats'] );
    }

    /**
     * Test basic stats retrieval
     */
    public function test_yourls_get_db_stats_basic() {
        $keyword1 = rand_str();
        $keyword2 = rand_str();
        yourls_add_new_link( 'http://example.com/1', $keyword1, 'Title 1' );
        yourls_add_new_link( 'http://example.com/2', $keyword2, 'Title 2' );

        $stats = yourls_get_db_stats();
        $this->assertIsArray( $stats );
        $this->assertArrayHasKey( 'total_links', $stats );
        $this->assertArrayHasKey( 'total_clicks', $stats );

        $initial_links = $stats['total_links'];
        $initial_clicks = $stats['total_clicks'];

        // Update clicks - yourls_update_clicks accepts a second argument for the absolute click count
        yourls_update_clicks( $keyword1, 10 );
        yourls_update_clicks( $keyword2, 5 );

        $stats = yourls_get_db_stats();
        $this->assertSame( $initial_links, $stats['total_links'] );
        $this->assertSame( $initial_clicks + 15, (int)$stats['total_clicks'] );
    }

    /**
     * Test stats retrieval with WHERE clause
     */
    public function test_yourls_get_db_stats_with_where() {
        $keyword = rand_str();
        yourls_add_new_link( 'http://example.org/' . $keyword, $keyword, 'Title' );
        yourls_update_clicks( $keyword, 42 );

        $where = [
            'sql'   => ' AND keyword = :keyword',
            'binds' => [ 'keyword' => $keyword ]
        ];

        $stats = yourls_get_db_stats( $where );
        $this->assertSame( 1, (int)$stats['total_links'] );
        $this->assertSame( 42, (int)$stats['total_clicks'] );
    }

    /**
     * Test get_db_stats filter
     */
    public function test_yourls_get_db_stats_filter() {
        yourls_add_filter( 'get_db_stats', function( $return ) {
            $return['total_links'] = 999;
            return $return;
        } );

        $stats = yourls_get_db_stats();
        $this->assertSame( 999, $stats['total_links'] );
    }

    /**
     * Test stats retrieval with malformed where clauses
     */
    public function test_yourls_get_db_stats_malformed_where() {
        $keyword = rand_str();
        yourls_add_new_link( 'http://example.org/' . $keyword, $keyword, 'Title' );
        yourls_update_clicks( $keyword, 42 );

        // Initial correct stats count
        $base_stats = yourls_get_db_stats();

        // 1. Pass a string instead of an array
        $stats1 = yourls_get_db_stats( " AND 1=0 UNION SELECT user_pass, 1 FROM yourls_users" );
        $this->assertSame( $base_stats['total_links'], $stats1['total_links'] );
        $this->assertSame( $base_stats['total_clicks'], $stats1['total_clicks'] );

        // 2. Pass an array missing 'sql' and 'binds'
        $stats2 = yourls_get_db_stats( [ 'malicious' => 'code' ] );
        $this->assertSame( $base_stats['total_links'], $stats2['total_links'] );
        $this->assertSame( $base_stats['total_clicks'], $stats2['total_clicks'] );

        // 3. Pass an array with invalid types for 'sql' and 'binds'
        $stats3 = yourls_get_db_stats( [ 'sql' => [ 'array' ], 'binds' => 'string' ] );
        $this->assertSame( $base_stats['total_links'], $stats3['total_links'] );
        $this->assertSame( $base_stats['total_clicks'], $stats3['total_clicks'] );
    }
}
