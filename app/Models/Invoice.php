<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'serie',
        'correlative',
        'base',
        'igv',
        'total',
        'user_id',
        'created_at'
    ];

    // Query Scopes
    public function scopeFilter($query, $filters){
        $query->when($filters['serie'] ?? null, function($query, $serie){
            $query->where('serie', $serie);
        })->when($filters['fromNumber'] ?? null, function($query, $fromNumber){
            $query->where('correlative', '>=', $fromNumber);
        })->when($filters['toNumber'] ?? null, function($query, $toNumber){
            $query->where('correlative', '<=', $toNumber);
        })->when($filters['fromDate'] ?? null, function($query, $fromDate){
            $query->where('created_at', '>=', $fromDate);
        })->when($filters['toDate'] ?? null, function($query, $toDate){
            $query->where('created_at', '<=', $toDate);
        });
    }

    // Relación 1:n inversa users - invoices
    public function user(){
        return $this->belongsTo(User::class);
    }
}
