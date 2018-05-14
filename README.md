# Web
Web-facing tool/page development

### Setting up an instance of TM's DB and web front end

The server should be running an instance of MySQL server (5.7.22 as of this writing) and the apache web server (2.4.33 as of this writing).  The web server needs to have PHP enabled (version 7.2.5 as of this writing) with the mysqli extension, and mod_php72 to get the loadable module.  The remaining instructions assume apache, MySQL, and PHP are all working together properly.  On FreeBSD, this involved installing the correct packages.

First, create the users needed for the database.  Connect and authenticate as root to the MySQL server.  Create passwords for an account that will have permission to administer the TM database that will be used for DB updates and an account that will have only read permission that will be used by the web front end.  In the example below, these use the TM default names "travmapadmin" and "travmap".

```
CREATE USER 'travmapadmin'@'localhost' IDENTIFIED WITH mysql_native_password BY 'YOURPASSWORDFORTHISUSER';
CREATE USER 'travmap'@'localhost' IDENTIFIED WITH mysql_native_password BY 'YOURPASSWORDFORTHISUSER';
```

Next, we create the database and give these users needed permissions.

```
CREATE DATABASE TravelMapping;
GRANT SELECT ON `TravelMapping`.* TO 'travmap'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX ON `TravelMapping`.* TO 'travmapadmin'@'localhost'
```

The files in this Web repository should be placed in a directory served by the web server.
