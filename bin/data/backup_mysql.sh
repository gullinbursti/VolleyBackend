DATE=`/bin/date +%Y%m%d%H`; /usr/bin/mysqldump -u root -Feq --single-transaction --databases hotornot-dev > /home/shane/mysqlbackups/xbackup_hotornot_$DATE.sql; gzip -f /home/shane/mysqlbackups/xbackup_hotornot_$DATE.sql;

