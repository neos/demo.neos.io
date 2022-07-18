#!/bin/bash

CURRENT_DATE=`date +"%Y-%m-%d"`
CURRENT_DATE_ALT=`date +"%y-%m-%d"`
CURRENT_TIME=`date +"%H:%M:%S"`
CURRENT_DAY=`date +"%d"`
CURRENT_HOUR=`date +"%H"`
YESTERDAY_DATE=`date -ud"${CURRENT_DATE} -1 days" +"%d.%m.%Y"`
DAY_OF_WEEK=`date +"%u"`

if [ "${DAY_OF_WEEK}" == "1" ] && [ "${CURRENT_HOUR}" == "04" ] ; then
  # empty all symlinks to resources and resources themselves
  rm -r /application/Web/_Resources/Persistent/
  rm -r /application/Data/Persistent/Resources/

  # truncate tables
  mysql -Nse 'SET FOREIGN_KEY_CHECKS=0'
  mysql -Nse 'SHOW TABLES' | while read table; do mysql -e "TRUNCATE TABLE $table"; done
  mysql -Nse 'SET FOREIGN_KEY_CHECKS=1'

  #rebuild application
  /application/flow doctrine:migrate
  /application/flow site:import --package-key=Neos.Demo
  /application/flow user:list | grep -q $NEOS_ADMIN_USERNAME || (./flow user:create $NEOS_ADMIN_USERNAME $NEOS_ADMIN_PASSWORD Admin User; ./flow user:addrole $NEOS_ADMIN_USERNAME Administrator)
  /application/flow resource:publish
fi
