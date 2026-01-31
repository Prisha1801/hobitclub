<?php

namespace App\Helpers;

use App\Models\User;

class RoleIdGenerator
{
    public static function generate(string $roleSlug): string
    {
        $prefixMap = [
            'agent'          => 'ag',
            'co-ordinators'  => 'co',
            'operation-head' => 'oh',
            'staff'          => 'st',
            'workers'        => 'w',
            'customer'       => 'c',
            'super-admin'    => 'sa',
        ];

        $prefix = $prefixMap[$roleSlug] ?? 'u';

        // Get last user of SAME ROLE
        $lastUser = User::whereHas('role', function ($q) use ($roleSlug) {
                $q->where('slug', $roleSlug);
            })
            ->whereNotNull('public_id')
            ->orderByDesc('id')
            ->first();

        $lastNumber = 0;

        if ($lastUser && preg_match('/-(\d+)$/', $lastUser->public_id, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        return sprintf('%s-%03d', $prefix, $lastNumber + 1);
    }
}

