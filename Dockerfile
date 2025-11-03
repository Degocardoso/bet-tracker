FROM php:8.1-apache

# Instala dependências do sistema
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libpq-dev \
    tesseract-ocr \
    tesseract-ocr-por \
    zip \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Configura e instala extensão GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

# Instala extensões PHP (separado para evitar erro)
RUN docker-php-ext-install gd
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_pgsql

# Copia Composer do container oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Habilita mod_rewrite
RUN a2enmod rewrite

# Define diretório de trabalho
WORKDIR /var/www/html

# Copia arquivos do projeto
COPY . .

# Instala dependências PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction || composer install --optimize-autoloader

# Cria diretórios temporários
RUN mkdir -p /tmp/uploads /tmp/data && chmod -R 777 /tmp

# Define permissões
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Expõe porta 80
EXPOSE 80

# Comando de inicialização
CMD ["apache2-foreground"]