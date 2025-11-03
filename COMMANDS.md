# 🛠️ Comandos Úteis - Bet Tracker

## 🚀 Heroku

### Deploy e Gerenciamento
```bash
# Ver logs em tempo real
heroku logs --tail -a seu-app

# Reiniciar o app
heroku restart -a seu-app

# Abrir o app no navegador
heroku open -a seu-app

# Ver informações do app
heroku info -a seu-app
```

### Buildpacks
```bash
# Listar buildpacks
heroku buildpacks -a seu-app

# Limpar e reconfigurar buildpacks
heroku buildpacks:clear -a seu-app
heroku buildpacks:add --index 1 https://github.com/pathwaysmedical/heroku-buildpack-tesseract
heroku buildpacks:add heroku/php

# Forçar rebuild
git commit --allow-empty -m "Force rebuild"
git push heroku main
```

### Banco de Dados
```bash
# Conectar ao PostgreSQL
heroku pg:psql -a seu-app

# Ver informações do banco
heroku pg:info -a seu-app

# Fazer backup
heroku pg:backups:capture -a seu-app

# Restaurar backup
heroku pg:backups:restore -a seu-app

# Ver dados
heroku pg:psql -a seu-app -c "SELECT * FROM bets LIMIT 10;"

# Limpar tabela
heroku pg:psql -a seu-app -c "DELETE FROM bets;"
```

### Variáveis de Ambiente
```bash
# Ver todas as variáveis
heroku config -a seu-app

# Adicionar variável
heroku config:set MINHA_VAR=valor -a seu-app

# Remover variável
heroku config:unset MINHA_VAR -a seu-app
```

### Add-ons
```bash
# Listar add-ons
heroku addons -a seu-app

# Adicionar PostgreSQL
heroku addons:create heroku-postgresql:mini -a seu-app

# Ver detalhes do PostgreSQL
heroku addons:info heroku-postgresql -a seu-app
```

---

## 💻 Local (Desenvolvimento)

### Servidor PHP
```bash
# Iniciar servidor na porta 8000
php -S localhost:8000

# Iniciar em outra porta
php -S localhost:3000

# Permitir acesso externo
php -S 0.0.0.0:8000
```

### Composer
```bash
# Instalar dependências
composer install

# Atualizar dependências
composer update

# Adicionar pacote
composer require nome/pacote

# Remover pacote
composer remove nome/pacote

# Limpar cache
composer clear-cache

# Validar composer.json
composer validate
```

### Tesseract
```bash
# Verificar versão
tesseract --version

# Listar idiomas instalados
tesseract --list-langs

# Testar OCR em uma imagem
tesseract imagem.jpg saida

# Testar OCR com português
tesseract imagem.jpg saida -l por
```

### Git
```bash
# Inicializar repositório
git init

# Adicionar todos os arquivos
git add .

# Fazer commit
git commit -m "Mensagem do commit"

# Ver status
git status

# Ver histórico
git log --oneline

# Criar nova branch
git checkout -b nova-feature

# Voltar para main
git checkout main
```

---

## 🗄️ SQLite (Local)

### Acessar banco SQLite
```bash
# Abrir banco
sqlite3 data/bets.db

# Ver tabelas
.tables

# Ver estrutura da tabela
.schema bets

# Consultar dados
SELECT * FROM bets;

# Contar apostas
SELECT COUNT(*) FROM bets;

# Apostas por usuário
SELECT usuario, COUNT(*) as total FROM bets GROUP BY usuario;

# Total investido
SELECT SUM(valor_apostado) FROM bets;

# Greens vs Reds
SELECT 
  COUNT(CASE WHEN green IS NOT NULL THEN 1 END) as greens,
  COUNT(CASE WHEN red IS NOT NULL THEN 1 END) as reds
FROM bets;

# Sair do SQLite
.quit
```

### Backup e Restauração (SQLite)
```bash
# Fazer backup
cp data/bets.db data/bets_backup_$(date +%Y%m%d).db

# Restaurar backup
cp data/bets_backup_20241103.db data/bets.db

# Exportar para SQL
sqlite3 data/bets.db .dump > backup.sql

# Importar de SQL
sqlite3 data/bets.db < backup.sql
```

