<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'support_ticket_id',
        'sender_id',
        'message',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function isFromClient(): bool
    {
        return $this->sender && $this->sender->isClient();
    }

    public function isFromAgent(): bool
    {
        return $this->sender && $this->sender->isAgent();
    }

    public function isFromAdmin(): bool
    {
        return $this->sender && $this->sender->isAdmin();
    }
}
