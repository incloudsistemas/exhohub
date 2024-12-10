<?php

namespace App\Services\Web;

use App\Services\BaseService;
use Illuminate\Support\Facades\Http;

abstract class BaseFormService extends BaseService
{
    protected array $message = [
        'success'           => 'Recebemos com sucesso o seu cadastro e entraremos em contato contigo assim que possível.',
        'error'             => 'O cadastro não pôde ser efetuado devido a algum erro inesperado. Por favor, tente novamente mais tarde.',
        'error_bot'         => 'Bot detectado! O formulário não pôde ser processado! Por favor, tente novamente!',
        'error_unexpected'  => 'Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.',
        'recaptcha_invalid' => 'Captcha não validado! Por favor, tente novamente!',
        'recaptcha_error'   => 'Captcha não enviado! Por favor, tente novamente.',
    ];

    protected function honeyPotCheckBot(array $data): void
    {
        $prefix = $data['prefix'] ?? '';

        $botcheck = $data[$prefix . 'botcheck'] ?? '';

        if (!empty($botcheck)) {
            throw new \Exception('Error. => ' . $this->message['error_bot']);
        }
    }

    // protected function reCaptchaProtection(array $data, ?string $recaptchaSecret): void
    // {
    //     if (isset($data['g-recaptcha-response']) && $recaptchaSecret) {
    //         $recaptchaData = [
    //             'secret'   => $recaptchaSecret,
    //             'response' => $data['g-recaptcha-response']
    //         ];

    //         $recapVerify = curl_init();

    //         curl_setopt($recapVerify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
    //         curl_setopt($recapVerify, CURLOPT_POST, true);
    //         curl_setopt($recapVerify, CURLOPT_POSTFIELDS, http_build_query($recaptchaData));
    //         curl_setopt($recapVerify, CURLOPT_SSL_VERIFYPEER, false);
    //         curl_setopt($recapVerify, CURLOPT_RETURNTRANSFER, true);

    //         $recapResponse = curl_exec($recapVerify);

    //         $gResponse = json_decode($recapResponse);

    //         if ($gResponse->success !== true) {
    //             throw new \Exception('Error. => ' . $this->message['recaptcha_invalid']);
    //         }
    //     }

    //     $forceRecap = !empty($data['force_recaptcha']) && $data['force_recaptcha'] !== false;

    //     if ($forceRecap && !isset($data['g-recaptcha-response'])) {
    //         throw new \Exception('Error. => ' . $this->message['recaptcha_error']);
    //     }
    // }

    protected function reCaptchaProtection(array $data, ?string $recaptchaSecret): void
    {
        if (isset($data['g-recaptcha-response']) && $recaptchaSecret) {
            $recaptchaData = [
                'secret'   => $recaptchaSecret,
                'response' => $data['g-recaptcha-response'],
            ];

            $response = Http::asForm()
                ->post('https://www.google.com/recaptcha/api/siteverify', $recaptchaData);

            if ($response->failed() || $response->json('success') !== true) {
                throw new \Exception('Error. => ' . $this->message['recaptcha_invalid']);
            }
        }

        $forceRecap = !empty($data['force_recaptcha']) && $data['force_recaptcha'] !== false;
        if ($forceRecap && !isset($data['g-recaptcha-response'])) {
            throw new \Exception('Error. => ' . $this->message['recaptcha_error']);
        }
    }

    protected function setCustomMessages(array $customMessages): void
    {
        $this->message['success'] = $customMessages['success'] ?? $this->message['success'];
        $this->message['error'] = $customMessages['error'] ?? $this->message['error'];
        $this->message['error_bot'] = $customMessages['error_bot'] ?? $this->message['error_bot'];
        $this->message['error_unexpected'] = $customMessages['error_unexpected'] ?? $this->message['error_unexpected'];
        $this->message['recaptcha_invalid'] = $customMessages['recaptcha_invalid'] ?? $this->message['recaptcha_invalid'];
        $this->message['recaptcha_error'] = $customMessages['recaptcha_error'] ?? $this->message['recaptcha_error'];
    }
}
