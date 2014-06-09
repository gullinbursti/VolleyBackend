#!/bin/bash

curl --verbose "http://api-dev.letsvolley.com/api/pedro/users/getActivity" \
    --request POST \
    --data-urlencode "userID=131820" \
    --data-urlencode "lastUpdated=2014-04-21 13:25:02"

