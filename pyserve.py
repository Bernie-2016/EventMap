#!/usr/bin/env python
import SimpleHTTPServer

class GZipFriendlyRequestHandler(SimpleHTTPServer.SimpleHTTPRequestHandler):
    def end_headers(self):
        self.send_my_headers()

        SimpleHTTPServer.SimpleHTTPRequestHandler.end_headers(self)

    def send_my_headers(self):
        MONKEYPATCHED_HEADERS = {
            '/d/us_postal_codes.gz': {
                'Content-Encoding': 'gzip',
                'Content-Type': 'text/csv'
            },
            '/js/event-data.gz': {
                'Content-Encoding': 'gzip',
                'Content-Type': 'text/javascript'
            },
            '/js/jquery.gz': {
                'Content-Encoding': 'gzip',
                'Content-Type': 'text/javascript'
            },
            '/js/d3.gz': {
                'Content-Encoding': 'gzip',
                'Content-Type': 'text/javascript'
            },
            '/js/deparam.min.gz': {
                'Content-Encoding': 'gzip',
                'Content-Type': 'text/javascript'
            },
            '/js/mapbox.gz': {
                'Content-Encoding': 'gzip',
                'Content-Type': 'text/javascript'
            },
        }

        if self.path in MONKEYPATCHED_HEADERS:
            for header, value in MONKEYPATCHED_HEADERS[self.path].iteritems():
                self.send_header(header, value)


if __name__ == '__main__':
    SimpleHTTPServer.test(HandlerClass=GZipFriendlyRequestHandler)
