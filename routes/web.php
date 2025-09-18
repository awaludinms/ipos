<?php

use App\Http\Controllers\Invoice\PrintInvoiceController;
use App\Livewire\Welcome;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
 
// Users will be redirected to this route if not logged in
Volt::route('/login', 'login')->name('login');
 
// Define the logout
Route::get('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
 
    return redirect('/');
});
 
// Protected routes here
Route::middleware('auth')->group(function () {
    Volt::route('/', 'product');
    Volt::route('/users', 'users.index');
    Volt::route('/users/create', 'users.create');
    Volt::route('/users/{user}/edit', 'users.edit');

    // Product
    Volt::route('/product', 'product');
    Volt::route('/products/create', 'products.create');
    Volt::route('/products/{product}/edit', 'products.edit');

    // Product Type
    Volt::route('/product_types', 'producttype.index');
    Volt::route('/product_types/create', 'producttype.create');
    Volt::route('/product_types/{product_type}/edit', 'producttype.edit');

    // Pengeluaran
     // Product Type
     Volt::route('/expenses', 'expenses.index');
     Volt::route('/expenses/create', 'expenses.create');
     Volt::route('/expenses/{expense}/edit', 'expenses.edit');
 
     Volt::route('/transactions', 'transactions.index');
     Volt::route('/transactions/create', 'transactions.create');
     Volt::route('/transactions/create-reseller', 'transactions.create_reseller');
     Volt::route('/transactions/{transaction}/detail', 'transactions.detail');
   
    // Experiment
    Volt::route('/exp', 'experiment.form');

    // Print & Download struk
    Route::get('/print/{transaction}', [PrintInvoiceController::class, 'index']);
    Route::get('/download/{transaction}', [PrintInvoiceController::class, 'download']);
    Route::get('/print-reseller/{transaction}', [PrintInvoiceController::class, 'reseller']);
    Route::get('/download-reseller/{transaction}', [PrintInvoiceController::class, 'downloadReseller']);
    
    // User Management
    Volt::route('/user/management', 'user.management');
    // ... more
});


// Route::get('/', Welcome::class);




