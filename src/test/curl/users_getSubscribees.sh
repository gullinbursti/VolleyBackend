#!/bin/bash

curl -v --request POST \
    "http://api-dev.letsvolley.com/api/pedro/users/getSubscribees" \
    --data-urlencode "userID=64846"

