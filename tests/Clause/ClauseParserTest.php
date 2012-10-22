<?php

use MistyDoctrine\Clause\ClauseParser;
use MistyTesting\UnitTest;

class ClauseParserTest extends UnitTest
{
    public function testWithDefault_useParam()
    {
        $parser = new ClauseParser();
        $parser->param('category')->field("c.category_id")->equalTo()->defaultTo(2);

        $sql = $parser->parse(array(
            'category' => 1,
        ));

        $this->assertEquals("c.category_id = 1", $sql);
    }

    public function testWithDefault_useDefault()
    {
        $parser = new ClauseParser();
        $parser->param('category')->field("c.category_id")->equalTo()->defaultTo(2);

        $sql = $parser->parse(array());

        $this->assertEquals("c.category_id = 2", $sql);
    }

    public function testShortCut()
    {
        $parser = new ClauseParser();
        $parser->add('category', 'c.category_id')->equalTo()->defaultTo(2);

        $sql = $parser->parse(array(
            'category' => 1,
        ));

        $this->assertEquals("c.category_id = 1", $sql);
    }

    public function testCustom()
    {
        $parser = new ClauseParser();
        $parser->custom(function(&$params){
            unset($params['name']);
            return 'not-sql';
        });

        $sql = $parser->parse(array(
            'name' => 1,
        ));

        $this->assertEquals('not-sql', $sql);
    }

    /**
     * @expectedException MistyDoctrine\Exception\QueryException
     */
    public function testMissingClause()
    {
        $parser = new ClauseParser();
        $parser->parse(array(
           'missing-clause' => 1
        ));
    }



}
