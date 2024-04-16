<?php

namespace App\GraphQL\Queries;

use App\Models\Comment;
use Illuminate\Support\Facades\Cache;

class CommentsQuery
{
    public function __invoke($_, array $args)
    {
        $sortBy = $args['sortBy'] ?? 'created_at';
        $sortOrder = $args['sortOrder'] ?? 'desc';
        $page = $args['page'] ?? 1;


            dd( Comment::with('children')
                ->whereNull('parent_id')
                ->orderBy($sortBy, $sortOrder)
                ->paginate(25));
    }
}
