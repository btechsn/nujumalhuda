<?php

declare(strict_types=1);

namespace Modules\Core\Actions;

use Modules\Core\Data\UserData;
use Modules\Core\Events\UserRegistered;
use Modules\Core\Models\User;
use Modules\Core\Support\PhoneNumber;

class RegisterUserAction
{
    public function execute(UserData $data, string $password): User
    {
        $user = User::create([
            'first_name' => $data->first_name,
            'last_name' => $data->last_name,
            'email' => $data->email,
            'phone' => PhoneNumber::e164($data->phone) ?? $data->phone,
            'password' => $password,
            'locale' => $data->locale,
            'timezone' => $data->timezone,
        ]);

        event(new UserRegistered($user));

        return $user;
    }
}
