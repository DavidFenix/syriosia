FROM php:8.1-apache

# Instalar GD (PNG, JPG, Freetype)
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# Define o diretório da aplicação
WORKDIR /app

# Copia tudo
COPY . .

# Permissões básicas
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Configurar o Apache para servir public/
RUN sed -i 's/\/var\/www\/html/\/app\/public/' /etc/apache2/sites-available/000-default.conf

EXPOSE 8080

CMD ["apache2-foreground"]
