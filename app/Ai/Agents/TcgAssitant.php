<?php

namespace App\Ai\Agents;

use App\Ai\Traits\PromptTemplate;
use Laravel\Ai\Concerns\RemembersConversations;
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
    use Promptable, PromptTemplate, RemembersConversations;

    /**
     * Create a new TCG Agent instance.
     *
     * @param string|null $language The language to use for the conversation (default is 'English').
     */
    public function __construct(
        public ?string $language = 'English'
    ) {
        // @todo
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $instructions = [
            'You are a TCG (Trading Card Game) expert',
            'conversation is always in {language}',
        ];
        return $this->formatTemplate(
            implode("\n", $instructions),
            [
                'language' => $this->language,
            ]
        );
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
