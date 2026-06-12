<?php

namespace Devdojo\Changelog\Http\Controllers;

use Devdojo\Changelog\Models\Changelog;

class ChangelogController extends Controller
{
    public function read()
    {
        $user = auth()->user();
        Changelog::whereDoesntHave('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get()
            ->pluck('id')
            ->tap(function ($missingChangelogNotifications) use ($user) {
                $user->changelogs()->attach($missingChangelogNotifications->toArray());
            });
    }
}
