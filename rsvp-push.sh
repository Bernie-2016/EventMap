#! /bin/bash
DATE=`date +%Y-%m-%d:%H:%M:%S`
HEROKU_FOLDER=/Library/D3/Projects/php-getting-started
HEROKU_GIT_FOLDER=/Library/D3/Projects/php-getting-started/web

echo "--------------------------------------------"
echo "$DATE | Bernie 2016 Events Data Scraped. Pushing to Server"
echo "--------------------------------------------"
cd $HEROKU_GIT_FOLDER
pwd
git pull origin master

echo "--------------------------------------------"
echo "$DATE | Pulled latest Deploying"
echo "--------------------------------------------"
sleep 2
cd $HEROKU_FOLDER
pwd
git add "web/d/july29.json"
sleep 2
git commit -m "Update july 29 data"

echo "--------------------------------------------"
echo "$DATE | Committed code "
echo "--------------------------------------------"
sleep 2
git push heroku master

echo "--------------------------------------------"
echo "$DATE | Bernie 2016 Events Data Push - END"
echo "--------------------------------------------"
