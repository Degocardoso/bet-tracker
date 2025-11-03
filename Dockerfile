FROM php:8.1-apache

# Instala apenas o essencial
RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pdo pdo_pgsql && \
    a2enmod rewrite && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copia tudo
COPY . .

# Cria pastas
RUN mkdir -p uploads data && \
    chmod -R 777 uploads data

EXPOSE 80
CMD ["apache2-foreground"]