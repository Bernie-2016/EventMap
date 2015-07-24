<?php
  require_once('./inc/_memcached.inc');

  define('BERNIE2016_URL', "https://go.berniesanders.com/page/event/search_results?format=json&wrap=no&orderby[0]=date&orderby[1]=desc&event_type=13&mime=text/json&limit=4000&country=*");
  define('ZIPCODES_URL', "./d/us_postal_codes.csv");

  $title = "July 29 Nationwide Organizing Meeting - Find Meetings Near You | Bernie Sanders 2016 Events";
  $description = "On July 29th, Bernie is asking Americans from across the country to come together for a series of conversations about how we can organize an unprecedented grassroots movement that takes on the greed of Wall Street and the billionaire class.";
  $og_img = "http://www.bernie2016events.org/img/social-july29.jpg";

  $zipcode = isset($_GET['zipcode']) ? $_GET['zipcode'] : "" ;
  $distance = isset($_GET['distance']) ? $_GET['distance'] : 5 ;
?>
<?php require_once(__DIR__ . '/inc/_header.inc'); ?>
<link href='https://api.tiles.mapbox.com/mapbox.js/v2.1.9/mapbox.css' rel='stylesheet' />
<link href='/css/map.css?version=<?php echo $APPVERSION ?>' rel='stylesheet' />
<!-- <section>
  <h2 class='page-title'><span id='page-title-event-count'></span> 07/29: Growing our political revolution</h2>
  <h4 class='page-subtitle'>34 meetings with 23,059 RSVPs. <a href='http://goo.gl/forms/1dCkCj4zi9' target='_blank'>Submit an event</a></h5>
  <h5 class='page-subtitle'>Bernie is asking Americans from across the country to come together for a series of conversations about how we can organize an unprecedented grassroots movement that takes on the greed of Wall Street and the billionaire class.</h5>
</section> -->
<section id='main-title-area'>
  <h4 style='font-size: 0.8em'><strong><span id="meetup-counter"><img src='/img/icon/ajax-loader.gif' /> Loading</span> Organizing Meetings</strong> <span>&bull;</span> <strong style='font-size: 1.2em; color: #ea504e;'><span id='rsvp-counter'><img src='/img/icon/ajax-loader-red.gif'></span> RSVPs <span style='font-size: 0.7em'></span><span id="capacity-counter"></span> </strong> <span>&bull;</span><span>Discover nearby meetings or</span> <a href='https://go.berniesanders.com/page/event/create' target='_blank'>Host an Event</a>&nbsp;&nbsp;
    <!-- <div id='social' style="padding-top: 4px;"> -->
    <a href="https://twitter.com/share" class="twitter-share-button" data-url="http://www.bernie2016events.org/july29" data-text="Join the July 29 @BernieSanders organizing kick-off! Find nearby events and #FeelTheBern @BernieMeetups" data-related="RedditForSanders">Tweet</a>
  <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
  <div class="fb-share-button" data-href="http://www.bernie2016events.org/july29" data-layout="button_count"></div>
  <!-- </div> -->
</h4>
</section>
<section id='map-section' />
 <!-- <div id='map-legend'>
    <svg width="100" height="100">
      <circle id="percent-100" fill="#ea504e" opacity="0.4" r="50" cx="50" cy="50"></circle>
      <text x="" y="10" style=" font-size: 10px; text-transform: uppercase; " text-anchor="center" width="100">100% Capacity</text>
    </svg>
  </div> -->
  <div id='map'></div>
  <div id='map-event-list'>
      <form id='zip-and-distance' action="#">
        <div id="error-box"></div>
        <div>
          <input type='text' name='zipcode' id='input-text-zipcode' value='<?php echo $zipcode ?>' placeholder='Enter zipcode' maxlength='5'/>
        </div>
        <div>
          <ul id='distance-list'>
              <?php
               echo implode("", array_map(function($d) use ($distance) {
                  return "<li>
                        <input type='radio' id='mile-{$d}' name='distance' value='{$d}' " .
                        ($d == $distance ? "checked='checked'" : "") . "/> <label for='mile-{$d}'>{$d}mi</label></li>";
                }, array(5, 10, 20, 50, 100, 250)));
              ?>
          </ul>

        </div>
      </form>
      <h2 id='event-results-count'><span id='event-counter'></span> <span>within</span> <span id='event-distance'></span> <span>of</span>
        <div id="event-city"></div>
      </h2>
      <div id='event-list-area'>
        <ul id='event-list'>
        </ul>
        <p style='text-align: center; margin-top: 20px;'><img src='/img/list-end.png' width='100px'/></p>
      </div>
  </div>
