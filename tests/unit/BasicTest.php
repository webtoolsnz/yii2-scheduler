<?php
namespace webtoolsnz\scheduler\tests;


class BasicTest extends \Codeception\TestCase\Test
{
    /**
     * @var \webtoolsnz\scheduler\tests\UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testMe()
    {
        $this->assertEquals(true, true);
    }

}