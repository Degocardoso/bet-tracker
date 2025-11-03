#!/bin/bash

# Este script configura o Tesseract no Heroku
# Adicione este buildpack antes do deploy:
# heroku buildpacks:add --index 1 https://github.com/pathwaysmedical/heroku-buildpack-tesseract

# Ou configure manualmente no Heroku Dashboard > Settings > Buildpacks:
# 1. https://github.com/pathwaysmedical/heroku-buildpack-tesseract
# 2. heroku/php

echo "Tesseract será instalado automaticamente via buildpack"
