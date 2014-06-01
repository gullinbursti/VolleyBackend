#!/bin/bash

curl -v --request POST \
    "http://api-dev.letsvolley.com/api/pedro/userPhone/validatePhone" \
    --data-urlencode "userID=131820" \
    --data-urlencode "pin=1234"

