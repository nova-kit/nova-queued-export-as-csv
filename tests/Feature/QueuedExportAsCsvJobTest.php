<?php

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use NovaKit\NovaQueuedExportAsCsv\Events\QueuedCsvExported;
use NovaKit\NovaQueuedExportAsCsv\Jobs\QueuedExportAsCsv as QueuedExportAsCsvJob;
use Orchestra\Testbench\Factories\UserFactory;

use function Laravie\SerializesQuery\serialize;

it('can generate export file from job', function () {
    Event::fake();

    $user = UserFactory::new()->create();
    UserFactory::new()->times(10)->create();

    $query = serialize(User::query());
    $options = [
        'storageDisk' => 'local',
        'filename' => 'users.csv',
    ];

    dispatch(new QueuedExportAsCsvJob($query, $user->id, null, $options));

    Event::assertDispatched(function (QueuedCsvExported $event) use ($user) {
        return $event->user->id === $user->id
            && $event->filename === 'nova-actions-export-as-csv/users.csv'
            && $event->storageDisk === 'local';
    });

    $this->assertTrue(Storage::disk('local')->exists('nova-actions-export-as-csv/users.csv'));
});
