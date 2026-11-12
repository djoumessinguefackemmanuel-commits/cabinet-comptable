FROM dunglas/frankenphp:1-php8.4-bookworm

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && install-php-extensions pdo_pgsql pgsql pdo_mysql intl zip gd exif \
    && printf 'upload_max_filesize=5M\npost_max_size=8M\nmemory_limit=256M\n' > "$PHP_INI_DIR/conf.d/zz-app.ini"

WORKDIR /app

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .

# Composer seulement si le projet l'utilise
RUN if [ -f composer.json ]; then \
      composer install --no-dev --no-interaction --no-progress --optimize-autoloader; \
    fi

COPY Caddyfile /etc/frankenphp/Caddyfile

RUN chown -R www-data:www-data /app \
    && setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp \
    && chown -R www-data:www-data /config/caddy /data/caddy

ENV PORT=80

USER www-data

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
