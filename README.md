# Web-Based YouTube Video Downloader

This is a web-based YouTube video downloader that you can host using a simple web server (e.g., Lighttpd or Apache). It is based on `yt-dlp`, allowing you to download videos with a simple click through a web page without having to type commands. It also uses `uqload_downloader` for downloading uqload videos.

**GitHub link for uqload_downloader:** [uqload_downloader](https://github.com/thomasarmel/uqload_downloader)
<div align="center">
  <img src="preview/sitepreview.gif" style="width: 70%;">
</div>
## Features

- Downloads videos from various hosting sites such as Twitter, Facebook, YouTube, and more.
- Provides a user-friendly web interface for downloading videos.
- Supports local downloads and files serving after download (server mode).
- Supports YouTube playlist downloads (just enter the playlist link)
- If you are using the web app on localhost on your laptop, use "local mode" and configure the symlink persistentDownloads to your downloads folder
- Easy to access, once installed just go to the server (or localhost if installed on your laptop) and type : http://localhost or http://server_IP
  with server_IP being the IP of your machine hosting the page

## Setup Instructions

For Windows installation, refer to the file README_WINDOWS.md
The following is for Linux (especially Ubuntu server 22.04 LTS)
Copy all the files in the /var/www/html folder if using Apache of Lighttpd

### Create the Symlinks

Create the following symlinks:

```bash
ln -s /path/to/persistentDownloads /var/www/html/persistentDownloads
ln -s /path/to/tempDownloads /var/www/html/tempDownloads
```

- **`persistentDownloads`**  
  Directory for persistent video downloads, used when the "download only on the server" option is selected. This can be useful if combined with a Samba share, for example.

- **`tempDownloads`**  
  Temporary download folder for playlists. Files will be deleted and are not accessible to users.

### Install Required Software

Download and install the following:

```bash
sudo apt-get install python3
pip3 install yt-dlp
# don't forget to regularly update yt-dlp with pip so it stays able to download videos if YouTube updates the site
# for example with this cronjob: 15 * * * * root pip3 install -U yt-dlp >/dev/null 2>&1
# tested on Ubuntu Server 22.04 LTS, you may have to adapt it for latest releases as venv are managed differently, as far as I know
git clone https://github.com/thomasarmel/uqload_downloader.git
cd uqload_downloader
# Follow the installation instructions in uqload_downloader repository
sudo apt-get install lighttpd php php-cgi
```

### Configuration

1. **Ensure `files_to_delete.txt`**  
   The `files_to_delete.txt` file must be present at the root of the website, as it is used to clean up videos downloaded as playlists, which are then retrieved via direct download (not sent to the server). It must be owned by `www-data` and be writable.

   ```bash
   touch /var/www/html/files_to_delete.txt
   chown www-data:www-data /var/www/html/files_to_delete.txt
   chmod 664 /var/www/html/files_to_delete.txt
   ```

2. **Set Up a Cron Job**  
   Create the following cron job to delete unnecessary files:

   ```bash
   01 * * * * root cd /var/www/html && /usr/bin/php /var/www/html/src/clean_old_files.php
   ```

   You can add this cron job by running:

   ```bash
   sudo crontab -e
   ```

   Then, paste the cron job line into the editor.

## Notes

- For age-restricted videos, the downloader is unable to retrieve the videos due to YouTube's security measures. Username/password authentication is no longer supported, and age bypass methods are ineffective.
- Automation tools like Playwright cannot manage logins effectively, preventing the retrieval of cookies necessary for `yt-dlp`.
