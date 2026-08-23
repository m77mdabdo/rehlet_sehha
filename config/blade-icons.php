<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Components
    |--------------------------------------------------------------------------
    |
    | blade-icons arrived as a dependency of Filament and registers <x-icon> as
    | a global Blade component. THIS PROJECT ALREADY OWNS THAT NAME:
    | resources/views/components/icon.blade.php is the public site's own icon
    | set, and it takes a `name` like "stethoscope" that has no equivalent in
    | any installed icon pack.
    |
    | With the default registration in place, blade-icons wins the name and
    | every public page that draws an icon dies with
    | "Svg by name 'stethoscope' from set 'default' not found" — a 500 on the
    | homepage, caused entirely by installing an admin panel.
    |
    | Setting the default component name to null unregisters the alias, so
    | <x-icon> resolves to the project's component again. Filament does not use
    | it: its own icons are rendered through Heroicon enums and the
    | <x-filament::icon> component, which are unaffected.
    |
    */

    'components' => [
        'default' => null,
    ],

];
