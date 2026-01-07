FROM php:8.2-fpm

# Instalar extensiones de PHP para MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli

# Establecer el directorio de trabajo
WORKDIR /var/www/html
