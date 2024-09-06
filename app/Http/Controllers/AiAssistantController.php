<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AiAssistantController extends Controller
{
    public function handleRequest(Request $request)
    {
        // Validation
        try {
            $request->validate([
                'message' => 'required|string|max:255',
                'history' => 'array',
                'grants' => 'array|nullable',
                'govgrants' => 'array|nullable',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json($e->errors()["message"][0], 400);
        }

        $userMessage = $request->input('message');
        $history = $request->input('history', []);

        // Add the user's message to the conversation history
        $history[] = ['role' => 'user', 'content' => $userMessage];

        // Check for the grants and govgrants inputs and prepend the context to the history
        // Enforce a limit of 100,000 characters
        if ($request->has('grants') || $request->has('govgrants')) {
            $grants = $request->input('grants', []);
            $govgrants = $request->input('govgrants', []);

            // Stringify the grants and govgrants arrays
            $grantContext = json_encode([
                'grants' => $grants,
                'govgrants' => $govgrants,
            ]);

            // Enforce the 100,000 character limit
            if (strlen($grantContext) > 100000) {
                $grantContext = substr($grantContext, 0, 100000) . '...';
            }

            // Prepend the grant context to the history as a system message
            $history = array_merge([['role' => 'user', 'content' => "Here is the list of relevant grants:\n" . $grantContext]], $history);
        }

        return new StreamedResponse(function() use ($history) {
            $this->streamAiResponse($history);
        });
    }


    private function streamAiResponse($history)
    {
        $maxTokens = 4096;
        $temperature = 0.5;
        $model = 'gpt-4o-mini';
        $client = new Client();
        $apiKey = config('services.openai.api_key');

        //prepend the context to the history
        //$history = array_merge([['role' => 'user', 'content' => $this->getContext()]], $history);

        $response = $client->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'messages' => $history, // Send the entire conversation history
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'model' => $model,
                'stream' => true,
            ],
            'stream' => true,
        ]);

        $body = $response->getBody();
        $buffer = '';

        while (!$body->eof()) {
            $buffer .= $body->read(1024);

            while (($pos = strpos($buffer, "\n")) !== false) {
                $chunk = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 1);

                if (strpos($chunk, 'data: ') === 0) {
                    $jsonData = substr($chunk, 6);

                    if ($jsonData === '[DONE]') {
                        break 2;
                    }

                    $decodedChunk = json_decode($jsonData, true);

                    if (isset($decodedChunk['choices'][0]['delta']['content'])) {
                        echo $decodedChunk['choices'][0]['delta']['content'];
                        ob_flush();
                        flush();
                    }
                }
            }
        }
    }

    private function getContext() {
        return 
        <<<EOT
        You are a chatbot assistant embedded in an introduction to AI grants website with several grants displayed to the user. Here is information about the grants:
                    [
                'id' => 1,
                'title' => 'NSF SBIR - AI Focused Grants',
                'description' => 'Funding for AI projects that aim to address national priorities, with a focus on commercialization potential.',
                'amount' => 'Up to $2 million',
                'deadline' => '2024-11-22',
                'match' => 95,
                'purpose' => 'To support small businesses engaging in scientific research and development that has the potential for commercialization.',
                'eligibility' => 'Small businesses with fewer than 500 employees, at least 50% owned by U.S. citizens or permanent residents.',
                'application_process' => 'Initial Project Pitch followed by Full Proposal if selected.',
                'contact' => 'nsf-sbir@nsf.gov',
                'website' => 'https://seedfund.nsf.gov/topics/artificial-intelligence/',
            ],
            [
                'id' => 2,
                'title' => 'Meta Llama 3.1 Impact Grants',
                'description' => 'Grants for leveraging Llama 3.1 models to address community challenges.',
                'amount' => 'Up to $500,000',
                'deadline' => '2024-11-22',
                'match' => 85,
                'purpose' => 'To foster the use of Llama 3.1 models in impactful community projects across various sectors such as education, public services, and economic development.',
                'eligibility' => 'Non-profits, academic institutions, and businesses with innovative AI application ideas.',
                'application_process' => 'Submission of a detailed proposal through the Meta platform.',
                'contact' => 'LlamaImpactEvent@meta.com',
                'website' => 'https://llama.meta.com/llama-impact-grants/',
            ],
            [
                'id' => 3,
                'title' => 'Climate Change AI Innovation Grants 2024',
                'description' => 'Funding for AI projects aimed at climate change mitigation and adaptation.',
                'amount' => 'Up to $150,000',
                'deadline' => '2024-09-15',
                'match' => 80,
                'purpose' => 'To support innovative AI projects that address climate change, particularly those that develop new datasets or computational models.',
                'eligibility' => 'Researchers affiliated with accredited universities in OECD Member Countries.',
                'application_process' => 'Proposals submitted via the CMT submission portal.',
                'contact' => 'grants@climatechange.ai',
                'website' => 'https://www.climatechange.ai/calls/innovation_grants_2024',
            ],
        ];
        EOT;

    }
}
