@if($isVisible)
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Notes & Comments</h3>
    </div>
    <div class="card-body">

        {{-- Add Comment Form --}}
        <form action="{{ route('admin.comments.store') }}" method="POST" class="mb-4 add-comment-form">
            @csrf
            <input type="hidden" name="commentable_type" value="{{ $type }}">
            <input type="hidden" name="commentable_id" value="{{ $modelId }}">

            <div class="d-flex align-items-start gap-3">
                <span class="avatar bg-primary-lt fw-bold flex-shrink-0">
                    {{ mb_strtoupper(mb_substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </span>
                <div class="flex-grow-1">
                    <textarea name="body" class="form-control" rows="2" placeholder="Add a new note or comment..." required style="resize: none;"></textarea>
                    <div class="mt-2 d-flex justify-content-end">
                        <button type="submit" class="btn btn-sm btn-primary btn-post-comment">
                            <i class="ti ti-send me-1"></i>Post Note
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <hr class="my-4" />

        {{-- Comments List --}}
        <div class="d-flex flex-column gap-4">
            @foreach($comments as $comment)
                <div class="d-flex align-items-start gap-3">
                    <span class="avatar bg-secondary-lt text-secondary fw-bold flex-shrink-0">
                        {{ mb_strtoupper(mb_substr($comment->creator->name ?? '?', 0, 1)) }}
                    </span>
                    <div class="flex-grow-1 border rounded p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="fw-semibold small">{{ $comment->creator->name ?? 'Unknown User' }}</span>
                                <span class="text-secondary small ms-2">
                                    {{ $comment->created_at->format('d M Y, h:i A') }}
                                    · {{ $comment->created_at->diffForHumans() }}
                                </span>
                            </div>

                            {{-- Delete Button --}}
                            <form action="{{ route('admin.comments.destroy', $comment->id) }}" method="POST" class="delete-comment-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-icon btn-sm btn-ghost-danger btn-delete-comment" title="Delete note">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </form>
                        </div>
                        <div class="small lh-lg">
                            {!! nl2br(e($comment->body)) !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {

        // Handle add comment via AJAX to prevent browser history back-button issues
        $('.add-comment-form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = form.find('.btn-post-comment');
            var originalText = btn.html();

            btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>').prop('disabled', true);

            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        // Reload the page smoothly by replacing the current state
                        window.location.replace(window.location.href);
                    }
                },
                error: function(xhr) {
                    btn.html(originalText).prop('disabled', false);
                    Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'error', title: 'Failed to post comment' });
                }
            });
        });

        // Handle delete comment via AJAX
        $('.btn-delete-comment').on('click', function(e) {
            e.preventDefault();
            var form = $(this).closest('form');

            Swal.fire({
                title: 'Delete Note?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-light ms-2'
                },
                buttonsStyling: false,
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: form.attr('action'),
                        type: 'POST',
                        data: form.serialize(),
                        success: function(response) {
                            if (response.success) {
                                window.location.replace(window.location.href);
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({ toast: true, position: 'top', showConfirmButton: false, timer: 1500, icon: 'error', title: 'Failed to delete comment' });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
@endif