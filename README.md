# Basic E-Signature API

## Requirement

* php 8.3.x
* GD PHP extension
  * `sudo apt install php8.3-gd`
* Create private and public key 
  * `openssl genpkey -algorithm RSA -out private_key.pem -pkeyopt rsa_keygen_bits:2048`
  * `openssl rsa -pubout -in private_key.pem -out public_key.pem`
