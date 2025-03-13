<div>
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0 d-flex align-items-center">
                    <i class="bi bi-envelope-paper me-2 text-primary"></i>
                    Email Templates
                </h4>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                    data-bs-target="#templateModal" wire:click="$set('editingId', null)">
                    <i class="bi bi-plus-lg me-2"></i>Add New Template
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="container-fluid py-4">
                <div class="row g-4">
                    @forelse ($templates as $template)
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100 border shadow-sm">
                                <div class="card-header bg-light border-bottom-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0 text-primary">
                                            {{ $template->name }}
                                        </h5>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary rounded-circle"
                                                data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                <li>
                                                    <button class="dropdown-item" wire:click="edit({{ $template->id }})"
                                                        data-bs-toggle="modal" data-bs-target="#templateModal">
                                                        <i class="bi bi-pencil-square me-2 text-primary"></i> Edit Template
                                                    </button>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <button class="dropdown-item"
                                                        onclick="previewTemplate('{{ $template->name }}', '{{ $template->subject }}', `{{ str_replace('`', '\`', $template->body) }}`)">
                                                        <i class="bi bi-eye me-2 text-info"></i> Preview
                                                    </button>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <button class="dropdown-item text-danger"
                                                        wire:click="delete({{ $template->id }})"
                                                        wire:confirm="Are you sure you want to delete this template?">
                                                        <i class="bi bi-trash me-2"></i> Delete
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div><i class="bi bi-tag"></i> {{ $template->slug }}</div>
                                    <h6><i class="bi bi-chat-square-text"></i> {{ Str::limit($template->subject, 80) }}</h6>

                                    <div class="my-2">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <small class="text-muted fw-bold">
                                                { Variables: }
                                            </small>
                                            <span class="badge bg-secondary">{{ count($template->variables) }}</span>
                                        </div>
                                        <div class="d-flex flex-wrap gap-1 mb-2">
                                            @foreach($template->variables as $key => $description)
                                                <span class="badge bg-light text-dark border mb-1" title="{{ $description }}"
                                                    data-bs-toggle="tooltip">
                                                    {{{ $key }}}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="card border shadow-sm p-5">
                                <div class="text-center">
                                    <i class="bi bi-envelope-x display-4 text-muted mb-3"></i>
                                    <h5>No Email Templates Found</h5>
                                    <p class="text-muted">Create your first email template to get started</p>
                                    <button type="button" class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal"
                                        data-bs-target="#templateModal" wire:click="$set('editingId', null)">
                                        <i class="bi bi-plus-lg me-2"></i>Add New Template
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Template Modal -->
    <div class="modal fade" id="templateModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form wire:submit="save">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title d-flex align-items-center">
                            <i class="bi bi-{{ $editingId ? 'pencil-square' : 'plus-circle' }} me-2 text-primary"></i>
                            {{ $editingId ? 'Edit' : 'Create' }} Email Template
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="nav nav-tabs mb-4" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab === 'basic' ? 'active' : '' }}"
                                    wire:click="setActiveTab('basic')" role="tab" href="#basic">
                                    <i class="bi bi-card-text me-2"></i>Basic Info
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab === 'variables' ? 'active' : '' }}"
                                    wire:click="setActiveTab('variables')" role="tab" href="#variables">
                                    <i class="bi bi-braces me-2"></i>Variables
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab === 'content' ? 'active' : '' }}"
                                    wire:click="setActiveTab('content')" role="tab" href="#content">
                                    <i class="bi bi-file-text me-2"></i>Content
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane fade {{ $activeTab === 'basic' ? 'show active' : '' }}" id="basic">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium">Template Name <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="bi bi-tag"></i></span>
                                            <input type="text" wire:model="name" class="form-control"
                                                placeholder="Enter template name">
                                        </div>
                                        @error('name')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-medium">Email Subject <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="bi bi-chat-square-text"></i></span>
                                            <input type="text" wire:model="subject" class="form-control"
                                                placeholder="Enter email subject">
                                        </div>
                                        @error('subject')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade {{ $activeTab === 'variables' ? 'show active' : '' }}"
                                id="variables">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Template Variables</h6>
                                    <button type="button" class="btn btn-primary btn-sm" wire:click="addVariable">
                                        <i class="bi bi-plus-lg me-2"></i>Add Variable
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead>
                                            <tr>
                                                <th style="width: 40%">Variable Name</th>
                                                <th style="width: 50%">Description</th>
                                                <th style="width: 10%" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($templateVariables as $index => $variable)
                                                <tr>
                                                    <td>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text bg-light">{</span>
                                                            <input type="text"
                                                                wire:model="templateVariables.{{ $index }}.key"
                                                                class="form-control" placeholder="name">
                                                            <span class="input-group-text bg-light">}</span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="text"
                                                            wire:model="templateVariables.{{ $index }}.description"
                                                            class="form-control form-control-sm"
                                                            placeholder="What this variable represents">
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                            wire:click="removeVariable({{ $index }})">
                                                            <i class="bi-trash3"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="100%" class="text-center py-3 text-muted">
                                                        No variables defined yet. Click "Add Variable" to create one.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade {{ $activeTab === 'content' ? 'show active' : '' }}" id="content">
                                <div class="mb-3">
                                    <label
                                        class="form-label fw-medium d-flex justify-content-between align-items-center">
                                        <span>Available Variables</span>
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            wire:click="setActiveTab('variables')">
                                            <i class="bi bi-pencil-square me-1"></i> Edit Variables
                                        </button>
                                    </label>
                                    <div class="card border shadow-sm p-3">
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($templateVariables as $variable)
                                                @if($variable['key'])
                                                    <span class="badge bg-light text-dark border"
                                                        onclick="insertVariable('{{ $variable['key'] }}')"
                                                        title="{{ $variable['description'] }}" data-bs-toggle="tooltip"
                                                        style="cursor: pointer;">
                                                        <i class="bi bi-braces me-1"></i>
                                                        {{{ $variable['key'] }}}
                                                    </span>
                                                @endif
                                            @endforeach

                                            @if(count(array_filter($templateVariables, fn($v) => !empty($v['key']))) === 0)
                                                <div class="text-muted small">No variables defined.
                                                    <a href="#" wire:click.prevent="setActiveTab('variables')">
                                                        Add some variables</a> first.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-medium">Email Body <span class="text-danger">*</span>
                                        <i class="bi bi-lightbulb me-1"></i>
                                        <strong>Tip:</strong> Click on a variable above to insert it at the cursor
                                        position.
                                    </label>
                                    <textarea wire:model="body" class="form-control" rows="12" id="bodyTextarea"
                                        placeholder="Enter your email content here..."></textarea>
                                    @error('body')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-2"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Save Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title d-flex align-items-center">
                        <i class="bi bi-eye me-2 text-primary"></i>
                        Template Preview
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-medium">Template Name:</label>
                            <span class="badge bg-primary" id="preview-name"></span>
                        </div>
                        <div class="p-1 border rounded bg-light">
                            <span id="preview-subject"></span>
                        </div>
                    </div>

                    <label class="form-label fw-medium">Email Body:</label>
                    <div class="py-0 px-2 border rounded bg-white overflow-auto"
                        style="min-height: 200px; max-height: 300px; white-space: pre-line;">
                        <div id="preview-body"></div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-2"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize tooltips
        document.addEventListener('livewire:initialized', () => {
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        });

        // Function to preview template
        function previewTemplate(name, subject, body) {
            document.getElementById('preview-name').textContent = name;
            document.getElementById('preview-subject').textContent = subject;
            document.getElementById('preview-body').innerHTML = body;

            new bootstrap.Modal(document.getElementById('previewModal')).show();
        }

        // Function to insert variable at cursor position
        function insertVariable(variable) {
            const textarea = document.getElementById('bodyTextarea');
            if (!textarea) return;

            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            const before = text.substring(0, start);
            const after = text.substring(end);

            const newText = before + '{' + variable + '}' + after;
            textarea.value = newText;

            // Update Livewire model
            $wire.set('body', newText);

            // Set cursor position after the inserted variable
            const newCursorPos = start + variable.length + 2;
            textarea.focus();
            textarea.setSelectionRange(newCursorPos, newCursorPos);
        }
    </script>

    @script
    <script>
        $wire.on('template-saved', () => {
            bootstrap.Modal.getInstance(document.getElementById('templateModal')).hide();
        });
    </script>
    @endscript
</div>