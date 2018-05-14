# Web
Web-facing tool/page development

### Setting up an instance of TM's DB and web front end

The server should be running an instance of mysql server (5.7.22 as of this writing) and the apache web server (2.4.33 as of this writing).  The web server needs to have PHP enabled (version 7.2.5 as of this writing) with the mysqli extension, and mod_php72 to get the loadable module.  The remaining instructions assume apache, mysql, and php are all working together properly.  On FreeBSD, this involved installing the correct packages.

The files in this Web repository should be placed in a directory served by the web server.
