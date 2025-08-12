<?php

use Illuminate\Support\Facades\Schedule;


Schedule::command('app:parse-top-category-positions')
    ->everyMinute()
    ->runInBackground();
