#!/bin/bash

    #"http://api-dev.letsvolley.com/api/pedro/users/getclubs" \

curl -v --request POST \
    "http://api-dev.letsvolley.com/api/pedro/users/verifyPhone" \
    --data-urlencode "userID=131820" \
    --data-urlencode "phone=15555555555" \
    --data-urlencode "code=c1NKms8QKSShc11c"

