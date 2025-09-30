<?php

namespace App\Utils;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Movie;

class DatabaseUtils {

    /**
     * insertMassOrOneByOneDB
     *
     * @param array $data
     * @param string $tableName
     * @return void
     */
    public static function insertMassOrOneByOneDB(array $data, string $tableName) {
        $numberOfRecords = count($data);
        Log::info("Bulk insert of $numberOfRecords records into $tableName table.");
        
        try {
            Log::info("[DB] Attempting Bulk insert of $numberOfRecords records into $tableName table.");
            DB::table($tableName)->insertOrIgnore($data);
            Log::info("[DB] Finished Bulk insert of $numberOfRecords records into $tableName table.");
        } catch (\Exception $e) {
            Log::error("[DB] Error while bulk inserting $numberOfRecords records into $tableName table.\n Attempting to insert one by one.\nError: {$e->getMessage()}");
            foreach ($data as $dataItem) {
                try {
                    DB::table($tableName)->insertOrIgnore($dataItem);
                } catch (\Exception $e) {
                    $dataJson = json_encode($dataItem);
                    Log::error("[DB] Error inserting single record into $tableName table.\nRecord: $dataJson\nError: {$e->getMessage()}");
                }   
            }
        }
    }

    /**
     * insertMassOrOneByOne
     *
     * @param array $data
     * @param string $className
     * @param string|null $tableName
     * @return void
     */
    public static function insertMassOrOneByOne(array $data, ?string $className) {
        Log::info("Bulk insert of $className records.");
        $numberOfRecords = count($data);
        $fullClassName = "App\\Models\\{$className}";
        try {
            Log::info("[DB] Attempting bulk insert of $className records.");
            $fullClassName::insert($data);
            Log::info("[DB] Done inserting {$numberOfRecords} records of type $className into database.");
        } catch (\Exception $e) {
            Log::error("[DB] Error mass inserting {$numberOfRecords} records of type {$className} into local database. Attempting entering records one by one. Error:\n {$e->getMessage()}");
            foreach ($data as $dataItem) {
                try {
                    $fullClassName::create($dataItem);
                } catch (\Exception $e) {
                    $dataJson = json_encode($dataItem);
                    Log::error("[DB] Error inserting record of type {$className} into local database.\n Record: {$dataJson} \nError: {$e->getMessage()}");
                }
            }
        }

        Log::info("Done inserting $className records into DB.");
    }

    /**
     * bulkUpsertMoviesOrOneByOne
     * 
     * //TODO: Make this method generalised
     *
     * @param array $dataToInsert
     * @return void
     */
    public static function bulkUpsertMoviesOrOneByOne(array $dataToInsert){
        try {
            Log::info("[DB] Attempting mass upsert of movie records.");
            Movie::whereIn('tmdb_id', array_column($dataToInsert, 'tmdb_id'))
                ->upsert($dataToInsert, ['tmdb_id'], ['budget', 'homepage', 'origin_country', 'revenue', 'tagline']);
            Log::info('[DB] Mass upsert successful');
        } catch (\Exception $e) {
            Log::error("[DB] Mass upsert failed. Attempting one by one. Error: {$e->getMessage()}");
            foreach ($dataToInsert as $dataItem) {
                try {
                    Movie::where('tmdb_id', $dataItem['tmdb_id'])
                        ->update($dataItem);
                } catch (\Exception $e) {
                    Log::error("[DB] Failed updating move record with tmdb_id {$dataItem['tmdb_id']}\nError: {$e->getMessage()}");
                }
            }
        }
    }
}
