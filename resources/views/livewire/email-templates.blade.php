<div>
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Email Templates</h4>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#templateModal"
                wire:click="$set('editingId', null)">
                <i class="bi bi-plus-lg me-2"></i>Add New Template
            </button>
        </div>

        <div class="row g-4">
            @foreach ($templates as $template)
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100 border shadow">
                        <div class="card-header bg-light border-bottom-0 py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-envelope me-2 text-primary"></i>
                                    {{ $template->name }}
                                </h5>
                                <div class="dropdown">
                                    <button class="btn btn-link" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <button class="dropdown-item" wire:click="edit({{ $template->id }})"
                                                data-bs-toggle="modal" data-bs-target="#templateModal">
                                                <i class="bi bi-pencil-square me-2"></i> Edit
                                            </button>
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
                            <h6 class="text-muted mb-3">{{ $template->subject }}</h6>
                            <div class="mb-3">
                                <small class="text-muted">Variables:</small>
                                <div class="mt-2">
                                    @foreach($template->variables as $key => $desc)
                                        <code> { {{ $key }} } </code>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Template Modal -->
    <div class="modal fade" id="templateModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form wire:submit="save">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingId ? 'Edit' : 'Create' }} Email Template</h5>
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
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Template Name</label>
                                        <input type="text" wire:model="name" class="form-control form-control-sm">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email Subject</label>
                                        <input type="text" wire:model="subject" class="form-control form-control-sm">
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade {{ $activeTab === 'variables' ? 'show active' : '' }}"
                                id="variables">
                                <div class="d-flex justify-content-end mb-3">
                                    <button type="button" class="btn btn-primary btn-sm" wire:click="addVariable">
                                        <i class="bi bi-plus-lg me-2"></i>Add Variable
                                    </button>
                                </div>
                                <div class="variables-container">
                                    @foreach($templateVariables as $index => $variable)
                                        <div class="row g-2 mb-3 align-items-center">
                                            <div class="col-md-5">
                                                <input type="text" wire:model="templateVariables.{{ $index }}.key"
                                                    class="form-control form-control-sm" placeholder="Variable name">
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" wire:model="templateVariables.{{ $index }}.description"
                                                    class="form-control form-control-sm" placeholder="Description">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-outline-danger btn-sm"
                                                    wire:click="removeVariable({{ $index }})">
                                                    <i class="bi-trash3"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="tab-pane fade {{ $activeTab === 'content' ? 'show active' : '' }}" id="content">
                                <div class="mb-3">
                                    <label class="form-label d-block">Available Variables</label>
                                    <div class="btn-group flex-wrap">
                                        @foreach($templateVariables as $variable)
                                            @if($variable['key'])
                                                <span class="badge bg-primary me-2 mb-2">
                                                    { {{ $variable['key'] }} }
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email Body</label>
                                    <textarea wire:model="body" class="form-control" rows="10"
                                        id="bodyTextarea"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
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

    @script
    <script>
        $wire.on('template-saved', () => {
            bootstrap.Modal.getInstance(document.getElementById('templateModal')).hide();
        });
    </script>
    @endscript
</div>