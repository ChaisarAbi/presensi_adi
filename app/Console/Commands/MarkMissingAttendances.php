<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Permission;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MarkMissingAttendances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:mark-missing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark students as "Tidak Hadir" if they have no attendance record for today';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->format('Y-m-d');
        $this->info("Checking missing attendances for date: {$today}");
        
        // Get all active students
        $students = Student::with('user')->whereHas('user', function($query) {
            $query->where('is_active', true);
        })->get();
        
        $this->info("Total active students: {$students->count()}");
        
        $markedCount = 0;
        $skippedCount = 0;
        
        foreach ($students as $student) {
            // Check if student already has attendance for today
            $existingAttendance = Attendance::where('student_id', $student->id)
                ->whereDate('tanggal', $today)
                ->first();
            
            // Check if student has pending permission for today
            $pendingPermission = Permission::where('student_id', $student->id)
                ->whereDate('tanggal', $today)
                ->where('status', 'Pending')
                ->exists();
            
            if ($existingAttendance) {
                // Student already has attendance record
                $skippedCount++;
                continue;
            }
            
            if ($pendingPermission) {
                // Student has pending permission, skip marking as "Tidak Hadir"
                $this->info("Student {$student->id} has pending permission, skipping...");
                $skippedCount++;
                continue;
            }
            
            // Mark as "Tidak Hadir"
            Attendance::create([
                'student_id' => $student->id,
                'tanggal' => $today,
                'waktu' => '00:00:00',
                'status' => 'Tidak Hadir',
            ]);
            
            $markedCount++;
            $this->info("Marked student {$student->id} as 'Tidak Hadir'");
            
            // Log for monitoring
            Log::info("Auto-marked student as Tidak Hadir", [
                'student_id' => $student->id,
                'date' => $today,
                'action' => 'auto_mark_missing'
            ]);
        }
        
        $this->info("Process completed!");
        $this->info("Marked {$markedCount} students as 'Tidak Hadir'");
        $this->info("Skipped {$skippedCount} students (already have attendance or pending permission)");
        
        return Command::SUCCESS;
    }
}
