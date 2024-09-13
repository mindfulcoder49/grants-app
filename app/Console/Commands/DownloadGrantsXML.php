<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class DownloadGrantsXML extends Command
{
    protected $signature = 'grants:download-xml';
    protected $description = 'Download and extract the latest Grants XML zip file from Grants.gov';

    public function handle()
    {
        // Try the latest 5 days (you can adjust this range)
        for ($i = 0; $i < 5; $i++) {
            $date = now()->subDays($i)->format('Ymd');  // Get the date in the required format YYYYMMDD
            $url = "https://prod-grants-gov-chatbot.s3.amazonaws.com/extracts/GrantsDBExtract{$date}v2.zip";
            $zipFile = storage_path("app/grants/GrantsDBExtract{$date}v2.zip");

            $this->info("Attempting to download Grants XML file for {$date}...");
            
            // Download the ZIP file
            if ($this->downloadFile($url, $zipFile)) {
                $this->info("Downloaded Grants XML file for {$date}");

                // Extract the ZIP file
                if ($this->extractZipFile($zipFile, storage_path('app/grants'))) {
                    $this->info("Extracted Grants XML file.");
                    unlink($zipFile);
                    break;  // Stop after successfully downloading and extracting
                } else {
                    $this->error("Failed to extract the ZIP file.");
                }
            } else {
                $this->info("File not available for {$date}, trying the previous day...");
            }
        }

        return 0;
    }


    /**
     * Download the zip file from the URL.
     * 
     * @param string $url
     * @param string $destination
     * @return bool
     */
    private function downloadFile(string $url, string $destination): bool
    {
        try {
            $fileContents = file_get_contents($url);
            if ($fileContents === false) {
                return false;
            }
            file_put_contents($destination, $fileContents);
            return true;
        } catch (\Exception $e) {
            $this->error("Error downloading the file: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Extract the zip file to the given destination.
     * 
     * @param string $zipFile
     * @param string $destination
     * @return bool
     */
    private function extractZipFile(string $zipFile, string $destination): bool
    {
        $zip = new ZipArchive;
        if ($zip->open($zipFile) === TRUE) {
            $zip->extractTo($destination);
            $zip->close();
            return true;
        }
        return false;
    }
}
