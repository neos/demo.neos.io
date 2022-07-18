#!/usr/bin/env bash

# Update database schema
./flow doctrine:migrate

# Create admin user if it does not exist yet
if [ -z "${NEOS_ADMIN_USERNAME:-}" ]
then
  echo "Not setting up admin user, NEOS_ADMIN_USERNAME not set"
else
  ./flow user:list | grep -q $NEOS_ADMIN_USERNAME || (./flow user:create $NEOS_ADMIN_USERNAME $NEOS_ADMIN_PASSWORD Admin User; ./flow user:addrole $NEOS_ADMIN_USERNAME Administrator)
fi

# Flush the content cache on each deployment
/application/flow flow:cache:flush Neos_Fusion_Content

# publish resources like images aand css to be useable by browsers
./flow resource:publish