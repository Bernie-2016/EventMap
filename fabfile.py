import datetime
import hashlib
import hmac
import json
import os
import pytz
import random
import string
import sys

import requests

from fabric.api import *
from fabric.operations import local
from string import Template


def update_office_locations():

    print "started downloading Campaign Offices"

    local('cd d; curl "https://docs.google.com/spreadsheets/d/1hJadb6JyDekHf5Vzx-77h7sdJRCOB01XUPvEpKIckDs/pub?gid=0&single=true&output=csv" > campaign-offices.csv')

    local('cd d; curl "https://docs.google.com/spreadsheets/d/1rRexu31MYdff4PLwgPW1A8TMXkFBLmBxU444khLhWaQ/pub?gid=0&single=true&output=csv" > go-the-distance.csv')
    
    print "Finished Downloading campaign offices"


def update_go_the_distance_offices():

    print "started downloading Go the Distance Offices"

    local('cd d; curl "https://sheetsu.com/apis/b835e696" > go-the-distance-offices.csv')

    print "Finished Downloading Go the Distance offices"


def update_event_data():

    eastern = pytz.timezone('US/Eastern')

    start_date = int((eastern.localize(datetime.datetime.now().replace(hour=0, minute=0, second=0, microsecond=0), is_dst=None) - datetime.datetime(1970, 1, 1, tzinfo=pytz.utc)).total_seconds())
    end_date = int((eastern.localize(datetime.datetime.now().replace(hour=0, minute=0, second=0, microsecond=0) + datetime.timedelta(days=21), is_dst=None) - datetime.datetime(1970, 1, 1, tzinfo=pytz.utc)).total_seconds() - 1)

    events_url = 'http://go.berniesanders.com/page/event/search_results?country=US&date_start=%(start_date)s&limit=7000&format=json' % {'start_date': start_date, 'end_date': end_date}

    print "Fetching events from %s" % events_url

    resp = requests.get(events_url)
    print "Request complete."

    data = json.loads(resp.text)
    print "JSON loaded."

    global rsvp_count
    rsvp_count = 0

    def clean_result(row):
        global rsvp_count
        for key in ['description', 'closed_msg', 'distance']:
            if key in row:
                del row[key]

        location_fields = filter(lambda x: x in row, ['venue_name', 'venue_addr1', 'venue_city', 'venue_state_cd', 'venue_zip'])
        row['location'] = " ".join(row[key] for key in location_fields)

        for key in ['venue_name', 'venue_addr1', 'venue_city', 'venue_state_cd']:
            if key in row:
                del row[key]

        # not sure we need these?
        for key in ['type_id', 'timezone']:
            if key in row:
                del row[key]

        # rsvp_count += row['depl_count']
        rsvp_count += row['attendee_count'] if 'attendee_count' in row else 0
        return row

    def remove_the_mormons(row):
        if 'id_obfuscated' in row:
            if row['id_obfuscated'] == '4vxqd':
                return False
        return True

    data_out = {'results': map(clean_result, filter(remove_the_mormons, data['results']))}


    data['settings']['rsvp'] = rsvp_count
    data['settings']['count'] = 3520 # hax.

    print "JSON cleaned! %s events, %s RSVP's." % (len(data['results']), data['settings']['rsvp'])

    data_out['settings'] = data['settings']

    json_dump = json.dumps(data)
    eventsjson = json.dumps(data)

    jsonfile = open('d/events.json', 'w')
    jsonfile.write(eventsjson)
    jsonfile.close()

    json_dump = "window.EVENT_DATA = " + json_dump

    print "Writing data..."

    outfile = open('js/event-data.js', 'w')
    outfile.write(json_dump)
    outfile.close()

    print "Done! GZipping..."

    local('cd js; gzip < event-data.js > event-data.gz')


