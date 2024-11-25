<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-email', function () {
    $data = [
        'name' => 'John Doe', 
    ];

    Mail::send('emails.test', $data, function ($message) {
        $message->to('joehaddad94@gmail.com')
                ->subject('Test Email from Laravel');
    });

    return 'Test email sent!';
});