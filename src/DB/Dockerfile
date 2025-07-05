FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo_mysql

RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    ffmpeg \
    build-essential \
 && pip3 install --no-cache-dir ffmpeg-python --break-system-packages \
 && rm -rf /var/lib/apt/lists/*
