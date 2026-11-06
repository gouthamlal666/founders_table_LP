<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

Artisan::command('admin:create', function () {
    $name = $this->ask('Name');
    $email = $this->ask('Email');
    $password = $this->secret('Password (min 10 characters)');

    $validator = Validator::make(compact('name', 'email', 'password'), [
        'name' => ['required', 'string', 'max:120'],
        'email' => ['required', 'email', 'max:200'],
        'password' => ['required', 'string', 'min:10'],
    ]);

    if ($validator->fails()) {
        foreach ($validator->errors()->all() as $error) {
            $this->error($error);
        }
        return 1;
    }

    User::updateOrCreate(['email' => $email], ['name' => $name, 'password' => $password]);
    $this->info("Admin ready: {$email} — sign in at ".route('login'));

    return 0;
})->purpose('Create (or reset the password of) a Founders Table admin');
