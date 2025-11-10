FROM php:8.1-apache

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

RUN a2enmod rewrite
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /app
COPY . .

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY apache/laravel.conf /etc/apache2/sites-available/laravel.conf

RUN a2dissite 000-default.conf
RUN a2ensite laravel.conf

EXPOSE 8080

CMD ["apache2-foreground"]
