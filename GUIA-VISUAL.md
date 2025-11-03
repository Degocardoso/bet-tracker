# 🎨 GUIA VISUAL - Bet Tracker

## 📦 O QUE VOCÊ RECEBEU

```
bet-tracker.zip (30KB)
├── 22 arquivos
├── Sistema completo
└── Pronto para deploy
```

---

## 🚀 INSTALAÇÃO EM 3 PASSOS

### PASSO 1: Extrair
```
📁 bet-tracker.zip
    ↓ (extrair)
📂 bet-tracker/
   ├── 📄 index.php (sistema principal)
   ├── 📁 src/ (classes PHP)
   ├── 📄 composer.json (dependências)
   └── 📚 documentação completa
```

### PASSO 2: Escolher Ambiente

#### Opção A: Heroku (Online - Recomendado)
```
💻 Seu computador
    ↓ (heroku login)
☁️ Heroku
    ↓ (git push)
🌐 https://seu-app.herokuapp.com
    ✅ Sistema funcionando!
```

#### Opção B: Local (Teste)
```
💻 Seu computador
    ↓ (composer install)
📦 Dependências instaladas
    ↓ (php -S localhost:8000)
🌐 http://localhost:8000
    ✅ Sistema funcionando!
```

### PASSO 3: Usar
```
📸 Tirar print da aposta
    ↓ (upload)
🤖 OCR processa imagem
    ↓ (extrai dados)
💾 Salva no banco
    ↓
📊 Visualiza relatórios
    ✅ Pronto!
```

---

## 🎯 FLUXO DE USO

```
┌─────────────────────────────────────────────┐
│         TELA INICIAL                         │
│  ┌────────────────────────────────────┐    │
│  │ 📊 ESTATÍSTICAS                    │    │
│  │ • Total Apostas: 15                │    │
│  │ • Total Investido: R$ 750          │    │
│  │ • Greens: 10 🟢                    │    │
│  │ • Reds: 5 🔴                       │    │
│  └────────────────────────────────────┘    │
│                                              │
│  ┌────────────────────────────────────┐    │
│  │ 📤 NOVA APOSTA                     │    │
│  │                                     │    │
│  │ Nome: [_____________]              │    │
│  │                                     │    │
│  │ ┌──────────────────────┐          │    │
│  │ │  📸 Arraste o print  │          │    │
│  │ │  ou clique aqui      │          │    │
│  │ └──────────────────────┘          │    │
│  │                                     │    │
│  │ [🤖 Processar com OCR]            │    │
│  └────────────────────────────────────┘    │
│                                              │
│  ┌────────────────────────────────────┐    │
│  │ 🔍 FILTROS                         │    │
│  │ Usuário: [▼] Data: [__] a [__]   │    │
│  │ [🔎 Filtrar]                      │    │
│  └────────────────────────────────────┘    │
│                                              │
│  ┌────────────────────────────────────┐    │
│  │ 📋 RELATÓRIO  [📊Excel] [📄CSV]   │    │
│  ├──────┬────────┬─────┬───────┬─────┤    │
│  │ Data │ Valor  │ ODD │ Green │ Red │    │
│  ├──────┼────────┼─────┼───────┼─────┤    │
│  │01/11 │ R$ 50  │2.10 │ R$105 │  -  │🟢 │
│  │02/11 │ R$ 30  │1.80 │   -   │ R$0 │🔴 │
│  │03/11 │ R$ 100 │3.50 │ R$350 │  -  │🟢 │
│  └──────┴────────┴─────┴───────┴─────┘    │
└─────────────────────────────────────────────┘
```

---

## 🎨 CORES E ELEMENTOS

### Dashboard
```
┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐
│   🎯 15     │ │  💰 R$750   │ │  🟢 10      │ │  🔴 5       │
│  Apostas    │ │  Investido  │ │  Greens     │ │  Reds       │
└─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘
   Roxo/Azul       Roxo/Azul        Verde           Vermelho
```

### Tabela
```
Linha Verde (Green):  ══════════════════════════ 🟢
Linha Vermelha (Red): ══════════════════════════ 🔴
Linha Normal:         ══════════════════════════ ⚪
```

---

## 📱 RESPONSIVIDADE

### Desktop (>1200px)
```
┌──────────────────────────────────────┐
│  [Stats] [Stats] [Stats] [Stats]   │
│  [Upload ─────────────────────]     │
│  [Filtros ────────────────────]     │
│  [Tabela completa ────────────]     │
└──────────────────────────────────────┘
```

### Tablet (768px-1200px)
```
┌────────────────────────┐
│ [Stats]  [Stats]      │
│ [Stats]  [Stats]      │
│ [Upload ─────────]    │
│ [Filtros ────────]    │
│ [Tabela ─────────]    │
└────────────────────────┘
```

### Mobile (<768px)
```
┌─────────────┐
│  [Stats]    │
│  [Stats]    │
│  [Stats]    │
│  [Stats]    │
│  [Upload]   │
│  [Filtros]  │
│  [Tabela]   │
└─────────────┘
```

---

## 🗂️ ESTRUTURA DE ARQUIVOS