</section>

<script src='https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.5/d3.min.js'></script>
<script id='zipcodes-datadump' type='text/plain'>
<?php
    // if ($mc->get(ZIPCODES_URL)) {
    //   $zip_content = $mc->get(ZIPCODES_URL);
    // } else {
    //   $zip_content = file_get_contents(ZIPCODES_URL);
    //   $mc->set(ZIPCODES_URL, $zip_content);
    // }
    // echo $zip_content;
?>
</script>
<script src="/js/jquery.js"></script>
<script src='/js/mapbox.js'></script>
<script type='text/javascript'>
var bernMap = bernMap || {};
bernMap.raw = {};
bernMap.raw.zipcode = $("#zipcodes-datadump").html().trim();

bernMap.raw.july29 = <?php
    if ($mc->get(BERNIE2016_URL)) {
      $content = $mc->get(BERNIE2016_URL);
    } else {
      $content = file_get_contents(BERNIE2016_URL);
      $mc->set(BERNIE2016_URL, $content);
    }
    echo $content;
?>;

var $jq = jQuery;

//Initialize items
$("h2#event-results-count").hide();

//Window Resize
$jq(window).on("resize", function() {
  var h = $jq("#header").height() + $jq("#main-title-area").height();
  var wH = $jq(window).height();
  var padding = 20;


  $("#map-section, #map").height(wH - h - 25);
  $("#event-list-area").css("maxHeight", wH - h - (padding * 2) - 240 - 25);



});
$jq(window).trigger("resize");


L.mapbox.accessToken = "pk.eyJ1IjoicmFwaWNhc3RpbGxvIiwiYSI6IjBlMGI3NTNhMWFiNGU4NmY4YmI4ZTNmOGRjYmQzZWVjIn0.KyTcvG8fiIStw8BkZjfvLA";
var mapboxTiles = L.tileLayer('https://{s}.tiles.mapbox.com/v4/mapbox.streets/{z}/{x}/{y}.png?access_token=' + L.mapbox.accessToken, {
    attribution: '<a href="http://www.openstreetmap.org/copyright" target="_blank">&copy; OpenStreetMap contributors</a>'
});

var WIDTH = $jq(window).width();

var bernMap = bernMap || {};
bernMap.constants = {};
// bernMap.constants.spreadsheetUrl = "/d/july29.json";
bernMap.constants.spreadsheetUrl = "https://go.berniesanders.com/page/event/search_results?format=json&wrap=no&orderby[0]=date&orderby[1]=desc&event_type=13&mime=text/json&limit=4000&country=*";


bernMap.mapBox = new L.Map("map", {center: [37.8, -96.9], zoom: 4, paddingTopLeft: [400, 0], scrollWheelZoom: false}).addLayer(mapboxTiles)

var offset = bernMap.mapBox.getSize().x * 0.15;
bernMap.mapBox.panBy(new L.Point(offset,0), {animate: false});

