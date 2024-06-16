# Basic E-Signature API

## Introduction

The Basic E-Signature API is a robust solution for electronically signing documents. This API allows users to securely upload documents, create and send signature requests, and manage document signatures. The system leverages Laravel 11 and provides authentication using Laravel Sanctum.

## Features

- User registration and login
- Document upload (PDF format only)
- Secure storage of documents and signatures
- Signature request functionality
- Document signing and verification
- Status tracking for documents (e.g., pending, signed)

## Requirements

- PHP 8.3.x
- GD PHP extension
    - Install via: `sudo apt install php8.3-gd`
- OpenSSL for key generation
    - Create private and public keys:
      ```bash
      openssl genpkey -algorithm RSA -out private_key.pem -pkeyopt rsa_keygen_bits:2048
      openssl rsa -pubout -in private_key.pem -out public_key.pem
      ```
    - Place the keys in the `storage/app/keys` and `tests/fixtures` folders
## Installation

1. **Clone the repository:**
    ```bash
    git clone https://github.com/bandaranaike/basic-e-signature-api.git
    cd basic-e-signature-api
    ```

2. **Install dependencies:**
    ```bash
    composer install
    ```

3. **Set up environment variables:**
   Copy the `.env.example` file to `.env` and update the database and mail configuration:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. **Run migrations:**
    ```bash
    php artisan migrate
    ```

5. **Set up storage links:**
    ```bash
    php artisan storage:link
    ```

6. **Start the server:**
    ```bash
    php artisan serve
    ```
7. To run all the tests, use this command:
    ```bash
    php artisan test
    ```
## API Documentation

For detailed API documentation, please refer to the [DOCUMENTATION.md](DOCUMENTATION.md) file.
