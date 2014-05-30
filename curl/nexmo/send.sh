#!/bin/bash


if false; then
    api_account="Pedro's"
    api_key='e1e24440'
    api_secret='531a4a3a'
    from='12134657587'
else
    api_account="Jason's"
    api_key='adf47d98'
    api_secret='f274407d'
    from='19189620405'
fi

echo -e "\n\nUsing: $api_account\n\n"
curl -v --get 'https://rest.nexmo.com/sc/us/2fa/json' \
    --data-urlencode "api_key=$api_key" \
    --data-urlencode "api_secret=$api_secret" \
    --data-urlencode "to=16463915410" \
    --data-urlencode "pin=1234"


#curl -v --get 'https://rest.nexmo.com/sms/json' \
#    --data-urlencode "api_key=$api_key" \
#    --data-urlencode "api_secret=$api_secret" \
#    --data-urlencode "from=$from" \
#    --data-urlencode "to=16463915410" \
#    --data-urlencode "text={$api_account} It's Pedro..   Please ignore..."

