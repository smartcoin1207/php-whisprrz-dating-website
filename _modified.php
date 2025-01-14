<?php
// Specify the directory path (public_html)
$directory = 'public_html/htdocs/whisprrz.com';

// Function to recursively scan directories and get all files
function getFiles($dir) {
    $files = [];
    
    // Get all files and directories in the current directory
    $entries = scandir($dir);
    
    foreach ($entries as $entry) {
        // Skip current and parent directory ('.' and '..')
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        
        $path = $dir . '/' . $entry;

        // If it's a directory, recurse into it
        if (is_dir($path)) {
            $files = array_merge($files, getFiles($path)); // Merge files from subdirectories
        } else {
            // If it's a file, add it to the list
            $files[] = $path;
        }
    }
    
    return $files;
}

// Function to check if a file has changed based on filemtime
function hasFileChanged($file) {
    // Get the stored modification time (this example assumes we use a session for storage)
    session_start();
    if (!isset($_SESSION['file_mod_times'])) {
        $_SESSION['file_mod_times'] = []; // Initialize session variable to store file modification times
    }

    $currentModificationTime = filemtime($file);
    $storedModificationTime = $_SESSION['file_mod_times'][$file] ?? null;

    // Compare the current modification time with the stored one
    if ($storedModificationTime !== $currentModificationTime) {
        // If the modification time is different, the file content has changed
        $_SESSION['file_mod_times'][$file] = $currentModificationTime;  // Update the stored time
        return true;
    }

    return false;
}

// Get all files from the specified directory and its subdirectories
$all_files = getFiles($directory);

// Display the files with their modification time and change status
echo '<h1>Recently Modified Files and Changes</h1>';
echo '<ul>';
foreach ($all_files as $file) {
    // Check if the file content has changed
    if (hasFileChanged($file)) {
        echo '<li>' . $file . ' - Content has been changed at: ' . date('Y-m-d H:i:s', filemtime($file)) . '</li>';
    } else {
        echo '<li>' . $file . ' - No change detected.</li>';
    }
}
echo '</ul>';
?>
