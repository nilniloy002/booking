<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SubCateoryController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TimeSlotController;
use App\Http\Controllers\BookingController;


use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard',[ProfileController::class,'dashboard'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // AJAX routes accessible to all authenticated users
    Route::post('booking/check', [BookingController::class, 'checkStudentBooking'])->name('booking.check');
    Route::post('booking/seats', [BookingController::class, 'getSeatAvailability'])->name('booking.seats');
    Route::post('booking/check-student', [BookingController::class, 'checkStudentExists'])->name('booking.check-student');
    Route::get('student/import', [StudentController::class, 'importForm'])->name('student.import.form');
    Route::post('student/import', [StudentController::class, 'import'])->name('student.import');

    
    Route::middleware(['role:admin'])->group(function(){
        Route::resource('user',UserController::class);
        Route::resource('role',RoleController::class);
        Route::resource('permission',PermissionController::class);
        Route::resource('category',CategoryController::class);
        Route::resource('subcategory',SubCateoryController::class);
        Route::resource('collection',CollectionController::class);
        Route::resource('student', StudentController::class);
        Route::resource('time_slot', TimeSlotController::class);
        Route::resource('booking', BookingController::class);
        Route::resource('product',ProductController::class);
        Route::get('/get/subcategory',[ProductController::class,'getsubcategory'])->name('getsubcategory');
        Route::get('/remove-external-img/{id}',[ProductController::class,'removeImage'])->name('remove.image');
    });
});

  

