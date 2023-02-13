$(function () {
  let json1 = "../public/json/test.json";
  let json2 = "../public/json/test2.json";
  let male = $("#male");
  let female = $("#female");
  let unisex = $("#unisex");
  let any = $("#any");
  let first = $("#first");
  let last = $("#last");
  let both = $("#both");
  let test;
  let test2;


  $("button").click(function () {
    $("span").empty();
    $.when(
      $.getJSON(json1, function (data) {
        test = data;
      }),
      $.getJSON(json2, function (data) {
        test2 = data;
      })
    ).then(function () {
      if (test) {
        console.log(test);
        if (first.prop("checked") || both.prop("checked")) {
          let fn = getRandomInt(0, test.length - 1);
          if (male.prop("checked")) {
            test = jQuery.grep(test, function (a, i) {
              return (a.gender === "M" && i === fn);
            });
            $("span").append(test[fn].name + " ");
          }
          if (female.prop("checked")) {
            if (test[fn].gender === "F") {
              $("span").append(test[fn].name + " ");
            } else {
              fn = getRandomInt(0, test.length - 1);
            }
          }
          if (unisex.prop("checked")) {
            if (test[fn].gender === "U") {
              $("span").append(test[fn].name + " ");
            } else {
              fn = getRandomInt(0, test.length - 1);
            }
          }
          if (any.prop("checked")) {
            $("span").append(test[fn].name + " ");
          }
        }
      } else {
        console.log("Couldn't handle test");
      }
      if (test2) {
        console.log(test2);
        if (last.prop("checked") || both.prop("checked")) {
          let ln = getRandomInt(0, test2.length - 1);
          $("span").append(test2[ln].name);
        }
      } else {
        console.log("Couldn't handle test2");
      }
    });
  });
});
