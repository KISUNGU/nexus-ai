<?php
// api/chat.php

ob_start(); // Empêche toute sortie parasite
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../php_error.log');
error_reporting(E_ALL);

// Chargement des dépendances
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use OpenAI\OpenAI; // ✅ Correct ici

// Chargement de l’environnement
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // 🔒 à restreindre en prod
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Prévol CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Fonction simulée
function get_sales_data(string $quarter = null): array
{
    $salesData = [
        'T1' => ['amount' => 1250000, 'product' => 'Nexus Alpha'],
        'T2' => ['amount' => 1780000, 'product' => 'Nexus X200'],
        'T3' => ['amount' => 1500000, 'product' => 'Nexus Beta'],
        'T4' => ['amount' => 2100000, 'product' => 'Nexus Prime']
    ];
    return $quarter && isset($salesData[strtoupper($quarter)]) ? $salesData[strtoupper($quarter)] : $salesData;
}

// Traitement principal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!isset($data['message'])) {
        http_response_code(400);
        ob_end_clean();
        echo json_encode(['error' => 'No message provided.']);
        exit();
    }

    $userMessage = $data['message'];
    $openAiApiKey = $_ENV['OPENAI_API_KEY'] ?? null;

    if (!$openAiApiKey) {
        error_log("Clé API OpenAI non définie.");
        ob_end_clean();
        echo json_encode(['reply' => "Désolé, la clé API n'est pas configurée."]);
        exit();
    }

    try {
        // ✅ Initialisation correcte
        $client = OpenAI::client($openAiApiKey);

        $tools = [
            [
                "type" => "function",
                "function" => [
                    "name" => "get_sales_data",
                    "description" => "Récupère les chiffres de vente d’un trimestre.",
                    "parameters" => [
                        "type" => "object",
                        "properties" => [
                            "quarter" => [
                                "type" => "string",
                                "enum" => ["T1", "T2", "T3", "T4"],
                                "description" => "Trimestre à consulter"
                            ]
                        ],
                        "required" => []
                    ]
                ]
            ]
        ];

        $messages = [
            ['role' => 'system', 'content' => 'You are a helpful and professional enterprise AI assistant named NexusAI. When asked about sales, use the get_sales_data tool.'],
            ['role' => 'user', 'content' => $userMessage]
        ];

        $response = $client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'tools' => $tools,
            'tool_choice' => 'auto'
        ]);

        $messageFromAI = $response->choices[0]->message;

        if (isset($messageFromAI->tool_calls)) {
            $messages[] = $messageFromAI;

            foreach ($messageFromAI->tool_calls as $toolCall) {
                $name = $toolCall->function->name;
                $args = json_decode($toolCall->function->arguments, true);
                $result = call_user_func('get_sales_data', ...array_values($args));

                $messages[] = [
                    "tool_call_id" => $toolCall->id,
                    "role" => "tool",
                    "name" => $name,
                    "content" => json_encode($result)
                ];
            }

            $final = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages
            ]);

            $botReply = $final->choices[0]->message->content;
        } else {
            $botReply = $messageFromAI->content;
        }

        ob_end_clean();
        echo json_encode(['reply' => $botReply]);

    } catch (Exception $e) {
        error_log("Erreur OpenAI : " . $e->getMessage());
        ob_end_clean();
        echo json_encode(['reply' => "Erreur technique : " . $e->getMessage()]);
    }

} else {
    http_response_code(405);
    ob_end_clean();
    echo json_encode(['error' => 'Méthode non autorisée']);
}
