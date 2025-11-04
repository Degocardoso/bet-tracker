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

# Copia script de inicialização
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Cria pastas
RUN mkdir -p uploads data && \
    chmod -R 777 uploads data

# Configura Apache para aceitar qualquer porta
RUN sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf && \
    sed -i 's/:80/:${PORT}/' /etc/apache2/sites-available/000-default.conf

EXPOSE ${PORT}
CMD ["/usr/local/bin/start.sh"]