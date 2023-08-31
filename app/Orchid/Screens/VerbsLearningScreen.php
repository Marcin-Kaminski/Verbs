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
        $randomVerb = Cache::get('random_verb');
        if (!$randomVerb) {
            $allVerbs = Verb::get()->toArray();
            $randomKey = array_rand($allVerbs);
            $randomVerb = [
                'polish' => ucfirst($allVerbs[$randomKey]['verb_in_polish']),
                'infinitive' => ucfirst($allVerbs[$randomKey]['verb_in_infinitive']),
                'past_simple' => ucfirst($allVerbs[$randomKey]['verb_in_past_simple']),
                'past_participle' => ucfirst($allVerbs[$randomKey]['verb_in_past_participle'])
            ];
            Cache::put('random_verb', $randomVerb, now()->addHour());
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
                        ->method('checkAnswers'),
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
        dd(Cache::get('random_verb'));
        $verbInInfinitiveInput = $request->input('verbInInfinitive');
        $verbInPastSimpleInput = $request->input('verbInPastSimple');
        $verbInPastParticipleInput = $request->input('verbInPastParticiple');
        if (ucfirst($verbInInfinitiveInput) === $this->randomVerbInInfinitive &&
            ucfirst($verbInPastSimpleInput) === $this->randomVerbInPastSimple &&
            ucfirst($verbInPastParticipleInput) === $this->randomVerbInInfinitive) {
            Alert::success('Sukces! prawidłowe tłumaczenie!');
        } else {
            dump($verbInInfinitiveInput = $request->input('verbInInfinitive'));
            dump($verbInPastSimpleInput = $request->input('verbInPastSimple'));
            dump($verbInPastParticipleInput = $request->input('verbInPastParticiple'));
            dump($this->randomVerbInInfinitive);
            dump($this->randomVerbInPastSimple);
            dd($this->randomVerbInPastParticiple);

            Alert::error('Niestety tym razem się nie udało. Spróbuj ponownie!');
        }
    }
    public function drawVerb()
    {

    }

}
