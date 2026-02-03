# Workshop AI chatbot

This repository implements an AI chatbot using Laravel `12.x` + Vue `3.x` + Tailwind CSS.

The implementation example is configured as the bot being a TCG (Trading card game) expert agent.

![TCG Chatbot sample](https://raw.githubusercontent.com/amostajo/workshop-ai-chatbot/refs/heads/main/storage/app/public/sample.jpg)

## AI

Supported AI services:
| AI |
| --- |
| Gemini |

## Dev

### Environment variables

Aside from built-in Laravel env variables you will need API keys from AI service providers:
| Env variable | Description|
| --- | --- |
| `GEMINI_API_KEY` | Gemini API key. |
| `GEMINI_MODEL` | Gemini model. |

### Start Laravel

Use Laravel sail to start your local:
```bash
./vendor/bin/sail up -d
```

Run migrations:
```bash
./vendor/bin/sail artisan migrate
```

Run vite:
```bash
npm run dev
```

Change to WSL on Windows.
```bash
wsl
```

To close docker containers:
```bash
./vendor/bin/sail down
```

Visit the following url to test the chatbot:
```bash
http://localhost
```

### Configuration

The file `/config/chatbot.php` contains varios chat configurations.

## AI context

Custom context is being stored in the `posts` database table (replicating WordPress posts structure), these records can be uploaded to the AI provider as static context files.

The repo comes pre-built with database seeds related to TCG, which can be populated with Laravel's seed command:
```bash
./vendor/bin/sail artisan db:migrate
```

### Gemini

#### Gemini: File search

For Gemini, this context is uploaded as text files and saved into "file search stores", this is great for a small knowledge database, for a larger one you will need to opt for LLMs.

##### Upload DB context

The following command removes the existing context and uploads a new one.
```bash
./vendor/bin/sail artisan app:upload-context gemini
```

##### Delete context

The following command deletes all existing context uploaded to Gemini.
```bash
./vendor/bin/sail artisan app:delete-context gemini
```