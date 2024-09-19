### Windows Installation

# Setup Instructions

1. Install Visual C++ Redistributable Runtime Package (All-in-One)
   - Download from: [TechPowerUp](https://www.techpowerup.com/download/visual-c-redistributable-runtime-package-all-in-one/)

2. Install EasyPHP
   - Download from: [EasyPHP DevServer](https://www.easyphp.org/save-easyphp-devserver-latest.php)

3. Copy web application files
   - Destination: `C:\Program Files (x86)\EasyPHP-Devserver-17\eds-www`

4. Install Python 3
   - Important: Check the option to add Python to PATH during installation

5. Open Command Prompt and run:
   python -m pip install yt-dlp ffmpeg

6. Modify `index.php`
- Update the `tempDownloads` variable with your desired path
- Example: `C:\Users\test\Downloads`

7. Grant write permissions
- Give LOCAL SERVICE write access to the downloads folder
- Note: The exact name may vary depending on your system language

8. Install FFmpeg (for MP3/MP4 handling)
- Download from: [LAME Ain't an MP3 Encoder](https://lame.buanzo.org/ffmpeg.php)
