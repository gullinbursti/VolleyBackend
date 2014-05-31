#!/bin/bash

curl -v --request POST \
    "http://api-dev.letsvolley.com/api/pedro/clubs/get" \
    --data-urlencode "clubID=8" \
    --data-urlencode "userID=2394"

