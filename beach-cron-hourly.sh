#!/bin/bash

CURRENT_DATE=`date +"%Y-%m-%d"`
CURRENT_DATE_ALT=`date +"%y-%m-%d"`
CURRENT_TIME=`date +"%H:%M:%S"`
CURRENT_DAY=`date +"%d"`
CURRENT_HOUR=`date +"%H"`
CURRENT_TIMESTAMP=`date +"%s"`
YESTERDAY_DATE=`date -ud"${CURRENT_DATE} -1 days" +"%d.%m.%Y"`
DAY_OF_WEEK=`date +"%u"`

if [ "${DAY_OF_WEEK}" == "1" ] && [ "${CURRENT_HOUR}" == "03" ] ; then
  # empty all symlinks to resources and resources themselves
  rm -r /application/Web/_Resources/Persistent/
  rm -r /application/Data/Persistent/Resources/
  rm /application/Data/Logs/Exceptions/*.txt
  rm /application/Data/Logs/*.log

  # truncate tables
  mysql -Nse 'SHOW TABLES' | while read table; do mysql -e "SET FOREIGN_KEY_CHECKS=0; DROP TABLE $table; SET FOREIGN_KEY_CHECKS=0;"; done

  #rebuild application
  /application/flow doctrine:migrate
  /application/flow site:import --package-key=Neos.Demo
  /application/flow user:create $NEOS_ADMIN_USERNAME $NEOS_ADMIN_PASSWORD Admin User
  /application/flow user:addrole $NEOS_ADMIN_USERNAME Administrator
  /application/flow resource:publish
fi
