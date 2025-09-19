<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Configurações da Loja
        Setting::set('store.name', 'ClickVenda', 'string', 'Nome da loja');
        Setting::set('store.cnpj', '', 'string', 'CNPJ da loja');
        Setting::set('store.phone', '', 'string', 'Telefone da loja');
        Setting::set('store.email', '', 'string', 'Email da loja');
        Setting::set('store.address', '', 'string', 'Endereço da loja');
        Setting::set('store.city', '', 'string', 'Cidade da loja');
        Setting::set('store.state', '', 'string', 'Estado da loja');
        Setting::set('store.zip_code', '', 'string', 'CEP da loja');
        Setting::set('store.receipt_footer', 'Obrigado pela preferência! Volte sempre!', 'string', 'Rodapé dos comprovantes');
        Setting::set('store.whatsapp', '', 'string', 'WhatsApp da loja');
        Setting::set('store.print_template', '80mm', 'string', 'Modelo de impressão (80mm, 58mm, a4)');
        Setting::set('store.whatsapp_message_template', '🛒 *CLICKVENDA - Comprovante de Venda*

📋 *Pedido:* #{PEDIDO_ID}
📅 *Data:* {DATA}
🕒 *Hora:* {HORA}

👤 *Cliente:* {CLIENTE_NOME}

📦 *Itens:*
{ITENS_LISTA}

💰 *Total: R$ {TOTAL}*

💳 *Pagamento:*
{FORMAS_PAGAMENTO}

✅ *Status:* {STATUS}

Obrigado pela preferência! 🙏', 'string', 'Template personalizado para mensagens do WhatsApp');

        // Configurações de Vendas
        Setting::set('sales.default_payment_term', 30, 'number', 'Prazo padrão para pagamentos a prazo (dias)');
        Setting::set('sales.default_credit_limit', 500.00, 'number', 'Limite de crédito padrão para novos clientes');
        Setting::set('sales.credit_alert_percentage', 80, 'number', 'Percentual de uso do crédito para alerta');
        Setting::set('sales.auto_send_reminders', false, 'boolean', 'Enviar lembretes automáticos de vencimento');

        // Configurações do Sistema
        Setting::set('system.require_customer_for_credit', true, 'boolean', 'Exigir cliente para vendas a prazo');
        Setting::set('system.block_negative_stock', true, 'boolean', 'Bloquear vendas com estoque negativo');
        Setting::set('system.auto_backup', true, 'boolean', 'Backup automático do sistema');
        Setting::set('system.theme', 'light', 'string', 'Tema da interface');
        Setting::set('system.language', 'pt-BR', 'string', 'Idioma do sistema');
        Setting::set('system.items_per_page', 25, 'number', 'Itens por página nas listagens');
        Setting::set('system.auto_print_receipts', false, 'boolean', 'Imprimir comprovantes automaticamente');
    }
}
