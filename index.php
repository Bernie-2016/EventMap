<!DOCTYPE html>
<meta charset="utf-8" />
<body>
<link href='css/custom.css' rel="stylesheet" type="text/css" />
<h1>
  <img src='./img/logo.png' style='float: left; margin-left: 10px;' />&nbsp;<span>Events for Bernie<span><br/>
<span style='font-size: 14px; font-weight: normal; margin-left: 10px; color: #999999;'>All events related to Bernie. Townhall meetings, meetups, etc. Click on state to filter results. <a target="_blank" href='http://goo.gl/forms/1dCkCj4zi9'>Submit an Event</a>
</span>
</h1>

<section>
  <div id='map-container'></div>
  <div id='map-event-list'>
    <article id="event-state-name">
      <h2></h2>
    </article>
    <ul id='event-list'>
    </ul>
    <p style='text-align: center; margin-top: 50px;'><img src='./img/list-end.png' width='100px'/></p>
  </div>
  <div style="clear: both"></div>
</section>

<script src='https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.5/d3.min.js'></script>
<script src="js/hexbin.js"></script>
<script src="js/jquery.js"></script>
<script>

/**
ub style='color: #999999;'>
  &reg; Rapinski for Bernie 2016. Inspired by http://www.r-bloggers.com/animated-us-hexbin-map-of-the-avian-flu-outbreak/ @ r-bloggers. Uses http://www.d3js.org ( d3 ) https://github.com/d3/d3-plugins/tree/master/hexbin.
**/
var $jq = jQuery;
var bernie = bernie || {};
bernie.constants = {};
bernie.constants.spreadsheetUrl = "https://docs.google.com/spreadsheets/d/1IaJQtbrsb8_bxpoayN-DhgAb3o_RMUDZyI4TwADmM1g/export?gid=0&format=csv";
bernie.constants.eventsFile = "./d/bernie.csv";
bernie.constants.states = {
    "AL": ["Alabama", 15, 6],
    "AK": ["Alaska", 1, 0],
    "AZ": ["Arizona", 7, 6],
    "AR": ["Arkansas", 12, 5],
    "CA": ["California", 4, 5],
    "CO": ["Colorado", 7, 4],
    "CT": ["Connecticut", 22, 3],
    "DC": ["District of Columbia",20,5],
    "DE": ["Delaware", 21, 4],
    "FL": ["Florida",16,7],
    "GA": ["Georgia",17,6],
    "HI": ["Hawaii",0,7],
    "ID": ["Idaho",4,3],
    "IL": ["Illinois",12,3],
    "IN": ["Indiana",14,3],
    "IA": ["Iowa",10,3],
    "KS": ["Kansas",10,5],
    "KY": ["Kentucky",13,4],
    "LA": ["Louisiana",11,6],
    "ME": ["Maine",23,0],
    "MD": ["Maryland",19,4],
    "MA": ["Massachusetts",21,2],
    "MI": ["Michigan", 15,2],
    "MN": ["Minnesota", 9,2],
    "MS": ["Mississippi",13,6],
    "MO": ["Missouri",11,4],
    "MT": ["Montana", 5,2],
    "NE": ["Nebraska",9,4],
    "NV": ["Nevada",5,4],
    "NH": ["New Hampshire",22,1],
    "NJ": ["New Jersey",20,3],
    "NM": ["New Mexico",8,5],
    "NY": ["New York",19,2],
    "NC": ["North Carolina",16,5],
    "ND": ["North Dakota", 7,2],
    "OH": ["Ohio",16,3],
    "OK": ["Oklahoma",9,6],
    "OR": ["Oregon",3,4],
    "PA": ["Pennsylvania",18,3],
    "RI": ["Rhode Island",23,2],
    "SC": ["South Carolina",18,5],
    "SD": ["South Dakota",8,3],
    "TN": ["Tennessee",14,5],
    "TX": ["Texas",10,7],
    "UT": ["Utah",6,5],
    "VT": ["Vermont",20,1],
    "VA": ["Virginia",17,4],
    "WA": ["Washington", 3, 2],
    "WV": ["West Virginia",15,4],
    "WI": ["Wisconsin",11,2],
    "WY": ["Wyoming",6,3]
};

var bernie = bernie || {};
    bernie.d = {}; //d for data;

var bernie = bernie || {};
    bernie.Events = function () {
      this.clickState = function(d) {
        window.location.hash = d.abbr;
      };

      this.hashchange = function() {
        var that = this;
        var currentHash = window.location.hash;
        if (!currentHash) {
          return false;
        }
        currentHash = currentHash.substr(1);

        //Color state
        if (currentHash) {
          // console.log(d);
          d3.selectAll("[data-state]").classed("highlight", false);
          d3.selectAll("[data-state='" + currentHash + "']").classed("highlight", true);
        }

        //List events
        bernieEventList.listEvents(currentHash);
      };

    };

