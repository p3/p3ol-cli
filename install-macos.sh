#!/bin/sh
link="https://github.com/p3/p3ol-cli/raw/main/builds/macos/p3ol"
curl --fail -OL "$link"
if [ $? -ne 0 ]; then
    exit 1
fi

chmod 744 p3ol
