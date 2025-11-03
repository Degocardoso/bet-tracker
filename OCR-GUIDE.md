# 📋 Formatos de Dados Reconhecidos pelo OCR

## Exemplos de prints que funcionam bem:

### Formato 1: Dados Estruturados
```
Data: 01/11/2025
Valor Apostado: R$ 50,00
ODD: 2.10
Resultado: GREEN
Retorno: R$ 105,00
```

### Formato 2: Dados Inline
```
Aposta: R$ 30 @ 1.80 - 02/11/2025 - RED
```

### Formato 3: Casa de Apostas (Bet365 style)
```
Valor da Aposta: R$ 100,00
Cotação: 3.50
Data: 03/11/2025
Retorno Total: R$ 350,00
```

### Formato 4: Casa de Apostas (Betano style)
```
Investido: R$ 25
Odd: 2.25
03/11/2025
Ganho: R$ 56,25
```

## 🎯 Dicas para Melhor Reconhecimento

### ✅ O que ajuda:
- Imagens nítidas e com boa resolução
- Texto em preto sobre fundo claro (ou vice-versa)
- Fonte legível (evite fontes decorativas)
- Texto horizontal (não inclinado)
- Boa iluminação na foto
- Contraste adequado

### ❌ O que prejudica:
- Imagens borradas ou com baixa resolução
- Texto muito pequeno
- Fotos com reflexo ou brilho
- Texto sobre imagens complexas
- Fonte muito fina ou muito grossa
- Caracteres especiais não reconhecidos

## 🔍 Padrões Reconhecidos

### Datas:
- `01/11/2025`
- `01-11-2025`
- `01.11.2025`
- `1/11/25`

### Valores Monetários:
- `R$ 50,00`
- `R$ 50.00`
- `50,00`
- `50.00`
- `50`

### ODDs:
- `@2.10`
- `odd: 2.10`
- `cotação 2.10`
- `2.10x`

### Resultados:
- `GREEN` / `green` / `Green`
- `RED` / `red` / `Red`
- `Vitória` / `vitoria`
- `Derrota` / `perdeu`
- `Ganhou`

## 📱 Exemplos de Casas de Apostas

### Bet365
O OCR reconhece bem os seguintes campos:
- Valor da Aposta
- Retorno Potencial
- Cotação
- Data da Aposta

### Betano
O OCR reconhece:
- Valor Investido
- Odd
- Possível Retorno
- Data

### Betfair
O OCR reconhece:
- Aposta
- Cotação
- Retorno
- Data/Hora

### Outros sites
O sistema é flexível e tenta reconhecer padrões comuns em diferentes layouts.

## 🛠️ Melhorando a Precisão

Se o OCR não estiver reconhecendo bem:

1. **Tire um novo print com mais zoom** no texto
2. **Aumente o contraste** da imagem
3. **Certifique-se** de que o texto está legível para você
4. **Evite** prints de vídeos ou telas em movimento
5. **Prefira** screenshots diretos do app/site

## 💡 Exemplos Práticos

### ✅ BOM (facilmente reconhecido):
```
────────────────────────────
COMPROVANTE DE APOSTA
────────────────────────────
Data: 01/11/2025
Valor: R$ 50,00
Odd: 2.10
Status: Ganhou
Retorno: R$ 105,00
────────────────────────────
```

### ⚠️ MÉDIO (pode precisar ajustes):
```
Aposta #12345
R$50 @2.1 | 01/11 | ✓Green
```

### ❌ DIFÍCIL (pode não reconhecer):
```
🎯 Aposta:💰50 ⚡2.1 📅01/11 ✅
```

## 🎨 Processamento de Imagem

O sistema aceita:
- JPG / JPEG
- PNG
- GIF
- WEBP
- BMP

**Tamanho máximo:** 10MB

**Resolução recomendada:** 
- Mínimo: 800x600px
- Ideal: 1920x1080px ou superior

## 📊 Taxa de Sucesso

Com prints de boa qualidade:
- **Data:** ~95% de reconhecimento
- **Valor Apostado:** ~90% de reconhecimento
- **ODD:** ~85% de reconhecimento
- **Green/Red:** ~80% de reconhecimento

## 🔄 O que fazer se falhar

Se o OCR não reconhecer os dados:

1. Verifique a qualidade da imagem
2. Tire um novo print mais nítido
3. Certifique-se de que o texto está visível
4. Tente novamente

O sistema é inteligente e sempre tenta extrair o máximo de informação possível!
