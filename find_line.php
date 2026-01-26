<?php
// CONFIGURATION
// ----------------------------------------
$directory = __DIR__; // Search the directory where this script is located
$lineNumber = 62;     // The line number to find (human-readable 1-based)

echo "Searching for Line $lineNumber in all .php files in: $directory\n";
echo "==========================================================\n";

// Create a recursive iterator to loop through all folders
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    // 1. Only process files with a 'php' extension
    if ($file->getExtension() === 'php') {
        
        // 2. Read the file into an array
        // We use @ to suppress errors in case of permission issues
        $lines = @file($file->getRealPath());

        // 3. Check if the file was read successfully and has enough lines
        // Note: Arrays are 0-indexed, so Line 62 is at index 61.
        $index = $lineNumber - 1;

        if ($lines && isset($lines[$index])) {
            $content = trim($lines[$index]);
            
            // 4. Print the result
            echo "File: " . $file->getPathname() . "\n";
            echo "Content: " . $content . "\n";
            echo "----------------------------------------------------------\n";
        }
    }
}
?>
