#!/bin/bash

curl --verbose "http://api-dev.letsvolley.com/api/pedro/users/setDeviceToken" \
    --request POST \
    --data-urlencode "userID=131820" \
    --data-urlencode "token=43c6af3e473697292a4aa26f59acd24acc6c48d2702546e68980d2af7c3155a9"

