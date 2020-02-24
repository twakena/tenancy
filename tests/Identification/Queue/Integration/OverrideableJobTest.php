<?php

namespace Tenancy\Tests\Identification\Queue\Integration;

use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Event;
use Tenancy\Identification\Drivers\Queue\Providers\IdentificationProvider;
use Tenancy\Testing\TestCase;
use Tenancy\Tests\Mocks\Jobs\OverrideableJob;

class OverrideableJobTest extends TestCase
{
    protected $additionalProviders = [IdentificationProvider::class];

    /** @test */
    public function it_does_not_contain_the_tenant_when_none_identified()
    {
        Event::listen([JobProcessing::class, JobProcessed::class], function ($event) {
            $payload = json_decode($event->job->getRawBody(), true);

            $this->assertArrayNotHasKey('tenant_identifier', $payload);
            $this->assertArrayNotHasKey('tenant_key', $payload);
        });

        dispatch(new OverrideableJob());
    }

    /** @test */
    public function it_does_contain_the_tenant_by_default()
    {
        $tenant = $this->mockTenant();

        $this->environment->setTenant($tenant);

        Event::listen([JobProcessing::class, JobProcessed::class], function ($event) use ($tenant) {
            $payload = json_decode($event->job->getRawBody(), true);

            $this->assertEquals($tenant->getTenantIdentifier(), $payload['tenant_identifier']);
            $this->assertEquals($tenant->getTenantKey(), $payload['tenant_key']);
        });

        dispatch(new OverrideableJob());
    }
}
