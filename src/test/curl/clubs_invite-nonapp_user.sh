#!/bin/bash

curl -v --request POST "http://api-dev.letsvolley.com/api/pedro/clubs/invite" \
    --data-urlencode "clubID=40" \
    --data-urlencode "userID=131820" \
    --data-urlencode "nonUsers=Pedro:::+15105025820:::" \
    --data-urlencode "users="

