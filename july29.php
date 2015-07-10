<?php require_once('./inc/_header.inc'); ?>
<link href='https://api.tiles.mapbox.com/mapbox.js/v2.1.9/mapbox.css' rel='stylesheet' />
<link href='./css/map.css?version=<?php echo $APPVERSION ?>' rel='stylesheet' />
<!-- <section>
  <h2 class='page-title'><span id='page-title-event-count'></span> 07/29: Growing our political revolution</h2>
  <h4 class='page-subtitle'>34 meetings with 23,059 RSVPs. <a href='http://goo.gl/forms/1dCkCj4zi9' target='_blank'>Submit an event</a></h5>
  <h5 class='page-subtitle'>Bernie is asking Americans from across the country to come together for a series of conversations about how we can organize an unprecedented grassroots movement that takes on the greed of Wall Street and the billionaire class.</h5>
</section> -->
<section id='main-title-area'>
  <h4><strong>July 29</strong> - Nationwide organizing meetings. Find a meeting nearby or <a href='https://go.berniesanders.com/page/s/organizing-meetings' target='_blank'>Host a meeting</a> </h4>
</section>
<section id='map-section' />
  <div id='map'></div>
  <div id='map-event-list'>
      <form id='zip-and-distance' action="#">
        <div id="error-box"></div>
        <div>
          <input type='text' name='zipcode' id='input-text-zipcode' placeholder='Enter zipcode' maxlength='5'/>
        </div>
        <div>
          <ul id='distance-list'>
            <li><input type='radio' id='mile-5' name='distance' value='5' checked='checked'b/> <label for='mile-5'>5mi</label></li>
            <li><input type='radio' id='mile-10' name='distance' value='10'/><label for='mile-10'>10mi</label></li>
            <li><input type='radio' id='mile-20' name='distance' value='20'/><label for='mile-20'>20mi</label></li>
            <li><input type='radio' id='mile-50' name='distance' value='50'/><label for='mile-50'>50mi</label></li>
            <li><input type='radio' id='mile-100' name='distance' value='100'/> <label for='mile-100'>100mi</label></li>
            <li><input type='radio' id='mile-250' name='distance' value='250'/><label for='mile-250'>250mi</label></li>
          </ul>

        </div>
      </form>
      <h2 id='event-results-count'><span id='event-counter'></span> Events within <span id='event-distance'></span></h2>
      <div id='event-list-area'>
        <ul id='event-list'>
          <!-- <li class='event-list-item'>
            <h5 class='event-basics'><span class='distance'>12MI</span>&nbsp;&bull;&nbsp;<span class="event-item-date">7:00 PM</span></h5>
            <h3><a target="_blank" href="http://www.facebook.com/1470121326632561"><span class="event-item-name">March for Bernie Sanders!  Everson-Nooksack parade and Bellingham Pride!</span></a></h3><h5>Bellingham High School 2020 Cornwall Ave Bellingham WA</h5>
          </li>
          <li class='event-list-item'>
            <h5 class='event-basics'><span class='distance'>12MI</span>&nbsp;&bull;&nbsp;<span class="event-item-date">7:00 PM</span></h5>
            <h3><a target="_blank" href="http://www.facebook.com/1470121326632561"><span class="event-item-name">March for Bernie Sanders!  Everson-Nooksack parade and Bellingham Pride!</span></a></h3><h5>Bellingham High School 2020 Cornwall Ave Bellingham WA</h5>
          </li>
          <li class='event-list-item'>
            <h5 class='event-basics'><span class='distance'>12MI</span>&nbsp;&bull;&nbsp;<span class="event-item-date">7:00 PM</span></h5>
            <h3><a target="_blank" href="http://www.facebook.com/1470121326632561"><span class="event-item-name">March for Bernie Sanders!  Everson-Nooksack parade and Bellingham Pride!</span></a></h3><h5>Bellingham High School 2020 Cornwall Ave Bellingham WA</h5>
          </li>
           -->
        </ul>
        <p style='text-align: center; margin-top: 20px;'><img src='./img/list-end.png' width='100px'/></p>
      </div>
  </div>
</section>

<script src='https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.5/d3.min.js'></script>
<script src="js/jquery.js"></script>
<script src='./js/mapbox.js'></script>
<script type='text/javascript'>

//Initialize items
$("h2#event-results-count").hide();

L.mapbox.accessToken = "pk.eyJ1IjoicmFwaWNhc3RpbGxvIiwiYSI6IjBlMGI3NTNhMWFiNGU4NmY4YmI4ZTNmOGRjYmQzZWVjIn0.KyTcvG8fiIStw8BkZjfvLA";
var mapboxTiles = L.tileLayer('https://{s}.tiles.mapbox.com/v4/mapbox.streets/{z}/{x}/{y}.png?access_token=' + L.mapbox.accessToken, {
    attribution: '<a href="https://secure.actblue.com/contribute/page/reddit-for-bernie/" target="_blank">Contribute to the Campaign</a>'
});

var $jq = jQuery;

var WIDTH = $jq(window).width();


