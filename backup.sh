#!/bin/bash
export BASE_DIR=/repo
# backup database if enabled
export DB_BACKUP_ENABLED=`php $BASE_DIR/src/get_info_from_config.php db_backup_enabled`
if [[ "$DB_BACKUP_ENABLED" = "1" ]]; then
    export DB_NAME=`php $BASE_DIR/src/get_info_from_config.php db_name`
    export DB_CMD=`php $BASE_DIR/src/get_info_from_config.php db_cmd`
    export DB_BACKUP_DIR=`php $BASE_DIR/src/get_info_from_config.php db_backup_dir`
    export DB_BACKUP_FN=$DB_BACKUP_DIR/$DB_NAME.sql
    export DB_DAILY_FN=$DB_BACKUP_DIR/$DB_NAME_`date +%a`.sql
    export DB_MONTHLY_FN=$DB_BACKUP_DIR/$DB_NAME_`date +%b`.sql
    # create current, daily and monthly backups
    $DB_CMD >$DB_BACKUP_FN
    cp $DB_BACKUP_FN $DB_DAILY_FN
    cp $DB_BACKUP_FN $DB_MONTHLY_FN
fi
# backup HTML
export HTML_DIR=`php $BASE_DIR/src/get_info_from_config.php html_dir`
export BACKUP_DIR=`php $BASE_DIR/src/get_info_from_config.php backup_dir`
export BACKUP_FN=$BACKUP_DIR/templates.zip
export BACKUP_DAILY_FN=$BACKUP_DIR/templates_`date +%Y_%a`.zip
export BACKUP_MONTHLY_FN=$BACKUP_DIR/templates_`date +%Y_%b`.zip
zip -r $BACKUP_FN $HTML_DIR/*
cp $BACKUP_FN $BACKUP_DAILY_FN
cp $BACKUP_FN $BACKUP_MONTHLY_FN
