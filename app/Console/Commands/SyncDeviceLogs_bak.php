<?php

namespace App\Console\Commands;

use App\Models\Punch;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function App\Helpers\caseMatchTable;

class SyncDeviceLogsBak extends Command
{
    public $updatableId;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'punches:sync-device-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync one by one device logs to punches table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $latestId = DB::table('last_synced_ids')->where('name', 'last_log_id')->value('value') ?? 1;
        $this->updatableId = $latestId;
        $timeStamp = Carbon::now()->toDateTimeString();

            DB::table(caseMatchTable('DeviceLogs_Processed'))
                            ->where(caseMatchTable('DeviceLogId'), '>', $latestId)
                            ->orderBy(caseMatchTable('DeviceLogId'))
                            ->chunk(200, function($datas) use ($timeStamp){
                                foreach($datas as $data)
                                {
                                    $punchDate = Carbon::parse($data->LogDate)->toDateString();
                                    $punch = DB::table('punches')->where([ 'emp_code'=> $data->UserId, 'punch_date'=> $punchDate ])->first();

                                    Log::info($data->DeviceLogId);
                                    if(!$punch)
                                    {
                                        DB::table('punches')->insert([
                                            'emp_code'=> $data->UserId, 'check_in' => $data->LogDate, 'device_id'=> $data->DeviceId, 'punch_date'=> $punchDate, 'created_at'=> $timeStamp ]);
                                    }
                                    else
                                    {
                                        $checkIn = $punch->check_in;
                                        $checkOut = $data->LogDate;
                                        if(Carbon::parse($checkOut)->lt( Carbon::parse($checkIn) ) )
                                        {
                                            $temp = $checkIn;
                                            $checkIn = $checkOut;
                                            $checkOut = $temp;
                                        }
                                        $duration = Carbon::parse($checkIn)->diffInSeconds($checkOut);
                                        DB::table('punches')
                                                ->where(['emp_code'=> $data->UserId, 'punch_date'=> $punchDate])
                                                ->update([
                                                    'check_in'=> $checkIn,
                                                    'check_out'=> $checkOut,
                                                    'duration'=> $duration,
                                                    'updated_at'=> $timeStamp,
                                                ]);
                                    }
                                    $this->updatableId = $data->DeviceLogId;
                                }
                            });

            DB::table('last_synced_ids')->where('name', 'last_log_id')->update(['value'=> $this->updatableId, 'updated_at'=> $timeStamp]);
            $this->info('Command executed successfully!');
    }

}
