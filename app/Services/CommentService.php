<?php

namespace App\Services;

use App\Models\Comment;
use App\Repository\CommentRepository;

class CommentService
{
    private CommentRepository $commentRepository;

    public function __construct(CommentRepository $commentRepository)
    {
        $this->commentRepository = $commentRepository;
    }
    public function create(array $data, int $userId)
    {
        return $this->commentRepository->create([
            ...$data,
            'user_id' => $userId,
        ]);
    }

    public function update(int $commentId, int $userId, array $data): Comment
    {
        $comment = $this->commentRepository->findOwnedByUserOrFail($commentId, $userId);

        return $this->commentRepository->update($comment, $data);
    }

    public function delete(int $commentId, int $userId): void
    {
        $comment = $this->commentRepository->findOwnedByUserOrFail($commentId, $userId);

        $this->commentRepository->delete($comment);
    }
}
