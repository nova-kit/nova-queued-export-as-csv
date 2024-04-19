<?php

namespace NovaKit\NovaQueuedExportAsCsv\Actions;

use Closure;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Laravel\Nova\Actions\ExportAsCsv;
use Laravel\Nova\Actions\Response;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\ActionRequest;
use Laravel\SerializableClosure\SerializableClosure;
use NovaKit\NovaQueuedExportAsCsv\Jobs\QueuedExportAsCsv as QueuedExportAsCsvJobs;

use function Laravie\SerializesQuery\serialize;

class QueuedExportAsCsv extends ExportAsCsv
{
    /**
     * Storage disk used to store the file.
     *
     * @var string|null
     */
    public $storageDisk;

    /**
     * Determine if file should be deleted after send.
     *
     * @var bool
     */
    public $deleteFileAfterSend = false;

    /**
     * Construct a new action instance.
     *
     * @return void
     */
    public function __construct(?string $name = null, ?string $storageDisk = null)
    {
        parent::__construct($name);

        $this->withStorageDisk($storageDisk);
    }

    /**
     * Set the storage disk.
     *
     * @return $this
     */
    public function withStorageDisk(?string $storageDisk)
    {
        $this->storageDisk = $storageDisk;

        return $this;
    }

    /**
     * Perform the action request using custom dispatch handler.
     *
     * @return \Laravel\Nova\Actions\Response
     */
    #[\Override]
    protected function dispatchRequestUsing(ActionRequest $request, Response $response, ActionFields $fields)
    {
        $query = $request->toSelectedResourceQuery();

        $query->when($this->withQueryCallback instanceof Closure, function ($query) use ($fields) {
            return call_user_func($this->withQueryCallback, $query, $fields);
        });

        $filename = $fields->get('filename') ?? sprintf('%s-%d.csv', $this->uriKey(), now()->format('YmdHis'));

        $extension = 'csv';

        if (Str::contains($filename, '.')) {
            [$filename, $extension] = explode('.', $filename);
        }

        $exportFilename = sprintf(
            '%s.%s',
            $filename,
            $fields->get('writerType') ?? $extension
        );

        $job = new QueuedExportAsCsvJobs(
            serialize($query),
            $request->user()->getKey(),
            ! is_null($this->withFormatCallback) ? \serialize(new SerializableClosure($this->withFormatCallback)) : null,
            /* @var array{exportFilename: string, storageDisk: string|null, notify: string} */
            [
                'filename' => $exportFilename,
                'extension' => $extension,
                'storageDisk' => $this->storageDisk,
                'notify' => 'email',
            ],
        );

        $connection = property_exists($this, 'connection') ? $this->connection : null;
        $queue = property_exists($this, 'queue') ? $this->queue : null;

        Queue::connection($connection)->pushOn($queue, $job);

        return $response->successful([
            response()->json(
                static::message(__('The action was executed successfully.'))
            ),
        ]);
    }
}
