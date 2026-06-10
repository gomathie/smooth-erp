FROM php:8.2-apache

# Enable rewrite (important for ERP routing)
RUN a2enmod rewrite

# Install ERP-needed extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Copy only app folder
COPY app/ /var/www/html/

# Set public folder as web root (VERY IMPORTANT for ERP security)
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html

EXPOSE 80