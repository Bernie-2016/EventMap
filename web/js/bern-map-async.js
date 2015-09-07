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

//Window Resize
$jq(window).on("resize", function() {
  var h = $jq("#header").height() + $jq("#main-title-area").height();
  var screenHeight = screen.height;
  var wH = $jq(window).height();
  var padding = 20;

  //Change billionaire size ^_-
  //Media
  if ($jq(window).width() < 720) {
    // alert("X");
    var _formHeight = $("#map-event-list").outerHeight();
    $("#map").height(screenHeight - _formHeight - (screenHeight*0.25))
      .css("marginTop", (_formHeight) + "px");
    $("#map-event-list").css("top", "-" + _formHeight + "px").width($("#map").width() + "px");
    $("#event-results-area").css("top", _formHeight + $("#map").height() + "px");

    // $("input[name='zipcode']").attr("placeholder", "zipcode");
  } else {
    $("#map-section, #map").height(wH - h - 25).css("marginTop", "auto");
    $("#event-list-area").css("maxHeight", wH - h - (padding * 2) - 240 - 25)
    $("#map-event-list").css("top", "20px");
    // $("input[name='zipcode']").attr("placeholder", "Enter zipcode to find events");
  }


});
$jq(window).trigger("resize");

L.mapbox.accessToken = "pk.eyJ1IjoiemFja2V4bGV5IiwiYSI6Ijc2OWFhOTE0ZDllODZiMTUyNDYyOGM5MTk1Y2NmZmEyIn0.mfl6MGaSrMmNv5o5D5WBKw";
var mapboxTiles = L.tileLayer('http://{s}.tiles.mapbox.com/v4/mapbox.streets/{z}/{x}/{y}.png?access_token=' + L.mapbox.accessToken, {
    attribution: '<a href="http://www.openstreetmap.org/copyright" target="_blank">&copy; OpenStreetMap contributors</a>'
});

var WIDTH = $jq(window).width();

var bernMap = bernMap || {};
bernMap.constants = {};
// bernMap.constants.spreadsheetUrl = "/d/july29.json";
bernMap.constants.spreadsheetUrl = "https://go.berniesanders.com/page/event/search_results?format=json&wrap=no&orderby[0]=date&orderby[1]=desc&event_type=13&mime=text/json&limit=4000&country=*";
bernMap.constants.mainOffices = {"locs":[{"address":"3420 Martin Luther King Parkway Suite 100 Des Moines IO 50310","name":"Des Moines - State HQ","phone":"(515) 277-6073 ","lat":41.6262238,"lon":-93.6523551,"photo":"/img/offices/IO-1.png"},{"address":"3839 Merle Hay #259 Des Moines IO 50310","name":"Polk","phone":"(515) 251-6335","lat":41.6308099,"lon":-93.697183,"photo":"/img/offices/IO-2.png"},{"address":"500 West Broadway #150 Council Bluffs IO 51503","name":"Council Bluffs","phone":"(712) 323-7232","lat":41.2612218,"lon":-95.8499378,"photo":"/img/offices/IO-3.png"},{"address":"105 N. Court St Ottumwa IO 52501","name":"Ottumwa","phone":"(641) 682-0686","lat":41.019002,"lon":-92.411845,"photo":"/img/offices/IO-4.png"},{"address":"1918 Pierce St Sioux City IO 51104","name":"Sioux City","phone":"(712) 258-9445","lat":42.5107837,"lon":-96.4048652,"photo":"/img/offices/IO-5.png"},{"address":"1728 Central Ave Fort Dodge IO 50501","name":"Fort Dodge","phone":"(515) 955-2016","lat":42.5064161,"lon":-94.1739369,"photo":"/img/offices/IO-6.png"},{"address":"303 Main St Ames IO 50010","name":"Ames","phone":"(515) 233-4414","lat":42.0251178,"lon":-93.6139446,"photo":"/img/offices/IO-15.png"},{"address":"513 Federal Ave Mason City 50401","name":"Mason City","phone":"(641) 424-4505","lat":43.1465735,"lon":-93.2010729,"photo":"/img/offices/IO-7.png"},{"address":"217 W 4th St Waterloo IO 50701","name":"Waterloo","phone":"(319) 232-1535","lat":42.494518,"lon":-92.340007,"photo":"/img/offices/IO-8.png"},{"address":"198 Main St #4 Dubuque IO 52001","name":"Dubuque","phone":"(563) 556-5276","lat":42.495859,"lon":-90.66412,"photo":"/img/offices/IO-9.png"},{"address":"736 Federal St #2101 Davenport IO 52801","name":"Davenport","phone":"(563) 323-0559","lat":41.5247596,"lon":-90.5633143,"photo":"/img/offices/IO-10.png"},{"address":"725 11th St Marion IO 52302","name":"Cedar Rapids","phone":"(319) 373-3088","lat":42.0337172,"lon":-91.5980691,"photo":"/img/offices/IO-11.png"},{"address":"Office #4 Second Floor 101 West Main Street West Branch IO 52358","name":"West Branch","phone":"(319) 643-3779","lat":41.6715074,"lon":-91.3468293,"photo":"/img/offices/IO-12.png"},{"address":"702 S Gilbert #101 Iowa City IO 52240","name":"Iowa City","phone":"(319) 338-1204","lat":41.6525972,"lon":-91.530271,"photo":"/img/offices/IO-13.png"},{"address":"1525 Mt. Pleasant St Burlington IO 52601","name":"Burlington ","phone":" (319) 752-8867  ","lat":40.819149,"lon":-91.120272,"photo":"/img/offices/IO-14.png"},{"address":"312 Mass Ave NE, Washington, DC 20002","name":"Washington D.C.","phone":"-","lat":38.8943255,"lon":-77.0013426,"photo":"/img/offices/DC-01.png"}]};


