<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Permission;
use Illuminate\Console\Command;

class SyncPermissionAttendances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:permission-attendances';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync approved/rejected permissions to attendance records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Syncing permissions to attendance records...');
        
        // Get all approved permissions
        $approvedPermissions = Permission::where('status', 'Disetujui')->get();
        $this->info("Found {$approvedPermissions->count()} approved permissions.");
        
        $approvedCount = 0;
        $updatedCount = 0;
        
        foreach ($approvedPermissions as $permission) {
            // Check if attendance record already exists for this permission
            $existingAttendance = Attendance::where('student_id', $permission->student_id)
                ->whereDate('tanggal', $permission->tanggal)
                ->first();
            
            if ($existingAttendance) {
                // Update existing record to "Izin"
                if ($existingAttendance->status !== 'Izin') {
                    $existingAttendance->update([
                        'status' => 'Izin',
                        'waktu' => $existingAttendance->waktu ?? '00:00:00',
                    ]);
                    $updatedCount++;
                }
            } else {
                // Create new attendance record for "Izin"
                Attendance::create([
                    'student_id' => $permission->student_id,
                    'tanggal' => $permission->tanggal,
                    'waktu' => '00:00:00',
                    'status' => 'Izin',
                ]);
                $approvedCount++;
            }
        }
        
        // Get all rejected permissions
        $rejectedPermissions = Permission::where('status', 'Ditolak')->get();
        $this->info("Found {$rejectedPermissions->count()} rejected permissions.");
        
        $rejectedCount = 0;
        $rejectedUpdatedCount = 0;
        
        foreach ($rejectedPermissions as $permission) {
            // Check if attendance record already exists for this permission
            $existingAttendance = Attendance::where('student_id', $permission->student_id)
                ->whereDate('tanggal', $permission->tanggal)
                ->first();
            
            if ($existingAttendance) {
                // Update existing record to "Tidak Hadir"
                if ($existingAttendance->status !== 'Tidak Hadir') {
                    $existingAttendance->update([
                        'status' => 'Tidak Hadir',
                        'waktu' => $existingAttendance->waktu ?? '00:00:00',
                    ]);
                    $rejectedUpdatedCount++;
                }
            } else {
                // Create new attendance record for "Tidak Hadir"
                Attendance::create([
                    'student_id' => $permission->student_id,
                    'tanggal' => $permission->tanggal,
                    'waktu' => '00:00:00',
                    'status' => 'Tidak Hadir',
                ]);
                $rejectedCount++;
            }
        }
        
        $this->info("Sync completed!");
        $this->info("Created {$approvedCount} new 'Izin' attendance records.");
        $this->info("Updated {$updatedCount} existing records to 'Izin'.");
        $this->info("Created {$rejectedCount} new 'Tidak Hadir' attendance records.");
        $this->info("Updated {$rejectedUpdatedCount} existing records to 'Tidak Hadir'.");
        
        return Command::SUCCESS;
    }
}