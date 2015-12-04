var bernMap = bernMap || {};
bernMap.raw = {};
bernMap.raw.zipcode = $("#zipcodes-datadump").html().trim();

var $jq = jQuery;


var timeFormat = d3.time.format("%I:%M %p");
var rawDateFormat = d3.time.format("%Y-%m-%d");
var rawTime = d3.time.format("%X");
var dateFormat = d3.time.format("%b %d");


/* some minor initialization */

var zip_code = window.location.hash.match(/zipcode=(\d{5})/i);
if(zip_code){
  $('#input-text-zipcode').val(zip_code[1]);
}

var distance = window.location.hash.match(/distance=(\d+)/i);
if(distance){
  $('#mile-' + distance[1]).click();
}

/* end init */
var d3format = d3.format("0,000");


//Initialize items
$("h2#event-results-count").hide();

L.mapbox.accessToken = "pk.eyJ1IjoicmFwaWNhc3RpbGxvIiwiYSI6IjBlMGI3NTNhMWFiNGU4NmY4YmI4ZTNmOGRjYmQzZWVjIn0.KyTcvG8fiIStw8BkZjfvLA";
var mapboxTiles = L.tileLayer('http://{s}.tiles.mapbox.com/v4/mapbox.streets/{z}/{x}/{y}.png?access_token=' + L.mapbox.accessToken, {
    attribution: '<a href="http://www.openstreetmap.org/copyright" target="_blank">&copy; OpenStreetMap contributors</a>'
});

var WIDTH = $jq(window).width();

var bernMap = bernMap || {};
bernMap.constants = {};
// bernMap.constants.spreadsheetUrl = "/d/july29.json";
bernMap.constants.spreadsheetUrl = "https://go.berniesanders.com/page/event/search_results?format=json&wrap=no&orderby[0]=date&orderby[1]=desc&event_type=13&mime=text/json&limit=4000&country=*";
bernMap.constants.mainOffices = null;

if (WIDTH >= 720) {
  bernMap.mapBox = new L.Map("map", {
        center: window.MAP_CENTER?window.MAP_CENTER.latlng:[37.8, -96.9],
        zoom: window.MAP_CENTER?window.MAP_CENTER.zoom:4,
        zoomAnimation: false,
        paddingTopLeft: [400, 0], scrollWheelZoom: false}).addLayer(mapboxTiles);
} else {
  bernMap.mapBox = new L.Map("map", {
    center: window.MAP_CENTER?window.MAP_CENTER.latlng:[37.8, -96.9],
    zoom: window.MAP_CENTER?window.MAP_CENTER.zoom:4,
    zoomControl: false, paddingTopLeft: [400, 0],  scrollWheelZoom: false}).addLayer(mapboxTiles);
}
// bernMap.mapBox = new L.Map("map", {center: [37.8, -96.9], zoom: 4, paddingTopLeft: [400, 0], scrollWheelZoom: true}).addLayer(mapboxTiles);
// bernMap.mapBox.touchZoom.disable();

var offset = bernMap.mapBox.getSize().x * 0.15;
if (WIDTH >= 720) {
  bernMap.mapBox.panBy(new L.Point(offset,0), {animate: false});
}

bernMap.d = {};
bernMap.scale = {};
bernMap.scale.radScale = d3.scale.pow().domain([0, 5, 150]);
bernMap.daterange = "all-events";
bernMap.sort = "distance";
bernMap.d.rsvp=0;
bernMap.d.capacity = 0;
bernMap.d.zipcodes = null;
bernMap.d.allZipcodes = null;
bernMap.d.meetupData = null;
bernMap.d.rawMeetupData = null;
bernMap.d.targetZipcodes = null;
bernMap.d.aggregatedRSVP = null;
bernMap.d.initialLoad = true;

