### Windows Installation

1. **Copy Files to Web Server Directory**

   Place all the files in the directory served by your Apache server. By default, this is usually `C:\Apache24\htdocs` for Apache on Windows.

2. **Install Required Software**

   - **Python and pip**  
     Download and install Python from the [official Python website](https://www.python.org/downloads/). Ensure you check the box to add Python to your PATH during installation.

     Open Command Prompt and install `yt-dlp` using pip:

     ```cmd
     pip install yt-dlp
     ```

   - **Clone `uqload_downloader` Repository**

     Open Command Prompt and run:

     ```cmd
     git clone https://github.com/thomasarmel/uqload_downloader.git
     cd uqload_downloader
     # Follow the installation instructions in the uqload_downloader repository
     ```

   - **Install Apache**

     If you don't have Apache installed, download and install it from the [Apache Lounge](https://www.apachelounge.com/download/). Follow the installation instructions to set up Apache on your Windows machine.

3. **Configuration**

   - **Create `files_to_delete.txt`**  
     Create a file named `files_to_delete.txt` in the root of the Apache directory (`C:\Apache24\htdocs`). Ensure it is writable by the web server.

   - **Set Up Scheduled Task to Update `yt-dlp`**

     1. Open Task Scheduler from the Start menu.
     2. Click on **Create Basic Task**.
     3. Name the task something like "Update yt-dlp" and click **Next**.
     4. Choose **Daily** or **Weekly** (you can set it to recur every hour as well), and click **Next**.
     5. Set the start time and click **Next**.
     6. Choose **Start a Program** and click **Next**.
     7. In the **Program/script** field, enter the path to `python.exe` (usually `C:\Python39\python.exe` or similar, depending on your Python version).
     8. In the **Add arguments (optional)** field, enter:

        ```cmd
        -m pip install -U yt-dlp
        ```

     9. Click **Next** and then **Finish**.

     This will create a scheduled task that updates `yt-dlp` automatically so you can always download even if YouTube updates the site.

   - **Set Up a Scheduled Task for Cleanup (not needed in local mode)**

     To automatically clean up old files, set up another scheduled task:

     1. Open Task Scheduler and create a new task.
     2. Set the trigger to run every hour (or your preferred interval).
     3. Set the action to run the PHP script that performs the cleanup. The action might look something like:

        ```cmd
        C:\path\to\php\php.exe C:\Apache24\htdocs\src\clean_old_files.php
        ```

        Adjust the paths to match your setup.
