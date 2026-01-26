<?php
// 1. Get the line number from the URL (default to line 1 if not set)
$lineNumber = readline("Line Number: ");
// 2. Get the content to search for
$searchFor = readline("Search For: ");

// Configuration
$directory = __DIR__; // Scans the directory where this script sits
$filePattern = "*.php"; // Only looks for PHP files

// Validation: Ensure line number is positive
if ($lineNumber < 1) {
    die("Error: Line number must be greater than 0.");
}

echo "Line Checker Tool\n";
echo "Scanning directory: $directory\n";
echo "Searching for: $searchFor\n";
echo "Showing contents of Line #$lineNumber\n\n";

// 2. Get all PHP files in the directory
$files = glob($directory . "/" . $filePattern);

if (!$files) {
    echo "No PHP files found in this directory.";
    exit;
}

echo "Filename\tContent of Line $lineNumber\n";

// 3. Loop through each file
foreach ($files as $filePath) {
    // Skip this script itself so it doesn't clutter the list
    if (basename($filePath) == basename(__FILE__)) {
        continue; 
    }

    $content = get_specific_line($filePath, $lineNumber);
    $fileName = basename($filePath);
    
    if (str_contains($content,$searchFor)) {
	    // Formatting for display
	    $displayContent = htmlspecialchars($content); // Prevent HTML tags from running

    	echo "$fileName\t$displayContent\n";
	}
}

/**
 * Efficiently retrieves a specific line from a file without loading the whole file.
 */
function get_specific_line($file, $line) {
    try {
        $fileObj = new SplFileObject($file);
        
        // Seek to the line number (Subtract 1 because array is 0-indexed)
        $fileObj->seek($line - 1);
        
        // Check if the line actually exists
        if ($fileObj->valid()) {
            return $fileObj->current();
        } else {
            return "[File is shorter than $line lines]\n";
        }
    } catch (Exception $e) {
        return "Error reading file.";
    }
}
?>