var bernMap = bernMap || {};
bernMap.draw = function() {

  this.filteredZipcode = null;
  this.svg = d3.select(bernMap.mapBox.getPanes().overlayPane).append("svg");
  this.activityLayer = this.svg.append("g").attr("class","leaflet-zoom-hide");
  this.zipcodeElements = null;
  this.mainOffices = null;
  this.centerItem = null;
  this.visibleTypes = { volunteerWork: true, grassrootsEvent: true, officialRally: true, debateWatchEvent: true, ballotAccess: true, campaignOffice: true};

  this.currentZipcode = null;

  this.showAll  = function() {
    var that = this;
    $("form#zip-and-distance").find("[name='eventtype']").prop("checked", true);
    $("form#zip-and-distance").submit();
  };

  this.hideAll  = function() {
    var that = this;
    $("form#zip-and-distance").find("[name='eventtype']").prop("checked", false);
    $("form#zip-and-distance").submit();
  };

  this._projectPoint = function(x,y) {
    var point = bernMap.mapBox.latLngToLayerPoint(new L.LatLng(y, x));

    // var point = bernMap.mapBox.latLngToContainerPoint(new L.LatLng(y,x));
    // this.stream.point(point.x, point.y);
    return [point.x, point.y];
  };

  this._getZoomValue = function(distance) {
    switch (distance) {
      case 5 : return 12;
      case 10: return 11;
      case 20: return 10;
      case 50: return 9;
      case 100: return 8;
      case 250: return 7;
    }
  };

  this._deserialize = function(query) {
    var params = $.deparam(query);
    if (typeof params.eventtype === "string") { params.eventtype = [params.eventtype]; }
    return params;
  };

  this._getRadius = function(d) {
    var that = this;
    switch (d.properties.type) {
      case "CW" : return 4; break;
      case "E" : return 4; break;
      case "R" : return 9; break;
      default: return 4;
    }
  };

  this._getVisibility = function(d) {
    var that = this;
    switch (d.properties.type) {
      case "CW" : return that.visibleTypes.volunteerWork ? "inherit" : "hidden"; break;
      case "E" : return that.visibleTypes.grassrootsEvent ? "inherit" : "hidden"; break;
      case "D" : return that.visibleTypes.debateWatchEvent ? "inherit" : "hidden"; break;
      case "R" : return that.visibleTypes.officialRally ? "inherit" : "hidden"; break;
      case "B" : return that.visibleTypes.ballotAccess ? "inherit" : "hidden"; break;
      case "O" : return that.visibleTypes.campaignOffices ? "inherit" : "hidden"

    }
  };

  //****
  // Show / hide event types
  this.changeVisibility = function(type, visibility) {
    var that = this;
    switch (type) {
      case "CW" : that.visibleTypes.volunteerWork = visibility; break;
      case "E" : that.visibleTypes.grassrootsEvent = visibility; break;
      case "R" : that.visibleTypes.officialRally = visibility; break;
      case "D" : that.visibleTypes.debateWatchEvent = visibility; break;
      case "B" : that.visibleTypes.ballotAccess = visibility; break;
      case "O" : that.visibleTypes.campaignOffices = visibility; break;
    };

    that.replot();
  };

  // *********************
  // FOCUS ZIPCODE
  // *********************
  this.focusZipcode = function(params) {
    var that = this;
    // var params = that._deserialize(hash);

    var target = bernMap.d.allZipcodes.filter(function(d) { return d.zip == params.zipcode; });

    if (target.length == 0) {
      bernieEvents.setError("Zipcode does not exist. <a href=\"https://go.berniesanders.com/page/event/search_results?orderby=zip_radius&zip_radius%5b0%5d=" + params.zipcode + "&zip_radius%5b1%5d=100&country=US&radius_unit=mi\">Try our events page</a>");
    }
    else {
      var t = target[0];



      //Plot zipcode center
      $("circle#center-item").remove();
      //Focus on map

      bernMap.mapBox.setView([parseFloat(t.lat), parseFloat(t.lon)], that._getZoomValue(parseInt(params.distance)), { animate: false });

      var offset = bernMap.mapBox.getSize().x * 0.15;
      if (WIDTH >= 720) {
        bernMap.mapBox.panBy(new L.Point(offset,0), {animate: false});
      }
      that.replot();

      //Setting the marker

      if (that.currentZipcode != params.zipcode) {
        if (that.centerItem) { bernMap.mapBox.removeLayer(that.centerItem); }
        that.centerItem = L.marker([t.lat, t.lon],
                              { bounceOnAdd: true,
                                bounceOnAddOptions: {duration: 700, height: 50}
                              }).addTo(bernMap.mapBox);
        that.currentZipcode = params.zipcode;
      }
    }


  };

  this.mapOffices = function() {
    var that = this;

    if (!bernMap.constants.mainOffices) return;
    //<image xlink:href="firefox.jpg" x="0" y="0" height="50px" width="50px"/>
    if (!that.mainOffices) {
      that.mainOffices = that.activityLayer.selectAll("image")
          .data(bernMap.constants.mainOffices)
          .enter()
            .append("image")
              .attr("xlink:href", "//d2bq2yf31lju3q.cloudfront.net/img/icon/star.png")
              .attr("xlink:xlink:href", function() {

                return "//d2bq2yf31lju3q.cloudfront.net/img/icon/star.png";})
              .attr("width", "10px")
              .attr("height", "10px")
              .attr("class", "bernie-main-office")
              .each(function(d) {
                var coordinates = that._projectPoint(d.lon, d.lat);
                  d3.select(this).attr("x", coordinates[0] - 5)
                      .attr("y", coordinates[1] - 5)
                  ;
                  d3.select(this).attr("xlink\:href", "//d2bq2yf31lju3q.cloudfront.net/img/icon/star.png");
              });

        $(".bernie-main-office").prependTo($(that.activityLayer[0]));

        that.mainOffices.on("click", function(d) {
          that.popupOffice(d);
        });

    } else {
      that.mainOffices =
          that.activityLayer.selectAll("image")
              .each(function(d) {
                var coordinates = that._projectPoint(d.lon, d.lat);
                  d3.select(this).attr("x", coordinates[0] - 5)
                      .attr("y", coordinates[1] - 5)
                  ;
              });
    }

  };

  this.plot = function () {
    var that = this;
    if (!bernMap.d.zipcodes) return;

    bernMap.scale.radScale.range([parseFloat(bernMap.mapBox.getZoom()),
                                      bernMap.mapBox.getZoom(),

                                      bernMap.mapBox.getZoom()
                                      + (bernMap.mapBox.getZoom()  * 3)]);

    that.activityLayer.selectAll("circle").remove();

    that.zipcodeElements = that.activityLayer.selectAll("circle")
                              .data(bernMap.d.zipcodes.features, function(d) { return d.properties.id; } ).enter()
                              .append("circle")
                              .attr("data-maxcapacity", function(d) { return d.properties.capacity > 0 && d.properties.attendee_count >= d.properties.capacity ? "true" : "false" } )
                              .attr("data-location-id", function(d) { return d.properties.id; })
                              .attr("stroke-width", 0)
                              .attr("opacity", 0.7)
                              .attr("class", function(d) {
                                switch (d.properties.type) {
                                  case "CW" : return "campaign-work"; break;
                                  case "E" : return "grassroots-event"; break;
                                  case "R" : return "official-rally"; break;
                                  case "D" : return "debate-watch"; break;
                                  case "B" : return "ballot-access"; break;
                                }
                              })
                              .style("visibility", function(d) {
                                switch (d.properties.type) {
                                  case "CW" : return that.show ? "visible" : "hidden"; break;
                                  case "E" : return that.show ? "visible" : "hidden"; break;
                                  case "R" : return that.show ? "visible" : "hidden"; break;
                                  case "D" : return that.show ? "visible" : "hidden"; break;
                                  case "B" : return that.show ? "visible" : "hidden"; break;
                                }
                              })
                              .each(function(d) {
                                var coordinates = that._projectPoint(d.geometry.coordinates[0], d.geometry.coordinates[1]);
                                  d3.select(this).attr("cx", coordinates[0])
                                      .attr("cy", coordinates[1])
                                  ;
                              }).call(function () {
                                    $(".official-rally").prependTo($(that.activityLayer[0]));
                              });

    //initialize event for zipcode
    that.zipcodeElements.on("click", function(d) {
      that.popupInfo(d);
    });

    // var bounds = that.activityLayer[0][0].getBoundingClientRect();
    var bounds = that.activityLayer[0][0].getBBox();

    that.svg.attr("width", (bounds.width + 20) + "px")
      .attr("height", (bounds.height + 20) + "px")
      // .attr("transform", "translate(" + -bounds.left + "," + -bounds.top + ")");
      .style("left", bounds.x-10 + "px")
      .style("top", bounds.y-10 + "px")
      .style("z-index", "1000");

    that.activityLayer.attr("transform", "translate(" + -(bounds.x-10) + "," + -(bounds.y-10) + ")");
  };

  this.popupInfo = function(d) {
    var that = this;
    setTimeout( function() { L.popup()
      .setLatLng([parseFloat(d.properties.latitude), parseFloat(d.properties.longitude)])
      .setContent(bernieEvents.buildEvent(d))
      .openOn(bernMap.mapBox);
    }
      , 100);
  };

  this.popupOffice = function(d) {
    var that = this;
    setTimeout( function() { L.popup()
      .setLatLng([parseFloat(d.lat), parseFloat(d.lon)])
      .setContent(bernieEvents.buildOffice(d))
      .openOn(bernMap.mapBox);
    }
      , 100);
  }


  this.replot = function () {
    var that = this;
    if (!bernMap.d.zipcodes) return;

     bernMap.scale.radScale.range([parseFloat(bernMap.mapBox.getZoom()) * 0.6,
                                      bernMap.mapBox.getZoom(),
                                      bernMap.mapBox.getZoom()
                                      + (bernMap.mapBox.getZoom()  * 3)]);

    that.zipcodeElements = that.activityLayer.selectAll("circle")
      .data(bernMap.d.zipcodes.features, function(d) { return d.properties.id; })
      .each(function(d) {
        var coordinates = that._projectPoint(d.geometry.coordinates[0], d.geometry.coordinates[1]);
          d3.select(this).attr("cx", coordinates[0])
              .attr("cy", coordinates[1])
              .attr("r", that._getRadius)
              .style("visibility", function(d) { return d.show ? "visible" : "hidden"; })
              .attr("opacity", 0.7)
          ;
      });

    var bounds = that.activityLayer[0][0].getBBox();

    that.svg.attr("width", (bounds.width + 0) + "px")
      .attr("height", (bounds.height + 0) + "px")
      .style("left", bounds.x + "px")
      .style("top", bounds.y + "px");

    that.activityLayer.attr("transform", "translate(" + -bounds.x + "," + -bounds.y + ")");

  };

  var _that = this;
  this.initialize = function() {

    if (window.MAP_CALLBACK) {
      window.MAP_CALLBACK(bernMap.mapBox);
    }

    bernMap.mapBox.on('zoomstart', function() {
      // _that.activityLayer.style("visibility","hidden");
      $(".leaflet-overlay-pane").css("visibility", "hidden");
    });
    bernMap.mapBox.on('zoomend', function() {
        _that.mapOffices();
        _that.replot();
        $(".leaflet-overlay-pane").css("visibility","visible");
    });

    // bernMap.mapBox.on('moveend', function() {
    //   _that.mapOffices();
    // });
  }();
};

