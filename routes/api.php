<?php

use App\Http\Controllers\Api\V1\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Api\V1\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Api\V1\Admin\RestaurantController as AdminRestaurantController;
use App\Http\Controllers\Api\V1\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Api\V1\Admin\SubscriptionPaymentController as AdminSubscriptionPaymentController;
use App\Http\Controllers\Api\V1\Admin\SubscriptionStatusController as AdminSubscriptionStatusController;
use App\Http\Controllers\Api\V1\AssistanceRequestController;
use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OptionGroupController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\OrderStatusController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PaymentRefundController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductImageController;
use App\Http\Controllers\Api\V1\PublicAssistanceRequestController;
use App\Http\Controllers\Api\V1\PublicMenuController;
use App\Http\Controllers\Api\V1\PublicOrderController;
use App\Http\Controllers\Api\V1\PublicQrCodeController;
use App\Http\Controllers\Api\V1\QrCodeController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\RestaurantClosureController;
use App\Http\Controllers\Api\V1\RestaurantConfigurationController;
use App\Http\Controllers\Api\V1\RestaurantController;
use App\Http\Controllers\Api\V1\RestaurantLogoController;
use App\Http\Controllers\Api\V1\RestaurantMemberController;
use App\Http\Controllers\Api\V1\RestaurantOpeningHourController;
use App\Http\Controllers\Api\V1\RestaurantTableController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/public')->name('api.v1.public.')->group(function (): void {
    Route::get('restaurants/{restaurant:slug}/menu', PublicMenuController::class)
        ->name('restaurants.menu.show');
    Route::get('qr-codes/{token}', PublicQrCodeController::class)
        ->name('qr-codes.show');
    Route::post('restaurants/{restaurant:slug}/orders', [PublicOrderController::class, 'store'])
        ->name('restaurants.orders.store');
    Route::get('orders/{token}', [PublicOrderController::class, 'show'])
        ->name('orders.show');
    Route::post('restaurants/{restaurant:slug}/assistance-requests', PublicAssistanceRequestController::class)
        ->name('restaurants.assistance-requests.store');
});

Route::middleware(['auth:sanctum', 'verified', 'platform.admin'])
    ->prefix('v1/admin')
    ->name('api.v1.admin.')
    ->group(function (): void {
        Route::get('dashboard', AdminDashboardController::class)->name('dashboard');
        Route::get('audit-logs', AdminAuditLogController::class)->name('audit-logs.index');
        Route::apiResource('plans', AdminPlanController::class)->only(['index', 'store', 'show', 'update']);
        Route::get('restaurants', [AdminRestaurantController::class, 'index'])->name('restaurants.index');
        Route::patch('restaurants/{restaurant}/status', [AdminRestaurantController::class, 'updateStatus'])
            ->name('restaurants.status.update');
        Route::apiResource('restaurants.subscriptions', AdminSubscriptionController::class)
            ->only(['index', 'store', 'show']);
        Route::patch('subscriptions/{subscription}/status', AdminSubscriptionStatusController::class)
            ->name('subscriptions.status.update');
        Route::apiResource('subscriptions.payments', AdminSubscriptionPaymentController::class)
            ->only(['index', 'store']);
    });

Route::middleware(['auth:sanctum', 'verified'])->prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::apiResource('restaurants', RestaurantController::class)->only([
        'index',
        'store',
        'show',
        'update',
    ]);

    Route::post('restaurants/{restaurant}/members/{membership}/reactivation', [RestaurantMemberController::class, 'reactivate'])
        ->name('restaurants.members.reactivate');
    Route::apiResource('restaurants.members', RestaurantMemberController::class)
        ->parameters(['members' => 'membership'])
        ->only(['index', 'store', 'show', 'update', 'destroy']);

    Route::patch('restaurants/{restaurant}/configuration', RestaurantConfigurationController::class)
        ->name('restaurants.configuration.update');
    Route::get('restaurants/{restaurant}/opening-hours', [RestaurantOpeningHourController::class, 'index'])
        ->name('restaurants.opening-hours.index');
    Route::put('restaurants/{restaurant}/opening-hours', [RestaurantOpeningHourController::class, 'update'])
        ->name('restaurants.opening-hours.update');
    Route::apiResource('restaurants.closures', RestaurantClosureController::class)
        ->parameters(['closures' => 'closure'])
        ->only(['index', 'store', 'destroy']);
    Route::post('restaurants/{restaurant}/logo', RestaurantLogoController::class)
        ->name('restaurants.logo.update');

    Route::apiResource('restaurants.categories', CategoryController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('restaurants.products', ProductController::class)
        ->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::post('restaurants/{restaurant}/products/{product}/image', ProductImageController::class)
        ->name('restaurants.products.image.update');
    Route::apiResource('restaurants.option-groups', OptionGroupController::class)
        ->parameters(['option-groups' => 'optionGroup'])
        ->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::apiResource('restaurants.tables', RestaurantTableController::class)
        ->parameters(['tables' => 'restaurantTable'])
        ->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::post('restaurants/{restaurant}/qr-codes/{qrCode}/regeneration', [QrCodeController::class, 'regenerate'])
        ->name('restaurants.qr-codes.regenerate');
    Route::apiResource('restaurants.qr-codes', QrCodeController::class)
        ->parameters(['qr-codes' => 'qrCode'])
        ->only(['index', 'store', 'destroy']);
    Route::patch('restaurants/{restaurant}/orders/{order}/status', OrderStatusController::class)
        ->name('restaurants.orders.status.update');
    Route::apiResource('restaurants.orders', OrderController::class)
        ->only(['index', 'show']);
    Route::apiResource('restaurants.assistance-requests', AssistanceRequestController::class)
        ->parameters(['assistance-requests' => 'assistanceRequest'])
        ->only(['index', 'show', 'update']);
    Route::apiResource('restaurants.orders.payments', PaymentController::class)
        ->only(['index', 'store']);
    Route::post('restaurants/{restaurant}/payments/{payment}/refunds', PaymentRefundController::class)
        ->name('restaurants.payments.refunds.store');
    Route::get('restaurants/{restaurant}/reports/overview', ReportController::class)
        ->name('restaurants.reports.overview');
    Route::get('restaurants/{restaurant}/audit-logs', AuditLogController::class)
        ->name('restaurants.audit-logs.index');
});
