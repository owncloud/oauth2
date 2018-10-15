#!/usr/bin/env bash

echo $(pwd)
mkdir -p etc
# Generate key material within etc
# cd etc
# openssl genpkey -algorithm RSA -out private-key.pem -pkeyopt rsa_keygen_bits:4096
# openssl rand -out konnectd-encryption-secret.key 32

CURRENT_UID=$(id -u):$(id -g) docker-compose up