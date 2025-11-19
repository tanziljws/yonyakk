<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Galeri;
use App\Models\User;
use App\Models\Agenda;
use App\Models\Informasi;
use App\Models\ActivityLog;
use App\Models\Like;
use App\Models\Comment;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get real statistics
        $stats = [
            'total_users' => User::count(),
            'total_gallery' => Galeri::count(),
            'total_agenda' => Agenda::count(),
            'total_views' => $this->calculateTotalViews(),
            
            // Growth percentages (compare with last month)
            'users_growth' => $this->calculateGrowth('users'),
            'gallery_growth' => $this->calculateGrowth('galeris'),
            'agenda_growth' => $this->calculateGrowth('agendas'),
            'views_growth' => 25, // You can implement real view tracking later
        ];

        // Get recent activities (last 4)
        $recent_activities = ActivityLog::with('user')
            ->latest()
            ->take(4)
            ->get()
            ->map(function($log) {
                return [
                    'time' => $log->created_at->diffForHumans(),
                    'text' => $log->activity_name,
                    'status' => $log->status,
                    'type' => $this->getActivityType($log->activity_type),
                ];
            });

        return view('admin.dashboard', compact('stats', 'recent_activities'));
    }

    private function calculateTotalViews()
    {
        // Calculate total interactions (likes + comments) as proxy for views
        $totalLikes = Like::count();
        $totalComments = Comment::count();
        $totalGallery = Galeri::count();
        
        // Estimate: each gallery gets average views
        return ($totalLikes + $totalComments) * 10 + ($totalGallery * 50);
    }

    private function calculateGrowth($table)
    {
        $currentMonth = DB::table($table)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $lastMonth = DB::table($table)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        if ($lastMonth == 0) {
            return $currentMonth > 0 ? 100 : 0;
        }

        $growth = (($currentMonth - $lastMonth) / $lastMonth) * 100;
        return round($growth);
    }

    private function getActivityType($activityType)
    {
        $types = [
            'admin_post_galeri' => 'success',
            'admin_post_agenda' => 'info',
            'admin_post_informasi' => 'info',
            'admin_edit_agenda' => 'info',
            'admin_edit_informasi' => 'info',
            'admin_delete_agenda' => 'warning',
            'admin_delete_informasi' => 'warning',
            'user_register' => 'warning',
            'user_login' => 'info',
            'user_like' => 'success',
            'user_comment' => 'success',
        ];

        return $types[$activityType] ?? 'info';
    }
}
