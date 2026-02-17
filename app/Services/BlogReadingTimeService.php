<?php

namespace App\Services;

class BlogReadingTimeService
{
    public function calculateMinutes(string $html): int
    {
        $wordCount = str_word_count(strip_tags($html));

        return max(1, (int) ceil($wordCount / 200));
    }
}

