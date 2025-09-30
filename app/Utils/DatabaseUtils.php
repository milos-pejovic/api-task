<?php

namespace App\Utils;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
            DB::table('genre_movie')->insertOrIgnore($data);
        } catch (\Exception $e) {
            Log::error("[DB] Error while bulk inserting $numberOfRecords into $tableName table.\n Attempting to insert one by one.\nError: {$e->getMessage()}");
            foreach ($data as $dataItem) {
                try {
                    DB::create($dataItem);
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
}
