<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingConcurrencyAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_concurrency_audit_refuses_non_mysql_connections(): void
    {
        $this->artisan('tracking:concurrency-audit')
            ->expectsOutputToContain('Concurrency audit requires MySQL/InnoDB.')
            ->assertFailed();
    }
}
