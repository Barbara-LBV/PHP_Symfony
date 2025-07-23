#/bin/sh
curl -I $1 | grep "Location" | cut -d' ' -f2