#!/usr/bin/env bash

# INSTRUCTIONS: MAKE THIS FILE EXECUTABLE TO USE: chmod +x ./dev.sh

### Set environment variables for dev or CLI

# Specific to Ubuntu
#export XDEBUG_HOST=$(hostname -I)
# Specific to Mac. For some machines it may be en1 instead of en0
export XDEBUG_HOST=$(ipconfig getifaddr en0)
# Look for the $APP_ENV or default to "local"
export APP_ENV=${APP_ENV:-local}
# THE FOLLOWING WILL OVERRIDE THE DOCKER COMPOSE ENVIRONMENT PARAMETERS DEFINED IN THE docker-compose .env
export DB_PORT=${DB_PORT:-33063}
export DB_ROOT_PASSWORD=${DB_ROOT_PASSWORD:-root}
export DB_USER=${DB_USER:-root}
export DB_PASSWORD=${DB_PASSWORD:-root}

## DECIDE docker-compose FILE TO USE

# Default to docker-compose.dev.yml
#COMPOSE_FILE="dev"

# Do NOT pass a pseudo-tty so it does not becaome interactive
#   PHPUnit will throw an error if it is run with tty (interactive)
# Disable pseudo-TTY allocation for CI (Jenkins)
TTY=""

# If $BUILD_NUMBER is defined
if [ ! -z "$BUILD_NUMBER" ]; then
  # COMPOSE_FILE="ci"
  # Run as NPN-interactive
  TTY="-T"
fi

#COMPOSE="docker-compose -f docker-compose.$COMPOSE_FILE.yml"

COMPOSE="docker-compose"

## DEFINE CASES FOR COMMAND PARAMETERS:

if [ $# -gt 0 ]; then

  if [ "$1" == "start" ]; then
      $COMPOSE up -d $TTY

  elif [ "$1" == "stop" ]; then
      $COMPOSE down $TTY

  elif [ "$1" == "run" ]; then
      # Run docker-compose commands inside the working directory.
      # docker-compose run SPINS UP A NEW CONTAINER. This is not the case with docker-compose exec (it re-uses the existing one)
      $COMPOSE run --rm $TTY \
          -w /var/www "$@"

  # If "art" is used, pass-through "artisan" inside the container
  elif [ "$1" == "artisan" ] || [ "$1" == "art" ]; then
      # shift 1 to get all the parameters except the first with "$@" furtner down
      # Run Laravel artisan commands
      shift 1
      # -w: use the working directory defined in docker-compose.yml
      # the name of the container running the application defined in docker-compose.yml: "app"
      $COMPOSE exec \
          app \
          php artisan "$@"

  # If "composer" is used, pass-through "composer" inside the container
  elif [ "$1" == "composer" ] || [ "$1" == "comp" ]; then
      shift 1
      $COMPOSE run --rm $TTY \
          app \
          composer "$@"
  # If "test" is used, pass-through "test" inside the container
  elif [ "$1" == "test" ]; then
      shift 1
      $COMPOSE exec \
          app \
          ./vendor/bin/phpunit "$@"

  # We can speed up our tests by not having Docker spin up a new container each time we run PHPUnit.
  #   Instead, if we have our containers running, we can use the existing app service.
  #   This makes use of the exec command instead of the run command
  elif [ "$1" == "t" ]; then
      shift 1
      $COMPOSE exec \
          app \
          sh -c "cd /var/www && ./vendor/bin/phpunit $@"

  # If "npm" is used, run npm from our node container
  elif [ "$1" == "npm" ]; then
      shift 1
      $COMPOSE run --rm $TTY \
          node \
          npm "$@"

  # If "gulp" is used, run gulp from our node container
  # Make sure npm is installed using: ./ot.sh npm install
  elif [ "$1" == "gulp" ]; then
      shift 1
      $COMPOSE run --rm $TTY \
          node \
          ./node-modules/.bin/gulp "$@"

  # If "ssh" is used:
  elif [ "$1" == "ssh" ]; then
      shift 1
      $COMPOSE exec app bash

  elif [ "$1" == "db" ]; then
      shift 1
      $COMPOSE exec otbsdb bash

  elif [ "$1" == "redis" ]; then
      shift 1
      docker exec -it lararedis redis-cli

  # Unnecessary entry, just for clarity
  elif [ "" == "check" ]; then
      shift 1
      $COMPOSE check

  # Else, pass-through args to docker-compose
  else
    $COMPOSE "$@"
  fi
else
  $COMPOSE ps
fi