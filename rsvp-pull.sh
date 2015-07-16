#! /bin/bash
DATE=`date +%Y-%m-%d:%H:%M:%S`
HEROKU_FOLDER=/Library/D3/Projects/php-getting-started
HEROKU_GIT_FOLDER=/Library/D3/Projects/php-getting-started/web
BERNIE_EVENTS_FOLDER=/Library/Php/Projects/Microsites/bernie-events
BERNIE_DATA_FOLDER=/Library/Php/Projects/Microsites/bernie-events/d

echo "--------------------------------------------"
echo "$DATE | Bernie 2016 Events Data Pull - START"
echo "--------------------------------------------"

cd $BERNIE_DATA_FOLDER
curl "https://go.berniesanders.com/page/event/search_results?format=json&wrap=no&orderby\[0\]=date&orderby\[1\]=desc&event_type=13&mime=text/json&limit=3000&country=*" > july29.json
git add "july29.json"
git commit -m "Updating july29 data"
git push origin master


echo "--------------------------------------------"
echo "$DATE | Bernie 2016 Events Data Scraped. Pushing to Server"
echo "--------------------------------------------"

cd $HEROKU_GIT_FOLDER
sudo git pull origin master


echo "--------------------------------------------"
echo "$DATE | Pulled latest Deploying"
echo "--------------------------------------------"

cd $HEROKU_FOLDER
sudo git add web/d/july29.json
sudo git commit -m "Update july 29 data"

echo "--------------------------------------------"
echo "$DATE | Committed code "
echo "--------------------------------------------"

sudo git push heroku master

echo "--------------------------------------------"
echo "$DATE | Bernie 2016 Events Data Pull - END"
echo "--------------------------------------------"