def deploy_event_data():
    update_event_data()
    update_office_locations()
    update_go_the_distance_offices()

    local("aws s3 cp js/event-data.gz s3://map.berniesanders.com/js/event-data.gz --metadata-directive REPLACE --content-encoding \"gzip\" --content-type \"text/javascript\" --region \"us-west-2\"")
    local("aws s3 cp d/events.json s3://map.berniesanders.com/d/events.json --metadata-directive REPLACE --content-type \"text/plain\" --region \"us-west-2\"")
    local("aws s3 cp d/campaign-offices.csv s3://map.berniesanders.com/d/campaign-offices.csv --metadata-directive REPLACE --content-type \"text/plain\" --region \"us-west-2\"")
    local("aws s3 cp d/go-the-distance.csv s3://map.berniesanders.com/d/go-the-distance.csv --metadata-directive REPLACE --content-type \"text/plain\" --region \"us-west-2\" --acl \"public-read\"")
    local("aws s3 cp d/go-the-distance-offices.csv s3://map.berniesanders.com/d/go-the-distance-offices.csv --metadata-directive REPLACE --content-type \"text/plain\" --region \"us-west-2\" --acl \"public-read\"")

    invalidate_cloudfront_event_cache()


def invalidate_cloudfront_event_cache():
    filein = open('cache-invalidation-tpl.xml')
    src = Template( filein.read() )
    invalidation_id = ''.join(random.choice(string.ascii_uppercase + string.digits) for _ in range(12))
    payload = src.substitute({'invalidation_id': invalidation_id})
    invalidate_cloudfront_cache(payload)


def zip_javascript():
    local('cd js; gzip < bern-map-async.js > bern-map-async.gz')


def deploy():
    local("aws s3 cp . s3://map.berniesanders.com/ --recursive --exclude \"fabfile.py*\" --exclude \".git*\" --exclude \"*.sublime-*\" --exclude \".DS_Store\" --exclude \"js/event-data.gz\" --region \"us-west-2\"")
    local("aws s3 cp . s3://map.berniesanders.com/ --exclude \"*\" --include \"*.gz\" --exclude \"js/event-data.gz\" --recursive --metadata-directive REPLACE --content-encoding \"gzip\" --region \"us-west-2\"")
    local("aws s3 cp . s3://map.berniesanders.com/ --exclude \"*\" --include \"js/*.gz\" --exclude \"js/event-data.gz\" --recursive --metadata-directive REPLACE --content-encoding \"gzip\" --content-type \"text/javascript\" --region \"us-west-2\"")
    local("aws s3 cp . s3://map.berniesanders.com/ --exclude \"*\" --include \"d/us_postal_codes.gz\" --exclude \"js/event-data.gz\" --recursive --metadata-directive REPLACE --content-encoding \"gzip\" --content-type \"text/csv\" --region \"us-west-2\"")
    invalidate_cloudfront_cache_from_last_commit()

def sign(key, msg):
    return hmac.new(key, msg.encode("utf-8"), hashlib.sha256).digest()

def getSignatureKey(key, date_stamp, regionName, serviceName):
    kDate = sign(('AWS4' + key).encode('utf-8'), date_stamp)
    kRegion = sign(kDate, regionName)
    kService = sign(kRegion, serviceName)
    kSigning = sign(kService, 'aws4_request')
    return kSigning


def invalidate_cloudfront_cache_from_last_commit():
    pass


