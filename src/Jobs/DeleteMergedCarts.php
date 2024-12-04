<?php

namespace Niladam\Cart\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Niladam\Cart\Models\Cart;

class DeleteMergedCarts implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $deletableCart;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Cart $deletableCart)
    {
        $this->deletableCart = $deletableCart;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->deletableCart->delete();
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return md5($this->deletableCart->id);
    }
}
