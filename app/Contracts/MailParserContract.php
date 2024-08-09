<?php

namespace App\Contracts;

interface MailParserContract
{
    public function generateUniqueEmail();

    public function getEmails(string $sidToken);
}
