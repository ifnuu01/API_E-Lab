<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RoomRequest;
use Carbon\Carbon;

class UpdateFinishedRoomRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    // protected
    protected $signature = 'room-requests:update-finished';
    protected $description = 'Update finished room requests';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        RoomRequest::where('status', 'approved')
            ->where(function ($query) use ($now) {
                $query->where('end_time', '<=', $now)
                    ->orWhere(function ($query) use ($now) {
                        $query->where('end_time', '>=', $now)
                            ->where('borrow_date', '<=', $now);
                    });
            })
            ->update(['status' => 'finished']);

        $this->info('Finished room requests updated successfully.');
    }
}
