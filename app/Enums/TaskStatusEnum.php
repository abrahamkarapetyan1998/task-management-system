<?php

namespace App\Enums;

enum TaskStatusEnum: string
{
    case TODO = 'todo';
    case INPROGRESS = 'in-progress';
    case DONE = 'done';
}
