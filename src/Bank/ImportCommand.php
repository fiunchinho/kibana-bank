<?php

namespace Bank;

use Bank\Parser\BankParserNotFoundException;
use Bank\Parser\Ing;
use Bank\Persistence\ElasticSearch;
use Elasticsearch\ClientBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class ImportCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('import')
            ->setDescription('Import transactions')
            ->addArgument(
                'bank',
                InputArgument::REQUIRED,
                'Bank name'
            )
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'Path containing xls files with bank transactions',
                __DIR__ . '/../../xls/'
            )
            ->addArgument(
                'config',
                InputArgument::OPTIONAL,
                'Path for config file',
                __DIR__ . '/../../config.yml'
            )
            ->addOption(
                'expenses-only',
                'e',
                InputOption::VALUE_NONE,
                'If set, only transactions with negative amount will be imported'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<info>Parsing xls files...</info>", OutputInterface::VERBOSITY_NORMAL);

        $config     = $this->getConfig($input, $output);
        $activity   = $this->parse($input, $config);
        $this->persist($config, $activity);
        $this->printOutput($output, $activity);
    }

    /**
     * @param $config
     * @return \Elasticsearch\Client
     */
    private function getPersistence($config)
    {
        $client = ClientBuilder::create()
            ->setHosts([$config['elasticsearch']['host']])
            ->build();

        return $client;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     */
    private function getConfig(InputInterface $input, OutputInterface $output)
    {
        $config = [];
        try {
            $config = Yaml::parse(@file_get_contents($input->getArgument('config')));
        } catch (ParseException $e) {
            $output->writeln("<error>Unable to find config file in " . $input->getArgument('config') . "</error>");
        }

        return $config;
    }

    /**
     * @param $config array
     * @param $activity Activity
     */
    private function persist(array $config, Activity $activity)
    {
        (new ElasticSearch($this->getPersistence($config), $config))->persist($activity);
    }

    /**
     * @param InputInterface $input
     * @param $config array
     * @return Activity
     */
    private function parse(InputInterface $input, array $config)
    {
        $bankParser = $this->createBankParser($input, $config);
        $parsed_transactions = $bankParser->parseTransactions($input->getArgument('path'));

        if ($input->getOption('expenses-only')) {
            $parsed_transactions = $parsed_transactions->expenses();
        }

        return $parsed_transactions->tag($config['tags']);
    }

    /**
     * @param OutputInterface $output
     * @param $activity Activity
     */
    private function printOutput(OutputInterface $output, Activity $activity)
    {
        $output->writeln("<info>" . count($activity) . " transactions have been imported</info>", OutputInterface::VERBOSITY_NORMAL);
        
        if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $untagged = count($activity->filter(function (\Bank\Transaction $transaction) {
                return empty($transaction->getTags());
            }));
            $tagged_revenue = count($activity->filter(function (\Bank\Transaction $transaction) {
                return !empty($transaction->getTags()) && $transaction->getType() == Transaction::REVENUE;
            }));

            $output->writeln("<info>" . $untagged . " transactions are untagged</info>");
            $output->writeln("<info>" . $tagged_revenue . " tagged revenues</info>");
        }
    }

    private function createBankParser(InputInterface $input, array $config)
    {
        $bank = $input->getArgument('bank');
        if(empty($config['parsers'][$bank])) {
            throw new BankParserNotFoundException('Bank parser not found!');
        }
        return new $config['parsers'][$bank];
    }
}