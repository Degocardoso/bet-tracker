FROM php:8.1-apache

# Instala dependências
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Instala extensões PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd pdo pdo_pgsql zip

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Habilita mod_rewrite
RUN a2enmod rewrite

# Define diretório de trabalho
WORKDIR /var/www/html

# Copia composer.json primeiro
COPY composer.json ./

# Instala dependências
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# Copia resto dos arquivos
COPY . .

# Cria diretórios e permissões
RUN mkdir -p /tmp/uploads /tmp/data && \
    chmod -R 777 /tmp && \
    chown -R www-data:www-data /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]