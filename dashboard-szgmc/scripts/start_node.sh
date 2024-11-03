#!/bin/bash

cd /opt/dashboard-szgmc

# install if any dependence  
npm ci --only=production
# Start or restart the "dashboard-szgmc" app on port 3002 using PM2
pm2 restart dashboard-szgmc || pm2 start npm --name dashboard-szgmc -- start

# Ensure PM2 saves the process so it restarts on reboot
pm2 save
