FROM php:8.1-apache

# Instala dependências básicas
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
    docker-php-ext-install -j$(nproc) gd pdo pdo_pgsql

# Instala Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configura Apache
RUN a2enmod rewrite

# Copia arquivos
WORKDIR /var/www/html
COPY composer.json composer.lock* ./

# Instala dependências (sem falhar se não houver)
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs || true

# Copia resto dos arquivos
COPY . .

# Cria diretórios
RUN mkdir -p /tmp/uploads /tmp/data && \
    chmod -R 777 /tmp && \
    chown -R www-data:www-data /var/www/html

EXPOSE 80
CMD ["apache2-foreground"]