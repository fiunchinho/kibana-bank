<?php

namespace Bank;


interface Parser
{
    public function parseTransactions($path);
}