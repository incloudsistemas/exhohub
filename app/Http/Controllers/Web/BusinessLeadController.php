<?php

namespace App\Http\Controllers\Web;

use App\Http\Requests\Web\BusinessLeadFromCanalProRequest;
use App\Http\Requests\Web\BusinessLeadFromMetaAdsRequest;
use App\Http\Requests\Web\BusinessLeadRequest;
use App\Services\Web\BusinessLeadService;

class BusinessLeadController extends Controller
{
    public function __construct(protected BusinessLeadService $service)
    {
        $this->recaptchaSecret = config('app.g_recapcha_server');
    }

    public function sendBusinessLeadForm(BusinessLeadRequest $request)
    {
        $data = $request->all();

        $response = $this->service->createFromWeb(
            data: $data,
            mailTo: $this->mailTo,
            recaptchaSecret: $this->recaptchaSecret
        );

        if ($request->wantsJson()) {
            return response()->json($response);
        }

        if ($response['success']) {
            session()->flash('response', $response);
            return redirect()->back();
        }

        // If errors...
        return redirect()->back()->withErrors($response['message'])->withInput();
    }

    public function receiveBusinessLeadFromCanalPro(BusinessLeadFromCanalProRequest $request)
    {
        $authorization = $request->header('Authorization');

        if (!$authorization || !str_starts_with($authorization, 'Basic ')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $base64 = substr($authorization, 6);
        $decoded = base64_decode($base64);
        $parts = explode(':', $decoded);

        $secretKey = $parts[1] ?? '';

        if ($secretKey !== config('app.olx_secret_key', env('OLX_SECRET_KEY'))) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $data = $request->all();

        $response = $this->service->createFromCanalPro(
            data: $data,
            mailTo: $this->mailTo
        );

        if ($response['success']) {
            return response()->json([
                'message' => 'Lead recebido com sucesso!',
                'data'    => $response['data']
            ], 200);
        }

        return response()->json([
            'message' => 'Erro ao processar o lead.',
            'error'   => $response['message']
        ], 400);
    }

    public function receiveBusinessLeadFromMetaAds(BusinessLeadFromMetaAdsRequest $request)
    {
        $data = $request->all();

        $response = $this->service->createFromMetaAds(
            data: $data,
            mailTo: $this->mailTo
        );

        if ($response['success']) {
            return response()->json([
                'message' => 'Lead recebido com sucesso!',
                'data'    => $response['data']
            ], 200);
        }

        return response()->json([
            'message' => 'Erro ao processar o lead.',
            'error'   => $response['message']
        ], 400);
    }
}