var bernMap = bernMap || {};
bernMap.eventList = function(container) {
  this.containerLabel = container;
  this.container = $jq(container);
  this.errorBox = this.container.find("#error-box");
  this.currentEntity = "events";

  this._getDistanceInMi = function (lat1,lon1,lat2,lon2) {
    var that = this;
    var distance = that._getDistanceInKm(lat1, lon1, lat2, lon2);

    return distance * 0.62;
  };

  this._getDistanceInKm = function (lat1,lon1,lat2,lon2) {
    var that = this;
    var R = 6371; // Radius of the earth in km
    var dLat = that._deg2rad(lat2-lat1);  // deg2rad below
    var dLon = that._deg2rad(lon2-lon1);
    var a =
      Math.sin(dLat/2) * Math.sin(dLat/2) +
      Math.cos(that._deg2rad(lat1)) * Math.cos(that._deg2rad(lat2)) *
      Math.sin(dLon/2) * Math.sin(dLon/2)
      ;
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    var d = R * c; // Distance in km
    return d;
  };

  this._deg2rad = function (deg) {
    return deg * (Math.PI/180)
  };

  this.hideError = function() { this.errorBox.text(''); };
  this.setError = function(message) {
    var that = this;
    that.errorBox.html(message);
  };

  //Events or Offices
  this.toggleEvents = function() {
    var that = this;
    // alert(that.currentEntity);
    if (that.currentEntity == "events") {
      $("ul#office-list").hide();
      $("ul#event-list").show();
    } else if (that.currentEntity == "offices") {
      $("ul#office-list").show();
      $("ul#event-list").hide();
    }
  };

  this.buildOffice = function(d) {
    var that = this;

    var $popupItem = $("<div/>").addClass("office-popup")
        .append($("<div/>").addClass("office-image-container").append(
            $("<img/>").prop("src", d.photo).addClass("office-main-image")
        ))
        .append($("<h3/>").text(d.name))
        .append($("<div/>").addClass("event-type campaign-office")
              .html('<span style="vertical-align: middle"><img src="//d2bq2yf31lju3q.cloudfront.net/img/icon/star.png" width="14px" height="14px"></span><span class="event-text">Campaign Office</span>'))
        .append($("<h5/>").html("<span><img src='//d2bq2yf31lju3q.cloudfront.net/img/icon/map.png'/>" + d.address + "</span>"))
        .append((d.phone && d.phone != "-") ? $("<h5/>").html("<span><img src='//d2bq2yf31lju3q.cloudfront.net/img/icon/phone.png'/>" + d.phone + "</span>") : "")
        .append($("<div/>").html("<a class='button-rsvp' href='https://www.google.com/maps?q=" + encodeURIComponent(d.address) + "' target='_blank'><img src='//d2bq2yf31lju3q.cloudfront.net/img/icon/map-white.png'/> VIEW MAP</a>"));

    return $popupItem.clone().wrap('<div>').parent().html();
  };

  this.shareFacebook = function(url, title) {
    var winWidth = 520, winHeight = 350;
    // function fbShare(url, title, descr, image, winWidth, winHeight) {
    var winTop = (screen.height / 2) - (winHeight / 2);
    var winLeft = (screen.width / 2) - (winWidth / 2);
    window.open('http://www.facebook.com/sharer.php?s=100&p[title]=' + title + '&p[url]=' + url, 'sharer', 'top=' + winTop + ',left=' + winLeft + ',toolbar=0,status=0,width=' + winWidth + ',height=' + winHeight);
    // // }
  };

  this.shareTwitter = function(url, date) {
    var message = "I'm attending a @BernieSanders Event on " + date + "! #Bernie2016 ";

    var twitter_url = "http://twitter.com/share?url=" + url + "&text=" + encodeURIComponent(message);
    var width  = 575,
        height = 400,
        left   = ($(window).width()  - width)  / 2,
        top    = ($(window).height() - height) / 2,
        url    = url,
        opts   = 'status=1' +
                 ',text=' + message +
                 ',width='  + width  +
                 ',height=' + height +
                 ',top='    + top    +
                 ',left='   + left;

    window.open(twitter_url, 'twitte', opts);
  }

  this.buildEvent = function(d) {


     var eventType, eventText;

     switch (d.properties.type) {
      case "CW": eventType = 'campaign-work'; break;
      case "E" : eventType = 'meetup'; break;
      case "R" : eventType = 'rally'; break;
      case "D" : eventType = 'debate-watch'; break;
      case "B" : eventType = 'ballot-access'; break;
     }

    eventText = d.properties.eventType;


     if (d.properties.attendee_count >= d.properties.capacity && d.properties.capacity > 0) {
          return "<h5>"
            + "<span class='event-item-date'>"
            + (d.properties.distance && !isNaN(d.properties.distance) ? ("~" + d3.round(d.properties.distance,1) + "MI&nbsp;&nbsp; ") : "")
            + (d.properties.Date ? ("" + dateFormat(d.properties.Date)) : "")
            //+ (d.properties.TimeStart ? "" + d.properties.TimeStart + (d.properties.TimeEnd ? " - " + d.properties.TimeEnd : "") + "" : "")
            + (d.properties.TimeStart ? " &nbsp;&nbsp; " + d.properties.TimeStart : "")
            + "</span>"
            +"</h5>"
            + "<h3><span class='event-item-name event-full'>" + d.properties.Title + " (FULL)</span></h3>"
            + "<div class='event-type " + eventType + "'><span class='event-bullet'></span><span class='event-text'>" + eventText + "</span></div>"

            // + (d.properties.description != "" ? ("<h4 class='event-organizer'>" + d.properties.description +"</h4>") : "")
            + "<h5 class='event-location'>" + d.properties.location + "</h5>"
            + "<p><a href='javascript: void(null);' target='_blank' class='button-rsvp button-full button-disabled'>FULL</a>"

            + (eventType =="rally" || d.properties.attendee_count <= 5 || isNaN(d.properties.attendee_count) ? "" : ("<span class='rsvp-counter'>" + d.properties.attendee_count + " SIGN UPS</span>" ))

            + "<span class='social-buttons'>"
            + "<a href='javascript: void(null)' onclick='bernieEvents.shareFacebook.call(this)' data-link='" + d.properties.link+ "'><img src='//d2bq2yf31lju3q.cloudfront.net/img/icon/facebook.png' /></a>"
            + "<a href='javascript: bernieEvents.shareTwitter(\"" + d.properties.link + "\", \"" + dateFormat(d.properties.Date) + "\")'><img src='//d2bq2yf31lju3q.cloudfront.net/img/icon/twitter.png' /></a>"
            + "</span>"

            + "</p>"
            ;;

        }
        else {
          return "<h5>"
            + "<span class='event-item-date'>"
            + (d.properties.distance && !isNaN(d.properties.distance) ? ("~" + d3.round(d.properties.distance,1) + "MI&nbsp;&nbsp; ") : "")
            + (d.properties.Date ? ("" + dateFormat(d.properties.Date)) : "")
            + (d.properties.TimeStart ? " &nbsp;&nbsp; " + d.properties.TimeStart : "")
            + "</span>"

            + "</h5>"
            + "<h3><a target='_blank' href='" + d.properties.link + "'><span class='event-item-name'>" + d.properties.Title + "</span></a></h3>"
            + "<div class='event-type " + eventType + "'><span class='event-bullet'></span><span class='event-text'>" + eventText + "</span></div>"
            // + (d.properties.description != "" ? ("<h4 class='event-organizer'>" + d.properties.description +"</h4>") : "")
            + "<h5 class='event-location'>" + d.properties.location + "</h5>"
            + "<p><a href='" + d.properties.link + "' target='_blank' class='button-rsvp'>RSVP</a>"

            + (eventType =="rally" || d.properties.attendee_count <= 5 || isNaN(d.properties.attendee_count) ? "" : ("<span class='rsvp-counter'>" + d.properties.attendee_count + " SIGN UPS</span>" ))
            + "<span class='social-buttons'>"
            + "<a href='javascript: bernieEvents.shareFacebook(\"" + d.properties.link+ "\")'><img src='//d2bq2yf31lju3q.cloudfront.net/img/icon/facebook.png' /></a>"
            + "<a href='javascript: bernieEvents.shareTwitter(\"" + d.properties.link + "\", \"" + dateFormat(d.properties.Date) + "\")'><img src='//d2bq2yf31lju3q.cloudfront.net/img/icon/twitter.png' /></a>"
            + "</span>"

            + "</p>";
        }
  };

  this.filterEvents = function(zipcode, allowedDistance) {
    var that = this;
    var targetZipcode = bernMap.d.allZipcodes.filter(function(d) { return d.zip == zipcode; });

    $("#event-results-count").hide();
    $("ul#event-list").children("li").remove();

    if (targetZipcode.length == 0 ) return ;
    var target = targetZipcode[0];

    $("h2#event-results-count").show();
    $("#event-counter").text("0 events");
    $("#event-distance").text(allowedDistance + "mi");
    if (target.primary_city != "" && target.state != "") {
      $("#event-city").text( target.primary_city + ", " + target.state);
    } else {
      $("#event-city").text(target.zip);
    }

    var targC = [parseFloat(target.lat), parseFloat(target.lon)];

    //FILTER OFFICES

    var nearByOffices = bernMap.constants.mainOffices.filter (function(d) {
                            var compC = [parseFloat(d.lat), parseFloat(d.lon)];
                            var distance = that._getDistanceInMi(targC[0], targC[1], compC[0], compC[1]);
                            d.distance = distance;
                            return  distance <= allowedDistance;
                        });

    if ( nearByOffices.length > 0) {
      $("input#entity-type-offices").removeAttr("disabled");
      nearByOffices.sort(function(a, b) { return a.distance - b.distance; })

      var ulOffices = d3.select(that.containerLabel).select("ul#office-list");

      $("#office-counter").text(nearByOffices.length + " " + (nearByOffices.length == 1 ? "office" : "offices"));
      var liOfficeContent =
            ulOffices.selectAll("li.office-list-item")
              .data(nearByOffices, function(d){ return d.name ;});

              liOfficeContent.enter().append("li")
              liOfficeContent.exit().remove();

              liOfficeContent.attr("data-location-id", function(d) { return d.name })
                .attr("class", "office-list-item")
                .html(that.buildOffice);
    } else {
      $("input#entity-type-events").prop("checked", true);
      that.currentEntity = 'events';
      // $("input[name='entitye-type']").trigger('change');
      that.toggleEvents();

      $("input#entity-type-offices").attr("disabled", "disabled");
      $("#office-counter").text("OFFICES");
    }

    //Filter events
    var nearByZipcodes = bernMap.d.zipcodes.features.filter(function(d) {
                            var compC = [parseFloat(d.properties.latitude), parseFloat(d.properties.longitude)];

                            var distance = that._getDistanceInMi(targC[0], targC[1], compC[0], compC[1]);
                            d.properties.distance = distance;

                            // consoel.log(distance);
                            return  distance <= allowedDistance && d.show;
                        });

    nearByZipcodes = nearByZipcodes.map(function(d) { return { "distance" : d.properties.distance, properties: d.properties}; });


    if (nearByZipcodes.length == 0) {

      $("ul#event-list").append($("<li/>").css("text-align", "center").html('<a href="https://go.berniesanders.com/page/event/create" style="font-size: 0.7em; letter-spacing: 3px;" target="_blank">HOST AN EVENT</a>'));

      if ( nearByOffices.length > 0) {
        $("input#entity-type-offices").prop("checked", true);
        that.currentEntity = "offices";
        that.toggleEvents();
      }

      return;
    }

    //START : Separate Full events and active events;
    var nearByActive = nearByZipcodes.filter(function(d) {
                        return d.properties.attendee_count < d.properties.capacity || d.properties.capacity == 0;
                      });

    var nearByFull = nearByZipcodes.filter(function(d) {
                        return !( d.properties.attendee_count < d.properties.capacity || d.properties.capacity == 0 );
                     });

    if (bernMap.sort == "distance") {
      nearByActive.sort(function(a,b) {
        return a.distance - b.distance;
      });
    } else if (bernMap.sort == "time") {
      nearByActive.sort(function(a,b) {
        return a.properties.Date - b.properties.Date;
      });
    }

    //END : Separate Full events and active events;

    // collate list:
    var collatedList = nearByActive.concat(nearByFull);
    $("#event-counter").text(collatedList.length + " " + (collatedList.length == 1 ? "event" : "events") );

    //Render list
    var ul = d3.select(that.containerLabel).select("ul#event-list");
    var dateFormat = d3.time.format("%B %d");
    var liContent =
          ul.selectAll("li.event-list-item")
            .data(collatedList, function(d){ return d.properties.id ;})
            .html(that.buildEvent)
            .enter()
              .append("li")
              .attr("data-location-id", function(d) { return d.properties.id })
              .attr("class", "event-list-item")
              .html(that.buildEvent)
              .on("mouseover", function() {
                var zip = $(this).attr("data-zip");
                var locationId = $(this).attr("data-location-id");

                d3.select("circle[data-location-id='" + locationId + "']")
                  .attr("fill", "#147FD7")
                  .classed("circle-selected", true);
              })
              .on("mouseout", function() {
                var locationId = $(this).attr("data-location-id");
                d3.select("circle[data-location-id='" + locationId+ "'")
                  .classed("circle-selected", false);
              });
            // .exit().remove();
            // .sort(function(a,b) {
            //   return a.properties.distance - b.properties.distance;
            // });
  };

  //Create tooltips
  this.initialize = function() {
    var that = this;

    $("[data-tooltip]").each(function(i,item) {
      var $this = $(this);
      $this.append(
          $("<div/>")
            .text($this.attr("data-tooltip"))
            .addClass("tooltip-data-item")
      );
      $this.append($("<div/>").addClass("tooltip-data-arrow"));
    });

    that.toggleEvents();
  };

  this.initialize();
};


