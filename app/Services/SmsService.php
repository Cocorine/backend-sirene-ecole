<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private string $provider;
    private string $apiKey;
    private string $apiSecret;
    private string $fromNumber;

    public function __construct()
    {
        $this->provider = config('services.sms.provider', 'twilio');
        $this->apiKey = config('services.sms.api_key');
        $this->apiSecret = config('services.sms.api_secret');
        $this->fromNumber = config('services.sms.from_number');
    }

    /**
     * Envoie un SMS
     */
    public function sendSms(string $to, string $message): bool
    {
        try {
            switch ($this->provider) {
                case 'twilio':
                    return $this->sendViaTwilio($to, $message);

                case 'africas_talking':
                    return $this->sendViaAfricasTalking($to, $message);

                default:
                    Log::warning("SMS not sent (no provider configured): {$to}");
                    return true; // En mode dev, on simule l'envoi
            }
        } catch (Exception $e) {
            Log::error('SMS sending failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Envoie via Twilio
     */
    private function sendViaTwilio(string $to, string $message): bool
    {
        if (empty($this->apiKey) || empty($this->apiSecret)) {
            Log::warning("Twilio credentials not configured. SMS not sent to: {$to}");
            Log::info("SMS Content: {$message}");
            return true;
        }

        $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$this->apiKey}/Messages.json", [
                'To' => $to,
                'From' => $this->fromNumber,
                'Body' => $message,
            ]);

        if ($response->successful()) {
            Log::info("SMS sent successfully to {$to}");
            return true;
        }

        throw new Exception('Twilio SMS failed: ' . $response->body());
    }

    /**
     * Envoie via Africa's Talking
     */
    private function sendViaAfricasTalking(string $to, string $message): bool
    {
        if (empty($this->apiKey)) {
            Log::warning("Africa's Talking API key not configured. SMS not sent to: {$to}");
            Log::info("SMS Content: {$message}");
            return true;
        }

        $response = Http::withHeaders([
            'apiKey' => $this->apiKey,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->asForm()->post('https://api.africastalking.com/version1/messaging', [
            'username' => config('services.sms.username', 'sandbox'),
            'to' => $to,
            'message' => $message,
            'from' => $this->fromNumber,
        ]);

        if ($response->successful()) {
            Log::info("SMS sent successfully to {$to}");
            return true;
        }

        throw new Exception('Africa\'s Talking SMS failed: ' . $response->body());
    }
}
