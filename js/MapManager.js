//Create an event node
var Event = (function($) { return function(properties) {
      this.properties = properties;
      this.blip = null;
      this.listing = null;
      this.className = properties.event_type_name.replace(/[^\w]/ig,"-").toLowerCase();
      this.LatLng = [parseFloat(this.properties.latitude),
                     parseFloat(this.properties.longitude)];
      this.startTime = moment(this.properties.start_dt)._d;
      this.visible = true;

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
        var lat = that.properties.latitude
        var lon = that.properties.longitude

        var rendered = $("<div class='lato'/>")
          .addClass('event-item ' + that.className)
          .append($("<div />").addClass('event-item lato ' + that.className+'').attr("lat",lat).attr("lon",lon) //appended lat-lon attributes to this class for marker highlighting
            .append(that.properties.is_campaign_office ? $("<a class='office-image' href='" + that.properties.url + "' />").append($("<img src='" + that.properties.image + "'>")) : "")
            .append($("<h5 class='time-info'/>").html((distance ? (distance + "mi &nbsp;&nbsp;") : "") + datetime))
            .append($("<h3/>").html("<a target='_blank' href='" + that.properties.url + "'>" + that.properties.name + "</a>"))
            .append(that.properties.is_official ? $("<h5 class='official-tag'/>").text("Official Event") : "")
            .append($("<span/>").addClass("label-icon"))
            .append($("<h5 class='event-type'/>").text(that.properties.event_type_name))
            .append($("<p/>").text(that.properties.location))
            .append(that.properties.phone && that.properties.phone != "-" ? $("<p/>").text("Phone: " + that.properties.phone) : "")
            .append(that.properties.notes ? that.properties.notes : "")
            .append(
              $("<div class='social-area'/>")
                .addClass(moreThan5RSVP ? "more-than-5" : "")
                .append(
                  $("<a class='rsvp-link' target='_blank'/>")
                    .attr("href", that.properties.url)
                    .text(that.isFull ? "FULL" : that.properties.is_campaign_office ? "Get Directions" : "RSVP")
                )
                .append(
                  $("<span class='rsvp-count'/>").text(that.properties.attendee_count + " SIGN UPS")
                )
            )
          );
        rendered.onmouseover = function(){/*console.log("rawr") */}
        return rendered.html();
      };
    }
  })(jQuery); //End of events

// /****
//  *  Campaign Offices
//  */
// var CampaignOffices = (function($) { 
//   return function(properties) {
//     this.properties = properties;

//     this.render = function (distance) {
//         var that = this;
//         var moreThan5RSVP = that.properties.attendee_count && parseInt(that.properties.attendee_count) > 5 ? true : false;

//         if (!that.properties.attendee_count) { moreThan5RSVP = false; }

//         var datetime = moment(that.properties.start_dt).format("MMM DD (ddd) h:mma")

//         var rendered = $("<div class='lato'/>")
//           .addClass('event-item ' + that.className)
//           .append($("<h5 class='time-info'/>").html((distance ? (distance + "mi &nbsp;&nbsp;") : "") + datetime))
//           .append($("<h3/>").html("<a target='_blank' href='" + that.properties.url + "'>" + that.properties.name + "</a>"))
//           .append(that.properties.is_official ? $("<h5 class='official-tag'/>").text("Official Event") : "")
//           .append($("<span/>").addClass("label-icon"))
//           .append($("<h5 class='event-type'/>").text(that.properties.event_type_name))
//           .append($("<p/>").text(that.properties.location))
//           .append(
//             $("<div class='social-area'/>")
//               .addClass(moreThan5RSVP ? "more-than-5" : "")
//               .append(
//                 $("<a class='rsvp-link' target='_blank'/>")
//                   .attr("href", that.properties.url)
//                   .text(that.isFull ? "FULL" : "RSVP")
//               )
//               .append(
//                 $("<span class='rsvp-count'/>").text(that.properties.attendee_count + " SIGN UPS")
//               )
//           );

//         return rendered.html();
//       };
//   };
// })(jQuery);




/****
 *  MapManager proper
 */
