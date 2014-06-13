#!/bin/bash

curl -v --request POST \
    "http://api-dev.letsvolley.com/api/pedro/userPhone/isValid" \
    --data-urlencode "userID=131853"

