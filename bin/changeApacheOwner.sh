#!/usr/bin/env bash
#Params are root dir
PATH=$1
OWNER=${2:-jenkins}

#proof if path exists
if [ ! -d "$PATH" ]
  then
     echo Path \"$PATH\" not found.
     echo Exit.
     exit 1
  else
     echo found "$PATH".
fi

echo "change recursive owner to \"www-data:www-data\" of \"$PATH\"."
/usr/bin/sudo /bin/chown www-data:www-data -R "$PATH"

exit 0
