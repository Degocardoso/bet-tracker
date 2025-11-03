# 🎯 Bet Tracker - Sistema de Rastreamento de Apostas com OCR

Sistema completo para gerenciamento de apostas esportivas com extração automática de dados via OCR (Optical Character Recognition).

## 🚀 Funcionalidades

- ✅ Upload de prints de apostas (imagens)
- 🤖 Extração automática de dados via OCR (Tesseract)
- 📊 Relatórios completos com filtros
- 📈 Dashboard com estatísticas em tempo real
- 📥 Exportação para Excel (.xlsx) e CSV
- 🗑️ Exclusão de apostas com confirmação
- 👥 Multi-usuário
- 📱 Design responsivo (Bootstrap 5)
- 🎨 Interface moderna e intuitiva

## 🏗️ Estrutura de Dados

Cada aposta contém:
- **Data** - Data da aposta
- **Valor Apostado** - Valor investido
- **ODD** - Cotação da aposta
- **Green** - Valor ganho (vitória)
- **Red** - Valor perdido (derrota)
- **Usuário** - Nome do apostador

## 🛠️ Tecnologias Utilizadas

- PHP 8+
- Bootstrap 5
- PostgreSQL (Heroku) / SQLite (desenvolvimento local)
- Tesseract OCR
- PHPOffice/PhpSpreadsheet
- Bootstrap Icons

## 📦 Instalação Local

### Pré-requisitos

- PHP 8.0 ou superior
- Composer
- Tesseract OCR instalado no sistema
- Extensões PHP: PDO, GD

### Passo a Passo

1. **Clone o repositório**
```bash
git clone https://github.com/yourusername/bet-tracker.git
cd bet-tracker
```

2. **Instale as dependências**
```bash
composer install
```

3. **Instale o Tesseract OCR**

**Ubuntu/Debian:**
```bash
sudo apt-get update
sudo apt-get install tesseract-ocr tesseract-ocr-por
```

**macOS:**
```bash
brew install tesseract tesseract-lang
```

**Windows:**
- Baixe e instale de: https://github.com/UB-Mannheim/tesseract/wiki

4. **Configure permissões**
```bash
chmod -R 777 data/
chmod -R 777 uploads/
```

5. **Inicie o servidor**
```bash
php -S localhost:8000
```

6. **Acesse no navegador**
```
http://localhost:8000
```

## 🌐 Deploy no Heroku

### Método 1: Via Heroku CLI

1. **Instale o Heroku CLI**
```bash
# Ubuntu/Debian
curl https://cli-assets.heroku.com/install.sh | sh

# macOS
brew install heroku/brew/heroku
```

2. **Login no Heroku**
```bash
heroku login
```

3. **Crie o app**
```bash
heroku create nome-do-seu-app
```

4. **Adicione o banco PostgreSQL**
```bash
heroku addons:create heroku-postgresql:mini
```

5. **Configure buildpack**
```bash
heroku buildpacks:set heroku/php
```

6. **Instale o Tesseract no Heroku**
```bash
heroku buildpacks:add --index 1 https://github.com/pathwaysmedical/heroku-buildpack-tesseract
```

7. **Deploy**
```bash
git init
git add .
git commit -m "Initial commit"
git push heroku main
```

8. **Abra o app**
```bash
heroku open
```

### Método 2: Via GitHub Integration

1. Faça push do código para o GitHub
2. Acesse o [Dashboard do Heroku](https://dashboard.heroku.com)
3. Clique em "New" → "Create new app"
4. Conecte com seu repositório GitHub
5. Ative o "Automatic Deploys"
6. Adicione o add-on PostgreSQL
7. Configure o buildpack do Tesseract nas Settings

## 📝 Como Usar

### 1. Cadastrar Nova Aposta

1. Digite seu nome no campo "Seu Nome"
2. Faça upload do print da aposta
3. Clique em "Processar com OCR"
4. O sistema extrairá automaticamente os dados

### 2. Visualizar Apostas

- Todas as apostas aparecem na tabela
- Apostas vencedoras (Green) ficam em verde
- Apostas perdedoras (Red) ficam em vermelho

### 3. Filtrar Apostas

- Filtre por usuário
- Filtre por período (data início e fim)
- Combine múltiplos filtros

### 4. Exportar Relatórios

- Clique em "Excel" para baixar .xlsx
- Clique em "CSV" para baixar .csv
- Os filtros ativos são aplicados na exportação

### 5. Excluir Aposta

1. Clique no botão vermelho de lixeira
2. Confirme a exclusão no modal
3. A aposta será removida permanentemente

## 🎨 Personalização

### Cores

Edite as variáveis CSS no `index.php`:
```css
:root {
    --green-color: #28a745;
    --red-color: #dc3545;
}
```

### Logo

Substitua o ícone no header:
```html
<i class="bi bi-trophy"></i>
```

## 🔧 Configuração Avançada

### Melhorar Precisão do OCR

Edite `src/OCRProcessor.php`:
```php
$text = (new TesseractOCR($imagePath))
    ->lang('por')
    ->psm(6) // Page Segmentation Mode
    ->oem(3) // OCR Engine Mode
    ->run();
```

### Adicionar Campos Personalizados

1. Edite `src/Database.php` para adicionar colunas
2. Modifique `src/OCRProcessor.php` para extrair novos dados
3. Atualize `index.php` para exibir os campos

## 🐛 Troubleshooting

### Erro: "Tesseract not found"
```bash
# Verifique se o Tesseract está instalado
tesseract --version

# Adicione ao PATH se necessário (Linux)
export PATH=$PATH:/usr/bin/tesseract
```

### Erro: "Permission denied"
```bash
# Dê permissões aos diretórios
chmod -R 777 uploads/
chmod -R 777 data/
```

### Erro no Heroku: "Application error"
```bash
# Veja os logs
heroku logs --tail
```

## 📊 Formato dos Dados Exportados

### Excel (.xlsx)
- Formatação colorida (verde para greens, vermelho para reds)
- Valores monetários formatados (R$)
- Colunas auto-dimensionadas

### CSV (.csv)
- Separador: ponto e vírgula (;)
- Compatível com Excel brasileiro
- Fácil importação em outras ferramentas

## 🔐 Segurança

- Validação de upload de imagens
- Proteção contra SQL Injection (prepared statements)
- Sanitização de inputs
- Confirmação antes de exclusões

## 📄 Licença

Este projeto está sob a licença MIT.

## 👨‍💻 Autor

Desenvolvido com ❤️ para facilitar o gerenciamento de apostas esportivas.

## 🤝 Contribuições

Contribuições são bem-vindas! Sinta-se à vontade para:
- Reportar bugs
- Sugerir novas funcionalidades
- Enviar pull requests

## 📞 Suporte

Para dúvidas ou problemas:
- Abra uma issue no GitHub
- Consulte a documentação do Tesseract: https://tesseract-ocr.github.io/

---

**Nota:** O OCR funciona melhor com imagens de boa qualidade. Para melhores resultados, certifique-se de que o print esteja nítido e legível.
