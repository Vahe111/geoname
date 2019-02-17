<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use ZipArchive;
use Curl\Curl;
use Exception;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UpdateGeoname extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geoname:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update geonames table';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Curl $curl, ZipArchive $zip)
    {
        $absolutePathToRuTxtFile = storage_path() . DIRECTORY_SEPARATOR . 'geoname' . DIRECTORY_SEPARATOR . 'RU.txt';
        $ch = curl_init('http://download.geonames.org/export/dump/RU.zip');
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FILETIME, true);
        $result = curl_exec($ch);
        if ($result === false) {
            die (curl_error($ch));
        }
        $modificaionDateRemoteFile = curl_getinfo($ch, CURLINFO_FILETIME);
        if ($modificaionDateRemoteFile != -1) {
            if (file_exists($absolutePathToRuTxtFile)) {
                $modificationDateLocalFile = filemtime($absolutePathToRuTxtFile);
                if($modificaionDateRemoteFile < $modificationDateLocalFile) {
                    return;
                }
            }
        }

        $zipFileName = storage_path() . DIRECTORY_SEPARATOR . 'RU.zip';
        $curl->get('http://download.geonames.org/export/dump/RU.zip');
        if ($curl->error) {
            throw new Exception("Unable to download the file");
        }
        $data = $curl->response;

        $bytesWritten = file_put_contents($zipFileName, $data);
        $storage = storage_path() . DIRECTORY_SEPARATOR . 'geoname';

        $zipOpenResult = $zip->open( $zipFileName );
        if ( TRUE !== $zipOpenResult ) {
            throw new Exception( "Error [" . $zipOpenResult . "] Unable to unzip the archive at " . $zipFileName );
        }
        $extractResult = $zip->extractTo( $storage );
        if ( FALSE === $extractResult ) {
            throw new Exception( "Unable to unzip the file at " . $zipFileName );
        }
        $closeResult = $zip->close();
        if ( FALSE === $closeResult ) {
            throw new Exception( "After unzipping unable to close the file at " . $zipFileName );
        }
        if(is_file($zipFileName))
        {
            unlink($zipFileName);
        }
        Schema::dropIfExists( 'geonames_temp' );
        DB::statement( 'CREATE TABLE geonames_temp LIKE geonames');

        $query = "LOAD DATA LOCAL INFILE '" . $absolutePathToRuTxtFile . "'
                INTO TABLE geonames_temp" . "
                    (geonameid, 
                         name, 
                         asciiname, 
                         alternatenames, 
                         latitude, 
                         longitude, 
                         feature_class, 
                         feature_code, 
                         country_code, 
                         cc2, 
                         admin1_code, 
                         admin2_code, 
                         admin3_code, 
                         admin4_code, 
                         population, 
                         elevation, 
                         dem, 
                         timezone, 
                         modification_date, 
                         @created_at, 
                         @updated_at)
            SET created_at=NOW(),updated_at=null";

        $rowsInserted = DB::getpdo()->exec( $query );
        if ( $rowsInserted === FALSE ) {
            throw new Exception( "Unable to execute the load data infile query.");
        }
        Schema::dropIfExists( 'geonames' );
        Schema::rename( 'geonames_temp', 'geonames' );
    }
}
