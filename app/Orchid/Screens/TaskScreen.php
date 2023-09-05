<?php

namespace App\Orchid\Screens;

use App\Models\Task;
use Orchid\Screen\TD;
use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\ModalToggle;

class TaskScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'tasks' => Task::latest()->get(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'A Simple To-Do list';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add Task')
                ->modal('taskModal')
                ->method('create')
                ->icon('plus'),
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
            Layout::table('tasks', [
                TD::make('name'),
                TD::make('deadline'),
                TD::make('Actions')
                    ->alignRight()
                    ->render(function (Task $task) {
                        return Button::make('Delete Task')
                            ->confirm('Jeśli to usuniesz, to zniknie na zawsze!')
                            ->method('delete', ['task' => $task->id]);
                    })
            ]),
            Layout::modal('taskModal', Layout::rows([
                Input::make('task.name')
                    ->title('Name')
                    ->placeholder('Enter task name')
                    ->help('The name of the task to be created'),
                Input::make('task.deadline')
                    ->title('Deadline')
                    ->placeholder('Enter task deadline')
                    ->help("Time's ticking..."),
            ]))
            ->title('Create Task')
            ->applyButton('Add Task'),
        ];
    }

    public function create(Request $request)
    {
        $request->validate([
            'task.name' => 'required|max:255',
            'task.deadline' => 'required|max:255'
        ]);

        $task = new Task();
        $task->name = $request->input('task.name');
        $task->deadline = $request->input('task.deadline');
        $task->save();
    }

    public function delete(Task $task)
    {
        $task->delete();
    }
}