if (WIDTH >= 720) {
  bernMap.mapBox = new L.Map("map", {center: [37.8, -96.9], zoom: 4, paddingTopLeft: [400, 0], scrollWheelZoom: false}).addLayer(mapboxTiles);
} else {
  bernMap.mapBox = new L.Map("map", {center: [37.8, -96.9], zoom: 4, zoomControl: false, paddingTopLeft: [400, 0],  scrollWheelZoom: false}).addLayer(mapboxTiles);
}
// bernMap.mapBox = new L.Map("map", {center: [37.8, -96.9], zoom: 4, paddingTopLeft: [400, 0], scrollWheelZoom: true}).addLayer(mapboxTiles);
// bernMap.mapBox.touchZoom.disable();

var offset = bernMap.mapBox.getSize().x * 0.15;
bernMap.mapBox.panBy(new L.Point(offset,0), {animate: false});

bernMap.d = {};
bernMap.scale = {};
bernMap.scale.radScale = d3.scale.pow().domain([0, 5, 150]);
bernMap.daterange = "all-events";
bernMap.d.rsvp=0;
bernMap.d.capacity = 0;
bernMap.d.zipcodes = null;
bernMap.d.allZipcodes = null;
bernMap.d.meetupData = null;
bernMap.d.rawMeetupData = null;
bernMap.d.targetZipcodes = null;
bernMap.d.aggregatedRSVP = null;

