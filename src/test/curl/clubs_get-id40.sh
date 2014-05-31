#!/bin/bash

curl -v --request POST \
    "http://api-dev.letsvolley.com/api/pedro/clubs/get" \
    --data-urlencode "clubID=40" \
    --data-urlencode "userID=131820"

