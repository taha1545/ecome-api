<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return "this is ecome api project";
});

Route::get('/socket-test', fn() => view('socket-test'));

// 
Route::get('/view/email/admin-message', function () {
    $user = (object)['name' => 'Test User'];
    $subjectText = 'Test Subject';
    $bodyMessage = 'This is a test message from the admin.';
    return view('emails.admin_message', compact('user', 'subjectText', 'bodyMessage'));
});

Route::get('/view/email/welcome', function () {
    $user = (object)['name' => 'Test User'];
    return view('Mailer.Welcome', compact('user'));
});

Route::get('/view/email/otp', function () {
    $otp = '123456';
    return view('Mailer.Otp', compact('otp'));
});

