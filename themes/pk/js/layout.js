define("layout/util/dataPreparationsForAnchors", ["utils", "lodash"], function (a, b) {
    "use strict";
    function e() {
        return{flat: {}, sortingY: {}, minHeight: {}, toBottomDistance: {}, ignoreOriginalValue: {}, ignoreHeightChangingAnchors: {}, noHeightChange: {}, containerHeightMargin: {}, currentHeight: {}, currentY: {}, heightDiff: {}, topDiff: {}, locked: {}, valueForFirstLockedAnchor: {}}
    }

    function f(a, b) {
        var c = a.id;
        a.layout && a.layout.rotationInDegrees && (b.noHeightChange[c] = !0), "Page" === a.type ? b.toBottomDistance[c] = 0 : ("Document" === a.type || "wysiwyg.viewer.components.PagesContainer" === a.componentType) && (b.ignoreOriginalValue[c] = !0), "wysiwyg.viewer.components.Group" === a.componentType && (b.ignoreHeightChangingAnchors[c] = !0)
    }

    function g(a, b, c) {
        var d = c * Math.PI / 180;
        return parseInt(Math.abs(b * Math.sin(d)) + Math.abs(a * Math.cos(d)), 10)
    }

    function h(a, b, c) {
        var d = a.id, e = a.layout ? a.layout.rotationInDegrees : 0, f = c.height[d], h = c.top && void 0 !== c.top[d] ? c.top[d] : a.layout && a.layout.y || 0;
        b.currentHeight[a.id] = e ? g(f, c.width[d], e) : f, b.heightDiff[d] = b.currentHeight[a.id] - f, b.topDiff[d] = Math.ceil(b.heightDiff[d] / 2), b.currentY[d] = e ? h - b.topDiff[d] : h
    }

    function i(c, d, e, g, k, l) {
        f(c, d, e), h(c, d, g), d.minHeight[c.id] = j(c, d), d.currentHeight[c.id] = Math.max(d.currentHeight[c.id], d.minHeight[c.id]), d.flat[c.id] = c;
        var m = d.currentY[c.id], n = k + m - l, o = d.currentHeight[c.id];
        d.sortingY[c.id] = n, d.valueForFirstLockedAnchor[c.id] = {pusherId: null, value: -Number.MAX_VALUE};
        var p = a.dataUtils.getChildrenData(c, e);
        b.each(p, function (a) {
            i(a, d, e, g, n, o || 5e3)
        })
    }

    function j(a, b) {
        var c = a.id;
        return c && !k(c) ? Math.max(b.minHeight[c] || 0, d) : b.minHeight[c] || 0
    }

    function k(a) {
        return a && b.contains(c, a)
    }

    function l(a, b, c, d) {
        i(a, b, c, d, 0, 0)
    }

    function m(a, b, c) {
        return{distance: c, type: a, targetComponent: b, locked: !0, notEnforcingMinValue: !0}
    }

    function n(a, c, d, e) {
        var f = a;
        a.$cloned || (f = b.clone(f), f.layout = b.clone(f.layout), f.layout.anchors = b.clone(f.layout.anchors), f.$cloned = !0);
        var g = f.layout.anchors;
        return g.push(m("TOP_TOP", c, d), m("BOTTOM_BOTTOM", c, e)), f
    }

    function o(a) {
        b.forEach(a, function (c, d) {
            if (c.layout && c.layout.anchors) {
                var e = b.filter(c.layout.anchors, {type: "LOCK_BOTTOM"});
                b.isEmpty(e) || (b.forEach(e, function (b) {
                    a[d] = n(a[d], b.targetComponent, b.topToTop, b.distance), a[b.targetComponent] = n(a[b.targetComponent], d, -1 * b.topToTop, -1 * b.distance)
                }), b.remove(a[d].layout.anchors, {type: "LOCK_BOTTOM"}))
            }
        })
    }

    function p(a, c, d) {
        var f = e();
        f.containerHeightMargin = c.containerHeightMargin || {}, f.minHeight = c.minHeight || {}, l(a, f, d, c), o(f.flat);
        var g = b.sortBy(b.keys(f.flat), function (a) {
            return f.sortingY[a]
        });
        return delete f.sortingY, {structureData: f, sortedIds: g}
    }

    function q(a, c) {
        b.forEach(c.flat, function (b, d) {
            a.height[d] = c.currentHeight[d] - c.heightDiff[d], a.top[d] = c.currentY[d] + c.topDiff[d]
        })
    }

    function r(c, d, e, f) {
        e = e || 0, e += c.top[f.id];
        var g = a.dataUtils.getChildrenData(f, d);
        return Math.max(e + c.height[f.id], Math.max.apply(null, b.map(g, r.bind(null, c, d, e))))
    }

    var c = ["WIX_ADS"], d = 5;
    return{getDataForAnchorsAndSort: p, fixMeasureMap: q, maxMeasureMapHeight: r}
}), define("layout/util/anchors", ["lodash", "utils", "layout/util/dataPreparationsForAnchors"], function (a, b, c) {
    "use strict";
    function k(a, b, c, d) {
        return b !== c && (d[a] = !0), d[a]
    }

    function l(b, c, d, e) {
        var f = d[e.targetComponent], g = d[c];
        e.notEnforcingMinValue && !a.isEmpty(g) && (e.notEnforcingMinValue = !1), f[c] = b;
        var h = Math.max.apply(null, a.values(f));
        return e.notEnforcingMinValue && delete f[c], h
    }

    function m(a, b, c, d, e) {
        var f = a.targetComponent, g = b.valueForFirstLockedAnchor[f];
        g && (a.locked ? (g.pusherId && (c[f] = {}, c[f][g.pusherId] = g.value), delete b.valueForFirstLockedAnchor[f]) : (g.pusherId = d, g.value = Math.max(e, g.value)))
    }

    function n(b, c, d, e) {
        var f = c;
        return a.isNumber(e.toBottomDistance[d]) ? f += e.toBottomDistance[d] : b.locked ? f += b.distance : (f += h, e.ignoreOriginalValue[d] || (f = Math.max(f, b.originalValue))), f
    }

    function o(b, c, d, e) {
        var f = b;
        return a.isNumber(e.minHeight[c]) && b < e.minHeight[c] && (f = e.minHeight[c], d.notEnforcingMinValue && (d.notEnforcingMinValue = !1, e.dirty[c] = !0)), f
    }

    function p(a, b) {
        var c = b.flat[a];
        return c.layout ? c.layout.anchors : []
    }

    function q(b, c, d, e, f) {
        function h(b) {
            var h = p(b, d), k = Number.MAX_VALUE;
            return!d.dirty[b] || e && !i[b] || a.each(h, function (a) {
                if (j[a.type] && d.flat[a.targetComponent] && (!e || i[a.targetComponent])) {
                    a.targetComponent === f && (g = !0);
                    var h = j[a.type](b, a, d) ? c[a.targetComponent] : Number.MAX_VALUE;
                    k = Math.min(h, k)
                }
            }), d.dirty[b] = !1, k
        }

        for (var g = !1, k = b.length, l = 0; k > l;) {
            var m = h(b[l]);
            l = Math.min(l + 1, m)
        }
        return g
    }

    function r(b, d, e, f, g) {
        var h = c.getDataForAnchorsAndSort(b, d, e), i = h.structureData, j = h.sortedIds, k = a.invert(j);
        i.dirty = {}, i.toTopAnchorsY = {}, i.toBottomAnchorsHeight = {}, i.locked = {}, g && a.forEach(g, function (a) {
            i.locked[a] = !0
        }), a(i.flat).keys().each(function (a) {
            i.dirty[a] = !0, i.toTopAnchorsY[a] = {}, i.toBottomAnchorsHeight[a] = {}
        });
        var l = q(j, k, i, f, b.id);
        return c.fixMeasureMap(d, i), l || (d.height[b.id] = c.maxMeasureMapHeight(d, e, 0, b)), i.flat
    }

    var d = "TOP_TOP", e = "BOTTOM_TOP", f = "BOTTOM_BOTTOM", g = "BOTTOM_PARENT", h = 10, i = {SITE_FOOTER: !0, SITE_HEADER: !0, SITE_PAGES: !0, PAGES_CONTAINER: !0, SITE_STRUCTURE: !0, SITE_BACKGROUND: !0}, j = {};
    return j[d] = function (b, c, d) {
        var e = c.targetComponent;
        if (a.has(d.locked, e))return!1;
        var f = d.currentY[e], g = d.currentY[b] + c.distance;
        return m(c, d, d.toTopAnchorsY, b, null), g = l(g, b, d.toTopAnchorsY, c), d.currentY[e] = g, k(e, f, g, d.dirty)
    }, j[e] = function (b, c, d) {
        var e = c.targetComponent;
        if (a.has(d.locked, e))return!1;
        var f = d.currentY[e], g = d.currentHeight[b] + d.currentY[b], i = null;
        return c.locked ? g += c.distance : (i = g + h, g = d.valueForFirstLockedAnchor[e] ? Math.max(g + h, c.originalValue) : i), g = Math.max(g, d.currentY[b] + d.currentHeight[b] / 2), m(c, d, d.toTopAnchorsY, b, i), g = l(g, b, d.toTopAnchorsY, c), d.currentY[e] = g, k(e, f, g, d.dirty)
    }, j[g] = function (b, c, d) {
        var e = c.targetComponent;
        if (a.has(d.locked, e))return!1;
        if (d.ignoreHeightChangingAnchors[e])return!1;
        if (d.flat[e].layout && d.flat[e].layout.rotationInDegrees)return!1;
        var f = d.currentHeight[e], g = d.currentHeight[b] + d.currentY[b], h = d.containerHeightMargin[e] || 0, i = n(c, g + h, e, d);
        return i = o(i, e, c, d), i = l(i, b, d.toBottomAnchorsHeight, c), d.currentHeight[e] = i, k(e, f, i, d.dirty)
    }, j[f] = function (b, c, d) {
        var e = c.targetComponent;
        if (a.has(d.locked, e))return!1;
        if (d.ignoreHeightChangingAnchors[e])return!1;
        if (d.flat[e].layout && d.flat[e].layout.rotationInDegrees)return!1;
        var f = d.currentHeight[e], g = d.currentHeight[b] + d.currentY[b], h = d.currentY[e], i = n(c, g - h, e, d);
        return i = o(i, e, c, d), i = l(i, b, d.toBottomAnchorsHeight, c), d.currentHeight[e] = i, k(e, f, i, d.dirty)
    }, {enforceAnchors: r, HARD_WIRED_COMPS: i}
}), define("layout/util/singleCompLayout", ["zepto", "lodash"], function (a, b) {
    "use strict";
    function h(a, b, c, d) {
        var f = c.layout ? c.layout.height : -1, g = c.layout ? c.layout.width : -1, h = d.isDeadComp[a];
        !h && e[c.componentType] ? (d.height[a] = Math.max(b.offsetHeight, f), d.width[a] = Math.max(b.offsetWidth, g)) : (d.height[a] = f >= 0 ? f : b.offsetHeight, d.width[a] = g >= 0 ? g : b.offsetWidth, d.innerWidth[a] = g >= 0 ? g : b.clientWidth)
    }

    function i(a) {
        return a.getAttribute("data-dead-comp")
    }

    function j(a, b) {
        if (!a)return!1;
        var c = b(a);
        return c ? c : !1
    }

    function k(a, b, c, e, f, k) {
        var l = b.id, m = j(l, c);
        if (m) {
            f[l] = m, h(l, m, a, e);
            var n = i(m);
            if (n)return void(e.isDeadComp[l] = !0);
            g[b.type] && g[b.type](l, e, f, k, b), d[b.type] && d[b.type](l, e, f, k, b)
        }
    }

    function l(a, c, e, g, h) {
        var k = a.id, l = j(k, c);
        if (l && !i(l) && f[a.type]) {
            var m = f[a.type];
            "function" == typeof m && (m = m(h, k, g, a)), b.each(m, function (f) {
                var i = b.isPlainObject(f), j = i ? f.pathArray : f, l = c.apply(void 0, [k].concat(j));
                if (l) {
                    var m = j.join(""), n = k + m;
                    g[n] = l, e.height[n] = l.offsetHeight, e.width[n] = l.offsetWidth, e.innerWidth[n] = l.clientWidth, i && d[f.type] && d[f.type](n, e, g, h, a)
                }
            })
        }
    }

    function m(b, c, d) {
        a(c[b]).css({top: d.top[b], height: d.height[b]})
    }

    function n(a, b, d, e) {
        var f = a.id;
        m(f, b, d);
        var g = d.isDeadComp[f], h = !1;
        return!g && c[a.type] && b[f] && (h = c[a.type](f, b, d, a, e)), h
    }

    var c = {}, d = {}, e = {}, f = {}, g = {};
    return{patchComponent: n, measureComponent: k, measureComponentChildren: l, isComponentDead: i, registerPatcher: function (a, b) {
        c[a] = b
    }, registerPatchers: function (a, d) {
        c[a] = function () {
            var a = arguments;
            b.forEach(d, function (b) {
                b.apply(null, a)
            })
        }
    }, registerCustomMeasure: function (a, b) {
        d[a] = b
    }, registerRequestToMeasureDom: function (a) {
        e[a] = !0
    }, registerRequestToMeasureChildren: function (a, b) {
        f[a] = b
    }, registerAdditionalMeasureFunction: function (a, b) {
        g[a] = b
    }, maps: {classBasedMeasureChildren: f, classBasedCustomMeasures: d, classBasedPatchers: c}}
}), define("layout/util/layout", ["zepto", "lodash", "layout/util/anchors", "utils", "layout/util/singleCompLayout"], function (a, b, c, d, e) {
    "use strict";
    function h(a, b, c) {
        return a && Math.max(b, Math.min(c, a))
    }

    function i(a, d) {
        return a && !b.contains(a, d.id) && !c.HARD_WIRED_COMPS[d.id] && "mobile.core.components.Page" !== d.type
    }

    function j(a, b, c) {
        var e = {dataItem: null, propertiesItem: null, layout: a.layout, styleItem: null, id: a.id, type: a.componentType, structure: a};
        return e.layout && (e.layout.height = h(e.layout.height, d.siteConstants.COMP_SIZE.MIN_HEIGHT, d.siteConstants.COMP_SIZE.MAX_HEIGHT), e.layout.width = h(e.layout.width, d.siteConstants.COMP_SIZE.MIN_WIDTH, d.siteConstants.COMP_SIZE.MAX_WIDTH)), a.dataQuery && (e.dataItem = b.getDataByQuery(a.dataQuery, c)), a.propertyQuery && (e.propertiesItem = b.getDataByQuery(a.propertyQuery, c, b.dataTypes.PROPERTIES)), a.styleId && (e.styleItem = b.getDataByQuery(a.styleId, c, b.dataTypes.THEME)), e
    }

    function k(a, c, d, f, g, h) {
        var k = [];
        return b.each(b.keys(c), function (b) {
            var l = j(c[b], f, g);
            if (!i(h, l)) {
                var m = e.patchComponent(l, a, d, f);
                m && k.push(b)
            }
        }), k
    }

    function l(a, c, d, e, f, g) {
        var h = [];
        return b.forEach(a, function (a, b) {
            h = h.concat(k(d, e[b], c, f, a.pageId, g))
        }), h
    }

    function m(a, b, c, d, e, f, h) {
        g[a.componentType] ? n(a, b, c, d, e, f, h) : o(a, b, c, d, e, f, h)
    }

    function n(a, b, c, d, e, f, g) {
        var h = j(a, e, f);
        p(a, b, d), q(a, e, b, c, d, f, g), r(h, b, c, d, e, a, g)
    }

    function o(a, b, c, d, e, f, g) {
        var h = j(a, e, f);
        p(a, b, d), r(h, b, c, d, e, a, g), q(a, e, b, c, d, f, g)
    }

    function p(a, b, c) {
        var d = b(a.id);
        d && (c[a.id] = d)
    }

    function q(a, c, e, f, g, h, i) {
        var j = d.dataUtils.getChildrenData(a, c);
        b.each(j, function (a) {
            m(a, e, f, g, c, h, i)
        })
    }

    function r(a, b, c, d, f, g, h) {
        i(h, a) || (e.measureComponentChildren(a, b, c, d, f), e.measureComponent(g, a, b, c, d, f))
    }

    function s(a, c, d, e, f) {
        c.clientWidth = e.getBodyClientWidth(), c.width.screen = e.isMobileView() ? 320 : c.clientWidth, c.height.screen = document.documentElement.clientHeight, c.innerHeight.screen = window.innerHeight, c.innerWidth.screen = window.innerWidth, b.forOwn(a, function (a) {
            m(a.structure, a.getDomNodeFunc, c, d, e, a.pageId, f)
        })
    }

    function t(a, d, e, f, g) {
        var h = {};
        return h.inner = c.enforceAnchors(a.inner.structure, d, e, f, g), d.height.SITE_PAGES && (d.height.SITE_PAGES = d.height[a.inner.pageId]), b.forOwn(a, function (a, b) {
            "inner" !== b && (h[b] = c.enforceAnchors(a.structure, d, e, f, g))
        }), h
    }

    function u(a, c, e, f, g) {
        a.id && (f += c.top[a.id], g += a.layout && a.layout.x ? a.layout.x : 0, c.absoluteTop[a.id] = f, c.absoluteLeft[a.id] = g);
        var h = d.dataUtils.getChildrenData(a, e);
        b.each(h, function (a) {
            u(a, c, e, f, g)
        })
    }

    function v(a, c, d) {
        var e = b.sortBy(b.keys(a), function (a) {
            return"inner" === a ? 1 : 0
        });
        b.each(e, function (b) {
            u(a[b].structure, c, d, "inner" === b ? c.absoluteTop.SITE_PAGES : 0, "inner" === b ? c.absoluteLeft.SITE_PAGES : 0)
        })
    }

    function w(a, c, g, h) {
        var i = a.id && c(a.id);
        if (!i || !e.isComponentDead(i)) {
            i && f[a.componentType] && h.push({structure: a, domNode: i});
            var j = d.dataUtils.getChildrenData(a, g);
            b.forEach(j, function (a) {
                w(a, c, g, h)
            })
        }
    }

    function x(a, c, d, e) {
        var g = [];
        return b.forEach(a, function (a) {
            var b = a.structure, h = f[b.componentType].measure(b, a.domNode, c, d, e);
            h && g.push(a)
        }), b.forEach(a, function (a) {
            var b = a.structure;
            f[b.componentType].patch(b, c, d, e)
        }), g
    }

    function y(a, c, d, e) {
        var f = [];
        b.forOwn(a, function (a) {
            w(a.structure, a.getDomNodeFunc, e, f)
        });
        for (var g = f, h = 0; g.length && 3 > h;)g = x(g, c, d, e), h++
    }

    function A(c) {
        z = z || a("#SITE_ROOT");
        var d = z.offset() || {top: 0};
        return{height: {}, width: {}, innerWidth: {}, innerHeight: {}, custom: {}, containerHeightMargin: {}, minHeight: {}, minWidth: {}, top: {}, left: {}, absoluteTop: {}, absoluteLeft: {}, isDeadComp: {}, siteMarginBottom: (b.parseInt(z.css("padding-bottom"), 10) || 0) - c.onlyForEyesBottomAdditionalMargin(), siteOffsetTop: d.top, clientWidth: 0}
    }

    function B(a) {
        return a && (a.top = {}), a
    }

    function C(a, b, c, d, e) {
        delete a.undefined;
        var f = {}, g = e && d && c;
        e = B(e) || A(b), y(a, e, f, b), s(a, e, f, b, g && d);
        var h = t(a, e, b, c, d), i = l(a, e, f, h, b, g && d);
        return v(a, e, b), b.measureMap = e, i
    }

    var z, f = {}, g = {};
    return{registerLayoutInnerCompsFirst: function (a, b, c) {
        f[a] = {measure: b, patch: c}
    }, registerMeasureChildrenFirst: function (a, b) {
        g[a] = b
    }, registerPatcher: function (a, b) {
        e.registerPatcher(a, b)
    }, registerPatchers: function (a, b) {
        e.registerPatchers(a, b)
    }, registerCustomMeasure: function (a, b) {
        e.registerCustomMeasure(a, b)
    }, registerAdditionalMeasureFunction: function (a, b) {
        e.registerAdditionalMeasureFunction(a, b)
    }, registerRequestToMeasureDom: function (a) {
        e.registerRequestToMeasureDom(a)
    }, registerRequestToMeasureChildren: function (a, b) {
        e.registerRequestToMeasureChildren(a, b)
    }, reLayout: C}
}), define("layout/specificComponents/wixHomepageMenuLayout", ["layout/util/layout"], function (a) {
    "use strict";
    a.registerRequestToMeasureChildren("wysiwyg.viewer.components.wixhomepage.WixHomepageMenu", [
        ["buttonsContainer"]
    ]), a.registerCustomMeasure("wysiwyg.viewer.components.wixhomepage.WixHomepageMenu", function (a, b) {
        var c = b.height[a + "buttonsContainer"];
        b.height[a] = Math.max(b.height[a], c), b.minHeight[a] = c
    })
}), define("layout/specificComponents/fiveGridLineLayout", ["zepto", "layout/util/layout"], function (a, b) {
    "use strict";
    var c = function (a, b, c, d, e) {
        var f = b[a], g = c.width.screen, h = e.getSiteWidth(), i = 0, j = h, k = d.propertiesItem;
        k && k.fullScreenModeOn && (g > h && (i = -(g - h) / 2, j = g), f.style.setProperty("width", j + "px"), f.style.setProperty("left", i + "px"), c.width[a] = j, c.left[a] = i)
    };
    b.registerPatcher("wysiwyg.viewer.components.FiveGridLine", c), b.registerRequestToMeasureChildren("wysiwyg.viewer.components.FiveGridLine", [
        ["line"]
    ]), b.registerRequestToMeasureDom("wysiwyg.viewer.components.FiveGridLine"), b.registerCustomMeasure("wysiwyg.viewer.components.FiveGridLine", function (b, c, d) {
        var e = c.height[b + "line"], f = window.getComputedStyle(a(d[b])[0]), g = parseFloat(f.borderBottomWidth);
        c.height[b] = Math.max(5, g, e), c.minHeight[b] = e
    })
}), define("layout/specificComponents/domainSearchLayout", ["zepto", "lodash", "layout/util/layout"], function (a, b, c) {
    "use strict";
    function d(a, b) {
        var c = a + "content";
        [
            ["width", "minWidth"],
            ["height", "minHeight"]
        ].forEach(function (d) {
            var e = d[0], f = d[1];
            b[e][c] > b[e][a] && (b[f] || (b[f] = {}), b[e][a] = b[f][a] = b[e][c])
        })
    }

    return c.registerRequestToMeasureChildren("wysiwyg.common.components.domainsearchbar.viewer.DomainSearchBar", [
        ["content"]
    ]), c.registerCustomMeasure("wysiwyg.common.components.domainsearchbar.viewer.DomainSearchBar", d), {}
}), define("layout/specificComponents/registerToMeasureOnly", ["layout/util/layout"], function (a) {
    "use strict";
    return a.registerRequestToMeasureDom("wysiwyg.common.components.pinterestpinit.viewer.PinterestPinIt"), a.registerRequestToMeasureDom("wysiwyg.common.components.spotifyfollow.viewer.SpotifyFollow"), a.registerRequestToMeasureDom("wysiwyg.common.components.spotifyplayer.viewer.SpotifyPlayer"), a.registerRequestToMeasureDom("wysiwyg.viewer.components.Video"), a.registerRequestToMeasureDom("wysiwyg.viewer.components.WTwitterTweet"), a.registerRequestToMeasureChildren("wysiwyg.viewer.components.PaginatedGridGallery", [
        ["itemsContainer"]
    ]), {}
}), define("layout/specificComponents/registerToPureDomMeasure", ["zepto", "layout/util/layout"], function (a, b) {
    "use strict";
    function c(a) {
        b.registerCustomMeasure(a, function (a, b, c) {
            var d = c[a];
            b.height[a] = d.offsetHeight, b.width[a] = d.offsetWidth
        })
    }

    return c("wysiwyg.viewer.components.FlashComponent"), c("wysiwyg.common.components.pinterestpinit.viewer.PinterestPinIt"), c("wysiwyg.viewer.components.WGooglePlusOne"), c("wysiwyg.viewer.components.LinkBar"), c("wysiwyg.common.components.singleaudioplayer.viewer.SingleAudioPlayer"), c("wysiwyg.common.components.facebooklikebox.viewer.FacebookLikeBox"), c("wysiwyg.viewer.components.PayPalButton"), c("wysiwyg.common.components.spotifyfollow.viewer.SpotifyFollow"), c("wysiwyg.viewer.components.WTwitterFollow"), c("wysiwyg.common.components.skypecallbutton.viewer.SkypeCallButton"), {}
}), define("layout/specificComponents/PinItPinWidgetLayout", ["layout/util/layout"], function (a) {
    "use strict";
    return a.registerCustomMeasure("wysiwyg.common.components.pinitpinwidget.viewer.PinItPinWidget", function (a, b, c) {
        b.height[a] = c[a].offsetHeight, b.width[a] = c[a].offsetWidth
    }), {}
}), define("layout/specificComponents/areaTooltipLayout", ["layout/util/layout"], function (a) {
    "use strict";
    a.registerCustomMeasure("wysiwyg.common.components.areatooltip.viewer.AreaTooltip", function (a, b) {
        var c = a + "content";
        b.custom[a] = b.height[a] / 2 - b.height[c] / 2
    }), a.registerPatcher("wysiwyg.common.components.areatooltip.viewer.AreaTooltip", function (a, b, c, d) {
        var e, f, g, h, i;
        if (f = a + "tooltip", c.height[f]) {
            e = a + "content";
            var j = d.propertiesItem.tooltipPosition;
            switch (j) {
                case"top":
                    i = -c.height[e] - 14 + "px", h = 0;
                    break;
                case"right":
                    i = c.custom[a] + "px", h = c.width[a] + 14 + "px";
                    break;
                case"bottom":
                    i = c.height[a] + 14 + "px", h = 0;
                    break;
                case"left":
                    i = c.custom[a] + "px", h = "-414px";
                    break;
                default:
                    return
            }
            g = b[f].style, g.top = i, g.left = h
        }
    }), a.registerRequestToMeasureChildren("wysiwyg.common.components.areatooltip.viewer.AreaTooltip", [
        ["tooltip"],
        ["content"]
    ])
}), define("layout/specificComponents/menuLayout", ["zepto", "lodash", "layout/util/layout", "utils"], function (a, b, c, d) {
    "use strict";
    function f(a, b, c) {
        var e = b[c];
        return d.style.unitize(a - parseInt(e.getAttribute("data-menuborder-y"), 10) - parseInt(e.getAttribute("data-label-pad"), 10) - parseInt(e.getAttribute("data-ribbon-els"), 10) - parseInt(e.getAttribute("data-menubtn-border"), 10) - parseInt(e.getAttribute("data-ribbon-extra"), 10))
    }

    function g(b, c, d, e, g, h, i) {
        for (var j = 0, k = null, l = 0; l < d.length; l++) {
            var m = e[l], n = m > 0, o = b + d[l], p = c[o];
            n ? (j++, k = p, a(p).css({width: m + "px", height: g - h.height + "px", position: "relative", "box-sizing": "border-box", overflow: "visible"}), a(a(p).find("p")).css({"line-height": f(g, c, b)})) : a(p).css({height: "0px", overflow: "hidden", position: "absolute"})
        }
        1 === j && (a(i).attr("data-listposition", "lonely"), a(k).attr("data-listposition", "lonely"))
    }

    function h(c, d, f, h, i) {
        var j = f.height[c], k = d[c], m = e.nonHiddenPageIdsFromMainMenu(i), n = m.concat("__more__"), o = d[c + "moreContainer"];
        l(d[c], f.custom[c + "needTopOpenMenuUp"]);
        var p = f.custom[c + "menuItemContainerExtraPixels"], q = f.custom[c + "realWidths"];
        f.custom.originalGapBetweenTextAndBtn && b.map(n, function (b) {
            var e = a(d[c + b]);
            e.attr("data-originalGapBetweenTextAndBtn") || e.attr("data-originalGapBetweenTextAndBtn", f.custom.originalGapBetweenTextAndBtn[c + b + "label"])
        }), a(d[c]).css({height: j}), a(d[c + "itemsContainer"]).css({height: j - parseInt(k.getAttribute("data-menuborder-y"), 10) - parseInt(k.getAttribute("data-ribbon-extra"), 10) - parseInt(k.getAttribute("data-ribbon-els"), 10) + "px"}), g(c, d, n, q, j, p, o)
    }

    function i(b) {
        var c = b.lastChild, d = parseInt(a(c).css("margin-left"), 10) || 0, e = parseInt(a(c).css("margin-right"), 10) || 0;
        return d + e
    }

    function j(a) {
        var b = parseFloat(a);
        return isFinite(b) ? b : 0
    }

    function k(b, c) {
        var d = a(b).css(["border-top-width", "border-bottom-width", "border-left-width", "border-right-width", "padding-top", "padding-bottom", "padding-left", "padding-right", "margin-top", "margin-bottom", "margin-left", "margin-right"]), e = j(d["border-top-width"]) + j(d["padding-top"]), f = j(d["border-bottom-width"]) + j(d["padding-bottom"]), g = j(d["border-left-width"]) + j(d["padding-left"]), h = j(d["border-right-width"]) + j(d["padding-right"]);
        return c && (e += j(d["margin-top"]), f += j(d["margin-bottom"]), g += j(d["margin-left"]), h += j(d["margin-right"])), {top: e, bottom: f, left: g, right: h, height: e + f, width: g + h}
    }

    function l(b, c) {
        c ? a(b).attr("data-dropmode", "dropUp") : a(b).attr("data-dropmode", "dropDown")
    }

    function m(b) {
        for (var c = 0, d = a(b)[0]; d;)c += d.offsetTop, d = d.offsetParent;
        var e = c - (window.pageYOffset || document.body.scrollTop);
        return e > window.innerHeight / 2
    }

    function n(a) {
        var c = e.nonHiddenPageIdsFromMainMenu(a).concat("__more__"), d = [
            []
        ], f = [
            ["moreContainer"],
            ["itemsContainer"]
        ];
        return b.each(d, function (a) {
            f = f.concat(b.map(c, function (b) {
                return[b].concat(a)
            }))
        }), f
    }

    function o(c, d, f, g) {
        var h = e.nonHiddenPageIdsFromMainMenu(f), i = h.concat("__more__");
        d.custom.labelWidths || (d.custom.labelWidths = {}), b.map(i, function (b) {
            var e = a(g[c + b]), f = e.find("p")[0], h = getComputedStyle(f);
            d.custom.labelWidths[c + b + "label"] = parseInt(h.width, 10) + parseInt(h.paddingLeft, 10) + parseInt(h.paddingRight, 10)
        })
    }

    function p(c, d, f, g, h) {
        var i = d.width[c], j = e.nonHiddenPageIdsFromMainMenu(f), k = j.concat("__more__"), l = b.map(k, function (b) {
            var f, e = a(h[c + b]);
            return e.attr("data-originalGapBetweenTextAndBtn") ? f = parseInt(e.attr("data-originalGapBetweenTextAndBtn"), 10) : (d.custom.originalGapBetweenTextAndBtn || (d.custom.originalGapBetweenTextAndBtn = {}), d.custom.originalGapBetweenTextAndBtn[c + b + "label"] = d.width[c + b] - d.custom.labelWidths[c + b + "label"], f = d.width[c + b] - d.custom.labelWidths[c + b + "label"]), d.width[c + b] > 0 ? d.custom.labelWidths[c + b + "label"] + f : 0
        }), m = l.pop(), n = g.sameWidthButtons, o = g.stretchButtonsToMenuWidth, p = !1, q = d.custom[c + "menuItemContainerMargins"], r = d.custom[c + "menuItemMarginForAllChildren"], s = d.custom[c + "menuItemContainerExtraPixels"], t = e.getMaxWidth(l), u = e.getDropDownWidthIfOk(i, n, o, l, q, t, r, s);
        if (!u) {
            for (var v = 1; v <= l.length; v++)if (u = e.getDropDownWidthIfOk(i, n, o, l.slice(0, -1 * v).concat(m), q, t, r, s)) {
                p = !0;
                break
            }
            u || (p = !0, u = [m])
        }
        if (p) {
            var w = u[u.length - 1];
            for (u = u.slice(0, -1); u.length < k.length;)u.push(0);
            u[u.length - 1] = w
        }
        return{realWidths: u, moreShown: p}
    }

    function q(b, c, d, e, f) {
        var g = a("#" + b + "itemsContainer")[0], h = d[b + "moreContainer"];
        c.custom[b + "menuItemContainerMargins"] = i(g), c.custom[b + "menuItemContainerExtraPixels"] = k(g, !0), c.custom[b + "needTopOpenMenuUp"] = m(d[b]), c.custom[b + "menuItemMarginForAllChildren"] = !f.propertiesItem.stretchButtonsToMenuWidth || "false" !== g.getAttribute("data-marginAllChildren"), o(b, c, e, d);
        var j = p(b, c, e, f.propertiesItem, d);
        c.custom[b + "realWidths"] = j.realWidths, c.custom[b + "isMoreShown"] = j.moreShown, c.custom[b + "moreContainerBorderLeft"] = parseInt(a(h).css("border-left"), 10)
    }

    var e = d.menuUtils;
    return c.registerPatcher("wysiwyg.viewer.components.menus.DropDownMenu", h), c.registerCustomMeasure("wysiwyg.viewer.components.menus.DropDownMenu", q), c.registerRequestToMeasureChildren("wysiwyg.viewer.components.menus.DropDownMenu", n), {}
}), define("layout/specificComponents/imageLayout", ["zepto", "lodash", "utils", "layout/util/layout"], function (a, b, c, d) {
    "use strict";
    function i(a, b, c, d, g, h) {
        var i = {width: c.width, height: c.height, id: c.uri}, j = {width: d.width, height: d.height, htmlTag: "img", alignment: f.alignTypes.CENTER, pixelAspectRatio: h ? g.mobile.getDevicePixelRatio() : 1}, k = f.getData(c.displayMode, i, j, null, g.browser);
        a.css(k.css.img);
        var l = g.getMediaFullStaticUrl(k.uri) + k.uri;
        e.isExternalUrl(k.uri) || l === b || a.attr("src", l)
    }

    function j(a, c) {
        var d = ["full", "fitHeight", "fitWidth"];
        return!a.getBrowser().ie || !b.contains(d, c.displayMode)
    }

    function k(c, d, f, k, l, m, n) {
        var o = a(d[c]), p = a(d[c + g]), q = f.custom[c] && f.custom[c].src;
        o.css("width", m.width), o.css("height", m.height), e.isUrlEmptyOrNone(l.uri) || (k.imageResizeHandlers = k.imageResizeHandlers || {}, k.imageResizeHandlers[c] || (k.imageResizeHandlers[c] = b.debounce(i, h, {trailing: !0})), j(k, l) && p.css({height: m.height, width: m.width}), k.imageResizeHandlers[c](p, q, l, m, k, n))
    }

    var e = c.urlUtils, f = c.imageTransform, g = "image", h = 100;
    return d.registerCustomMeasure("core.components.Image", function (b, c, d) {
        var e = a(d[b]).children("img")[0];
        d[b + g] = e, c.custom[b] = {src: e.src}
    }), {patchNodeImage: k}
}), define("layout/specificComponents/sliderGalleryLayout", ["layout/util/layout", "utils", "zepto", "lodash", "layout/specificComponents/imageLayout"], function (a, b, c, d, e) {
    "use strict";
    function h(a, b, c, d, e) {
        var g = Math.floor(b + parseInt(a.attr("data-additional-height"), 10));
        return f.getSizeAfterScaling({itemHeight: g, itemWidth: Math.floor(g * (e ? e.aspectRatio : 1)), displayerData: c, imageMode: e ? e.imageMode : "clipImage", heightDiff: parseInt(d.attr("data-height-diff"), 10) || 0, widthDiff: parseInt(d.attr("data-width-diff"), 10) || 0, bottomGap: parseInt(d.attr("data-bottom-gap"), 10) || 0})
    }

    function i(a, b, e) {
        var f = e[a + "images"], g = c(f).children();
        b.custom[a + "images"] = g.length, d.forEach(g, function (d, e) {
            var f = a + e, g = c(d), h = {width: g.data("displayer-width"), height: g.data("displayer-height"), uri: g.data("displayer-uri")};
            b.custom[f] = {node: d, data: h}
        })
    }

    function j(a, b, d, f, i) {
        var j = d.height[a], k = d.width[a], l = i.isMobileDevice() || i.isMobileView(), m = c(b[a]), n = d.custom[a + "images"];
        c(b[a]).css({height: j, width: k});
        for (var o = 0; n > o; o++) {
            var p = a + o, q = c(d.custom[p].node), r = d.custom[p].data, s = h(m, j, r, q, f.propertiesItem);
            g.updateImageWrapperSizes(c(q.children()[0]), s), e.patchNodeImage(a + q.find("img").parent()[0].id, b, d, i, g.getImageDataForLayout(r), g.getContainerSize(s.imageWrapperSize, c(q)), l)
        }
    }

    function k(a, b) {
        var e = [
            ["images"],
            ["itemsContainer"]
        ], f = c("#" + b + "itemsContainer").children().children();
        return d.each(f, function (a) {
            var b = {pathArray: [a.id, "image"], type: "core.components.Image"};
            e.push(b)
        }), e
    }

    var f = b.matrixScalingCalculations, g = b.galleriesCommonLayout;
    return a.registerRequestToMeasureChildren("wysiwyg.viewer.components.SliderGallery", k), a.registerCustomMeasure("wysiwyg.viewer.components.SliderGallery", i), a.registerPatcher("wysiwyg.viewer.components.SliderGallery", j), {}
}), define("layout/specificComponents/verticalMenuLayout", ["zepto", "lodash", "layout/util/layout", "utils", "skins"], function (a, b, c, d, e) {
    "use strict";
    function f(a, b) {
        var c = a[b].dataset;
        return{border: c.paramBorder ? parseInt(c.paramBorder, 10) : 0, separator: c.paramSeparator ? parseInt(c.paramSeparator, 10) : 0}
    }

    function g(b, c, d, e) {
        var f = {"line-height": c + "px"};
        e.offsetWidth > b && (f["text-overflow"] = "ellipsis"), a(e).css(f)
    }

    function h(c, h, i, j, k) {
        var l = a(h[c]), m = b.filter(i.custom, function (a, b) {
            return d.stringUtils.startsWith(b, c)
        }), n = d.menuUtils.getSiteMenu(k), o = f(h, c), p = e.params.getSkinExports(j.styleItem.skin), q = d.verticalMenuCalculations.getVisibleItemsCount(n), r = Math.max(i.minHeight[c], j.layout.height), s = d.verticalMenuCalculations.getItemHeight(r, o.separator, q, p), t = d.verticalMenuCalculations.getLineHeight(s, o.separator, o.border, p), u = b.max(m);
        l.css({width: i.width[c]}), l.find("a").each(g.bind(null, u, t)), l.find("li").css({height: s + "px"})
    }

    function i(c, d, e) {
        var f = e[c], g = a(f), h = g.find("a.level0"), i = 0, j = 0;
        b.forEach(h, function (a, b) {
            a.offsetWidth > i && (i = a.offsetWidth), d.custom[c + "_" + b] = a.offsetWidth, j += a.offsetHeight
        });
        var k = parseInt(f.getAttribute("data-param-border") || 0, 10), l = parseInt(f.getAttribute("data-param-padding") || 0, 10), m = i + 2 * l + 2 * k;
        m > d.width[c] && (d.width[c] = m), d.minHeight[c] = j
    }

    return c.registerCustomMeasure("wysiwyg.common.components.verticalmenu.viewer.VerticalMenu", i), c.registerPatcher("wysiwyg.common.components.verticalmenu.viewer.VerticalMenu", h), {}
}), define("layout/specificComponents/loginButtonLayout", ["zepto", "lodash", "layout/util/layout"], function (a, b, c) {
    "use strict";
    function d(a, b) {
        var c = a + "container";
        b.minHeight[a] = b.height[c], b.height[c] > b.height[a] && (b.height[a] = b.height[c])
    }

    function e(b, c, d) {
        var e = b + "container", f = c[e], g = d.height[b], h = d.height[e], i = (g - h) / 2;
        a(f).css({"margin-top": i + "px"})
    }

    c.registerRequestToMeasureChildren("wysiwyg.viewer.components.LoginButton", [
        ["container"]
    ]), c.registerCustomMeasure("wysiwyg.viewer.components.LoginButton", d), c.registerPatcher("wysiwyg.viewer.components.LoginButton", e)
}), define("layout/specificComponents/dialogLayout", ["zepto", "lodash", "layout/util/layout"], function (a, b, c) {
    "use strict";
    function d(b, c, d) {
        var e = b + "dialog", f = d.height.screen, g = d.height[e], h = c[e], i = c[b];
        a(i).css({height: f + "px"}), f - g > 0 && a(h).css({top: (f - g) / 2 + "px"})
    }

    c.registerRequestToMeasureChildren("wysiwyg.viewer.components.dialogs.EnterPasswordDialog", [
        ["dialog"]
    ]), c.registerPatcher("wysiwyg.viewer.components.dialogs.EnterPasswordDialog", d), c.registerRequestToMeasureChildren("wysiwyg.viewer.components.dialogs.siteMemberDialogs.MemberLoginDialog", [
        ["dialog"]
    ]), c.registerPatcher("wysiwyg.viewer.components.dialogs.siteMemberDialogs.MemberLoginDialog", d), c.registerRequestToMeasureChildren("wysiwyg.viewer.components.dialogs.siteMemberDialogs.SignUpDialog", [
        ["dialog"]
    ]), c.registerPatcher("wysiwyg.viewer.components.dialogs.siteMemberDialogs.SignUpDialog", d), c.registerRequestToMeasureChildren("wysiwyg.viewer.components.dialogs.siteMemberDialogs.RequestPasswordResetDialog", [
        ["dialog"]
    ]), c.registerPatcher("wysiwyg.viewer.components.dialogs.siteMemberDialogs.RequestPasswordResetDialog", d), c.registerRequestToMeasureChildren("wysiwyg.viewer.components.dialogs.siteMemberDialogs.ResetPasswordDialog", [
        ["dialog"]
    ]), c.registerPatcher("wysiwyg.viewer.components.dialogs.siteMemberDialogs.ResetPasswordDialog", d), c.registerRequestToMeasureChildren("wysiwyg.viewer.components.dialogs.NotificationDialog", [
        ["dialog"]
    ]), c.registerPatcher("wysiwyg.viewer.components.dialogs.NotificationDialog", d), c.registerRequestToMeasureChildren("wysiwyg.viewer.components.dialogs.CreditsDialog", [
        ["dialog"]
    ]), c.registerPatcher("wysiwyg.viewer.components.dialogs.CreditsDialog", d)
}), define("layout/specificComponents/siteBackgroundLayout", ["zepto", "lodash", "layout/util/layout", "utils"], function (a, b, c, d) {
    "use strict";
    function h(a) {
        var c = {};
        return b.some(g, function (d) {
            return c = b.find(a, {quality: d})
        }), c
    }

    function i(b, c, d, e, f) {
        d.top[b] = q(d), d.width[b] = r(d, f), d.height[b] = p(d, f);
        var m, n, g = c[b], h = c.siteBackgroundcurrentImage, i = c.siteBackgroundcurrentVideo;
        a(g).css({top: d.top[b] + "px", height: d.height[b] + "px", width: d.width[b] + "px"}), m = f.getCurrentPageId(), n = j(f, m), a(h).css(l(n, f)), i && a(i).css(k(n, d, f))
    }

    function j(a, b) {
        var c = a.getDataByQuery(b), d = a.isMobileView() ? "mobile" : "desktop", e = c.pageBackgrounds[d].ref, f = a.getDataByQuery(e, b);
        return f
    }

    function k(a, c, d) {
        var e = {position: "relative", minWidth: 0, minHeight: 0, top: 0, left: 0}, f = b.isPlainObject(a.mediaRef) ? a.mediaRef : d.getDataByQuery(a.mediaRef);
        if (!f || "WixVideo" !== f.type || d.isTouchDevice())return e;
        var g = h(f.qualities), i = m(c.width.screen, c.height.screen, g.width, g.height), j = n(i, g.width, g.height), k = o("center", i, j, c.width.screen, c.height.screen);
        return e = {position: "relative", minWidth: j.width, minHeight: j.height, top: k.top, left: k.left}
    }

    function l(a, c) {
        var g = b.isPlainObject(a.mediaRef) ? a.mediaRef : c.getDataByQuery(a.mediaRef), h = {backgroundImage: "", backgroundSize: "", backgroundPosition: "", backgroundRepeat: ""};
        if (g) {
            "WixVideo" === g.type && (g = c.getDataByQuery(g.posterImageRef));
            var i = {id: g.uri, width: g.width, height: g.height}, j = c.isMobileView() ? f : e, k = Math.min(j, i.width), l = Math.min(j, Math.ceil(k / (i.width / i.height))), m = {width: k, height: l, htmlTag: "bg", alignment: a.alignType}, n = d.imageTransform.getData(a.fittingType, i, m, {quality: 85}, c.browser), o = d.urlUtils.joinURL(c.getStaticMediaUrl(), n.uri);
            h = {backgroundImage: "url(" + o + ")", backgroundSize: n.css.container.backgroundSize, backgroundPosition: n.css.container.backgroundPosition, backgroundRepeat: n.css.container.backgroundRepeat}
        }
        return h
    }

    function m(a, b, c, d) {
        return{wScale: a / c, hScale: b / d}
    }

    function n(a, b, c) {
        var d = Math.max(a.wScale, a.hScale), e = Math.round(b * d), f = Math.round(c * d);
        return{width: e, height: f}
    }

    function o(a, b, c, d, e) {
        var f = {};
        switch (a) {
            case"center":
                f.left = b.wScale >= b.hScale ? 0 : Math.floor((d - c.width) / 2), f.top = b.wScale <= b.hScale ? 0 : Math.floor((e - c.height) / 2)
        }
        return f
    }

    function p(a, b) {
        var c = a.height[b.getStructureCompId()], d = a.height.screen;
        return Math.max(d, c)
    }

    function q(a) {
        var b = parseInt(a.height.WIX_ADS, 10);
        b = isNaN(b) ? 0 : b;
        var c = parseInt(a.top.WIX_ADS, 10);
        return c = isNaN(c) ? 0 : c, b + c
    }

    function r(a, b) {
        var c = a.width[b.getStructureCompId()];
        return Math.ceil(Math.max(a.width.screen, c))
    }

    function s(a) {
        return[
            [a.getCurrentPageId()]
        ]
    }

    function t(a, b, c) {
        c.siteBackgroundcurrentImage = document.querySelector(".siteBackgroundcurrentImage"), c.siteBackgroundcurrentVideo = document.querySelector(".siteBackgroundcurrentVideo")
    }

    var e = 1920, f = 1e3, g = ["1080p", "720p", "480p", "360p", "240p", "144p"];
    c.registerRequestToMeasureDom("wysiwyg.viewer.components.SiteBackground"), c.registerRequestToMeasureChildren("wysiwyg.viewer.components.SiteBackground", s), c.registerPatcher("wysiwyg.viewer.components.SiteBackground", i), c.registerCustomMeasure("wysiwyg.viewer.components.SiteBackground", t)
}), define("layout/specificComponents/buttonLayout", ["zepto", "lodash", "layout/util/layout"], function (a, b, c) {
    "use strict";
    function d(a) {
        var b = window.getComputedStyle(a);
        return parseInt(b.paddingTop, 10) + parseInt(b.paddingBottom, 10)
    }

    function e(b, c, e) {
        var f = b + "label", g = window.getComputedStyle(e[b]).minHeight;
        g && parseInt(g, 10) || (g = c.height[f]), c.minHeight[b] = parseInt(g, 10), c.minWidth[b] = c.width[f], c.width[b] = Math.max(c.width[b], c.minWidth[b]), c.height[b] = Math.max(c.height[b], c.minHeight[b]);
        var h = a(e[b]);
        c.custom[b] = {align: h.attr("data-align"),
            margin: parseInt(h.attr("data-margin"), 10)}, c.custom[f] = {verticalPadding: d(e[f])};
        var i = c.width[f] + c.custom[b].margin > c.width[b], j = c.custom[b].align;
        "center" !== j && (c.custom[f]["margin-" + j] = i ? c.width[b] - c.width[f] : c.custom[b].margin)
    }

    function f(a, c, d) {
        var e = {"line-height": d.height[a] - d.custom[c].verticalPadding + "px"};
        return b.reduce(["margin-left", "margin-right"], function (a, e) {
            return b.isUndefined(d.custom[c][e]) || (a[e] = d.custom[c][e]), a
        }, e)
    }

    function g(b, c, d) {
        a(c[b]).css({width: d.width[b], height: d.height[b], "min-height": d.minHeight[b]});
        var e = b + "label", g = f(b, e, d);
        a(c[e]).css(g)
    }

    c.registerRequestToMeasureChildren("wysiwyg.viewer.components.SiteButton", [
        ["label"]
    ]), c.registerCustomMeasure("wysiwyg.viewer.components.SiteButton", e), c.registerPatcher("wysiwyg.viewer.components.SiteButton", g)
}), define("layout/specificComponents/photoLayout", ["zepto", "layout/util/layout", "layout/specificComponents/imageLayout", "utils"], function (a, b, c, d) {
    "use strict";
    function f(a) {
        return a === d.imageUtils.displayModes.FIT_WIDTH ? d.imageUtils.displayModes.FIT_HEIGHT : e[a] || a
    }

    function g(a, b, c) {
        var e = b.height[a] - b.custom[a].marginHeight, f = b.width[a] - b.custom[a].marginWidth, g = {width: f > 0 ? f : b.width[a], height: e > 0 ? e : b.height[a]}, h = b.custom[a].exactHeight - b.custom[a].marginHeight;
        return c === d.imageUtils.displayModes.FIT_HEIGHT && d.imageUtils.isSameValueButRounded(h, g.height) && (g.height = h), g
    }

    function h(b, c, d) {
        var e = a(d[b]), f = parseInt(e.data("content-padding-horizontal"), 10), g = parseInt(e.data("content-padding-vertical"), 10), h = parseFloat(e.data("exact-height"));
        c.height[b] = c.custom[b] && c.custom[b].thisIsMyHeight || d[b].offsetHeight, c.custom[b] = {marginWidth: f, marginHeight: g, exactHeight: h}
    }

    function i(b, e, h, i, j) {
        var k = b + "img", l = b + "link", m = i.dataItem, n = i.propertiesItem, o = n && n.displayMode || "fill", p = f(o), q = d.imageUtils.getContainerSize(g(b, h, p), {width: m.width, height: m.height}, p), r = {width: q.width + h.custom[b].marginWidth, height: q.height + h.custom[b].marginHeight}, s = {width: m.width, height: m.height, displayMode: p, uri: m.uri};
        c.patchNodeImage(k, e, h, j, s, q, j.isMobileDevice());
        var t = a(e[b]);
        t.css("width", r.width), t.css("height", r.height);
        var u = a(e[l]);
        u.css("width", q.width), u.css("height", q.height)
    }

    var e = {fitWidthStrict: d.imageUtils.displayModes.FIT_WIDTH, fitHeightStrict: d.imageUtils.displayModes.FIT_HEIGHT}, j = {pathArray: ["img"], type: "core.components.Image"};
    b.registerRequestToMeasureDom("wysiwyg.viewer.components.WPhoto"), b.registerRequestToMeasureChildren("wysiwyg.viewer.components.WPhoto", [j, ["link"]]), b.registerPatcher("wysiwyg.viewer.components.WPhoto", i), b.registerCustomMeasure("wysiwyg.viewer.components.WPhoto", h), b.registerRequestToMeasureDom("wysiwyg.viewer.components.ClipArt"), b.registerRequestToMeasureChildren("wysiwyg.viewer.components.ClipArt", [j, ["link"]]), b.registerPatcher("wysiwyg.viewer.components.ClipArt", i), b.registerCustomMeasure("wysiwyg.viewer.components.ClipArt", h)
}), define("layout/specificComponents/documentMediaLayout", ["zepto", "lodash", "layout/util/layout", "layout/specificComponents/imageLayout"], function (a, b, c, d) {
    "use strict";
    function e(b, c, e, f, g) {
        var h = f.dataItem, i = {width: h.width, height: h.height, displayMode: "full", uri: h.uri};
        d.patchNodeImage(b + "img", c, e, g, i, e.custom[b].containerSize), a(c[b]).width(e.width[b])
    }

    function f(b, c, d) {
        var e = a(d[b]), f = b + "label", g = c.width[f], h = c.height[f], i = Math.max(c.width[b], g), j = e.data("content-padding-left"), k = e.data("content-padding-right"), l = e.data("content-padding-top"), m = e.data("content-image-height"), n = {width: i - j - k, height: Math.max(m - l - h, 1)};
        c.custom[b] = {containerSize: n}, c.width[b] = i, c.minWidth[b] = g
    }

    c.registerRequestToMeasureChildren("wysiwyg.viewer.components.documentmedia.DocumentMedia", [
        ["label"],
        {pathArray: ["img"], type: "core.components.Image"},
        ["link"]
    ]), c.registerCustomMeasure("wysiwyg.viewer.components.documentmedia.DocumentMedia", f), c.registerPatcher("wysiwyg.viewer.components.documentmedia.DocumentMedia", e)
}), define("layout/util/calculateScreenWidthDimensions", [], function () {
    "use strict";
    function a(a, b) {
        return Math.min(parseInt(Math.floor((a - b) / 2), 10), 0)
    }

    function b(b, c) {
        var d = c.getSiteWidth(), e = Math.max(d, b.width.screen);
        return{width: e, left: a(d, e)}
    }

    return b
}), define("layout/specificComponents/containerAndScreenWidthLayout", ["lodash", "zepto", "layout/util/layout", "utils", "layout/util/calculateScreenWidthDimensions"], function (a, b, c, d, e) {
    "use strict";
    function f(a, b, c) {
        b.height[a] = c[a].offsetHeight, j(a, b, c)
    }

    function g(b, c) {
        return a.map(c, function (a) {
            var c = b[a.id];
            if (!c)return 0;
            var e = d.boundingLayout.getBoundingLayout({x: c.offsetLeft, y: c.offsetTop, width: c.offsetWidth, height: c.offsetHeight, rotationInDegrees: a.layout.rotationInDegrees});
            return e.y + e.height
        })
    }

    function h(b, c, d) {
        var e = c.isMobileView() ? d.structure.mobileComponents : d.structure.components;
        if (0 === e.length)return 0;
        var f = g(b, e);
        return a.max(f)
    }

    function i(a, b, c, d, e) {
        f(a, b, c), b.minHeight[a] = d.getPageMinHeight();
        var g = h(c, d, e) + b.containerHeightMargin[a];
        b.height[a] = Math.max(b.minHeight[a], g)
    }

    function j(a, b, c) {
        var d = b.height[a + "inlineContent"] || 0;
        b.containerHeightMargin[a] = d ? c[a].offsetHeight - d : 0
    }

    function k(a, c, d, e, f) {
        var g = a + "screenWidthBackground";
        b(c[g]).css({width: d.width.screen, left: n(e.layout.width, d.width.screen, f)}), d.left[a] = 0, b(c[a]).css({left: 0})
    }

    function l(a, c) {
        b(c[a]).css({visibility: ""})
    }

    function m(a, c, d, f, g) {
        var h = e(d, g);
        (g.isMobileView() || g.isMobileDevice()) && (h.left = 0, h.width = g.getSiteWidth()), d.width[a] = h.width, d.left[a] = h.left, b(c[a]).css({width: h.width + "px", left: h.left + "px"})
    }

    function n(a, b, c) {
        return c.isMobileView() || c.isMobileDevice() ? 0 : Math.min(parseInt(Math.floor((a - b) / 2), 10), 0) + "px"
    }

    function o(a, c, d, e) {
        var f = e.layout && e.layout.fixedPosition, g = {height: d.height[a], bottom: f ? d.siteMarginBottom : "auto", top: f ? "auto" : d.top[a]};
        b(c[a]).css(g)
    }

    c.registerRequestToMeasureDom("mobile.core.components.Container"), c.registerRequestToMeasureDom("mobile.core.components.Page"), c.registerRequestToMeasureDom("wixapps.integration.components.AppPage"), c.registerRequestToMeasureDom("wysiwyg.viewer.components.FooterContainer"), c.registerRequestToMeasureDom("wysiwyg.viewer.components.HeaderContainer"), c.registerRequestToMeasureDom("wysiwyg.viewer.components.ScreenWidthContainer"), c.registerMeasureChildrenFirst("mobile.core.components.Page", !0), c.registerMeasureChildrenFirst("wixapps.integration.components.AppPage", !0), c.registerRequestToMeasureChildren("mobile.core.components.Container", [
        ["inlineContent"]
    ]), c.registerRequestToMeasureChildren("mobile.core.components.Page", [
        ["inlineContent"]
    ]), c.registerRequestToMeasureChildren("wixapps.integration.components.AppPage", [
        ["inlineContent"]
    ]), c.registerRequestToMeasureChildren("wysiwyg.viewer.components.FooterContainer", [
        ["screenWidthBackground"],
        ["inlineContent"]
    ]), c.registerRequestToMeasureChildren("wysiwyg.viewer.components.PagesContainer", [
        ["screenWidthBackground"],
        ["inlineContent"]
    ]), c.registerRequestToMeasureChildren("wysiwyg.viewer.components.HeaderContainer", [
        ["screenWidthBackground"],
        ["inlineContent"]
    ]), c.registerRequestToMeasureChildren("wysiwyg.viewer.components.ScreenWidthContainer", [
        ["screenWidthBackground"],
        ["inlineContent"]
    ]), c.registerCustomMeasure("mobile.core.components.Container", j), c.registerCustomMeasure("mobile.core.components.Page", i), c.registerCustomMeasure("wixapps.integration.components.AppPage", i), c.registerCustomMeasure("wysiwyg.viewer.components.FooterContainer", j), c.registerCustomMeasure("wysiwyg.viewer.components.PagesContainer", f), c.registerCustomMeasure("wysiwyg.viewer.components.HeaderContainer", j), c.registerCustomMeasure("wysiwyg.viewer.components.ScreenWidthContainer", j), c.registerPatchers("wysiwyg.viewer.components.FooterContainer", [k, o]), c.registerPatcher("wysiwyg.viewer.components.HeaderContainer", k), c.registerPatcher("mobile.core.components.Page", l), c.registerPatcher("wysiwyg.viewer.components.PagesContainer", k), c.registerPatcher("wysiwyg.viewer.components.ScreenWidthContainer", k), c.registerPatcher("wysiwyg.viewer.components.BgImageStrip", m)
}), define("layout/specificComponents/groupLayout", ["lodash", "zepto", "layout/util/layout", "utils"], function (a, b, c, d) {
    "use strict";
    function e(b, c, e, g, h) {
        var i = d.dataUtils.getChildrenData(h.structure, g), j = f(i, c);
        c.left[b] = h.layout.x, i.length > 0 && (c.width[b] = j.right - j.left, c.height[b] = j.bottom - j.top, c.top[b] = h.layout.y + j.top, c.left[b] = h.layout.x + j.left, a.forEach(i, function (a) {
            c.top[a.id] = a.layout.y - j.top, c.left[a.id] = a.layout.x - j.left
        }))
    }

    function f(b, c) {
        var e, f, g, h;
        return e = g = Number.MAX_VALUE, f = h = -Number.MAX_VALUE, a.forEach(b, function (a) {
            var b = a.layout, i = c.left && c.left[a.id] ? c.left[a.id] : b.x, j = c.width && c.width[a.id] ? c.width[a.id] : b.width, k = c.top && c.top[a.id] ? c.top[a.id] : b.y, l = c.height && c.height[a.id] ? c.height[a.id] : b.height, m = d.boundingLayout.getBoundingLayout({x: i, y: k, width: j, height: l, rotationInDegrees: b.rotationInDegrees});
            e = Math.min(e, m.x), f = Math.max(f, m.x + m.width), g = Math.min(g, m.y), h = Math.max(h, m.y + m.height)
        }), {left: e, right: f, top: g, bottom: h}
    }

    function g(c, e, f, g, h) {
        b(e[c]).css({width: f.width[c], height: f.height[c], top: f.top[c], left: f.left[c]});
        var i = d.dataUtils.getChildrenData(g.structure, h);
        a.forEach(i, function (a) {
            b(e[a.id]).css({top: f.top[a.id], left: f.left[a.id]})
        })
    }

    c.registerMeasureChildrenFirst("wysiwyg.viewer.components.Group", !0), c.registerRequestToMeasureDom("wysiwyg.viewer.components.Group"), c.registerCustomMeasure("wysiwyg.viewer.components.Group", e), c.registerPatcher("wysiwyg.viewer.components.Group", g)
}), define("layout/wixappsLayout/proxyLayoutRegistrar", [], function () {
    "use strict";
    var a = {};
    return{registerCustomMeasure: function (b, c) {
        a[b] = c
    }, getProxiesToMeasure: function () {
        return a
    }}
}), define("layout/specificComponents/wixappsLayout", ["zepto", "lodash", "layout/wixappsLayout/proxyLayoutRegistrar", "layout/util/layout", "layout/util/singleCompLayout"], function (a, b, c, d, e) {
    "use strict";
    function g() {
        var c = b.toArray(arguments).join("");
        return a("#" + c)[0]
    }

    function h(a, b, c) {
        return c.custom[a] && c.custom[a].appPartShouldRenderAgain
    }

    function i(a, b, c, d) {
        e.measureComponentChildren(a, g, d, c, b)
    }

    function j(a, b, c, d) {
        var f = a.compId, h = g(f);
        b[f] = h;
        var j = a.structureInfo;
        j.id = f, j.type = a.compType, i(j, d, b, c), e.maps.classBasedCustomMeasures[a.compType] && e.maps.classBasedCustomMeasures[a.compType](f, c, b, d, a.structureInfo)
    }

    function k(a, b, c) {
        var d = a + f;
        "true" === c[a].getAttribute("data-dynamic-height") && "loading" !== c[a].attributes["data-state"].value && (b.height[a] = b.height[d], b.minHeight[a] = b.height[d])
    }

    function l(a, d, e, g, h) {
        var i = a.id, k = i + f, l = d.querySelector("#" + k);
        e.custom[i] = e.custom[i] || {};
        var m = !1;
        return b.each(c.getProxiesToMeasure(), function (a, c) {
            var d = o(c, l);
            e.custom[i][c] = e.custom[i][c] || [], b.map(d, function (b, d) {
                var f = a(b, h, e);
                e.custom[i][c][d] = f, f.comp && j(f.comp, g, e, h), f.needsRelayout && !e.custom[i][c][d].didRelayout && (m = !0, e.custom[i][c][d].didRelayout = !0)
            })
        }), m
    }

    function m(a, d, f, g) {
        var h = a.id;
        d.custom[h].appPartShouldRenderAgain = b.reduce(c.getProxiesToMeasure(), function (a, c, i) {
            var j = d.custom[h][i], k = b.reduce(j, function (a, b) {
                n(b.domManipulations);
                var c = !1, h = b.comp;
                return h && (c = e.patchComponent(h.structureInfo, f, d, g)), a || c
            }, !1);
            return a || k
        }, !1)
    }

    function n(c) {
        b.forEach(c, function (c) {
            var d = a(c.node), e = c.funcName, f = b.isArray(c.params) ? c.params : [c.params];
            b.isFunction(d[e]) && d[e].apply(d, f)
        })
    }

    function o(b, c) {
        return a("[data-proxy-name=" + b + "]", c)
    }

    var f = "inlineContent";
    return d.registerLayoutInnerCompsFirst("wixapps.integration.components.AppPart", l, m), d.registerLayoutInnerCompsFirst("wixapps.integration.components.AppPart2", l, m), d.registerRequestToMeasureChildren("wixapps.integration.components.AppPart", [
        [f]
    ]), d.registerRequestToMeasureDom("wixapps.integration.components.AppPart"), d.registerCustomMeasure("wixapps.integration.components.AppPart", k), d.registerPatcher("wixapps.integration.components.AppPart", h), d.registerRequestToMeasureChildren("wixapps.integration.components.AppPart2", [
        [f]
    ]), d.registerRequestToMeasureDom("wixapps.integration.components.AppPart2"), d.registerCustomMeasure("wixapps.integration.components.AppPart2", k), d.registerPatcher("wixapps.integration.components.AppPart2", h), {appPartMeasureFunction: k, appPartPatcherFunction: h, inlineContentId: f, preMeasureProxies: l, postPatchProxies: m}
}), define("layout/specificComponents/svgShape/svgScalerUtils", [], function () {
    "use strict";
    return{trim: function (a) {
        return a.replace(/^\s+|\s+$/g, "")
    }, round: function (a) {
        return Math.round(1e6 * a) / 1e6
    }}
}), define("layout/specificComponents/svgShape/svgPathParser", ["layout/specificComponents/svgShape/svgScalerUtils"], function (a) {
    "use strict";
    return{getParsedPath: function (b) {
        var c = a.trim(b);
        if ("" === c)return[];
        var d = c.match(/[a-z][^a-z]*/gi), e = d.map(function (b) {
            for (b = b.replace(/-/g, " -").replace(/,/g, " "); -1 !== b.indexOf("  ");)b = b.replace(/ {2}/g, " ");
            return[b.substring(0, 1), a.trim(b.substring(1))]
        });
        return e.map(function (a) {
            var b, c = [];
            return-1 === "QSCL".indexOf(a[0].toUpperCase()) || 1 !== a[1].split(",").length ? a : (b = a[1].split(" "), b.length % 2 !== 0 ? a : (b.forEach(function (a, b, d) {
                b % 2 === 0 && c.push(a + " " + d[b + 1])
            }), [a[0], c.join(",")]))
        })
    }, stringifyParsedPath: function (a) {
        var b = "";
        return a.forEach(function (a) {
            a.forEach(function (a) {
                b += a
            })
        }), b
    }}
}), define("layout/specificComponents/svgShape/svgBasicScaler", ["layout/specificComponents/svgShape/svgScalerUtils"], function (a) {
    "use strict";
    var b = function (b, c) {
        return a.round(parseFloat(b) * c)
    }, c = function (a, c, d) {
        var e = a.split(/[\s,]+/);
        return b(e[0], c) + " " + b(e[1], d)
    }, d = function (b, d, e) {
        if ("" === b)return"";
        var f = b.split(",").map(function (b) {
            return c(a.trim(b), d, e)
        });
        return f.join(",")
    };
    return{scaleSingleValue: b, scalePairString: c, scaleMultiplePairStrings: d}
}), define("layout/specificComponents/svgShape/svgPathScaler", ["lodash", "layout/specificComponents/svgShape/svgPathParser", "layout/specificComponents/svgShape/svgBasicScaler"], function (a, b, c) {
    "use strict";
    var d = function () {
        return""
    }, e = function (a, b, d) {
        return c.scaleSingleValue(a, d / (360 * b))
    }, f = function (a, b, d) {
        var f = a.split(/[\s,]+/);
        return 7 !== f.length ? (console.log("incorrect arc string, should have exactly 7 parameters. (value was " + a), a) : (f[0] = c.scaleSingleValue(f[0], b), f[1] = c.scaleSingleValue(f[1], d), f[2] = e(f[2], b, d), f[5] = c.scaleSingleValue(f[5], b), f[6] = c.scaleSingleValue(f[6], d), f.join(" "))
    }, g = function (c, d, e) {
        var f = b.getParsedPath(c.getAttribute("d"));
        a.forEach(f, function (a) {
            var b = [a[1]], c = a[0].toUpperCase();
            "V" === c ? b.push(e) : "H" === c ? b.push(d) : (b.push(d), b.push(e)), a[1] = h[a[0].toUpperCase()].apply(null, b)
        }), c.setAttribute("d", b.stringifyParsedPath(f))
    }, h = {M: c.scalePairString, L: c.scaleMultiplePairStrings, H: c.scaleSingleValue, V: c.scaleSingleValue, Z: d, C: c.scaleMultiplePairStrings, S: c.scaleMultiplePairStrings, Q: c.scaleMultiplePairStrings, T: c.scalePairString, A: f};
    return{scale: g}
}), define("layout/specificComponents/svgShape/svgPolygonParser", ["layout/specificComponents/svgShape/svgScalerUtils"], function (a) {
    "use strict";
    return{getParsedPoints: function (b) {
        var f, c = a.trim(b), d = c.split(/[\s,]+/), e = [];
        if ("" === c)return[];
        for (f = 0; f < d.length; f += 2)e.push([d[f], d[f + 1]]);
        return e
    }, stringifyPoints: function (b) {
        var c = "";
        return b.forEach(function (a) {
            c += a.join(",") + " "
        }), a.trim(c)
    }}
}), define("layout/specificComponents/svgShape/svgPolygonScaler", ["lodash", "layout/specificComponents/svgShape/svgScalerUtils", "layout/specificComponents/svgShape/svgBasicScaler", "layout/specificComponents/svgShape/svgPolygonParser"], function (a, b, c, d) {
    "use strict";
    return{scale: function (b, e, f) {
        var g = d.getParsedPoints(b.getAttribute("points"));
        a.forEach(g, function (a) {
            a[0] = c.scaleSingleValue(a[0], e), a[1] = c.scaleSingleValue(a[1], f)
        }), b.setAttribute("points", d.stringifyPoints(g))
    }}
}), define("layout/specificComponents/svgShape/svgCircleScaler", ["layout/specificComponents/svgShape/svgBasicScaler"], function (a) {
    "use strict";
    return{scale: function (b, c, d) {
        var e = b.getAttribute("cx"), f = b.getAttribute("cy"), g = b.getAttribute("r"), h = Math.min(c, d);
        e && f && (b.setAttribute("cx", a.scaleSingleValue(e, h)), b.setAttribute("cy", a.scaleSingleValue(f, h)), b.setAttribute("r", a.scaleSingleValue(g, h)))
    }}
}), define("layout/specificComponents/svgShape/svgRectScaler", ["layout/specificComponents/svgShape/svgBasicScaler"], function (a) {
    "use strict";
    var b = function (b, c, d, e) {
        d && b.setAttribute(c, a.scaleSingleValue(d, e))
    };
    return{scale: function (a, c, d) {
        var e = a.getAttribute("width"), f = a.getAttribute("height"), g = a.getAttribute("rx"), h = a.getAttribute("ry"), i = a.getAttribute("x"), j = a.getAttribute("y");
        b(a, "width", e, c), b(a, "height", f, d), b(a, "rx", g, c), b(a, "ry", h, d), b(a, "x", i, c), b(a, "y", j, d)
    }}
}), define("layout/specificComponents/svgShape/svgEllipseScaler", ["layout/specificComponents/svgShape/svgBasicScaler"], function (a) {
    "use strict";
    return{scale: function (b, c, d) {
        var e = b.getAttribute("cx"), f = b.getAttribute("cy"), g = b.getAttribute("rx"), h = b.getAttribute("ry");
        e && g && (b.setAttribute("cx", a.scaleSingleValue(e, c)), b.setAttribute("rx", a.scaleSingleValue(g, c))), f && h && (b.setAttribute("cy", a.scaleSingleValue(f, d)), b.setAttribute("ry", a.scaleSingleValue(h, d)))
    }}
}), define("layout/specificComponents/svgShape/svgPolylineParser", ["lodash", "layout/specificComponents/svgShape/svgScalerUtils"], function (a, b) {
    "use strict";
    return{getParsedPoints: function (a) {
        var f, c = b.trim(a), d = c.split(/[\s,]+/), e = [];
        if ("" === c)return[];
        for (f = 0; f < d.length; f += 2)e.push([d[f], d[f + 1]]);
        return e
    }, stringifyPoints: function (a) {
        var c = "";
        return a.forEach(function (a) {
            c += a.join(",") + " "
        }), b.trim(c)
    }}
}), define("layout/specificComponents/svgShape/svgPolylineScaler", ["lodash", "layout/specificComponents/svgShape/svgScalerUtils", "layout/specificComponents/svgShape/svgBasicScaler", "layout/specificComponents/svgShape/svgPolylineParser"], function (a, b, c, d) {
    "use strict";
    return{scale: function (b, e, f) {
        var g = d.getParsedPoints(b.getAttribute("points"));
        g = a.map(g, function (b) {
            return a.map(b, function (a, b) {
                return c.scaleSingleValue(a, b ? f : e)
            })
        }), b.setAttribute("points", d.stringifyPoints(g))
    }}
}), define("layout/specificComponents/svgShape/svgLineScaler", ["layout/specificComponents/svgShape/svgBasicScaler"], function (a) {
    "use strict";
    return{scale: function (b, c, d) {
        var e = b.getAttribute("x1"), f = b.getAttribute("y1"), g = b.getAttribute("x2"), h = b.getAttribute("y2");
        e && f && g && h && (b.setAttribute("x1", a.scaleSingleValue(e, c)), b.setAttribute("x2", a.scaleSingleValue(g, c)), b.setAttribute("y1", a.scaleSingleValue(f, d)), b.setAttribute("y2", a.scaleSingleValue(h, d)))
    }}
}), define("layout/specificComponents/svgShape/svgScaler", ["lodash", "layout/specificComponents/svgShape/svgScalerUtils", "layout/specificComponents/svgShape/svgPathScaler", "layout/specificComponents/svgShape/svgPolygonScaler", "layout/specificComponents/svgShape/svgCircleScaler", "layout/specificComponents/svgShape/svgRectScaler", "layout/specificComponents/svgShape/svgEllipseScaler", "layout/specificComponents/svgShape/svgPolylineScaler", "layout/specificComponents/svgShape/svgLineScaler"], function (a, b, c, d, e, f, g, h, i) {
    "use strict";
    var j = function (b) {
        return a(["path", "polygon", "rect", "circle", "ellipse", "polyline", "line"]).contains(b.toLowerCase())
    }, k = function (a, c, d, e) {
        var g, h, k, l, f = c.width / c.height, i = f > e, j = e > f;
        return a.width ? d && i ? (k = c.height * e, g = k / a.width) : g = c.width / a.width : g = 1, a.height ? d && j ? (l = c.width / e, h = l / a.height) : h = c.height / a.height : h = 1, {scaleX: b.round(g), scaleY: b.round(h)}
    }, l = function (a, b, c) {
        return Math.abs(a.width - b.width) < c && Math.abs(a.height - b.height) < c
    }, m = function (a, b, c) {
        a.setAttribute("transform", "translate(" + parseFloat(-1 * c.x + .5 * b) + "," + parseFloat(-1 * c.y + .5 * b) + ")")
    }, n = function (a) {
        var b = a.getAttribute("data-original-aspect-ratio");
        return b ? Number(b) : null
    }, o = function (a, b) {
        a.hasAttribute("data-original-aspect-ratio") || (a.setAttribute("data-original-aspect-ratio", b), a.setAttribute("viewBox", ""), a.removeAttribute("viewBox"))
    }, p = function (b, p, q, r, s) {
        var t = b.getElementsByTagName("g")[0], u = a.pick(q, ["width", "height"]), v = u.width / u.height, w = a.pick(q, ["x", "y"]), x = {path: c, polygon: d, rect: f, circle: e, ellipse: g, polyline: h, line: i}, y = n(b);
        if (y || (y = q.width / q.height, o(b, y)), !l(p, u, 1) || s && v !== y) {
            var z = k(u, p, s, y);
            a.filter(t.childNodes,function (a) {
                return j(a.nodeName)
            }).forEach(function (a) {
                var b = a.nodeName.toLowerCase();
                x[b].scale(a, z.scaleX, z.scaleY)
            }), m(t, r, {x: w.x * z.scaleX, y: w.y * z.scaleY}), b.style.width = Math.ceil(u.width * z.scaleX + r) + "px", b.style.height = Math.ceil(u.height * z.scaleY + r) + "px"
        }
    };
    return{scale: p}
}), define("layout/specificComponents/shapeLayout", ["lodash", "layout/util/layout", "layout/specificComponents/svgShape/svgScaler"], function (a, b, c) {
    "use strict";
    function d(a, b, c) {
        var d = c.width / c.height;
        a.shapesOriginalAspectRatio = a.shapesOriginalAspectRatio || {}, a.shapesOriginalAspectRatio[b] = a.shapesOriginalAspectRatio[b] || d
    }

    function e(a, b, c, e) {
        var f = c[a];
        f && f.getElementsByTagName || (c[a] = f = document.createElementNS("http://www.w3.org/2000/svg", "svg"));
        var g = f.getElementsByTagName("svg")[0];
        if (g) {
            var j, h = Array.prototype.slice.call(g.childNodes), i = h.filter(function (a) {
                return"g" === a.tagName
            });
            1 === i.length ? j = i[0] : (j = document.createElementNS("http://www.w3.org/2000/svg", "g"), h.forEach(function (a) {
                j.appendChild(a)
            }), g.appendChild(j));
            var k = j.getBBox();
            b.custom[a] = k, d(e, a, k)
        }
    }

    function f(a, b, d, e) {
        var f = b[a].getElementsByTagName("svg")[0];
        if (f) {
            var g = e.propertiesItem, h = g && g.maintainAspectRatio, i = e.styleItem, j = parseInt(i && i.style ? i.style.properties.strokewidth || 1 : 1, 10), k = {width: d.width[a] - j, height: d.height[a] - j};
            c.scale(f, k, d.custom[a], j, h)
        }
    }

    return b.registerCustomMeasure("wysiwyg.viewer.components.svgshape.SvgShape", e), b.registerPatcher("wysiwyg.viewer.components.svgshape.SvgShape", f), {}
}), define("layout/specificComponents/richtextLayout", ["zepto", "layout/util/layout"], function (a, b) {
    "use strict";
    function d(b, c) {
        a(c[b]).css({height: ""})
    }

    var c = "wysiwyg.viewer.components.WRichText";
    return b.registerPatcher(c, d), b.registerCustomMeasure(c, function (a, b, c) {
        var d = c[a];
        b.minHeight[a] = d.offsetHeight
    }), b.registerRequestToMeasureDom(c), {}
}), define("layout/specificComponents/matrixGalleryLayout", ["layout/util/layout", "zepto", "utils", "lodash", "layout/specificComponents/imageLayout"], function (a, b, c, d, e) {
    "use strict";
    function k(a, b, c, d, e, f) {
        return h.getSizeAfterScaling({itemHeight: b, itemWidth: c, displayerData: a, imageMode: d.imageMode, heightDiff: parseInt(f.attr("data-height-diff"), 10) || 0, widthDiff: parseInt(f.attr("data-width-diff"), 10) || 0, bottomGap: parseInt(f.attr("data-bottom-gap"), 10) || 0})
    }

    function l(a, b, c) {
        g.measureFlexibleHeightGallery(a, b, c), b.width[a] = Math.max(j, b.width[a]), b.height[a] = Math.max(i, b.height[a])
    }

    function m(a, c, d, h, i) {
        var j = h.propertiesItem, l = d.height[a], m = d.width[a], n = i.isMobileDevice() || i.isMobileView(), o = b(c[a + "itemsContainer"]), p = parseInt(b(c[a]).attr("data-presented-row"), 10), q = d.custom[a + "itemsContainer"], r = f.getItemWidth(j.margin, j.numCols, m, parseInt(b(c[a]).attr("data-width-diff"), 10) || 0), s = f.getItemHeight(j.margin, l, p, parseInt(b(c[a]).attr("data-height-diff"), 10) || 0);
        o.css({height: l, width: m}), b(c[a]).css({height: l, width: m});
        for (var t = 0; q > t; t++) {
            var u = a + t, v = b(d.custom[u].node), w = d.custom[u].data, x = k(w, s, r, j, b(c[a]), v), y = f.getItemPosition(t, r, s, j.margin, j.numCols);
            v.css({height: x.displayerSize.height, width: x.displayerSize.width, left: y.left, top: y.top}), g.updateImageWrapperSizes(b(v.children()[0]), x), e.patchNodeImage(a + v.find("img").parent()[0].id, c, d, i, g.getImageDataForLayout(w), g.getContainerSize(x.imageWrapperSize, v), n)
        }
    }

    function n(a, c) {
        var e = [
            ["itemsContainer"]
        ], f = b("#" + c + "itemsContainer").children();
        return d.each(f, function (a) {
            var b = {pathArray: [a.id, "image"], type: "core.components.Image"};
            e.push(b)
        }), e
    }

    var f = c.matrixCalculations, g = c.galleriesCommonLayout, h = c.matrixScalingCalculations, i = 70, j = 45;
    return a.registerRequestToMeasureDom("wysiwyg.viewer.components.MatrixGallery"), a.registerCustomMeasure("wysiwyg.viewer.components.MatrixGallery", l), a.registerRequestToMeasureChildren("wysiwyg.viewer.components.MatrixGallery", n), a.registerPatcher("wysiwyg.viewer.components.MatrixGallery", m), {}
}), define("layout/specificComponents/slideShowLayout", ["layout/util/layout", "zepto", "utils", "lodash", "layout/specificComponents/imageLayout"], function (a, b, c, d, e) {
    "use strict";
    function h(a, b, c, d, e, g) {
        return f.getSizeAfterScaling({itemHeight: b - (parseInt(e.attr("data-height-diff"), 10) || 0), itemWidth: c - (parseInt(e.attr("data-height-diff"), 10) || 0), displayerData: a, imageMode: d.imageMode, heightDiff: parseInt(g.attr("data-height-diff"), 10) || 0, widthDiff: parseInt(g.attr("data-width-diff"), 10) || 0, bottomGap: parseInt(g.attr("data-bottom-gap"), 10) || 0})
    }

    function i(a, b, c) {
        g.measureFlexibleHeightGallery(a, b, c)
    }

    function j(a, c, d, f, i) {
        var j = f.propertiesItem, k = d.height[a], l = d.width[a], m = i.isMobileDevice() || i.isMobileView(), n = d.custom[a + "itemsContainer"], o = b(c[a + "border"]), p = b(c[a]);
        if ("flexibleHeight" === j.imageMode) {
            var q = a + (n - 1), r = b(d.custom[q].node), s = d.custom[q].data, t = h(s, k, l, j, p, r);
            k = t.displayerSize.height
        }
        p.css({height: k, width: l}), o.css({height: k, width: l});
        for (var u = 0; n > u; u++) {
            var v = a + u, w = b(d.custom[v].node);
            w.css({height: k, width: l});
            var x = d.custom[v].data, y = h(x, k, l, j, b(c[a]), w);
            g.updateImageWrapperSizes(b(w.children()[0]), y);
            var z = w.find("img").parent()[0], A = z ? z.id : "";
            e.patchNodeImage(a + A, c, d, i, g.getImageDataForLayout(x), g.getContainerSize(y.imageWrapperSize, w), m)
        }
    }

    function k(a, c) {
        var e = [
            ["border"],
            ["itemsContainer"]
        ], f = b("#" + c + "itemsContainer").children();
        return d.each(f, function (a) {
            var b = {pathArray: [a.id, "image"], type: "core.components.Image"};
            e.push(b)
        }), e
    }

    var f = c.matrixScalingCalculations, g = c.galleriesCommonLayout;
    return a.registerRequestToMeasureDom("wysiwyg.viewer.components.SlideShowGallery"), a.registerCustomMeasure("wysiwyg.viewer.components.SlideShowGallery", i), a.registerRequestToMeasureChildren("wysiwyg.viewer.components.SlideShowGallery", k), a.registerPatcher("wysiwyg.viewer.components.SlideShowGallery", j), {}
}), define("layout/specificComponents/anchorLayout", ["zepto", "layout/util/layout"], function (a, b) {
    "use strict";
    function c(b, c) {
        var d = c[b];
        a(d).css({width: "0px"})
    }

    b.registerPatcher("wysiwyg.common.components.anchor.viewer.Anchor", c)
}), define("layout/specificComponents/tinyMenuLayout", ["zepto", "layout/util/layout", "utils"], function (a, b, c) {
    "use strict";
    function h(b, c, e, f, h) {
        var k, l, i = h.getSiteWidth(), j = i - 2 * g;
        f.layout.fixedPosition ? (l = "calc(50% + " + (f.layout.x - i / 2) + "px)", k = -f.layout.x + g) : (l = f.layout.x + "px", k = -e.custom[b].menuContainerLeft), a(c[b]).css({left: l}), a(c[b + d]).css({width: j + "px", left: k + "px"})
    }

    function i(b, d, h) {
        var i = a("#SITE_ROOT")[0], j = c.domMeasurements.getBoundingRect(h[b], i);
        d.custom[b] = {menuContainerTop: j.bottom, menuContainerLeft: j.left - g};
        var k = c.domMeasurements.getElementRect(h[b + f], i);
        d.height[b] = d.height[b + e], d.minHeight.SITE_STRUCTURE = Math.max(d.minHeight.SITE_STRUCTURE || 0, k.bottom)
    }

    var d = "menuContainer", e = "menuButton", f = "menuItems", g = 20;
    b.registerRequestToMeasureChildren("wysiwyg.viewer.components.mobile.TinyMenu", [
        [d],
        [e],
        [f]
    ]), b.registerCustomMeasure("wysiwyg.viewer.components.mobile.TinyMenu", i), b.registerPatcher("wysiwyg.viewer.components.mobile.TinyMenu", h)
}), define("layout/specificComponents/imageZoomLayout", ["lodash", "zepto", "layout/util/layout", "layout/specificComponents/imageLayout", "utils"], function (a, b, c, d, e) {
    "use strict";
    function h(a, c, e, g, h) {
        var i = h.currentPageInfo.pageItemId, j = h.isMobileDevice() || h.isMobileView(), k = h.getDataByQuery(i), l = {width: k.width, height: k.height, displayMode: f.displayModes.CENTER, uri: k.uri}, m = e.custom[a], n = {width: m.imageContainerWidth, height: m.imageContainerHeight};
        d.patchNodeImage(a + i + "image", c, e, h, l, n, j);
        var o = b(c[a + k.id + "panel"]);
        o && o.length > 0 && o.css({width: n.width}), h.isMobileView() || !h.isMobileDevice() && !h.isTabletDevice() || (b(c[a + "buttonPrev"]).css("height", e.custom[a].imageContainerHeight), b(c[a + "buttonNext"]).css("height", e.custom[a].imageContainerHeight))
    }

    function i(a, c, d, e, f) {
        var g = d.custom[a];
        b(c[a + "dialogBox"]).css({width: g.imageContainerWidth, minHeight: g.dialogBoxHeight, paddingTop: g.paddingTop}), h(a, c, d, e, f);
        var i = f.currentPageInfo.pageItemId, j = b(c[a + i + "description"]), k = b(c[a + i + "ellipsis"]);
        g.showDescription && g.descriptionHeight > g.descriptionHeightLimit && (j.height(g.descriptionHeightLimit), k.show())
    }

    function j(a, c, d, e) {
        var f = e.currentPageInfo.pageItemId, h = e.getDataByQuery(f), i = b(d[a]), j = b(d[a + "dialogBox"]), l = k(j);
        c.custom[a] = g.getDesktopViewDimensions(h, e, c, parseInt(i.data("width-spacer"), 10), parseInt(i.data("height-spacer"), 10), c.height[a + f + "panel"], l)
    }

    function k(a) {
        var b = l(a, "padding-bottom") + l(a, "padding-top"), c = l(a, "padding-right") + l(a, "padding-left");
        return{horizontal: c, vertical: b}
    }

    function l(a, b) {
        return a && b ? parseInt(a.css(b), 10) || 0 : 0
    }

    function m(a, c) {
        var d = b(c);
        if ("none" !== d.css("display")) {
            var e = parseInt(d.css("line-height"), 10);
            a.showDescription = !0, a.descriptionHeight = d.height(), a.descriptionHeightLimit = Math.floor(2 * e)
        }
    }

    function n(a, b, c, d, e) {
        var f = d.currentPageInfo.pageItemId, h = d.getDataByQuery(f), i = g.getMobileViewDimensions(h, d, b), j = c[a + f + "description"];
        m(i, j), i.isMobileZoom = e, b.custom[a] = i
    }

    var f = e.imageUtils, g = e.mediaZoomCalculations, o = "wysiwyg.components.imageZoom";
    c.registerCustomMeasure(o, function (a, b, c, d, e) {
        var f = e.dataItem.id, g = d.isMobileView() ? n : j;
        g(a + f, b, c, d)
    }), c.registerPatcher(o, function (a, b, c, d, e) {
        var f = d.dataItem.id, g = e.isMobileView() ? i : h;
        g(a + f, b, c, d, e)
    }), c.registerRequestToMeasureChildren(o, function (a, b, c, d) {
        var e = d.dataItem.id, f = a.currentPageInfo.pageItemId, g = {pathArray: [e, f, "image"], type: "core.components.Image"};
        return a.isMobileView() ? [
            [e],
            g,
            [e, "dialogBox"],
            [e, f, "description"],
            [e, f, "ellipsis"]
        ] : [
            [e],
            g,
            [e, "dialogBox"],
            [e, "buttonPrev"],
            [e, "buttonNext"],
            [e, f, "panel"]
        ]
    })
}), define("layout/specificComponents/tpaGalleryLayout", ["zepto", "layout/util/layout", "layout/util/calculateScreenWidthDimensions"], function (a, b, c) {
    "use strict";
    function d(a) {
        return function (b, c, d, e, f) {
            a.forEach(function (a) {
                a(b, c, d, e, f)
            })
        }
    }

    function e(a, b) {
        b.width[a] = Math.max(10, b.width[a]), b.minHeight[a] = 10
    }

    function f(b, c, d) {
        a(c[b + "iframe"]).css({width: d.width[b], height: d.height[b]})
    }

    function g(a, b, d, e) {
        var f = b[a], g = b[a + "iframe"], h = c(d, e);
        (e.isMobileView() || e.isMobileDevice()) && (h.left = 0, h.width = e.getSiteWidth()), f.style.width = h.width + "px", f.style.left = h.left + "px", d.width[a] = h.width, d.left[a] = h.left, g.setAttribute("style", "width: " + h.width + "px")
    }

    function h(a, b) {
        var c = .33;
        b.height[a] = c * b.width[a]
    }

    function i(b, d, e, f, g) {
        d.height[b] = a(e[b]).find("iframe").height();
        var h = g.propertiesItem;
        if (h.fitToScreenWidth) {
            var i = e[b], j = c(d, f);
            (f.isMobileView() || f.isMobileDevice()) && (j.left = 0, j.width = f.getSiteWidth()), i.style.width = j.width + "px", i.style.left = j.left + "px", d.width[b] = j.width, d.left[b] = j.left
        }
    }

    function j(a, b, c, d, e) {
        "vertical" === e.propertiesItem.orientation && k(a, b, c)
    }

    function k(b, c, d) {
        c.height[b] = a(d[b]).find("iframe").height()
    }

    function l(a, b, c, d, e) {
        g(a, b, c, e)
    }

    b.registerRequestToMeasureChildren("tpa.viewer.components.StripSlideshow", [
        ["iframe"]
    ]), b.registerRequestToMeasureChildren("tpa.viewer.components.StripShowcase", [
        ["iframe"]
    ]), b.registerRequestToMeasureChildren("tpa.viewer.components.Collage", [
        ["iframe"]
    ]), b.registerRequestToMeasureChildren("tpa.viewer.components.Honeycomb", [
        ["iframe"]
    ]), b.registerRequestToMeasureChildren("tpa.viewer.components.Accordion", [
        ["iframe"]
    ]), b.registerRequestToMeasureChildren("tpa.viewer.components.Masonry", [
        ["iframe"]
    ]), b.registerRequestToMeasureChildren("tpa.viewer.components.Impress", [
        ["iframe"]
    ]), b.registerRequestToMeasureChildren("tpa.viewer.components.Freestyle", [
        ["iframe"]
    ]), b.registerRequestToMeasureChildren("tpa.viewer.components.Thumbnails", [
        ["iframe"]
    ]), b.registerRequestToMeasureChildren("wysiwyg.viewer.components.tpapps.TPA3DGallery", [
        ["iframe"]
    ]), b.registerRequestToMeasureChildren("wysiwyg.viewer.components.tpapps.TPA3DCarousel", [
        ["iframe"]
    ]), b.registerPatcher("tpa.viewer.components.StripShowcase", l), b.registerPatcher("tpa.viewer.components.StripSlideshow", l), b.registerPatcher("tpa.viewer.components.Collage", f), b.registerPatcher("tpa.viewer.components.Accordion", f), b.registerPatcher("tpa.viewer.components.Impress", f), b.registerPatcher("tpa.viewer.components.Freestyle", f), b.registerPatcher("tpa.viewer.components.Thumbnails", f), b.registerPatcher("tpa.viewer.components.Honeycomb", f), b.registerPatcher("tpa.viewer.components.Masonry", f), b.registerPatcher("wysiwyg.viewer.components.tpapps.TPA3DGallery", f), b.registerPatcher("wysiwyg.viewer.components.tpapps.TPA3DCarousel", f), b.registerCustomMeasure("tpa.viewer.components.Collage", d([i, e])), b.registerCustomMeasure("tpa.viewer.components.Masonry", d([k, e])),
        b.registerCustomMeasure("tpa.viewer.components.Honeycomb", e), b.registerCustomMeasure("tpa.viewer.components.Accordion", e), b.registerCustomMeasure("tpa.viewer.components.Impress", e), b.registerCustomMeasure("tpa.viewer.components.Freestyle", d([j, e])), b.registerCustomMeasure("tpa.viewer.components.StripShowcase", e), b.registerCustomMeasure("tpa.viewer.components.StripSlideshow", e), b.registerCustomMeasure("tpa.viewer.components.Thumbnails", e), b.registerCustomMeasure("wysiwyg.viewer.components.tpapps.TPA3DGallery", e), b.registerCustomMeasure("wysiwyg.viewer.components.tpapps.TPA3DCarousel", d([e, h]))
}), define("layout/specificComponents/youTubeSubscribeButtonLayout", ["layout/util/layout"], function (a) {
    "use strict";
    function c(a, c, d, e, f) {
        var g = f.propertiesItem.layout, h = b[g + (e.browser.ie ? "IE" : "")];
        c.width[a] = h.width, c.height[a] = h.height
    }

    var b = {"default": {width: 145, height: 33}, defaultIE: {width: 145, height: 33}, full: {width: 212, height: 55}, fullIE: {width: 212, height: 67}};
    return a.registerRequestToMeasureDom("wysiwyg.common.components.youtubesubscribebutton.viewer.YouTubeSubscribeButton"), a.registerCustomMeasure("wysiwyg.common.components.youtubesubscribebutton.viewer.YouTubeSubscribeButton", c), {}
}), define("layout/specificComponents/wFacebookCommentLayout", ["layout/util/layout", "zepto"], function (a, b) {
    "use strict";
    function d(a, d, e, f) {
        var g = b(e[a]).find("iframe")[0], h = f.isMobileView() ? c.MOBILE : c.DESKTOP;
        g && (d.height[a] = Math.max(d.height[a], g.offsetHeight), d.width[a] = Math.max(d.width[a], h))
    }

    var c = {DESKTOP: 400, MOBILE: 280};
    a.registerCustomMeasure("wysiwyg.viewer.components.WFacebookComment", d)
}), define("layout/specificComponents/facebookLikeLayout", ["zepto", "layout/util/layout"], function (a, b) {
    "use strict";
    function e(a) {
        var b = a ? a.layout : c.layout;
        b = a && a.show_faces ? b + "_showFaces" : b;
        var e = a ? a.action : c.action;
        return d[b][e]
    }

    function f(a, b, c, d, f) {
        var g = e(f.propertiesItem);
        b.width[a] = g.w, b.height[a] = g.h
    }

    function g(b, c, d) {
        var e = d.width[b], f = d.height[b], g = a(c[b]).find("iframe")[0];
        g && (a(g).width(e), a(g).height(f))
    }

    var c = {layout: "box_count", show_faces: "box_count", action: "like"}, d = {standard: {like: {w: 250, h: 40}, recommend: {w: 290, h: 40}}, standard_showFaces: {like: {w: 250, h: 85}, recommend: {w: 290, h: 85}}, button_count: {like: {w: 137, h: 20}, recommend: {w: 137, h: 20}}, box_count: {like: {w: 85, h: 65}, recommend: {w: 125, h: 65}}, box_count_showFaces: {like: {w: 85, h: 65}, recommend: {w: 125, h: 65}}, button_count_showFaces: {like: {w: 137, h: 20}, recommend: {w: 137, h: 20}}};
    return b.registerCustomMeasure("wysiwyg.viewer.components.WFacebookLike", f), b.registerPatcher("wysiwyg.viewer.components.WFacebookLike", g), {}
}), define("layout/specificComponents/formMixinLayout", ["layout/util/layout"], function (a) {
    "use strict";
    function b(a, b) {
        var c = b.height[a + "wrapper"];
        c && (b.height[a] = c)
    }

    function c(a, c, d) {
        b(a, c, d);
        var e = 180;
        c.width[a] = Math.max(c.width[a], e), c.minWidth[a] = e, c.minHeight[a] = 180
    }

    function d(a, b) {
        var c = b.height[a + "wrapper"];
        c && (b.height[a] = c)
    }

    a.registerCustomMeasure("wysiwyg.viewer.components.ContactForm", c), a.registerRequestToMeasureChildren("wysiwyg.viewer.components.ContactForm", [
        ["wrapper"]
    ]), a.registerRequestToMeasureDom("wysiwyg.common.components.subscribeform.viewer.SubscribeForm"), a.registerRequestToMeasureChildren("wysiwyg.common.components.subscribeform.viewer.SubscribeForm", [
        ["wrapper"]
    ]), a.registerCustomMeasure("wysiwyg.common.components.subscribeform.viewer.SubscribeForm", d)
}), define("layout/specificComponents/backOfficeTextLayout", ["layout/util/layout"], function (a) {
    "use strict";
    function b(a, b, c) {
        var d = c[a].childNodes[0];
        d && (b.height[a] = Math.max(b.height[a], d.offsetHeight))
    }

    a.registerCustomMeasure("wysiwyg.common.components.backofficetext.viewer.BackOfficeText", b)
}), define("layout/specificComponents/vkShareLayout", ["layout/util/layout"], function (a) {
    "use strict";
    function b(a, b, c) {
        var d = c[a];
        d && (b.height[a] = d.offsetHeight, b.width[a] = d.offsetWidth)
    }

    a.registerCustomMeasure("wysiwyg.viewer.components.VKShareButton", b), a.registerRequestToMeasureDom("wysiwyg.viewer.components.VKShareButton")
}), define("layout/specificComponents/adminLoginLayout", ["zepto", "layout/util/layout"], function (a, b) {
    "use strict";
    function d(b, d, e) {
        var f = b + "label", g = a(e[f]);
        d.width[b] = Math.max(g.offset().width, d.width[b]), d.height[b] = Math.max(c, d.height[b]), d.minWidth[b] = g.offset().width
    }

    var c = 17;
    b.registerCustomMeasure("wysiwyg.viewer.components.AdminLoginButton", d), b.registerRequestToMeasureChildren("wysiwyg.viewer.components.AdminLoginButton", [
        ["label"]
    ])
}), define("layout/specificComponents/iTunesButtonLayout", ["layout/util/layout"], function (a) {
    "use strict";
    function b(a, b) {
        b.minWidth[a] = 110, b.width[a] = Math.min(400, b.width[a]), b.height[a] = 40 * b.width[a] / 110
    }

    a.registerCustomMeasure("wysiwyg.viewer.components.ItunesButton", b)
}), define("layout/wixappsLayout/proxyLayout/util/masonryCalculations", ["lodash"], function (a) {
    "use strict";
    var b = {getColumnSidePadding: function (a, b, c, d) {
        var e = "rtl" === d, f = Math.floor(c / b), g = a * f, h = (b - 1 - a) * f;
        return{right: e ? g : h, left: e ? h : g}
    }, getMasonryRowsAndColumns: function (b, c) {
        for (var d = [], e = [], f = [], g = 0; c > g; g++)e.push(0), f.push(0);
        for (var h = 0; h < b.length; h++) {
            var i = a.indexOf(e, a.min(e));
            d.push({col: i, row: f[i], topOffset: e[i]}), e[i] += b[h], f[i]++
        }
        return d
    }};
    return b
}), define("layout/wixappsLayout/proxyLayout/paginatedColumnGalleryProxyLayout", ["zepto", "lodash", "layout/wixappsLayout/proxyLayoutRegistrar", "layout/wixappsLayout/proxyLayout/util/masonryCalculations"], function (a, b, c, d) {
    "use strict";
    function e(b) {
        var c = parseInt(a(b).attr("data-columns"), 10), d = a(b).attr("data-direction"), e = parseInt(a(b).attr("data-horizontal-gap"), 10), f = parseInt(a(b).attr("data-vertical-gap"), 10);
        return{columns: c, direction: d, horizontalGap: e, verticalGap: f}
    }

    function f(a) {
        var c = [];
        return b.each(a.childNodes, function (a) {
            c.push(a.clientHeight)
        }), c
    }

    function g(a) {
        var c = f(a), g = e(a), h = a.childNodes, i = d.getMasonryRowsAndColumns(c, g.columns), j = Math.floor(a.clientWidth / g.columns), k = {domManipulations: [], needsRelayout: !0}, l = k.domManipulations, m = [];
        b.map(h, function (a, b) {
            var c = i[b], e = c.row * g.verticalGap + c.topOffset, f = d.getColumnSidePadding(c.col, g.columns, g.horizontalGap, g.direction), h = c.col * j;
            m.push(e);
            var k = {position: "absolute", top: e + "px", "padding-right": f.right + "px", "padding-left": f.left + "px"};
            "rtl" === g.direction ? k.right = h + "px" : k.left = h + "px", l[b] = {node: a, funcName: "css", params: k}
        });
        var n = b(h).map(function (a, b) {
            return m[b] + c[b]
        }).max().value();
        return l.push({node: a, funcName: "css", params: {height: n + "px"}}), k
    }

    c.registerCustomMeasure("PaginatedColumnGalleryProxy", g)
}), define("layout/wixappsLayout/proxyLayout/galleryProxyLayout", ["zepto", "lodash", "layout/wixappsLayout/proxyLayoutRegistrar"], function (a, b, c) {
    "use strict";
    function d(c, d, e) {
        var f = c.id + "itemsContainer", g = a("#" + f), h = a(c), i = Number(h.attr("data-total-columns")), j = Number(h.attr("data-total-rows")), k = Number(h.attr("data-gap")), l = [], m = g.children();
        if (m.length) {
            var n = m[0], o = n.getBoundingClientRect(), p = o.width, q = o.height;
            l = b.map(m, function (b) {
                var c = parseInt(a(b).attr("data-index"), 10), d = Math.floor(c / i) % j, e = d * k, f = c % i, g = f * k;
                return{node: b, funcName: "css", params: {top: d * q + e, left: f * p + g}}
            })
        }
        return e.height[f] = g.height(), e.width[f] = g.width(), e.height[c.id] = h.height(), e.width[c.id] = h.width(), {domManipulations: l}
    }

    c.registerCustomMeasure("GalleryProxy", d)
}), define("layout/wixappsLayout/proxyLayout/imageProxyLayout", ["zepto", "layout/wixappsLayout/proxyLayoutRegistrar"], function (a, b) {
    "use strict";
    function c(b, c, d) {
        var e = a(b), f = b.firstChild, g = f.id, h = {width: parseInt(e.attr("data-width"), 10), height: parseInt(e.attr("data-height"), 10), uri: e.attr("data-uri")}, i = {displayMode: e.attr("data-display-mode")};
        return d.width[g] = b.offsetWidth, d.height[g] = b.offsetHeight, d.custom[g] = {thisIsMyHeight: d.height[g]}, {comp: {compType: "wysiwyg.viewer.components.WPhoto", compId: g, structureInfo: {dataItem: h, propertiesItem: i, layout: null, styleItem: null}}, domManipulations: []}
    }

    b.registerCustomMeasure("Image", c)
}), define("layout/wixappsLayout/proxyLayout/buttonProxyLayout", ["layout/wixappsLayout/proxyLayoutRegistrar"], function (a) {
    "use strict";
    function b(a, b, c) {
        var d = a.id;
        return c.width[d] = a.offsetWidth, c.height[d] = a.offsetHeight, {comp: {compType: "wysiwyg.viewer.components.SiteButton", compId: d, structureInfo: {dataItem: null, propertiesItem: null, layout: null, styleItem: null}}, domManipulations: []}
    }

    a.registerCustomMeasure("Button", b), a.registerCustomMeasure("Button2", b)
}), define("layout/wixappsLayout/proxyLayout/mediaLabelProxyLayout", ["layout/wixappsLayout/proxyLayoutRegistrar"], function (a) {
    "use strict";
    a.registerCustomMeasure("MediaLabel", function (a, b, c) {
        var d = a.id;
        return c.width[d] = a.offsetWidth, c.height[d] = a.offsetHeight, {comp: {compType: "wysiwyg.viewer.components.MediaRichText", compId: d, structureInfo: {dataItem: null, propertiesItem: null, layout: null, styleItem: null}}, domManipulations: []}
    })
}), define("layout/wixappsLayout/proxyLayout/sliderGalleryProxyLayout", ["zepto", "layout/wixappsLayout/proxyLayoutRegistrar"], function (a, b) {
    "use strict";
    function c(b, c, d) {
        var e = a(b), f = b.firstChild, g = f.id, h = {aspectRatio: e.attr("data-aspect-ratio"), imageMode: e.attr("data-image-mode")};
        return d.width[g] = b.offsetWidth, d.height[g] = b.offsetHeight, {comp: {compType: "wysiwyg.viewer.components.SliderGallery", compId: g, structureInfo: {dataItem: null, propertiesItem: h, layout: null, styleItem: null}}, domManipulations: []}
    }

    b.registerCustomMeasure("SliderGallery", c)
}), define("layout/specificComponents/mediaRichTextLayout", ["layout/util/layout", "zepto"], function (a, b) {
    "use strict";
    a.registerRequestToMeasureDom("wysiwyg.viewer.components.MediaRichText"), a.registerPatcher("wysiwyg.viewer.components.MediaRichText", function (a, c, d) {
        var e = b(c[a]), f = e.attr("data-width"), g = parseInt(d.width[a], 10);
        return f && parseInt(f, 10) === g ? (e.css({height: ""}), !1) : (e.attr("data-width", g), !0)
    })
}), define("layout/specificComponents/backToTopButtonLayout", ["layout/util/layout", "zepto"], function (a, b) {
    "use strict";
    function c(a, b, c) {
        b.custom[a + "siteStructureBoundingClientRect"] = c.SITE_STRUCTURE.getBoundingClientRect()
    }

    function d(a, c, d) {
        var e = b(c[a]).find("div")[0];
        b(e).css({right: d.custom[a + "siteStructureBoundingClientRect"].left})
    }

    a.registerCustomMeasure("wysiwyg.common.components.backtotopbutton.viewer.BackToTopButton", c), a.registerPatcher("wysiwyg.common.components.backtotopbutton.viewer.BackToTopButton", d)
}), define("layout/specificComponents/appPartZoomLayout", ["zepto", "layout/util/layout", "layout/specificComponents/wixappsLayout"], function (a, b, c) {
    "use strict";
    function d(a) {
        return a.appPartName ? a.id : a.dataItemRef.slice(1)
    }

    function e(a, b) {
        var c = b.id, e = d(b);
        return a + c + e
    }

    function f(b, c, d) {
        var e = b + "inlineContent", f = a(c[e]), g = Math.max(d.height[b], d.height.screen);
        f.css({height: g})
    }

    function g(b, c, d, e) {
        var f = a(d[b]), g = Math.max(e.height[c], e.height.screen);
        f.css({height: g})
    }

    b.registerRequestToMeasureChildren("wixapps.integration.components.AppPartZoom", function (a, b, e, f) {
        var g = d(f.dataItem), h = f.dataItem.id;
        return[
            [h, g],
            [h, g, c.inlineContentId]
        ]
    }), b.registerCustomMeasure("wixapps.integration.components.AppPartZoom", function (a, b, d, f, g) {
        var h = e(a, g.dataItem);
        c.appPartMeasureFunction(h, b, d, f), b.custom[a] = {marginTop: Math.max((b.height.screen - b.height[h]) / 2, 0), height: b.height[h]}
    }), b.registerPatcher("wixapps.integration.components.AppPartZoom", function (a, b, d, h, i) {
        var j = e(a, h.dataItem);
        i.isMobileView() ? (g(a, j, b, d), f(j, b, d)) : c.appPartPatcherFunction(j, b, d, null, i)
    }), b.registerLayoutInnerCompsFirst("wixapps.integration.components.AppPartZoom", function (b, d, f, g, h) {
        var i = h.getDataByQuery(b.dataQuery), j = e(b.id, i), k = {id: j}, l = a("#" + j)[0];
        return c.preMeasureProxies(k, l, f, g, h)
    }, function (a, b, d, f) {
        var g = f.getDataByQuery(a.dataQuery), h = e(a.id, g), i = {id: h};
        return c.postPatchProxies(i, b, d, f)
    })
}), define("layout/specificComponents/headerContainerLayout", ["layout/util/layout"], function (a) {
    "use strict";
    a.registerCustomMeasure("wysiwyg.viewer.components.HeaderContainer", function (a, b, c, d, e) {
        b.custom[a] = {isFixedPosition: e.layout.fixedPosition}
    })
}), define("layout/specificComponents/footerContainerLayout", ["layout/util/layout"], function (a) {
    "use strict";
    a.registerCustomMeasure("wysiwyg.viewer.components.FooterContainer", function (a, b, c, d, e) {
        b.custom[a] = {isFixedPosition: e.layout.fixedPosition}
    })
}), define("layout/specificComponents/wTwitterTweetLayout", ["zepto", "layout/util/layout"], function (a, b) {
    "use strict";
    function c(a, b, c) {
        b.width[a] = c[a].clientWidth, b.height[a] = c[a].clientHeight
    }

    b.registerCustomMeasure("wysiwyg.viewer.components.WTwitterTweet", c)
}), define("layout/specificComponents/facebookShareLayout", ["layout/util/layout"], function (a) {
    "use strict";
    function b(a, b, c) {
        var d = c[a].children[0];
        d && (b.height[a] = Math.max(b.height[a], d.offsetHeight), b.width[a] = Math.max(b.width[a], d.offsetWidth))
    }

    a.registerCustomMeasure("wysiwyg.viewer.components.FacebookShare", b), a.registerRequestToMeasureDom("wysiwyg.viewer.components.FacebookShare")
}), define("layout/specificComponents/paginatedGridGalleryLayout", ["layout/util/layout", "zepto", "lodash", "utils", "layout/specificComponents/imageLayout"], function (a, b, c, d, e) {
    "use strict";
    function h(a, d, h, i, j, k, l, m) {
        var n = j.isMobileDevice() || j.isMobileView();
        c.forEach(a, function (a, c) {
            var o = f.getItemPosition(c, d, h, i.margin, i.numCols);
            b(a).css({width: d, height: h, left: o.left, top: o.top});
            var p = b(a).attr("data-query"), q = j.getDataByQuery(p, j.currentPageInfo.pageId);
            e.patchNodeImage(m + b(a).find("img").parent()[0].id, k, l, j, g.getImageDataForLayout(q), {width: d, height: h}, n)
        })
    }

    function i(a, d) {
        var e = [
            ["itemsContainer"]
        ], f = b("#" + d + "itemsContainer").children();
        return c.each(f, function (a) {
            e.push([a.id])
        }), e
    }

    var f = d.matrixCalculations, g = d.galleriesCommonLayout;
    return a.registerPatcher("wysiwyg.viewer.components.PaginatedGridGallery", function (a, c, d, e, g) {
        var i = d.height[a], j = d.width[a], k = parseInt(b(c[a]).attr("data-height-diff"), 10), l = parseInt(b(c[a]).attr("data-width-diff"), 10), m = c[a + "itemsContainer"], n = e.propertiesItem;
        b(m).css({width: j - l, height: i - k});
        var o = f.getItemWidth(n.margin, n.numCols, j, l), p = f.getAvailableRowsNumber(n.maxRows, n.numCols, e.dataItem.items.length), q = f.getItemHeight(n.margin, i, p, k);
        h(b(m).children('div[data-page-desc="next"]'), o, q, n, g, c, d, a), h(b(m).children('div[data-page-desc="prev"]'), o, q, n, g, c, d, a), h(b(m).children('div[data-page-desc="curr"]'), o, q, n, g, c, d, a)
    }), a.registerRequestToMeasureChildren("wysiwyg.viewer.components.PaginatedGridGallery", i), {}
}), define("layout/specificComponents/PinterestFollowLayout", ["layout/util/layout"], function (a) {
    "use strict";
    return a.registerCustomMeasure("wysiwyg.viewer.components.PinterestFollow", function (a, b, c) {
        b.height[a] = c[a + "followButtonTag"].offsetHeight, b.width[a] = c[a + "followButtonTag"].offsetWidth
    }), a.registerRequestToMeasureChildren("wysiwyg.viewer.components.PinterestFollow", [
        ["followButtonTag"]
    ]), {}
}), define("layout/specificComponents/videoLayout", ["layout/util/layout"], function (a) {
    "use strict";
    function d(a, d, e, f, g) {
        var h = b[g.dataItem.videoType] || c;
        d.minWidth[a] = h.WIDTH, d.minHeight[a] = h.HEIGHT, d.width[a] = Math.max(d.width[a], h.WIDTH), d.height[a] = Math.max(d.height[a], h.HEIGHT)
    }

    var b = {VIMEO: {WIDTH: 100, HEIGHT: 100}, YOUTUBE: {WIDTH: 200, HEIGHT: 200}}, c = {WIDTH: 10, HEIGHT: 10};
    a.registerCustomMeasure("wysiwyg.viewer.components.Video", d)
}), define("layout/specificComponents/soundCloudLayout", ["layout/util/layout"], function (a) {
    "use strict";
    function b(a, b) {
        var c = 250, d = 50;
        b.minWidth[a] = c, b.minHeight[a] = d, b.width[a] = Math.max(b.width[a], c), b.height[a] = Math.max(b.height[a], d)
    }

    a.registerCustomMeasure("wysiwyg.viewer.components.SoundCloudWidget", b)
}), define("layout/specificComponents/verticalLineLayout", ["layout/util/layout"], function (a) {
    "use strict";
    var b = 2e3;
    a.registerCustomMeasure("wysiwyg.viewer.components.VerticalLine", function (a, c) {
        c.height[a] = Math.min(c.height[a], b)
    })
}), define("layout", ["layout/util/layout", "layout/specificComponents/wixHomepageMenuLayout", "layout/specificComponents/fiveGridLineLayout", "layout/specificComponents/domainSearchLayout", "layout/specificComponents/registerToMeasureOnly", "layout/specificComponents/registerToPureDomMeasure", "layout/specificComponents/PinItPinWidgetLayout", "layout/specificComponents/areaTooltipLayout", "layout/specificComponents/menuLayout", "layout/specificComponents/sliderGalleryLayout", "layout/specificComponents/verticalMenuLayout", "layout/specificComponents/loginButtonLayout", "layout/specificComponents/dialogLayout", "layout/specificComponents/siteBackgroundLayout", "layout/specificComponents/buttonLayout", "layout/specificComponents/photoLayout", "layout/specificComponents/documentMediaLayout", "layout/specificComponents/containerAndScreenWidthLayout", "layout/specificComponents/groupLayout", "layout/specificComponents/wixappsLayout", "layout/specificComponents/shapeLayout", "layout/specificComponents/richtextLayout", "layout/specificComponents/matrixGalleryLayout", "layout/specificComponents/slideShowLayout", "layout/specificComponents/anchorLayout", "layout/specificComponents/tinyMenuLayout", "layout/specificComponents/imageZoomLayout", "layout/specificComponents/tpaGalleryLayout", "layout/specificComponents/youTubeSubscribeButtonLayout", "layout/specificComponents/wFacebookCommentLayout", "layout/specificComponents/facebookLikeLayout", "layout/specificComponents/formMixinLayout", "layout/specificComponents/backOfficeTextLayout", "layout/specificComponents/vkShareLayout", "layout/specificComponents/adminLoginLayout", "layout/specificComponents/iTunesButtonLayout", "layout/wixappsLayout/proxyLayoutRegistrar", "layout/wixappsLayout/proxyLayout/paginatedColumnGalleryProxyLayout", "layout/wixappsLayout/proxyLayout/galleryProxyLayout", "layout/wixappsLayout/proxyLayout/imageProxyLayout", "layout/wixappsLayout/proxyLayout/buttonProxyLayout", "layout/wixappsLayout/proxyLayout/mediaLabelProxyLayout", "layout/wixappsLayout/proxyLayout/sliderGalleryProxyLayout", "layout/specificComponents/mediaRichTextLayout", "layout/specificComponents/backToTopButtonLayout", "layout/specificComponents/appPartZoomLayout", "layout/specificComponents/headerContainerLayout", "layout/specificComponents/footerContainerLayout", "layout/specificComponents/wTwitterTweetLayout", "layout/specificComponents/facebookShareLayout", "layout/specificComponents/paginatedGridGalleryLayout", "layout/specificComponents/PinterestFollowLayout", "layout/specificComponents/videoLayout", "layout/specificComponents/soundCloudLayout", "layout/specificComponents/verticalLineLayout"], function (a) {
    "use strict";
    return a
});