var qtree = null;
var bernie = new bernMap.draw();
var bernieEvents = new bernMap.eventList("#map-event-list");

// d3.json("./csv-grab.php?u=" + encodeURIComponent(bernMap.constants.spreadsheetUrl),
  // d3.json("/d/july29.json",
  // function(data) {

window.dataCallback = function(){

  // bernMap.raw.workdata = d3.csv.parse(window.WORKDATA);

  bernMap.raw.events = window.EVENT_DATA;

  bernMap.d.meetupData = bernMap.raw.events.results;
  bernMap.d.rawMeetupData = bernMap.raw.events.results;

  $(bernMap.d.meetupData).each(function(i, item) {
    item.Date = rawDateFormat.parse(item.start_day);
    var tempTime = rawTime.parse(item.start_time);
    item.TimeStart = timeFormat(tempTime);
    item.TimeEnd = "";
    item.Link1 = "RSVP at BernieSanders.com," + item.url;
    item.link = item.url;
    item.Title = item.name;
    item.zip = item.venue_zip;
    item.Zipcode = item.venue_zip;
    item.show = true;

    item.lat = item.latitude;
    item.lon = item.longitude;

    item.attendee_count = parseInt(item.attendee_count);
    item.AttendeeCount = item.attendee_count;
    item.capacity = parseInt(item.capacity);

    // item.event_type_name = parseInt(item.is_official) ? "Official Event" : item.event_type_name;
    switch(item.event_type_name) {
      case "Iowa Event":
      case "New Hampshire Event":
      case "South Carolina Event":
      case "Organizing Meeting":
        // item.event_type_name =
        item.eventType = "Official Event";
        item.type = "CW"; break;
      case "Rally":
      case "Town Meeting":
        item.eventType = item.event_type_name;
        item.type = "R"; break;
      case "Gather ballot access signatures":
        item.eventType = "Ballot Access";
        item.type = "B"; break;
      // case "Debate Watch Parties (October 13)":
      // case "Debate Watch Party (Nov 14th)":
        // item.eventType = "Debate Watch Party";
      case "Phonebanks" :
        item.eventType = "Phonebank";
        item.type = "D"; break;
      default:

        switch (item.event_type_name) {
          case "Phonebanks" :
            item.eventType = "Phonebank";
            break;
          // case "Debate Watch Party (Nov 14th)":
          // case "Debate Watch Parties (October 13)" : item.eventType = "Debate Watch Party"; break;
          case "Gather ballot access signatures" : itemEventType = "Ballot Access"; break;
          case "Volunteer meeting to get organized or learn more " : item.eventType = "Volunteer Meeting"; break;
          case "Volunteer activity (flyering, calling, walking, etc)" : item.eventType = "Volunteer Activity"; break;
          default: item.eventType = "Volunteer Event"; break;
        }
        item.type = "E"; break;
    }

    if ( item.is_official == "1" &&
          !( item.event_type_name == 'Town Meeting' ||
             item.event_type_name == 'Rally') ) {
      item.eventType = "Official Event";
      item.type = "CW";
    }
  });

  var today = new Date(); today.setDate(today.getDate() - 1); today.setHours(0); today.setMinutes(0); today.setSeconds(0);
  bernMap.d.meetupData = bernMap.d.meetupData.filter(function(d) { return d.Date >= today; });
  loadZipcodeData();
};

