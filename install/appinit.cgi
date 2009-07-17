#!/bin/sh
##############################################################
#
#   Generic 3rd party application initializer
#
#   this application handling will take care of integrating
#   third party applications in a generic non-intrusive way
#   Installing and Uninstalling is made easy by just
#   storing or removing a folder in the APPS_FOLDER
#   all subsequent configuration is done by inspecting
#   a configuration file in the application folder itself
#
#-------------------------------------------------------------
#
#   Version 0.1:
#       Ger Teunis: Initial version
#
#   Version 0.2:
#       Ger Teunis: Added auto-upgrade
#
#   Version 0.3
#       Ger Teunis: All files, including the script itself, should be stored in
#       profile folder.
#
#   Version 0.4
#       Ger Teunis: Fixed some small bug regarding creating items in crontab.
#
#   Version 0.5
#       Ger Teunis: Implemented Minimal AppInfo version
#
#   Version 0.6
#       Ger Teunis: Added the ability to install from tar or rar
#                   appinit will register itself on boot and start all applications
#                   implemented info; will be used by CSI for current statussen
#
#   Version 0.7
#       Ger Teunis: Renamed the script from .sh to .cgi and .appinfo folder to Appinfo
#                   this will allow the script being called by sybhttpd
#
#-------------------------------------------------------------
#   Legal: published under GPL v3
#   http://www.gnu.org/licenses/gpl-3.0.txt
##############################################################




APPS_FOLDER="/share/Apps"
APPS_MINIMAL_APPINFO_VERSION="1"
APPINIT_NAME="Application Initializer"
APPINIT_FILENAME="appinit.cgi"
APPINIT_PROFILE="$APPS_FOLDER/AppInit"
APPINIT_VERSION="0.7"
APPINIT_VERSION_URL="http://repository.nmtinstaller.com/appinit_version"
APPINIT_UPGRADE_URL="http://repository.nmtinstaller.com/appinit.cgi"
APPINIT_AUTOSTART_STATE="/tmp/appinit_state"
CRONTAB_RELOAD="0"
APPINIT_APPS_AUTOSTART="0"
APPINIT_APPS_BOOTSTART="1"
UNRAR="/mnt/syb8634/bin/unrar"
TAR="/bin/tar"




auto_upgrade()
{
    echo -n "Checking for new version: "
    if [ ! -d "$APPINIT_PROFILE" ]; then
        mkdir -p "$APPINIT_PROFILE"
    fi
    rm "$APPINIT_PROFILE/version_online" 2>/dev/null
    wget -q -O "$APPINIT_PROFILE/version_online" $APPINIT_VERSION_URL 2>/dev/null
    if [ -f "$APPINIT_PROFILE/version_online" ] && [ -n "`cat "$APPINIT_PROFILE/version_online"`" ] ; then
        if [ "$APPINIT_VERSION" != "`cat "$APPINIT_PROFILE/version_online"`" ]; then
            wget -q -O "$APPINIT_PROFILE/$APPINIT_FILENAME" $APPINIT_UPGRADE_URL
            echo "Upgraded"
            chmod a+x "$APPINIT_PROFILE/$APPINIT_FILENAME"
            eval "$APPINIT_PROFILE/$APPINIT_FILENAME" "$1" "$2"
            exit 0
        else
           echo "up to date"
        fi
    else
        echo "Can't check for new version"
    fi
}

appinit_autostart_add()
{
    if [ -z "`crontab -l -u nmt 2>/dev/null | grep "$APPINIT_FILENAME"`" ]; then
        crontab -l -u nmt > "$APPINIT_PROFILE/crontab" 2>/dev/null
        echo "*/10 * * * * $APPINIT_PROFILE/$APPINIT_FILENAME start" >> "$APPINIT_PROFILE/crontab"
        CRONTAB_RELOAD="1"
    fi
}

appinit_autostart_remove()
{
    if [ -n "`crontab -l -u nmt 2>/dev/null | grep "$APPINIT_FILENAME"`" ]; then
        TEMP="`crontab -l -u nmt 2>/dev/null | grep -v "$APPINIT_FILENAME"`"
        echo "$TEMP" > "$APPINIT_PROFILE/crontab"
        CRONTAB_RELOAD="1"
    fi
}


