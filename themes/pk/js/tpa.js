define("tpa/mixins/tpaCompBaseMixin", ["zepto", "lodash"], function (a, b) {
    "use strict";
    var c = {mobile: "unavailableInMobile", https: "unavailableInHttps"}, d = function (c) {
        var d = c.getDOMNode(), e = a(d).offset();
        return b.extend({scrollTop: document.body.scrollTop, scrollLeft: document.body.scrollLeft, documentHeight: a(document.body).height(), documentWidth: a(document.body).width(), x: e.left, y: e.top}, d.getBoundingClientRect())
    };
    return{ALIVE_TIMEOUT: 2e4, OVERLAY_GRACE: 2e3, onScroll: function () {
        var a = d(this);
        this.sendPostMessage({intent: "addEventListener", eventType: "SCROLL", params: a})
    }, isUnderMobileView: function () {
        return this.props.siteData.isMobileView()
    }, isInMobileDevMode: function () {
        var a = this.isUnderMobileView(), c = this.props.siteData.currentUrl.query.appDefinitionId, d = this.getAppData();
        return a && c && b.contains(c, d.appDefinitionId)
    }, getEcomParams: function () {
        var a = this.getAppData();
        return"1380b703-ce81-ff05-f115-39571d94dfcd" === a.appDefinitionId && this.props.siteData.currentUrl.query["ecom-tpa-params"]
    }, _createOverlayChildComponent: function (a, c, d, e) {
        return e = e || {}, this.createChildComponent(b.merge(e, {id: d, style: this.props.style, overlay: c, applicationId: this.props.compData.applicationId, hideOverlayFunc: this._hideOverlay}), a, d)
    }, _hideOverlay: function () {
        this.setState({showOverlay: !1})
    }, _createOverlay: function (a) {
        if (!a)return null;
        switch (a) {
            case"preloader":
                return this._createOverlayChildComponent("wysiwyg.viewer.components.tpapps.TPAPreloaderOverlay", a, "preloaderOverlay");
            case"unresponsive":
                return this._createOverlayChildComponent("wysiwyg.viewer.components.tpapps.TPAUnavailableMessageOverlay", a, "unavailableMessageOverlay", {text: "We're sorry, this content cannot be displayed. Please try again later."});
            case c.https:
                return this._createOverlayChildComponent("wysiwyg.viewer.components.tpapps.TPAUnavailableMessageOverlay", a, "unavailableMessageOverlay", {text: "We're sorry, this content cannot be displayed."});
            case c.mobile:
                return this._createOverlayChildComponent("wysiwyg.viewer.components.tpapps.TPAUnavailableMessageOverlay", a, "unavailableMessageOverlay", {text: "We're sorry, this content is currently not optimized for mobile view."});
            default:
                return null
        }
    }, getRootStyle: function (a) {
        return b.assign({minHeight: a.height, minWidth: a.width, visibility: this.state.visibility}, a)
    }, getCompRootStyle: function (a) {
        return this.state && !b.isUndefined(this.state.height) && (a.height = this.state.height), this.state && !b.isUndefined(this.state.width) && (a.width = this.state.width), this.state && (this.state.isAlive || "preloader" === this.state.overlay || "unresponsive" === this.state.overlay || this.state.overlay === c.mobile) && (this.state.visibility = "visible"), a = this.getRootStyle(a)
    }, shouldRenderIframe: function () {
        var a = this.state.overlay, d = !b.contains(c, a), e = "unresponsive" === this.state.overlay, f = this.props.siteData.isViewerMode() || !b.isEmpty(this.state.viewMode), g = d && !e && f;
        return this._shouldClientSpecMapBeReloaded() && (g = g && !!this.props.siteData.dynamicModelState), g
    }, _getIframeProperties: function () {
        if (!this.shouldRenderIframe())return null;
        var a = this._shouldShowIframe(this.state), b = this.buildUrl(this.getBaseUrl());
        return{src: b, key: b, scrolling: "no", frameBorder: "0", allowTransparency: !0, allowFullScreen: !0, style: {display: a ? "block" : "none", width: "100%", height: "100%", overflow: "hidden", position: "absolute"}}
    }, getSkinProperties: function () {
        var a = this._createOverlay(this.state.overlay), b = this._getIframeProperties(), c = {"": {style: this.getCompRootStyle(this.props.style)}, overlay: this.state.showOverlay && this.isNotWorker() ? a : null, iframe: b ? b : "remove"};
        return this.checkIfNeedToSendMemberData(), this.mutateSkinProperties && (c = this.mutateSkinProperties(c)), c
    }, checkIfNeedToSendMemberData: function () {
        var a = this.state.shouldGetSiteMemberDetails;
        if (a) {
            var b = this.props.siteAPI.getSiteAspect("siteMembers"), c = b.getMemberDetails();
            c && a.callback({authResponse: !0, data: c})
        }
    }, getInitialState: function () {
        var a = this.isUnderMobileView() && this.isMobileReady && !this.isMobileReady(), b = a ? c.mobile : null, d = {visibility: "hidden", overlay: b, isAlive: !1, registeredEvents: [], showOverlay: !0, initialWidth: this.props.style.width};
        return this.mutateInitialState && (d = this.mutateInitialState(d)), d
    }, _showOverlayIfNeeded: function () {
        this.state && this.state.isAlive === !1 && !this.state.overlay && this.setState({overlay: this._getInitialOverlay(), visibility: "visible"})
    }, _shouldShowIframe: function (a) {
        var b = !0;
        return this._shouldClientSpecMapBeReloaded() && (b = this._isClientSpecMapReloaded()), b && (!a.overlay || "preloader" === a.overlay)
    }, _isClientSpecMapReloaded: function () {
        return"success" === this.props.siteData.dynamicModelState
    }, _shouldClientSpecMapBeReloaded: function () {
        return this.props.siteData.isViewerMode()
    }, _getInitialOverlay: function () {
        return this.isMobileReady && this.isUnderMobileView() && !this.isMobileReady() ? c.mobile : "preloader"
    }, componentWillReceiveProps: function (a) {
        this.resize && this.resize(a)
    }, componentDidMount: function () {
        var a = this.state.overlay;
        this.isNotWorker() && (this.setTimeout(this._showOverlayIfNeeded, this.OVERLAY_GRACE), this.props.siteData.isViewerMode() || this.setOverlayState && this.setOverlayState()), this.state.isAlive || b.contains(c, a) || !this.isNotWorker() || (this._appAliveTimeout = setTimeout(this._onAppAliveTimeoutExpires, this.ALIVE_TIMEOUT))
    }, isNotWorker: function () {
        return this.isTPAWorker ? !this.isTPAWorker() : !0
    }, _onAppAliveTimeoutExpires: function () {
        this.showUnresponsiveOverlay()
    }, showUnresponsiveOverlay: function () {
        this.state.isAlive || this.setState({overlay: "unresponsive", visibility: "visible"})
    }, componentWillUnmount: function () {
        this._clearAliveTimeout()
    }, _clearAliveTimeout: function () {
        this._appAliveTimeout && clearTimeout(this._appAliveTimeout)
    }, setAppIsAlive: function (a) {
        this._clearAliveTimeout(), this.setState({isAlive: !0, overlay: "preloader" === this.state.overlay ? null : this.state.overlay}, a)
    }, _isUrlSecure: function (a) {
        return/^https/.test(a)
    }}
}), define("tpa/utils/tpaUtils", ["lodash"], function (a) {
    "use strict";
    var c, b = "TPA_PUB_SUB_", d = function () {
        return a.isUndefined(c) && (c = a.now() + ""), c
    }, e = function (a, b) {
        var c = a.getComponentById(b), d = c && a.getSiteData().getClientSpecMapEntry(c.props.compData.applicationId);
        return d
    }, f = function (a) {
        return a.getSiteData().getClientSpecMap()
    }, g = function (a, b) {
        var c = e(a, b);
        return c ? c.appDefinitionId : null
    }, h = function (a) {
        var c = new RegExp("^" + b);
        return a.replace(c, "")
    }, i = function (a) {
        return b.concat(a)
    }, j = function (a) {
        return a && "wysiwyg.viewer.components.tpapps.TPASection" === a.props.structure.componentType
    }, k = function (b, c) {
        b = b || "0.0.0", c = c || "1.41.0";
        var d = a.map(b.split("."), function (a) {
            return parseInt(a, 10)
        }), e = a.map(c.split("."), function (a) {
            return parseInt(a, 10)
        });
        return 3 === d.length && 3 === e.length ? d[0] >= e[0] && d[1] >= e[1] && d[2] >= e[2] : !1
    }, l = function (b) {
        var c = b.cookieUtils.getCookie("_wixUIDX") || "";
        return c = c.slice(a.lastIndexOf(c, "|") + 1), c = c.replace(/^(null-user-id|null)$/g, "")
    }, m = function (a, b) {
        var c = f(a), d = c[b] && c[b].instance.split(".")[1];
        return d && JSON.parse(atob(d))
    };
    return{Constants: {TPA_PUB_SUB_PREFIX: b}, getCacheKiller: d, getAppData: e, getAppDefId: g, stripPubSubPrefix: h, addPubSubEventPrefix: i, isTPASection: j, sdkVersionIsAtLeast: k, getVisitorUuid: l, getInstance: m, getClientSpecMap: f}
}), define("tpa/common/TPABaseUrlBuilder", ["lodash", "utils"], function (a, b) {
    "use strict";
    var c = function (a) {
        this.url = b.urlUtils.parseUrl(a), this.url.query = this.url.query || {}, this.url.search = null
    };
    return c.prototype = {addQueryParam: function (b, c) {
        var d = this.url.query;
        return c && !a.isEmpty(c) && (d[b] ? a.isArray(d[b]) ? d[b].push(c) : d[b] = [d[b], c] : d[b] = c), this
    }, addMultipleQueryParams: function (b) {
        return b && !a.isEmpty(b) && a.assign(this.url.query, b), this
    }, mutateIframeSrc: function (a) {
        return a && (this.url = a(this.url)), this
    }, filterQueryParams: function (b) {
        return b && !a.isEmpty(b) && (this.url.query = a.pick(this.url.query, b)), this
    }, build: function () {
        return b.urlUtils.buildFullUrl(this.url)
    }}, c
}), define("tpa/common/TPAUrlBuilder", ["lodash", "utils", "tpa/common/TPABaseUrlBuilder"], function (a, b, c) {
    "use strict";
    var d = function (a) {
        c.call(this, a)
    };
    return d.prototype = a.extend(new c, {addCacheKiller: function (a) {
        return this.addQueryParam("cacheKiller", a)
    }, addInstance: function (a) {
        return this.addQueryParam("instance", a)
    }, addWidth: function (a) {
        return this.addQueryParam("width", a)
    }, addLocale: function (a) {
        return this.addQueryParam("locale", a)
    }, addViewMode: function (a) {
        return this.addQueryParam("viewMode", a)
    }, addCompId: function (a) {
        return this.addQueryParam("compId", a)
    }, addDeviceType: function (a) {
        return this.addQueryParam("deviceType", a)
    }, addEndpointType: function (a) {
        return this.addQueryParam("endpointType", a)
    }, addOrigCompId: function (a) {
        return this.addQueryParam("origCompId", a)
    }, addExternalId: function (a) {
        return this.addQueryParam("externalId", a)
    }}), d
}), define("tpa/mixins/tpaUrlBuilderMixin", ["lodash", "utils", "tpa/utils/tpaUtils", "tpa/common/TPAUrlBuilder"], function (a, b, c, d) {
    "use strict";
    var e = function (a, b) {
        return a.mutateIframeUrlQueryParam && (b = a.mutateIframeUrlQueryParam(b)), b
    };
    return{getInitialState: function () {
        return{viewMode: this.props.siteData.isViewerMode() ? "site" : this.props.siteData.renderFlags.componentViewMode}
    }, buildUrl: function (b, f) {
        var g = this.state.queryParams || {};
        g = a.merge(g, e(this, g)), this.getEcomParams && this.getEcomParams() && (g["ecom-tpa-params"] = this.getEcomParams());
        var h = new d(b).addCompId(this.props.id).addDeviceType(this.getDeviceType(this)).addInstance(this.getAppData(this).instance).addLocale(this.props.siteData.rendererModel.languageCode).addViewMode(this.state.viewMode).addCacheKiller(c.getCacheKiller()).addExternalId(this.props.compData.referenceId).filterQueryParams(f).addMultipleQueryParams(g);
        return this.mutateIframeSrc && h.mutateIframeSrc(this.mutateIframeSrc), h.build()
    }}
}), define("tpa/mixins/tpaCompApiMixin", ["lodash"], function (a) {
    "use strict";
    return{SUPPORTED_SITE_EVENTS: {SCROLL: "SCROLL", PAGE_NAVIGATION: "PAGE_NAVIGATION", PAGE_NAVIGATION_IN: "PAGE_NAVIGATION_IN", PAGE_NAVIGATION_OUT: "PAGE_NAVIGATION_OUT", PAGE_NAVIGATION_CHANGE: "PAGE_NAVIGATION_CHANGE", STATE_CHANGED: "STATE_CHANGED"}, isEventSupported: function (a) {
        return!!this.SUPPORTED_SITE_EVENTS[a]
    }, isCompListensTo: function (b) {
        return a.contains(this.state.registeredEvents, b)
    }, getAppData: function () {
        return this.props.siteData.rendererModel.clientSpecMap[this.props.compData.applicationId] || {}
    }, getDeviceType: function () {
        return this.props.siteData.isMobileView() ? "mobile" : "desktop"
    }, getViewMode: function () {
        return this.props.siteData.viewMode
    }, sendPostMessage: function (a) {
        this.props.siteAPI.getSiteAspect("tpaPostMessageAspect").sendPostMessage(this, a)
    }, getIframe: function () {
        return this.refs.iframe && this.refs.iframe.getDOMNode()
    }, startListen: function (b) {
        this.isEventSupported(b) && (this.setState({registeredEvents: this.state.registeredEvents.concat(b)}), this.props && b === this.SUPPORTED_SITE_EVENTS.SCROLL && this.props.siteAPI.getSiteAspect("windowScrollEvent").registerToScroll(this), (this.props && a.contains(b, this.SUPPORTED_SITE_EVENTS.PAGE_NAVIGATION) || this.props && a.contains(b, this.SUPPORTED_SITE_EVENTS.PAGE_NAVIGATION_CHANGE)) && this.props.siteAPI.getSiteAspect("tpaPageNavigationAspect").registerToPageChanged(this, b))
    }, stopListen: function (b) {
        this.setState({registeredEvents: a.without(this.state.registeredEvents, b)}), this.props && b === this.SUPPORTED_SITE_EVENTS.SCROLL && this.props.siteAPI.getSiteAspect("windowScrollEvent").unregisterToScroll(this, b), this.props && a.contains(b, this.SUPPORTED_SITE_EVENTS.PAGE_NAVIGATION) && this.props.siteAPI.getSiteAspect("tpaPageNavigationAspect").unregisterToPageChanged(this)
    }, setSiteMemberDataState: function (a) {
        this.setState({shouldGetSiteMemberDetails: a})
    }, hasOrigComponent: function () {
        return!a.isUndefined(this.props.compData.origCompId)
    }, setQueryParams: function (b) {
        a.isObject(b) && this.setState({queryParams: b})
    }}
}), define("tpa/mixins/tpaWidgetMixin", [], function () {
    "use strict";
    return{getBaseUrl: function () {
        var a = this.getAppData();
        if (a.widgets) {
            var b = this.props.compData.widgetId, c = a.widgets[b];
            if (c) {
                var d = this.isInMobileDevMode();
                if (this.isUnderMobileView()) {
                    var e = c.mobileUrl && (d || c.mobilePublished);
                    return e ? c.mobileUrl : c.widgetUrl
                }
                return c.widgetUrl
            }
        }
        return""
    }}
}), define("tpa/mixins/tpaResizeWindowMixin", ["lodash"], function (a) {
    "use strict";
    function b(b) {
        return a.isString(b) && /^[0-9]+%$/.test(b)
    }

    function c(c) {
        return a.isNumber(c) && c >= 0 || b(c)
    }

    return{resizeWindow: function (a, b, d) {
        var e = {};
        this.registerReLayout(), c(b) && (e.height = b), c(a) && (e.width = a), this.setState(e, d)
    }}
}), define("tpa/components/tpaWidget", ["lodash", "skins", "react", "core", "tpa/mixins/tpaCompBaseMixin", "tpa/mixins/tpaUrlBuilderMixin", "tpa/mixins/tpaCompApiMixin", "tpa/mixins/tpaWidgetMixin", "tpa/mixins/tpaResizeWindowMixin"], function (a, b, c, d, e, f, g, h, i) {
    "use strict";
    var j = d.compFactory, k = d.compMixins, l = {displayName: "TPAWidget", mixins: [k.skinBasedComp, k.timeoutsMixin, e, f, g, h, i], mutateIframeUrlQueryParam: function (a) {
        return a.width = this.state.initialWidth, a
    }};
    return j.register("wysiwyg.viewer.components.tpapps.TPAWidget", l), l
}), define("tpa/mixins/tpaSectionMixin", ["lodash"], function (a) {
    "use strict";
    return{isTPASection: !0, getSiteAdditionalDataFromProps: function (a) {
        return a.siteData.currentPageInfo.pageAdditionalData || ""
    }, mutateSkinProperties: function (a) {
        return"object" == typeof a.iframe && (a.iframe.src = this.buildUrl(this.getBaseUrl())), a
    }, mutateInitialState: function (a) {
        return a.sectionUrlState = this.getSiteAdditionalDataFromProps(this.props), a.sectionUrl = this.fixSectionUrl(), a
    }, fixSectionUrl: function () {
        var a = this.props.siteAPI.getPageUrl(!0);
        return this.endsWith(a, "/") ? a : a += "/"
    }, endsWith: function (a, b) {
        return-1 !== a.indexOf(b, a.length - b.length)
    }, componentWillReceiveProps: function (b) {
        var c = this.state.sectionUrlState, d = this.getSiteAdditionalDataFromProps(b);
        if ((c !== d || a.isEmpty(d) && !a.isEmpty(this.state.pushState)) && this.isCompListensTo(this.SUPPORTED_SITE_EVENTS.STATE_CHANGED) && this.sendPostMessage({intent: "addEventListener", eventType: "STATE_CHANGED", params: {newState: d}}), b.currentPage === this.props.pageId) {
            var e = this.props.siteData.currentPageInfo.pageAdditionalData;
            this.state.pushState ? (e !== c && e !== this.state.pushState || this.props.currentPage !== b.currentPage) && this.setState({sectionUrlState: d, sectionUrl: this.fixSectionUrl(), pushState: void 0}) : e !== c && this.setState({sectionUrlState: d, sectionUrl: this.fixSectionUrl()})
        }
    }, isMobileReady: function () {
        var a = this.getAppData(), b = this.isInMobileDevMode();
        return a.sectionMobileUrl && (b || a.sectionMobilePublished)
    }, mutateIframeUrlQueryParam: function (a) {
        return this.props.siteData.isViewerMode() ? (a["section-url"] = this.state.sectionUrl, a.target = "_top") : (a["section-url"] = this.getBaseUrl(), a.target = "_self"), a.width = this.state.initialWidth, a
    }, mutateIframeSrc: function (a) {
        return a = this.addStateToUrlObj(a, this.state.sectionUrlState)
    }, addStateToUrlObj: function (a, b) {
        if (b) {
            var c = "#" === b.charAt(0);
            c ? a.hash = b : ("/" !== a.path.slice(-1) && (a.path += "/"), a.path += b)
        }
        return a
    }}
}), define("tpa/components/tpaSection", ["lodash", "skins", "react", "core", "tpa/mixins/tpaCompBaseMixin", "tpa/mixins/tpaUrlBuilderMixin", "tpa/mixins/tpaCompApiMixin", "tpa/mixins/tpaSectionMixin", "tpa/mixins/tpaResizeWindowMixin"], function (a, b, c, d, e, f, g, h, i) {
    "use strict";
    var j = d.compFactory, k = d.compMixins, l = {displayName: "TPASection", mixins: [k.skinBasedComp, k.timeoutsMixin, e, f, g, h, i], getBaseUrl: function () {
        var b = this.getAppData(), c = this.props.compData.widgetId, d = b.sectionUrl, e = b.sectionDefaultPage, f = this.props.compData.type, g = this.isUnderMobileView() && this.isMobileReady();
        if ("TPAWidget" === f && c) {
            var h = a.find(b.widgets, function (a) {
                return a.widgetId === c
            });
            h && (e = h.appPage.defaultPage, d = g ? h.mobileUrl : h.widgetUrl)
        } else g && (d = b.sectionMobileUrl);
        return e && !a.isEmpty(e) && ("/" !== d.slice(-1) && (d += "/"), d += e), d
    }};
    return j.register("wysiwyg.viewer.components.tpapps.TPASection", l), l
}), define("tpa/components/tpaMultiSection", ["lodash", "skins", "react", "core", "tpa/mixins/tpaCompBaseMixin", "tpa/mixins/tpaUrlBuilderMixin", "tpa/mixins/tpaCompApiMixin", "tpa/mixins/tpaSectionMixin"], function (a, b, c, d, e, f, g, h) {
    "use strict";
    var i = d.compFactory, j = d.compMixins, k = {displayName: "TPAMultiSection", mixins: [j.skinBasedComp, j.timeoutsMixin, e, f, g, h], getBaseUrl: function () {
        var b = this.getAppData(), c = b.sectionUrl, d = b.widgets[this.props.compData.widgetId], e = b.sectionDefaultPage;
        return d && (c = this.isUnderMobileView() && this.isMobileReady() ? d.mobileUrl : d.widgetUrl, e = d.appPage.defaultPage), e && !a.isEmpty(e) && ("/" !== c.slice(-1) && (c += "/"), c += e), c
    }};
    return i.register("wysiwyg.viewer.components.tpapps.TPAMultiSection", k), k
}), define("tpa/components/tpaWorker", ["lodash", "skins", "core", "tpa/mixins/tpaCompBaseMixin", "tpa/mixins/tpaUrlBuilderMixin", "tpa/mixins/tpaCompApiMixin"], function (a, b, c, d, e, f) {
    "use strict";
    var g = c.compFactory, h = c.compMixins, i = {mixins: [h.skinBasedComp, d, e, f], getBaseUrl: function () {
        var a = this.getAppData();
        return a.appWorkerUrl
    }, mutateIframeUrlQueryParam: function (a) {
        return a.endpointType = "worker", a
    }, mutateSkinProperties: function (a) {
        return a.iframe && a.iframe.style && (a.iframe.style.display = "none"), a
    }, isTPAWorker: function () {
        return!0
    }};
    g.register("tpa.viewer.classes.TPAWorker", i)
}), define("tpa/components/tpaGluedWidget", ["lodash", "skins", "react", "core", "tpa/mixins/tpaCompBaseMixin", "tpa/mixins/tpaUrlBuilderMixin", "tpa/mixins/tpaCompApiMixin", "tpa/mixins/tpaWidgetMixin", "tpa/mixins/tpaResizeWindowMixin"], function (a, b, c, d, e, f, g, h, i) {
    "use strict";
    var j = d.compFactory, k = d.compMixins, l = {displayName: "TPAGluedWidget", mixins: [k.skinBasedComp, k.timeoutsMixin, e, f, g, h, i], mutateIframeUrlQueryParam: function (a) {
        return a.width = this.state.initialWidth, a
    }, mutateSkinProperties: function (a) {
        var b = a[""].style;
        return b.position = "fixed", b.zIndex = 100, a
    }};
    j.register("wysiwyg.viewer.components.tpapps.TPAGluedWidget", l)
}), define("tpa/aspects/TPAWorkerAspect", ["lodash", "core"], function (a, b) {
    "use strict";
    function e(a) {
        this._aspectSiteAPI = a
    }

    var c = function (a) {
        return{componentType: "tpa.viewer.classes.TPAWorker", skin: "wysiwyg.viewer.skins.TPAWidgetSkin", type: "Component", id: a}
    }, d = function (a, d, e) {
        var f = "tpaWorker_" + e.applicationId, g = c(f), h = b.componentPropsBuilder.getCompProps(g, a, null, d);
        return h.compData = e, b.compFactory.getCompClass(g.componentType)(h)
    };
    return e.prototype = {getComponentStructures: function () {
        var b = [], d = this._aspectSiteAPI.getSiteData().getClientSpecMap(), e = this.getTPAWorkers(d);
        if (!a.isEmpty(e)) {
            var f = c();
            b.push(f)
        }
        return b
    }, getTPAWorkers: function (b) {
        var c = a.filter(b, function (b) {
            return a.isString(b.appWorkerUrl) && b.permissions && !b.permissions.revoked
        });
        return c
    }, getReactComponents: function (b) {
        var c = this._aspectSiteAPI.getSiteData().getClientSpecMap(), e = this._aspectSiteAPI.getSiteAPI(), f = this.getTPAWorkers(c), g = [];
        return a.forEach(f, function (a) {
            var c = d(e, b, a);
            g.push(c)
        }), g
    }}, e
}), define("tpa/bi/events.json", [], function () {
    return{JS_SDK_FUNCTION_CALL: {eventId: 12, adapter: "sdk", src: 11, params: {visitor_id: "visitorUuid", sdk_ver: "sdkVersion", origin: "origin", function_name: "fnName", namespace: "namespace", app_id: "appId", instance_id: "instanceId", is_published: "isPublished"}}, GALLERY_FUNCTION_CALL: {eventId: 11, adapter: "sdk", src: 11, params: {visitor_id: "visitorUuid", sdk_ver: "sdkVersion", origin: "origin", function_name: "fnName", namespace: "namespace"}}}
}), define("tpa/bi/events", ["tpa/bi/events.json", "lodash", "utils"], function (a, b, c) {
    "use strict";
    var d = c.logger;
    return d.register("tpa", "event", a), a
}), define("tpa/bi/tpaBi", ["lodash", "tpa/utils/tpaUtils", "tpa/bi/events", "utils"], function (a, b, c, d) {
    "use strict";
    var e = function (a) {
        return a ? c.JS_SDK_FUNCTION_CALL : c.GALLERY_FUNCTION_CALL
    }, f = function (a, b) {
        var c = a.widgets, d = c[b.props.compData.widgetId], e = d ? d.published : a.sectionPublished;
        return e ? 1 : 0
    }, g = function (a, c) {
        var d = b.getClientSpecMap(a), e = b.getInstance(a, c.appId);
        if (!d[c.appId])throw"app definition could not be found by the given appId";
        var g = a.getComponentById(c.compId);
        return{appId: d[c.appId].appDefinitionId, instanceId: e.instanceId, isPublished: f(d[c.appId], g)}
    }, h = function (a, b) {
        var c = a.getComponentById(b.compId), d = c && c.props && c.props.compData && c.props.compData.applicationId;
        return b.appId = d, b.component = c, b
    }, i = function (a) {
        return a.component && a.component.props.compData && a.version && a.type && a.namespace
    }, j = function (c, f) {
        var j = h(f, c);
        if (i(j)) {
            var k = {visitorUuid: b.getVisitorUuid(d), sdkVersion: j.version, origin: "viewer", fnName: j.type, namespace: j.namespace};
            if (j.appId) {
                var l = g(f, j);
                a.merge(k, l)
            }
            var m = e(j.appId);
            f.reportBI(m, k)
        }
    };
    return{sendBIEvent: j}
}), define("tpa/common/tpaPostMessageCommon", ["tpa/bi/tpaBi"], function (a) {
    "use strict";
    var b = {TPA_MESSAGE: "TPA", TPA_MESSAGE_PREVIEW: "TPA_PREVIEW", TPA_MESSAGE2: "TPA2", TPA_RESPONSE: "TPA_RESPONSE", TPA_PREVIEW_RESPONSE: "TPA_PREVIEW_RESPONSE", PINGPONG_PREFIX: "pingpong:"}, c = function (a, b) {
        var c = {getSectionUrl: !0, siteInfo: !0, navigateToPage: !0, getExternalId: !0, smRequestLogin: !0};
        return!("preview" === a.getSiteData().viewMode && c[b.type])
    }, d = function (a, b, c) {
        var d = "";
        try {
            d = JSON.stringify(b)
        } catch (e) {
            return
        }
        a.postMessage || (a = a.contentWindow), a.postMessage(d, c || "*")
    }, e = function (a, c, e) {
        return function (f) {
            try {
                d(a, {intent: e || b.TPA_RESPONSE, callId: c.callId, type: c.type, res: f, status: !0})
            } catch (g) {
            }
        }
    }, f = function (a) {
        return a === b.TPA_MESSAGE || a === b.TPA_MESSAGE2
    }, g = function (a) {
        return a === b.TPA_MESSAGE_PREVIEW
    }, h = function (a) {
        return a.replace(b.PINGPONG_PREFIX, "")
    }, i = function (d, h, i, j) {
        var k;
        try {
            j.data ? k = JSON.parse(j.data) : j.originalEvent && j.originalEvent.data && (j = j.originalEvent, k = JSON.parse(j.data))
        } catch (l) {
            return
        }
        k && f(k.intent) ? d ? (i(d, h, k, e(j.source, k)), a.sendBIEvent(k, h)) : c(h, k) && (i(h, k, e(j.source, k)), a.sendBIEvent(k, h)) : k && g(k.intent) && (d ? i(d, h, k, e(j.source, k, b.TPA_PREVIEW_RESPONSE)) : i(h, k, e(j.source, k, b.TPA_PREVIEW_RESPONSE)))
    };
    return{Intents: b, callPostMessage: d, generateResponseFunction: e, isTPAMessage: f, fixOldPingPongMessageType: h, handleTPAMessage: i}
}), define("tpa/utils/sitePages", ["lodash", "utils"], function (a, b) {
    "use strict";
    var c = function (a) {
        var c = b.menuUtils.getSiteMenu(a, !1);
        return e(c)
    }, d = function (a) {
        var b = c(a);
        return h(b, a)
    }, e = function (b) {
        var c = [];
        return a.each(b, function (b) {
            var d = f(b), e = b.items;
            a.each(e, function (a) {
                var b = f(a);
                d.subPages = d.subPages || [], d.subPages.push(b)
            }), c.push(d)
        }), c
    }, f = function (a) {
        if (a.link) {
            var c = a.link.pageId;
            return{title: a.label || "", id: b.stringUtils.startsWith(c, "#") ? c.substr(1) : c, hide: !a.isVisible || !1}
        }
        return{}
    }, g = function (a, b) {
        return b.getDataByQuery(a, "masterPage")
    }, h = function (b, c) {
        var d = [];
        return a.each(b, function (b) {
            a.each(b.subPages, function (a) {
                d.push(g(a.id, c))
            }), d.push(g(b.id, c))
        }), d
    };
    return{getSitePagesInfoData: c, getSitePagesData: d}
}), define("tpa/services/pageService", ["lodash", "tpa/utils/sitePages"], function (a, b) {
    "use strict";
    var c = {}, d = function (d) {
        if (a.size(c) > 0)return c;
        var e = b.getSitePagesData(d.getSiteData());
        return a.each(e, function (b) {
            if (b && b.tpaApplicationId > 0) {
                var d = {pageId: b.id, tpaId: b.tpaApplicationId, tpaPageId: b.tpaPageId};
                a.isUndefined(c[d.tpaId]) && (c[d.tpaId] = []), c[d.tpaId].push(d)
            }
        }), c
    };
    return{mapPageToWidgets: d}
}), define("tpa/utils/tpaStyleUtils", ["lodash", "color", "fonts", "utils"], function (a, b, c, d) {
    "use strict";
    var e = "param_color_", f = "param_number_", g = "param_boolean_", h = "param_font_", i = function () {
        return{Title: "font_0", Menu: "font_1", "Page-title": "font_2", "Heading-XL": "font_3", "Heading-L": "font_4", "Heading-M": "font_5", "Heading-S": "font_6", "Body-L": "font_7", "Body-M": "font_8", "Body-S": "font_9", "Body-XS": "font_10"}
    }, j = function (a, b) {
        var d = c.fontUtils.parseFontStr(a), e = c.fontUtils.getFontFamilyWithFallbacks(d.family);
        return{editorKey: b, lineHeight: d.lineHeight, style: d.style, weight: d.weight, size: d.size, fontFamily: d.family.toLowerCase(), value: "font:" + [d.style, d.variant, d.weight, d.size + "/" + d.lineHeight, e].join(" ") + ";"}
    }, k = function (a) {
        var c = a.split(",");
        return b({r: c[0], g: c[1], b: c[2]}).hexString()
    }, l = function (b, c, d) {
        return m(a.extend(n(d, c), w(b)), c)
    }, m = function (b, d) {
        var e = {};
        a.each(b, function (a) {
            "string" == typeof a && (a = JSON.parse(a)), e[a.family || a.fontFamily] = 1
        });
        var f = c.fontUtils.getFontsUrl(e, d);
        return-1 !== f.indexOf("family=null") ? "" : f
    }, n = function (b, c) {
        var d = {}, e = o(b, c);
        return a.each(e, function (a, b) {
            b.match(h) && (d[b.replace(h, "")] = a)
        }, this), d
    }, o = function (a, b) {
        var c = b.getAllTheme(), d = c[a.props.structure.styleId];
        if (!d)return null;
        var e = d.style && d.style.properties;
        return e ? e : null
    }, p = function (c, e, f, g) {
        var i, h = e[c];
        if (h && !a.isString(h) && h.value)return{themeName: void 0, value: h.value.cssColor || h.value.color.value || h.value.rgba};
        if (!f && h && a.isString(h) && d.stringUtils.startsWith(h, "color_")) {
            var j = g.getColor(h);
            j = j.indexOf(",") > -1 ? k(j) : j;
            var l = b(j);
            return i = e.hasOwnProperty("alpha-" + c) && 1 !== e["alpha-" + c] ? "rgba(" + l.values.rgb.join(",") + "," + e["alpha-" + c] + ")" : l.hexString(), {themeName: h, value: i}
        }
        return f ? {themeName: void 0, value: void 0} : {themeName: void 0, value: h}
    }, q = function (b, c, i, j) {
        var k = j.getAllTheme().THEME_DATA, l = d.stringUtils.startsWith(b, "alpha-");
        if (c.hasOwnProperty(b) && !l)if (b.match(e))i.colors[b.replace(e, "")] = p(b, c, l, j); else if (b.match(f))i.numbers[b.replace(f, "")] = +c[b]; else if (b.match(g)) {
            var m;
            m = a.isBoolean(c[b]) ? c[b] : "false" !== c[b], i.booleans[b.replace(g, "")] = m
        } else if (b.match(h)) {
            var n = "object" == typeof c[b] ? c[b].value : JSON.parse(c[b]);
            n.fontStyleParam && "Custom" === n.preset ? r(n) : n.fontStyleParam ? s(n, k.font) : n.fontParam && t(n), i.fonts[b.replace(h, "")] = n
        }
    }, r = function (a) {
        var b = c.fontUtils.getFontFamilyWithFallbacks(a.family), d = a.size + "px", e = a.size + (a.size / 4 << 0) + "px", f = a.style.italic ? "italic" : "normal", g = a.style.bold ? "bold" : "normal", h = "normal", i = "";
        i += "font:" + [f, h, g, d + "/" + e, b].join(" ") + ";", i += a.style.underline ? "text-decoration:underline;" : "", a.value = i
    }, s = function (a, b) {
        var c = w(b), d = c[a.preset];
        a.value = d.value, a.size = parseInt(d.size, 10), a.family = d.fontFamily, a.style.bold = "bold" === d.weight, a.style.italic = "italic" === d.style, a.style.underline = !1
    }, t = function (a) {
        a.family = a.value, a.value = u(a), a.size = 0, a.style = {bold: !1, italic: !1, underline: !1}
    }, u = function (a) {
        var b = "font-family:" + (a.cssFontFamily || a.value) + ";";
        return b = b.replace(/''/g, "'")
    }, v = function (b, c) {
        var d = b.getAllTheme().THEME_DATA, e = {colors: {}, numbers: {}, booleans: {}, fonts: {}, googleFontsCssUrl: l(d.font, b, c)}, f = o(c, b);
        return f ? (a.forEach(f, function (a, c) {
            q(c, f, e, b)
        }), e) : e
    }, w = function (b) {
        var c = 0;
        return a.reduce(i(), function (a, d, e) {
            return a[e] = j(b[c++], d), a
        }, {})
    }, x = function (b) {
        return a.reduce(b, function (a, b, c) {
            return(c >= 1 && 5 >= c || c >= 11 && 35 >= c) && a.push({name: "color_" + c, value: b.indexOf(",") > -1 ? k(b) : b}), a
        }, [])
    }, y = function (b) {
        return a.forEach(b, function (b) {
            b.fonts = a.filter(b.fonts, function (a) {
                return"legacy" !== a.permissions
            })
        })
    }, z = function (b, d) {
        var e = b.getSiteData(), f = e.getAllTheme().THEME_DATA, g = d && d.hasOrigComponent() ? b.getComponentById(d.getOrigComponentId()) : d, h = (a.contains(e.santaBase, "localhost") ? e.santaBase : e.santaBase.replace("http://", "//")) + "/static/images/editorUI/fonts.png", i = y(c.fontUtils.getCurrentSelectablefonts(e));
        return{fonts: {cssUrls: c.fontUtils.getWixStoredFontsCssUrls(e), imageSpriteUrl: h, fontsMeta: i}, siteTextPresets: w(f.font), siteColors: x(f.color), style: g ? v(e, g) : {}}
    };
    return{getTextPresets: w, getSiteColors: x, getStylesForSDK: v, getStyleDataToPassIntoApp: z}
}), define("tpa/bi/errors.json", [], function () {
    return{SDK_SET_HEIGHT_ERROR: {errorCode: 190, desc: "sdk - set height with a big number", severity: "error", params: {p1: "height"}}, SDK_PUBSUB_PUBLISH_ERROR: {errorCode: 191, desc: "sdk - pubSub: publish error. App not installed on site - cannot find appDefId", severity: "error"}}
}), define("tpa/bi/errors", ["tpa/bi/errors.json", "lodash", "utils"], function (a, b, c) {
    "use strict";
    var d = c.logger;
    return b.forEach(a, function (a, b) {
        a.errorName = b
    }), d.register("tpa", "error", a), a
}), define("tpa/handlers/tpaHandlers", ["zepto", "lodash", "react", "core", "utils", "tpa/utils/sitePages", "tpa/services/pageService", "tpa/utils/tpaUtils", "tpa/utils/tpaStyleUtils", "tpa/bi/errors"], function (a, b, c, d, e, f, g, h, i, j) {
    "use strict";
    var k = 2e4, l = d.activityTypes.TPAActivity, m = d.ActivityService, n = function (a, b, c) {
        var d = {}, e = a.getCurrentPageData();
        h.sdkVersionIsAtLeast(b.version, "1.42.0") ? d.pageTitle = e.siteTitle : (d.siteTitle = e.siteTitle, d.pageTitle = e.title), d.siteDescription = e.descriptionSEO, d.siteKeywords = e.metaKeywordsSEO;
        var f = a.getSiteData().currentUrl;
        d.url = f.full, d.baseUrl = f.protocol + "//" + f.host + f.path, d.referer = document.referrer, c(d)
    }, o = function (a, b) {
        var c = "number" == typeof b.data ? b.data : b.data.height;
        if (c > k) {
            var d = {height: c};
            e.logger.reportBI(a.getSiteData(), j.SDK_SET_HEIGHT_ERROR, d)
        }
        var f = {height: c}, g = a.getComponentById(b.compId);
        g.registerReLayout(), g.setState(f)
    }, p = function (a, b, c) {
        c(a.getCurrentPageId())
    }, q = function (a, b) {
        a.navigateToPage({pageId: b.data.pageId})
    }, r = function (a, b, c) {
        c(f.getSitePagesInfoData(a.getSiteData()))
    }, s = function (a, c, d) {
        var e = a.getComponentsByPageId(c), f = b.find(e, function (a) {
            return a.isTPASection
        });
        f && f.setState({sectionUrlState: d})
    }, t = function (a, c, d) {
        var e = a.getComponentById(c.compId), f = g.mapPageToWidgets(a), h = e.getAppData(), i = f[h.applicationId];
        if (b.isUndefined(i))d({error: {message: 'Page with app "' + h.appDefinitionName + '" was not found.'}}); else {
            var j, k, l = c.data;
            if (b.isObject(c.data) && (l = c.data.state, k = c.data.sectionIdentifier && c.data.sectionIdentifier.sectionId), b.isUndefined(k))j = i[0].pageId; else {
                var m = b.find(i, function (a) {
                    return a.tpaPageId === k
                });
                b.isUndefined(m) ? d({error: {message: 'App page with sectionId "' + k + '" was not found.'}}) : j = m.pageId
            }
            s(a, j, l), a.navigateToPage({pageId: j, pageAdditionalData: l})
        }
    }, u = function (a, b) {
        a.scrollSiteBy(b.data.x, b.data.y)
    }, v = function (a, b) {
        a.scrollSiteTo(b.data.x, b.data.y)
    }, w = function (b, c, d) {
        var e = b.getComponentById(c.compId), f = e.getDOMNode(), g = y(a(f).offset());
        d({rect: x(f), offsets: g})
    }, x = function (a) {
        var c = a.getBoundingClientRect(), d = b(c).pick(["left", "right", "top", "bottom", "height", "width"]).mapValues(function (a) {
            return Math.floor(a)
        }).value();
        return d
    }, y = function (a) {
        return{x: a.left, y: a.top}
    }, z = function (a, b, c) {
        var d = a.getComponentById(b.compId), e = a.getSiteAspect("tpaModalAspect");
        e.showModal(G(b, d), c)
    }, A = function (a, b, c) {
        var d = a.getComponentById(b.compId);
        if (!B(d)) {
            var e = new Error;
            throw e.name = "Operation not supported", e.message = "An app can not open a popup from a modal.", e
        }
        var f = a.getSiteAspect("tpaPopupAspect");
        f.showPopup(G(b, d), c)
    }, B = function (a) {
        var c = ["wysiwyg.viewer.components.tpapps.TPAModal"];
        return!b.contains(c, a.props.structure.componentType)
    }, C = function (a, b) {
        var c = a.getComponentById(b.compId);
        c && c.hide && c.hide(b.data)
    }, D = function (a) {
        return b.isString(a) && /^[0-9]+%$/.test(a)
    }, E = function (a) {
        var c = ["wysiwyg.viewer.components.tpapps.TPAGluedWidget", "wysiwyg.viewer.components.tpapps.TPAPopup", "wysiwyg.viewer.components.tpapps.TPAModal"];
        return a && a.resizeWindow && b.contains(c, a.props.structure.componentType)
    }, F = function (a, b, c) {
        var d = b.data.width, e = b.data.height;
        D(d) || (d = parseFloat(d)), D(e) || (e = parseFloat(e));
        var f = a.getComponentById(b.compId);
        E(f) && f.resizeWindow(d, e, c)
    }, G = function (c, d) {
        var e = b.merge(c.data, {origCompId: c.compId});
        return e.origCompStyle = a(d.getDOMNode()).offset(), e.position = b.defaults(e.position || {}, {origin: "FIXED", placement: "CENTER", x: 0, y: 0}), e.position.x = H(e.position.x), e.position.y = H(e.position.y), e.windowSize = {width: a(window).width(), height: a(window).height()}, e.applicationId = d.props.compData.applicationId, e
    }, H = function (a) {
        if (b.isString(a)) {
            var c = parseInt(a, 10);
            return b.isNaN(c) ? 0 : c
        }
        return b.isNumber(a) ? a : 0
    }, I = function (a, b) {
        var c = b.data.eventKey, d = a.getComponentById(b.compId);
        d && !d.isCompListensTo(c) && d.startListen(c)
    }, J = function (a, b) {
        var c = b.data.eventKey || b.data, d = a.getComponentById(b.compId);
        d && d.stopListen(c)
    }, K = function (a, b) {
        var f, c = "string" == typeof b.data ? b.data : b.data.state, d = a.getCurrentPageId(), e = a.getComponentById(b.compId);
        try {
            switch (f = JSON.parse(c), f.cmd) {
                case"zoom":
                    e.processImageClick(f);
                    break;
                case"componentReady":
                    e.setComponentInIframeReady();
                    break;
                case"navigateToAnchor":
                    var g = f.args[0], h = f.args[1];
                    a.getCurrentPageId() === g ? h && a.scrollToAnchor(h) : a.navigateToPage({pageId: g, pageAdditionalData: null, anchorData: h});
                    break;
                default:
                    L(a, e, d, c)
            }
        } catch (i) {
            L(a, e, d, c)
        }
    }, L = function (a, b, c, d) {
        h.isTPASection(b) && (b.setState({pushState: d}), a.navigateToPage({pageId: c, pageAdditionalData: d}))
    }, M = function (a, c, d) {
        var e = a.getComponentById(c.compId), f = g.mapPageToWidgets(a), h = a.getSiteData().getClientSpecMap()[e.props.compData.applicationId], i = h.applicationId;
        if (b.isEmpty(f) || b.isUndefined(f[i]))d({error: {message: 'Page with app "' + h.appDefinitionName + '" was not found.'}}); else {
            var m, j = f[i], k = c.data.sectionIdentifier, l = b.find(j, {tpaPageId: k}) || j[0];
            if (l && (m = l.pageId),
                b.isUndefined(m))d({error: {message: "Page was not found."}}); else {
                var n = a.getPageUrlFor(m);
                d(b.isUndefined(n) ? {error: {message: "Page was not found."}} : {url: n})
            }
        }
    }, N = function (a, b, c, d) {
        var e = c.getMemberDetails();
        null === e ? a.setSiteMemberDataState({callback: d}) : (d(e), a.setSiteMemberDataState(null))
    }, O = function (a, b, c) {
        var d = a.getComponentById(b.compId), e = a.getSiteAspect("siteMembers"), f = e.isLoggedIn();
        f ? N(d, b, e, function (a) {
            c({authResponse: !0, data: a})
        }) : e.showSignUpDialog(function (a) {
            c({authResponse: !0, data: a})
        })
    }, P = function (a, b, c) {
        var d = a.getComponentById(b.compId), e = a.getSiteAspect("siteMembers"), f = e.isLoggedIn();
        f ? N(d, b, e, function (a) {
            c(a)
        }) : c(null)
    }, Q = function (a, b) {
        var c = b.data, d = a.getComponentById(b.compId), e = a.getSiteData(), f = h.getAppData(a, b.compId), g = f.instance, i = {type: c.activity.type || "TPA", appDefinitionId: f.appDefinitionId || "TPA", info: c.activity.info || {}, details: c.activity.details || {}, contactUpdate: c.activity.contactUpdate || {}, instance: g}, j = function (a) {
            n({status: !0, response: a})
        }, k = function (a) {
            var b = {status: a.status, statusText: a.statusText, responseText: a.responseText};
            n({status: !1, response: b})
        }, n = function (a) {
            d.sendPostMessage({intent: "TPA_RESPONSE", compId: b.compId, callId: b.callId, type: b.type, status: a.status, res: {response: a.response, status: a.status}})
        }, o = new l(e, i), p = new m(e.currentUrl.host);
        p.reportActivity(o, j, k)
    }, R = function (a, b, c) {
        c(a.getUserSession())
    }, S = function (a, b, c) {
        var d = a.getComponentById(b.compId);
        d && d.setAppIsAlive && d.setAppIsAlive(), c(i.getStyleDataToPassIntoApp(a, d))
    }, T = function (a, b) {
        return i.getStyleDataToPassIntoApp(a, b)
    }, U = function (a, b, c) {
        b.data.messageId = Date.now();
        var d = {ctToken: a.getSiteData().ctToken}, f = b.data, g = "?" + e.urlUtils.toQueryString(d), h = "http://player-counters.wix.com/collector/rest/collect-js" + g, i = function (a) {
            c({status: "success", response: a})
        }, j = function (a) {
            var b = a && a.responseText;
            c({status: "error", response: b})
        };
        e.ajaxLibrary.ajax({type: "POST", url: h, data: JSON.stringify(f), dataType: "json", contentType: "application/json", success: i, error: j})
    }, V = function (a, b, c) {
        var d = a.getComponentById(b.compId);
        d && c(d.props.compData.referenceId)
    }, W = function () {
    };
    return{siteInfo: n, heightChanged: o, registerEventListener: I, navigateToPage: q, smRequestLogin: O, smCurrentMember: P, scrollBy: u, scrollTo: v, postActivity: Q, getCurrentPageId: p, getUserSession: R, boundingRectAndOffsets: w, navigateToSectionPage: t, getSitePages: r, removeEventListener: J, appIsAlive: S, openModal: z, openPopup: A, closeWindow: C, resizeWindow: F, appStateChanged: K, getSectionUrl: M, getStyleDataToPassIntoApp: T, postCountersReport: U, getExternalId: V, tpaWorker: {siteInfo: n, getSitePages: r, removeEventListener: J, registerEventListener: I, smCurrentMember: P, appIsAlive: S, navigateToSectionPage: t, getViewMode: W, getDeviceType: W, getLocale: W, getInstanceId: W, getIpAndPort: W}, toWixDate: W, getViewMode: W, getCompId: W, getOrigCompId: W, getWidth: W, getLocale: W, getCacheKiller: W, getTarget: W, getInstanceId: W, getSignDate: W, getUid: W, getPermissions: W, getIpAndPort: W, getDemoMode: W, getDeviceType: W, getImageUrl: W, getResizedImageUrl: W, getAudioUrl: W, getDocumentUrl: W, getSwfUrl: W}
}), define("tpa/handlers/tpaPubSubHandlers", ["tpa/utils/tpaUtils", "utils", "tpa/bi/errors"], function (a, b, c) {
    "use strict";
    var d = b.logger;
    return{registerEventListener: function (a, b) {
        a.getSiteAspect("tpaPubSubAspect").subscribe(b)
    }, publish: function (b, e) {
        var f = e.data, g = e.compId, h = a.getAppDefId(b, g);
        h ? (f.eventKey = a.stripPubSubPrefix(f.eventKey), b.getSiteAspect("tpaPubSubAspect").publish(h, g, f)) : d.reportBI(b.getSiteData(), c.SDK_PUBSUB_PUBLISH_ERROR)
    }, removeEventListener: function (b, c) {
        var d = c.compId, e = c.data, f = a.getAppDefId(b, d), g = a.stripPubSubPrefix(e.eventKey);
        b.getSiteAspect("tpaPubSubAspect").unsubscribe(f, d, g)
    }}
}), define("tpa/aspects/TPAPostMessageAspect", ["lodash", "tpa/common/tpaPostMessageCommon", "tpa/handlers/tpaHandlers", "tpa/handlers/tpaPubSubHandlers", "tpa/utils/tpaUtils", "utils"], function (a, b, c, d, e, f) {
    "use strict";
    var g = function (b) {
        return a.has(b, "data") && !a.isNull(b.data) ? b.data.eventKey && f.stringUtils.startsWith(b.data.eventKey, e.Constants.TPA_PUB_SUB_PREFIX) : void 0
    }, h = function (a) {
        return g(a) ? d : j(a.compId) ? c.tpaWorker : c
    }, i = function (a, c, d) {
        if (k(a, c.compId, c.type)) {
            var e = b.fixOldPingPongMessageType(c.type), f = h(c);
            f[e] && f[e].apply(this, [a, c, d])
        }
    }, j = function (a) {
        return a && f.stringUtils.startsWith(a, "tpaWorker_")
    }, k = function (a, b, c) {
        var d = b && a.getComponentById(b);
        if (d && "masterPage" === d.props.pageId || "appStateChanged" !== c)return!0;
        var e = a.getCurrentPageId();
        return d && d.props.pageId === e
    }, l = function (a) {
        a.registerToMessage(this.handleTPAMessage.bind(this)), this._siteAPI = a
    };
    return l.prototype = {handleTPAMessage: function (a) {
        b.handleTPAMessage.call(this, void 0, this._siteAPI, i, a)
    }, sendPostMessage: function (a, c) {
        var d = a.getIframe();
        if (!d)throw new Error("No iframe found in TPA component", a);
        b.callPostMessage(d, c)
    }, callHandler: i}, l
}), define("tpa/mixins/tpaRuntimeCompMixin", [], function () {
    "use strict";
    return{getOrigComponentId: function () {
        return this.props.compData.origCompId
    }}
}), define("tpa/components/tpaPopup", ["lodash", "zepto", "skins", "react", "core", "utils", "tpa/mixins/tpaUrlBuilderMixin", "tpa/mixins/tpaCompApiMixin", "tpa/mixins/tpaRuntimeCompMixin", "tpa/mixins/tpaResizeWindowMixin"], function (a, b, c, d, e, f, g, h, i, j) {
    "use strict";
    var k = e.compFactory, l = e.compMixins, m = {FIXED: "FIXED", ABSOLUTE: "ABSOLUTE", RELATIVE: "RELATIVE", DEFAULT: "DEFAULT"}, n = {bottomCenter: "BOTTOM_CENTER", bottomLeft: "BOTTOM_LEFT", bottomRight: "BOTTOM_RIGHT", center: "CENTER", centerLeft: "CENTER_LEFT", centerRight: "CENTER_RIGHT", topCenter: "TOP_CENTER", topLeft: "TOP_LEFT", topRight: "TOP_RIGHT"}, o = function (a, b, c) {
        return a + b / 2 - c / 2
    }, p = function (a, b, c) {
        return a - (b + c)
    }, q = function (a, b, c) {
        return a - (b + c)
    }, r = function (a, b, c) {
        return a + b / 2 - c / 2
    }, s = function (b) {
        function c(a) {
            return/(%)$/.exec(a)
        }

        function d(a) {
            return/^([0-9]+)/.exec(a)
        }

        var e = 0, f = "";
        if (a.isNumber(b))e = b; else if (a.isString(b)) {
            var g = c(b), h = d(b);
            e = h && h[1] ? parseInt(h[1], 10) : 0, g && g[1] && (f = g[1])
        }
        return{size: e, unit: f}
    }, t = function (b, c, d, e) {
        var f = {position: "fixed", display: "block", width: d, height: e};
        return e = s(e), e.size = "%" === e.unit ? a.min([e.size, 100]) : a.min([e.size, c.height]), d = s(d), d.size = "%" === d.unit ? a.min([d.size, 100]) : a.min([d.size, c.width]), b.placement === n.center ? a.extend(f, {height: e.size + e.unit, width: d.size + d.unit, marginLeft: d.size / -2 + d.unit, marginTop: "%" === e.unit ? 0 : e.size / -2 + e.unit, left: "50%", top: "%" === e.unit ? 0 : "50%"}) : b.placement === n.topLeft ? a.extend(f, {height: e.size + e.unit, width: d.size + d.unit, left: "0px", top: "0px"}) : b.placement === n.topRight ? a.extend(f, {height: e.size + e.unit, width: d.size + d.unit, right: "0px", top: "0px"}) : b.placement === n.topCenter ? a.extend(f, {height: e.size + e.unit, width: d.size + d.unit, marginLeft: d.size / -2 + d.unit, top: "0px", left: "50%"}) : b.placement === n.centerRight ? a.extend(f, {height: e.size + e.unit, width: d.size + d.unit, marginTop: "%" === e.unit ? 0 : e.size / -2 + e.unit, top: "%" === e.unit ? 0 : "50%", right: "0px"}) : b.placement === n.centerLeft ? a.extend(f, {height: e.size + e.unit, width: d.size + d.unit, marginTop: "%" === e.unit ? 0 : e.size / -2 + e.unit, top: "%" === e.unit ? 0 : "50%", left: "0px"}) : b.placement === n.bottomLeft ? a.extend(f, {height: e.size + e.unit, width: d.size + d.unit, bottom: "0px", left: "0px"}) : b.placement === n.bottomRight ? a.extend(f, {height: e.size + e.unit, width: d.size + d.unit, bottom: "0px", right: "0px"}) : b.placement === n.bottomCenter ? a.extend(f, {height: e.size + e.unit, width: d.size + d.unit, marginLeft: d.size / -2 + d.unit, left: "50%", bottom: "0px"}) : f
    }, u = function (b, c, d, e, f) {
        var h, i, g = {position: "absolute", display: "block"};
        return b.placement === n.center ? (e = a.min([e, d.height]), f = a.min([f, d.width]), h = r(c.top, c.height, e), i = o(c.left, c.width, f)) : b.placement === n.topLeft ? (e = a.min([e, c.top]), f = a.min([f, c.left]), h = c.top - e, i = c.left - f) : b.placement === n.topRight ? (e = a.min([e, c.top]), f = a.min([f, q(d.width, c.width, c.left)]), h = c.top - e, i = c.width + c.left) : b.placement === n.topCenter ? (e = a.min([e, c.top]), f = a.min([f, d.width]), h = c.top - e, i = o(c.left, c.width, f)) : b.placement === n.centerRight ? (e = a.min([e, d.height]), f = a.min([f, q(d.width, c.width, c.left)]), h = r(c.top, c.height, e), i = c.width + c.left) : b.placement === n.centerLeft ? (e = a.min([e, d.height]), f = a.min([f, c.left]), h = r(c.top, c.height, e), i = c.left - f) : b.placement === n.bottomLeft ? (e = a.min([e, p(d.height, c.top, c.height)]), f = a.min([f, c.left]), h = c.top + c.height, i = c.left - f) : b.placement === n.bottomRight ? (e = a.min([e, p(d.height, c.top, c.height)]), f = a.min([f, q(d.width, c.width, c.left)]), h = c.top + c.height, i = c.width + c.left) : b.placement === n.bottomCenter && (e = a.min([e, p(d.height, c.top, c.height)]), f = a.min([f, d.width]), h = c.top + c.height, i = o(c.left, c.width, f)), h = a.max([0, h]), i = a.max([0, i]), a.extend(g, {top: h, left: i, width: f, height: e})
    }, v = function (b, c, d, e, f) {
        var h, i, j, k, g = {position: "absolute", display: "block"};
        return b.placement === n.center ? (j = a.min([c.top + b.y, d.height - (c.top + b.y)]), e = a.min([e, 2 * j]), k = a.min([c.left + b.x, d.width - (c.left + b.x)]), f = a.min([f, 2 * k]), h = c.top + b.y - e / 2, i = c.left + b.x - f / 2) : b.placement === n.topLeft ? (e = a.min([e, c.top + b.y]), f = a.min([f, c.left + b.x]), h = c.top + b.y - e, i = c.left + b.x - f) : b.placement === n.topRight ? (e = a.min([e, c.top + b.y]), f = a.min([f, d.width - (c.left + b.x)]), h = c.top + b.y - e, i = c.left + b.x) : b.placement === n.topCenter ? (e = a.min([e, c.top + b.y]), k = a.min([c.left + b.x, d.width - (c.left + b.x)]), f = a.min([f, 2 * k]), h = c.top + b.y - e, i = c.left + b.x - f / 2) : b.placement === n.centerRight ? (j = a.min([c.top + b.y, d.height - (c.top + b.y)]), e = a.min([e, 2 * j]), f = a.min([f, d.width - (c.left + b.x)]), h = c.top + b.y - e / 2, i = c.left + b.x) : b.placement === n.centerLeft ? (j = a.min([c.top + b.y, d.height - (c.top + b.y)]), e = a.min([e, 2 * j]), f = a.min([f, c.left + b.x]), h = c.top + b.y - e / 2, i = c.left + b.x - f) : b.placement === n.bottomLeft ? (e = a.min([e, d.height - (c.top + b.y)]), f = a.min([f, c.left + b.x]), h = c.top + b.y, i = c.left + b.x - f) : b.placement === n.bottomRight ? (e = a.min([e, d.height - (c.top + b.y)]), f = a.min([f, d.width - (c.left + b.x)]), h = c.top + b.y, i = c.left + b.x) : b.placement === n.bottomCenter && (e = a.min([e, d.height - (c.top + b.y)]), k = a.min([c.left + b.x, d.width - (c.left + b.x)]), f = a.min([f, 2 * k]), h = c.top + b.y, i = c.left + b.x - f / 2), h = a.max([0, h]), i = a.max([0, i]), a.extend(g, {top: h, left: i, width: f, height: e})
    }, w = function (b, c, d, e) {
        var f = 10;
        if (e.width < f || e.height < f) {
            var g = s(d);
            g.size = "%" === g.unit ? a.min([g.size, 100]) : a.min([g.size, b.height]);
            var h = s(c);
            return h.size = "%" === h.unit ? a.min([h.size, 100]) : a.min([h.size, b.width]), {position: "fixed", display: "block", width: h.size + g.unit, height: g.size + h.unit, marginLeft: h.size / -2 + g.unit, marginTop: g.size / -2 + h.unit, left: "50%", top: "50%"}
        }
        return e
    }, x = function (a, b, c, d, e) {
        var f = {};
        return(a.origin === m.DEFAULT || a.origin === m.FIXED) && (f = t(a, c, d, e)), a.origin === m.RELATIVE && (f = u(a, b, c, e, d)), a.origin === m.ABSOLUTE && (f = v(a, b, c, e, d)), f = w(c, d, e, f)
    }, y = function (a) {
        var b = a.getSiteAspect("tpaModalAspect").getReactComponents(), c = b && b.length > 0;
        return c ? 1001 : null
    }, z = {displayName: "TPAPopup", mixins: [l.skinBasedComp, g, h, i, j], getInitialState: function () {
        return{showComponent: !0, registeredEvents: [], $displayDevice: this.getDeviceType()}
    }, getSkinProperties: function () {
        var a = this.state.showComponent ? this.getSelfStyle() : {display: "none"}, b = this.isBareTheme() ? "none" : "block";
        return this.state.showComponent ? {"": {style: a}, closeButton: {onClick: this.hide, style: {display: b}}, iframe: {src: this.buildUrl(this.props.compData.url), scrolling: "no", frameBorder: "0", allowTransparency: !0, allowFullScreen: !0}} : {"": {style: a}}
    }, mutateIframeUrlQueryParam: function (a) {
        return a.origCompId = this.props.compData.origCompId, a
    }, getSelfStyle: function () {
        var b = a.defaults(this.props.compData.position, {x: 0, y: 0}), c = this.props.compData.origCompStyle, d = this.props.compData.windowSize, e = a.isUndefined(this.state.width) ? this.props.compData.width : this.state.width, f = a.isUndefined(this.state.height) ? this.props.compData.height : this.state.height, g = x(b, c, d, e, f);
        return g.zIndex = y(this.props.siteAPI), this.getThemeStyle(g)
    }, getThemeStyle: function (b) {
        return this.isBareTheme() ? a.merge(b, {background: "none", boxShadow: "none", borderRadius: 0}) : b
    }, hide: function (b, c) {
        var d = this;
        e.compFactory.invalidate("wysiwyg.viewer.components.tpapps.TPAPopup"), this.setState({showComponent: !1}, function () {
            var e = b && b.message ? b : void 0;
            d.props.onCloseCallback && d.props.onCloseCallback(e), this.props.siteAPI.getSiteAspect("tpaPopupAspect").removePopup(d), a.isFunction(c) && c()
        })
    }, isBareTheme: function () {
        return"BARE" === this.props.compData.theme
    }};
    return k.register("wysiwyg.viewer.components.tpapps.TPAPopup", z), z
}), define("tpa/aspects/TPAPopupAspect", ["lodash", "core", "utils", "tpa/components/tpaPopup"], function (a, b, c, d) {
    "use strict";
    function e() {
        return{componentType: "wysiwyg.viewer.components.tpapps.TPAPopup", type: "Component", id: c.guidUtils.getUniqueId(), skin: "wysiwyg.viewer.skins.TPAPopupSkin", styleId: ""}
    }

    function f(a, c, d, e, f) {
        var g = b.componentPropsBuilder.getCompProps(a, c, null, d);
        return g.compData = e, g.onCloseCallback = f, b.compFactory.getCompClass(a.componentType)(g)
    }

    function g(a) {
        this.aspectSiteApi = a, this.shouldShowPopup = !1, this.tpaPopup = [], this.aspectSiteApi.registerToPageChange(this.removeAllPopups.bind(this))
    }

    return g.prototype = {getComponentStructures: function () {
        return this.shouldShowPopup ? [e()] : null
    }, getReactComponents: function (a) {
        var b;
        if (this.shouldShowPopup) {
            if (this.shouldShowNewPopup) {
                this.shouldShowNewPopup = !1;
                var c = e();
                b = f(c, this.aspectSiteApi.getSiteAPI(), a, this.popupData, this.popupOnClose), this.tpaPopup.push(b)
            }
            return this.tpaPopup
        }
        return null
    }, showPopup: function (a, b) {
        this.shouldShowPopup = !0, this.shouldShowNewPopup = !0, this.popupData = a, this.popupOnClose = b, this.aspectSiteApi.forceUpdate()
    }, removePopup: function (b) {
        var c = b.props.id, d = this.tpaPopup;
        this.tpaPopup = a.reject(d, function (a) {
            return a.props.id === c
        })
    }, removeAllPopups: function () {
        a.isEmpty(this.tpaPopup) || (this.tpaPopup = [], this.shouldShowPopup = !1, this.aspectSiteApi.forceUpdate())
    }}, g
}), define("tpa/classes/PubSubHub", ["lodash"], function (a) {
    "use strict";
    var b = function (a) {
        this.hub = a || {}
    };
    return b.prototype = {persistData: function (a, b, c, d) {
        var e = {data: d, name: b, origin: c}, f = this._addEvent(a, b);
        f.data.push(e)
    }, addEventListener: function (b, c, d) {
        var e = this._addEvent(b, c);
        a.contains(e.listeners, d) || e.listeners.push(d)
    }, removeEventListener: function (b, c, d) {
        var e = this._getEvent(b, c);
        e && (e.listeners = a.without(e.listeners, d))
    }, getPersistedData: function (a, b) {
        var c = this._getEvent(a, b);
        return c && c.data
    }, getEventListeners: function (a, b) {
        var c = this._getEvent(a, b);
        return c && c.listeners
    }, _addEvent: function (a, b) {
        var c = this.hub[a];
        c || (c = this.hub[a] = {});
        var d = c[b];
        return d || (d = c[b] = {data: [], listeners: []}), d
    }, _getEvent: function (a, b) {
        var c = this.hub[a];
        return c ? c[b] : void 0
    }}, b
}), define("tpa/aspects/TPAPubSubAspect", ["lodash", "tpa/utils/tpaUtils", "tpa/classes/PubSubHub"], function (a, b, c) {
    "use strict";
    var d = function (a) {
        this.aspectSiteAPI = a, this.hub = new c
    };
    return d.prototype = {publish: function (c, d, e) {
        var f = e.eventKey, g = e.isPersistent, h = this.hub.getEventListeners(c, f), i = {eventType: b.addPubSubEventPrefix(f), intent: "addEventListener", params: {data: e.eventData, name: f, origin: d}};
        if (a.forEach(h, function (a) {
            this._sendDataToComp(a, i)
        }.bind(this)), g) {
            var j = e.eventData;
            this.hub.persistData(c, f, d, j)
        }
    }, subscribe: function (c) {
        var d = c.compId, e = c.data, f = b.stripPubSubPrefix(e.eventKey), g = e.receivePastEvents, h = b.getAppDefId(this.aspectSiteAPI, d);
        if (this.hub.addEventListener(h, f, d), g) {
            var i = this.hub.getPersistedData(h, f);
            if (!a.isEmpty(i)) {
                var j = {intent: "TPA_RESPONSE", compId: d, callId: c.callId, type: c.type, status: !0, res: {drain: !0, data: i}};
                this._sendDataToComp(d, j)
            }
        }
    }, unsubscribe: function (a, b, c) {
        var d = this.hub.getEventListeners(a, c);
        d && this.hub.removeEventListener(a, c, b)
    }, _sendDataToComp: function (a, b) {
        var c = this.aspectSiteAPI.getComponentById(a);
        c && c.sendPostMessage(b)
    }}, d
}), define("tpa/aspects/TPAPixelTrackerAspect", ["lodash", "utils", "tpa/common/TPABaseUrlBuilder"], function (a, b, c) {
    "use strict";
    function l(a) {
        f = a, f.registerToComponentDidMount(k), f.registerToPageChange(k)
    }

    var f, d = b.urlUtils, e = b.throttleUtils.throttledForEach, g = !1, h = function (b) {
        return a.filter(b, function (b) {
            return a.isString(b.pixelUrl) && b.permissions && !b.permissions.revoked
        })
    }, i = function () {
        return"success" === f.getSiteData().dynamicModelState
    }, j = function (a) {
        new Image(0, 0).src = a
    }, k = function () {
        if (i()) {
            var b = f.getPageUrl.bind(f), j = h(f.getSiteData().getClientSpecMap()), k = a.map(j, function (a) {
                return new c(a.pixelUrl).addMultipleQueryParams({instance: a.instance, page: b(), ck: d.cacheKiller()}).build()
            });
            e(k, l.sendRequest, l.CHUNK_SIZE, l.CHUNK_INTERVAL)
        } else g = !0
    };
    return l.prototype = {getReactComponents: function () {
        return g && i() && (k(), g = !1), null
    }}, l.sendRequest = j, l.CHUNK_SIZE = 1, l.CHUNK_INTERVAL = 100, l
}), define("tpa/aspects/TPAPageNavigationAspect", ["lodash"], function (a) {
    "use strict";
    var b = function (a, b, c, d) {
        return"PAGE_NAVIGATION" === a.type || "PAGE_NAVIGATION_IN" === a.type && d === b || "PAGE_NAVIGATION_OUT" === a.type && d === c
    }, c = function (a, b) {
        this._listeners = {}, this._siteAPI = a, this._currentPageId = b || this._siteAPI.getCurrentPageId(), this._siteAPI.registerToPageChange(this.notifyPageChanged.bind(this))
    };
    return c.prototype = {notifyPageChanged: function () {
        var c = this._siteAPI.getCurrentPageId(), d = this._currentPageId;
        a.forEach(this._listeners, function (e) {
            a.forEach(e, function (a) {
                var e = a.comp.props.pageId, f = a.comp.isMounted();
                f && b(a, c, d, e) && a.comp.sendPostMessage({intent: "addEventListener", eventType: a.type, params: {toPage: c, fromPage: d, isAppOnPage: e === c, wasAppOnPage: e === d}})
            })
        }), this._currentPageId = c
    }, registerToPageChanged: function (a, b) {
        var c = this._listeners[a.props.id];
        c || (c = this._listeners[a.props.id] = []), c.push({comp: a, type: b})
    }, unregisterToPageChanged: function (a) {
        delete this._listeners[a.props.id]
    }}, c
}), define("tpa/mixins/tpaBlockOuterScrollMixin", [], function () {
    "use strict";
    var a = function (a) {
        var b = a.currentTarget, c = a.currentTarget.scrollHeight, d = c - a.currentTarget.offsetHeight, e = a.deltaY % 3 ? a.deltaY : 10 * a.deltaY;
        b.scrollTop + e <= 0 ? (b.scrollTop = 0, a.preventDefault()) : b.scrollTop + e >= d && (b.scrollTop = d, a.preventDefault()), a.stopPropagation()
    };
    return{blockOuterScroll: a}
}), define("tpa/components/tpaModal", ["lodash", "zepto", "skins", "react", "core", "utils", "tpa/mixins/tpaUrlBuilderMixin", "tpa/mixins/tpaCompApiMixin", "tpa/mixins/tpaRuntimeCompMixin", "tpa/mixins/tpaResizeWindowMixin", "tpa/mixins/tpaBlockOuterScrollMixin"], function (a, b, c, d, e, f, g, h, i, j, k) {
    "use strict";
    var l = e.compFactory, m = e.compMixins, n = {displayName: "TPAModal", mixins: [m.skinBasedComp, g, h, i, j, k], getInitialState: function () {
        return this.props.siteAPI.getSiteAspect("windowResizeEvent").registerToResize(this), {showComponent: !0, registeredEvents: [], $displayDevice: this.getDeviceType(), windowSize: this.props.compData.windowSize}
    }, getSkinProperties: function () {
        var a = this;
        "mobile" === this.state.$displayDevice && this.props.siteAPI.enterFullScreenMode();
        var b = this.state.showComponent ? {position: "fixed", display: "block"} : {display: "none"}, c = this.isBareTheme() ? "none" : "block";
        return this.state.showComponent ? {"": {style: b, onWheel: this.blockOuterScroll}, blockingLayer: {onClick: function (b) {
            a.hide({}, b)
        }}, frameWrap: {style: this.isBareTheme() ? {background: "transparent", border: "none"} : this.getIframeWrapperStyle()}, dialog: {style: this.getDialogStyle()}, xButton: {children: "Г—", onClick: this.hide, style: {display: c}}, iframe: {src: this.buildUrl(this.props.compData.url), frameBorder: "0", allowTransparency: !0, allowFullScreen: !0}} : {"": {style: b}}
    }, getIframeWrapperStyle: function () {
        var a = this.props.siteData.isMobileView(), b = {};
        return a && (b = {WebkitOverflowScrolling: "touch", overflowY: "scroll"}), b
    }, getDialogStyle: function () {
        if ("mobile" === this.getDeviceType())return{};
        var b = this.state.windowSize, c = a.isNumber(this.state.width) ? this.state.width : this.props.compData.width, d = a.isNumber(this.state.height) ? this.state.height : this.props.compData.height;
        return c = Math.min(c, b.width), d = Math.min(d, b.height), {width: c, height: d, marginTop: -d / 2, marginLeft: -c / 2}
    }, mutateIframeUrlQueryParam: function (a) {
        return a.origCompId = this.props.compData.origCompId, a
    }, hide: function (b, c) {
        e.compFactory.invalidate("wysiwyg.viewer.components.tpapps.TPAModal");
        var d = this;
        this.setState({showComponent: !1}, function () {
            var e = b && b.message ? b : void 0;
            d.props.onCloseCallback && d.props.onCloseCallback(e), "mobile" === d.state.$displayDevice && d.props.siteAPI.exitFullScreenMode(), d.props.siteAPI.getSiteAspect("windowResizeEvent").unregisterToResize(d), d.props.siteAPI.getSiteAspect("tpaModalAspect").removeModal(d), a.isFunction(c) && c()
        })
    }, isBareTheme: function () {
        return"BARE" === this.props.compData.theme
    }, onResize: function () {
        this.setState({windowSize: {width: b(window).width(), height: b(window).height()}})
    }};
    return l.register("wysiwyg.viewer.components.tpapps.TPAModal", n), n
}), define("tpa/aspects/TPAModalAspect", ["lodash", "core", "utils", "tpa/components/tpaModal"], function (a, b, c, d) {
    "use strict";
    function e() {
        return{componentType: "wysiwyg.viewer.components.tpapps.TPAModal", type: "Component", id: c.guidUtils.getUniqueId(), skin: "wysiwyg.viewer.skins.TPAModalSkin"}
    }

    function f(a, c, d, e, f) {
        var g = b.componentPropsBuilder.getCompProps(a, c, null, d);
        return g.compData = e, g.onCloseCallback = f, g.key = a.id, b.compFactory.getCompClass(a.componentType)(g)
    }

    function g(a) {
        this.aspectSiteApi = a, this.modalStructure = null
    }

    return g.prototype = {getComponentStructures: function () {
        return this.modalStructure ? [this.modalStructure] : null
    }, getReactComponents: function (a) {
        return this.modalStructure ? [f(this.modalStructure, this.aspectSiteApi.getSiteAPI(), a, this.modalData, this.modalOnClose)] : null
    }, showModal: function (a, b) {
        this.modalStructure = e(), this.modalData = a, this.modalOnClose = b, this.aspectSiteApi.forceUpdate()
    }, removeModal: function (a) {
        this.aspectSiteApi.getSiteData().isMobileView() && this.aspectSiteApi.exitFullScreenMode(), this.modalStructure = null, this.aspectSiteApi.forceUpdate()
    }}, g
}), define("tpa/aspects/tpaAspectCollector", ["core", "tpa/aspects/TPAWorkerAspect", "tpa/aspects/TPAPostMessageAspect", "tpa/aspects/TPAPopupAspect", "tpa/aspects/TPAPubSubAspect", "tpa/aspects/TPAPixelTrackerAspect", "tpa/aspects/TPAPageNavigationAspect", "tpa/aspects/TPAModalAspect"], function (a) {
    "use strict";
    var b = a.siteAspectsRegistry;
    b.registerSiteAspect("tpaWorkerAspect", arguments[1]), b.registerSiteAspect("tpaPostMessageAspect", arguments[2]), b.registerSiteAspect("tpaPopupAspect", arguments[3]), b.registerSiteAspect("tpaPubSubAspect", arguments[4]), b.registerSiteAspect("tpaPixelTrackerAspect", arguments[5]), b.registerSiteAspect("tpaPageNavigationAspect", arguments[6]), b.registerSiteAspect("tpaModalAspect", arguments[7])
}), define("tpa/layout/gluedWidgetPatcher", ["lodash", "zepto"], function (a, b) {
    "use strict";
    function d(b, d, e) {
        var f = {};
        return a.forEach(e, function (e) {
            var g = d[e] && d[e][b], h = e;
            a.isUndefined(g) || (0 === e.indexOf(c) && (h = h.slice(c.length).toLowerCase()), f[h] = g)
        }), f
    }

    function e(b, c, d) {
        return{top: a.isNumber(b.top) ? a.parseInt(b.top) : d - b.height, bottom: a.isNumber(b.bottom) ? d - a.parseInt(b.bottom) : a.parseInt(b.top) + b.height, right: a.isNumber(b.right) ? c - a.parseInt(b.right) : a.parseInt(b.left) + b.width, left: a.isNumber(b.left) ? a.parseInt(b.left) : c - b.width}
    }

    function f(a, b) {
        return!(a.right < b.left || a.left > b.right || a.bottom < b.top || a.top > b.bottom)
    }

    function g(b, c, d) {
        return f(d, c) && a.isNumber(b.top) && (b.top = (b.top || 0) + (d.bottom - c.top)), b
    }

    function h(a, b, c) {
        var d = a, f = b.height.screen, h = b.width.screen, i = b.custom && b.custom[c.WIX_ADS_ID] && b.custom[c.WIX_ADS_ID].topAd;
        if (i) {
            var j = e(d, h, f);
            d = g(d, j, i)
        }
        return d
    }

    function i(c, e, f, g, i) {
        var j = ["position", "fixedTop", "fixedLeft", "right", "bottom", "width", "height"], k = d(c, f, j);
        k = h(k, f, i), k = a.omit(k, "width", "height"), b(e[c]).css(k)
    }

    function j() {
        this.patchGluedWidget = i
    }

    var c = "fixed";
    return j
}), define("tpa/layout/tpaMeasurer", ["zepto"], function (a) {
    "use strict";
    return{measureTPA: function (b, c, d, e, f) {
        var g = a(d[b]), h = parseInt(g.css("min-height"), 10), i = parseInt(g.css("min-width"), 10), j = g.attr("data-ignore-anchors");
        h && "true" !== j && (c.minHeight[b] = h, c.height[b] = h), h && "true" === j && (c.minHeight[b] = f.structure.layout.height, c.height[b] = f.structure.layout.height), i && (c.minWidth[b] = i, c.width[b] = i)
    }}
}), define("tpa/layout/tpaGluedWidgetPlacement", [], function () {
    "use strict";
    function a(a, b) {
        var c = a.applicationId, d = a.widgetId, e = b[c];
        if (e) {
            var f = e.widgets[d];
            if (f) {
                var g = f.gluedOptions || {horizontalMargin: 0, placement: "BOTTOM_RIGHT", verticalMargin: 0};
                return g.placement
            }
        }
    }

    return{getDefaultPlacement: a}
}), define("tpa/utils/gluedWidgetMeasuringUtils", ["lodash", "tpa/layout/tpaGluedWidgetPlacement"], function (a, b) {
    "use strict";
    function e(a) {
        return"number" == typeof a ? a : a ? parseFloat(a) || 0 : 0
    }

    function f(b, f, g, h, i, j, k) {
        function n() {
            if (j >= -1 && 1 >= j) {
                var a = h / 2 - f / 2, b = h - 2 * d, c = b / 2 - f / 2, e = j * c;
                l.top = a + e
            } else if (-1 > j && j >= -2)m = 2 + j, l.top = m * d; else if (j > 1 && 2 >= j) {
                m = 2 - j;
                var g = Math.floor(m * d);
                l.top = h - g - f
            }
        }

        function o() {
            if (k >= -1 && 1 >= k) {
                var a = g / 2 - b / 2, d = g - 2 * c, e = d / 2 - b / 2, f = k * e;
                l.left = a + f
            } else if (k > 1 && 2 >= k) {
                m = 2 - k;
                var h = Math.floor(m * c);
                l.left = g - h - b
            } else-1 > k && k >= -2 && (m = 2 + k, l.left = m * c)
        }

        j = e(j), k = e(k);
        var m, l = {};
        switch (i) {
            case"TOP_LEFT":
                l.top = 0, l.left = 0, l.bottom = "auto";
                break;
            case"TOP_RIGHT":
                l.top = 0, l.right = 0, l.bottom = "auto";
                break;
            case"TOP_CENTER":
                l.top = 0, l.bottom = "auto", o();
                break;
            case"CENTER_RIGHT":
                l.right = 0, n();
                break;
            case"CENTER_LEFT":
                l.left = 0, n();
                break;
            case"BOTTOM_LEFT":
                l.bottom = 0, l.left = 0, l.top = "auto";
                break;
            case"BOTTOM_CENTER":
                l.bottom = 0, l.top = "auto", o();
                break;
            case"BOTTOM_RIGHT":
            default:
                l.bottom = 0, l.right = 0, l.top = "auto"
        }
        return a.defaults(l, {position: "fixed", top: "", left: ""})
    }

    function g(b, c) {
        var d = {};
        return c && c > 0 && a.isNumber(b.bottom) && b.bottom < c && (d.bottom = c), d
    }

    function h(a) {
        return(a > 2 || -2 > a) && (a = 0), a
    }

    function i(c, d, e, i, j) {
        var k = h(d.props.horizontalMargin), l = h(d.props.verticalMargin), m = d.props.placement || b.getDefaultPlacement(d.data, c), n = f(d.layout.width, d.layout.height, e, i, m, l, k), o = g(n, j || 0), p = a.assign(n, o);
        return 0 === p.right && (p.left = e - d.layout.width), 0 === p.bottom && (p.top = i - d.layout.height - (j || 0)), p
    }

    var c = 300, d = 120;
    return{getGluedWidgetMeasurements: i}
}), define("tpa/layout/gluedWidgetMeasurer", ["lodash", "zepto", "tpa/layout/tpaMeasurer", "tpa/utils/gluedWidgetMeasuringUtils"], function (a, b, c, d) {
    "use strict";
    function e(a, b, c, d) {
        var e = d.isMobileView() ? "mobileWADTop" : "desktopWADTop", f = a + e, g = c[a].querySelector("#" + f);
        g && (b.custom[a] = {topAd: g.getBoundingClientRect()})
    }

    function f(a) {
        return 0 === a.right && (a.left = "auto"), 0 === a.bottom && (a.top = "auto"), 0 === a.left && (a.right = "auto"), 0 === a.top && (a.bottom = "auto"), a
    }

    function g(e, g, h, i, j, k) {
        var l = k.propertiesItem, m = k.dataItem, n = a.assign(k.layout, {height: b(i[g]).height(), width: b(i[g]).width()}), o = h.width.screen, p = h.height.screen;
        l.placement = l.placement || e(m, j);
        var q = {props: l, data: m, layout: n}, r = j.rendererModel.clientSpecMap, s = d.getGluedWidgetMeasurements(r, q, o, p, h.siteMarginBottom);
        s = f(s), s.fixedTop = s.top, s.fixedLeft = s.left, delete s.top, delete s.left, a.forEach(s, function (a, b) {
            h[b] = h[b] || {}, h[b][g] = a
        }), h.width[g] = n.width, c.measureTPA(g, h, i)
    }

    function h(a) {
        function b(b, c) {
            try {
                return a.getDefaultPlacement(b, c)
            } catch (d) {
                return""
            }
        }

        this.measureWixAdComponent = e, this.measureGluedWidget = g.bind(this, b)
    }

    return h
}), define("tpa/layout/tpaSectionPatcher", ["zepto"], function (a) {
    "use strict";
    return{patchTPASection: function (b, c, d, e, f) {
        var g = d.height[b], h = d.width[b], i = c[b + "iframe"], j = f.os.ios && f.browser.safari;
        j && i && a(i).css({width: h, height: g})
    }}
}), define("tpa/layout/mobileSafariPatcher", ["zepto"], function (a) {
    "use strict";
    return{patchWidth: function (b, c, d, e, f) {
        var g = a(c[b]).find("iframe"), h = f.os.ios && f.browser.safari;
        h && g && a(g).css({width: 1, minWidth: "100%"})
    }}
}), define("tpa/layout/tpaLayout", ["layout", "tpa/layout/gluedWidgetPatcher", "tpa/layout/gluedWidgetMeasurer", "tpa/layout/tpaSectionPatcher", "tpa/layout/tpaMeasurer", "tpa/layout/tpaGluedWidgetPlacement", "tpa/layout/mobileSafariPatcher"], function (a, b, c, d, e, f, g) {
    "use strict";
    var h = new b(f), i = new c(f);
    return a.registerCustomMeasure("wysiwyg.viewer.components.tpapps.TPAWidget", e.measureTPA), a.registerCustomMeasure("wysiwyg.viewer.components.tpapps.TPASection", e.measureTPA), a.registerCustomMeasure("wysiwyg.viewer.components.tpapps.TPAMultiSection", e.measureTPA), a.registerCustomMeasure("wysiwyg.viewer.components.tpapps.TPAGluedWidget", i.measureGluedWidget), a.registerRequestToMeasureDom("wysiwyg.viewer.components.tpapps.TPAWidget"), a.registerRequestToMeasureDom("wysiwyg.viewer.components.tpapps.TPAGluedWidget"), a.registerRequestToMeasureDom("wysiwyg.viewer.components.tpapps.TPASection"), a.registerRequestToMeasureDom("wysiwyg.viewer.components.tpapps.TPAMultiSection"), a.registerRequestToMeasureChildren("wysiwyg.viewer.components.tpapps.TPASection", [
        ["iframe"]
    ]), a.registerRequestToMeasureChildren("wysiwyg.viewer.components.tpapps.TPAMultiSection", [
        ["iframe"]
    ]), a.registerPatcher("wysiwyg.viewer.components.tpapps.TPAGluedWidget", h.patchGluedWidget), a.registerPatcher("wysiwyg.viewer.components.tpapps.TPAMultiSection", d.patchTPASection), a.registerPatcher("wysiwyg.viewer.components.tpapps.TPASection", d.patchTPASection), a.registerPatcher("wysiwyg.viewer.components.tpapps.TPAWidget", g.patchWidth), ["wysiwyg.viewer.components.WixAdsMobile", "wysiwyg.viewer.components.WixAdsDesktop"].forEach(function (b) {
        a.registerRequestToMeasureChildren(b, [
            ["desktopWADTop"],
            ["mobileWADTop"]
        ]), a.registerCustomMeasure(b, i.measureWixAdComponent)
    }), {}
}), define("tpa/components/tpaPreloaderOverlay", ["lodash", "skins", "react", "core"], function (a, b, c, d) {
    "use strict";
    var e = d.compFactory, f = d.compMixins, g = {mixins: [f.skinBasedComp], getSkinProperties: function () {
        return{content: {style: {background: "rgba(255, 255, 255, .9) url(" + this.props.siteData.santaBase + "/static/images/tpaPreloader/Loader_29px.gif) no-repeat center 50%"}}}
    }};
    e.register("wysiwyg.viewer.components.tpapps.TPAPreloaderOverlay", g)
}), define("tpa/services/tpaPreviewEditorCommunicationService", ["core"], function (a) {
    "use strict";
    var d, b = [], c = 1, e = a.siteAspectsRegistry, f = function (a) {
        d = a, g()
    };
    e.registerSiteAspect("tpaPreviewEditorAspect", f);
    var g = function () {
        d.registerToMessage(j.bind(this))
    }, h = function (a, b, c, d) {
        var f, e = i(a, b, c, d);
        parent.postMessage ? f = parent : parent.document.postMessage && (f = parent.document), f && "undefined" != typeof f && f.postMessage(JSON.stringify(e), "*")
    }, i = function (a, d, e, f) {
        var g = {intent: "TPA_PREVIEW", callId: c++, type: a, compId: e, data: d};
        return f && (b[g.callId] = f), g
    }, j = function (a) {
        if (a && a.data) {
            var c = {};
            try {
                c = JSON.parse(a.data)
            } catch (d) {
                return
            }
            switch (c.intent) {
                case"TPA_PREVIEW":
                    c.callId && b[c.callId] && (b[c.callId](c.res), delete b[c.callId])
            }
        }
    };
    return{doPostMessage: h}
}), define("tpa/mixins/tpaPreviewEditorMixin", ["tpa/services/tpaPreviewEditorCommunicationService"], function (a) {
    "use strict";
    var b = function (b, c, d) {
        a.doPostMessage("translate", b, c, d)
    }, c = function (b) {
        a.doPostMessage("openHelp", b)
    };
    return{getTranslation: b, openHelp: c}
}), define("tpa/components/tpaUnavailableMessageOverlay", ["lodash", "skins", "react", "core", "tpa/mixins/tpaPreviewEditorMixin"], function (a, b, c, d, e) {
    "use strict";
    var f = d.compFactory, g = d.compMixins, h = {mixins: [g.skinBasedComp, e], getInitialState: function () {
        return{showOverlay: !0}
    }, componentDidMount: function () {
        this.props.siteData.isViewerMode() || this.initializePreviewData()
    }, initializePreviewData: function () {
        var a = function (a) {
            this.setState(a)
        }.bind(this);
        this.getTranslation(["tpa_unavail_problems", "tpa_unavail_problems_2", "tpa_oops"], this.props.id, a)
    }, getSkinProperties: function () {
        var a = {text: {children: [this.props.compData.text]}, dismissButton: {onClick: this.props.compData.hideOverlayFunc}, openHelp: this.openHelp};
        return a
    }};
    f.register("wysiwyg.viewer.components.tpapps.TPAUnavailableMessageOverlay", h)
}), define("tpa", ["tpa/components/tpaWidget", "tpa/components/tpaSection", "tpa/components/tpaMultiSection", "tpa/components/tpaWorker", "tpa/components/tpaGluedWidget", "tpa/aspects/tpaAspectCollector", "tpa/mixins/tpaUrlBuilderMixin", "tpa/mixins/tpaCompApiMixin", "tpa/layout/tpaLayout", "tpa/components/tpaPreloaderOverlay", "tpa/components/tpaUnavailableMessageOverlay", "tpa/layout/gluedWidgetPatcher", "tpa/layout/tpaMeasurer", "tpa/layout/tpaSectionPatcher", "tpa/mixins/tpaCompBaseMixin", "tpa/common/tpaPostMessageCommon", "tpa/handlers/tpaHandlers", "tpa/common/TPAUrlBuilder", "tpa/common/TPABaseUrlBuilder", "tpa/utils/tpaStyleUtils", "tpa/utils/tpaUtils", "tpa/utils/gluedWidgetMeasuringUtils", "tpa/services/tpaPreviewEditorCommunicationService"], function (a, b, c, d, e, f, g, h, i, j, k, l, m, n, o, p, q, r, s, t, u, v, w) {
    "use strict";
    return{widget: a, section: b, multiSection: c, worker: d, gluedWidget: e, gluedWidgetMeasuringUtils: v, tpaMixins: {tpaUrlBuilder: g, tpaCompApi: h, tpaCompBase: o}, GluedWidgetPatcher: l, tpaMeasurer: m, tpaSectionPatcher: n, tpaHandlers: q, common: {tpaPostMessageCommon: p, TPAUrlBuilder: r, styleUtils: t, TPABaseUrlBuilder: s, utils: u
    }, services: {tpaPreviewEditorCommunicationService: w}}
});