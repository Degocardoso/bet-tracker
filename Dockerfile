FROM php:8.1-cli

# Instala dependências
RUN apt-get update && \
    apt-get install -y libpq-dev unzip git && \
    docker-php-ext-install pdo pdo_pgsql && \
    rm -rf /var/lib/apt/lists/*

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copia composer.json primeiro
COPY composer.json ./

# Instala dependências
RUN composer install --no-dev --optimize-autoloader --no-interaction || composer dump-autoload

# Copia resto dos arquivos
COPY . .

# Cria pastas
RUN mkdir -p uploads data && chmod -R 777 uploads data

EXPOSE $PORT

CMD php -S 0.0.0.0:$PORT -t .