#!/bin/bash

curl -v --request POST "http://api-dev.letsvolley.com/api/pedro/challenges/join" \
     --data-urlencode "imgURL=https://hotornot-challenges.s3.amazonaws.com/86793eee81144ca9ae32c4e7544457a6-bcafe25a99b64c8db308cbe77b07854e_1400615027" \
     --data-urlencode "userID=2466" \
     --data-urlencode "challengeID=252973" \
     --data-urlencode "subject=#bangbang" \
     --data-urlencode "subjects=[ \"#mangojuice\", \"#dexter\" ]"

