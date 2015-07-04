<!DOCTYPE html>
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="description" content="All events related to Bernie. Townhall meetings, meetups, etc. Click on state to filter results.">
<meta name="keywords" content="Bernie Sanders, FeelTheBern, Events, Bernie, #bernie2016, #feelthebern">
<meta property="og:image" content="http://www.bernie2016events.org/img/bernie-logo.png"/>
<meta property="og:title" content="Bernie 2016 - Events Around the States"/>
<meta property="og:description" content="All events related to Bernie. Townhall meetings, meetups, etc. Click on state to filter results."/>
<link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700,800">
<title>#Bernie2016 Events Around the States - All events related to Bernie Sanders. Townhall meetings, meetups, etc. Click on state to filter results.</title>
<link rel="shortcut icon" href="http://www.bernie2016events.org/favicon.ico">
</head>
<body>
  <script>
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '1465128650469416',
      xfbml      : true,
      version    : 'v2.3'
    });
  };

  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "//connect.facebook.net/en_US/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));
</script>

<link href='css/custom.css' rel="stylesheet" type="text/css" />

<?php require_once('./inc/_header.php'); ?>


<section>
  <h2 class='page-title'>Meetups and Events Around the States</h2>
  <h5 class='page-subtitle'>Click on a state to filter results OR <a href='http://goo.gl/forms/1dCkCj4zi9' target='_blank'>Submit an event</a></h5>
  <div id='map-container'></div>
  <div id='map-event-list'>
    <article id="event-state-name">
      <h2></h2>
    </article>
    <ul id='event-list'>
      <li><span style='color: lightgray; font-weight:600;'>LOADING EVENTS...</span></li>
    </ul>
    <p style='text-align: center'></p>
    <div id='event-nationwide'>
      <h4>NATIONWIDE EVENTS</h4>
      <ul id='event-nationwide-event-list'>
     <!--    <li class='event-nationwide-item'>
          <h3><span class="event-item-date">July 04 &nbsp;&nbsp; </span> <a target="_blank" href="http://www.signupgenius.com/go/20f0549a5ad22a0ff2-march"><span class="event-item-name">March for Bernie in Ann Arbor Parade</span></a></h3>
          <p><a target="_blank" href="http://www.signupgenius.com/go/20f0549a5ad22a0ff2-march" class="sign-up-site-link">Sign Up site</a> • <a target="_blank" href="http://www.signupgenius.com/go/20f0549a5ad22a0ff2-march" class="reddit-link">Reddit</a></p>
        </li> -->
      </ul>
    </div>
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


      this.listNationwideEvents = function() {
        var targetList = bernie.d.events.filter(function(d) { return d.State == "ALL" });
        var dateFormat = d3.time.format("%B %d");

        if (targetList.length == 0) {
          d3.select("#event-nationwide").style("display", "none");
          return;
        }

        $(container).find("ul#event-nationwide-event-list li").remove();

        var nationwideItem = d3.select("#event-nationwide").select("ul")
          .selectAll("li").data(targetList).enter()
            .append("li")
            .attr("class", "event-nationwide-item");

        nationwideItem.each(function(d, i) {
          // console.log(d, i, this);
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

          var linkText = links.map(function(d) { return "<a target='" + (d.link.indexOf("mailto")!=0?"_blank":"_self") + "' href='" + d.link + "' class='" + d.name.toLowerCase().replace(/ /g, "-") + "-link'>" + d.name + "</a>"; });


          d3.select(this).html(
            "<h5><span class='event-item-date'>" + dateFormat(d.Date)
              + " &nbsp;&nbsp; "
              + (d.TimeStart ? "" + d.TimeStart + (d.TimeEnd ? " - " + d.TimeEnd : "") + "" : "")
              + "</span></h5>"

            + "<h3><a target='_blank' href='" + links[0].link + "'><span class='event-item-name'>" + d.Title + "</span></a></h3>"
              + (d.Organizer != "" ? ("<h4 class='event-organizer'>by <a target='_blank' href='" + (d.OrganizerWebsite ? d.OrganizerWebsite : "javascript: void(0);") + "'>" + d.Organizer + "</a></h4>") : "")
              + "<p>" + linkText.join(" &bull; ")+ "</p>"
          );
        });
      };


      this.listEvents = function(state) {

      //<img src='./img/states/" + state + ".png' />
        $(container).find("#event-state-name h2").html("<span>" + bernie.constants.states[state][0] + "</span>");

        var dateFormat = d3.time.format("%B %d");
        var targetList = bernie.d.events.filter(function(d) { return d.State == state });

        $(container).find("ul#event-list li").remove();

        var eventListArea = d3.select(container).select("ul").attr("id", "event-list");

        // console.log(targetList);

        var eventListItems = targetList.length > 0
              ? eventListArea.selectAll("li").data(targetList).enter().append("li").attr("class", "event-list-item")
              : eventListArea.append("li").attr("class", "event-list-item")
                  .html("<h5 class='page-subtitle'>No events lined up. <a href='http://goo.gl/forms/1dCkCj4zi9' target='_blank'>Submit an event</a></h5>");

      /*<li>
        <h3>July 1: Town-hall meeting with Bernie</h3>
        <h5>Location of Town Hall</h5>
        <p><a href='#' class='official-link'>Official Bernie Campaign</a> &bull; <a href='#' class='reddit-link'>Reddit</a> &bull; <a href='#' class='facebook-link'>Facebook</a> &bull; <a href='#' class='other-link'>People for Bernie</a></p>
      </li>
      */
          if ( targetList.length ) {
              eventListItems.each(function(d, i) {
                // console.log("THIS", d, i, this);
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

                var linkText = links.map(function(d) { return "<a target='"+ (d.link.indexOf("mailto")!=0?"_blank":"_self") +"' href='" + d.link + "' class='" + d.name.toLowerCase().replace(/ /g, "-") + "-link'>" + d.name + "</a>"; });


                d3.select(this).html(
                  "<h5><span class='event-item-date'>" + dateFormat(d.Date)
                    + " &nbsp;&nbsp; "
                    + (d.TimeStart ? "" + d.TimeStart + (d.TimeEnd ? " - " + d.TimeEnd : "") + "" : "")
                    + "</span></h5>"

                  + "<h3><a target='_blank' href='" + links[0].link + "'><span class='event-item-name'>" + d.Title + "</span></a></h3>"

                    + (d.Organizer != "" ? ("<h4 class='event-organizer'>by <a target='_blank' href='" + (d.OrganizerWebsite ? d.OrganizerWebsite : "javascript: void(0);") + "'>" + d.Organizer + "</a></h4>") : "")

                    + "<h5>" + d.Location + "</h5>"
                    + "<p>" + linkText.join(" &bull; ")+ "</p>"
                );
              });
          }
      }
    };

