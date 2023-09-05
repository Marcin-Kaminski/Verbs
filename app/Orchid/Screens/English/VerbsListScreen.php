<?php

namespace App\Orchid\Screens\English;

use App\Models\EnglishVerbs;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class VerbsListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'verbs' => EnglishVerbs::latest()->get()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Lista czasowników';
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
            Layout::tabs([
                'Lista Czasowników' => ([
                    Layout::modal('Edytuj czasownik', [
                        Layout::rows([
                            Input::make('editVerbInPolish')
                                ->autocomplete('off')
                                ->title('PL'),
                            Input::make('editVerbInInfinitive')
                                ->autocomplete('off')
                                ->title('Infinitive'),
                            Input::make('editVerbInPastSimple')
                                ->autocomplete('off')
                                ->title('Past Simple'),
                            Input::make('editVerbInPastParticiple')
                                ->autocomplete('off')
                                ->title('Past Participle'),
                        ]),
                    ])->title('Edytuj wybrany czasownik. Jeśli nic nie wpiszesz, to nic sie nie zmieni.'),
                    Layout::table('verbs', [
                        TD::make('verb_in_polish', 'PL'),
                        TD::make('verb_in_infinitive', 'Infinitive'),
                        TD::make('verb_in_past_simple', 'Past Simple'),
                        TD::make('verb_in_past_participle', 'Past Participle'),
                        TD::make('edytuj')
                            ->align('right')
                            ->render(function (EnglishVerbs $verb) {
                                return  ModalToggle::make('Edytuj czasownik')
                                    ->modal('Edytuj czasownik')
                                    ->type(Color::INFO)
                                    ->method('editVerb', ['id' => $verb->id])
                                    ->icon('full-screen');
                            }),
                        TD::make('usuń')
                            ->align('right')
                            ->render(function (EnglishVerbs $verb) {
                                return Button::make('Usuń czasownik')
                                   ->type(Color::ERROR())
                                   ->method('editVerb')
                                   ->confirm('Jesteś pewny, że chcesz usunąć ten czasownik z bazy?');
                            }),
                    ]),
                ])
            ])
        ];
    }
    public function delete(EnglishVerbs $verb): void
    {
        $verb->delete();
    }
    public function editVerb(Request $request)
    {
        $input = $request->all();
        $verbId = $request->all()['id'];
        $updates = [];
        $flag = false;
        if ($input['editVerbInPolish'] !== null) {
            $updates['verb_in_polish'] = $input['editVerbInPolish'];
            $flag = true;
        }
        if ($input['editVerbInInfinitive'] !== null) {
            $updates['verb_in_infinitive'] = $input['editVerbInInfinitive'];
            $flag = true;
        }
        if ($input['editVerbInPastSimple'] !== null) {
            $updates['verb_in_past_simple'] = $input['editVerbInPastSimple'];
            $flag = true;
        }
        if ($input['editVerbInPastParticiple'] !== null) {
            $updates['verb_in_past_participle'] = $input['editVerbInPastParticiple'];
            $flag = true;
        }
        EnglishVerbs::where('id', $verbId)->update($updates);
        $flag ? Alert::success(sprintf('Pomyślnie zedytowano czasownik'))
            : Alert::error(sprintf('Nie podano żadnych wartości do zedytowania'));
    }
}
