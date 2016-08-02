# Bank Transactions Dashboard
This tool will parse excel files exported from your [ING Direct](https://www.ingdirect.es/) account, tag them and save your bank transactions to a local ElasticSearch so you can use Kibana to build a dashboard and explore your money transactions.

## Configuration
A `config.yml` file is needed to tell configuration options to the import tool.

```yml
elasticsearch:
  host: http://localhost:9200
  index_name: financial
  mapping: transaction

tags:
  rent:
    - Real State Co.
  shopping:
    - El corte ingles
  work:
    - Github
    - Amazon Web Services

parsers:
  ing: \Bank\Parser\Ing
  lacaixa: \Bank\Parser\LaCaixa
```

If you are not sure about ElasticSearch options, just leave the defaults.

### Tags
You can define an array of tags that the import tool will use to tag your transactions if the transaction's description contains the keywords for that tag.

## Usage
You need docker and docker compose installed (for [Linux](https://docs.docker.com/engine/installation/linux/), [Mac](https://docs.docker.com/docker-for-mac/), [Windows](https://docs.docker.com/docker-for-windows/)), since we'll save the transactions on a running ElasticSearch container.
You also need to download the excel files manually from your [ING Direct](https://www.ingdirect.es/) or [LaCaixa](https://portal.lacaixa.es/home/particulares_es.html) bank account.

Once downloaded the excel files, just import them

```bash
$ ./start.sh ing
```

This will start containers for both ElasticSearch and Kibana. When it's done just go to [http://localhost:5601](http://localhost:5601) to open Kibana. It'll ask you which pattern to look for, and which field use for timestamp. Just choose the wildcard "*" for the pattern and the `timestamp` field.

Transactions will be lost everytime you remove the containers, but it only takes a few seconds to parse and save them again.

## Manually importing transactions
The bash script `start.sh` will:
- Start `elasticsearch` container
- Start `kibana` container
- Execute the command to parse excel files and save them in ElasticSearch

You can run the command manually

```bash
$ ./bin/bank import -h

Usage:
  import [options] [--] <bank> [<path>] [<config>]

Arguments:
  bank                  Bank name
  path                  Path containing xls files with bank transactions [default: "./xls/"]
  config                Path for config file [default: "./config.yml"]

Options:
  -e, --expenses-only   If set, only transactions with negative amount will be imported
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
      --ansi            Force ANSI output
      --no-ansi         Disable ANSI output
  -n, --no-interaction  Do not ask any interactive question
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

```

## Kibana
If you don't know Kibana, this is what it looks like

![this is what it looks like](https://cdn.discourse.org/elastic/uploads/default/original/2X/2/26bac5dd232e4117c7977c995ee069a18cf95499.png)

There is an example dashboard in the repository. You can import the `dashboard.json` into Kibana to get it, or you can build your own visualizations and dashboard.