# Web
Web-facing tool/page development

### Setting up an instance of TM's DB and web front end

The server should be running an instance of mysql server (5.6.40 as of this writing) and the apache web server (2.4.33 as of this writing).  The web server needs to have PHP enabled (version 5.6.35 as of this writing) with the mysqli extension, and mod_php56 to get the loadable module.

The files in this repository should be placed in a directory served by the web server.