window.loadCampaignOffices = function (officeLocations) {
  bernMap.constants.mainOffices = officeLocations;

  if (bernie) { bernie.mapOffices(); }
};

function loadZipcodeData() {

    function reformat(array) {
      var data = [];
      array.map(function(d,i) {
        data.push({
          id : i,
          type : "Feature",
          geometry: {
            coordinates: [+d.lon,+d.lat],
            type: "Point"
          },
          properties: d
        });
      });

      return data;
    }

    var _features = reformat(bernMap.d.meetupData);
    _features.sort(function(a, b) { return b.properties.attendee_count - a.properties.attendee_count; });
    bernMap.d.meetupData = _features;
    bernMap.d.zipcodes = {type: "FeatureCollection", features: _features };

    bernie.plot();
    if (bernMap.constants.mainOffices) { bernie.mapOffices(); }

    if (!bernMap.d.allZipcodes) {
      d3.csv('//d2bq2yf31lju3q.cloudfront.net/d/us_postal_codes.gz', function(data) {
        bernMap.d.allZipcodes = data;
        $jq(window).trigger("hashchange");
      });
    }

}

/* Event listeners */
$jq("input[name=eventtype]").on("click", function(d) {
  var $this = $(this);
  $(this).closest("form").submit();
});

$jq("input[name='entity-type']").on("change", function() {
  bernieEvents.currentEntity = $(this).val();
  bernieEvents.toggleEvents();
});

