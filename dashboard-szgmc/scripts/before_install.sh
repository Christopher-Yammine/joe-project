#!/bin/bash
APP_DIR="/opt/dashboard-szgmc"
BACKUP_DIR="/opt/dashboard-szgmc_backup"

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup the entire app directory
rsync -av $APP_DIR/ $BACKUP_DIR/

# Remove the entire app directory including node_modules
rm -rf $APP_DIR

# Create app directory
mkdir -p $APP_DIR