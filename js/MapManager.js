//Create an event node
var Event = (function($) { return function(properties) {
      this.properties = properties;
      this.blip = null;
      this.listing = null;
      this.className = properties.event_type_name.replace(/[^\w]/ig,"-").toLowerCase();
      this.LatLng = [parseFloat(this.properties.latitude),
                     parseFloat(this.properties.longitude)];
      this.startTime = moment(this.properties.start_dt)._d;
      this.endTime = this.properties.end_dt ? moment(this.properties.end_dt)._d : null;
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

      this.render = function (distance, zipcode) {
        var that = this;
        var moreThan5RSVP = that.properties.attendee_count && parseInt(that.properties.attendee_count) > 5 ? true : false;

        if (!that.properties.attendee_count) { moreThan5RSVP = false; }

        var datetime = that.properties.id_obfuscated && that.properties.id_obfuscated == '4gw5k' ? 'Mar 20 (Sun) 11:00am' : moment(that.properties.start_dt).format("MMM DD (ddd) h:mma")
        var lat = that.properties.latitude
        var lon = that.properties.longitude

        var endtime = that.endTime ? moment(that.endTime).format("h:mma") : null;



        var shiftElems = null;
        if ( that.properties.shift_details ) {

          var shiftList = that.properties.shift_details.map(
                             function(item) {
                               var current = moment();
                               var start = moment(item.start);
                               var end = moment(item.end);

                               if (end.isBefore(current)) { return; }

                               return $("<li />")
                                 .append($("<input type='checkbox' value='" + item.event_shift_id + "' id='" + item.event_shift_id + "' name='shift_id[]'>"))
                                 .append("<label for='" + item.event_shift_id + "'>" + start.format("h:mma") + " - " + end.format("h:mma"))
                             }
                           );
          shiftElems = $("<div class='shift-details'/>")
                         .append("<h5>Shifts</h5>")
                         .append($("<ul/>").append(shiftList))
        } // end of creating shift items
        var rendered = $("<div class='lato'/>")
          .addClass('event-item ' + that.className)
          .append($("<div />").addClass('event-item lato ' + that.className+'').attr("lat",lat).attr("lon",lon) //appended lat-lon attributes to this class for marker highlighting
            .append(that.properties.is_campaign_office ? $("<a class='office-image' href='" + (that.properties.opening_event ? that.properties.opening_event : that.properties.url) + "' />").append($("<img src='" + that.properties.image + "'>")) : "")
            .append($("<h5 class='time-info'/>").html((distance ? ("<span class='time-info-dist'>" + distance + "mi &nbsp;&nbsp;</span>") : "") + datetime + (endtime ? " - " + endtime : "" )))
            .append($("<h3/>").html("<a target='_blank' href='" +  (that.properties.opening_event ? that.properties.opening_event : that.properties.url) + "'>" + that.properties.name + "</a>"))
            .append(that.properties.is_official ? $("<h5 class='official-tag'/>").text("Official Event") : "")
            .append($("<span/>").addClass("label-icon"))
            .append($("<h5 class='event-type'/>").text(that.properties.event_type_name))
            .append($("<p/>").html(that.properties.location))
            .append(that.properties.phone && that.properties.phone != "-" ? $("<p/>").text("Phone: " + that.properties.phone) : "")
            .append(that.properties.notes ? that.properties.notes : "")
            //Append RSVP Form
            .append($("<div class='event-rsvp-activity' />")
                      .append($("<form class='event-form lato'>")
                             .append($("<h4/>").html("RSVP to <strong>" + that.properties.name + "</strong>"))
                             .append($("<div class='event-error' />"))
                             .append(shiftElems ? shiftElems : "")
                             // .append($("<input type='text' name='name' placeholder='Name'/>"))
                             .append($("<input type='hidden' name='has_shift'/>").val(shiftElems != null))
                             .append($("<input type='hidden' name='zipcode'/>").val(zipcode?zipcode:that.properties.venue_zip))
                             .append($("<input type='hidden' name='id_obfuscated'/>").val(that.properties.id_obfuscated))
                             .append($("<input type='text' name='phone' placeholder='Phone Number'/>"))
                             .append($("<input type='text' name='email' placeholder='Email Address'/>"))
                             .append($("<input type='submit' class='lato' value='Confirm RSVP' />"))
                      )
                   )
            .append(
              $("<div class='social-area' />")
                .addClass(moreThan5RSVP ? "more-than-5" : "")
                .append(
                  $("<a class='rsvp-link'/>")
                    .attr("href", that.properties.is_campaign_office ? (that.properties.opening_event ? that.properties.opening_event : that.properties.url) : "javascript: void(null) ")
                    .attr("onclick", that.properties.is_campaign_office ? null: "$('.event-rsvp-activity').hide(); $(document).trigger('show-event-form', [this])")
                    // .attr('target', 'blank')
                    // .attr("href", that.properties.is_campaign_office ? (that.properties.opening_event ? that.properties.opening_event : that.properties.url) : that.properties.url)
                    .attr("data-id", that.properties.id_obfuscated)
                    .attr("data-url", (that.properties.opening_event ? that.properties.opening_event : that.properties.url))
                    .text(that.isFull ? "FULL" : that.properties.is_campaign_office ? (that.properties.opening_event ? "RSVP" : "Get Directions") : "RSVP")
                )
                .append(
                  $("<span class='rsvp-count'/>").text(that.properties.attendee_count + " SIGN UPS")
                )
            )
            .append($("<div class='rsvp-attending'/>").html('<a href="https://go.berniesanders.com/page/event/myevents" target="_blank">You are attending this event</a>'))
          );

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
          iconUrl: '//dcxc7a0ls04u1.cloudfront.net/img/icon/star.png',
          iconSize:     [17, 14], // size of the icon
          // shadowSize:   [50, 64], // size of the shadow
          // iconAnchor:   [22, 94], // point of the icon which will correspond to marker's location
          // shadowAnchor: [4, 62],  // the same for the shadow
          // popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
      });
      var GOTV_CENTER_ICON = L.icon({
          iconUrl: '//dcxc7a0ls04u1.cloudfront.net/img/icon/gotv-star.png',
          iconSize:     [13, 10], // size of the icon
      });
      var defaultCoord = options&&options.defaultCoord ? options.defaultCoord : {center: [37.8, -96.9], zoom: 4};

      var centralMap =  new leaflet
                            .Map("map-container", window.customMapCoord ? window.customMapCoord : defaultCoord)
                            .addLayer(mapboxTiles);
      if(centralMap) {}

      var overlays = L.layerGroup().addTo(centralMap);
      var offices = L.layerGroup().addTo(centralMap);
      var gotvCenter = L.layerGroup().addTo(centralMap);

      var campaignOfficeLayer = L.layerGroup().addTo(centralMap);

      //initialize map
      var filteredEvents = [];
      var module = {};


      var _popupEvents = function(event) {
        var target = event.target._latlng;


        var filtered = eventsList.filter(function(d) {

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
                  return $("<li class='lato'/>")
                            .attr('data-attending', (function(prop) {
                                var email = Cookies.get('map.bernie.email');
                                var events_attended_raw = Cookies.get('map.bernie.eventsJoined.' + email);
                                var events_attended = events_attended_raw ? JSON.parse(events_attended_raw) : [];
                                return $.inArray(prop.id_obfuscated, events_attended) > -1;

                              })(d.properties))
                            .addClass(d.isFull?"is-full":"not-full")
                            .addClass(d.visible ? "is-visible" : "not-visible")
                            .append(d.render());
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




      uniqueLocs = uniqueLocs.map(function(d) {
        var split = d.split("||");
        return { latLng: [ parseFloat(split[0]), parseFloat(split[1])],
                 className: split[2] };
      });




      uniqueLocs.forEach(function(item) {
         // setTimeout(function() {
          if (item.className == "campaign-office") {
            L.marker(item.latLng, {icon: CAMPAIGN_OFFICE_ICON, className: item.className})
              .on('click', function(e) { _popupEvents(e); })
              .addTo(offices);
          } else if (item.className == "gotv-center") {
            L.marker(item.latLng, {icon: GOTV_CENTER_ICON, className: item.className})
              .on('click', function(e) { _popupEvents(e); })
              .addTo(gotvCenter);
          }else if (item.className.match(/bernie\-event/ig)) {
            L.circleMarker(item.latLng, { radius: 12, className: item.className, color: 'white', fillColor: '#F55B5B', opacity: 0.8, fillOpacity: 0.7, weight: 2 })
              .on('click', function(e) { _popupEvents(e); })
              .addTo(overlays);
          } else {

            L.circleMarker(item.latLng, { radius: 5, className: item.className, color: 'white', fillColor: '#1462A2', opacity: 0.8, fillOpacity: 0.7, weight: 2 })
              .on('click', function(e) { _popupEvents(e); })
              .addTo(overlays);
          }
        // }, 10);
      });



      // $(".leaflet-overlay-pane").find(".bernie-event").parent().prependTo('.leaflet-zoom-animated');

    }; // End of initialize

    var toMile = function(meter) { return meter * 0.00062137; };

    var filterEventsByCoords = function (center, distance, filterTypes) {
      var zipLatLng = leaflet.latLng(center);

      var filtered = eventsList.filter(function(d) {
        var dist = toMile(zipLatLng.distanceTo(d.LatLng));
        if (dist < distance) {
          d.distance = Math.round(dist*10)/10;

          //If no filter was a match on the current filter
          if (options && options.defaultCoord && !filterTypes) {
            return true;
          }

          if($(d.properties.filters).not(filterTypes).length == d.properties.filters.length) {
            return false;
          }

          return true;
        }
        return false;
      });

      return filtered;
    };

    var filterEvents = function (zipcode, distance, filterTypes) {
      return filterEventsByCoords([parseFloat(zipcode.lat), parseFloat(zipcode.lon)], distance, filterTypes)
    };

    var sortEvents = function(filteredEvents, sortType) {
      switch (sortType) {
        case 'distance':
          filteredEvents = filteredEvents.sort(function(a,b) { return a.distance - b.distance; });
          break;
        default:
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

    setTimeout(function(){
       initialize();
    }, 10);


    module._eventsList = eventsList;
    module._zipcodes = zipcodes;
    module._options = options;

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

      //For gotv-centers
      if (!type) {
        centralMap.removeLayer(gotvCenter);
      } else if (type &&  type.indexOf('gotv-center') < 0) {
        centralMap.removeLayer(gotvCenter);
      } else {
        centralMap.addLayer(gotvCenter);
      }
    }
    return;
  };

    module.filterByCoords = function(coords, distance, sort, filterTypes) {
      //Remove list
      d3.select("#event-list")
        .selectAll("li").remove();

      var filtered = filterEventsByCoords(coords, parseInt(distance), filterTypes);
      //Sort event
      filtered = sortEvents(filtered, sort, filterTypes);

      //Check cookies
      var email = Cookies.get('map.bernie.email');
      var events_attended_raw = Cookies.get('map.bernie.eventsJoined.' + email);
      var events_attended = events_attended_raw ? JSON.parse(events_attended_raw) : [];

      //Render event
      var eventList = d3.select("#event-list")
        .selectAll("li")
        .data(filtered, function(d){ return d.properties.id_obfuscated; });

        eventList.enter()
          .append("li")
          .attr("data-attending", function(d, id) {  return $.inArray(d.properties.id_obfuscated, events_attended) > -1;  })
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

      var officeCount = $("div#event-list-container ul#event-list li .campaign-office").length;
      $("#hide-show-office").attr("data-count", officeCount);
      $("#campaign-off-count").text(officeCount);
      $("section#campaign-offices ul#campaign-office-list *").remove();
      $("div#event-list-container ul#event-list li .campaign-office").parent().appendTo("section#campaign-offices ul#campaign-office-list");

    }

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
        case 500: zoom = 5; break;
        case 750: zoom = 5; break;
        case 1000: zoom = 4; break;
        case 2000: zoom = 4; break;
        case 3000: zoom = 3; break;
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

      //Check cookies
      var email = Cookies.get('map.bernie.email');
      var events_attended_raw = Cookies.get('map.bernie.eventsJoined.' + email);
      var events_attended = events_attended_raw ? JSON.parse(events_attended_raw) : [];

      //Render event
      var eventList = d3.select("#event-list")
        .selectAll("li")
        .data(filtered, function(d){ return d.properties.id_obfuscated; });

        eventList.enter()
          .append("li")
          .attr("data-attending", function(d, id) {  return $.inArray(d.properties.id_obfuscated, events_attended) > -1;  })
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

var VotingInfoManager = (function($) {
  return (function(votingInfo) {
    var votingInfo = votingInfo;
    var module = {};

    function buildRegistrationMessage(state) {
      var $msg = $("<div class='registration-msg'/>").append($("<h3/>").text("Registration deadline: " + moment(new Date(state.registration_deadline)).format("MMM D")))
                    .append($("<p />").html(state.name + " has <strong>" + state.is_open + " " + state.type + "</strong>. " + state.you_must))
                    .append($("<p />").html("Find out where and how to register at <a target='_blank' href='https://vote.berniesanders.com/" + state.state + "'>vote.berniesanders.com</a>"))

      return $msg;
    }

    function buildPrimaryInfo(state) {

      var $msg = $("<div class='registration-msg'/>").append($("<h3/>").text("Primary day: " + moment(new Date(state.voting_day)).format("MMM D")))
                    .append($("<p />").html(state.name + " has <strong>" + state.is_open + " " + state.type + "</strong>. "  + state.you_must))
                    .append($("<p />").html("Find out where and how to vote at <a target='_blank' href='https://vote.berniesanders.com/" + state.state + "'>vote.berniesanders.com</a>"))

      return $msg;

    }

    function buildCaucusInfo(state) {
      var $msg = $("<div class='registration-msg'/>").append($("<h3/>").text("Caucus day: " + moment(new Date(state.voting_day)).format("MMM D")))
                    .append($("<p />").html(state.name + " has <strong>" + state.is_open + " " + state.type + "</strong>. " + state.you_must))
                    .append($("<p />").html("Find out where and how to caucus at <a target='_blank' href='https://vote.berniesanders.com/" + state.state + "'>vote.berniesanders.com</a>"))

      return $msg;
    }

    module.getInfo = function(state) {
      var targetState = votingInfo.filter(function(d) { return d.state == state })[0]; //return first
      if(!targetState) return null;

      var today = new Date();
      today.setDate(today.getDate() - 1);

      if(today <= new Date(targetState.registration_deadline)) {
        return buildRegistrationMessage(targetState);
      } else if (today <= new Date(targetState.voting_day)) {
        if (targetState.type == "primaries") {
          return buildPrimaryInfo(targetState);
        } else { //
          return buildCaucusInfo(targetState);
        }
      } else {
        return null;
      }
    }

    return module;
  });
})(jQuery);

// More events
(function($) {
  $(document).on("click", function(event, params) {
    $(".event-rsvp-activity").hide();
  });

  $(document).on("click", ".rsvp-link, .event-rsvp-activity", function(event, params) {
    event.stopPropagation();
  });

  //Show email
  $(document).on("show-event-form", function(events, target) {
    var form = $(target).closest(".event-item").find(".event-rsvp-activity");
      if (Cookies.get('map.bernie.email')) {
        form.find("input[name=email]").val(Cookies.get('map.bernie.email'));
      }

      if (Cookies.get('map.bernie.phone')) {
        form.find("input[name=phone]").val(Cookies.get('map.bernie.phone'));
      }

      // var params =  $.deparam(window.location.hash.substring(1) || "");
      // form.find("input[name=zipcode]").val(params.zipcode ? params.zipcode : Cookies.get('map.bernie.zipcode'));

      form.fadeIn(100);
  });

  $(document).on("submit", "form.event-form", function() {
    var query = $.deparam($(this).serialize());
    var params = $.deparam(window.location.hash.substring(1) || "");
    query['zipcode'] = params['zipcode'] || query['zipcode'];



    var $error = $(this).find(".event-error");
    var $container = $(this).closest(".event-rsvp-activity");

    if (query['has_shift'] == 'true' && (!query['shift_id'] || query['shift_id'].length == 0)) {
      $error.text("You must pick a shift").show();
      return false;
    }

    var shifts = null;
    var guests = 0;
    if (query['shift_id']) {
      shifts = query['shift_id'].join();
    }

    if (!query['phone'] || query['phone'] == '') {
      $error.text("Phone number is required").show();
      return false;
    }

    if (!query['email'] || query['email'] == '') {
      $error.text("Email is required").show();
      return false;
    }

    if (!query['email'].toUpperCase().match(/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/)) {
      $error.text("Please input valid email").show();
      return false;
    }

    // if (!query['name'] || query['name'] == "") {
    //   $error.text("Please include your name").show();
    //   return false;
    // }

    $(this).find(".event-error").hide();
    var $this = $(this)
    $.ajax({
      type: 'POST',
      url: 'https://organize.berniesanders.com/events/add-rsvp',
      // url: 'https://bernie-ground-control-staging.herokuapp.com/events/add-rsvp',
      crossDomain: true,
      dataType: 'json',
      data: {
        // name: query['name'],
        phone: query['phone'],
        email: query['email'],
        zip: query['zipcode'],
        shift_ids: shifts,
        event_id_obfuscated: query['id_obfuscated']
      },
      success: function(data) {
        Cookies.set('map.bernie.zipcode', query['zipcode'], {expires: 7});
        Cookies.set('map.bernie.email', query['email'], {expires: 7});
        Cookies.set('map.bernie.name', query['name'], {expires: 7});

        if (query['phone'] != '') {
          Cookies.set('map.bernie.phone', query['phone'], {expires: 7});
        }

        //Storing the events joined
        var events_joined = JSON.parse(Cookies.get('map.bernie.eventsJoined.' + query['email']) || "[]") || [];

        events_joined.push(query['id_obfuscated']);
        Cookies.set('map.bernie.eventsJoined.' + query['email'], events_joined, {expires: 7});


        $this.closest("li").attr("data-attending", true);

        $this.html("<h4 style='border-bottom: none'>RSVP Successful! Thank you for joining to this event!</h4>");
        $container.delay(1000).fadeOut('fast')
      }
    })


    return false;
  });
})(jQuery);
