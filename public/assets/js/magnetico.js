$(document).ready(function () {
    console.log("ready!");
    renderURL("/service.php/info");

    $("#txtbox-search").focus();

    skel.breakpoints({
        xlarge: '(max-width: 1680px)',
        large: '(max-width: 1280px)',
        medium: '(max-width: 980px)',
        small: '(max-width: 736px)',
        xsmall: '(max-width: 480px)',
        xxsmall: '(max-width: 360px)'
    });

    var $window = $(window),
        $body = $('body'),
        $wrapper = $('#wrapper'),
        $header = $('#header'),
        $footer = $('#footer'),
        $main = $('#main'),
        $main_articles = $main.children('article');

    // Disable animations/transitions until the page has loaded.
    $body.addClass('is-loading');

    $window.on('load', function () {
        window.setTimeout(function () {
            $body.removeClass('is-loading');
        }, 100);
    });

    // Fix: Placeholder polyfill.
    $('form').placeholder();

    // Fix: Flexbox min-height bug on IE.
    if (skel.vars.IEVersion < 12) {

        var flexboxFixTimeoutId;

        $window.on('resize.flexbox-fix', function () {

            clearTimeout(flexboxFixTimeoutId);

            flexboxFixTimeoutId = setTimeout(function () {

                if ($wrapper.prop('scrollHeight') > $window.height())
                    $wrapper.css('height', 'auto');
                else
                    $wrapper.css('height', '100vh');

            }, 250);

        }).triggerHandler('resize.flexbox-fix');

    }

    function renderURL($url) {
        $.getJSON($url, function (data) {
            var items = [];
            if (data.result === 'ok') {
                switch (data.type) {
                    case 'torrent_count' :
                        $("<p/>", {
                            html: data['data'] + " Torrents Online"
                        }).appendTo("footer");
                        break;
                    case 'search':
                        $("#searchresultstable tbody").empty();

                        for (var i = 0; i < data.data.results.length; i++) {
                            drawSearchRow(data.data.results[i]);
                        }
                        drawPageination(data.data.page_info.pages, data.data.page_info.current, $url);
                        showPanel("#searchresults");
                        break;
                    case 'torrentdetail':
                        $("#torrentdetails").empty();
                        drawTorrentDetail(data.data.torrent[0]);
                        $("#torrentdetailtable tbody").empty();
                        for (var i = 0; i < data.data.files.length; i++) {
                            drawDetailRow(data.data.files[i]);
                        }

                        showPanel("#torrentdetail");
                        break;
                }
            }
        });
    }

    function formatBytes(a, b) {
        if (0 == a) return "0 Bytes";
        var c = 1024, d = b || 2, e = ["Bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"],
            f = Math.floor(Math.log(a) / Math.log(c));
        return parseFloat((a / Math.pow(c, f)).toFixed(d)) + " " + e[f]
    }

    function drawSearchRow(rowData) {
        var row = $("<tr />")
        $("#searchresultstable tbody").append(row); //this will append tr element to table... keep its reference for a while since we will add cels into it
        row.append($("<td><a href='" + rowData.magnet + "'><i class=\"fa fa-magnet\" aria-hidden=\"true\"></i></a></td>"));
        row.append($("<td><a href='#' data-action='showDetail' data-torrentID='" + rowData.id + "'>" + rowData.name + "</a></td>"));
        row.append($("<td>" + formatBytes(rowData.total_size, 1) + "</td>"));

    }


    function drawTorrentDetail(torrentData) {
        var div = $("<div/>").addClass("flex-container");
        $("#torrentdetails").append(div);
        div.append("<span> <b>Name: </b> " + torrentData.name + "</span>");
        div.append("<span> <b>Size: </b>" + formatBytes(torrentData.total_size, 1) + "</span>");
        div.append("<span><a href='" + torrentData.magnet + "'><i class=\"fa fa-magnet\" aria-hidden=\"true\"></i></a></span>");
    }

    function drawDetailRow(rowData) {
        var row = $("<tr />")
        $("#torrentdetailtable tbody").append(row); //this will append tr element to table... keep its reference for a while since we will add cels into it
        row.append($("<td>./" + rowData.path + "</td>"));
        row.append($("<td>" + formatBytes(rowData.size, 1) + "</td>"));

    }

    function drawPageination(pageCount, currentPage, currentURL) {
        currentPage = parseInt(currentPage);
        pageCount = parseInt(pageCount);
        var ul = $("<ul/>")
            .addClass("pagination")
            .attr("data-url", currentURL);
        var prevClass = "button small ";
        if (currentPage == 1) {
            prevClass += "disabled";
        }

        ul.append("<li><span data-action=\"" + (currentPage - 1) + "\" class=\"" + prevClass + "\">Prev</span></li>");

        for (var i = 1; i <= pageCount; i++) {
            var aclass = "page ";
            if (i === currentPage) {
                aclass += " active";
            }


            ul.append("<li><a href=\"#\" class=\"" + aclass + "\">" + i + "</a></li>");
        }
        var nextClass = "button small ";

        if (currentPage == pageCount) {
            nextClass += "disabled";
        }
        ul.append("<li><span data-action=\"" + (currentPage + 1) + "\" class=\"" + nextClass + "\">Next</span></li>");
        $("#pageination").empty();
        $("#pageination").append(ul);
    }

    $('#txtbox-search').keypress(function (e) {
        if (e.which == 13) {
            renderURL("/service.php/search/" + encodeURIComponent($('#txtbox-search').val()));
            $('#searchresults')[0].click();

            return false;    //<---- prevent form submission
        }
    });


    $("#pageination").on("click", "li .page", function (event) {
        var termURL = $(this).closest('ul').attr("data-url");
        termURL = termURL.replace(/(\/[0-9]+$)/i, '');

        var requestedPage = $(this).text();
        //alert(termURL + '/' + requestedPage);
        renderURL(termURL + '/' + requestedPage);
        return false;
    });

    $("#pageination").on("click", ".button", function (event) {
        var termURL = $(this).closest('ul').attr("data-url");
        termURL = termURL.replace(/(\/[0-9]+$)/i, '');
        var requestedPage = $(this).attr("data-action");
        //alert(termURL + '/' + requestedPage);
        renderURL(termURL + '/' + requestedPage);
        return false;
    });

    $("#searchresultstable").on("click", "a[data-torrentID]", function (event) {
        var torrentID = $(this).attr("data-torrentID");
        renderURL('/service.php/detail/' + torrentID);
        return false;
    });


    function showPanel(panel) {
        $("article").each(function () {
            $(this).removeClass("active")
        });
        $(panel).addClass("active").css('outline', 'none !important')
            .attr("tabindex", -1)
            .focus();
    }

    $("#backtosearch").on("click", function (event) {
        showPanel("#searchresults");
        return false;
    });
});
