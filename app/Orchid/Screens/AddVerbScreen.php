<?php

namespace App\Orchid\Screens;

use App\Models\Verb;
use Orchid\Screen\TD;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Alert;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Fields\TextArea;
use Orchid\Support\Facades\Layout;

class AddVerbScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'verbs' => Verb::latest()->get()
        ];
    }
    /**
     * Display header description.
     *
     * @var string
     */
    public $description = '';

       /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Dodaj czasownik do bazy danych';

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
            Layout::tabs ([
                "Dodaj Czasownik" => [
                    Layout::block([
                        Layout::rows([
                            Input::make('verbInPolish')
                                ->title('PL')
                                ->type('text')
                                ->autocomplete('off')
                                ->required(),
                            Input::make('verbInInfinitive')
                                ->title('Infinitive')
                                ->autocomplete('off')
                                ->type('text')
                                ->required(),
                            Input::make('verbInPastSimple')
                                ->title('Past simple')
                                ->autocomplete('off')
                                ->type('text')
                                ->required(),
                            Input::make('verbInPastParticiple')
                                ->title('Past Participle')
                                ->autocomplete('off')
                                ->type('text')
                                ->required(),
                            TextArea::make('additionalDescription')
                                ->title('Dodatkowy opis')
                                ->autocomplete('off')
                                ->type('text')
                                ->rows(5),
                        ])
                    ])  ->title('Dodaj nowy czasownik do bazy danych')
                            ->description('PL - wiadomo (spać) <br> Infinitive - bezokolicznik (to sleep) <br> Past Simple - czas przeszły  (slept) <br> Past Participle - sam nie wiem (slept)')
                            ->commands(
                            Button::make('Zapisz czasownik do bazy')
                            ->type(Color::PRIMARY())
                            ->method('addVerbsToDb')
                        )
                ],
            ])
        ];
    }
    public function addVerbsToDb(Request $request)
    {
        $data = $request->input();
        $isVerbAlreadyInBase = false;
        $verbInPolish = $data['verbInPolish'];
        $verbs = Verb::get()->toArray();
        foreach($verbs as $verb){
            $isVerbAlreadyInBase = $verb['verb_in_polish'] === $data['verbInPolish'] ? true : $isVerbAlreadyInBase ;
            $isVerbAlreadyInBase = $verb['verb_in_infinitive'] === $data['verbInInfinitive'] ? true : $isVerbAlreadyInBase ;
            $isVerbAlreadyInBase = $verb['verb_in_past_simple'] === $data['verbInPastSimple'] ? true : $isVerbAlreadyInBase ;
            $isVerbAlreadyInBase = $verb['verb_in_past_participle'] === $data['verbInPastParticiple'] ? true : $isVerbAlreadyInBase ;
        }
        if (!$isVerbAlreadyInBase) {
            Verb::insert([
                'verb_in_polish' => $data['verbInPolish'],
                'verb_in_infinitive' => $data['verbInInfinitive'],
                'verb_in_past_simple' => $data['verbInPastSimple'],
                'verb_in_past_participle' => $data['verbInPastParticiple'],
                'additional_description' => $data['additionalDescription'],
            ]);
            Alert::success(sprintf('Pomyślnie dodano czasownik do bazy!'));
        } else {
            Alert::error(sprintf('Jest już taki czasownik w bazie!'));
        }
    }
}
