#!/bin/bash
KODIPATH="/opt/kodi-server/share/kodi/portable_data"
if [ -d "$KODIPATH/userdata" ]; then
echo "Using existing datafiles"
else
echo "Creating datafiles"
mkdir -p "$KODIPATH/userdata"
fi
if [ -f "$KODIPATH/userdata/advancedsettings.xml" ]; then
echo "Using existing advancedsettings.xml"
else
echo "Creating advancedsettings.xml"
cp /root/advancedsettings.xml "$KODIPATH/userdata/advancedsettings.xml"
fi

#Check if the KODIWATCHDOG variable has been passed in as TRUE
TRUEVAR=TRUE
if [ "${KODIWATCHDOG,,}" = "${TRUEVAR,,}" ]; then
	echo "Watchdog set"
	if [ -d "$KODIPATH/addons/service.watchdog" ]; then
		echo "Watchdog already installed"
	else
		echo "Installaing Watchdog addon"
		if [ -d "$KODIPATH/addons" ]; then
			echo "Addons folder exists already"
		else
			mkdir -p "$KODIPATH/addons"
		fi
		git clone -b headless --single-branch https://github.com/nicjo814/xbmc-addon-watchdog.git "$KODIPATH/addons/service.watchdog"
		if [ -d "$KODIPATH/userdata/addon_data/service.watchdog" ]; then
			echo "Watchdog settings folder exists already"
		else
			mkdir -p "$KODIPATH/userdata/addon_data/service.watchdog"
		fi
		if [ -f "$KODIPATH/userdata/addon_data/service.watchdog/settings.xml" ]; then
			echo "Watchdog settings exists already"
		else
			cp /root/settings.xml "$KODIPATH/userdata/addon_data/service.watchdog"
		fi
		touch "$KODIPATH/userdata/sources.xml"
	fi
fi

#Check if any plugins are passed to the container
if [ -z "$KODIADDONS" ]; then
	echo "No addons passed"
else
	cd /tmp
	mkdir -p "$KODIPATH/temp"
	/root/addon_install.php $KODIADDONS > "$KODIPATH/temp/addons_install.log" 2>&1
fi

chown -R nobody:users /opt/kodi-server
sed -i "s|\(<host>\)[^<>]*\(</host>\)|\1${MYSQLip}\2|" "$KODIPATH/userdata/advancedsettings.xml"
sed -i "s|\(<port>\)[^<>]*\(</port>\)|\1${MYSQLport}\2|" "$KODIPATH/userdata/advancedsettings.xml"
sed -i "s|\(<user>\)[^<>]*\(</user>\)|\1${MYSQLuser}\2|" "$KODIPATH/userdata/advancedsettings.xml"
sed -i "s|\(<pass>\)[^<>]*\(</pass>\)|\1${MYSQLpass}\2|" "$KODIPATH/userdata/advancedsettings.xml"
