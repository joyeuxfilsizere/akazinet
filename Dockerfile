# AkaziNet - plain PHP 8.2 on Apache, MySQL via environment variables.
#
# Build:  docker build -t akazinet .
# Run:    docker run -p 8080:80 \
#           -e DB_HOST=... -e DB_NAME=... -e DB_USER=... -e DB_PASS=... \
#           -e SITE_URL=https://akazinet.com \
#           akazinet
#
# No .env file is baked into the image: config/env.php reads the real process
# environment first, so the deployment platform's environment settings are enough.

FROM php:8.2-apache

# pdo_mysql  - database driver (config/database.php)
# mbstring    - string helpers throughout the app
# opcache    - production bytecode cache
# fileinfo   - built into PHP 8, used by upload_image() for MIME detection
RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring opcache \
    && rm -rf /var/lib/apt/lists/*

# The whole app is .htaccess-driven (clean URLs, security blocks, error pages),
# so Apache must honour .htaccess files in the document root.
RUN a2enmod rewrite headers expires \
    && sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Production PHP settings; APP_DEBUG still controls error display at runtime.
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'expose_php=Off'; \
        echo 'upload_max_filesize=10M'; \
        echo 'post_max_size=12M'; \
    } > /usr/local/etc/php/conf.d/akazinet.ini

# Uploads are the only directory PHP writes to at runtime (config/database.php:
# UPLOAD_PATH). Seed it here so the copy below lands on top of an owned dir.
RUN mkdir -p /var/www/html/assets/uploads/logos \
             /var/www/html/assets/uploads/documents \
             /var/www/html/assets/uploads/banners

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html/assets/uploads

# The committed Tailwind build (assets/css/tailwind.css) ships with the image;
# Node is a dev-only tool and is intentionally not part of the runtime.

WORKDIR /var/www/html

EXPOSE 80
