<?php

use Illuminate\Support\Facades\Queue;
use NovaKit\NovaQueuedExportAsCsv\Actions\QueuedExportAsCsv as QueuedExportAsCsvAction;
use NovaKit\NovaQueuedExportAsCsv\Jobs\QueuedExportAsCsv as QueuedExportAsCsvJob;
use Orchestra\Testbench\Factories\UserFactory;

it('can generate queued action', function () {
    Queue::fake();

    $user = UserFactory::new()->create();

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
});

it('can generate queued action with custom response', function () {
    Queue::fake();

    $user = UserFactory::new()->create();

    $response = $this->withoutMix()
        ->actingAs($user)
        ->post('/nova-api/subscribers/action?action='.(new QueuedExportAsCsvAction)->uriKey(), [
            'resources' => 'all',
        ]);

    $response->assertOk()
        ->assertJson(['message' => 'Action has been queued!']);

    Queue::assertPushed(function (QueuedExportAsCsvJob $job) use ($user) {
        return $job->userId === $user->id;
    });
});
