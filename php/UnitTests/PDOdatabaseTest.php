<?php
/**
 * Test the database classes
 *
 * This file is part of Zoph.
 *
 * Zoph is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * Zoph is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Zoph; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package ZophUnitTest
 * @author Jeroen Roos
 */

/**
 * Test class that tests the database classes
 * @package ZophUnitTest
 * @author Jeroen Roos
 */
class PDOdatabaseTest extends ZophDataBaseTestCase {
    /**
     * Create queries
     * @dataProvider getQueries();
     * @param string Table to run query on
     * @param array Fields to query
     * @param string Expected SQL query
     */
    public function testCreateQuery($table, $fields, $exp_sql) {
        if(is_array($fields)) {
            $sql=(string) new query($table, $fields);
        } else {
            $sql=(string) new query($table);
        }
        $this->assertEquals($exp_sql, $sql);
    }

    /**
     * Run queries
     * @dataProvider getQueries();
     * @param string Table to run query on
     * @param array Fields to query
     * @param string Expected SQL query
     */
    public function testRunQuery($table, $fields, $exp_sql) {
        // not used
        $exp_sql=null;
        if(is_array($fields)) {
            $result=db::query(new query($table, $fields));
        } else {
            $result=db::query(new query($table));
        }
        $this->assertInstanceOf("PDOStatement", $result);
    }

    /**
     * Test a query with a WHERE clause
     */
    public function testQueryWithClause() {
        
        $qry=new query("photos");
        $where=new clause("photo_id > :minid", array(":minid", 5));

        $qry->where($where);

        $sql=(string) $qry;
        $exp_sql="SELECT * FROM zoph_photos WHERE (photo_id > :minid);";

        $this->assertEquals($exp_sql, $sql);

        unset($qry);
        unset($clause);

        $qry=new query("photos");
        $where=new clause("photo_id > :minid", array(":minid", 5));
        $where=$where->addAnd(new clause("photo_id < :maxid", array(":maxid", 10)));

        $qry->where($where);

        $sql=(string) $qry;
        $exp_sql="SELECT * FROM zoph_photos WHERE (photo_id > :minid) AND (photo_id < :maxid);";

        $this->assertEquals($exp_sql, $sql);
        
        unset($qry);
        unset($clause);

    }

    /**
     * Provide queries to use as test input
     */
    public function getQueries() {
        return array(
            array("photos", array("photo_id"), "SELECT zoph_photos.photo_id FROM zoph_photos;"),
            array("photos", null, "SELECT * FROM zoph_photos;"),
            array("photos", array("photo_id", "name"), 
                "SELECT zoph_photos.photo_id, zoph_photos.name FROM zoph_photos;")
        );
    }

}