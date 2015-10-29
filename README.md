# Event Map

## Getting started

To run this, simply run a simple local server.  Here's an example if you have Python installed:

`python -c 'from SimpleHTTPServer import test; test()' 6000`

Then go to `http://localhost:6000` and you'll see the map.

## Deploying

1. Set the AWS keys as environment variables.  You'll need to ask Saikat or Rapi for these.
2. Run `mkvirtualenv bernieevents`
3. Run `fab deploy`
