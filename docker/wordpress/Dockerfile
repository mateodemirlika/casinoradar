# Local development image: official WP core + wp-cli + xdebug.
# wp-content is bind-mounted from the host, so this image never needs rebuilding
# just to see theme/plugin changes.
FROM wordpress:php8.3-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
        less \
        default-mysql-client \
        unzip \
        rsync \
        git \
    && rm -rf /var/lib/apt/lists/*

# wp-cli
RUN curl -o /usr/local/bin/wp -sSL https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
    && chmod +x /usr/local/bin/wp

# Xdebug (enabled/configured only via docker-compose.override.yml in local dev)
RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-wagerwise.ini

WORKDIR /var/www/html