var bernie = bernie || {};
    bernie.EventList = function(container) {
      this.container = container;
      this.listEvents = function(state) {


        $(container).find("#event-state-name h2").html("<img src='./img/states/" + state + ".png' /> <span>" + bernie.constants.states[state][0] + "</span>");

        var dateFormat = d3.time.format("%B %d");
        var targetList = bernie.d.events.filter(function(d) { return d.State == state });

        $(container).find("ul#event-list li").remove();

        var eventListArea = d3.select(container).select("ul").attr("id", "event-list");

        var eventListItems = eventListArea.selectAll("li").data(targetList).enter().append("li").attr("class", "event-list-item");

      /*<li>
        <h3>July 1: Town-hall meeting with Bernie</h3>
        <h5>Location of Town Hall</h5>
        <p><a href='#' class='official-link'>Official Bernie Campaign</a> &bull; <a href='#' class='reddit-link'>Reddit</a> &bull; <a href='#' class='facebook-link'>Facebook</a> &bull; <a href='#' class='other-link'>People for Bernie</a></p>
      </li>
      */
        eventListItems.each(function(d, i) {
          console.log(d, i, this);
          // var date = rawDateFormat.parse(d.Date);
          //Gather links

          var links = [];
          for ( var i = 1; i <= 9; i++) {
            var link = "Link" + i;

            if ( d[link] ) {
              var name_link = d[link].split(/,(.+)?/)
              links.push ( {name: name_link[0], link: name_link[1]} );
            }
          }

          var linkText = links.map(function(d) { return "<a target='_blank' href='" + d.link + "' class='" + d.name.toLowerCase().replace(/ /g, "-") + "-link'>" + d.name + "</a>"; });


          d3.select(this).html(
            "<h3><span class='event-item-date'>" + dateFormat(d.Date)
              + ":</span> <a target='_blank' href='" + links[0].link + "'><span class='event-item-name'>" + d.Title + "</span></a></h3>"
              + "<h5>" + d.Location + "</h5>"
              + (d.TimeStart ? "<p>" + d.TimeStart + (d.TimeEnd ? " - " + d.TimeEnd : "") + "</p>" : "")
              + "<p>" + linkText.join(" &bull; ")+ "</p>"
          );
        });
      }
    };

var bernieEventList = new bernie.EventList('#map-event-list');

