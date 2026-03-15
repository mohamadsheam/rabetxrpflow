<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    public function log(
        string $description,
        ?User $user = null,
        ?Model $model = null,
        array $properties = []
    ): void {

        $activity = activity()
            ->causedBy($user)
            ->withProperties(array_merge([
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->url(),
                'method' => request()->method(),
            ], $properties));

        if ($model) {
            $activity->performedOn($model);
        }

        $activity->log($description);
    }
}
