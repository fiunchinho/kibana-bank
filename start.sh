#!/bin/bash

set -e

docker-compose up -d

echo "Waiting Elasticsearch to start..."

while ! nc -z localhost 9200; do
  sleep 0.1 # wait for 1/10 of the second before check again
done

./bin/bank import -v
