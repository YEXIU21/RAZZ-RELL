<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController as UserController;
use App\Http\Controllers\Global\PackageController as PackageController;
use App\Http\Controllers\Admin\GuestController as GuestController;
use App\Http\Controllers\Global\BookingController as BookingController;
use App\Http\Controllers\Global\PortfolioController as PortfolioController;
use App\Http\Controllers\Global\RateController as RateController;
use App\Http\Controllers\Admin\DashboardControler as DashboardControler;
use App\Http\Controllers\Global\PaymentController as PaymentController;
use App\Http\Controllers\Global\MessageController as MessageController;
use App\Http\Controllers\Global\ChatController as ChatController;
use App\Http\Controllers\AnalyticsController as AnalyticsController;
use App\Http\Controllers\ArchivedBookingController as ArchivedBookingController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Global\StorageController as StorageController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Authentication Routes
Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'register']);
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');

//Booking
Route::post('/add-booking', [BookingController::class, 'addBooking']);
Route::get('/get-all-bookings', [BookingController::class, 'index']);
Route::get('/get-booking-by-id/{id}', [BookingController::class, 'getBookingById']);
Route::get('/bookings/user/{id}', [BookingController::class, 'getBookingByUserId']);
Route::post('/update-booking', [BookingController::class, 'updateBooking']);
Route::delete('/bookings/{id}', [BookingController::class, 'deleteBooking']);

//Guest
Route::post('/add-guest', [GuestController::class, 'addGuest']);
Route::get('/get-all-guests', [GuestController::class, 'index']);

// Users
Route::post('/add-user', [UserController::class, 'addUser']);
Route::get('/get-all-users', [UserController::class, 'getAllUsers']);
Route::get('/get-user-by-id/{id}', [UserController::class, 'getUserById']);
Route::post('/update-user-info', [UserController::class, 'updateUserInfo']);
Route::post('/contact-us', [UserController::class, 'contactUs']);
Route::post('/update-user-password', [UserController::class, 'updateUserPassword']);
Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
Route::get('/guests', [GuestController::class, 'index']);
Route::get('/my-info', [UserController::class, 'getMyInfo']);
Route::get('/get-all-guest', [GuestController::class, 'index']);
Route::post('/delete-user/{id}', [GuestController::class, 'deleteGuest']);
Route::post('/admin/block-user/{id}', [UserController::class, 'blockUser']);
Route::post('/change-avatar', [UserController::class, 'changeProfile']);

// Authentication
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'logout']);
Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
Route::post('/reset-password', [UserController::class, 'resetPassword']);

// Packages
Route::get('/get-all-packages', [PackageController::class, 'index']);
Route::post('/add-package', [PackageController::class, 'addPackage']);
Route::get('/get-package-by-id/{id}', [PackageController::class, 'getPackageById']);
Route::post('/delete-package/{id}', [PackageController::class, 'deletePackage']);
Route::post('/update-package', [PackageController::class, 'updatePackage']);
//Booking

// Portfolio
Route::get('/get-all-portfolios', [PortfolioController::class, 'allPortfolios']);
Route::post('/add-portfolio', [PortfolioController::class, 'addPortfolio']);
Route::post('/delete-portfolio/{id}', [PortfolioController::class, 'deletePortfolio']);
Route::post('/update-portfolio', [PortfolioController::class, 'updatePortfolio']);

// Contact Us
Route::get('/get-all-contact-us', [UserController::class, 'getAllContactUs']);

// Rating
Route::post('/add-rating', [RateController::class, 'addRating']);
Route::get('/get-all-ratings/{id}', [RateController::class, 'getAllRatings']);
Route::get('/get-all-ratings', [App\Http\Controllers\Client\RatingController::class, 'getAllRatings']);
Route::get('/package-reviews/{packageId}', [RateController::class, 'getPackageReviews']);

// Dashboard
Route::get('/get-event-type-stats', [DashboardControler::class, 'getEventTypeStats']);
Route::get('/get-monthly-revenue', [DashboardControler::class, 'getMonthlyRevenue']);
Route::get('/get-satisfaction-rate', [DashboardControler::class, 'getSatisfactionRate']);

// Payment routes
Route::post('/payments', [App\Http\Controllers\Global\PaymentController::class, 'store']);
Route::get('/bookings/{bookingId}/payments', [App\Http\Controllers\Global\PaymentController::class, 'getPayments']);

// Message routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/bookings/{bookingId}/messages', [MessageController::class, 'getMessages']);
    Route::post('/messages', [MessageController::class, 'sendMessage']);
    Route::put('/messages/read', [MessageController::class, 'markAsRead']);
    Route::get('/bookings/{bookingId}/messages/unread', [MessageController::class, 'getUnreadCount']);
    Route::get('/chat/unread-count', [ChatController::class, 'getUnreadCount']);
    
    // Add new chat routes
    Route::get('/chat/messages', [ChatController::class, 'getMessages']);
    Route::post('/chat/send', [ChatController::class, 'sendMessage']);
    Route::put('/chat/mark-read', [ChatController::class, 'markAsRead']);
});

// Analytics
Route::get('/get-active-users', [AnalyticsController::class, 'getActiveUsers']);
Route::get('/get-ratings', [AnalyticsController::class, 'getRatings']);
Route::get('/get-revenue-overview', [AnalyticsController::class, 'getRevenueOverview']);
Route::get('/get-bookings-by-event-type', [AnalyticsController::class, 'getBookingsByEventType']);
Route::get('/get-monthly-revenue', [AnalyticsController::class, 'getMonthlyRevenue']);
Route::get('/get-popular-packages', [AnalyticsController::class, 'getPopularPackages']);

// Archived Bookings
Route::prefix('bookings')->group(function () {
    Route::get('/archived', [ArchivedBookingController::class, 'index']);
    Route::post('/{id}/archive', [ArchivedBookingController::class, 'archive']);
    Route::post('/{id}/restore', [ArchivedBookingController::class, 'restore']);
});

Route::get('/health', function () {
    return response()->json(['status' => 'healthy'], 200);
});

// Test database connection
Route::get('/test-db', function () {
    try {
        DB::connection()->getPdo();
        return response()->json([
            'message' => 'Database connection successful',
            'database' => DB::connection()->getDatabaseName()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Database connection failed',
            'error' => $e->getMessage()
        ], 500);
    }
});

// Storage routes for direct access
Route::get('storage/{path}', [StorageController::class, 'getFile'])->where('path', '.*');
Route::post('storage/{folder}', [StorageController::class, 'uploadFile']);


