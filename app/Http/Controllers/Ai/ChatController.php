<?php

namespace App\Http\Controllers\Ai;

use App\Ai\Agents\TcgAssitant;
use App\Http\Controllers\Controller;
use App\Models\UploadedFile;
use App\Models\User;
use Exception;
use FastVolt\Helper\Markdown;
use Laravel\Ai\Enums\Lab;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Gemini;
use Gemini\Data\FileSearch;
use Gemini\Data\GenerationConfig;
use Gemini\Data\Tool;
use Log;

/**
 * Gemini chat controller.
 * 
 * @author Ale Mostajo <https://github.com/amostajo>
 * @version 1.0.0
 */
class ChatController extends Controller
{
    /**
     * Render AI chat.
     * @since 1.0.0
     * 
     * @return Inertia\Response
     */
    public function create(): Response
    {
        $me = User::me()->first();
        return Inertia::render('Chat', [
            'conversations' => $me->conversations()
                ->orderBy('created_at', 'desc')
                ->get(), 
            'role' => config('chatbot.role'),
        ]);
    }

    /**
     * Handles question asked to chat agent.
     * @since 1.0.0
     * 
     * @param Illuminate\Http\Request $request
     */
    public function chat(Request $request)
    {
        $request->validate([
            'conversationId' => 'string|exists:agent_conversations,id',
            'message' => 'required|string|max:2000',
            'useAgent' => 'boolean',
            'useFileSearch' => 'boolean',
        ]);
        if ($request->useAgent) {
            $me = User::me()->first();
            return response()->json(
                empty($request->conversationId)
                    ? (new TcgAssitant(language: 'English'))
                        ->forUser($me)
                        ->prompt(
                            $request->message,
                            provider: Lab::Gemini,
                            model: config('services.gemini.model'),
                        )
                    : (new TcgAssitant(language: 'English'))
                        ->continue($request->conversationId, as: $me)
                        ->prompt(
                            $request->message,
                            provider: Lab::Gemini,
                            model: config('services.gemini.model'),
                        )
            );
        } else {
            try {
                $file = UploadedFile::where('provider', 'gemini')
                    ->where('key', 'posts')
                    ->first();
                $client = Gemini::client(config('services.gemini.key'));
                $response = $request->useFileSearch
                    ? $client
                        ->generativeModel(model: config('services.gemini.model'))
                        ->withTool(new Tool(
                            fileSearch: new FileSearch([
                                $file->provider_id,
                            ])
                        ))
                        ->withGenerationConfig(new GenerationConfig(
                            temperature: 1,
                            maxOutputTokens: 2048,
                        ))
                        ->generateContent(preg_replace(
                            ['/:role/', '/:question/'],
                            [config('chatbot.role'), $request->message],
                            config('chatbot.prompts.base')
                        ))
                    : $client
                        ->generativeModel(model: config('services.gemini.model'))
                        ->withGenerationConfig(new GenerationConfig(
                            temperature: 1,
                            maxOutputTokens: 2048,
                        ))
                        ->generateContent(preg_replace(
                            ['/:role/', '/:question/'],
                            [config('chatbot.role'), $request->message],
                            config('chatbot.prompts.base')
                        ));
                // Extract sources / grounding chunks
                $sources = [];
                if (!empty($response->candidates[0]->groundingMetadata->groundingChunks)) {
                    foreach ($response->candidates[0]->groundingMetadata->groundingChunks as $chunk) {
                        $fileInfo = $chunk->file ?? $chunk->retrievedContext ?? (object)[];
                        $sources[] = [
                            'file'       => $fileInfo->displayName ?? $fileInfo->uri ?? 'Workshop database',
                            'confidence' => $chunk->confidence ?? null,
                        ];
                    }
                }
                $markdown = new Markdown();
                return response()->json([
                    'text' => $response->text()
                        ? $response->text()
                        : 'No response',
                    'sources' => $sources,
                ]);
            } catch (Exception $e) {
                Log::error('Gemini error: ' . $e->getMessage());
                return response()->json([
                    'error' => 'Failed to get response: ' . $e->getMessage()
                ], 500);
            }
        }
    }

    /**
     * Returns conversation messages.
     * @since 1.0.0
     * 
     * @param Illuminate\Http\Request $request
     */
    public function messages(Request $request)
    {
        $request->validate([
            'conversationId' => 'string|exists:agent_conversations,id',
        ]);
        $me = User::me()->first();
        return response()->json(
            $me->conversations()
                ->where('id', $request->conversationId)
                ->first()
                ->messages()
                ->orderBy('created_at', 'asc')
                ->get()
        );
    }

    /**
     * Deletes a conversation.
     * @since 1.0.0
     * 
     * @param string $conversationId
     */
    public function deleteConversation(string $conversationId)
    {
        $me = User::me()->first();
        $conversation = $me->conversations()
                ->where('id', $conversationId)
                ->delete();
        $me->conversations()
                ->where('id', $conversationId)
                ->delete();
        return response()->json(true);

    }
}
