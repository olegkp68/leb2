/* Script by: www.jtricks.com
 * Version: 1.5 (20140515)
 * Latest version: www.jtricks.com/javascript/navigation/fixed_n_sticky.html
 *
 * License:
 * GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */
var FixedMenu =
{
    hasInner: typeof(window.innerWidth) == 'number',
    hasElement: typeof(document.documentElement) == 'object'
        && typeof(document.documentElement.clientWidth) == 'number',

    items:
    [
    ]

};

FixedMenu.add = function(obj, options)
{
    var name;
    var menu;

    if (typeof(obj) === "string")
        name = obj;
    else
        menu = obj;
        

    if (options == undefined)
    {
        this.items.push( 
            {
                id: name,
                menu: menu,

                targetLeft: 0,
                targetTop: 0,
                updateParentHeight: false
            });
    }
    else
    {
        this.items.push( 
            {
                id: name,
                menu: menu,

                targetLeft: options.targetLeft,
                targetRight: options.targetRight,
                targetTop: options.targetTop,
                targetBottom: options.targetBottom,

                ignoreParentDimensions: options.ignoreParentDimensions,

                updateParentHeight:
                    options.updateParentHeight == undefined
                    ? false
                    : options.updateParentHeight,

                scrollContainer: options.scrollContainer,
                scrollContainerId: options.scrollContainerId,

                confinementArea: options.confinementArea,

                confinementAreaId:
                    options.confinementArea != undefined
                    && options.confinementArea.substring(0, 1) == '#'
                    ? options.confinementArea.substring(1)
                    : undefined,

                confinementAreaClassRegexp:
                    options.confinementArea != undefined
                    && options.confinementArea.substring(0, 1) == '.'
                    ? new RegExp("(^|\\s)" + options.confinementArea.substring(1) + "(\\s|$)")
                    : undefined,

                mediaStopCheck: options.mediaStopCheck,
                mediaStopCheckId: options.mediaStopCheckId
            });
    }
};

FixedMenu.findSingle = function(item)
{
    if (item.id)
        item.menu = document.getElementById(item.id);

    if (item.scrollContainerId)
        item.scrollContainer = document.getElementById(item.scrollContainerId);

    if (item.mediaStopCheckId)
        item.mediaStopCheck = document.getElementById(item.mediaStopCheckId);
};

FixedMenu.getStyle = function (x, styleProp)
{
    if (x.currentStyle)
        return x.currentStyle[styleProp];
    else 
	return document.defaultView.getComputedStyle(x, null).getPropertyValue(styleProp);
}

FixedMenu.scrollLeft = function(item)
{
    // If floating within scrollable container use it's scrollLeft
    if (item.scrollContainer)
        return item.scrollContainer.scrollLeft;

    var w = window.top;

    return this.hasInner
        ? w.pageXOffset  
        : this.hasElement  
          ? w.document.documentElement.scrollLeft  
          : w.document.body.scrollLeft;
};

FixedMenu.scrollTop = function(item)
{
    // If floating within scrollable container use it's scrollTop
    if (item.scrollContainer)
        return item.scrollContainer.scrollTop;

    var w = window.top;

    return this.hasInner
        ? w.pageYOffset
        : this.hasElement
          ? w.document.documentElement.scrollTop
          : w.document.body.scrollTop;
};

FixedMenu.documentHeight = function()
{
    var innerHeight = this.hasInner
        ? window.innerHeight
        : 0;

    var body = document.body,
        html = document.documentElement;

    return Math.max(
        body.scrollHeight,
        body.offsetHeight, 
        html.scrollHeight,
        html.offsetHeight,
        innerHeight);
};

FixedMenu.documentWidth = function()
{
    var innerWidth = this.hasInner
        ? window.innerWidth
        : 0;

    var body = document.body,
        html = document.documentElement;

    return Math.max(
        body.scrollWidth,
        body.offsetWidth, 
        html.scrollWidth,
        html.offsetWidth,
        innerWidth);
};

FixedMenu.isConfinementArea = function(item, area)
{
    return item.confinementAreaId != undefined
        && area.id == item.confinementAreaId
        || item.confinementAreaClassRegexp != undefined
        && area.className
        && item.confinementAreaClassRegexp.test(area.className);
};

FixedMenu.computeParent = function(item)
{
    if (item.ignoreParentDimensions)
    {
        item.confinedHeightReserve = this.documentHeight();
        item.confinedWidthReserver = this.documentWidth();
        item.parentLeft = 0;  
        item.parentTop = 0;  
        return;
    }

    var parentNode = item.menu.parentNode;
    var parentOffsets = this.offsets(parentNode, item);
    item.parentLeft = parentOffsets.left;
    item.parentTop = parentOffsets.top;

    item.confinedWidthReserve = parentNode.clientWidth;

    // We could have DIV wrapped
    // inside relatively-positioned. Then parent might not
    // have any height. Try to find parent that has
    // and try to find whats left of its height for us.
    var obj = parentNode;
    var objOffsets = this.offsets(obj, item);

    if (item.confinementArea == undefined)
    {
        while (obj.clientHeight + objOffsets.top
                   < item.menu.scrollHeight + parentOffsets.top
               || item.menu.parentNode == obj
               && item.updateParentHeight
               && obj.clientHeight + objOffsets.top
                   == item.menu.scrollHeight + parentOffsets.top)
        {
            obj = obj.parentNode;
            objOffsets = this.offsets(obj, item);
        }
    }
    else
    {
        while (obj.parentNode != undefined
               && !this.isConfinementArea(item, obj))
        {
            obj = obj.parentNode;
            objOffsets = this.offsets(obj, item);
        }
    }

    item.confinedHeightReserve = obj.clientHeight
        - (parentOffsets.top - objOffsets.top);
};

