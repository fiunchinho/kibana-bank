<?php

namespace Bank;


use DateTime;

class Transaction
{
    const EXPENSE = 'expense';

    const REVENUE = 'revenue';

    /**
     * @var string
     */
    private $type;

    /**
     * @var DateTime
     */
    private $timestamp;

    /**
     * @var string
     */
    private $description;

    /**
     * @var double
     */
    private $amount;

    /**
     * @var array
     */
    private $tags = [];

    /**
     * Transaction constructor.
     * 
     * @param DateTime $timestamp
     * @param $description
     * @param $amount
     */
    public function __construct($type, DateTime $timestamp, $description, $amount)
    {
        $this->timestamp    = $timestamp;
        $this->description  = $description;
        $this->amount       = (double)$amount;
        $this->type         = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return double
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    public function tag($tag)
    {
        if (!in_array($tag, $this->tags, true)) {
            $this->tags[] = $tag;
        }
    }

    public function toArray()
    {
        return [
            'type' => $this->type,
            'timestamp' => $this->timestamp->format('Y-m-d'),
            'description' => $this->description,
            'amount' => $this->amount,
            'tags' => $this->tags,
        ];
    }
}