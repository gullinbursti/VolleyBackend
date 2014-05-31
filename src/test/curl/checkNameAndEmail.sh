#!/bin/bash

#source "./00-config.sh"

#ip="54.221.205.30"  # Matt
ip="54.243.163.24"  # Pedro


# return values are:
# result=0 - username & password available
# result=1 - username taken
# result=2 - password taken
# result=3 - username & password taken
curl -v --request POST \
    "http://api-dev.letsvolley.com/api/pedro/users/checkNameAndEmail" \
    --data-urlencode "userID=2394" \
    --data-urlencode "username=pedro" \
    --data-urlencode "password=kenshabby@mpfc.net"


