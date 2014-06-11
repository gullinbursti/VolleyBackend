#!/bin/bash

time_stamp=$(date +%Y%m%d\.%H%M%S)

curl "http://api-dev.letsvolley.com/api/pedro/clubs/create" \
    --request POST \
    --data-urlencode "userID=133703" \
    --data-urlencode "name=TEST_CLUB_${time_stamp}" \
    --data-urlencode "description=Test club ${time_stamp}" \
    --data-urlencode "imageURL=0007ab92a1df4d27a91670f9ac9c1ca3_43850f0426e04ca5826060f44163183f-1387815886" \

