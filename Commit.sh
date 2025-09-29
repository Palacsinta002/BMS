#!/bin/bash
git add .
git commit -m "$(date '+%Y-%m-%d %H:%M:%S')"
git push
read -n 1 -p $'Press enter to continue...\n'
