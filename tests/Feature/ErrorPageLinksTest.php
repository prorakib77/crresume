<?php

namespace Tests\Feature;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ErrorPageLinksTest extends TestCase
{
    public function test_guest_error_pages_use_login_and_public_contact_links(): void
    {
        $loginUrl = route('login');
        $contactUrl = route('contact.page');
        $views = [
            'errors.403' => [],
            'errors.404' => [],
            'errors.419' => [],
            'errors.500' => [],
            'errors.error' => ['exception' => new HttpException(418, 'Test error')],
        ];

        foreach ($views as $view => $data) {
            auth()->logout();

            $html = view($view, $data)->render();

            $this->assertStringContainsString('href="' . $loginUrl . '"', $html, $view);
            $this->assertStringContainsString('href="' . $contactUrl . '"', $html, $view);
        }
    }
}
