<?php

namespace Bank\Parser;

use Bank\Activity;
use Bank\Transaction;
use PHPUnit_Framework_TestCase;

class LacaixaTest extends PHPUnit_Framework_TestCase
{
    const XLS_DIRECTORY = __DIR__.'/../../resources/lacaixa/';

    private $parser;

    public function setUp()
    {
        $this->parser = new Lacaixa();
    }

    /**
     * @test
     */
    public function shouldParseBankXls()
    {
        $parsedTransactions = $this->parser->parseTransactions(self::XLS_DIRECTORY);

        $this->assertInstanceOf(Activity::class, $parsedTransactions);
    }

    /**
     * @test
     */
    public function shouldGetExpenses()
    {
        $parsedTransactions = $this->parser->parseTransactions(self::XLS_DIRECTORY);

        foreach($parsedTransactions->expenses() as $expense) {
            $this->assertEquals(Transaction::EXPENSE, $expense->getType());
        }
    }

    /**
     * @test
     */
    public function shouldGetRevenues()
    {
        $parsedTransactions = $this->parser->parseTransactions(self::XLS_DIRECTORY);

        foreach($parsedTransactions->revenues() as $revenue) {
            $this->assertEquals(Transaction::REVENUE, $revenue->getType());
        }
    }
}