```
bet-tracker/
│
├── 📄 index.php              ← Interface principal (PRINCIPAL)
├── 📄 test.php               ← Testa configuração
├── 📄 composer.json          ← Dependências PHP
├── 📄 Procfile               ← Config Heroku
├── 📄 .htaccess              ← Config Apache
│
├── 📁 src/                   ← Classes PHP
│   ├── Database.php          ← Banco de dados
│   ├── BetManager.php        ← Gerencia apostas
│   ├── OCRProcessor.php      ← OCR Tesseract
│   ├── OCRProcessorGoogleVision.php  ← OCR Google
│   └── ExcelExporter.php     ← Exporta Excel/CSV
│
├── 📁 data/                  ← Banco SQLite (local)
├── 📁 uploads/               ← Imagens enviadas
│
└── 📚 Documentação
    ├── README.md             ← Documentação técnica completa
    ├── QUICKSTART.md         ← Início rápido (5 min)
    ├── DEPLOY.md             ← Deploy Heroku detalhado
    ├── OCR-GUIDE.md          ← Guia do OCR
    ├── COMMANDS.md           ← Comandos úteis
    └── PROJETO-RESUMO.md     ← Este resumo
```

---

## 🎯 ARQUIVOS POR PRIORIDADE

### 🔥 ESSENCIAIS (para funcionar)
1. `index.php` - Sistema principal
2. `src/*.php` - Classes necessárias
3. `composer.json` - Dependências
4. `Procfile` - Deploy Heroku

### 📖 DOCUMENTAÇÃO (para entender)
1. `QUICKSTART.md` - **COMECE AQUI!**
2. `README.md` - Completo
3. `DEPLOY.md` - Deploy
4. `PROJETO-RESUMO.md` - Resumo

### 🛠️ AUXILIARES (para facilitar)
1. `test.php` - Testa ambiente
2. `OCR-GUIDE.md` - Dicas OCR
3. `COMMANDS.md` - Comandos

---

## 🚀 QUAL ARQUIVO LER PRIMEIRO?

```
Você é:

🆕 Iniciante?
   └─→ QUICKSTART.md (5 minutos para começar)

👨‍💻 Desenvolvedor?
   └─→ README.md (documentação técnica completa)

☁️ Vai fazer deploy?
   └─→ DEPLOY.md (guia passo a passo)

🤔 Quer entender tudo?
   └─→ PROJETO-RESUMO.md (overview completo)

💻 Vai gerenciar?
   └─→ COMMANDS.md (comandos úteis)

📸 Problemas com OCR?
   └─→ OCR-GUIDE.md (dicas de prints)
```

---

## 🎨 PALETA DE CORES

```css
Primary (Roxo):   #667eea ████████
Secondary (Rosa): #764ba2 ████████
Success (Verde):  #28a745 ████████
Danger (Vermelho):#dc3545 ████████
Info (Azul):      #17a2b8 ████████
Warning (Amarelo):#ffc107 ████████
```

---

## 📊 DADOS DO PROJETO

```
📦 Tamanho total: ~30KB (compactado)
📄 Total de arquivos: 22
📝 Linhas de código: ~2.000
🔧 Classes PHP: 5
📚 Documentação: 6 arquivos
⏱️ Tempo de leitura: ~30 minutos (toda doc)
🚀 Tempo de deploy: 5-10 minutos
```

---

## ✅ CHECKLIST DE USO

```
Antes de começar:
□ Extraiu o bet-tracker.zip
□ Leu o QUICKSTART.md
□ Escolheu ambiente (Heroku ou Local)

Para Heroku:
□ Criou conta no Heroku
□ Instalou Heroku CLI
□ Configurou Git
□ Fez deploy

Para Local:
□ Instalou PHP 8+
□ Instalou Composer
□ Instalou Tesseract OCR
□ Rodou composer install

Primeiro uso:
□ Acessou o sistema
□ Testou upload de print
□ Verificou OCR funcionando
□ Viu dados na tabela
□ Testou exportação

Está funcionando? 🎉
```

---

## 💡 DICAS RÁPIDAS

```
✅ Sempre leia o QUICKSTART.md primeiro
✅ Use prints nítidos para melhor OCR
✅ Teste localmente antes do deploy
✅ Faça backup do banco regularmente
✅ Monitore os logs do Heroku
✅ Mantenha dependências atualizadas
```

---

## 🎯 PRÓXIMOS PASSOS

```
1. Extrair bet-tracker.zip
   └─→ Você está aqui! 📍

2. Ler QUICKSTART.md
   └─→ 5 minutos

3. Escolher ambiente
   ├─→ Heroku (recomendado)
   └─→ Local (teste)

4. Seguir instruções
   └─→ Deploy em 5-10 min

5. Usar o sistema
   └─→ Cadastrar apostas

6. Gerar relatórios
   └─→ Exportar Excel

7. Analisar resultados
   └─→ Melhorar apostas

✅ Sucesso!
```

---

## 📞 PRECISA DE AJUDA?

```
Erro no OCR?
└─→ Leia OCR-GUIDE.md

Erro no deploy?
└─→ Leia DEPLOY.md

Comando não funciona?
└─→ Leia COMMANDS.md

Dúvida técnica?
└─→ Leia README.md

Quer visão geral?
└─→ Leia PROJETO-RESUMO.md
```

---

## 🎉 RESULTADO FINAL

```
Você terá:

📱 App web responsivo
🤖 OCR automático
💾 Banco de dados
📊 Relatórios Excel/CSV
📈 Dashboard estatísticas
🎨 Design moderno
☁️ Deploy no Heroku
✅ Sistema completo!

Tudo pronto para uso! 🚀
```

---

**Desenvolvido com ❤️ para facilitar sua gestão de apostas**

🍀 Boa sorte nas apostas!
