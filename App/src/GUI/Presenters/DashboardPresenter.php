<?php

namespace App\GUI\Presenters;

use App\Domain\User\User;
use App\Domain\SAE\SaeSubject;

class DashboardPresenter
{
    /**
     * Prepares data for the DashboardView.
     *
     * @param User       $user
     * @param array<SaeSubject> $saes
     * @return array<string, mixed>
     */
    public function present(User $user, array $saes): array
    {
        return [
            'user' => $user,
            'saes' => $saes
        ];
    }
}
