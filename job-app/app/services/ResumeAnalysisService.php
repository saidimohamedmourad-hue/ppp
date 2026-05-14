<?php 
namespace App\Services;
use Illuminate\Support\Facades\Log;
use App\Models\Resume;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Storage;

use Spatie\PdfToText\Pdf;

class ResumeAnalysisService
{
    public function extractResumeInformation(string $fileUri){
        try{
        // Exract raw text from the resume pdf file (read pdf file , and get the text)
        $rawText = $this->extractRawText($fileUri);

        log::debug('sucssfully extracted raw text from the resume pdf file'.strlen($rawText).'characters');
        // Use OpenAI API to organize the text into a structured format
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'system',
                 'content' => 'You are a percice resume parser,Extract information exactly as it appears in the resume, whithouth adding any interpretation or additonal information.The output should be in JSON format.'],
                ['role' => 'user',
                 'content' => "parse the following resume and extarct the information as a JSON object whith the exact keys:'summary', 'skills', 'experience', 'education'. The resume text is: {$rawText}. Ruturn an empty string for key that if not found."],
            ],
            'response_format' => [  
                'type' => 'json_object',
            ],
            'temperature' => 0,
        ]); 
        $result = $response->choices[0]->message->content;
        log::debug('OpenAI response: '.$result);
        
        $parsedResult = json_decode($result, true);

        if(json_last_error() !== JSON_ERROR_NONE){
            log::error('Failed to parse OpenAI response: '.json_last_error_msg());
            throw new \Exception('Failed to parse OpenAI response: '.json_last_error_msg());
        }
        //Validate the parsed result
        $requiredKeys = ['summary', 'skills', 'experience', 'education'];
        $missingKeys = array_diff($requiredKeys, array_keys($parsedResult));

        if(count($missingKeys) > 0){
            log::error('Missing required keys in OpenAI response: '.implode(', ', $missingKeys));
            throw new \Exception('Missing required keys in the parser result: ');

        }

        // return the json Object
        return ['summary' => $parsedResult['summary']??'',
         'skills' => $parsedResult['skills']??'', 
        'experience' => $parsedResult['experience']??'',
         'education' => $parsedResult['education']??''];

    } catch (\Exception $e){
        log::error('Error extracting resume information: '.$e->getMessage());
        return ['summary' => '', 'skills' => '', 'experience' => '', 'education' => ''];
    }
    }      
    public function extractRawText(string $fileUri):string{
        // Reading the file from the cloud to local disk in temp file
        $tempFile = tempnam(sys_get_temp_dir(), 'resume_');

        $filepath = parse_url($fileUri, PHP_URL_PATH);

        if(!$filepath){ 
            throw new \Exception('Invalid file URI');}
        $filename = basename($filepath);
         
        $storagePath = 'resumes/'.$filename;

        if(!Storage::disk('cloud')->exists($storagePath)){
            throw new \Exception('File not found');
        }

        $pdfContent = Storage::disk('cloud')->get($storagePath);
        if(!$pdfContent){
            throw new \Exception('Failed to retrieve PDF content');
        }

        file_put_contents($tempFile, $pdfContent);

        $pdfToTextPaths = [
            env('PDFTOTEXT_PATH'),                      // Custom path
            'C:/poppler-26.02.0/Library/bin/pdftotext.exe', // Windows (Poppler)
            'C:/Program Files/poppler/Library/bin/pdftotext.exe', // Windows (Poppler)
            'C:/Program Files/xpdf-tools-win/bin64/pdftotext.exe', // Windows (Xpdf)
            '/opt/homebrew/bin/pdftotext',               // macOS (Homebrew)
            '/usr/bin/pdftotext',                        // Linux
            '/usr/local/bin/pdftotext',                  // Linux (alt)
        ];

        $pdfToTextBinary = null;
        foreach ($pdfToTextPaths as $path) {
            if ($path && file_exists($path)) {
                $pdfToTextBinary = $path;
                break;
            }
        }

        if (!$pdfToTextBinary) {
            throw new \Exception('pdftotext binary not found. Please install Poppler.');
        }


        //Exract text from the pdf file 
        $instense = new Pdf($pdfToTextBinary);
        $instense->setPdf($tempFile);
        $text = $instense->text();

        //clean up the temp file
        unlink($tempFile);
        return $text;
    }
}