---

## 🧪 Testes e Debug

### PHP
```bash
# Verificar sintaxe
php -l index.php

# Executar script de teste
php test.php

# Ver configuração PHP
php -i | grep -i "tesseract\|gd\|pdo"

# Ver extensões instaladas
php -m

# Verificar versão
php -v
```

### Debug de Upload
```bash
# Ver limites de upload
php -i | grep -i "upload\|post"

# Aumentar limite (php.ini)
# upload_max_filesize = 10M
# post_max_size = 10M
```

### Permissões
```bash
# Ver permissões
ls -la data uploads

# Dar permissões totais
chmod 777 data uploads

# Dar permissões recursivas
chmod -R 777 data uploads

# Ver dono dos arquivos
ls -la

# Mudar dono
sudo chown -R www-data:www-data data uploads
```

---

## 📦 Manutenção

### Limpeza
```bash
# Remover uploads antigos (mais de 30 dias)
find uploads -type f -mtime +30 -delete

# Limpar cache do Composer
composer clear-cache

# Limpar logs do Heroku (local)
> storage/logs/laravel.log
```

### Otimização
```bash
# Otimizar autoloader
composer dump-autoload -o

# Limpar vendor e reinstalar
rm -rf vendor
composer install --no-dev --optimize-autoloader
```

### Backup Completo
```bash
# Backup do projeto
tar -czf bet-tracker-backup-$(date +%Y%m%d).tar.gz bet-tracker/

# Extrair backup
tar -xzf bet-tracker-backup-20241103.tar.gz
```

---

## 🔍 Monitoramento

### Heroku
```bash
# Ver uso de recursos
heroku ps -a seu-app

# Ver métricas
heroku metrics -a seu-app

# Ver eventos
heroku releases -a seu-app
```

### Local
```bash
# Ver processos PHP
ps aux | grep php

# Monitorar logs do PHP
tail -f /var/log/php/error.log

# Ver uso de disco
df -h

# Ver uso de memória
free -m
```

---

## 🚨 Resolução de Problemas

### Reset Completo (Heroku)
```bash
# Recriar banco
heroku pg:reset DATABASE_URL -a seu-app --confirm seu-app

# Limpar buildpacks
heroku buildpacks:clear -a seu-app

# Reconfigurar tudo
heroku buildpacks:add --index 1 https://github.com/pathwaysmedical/heroku-buildpack-tesseract
heroku buildpacks:add heroku/php

# Deploy novamente
git push heroku main --force
```

### Reset Completo (Local)
```bash
# Remover banco
rm data/bets.db

# Remover vendor
rm -rf vendor

# Reinstalar
composer install

# Recriar diretórios
mkdir -p data uploads
chmod 777 data uploads

# Reiniciar servidor
php -S localhost:8000
```

---

## 📝 Scripts Úteis

### Script de Backup Automático
```bash
#!/bin/bash
# backup.sh
DATE=$(date +%Y%m%d_%H%M%S)
tar -czf "backups/bet-tracker-$DATE.tar.gz" data/ uploads/
echo "Backup criado: bet-tracker-$DATE.tar.gz"
```

### Script de Deploy
```bash
#!/bin/bash
# deploy.sh
git add .
git commit -m "Deploy $(date +%Y-%m-%d)"
git push heroku main
heroku open
```

### Script de Teste
```bash
#!/bin/bash
# test.sh
echo "Testando PHP..."
php -v
echo "Testando Tesseract..."
tesseract --version
echo "Testando servidor..."
php test.php
```

---

## 🎯 Dicas Finais

1. **Sempre faça backup antes de mudanças grandes**
2. **Use git para versionar seu código**
3. **Monitore os logs regularmente**
4. **Mantenha o Tesseract atualizado**
5. **Teste localmente antes de fazer deploy**

---

**💡 Dica:** Adicione estes comandos aos seus favoritos para acesso rápido!
