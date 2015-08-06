<!DOCTYPE html>
<?php

  if(!isset($_GET['sample'])) { return; }
?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700,800">
<link href='/css/quotes.css' rel='stylesheet' />
<body>
<section id='header'>
<h1>Bernie Quote Rater</h1>
<h4>arrange quotes by preference</h4>
</section>

<section id='ordered-list-container'>
  <ul id='ordered-list'>
    <li class="ordered-list-item"><span class='empty'>Drop <strong>best</strong> quote here</span></li>
    <li class="ordered-list-item"><span class='empty'>Drop quote here</span></li>
    <li class="ordered-list-item"><span class='empty'>Drop quote here</span></li>
    <li class="ordered-list-item"><span class='empty'>Drop quote here</span></li>
    <li class="ordered-list-item"><span class='empty'>Drop quote here</span></li>
    <li class="ordered-list-item"><span class='empty'>Drop quote here</span></li>
    <li class="ordered-list-item"><span class='empty'>Drop quote here</span></li>
    <li class="ordered-list-item"><span class='empty'>Drop quote here</span></li>
    <li class="ordered-list-item"><span class='empty'>Drop quote here</span></li>
    <li class="ordered-list-item"><span class='empty'>Drop <strong>worst</strong> quote here</span></li>
  </ul>
</section>

<section id='quotes-area'>
<div id='quotes-container'>
  <div id='quote-count'>Drag <span id='counter'></span> of 10 quotes</div>
  <ul id="quotes-list">
    <li class="ordered-list-item"><span class='quote-text'>We must remember that the struggle for our rights is not the struggle of a day, or a year, or a generation. It is the struggle of a lifetime, and one that must be fought by every generation. Our time to fight is now.</span></li>

    <li class="ordered-list-item"><span class='quote-text'>Jeb Bush saying that a half billion dollars for women's health care is too much shows just how low the Republican candidates will sink to cater to the radical fringe in this election. We must fight Republican attacks on women's health care.</span></li>

    <li class="ordered-list-item"><span class='quote-text'>We will take on the current ruling class. From Wall Street, to the insurance companies, to the drug companies to Big Energy, to the Koch Brothers to the Military Industrial Complex.</span></li>

    <li class="ordered-list-item"><span class='quote-text'>For decades, Republicans have been trying to deny a woman the right to control her own body. At a time when 35 million Americans lack health insurance, Planned Parenthood clinics all over the country provide health care to over 2 million women each year, including 1.5 million low-income patients. Instead of trying to take away health care from millions of women, we should be passing legislation to provide family and medical leave to all of our families.</span></li>

    <li class="ordered-list-item"><span class='quote-text'>I understand that Republicans, including many of those running for president, are dependent on the Koch brothers, oil companies and other fossil-fuel contributors. Maybe for once they can overcome the needs of their campaign contributors and worry instead about the planet.</span></li>

    <li class="ordered-list-item"><span class='quote-text'>We cannot live in a vibrant democracy unless people get divergent sources of information. Unfortunately, today a few massive multinational media conglomerates control more and more of the sources of our information.</span></li>

    <li class="ordered-list-item"><span class='quote-text'>Today, according to the Department of Labor, nearly eight out of ten workers who are eligible to take time off under current law cannot do so because they can't afford it. Even worse, 40 percent of American workers aren't even eligible for this unpaid leave.</span></li>

    <li class="ordered-list-item"><span class='quotes-text'>I am very honored to receive the endorsement of one of the great environmental organizations not only in America but in the world.</span></li>

    <li class="ordered-list-item"><span class='quotes-text'>At a time when our middle class is disappearing and the gap between the very rich and everyone else is growing wider, this anti-worker trade agreement must be defeated.</span></li>

    <li class="ordered-list-item" data-rank='10'><span class='quotes-text'>New Hampshire, I will be in Franklin for a town meeting tomorrow. Join me for a discussion on getting big money out of politics, combating climate change, making college education more affordable, and dealing with obscene wealth and income inequality.</span></li>

    <li class='not-ordered-item'>
      <a href='#'>Submit Ratings</a>
    </li>
  </ul>

</div>
</section>

<script>
  // var qNum = function() {
  //   $("#quotes-list .ordered-list-number").
  // };


    var quoteHandler = function() {

      this.updateQuoteCounter = function() {

        if($("#quotes-list").children(".ordered-list-item").length) {
          $("#quote-count span#counter").text(11-$("#quotes-list").children(".ordered-list-item").length);
        } else {
          $("#quote-count").text("Done! Drag to rearrange. Click for full view.");
        }

      };

      this.initialize = function() {
        var that = this;
        //Set events
        $( "#ordered-list" ).sortable({
          cursorAt: { top: 5, left: 5 },
          start: function(event, ui) {
            $(ui.placeholder).removeClass("item-show-all");
            $(ui.item).removeClass("item-show-all");

            var ind = $(ui.item).index();
            $(ui.placeholder).text(ind < $(ui.placeholder).index() ? $(ui.placeholder).index(): $(ui.placeholder).index()+1);
          },
          change: function(event, ui) {
              if (ui.sender) { return; }
              var ind = $(ui.item).index();
            // $(this).children().each(function(i, item) {
              // $(item).find(".bg-text").text(i+1);
              // console.log(ui.placeholder.text());
              $(ui.placeholder).text(ind < $(ui.placeholder).index() ? $(ui.placeholder).index(): $(ui.placeholder).index()+1);
            // });
          },
          update: function(event, ui) {
            console.log(ui, ui.sender);
          }
        });
        $( "#ordered-list" ).disableSelection();

        $( "#ordered-list .ordered-list-item").droppable({
          scope: "quotes",
          connectToSortable: "#ordered-list",
          over: function(event, ui) {
            $(event.target).addClass("highlight");
          }, out: function(event, ui) {
            $(event.target).removeClass("highlight");
          }, drop: function(event, ui) {

            console.log(ui.helper);
            $(event.target).removeClass("highlight");
            console.log("XX", $(ui.draggable).children(".quote-text"));
            if ($(event.target).children(".quote-text").length > 0) {
              var tmp = $(ui.draggable).html();
              $(ui.draggable).html($(event.target).html());
              $(ui.helper).html($(event.target).html());
              $(event.target).html(tmp);
            } else {
              $(event.target).html($(ui.draggable).html());
              $(ui.draggable).remove();
              $(ui.helper).remove();
            }

            that.updateQuoteCounter();

          }
        });
        $("#quotes-list .ordered-list-item").draggable({ helper: "clone", scope: "quotes", revert: true, cursorAt: { top: 5, left: 5 } });

        $(".ordered-list-item").on("click", function() {
          $(this).toggleClass("item-show-all");
        });

        $(document).bind('touchmove', function(e) {
           e.preventDefault();
        }, false);

        that.updateQuoteCounter();
      };

      this.initialize();
    };

  var bernieQuote = new quoteHandler();

  </script>
</body>
