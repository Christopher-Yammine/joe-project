#!/bin/bash

# Fetch secret from AWS Secrets Manager
SECRET_JSON=$(aws secretsmanager get-secret-value --secret-id dashboard-szgmc-secrets --query SecretString --output text)

ENV_FILE=".env"
touch $ENV_FILE
# Extract the secret values and set as environment variables in app root directory
echo "NEXT_PUBLIC_JWT_EXPIRATION=\"$(echo $SECRET_JSON | jq -r '.NEXT_PUBLIC_JWT_EXPIRATION')\"" >> $ENV_FILE
echo "NEXT_PUBLIC_JWT_SECRET=\"$(echo $SECRET_JSON | jq -r '.NEXT_PUBLIC_JWT_SECRET')\"" >> $ENV_FILE
echo "NEXT_PUBLIC_JWT_REFRESH_TOKEN_SECRET=\"$(echo $SECRET_JSON | jq -r '.NEXT_PUBLIC_JWT_REFRESH_TOKEN_SECRET')\"" >> $ENV_FILE