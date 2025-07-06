FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo_mysql

RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    ffmpeg \
    build-essential \
    curl \
    unzip \
 && pip3 install --no-cache-dir ffmpeg-python --break-system-packages \
 && rm -rf /var/lib/apt/lists/*

ARG BENTO_VER=1-6-0-641
RUN curl -sSL -o /tmp/bento4.zip \
    https://www.bok.net/Bento4/binaries/Bento4-SDK-1-6-0-641.x86_64-unknown-linux.zip \
 && unzip /tmp/bento4.zip -d /opt/bento4 \
 && rm -rf /tmp/bento4.zip
