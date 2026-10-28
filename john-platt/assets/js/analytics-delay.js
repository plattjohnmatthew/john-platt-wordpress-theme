(() => {
 'use strict';
 const type = 'application/x-john-platt-delayed-analytics';
 const markers = Array.from(document.querySelectorAll(`script[type="${type}"]`));
 if (!markers.length) return;
 const initialLocation = window.location.href;
 const initialTitle = document.title;
 let finished = false, readyAt = Infinity, idle, timer, fallback;
 const consent = () => typeof window.wp_has_consent !== 'function' || window.wp_has_consent('statistics') === true;
 const cleanup = () => {
  clearTimeout(timer); clearTimeout(fallback);
  if (idle !== undefined && window.cancelIdleCallback) window.cancelIdleCallback(idle);
  document.removeEventListener('visibilitychange', start);
  document.removeEventListener('wp_listen_for_consent_change', start);
 };
 function start() {
  if (finished || Date.now() < readyAt || document.visibilityState === 'hidden' || !consent()) return;
  let blocked = false;
  markers.forEach(marker => {
   if (!marker.isConnected || marker.type !== type) return;
   // A consent manager may have annotated the inert tag since initial parsing.
   if (Array.from(marker.attributes).some(attr => attr.name.startsWith('data-') && attr.name !== 'data-jp-analytics-type')) { blocked = true; return; }
   const url = new URL(marker.src);
   const existing = Array.from(document.querySelectorAll('script[src]')).some(script => script !== marker && script.src === marker.src && (!script.type || /^(text|application)\/javascript$/i.test(script.type)));
   if (existing) { marker.remove(); return; }
   // Retain the landing context when a filter changes history during the delay.
   (window.dataLayer || []).forEach(command => {
    if (command && command[0] === 'config' && command[1] === url.searchParams.get('id')) {
     command[2] = Object.assign({page_location: initialLocation, page_title: initialTitle}, command[2] || {});
    }
   });
   const script = document.createElement('script');
   Array.from(marker.attributes).forEach(attr => {
    if (attr.name !== 'type' && attr.name !== 'data-jp-analytics-type' && attr.name !== 'src') script.setAttribute(attr.name, attr.value);
   });
   // Browsers hide CSP nonce attribute values; copy the nonce property explicitly.
   if (marker.nonce) script.nonce = marker.nonce;
   script.type = marker.dataset.jpAnalyticsType || 'text/javascript';
   script.async = true;
   script.fetchPriority = 'low';
   script.src = marker.src;
   marker.replaceWith(script);
  });
  if (!blocked) { finished = true; cleanup(); }
 }
 const schedule = () => {
  // Two finite frames allow the initial DOM/layout to paint. No load-event wait:
  // third-party embeds must not hold analytics indefinitely.
  requestAnimationFrame(() => requestAnimationFrame(() => {
   readyAt = Date.now() + 1000;
   timer = setTimeout(() => {
    if (window.requestIdleCallback) idle = window.requestIdleCallback(start, {timeout: 1000});
    else start();
   }, 1000);
   fallback = setTimeout(start, 2000);
  }));
 };
 document.addEventListener('visibilitychange', start);
 document.addEventListener('wp_listen_for_consent_change', start);
 if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', schedule, {once: true});
 else schedule();
})();
