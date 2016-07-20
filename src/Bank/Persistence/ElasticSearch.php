<?php

namespace Bank\Persistence;


use Bank\Activity;
use Bank\Persistence;
use Elasticsearch\Client;

class ElasticSearch implements Persistence
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var array
     */
    private $config;

    public function __construct(Client $client, $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    public function persist(Activity $activity)
    {
        $params = [
            'index' => $this->config['elasticsearch']['index_name'],
            'type' => $this->config['elasticsearch']['mapping'],
        ];
        foreach ($activity as $transaction) {
            $params['body']  = $transaction->toArray();
            $this->client->index($params);
        }
    }
}