#/bin/sh
curl  -sSI $1 | grep "Location" | cut -d' ' -f2