var MapManager = (function($, d3, leaflet) {
  return (
    function(eventData, campaignOffices, zipcodes, options) {

    var allFilters = window.eventTypeFilters.map(function(i) { return i.id; });

    var popup = L.popup();
    var options = options;
    var zipcodes = zipcodes.reduce(function(zips, item) { zips[item.zip] = item; return zips; }, {});
    var current_filters = [], current_zipcode = "", current_distance = "", current_sort = "";

    var originalEventList = eventData.map(function(d) { return new Event(d); });
    var eventsList = originalEventList.slice(0);

    // var officeList = campaignOffices.map(function(d) { return new CampaignOffices(d); });

    leaflet.mapbox.accessToken = "pk.eyJ1IjoiemFja2V4bGV5IiwiYSI6Ijc2OWFhOTE0ZDllODZiMTUyNDYyOGM5MTk1Y2NmZmEyIn0.mfl6MGaSrMmNv5o5D5WBKw";
    var mapboxTiles = leaflet.tileLayer('http://{s}.tiles.mapbox.com/v4/mapbox.streets/{z}/{x}/{y}.png?access_token=' + leaflet.mapbox.accessToken, { attribution: '<a href="http://www.openstreetmap.org/copyright" target="_blank">&copy; OpenStreetMap contributors</a>'});

    var CAMPAIGN_OFFICE_ICON = L.icon({
        iconUrl: '//d2bq2yf31lju3q.cloudfront.net/img/icon/star.png',
        iconSize:     [14, 14], // size of the icon
        // shadowSize:   [50, 64], // size of the shadow
        // iconAnchor:   [22, 94], // point of the icon which will correspond to marker's location
        // shadowAnchor: [4, 62],  // the same for the shadow
        // popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
    });
    var centralMap =  new leaflet.Map("map-container", {
    center: [37.8, -96.9],
    zoom: 4}).addLayer(mapboxTiles);

    var overlays = L.layerGroup().addTo(centralMap);
    var offices = L.layerGroup().addTo(centralMap);

    var campaignOfficeLayer = L.layerGroup().addTo(centralMap);

    //initialize map
    var filteredEvents = [];
    var module = {};


    var _popupEvents = function(event) {
      var target = event.target._latlng;
      // console.log(current_filters);

      var filtered = eventsList.filter(function(d) {
        // console.log($(d.properties.filters).not(current_filters),)
        return target.lat == d.LatLng[0] && 
               target.lng == d.LatLng[1] &&
               (!current_filters || current_filters.length == 0 
                  || $(d.properties.filters).not(current_filters).length != d.properties.filters.length);
      }).sort(function(a, b) { return a.startTime - b.startTime; });

      var div = $("<div />")
        .append(filtered.length > 1 ? "<h3 class='sched-count'>" + filtered.length + " Scheduled Events</h3>" : "")
        .append(
        $("<div class='popup-list-container'/>")
          .append($("<ul class='popup-list'>")
            .append(
              filtered.map(function(d) {
                return $("<li class='lato'/>").addClass(d.isFull?"is-full":"not-full").addClass(d.visible ? "is-visible" : "not-visible").append(d.render());
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
        var className = item.properties.filters.join(" ");
        if ( arr.indexOf(item.properties.latitude + "||" + item.properties.longitude + "||" + className) >= 0 ) {
          return arr;
        } else {
          arr.push(item.properties.latitude  + "||" +  item.properties.longitude + "||" + className);
          return arr;
        }
      }, []);

      // console.log(uniqueLocs);

      uniqueLocs = uniqueLocs.map(function(d) {
        var split = d.split("||");
        return { latLng: [ parseFloat(split[0]), parseFloat(split[1])],
                 className: split[2] };
      });

      uniqueLocs.forEach(function(item) {

          if (item.className == "campaign-office") {
            L.marker(item.latLng, {icon: CAMPAIGN_OFFICE_ICON, className: item.className})
              .on('click', function(e) { _popupEvents(e); })
              .addTo(offices);
          } else if (item.className.match(/bernie\-event/ig)) {
            L.circleMarker(item.latLng, { radius: 12, className: item.className, color: 'white', fillColor: '#F55B5B', opacity: 0.8, fillOpacity: 0.7, weight: 2 })
              .on('click', function(e) { _popupEvents(e); })
              .addTo(overlays);
          } else {
            L.circleMarker(item.latLng, { radius: 5, className: item.className, color: 'white', fillColor: '#1462A2', opacity: 0.8, fillOpacity: 0.7, weight: 2 })
              .on('click', function(e) { _popupEvents(e); })
              .addTo(overlays);
          }
      });

      // console.log($(".leaflet-overlay-pane").find(".bernie-event").parent());
      // $(".leaflet-overlay-pane").find(".bernie-event").parent().prependTo('.leaflet-zoom-animated');

    };

    var toMile = function(meter) { return meter * 0.00062137; };

    var filterEvents = function (zipcode, distance, filterTypes) {
      var zipLatLng = leaflet.latLng([parseFloat(zipcode.lat), parseFloat(zipcode.lon)]);

      var filtered = eventsList.filter(function(d) {
        var dist = toMile(zipLatLng.distanceTo(d.LatLng));
        if (dist < distance) {
          d.distance = Math.round(dist*10)/10;

          //If no filter was a match on the current filter
          // console.log(d);
          if($(d.properties.filters).not(filterTypes).length == d.properties.filters.length) {
            return false;
          }

          return true;
        }
        return false;
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

    /*
    * Refresh map with new events map
    */
    var _refreshMap = function() {
      overlays.clearLayers();
      initialize();

    };

    module.filterByType = function(type) {
      if ($(filters).not(type).length != 0 || $(type).not(filters).length != 0) {
        current_filters = type;

        //Filter only items in the list
        // eventsList = originalEventList.filter(function(eventItem) {
        //   var unmatch = $(eventItem.properties.filters).not(filters);
        //   return unmatch.length != eventItem.properties.filters.length;
        // });
  

      // console.log(type.map(function(i) { return "." + i }));
      // var target = type.map(function(i) { return "." + i }).join(",");
      // $(".leaflet-overlay-pane").find("path:not("+type.map(function(i) { return "." + i }).join(",") + ")")

      var toHide = $(allFilters).not(type);

      if (toHide && toHide.length > 0) {
        toHide = toHide.splice(0,toHide.length);
        $(".leaflet-overlay-pane").find("." + toHide.join(",.")).hide();
      }

      if (type && type.length > 0) {
        $(".leaflet-overlay-pane").find("." + type.join(",.")).show();
        // _refreshMap();
      }

      //Specifically for campaign office
      if (!type) {
        centralMap.removeLayer(offices);
      } else if (type &&  type.indexOf('campaign-office') < 0) {
        centralMap.removeLayer(offices);
      } else {
        centralMap.addLayer(offices);
      }
    }
    return;
  };

    /***
     * FILTER()  -- When the user submits query, we will look at this.
     */
    module.filter = function(zipcode, distance, sort, filterTypes) {
      //Check type filter

      if (!zipcode || zipcode == "") { return; };

      //Start if other filters changed
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
      if (!(targetZipcode.lat && targetZipcode.lat != "")) {
        return;
      }

      if (current_zipcode != zipcode || current_distance != distance) {
        current_zipcode = zipcode;
        current_distance = distance;
        centralMap.setView([parseFloat(targetZipcode.lat), parseFloat(targetZipcode.lon)], zoom);
      }
      
      var filtered = filterEvents(targetZipcode, parseInt(distance), filterTypes);


      //Sort event
      filtered = sortEvents(filtered, sort, filterTypes);

      //Render event
      var eventList = d3.select("#event-list")
        .selectAll("li")
        .data(filtered, function(d){ return d.properties.id_obfuscated; });

        eventList.enter()
          .append("li")
          .attr("class", function(d) { return (d.isFull ? 'is-full' : 'not-full') + " " + (this.visible ? "is-visible" : "not-visible")})
          .classed("lato", true)
          .html(function(d){ return d.render(d.distance); });

        eventList.exit().remove();

			//add a highlighted marker 
    	function addhighlightedMarker(lat,lon){
    		var highlightedMarker = new L.circleMarker([lat,lon],{radius: 5, color: '#ea504e', fillColor: '#1462A2', opacity: 0.8, fillOpacity: 0.7, weight: 2}).addTo(centralMap);
    		// event listener to remove highlighted markers
    		$(".not-full").mouseout(function(){
    			centralMap.removeLayer(highlightedMarker)
    		})
    	} 

    	// event listener to get the mouseover
  		$(".not-full" ).mouseover(function(){
  					$(this).toggleClass("highlight")
  					var cMarkerLat = $(this).children('div').attr('lat')
  					var cMarkerLon = $(this).children('div').attr('lon')
  					// function call to add highlighted marker
  					addhighlightedMarker(cMarkerLat,cMarkerLon);
        })

      //Push all full items to end of list
      $("div#event-list-container ul#event-list li.is-full").appendTo("div#event-list-container ul#event-list");

      //Move campaign offices to 
      // console.log($("div#event-list-container ul#event-list li .campaign-office").length);
      var officeCount = $("div#event-list-container ul#event-list li .campaign-office").length;
      $("#hide-show-office").attr("data-count", officeCount);
      $("#campaign-off-count").text(officeCount);
      $("section#campaign-offices ul#campaign-office-list *").remove();
      $("div#event-list-container ul#event-list li .campaign-office").parent().appendTo("section#campaign-offices ul#campaign-office-list");

    };

    module.toMapView = function () {
      $("body").removeClass("list-view").addClass("map-view");
      centralMap.invalidateSize();
      centralMap._onResize();
    }
    module.toListView = function () {
      $("body").removeClass("map-view").addClass("list-view");
    }

    module.getMap = function() {
      return centralMap;  
    }

    return module;
  });

})(jQuery, d3, L);
