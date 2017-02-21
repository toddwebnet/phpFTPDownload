# phpFTPDownload
downloads an entire ftp folder (with exceptions) to a backup folder then does a git update on the backup folder

#Does the following:
1. read all the files in an ftp folder (recursively)
2. saves all fiiles to the backup folder if size or date do not match
3. does a git commit and push on the folder if it is a git repository
(git must be in master checkout and have no other activitiy to it)