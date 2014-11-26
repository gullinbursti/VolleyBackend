#!/bin/bash

hostname='volley-api.dev.selfieclubapp.com'
service_url="http://$hostname/master"

curl --request POST \
    "$service_url/users/firstruncomplete" \
    --data-urlencode "userID=131820" \
    --data-urlencode "username=freakyfreakerson9385" \
    --data-urlencode "password=131820@whozimwha-skdjfhskjh.com" \
    --data-urlencode "age=0000-00-00 00:00:00" \
    --data-urlencode "token=3aba40e73d63a45f533d04afdbbb64c93eba4d1934cfeef4941dc99d7e205c2a" \
    --data-urlencode "imgURL=https://s3.amazonaws.com/hotornot-avatars/defaultAvatar" \
    --data-urlencode "sku=selfieclub"
