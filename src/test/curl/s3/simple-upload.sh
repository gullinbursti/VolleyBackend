#!/bin/bash

#S3_KEY=
#S3_SECRET=

file='test-image.png'
bucket='volley-test'

resource="/${bucket}/${file}"
contentType="application/x-compressed-tar"
dateValue=`date -R`
stringToSign="PUT\n\n${contentType}\n${dateValue}\n${resource}"
signature=`echo -en ${stringToSign} | openssl sha1 -hmac ${S3_SECRET} -binary | base64`

curl -v -X PUT -T "${file}" \
  -H "Host: ${bucket}.s3.amazonaws.com" \
  -H "Date: ${dateValue}" \
  -H "Content-Type: ${contentType}" \
  -H "Authorization: AWS ${S3_KEY}:${signature}" \
  https://${bucket}.s3.amazonaws.com/${file}

