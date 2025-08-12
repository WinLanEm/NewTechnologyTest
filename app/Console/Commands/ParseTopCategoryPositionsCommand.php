<?php

namespace App\Console\Commands;

use App\Models\CategoryPosition;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ParseTopCategoryPositionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:parse-top-category-positions
                            {--first-run : Whether this is the first run (load last 30 days)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse top category positions';

    /**
     * Execute the console command.
     */


    public function handle()
    {
        $isFirstRun = $this->option('first-run');

        $today = Carbon::today();
        if($isFirstRun) {
            $dateFrom = $today->copy()->subDays(30);
            $dateTo = $today;
        }else{
            $dateFrom = $today->copy();
            $dateTo = $today;
        }

        $data = $this->fetchData($dateFrom,$dateTo);

        $preparedData = $this->prepareDataToSave($data);

        $this->savePreparedData($preparedData);

    }

    private function fetchData(Carbon $dateFrom, Carbon $dateTo)
    {
        $host = config('apptica.host');
        $topPositionsHistory = config('apptica.top_history');
        $applicationId = config('apptica.application_id');
        $countryId = config('apptica.country_id');

        try {
            $response = Http::get("$host/$topPositionsHistory/$applicationId/$countryId", [
                'date_from' => $dateFrom->format('Y-m-d'),
                'date_to' => $dateTo->format('Y-m-d'),
            ]);
            return $response->json();
        }catch (\Exception $exception){
            Log::error('Fetch top category positions failed: ',[
                'exception' => $exception->getMessage(),
                'date_from' => $dateFrom->format('Y-m-d'),
                'date_to' => $dateTo->format('Y-m-d'),
                'application_id' => $applicationId,
                'country_id' => $countryId,
                'host' => $host,
                'top_positions_history' => $topPositionsHistory,
                'stack_trace' => $exception->getTraceAsString(),
            ]);
            throw $exception;
        }
    }

    private function prepareDataToSave(array $data)
    {
        if($data['status_code'] === 200) {
            $result = [];
            foreach ($data['data'] as $categoryId => $subCategories) {
                foreach ($subCategories as $subCategoryId => $positionsByDate) {
                    foreach ($positionsByDate as $date => $position) {
                        if ($position === null) {
                            continue;
                        }

                        if (!isset($result[$categoryId][$date]) || $position < $result[$categoryId][$date]) {
                            $result[$categoryId][$date] = $position;
                        }
                    }
                }
            }
            return $result;
        }else{
            return [];
        }
    }

    private function savePreparedData(array $data)
    {
        $upsertData = [];
        $now = now()->toDateTimeString();
        $allDates = array_merge(...array_values(array_map('array_keys', $data)));


        $existingPositions = CategoryPosition::whereIn('category_id', array_keys($data))
            ->whereBetween('date', [min($allDates), max($allDates)])
            ->select('category_id','date','position')
            ->get()
            ->keyBy(function ($item) {
                return $item->category_id . '_' . $item->date;
            });

        foreach ($data as $categoryId => $dates) {
            foreach ($dates as $date => $newPosition) {
                $key = $categoryId . '_' . $date;

                if ($existingPositions->has($key) &&
                    $existingPositions[$key]->position <= $newPosition) {
                    continue;
                }

                $upsertData[] = [
                    'category_id' => $categoryId,
                    'date' => $date,
                    'position' => $newPosition,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        Log::info('Parsed top category positions', ['New data' => $upsertData]);
        if (!empty($upsertData)) {
            collect($upsertData)->chunk(500)->each(function ($upsertChunk) {
                CategoryPosition::upsert(
                    $upsertChunk->toArray(),
                    ['category_id', 'date'],
                    ['position', 'updated_at']
                );
            });
        }
    }
}
