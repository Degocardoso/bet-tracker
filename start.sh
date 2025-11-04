#!/bin/bash
set -e

# Configura Apache para usar a porta do Render
sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# Inicia Apache
apache2-foreground