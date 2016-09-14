#!/bin/sh

# to be run as a cron job
# script will do a mysqldump of the entire _EL & _EL_history database

DAY=$(date +%d) # get day of the month so that we can keep at least a month of changes

mysqldump --defaults-extra-file=/home/215537/users/.home/expresslims/.my.cnf -h internal-db.s215537.gridserver.com --databases db215537_EL db215537_EL > "/home/215537/users/.home/expresslims/backups/EL_mysqldump_$DAY.sql"

