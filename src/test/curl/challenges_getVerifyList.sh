#!/bin/bash

curl -v --request POST \
    "http://api-dev.letsvolley.com/api/pedro/challenges/getVerifyList" \
    --data-urlencode "userID=131820"

