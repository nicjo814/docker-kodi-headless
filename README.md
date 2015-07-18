# docker-kodi-headless
**Headless Kodi images based on the images created by sparklyballs. All files required to build the Docker image is available on my Github account.**

Usage: docker run -d [–restart-always] [-p <hostport>:<containerport>] [-e MYSQLip=<IP>] [-e MYSQLport=<port>] [-e MYSQLuser=<user>] [-e MYSQLpass=<pwd>] [-e KODIWATCHDOG=True|False] [-e KODIADDONS=<addons>] -v <hostpath>:/opt/kodi-server/share/kodi/portable_data nicjo814/docker-kodi-headless:<tag>

*\<hostport\>:* Port to map the Kodi webserver to on the host (port that will be used to actually reach the webserver).

*\<containerport\>:* Webserver port as defined in the advancedsettings.xml file (8080 by default).

*\<IP\>:* IP of the Mysql server to connect to.

*\<port\>:* Port where the Mysql server is running.

*\<user\>:* Kodi Mysql user (xbmc/kodi by default).

*\<pwd\>:* Kodi Mysql user password (xbmc/kodi by default).

*\<addons\>:* List of addons (separated by pipe (|)) that should be downloaded and installed. If multiple addons are provided, make sure that they are properly quoted like -e KODIADDONS=“metadata.universal|service.watchdog”. All addon dependencies will also be resolved.

*\<hostpath\>:* Path on the host to bind-mount into the container (usually /opt/kodi-server or similar).

*\<tag\>:* The tag of the Kodi docker image to run. Check available tags on my Docker Hub.


If the KODIWATCHDOG parameter is set to True, a patched version of the service.watchdog addon will be downloaded and installed (supports headless mode). To use the addon your sources.xml file from an existing Kodi installation must be placed within the portable_data/userdata folder.