$jq("form input[type=radio]").on("click", function(d) {
  if( $jq("form input[name=zipcode]").val().length == 5 ) {
    $jq("form#zip-and-distance").trigger("submit");
  }
});

$jq("form input[name=zipcode]").on("keyup", function(e) {
  if (e.keyCode == 13|| e.which == 13) {
    return false;
  }

  if ( $(this).val().length == 5 ) {
    $jq("form#zip-and-distance").trigger("submit");
    // window.location.hash = $(this).closest("form").serialize();
    $(this).blur();
    document.activeElement.blur();
  } else {
    bernieEvents.hideError();
  }
});

//Just change value of select[name=daterange]
$jq(":not(#daterange-opt)").on({
  touchend : function(e) {
    $jq("#daterange-opt ul").hide();
  }
});

$jq("#daterange-opt").on(
    {
        touchend: function(e) {
          e.stopPropagation();
          $jq("#daterange-opt ul").show();
        },
        mouseover : function(e) {
          e.stopPropagation();
          $jq("#daterange-opt ul").show();
        },
        mouseout : function(e) {
          e.stopPropagation();
          $jq("#daterange-opt ul").hide();
        }
    }
  );
//For a more direct clicking of a filter...
$jq(".daterange-options-item").on("touchend", function(e) {
  e.stopPropagation();
  $jq("#daterange-opt ul").hide();
  $jq(this).find("label").trigger("click");
});

