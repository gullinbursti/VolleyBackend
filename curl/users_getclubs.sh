#!/bin/bash

    #"http://api-dev.letsvolley.com/api/pedro/users/getclubs" \

curl -v --request POST \
    "http://api-dev.letsvolley.com/api/pedro/users/getclubs" \
    --data-urlencode "userID=131820" \
    --data-urlencode "sort=1"

