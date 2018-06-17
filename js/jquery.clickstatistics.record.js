(function () {
    var c = {
        fps: 24,
        recTime: 120,
        postInterval: 2,
        trackingServer: "/smt2/",
        storageServer: "",
        warn: false,
        warnText: "We'd like to track your mouse activity\nin order to improve this website's usability.\nDo you agree?",
        cookieDays: 365,
        disabled: 0
    };
    var b = window.smt2fn;
    if (typeof b === "undefined") {
        throw ("auxiliar (smt)2 functions not found")
    }
    var a = {
        i: 0,
        mouse: {
            x: 0,
            y: 0
        },
        page: {
            width: 0,
            height: 0
        },
        discrepance: {
            x: 1,
            y: 1
        },
        coords: {
            x: [],
            y: []
        },
        clicks: {
            x: [],
            y: []
        },
        elem: {
            hovered: [],
            clicked: []
        },
        url: null,
        rec: null,
        userId: null,
        append: null,
        paused: false,
        clicked: false,
        timestamp: null,
        timer: null,
        timeout: c.fps * c.recTime,
        xmlhttp: b.createXMLHTTPObject(),
        firstTimeUser: 1,
        pauseRecording: function () {
            a.paused = true
        },
        resumeRecording: function () {
            a.paused = false
        },
        normalizeData: function () {
            var d = b.getPageSize();
            a.discrepance.x = b.roundTo(d.width / a.page.width);
            a.discrepance.y = b.roundTo(d.height / a.page.height)
        },
        getMousePos: function (d) {
            var g = 0,
                f = 0;
            if (!d) {
                d = window.event
            }
            if (d.pageX || d.pageY) {
                g = d.pageX;
                f = d.pageY
            } else {
                if (d.clientX || d.clientY) {
                    g = d.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
                    f = d.clientY + document.body.scrollTop + document.documentElement.scrollTop
                }
            }
            if (g < 0) {
                g = 0
            }
            if (f < 0) {
                f = 0
            }
            a.mouse.x = g;
            a.mouse.y = f
        },
        setClick: function () {
            a.clicked = true
        },
        releaseClick: function () {
            a.clicked = false
        },
        recMouse: function () {
            if (a.paused) {
                return
            }
            if (a.i < a.timeout) {
                var d = a.mouse.x;
                var e = a.mouse.y;
                a.coords.x.push(d);
                a.coords.y.push(e);
                if (!a.clicked) {
                    a.clicks.x.push(null);
                    a.clicks.y.push(null)
                } else {
                    a.clicks.x.push(d);
                    a.clicks.y.push(e)
                }
            } else {
                clearInterval(a.rec);
                clearInterval(a.append)
            }++a.i
        },
        initMouseData: function () {
            a.computeAvailableSpace();
            var d = "url=" + a.url;
            d += "&urltitle=" + document.title;
            d += "&cookies=" + document.cookie;
            d += "&screenw=" + screen.width;
            d += "&screenh=" + screen.height;
            d += "&pagew=" + a.page.width;
            d += "&pageh=" + a.page.height;
            d += "&time=" + a.getTime();
            d += "&fps=" + c.fps;
            d += "&ftu=" + a.firstTimeUser;
            d += "&xcoords=" + a.coords.x;
            d += "&ycoords=" + a.coords.y;
            d += "&xclicks=" + a.clicks.x;
            d += "&yclicks=" + a.clicks.y;
            d += "&elhovered=" + a.elem.hovered;
            d += "&elclicked=" + a.elem.clicked;
            d += "&action=store";
            d += "&remote=" + c.storageServer;
            b.sendAjaxRequest({
                url: c.trackingServer + "/core/gateway.php",
                callback: a.setUserId,
                postdata: d,
                xmlhttp: a.xmlhttp
            });
            a.clearMouseData()
        },
        setUserId: function (d) {
            a.userId = parseInt(d);
            if (a.userId > 0) {
                a.append = setInterval(a.appendMouseData, c.postInterval * 1000)
            }
        },
        getTime: function () {
            var d = (new Date()).getTime() - a.timestamp;
            return d / 1000
        },
        appendMouseData: function () {
            if (!a.rec || a.paused) {
                return false
            }
            var d = "uid=" + a.userId;
            d += "&time=" + a.getTime();
            d += "&pagew=" + a.page.width;
            d += "&pageh=" + a.page.height;
            d += "&xcoords=" + a.coords.x;
            d += "&ycoords=" + a.coords.y;
            d += "&xclicks=" + a.clicks.x;
            d += "&yclicks=" + a.clicks.y;
            d += "&elhovered=" + a.elem.hovered;
            d += "&elclicked=" + a.elem.clicked;
            d += "&action=append";
            d += "&remote=" + c.storageServer;
            b.sendAjaxRequest({
                url: c.trackingServer + "/core/gateway.php",
                postdata: d,
                xmlhttp: a.xmlhttp
            });
            a.clearMouseData()
        },
        clearMouseData: function () {
            a.coords.x = [];
            a.coords.y = [];
            a.clicks.x = [];
            a.clicks.y = [];
            a.elem.hovered = [];
            a.elem.clicked = []
        },
        findElement: function (d) {
            if (!d) {
                d = window.event
            }
            b.widget.findDOMElement(d, function (e) {
                if (d.type == "mousedown") {
                    a.elem.clicked.push(e)
                } else {
                    if (d.type == "mousemove") {
                        a.elem.hovered.push(e)
                    }
                }
            })
        },
        computeAvailableSpace: function () {
            var d = b.getPageSize();
            a.page.width = d.width;
            a.page.height = d.height
        },
        init: function () {
            a.computeAvailableSpace();
            a.url = escape(window.location.href);
            var d = Math.round(1000 / c.fps);
            a.rec = setInterval(a.recMouse, d);
            b.allowTrackingOnFlashObjects();
            b.addEvent(document, "mousemove", a.getMousePos);
            b.addEvent(document, "mousedown", a.setClick);
            b.addEvent(document, "mouseup", a.releaseClick);
            b.addEvent(window, "resize", a.computeAvailableSpace);
            if (document.attachEvent) {
                b.addEvent(document.body, "focusout", a.pauseRecording);
                b.addEvent(document.body, "focusin", a.resumeRecording)
            } else {
                b.addEvent(window, "blur", a.pauseRecording);
                b.addEvent(window, "focus", a.resumeRecording)
            }
            b.addEvent(document, "mousedown", a.findElement);
            b.addEvent(document, "mousemove", a.findElement);
            if (typeof window.onbeforeunload == "function") {
                b.addEvent(window, "beforeunload", a.appendMouseData)
            } else {
                b.addEvent(window, "unload", a.appendMouseData)
            }
            setTimeout(a.initMouseData, c.postInterval * 1000);
            a.timestamp = (new Date()).getTime()
        }
    };
    if (typeof window.smt2 !== "undefined") {
        throw ("smt2 namespace conflict")
    }
    window.smt2 = {
        record: function (d) {
            if (typeof d !== "undefined") {
                b.overrideTrackingOptions(c, d)
            }
            var j = b.cookies.checkCookie("smt-ftu");
            if (c.disabled && j) {
                return
            }
            a.firstTimeUser = (!j | 0);
            b.cookies.setCookie("smt-ftu", a.firstTimeUser, c.cookieDays);
            if (c.warn) {
                var k = b.cookies.checkCookie("smt-agreed");
                var l = (k) ? b.cookies.getCookie("smt-agreed") : window.confirm(c.warnText);
                if (l > 0) {
                    b.cookies.setCookie("smt-agreed", 1, c.cookieDays)
                } else {
                    b.cookies.setCookie("smt-agreed", 0, 1);
                    return false
                }
            }
            var f = document.getElementsByTagName("script");
            for (var g = 0, n = f.length; g < n; ++g) {
                var e = f[g].src;
                if (/smt-record/i.test(e)) {
                    var m = e.split("/");
                    var h = b.array.indexOf(m, "smt2");
                    if (h && c.trackingServer === null) {
                        c.trackingServer = m.slice(0, h + 1).join("/")
                    }
                }
            }
            b.onDOMload(a.init)
        }
    }
})();