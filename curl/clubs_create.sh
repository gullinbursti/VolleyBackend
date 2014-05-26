#!/bin/bash

time_stamp=$(date +%Y%m%d%H%M%S)

curl -v --request POST \
    "http://api-dev.letsvolley.com/api/pedro/clubs/create" \
    --data-urlencode "userID=13616" \
    --data-urlencode "name=Test Club $time_stamp"

