'use strict';
const fs=require('fs'),vm=require('vm'),assert=require('assert'),path=require('path');
const source=fs.readFileSync(path.join(__dirname,'../john-platt/assets/js/analytics-delay.js'),'utf8');
const delayed='application/x-john-platt-delayed-analytics';
function setup({idle=true,consent=true,hidden=false,loading=false,duplicate=false,markers=true}={}){
 let now=0,next=0;const timers=new Map(),frames=[],idles=new Map(),listeners=new Map(),nodes=[];
 class Script{
  constructor(attrs={}){this.attrs=new Map(Object.entries(attrs));this.isConnected=true;this.nonce='';}
  get attributes(){return Array.from(this.attrs,([name,value])=>({name,value}));}
  setAttribute(k,v){this.attrs.set(k,String(v));}
  get src(){return this.attrs.get('src')||'';}set src(v){this.setAttribute('src',v);}
  get type(){return this.attrs.get('type')||'';}set type(v){this.setAttribute('type',v);}
  get dataset(){return {jpAnalyticsType:this.attrs.get('data-jp-analytics-type')};}
  replaceWith(node){nodes.splice(nodes.indexOf(this),1,node);this.isConnected=false;}
  remove(){nodes.splice(nodes.indexOf(this),1);this.isConnected=false;}
 }
 const marker=new Script({src:'https://www.googletagmanager.com/gtag/js?id=G-TEST123',type:delayed,'data-jp-analytics-type':'',async:''});marker.nonce='test-nonce';
 if(markers)nodes.push(marker);
 if(duplicate)nodes.push(new Script({src:marker.src}));
 const command=(function(){return arguments;})('config','G-TEST123');
 const document={title:'Landing title',visibilityState:hidden?'hidden':'visible',readyState:loading?'loading':'complete',
  querySelectorAll:s=>nodes.filter(n=>s==='script[src]'?!!n.src:n.type===delayed),createElement:()=>new Script(),
  addEventListener:(name,fn,options)=>listeners.set(name,{fn,once:options?.once}),removeEventListener:name=>listeners.delete(name)};
 const window={location:{href:'https://example.test/writing/'},dataLayer:[['js','time'],command],wp_has_consent:()=>consent};
 if(idle){window.requestIdleCallback=(fn)=>{idles.set(++next,fn);return next;};window.cancelIdleCallback=id=>idles.delete(id);}
 const context={window,document,URL,Date:{now:()=>now},requestAnimationFrame:fn=>frames.push(fn),
  setTimeout:(fn,delay)=>{timers.set(++next,{fn,at:now+delay});return next;},clearTimeout:id=>timers.delete(id)};
 vm.runInNewContext(source,context);
 const dispatch=name=>{const entry=listeners.get(name);if(entry){if(entry.once)listeners.delete(name);entry.fn();}};
 const tick=time=>{now=time;for(const[id,t]of [...timers])if(t.at<=now){timers.delete(id);t.fn();}};
 return {window,document,marker,nodes,command,listeners,timers,idles,dispatch,tick,
  frames:()=>{while(frames.length)frames.shift()();},idle:()=>{for(const[id,fn]of [...idles]){idles.delete(id);fn();}},grant:()=>{consent=true;dispatch('wp_listen_for_consent_change');},
  active:()=>nodes.filter(n=>n.type!==delayed&&n.type!=='text/plain')};
}
let s=setup();assert.equal(s.active().length,0);s.frames();s.tick(999);assert.equal(s.active().length,0);
s.window.location.href='https://example.test/writing/?topics=ai';s.window.dataLayer.push(['event','early']);s.tick(1000);assert.equal(s.active().length,0);s.idle();
assert.equal(s.active().length,1);assert.equal(s.active()[0].nonce,'test-nonce');assert.equal(s.active()[0].fetchPriority,'low');
assert.equal(s.command[2].page_location,'https://example.test/writing/');assert.equal(s.command[2].page_title,'Landing title');assert.equal(s.window.dataLayer.length,3);
s.tick(3000);s.dispatch('visibilitychange');assert.equal(s.active().length,1);assert.equal(s.listeners.size,0);assert.equal(s.timers.size,0);
s=setup();s.frames();s.tick(1000);s.tick(2000);assert.equal(s.active().length,1,'Idle-starvation fallback');
s=setup({idle:false});s.frames();s.tick(1000);assert.equal(s.active().length,1,'No-idle fallback');
s=setup({consent:false});s.frames();s.tick(2000);s.idle();assert.equal(s.active().length,0);s.grant();assert.equal(s.active().length,1,'Consent grant resumes');
s=setup({hidden:true});s.frames();s.tick(2000);s.idle();assert.equal(s.active().length,0);s.document.visibilityState='visible';s.dispatch('visibilitychange');assert.equal(s.active().length,1,'Hidden tab waits');
s=setup();s.frames();s.marker.type='text/plain';s.tick(2000);s.idle();assert.equal(s.active().length,0,'CMP-controlled type is preserved');
s=setup({duplicate:true});s.frames();s.tick(2000);s.idle();assert.equal(s.active().length,1,'No duplicate library');
s=setup({loading:true});s.frames();s.tick(3000);assert.equal(s.active().length,0);s.dispatch('DOMContentLoaded');s.frames();s.tick(5000);assert.equal(s.active().length,1);
s=setup({markers:false});assert.equal(s.listeners.size,0);assert.equal(s.timers.size,0);
s=setup();s.command[2]={page_location:'https://example.test/author-defined',send_page_view:false};s.frames();s.tick(2000);assert.equal(s.command[2].page_location,'https://example.test/author-defined');assert.equal(s.command[2].send_page_view,false);
console.log('PASS: delayed start, finite frames, idle/fallback timers, consent/visibility, CSP nonce, landing context, existing config/events, duplicate prevention and cleanup.');
