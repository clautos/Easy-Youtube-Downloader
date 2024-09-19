<?php

require_once 'utils.php';

function downloadUqloadVideo($url, $path, $option_dl) {
    $title = getUqloadVideoTitle($url);
	$title === '' ? exit() : null;

    $cmd = "cd $path && uqload_downloader $url \"$title\"";
    $fullname = $path . $title;
    exec($cmd, $output);

    if ($option_dl == "local") {
        if (file_exists($fullname)) {
            serveFile($fullname);
            exit();
        } else {
            echo "<h2>Unable to download the video</h2><br/>";
            exit();
        }
    } elseif (!file_exists($fullname)) {
        echo "<h2>Unable to download the video</h2><br/>";
        exit();
    }
}

function downloadYTPlaylist($playlistUrl, $choice, $path, $option_dl, &$playListState) {
    
	$videos = getVideosList($playlistUrl);

    foreach ($videos as $videoData) {
        if (isset($videoData->title) && isset($videoData->id)) {
            if (!downloadRegularVideo('https://www.youtube.com/watch?v=' . $videoData->id, $choice, $path, 'remote', $playListState)) {
                $playListState['playList'][$playListState['plIndex']] = "";
            }
            $playListState['plIndex']++;
        }
    }

    if ($option_dl === "local") {
        foreach ($playListState['playList'] as $filePath) {
            if ($filePath) {
                $playListState['downloadLinks'][] = $filePath;
            }
        }
    }
}

function downloadRegularVideo($url, $choice, $path, $option_dl, &$playListState) {
    $filename = getFilename($url);
    if (empty($filename)) {
        echo "<h2>Unable to download the video $url, can't fetch the video name</h2><br/>";
        return 0;
    }

    $filename = sanitizeFilename($filename);
    if ($choice === 'audio') {
        $filename = adjustFilenameForAudio($filename);
    }

    if (!attemptDownload($choice, $path, $url, $filename)) {
        echo "<h2>Unable to download the video $url, permission issue or maybe the video is unavailable</h2><br/>";
        return 0;
    }

    $fullname = $path . $filename;
    $downloadedFile = findDownloadedFile($fullname);

    if ($option_dl == "local" && $downloadedFile) {
        serveFile($downloadedFile);
        exit;
    } elseif ($option_dl == "remote" && !$downloadedFile) {
        echo "<h2>Error while downloading the file.</h2><br/>" . $filename;
        return 0;
    }

    $playListState['playList'][$playListState['plIndex']] = str_replace('/var/www/html/', '', $downloadedFile);
    return 1;
}

// Generate download links in JavaScript
function generateDownloadScript($downloadLinks) {
    if (!empty($downloadLinks)) {
        $script = "<script>
        var downloadLinks = " . json_encode($downloadLinks) . ";
        console.log('Download links generated:', downloadLinks);

        function downloadAndDelete(filePath) {
            return new Promise((resolve, reject) => {
                var a = document.createElement('a');
                a.href = filePath;
                a.download = filePath.split('/').pop();
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);

                setTimeout(() => {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', 'src/add_file_to_deletion_list.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            resolve();
                        } else {
                            reject(new Error('Error while deleting the file'));
                        }
                    };
                    xhr.onerror = () => reject(new Error('Network error'));
                    xhr.send('file=' + encodeURIComponent(filePath));
                }, 1000);
            });
        }

        async function downloadSequentially() {
            for (const filePath of downloadLinks) {
                try {
                    await downloadAndDelete(filePath);
                    console.log('File downloaded and deleted:', filePath);
                } catch (error) {
                    console.error('Error:', error);
                }
            }
        }

        downloadSequentially();
        </script>";

        return $script;
    }
    return "";
}
?>