appinit_bootstart_add()
{
    if [ -z "`cat "/mnt/syb8634/etc/ftpserver.sh" | grep "$APPINIT_FILENAME"`" ]; then
        echo -n "Configuring system to start all applications on boot: "
        escapedpath=`echo "$APPINIT_PROFILE" | sed 's/\\//\\\\\\//g'`
        TEMP="`cat "/mnt/syb8634/etc/ftpserver.sh" | sed "s/case \\\"\\\$1\\\" in/$escapedpath\/$APPINIT_FILENAME \\\"\\\$1\\\"\ncase \\\"\\\$1\\\" in/g"`"
        echo "$TEMP" > "/mnt/syb8634/etc/ftpserver.sh"
        echo "Done"
    fi
}

appinit_bootstart_remove()
{
    if [ -n "`cat "/mnt/syb8634/etc/ftpserver.sh" | grep "$APPINIT_FILENAME"`" ]; then
        echo -n "Configuring system not to start all applications on boot: "
        TEMP="`cat "/mnt/syb8634/etc/ftpserver.sh" | grep -v "$APPINIT_FILENAME"`"
        echo "$TEMP" > "/mnt/syb8634/etc/ftpserver.sh"
        echo "Done"
    fi
}


parameter_value()
{
    escapedpath=`echo "$path" | sed 's/\\//\\\\\\//g'`
    echo "`echo "$1" | sed 's/.*=\"\(.*\)\".*/\1/g' | sed 's/\\"/\"/g' | sed "s/#PATH#/$escapedpath/g"`"
    #'
}

parameter_name()
{
    echo "`echo "$1" | sed 's/[ ]*\(.*\)=.*/\1/g'`"
}

appinit_profile_create()
{
    echo -n "Checking $APPINIT_NAME profile: "

    #fix old filename
    if [ -d "$APPS_FOLDER/.appinit" ]; then
        mv "$APPS_FOLDER/.appinit" "$APPINIT_PROFILE" >/dev/null 2>/dev/null
        mv "$APPINIT_PROFILE/appinit.sh" "$APPINIT_PROFILE/$APPINIT_FILENAME" >/dev/null 2>/dev/null
    fi
    
    if [ ! -d "$APPINIT_PROFILE" ] || [ ! -f "$APPINIT_PROFILE/version" ] || [ "`cat "$APPINIT_PROFILE/version"`" != "$APPINIT_VERSION" ] ; then

        if [ ! -d "$APPINIT_PROFILE" ]; then
            mkdir -p "$APPINIT_PROFILE"
        fi
        
        rm -Rf `ls -1 "$APPINIT_PROFILE" | grep -v "$APPINIT_FILENAME"` >/dev/null 2>&1
        
        if [ ! -f "$APPINIT_PROFILE/$APPINIT_FILENAME" ]; then
            cp "$0" "$APPINIT_PROFILE/$APPINIT_FILENAME"
            rm -Rf "$0"
            chmod a+x "$APPINIT_PROFILE/$APPINIT_FILENAME"
        fi
        
        mkdir -p "$APPINIT_PROFILE/websites"
        chown nobody.99 "$APPINIT_PROFILE/websites"
        echo "$APPINIT_VERSION" > "$APPINIT_PROFILE/version"
        
        cat >"$APPINIT_PROFILE/httpd.conf" <<EOF
Port 9999
Listen 9999
<VirtualHost *:9999>
    ScriptAlias /php/ /mnt/syb8634/server/
    AddType application/x-httpd-php5 .php
    Action application/x-httpd-php5 /php/php5-cgi
    DocumentRoot $APPINIT_PROFILE/websites
    DirectoryIndex index.php index.html
    Options +ExecCGI
    AddHandler cgi-script .cgi
    ServerName `ifconfig | grep "inet addr" | sed 's/.*inet addr:\s*\([0-9.]\+\).*/\1/g' | sed "2,$ d"`
</VirtualHost>
EOF
        echo "Recreated"
    else
        echo "Valid"
    fi
}

crontab_remove()
{
    if [ -n "`crontab -l -u nmt 2>/dev/null | grep "#APPINIT_${name}#"`" ]; then
        crontab -u nmt -l | grep -v "#APPINIT_${1}#" 2>/dev/null > "$APPINIT_PROFILE/crontab"
        CRONTAB_RELOAD="1"
    fi
}


crontab_add()
{
    if [ -n "$crontab" ] && [ -z "`crontab -l -u nmt 2>/dev/null | grep "#APPINIT_${name}#"`" ]; then
        echo "$crontab #APPINIT_${name}#" >> "$APPINIT_PROFILE/crontab"
        CRONTAB_RELOAD="1"
    fi
}

webservice_add()
{
    if [ -n "$1" -a -n "$2" ]; then
        url="$2"
        name="$1"
        webservice_remove "$name" "$url"
        
        #Find a free id
        id=3
        test=`cat /opt/sybhttpd/default/webservices_edit.html | grep "option value=\"$id\""`
        while [ -n  "$test" ] && [ "$id" -le "13" ]; do
            id=$(( $id + 1 ))
            test=`cat /opt/sybhttpd/default/webservices_edit.html | grep "option value=\"$id\""`
        done
        
        if [ "$id" -le "12" ]; then
            #then add it again
            call="http://localhost:8883/webservices.cgi?%7Fadd=add&%7FhiDe=2&%7Faction=add&%7Fwebimg=&%7Fservlist=$id&_web_name_=$name&_web_url_=$url"
            wget -q -O - $call >/dev/null
        else
            echo "<b>ADDING WEBSERVICE FAILED</b><br><br>"
            echo "<h3>All ten positions already occuped</h3>Please remove a web service before adding one again."
        fi
    fi
}


webservice_remove()
{
    url="$2"
    name="$1"
    name_nice=`echo "$name" | sed 's/%20/ /g'`
    
    #first try to remove link, search by name
    id=`cat /opt/sybhttpd/default/webservices_edit.html | grep "option value=\"" | grep "$name_nice" | sed 's/.*"\([0-9]\)".*/\1/g'`
    call="http://localhost:8883/webservices.cgi?%7Fdel=remove&%7FhiDe=2&%7Faction=save&%7Fwebimg=&%7Fservlist=$id&_web_name_=$name&_web_url_=$url"
    wget -q -O - $call >/dev/null
}


webserver_add()
{
    TEST=`cat /mnt/syb8634/server/php5server/httpd.conf | grep "$APPINIT_PROFILE/httpd.conf"`
    if [ -z "$TEST" ]; then
        escaped=`echo "$APPINIT_PROFILE" | sed 's/\\//\\\\\\//g'`
        mv /mnt/syb8634/server/php5server/httpd.conf /mnt/syb8634/server/php5server/httpd_old.conf
            cat /mnt/syb8634/server/php5server/httpd_old.conf | grep -v "\.appinit" | \
                            sed "s/Include httpd_inc.conf/Include httpd_inc.conf\nInclude $escaped\/httpd.conf/g" > \
                            /mnt/syb8634/server/php5server/httpd.conf
        rm /mnt/syb8634/server/php5server/httpd_old.conf
        
        su -c "cd /mnt/syb8634/server && ./apachectl_php5 restart" nobody >/dev/null 2>&1
    fi
    
    #webserver is not running
    if [ -z "`ps | grep "/mnt/syb8634/server/php5server/httpd" | grep -v "grep"`" ]; then
        su -c "cd /mnt/syb8634/server && ./apachectl_php5 restart" nobody
    fi
}


websites_add()
{
    if [ -d "$webui_path" ] && [ ! -d "$APPINIT_PROFILE/websites/${name}_web" ]; then
        ln -s "$webui_path" "$APPINIT_PROFILE/websites/${name}_web"
    fi
    
    if [ -d "$gayaui_path" ] && [ ! -d "$APPINIT_PROFILE/websites/${name}_gaya" ]; then
        ln -s "$gayaui_path" "$APPINIT_PROFILE/websites/${name}_gaya"
        webservice_add "$name" "http://localhost:9999/${name}_gaya"
    fi
}


websites_remove()
{
    if [ -d "$APPINIT_PROFILE/websites/${name}_web" ]; then
        rm -Rf "$APPINIT_PROFILE/websites/${name}_web"
    fi
    
    if [ -d "$APPINIT_PROFILE/websites/${name}_gaya" ]; then
        rm -Rf "$APPINIT_PROFILE/websites/${name}_gaya"
        webservice_remove "$name" "http://localhost:9999/${name}_gaya"
    fi
}


app_fixpermissions()
{
    chown -R nmt.nmt "$1" >/dev/null 2>&1
    chmod -R 777 "$1" >/dev/null 2>&1
}



app_daemon_execute()
{
    if [ -n "$daemon_script" ] && [ -f "$path/$daemon_script" ] ; then
        chmod a+x "$path/$daemon_script"
        cd "$path" && eval "./$daemon_script" "$1" >/dev/null 2>&1
    fi
}

app_setup_execute()
{
    if [ -n "$1" ] && [ -n "$setup_script" ] && [ -f "$path/$setup_script" ]; then
        chmod a+x "$path/$setup_script"
        cd "$path" && eval "./$setup_script" "$1" >/dev/null 2>/dev/null

        if [ "$1" == "install" ] && [ ! -f "$path/.installed" ]; then
            touch "$path/.installed"
        fi

        if [ "$1" == "uninstall" ] && [ -f "$path/.installed" ]; then
            rm -Rf "$path/.installed"
        fi
    fi
}



app_appinfo_parse()
{
    appinfo_format=""
    uniqueid=""
    name=""
    version=""
    enabled="0"
    daemon_script=""
    path=`echo "$1" | sed 's/\(.*\)\/.*/\1/g'`
    crontab=""
    setup_script=""
    gayaui_path=""
    webui_path=""
    
    #explicit parsing of options
    #not using generic to prevent overwrite
    #of important vars or 
    #in case of using eval: security issues
    while read LINE ; do
        case "`parameter_name "$LINE"`" in
            appinfo_format)
            appinfo_format=`parameter_value "$LINE"`
            ;;
            uniqueid)
            uniqueid=`parameter_value "$LINE"`
            ;;
            name)
            name=`parameter_value "$LINE"`
            ;;
            version)
            version=`parameter_value "$LINE"`
            ;;
            enabled)
            enabled=`parameter_value "$LINE"`
            ;;
            daemon_script)
            daemon_script=`parameter_value "$LINE"`
            ;;
            crontab)
            crontab=`parameter_value "$LINE"`
            ;;
            setup_script)
            setup_script=`parameter_value "$LINE"`
            ;;
            gayaui_path)
            gayaui_path=`parameter_value "$LINE"`
            ;;
            webui_path)
            webui_path=`parameter_value "$LINE"`
            ;;
        esac
    done < "$1"
}



app_autoinstall()
{
    if [ -n "$setup_script" ] && [ -f "$path/$setup_script" ] && [ ! -f "$path/.installed" ]; then
        app_setup_execute install
    fi
}


app_startstate_isstarted()
{
    if [ -n "`cat "$APPINIT_AUTOSTART_STATE" 2>/dev/null | grep "#${name}#"`" ]; then
        return 1
    else
        return 0
    fi
}


app_startstate_add()
{
    app_startstate_isstarted
    if [ "$?" == "0" ]; then
        echo "#${name}#" >> $APPINIT_AUTOSTART_STATE
    fi
}


app_startstate_remove()
{
    TEMP="`cat "$APPINIT_AUTOSTART_STATE"`"
    echo "$TEMP" | grep -v "#${name}#" > "$APPINIT_AUTOSTART_STATE"
}


app_process()
{
    for appinfo in `ls -1d $APPS_FOLDER/*/appinfo.json 2>/dev/null`
    do
        app_appinfo_parse "$appinfo"
        
        if  [ "$APPS_MINIMAL_APPINFO_VERSION" -le "$appinfo_format" ]; then
            if [ -z "$2" ] || [ "$2" == "$name" ]; then 
                if [ "$enabled" == "1" ]; then
                    app_fixpermissions "$path"
                    cd "$path"
                
                    case "$1" in
                        start)
                        echo -n "Starting $name: "
                        app_startstate_isstarted
                        if [ "$?" == "0" ]; then
                            app_autoinstall
                            websites_add
                            crontab_add
                            app_daemon_execute "$1"
                            app_startstate_add
                            echo "Done"
                        else
                            echo "Already started"
                        fi
                        ;;
                    
                        stop)
                        echo -n "Stopping $name: "
                        app_startstate_isstarted
                        if [ "$?" == "1" ]; then
                            websites_remove
                            crontab_remove "$name"
                            app_daemon_execute "$1"
                            app_startstate_remove
                            echo "Done"
                        else
                            echo "Already stopped"
                        fi
                        ;;
     
                        rescan)
                        if [ -n "$2" ]; then
                            echo -n "Rescanning $name: "
                            app_startstate_isstarted
                            if [ "$?" == "1" ]; then
                                websites_remove
                                crontab_remove "$name"
                                websites_add
                                crontab_add
                                echo "Done"
                            else
                                echo "Application not started"
                            fi
                        fi
                        ;;
                        
                        uninstall)
                        if [ -n "$2" ]; then
                            echo "Uninstalling $name: "
                            crontab_remove "$name"
                            websites_remove
                            app_startstate_isstarted
                            if [ "$?" == "1" ]; then
                                app_daemon_execute stop
                            fi
                            app_startstate_remove
                            app_setup_execute "$1"
                            rm -Rf "$path"
                            echo "Done"
                        fi
                        ;;
                        
                        info)
                        escapedpath=`echo "$path" | sed 's/\\//\\\\\\//g'`
                        cat "$path/appinfo.json" | sed "s/{/{\n    path=\"$escapedpath\",/g"
                        echo
                        ;;
                    esac
                else
                    echo -n "Disabeling application $name: "
                    websites_remove
                    crontab_remove "$name"
                    app_startstate_isstarted
                    if [ "$?" == "1" ]; then
                        app_daemon_execute stop
                    fi
                    app_startstate_remove
                    echo "Done"
                fi
            fi
        fi
    done
}


app_install_fromfile()
{
    echo -n "Installing application from file $1: "
    rm -Rf "$APPINIT_PROFILE/temp" >/dev/null 2>/dev/null
    mkdir -p "$APPINIT_PROFILE/temp"
    cd "$APPINIT_PROFILE/temp"
    
    if [ -n "`echo "$1" | grep "\.rar$"`" ]; then
        eval "$UNRAR x \"$1\" >/dev/null 2>/dev/null"
    fi
    
    if [ -n "`echo "$1" | grep "\.tar$"`" ]; then
        eval "$TAR xvf \"$1\" >/dev/null 2>/dev/null"
    fi
    
    
    if [ -f "$APPINIT_PROFILE/temp/appinfo.json" ]; then
        app_appinfo_parse "$APPINIT_PROFILE/temp/appinfo.json"

        if [ -d "$APPS_FOLDER/$name" ]; then
            echo -n "(and uninstalling current) "
            eval "$APPINIT_PROFILE/$APPINIT_FILENAME uninstall $name >/dev/null 2>/dev/null"
            rm -Rf "$APPS_FOLDER/$name" >/dev/null 2>/dev/null
        fi

        rm -Rf "$APPS_FOLDER/$name"
        mv "$APPINIT_PROFILE/temp" "$APPS_FOLDER/$name"
    fi
    rm -Rf "$APPINIT_PROFILE/temp" >/dev/null 2>/dev/null
    
    if [ -n "$name" ]; then
        echo "Done"
    else
        echo "Failed"
        exit 1
    fi
}


echo
echo
echo "$APPINIT_NAME version $APPINIT_VERSION"
echo "---------------------------------------"


auto_upgrade "$1" "$2"
appinit_profile_create
webserver_add

#Fix webcall parameters
if [ -n "$1" ] && [ -z "$2" ] && [ -n "`echo "$1" | grep "&"`" ] ; then
    vars="`echo "$1" | sed "s/\&/ /g" | sed "s/%2F/\//g"`"
    set `echo "$vars" `
fi

if [ "$APPINIT_APPS_AUTOSTART" == "1" ]; then
    appinit_autostart_add
else
    appinit_autostart_remove
fi

if [ "$APPINIT_APPS_BOOTSTART" == "1" ]; then
    appinit_bootstart_add
else
    appinit_bootstart_remove
fi


case "$1" in
    rescan)
    app_process "$1" "$2"
    ;;

    start)
    app_process "$1" "$2"
    ;;
    
    stop)
    app_process "$1" "$2"
    ;;
    
    restart)
    app_process stop "$2"
    app_process start "$2"
    ;;
    
    uninstall)
    app_process "$1" "$2"
    ;;
    
    install)
    if [ -n "$2" ]; then
        app_install_fromfile "$2"
        app_process start ""$name""
    fi
    ;;
    
    info)
    echo "Content-type: text/html\n\n"
    app_process "$1" "$2"
    cat "/tmp/appinit_state"
    ;;
    
    *)
    echo
    echo "Usage: "
    echo "# $APPINIT_FILENAME {start|stop|restart|install|uninstall|rescan} [application name]"
    echo "    [application name] is not optional for install, uninstall and rescan"
    echo "    this will control already installed applications."
    echo
    echo "# $APPINIT_FILENAME {filename} (tar or rar)"
    echo "    This will install a application from tar or rar file."
    echo
    echo "# $APPINIT_FILENAME info"
    echo "    Will display some systems stats, to be used by CSI."

    ;;
esac

if [ "$CRONTAB_RELOAD" == "1" ]; then
    #reload updated crontab
    crontab "$APPINIT_PROFILE/crontab" -u nmt
    CRONTAB_RELOAD="0"
fi

echo