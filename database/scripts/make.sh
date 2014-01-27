#!/bin/bash

while getopts ":r:" opt; do
  case $opt in
    r)
      SQLSCRIPT=rebuild.sql
      ;;
    *)
      SQLSCRIPT=build.sql
      ;;
  esac
done

echo "Building new PHP Class Files from model..."
rm ../../classes/db_*
./create_classes.php ../creation/model.sql ../../classes/ >> /dev/null
echo "Done!"
echo ""
echo "Ready to build the database: $SQLSCRIPT"
mysql -u root -p < $SQLSCRIPT

echo "Done!"
