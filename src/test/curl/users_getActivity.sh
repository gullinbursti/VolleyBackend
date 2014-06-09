#!/bin/bash

curl --verbose "http://api-dev.letsvolley.com/api/pedro/users/getActivity" \
    --request POST \
    --data-urlencode "userID=131820" \
    --data-urlencode "lastUpdated=2014-05-25 02:44:50"

    #--data-urlencode "lastUpdated="
    #--data-urlencode "lastUpdated=0000-00-00 00:00:00"
