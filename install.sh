#!/bin/sh
link="https://github.com/joecampo/retro-aol-cli/raw/main/builds/reaol"
curl --fail -OL "$link"
if [ $? -ne 0 ]; then
    exit 1
fi

chmod 744 reaol
