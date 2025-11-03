FROM php:8.1-apache

# Instala extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    tesseract-ocr \
    tesseract-ocr-por \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql pdo_pgsql

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copia arquivos do projeto
COPY . /var/www/html/

# Define o diretório de trabalho
WORKDIR /var/www/html

# Instala dependências PHP
RUN composer install --no-dev --optimize-autoloader

# Dá permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expõe a porta 80
EXPOSE 80

# Comando para iniciar
CMD ["apache2-foreground"]