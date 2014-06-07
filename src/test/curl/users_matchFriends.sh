#!/bin/bash

    #"http://api-dev.letsvolley.com/api/pedro/users/getclubs" \

curl -v --request POST \
    "http://api-dev.letsvolley.com/api/pedro/users" \
    --data-urlencode "action=11" \
    --data-urlencode "userID=131826" \
    --data-urlencode "phone=+14152549391|+16544329852|+14519854455"


