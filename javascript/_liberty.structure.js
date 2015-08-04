// Console fix

(function(b){function c(){}for(var d='assert,count,debug,dir,dirxml,error,exception,group,groupCollapsed,groupEnd,info,log,markTimeline,profile,profileEnd,time,timeEnd,trace,warn'.split(','),a;a=d.pop();)b[a]=b[a]||c})(window.console=window.console||{});

// Mousewheel support

(function(a){function d(b){var c=b||window.event,d=[].slice.call(arguments,1),e=0,f=!0,g=0,h=0;return b=a.event.fix(c),b.type="mousewheel",c.wheelDelta&&(e=c.wheelDelta/120),c.detail&&(e=-c.detail/3),h=e,c.axis!==undefined&&c.axis===c.HORIZONTAL_AXIS&&(h=0,g=-1*e),c.wheelDeltaY!==undefined&&(h=c.wheelDeltaY/120),c.wheelDeltaX!==undefined&&(g=-1*c.wheelDeltaX/120),d.unshift(b,e,g,h),(a.event.dispatch||a.event.handle).apply(this,d)}var b=["DOMMouseScroll","mousewheel"];if(a.event.fixHooks)for(var c=b.length;c;)a.event.fixHooks[b[--c]]=a.event.mouseHooks;a.event.special.mousewheel={setup:function(){if(this.addEventListener)for(var a=b.length;a;)this.addEventListener(b[--a],d,!1);else this.onmousewheel=d},teardown:function(){if(this.remove_eventListener)for(var a=b.length;a;)this.remove_eventListener(b[--a],d,!1);else this.onmousewheel=null}},a.fn.extend({mousewheel:function(a){return a?this.bind("mousewheel",a):this.trigger("mousewheel")},unmousewheel:function(a){return this.unbind("mousewheel",a)}})})(jQuery)

// HTML5 fallbacks

if (!('placeholder' in document.createElement('input')))
{
	$('input[placeholder],textarea[placeholder]').each(function() {
		var p = $(this).attr('placeholder');
		$(this)
			.focus(function () { $(this).val() == p  && $(this).val('').css('color', '').addClass('placeholder'); })
			.blur (function () { $(this).val() == '' && $(this).val(p ).css('color', '#aaa').removeClass('placeholder'); })
			.blur ();
	});
}

// Custom link handling

(function ($) {
	$.fn.link = function (address) {
		return this.each(function () {
			$(this)	.mousedown(function (e) { e.which == 2 && e.preventDefault(); })
				.mouseup(function (e) { e.which == 1 && (e.ctrlKey ? open(address) : (location.href = address)) && e.preventDefault() || e.which == 2 && open(address); });
		});
	};
})(jQuery);

var parseLinks = function (s) {
	var f = function (s) {
		$('a[data-link],input[data-link]', s)
		.each(function () { $(this).data('click', $(this).data('link')).removeAttr('data-link'); })
		.click(function (e) {
			e.preventDefault();
			var fn = window['link_' + $(this).data('click')];
			if (typeof(fn) == 'function') fn.call($(this), e); else console.log('Undefined function: ' + $(this).data('click'));
		})
		.filter('a').attr('href', '/').end()
		.filter('[data-click]').click();
	};
	f();
	return f;
}();

function arp(a,k,d) { return a.hasOwnProperty(k)?a[k]:d||''; }
function limit(s,l) { return s.length < (l=l||50) ? s : (s.substr(0, l).replace(/ ?[^ ]*$/i, '') || s.substr(0, l)) + '...'; }
function moneyFormat(v) { var t,l=(v+=[]).length; return $.map(v.split([]), function (a,b) { return a + (!(t=l-b-1) || t%3? '': ','); }).join(''); }

// Make time

if (!Date.now) { Date.now = function() { return new Date().valueOf(); }; }

function mktime() {
	// http://phpjs.org/functions/mktime
	var d = new Date(), r = arguments, i = 0, e = ['Hours', 'Minutes', 'Seconds', 'Month', 'Date', 'FullYear'];
	for (i = 0; i < e.length; i++) {
		if (typeof r[i] === 'undefined') {
			r[i] = d['get' + e[i]]();
			r[i] += (i === 3);
		} else {
			r[i] = parseInt(r[i], 10);
			if (isNaN(r[i])) { return false; }
		}
	}
	r[5] += (r[5] >= 0 ? (r[5] <= 69 ? 2e3 : (r[5] <= 100 ? 1900 : 0)) : 0);
	d.setFullYear(r[5], r[3] - 1, r[4]);
	d.setHours(r[0], r[1], r[2]);
	return (d.getTime() / 1e3 >> 0) - (d.getTime() < 0);
}