<?php

namespace App\Orchid\Screens;

use App\Models\Verb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Orchid\Support\Facades\Alert;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;

class VerbsLearningScreen extends Screen
{
    private $randomVerbInPolish;
    private $randomVerbInInfinitive;
    private $randomVerbInPastSimple;
    private $randomVerbInPastParticiple;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $verbsLeftToLearn = Cache::get('verbs_left_to_learn');
        if (!$verbsLeftToLearn){
            $allVerbs = Verb::get()->toArray();
            $allVerbsArray = [];
            foreach ($allVerbs as $Verb) {
                $allVerbsArray[] = $Verb;
            }
            $verbsLeftToLearn = Cache::put('verbs_left_to_learn', $allVerbsArray, now()->addMinutes(30));
        }
        $randomVerb = Cache::get('random_verb');
        if (!$randomVerb) {
            $this->drawAndSaveVerbToCache($verbsLeftToLearn);
        }
        $this->randomVerbInPolish = $randomVerb['polish'];
        $this->randomVerbInInfinitive = $randomVerb['infinitive'];
        $this->randomVerbInPastSimple = $randomVerb['past_simple'];
        $this->randomVerbInPastParticiple = $randomVerb['past_participle'];

        return [];
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
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::view('verbsLearningScreen'),
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
                    Button::make('Sprawdź tłumaczenie')
                        ->type(Color::INFO)
                        ->method('checkTranslation'),
                    Button::make('Pokaż odpowiedzi')
                        ->type(Color::ERROR)
                        ->method('showTranslation'),
                Button::make('Wylosuj nowy')
                        ->type(Color::DARK)
                        ->method('drawVerb'),
                ])
            ])
              ->title($this->randomVerbInPolish)
        ];
    }
    public function checkTranslation(Request $request)
    {
        $verbInInfinitiveInput = $request->input('verbInInfinitive');
        $verbInPastSimpleInput = $request->input('verbInPastSimple');
        $verbInPastParticipleInput = $request->input('verbInPastParticiple');

        $randomVerbData = Cache::get('random_verb');
        $randomVerbInPolish = $randomVerbData['polish'];
        $randomVerbInInfinitive = $randomVerbData['infinitive'];
        $randomVerbInPastSimple = $randomVerbData['past_simple'];
        $randomVerbInPastParticiple = $randomVerbData['past_participle'];

        if (ucfirst($verbInInfinitiveInput) === $randomVerbInInfinitive &&
            ucfirst($verbInPastSimpleInput) === $randomVerbInPastSimple &&
            ucfirst($verbInPastParticipleInput) === $randomVerbInPastParticiple) {
            Alert::success('Sukces! prawidłowe tłumaczenie! Spróbuj z kolejnym!');
            $this->unsetVerbFromArray($randomVerbInPolish);
            $this->drawVerb();
        } else {
            Alert::error('Niestety tym razem się nie udało. Spróbuj ponownie!');
        }
    }
    public function unsetVerbFromArray(string $verbToRemove)
    {
        $availableVerbs = Cache::get('verbs_left_to_learn');
        foreach ($availableVerbs as $key => $verb) {
            if ($verb['verb_in_polish'] === strtolower($verbToRemove)) {
                unset($availableVerbs[$key]);
            }
        }
        Cache::put('verbs_left_to_learn', $availableVerbs, now()->addMinutes(30));
    }
    public function drawVerb()
    {
        $verbs = Cache::get('verbs_left_to_learn');
        $this->drawAndSaveVerbToCache($verbs);
    }
    public function showTranslation()
    {
        $randomVerb = Cache::get('random_verb');
        $this->randomVerbInPolish = $randomVerb['polish'];
        $this->randomVerbInInfinitive = $randomVerb['infinitive'];
        $this->randomVerbInPastSimple = $randomVerb['past_simple'];
        $this->randomVerbInPastParticiple = $randomVerb['past_participle']; //
    }

    public function drawAndSaveVerbToCache($verbsArray)
    {
        $randomKey = array_rand($verbsArray);
        $randomVerb = [
            'polish' => ucfirst($verbsArray[$randomKey]['verb_in_polish']),
            'infinitive' => ucfirst($verbsArray[$randomKey]['verb_in_infinitive']),
            'past_simple' => ucfirst($verbsArray[$randomKey]['verb_in_past_simple']),
            'past_participle' => ucfirst($verbsArray[$randomKey]['verb_in_past_participle'])
        ];
        Cache::put('random_verb', $randomVerb, now()->addMinutes(30));
    }

}
