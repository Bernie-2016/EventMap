//Create an event node
var Event = (function($) { return function(properties) {
      this.properties = properties;
      this.blip = null;
      this.listing = null;
      this.className = properties.event_type_name.replace(/[^\w]/ig,"-").toLowerCase();
      this.LatLng = [parseFloat(this.properties.latitude),
                     parseFloat(this.properties.longitude)];
      this.startTime = moment(this.properties.start_dt)._d;

      if (this.properties.capacity) {
        this.properties.capacity = parseInt(this.properties.capacity);
      }

      if (this.properties.attendee_count) {
        this.properties.attendee_count = parseInt(this.properties.attendee_count);
      }

      this.isFull = this.properties.attendee_count &&
          this.properties.capacity > 0 &&
          this.properties.attendee_count >= this.properties.capacity;

      this.render = function (distance) {
        var that = this;
        var moreThan5RSVP = that.properties.attendee_count && parseInt(that.properties.attendee_count) > 5 ? true : false;

        if (!that.properties.attendee_count) { moreThan5RSVP = false; }

        var datetime = moment(that.properties.start_dt).format("MMM DD (ddd) h:mma")

        var rendered = $("<div class='lato'/>")
          .addClass('event-item ' + that.className)
          .append($("<h5 class='time-info'/>").html((distance ? (distance + "mi &nbsp;&nbsp;") : "") + datetime))
          .append($("<h3/>").html("<a target='_blank' href='" + that.properties.url + "'>" + that.properties.name + "</a>"))
          .append($("<span/>").addClass("label-icon"))
          .append($("<h5 class='event-type'/>").text(that.properties.event_type_name))
          .append($("<p/>").text(that.properties.location))
          .append(
            $("<div class='social-area'/>")
              .addClass(moreThan5RSVP ? "more-than-5" : "")
              .append(
                $("<a class='rsvp-link' target='_blank'/>")
                  .attr("href", that.properties.url)
                  .text(that.isFull ? "FULL" : "RSVP")
              )
              .append(
                $("<span class='rsvp-count'/>").text(that.properties.attendee_count + " SIGN UPS")
              )
          );

        return rendered.html();
      };
    }
  })(jQuery);






/****
 *  MapManager proper
 */
