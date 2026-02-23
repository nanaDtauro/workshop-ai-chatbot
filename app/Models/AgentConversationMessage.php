<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Agent converstion model.
 * 
 * @author Ale Mostajo <https://github.com/amostajo>
 * @version 1.0.0
 * @link https://laravel.com/docs/12.x/ai-sdk
 */
class AgentConversationMessage extends Model
{
    /**
     * Table.
     * @since 1.0.0
     * 
     * @var string
     */
    protected $table = 'agent_conversation_messages';

    /**
     * Fillable attributes.
     * @since 1.0.0
     * 
     * @var array
     */
    protected $fillable = [
        'conversation_id',
        'user_id',
        'agent',
        'role',
        'content',
        'attachments',
        'tool_calls',
        'tool_results',
        'usage',
        'meta',
    ];

    /**
     * Conversation parent.
     * @since 1.0.0
     *
     * @return BelongsTo
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AgentConversation::class);
    }

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
}
