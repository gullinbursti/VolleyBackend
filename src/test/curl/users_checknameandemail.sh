#!/bin/bash

hostname='volley-api.dev.selfieclubapp.com'
service_url="http://$hostname/master"

curl --request POST \
    "$service_url/users/checknameandemail" \
    --data-urlencode "userID=131820" \
    --data-urlencode "username=freakyfreakerson9385" \
    --data-urlencode "password=131820@whozimwha-skdjfhskjh.com" \
    --data-urlencode "sku=selfieclub"
