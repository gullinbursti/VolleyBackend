#!/bin/bash

curl -v --request POST "http://api-dev.letsvolley.com/api/pedro/challenges/create" \
     --data-urlencode "challengeID=0" \
     --data-urlencode "clubID=0" \
     --data-urlencode "imgURL=https://hotornot-challenges.s3.amazonaws.com/86793eee81144ca9ae32c4e7544457a6-bcafe25a99b64c8db308cbe77b07854e_1400615027" \
     --data-urlencode "subject=#bestFriend" \
     --data-urlencode "subjects=[ \"#bestFail\", \"#funnyFace\" ]" \
     --data-urlencode "targets=" \
     --data-urlencode "userID=131820"