var bernMap = bernMap || {};
bernMap.mapBox = new L.Map("map", {center: [37.8, -96.9], zoom: 4, paddingTopLeft: [400, 0], scrollWheelZoom: false}).addLayer(mapboxTiles)

var offset = bernMap.mapBox.getSize().x * 0.15;
bernMap.mapBox.panBy(new L.Point(offset,0), {animate: false});

bernMap.zipcodes = null;
bernMap.allZipcodes = null;
bernMap.meetupData = null;
bernMap.targetZipcodes = null;
bernMap.aggregatedRSVP = null;

var bernMap = bernMap || {};
bernMap.draw = function() {
  this.filteredZipcode = null;

  this.svg = d3.select(bernMap.mapBox.getPanes().overlayPane).append("svg");
  this.activityLayer = this.svg.append("g").attr("class","leaflet-zoom-hide");
  this.zipcodeElements = null;

  this.centerItem = null;

  this._projectPoint = function(x,y) {
    var point = bernMap.mapBox.latLngToLayerPoint(new L.LatLng(y, x));
    // console.log(x,y);
    // var point = bernMap.mapBox.latLngToContainerPoint(new L.LatLng(y,x));
    // this.stream.point(point.x, point.y);
    return [point.x, point.y];
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

    var target = bernMap.allZipcodes.filter(function(d) { return d.zip == params.zipcode; });
    // console.log(target);
    if (target.length == 0) {
      bernieEvents.setError("Zipcode does not exist.");
    }
    else {
      var t = target[0];

      // console.log("XXX", t);

      //Plot zipcode center
      $("circle#center-item").remove();
      var centerCoords = that._projectPoint(t.lon, t.lat);

      that.centerItem = that.activityLayer.append("circle")
            .datum(t)
            .attr("id", "center-item")
            .attr("cx", centerCoords[0])
            .attr("cy", centerCoords[1])
            .attr("r", bernMap.mapBox.getZoom() * 1 )
            .attr("fill", "#147FD7")
            .attr("opacity", 0.9);

      //Focus on map
      bernMap.mapBox.setView([parseFloat(t.lat), parseFloat(t.lon)], 12, { animate: true });
      var offset = bernMap.mapBox.getSize().x * 0.15;
      bernMap.mapBox.panBy(new L.Point(offset,0), {animate: false});
    }


  };

  // this.filter = function(str) {
  //   var that = this;
  //   if ( str == '' ) { that.filteredZipcode = null; }
  //   else {
  //     if ( that.filteredZipcode == null ) {
  //       that.filteredZipcode = bernMap.allZipcodes.filter(function(d) { return d.zip.indexOf(str) >= 0; });
  //     } else {
  //       that.filteredZipcode = that.filteredZipcode.filter(function(d) { return d.zip.indexOf(str) >= 0; });
  //     }
  //   }
  //   console.log( that.filteredZipcode );
  // };

  this.plot = function () {
    var that = this;
    if (!bernMap.zipcodes) return;

    that.zipcodeElements = that.activityLayer.selectAll("circle.zipcode")
                              .data(bernMap.zipcodes.features).enter()
                              .append("circle")
                              .each(function(d) {
                                var coordinates = that._projectPoint(d.geometry.coordinates[0], d.geometry.coordinates[1]);
                                  d3.select(this).attr("cx", coordinates[0])
                                      .attr("cy", coordinates[1])
                                      .attr("r", bernMap.mapBox.getZoom() * 3)
                                      .attr("opacity", 0.4)
                                  ;
                              });

    //initialize event for zipcode
    that.zipcodeElements.on("click", function(d) {
      // console.log(d.properties.zip);
      $("input[name=zipcode]").val(d.properties.zip);
      $jq("form#zip-and-distance").submit();
    });

    // var bounds = that.activityLayer[0][0].getBoundingClientRect();
    var bounds = that.activityLayer[0][0].getBBox();
    // console.log(bounds);
    that.svg.attr("width", (bounds.width + 0) + "px")
      .attr("height", (bounds.height + 0) + "px")
      // .attr("transform", "translate(" + -bounds.left + "," + -bounds.top + ")");
      .style("left", bounds.x + "px")
      .style("top", bounds.y + "px");

    that.activityLayer.attr("transform", "translate(" + -bounds.x + "," + -bounds.y + ")");
  };

  this.replot = function () {
    var that = this;
    if (!bernMap.zipcodes) return;


    // console.log(that.centerItem);
    if (that.centerItem) {
      that.centerItem.each(function(d) {
                      var coordinates = that._projectPoint(d.lon, d.lat)
                        // console.log(coordinates);
                        d3.select(this).attr("cx", coordinates[0])
                            .attr("cy", coordinates[1])
                            .attr("r", bernMap.mapBox.getZoom())
                            .attr("opacity", 0.9)
                        ;
                    });
    }

    that.zipcodeElements.each(function(d) {
                                var coordinates = that._projectPoint(d.geometry.coordinates[0], d.geometry.coordinates[1]);

                                  // console.log(coordinates);
                                  d3.select(this).attr("cx", coordinates[0])
                                      .attr("cy", coordinates[1])
                                      .attr("r", bernMap.mapBox.getZoom() * 3)
                                      .attr("opacity", 0.6)
                                  ;
                              });

    var bounds = that.activityLayer[0][0].getBBox();
    // console.log(bounds);
    that.svg.attr("width", (bounds.width + 0) + "px")
      .attr("height", (bounds.height + 0) + "px")
      // .attr("transform", "translate(" + -bounds.left + "," + -bounds.top + ")");
      .style("left", bounds.x + "px")
      .style("top", bounds.y + "px");

    that.activityLayer.attr("transform", "translate(" + -bounds.x + "," + -bounds.y + ")");

  };

  var _that = this;
  this.initialize = function() {

    // console.log(_that);
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
    var targetZipcode = bernMap.allZipcodes.filter(function(d) { return d.zip == zipcode; });
    $("h2#event-results-count").show();
    $("#event-counter").text("0");
    $("#event-distance").text(allowedDistance + "mi");
    if (targetZipcode.length == 0 ) return ;
    var target = targetZipcode[0];
    var targC = [parseFloat(target.lat), target.lon];
    var nearByZipcodes = bernMap.zipcodes.features.filter(function(d) {
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
       var events = bernMap.aggregatedRSVP[d.zipcode];
       events.forEach(function(t) {
          t['distance'] = d.distance;
       });
       return events;
    });

    var finalCollatedList = [];
    collatedList.forEach(function(item) { finalCollatedList = finalCollatedList.concat(item); });

    $("#event-counter").text(finalCollatedList.length);

    //Render list
    $("ul#event-list").children("li").remove();
    var ul = d3.select(that.containerLabel).select("ul#event-list");

    var liContent = ul.selectAll("li.event-list-item")
                .data(finalCollatedList, function(d){ return d.ID ;});

              liContent.enter()
                .append("li")
                  .attr("class", "event-list-item")
                  .html(function(d) {
                    var type = d.LinkType;
                    var url = d[type+"URL"];
                      return "<h5 class='event-basics'>" +
                            "<span class='distance'>" + d3.round(d.distance, 1) + "MI </span>&nbsp;&nbsp;&nbsp;&nbsp;<span class='event-item-date'>" + d.Time + "</span>" +
                        "</h5>" +
                        "<h3>" +
                          "<a target='_blank' href='" + url + "'>" +
                            "<span class='event-item-name'>" + d.Title + "</span>" +
                            "</a>" +
                        "</h3>" +
                        "<h5 class='event-location'>" + d.Location + "</h5>";
                  })
                  ;

//<li class='event-list-item'>
          //   <h5 class='event-basics'><span class='distance'>12MI</span>&nbsp;&bull;&nbsp;<span class="event-item-date">7:00 PM</span></h5>
          //   <h3><a target="_blank" href="http://www.facebook.com/1470121326632561"><span class="event-item-name">March for Bernie Sanders!  Everson-Nooksack parade and Bellingham Pride!</span></a></h3><h5>Bellingham High School 2020 Cornwall Ave Bellingham WA</h5>
          // </li>

    liContent.exit().remove();

    // console.log("NEW LIST", li);

  };
};


