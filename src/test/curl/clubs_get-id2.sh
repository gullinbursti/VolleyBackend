#!/bin/bash

curl -v --request POST \
    "http://volley-api.dev.selfieclubapp.com/sc0005/clubs/get" \
    --data-urlencode "clubID=133" \
    --data-urlencode "userID=130546"

