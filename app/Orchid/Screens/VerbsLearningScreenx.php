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
        if (!$randomVerb ) {
            $this->drawAndSaveVerbToCache($verbsLeftToLearn);
            $randomVerb = (Cache::get('random_verb'));
        }

        $verbForm = (Cache::get('verb_form'));
        $verbForm = !$verbForm ? 'verb_in_polish' : $verbForm;

        return [
            'verbs' => [
                'verb_in_polish' => $this->randomVerbInPolish = $randomVerb['polish'],
                'verb_in_infinitive' => $this->randomVerbInInfinitive = $randomVerb['infinitive'],
                'verb_in_past_simple' => $this->randomVerbInPastSimple = $randomVerb['past_simple'],
                'verb_in_past_participle' => $this->randomVerbInPastParticiple = $randomVerb['past_participle']
            ],
            'verbForm' => $verbForm
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
            Button::make('wyczyść cache')
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
                    Input::make('verbInPolish')
                        ->title('PL')
                        ->autocomplete('off'),
                    Input::make('verbInInfinitive')
                        ->title('Infinitive')
                        ->autocomplete('off'),
                    Input::make('verbInPastSimple')
                        ->title('Past Simple')
                        ->autocomplete('off'),
                    Input::make('verbInPastParticiple')
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
        $verbInInfinitiveInput = $request->input('verbInInfinitive');
        $verbInPastSimpleInput = $request->input('verbInPastSimple');
        $verbInPastParticipleInput = $request->input('verbInPastParticiple');

        $randomVerb = Cache::get('random_verb');
        $randomVerbInPolish = $randomVerb['polish'];
        $randomVerbInInfinitive = $randomVerb['infinitive'];
        $randomVerbInPastSimple = $randomVerb['past_simple'];
        $randomVerbInPastParticiple = $randomVerb['past_participle'];

        if (ucfirst($verbInInfinitiveInput) === $randomVerbInInfinitive &&
            ucfirst($verbInPastSimpleInput) === $randomVerbInPastSimple &&
            ucfirst($verbInPastParticipleInput) === $randomVerbInPastParticiple) {
            Alert::success('Sukces! prawidłowe tłumaczenie! Spróbuj z kolejnym!');
            $howManyTimes = $this->incrementVerbHowManyTimes($randomVerbInPolish);
            if ($howManyTimes === $this->howManyTimesBeforeItIsGone) {
                $this->unsetVerbFromArray($randomVerbInPolish);
            }
            $this->drawVerb();
        } else {
            Alert::error('Niestety tym razem się nie udało. Spróbuj ponownie!');
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

    public function incrementVerbHowManyTimes(string $verbToRemove): int
    {
        $availableVerbs = Cache::get('verbs_left_to_learn');
        foreach ($availableVerbs as $key => $verb) {
            if ($verb['verb_in_polish'] === strtolower($verbToRemove)) {
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
        $randomKey = array_rand($verbsArray);
        $randomVerb = [
            'polish' => ucfirst($verbsArray[$randomKey]['verb_in_polish']),
            'infinitive' => ucfirst($verbsArray[$randomKey]['verb_in_infinitive']),
            'past_simple' => ucfirst($verbsArray[$randomKey]['verb_in_past_simple']),
            'past_participle' => ucfirst($verbsArray[$randomKey]['verb_in_past_participle']),
            'how_many_times' => $verbsArray[$randomKey]['how_many_times']
        ];
        Cache::put('random_verb', $randomVerb, now()->addHours(24));
    }
    public function clearCache(): void
    {
        Cache::flush();
    }
}
