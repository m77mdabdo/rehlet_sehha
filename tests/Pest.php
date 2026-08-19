<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Feature tests run against the real MySQL 8 test database (see phpunit.xml)
| and are wrapped in a transaction that is rolled back after each test, so
| every test sees a freshly migrated schema without paying to rebuild it.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
| The Unit suite gets the application container but NOT the database.
|
| Its tests are conventions checks — they read Blade files and translation
| files off disk and need lang_path(), resource_path() and config() to resolve.
| That needs a booted app. It does not need a schema, and paying for
| RefreshDatabase here would add a transaction per test to prove nothing.
*/
pest()->extend(TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain
| conditions. The "expect()" function gives you access to a set of
| "expectations" methods that you can use to assert different things.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});
