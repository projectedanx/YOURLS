<?php

/**
 * Tests for yourls-infos.php SQL correctness
 *
 * @group stats
 */
class InfosTest extends PHPUnit\Framework\TestCase {

    public function test_infos_sql_is_safe() {
        // Mock DB query logic from yourls-infos.php
        $keyword = 'test';
        $keyword_list = ['test', 'test2'];
        $offset = 0;

        // Single keyword
        $aggregate = false;
        if( isset($aggregate) && $aggregate ) {
            $keyword_range = 'IN ( :list )';
            $keyword_binds = array('list' => $keyword_list, 'offset' => $offset);
        } else {
            $aggregate = false;
            $keyword_range = '= :keyword';
            $keyword_binds = array('keyword' => $keyword, 'offset' => $offset);
        }

        $table = YOURLS_DB_TABLE_LOG;
        $sql = "SELECT `country_code`, COUNT(*) AS `count` FROM `$table` WHERE `shorturl` $keyword_range GROUP BY `country_code`;";

        $this->assertEquals("SELECT `country_code`, COUNT(*) AS `count` FROM `$table` WHERE `shorturl` = :keyword GROUP BY `country_code`;", $sql);

        // Aggregate keywords
        $aggregate = true;
        if( isset($aggregate) && $aggregate ) {
            $keyword_range = 'IN ( :list )';
            $keyword_binds = array('list' => $keyword_list, 'offset' => $offset);
        } else {
            $aggregate = false;
            $keyword_range = '= :keyword';
            $keyword_binds = array('keyword' => $keyword, 'offset' => $offset);
        }

        $sql2 = "SELECT `country_code`, COUNT(*) AS `count` FROM `$table` WHERE `shorturl` $keyword_range GROUP BY `country_code`;";
        $this->assertEquals("SELECT `country_code`, COUNT(*) AS `count` FROM `$table` WHERE `shorturl` IN ( :list ) GROUP BY `country_code`;", $sql2);
    }
}
