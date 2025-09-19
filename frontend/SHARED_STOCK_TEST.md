# 📦 TESTE DE ESTOQUE COMPARTILHADO

## 🎯 Como testar o sistema:

### **1. Verificar Produtos Criados:**
- ✅ **Cerveja Skol 350ml** (Produto Pai) - 240 unidades
- ✅ **Caixa Skol 350ml (24un)** (Produto Filho) - 10 caixas

### **2. Verificar Interface:**
- ✅ Tags visuais: 📦 Pai / 📎 Filho (24x)
- ✅ Estoque exibido corretamente
- ✅ Informação de relacionamento

### **3. Testar Formulário:**
- ✅ Checkbox "Este produto gerencia o próprio estoque"
- ✅ Dropdown de produtos pai (quando desmarcado)
- ✅ Campo "Multiplicador de estoque"
- ✅ Exemplos visuais

### **4. Testar Funcionalidades:**
- 🔄 Vender 1 cerveja → desconta 1 do estoque
- 🔄 Vender 1 caixa → desconta 24 do estoque
- 🔄 Adicionar estoque em qualquer produto atualiza ambos

### **5. Cenários de Teste:**

#### **Cenário 1: Venda de Unidade**
- Estado inicial: 240 cervejas / 10 caixas
- Vender: 1 cerveja
- Resultado esperado: 239 cervejas / 9 caixas (239/24)

#### **Cenário 2: Venda de Caixa**
- Estado inicial: 240 cervejas / 10 caixas  
- Vender: 1 caixa
- Resultado esperado: 216 cervejas / 9 caixas

#### **Cenário 3: Adicionar Estoque no Pai**
- Adicionar: 24 cervejas
- Resultado esperado: +24 cervejas / +1 caixa

#### **Cenário 4: Adicionar Estoque no Filho**
- Adicionar: 1 caixa
- Resultado esperado: +24 cervejas / +1 caixa

### **6. Verificações Backend:**
```bash
# Verificar produtos criados
curl http://localhost:8000/api/products

# Verificar produtos pai
curl http://localhost:8000/api/products/parents

# Adicionar estoque
curl -X POST http://localhost:8000/api/products/1/add-stock \
     -H "Content-Type: application/json" \
     -d '{"quantity": 24}'
```

## ✅ Sistema implementado com sucesso!