var bernieEventList = new bernie.EventList('#map-event-list');

var bernie = bernie || {};
    bernie.States = function(container) {

      this.mobileFormat = $(window).width() < 700;

      this.container = container;
      this.margin = this.mobileFormat ? {top:0,right: 0, left: 0, bottom: 0} : {top: 40, right: 40, bottom: 40, left: 40} ;
      this.radiusSize = this.mobileFormat ? 18 : 25;
      this.width = this.mobileFormat ? 600 : (768 - this.margin.left - this.margin.right);
      this.height = this.mobileFormat ? 300 : 400 + this.radiusSize - this.margin.top - this.margin.bottom;

      this.svg = null,
      this.statesArea = null,
      this.statesHexes = null,
      this.statesText = null
      ;

      this.eventsHandler = new bernie.Events();

      this.scale = {
          ordinalX : d3.scale.ordinal()
                          .domain(d3.range(24))
                          .rangeRoundBands([this.mobileFormat ? this.radiusSize*.35 : this.radiusSize, this.mobileFormat ? this.width-this.radiusSize*9 : this.width-this.radiusSize*3]),
          ordinalY : d3.scale.ordinal()
                        .domain(d3.range(8))
                        .rangeRoundBands([this.radiusSize, this.mobileFormat ? this.height - this.radiusSize * 2: this.height+this.radiusSize]),
          color : d3.scale.linear()
                          .domain([0, 20])
                          .range(["white", "#147FD7"])
                          .interpolate(d3.interpolateLab)
      };

      this.hexbin = d3.hexbin()
                        .size([this.width, this.height+this.radiusSize])
                        .radius(this.radiusSize);

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

          // console.log(count);
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


        that.svg = d3.select(that.container).append("svg")
                    .attr("width", that.mobileFormat ? $(window).width() : that.width )
                    .attr("height", that.height + that.radiusSize + that.margin.top )
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

    // console.log("Initializing Events");
    that.statesEventCount.on("click", that.eventsHandler.clickState);
    that.statesHexes.on("click", that.eventsHandler.clickState);
    that.statesText.on("click", that.eventsHandler.clickState);

    //Reposition

    if (that.mobileFormat) {
      // console.log(that.svg);
      var winWidth = $(window).width();
      var svgWidth = $(that.svg[0])[0].getBoundingClientRect().width;

      // console.log(svgWidth, winWidth);
      that.svg.attr("transform", "translate(" + (winWidth/2 - svgWidth/2) + ",0)");
    }
};

var statesDraw = new bernie.States('#map-container');

/*Styling */
$(window).on("resize",function() {
  $("#map-event-list").width($(window).width()-780);
});
$(window).on("hashchange", statesDraw.eventsHandler.hashchange);

$(window).trigger("resize");

//Load data
// console.log("./csv-grab.php?u=" + encodeURIComponent(bernie.constants.spreadsheetUrl));
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
    bernieEventList.listNationwideEvents();
    $("ul#event-list li").remove();

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

<footer>
  <div id='social'>
    <a href='https://secure.actblue.com/contribute/page/reddit-for-bernie' class='contribute' target='_blank'>Contribute to the Campaign</a>
    <a href="https://twitter.com/share" class="twitter-share-button" data-url="http://www.bernie2016events.org" data-text="Look Up #Bernie2016 Meetups&amp;events From All 50 States. Organizers Submit Your Meet Up. #FeelTheBern @BernieMeetups" data-related="RedditForSanders">Tweet</a>
  <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
  <div class="fb-like" href="http://www.bernie2016events.org/" data-layout="button_count" data-action="like" data-show-faces="false" data-share="false"></div>
  </div>
  <sub style='color: #999999; text-align: center;' >
    &reg; <a href='http://www.reddit.com/r/SandersForPresident' target='_blank'>Reddit for Bernie 2016</a>. Report a bug <a target='_blank' href='http://www.reddit.com/r/CodersForSanders/comments/3blip8/events_aggregator_for_bernie_takes_data_from/' target="_blank">here</a>!</sub>
</footer>
  <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-64649524-1', 'auto');
  ga('send', 'pageview');

</script>
</body>