$jq("#daterange-opt ul li.daterange-options-item input[name='daterange']").on("change", function() {
  var value = $(this).attr("data-daterange");
  $jq("#daterange-opt ul").hide();
  $jq("form#zip-and-distance").trigger("submit");
});

//Sort..
$jq("#sort-opt").on(
    {
        touchend: function(e) {
          e.stopPropagation();
          $jq("#sort-opt ul").show();
        },
        mouseover : function(e) {
          e.stopPropagation();
          $jq("#sort-opt ul").show();
        },
        mouseout : function(e) {
          e.stopPropagation();
          $jq("#sort-opt ul").hide();
        }
    }
  );
//For a more direct clicking of a filter...
$jq(".sort-options-item").on("touchend", function(e) {
  e.stopPropagation();
  $jq("#sort-opt ul").hide();
  $jq(this).find("label").trigger("click");
});

$jq("#sort-opt ul li.sort-options-item input[name='sort']").on("change", function() {
  var value = $(this).attr("data-sort");
  $jq("#sort-opt ul").hide();
  $jq("form#zip-and-distance").trigger("submit");
  // window.location.hash = $(this).closest("form").serialize();
});


$jq("form#zip-and-distance").on("submit", function() {

  var serializedForm = $(this).closest("form").serialize();
  window.location.hash = serializedForm;

  if ( $jq("form input[name=zipcode]").val().length == 5 ) {
    // if( window.location.hash == "#" + serializedForm) {
    //   $jq(window).trigger("hashchange");
    // } else {

    // }
    // window.location.hash = serializedForm;
    // if mobile focus outside
    if ( $jq(window).width() < 720 ) {
      $jq("input#hidden-submit").focus();
    }
  } else {
    // window.location.hash = $(this).closest("form").serialize();
  }

  return false;
});

