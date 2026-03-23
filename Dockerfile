FROM php:8.2-apache

# Instalar extensiones necesarias
RUN docker-php-ext-install pdo pdo_mysql

# Habilitar mod_rewrite (por si lo usas)
RUN a2enmod rewrite

# Copiar código al contenedor
COPY ./src /var/www/html/

EXPOSE 80