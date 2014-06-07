#!/bin/bash

curl -v --request POST \
    "http://api-dev.letsvolley.com/api/pedro/userPhone/updatePhone" \
    --data-urlencode "userID=2466" \
    --data-urlencode "phone=15555555555"

