<?php

namespace App\Orchid\Screens\Norwegian;

use App\Models\NorwegianWords;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class AddWordScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [];
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
    public $name = 'Dodaj wyraz do bazy danych';

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
                "Dodaj Wyraz" => [
                    Layout::block([
                        Layout::rows([
                            Input::make('word_in_polish')
                                ->title('Wyraz po Polsku')
                                ->type('text')
                                ->autocomplete('off')
                                ->required(),
                            Input::make('word_in_norwegian')
                                ->title('Wyraz po Norwesku')
                                ->autocomplete('off')
                                ->type('text')
                                ->required(),
                        ])
                    ])->commands(
                        Button::make('Zapisz wyraz do bazy')
                                ->type(Color::PRIMARY())
                                ->method('addWordToDatabase')
                    )
                ],
            ])
        ];
    }
    public function addWordToDatabase(Request $request)
    {
        $data = $request->input();
        $isWordAlreadyInBase = false;
        $words = NorwegianWords::get()->toArray();
        foreach ($words as $word) {
            $isWordAlreadyInBase = $word['word_in_norwegian'] === $data['word_in_norwegian'];
        }
        if (!$isWordAlreadyInBase) {
            $norwegianWords = new NorwegianWords();
            $norwegianWords->word_in_polish = ucfirst($data['word_in_polish']);
            $norwegianWords->word_in_norwegian = ucfirst($data['word_in_norwegian']);
            $norwegianWords->save();
            Alert::success(sprintf('Pomyślnie dodano słowo do bazy!'));
        } else {
            Alert::error(sprintf('Jest już takie słowo w bazie!'));
        }
    }
}
