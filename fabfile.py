import json
import requests

from fabric.api import *
from fabric.operations import local

def update_event_data():
    
    resp = requests.get('https://go.berniesanders.com/page/event/search_results?format=json&wrap=no&orderby[0]=date&orderby[1]=desc&event_type=13&mime=text/json&limit=4000&country=*')
    print "Request complete."
    
    data = json.loads(resp.text)
    print "JSON loaded."

    global rsvp_count
    rsvp_count = 0

    def clean_result(row):
        global rsvp_count
        for key in ['description', 'closed_msg', 'distance', 'url']:
            if key in row:
                del row[key]

        location_fields = filter(lambda x: x in row, ['venue_name', 'venue_addr1', 'venue_city', 'venue_state_cd', 'venue_zip'])
        row['location'] = " ".join(row[key] for key in location_fields)
        
        for key in ['venue_name', 'venue_addr1', 'venue_city', 'venue_state_cd']:
            if key in row:
                del row[key]

        # not sure we need these?
        for key in ['type_id', 'timezone', 'is_official', 'event_type_name']:
            if key in row:
                del row[key]

        rsvp_count += row['attendee_count']
        return row


    data_out = {'results': map(clean_result, data['results'])}

    print "JSON cleaned! %s events." % len(data['results'])

    data['settings']['rsvp'] = rsvp_count

    data['settings']['count'] = 3146

    data_out['settings'] = data['settings']    

    json_dump = json.dumps(data)
    json_dump = "window.JULY_29_EVENT_DATA = " + json_dump

    print "Writing data..."

    outfile = open('js/bern-july-29-data.js', 'w')
    outfile.write(json_dump)
    outfile.close()

    print "Done! GZipping..."

    local('cd js; gzip < bern-july-29-data.js > bern-july-29-data.gz')


def deploy_event_data():
    update_event_data()
    local("aws s3 cp js/bern-map-async.gz s3://map.berniesanders.com/js/bern-map-async.gz --metadata-directive REPLACE --content-encoding \"gzip\" --content-type \"text/javascript\" --region \"us-west-2\"")
    # todo: clear cache invalidation



def zip_javascript():
    local('cd js; gzip < bern-map-async.js > bern-map-async.gz')


def deploy():
    local("aws s3 cp . s3://map.berniesanders.com/ --recursive --exclude \"fabfile.py*\" --exclude \".git*\" --exclude \"*.sublime-*\" --exclude \".DS_Store\" --region \"us-west-2\"")
    local("aws s3 cp . s3://map.berniesanders.com/ --exclude \"*\" --include \"*.gz\" --recursive --metadata-directive REPLACE --content-encoding \"gzip\" --region \"us-west-2\"")
    local("aws s3 cp . s3://map.berniesanders.com/ --exclude \"*\" --include \"js/*.gz\" --recursive --metadata-directive REPLACE --content-encoding \"gzip\" --content-type \"text/javascript\" --region \"us-west-2\"")
    local("aws s3 cp . s3://map.berniesanders.com/ --exclude \"*\" --include \"d/us_postal_codes.gz\" --recursive --metadata-directive REPLACE --content-encoding \"gzip\" --content-type \"text/csv\" --region \"us-west-2\"")
