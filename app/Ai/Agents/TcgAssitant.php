<?php

namespace App\Ai\Agents;

use App\Ai\Traits\PromptTemplate;
use App\Models\User;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Providers\Tools\WebSearch;
use Illuminate\Support\Str;
use Stringable;

/**
 * TCG Agent
 * 
 * @author Ale Mostajo <https://github.com/amostajo>
 * @version 1.0.0
 */
class TcgAssitant implements Agent, Conversational, HasTools
{
    use Promptable, PromptTemplate;

    /**
     * Create a new TCG Agent instance.
     *
     * @param User        $user           The user associated with the conversation.
     * @param string|null $conversationId The ID of the conversation to retrieve messages from.
     * @param string|null $language       The language to use for the conversation (default is 'English').
     */
    public function __construct(
        public ?User $user,
        public ?string $conversationId = null,
        public ?string $language = 'English'
    ) {
        // @todo
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return $this->formatTemplate(
            'You are a TCG (Trading Card Game) expert and SEO expert'."\n"
                . '"hero" refers to a section in a website that highlights its main purpose'."\n"
                . 'conversation is always in {language}'."\n"
                . 'answer:'."\n",
            [
                'language' => $this->language,
            ]
        );
    }

    /**
     * Get the list of messages comprising the conversation so far.
     */
    public function messages(): iterable
    {
        $conversation = $this->user->conversations()
            ->where('id', $this->conversationId)
            ->first();
        return $conversation
            ? $conversation->messages()
                ->latest()
                ->limit(50)
                ->get()
                ->reverse()
                ->get()
                ->map(function ($message) {
                    return new Message(
                        role: $message->role,
                        content: $message->content,
                        attachments: $message->attachments,
                        tool_calls: $message->tool_calls,
                        tool_results: $message->tool_results,
                        usage: $message->usage,
                        meta: $message->meta,
                    );
                })
            : [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            //new WebSearch,
        ];
    }
}
