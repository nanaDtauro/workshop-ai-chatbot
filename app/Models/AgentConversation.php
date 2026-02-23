<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Agent converstion model.
 * 
 * @author Ale Mostajo <https://github.com/amostajo>
 * @version 1.0.0
 * @link https://laravel.com/docs/12.x/ai-sdk
 */
class AgentConversation extends Model
{
    /**
     * Table.
     * @since 1.0.0
     * 
     * @var string
     */
    protected $table = 'agent_conversations';

    // Optional: Define custom primary key name
    protected $primaryKey = 'string';

    /**
     * Fillable attributes.
     * @since 1.0.0
     * 
     * @var array
     */
    protected $fillable = [
        'id',
        'user_id',
        'title',
    ];

    /**
     * User parent.
     * @since 1.0.0
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Messages.
     * @since 1.0.0
     *
     * @return HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(AgentConversationMessage::class, 'conversation_id', 'id');  
    }
}
