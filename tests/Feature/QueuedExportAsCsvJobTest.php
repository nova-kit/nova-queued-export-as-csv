<?php

namespace NovaKit\NovaQueuedExportAsCsv\Tests\Feature;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use function Laravie\SerializesQuery\serialize;
use NovaKit\NovaQueuedExportAsCsv\Events\QueuedCsvExported;
use NovaKit\NovaQueuedExportAsCsv\Jobs\QueuedExportAsCsv as QueuedExportAsCsvJob;
use NovaKit\NovaQueuedExportAsCsv\Tests\TestCase;
use Orchestra\Testbench\Factories\UserFactory;

class QueuedExportAsCsvJobTest extends TestCase
{
    /** @test */
    public function it_can_generate_export_file_from_job()
    {
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
    }
}
