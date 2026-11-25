# Dockerfile untuk deploy SIPINLAB API ke Render (paket gratis)
FROM php:8.3-apache

# 1. Paket sistem dan ekstensi PHP yang diperlukan Laravel + MySQL
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libicu-dev ca-certificates \
    && docker-php-ext-install pdo_mysql zip intl bcmath \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# 2. Apache melayani folder public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 3. Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress \
    && chown -R www-data:www-data storage bootstrap/cache

# 4. Skrip awal: migrasi basis data lalu menjalankan Apache
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 10000
CMD ["/usr/local/bin/start.sh"]