var qtree = null;
var bernie = new bernMap.draw();
var bernieEvents = new bernMap.eventList("#map-event-list");

d3.csv('./d/july29.csv', function(data) {
  bernMap.meetupData = data;

  var map = bernMap.meetupData.map(function(d) { return [d.Zipcode, d]; });
  bernMap.aggregatedRSVP = map.reduce(
      function(init, next) {
        // console.log("X", init);
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
});


function loadZipcodeData() {
  d3.tsv('./d/zipcodes.tsv', function(data) {
    bernMap.allZipcodes = data;

    data = data.filter(function(d) {
      return bernMap.aggregatedRSVP[d.zip];
    });
    // console.log(data);

    function reformat(array) {
      var data = [];
      array.map(function(d,i) {
        //add rsvps
        d["rsvp"] = bernMap.aggregatedRSVP[d.zip];
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

    // console.log(data);
    bernMap.zipcodes = {type: "FeatureCollection", features: reformat(data) };
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

    // console.log(window.location.hash,$(this).closest("form").serialize());

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

$jq(window).on("hashchange", function(){
  var hash = window.location.hash;

  if (hash.length > 1) {
    //Set it on the form
    var parameters = bernie._deserialize(hash.substr(1));



    if ($jq("form input[type=radio]").val() != parameters.distance ) {
      $jq("form input[type=radio][value=" + parameters.distance + "]").attr("checked", "checked");
    }

    if ($jq("form input[name=zipcode]").val() != parameters.zipcode) {
      $jq("form input[name=zipcode]").val(parameters.zipcode);
    }

    bernie.focusZipcode(hash.substr(1));
    bernieEvents.filterEvents(parameters.zipcode, parameters.distance);
  }

});



</script>
<?php require_once('./inc/_footer.inc'); ?>
