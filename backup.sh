#!/bin/bash

SITE="cwops.telegraphy.de"

echo "Backup of $SITE..." > /tmp/$SITE-backup

echo "Dumping data..." >> /tmp/$SITE-backup
DATE=$( date +%Y%m%d-%H%M )

USER=cwops
PASS=cwops
DB=CWops

mysqldump -u$USER -p$PASS $DB  | gzip > /home/fabian/sites/$SITE/backup/$SITE-daily-${DATE}.sql.gz

ls -l /home/fabian/sites/$SITE/backup/ >> /tmp/$SITE-backup

# sleep long enough to make sure the mtime +7 below only finds the stuff that's
# older than one week, not a file that may be *exactly* one week old
sleep 60

echo "Removing old files (7 days or older)" >> /tmp/$SITE-backup

find /home/fabian/sites/$SITE/backup/$SITE-daily*.sql.gz  \
    -mtime +7 -exec echo {} \;  >> /tmp/$SITE-backup

find /home/fabian/sites/$SITE/backup/$SITE-daily*.sql.gz  \
    -mtime +7 -exec rm {} \;  >> /tmp/$SITE-backup

cat /tmp/$SITE-backup | mail -s "CRON: $SITE backup" fabian@fkurz.net
