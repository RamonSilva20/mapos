# Imagem Base
FROM php:8.2.5-apache

# Versão Dockerize
ENV DOCKERIZE_VERSION v0.7.0

# Instalar dependencias e extenssões php
RUN apt update && apt upgrade -y \
    && apt install -y \
    g++ \
    libbz2-dev \
    libc-client-dev \
    libcurl4-gnutls-dev \
    libedit-dev \
    libfreetype6-dev \
    libicu-dev \
    libjpeg62-turbo-dev \
    libkrb5-dev \
    libldap2-dev \
    libldb-dev \
    libmagickwand-dev \
    libmcrypt-dev \
    libmemcached-dev \
    libpng-dev \
    libpq-dev \
    libsqlite3-dev \
    libssl-dev \
    libreadline-dev \
    libxslt1-dev \
    libzip-dev \
    libwebp-dev \
    libfreetype-dev \
    memcached \
    wget \
    unzip \
    zlib1g-dev \
    libxpm-dev \
    dumb-init \
    && docker-php-ext-install -j$(nproc) \
    bcmath \
    bz2 \
    calendar \
    exif \
    gettext \
    mysqli \
    opcache \
    pdo_mysql \
    xsl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && PHP_OPENSSL=yes docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install -j$(nproc) imap \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) intl \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && CFLAGS="$CFLAGS -D_GNU_SOURCE" docker-php-ext-install sockets \
    && pecl install xmlrpc-1.0.0RC3 && docker-php-ext-enable xmlrpc \
    && pecl install memcached && docker-php-ext-enable memcached \
    && pecl install redis && docker-php-ext-enable redis \
    && yes '' | pecl install imagick && docker-php-ext-enable imagick \
    && docker-php-source delete \
    && wget https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && tar -C /usr/local/bin -xzvf dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && rm dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && apt remove -y g++ wget \
    && apt autoremove --purge -y && apt autoclean -y && apt clean -y \
    && rm -rf /var/lib/apt/lists/* \
    && rm -rf /tmp/* /var/tmp/*

# Instalação do Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Setando permissão para o php
RUN usermod -u 1000 www-data

# Habilitando o mod_rewrite do Apache
RUN a2enmod rewrite

# Copiando arquivos php
COPY docker/etc/php/php.ini /usr/local/etc/php/conf.d/php.ini
COPY docker/etc/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# Instalando supervisor e cron
RUN apt update && apt install supervisor cron -y

# Configurando cron jobs
RUN touch /var/log/cron.log
COPY docker/etc/php/cron-jobs /etc/cron.d/cron-jobs
RUN chmod 0644 /etc/cron.d/cron-jobs
RUN crontab /etc/cron.d/cron-jobs

# Copiar arquivos necessários do mapos
WORKDIR /var/www/html/
COPY . .

# Definindo permissões de diretório
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 777 /var/www/html/install/
RUN chmod 777 /var/www/html/updates/ /var/www/html/index.php /var/www/html/application/config/config.php /var/www/html/application/config/database.php

# Instalando dependencias vendor
RUN composer install --no-dev

# Criando pasta de logs do Supervisor
RUN mkdir -p /var/log/supervisor

# Copiando arquivo de config do Supervisor
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

ENV DASHBOARD_URL=

ENTRYPOINT ["/usr/bin/dumb-init", "--"]
CMD dockerize -wait tcp://mysql:3306 -timeout 10s /usr/bin/supervisord