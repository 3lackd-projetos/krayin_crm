<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Quotes Attributes
        DB::table('attributes')->where(['entity_type' => 'quotes', 'code' => 'subject'])->update(['name' => 'Assunto']);
        DB::table('attributes')->where(['entity_type' => 'quotes', 'code' => 'description'])->update(['name' => 'Descrição']);
        DB::table('attributes')->where(['entity_type' => 'quotes', 'code' => 'user_id'])->update(['name' => 'Responsável']);
        DB::table('attributes')->where(['entity_type' => 'quotes', 'code' => 'expired_at'])->update(['name' => 'Expira em']);
        DB::table('attributes')->where(['entity_type' => 'quotes', 'code' => 'person_id'])->update(['name' => 'Pessoa']);
        DB::table('attributes')->where(['entity_type' => 'quotes', 'code' => 'billing_address'])->update(['name' => 'Endereço de Cobrança']);
        DB::table('attributes')->where(['entity_type' => 'quotes', 'code' => 'shipping_address'])->update(['name' => 'Endereço de Entrega']);

        // Leads Attributes
        DB::table('attributes')->where(['entity_type' => 'leads', 'code' => 'title'])->update(['name' => 'Título']);
        DB::table('attributes')->where(['entity_type' => 'leads', 'code' => 'description'])->update(['name' => 'Descrição']);
        DB::table('attributes')->where(['entity_type' => 'leads', 'code' => 'lead_value'])->update(['name' => 'Valor da Oportunidade']);
        DB::table('attributes')->where(['entity_type' => 'leads', 'code' => 'expected_close_date'])->update(['name' => 'Fechamento Esperado']);
        DB::table('attributes')->where(['entity_type' => 'leads', 'code' => 'user_id'])->update(['name' => 'Responsável']);
        DB::table('attributes')->where(['entity_type' => 'leads', 'code' => 'lead_source_id'])->update(['name' => 'Origem']);
        DB::table('attributes')->where(['entity_type' => 'leads', 'code' => 'lead_type_id'])->update(['name' => 'Tipo']);
        DB::table('attributes')->where(['entity_type' => 'leads', 'code' => 'lead_pipeline_id'])->update(['name' => 'Funil']);
        DB::table('attributes')->where(['entity_type' => 'leads', 'code' => 'lead_pipeline_stage_id'])->update(['name' => 'Estágio']);

        // Persons Attributes
        DB::table('attributes')->where(['entity_type' => 'persons', 'code' => 'name'])->update(['name' => 'Nome']);
        DB::table('attributes')->where(['entity_type' => 'persons', 'code' => 'emails'])->update(['name' => 'E-mail']);
        DB::table('attributes')->where(['entity_type' => 'persons', 'code' => 'contact_numbers'])->update(['name' => 'Telefone']);
        DB::table('attributes')->where(['entity_type' => 'persons', 'code' => 'organization_id'])->update(['name' => 'Empresa']);
        DB::table('attributes')->where(['entity_type' => 'persons', 'code' => 'user_id'])->update(['name' => 'Responsável']);

        // Organizations Attributes
        DB::table('attributes')->where(['entity_type' => 'organizations', 'code' => 'name'])->update(['name' => 'Nome']);
        DB::table('attributes')->where(['entity_type' => 'organizations', 'code' => 'address'])->update(['name' => 'Endereço']);
        DB::table('attributes')->where(['entity_type' => 'organizations', 'code' => 'user_id'])->update(['name' => 'Responsável']);

        // Products Attributes
        DB::table('attributes')->where(['entity_type' => 'products', 'code' => 'name'])->update(['name' => 'Nome']);
        DB::table('attributes')->where(['entity_type' => 'products', 'code' => 'price'])->update(['name' => 'Preço']);
        DB::table('attributes')->where(['entity_type' => 'products', 'code' => 'quantity'])->update(['name' => 'Quantidade']);
        DB::table('attributes')->where(['entity_type' => 'products', 'code' => 'sku'])->update(['name' => 'Código']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed as this is a one-way translation fix for a specific installation
    }
};
