<?php

namespace App\Service;

use Mailjet\Client;
use Mailjet\Resources;

class EmailService
{
    private $client;
    private $senderEmail;
    private $senderName;

    public function __construct(string $apiKey, string $apiSecret, string $sender, string $name)
    {
        $this->client = new Client($apiKey, $apiSecret, true, ['version' => 'v3.1', 'verify' => false]);
        $this->senderEmail = $sender;
        $this->senderName = $name;
    }

    public function sendEmail(string $to, string $subject, string $body): void
    {
        $message = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => $this->senderEmail,
                        'Name' => $this->senderName,
                    ],
                    'To' => [
                        [
                            'Email' => $to,
                            'Name' => 'Recipient Name'
                        ]
                    ],
                    'Subject' => $subject,
                    'TextPart' => $body,
                    'HTMLPart' => $body,
                ]
            ]
        ];

        $response = $this->client->post(Resources::$Email, ['body' => $message]);
        if (!$response->success()) {
            throw new \Exception('Failed to send email: ' . $response->getReasonPhrase());
        }
    }
}
