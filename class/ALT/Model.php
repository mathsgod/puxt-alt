<?php

namespace ALT;

use App\User;

interface Model
{

    public function uri(string $uri);
    public function canReadBy(User $user);
    public function canUpdateBy(User $user);
    public function canDeleteBy(User $user);
}
