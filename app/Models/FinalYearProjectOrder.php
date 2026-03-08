<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalYearProjectOrder extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'order_reference',
        'customer_name',
        'customer_email',
        'customer_phone',
        'institution',
        'project_title',
        'system_name',
        'system_repo_url',
        'source_zip_path',
        'source_zip_original_name',
        'package_key',
        'package_label',
        'domain_name',
        'notes',
        'currency',
        'amount',
        'payment_status',
        'payment_status_description',
        'order_tracking_id',
        'pesapal_redirect_url',
        'gateway_response',
        'gateway_status_payload',
        'paid_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'gateway_response' => 'array',
            'gateway_status_payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }
}
