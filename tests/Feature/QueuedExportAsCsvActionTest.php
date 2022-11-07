<?php

namespace NovaKit\NovaQueuedExportAsCsv\Tests\Feature;

use Illuminate\Support\Facades\Queue;
use NovaKit\NovaQueuedExportAsCsv\Actions\QueuedExportAsCsv as QueuedExportAsCsvAction;
use NovaKit\NovaQueuedExportAsCsv\Jobs\QueuedExportAsCsv as QueuedExportAsCsvJob;
use NovaKit\NovaQueuedExportAsCsv\Tests\TestCase;
use Orchestra\Testbench\Factories\UserFactory;

class QueuedExportAsCsvActionTest extends TestCase
{
    /** @test */
    public function it_can_generate_queued_action()
    {
        Queue::fake();

        $user = UserFactory::new()->create();
        UserFactory::new()->times(10)->create();

        $response = $this->withoutMix()
            ->actingAs($user)
            ->post('/nova-api/users/action?action='.(new QueuedExportAsCsvAction)->uriKey(), [
                'resources' => 'all',
            ]);

        $response->assertOk()
            ->assertJson(['message' => 'The action was executed successfully.']);

        Queue::assertPushed(function (QueuedExportAsCsvJob $job) use ($user) {
            return $job->userId === $user->id;
        });
    }
}
