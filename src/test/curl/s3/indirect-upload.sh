#!/bin/bash

#S3_KEY='FILL ME IN'
#S3_SECRET='FILL ME IN'

file='test-image.png'
fileSize=$(stat -c '%s' ${file})
bucket='volley-test'
contentType='image/png'
expiry=$(date '+%Y-%m-%dT23:59:59Z')

read -d '' policy <<EOT
{"expiration": "${expiry}",
  "conditions": [
    {"bucket": "${bucket}"},
    {"key": "${file}"},
    {"acl": "public-read"},
    {"Content-Type": "${contentType}"},
    ["content-length-range", ${fileSize}, ${fileSize}]
  ]
}
EOT

# The `-n` for `echo` is critical!
policyBase64=$(echo -n ${policy} | base64 --wrap=0)
policySig=$(echo -n ${policyBase64} | openssl sha1 -hmac ${S3_SECRET} -binary | base64)

curl -v https://${bucket}.s3.amazonaws.com/ \
    --form "key=${file}" \
    --form "acl=public-read" \
    --form "AWSAccessKeyId=${S3_KEY}" \
    --form "Policy=${policyBase64}" \
    --form "Signature=${policySig}" \
    --form "Content-Type=${contentType}" \
    --form "file=@${file}" \

