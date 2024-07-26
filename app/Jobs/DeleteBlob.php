<?php

namespace App\Jobs;

use App\Repositories\StorageFacade;
use App\Utils\Encoder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeleteBlob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $id,
    ) {}

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return $this->id;
    }

    /**
     * Execute the job.
     */
    public function handle(StorageFacade $storageFacade): void
    {
        $storageFacade->delete(Encoder::base64UrlDecode($this->id));
    }
}
