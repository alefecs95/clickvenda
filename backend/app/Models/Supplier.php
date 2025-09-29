<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'cpf_cnpj',
        'address',
        'city',
        'state',
        'zip_code',
        'contact_person',
        'bank_name',
        'bank_agency',
        'bank_account',
        'pix_key',
        'notes',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Relacionamento com PayablePayment
     */
    public function payablePayments()
    {
        return $this->hasMany(PayablePayment::class);
    }

    /**
     * Scope para fornecedores ativos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