FixedMenu.offsets = function(obj, item)
{
    var result =
    {
        left: 0,
        top: 0
    };

    if (obj === item.scrollContainer)
        return;

    while (obj.offsetParent && obj.offsetParent != item.scrollContainer)
    {  
        result.left += obj.offsetLeft;  
        result.top += obj.offsetTop;  
        obj = obj.offsetParent;
    }  

    if (window == window.top)
        return result;

    // we're IFRAMEd
    var iframes = window.top.document.body.getElementsByTagName("IFRAME");
    for (var i = 0; i < iframes.length; i++)
    {
        if (iframes[i].contentWindow != window)
           continue;

        obj = iframes[i];
        while (obj.offsetParent)  
        {  
            result.left += obj.offsetLeft;  
            result.top += obj.offsetTop;  
            obj = obj.offsetParent;
        }  
    }

    return result;
};

FixedMenu.insertEvent = function(element, event, handler)
{
    // W3C
    if (element.addEventListener != undefined)
    {
        element.addEventListener(event, handler, false);
        return;
    }

    var listener = 'on' + event;

    // MS
    if (element.attachEvent != undefined)
    {
        element.attachEvent(listener, handler);
        return;
    }

    // Fallback
    var oldHandler = element[listener];
    element[listener] = function (e)
        {
            e = (e) ? e : window.event;
            var result = handler(e);
            return (oldHandler != undefined) 
                && (oldHandler(e) == true)
                && (result == true);
        };
};

FixedMenu.applyStyle = function(item, name, value)
{
    if (item.menu.style[name] != value)
    {
        item.menu.style[name] = value;
        return true;
    }
    else
        return false;
};

FixedMenu.fix = function()
{
    for (var i = 0; i < FixedMenu.items.length; i++)
    {
        var item = FixedMenu.items[i];

        FixedMenu.findSingle(item);

        if (item.updateParentHeight)
        {
            item.menu.parentNode.style.minHeight = 
                item.menu.scrollHeight + 'px';
        }

        FixedMenu.computeParent(item);

        var scrollLeft = FixedMenu.scrollLeft(item);
        var scrollTop = FixedMenu.scrollTop(item);

        if (scrollLeft + item.targetLeft <= item.parentLeft 
            && scrollTop + item.targetTop <= item.parentTop
            || item.mediaStopCheck
            && FixedMenu.getStyle(item.mediaStopCheck, 'visibility') == 'hidden')
        {
            FixedMenu.applyStyle(item, 'position', 'static');
            FixedMenu.applyStyle(item, 'left', '');
            FixedMenu.applyStyle(item, 'top', '');
        }
        else
        {
            if (FixedMenu.applyStyle(item, 'position', 'fixed'))
                FixedMenu.computeParent(item);

            var diffLeft = item.parentLeft - scrollLeft;
            var diffTop = item.parentTop - scrollTop;

            var left = (diffLeft > item.targetLeft ? diffLeft : item.targetLeft);
            var top = (diffTop > item.targetTop ? diffTop : item.targetTop);

            if (left + scrollLeft + item.menu.offsetWidth > item.parentLeft + item.confinedWidthReserve)
                left = item.parentLeft + item.confinedWidthReserve - scrollLeft - item.menu.offsetWidth;
            if (top + scrollTop + item.menu.offsetHeight > item.parentTop + item.confinedHeightReserve)
                top = item.parentTop + item.confinedHeightReserve - scrollTop - item.menu.offsetHeight;

            FixedMenu.applyStyle(item, 'left', left + 'px');
            FixedMenu.applyStyle(item, 'top', top + 'px');
        }

        FixedMenu.applyStyle(item, 'width', item.menu.parentNode.offsetWidth + 'px');
    }
};

FixedMenu.insertEvent(window, 'scroll', FixedMenu.fix);
FixedMenu.insertEvent(window, 'resize', FixedMenu.fix);

setInterval(FixedMenu.fix, 1000);

try
{
    if (window.addEvent)
        window.addEvent('domready', FixedMenu.fix);
}
catch (err) {}

FixedMenu.insertEvent(window, 'load', FixedMenu.fix);

// Register ourselves as jQuery plugin if jQuery is present
if (typeof(jQuery) !== 'undefined')
{
    (function ($)
    {
        $.fn.addFixed = function(options)
        {
            return this.each(function()
            {
                FixedMenu.add(this, options);
            });
        };
    }) (jQuery);
}
