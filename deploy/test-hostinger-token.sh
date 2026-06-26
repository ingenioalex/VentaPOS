#!/bin/bash
TOKEN=$(grep '^dns_hostinger_api_token' /root/.secrets/hostinger.ini | sed 's/^dns_hostinger_api_token = //')
CODE=$(curl -s -o /tmp/hostinger-test.json -w "%{http_code}" \
  -H "Authorization: Bearer ${TOKEN}" \
  "https://developers.hostinger.com/api/dns/v1/zones/ventasagil.com")
echo "HTTP ${CODE}"
head -c 120 /tmp/hostinger-test.json
echo