var MapManager = (function($, d3, leaflet) {
  return (
    function(eventData, zipcodes, options) {

    var popup = L.popup();
    var options = options;
    var zipcodes = zipcodes.reduce(function(zips, item) { zips[item.zip] = item; return zips; }, {});
    var eventsList = eventData.map(function(d) { return new Event(d); });

    leaflet.mapbox.accessToken = "pk.eyJ1IjoiemFja2V4bGV5IiwiYSI6Ijc2OWFhOTE0ZDllODZiMTUyNDYyOGM5MTk1Y2NmZmEyIn0.mfl6MGaSrMmNv5o5D5WBKw";
    var mapboxTiles = leaflet.tileLayer('http://{s}.tiles.mapbox.com/v4/mapbox.streets/{z}/{x}/{y}.png?access_token=' + leaflet.mapbox.accessToken, { attribution: '<a href="http://www.openstreetmap.org/copyright" target="_blank">&copy; OpenStreetMap contributors</a>'});

    var centralMap =  new leaflet.Map("map", {
    center: [37.8, -96.9],
    zoom: 4}).addLayer(mapboxTiles);

    //initialize map
    var filteredEvents = [];
    var module = {};


    var _popupEvents = function(event) {
      var target = event.target._latlng;
      var filtered = eventsList.filter(function(d) {
        return target.lat == d.LatLng[0] && target.lng == d.LatLng[1];
      }).sort(function(a, b) { return a.startTime - b.startTime; });

      var div = $("<div />")
        .append(filtered.length > 1 ? "<h3 class='sched-count'>" + filtered.length + " Scheduled Events</h3>" : "")
        .append(
        $("<div class='popup-list-container'/>")
          .append($("<ul class='popup-list'>")
            .append(
              filtered.map(function(d) {
                return $("<li class='lato'/>").addClass(d.isFull?"is-full":"not-full").append(d.render());
              })
            )
          )
        );


      setTimeout(
        function() { L.popup()
          .setLatLng(event.target._latlng)
          .setContent(div.html())
          .openOn(centralMap);
        }
      , 100);
    };
    /***
     * Initialization
     */
    var initialize = function() {
      var uniqueLocs = eventsList.reduce(function(arr, item){
        if ( arr.indexOf(item.properties.latitude + "||" + item.properties.longitude) >= 0 ) {
          return arr;
        } else {
          arr.push(item.properties.latitude  + "||" +  item.properties.longitude);
          return arr;
        }
      }, []);

      uniqueLocs = uniqueLocs.map(function(d) {
        var split = d.split("||");
        return [parseFloat(split[0]), parseFloat(split[1])];
      });

      uniqueLocs.forEach(function(item) {
          L.circle(item, 100, { className: item.className, color: 'blue', stroke: '#33495A' })
            .on('click', function(e) { _popupEvents(e); })
            .addTo(centralMap);
      });
    };

    var toMile = function(meter) { return meter * 0.00062137; };

    var filterEvents = function (zipcode, distance, timespan) {
      var zipLatLng = leaflet.latLng([parseFloat(zipcode.lat), parseFloat(zipcode.lon)]);

      var filtered = eventsList.filter(function(d) {
        var dist = toMile(zipLatLng.distanceTo(d.LatLng));
        if (dist < distance) {
          d.distance = Math.round(dist*10)/10;
          return true;
        }
      });

      return filtered;
    };

    var sortEvents = function(filteredEvents, sortType) {
      switch (sortType) {
        case 'distance':

          filteredEvents = filteredEvents.sort(function(a,b) { return a.distance - b.distance; });
          break;
        case 'time':
          filteredEvents = filteredEvents.sort(function(a,b) { return a.startTime - b.startTime; });
          break;
      }

      // filteredEvents = filteredEvents.sort(function(a, b) {
      //   var aFull = a.isFull();
      //   var bFull = b.isFull();

      //   if (aFull && bFull) { return 0; }
      //   else if (aFull && !bFull) { return 1; }
      //   else if (!aFull && bFull) { return -1; }
      // });
      //sort by fullness;
      //..
      return filteredEvents;
    };

    initialize();

    module._eventsList = eventsList;
    module._zipcodes = zipcodes;

    /***
     * FILTER()  -- When the user submits query, we will look at this.
     */
    module.filter = function(zipcode, distance, sort) {
      //Filter event
      var targetZipcode = zipcodes[zipcode];

      //Remove list
      d3.select("#event-list")
        .selectAll("li").remove();

      if (targetZipcode == undefined || !targetZipcode) {
        $("#event-list").append("<li class='error lato'>Zipcode does not exist. <a href=\"https://go.berniesanders.com/page/event/search_results?orderby=zip_radius&zip_radius%5b0%5d=" + zipcode + "&zip_radius%5b1%5d=100&country=US&radius_unit=mi\">Try our events page</a></li>");
        return;
      }

      //Calibrate map
      var zoom = 4;
      switch(parseInt(distance))
      {
        case 5 : zoom = 12; break;
        case 10: zoom = 11; break;
        case 20: zoom = 10; break;
        case 50: zoom = 9; break;
        case 100: zoom = 8; break;
        case 250: zoom = 7; break;
      }
      centralMap.setView([parseFloat(targetZipcode.lat), parseFloat(targetZipcode.lon)], zoom);


      var filtered = filterEvents(targetZipcode, parseInt(distance));

      //Sort event
      filtered = sortEvents(filtered, sort);
      //Render event
      var eventList = d3.select("#event-list")
        .selectAll("li")
        .data(filtered, function(d){ return d.properties.id_obfuscated; });

        eventList.enter()
          .append("li")
          .attr("class", function(d) { return d.isFull ? 'is-full' : 'not-full' })
          .classed("lato", true)
          .html(function(d){ return d.render(d.distance); });

        eventList.exit().remove();

      //Push all full items to end of list
      $("div#event-list-container ul#event-list li.is-full").appendTo("div#event-list-container ul#event-list");

    };

    module.toMapView = function () {
      $("body").removeClass("list-view").addClass("map-view");
      centralMap.invalidateSize();
      centralMap._onResize();
    }
    module.toListView = function () {
      $("body").removeClass("map-view").addClass("list-view");
    }

    return module;
  });

})(jQuery, d3, L);
