{{-- DataTables Core --}}
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>

{{-- Toastr --}}
<script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>

{{-- Flatpickr --}}
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    $.extend(true, $.fn.dataTable.defaults, {
        language: {
            emptyTable: `<div class="empty text-center mx-auto" style="padding: 2rem 0;">
              <div class="empty-icon mb-3">
                <i class="ti ti-folder-off text-muted" style="font-size: 3rem;"></i>
              </div>
              <p class="empty-title mb-1 h3">No data available</p>
              <p class="empty-subtitle text-muted">There are currently no records to display in this table.</p>
            </div>`,
            zeroRecords: `<div class="empty text-center mx-auto" style="padding: 2rem 0;">
              <div class="empty-icon mb-3">
                <i class="ti ti-file-search text-muted" style="font-size: 3rem;"></i>
              </div>
              <p class="empty-title mb-1 h3">No matching records found</p>
              <p class="empty-subtitle text-muted">Try adjusting your search query or filters.</p>
            </div>`
        }
    });
</script>