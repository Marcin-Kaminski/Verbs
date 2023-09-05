<?php

namespace App\Orchid\Screens\Norwegian;

use App\Models\NorwegianWords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class LearningScreen extends Screen
{

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $wordsLeftToLearn = Cache::get('words_left_to_learn') /** @todo rozbić to jakoś na funckje, to nie powinno tu chyba być */;
        if (!$wordsLeftToLearn) { /** @todo sprawdź, czy jak klikniesz zresetuj dane to sie restuje ilość powtórzen i typ nauki i ogolnie */
            $this->addAllWordsToCache();
            $wordsLeftToLearn = Cache::get('words_left_to_learn');
        }
        $randomWord = (Cache::get('random_word'));
        if (!$randomWord) {
            $this->drawAndSaveWordToCache($wordsLeftToLearn);
            $randomWord = (Cache::get('random_word'));
        }

        $wordForm = (Cache::get('word_form'));
        $wordForm = !$wordForm ? 'word_in_polish' : $wordForm;

        $allErrors = (Cache::get('all_errors'));
        $allErrors = !$allErrors ? 0 : $allErrors;

        $howManyTimesBeforeItIsGone = Cache::get('repeatCounter');
        $howManyTimesBeforeItIsGone === null
            ? Cache::put('repeatCounter', 1, now()->addHours(24)): $howManyTimesBeforeItIsGone;

        return [
            'words' => [
                'word_in_polish' => $randomWord['word_in_polish'],
                'word_in_norwegian' => $randomWord['word_in_norwegian'],
                'errors' => $randomWord['errors']
            ],
            'wordForm' => $wordForm,
            'allErrors' => $allErrors
        ];
    }
    public function name(): ?string
    {
        return 'Szybka powtórka z angielskiego';
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Sprawdź tłumaczenie')
                ->type(Color::INFO)
                ->method('checkTranslation'),
            Button::make('Wylosuj nowy')
                ->type(Color::DARK)
                ->method('drawWord'),
            ModalToggle::make('Pokaż odpowiedzi')
                ->type(Color::ERROR)
                ->modal('Tłumaczenie'),
            Button::make('Zresetuj dane')
                ->type(Color::PRIMARY)
                ->method('clearCache'),
            ModalToggle::make('Ustawienia')
                ->type(Color::WARNING)
                ->modal('Ustawienia')
                ->method('changeSettings')
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::modal('Tłumaczenie', [
                Layout::view('norwegian.translation')
            ]),
            Layout::modal('Ustawienia', Layout::rows([
                Select::make('learningMode')
                    ->options([
                        'word_in_polish' => 'Polski',
                        'word_in_norwegian' => 'Norweski',
                    ])
                    ->title('Jaki typ nauki wybierasz?')
                    ->required()
                    ->help('Wybierz w jakim języku będzie słówko bazowe!'),
                Select::make('repeatCounter')
                    ->options([
                        1 => 1,
                        2 => 2,
                        3 => 3
                    ])
                    ->allowAdd()
                    ->title('Wybierz ilość powtórzeń')
                    ->required()
                    ->help('Czasownik zniknie z puli losowanych do nauczenia po tylu razach
                (oczywiście dobrze przetłumaczonych), ile wybierzesz')
            ])),
            Layout::view('norwegian.script'),
            Layout::block([
                Layout::rows([
                    Input::make('word_in_polish')
                        ->title('PL')
                        ->autocomplete('off'),
                    Input::make('word_in_norwegian')
                        ->title('Norweski')
                        ->autocomplete('off'),
                ])
            ])];
    }

    public function changeSettings(Request $request): void
    {
        $request->validate([
            'learningMode' => 'required',
            'repeatCounter' => 'required|integer',
        ], [
            'repeatCounter.integer' => 'To pole musi być liczbą całkowitą'
        ]);
        $wordForm = $request->input('learningMode');
        Cache::put('word_form', $wordForm, now()->addHours(24));
        $howManyTimesBeforeItIsGone = $request->input('repeatCounter');
        Cache::put('repeatCounter', $howManyTimesBeforeItIsGone, now()->addHours(24));
    }

    public function addAllWordsToCache(): void
    {
        $allWords = (new NorwegianWords)->get()->toArray();
        $allWordsArray = [];
        foreach ($allWords as $word) {
            $allWordsArray[] = $word;
        }
        Cache::put('words_left_to_learn', $allWordsArray, now()->addHours(24));
    }

    public function checkTranslation(Request $request): void
    {
        $wordInPolishInput = $request->input('word_in_polish');
        $wordInNorwegianInput = $request->input('word_in_norwegian');

        $randomWord = Cache::get('random_word');

        $randomWordInPolish = $randomWord['word_in_polish'];
        $randomWordInNorwegian = $randomWord['word_in_norwegian'];

        $wordForm = Cache::get('word_form');
        switch ($wordForm) {
            case 'word_in_norwegian':
                $input1 = $wordInPolishInput;
                $comparator1 = $randomWordInPolish;
                break;
            default:
                $input1 = $wordInNorwegianInput;
                $comparator1 = $randomWordInNorwegian;
        }
        if (ucfirst($input1) === $comparator1) {
            Alert::success('Sukces! prawidłowe tłumaczenie! Spróbuj z kolejnym!');
            $howManyTimes = $this->incrementWordHowManyTimes($randomWordInPolish);
            if ($howManyTimes === intval(Cache::get('repeatCounter'))) {
                $this->unsetWordFromArray($randomWordInPolish);
            }
            $this->drawWord();
        } else {
            $this->incrementWordErrorsCount($randomWordInPolish);
            Alert::error('Niestety tym razem się nie udało. Spróbuj ponownie!');
        }
    }

    public function incrementWordErrorsCount(string $wordAddToErrors): void
    {
        $availableWords = Cache::get('words_left_to_learn');
        foreach ($availableWords as $key => $word) {
            if ($word['word_in_polish'] === $wordAddToErrors) {
                $availableWords[$key]['errors']++;
                $allErrors = Cache::get('all_errors');
                $allErrors++;
                Cache::put('all_errors', $allErrors, now()->addHours(24));
                Cache::put('words_left_to_learn', $availableWords, now()->addHours(24));
                Cache::put('random_word', $availableWords[$key], now()->addHours(24));
            }
        }
    }

    public function unsetWordFromArray(string $wordToRemove): void
    {
        $availableWords = Cache::get('words_left_to_learn');
        foreach ($availableWords as $key => $word) {
            if ($word['word_in_polish'] === $wordToRemove) {
                unset($availableWords[$key]);
            }
        }
        Cache::put('words_left_to_learn', $availableWords, now()->addHours(24));
    }

    public function incrementWordHowManyTimes(string $wordToIncrement): mixed
    {
        $availableWords = Cache::get('words_left_to_learn');
        foreach ($availableWords as $key => $word) {
            if ($word['word_in_polish'] === $wordToIncrement) {
                $availableWords[$key]['how_many_times']++;
                Cache::put('words_left_to_learn', $availableWords, now()->addHours(24));
                return $availableWords[$key]['how_many_times'];
            }
        }
        return 'Błąd';
    }
    public function drawWord(): void
    {
        $words = Cache::get('words_left_to_learn');
        $this->drawAndSaveWordToCache($words);
    }

    public function drawAndSaveWordToCache($wordsArray): void
    {
        if (empty($wordsArray)) {
            Alert::success('Brawo! Poprawnie powtórzyłeś wszystkie czasowniki! Zaczynam losować od nowa!');
            $this->addAllWordsToCache();
            $wordsArray = Cache::get('words_left_to_learn');
        }
        $randomKey = array_rand($wordsArray);
        $randomWord = [
            'word_in_polish' => ucfirst($wordsArray[$randomKey]['word_in_polish']),
            'word_in_norwegian' => ucfirst($wordsArray[$randomKey]['word_in_norwegian']),
            'how_many_times' => $wordsArray[$randomKey]['how_many_times'],
            'errors' => $wordsArray[$randomKey]['errors']
        ];
        Cache::put('random_word', $randomWord, now()->addHours(24));
    }
    public function clearCache(): void
    {
        Cache::flush();
    }
}
