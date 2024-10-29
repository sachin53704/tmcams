<?php

namespace App\Console\Commands;

use App\Models\Punch;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function App\Helpers\caseMatchTable;

class SyncDeviceLogs extends Command
{
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
        Log::info('Cron run ' . now());
        
        $latestId = DB::table('last_synced_ids')->where('name', 'last_log_id')->value('value') ?? 1;
        $isSync = DB::table('last_synced_ids')->where('name', 'is_data_sync')->value('value') ?? 1;
        
        if ($isSync == 1) {
            DB::table('last_synced_ids')->where('name', 'is_data_sync')->update(['value' => 0, 'updated_at' => now()]);
            
            DB::table('DeviceLogs_Processed')
                ->where('DeviceLogId', '>', $latestId)
                ->orderBy('DeviceLogId')
                ->chunk(200, function ($datas) use ($latestId) {
                    $updatableId = $latestId;
                    $timeStamp = Carbon::now()->toDateTimeString();
                    $startOfDay = Carbon::today()->startOfDay();
                    
                    foreach($datas as $data)
                    {
                        $punchDate = Carbon::parse($data->LogDate)->toDateString();
                        $punch = DB::table('punches')->where([ 'emp_code'=> $data->UserId, 'punch_date'=> $punchDate, 'punch_by'=> '0'])->first();
            
                        if(!$punch)
                        {
                            DB::table('punches')->insert(
                                ['emp_code'=> $data->UserId, 'check_in' => $data->LogDate, 'device_id'=> $data->DeviceId, 'punch_date'=> $punchDate, 'created_at'=> $timeStamp ]
                            );
                        }
                        else
                        {
                            if($punch->check_out)
                            {
                                if(
                                    $startOfDay->diffInSeconds(substr($data->LogDate, 11)) <= $startOfDay->diffInSeconds(substr($punch->check_out, 11)) &&
                                    $startOfDay->diffInSeconds(substr($data->LogDate, 11)) >= $startOfDay->diffInSeconds(substr($punch->check_in, 11))
                                )
                                {  }
                                else
                                {
                                    $checkIn = $punch->check_in;
                                    $checkOut = $data->LogDate;
                                    if( $startOfDay->diffInSeconds(substr($data->LogDate, 11)) <= $startOfDay->diffInSeconds(substr($punch->check_in, 11)) )
                                    {
                                        $checkIn = $data->LogDate;
                                        $checkOut = $punch->check_out;
                                    }
            
                                    $duration = Carbon::parse($checkIn)->diffInSeconds($checkOut);
                                    DB::table('punches')->where([ 'emp_code' => $data->UserId, 'punch_date' => $punchDate, 'punch_by'=> '0'])
                                                ->update([
                                                    'check_in'=> $checkIn,
                                                    'check_out'=> $checkOut,
                                                    'duration'=> $duration,
                                                    'updated_at'=> $timeStamp,
                                                ]);
                                }
                            }
                            else
                            {
                                $checkIn = $punch->check_in;
                                $checkOut = $data->LogDate;
                                if( $startOfDay->diffInSeconds(substr($data->LogDate, 11)) <= $startOfDay->diffInSeconds(substr($punch->check_in, 11)) )
                                {
                                    $checkIn = $data->LogDate;
                                    $checkOut = $punch->check_in;
                                }
                                $duration = Carbon::parse($checkIn)->diffInSeconds($checkOut);
            
                                DB::table('punches')->where([ 'emp_code' => $data->UserId, 'punch_date' => $punchDate, 'punch_by'=> '0'])
                                                ->update([
                                                    'check_in'=> $checkIn,
                                                    'check_out'=> $checkOut,
                                                    'duration'=> $duration,
                                                    'updated_at'=> $timeStamp,
                                                ]);
                            }
                        }
                        $updatableId = $data->DeviceLogId;
                    }
                    DB::table('last_synced_ids')->where('name', 'last_log_id')->update(['value' => $updatableId, 'updated_at' => $timeStamp]);
                    
                });
                DB::table('last_synced_ids')->where('name', 'is_data_sync')->update(['value' => 1, 'updated_at' => now()]);
        }
        
        
        
        
        
        
        
