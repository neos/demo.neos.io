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

# Setup caches
/application/flow flow:cache:setupall
# Remove current site and re-add it. This can be an issue for all current users
# but is needed when we change content.
/application/flow site:prune --site-node=neosdemo
/application/flow site:import --package-key=Neos.Demo
# Flush the content cache on each deployment
/application/flow flow:cache:flush Neos_Fusion_Content


# publish resources like images aand css to be useable by browsers
./flow resource:publish
