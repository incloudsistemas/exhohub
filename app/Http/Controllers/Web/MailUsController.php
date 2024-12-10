<?php

namespace App\Http\Controllers\Web;

use App\Http\Requests\Web\ContactUsRequest;
use App\Http\Requests\Web\NewsletterSubscribeRequest;
use App\Http\Requests\Web\WorkWithUsRequest;
use App\Models\Cms\Page;
use App\Services\Web\MailUsService;

class MailUsController extends Controller
{
    public function __construct(
        protected Page $page,
        protected MailUsService $service
    ) {
        parent::__construct(page: $page);

        $this->recaptchaSecret = config('app.g_recapcha_server');
    }

    public function sendContactUsForm(ContactUsRequest $request)
    {
        $data = $request->all();

        $response = $this->service->create(
            data: $data,
            role: 'contact-us',
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

    public function sendWorkWithUsForm(WorkWithUsRequest $request)
    {
        $data = $request->all();

        $response = $this->service->create(
            data: $data,
            role: 'work-with-us',
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

    public function sendNewsletterSubscribeForm(NewsletterSubscribeRequest $request)
    {
        $data = $request->all();

        $response = $this->service->create(
            data: $data,
            role: 'newsletter-subscribe',
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
}
