<?php

declare(strict_types=1);

use App\Orchid\Screens\English\AddVerbScreen;
use App\Orchid\Screens\English\VerbsLearningScreenx;
use App\Orchid\Screens\English\VerbsListScreen;
use App\Orchid\Screens\Norwegian\AddWordScreen;
use App\Orchid\Screens\Norwegian\LearningScreen;
use App\Orchid\Screens\Norwegian\WordsListScreen;
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the need "dashboard" middleware group. Now create something great!
|
*/

// Main
Route::screen('/main', PlatformScreen::class)
    ->name('platform.main');

// Platform > Profile
Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Profile'), route('platform.profile')));

// Platform > System > Users > User
Route::screen('users/{user}/edit', UserEditScreen::class)
    ->name('platform.systems.users.edit')
    ->breadcrumbs(fn (Trail $trail, $user) => $trail
        ->parent('platform.systems.users')
        ->push($user->name, route('platform.systems.users.edit', $user)));

// Platform > System > Users > Create
Route::screen('users/create', UserEditScreen::class)
    ->name('platform.systems.users.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.users')
        ->push(__('Create'), route('platform.systems.users.create')));

// Platform > System > Users
Route::screen('users', UserListScreen::class)
    ->name('platform.systems.users')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Users'), route('platform.systems.users')));

Route::screen('addVerb', AddVerbScreen::class)
            ->name('platform.verbs.add')
            ->breadcrumbs(function (Trail $trail) {
                return $trail
                    ->parent('platform.index')
                    ->push('addVerb');
            });

Route::screen('verbsList', VerbsListScreen::class)
            ->name('platform.verbs.list')
            ->breadcrumbs(function (Trail $trail) {
                return $trail
                    ->parent('platform.index')
                    ->push('verbsList');
            });

Route::screen('verbsLearning', VerbsLearningScreenx::class)
            ->name('platform.verbs.learning')
            ->breadcrumbs(function (Trail $trail) {
                return $trail
                    ->parent('platform.index')
                    ->push('verbsLearning');
            });

Route::screen('addWord', AddWordScreen::class)
    ->name('platform.words.add')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('wordsLearning');
    });

Route::screen('wordsList', WordsListScreen::class)
    ->name('platform.words.list')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('wordsList');
    });

Route::screen('Learning', LearningScreen::class)
    ->name('platform.learning')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push('wordsLearning');
    });



Route::screen('english/verb/add', AddVerbScreen::class)->name('platform.english.verbs.add');
Route::screen('english/verbs/list', VerbsListScreen::class)->name('platform.english.verbs.list');
Route::screen('english/verbs/learn', VerbsLearningScreenx::class)->name('platform.english.verbs.learn');
Route::screen('norwegian/words/learn', LearningScreen::class)->name('platform.norwegian.words.learn');
Route::screen('norwegian/words/list', WordsListScreen::class)->name('platform.norwegian.words.list');
Route::screen('norwegian/words/add', AddWordScreen::class)->name('platform.norwegian.words.add');

//Route::screen('idea', Idea::class, 'platform.screens.idea');
