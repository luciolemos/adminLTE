#!/bin/bash

# Diretório onde os certificados serão salvos
SSL_DIR="./ssl"
mkdir -p "$SSL_DIR"

# 1. Certificado para domínio específico
read -p "Informe o domínio (ex: site1.test): " DOMAIN
DOMAIN_KEY="$SSL_DIR/${DOMAIN}-key.pem"
DOMAIN_CRT="$SSL_DIR/${DOMAIN}.pem"

echo "Gerando certificado autoassinado para $DOMAIN..."
openssl req -x509 -nodes -days 825 -newkey rsa:2048 \
    -keyout "$DOMAIN_KEY" \
    -out "$DOMAIN_CRT" \
    -subj "/CN=$DOMAIN"

echo "Certificado específico salvo como:"
echo "  Chave: $DOMAIN_KEY"
echo "  Certificado: $DOMAIN_CRT"
echo

# 2. Certificado genérico (selfsigned)
SELF_KEY="$SSL_DIR/selfsigned.key"
SELF_CRT="$SSL_DIR/selfsigned.crt"

echo "Gerando certificado selfsigned genérico para localhost..."
openssl req -x509 -nodes -days 825 -newkey rsa:2048 \
    -keyout "$SELF_KEY" \
    -out "$SELF_CRT" \
    -subj "/CN=localhost"

echo "Certificado selfsigned salvo como:"
echo "  Chave: $SELF_KEY"
echo "  Certificado: $SELF_CRT"

echo
echo "Concluído! Seus certificados estão em: $SSL_DIR/"
