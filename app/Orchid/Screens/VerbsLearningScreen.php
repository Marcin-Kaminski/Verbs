<?php

namespace App\Orchid\Screens;

use App\Models\Verb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class VerbsLearningScreen extends Screen
{
    private string $randomVerbInPolish;
    private string $randomVerbInInfinitive;
    private string $randomVerbInPastSimple;
    private string $randomVerbInPastParticiple;
    private int $howManyTimesBeforeItIsGone = 1;
    private string $baseVerbForm;
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
            $randomVerb = (Cache::get('random_verb')); //
        }

        return [
            'items' => [
                'Po polsku' => $this->randomVerbInPolish = $randomVerb['polish'],
                'Infinitive' => $this->randomVerbInInfinitive = $randomVerb['infinitive'],
                'Past Simple' => $this->randomVerbInPastSimple = $randomVerb['past_simple'],
                'Past Participle' => $this->randomVerbInPastParticiple = $randomVerb['past_participle']
            ]
        ];
    }
    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
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
            Button::make('wyczyść cache')
                ->type(Color::PRIMARY)
                ->method('clearCache'),
            ModalToggle::make('Pokaż odpowiedzi')
                ->type(Color::ERROR)
                ->modal('Tłumaczenie'),
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
            Layout::view('script'),
            Layout::block([
                Layout::view('script'),
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
            ])
              ->title($this->randomVerbInPolish)
        ];
    }
    public function addAllVerbsToCache(): void
    {
        $allVerbs = (new Verb)->get()->toArray();
        $allVerbsArray = [];
        foreach ($allVerbs as $Verb) {
            $allVerbsArray[] = $Verb;
        }
        Cache::put('verbs_left_to_learn', $allVerbsArray, now()->addMinutes(30));
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
        Cache::put('verbs_left_to_learn', $availableVerbs, now()->addMinutes(30));
    }
    public function incrementVerbHowManyTimes(string $verbToRemove): int
    {
        $availableVerbs = Cache::get('verbs_left_to_learn');
        foreach ($availableVerbs as $key => $verb) {
            if ($verb['verb_in_polish'] === strtolower($verbToRemove)) {
                $availableVerbs[$key]['how_many_times']++;
                Cache::put('verbs_left_to_learn', $availableVerbs, now()->addMinutes(30));
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
    public function showTranslation(): void
    {
        $randomVerb = Cache::get('random_verb');
        $this->randomVerbInPolish = $randomVerb['polish'];
        $this->randomVerbInInfinitive = $randomVerb['infinitive'];
        $this->randomVerbInPastSimple = $randomVerb['past_simple'];
        $this->randomVerbInPastParticiple = $randomVerb['past_participle'];
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
        Cache::put('random_verb', $randomVerb, now()->addMinutes(30));
    }
    public function clearCache()
    {
        Cache::flush();
    }
}
