# photo-print-aspect-ratio
This was a project to fix auto-cropping done by online photo services.  It forces a 4x6 aspect ratio and adds blurred borders if necessary in order to accomplish the aspect ratio modification.


Create and deploy development environments
https://www.vagrantup.com/downloads.html

Virtualizer
https://www.virtualbox.org/wiki/Downloads

GIT
https://git-scm.com/downloads

Run GIT Bash from this directory and run "vagrant up"

Drop your photos into the /html/originals/ directory and visit the http://localhost:8080
Photos will be copied into the /html/resized/ directory and you can use those to prevent photo printing websites from messing with your photos!