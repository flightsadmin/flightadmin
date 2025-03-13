<?php

namespace App\Livewire\Airline;

use App\Models\EmailTemplate;
use Illuminate\Support\Str;
use Livewire\Component;

class EmailTemplates extends Component
{
    public $templates;
    public $name;
    public $subject;
    public $body;
    public $templateVariables = [];
    public $editingId = null;
    public $activeTab = 'basic';
    public function mount()
    {
        $this->templates = EmailTemplate::all();
        $this->templateVariables = [['key' => '', 'description' => '']];
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function addVariable()
    {
        $this->templateVariables[] = ['key' => '', 'description' => ''];
        $this->activeTab = 'variables';
    }

    public function removeVariable($index)
    {
        unset($this->templateVariables[$index]);
        $this->templateVariables = array_values($this->templateVariables);
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:20'],
            'subject' => ['required', 'string', 'max:50'],
            'body' => ['required', 'string'],
            'templateVariables.*.key' => ['required', 'string', 'max:20'],
            'templateVariables.*.description' => ['nullable', 'string', 'max:100'],
        ]);

        $variables = collect($this->templateVariables)
            ->filter(fn($var) => !empty($var['key']))
            ->mapWithKeys(fn($var) => [$var['key'] => $var['description']])
            ->toArray();

        try {
            EmailTemplate::updateOrCreate(
                ['slug' => Str::slug($this->name)],
                [
                    'name' => $this->name,
                    'subject' => $this->subject,
                    'body' => $this->body,
                    'variables' => $variables,
                ]
            );

            $this->dispatch('template-saved');
            $this->reset(['name', 'subject', 'body', 'templateVariables', 'editingId']);
            $this->templates = EmailTemplate::all();
            $this->templateVariables = [['key' => '', 'description' => '']];
            $this->activeTab = 'basic';

        } catch (\Exception $e) {
            $this->addError('name', 'Template name must be unique');
        }
    }

    public function edit($id)
    {
        $template = EmailTemplate::findOrFail($id);
        $this->editingId = $id;
        $this->name = $template->name;
        $this->subject = $template->subject;
        $this->body = $template->body;

        $this->templateVariables = collect($template->variables ?? [])
            ->map(fn($desc, $key) => ['key' => $key, 'description' => $desc])
            ->values()
            ->toArray();

        if (empty($this->templateVariables)) {
            $this->templateVariables = [['key' => '', 'description' => '']];
        }

        $this->activeTab = 'basic';
    }

    public function delete($id)
    {
        EmailTemplate::findOrFail($id)->delete();
        $this->templates = EmailTemplate::all();
    }

    public function render()
    {
        return view('livewire.airline.email-templates');
    }
}
