<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SlotStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'slot_status_history';

    protected $fillable = [
        'slot_type',
        'date',
        'hour',
        'slot_index',
        'previous_status',
        'new_status',
        'changed_by',
        'notes',
        'ip_address',
    ];

    protected $casts = [
        'date' => 'date',
        'hour' => 'integer',
        'slot_index' => 'integer',
    ];

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
