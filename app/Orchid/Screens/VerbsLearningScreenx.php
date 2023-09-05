<?php

namespace App\Orchid\Screens;

use App\Models\Verb;
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

class VerbsLearningScreenx extends Screen
{
    private string $randomVerbInPolish;
    private string $randomVerbInInfinitive;
    private string $randomVerbInPastSimple;
    private string $randomVerbInPastParticiple;
    private int $howManyTimesBeforeItIsGone = 1;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $verbsLeftToLearn = Cache::get('verbs_left_to_learn');
        if (!$verbsLeftToLearn) {
            $this->addAllVerbsToCache();
            $verbsLeftToLearn = Cache::get('verbs_left_to_learn');
        }
        $randomVerb = (Cache::get('random_verb'));
        if (!$randomVerb) {
            $this->drawAndSaveVerbToCache($verbsLeftToLearn);
            $randomVerb = (Cache::get('random_verb'));
        }
//        dd($randomVerb);
        $verbForm = (Cache::get('verb_form'));
        $verbForm = !$verbForm ? 'verb_in_polish' : $verbForm;
        $allErrors = (Cache::get('all_errors'));
        $allErrors = !$allErrors ? 0 : $allErrors;

        return [
            'verbs' => [
                'verb_in_polish' => $this->randomVerbInPolish = $randomVerb['verb_in_polish'],
                'verb_in_infinitive' => $this->randomVerbInInfinitive = $randomVerb['verb_in_infinitive'],
                'verb_in_past_simple' => $this->randomVerbInPastSimple = $randomVerb['verb_in_past_simple'],
                'verb_in_past_participle' => $this->randomVerbInPastParticiple = $randomVerb['verb_in_past_participle'],
                'errors' => $randomVerb['errors']
            ],
            'verbForm' => $verbForm,
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
                ->method('drawVerb'),
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
                Layout::view('translation')
            ]),
            Layout::modal('Ustawienia', Layout::rows([
                Select::make('learningMode')
                    ->options([
                        'verb_in_polish' => 'Polski',
                        'verb_in_infinitive' => 'Infinitive',
                        'verb_in_past_simple' => 'Past Simple',
                        'verb_in_past_participle' => 'Past Participle',
                    ])
                    ->title('Jaki typ nauki wybierasz?')
                    ->required()
                    ->help('Wybierz jaka będzie baza twojego tłumaczenia!'),
                Select::make('howManyTimesBeforeItIsGone')
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
            Layout::view('script'),
            Layout::block([
                Layout::rows([
                    Input::make('verb_in_polish')
                        ->title('PL')
                        ->autocomplete('off'),
                    Input::make('verb_in_infinitive')
                        ->title('Infinitive')
                        ->autocomplete('off'),
                    Input::make('verb_in_past_simple')
                        ->title('Past Simple')
                        ->autocomplete('off'),
                    Input::make('verb_in_past_participle')
                        ->title('Past Participle')
                        ->autocomplete('off'),
                ])
            ])];
    }

    public function changeSettings(Request $request): void
    {
        $request->validate([
            'learningMode' => 'required',
            'howManyTimesBeforeItIsGone' => 'required|integer',
        ], [
            'howManyTimesBeforeItIsGone.integer' => 'To pole musi być liczbą całkowitą'
        ]);
        $verbForm = $request->input('learningMode');
        Cache::put('verb_form', $verbForm, now()->addHours(24));
        $howManyTimesBeforeItIsGone = $request->input('howManyTimesBeforeItIsGone'); /** @todo dokończ*/
        Cache::put('howManyTimesBeforeItIsGone', $howManyTimesBeforeItIsGone, now()->addHours(24));
    }

    public function addAllVerbsToCache(): void
    {
        $allVerbs = (new Verb)->get()->toArray();
        $allVerbsArray = [];
        foreach ($allVerbs as $Verb) {
            $allVerbsArray[] = $Verb;
        }
        Cache::put('verbs_left_to_learn', $allVerbsArray, now()->addHours(24));
    }

    public function checkTranslation(Request $request): void
    {
        $verbInPolishInput = $request->input('verb_in_polish');
        $verbInInfinitiveInput = $request->input('verb_in_infinitive');
        $verbInPastSimpleInput = $request->input('verb_in_past_simple');
        $verbInPastParticipleInput = $request->input('verb_in_past_participle');

        $randomVerb = Cache::get('random_verb');

        $randomVerbInPolish = $randomVerb['verb_in_polish'];
        $randomVerbInInfinitive = $randomVerb['verb_in_infinitive'];
        $randomVerbInPastSimple = $randomVerb['verb_in_past_simple'];
        $randomVerbInPastParticiple = $randomVerb['verb_in_past_participle'];

        $verbForm = Cache::get('verb_form');
        switch ($verbForm) {
            case 'verb_in_infinitive':
                $input1 = $verbInPolishInput;
                $comparator1 = $randomVerbInPolish;
                $input2 = $verbInPastSimpleInput;
                $comparator2 = $randomVerbInPastSimple;
                $input3 = $verbInPastParticipleInput;
                $comparator3 = $randomVerbInPastParticiple;
                break;
            case 'verb_in_past_simple':
                $input1 = $verbInInfinitiveInput;
                $comparator1 = $randomVerbInInfinitive;
                $input2 = $verbInPolishInput;
                $comparator2 = $randomVerbInPolish;
                $input3 = $verbInPastParticipleInput;
                $comparator3 = $randomVerbInPastParticiple;
                break;
            case 'verb_in_past_participle':
                $input1 = $verbInInfinitiveInput;
                $comparator1 = $randomVerbInInfinitive;
                $input2 = $verbInPolishInput;
                $comparator2 = $randomVerbInPolish;
                $input3 = $verbInPastSimpleInput;
                $comparator3 = $randomVerbInPastSimple;
                break;
            default:
                $input1 = $verbInInfinitiveInput;
                $comparator1 = $randomVerbInInfinitive;
                $input2 = $verbInPastSimpleInput;
                $comparator2 = $randomVerbInPastSimple;
                $input3 = $verbInPastParticipleInput;
                $comparator3 = $randomVerbInPastParticiple;
        }
        if (ucfirst($input1) === $comparator1 &&
            ucfirst($input2) === $comparator2 &&
            ucfirst($input3) === $comparator3) {
            Alert::success('Sukces! prawidłowe tłumaczenie! Spróbuj z kolejnym!');
            $howManyTimes = $this->incrementVerbHowManyTimes($randomVerbInPolish);
            if ($howManyTimes === $this->howManyTimesBeforeItIsGone) {
                $this->unsetVerbFromArray($randomVerbInPolish);
            }
            $this->drawVerb();
        } else {
            $this->incrementVerbErrorsCount($randomVerbInPolish);
            Alert::error('Niestety tym razem się nie udało. Spróbuj ponownie!');
        }
    }

    public function incrementVerbErrorsCount(string $verbToAddErrorTo)
    {
        $availableVerbs = Cache::get('verbs_left_to_learn');
        foreach ($availableVerbs as $key => $verb) {
            if ($verb['verb_in_polish'] === strtolower($verbToAddErrorTo)) {
                $availableVerbs[$key]['errors']++;
                $allErrors = Cache::get('all_errors');
                $allErrors++;
                Cache::put('all_errors', $allErrors, now()->addHours(24));
                Cache::put('verbs_left_to_learn', $availableVerbs, now()->addHours(24));
                Cache::put('random_verb', $availableVerbs[$key], now()->addHours(24));
            }
        }
    }

    public function unsetVerbFromArray(string $verbToRemove): void
    {
        $availableVerbs = Cache::get('verbs_left_to_learn');
        foreach ($availableVerbs as $key => $verb) {
            if ($verb['verb_in_polish'] === strtolower($verbToRemove)) {
                unset($availableVerbs[$key]);
            }
        }
        Cache::put('verbs_left_to_learn', $availableVerbs, now()->addHours(24));
    }

    public function incrementVerbHowManyTimes(string $verbToIncrement): int
    {
        $availableVerbs = Cache::get('verbs_left_to_learn');
        foreach ($availableVerbs as $key => $verb) {
            if ($verb['verb_in_polish'] === strtolower($verbToIncrement)) {
                $availableVerbs[$key]['how_many_times']++;
                Cache::put('verbs_left_to_learn', $availableVerbs, now()->addHours(24));
                return $availableVerbs[$key]['how_many_times'];
            }
        }
        return 'Błąd';
    }
    public function drawVerb(): void
    {
        $verbs = Cache::get('verbs_left_to_learn');
        $this->drawAndSaveVerbToCache($verbs);
    }

    public function drawAndSaveVerbToCache($verbsArray): void
    {
        if (empty($verbsArray)) {
            Alert::success('Brawo! Poprawnie powtórzyłeś wszystkie czasowniki! Zaczynam losować od nowa!');
            $this->addAllVerbsToCache();
            $verbsArray = Cache::get('verbs_left_to_learn');
        }
        $randomKey = array_rand($verbsArray);
        $randomVerb = [
            'verb_in_polish' => ucfirst($verbsArray[$randomKey]['verb_in_polish']),
            'verb_in_infinitive' => ucfirst($verbsArray[$randomKey]['verb_in_infinitive']),
            'verb_in_past_simple' => ucfirst($verbsArray[$randomKey]['verb_in_past_simple']),
            'verb_in_past_participle' => ucfirst($verbsArray[$randomKey]['verb_in_past_participle']),
            'how_many_times' => $verbsArray[$randomKey]['how_many_times'],
            'errors' => $verbsArray[$randomKey]['errors']
        ];
        Cache::put('random_verb', $randomVerb, now()->addHours(24));
    }
    public function clearCache(): void
    {
        Cache::flush();
    }
}
