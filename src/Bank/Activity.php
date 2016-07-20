<?php

namespace Bank;


class Activity extends \ArrayIterator
{
    /**
     * @var Transaction[]
     */
    private $transactions;

    /**
     * Collection of Transactions
     * 
     * @param Transaction[] $transactions
     */
    public function __construct($transactions = [])
    {
        parent::__construct($transactions);
        $this->transactions = $transactions;
    }

    /**
     * Apply tag to transactions that match given string
     * 
     * @param array $config Mapping between tags to strings that should match
     * @return Activity
     */
    public function tag(array $config)
    {
        foreach ($this->transactions as $transaction) {
            foreach ($config as $type => $matches) {
                $matching = false;
                foreach ($matches as $match) {
                    $matching = $matching || preg_match("/$match/i", $transaction->getDescription(), $m);
                }

                if ($matching) {
                    $transaction->tag($type);
                }
            }
        }

        return new Activity($this->transactions);
    }

    /**
     * Filter only expenses
     * 
     * @return Activity
     */
    public function expenses()
    {
        return $this->filter(function (Transaction $transaction) {
            return $transaction->getType() == Transaction::EXPENSE;
        });
    }

    /**
     * Filter only revenue
     * 
     * @return Activity
     */
    public function revenues()
    {
        return $this->filter(function (Transaction $transaction) {
            return $transaction->getType() == Transaction::REVENUE;
        });
    }

    /**
     * Filter the activity collection using the given callable
     * 
     * @return Activity
     */
    public function filter(callable $callable)
    {
        return new Activity(array_filter($this->transactions, $callable));
    }
}