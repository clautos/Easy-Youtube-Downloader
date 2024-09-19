<?php

// if PHP < version 8 to avoid errors
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}

if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle) {
        // Retourne true si $haystack se termine par $needle
        return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
    }
}

// Function to truncate long video filenames (used for Twitter)
function truncateFilename($filename) 
{
    $maxLength = 150;
    if (strlen($filename) > $maxLength) {
        $path_info = pathinfo($filename);
        $name = $path_info['filename'];
        $extension = isset($path_info['extension']) ? '.' . $path_info['extension'] : '';
        $nameLength = $maxLength - strlen($extension);
        if ($nameLength > 0) {
            $truncatedName = substr($name, 0, $nameLength);
            $filename = $truncatedName . $extension;
        } else {
            $filename = '';
        }
    }
    return $filename;
}

function getVideosList($playlistUrl)
{
	$command = "yt-dlp --flat-playlist --dump-json " . escapeshellarg($playlistUrl);
    $output = shell_exec($command);
    $videos = array_filter(array_map('json_decode', explode("\n", trim($output))));
	return $videos;
}

function getFilename($url) {
    try
    {
		// to prevent a crash of the app on easy PHP on Windows
		set_time_limit(0);
    	$cmd = "yt-dlp --get-filename -o \"%(title)s.%(ext)s\" " . escapeshellarg($url);
    	exec($cmd, $output, $return_var);
    	if ($return_var !== 0) {
        	return "";
    	}
    }
    catch (Exception $e)
    {
        echo "<h2>Erreur : " . $e->getMessage() . "</h2>";
        return "";
    }
    return !empty($output) ? $output[0] : "";
}

function sanitizeFilename($filename) {
    $filename = str_replace(",", " ", $filename);
    return truncateFilename($filename);
}

function getUqloadVideoTitle($url)
{
	$title = "";
    $html = file_get_contents($url);
    $dom = new DOMDocument;
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();
    $xpath = new DOMXPath($dom);
    // Search for the first <h1> tag, which contains the title of the video
    $h2Node = $xpath->query('//h1')->item(0);
    if ($h2Node) {
        $title = trim($h2Node->textContent) . ".mp4";
        $title = str_replace(",", " ", $title);
		return $title;
    } else {
        echo "<h2>Unable to download the video</h2><br/>";
        return $title;
    }
}

function adjustFilenameForAudio($filename) {
    if (str_ends_with($filename, "webm")) {
        return str_replace(".webm", ".mp3", $filename);
    } elseif (str_ends_with($filename, "mkv")) {
        return str_replace(".mkv", ".mp3", $filename);
    } elseif (str_ends_with($filename, "avi")) {
        return str_replace(".avi", ".mp3", $filename);
    } elseif (str_ends_with($filename, "mov")) {
        return str_replace(".mov", ".mp3", $filename);
    } elseif (str_ends_with($filename, "flv")) {
        return str_replace(".flv", ".mp3", $filename);
    } elseif (str_ends_with($filename, "wmv")) {
        return str_replace(".wmv", ".mp3", $filename);
    } elseif (str_ends_with($filename, "hevc") || str_ends_with($filename, "h265")) {
        return str_replace([".hevc", ".h265"], ".mp3", $filename);
    } elseif (str_ends_with($filename, "3gp")) {
        return str_replace(".3gp", ".mp3", $filename);
    } elseif (str_ends_with($filename, "ogv")) {
        return str_replace(".ogv", ".mp3", $filename);
    } else {
        return str_replace(".mp4", ".mp3", $filename);
    }
}

// Serve the file for download to the client
function serveFile($downloadedFile)
{
	$contentType = getContentType($downloadedFile);
    header("Content-Type: $contentType");
    header('Content-Disposition: attachment; filename=' . basename($downloadedFile));
    header('Content-Length: ' . filesize($downloadedFile));
    ob_get_clean();
    readfile($downloadedFile, 'rb');
    ob_end_flush();
    exec("rm -fr " . escapeshellarg($downloadedFile));
}

function attemptDownload($choice, $path, $url, $filename)
{
	$downloadSuccessful = false;
	
	$formats = [
        'video' => [
            '-f \'bestvideo[height<=1080]+bestaudio\'',
            '-f b',
            '-F'
        ],
        'audio' => ['-x --audio-format mp3']
    ];

    foreach ($formats[$choice] as $format) {
        $returnVar = executeDownloadCommand($path, $url, $filename, $format);
        if ($returnVar == 0) {
            $downloadSuccessful = true;
            break;
        }
        if ($format === '-F') {
            // Specific logic for the last video format
            exec("cd " . escapeshellarg($path) . " && yt-dlp -F " . escapeshellarg($url), $formatOutput);
            $lastFormat = explode(" ", end($formatOutput))[0];
            $returnVar = executeDownloadCommand($path, $url, $filename, "-f $lastFormat");
            $downloadSuccessful = ($returnVar === 0);
        }
    }
	return $downloadSuccessful;
}

function executeDownloadCommand($path, $url, $filename, $format) {
	try
	{
		// to prevent a crash of the app on easy PHP on Windows
		set_time_limit(0);
		$cmd = "cd " . escapeshellarg($path) . " && yt-dlp " . escapeshellarg($url) . " $format -o " . escapeshellarg($filename);
		exec($cmd, $output, $returnVar);
		return $returnVar;
	}
	catch (Exception $e)
    {
        echo "<h2>Erreur : " . $e->getMessage() . "</h2>";
        return "";
    }
}

function cleanFolderName($folder)
{
	return rtrim($folder, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
}

function findDownloadedFile($fullname) {
    if (file_exists($fullname)) {
        return $fullname;
    }
    $matchingFiles = glob("$fullname.*");
    return count($matchingFiles) > 0 ? $matchingFiles[0] : "";
}

function getContentType($filename) {
    if (str_ends_with($filename, "webm")) {
        return 'video/webm';
    } elseif (str_ends_with($filename, "mkv")) {
        return 'video/x-matroska';
    } elseif (str_ends_with($filename, "mp4")) {
        return 'video/mp4';
    } elseif (str_ends_with($filename, "avi")) {
        return 'video/x-msvideo';
    } elseif (str_ends_with($filename, "mov")) {
        return 'video/quicktime';
    } elseif (str_ends_with($filename, "flv")) {
        return 'video/x-flv';
    } elseif (str_ends_with($filename, "wmv")) {
        return 'video/x-ms-wmv';
    } elseif (str_ends_with($filename, "hevc") || str_ends_with($filename, "h265")) {
        return 'video/hevc';
    } elseif (str_ends_with($filename, "3gp")) {
        return 'video/3gpp';
    } elseif (str_ends_with($filename, "ogv")) {
        return 'video/ogg';
    } else {
        return 'application/octet-stream';
    }
}

?>