bernMap.d = {};
bernMap.scale = {};
bernMap.scale.radScale = d3.scale.pow().domain([0, 5, 150]);
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

  this.centerItem = null;

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

  // *********************
  // FOCUS ZIPCODE
  // *********************
  this.focusZipcode = function(hash) {
    var that = this;
    var params = that._deserialize(hash);

    var target = bernMap.d.allZipcodes.filter(function(d) { return d.zip == params.zipcode; });

    if (target.length == 0) {
      bernieEvents.setError("Zipcode does not exist.");
    }
    else {
      var t = target[0];



      //Plot zipcode center
      $("circle#center-item").remove();
      // console.log("Center", t);
      var centerCoords = that._projectPoint(t.lon, t.lat);
      // console.log("CenterCoords", centerCoords)

      that.centerItem = that.activityLayer.append("circle")
            .datum(t)
            .attr("id", "center-item")
            .attr("cx", centerCoords[0])
            .attr("cy", centerCoords[1])
            .attr("r", bernMap.mapBox.getZoom() * 0.4 )
            .attr("fill", "#147FD7")
            .attr("opacity", 0.9);

      //Focus on map

      bernMap.mapBox.setView([parseFloat(t.lat), parseFloat(t.lon)], that._getZoomValue(parseInt(params.distance)), { animate: false });
      var offset = bernMap.mapBox.getSize().x * 0.15;


      bernMap.mapBox.panBy(new L.Point(offset,0), {animate: false});
      that.replot();
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

  this.plot = function () {
    var that = this;
    if (!bernMap.d.zipcodes) return;

    bernMap.scale.radScale.range([parseFloat(bernMap.mapBox.getZoom()),
                                      bernMap.mapBox.getZoom(),

                                      bernMap.mapBox.getZoom()
                                      + (bernMap.mapBox.getZoom()  * 3)]);
    that.zipcodeElements = that.activityLayer.selectAll("circle.zipcode")
                              .data(bernMap.d.zipcodes.features).enter()
                              .append("circle")
                              .attr("data-zip", function(d) { return d.properties.zip; })
                              .attr("r", function(d) {
                                  return bernMap.scale.radScale(d.properties.zip_rsvp);
                              })
                              .attr("stroke-width", 0)
                              .attr("opacity", 0.5)
                              .each(function(d) {
                                var coordinates = that._projectPoint(d.geometry.coordinates[0], d.geometry.coordinates[1]);
                                  d3.select(this).attr("cx", coordinates[0])
                                      .attr("cy", coordinates[1])
                                  ;
                              });



    // that.zipcodeElements
          // .transition()
          // .duration(500)
          // .delay(function() { return Math.random() * 3000})
          // .attr("r", bernMap.mapBox.getZoom() * 3);

    //initialize event for zipcode
    that.zipcodeElements.on("click", function(d) {

      $("input[name=zipcode]").val(d.properties.zip);
      $jq("form#zip-and-distance").submit();
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

  this.replot = function () {
    var that = this;
    if (!bernMap.d.zipcodes) return;

     bernMap.scale.radScale.range([parseFloat(bernMap.mapBox.getZoom()) * 0.6,
                                      bernMap.mapBox.getZoom(),

                                      bernMap.mapBox.getZoom()
                                      + (bernMap.mapBox.getZoom()  * 3)]);

    if (that.centerItem) {
      that.centerItem.each(function(d) {
                      var coordinates = that._projectPoint(d.lon, d.lat)

                        d3.select(this).attr("cx", coordinates[0])
                            .attr("cy", coordinates[1])
                            .attr("r", bernMap.mapBox.getZoom() * 0.4)
                            .attr("opacity", 0.9)
                        ;
                    });
    }

    that.zipcodeElements.each(function(d) {
                                var coordinates = that._projectPoint(d.geometry.coordinates[0], d.geometry.coordinates[1]);




                                  d3.select(this).attr("cx", coordinates[0])
                                      .attr("cy", coordinates[1])
                                      .attr("r", function (d) {
                                          return bernMap.scale.radScale(d.properties.zip_rsvp);
                                        })
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
      _that.activityLayer.style("display","none");
      // in_need_layer.style("visibility", "hidden");
      // percent_change_layer.style("visibility", "hidden");
      // funding_layer.style("visibility", "hidden");
    });
    bernMap.mapBox.on('zoomend', function() {
        // triggerLayerChange();
        _that.replot();
        _that.activityLayer.style("display","block");
        // regionalLayer.recalibrateLayer();
    });
  }();
};

var bernMap = bernMap || {};
bernMap.eventList = function(container) {
  this.containerLabel = container;
  this.container = $jq(container);
  this.errorBox = this.container.find("#error-box");

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
    that.errorBox.text(message);
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

    var targC = [parseFloat(target.lat), target.lon];
    var nearByZipcodes = bernMap.d.zipcodes.features.filter(function(d) {
                            var compC = [parseFloat(d.properties.lat), parseFloat(d.properties.lon)];
                            var distance = that._getDistanceInMi(targC[0], targC[1], compC[0], compC[1]);
                            d.properties.distance = distance;
                            return  distance <= allowedDistance;
                        }).map(function(d) { return { "distance" : d.properties.distance, "zipcode" : d.properties.zip}; });

    if (nearByZipcodes.length == 0) return;


    nearByZipcodes.sort(function(a,b) {
      return a.distance - b.distance;
    })

    // collate list:
    var collatedList = nearByZipcodes.map(function(d) {
       var events = bernMap.d.aggregatedRSVP[d.zipcode];
       events.forEach(function(t) {
          t['distance'] = d.distance;
       });
       return events;
    });

    var finalCollatedList = [];
    collatedList.forEach(function(item) { finalCollatedList = finalCollatedList.concat(item); });

    $("#event-counter").text(finalCollatedList.length + " " + (finalCollatedList.length == 1 ? "event" : "events") );

    //Render list
    var ul = d3.select(that.containerLabel).select("ul#event-list");


    var dateFormat = d3.time.format("%B %d");
    var liContent = ul.selectAll("li.event-list-item")
                .data(finalCollatedList, function(d){ return d["id"] ;});


    liContent.enter()
      .append("li")
        .attr("data-zip", function(d) { return d.zip; })
        .attr("class", "event-list-item")
        .html(function(d) {
          var links = [];
          for ( var i = 1; i <= 9; i++) {
            var link = "Link" + i;

            if ( d[link] ) {
              var name_link = d[link].split(/,(.+)?/)
              links.push ( {name: name_link[0], link: name_link[1]} );
            }
          }


          var linkText = ["<a target='" + (links[0].link.indexOf("mailto")!=0?"_blank":"_self") + "' href='" + links[0].link + "' class='" + links[0].name.toLowerCase().replace(/ /g, "-") + "-link'>" + d.AttendeeCount + " RSVPs </a>"];

          // var linkText = ["<a target='" + (links[0].link.indexOf("mailto")!=0?"_blank":"_self") + "' href='" + links[0].link + "' class='" + links[0].name.toLowerCase().replace(/ /g, "-") + "-link'>RSVP @ BernieSanders.com </a>"];

          return "<h5><span class='event-item-date'>"
            + d3.round(d.distance,1) + "MI"
            + " &nbsp;&nbsp; "
            + dateFormat(d.Date)
            + " &nbsp;&nbsp; "
            + (d.TimeStart ? "" + d.TimeStart + (d.TimeEnd ? " - " + d.TimeEnd : "") + "" : "")
            + "</span></h5>"
            + "<h3><a target='_blank' href='" + links[0].link + "'><span class='event-item-name'>" + d.Title + "</span></a></h3>"
            + (d.Organizer != "" ? ("<h4 class='event-organizer'>by <a target='_blank' href='" + (d.OrganizerWebsite ? d.OrganizerWebsite : "javascript: void(0);") + "'>" + d.Organizer + "</a></h4>") : "")
            + "<h5 class='event-location'>" + d.Location + "</h5>"
            + "<p>" + linkText.join(" &bull; ")+ "</p>"
              ;
        });


        liContent.on("mouseover", function() {
          var zip = $(this).attr("data-zip");

          // d3.select("circle[data-zip='" + zip + "']").attr("stroke-width", 5);
          $("circle[data-zip=" + zip + "]").attr("stroke-width", 5);

          //


        })
        .on("mouseout", function() {
          var zip = $(this).attr("data-zip");
          d3.select("circle[data-zip='" + zip + "']").attr("stroke-width", 0);
        })
        ;
//<li class='event-list-item'>
          //   <h5 class='event-basics'><span class='distance'>12MI</span>&nbsp;&bull;&nbsp;<span class="event-item-date">7:00 PM</span></h5>
          //   <h3><a target="_blank" href="http://www.facebook.com/1470121326632561"><span class="event-item-name">March for Bernie Sanders!  Everson-Nooksack parade and Bellingham Pride!</span></a></h3><h5>Bellingham High School 2020 Cornwall Ave Bellingham WA</h5>
          // </li>

    // liContent.exit().remove();



  };
};


var qtree = null;
var bernie = new bernMap.draw();
var bernieEvents = new bernMap.eventList("#map-event-list");

// d3.json("./csv-grab.php?u=" + encodeURIComponent(bernMap.constants.spreadsheetUrl),
  // d3.json("/d/july29.json",
  // function(data) {

  bernMap.d.meetupData = bernMap.raw.july29.results;
  bernMap.d.rawMeetupData = bernMap.raw.july29.results;

  var timeFormat = d3.time.format("%I:%M %p");
  var rawDateFormat = d3.time.format("%Y-%m-%d");
  var rawTime = d3.time.format("%X")
  $(bernMap.d.meetupData).each(function(i, item) {

    item.Date = rawDateFormat.parse(item.start_day);


    var tempTime = rawTime.parse(item.start_time);
    item.TimeStart = timeFormat(tempTime);
    item.TimeEnd = "";
    item.Link1 = "RSVP at BernieSanders.com," + item.url;
    item.OrganizerWebsite = "http://www.berniesanders.com";
    item.Organizer = "Bernie Sanders Campaign Volunteers";
    item.Title = item.name;
    item.zip = item.venue_zip;
    item.Zipcode = item.venue_zip;
    item.Location = item.venue_name + " " + item.venue_addr1 + " " + item.venue_city + " " + item.venue_state_cd + " " + item.venue_zip;

    item.AttendeeCount = item.attendee_count;

    bernMap.d.rsvp += parseInt(item.attendee_count);
    bernMap.d.capacity += parseInt(item.capacity);

  });

  var weekStart = rawDateFormat.parse("7/05/2015");
  var weekEnd = rawDateFormat.parse("7/12/2015");

  var today = new Date();
      today.setDate(today.getDate() - 1);
      today.setHours(0);
      today.setMinutes(0);
      today.setSeconds(0);

  bernMap.d.meetupData = bernMap.d.meetupData.filter(function(d){

    return d.Date >= today;
    // return d.Date <= weekEnd && d.Date >= weekStart;
  });


  var map = bernMap.d.meetupData.map(function(d) { return [d.Zipcode, d]; });
  bernMap.d.aggregatedRSVP = map.reduce(
      function(init, next) {

        if (init[next[0]]) {
          init[next[0]].push(next[1]);
        } else {
          init[next[0]] = [next[1]];
        }
        return init;
        //  = init[next[0]]
        // ? init[next[0]] + parseInt(next[1])
        // : [next[1]]; return init;
      }
  , {});

  loadZipcodeData();
// });


function loadZipcodeData() {
  // d3.tsv('/d/zipcodes.tsv', function(data) {
  // d3.csv('./d/zipcode-lookup.csv', function(data) {
  d3.csv("./csv-grab.php?u=" + encodeURIComponent('./d/us_postal_codes.csv'), function(data) {
    // bernMap.d.allZipcodes = d3.csv.parse(bernMap.raw.zipcode);
    // var data = bernMap.d.allZipcodes;

    bernMap.d.allZipcodes = data;
    data = data.filter(function(d) {
      return bernMap.d.aggregatedRSVP[d.zip];
    });


    function reformat(array) {
      var data = [];
      array.map(function(d,i) {
        //add rsvps
        d["rsvp"] = bernMap.d.aggregatedRSVP[d.zip];

        var totalRsvp = 0;
        var totalCapacity = 0;
        bernMap.d.aggregatedRSVP[d.zip].forEach(function(dI) {
          totalRsvp += parseInt(dI.attendee_count);
          totalCapacity += parseInt(dI.capacity);
        });
        d["zip_rsvp"] = totalRsvp;
        d["zip_capacity"] = totalCapacity;

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

      var d3format = d3.format("0,000");
      var percFormat = d3.format("0.2%");
      var percFull = parseFloat(parseFloat(bernMap.d.rsvp) / parseFloat(bernMap.d.capacity));
      $("#meetup-counter").text(bernMap.d.meetupData.length);
      $("#rsvp-counter").text(d3format(bernMap.d.rsvp));
      // $("#capacity-counter").text("(" + percFormat(percFull) + " Full)");

      return data;
    }

    var _features = reformat(data);
    _features.sort(function(a, b) { return b.properties.zip_rsvp - a.properties.zip_rsvp; });

    // console.log(_features);

    bernMap.d.zipcodes = {type: "FeatureCollection", features: _features };
    bernie.plot();

    $jq(window).trigger("hashchange");
  });
}

$jq("form input[type=radio]").on("click", function(d) {
  if( $jq("form input[name=zipcode]").val().length == 5 ) {
    window.location.hash = $(this).closest("form").serialize();
  }
});
$jq("form input[name=zipcode]").on("keyup", function(e) {
  // bernie.filter($(this).val());
  if (e.keyCode == 13|| e.which == 13) {
    return false;
  }

  if ( $(this).val().length == 5 ) {
    window.location.hash = $(this).closest("form").serialize();
  } else {
    bernieEvents.hideError();
  }
});

$jq("form#zip-and-distance").on("submit", function() {
  if ( $jq("form input[name=zipcode]").val().length == 5 ) {



    if( window.location.hash == "#" + $(this).closest("form").serialize()) {
      $jq(window).trigger("hashchange");
    } else {
      window.location.hash = $(this).closest("form").serialize();
    }

  } else {
    bernieEvents.setError("Complete Zipcode");
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

    // console.log(hash.substr(1));
    bernie.focusZipcode(hash.substr(1));
    bernieEvents.filterEvents(parameters.zipcode, parameters.distance);
  } else {
    bernMap.mapBox.setView([37.8, -96.9], 4);
    var offset = bernMap.mapBox.getSize().x * 0.15;
    bernMap.mapBox.panBy(new L.Point(offset,0), {animate: false});
  }

});

if ($jq("form input[name=zipcode]").val().length != 0 ) {
  $jq("form#zip-and-distance").trigger("submit");
}

</script>
<?php require_once(__DIR__ . '/inc/_footer.inc'); ?>
