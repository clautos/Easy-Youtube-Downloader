<?php

require_once 'src/downloads.php';

// Configure the following symlinks so they point to actual folders
// Or configure to the actual Windows download folder if using Windows
$tmpDownloads = 'C:\Users\test\Downloads';
$persistentDownloads = 'C:\Users\test\Downloads';

$playListState = [
    'downloadLinks' => [],
    'playList' => [],
    'plIndex' => 0,
    'errors' => []
];


if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (isset($_POST['url']))
    {
        $url = $_POST['url'];
    }

    if (isset($_POST['choice']))
    {
        $choice = $_POST['choice'];
    }

    if (isset($_POST['download_option']))
    {
        $downloadOption = $_POST['download_option'];
        if ($downloadOption === 'local')
        {
            $path = cleanFolderName($tmpDownloads);
        }
        elseif ($downloadOption === 'remote')
        {
			$path = cleanFolderName($persistentDownloads);
        }
    }
}



if (isset($choice))
{
    if (str_contains($_POST['url'], 'uqload.'))
    {
        downloadUqloadVideo($url, $path, $downloadOption, $playListState);
    }
    elseif (
        (str_contains($url, 'https://youtube.com') || str_contains($url, 'youtu.be') || str_contains($url, 'www.youtube.com')) 
        && str_contains($url, '&list=')
    )
    {
        downloadYTPlaylist($url, $choice, $path, $downloadOption, $playListState);
    }
    else
    {
        downloadRegularVideo($url, $choice, $path, $downloadOption, $playListState);
    }
}

// If included above, and before the download takes place, the download is bugged
include 'src/main.html';
//So errors are printed after the page
if ($playListState['errors'] != [])
{
	foreach ($playListState['errors'] as $err)
	{
		echo $err;
	}
	echo "<br>";
}

if (!empty($playListState['downloadLinks']))
{
    echo generateDownloadScript($playListState['downloadLinks']);
}

?>
