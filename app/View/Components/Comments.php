<?php

namespace App\View\Components;

use App\Models\Comment;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Comments extends Component
{
    public string $type;
    public int $modelId;
    public bool $isVisible;
    public $comments;

    /**
     * Create a new component instance.
     */
    public function __construct(string $type, int $modelId, bool $isVisible = true)
    {
        $this->type = $type;
        $this->modelId = $modelId;
        $this->isVisible = $isVisible;

        if ($this->isVisible) {
            $this->comments = Comment::with('creator')
                ->where('commentable_type', $this->type)
                ->where('commentable_id', $this->modelId)
                ->latest()
                ->get();
        } else {
            $this->comments = collect();
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.comments');
    }
}
