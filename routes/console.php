<?php

use Illuminate\Support\Facades\Schedule;


Schedule::command('app:parse-top-category-positions')
    ->everyThirtyMinutes()
    ->runInBackground();
