# 🚀 Início Rápido - Bet Tracker

## 📦 Arquivos do Projeto

Você recebeu o sistema completo de rastreamento de apostas. Siga os passos abaixo para colocar no ar!

---

## ⚡ Opção 1: Deploy Rápido no Heroku (5 minutos)

### Passo 1: Extraia os arquivos
```bash
unzip bet-tracker.zip
cd bet-tracker
```

### Passo 2: Instale o Heroku CLI
```bash
# Ubuntu/Debian/WSL
curl https://cli-assets.heroku.com/install.sh | sh

# macOS
brew install heroku/brew/heroku
```

### Passo 3: Login e Deploy
```bash
# Login no Heroku
heroku login

# Inicializa Git
git init
git add .
git commit -m "First deploy"

# Cria o app
heroku create seu-bet-tracker

# Adiciona PostgreSQL
heroku addons:create heroku-postgresql:mini

# Configura Tesseract (IMPORTANTE!)
heroku buildpacks:add --index 1 https://github.com/pathwaysmedical/heroku-buildpack-tesseract
heroku buildpacks:add heroku/php

# Deploy!
git push heroku main

# Abre o app
heroku open
```

**Pronto! Seu app está no ar! 🎉**

---

## 🏠 Opção 2: Testar Localmente (10 minutos)

### Pré-requisitos
- PHP 8.0+
- Composer
- Tesseract OCR

### Instalação

#### 1. Instale o Tesseract

**Ubuntu/Debian/WSL:**
```bash
sudo apt-get update
sudo apt-get install tesseract-ocr tesseract-ocr-por
```

**macOS:**
```bash
brew install tesseract tesseract-lang
```

**Windows:**
- Baixe: https://github.com/UB-Mannheim/tesseract/wiki
- Instale e adicione ao PATH

#### 2. Instale as dependências PHP
```bash
cd bet-tracker
composer install
```

#### 3. Configure permissões
```bash
chmod -R 777 data uploads
```

#### 4. Teste o ambiente
```bash
php -S localhost:8000
```

Abra: http://localhost:8000/test.php

#### 5. Se tudo estiver verde, acesse:
http://localhost:8000

**Sistema funcionando! 🎉**

---

## 📖 Estrutura do Projeto

```
bet-tracker/
├── src/                    # Classes PHP
│   ├── Database.php        # Gerenciamento do banco
│   ├── BetManager.php      # Lógica de apostas
│   ├── OCRProcessor.php    # Processamento de imagens
│   └── ExcelExporter.php   # Exportação de relatórios
├── index.php               # Interface principal
├── test.php               # Teste de ambiente
├── composer.json          # Dependências
├── Procfile              # Configuração Heroku
├── README.md             # Documentação completa
├── DEPLOY.md             # Guia de deploy detalhado
└── OCR-GUIDE.md          # Guia do OCR
```

---

## 🎯 Como Usar

### 1. Cadastrar Aposta
1. Digite seu nome
2. Faça upload do print
3. Clique em "Processar com OCR"
4. Pronto! Dados extraídos automaticamente

### 2. Ver Relatórios
- Todas as apostas aparecem na tabela
- Verde = Green (vitória)
- Vermelho = Red (derrota)

### 3. Filtrar
- Filtre por usuário
- Filtre por período
- Combine filtros

### 4. Exportar
- Excel (.xlsx) - Formatado e colorido
- CSV (.csv) - Para análise em outras ferramentas

### 5. Excluir
- Clique na lixeira
- Confirme a exclusão

---

## 🔧 Problemas Comuns

### "Tesseract not found"
**Solução:** Instale o Tesseract:
```bash
sudo apt-get install tesseract-ocr tesseract-ocr-por
```

### "Permission denied"
**Solução:** Dê permissões:
```bash
chmod -R 777 data uploads
```

### "Composer not found"
**Solução:** Instale o Composer:
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### OCR não reconhece os dados
**Solução:** 
- Use imagens nítidas e com boa resolução
- Veja dicas em: OCR-GUIDE.md

---

## 📚 Documentação Completa

- **README.md** - Documentação técnica completa
- **DEPLOY.md** - Guia detalhado de deploy
- **OCR-GUIDE.md** - Como tirar prints melhores

---

## 🆘 Precisa de Ajuda?

### Heroku não funciona?
1. Veja os logs: `heroku logs --tail`
2. Verifique buildpacks: `heroku buildpacks`
3. Consulte DEPLOY.md

### Local não funciona?
1. Execute: `php test.php`
2. Verifique os erros em vermelho
3. Siga as instruções de instalação

### OCR não funciona?
1. Verifique se o Tesseract está instalado
2. Teste com: `tesseract --version`
3. Leia OCR-GUIDE.md para dicas

---

## ✅ Checklist de Sucesso

- [ ] Heroku CLI instalado (se for usar Heroku)
- [ ] Git configurado
- [ ] PostgreSQL adicionado (Heroku)
- [ ] Buildpack do Tesseract configurado
- [ ] Deploy realizado
- [ ] App abrindo no navegador
- [ ] Upload de imagem funcionando
- [ ] OCR extraindo dados
- [ ] Exportação funcionando

---

## 🎉 Parabéns!

Se chegou até aqui, seu sistema está funcionando!

Agora você pode:
- ✅ Rastrear suas apostas automaticamente
- ✅ Gerar relatórios profissionais
- ✅ Analisar seu desempenho
- ✅ Exportar dados para Excel

**Boas apostas! 🍀**

---

## 📞 Suporte

- 📖 Leia a documentação em README.md
- 🚀 Guia de deploy em DEPLOY.md
- 📸 Dicas de OCR em OCR-GUIDE.md

**Desenvolvido com ❤️ para facilitar sua gestão de apostas**
