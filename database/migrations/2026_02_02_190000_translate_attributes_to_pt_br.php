<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $translations = [
            // Quotes
            ['code' => 'subject', 'entity_type' => 'quotes', 'name' => 'Assunto'],
            ['code' => 'description', 'entity_type' => 'quotes', 'name' => 'Descrição'],
            ['code' => 'user_id', 'entity_type' => 'quotes', 'name' => 'Responsável'],
            ['code' => 'expired_at', 'entity_type' => 'quotes', 'name' => 'Expira em'],
            ['code' => 'person_id', 'entity_type' => 'quotes', 'name' => 'Pessoa'],
            ['code' => 'billing_address', 'entity_type' => 'quotes', 'name' => 'Endereço de Faturamento'],
            ['code' => 'shipping_address', 'entity_type' => 'quotes', 'name' => 'Endereço de Entrega'],
            ['code' => 'discount_amount', 'entity_type' => 'quotes', 'name' => 'Desconto'],
            ['code' => 'tax_amount', 'entity_type' => 'quotes', 'name' => 'Imposto'],
            ['code' => 'adjustment_amount', 'entity_type' => 'quotes', 'name' => 'Ajuste'],
            ['code' => 'sub_total', 'entity_type' => 'quotes', 'name' => 'Sub Total'],
            ['code' => 'grand_total', 'entity_type' => 'quotes', 'name' => 'Total Geral'],

            // Leads
            ['code' => 'title', 'entity_type' => 'leads', 'name' => 'Título'],
            ['code' => 'description', 'entity_type' => 'leads', 'name' => 'Descrição'],
            ['code' => 'lead_value', 'entity_type' => 'leads', 'name' => 'Valor do Lead'],
            ['code' => 'user_id', 'entity_type' => 'leads', 'name' => 'Responsável'],
            ['code' => 'person_id', 'entity_type' => 'leads', 'name' => 'Pessoa'],
            ['code' => 'lead_source_id', 'entity_type' => 'leads', 'name' => 'Fonte'],
            ['code' => 'lead_type_id', 'entity_type' => 'leads', 'name' => 'Tipo'],
            ['code' => 'closed_at', 'entity_type' => 'leads', 'name' => 'Fechado em'],
            ['code' => 'expected_close_date', 'entity_type' => 'leads', 'name' => 'Fechamento Esperado'],

            // Persons
            ['code' => 'name', 'entity_type' => 'persons', 'name' => 'Nome'],
            ['code' => 'emails', 'entity_type' => 'persons', 'name' => 'E-mails'],
            ['code' => 'contact_numbers', 'entity_type' => 'persons', 'name' => 'Telefones'],
            ['code' => 'job_title', 'entity_type' => 'persons', 'name' => 'Cargo'],
            ['code' => 'organization_id', 'entity_type' => 'persons', 'name' => 'Organização'],

            // Organizations
            ['code' => 'name', 'entity_type' => 'organizations', 'name' => 'Nome'],
            ['code' => 'address', 'entity_type' => 'organizations', 'name' => 'Endereço'],

            // Products
            ['code' => 'name', 'entity_type' => 'products', 'name' => 'Nome'],
            ['code' => 'sku', 'entity_type' => 'products', 'name' => 'SKU'],
            ['code' => 'description', 'entity_type' => 'products', 'name' => 'Descrição'],
            ['code' => 'price', 'entity_type' => 'products', 'name' => 'Preço'],
        ];

        foreach ($translations as $translation) {
            DB::table('attributes')
                ->where('code', $translation['code'])
                ->where('entity_type', $translation['entity_type'])
                ->update(['name' => $translation['name']]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
