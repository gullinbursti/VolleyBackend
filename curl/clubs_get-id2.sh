#!/bin/bash

curl -v --request POST \
    "http://api-dev.letsvolley.com/api/pedro/clubs/get" \
    --data-urlencode "clubID=2" \
    --data-urlencode "userID=122878"

