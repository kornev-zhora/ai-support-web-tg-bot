<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageStat extends Model
{
    /** @use HasFactory<\Database\Factories\MessageStatFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'stat_date',
        'channel',
        'message_count',
        'conversation_count',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stat_date' => 'date',
            'message_count' => 'integer',
            'conversation_count' => 'integer',
        ];
    }
}
