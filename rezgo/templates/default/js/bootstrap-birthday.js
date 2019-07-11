(function ($) {
  'use strict';

  /**
   * Default settings.
   */
  var defaults = {

    /**
     * The maxAge setting determines the oldest year you can pick for a birthday.
     * So if you set the maxAge to 100 and the current year is 2010, then the
     * oldest year you can pick will be 1910.
     */
    maxAge: 120,

    /**
     * The opposite of maxAge. If current year is 2010 and minAge is set to 18,
     * the earliest year that can be picked is 1992.
     */
    minAge: 0,

    /**
     * The futureDates setting determines whether birthdays in the future can be
     * selected. Unless you need to support entering birthdays of unborn babies,
     * this should generally be false.
     */
    futureDates: false,

    /**
     * The maxYear setting allows you to set the maximum year that can be chosen,
     * counting up from 0. If you pass in a year (such as 1980) then it uses that
     * year. If you pass in a number under 1000 (such as 5) then it adds it to
     * the current year, so if the year was 2010 then the maxYear would become
     * 2015.
     *
     * If you want the maxYear to be in the future, you must set futureDates to
     * true.
     * If you want the maxYear in the past, you can pass in a negative number or
     * a past year (if its over 1000).
     */
    maxYear: 0,

    /**
     * The dateFormat setting determines the order of the select fields in the
     * markup and supports the following three values:
     * - middleEndian: Month, Day, Year
     * - littleEndian: Day, Month, Year
     * - bigEndian: Year, Month, Day
     */
    dateFormat: 'middleEndian',

    /**
     * The monthFormat setting determines the text displayed in the month select
     * box. It can be either short, or long. i.e. Jan or January
     */
    monthFormat: 'short',

    /**
     * The placeholder adds a default option to each select list just like
     * Facebook does on their sign-up page.
     * The default option just says Month, Day, or Year with a colon after it.
     * If you keep this set to true, you will need to add logic, preferably on
     * the client and server, to ensure this option isn't chosen. The value for
     * these options is 0.
     */
    placeholder: true,

    /**
     * The tabindex setting determines the tab order of select elements.
     */
    tabindex: null,

    // Localization.
    text: {
      year: "Year",
      month: "Month",
      day: "Day",
      months: {
        short: [
          "Jan",
          "Feb",
          "Mar",
          "Apr",
          "May",
          "Jun",
          "Jul",
          "Aug",
          "Sep",
          "Oct",
          "Nov",
          "Dec"
        ],
        long: [
          "January",
          "February",
          "March",
          "April",
          "May",
          "June",
          "July",
          "August",
          "September",
          "October",
          "November",
          "December"
        ]
      }
    },

    // Widget options.
    widget: {
      wrapper: {
        tag: 'div',
        class: 'input-group'
      },
      wrapperYear: {
        use: true,
        tag: 'span',
        class: 'input-group-addon'
      },
      wrapperMonth: {
        use: true,
        tag: 'span',
        class: 'input-group-addon'
      },
      wrapperDay: {
        use: true,
        tag: 'span',
        class: 'input-group-addon'
      },
      selectYear: {
        name: 'birthday[year]',
        class: 'form-control input-sm'
      },
      selectMonth: {
        name: 'birthday[month]',
        class: 'form-control input-sm'
      },
      selectDay: {
        name: 'birthday[day]',
        class: 'form-control input-sm'
      }
    },

    // Callback function.
    onChange: function () {
    }

  };

  $.fn.bootstrapBirthday = function (options) {
    // Create namespace.
    var bsBD = {};

    var init = function (element) {
      // Merge options.
      bsBD.settings = $.extend({}, defaults, options);
      // Save initial element for later use.
      bsBD.$element = $(element);

      createHtmlSkeleton();
      createHtmlWidget();
    };

    /**
     * Creates HTML picker skeleton.
     */
    var createHtmlSkeleton = function () {
      bsBD.$wrapper = $("<" + bsBD.settings.widget.wrapper.tag + "/>");
      if (bsBD.settings.widget.wrapper.class != "") {
        bsBD.$wrapper.attr('class', bsBD.settings.widget.wrapper.class);
      }

      bsBD.$wrapperYear = $("<" + bsBD.settings.widget.wrapperYear.tag + "/>");
      if (bsBD.settings.widget.wrapperYear.class != "") {
        bsBD.$wrapperYear.attr('class', bsBD.settings.widget.wrapperYear.class);
      }

      bsBD.$wrapperMonth = $("<" + bsBD.settings.widget.wrapperMonth.tag + "/>");
      if (bsBD.settings.widget.wrapperMonth.class != "") {
        bsBD.$wrapperMonth.attr('class', bsBD.settings.widget.wrapperMonth.class);
      }

      bsBD.$wrapperDay = $("<" + bsBD.settings.widget.wrapperDay.tag + "/>");
      if (bsBD.settings.widget.wrapperDay.class != "") {
        bsBD.$wrapperDay.attr('class', bsBD.settings.widget.wrapperDay.class);
      }

      bsBD.$year = $("<select></select>");
      if (bsBD.settings.widget.selectYear.name != "") {
        bsBD.$year.attr('name', bsBD.settings.widget.selectYear.name);
      }
      if (bsBD.settings.widget.selectYear.class != "") {
        bsBD.$year.attr('class', bsBD.settings.widget.selectYear.class);
      }

      bsBD.$month = $("<select></select>");
      if (bsBD.settings.widget.selectMonth.name != "") {
        bsBD.$month.attr('name', bsBD.settings.widget.selectMonth.name);
      }
      if (bsBD.settings.widget.selectMonth.class != "") {
        bsBD.$month.attr('class', bsBD.settings.widget.selectMonth.class);
      }

      bsBD.$day = $("<select></select>");
      if (bsBD.settings.widget.selectDay.name != "") {
        bsBD.$day.attr('name', bsBD.settings.widget.selectDay.name);
      }
      if (bsBD.settings.widget.selectDay.class != "") {
        bsBD.$day.attr('class', bsBD.settings.widget.selectDay.class);
      }
    };

    /**
     * Creates HTML widget.
     */
    var createHtmlWidget = function () {
      if (bsBD.settings.widget.wrapperYear.use == true) {
        bsBD.$wrapperYear.append(bsBD.$year);
      }
      else {
        bsBD.$wrapperYear = bsBD.$year;
      }

      if (bsBD.settings.widget.wrapperMonth.use == true) {
        bsBD.$wrapperMonth.append(bsBD.$month);
      }
      else {
        bsBD.$wrapperMonth = bsBD.$month;
      }

      if (bsBD.settings.widget.wrapperDay.use == true) {
        bsBD.$wrapperDay.append(bsBD.$day);
      }
      else {
        bsBD.$wrapperDay = bsBD.$day;
      }

      switch (bsBD.settings.dateFormat) {
        case 'bigEndian':
          bsBD.$wrapper
            .append(bsBD.$wrapperYear)
            .append(bsBD.$wrapperMonth)
            .append(bsBD.$wrapperDay);

          if (bsBD.settings.tabindex != null) {
            bsBD.$year.attr('tabindex', bsBD.settings.tabindex);
            bsBD.$month.attr('tabindex', bsBD.settings.tabindex++);
            bsBD.$day.attr('tabindex', bsBD.settings.tabindex++);
          }
          break;

        case 'littleEndian':
          bsBD.$wrapper
            .append(bsBD.$wrapperDay)
            .append(bsBD.$wrapperMonth)
            .append(bsBD.$wrapperYear);

          if (bsBD.settings.tabindex != null) {
            bsBD.$day.attr('tabindex', bsBD.settings.tabindex);
            bsBD.$month.attr('tabindex', bsBD.settings.tabindex++);
            bsBD.$year.attr('tabindex', bsBD.settings.tabindex++);
          }
          break;

        case 'middleEndian':
        default:
          bsBD.$wrapper
            .append(bsBD.$wrapperMonth)
            .append(bsBD.$wrapperDay)
            .append(bsBD.$wrapperYear);

          if (bsBD.settings.tabindex != null) {
            bsBD.$month.attr('tabindex', bsBD.settings.tabindex);
            bsBD.$day.attr('tabindex', bsBD.settings.tabindex++);
            bsBD.$year.attr('tabindex', bsBD.settings.tabindex++);
          }
          break;
      }

      // Add the option placeholders if specified.
      if (bsBD.settings.placeholder) {
        $("<option value='0'>" + bsBD.settings.text.year + "</option>")
          .appendTo(bsBD.$year);
        $("<option value='0'>" + bsBD.settings.text.month + "</option>")
          .appendTo(bsBD.$month);
        $("<option value='0'>" + bsBD.settings.text.day + "</option>")
          .appendTo(bsBD.$day);
      }

      // Make text input hidden.
      $(this).attr('type', 'hidden');

      // Build the initial option sets.
      var todayDate = new Date();
      var todayYear = todayDate.getFullYear();
      var startYear = todayYear - bsBD.settings.minAge;
      var endYear = todayYear - bsBD.settings.maxAge;

      if (bsBD.settings.futureDates && bsBD.settings.maxYear != todayYear) {
        if (bsBD.settings.maxYear > 1000) {
          startYear = bsBD.settings.maxYear;
        }
        else {
          startYear = todayYear + bsBD.settings.maxYear;
        }
      }

      for (var i = startYear; i >= endYear; i--) {
        $("<option></option>")
          .attr("value", i)
          .text(i)
          .appendTo(bsBD.$year);
      }

      for (var j = 0; j < 12; j++) {
        $("<option></option>")
          .attr("value", j + 1)
          .text(bsBD.settings.text.months[bsBD.settings.monthFormat][j])
          .appendTo(bsBD.$month);
      }

      for (var k = 1; k < 32; k++) {
        $("<option></option>")
          .attr("value", k)
          .text(k)
          .appendTo(bsBD.$day);
      }

      // Hide initial text input.
      bsBD.$element.attr('type', 'hidden');

      // Append widget to DOM.
      bsBD.$element.after(bsBD.$wrapper);

      // Set the default date if given.
      setDefaultValue();

      // Update the option sets according to options and user selections.
      bsBD.$wrapper.change(function () {
        // Today date values.
        var todayDate = new Date();
        var todayMonth = todayDate.getMonth() + 1;
        var todayDay = todayDate.getDate();
        // Currently selected values.
        var selectedYear = parseInt(bsBD.$year.val(), 10);
        var selectedMonth = parseInt(bsBD.$month.val(), 10);
        var selectedDay = parseInt(bsBD.$day.val(), 10);
        // Number of days in currently selected year/month.
        var actMaxDay = (new Date(selectedYear, selectedMonth, 0)).getDate();
        // Max values currently in the markup.
        var curMaxMonth = parseInt(bsBD.$month.children(":last").val());
        var curMaxDay = parseInt(bsBD.$day.children(":last").val());

        // Dealing with the number of days in a month.
        // http://bugs.jquery.com/ticket/3041
        if (curMaxDay > actMaxDay) {
          while (curMaxDay > actMaxDay) {
            bsBD.$day.children(":last").remove();
            curMaxDay--;
          }
        }
        else {
          if (curMaxDay < actMaxDay) {
            while (curMaxDay < actMaxDay) {
              curMaxDay++;
              bsBD.$day.append("<option value=" + curMaxDay + ">" + curMaxDay + "</option>");
            }
          }
        }

        // Dealing with future months/days in current year or months/days that
        // fall after the minimum age.
        if (!bsBD.settings.futureDates && selectedYear == startYear) {
          if (curMaxMonth > todayMonth) {
            while (curMaxMonth > todayMonth) {
              bsBD.$month.children(":last").remove();
              curMaxMonth--;
            }
            // Reset the day selection.
            bsBD.$day.children(":first").attr("selected", "selected");
          }
          if (selectedMonth === todayMonth) {
            while (curMaxDay > todayDay) {
              bsBD.$day.children(":last").remove();
              curMaxDay -= 1;
            }
          }
        }

        // Adding months back that may have been removed.
        // http://bugs.jquery.com/ticket/3041
        if (selectedYear != startYear && curMaxMonth != 12) {
          while (curMaxMonth < 12) {
            bsBD.$month.append("<option value=" + (curMaxMonth + 1) + ">" + bsBD.settings.text.months[bsBD.settings.monthFormat][curMaxMonth] + "</option>");
            curMaxMonth++;
          }
        }

        // Update the hidden date.
        if ((selectedYear * selectedMonth * selectedDay) != 0) {
          if (selectedMonth < 10) {
            selectedMonth = "0" + selectedMonth;
          }

          if (selectedDay < 10) {
            selectedDay = "0" + selectedDay;
          }

          var hiddenDate = selectedYear + "-" + selectedMonth + "-" + selectedDay;

          bsBD.$element.val(hiddenDate);
          bsBD.settings.onChange(hiddenDate);
        }
      });
    };

    /**
     * Set default value if given.
     */
    var setDefaultValue = function () {
      var value = bsBD.$element.val();

      if (value != "") {
        var dP = value.split("-");
        var date;

        switch (bsBD.settings.dateFormat) {
          case 'bigEndian':
            date = new Date(dP[0] + "-" + dP[1] + "-" + dP[2] + "T00:00:00");
            break;

          case 'littleEndian':
            date = new Date(dP[2] + "-" + dP[1] + "-" + dP[0] + "T00:00:00");
            break;

          case 'middleEndian':
          default:
            date = new Date(dP[2] + "-" + dP[0] + "-" + dP[1] + "T00:00:00");
            break;
        }

        if (date) {
          bsBD.$year.val(date.getFullYear());
          bsBD.$month.val(date.getMonth() + 1);
          bsBD.$day.val(date.getDate());
        }
      }
    };

    // Run!
    init(this);
  };

})(jQuery);