var bernie = bernie || {};
    bernie.States = function(container) {

      this.container = container;
      this.margin = {top: 40, right: 40, bottom: 40, left: 40};
      this.radiusSize = 24;
      this.width = 768 - this.margin.left - this.margin.right;
      this.height = 400 + this.radiusSize - this.margin.top - this.margin.bottom;

      this.svg = null,
      this.statesArea = null,
      this.statesHexes = null,
      this.statesText = null
      ;

      this.eventsHandler = new bernie.Events();

      this.scale = {
          ordinalX : d3.scale.ordinal()
                          .domain(d3.range(24))
                          .rangeRoundBands([this.radiusSize, this.width-this.radiusSize*3]),
          ordinalY : d3.scale.ordinal()
                        .domain(d3.range(8))
                        .rangeRoundBands([this.radiusSize, this.height+this.radiusSize]),
          color : d3.scale.linear()
                          .domain([0, 20])
                          .range(["white", "#147FD7"])
                          .interpolate(d3.interpolateLab)
      };

      this.hexbin = d3.hexbin()
                        .size([this.width, this.height+this.radiusSize])
                        .radius(25);

      this.collatedStates = function() {
        var statesItem = [];
        for ( var key in bernie.constants.states ) {

          statesItem.push({
             "state": bernie.constants.states[key][0],
             "abbr": key,
             "x" : bernie.constants.states[key][1],
             "y" : bernie.constants.states[key][2]
          });
        }
        return statesItem;
      }();

      this.initialize();

      this.updateEventCount = function() {
        var that = this;
        //Arrange events into pig-holes
        var eventCounts = bernie.d.events.filter(function(d) { return d.State != "ALL"; })
            .map(function(d) { return d.State; })
            .reduce(function(prev, curr, ind) {
                if (ind == 1) {
                  var obj = {};
                  obj[prev] = 1;
                  prev = obj;
                }

                prev[curr] = prev[curr] ? prev[curr] + 1 : 1;
                return prev;
            });
        that.statesHexes.style("fill", function(d) {
            var count = eventCounts[d.abbr] || 0;
            return that.scale.color(count);
        });
        that.statesText.style("fill", function(d) {
          var count = eventCounts[d.abbr];

          console.log(count);
          if (!count || count == 0) { return "lightgray"; }
          else {
            return "#333333";
          }
        });

        that.statesEventCount.text(function(d) {
          var count = eventCounts[d.abbr];
          if (!count || count == 0) { return ""; }
          else {
            return count;
          }
        });
      };
    };

 //Render map
 bernie.States.prototype.initialize = function() {
        var that = this;
        console.log(that);

        that.svg = d3.select(that.container).append("svg")
                    .attr("width", that.width + that.margin.left + that.margin.right)
                    .attr("height", that.height + that.radiusSize + that.margin.top + that.margin.bottom)
                    .append("g")
                    .attr("transform", "translate(" + that.margin.left + "," + that.margin.top + ")")

        that.svg.append("clipPath")
            .attr("id", "clip")
            .append("rect")
            .attr("class", "mesh")
            .attr("width", that.width)
            .attr("height", that.height+that.radiusSize);

        that.statesArea = that.svg.append("g")
          .attr("clip-path", "url(#clip)");

        that.statesHexes = that.statesArea.selectAll("path.hexagon")
            .data(that.collatedStates)
            .enter().append("path")
            .attr("class", "hexagon")
            .attr("data-state", function(d) { return d.abbr; })
            .attr("d", that.hexbin.hexagon())
            .attr("transform", function(d) {
                return "translate(" + that.scale.ordinalX(d.x) + "," + that.scale.ordinalY(d.y) + ")"; });


        that.statesText = that.statesArea.selectAll("text.state-label state-country")
          .data(that.collatedStates)
          .enter().append("text")
            .attr("class", "state-label")
            .attr("data-state", function(d) { return d.abbr; })
            .attr("x", function(d) { return that.scale.ordinalX(d.x); })
            .attr("y", function(d) { return that.scale.ordinalY(d.y); })
            .attr("text-anchor", "middle")
            .text(function(d) { return d.abbr; });

    that.statesEventCount = that.statesArea.selectAll("text.state-events")
      .data(that.collatedStates)
      .enter().append("text")
        .attr("class", "state-label state-events")
        .attr("data-state", function(d) { return d.abbr; })
        .attr("x", function(d) { return that.scale.ordinalX(d.x); })
        .attr("y", function(d) { return that.scale.ordinalY(d.y) + 14; })
        .attr("text-anchor", "middle");

    console.log("Initializing Events");
    that.statesEventCount.on("click", that.eventsHandler.clickState);
    that.statesHexes.on("click", that.eventsHandler.clickState);
    that.statesText.on("click", that.eventsHandler.clickState);
};

var statesDraw = new bernie.States('#map-container');

/*Styling */
$("#map-event-list").width($(window).width()-768-40);
$(window).on("hashchange", statesDraw.eventsHandler.hashchange);

//Load data
console.log("./csv-grab.php?u=" + encodeURIComponent(bernie.constants.spreadsheetUrl));
d3.csv("./csv-grab.php?u=" + encodeURIComponent(bernie.constants.spreadsheetUrl),
  function(data) {
    bernie.d.events = data;

    var rawDateFormat = d3.time.format("%m/%d/%Y");
    $(bernie.d.events).each(function(i, item) {
      item.Date = rawDateFormat.parse(item.Date);
    });

    bernie.d.events = bernie.d.events.filter(function(d){
      var today = new Date();
      today.setDate(today.getDate() - 1);
      today.setHours(0);
      today.setMinutes(0);
      today.setSeconds(0);

      return d.Date >= today;
    });

    bernie.d.events.sort(function(a,b){
      // Turn your strings into dates, and then subtract them
      // to get a value that is either negative, positive, or zero.
      return a.Date - b.Date;
    });

    // console.log("EVENTS", bernie.d.events);
    statesDraw.updateEventCount();
    $(window).trigger("hashchange");
  }
);

//Arranging states by spot
// var randomX = d3.random.normal(width / 2, 80),
//     randomY = d3.random.normal(height / 2, 80);
    // points = [[ordinalX(2), ordinalY(3)]];
    // points = d3.range(2000).map(function() { return [randomX(), randomY()]; });

// var hexbin = ;

// var x = d3.scale.identity()
//     .domain([0, width]);

// var y = d3.scale.linear()
//     .domain([0, height])
//     .range([height, 0]);



</script>
<div style="clear: both"></div>
<sub style='color: #999999;'>
  &reg; Rapinski for Bernie 2016. <a href='http://goo.gl/forms/1dCkCj4zi9'>Submit an event</a> or to be a moderator: contact <a href='mailto:bernie2016-events@gmail.com'>bernie2016-events@gmail.com</a>. <a href='reddit.com/r/SandersForPresident'>reddit.com/r/SandersForPresident</a>. See something wrong? Let me know <a href='http://www.reddit.com/r/CodersForSanders/comments/3blip8/events_aggregator_for_bernie_takes_data_from/' target="_blank">here</a>!</sub>
</body>