def invalidate_cloudfront_cache(payload):
    method = 'POST'
    service = 'cloudfront'
    host = 'cloudfront.amazonaws.com'
    region = 'us-east-1'
    path = "/2015-04-17/distribution/%s/invalidation" % ("E2SIHVDQUPIR5Z")
    endpoint = "https://cloudfront.amazonaws.com%s" % path
    content_type = 'text/xml'

    # Read AWS access key from env. variables or configuration file. Best practice is NOT
    # to embed credentials in code.
    access_key = os.environ.get('AWS_ACCESS_KEY_ID')
    secret_key = os.environ.get('AWS_SECRET_ACCESS_KEY')
    if access_key is None or secret_key is None:
        print 'No access key is available.'
        sys.exit()

    # Create a date for headers and the credential string
    t = datetime.datetime.utcnow()
    amz_date = t.strftime('%Y%m%dT%H%M%SZ')
    date_stamp = t.strftime('%Y%m%d') # Date w/o time, used in credential scope


    # ************* TASK 1: CREATE A CANONICAL REQUEST *************
    # http://docs.aws.amazon.com/general/latest/gr/sigv4-create-canonical-request.html

    # Step 1 is to define the verb (GET, POST, etc.)--already done.

    # Step 2: Create canonical URI--the part of the URI from domain to query
    # string (use '/' if no path)
    canonical_uri = path

    ## Step 3: Create the canonical query string. In this example, request
    # parameters are passed in the body of the request and the query string
    # is blank.
    canonical_querystring = ''

    # Step 4: Create the canonical headers. Header names and values
    # must be trimmed and lowercase, and sorted in ASCII order.
    # Note that there is a trailing \n.
    canonical_headers = 'content-type:' + content_type + '\n' + 'host:' + host + '\n' + 'x-amz-date:' + amz_date + '\n'

    # Step 5: Create the list of signed headers. This lists the headers
    # in the canonical_headers list, delimited with ";" and in alpha order.
    # Note: The request can include any headers; canonical_headers and
    # signed_headers include those that you want to be included in the
    # hash of the request. "Host" and "x-amz-date" are always required.
    # For DynamoDB, content-type and x-amz-target are also required.
    signed_headers = 'content-type;host;x-amz-date'

    # Step 6: Create payload hash. In this example, the payload (body of
    # the request) contains the request parameters.
    payload_hash = hashlib.sha256(payload).hexdigest()

    # Step 7: Combine elements to create create canonical request
    canonical_request = method + '\n' + canonical_uri + '\n' + canonical_querystring + '\n' + canonical_headers + '\n' + signed_headers + '\n' + payload_hash


    # ************* TASK 2: CREATE THE STRING TO SIGN*************
    # Match the algorithm to the hashing algorithm you use, either SHA-1 or
    # SHA-256 (recommended)
    algorithm = 'AWS4-HMAC-SHA256'
    credential_scope = date_stamp + '/' + region + '/' + service + '/' + 'aws4_request'
    string_to_sign = algorithm + '\n' +  amz_date + '\n' +  credential_scope + '\n' +  hashlib.sha256(canonical_request).hexdigest()


    # ************* TASK 3: CALCULATE THE SIGNATURE *************
    # Create the signing key using the function defined above.
    signing_key = getSignatureKey(secret_key, date_stamp, region, service)

    # Sign the string_to_sign using the signing_key
    signature = hmac.new(signing_key, (string_to_sign).encode('utf-8'), hashlib.sha256).hexdigest()


    # ************* TASK 4: ADD SIGNING INFORMATION TO THE REQUEST *************
    # Put the signature information in a header named Authorization.
    authorization_header = algorithm + ' ' + 'Credential=' + access_key + '/' + credential_scope + ', ' +  'SignedHeaders=' + signed_headers + ', ' + 'Signature=' + signature

    # For DynamoDB, the request can include any headers, but MUST include "host", "x-amz-date",
    # "x-amz-target", "content-type", and "Authorization". Except for the authorization
    # header, the headers must be included in the canonical_headers and signed_headers values, as
    # noted earlier. Order here is not significant.
    # # Python note: The 'host' header is added automatically by the Python 'requests' library.
    headers = {'Content-Type':content_type,
               'Authorization':authorization_header,
               'X-Amz-Date':amz_date}


    # ************* SEND THE REQUEST *************
    print '\nBEGIN REQUEST++++++++++++++++++++++++++++++++++++'
    print 'Request URL = ' + endpoint

    r = requests.post(endpoint, data=payload, headers=headers)

    print '\nRESPONSE++++++++++++++++++++++++++++++++++++'
    print 'Response code: %d\n' % r.status_code
    print r.text
