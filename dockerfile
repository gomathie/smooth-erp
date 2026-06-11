FROM php:8.2-apache

# Apache: enable URL rewriting (route + security .htaccess) and allow .htaccess overrides
RUN a2enmod rewrite \
 && sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# PHP extensions the ERP needs — GD (image/photo resizing) and BOTH database
# drivers: pdo_mysql (local/XAMPP) and pdo_pgsql (Supabase/PostgreSQL production).
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev libjpeg62-turbo-dev libfreetype6-dev libpq-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_mysql mysqli gd pdo_pgsql pgsql \
 && rm -rf /var/lib/apt/lists/*

# App code. Document root stays at the project root (harden-in-place): sensitive
# dirs/files (config/, storage/, models/, .env, etc.) are denied by the root
# .htaccess rather than by moving the docroot.
COPY . /var/www/html/

# Writable storage (logs / cache / uploads / exports)
RUN chown -R www-data:www-data /var/www/html/storage \
 && chmod -R 0775 /var/www/html/storage

WORKDIR /var/www/html
EXPOSE 80
