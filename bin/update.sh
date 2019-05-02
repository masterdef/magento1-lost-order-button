rm -rf var/cache/*
/bin/systemctl reload php-fpm.service

mysqldump -u pdmngr_xps --password='daTee6ja' lostorderadmin_xps > lostorderadmin_xps-`date +'%Y%m'`.sql
du -s *.sql