        // $latestId = DB::table('last_synced_ids')->where('name', 'last_log_id')->value('value') ?? 1;
        // $updatableId = $latestId;
        // $timeStamp = Carbon::now()->toDateTimeString();
        // $startOfDay = Carbon::today()->startOfDay();

        // $datas = DB::table(caseMatchTable('DeviceLogs_Processed'))
        //                     ->where(caseMatchTable('DeviceLogId'), '>', $latestId)
        //                     ->limit(300)
        //                     ->get();

        // foreach($datas as $data)
        // {
        //     $punchDate = Carbon::parse($data->LogDate)->toDateString();
        //     $punch = DB::table('punches')->where([ 'emp_code'=> $data->UserId, 'punch_date'=> $punchDate, 'punch_by'=> '0'])->first();

        //     if(!$punch)
        //     {
        //         DB::table('punches')->insert(
        //             ['emp_code'=> $data->UserId, 'check_in' => $data->LogDate, 'device_id'=> $data->DeviceId, 'punch_date'=> $punchDate, 'created_at'=> $timeStamp ]
        //         );
        //     }
        //     else
        //     {
        //         if($punch->check_out)
        //         {
        //             if(
        //                 $startOfDay->diffInSeconds(substr($data->LogDate, 11)) <= $startOfDay->diffInSeconds(substr($punch->check_out, 11)) &&
        //                 $startOfDay->diffInSeconds(substr($data->LogDate, 11)) >= $startOfDay->diffInSeconds(substr($punch->check_in, 11))
        //             )
        //             {  }
        //             else
        //             {
        //                 $checkIn = $punch->check_in;
        //                 $checkOut = $data->LogDate;
        //                 if( $startOfDay->diffInSeconds(substr($data->LogDate, 11)) <= $startOfDay->diffInSeconds(substr($punch->check_in, 11)) )
        //                 {
        //                     $checkIn = $data->LogDate;
        //                     $checkOut = $punch->check_out;
        //                 }

        //                 $duration = Carbon::parse($checkIn)->diffInSeconds($checkOut);
        //                 DB::table('punches')->where([ 'emp_code' => $data->UserId, 'punch_date' => $punchDate, 'punch_by'=> '0'])
        //                             ->update([
        //                                 'check_in'=> $checkIn,
        //                                 'check_out'=> $checkOut,
        //                                 'duration'=> $duration,
        //                                 'updated_at'=> $timeStamp,
        //                             ]);
        //             }
        //         }
        //         else
        //         {
        //             $checkIn = $punch->check_in;
        //             $checkOut = $data->LogDate;
        //             if( $startOfDay->diffInSeconds(substr($data->LogDate, 11)) <= $startOfDay->diffInSeconds(substr($punch->check_in, 11)) )
        //             {
        //                 $checkIn = $data->LogDate;
        //                 $checkOut = $punch->check_in;
        //             }
        //             $duration = Carbon::parse($checkIn)->diffInSeconds($checkOut);

        //             DB::table('punches')->where([ 'emp_code' => $data->UserId, 'punch_date' => $punchDate, 'punch_by'=> '0'])
        //                             ->update([
        //                                 'check_in'=> $checkIn,
        //                                 'check_out'=> $checkOut,
        //                                 'duration'=> $duration,
        //                                 'updated_at'=> $timeStamp,
        //                             ]);
        //         }
        //     }
        //     $updatableId = $data->DeviceLogId;
        // }

        // DB::table('last_synced_ids')->where('name', 'last_log_id')->update(['value'=> $updatableId, 'updated_at'=> $timeStamp]);
        $this->info('Command executed successfully!');
    }

}
