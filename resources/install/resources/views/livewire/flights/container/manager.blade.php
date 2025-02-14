 <div>
     <button class="btn btn-primary btn-sm" wire:click="$toggle('showForm')" data-bs-toggle="modal"
         data-bs-target="#containerFormModal">
         <i class="bi bi-plus-circle"></i> Add Container
     </button>

     <!-- Modal -->
     <div class="modal fade" id="containerFormModal" tabindex="-1" wire:ignore.self>
         <div class="modal-dialog modal-lg">
             <div class="modal-content">
                 <form wire:submit="save">
                     <div class="modal-header">
                         <h5 class="modal-title">{{ $editingContainer ? 'Edit' : 'Add' }} Container</h5>
                         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                     </div>
                     <div class="modal-body">
                         <div class="row g-3">
                             <div class="col-md-3">
                                 <label class="form-label">Container Number</label>
                                 <input type="text" class="form-control form-control-sm"
                                     wire:model="form.container_number"
                                     placeholder="AKE12345XX">
                                 @error('form.container_number')
                                     <span class="text-danger">{{ $message }}</span>
                                 @enderror
                             </div>

                             <div class="col-md-3">
                                 <label class="form-label">Type</label>
                                 <select class="form-select form-select-sm" wire:model="form.type">
                                     <option value="" disabled selected>Select Type</option>
                                     <option value="baggage">Baggage</option>
                                     <option value="cargo">Cargo</option>
                                 </select>
                                 @error('form.type')
                                     <span class="text-danger">{{ $message }}</span>
                                 @enderror
                             </div>

                             <div class="col-md-3">
                                 <label class="form-label">Tare Weight (kg)</label>
                                 <input type="number" class="form-control form-control-sm"
                                     wire:model="form.tare_weight"
                                     placeholder="0">
                                 @error('form.tare_weight')
                                     <span class="text-danger">{{ $message }}</span>
                                 @enderror
                             </div>

                             <div class="col-md-3">
                                 <label class="form-label">Max Weight (kg)</label>
                                 <input type="number" class="form-control form-control-sm"
                                     wire:model="form.max_weight"
                                     placeholder="2000">
                                 @error('form.max_weight')
                                     <span class="text-danger">{{ $message }}</span>
                                 @enderror
                             </div>
                         </div>
                     </div>
                     <div class="modal-footer">
                         <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                             <i class="bi bi-x-circle"></i> Cancel
                         </button>
                         <button type="submit" class="btn btn-sm btn-primary">
                             <i class="bi bi-check-circle"></i> Save Container
                         </button>
                     </div>
                 </form>
             </div>
         </div>
     </div>

     @script
         <script>
             $wire.on('containerSaved', () => {
                 const modal = bootstrap.Modal.getInstance(document.getElementById('containerFormModal'));
                 modal.hide();
             });
         </script>
     @endscript
 </div>