var bernMap = bernMap || {};
bernMap.draw = function() {


  this.filteredZipcode = null;

  this.svg = d3.select(bernMap.mapBox.getPanes().overlayPane).append("svg");
  this.activityLayer = this.svg.append("g").attr("class","leaflet-zoom-hide");
  this.zipcodeElements = null;
  this.mainOffices = null;
  this.centerItem = null;
  this.visibleTypes = { volunteerWork: true, grassrootsEvent: true, officialRally: true};

  this.currentZipcode = null;

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
    return query.split("&").map(function(d) { var q = d.split("="); return [q[0], q[1]]; }).reduce(function(init, next) { init[next[0]] = next[1]; return init;}, {});
  };

  this._getRadius = function(d) {
    var that = this;
    switch (d.properties.type) {
      case "CW" : return 5; break;
      case "E" : return 5; break;
      case "R" : return 9; break;
    }
  };

  this._getVisibility = function(d) {
    var that = this;
    switch (d.properties.type) {
      case "CW" : return that.visibleTypes.volunteerWork ? "inherit" : "hidden"; break;
      case "E" : return that.visibleTypes.grassrootsEvent ? "inherit" : "hidden"; break;
      case "R" : return that.visibleTypes.officialRally ? "inherit" : "hidden"; break;
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
    };

    that.replot();
  };

  // *********************
  // FOCUS ZIPCODE
  // *********************
  this.focusZipcode = function(hash) {
    var that = this;
    var params = that._deserialize(hash);

    var target = bernMap.d.allZipcodes.filter(function(d) { return d.zip == params.zipcode; });

    if (target.length == 0) {
      bernieEvents.setError("Zipcode does not exist. <a href=\"https://go.berniesanders.com/page/event/search_results?orderby=zip_radius&zip_radius%5b0%5d=" + params.zipcode + "&zip_radius%5b1%5d=100&country=US&radius_unit=mi\">Try our events page</a>");
    }
    else {
      var t = target[0];



      //Plot zipcode center
      $("circle#center-item").remove();

      // var centerCoords = that._projectPoint(t.lon, t.lat);
      // that.centerItem = that.activityLayer.append("circle")
      //       .datum(t)
      //       .attr("id", "center-item")
      //       .attr("cx", centerCoords[0])
      //       .attr("cy", centerCoords[1])
      //       .attr("r", bernMap.mapBox.getZoom() * 0.4 )
      //       .attr("fill", "#147FD7")
      //       .attr("opacity", 0.9);

      //Focus on map

      bernMap.mapBox.setView([parseFloat(t.lat), parseFloat(t.lon)], that._getZoomValue(parseInt(params.distance)), { animate: false });

      // if (that.centerItem) { bernMap.mapBox.removeLayer(that.centerItem); }
      // that.centerItem = L.marker([t.lat, t.lon],
      //                       { bounceOnAdd: true,
      //                         bounceOnAddOptions: {duration: 500, height: 100},
      //                         bounceOnAddCallback: function() {console.log("done");}
      //                       }).addTo(bernMap.mapBox);

      var offset = bernMap.mapBox.getSize().x * 0.15;


      bernMap.mapBox.panBy(new L.Point(offset,0), {animate: false});
      that.replot();

      //Setting the marker

      if (that.currentZipcode != params.zipcode) {
        if (that.centerItem) { bernMap.mapBox.removeLayer(that.centerItem); }
        that.centerItem = L.marker([t.lat, t.lon],
                              { bounceOnAdd: true,
                                bounceOnAddOptions: {duration: 700, height: 50}
                                // bounceOnAddCallback: function() {console.log("done");}
                              }).addTo(bernMap.mapBox);
        that.currentZipcode = params.zipcode;
      }
    }


  };

  // this.filter = function(str) {
  //   var that = this;
  //   if ( str == '' ) { that.filteredZipcode = null; }
  //   else {
  //     if ( that.filteredZipcode == null ) {
  //       that.filteredZipcode = bernMap.d.allZipcodes.filter(function(d) { return d.zip.indexOf(str) >= 0; });
  //     } else {
  //       that.filteredZipcode = that.filteredZipcode.filter(function(d) { return d.zip.indexOf(str) >= 0; });
  //     }
  //   }

  // };

  this.mapOffices = function() {
    var that = this;
    //<image xlink:href="firefox.jpg" x="0" y="0" height="50px" width="50px"/>
    if (!that.mainOffices) {
      that.mainOffices = that.activityLayer.selectAll("image")
          .data(bernMap.constants.mainOffices.locs)
          .enter()
            .append("image")
              .attr("xlink:href", "/img/icon/star.png")
              .attr("xlink:xlink:href", function() { return "/img/icon/star.png";})
              .attr("width", "14px")
              .attr("height", "14px")
              .attr("class", "bernie-main-office")
              .each(function(d) {
                var coordinates = that._projectPoint(d.lon, d.lat);
                  d3.select(this).attr("x", coordinates[0] - 7)
                      .attr("y", coordinates[1] - 7)
                  ;
                  d3.select(this).attr("xlink\:href", "/img/icon/star.png");
              });

        that.mainOffices.on("click", function(d) {
          that.popupOffice(d);
        });

    } else {
      that.mainOffices =
          that.activityLayer.selectAll("image")
              .each(function(d) {
                var coordinates = that._projectPoint(d.lon, d.lat);
                  d3.select(this).attr("x", coordinates[0] - 7)
                      .attr("y", coordinates[1] - 7)
                  ;
                  // d3.select(this).attr("xlink\:href", "/img/icon/star.png");
              });
    }
    // console.log(that.mainOffices);

  };

  this.plot = function () {
    var that = this;
    if (!bernMap.d.zipcodes) return;

    bernMap.scale.radScale.range([parseFloat(bernMap.mapBox.getZoom()),
                                      bernMap.mapBox.getZoom(),

                                      bernMap.mapBox.getZoom()
                                      + (bernMap.mapBox.getZoom()  * 3)]);

    // console.log(bernMap.mapBox.getZoom());
    that.activityLayer.selectAll("circle").remove();

    that.zipcodeElements = that.activityLayer.selectAll("circle")
                              .data(bernMap.d.zipcodes.features).enter()
                              .append("circle")
                              .attr("data-maxcapacity", function(d) { return d.properties.capacity > 0 && d.properties.attendee_count >= d.properties.capacity ? "true" : "false" } )
                              .attr("data-location-id", function(d) { return d.properties.id; })

                              //   function(d) {
                              //     return bernMap.scale.radScale(d.properties.attendee_count);
                              // })
                              .attr("stroke-width", 0)
                              .attr("opacity", 0.5)
                              .attr("class", function(d) {
                                switch (d.properties.type) {
                                  case "CW" : return "campaign-work"; break;
                                  case "E" : return "grassroots-event"; break;
                                  case "R" : return "official-rally"; break;
                                }
                              })
                              .attr("r", that._getRadius).style("visibility", function(d) {
                                switch (d.properties.type) {
                                  case "CW" : return that.visibleTypes.volunteerWork ? "inherit" : "hidden"; break;
                                  case "E" : return that.visibleTypes.grassrootsEvent ? "inherit" : "hidden"; break;
                                  case "R" : return that.visibleTypes.officialRally ? "inherit" : "hidden"; break;
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




    // that.zipcodeElements
          // .transition()
          // .duration(500)
          // .delay(function() { return Math.random() * 3000})
          // .attr("r", bernMap.mapBox.getZoom() * 3);

    //initialize event for zipcode
    that.zipcodeElements.on("click", function(d) {
      that.popupInfo(d);
      // $("input[name=zipcode]").val(d.properties.zip);
      // $jq("form#zip-and-distance").submit();
    });

    // var bounds = that.activityLayer[0][0].getBoundingClientRect();
    var bounds = that.activityLayer[0][0].getBBox();

    that.svg.attr("width", (bounds.width + 20) + "px")
      .attr("height", (bounds.height + 20) + "px")
      // .attr("transform", "translate(" + -bounds.left + "," + -bounds.top + ")");
      .style("left", bounds.x-10 + "px")
      .style("top", bounds.y-10 + "px");

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

    // if (that.centerItem) {
    //   that.centerItem.each(function(d) {
    //                   var coordinates = that._projectPoint(d.lon, d.lat)

    //                     d3.select(this).attr("cx", coordinates[0])
    //                         .attr("cy", coordinates[1])
    //                         .attr("r", bernMap.mapBox.getZoom() * 0.4)
    //                         .attr("opacity", 0.2)
    //                     ;
    //                 });
    // }


    that.zipcodeElements.each(function(d) {
      var coordinates = that._projectPoint(d.geometry.coordinates[0], d.geometry.coordinates[1]);
        d3.select(this).attr("cx", coordinates[0])
            .attr("cy", coordinates[1])
            .attr("r", that._getRadius)
            .style("visibility", function(d) { return that._getVisibility(d); })
            .attr("opacity", 0.6)
        ;
    });

    var bounds = that.activityLayer[0][0].getBBox();

    that.svg.attr("width", (bounds.width + 0) + "px")
      .attr("height", (bounds.height + 0) + "px")
      // .attr("transform", "translate(" + -bounds.left + "," + -bounds.top + ")");
      .style("left", bounds.x + "px")
      .style("top", bounds.y + "px");

    that.activityLayer.attr("transform", "translate(" + -bounds.x + "," + -bounds.y + ")");

  };

  var _that = this;
  this.initialize = function() {


    bernMap.mapBox.on('zoomstart', function() {
      _that.activityLayer.style("visibility","hidden");
      // in_need_layer.style("visibility", "hidden");
      // percent_change_layer.style("visibility", "hidden");
      // funding_layer.style("visibility", "hidden");
    });
    bernMap.mapBox.on('zoomend', function() {
        // triggerLayerChange();
        _that.replot();
        _that.mapOffices();
        _that.activityLayer.style("visibility","visible");
        // regionalLayer.recalibrateLayer();
    });

    bernMap.mapBox.on('moveend', function() {
      _that.mapOffices();
    });
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
              .html('<span style="vertical-align: middle"><img src="/img/icon/star.png" width="14px" height="14px"></span><span class="event-text">Campaign Office</span>'))
        .append($("<h5/>").html("<span><img src='/img/icon/map.png'/>" + d.address + "</span>"))
        .append($("<h5/>").html("<span><img src='/img/icon/phone.png'/>" + d.phone + "</span>"))
        .append($("<div/>").html("<a class='button-rsvp' href='https://www.google.com/maps?q=" + encodeURIComponent(d.address) + "' target='_blank'><img src='/img/icon/map-white.png'/> VIEW MAP</a>"));

    return $popupItem.clone().wrap('<div>').parent().html();
  };

  this.buildEvent = function(d) {


     var eventType, eventText;

     switch (d.properties.type) {
      case "CW": eventType = 'campaign-work'; eventText = "Volunteer Activity"; break;
      case "E" : eventType = 'meetup'; eventText = "Meeting"; break;
      case "R" : eventType = 'rally'; eventText = "Official Event"; break;
     }


     if (d.properties.attendee_count >= d.properties.capacity && d.properties.capacity > 0) {
          return "<h5><span class='event-item-date'>"
            + (d.properties.distance && !isNaN(d.properties.distance) ? ("~" + d3.round(d.properties.distance,1) + "MI&nbsp;&nbsp; ") : "")
            + (d.Date ? (" " + dateFormat(d.properties.Date)) : "")
            //+ (d.properties.TimeStart ? "" + d.properties.TimeStart + (d.properties.TimeEnd ? " - " + d.properties.TimeEnd : "") + "" : "")
            + (d.properties.TimeStart ? d.properties.TimeStart : "")
            + "</span></h5>"
            + "<h3><span class='event-item-name event-full'>" + d.properties.Title + " (FULL)</span></h3>"
            + "<div class='event-type " + eventType + "'><span class='event-bullet'>&bull;</span><span class='event-text'>" + eventText + "</span></div>"

            // + (d.properties.description != "" ? ("<h4 class='event-organizer'>" + d.properties.description +"</h4>") : "")
            + "<h5 class='event-location'>" + d.properties.location + "</h5>"
            + "<p><a href='javascript: void(null);' target='_blank' class='button-rsvp button-full button-disabled'>FULL</a>"

            + (eventType =="rally" || d.properties.attendee_count <= 5 ? "" : ("<span class='rsvp-counter'>" + d.properties.attendee_count + " SIGN UPS</span></p>" )) ;;
        }
        else {
          return "<h5><span class='event-item-date'>"
            + (d.properties.distance && !isNaN(d.properties.distance) ? ("~" + d3.round(d.properties.distance,1) + "MI&nbsp;&nbsp; ") : "")
            + (d.properties.Date ? ("" + dateFormat(d.properties.Date)) : "")
            + (d.properties.TimeStart ? " &nbsp;&nbsp; " + d.properties.TimeStart : "")
            + "</span></h5>"
            + "<h3><a target='_blank' href='" + d.properties.link + "'><span class='event-item-name'>" + d.properties.Title + "</span></a></h3>"
            + "<div class='event-type " + eventType + "'><span class='event-bullet'>&bull;</span><span class='event-text'>" + eventText + "</span></div>"
            // + (d.properties.description != "" ? ("<h4 class='event-organizer'>" + d.properties.description +"</h4>") : "")
            + "<h5 class='event-location'>" + d.properties.location + "</h5>"
            + "<p><a href='" + d.properties.link + "' target='_blank' class='button-rsvp'>RSVP</a>"

            + (eventType =="rally" || d.properties.attendee_count <= 5 ? "" : ("<span class='rsvp-counter'>" + d.properties.attendee_count + " SIGN UPS</span></p>" )) ;
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
    var nearByOffices = bernMap.constants.mainOffices.locs.filter (function(d) {
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
      $("input#entity-type-offices").attr("disabled", "disabled");
      $("input#entity-type-events").prop("checked", true);
      $("#office-counter").text("OFFICES");

      that.toggleEvents();
    }

    //Filter events
    var nearByZipcodes = bernMap.d.zipcodes.features.filter(function(d) {
                            var compC = [parseFloat(d.properties.latitude), parseFloat(d.properties.longitude)];

                            var distance = that._getDistanceInMi(targC[0], targC[1], compC[0], compC[1]);
                            d.properties.distance = distance;

                            // consoel.log(distance);
                            return  distance <= allowedDistance;
                        });

    nearByZipcodes = nearByZipcodes.map(function(d) { return { "distance" : d.properties.distance, properties: d.properties}; });


    if (nearByZipcodes.length == 0) {

      $("ul#event-list").append($("<li/>").css("text-align", "center").html('<a href="https://go.berniesanders.com/page/event/create" class="contribute contribute-big" target="_blank">HOST AN EVENT</a>'));

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
    nearByActive.sort(function(a,b) {
      return a.distance - b.distance;
    });

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

    bernMap.d.meetupData = bernMap.raw.events.results; //.filter(function(d) { return !isNaN(d.lon) && !isNaN(d.lat); });
    bernMap.d.rawMeetupData = bernMap.raw.events.results;

    // bernMap.d.meetupData = bernMap.raw.workdata.filter(function(d) { return d.hide !== "Y"; });
    //bernMap.d.rawMeetupData = bernMap.d.meetupData;

    // $jq("#meetup-counter").text(d3format(bernMap.raw.workdata.settings.count));
    // $jq("#rsvp-counter").text(d3format(bernMap.raw.workdata.settings.rsvp));

    $(bernMap.d.meetupData).each(function(i, item) {


      item.Date = rawDateFormat.parse(item.start_day);



      var tempTime = rawTime.parse(item.start_time);
      item.TimeStart = timeFormat(tempTime);
      item.TimeEnd = "";
      item.Link1 = "RSVP at BernieSanders.com," + item.url;
      item.link = item.url;
      item.OrganizerWebsite = "http://www.berniesanders.com";
      item.Organizer = "Bernie Sanders Campaign Volunteers";
      // item.Date = rawDateFormat.parse(item.start_day);
      item.Title = item.name;
      item.zip = item.venue_zip;
      item.Zipcode = item.venue_zip;

      // item.Location = item.venue_addr1 + " "
                        // + item.venue_city + " "
                        // + item.venue_state_cd + " "
                        // + item.venue_zip;
      // item.location = item.Location;


      item.lat = item.latitude;
      item.lon = item.longitude;

      item.attendee_count = parseInt(item.attendee_count);
      item.AttendeeCount = item.attendee_count;
      item.capacity = parseInt(item.capacity);

      item.event_type_name = parseInt(item.is_official) ? "Official Event" : item.event_type_name;
      switch(item.event_type_name) {
        case "Volunteer activity (flyering, calling, walking, etc)":
          item.type = "CW"; break;
        case "Volunteer meeting to get organized or learn more" :
          item.type = "E"; break;
        case "Official Event":
          item.type = "R"; break;
      }
      // bernMap.d.rsvp += parseInt(item.attendee_count);
      // bernMap.d.capacity += parseInt(item.capacity);

    });

    // var weekStart = rawDateFormat.parse("7/05/2015");
    // var weekEnd = rawDateFormat.parse("7/12/2015");

    // var today = new Date();
    //     today.setDate(today.getDate() - 1);
    //     today.setHours(0);
    //     today.setMinutes(0);
    //     today.setSeconds(0);

    // var inTwoMonths = new Date(new Date(today).setMonth(today.getMonth()+2));

    // bernMap.d.meetupData = bernMap.d.meetupData.filter(function(d){
    //   // return d.Date >= today;
    //   return d.Date <= inTwoMonths && d.Date >= today;
    // });


    loadZipcodeData();

  };


  // var map = bernMap.d.meetupData.map(function(d) { return [d.Zipcode, d]; });
  // bernMap.d.aggregatedRSVP = map.reduce(
  //     function(init, next) {

  //       if (init[next[0]]) {
  //         init[next[0]].push(next[1]);
  //       } else {
  //         init[next[0]] = [next[1]];
  //       }
  //       return init;
  //       //  = init[next[0]]
  //       // ? init[next[0]] + parseInt(next[1])
  //       // : [next[1]]; return init;
  //     }
  // , {});

  // bernMap.d.meetupData

// });

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
    bernMap.d.zipcodes = {type: "FeatureCollection", features: _features };
    bernie.plot();
    bernie.mapOffices();

    ////d2bq2yf31lju3q.cloudfront.net
    if (!bernMap.d.allZipcodes) {
      d3.csv('//d2bq2yf31lju3q.cloudfront.net/d/us_postal_codes.gz', function(data) {
        bernMap.d.allZipcodes = data;
        $jq(window).trigger("hashchange");
      });
    }

}

$jq("input[name=eventtype]").on("click", function(d) {
  var $this = $(this);
  bernie.changeVisibility($this.val(),$this[0].checked);
});

$jq("input[name='entity-type']").on("change", function() {
  bernieEvents.currentEntity = $(this).val();
  bernieEvents.toggleEvents();
});

$jq("form input[type=radio]").on("click", function(d) {
  if( $jq("form input[name=zipcode]").val().length == 5 ) {
    window.location.hash = $(this).closest("form").find(":not([name='eventtype'])").serialize();
  }
});

$jq("form input[name=zipcode]").on("keyup", function(e) {
  // bernie.filter($(this).val());
  if (e.keyCode == 13|| e.which == 13) {
    return false;
  }

  if ( $(this).val().length == 5 ) {
    window.location.hash = $(this).closest("form").find(":not([name='eventtype'])").serialize();
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
  // $jq("[name='daterange']").val(value);
  // $jq("#daterange-opt ul").hide();
  // $("[name='daterange']").trigger("change");
  $jq("#daterange-opt ul").hide();
  $jq("form#zip-and-distance").trigger("submit");
});

$("select[name='daterange']").on("change", function() {
  // console.log("Hello World", $(this).val());
});

$jq("form#zip-and-distance").on("submit", function() {
  if ( $jq("form input[name=zipcode]").val().length == 5 ) {

    var serializedForm = $(this).closest("form").find(":not([name='eventtype'])").serialize();
    if( window.location.hash == "#" + serializedForm) {
      $jq(window).trigger("hashchange");
    } else {
      window.location.hash = serializedForm;
    }

    // if mobile focus outside
    if ( $jq(window).width() < 720 ) {
      $jq("input#hidden-submit").focus();
    }

    // $jq("form input[name=zipcode]").blur();
  } else {
    window.location.hash = $(this).closest("form").find(":not([name='eventtype'])").serialize();
  }
  return false;
});

//Window Hashchange
$jq(window).on("hashchange", function(){
  var hash = window.location.hash;

  if (hash.length > 1) {
    //Set it on the form
    var parameters = bernie._deserialize(hash.substr(1));

    if ($jq("input[name=distance]:checked", "form#zip-and-distance").val() != parameters.distance ) {
      $jq("form input[name=distance]").removeAttr("checked");
      $jq("form input[name=distance][value=" + parameters.distance + "]").prop("checked", true);
    }

    if ($jq("form input[name=zipcode]").val() != parameters.zipcode) {
      $jq("form input[name=zipcode]").val(parameters.zipcode);
    }

    if ($jq("form select[name=daterange]").val() != parameters.daterange) {
      $jq("form select[name=daterange]").val(parameters.daterange);
    }

    //if bernMap daterange does not match, refilter
    if (bernMap.daterange != parameters.daterange) {
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
        future.setHours(23); future.setMinutes(59); future.setSeconds(59);

        bernMap.d.meetupData = bernMap.d.rawMeetupData.filter(function(d) { return d.Date >= today && d.Date <= future; });
        loadZipcodeData();
      } else {
        $("#daterange-value").text("All Events");
        bernMap.d.meetupData = bernMap.d.rawMeetupData;
        loadZipcodeData();
      }

    }

    //Will avoid focusing if zipcode is not equal to 5
    if (parameters.zipcode.length == 5) {
      if(bernMap.d.allZipcodes){
        bernie.focusZipcode(hash.substr(1));
        bernieEvents.filterEvents(parameters.zipcode, parameters.distance);
      }
    }


  } else {
    if (bernMap.daterange != "all-events") {
      bernMap.d.meetupData = bernMap.d.rawMeetupData;
      loadZipcodeData();
    }

    bernMap.mapBox.setView([37.8, -96.9], 4);
    var offset = bernMap.mapBox.getSize().x * 0.15;
    bernMap.mapBox.panBy(new L.Point(offset,0), {animate: false});
  }

});

// if ($jq("form input[name=zipcode]").val().length != 0 ) {
//   $jq("form#zip-and-distance").trigger("submit");
// } else {
//   $jq(window).trigger("hashchange");
// }
