<?php

namespace NovaKit\NovaQueuedExportAsCsv\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Util;
use NovaKit\NovaQueuedExportAsCsv\Events\QueuedCsvExported;
use Rap2hpoutre\FastExcel\FastExcel;

use function Laravie\SerializesQuery\unserialize;

class QueuedExportAsCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The configuration options.
     *
     * @var array{filename: string, storageDisk: string|null, notify: string}
     */
    public $options;

    /**
     * Create a new job instance.
     *
     * @param  array<string, mixed>  $query
     * @param  (callable(\Illuminate\Database\Eloquent\Model):array<string, mixed>)|null  $withFormatCallback
     * @param  array{filename: string, storageDisk: string|null, notify: string}  $options
     * @return void
     */
    public function __construct(
        public array $query,
        public string|int $userId,
        public $withFormatCallback,
        array $options
    ) {
        $this->options = array_merge([
            'storageDisk' => null,
        ], $options);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $query = unserialize($this->query);

        $eloquentGenerator = function () use ($query) {
            foreach ($query->cursor() as $model) {
                yield $model;
            }
        };

        /** @phpstan-ignore-next-line */
        $withFormatCallback = ! is_null($this->withFormatCallback) ? \unserialize($this->withFormatCallback)->getClosure() : null;

        /** @var \Illuminate\Contracts\Auth\Authenticatable&\Illuminate\Database\Eloquent\Model $userModel */
        $userModel = Util::userModel();
        $storageDisk = $this->options['storageDisk'];
        $filename = $this->options['filename'];

        $exportedFilename = (new FastExcel($eloquentGenerator()))->export("/tmp/{$filename}", $withFormatCallback);

        $storedFilename = Storage::disk($storageDisk)->putFileAs(
            'nova-actions-export-as-csv', new File($exportedFilename), $filename, 'public'
        );

        (new Filesystem())->delete($exportedFilename);

        /** @var \Illuminate\Contracts\Auth\Authenticatable&\Illuminate\Database\Eloquent\Model $user */
        $user = $userModel::findOrFail($this->userId);

        QueuedCsvExported::dispatch(
            $user, $storedFilename, $storageDisk
        );
    }
}
