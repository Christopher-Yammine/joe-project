#!/bin/bash
# Check if the "obsrvr" app is running in PM2

cd /opt/dashboard-szgmc

# Check if the "obsrvr" app is running in PM2
if pm2 list | grep 'dashboard-szgmc' > /dev/null; then
  echo "Stopping the 'dashboard-szgmc' PM2 process..."
  pm2 stop dashboard-szgmc
else
  echo "No 'dashboard-szgmc' PM2 process found running."
fi