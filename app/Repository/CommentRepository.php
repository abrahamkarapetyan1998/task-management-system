<?php

namespace App\Repository;

use App\Models\Comment;

class CommentRepository
{
    public function create(array $data): Comment
    {
        return Comment::query()->create($data);
    }

    public function update(Comment $comment, array $attributes): Comment
    {
        $comment->update($attributes);

        return $comment->refresh();
    }

    public function delete(Comment $comment): void
    {
        $comment->delete();
    }
}
