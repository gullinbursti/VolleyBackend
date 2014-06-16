#!/bin/bash

./vendor/bin/phpunit --colors \
    --bootstrap "integration-bootstrap.php" \
    "src/test/integration/test/"