//Window Hashchange
$jq(window).on("hashchange", function(){
  var hash = window.location.hash;

  if (hash.length > 1) {
    parameters = bernie._deserialize(hash.substr(1));

    if ($jq("input[name=distance]:checked", "form#zip-and-distance").val() != parameters.distance ) {
      $jq("form input[name=distance]").removeAttr("checked");
      $jq("form input[name=distance][value=" + parameters.distance + "]").prop("checked", true);
    }

    if ($jq("form input[name=zipcode]").val() != parameters.zipcode) {
      $jq("form input[name=zipcode]").val(parameters.zipcode);
    }

    if ($jq("form select[name=daterange]").val() != parameters.daterange) {
      $jq("form input[name=daterange][value=" + parameters.daterange + "]").prop("checked", true);
    }

    if ($jq("form select[name=sort]").val() != parameters.sort) {
      $jq("form input[name=sort][value=" + parameters.sort + "]").prop("checked", true);
    }

    if (parameters.eventtype && parameters.eventtype.length > 0) {
      $jq("form input[name=eventtype]").prop("checked", false);
      parameters.eventtype.forEach(function(d) {
        $jq("form input[name=eventtype][value=" + d + "]").prop("checked", true);
      });
    }


    //Listen to event types
    if (!parameters.eventtype ||
          $jq("form input[name=eventtype]").length != parameters.eventtype.length) {
      $(".etype-name-vis.show-all").addClass("activated");
      $(".etype-name-vis.hide-all").removeClass("activated");
    }
    else {
      $(".etype-name-vis.hide-all").addClass("activated");
      $(".etype-name-vis.show-all").removeClass("activated");
    }

    if ((!parameters.eventtype && !bernMap.d.initialLoad) || (parameters.eventtype && parameters.eventtype.filter(function(d) { return d == "O" }).length == 0)) {
      $(".bernie-main-office").css("visibility", "hidden");
    } else { //bernMap.d.initialLoad || length > 1
      $(".bernie-main-office").css("visibility", "visible");
    }

    bernMap.sort = parameters.sort;
    switch(bernMap.sort) {
      case "distance": $("#sort-value").text("By Distance"); break;
      case "time": $("#sort-value").text("By Time"); break;
    }

    //if bernMap daterange does not match, refilter
    // if (bernMap.daterange != parameters.daterange) {
      bernMap.daterange = parameters.daterange;


      if (bernMap.daterange != "all-events") {
        var today = new Date();
        today.setDate(today.getDate() - 1);
        today.setHours(0);
        today.setMinutes(0);
        today.setSeconds(0);

        var future = null;
        switch (bernMap.daterange) {
          case "today" :
            $("#daterange-value").text("Today");
            future = new Date();
            break;
          case "this-week":
            $("#daterange-value").text("This Week");
            future = new Date(); future.setDate(future.getDate()+7);
            break;
          case "in-2-weeks":
            $("#daterange-value").text("In 2 Weeks");
            future = new Date(); future.setDate(future.getDate()+14);
            break;
          case "this-month":
            $("#daterange-value").text("This Month");
            future = new Date(); future.setDate(future.getDate()+31);
            break;
        }

        if (future) {
          future.setHours(23); future.setMinutes(59); future.setSeconds(59);
        }
      } else {
        $("#daterange-value").text("All Events");
        future = null;
        // bernMap.d.meetupData = bernMap.d.rawMeetupData;
      }

    // }

    //Filter by time
    if (bernMap.d.meetupData) {
      bernMap.d.meetupData.forEach(function(d) {
        if (future == null && parameters.eventtype == undefined) {
          if (bernMap.d.initialLoad) {
            d.show = true;
          } else {
            d.show = false;
          }
        } else if (future == null && parameters.eventtype.length == 0) {
          d.show = true;
        } else if (future == null) {
          d.show = parameters.eventtype.indexOf(d.properties.type) >= 0;
        } else {
          d.show = ((d.properties.Date >= today && d.properties.Date <= future)
                    && parameters.eventtype.indexOf(d.properties.type) >= 0);

        }

      });

      bernMap.d.zipcodes.features = bernMap.d.meetupData;
    }

    //Will avoid focusing if zipcode is not equal to 5
    if (parameters.zipcode.length == 5) {
      if(bernMap.d.allZipcodes){
        bernie.focusZipcode(parameters);
        bernieEvents.filterEvents(parameters.zipcode, parameters.distance);
      }
    } else {
      bernie.replot();
    }

  } else {

    if (window.MAP_CENTER) {
      bernMap.mapBox.setView(window.MAP_CENTER.latlng, window.MAP_CENTER.zoom);
    } else {
      bernMap.mapBox.setView([37.8, -96.9], 4);
    }
    var offset = bernMap.mapBox.getSize().x * 0.15;
    if (WIDTH >= 720) {
      bernMap.mapBox.panBy(new L.Point(offset,0), {animate: false});
    }
    $("form#zip-and-distance").submit();
  }

  bernMap.d.initialLoad = false;

});
