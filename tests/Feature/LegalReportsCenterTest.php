<?php

namespace Tests\Feature;

use Tests\TestCase;

class LegalReportsCenterTest extends TestCase
{
    public function test_guests_are_redirected_from_the_legal_reports_center(): void
    {
        $response = $this->get(route('legal-reports.index'));

        $response->assertRedirect(route('login'));
    }
}
