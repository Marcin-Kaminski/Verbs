<?php

namespace App\Orchid\Screens\Norwegian;

use App\Models\NorwegianWords;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class WordsListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'words' => NorwegianWords::latest()->paginate(50)
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Lista słówek';
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
                'Lista Słówek' => ([
                    Layout::modal('Edytuj słowo', [
                        Layout::rows([
                            Input::make('editWordInPolish')
                                ->autocomplete('off')
                                ->title('PL'),
                            Input::make('editWordInNorwegian')
                                ->autocomplete('off')
                                ->title('Infinitive'),
                        ]),
                    ])->title('Edytuj wybrany słowo. Jeśli nic nie wpiszesz, to nic sie nie zmieni.'),
                    Layout::table('words', [
                        TD::make('word_in_polish', 'Polski'),
                        TD::make('word_in_norwegian', 'Norweski'),
                        TD::make('edytuj')
                            ->align('right')
                            ->render(function (NorwegianWords $word) {
                                return  ModalToggle::make('Edytuj słowo')
                                    ->modal('Edytuj słowo')
                                    ->type(Color::INFO)
                                    ->method('editWord', ['id' => $word->id])
                                    ->icon('full-screen');
                            }),
                        TD::make('usuń')
                            ->align('right')
                            ->render(function (NorwegianWords $word) {
                                return Button::make('Usuń słowo')
                                    ->type(Color::ERROR())
                                    ->method('editWord')
                                    ->confirm('Jesteś pewny, że chcesz usunąć ten słowo z bazy?');
                            }),
                    ]),
                ])
            ])
        ];
    }
    public function delete(NorwegianWords $word): void
    {
        $word->delete();
    }
    public function editWord(Request $request)
    {
        $input = $request->all();
        $wordId = $request->all()['id'];
        $updates = [];
        $flag = false;
        if ($input['editWordInPolish'] !== null) {
            $updates['word_in_polish'] = $input['editWordInPolish'];
            $flag = true;
        }
        if ($input['editWordInInNorwegian'] !== null) {
            $updates['word_in_norwegian'] = $input['editWordInInNorwegian'];
            $flag = true;
        }
        NorwegianWords::where('id', $wordId)->update($updates);
        $flag ? Alert::success(sprintf('Pomyślnie zedytowano słowo'))
            : Alert::error(sprintf('Nie podano żadnych wartości do zedytowania'));
    }
}
