<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Models\Package;
use App\Models\Rating;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function getAboutUsStats()
    {
        try {
            // Get completed events count
            $completedEvents = Booking::where('status', 'completed')->count();

            // Get active team members count (staff and admin)
            $teamMembers = User::whereIn('role', ['admin', 'staff'])
                ->where('status', 'active')
                ->count();

            // Get unique venues count from completed bookings
            $partnerVenues = Booking::where('status', 'completed')
                ->distinct('venue_name')
                ->count('venue_name');

            // Get count of users who have made bookings
            $happyClients = User::whereHas('bookings', function($query) {
                $query->where('status', 'completed');
            })->count();

            return response()->json([
                'status' => 200,
                'data' => [
                    'events_completed' => $completedEvents,
                    'team_members' => $teamMembers,
                    'partner_venues' => $partnerVenues,
                    'happy_clients' => $happyClients
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Error retrieving statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}