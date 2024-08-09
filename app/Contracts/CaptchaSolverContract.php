<?php

namespace App\Contracts;

interface CaptchaSolverContract
{
    public function getResult($taskId);
}
