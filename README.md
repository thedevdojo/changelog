# DevDojo Changelog

A drop-in **product changelog / release notes** feature for Laravel — with per‑user
"read" tracking so you can show a "what's new" indicator. Ships the `Changelog` model, a
`HasChangelogs` trait for your `User`, a mark‑as‑read endpoint, and a Filament admin
resource. Front-end agnostic, so it fits any theme.

`devdojo/changelog` is one of the feature packages bundled by
[`devdojo/foundation`](https://github.com/thedevdojo/foundation), but it works perfectly
well **standalone** in any Laravel app.

---

## Table of contents

- [Requirements](#requirements)
- [How it works](#how-it-works)
- [Installation](#installation)
- [Configuration](#configuration)
- [Wiring your User model](#wiring-your-user-model)
- [The data model](#the-data-model)
- [Creating changelog entries](#creating-changelog-entries)
- [Read tracking & the "what's new" indicator](#read-tracking--the-whats-new-indicator)
- [Rendering a changelog page](#rendering-a-changelog-page)
- [Filament admin](#filament-admin)
- [Using with DevDojo Foundation](#using-with-devdojo-foundation)
- [Configuration reference](#configuration-reference)
- [FAQ / troubleshooting](#faq--troubleshooting)

---

## Requirements

| Requirement | Notes |
| --- | --- |
| PHP `^8.2` | |
| Laravel `^10 / ^11 / ^12` | |
| `filament/filament` `^4` *(optional)* | Only needed for the bundled Changelog admin resource. |

The changelog is **front-end agnostic**: it provides the model, read‑tracking, and admin,
and leaves the public pages to your application/theme.

---

## How it works

```
┌──────────────────────────────────────────────────────────────┐
│ devdojo/changelog                                            │
│                                                              │
│  Changelog ──belongsToMany──▶ User (your app)                │
│        (read-tracking pivot: changelog_user)                 │
│                                                              │
│  Trait added to your User:                                   │
│   • HasChangelogs  (changelogs(), hasChangelogNotifications())│
│                                                              │
│  POST /changelog/read  •  Filament ChangelogResource         │
└──────────────────────────────────────────────────────────────┘
```

- A **Changelog** is a release note (`title`, `description`, `body`).
- Each user's **read** state is tracked through a `changelog_user` pivot.
- `hasChangelogNotifications()` tells you whether the user has unread entries, so you can
  show a "what's new" badge or popup. Hitting `POST /changelog/read` marks everything read.

---

## Installation

```bash
composer require devdojo/changelog
```

Publish the migrations and config, then migrate:

```bash
php artisan vendor:publish --tag=changelog:migrations
php artisan vendor:publish --tag=changelog:config
php artisan migrate
```

> Migrations are **publish-only** (not auto-loaded) so the `changelogs` and `changelog_user`
> tables live in your app's `database/migrations` and are yours to edit.

If your `User` model isn't discoverable from `config('auth.providers.users.model')`, set it
explicitly:

```env
CHANGELOG_USER_MODEL="App\\Models\\User"
```

Then [wire your User model](#wiring-your-user-model) and (optionally)
[register the Filament admin](#filament-admin).

---

## Configuration

Publishing `changelog:config` writes `config/devdojo/changelog/settings.php` (config key
`devdojo.changelog.settings`):

```php
return [
    // The host User model used for read-tracking. Null → auth.providers.users.model.
    'user_model' => env('CHANGELOG_USER_MODEL'),
];
```

---

## Wiring your User model

Add the `HasChangelogs` trait to your `User` model:

```php
use Devdojo\Changelog\Traits\HasChangelogs;

class User extends Authenticatable
{
    use HasChangelogs;
}
```

This adds:

```php
$user->changelogs;                    // BelongsToMany — entries the user has read
$user->hasChangelogNotifications();   // bool — is the latest entry unread?
```

---

## The data model

### `changelogs`

| Column | Type | Notes |
| --- | --- | --- |
| `id` | increments | |
| `title` | string(191) | |
| `description` | string(191) | short summary shown in the notification |
| `body` | text | full HTML body |
| timestamps | | |

### `changelog_user` (read-tracking pivot)

| Column | Type | Notes |
| --- | --- | --- |
| `changelog_id` | unsigned int | FK → `changelogs.id` (`cascade`) |
| `user_id` | unsigned big int | FK → `users.id` (`cascade`) |
| primary key | `(changelog_id, user_id)` | a row means "this user has read this entry" |

The `Changelog` model is mass-assignable for `title`, `description`, and `body`.

---

## Creating changelog entries

```php
use Devdojo\Changelog\Models\Changelog;

Changelog::create([
    'title'       => 'v3.0 Released',
    'description' => 'A big update with new features and improvements.',
    'body'        => '<p>Here is everything that changed…</p>',
]);
```

Most teams author entries from the [Filament admin](#filament-admin) instead.

---

## Read tracking & the "what's new" indicator

Show an indicator when the signed-in user has unread entries:

```blade
@auth
    @if (auth()->user()->hasChangelogNotifications())
        <a href="{{ route('changelog') }}" class="badge">What's new</a>
    @endif
@endauth
```

`hasChangelogNotifications()` returns `true` when the **latest** changelog has not been read
by the user.

When the user views/dismisses the changelog, mark everything as read by POSTing to the
package's endpoint:

| Method | URI | Name | Middleware |
| --- | --- | --- | --- |
| `POST` | `/changelog/read` | `changelog.read` | `web`, `auth` |

```js
// e.g. when the "what's new" popup is dismissed
fetch('{{ route('changelog.read') }}', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
});
```

Under the hood this attaches every unread entry to the user via the `changelog_user` pivot,
so `hasChangelogNotifications()` returns `false` afterwards.

---

## Rendering a changelog page

The package is headless on the front-end — render the changelog however your app prefers
using the model:

```php
use Devdojo\Changelog\Models\Changelog;

// List page
$logs = Changelog::orderByDesc('created_at')->paginate(10);

// Single entry
$changelog = Changelog::findOrFail($id);
```

A minimal Folio example:

```php
// resources/views/pages/changelog/index.blade.php
<?php
use function Laravel\Folio\name;
use Devdojo\Changelog\Models\Changelog;

name('changelog');
$logs = Changelog::orderByDesc('created_at')->paginate(10);
?>

<x-layout>
    @foreach ($logs as $log)
        <article>
            <time>{{ $log->created_at->toFormattedDateString() }}</time>
            <h2>{{ $log->title }}</h2>
            <div>{!! $log->body !!}</div>
        </article>
    @endforeach
    {{ $logs->links() }}
</x-layout>
```

---

## Filament admin

If you use Filament, register the plugin in your panel to get a **Changelogs** resource at
`/admin/changelogs`:

```php
use Devdojo\Changelog\Filament\ChangelogPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(ChangelogPlugin::make());
}
```

The resource manages the title, description, and a rich-text body. Filament is an optional
dependency — the model/migrations/trait work without it.

---

## Using with DevDojo Foundation

When the [`devdojo/foundation`](https://github.com/thedevdojo/foundation) metapackage is
installed, the changelog **self-gates** on its feature flag:

```php
// config/foundation.php
'features' => [
    'changelog' => true,   // flip to false (or toggle at /foundation/setup) to disable
],
```

When `changelog` is disabled, the `changelog/read` route and the Filament resource are not
registered. The model, trait, and migrations remain available, so toggling is lossless.

Standalone (no Foundation present), the flag is absent and the changelog defaults to **on**.

---

## Configuration reference

### `config/devdojo/changelog/settings.php`

```php
return [
    'user_model' => env('CHANGELOG_USER_MODEL'), // null → auth.providers.users.model
];
```

### Publish tags

| Tag | Publishes to |
| --- | --- |
| `changelog:config` | `config/devdojo/changelog/settings.php` |
| `changelog:migrations` | `database/migrations` |

---

## FAQ / troubleshooting

**`hasChangelogNotifications()` always returns true.**
It compares the latest entry against the user's read pivot. Make sure you call the
`changelog.read` endpoint when the user views the changelog, and that your `User` uses the
`HasChangelogs` trait.

**Where is the changelog page / popup?**
The package is headless on the front-end. Render the list/detail with the model (see
[Rendering a changelog page](#rendering-a-changelog-page)); in the Wave starter kit they ship
as theme Folio pages and a notification partial you can edit.

**Where do `changelogs` / `changelog_user` tables come from?**
They're publish-only migrations — run
`php artisan vendor:publish --tag=changelog:migrations && php artisan migrate`.

---

## License

MIT © [DevDojo](https://devdojo.com)
