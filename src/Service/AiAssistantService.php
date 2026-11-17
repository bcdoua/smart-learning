<?php

namespace App\Service;

use App\Entity\Conversation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AiAssistantService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $em,
        private readonly string $openAiKey = '',
        private readonly string $geminiKey = '',
    ) {}

    public function answer(User $student, string $question, string $courseContext = ''): Conversation
    {
        $answer = $this->localAnswer($question, $courseContext);
        $provider = 'local';

        if ($this->openAiKey !== '') {
            [$provider, $answer] = ['openai', $this->askOpenAi($question, $courseContext)];
        } elseif ($this->geminiKey !== '') {
            [$provider, $answer] = ['gemini', $this->askGemini($question, $courseContext)];
        }

        $conversation = (new Conversation())
            ->setStudent($student)
            ->setQuestion($question)
            ->setAnswer($answer)
            ->setProvider($provider);

        $this->em->persist($conversation);
        $this->em->flush();

        return $conversation;
    }

    private function askOpenAi(string $question, string $context): string
    {
        try {
            $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => ['Authorization' => 'Bearer '.$this->openAiKey],
                'json' => [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Tu es un tuteur e-learning concis. Reponds selon le contexte pédagogique fourni.'],
                        ['role' => 'user', 'content' => "Contexte:\n".$context."\n\nQuestion:\n".$question],
                    ],
                ],
            ]);

            return $response->toArray(false)['choices'][0]['message']['content'] ?? $this->localAnswer($question, $context);
        } catch (\Throwable) {
            return $this->localAnswer($question, $context);
        }
    }

    private function askGemini(string $question, string $context): string
    {
        try {
            $response = $this->httpClient->request('POST', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key='.$this->geminiKey, [
                'json' => [
                    'contents' => [[
                        'parts' => [['text' => "Contexte pedagogique:\n".$context."\n\nQuestion:\n".$question]],
                    ]],
                ],
            ]);

            return $response->toArray(false)['candidates'][0]['content']['parts'][0]['text'] ?? $this->localAnswer($question, $context);
        } catch (\Throwable) {
            return $this->localAnswer($question, $context);
        }
    }

    private function localAnswer(string $question, string $context): string
    {
        $summary = trim($context) !== '' ? substr(strip_tags($context), 0, 600) : 'Aucun contenu de cours specifique n est encore associe a cette question.';

        return sprintf(
            "Voici une aide structuree pour ta revision.\n\nQuestion: %s\n\nContexte retenu: %s\n\nPiste: reformule le probleme, repere les notions cles, puis fais un exemple simple avant de passer a l exercice complet.",
            $question,
            $summary
        );
    }
}
