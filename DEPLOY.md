# 🚀 Guia Rápido de Deploy no Heroku

## Opção 1: Deploy Automático (Recomendado)

### 1. Preparar o repositório
```bash
cd bet-tracker
git init
git add .
git commit -m "Initial commit"
```

### 2. Criar conta no Heroku
- Acesse: https://signup.heroku.com/
- Crie uma conta gratuita

### 3. Instalar Heroku CLI
```bash
# Ubuntu/Debian
curl https://cli-assets.heroku.com/install.sh | sh

# macOS
brew install heroku/brew/heroku

# Windows
# Baixe em: https://devcenter.heroku.com/articles/heroku-cli
```

### 4. Login no Heroku
```bash
heroku login
```

### 5. Criar o app
```bash
heroku create seu-bet-tracker
```

### 6. Adicionar PostgreSQL
```bash
heroku addons:create heroku-postgresql:mini
```

### 7. Configurar Buildpacks (IMPORTANTE!)
```bash
# Adiciona Tesseract OCR (deve ser o primeiro)
heroku buildpacks:add --index 1 https://github.com/pathwaysmedical/heroku-buildpack-tesseract

# Adiciona PHP
heroku buildpacks:add heroku/php
```

### 8. Deploy
```bash
git push heroku main
```

### 9. Abrir o app
```bash
heroku open
```

---

## Opção 2: Deploy via GitHub

### 1. Criar repositório no GitHub
```bash
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/SEU-USUARIO/bet-tracker.git
git push -u origin main
```

### 2. No Heroku Dashboard
1. Acesse: https://dashboard.heroku.com/
2. Clique em **"New"** → **"Create new app"**
3. Escolha um nome para o app
4. Selecione a região (Estados Unidos ou Europa)
5. Clique em **"Create app"**

### 3. Conectar com GitHub
1. Na aba **"Deploy"**
2. Em **"Deployment method"**, escolha **"GitHub"**
3. Conecte sua conta do GitHub
4. Busque o repositório **"bet-tracker"**
5. Clique em **"Connect"**

### 4. Adicionar PostgreSQL
1. Vá na aba **"Resources"**
2. Em **"Add-ons"**, busque por **"Heroku Postgres"**
3. Selecione o plano **"Mini"** (gratuito)
4. Clique em **"Submit Order Form"**

### 5. Configurar Buildpacks
1. Vá na aba **"Settings"**
2. Role até **"Buildpacks"**
3. Clique em **"Add buildpack"**
4. Adicione primeiro: `https://github.com/pathwaysmedical/heroku-buildpack-tesseract`
5. Adicione depois: `heroku/php`
6. **IMPORTANTE:** O Tesseract deve estar ACIMA do PHP

### 6. Deploy
1. Volte na aba **"Deploy"**
2. Role até **"Manual deploy"**
3. Selecione a branch **"main"**
4. Clique em **"Deploy Branch"**
5. Aguarde o build completar

### 7. Abrir o app
- Clique em **"Open app"** no canto superior direito

---

## Opção 3: Deploy com One-Click

### 1. Clique no botão abaixo:

[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy)

### 2. Preencha as informações
- Nome do app
- Região
- Clique em **"Deploy app"**

### 3. Aguarde o deploy
- O Heroku instalará tudo automaticamente
- PostgreSQL será configurado
- Tesseract será instalado

---

## ⚠️ Problemas Comuns

### Erro: "Application error"
```bash
# Veja os logs
heroku logs --tail -a seu-bet-tracker
```

### Erro: "Tesseract not found"
**Solução:** Verifique se o buildpack do Tesseract foi adicionado ANTES do PHP:
```bash
heroku buildpacks -a seu-bet-tracker
```

Deve aparecer:
```
1. https://github.com/pathwaysmedical/heroku-buildpack-tesseract
2. heroku/php
```

Se estiver errado:
```bash
heroku buildpacks:clear -a seu-bet-tracker
heroku buildpacks:add --index 1 https://github.com/pathwaysmedical/heroku-buildpack-tesseract
heroku buildpacks:add heroku/php
```

### Erro: "Database connection failed"
**Solução:** Certifique-se de que o PostgreSQL foi adicionado:
```bash
heroku addons -a seu-bet-tracker
```

Se não aparecer o Postgres:
```bash
heroku addons:create heroku-postgresql:mini -a seu-bet-tracker
```

### Upload de imagens não funciona
**Solução:** O Heroku tem sistema de arquivos efêmero. Para produção, use S3:
```bash
heroku addons:create bucketeer:hobbyist -a seu-bet-tracker
```

---

## 🔧 Comandos Úteis

### Ver logs em tempo real
```bash
heroku logs --tail -a seu-bet-tracker
```

### Reiniciar o app
```bash
heroku restart -a seu-bet-tracker
```

### Acessar o banco de dados
```bash
heroku pg:psql -a seu-bet-tracker
```

### Ver variáveis de ambiente
```bash
heroku config -a seu-bet-tracker
```

### Executar comandos no Heroku
```bash
heroku run bash -a seu-bet-tracker
```

---

## 📱 Acessar o App

Após o deploy bem-sucedido, seu app estará disponível em:
```
https://seu-bet-tracker.herokuapp.com
```

---

## 🎉 Pronto!

Seu sistema de apostas está no ar! Agora você pode:
- ✅ Fazer upload de prints
- ✅ Extrair dados automaticamente
- ✅ Gerar relatórios
- ✅ Exportar para Excel

**Dica:** Salve a URL do seu app para acessar de qualquer lugar!
