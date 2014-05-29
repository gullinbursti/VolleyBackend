#!/bin/bash

curl -v --request POST "http://api-dev.letsvolley.com/api/pedro/challenges/get" \
     --data-urlencode "challengeID=252973"

curl -v 'https://rest.nexmo.com/sms/json?api_key=e1e24440&api_secret=531a4a3a&from=12134657587&to=16463915410&text=Welcome+to+Nexmo'
