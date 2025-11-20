FROM php:8.3.11-fpm-alpine3.20

# A priori, on est déjà root, mais soyons sûrs
USER root

# Ces args viennent du docker-compose.yml
ARG USER_NAME
ARG USER_ID
ARG GROUP_NAME
ARG GROUP_ID

# Créons un utilisateur qui soit le même que celui de l'utilisateur sous linux (si les args ont été fournis)
RUN if [ -n "${USER_NAME}" ] && [ -n "${GROUP_NAME}" ] && [ "${USER_ID:-0}" -ne 0 ] && [ "${GROUP_ID:-0}" -ne 0 ]; then \
    addgroup -g "$GROUP_ID" "$GROUP_NAME" 2>/dev/null || true; \
    adduser -D -h "/home/$USER_NAME" -G "$GROUP_NAME" -u "$USER_ID" "$USER_NAME"; \
    addgroup "$USER_NAME" root; \
    addgroup "$USER_NAME" www-data; \
    mkdir -p "/home/$USER_NAME/.composer"; \
    chown -Rf "${USER_NAME}:${GROUP_NAME}" "/home/$USER_NAME"; \
    chown -R "${USER_NAME}:${GROUP_NAME}" /app; \
  fi

# Installer XDebug
RUN apk add linux-headers
COPY ./docker/php/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN apk add bash git $PHPIZE_DEPS
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

# Modules pour utiliser MySQL
RUN docker-php-ext-install mysqli pdo_mysql
# Intl
RUN apk add icu-dev icu-data-full
RUN docker-php-ext-install intl

# Installer Composer proprement
RUN apk add --no-cache curl ca-certificates \
    && curl -sS https://getcomposer.org/installer -o composer-setup.php \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php

# Installer la CLI Symfony
RUN apk add --no-cache bash
RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.alpine.sh' | bash
RUN apk add symfony-cli

# Sous linux, l'utilisateur se loguera comme l'utilisateur courant
USER ${USER_ID:-0}:${GROUP_ID:-0}
