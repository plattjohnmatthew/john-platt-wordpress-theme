'use strict';
const fs=require('fs'),path=require('path'),vm=require('vm'),assert=require('assert');
const full=fs.readFileSync(path.join(__dirname,'../john-platt/assets/js/projects.js'),'utf8');
const source=full.slice(full.indexOf('/* Project video playback is independent'));
class Target {constructor(){this.events={};}addEventListener(name,fn){(this.events[name]??=[]).push(fn);}emit(name,event={}){for(const fn of this.events[name]||[])fn(event);}}
class Button extends Target{constructor(dataset){super();this.dataset=dataset;this.hidden=true;this.textContent='';this.attrs={};}setAttribute(name,value){this.attrs[name]=value;}click(){this.emit('click');}}
class Video extends Target{
 constructor(immersive=true){super();this.immersive=immersive;this.muted=true;this.volume=1;this.controls=true;this.paused=true;this.hidden=false;this.currentTime=12;this.src='original.mp4';this.loads=0;this.plays=0;this.reject=false;
  this.pauseButton=new Button({pauseLabel:'Pause video',playLabel:'Play video'});this.soundButton=new Button({muteLabel:'Mute video',unmuteLabel:'Unmute video'});this.source=new Target();this.failure={hidden:true};
  this.parentElement={querySelector:s=>s==='.project-media__toggle'?this.pauseButton:s==='.project-media__sound'?this.soundButton:this.failure};
 }
 querySelector(){return this.source;}closest(){return this.immersive?{}:null;}load(){this.loads++;}pause(){this.paused=true;}
 play(){this.plays++;if(this.reject)return Promise.reject(new Error('Autoplay blocked'));this.paused=false;this.emit('play');return Promise.resolve();}
}
async function run(){
 const first=new Video(),second=new Video(),simple=new Video(false);let videos=[first,second,simple];const observers=[];
 const root=new Target();root.querySelectorAll=()=>videos;
 const document=new Target();document.hidden=false;document.querySelector=()=>root;
 const window=new Target();
 const desktop=new Target();desktop.matches=true;const reduced=new Target();reduced.matches=false;
 class IO{constructor(callback){this.callback=callback;observers.push(this);}observe(){}unobserve(){}disconnect(){}fire(video,ratio){this.callback([{target:video,isIntersecting:ratio>0,intersectionRatio:ratio}]);}}
 window.IntersectionObserver=IO;
 vm.runInNewContext(source,{document,window,IntersectionObserver:IO,matchMedia:q=>q.includes('reduced')?reduced:desktop,Promise,Map,Set,WeakSet});
 const settle=async()=>{for(let i=0;i<8;i++)await Promise.resolve();};
 const visible=(video,ratio)=>observers[1].fire(video,ratio);
 assert(first.muted);assert(first.soundButton.hidden);visible(first,1);await settle();
 assert(!first.controls);assert(!first.soundButton.hidden);assert.equal(first.soundButton.attrs['aria-label'],'Unmute video');
 const time=first.currentTime,loads=first.loads;
 first.soundButton.click();assert(!first.muted);assert.equal(first.soundButton.attrs['aria-label'],'Mute video');assert.equal(first.currentTime,time);assert.equal(first.loads,loads);
 visible(first,.5);await settle();assert(!first.muted);assert.equal(first.loads,loads,'Scroll must not reload');
 first.pauseButton.click();assert(first.paused);first.soundButton.click();assert(first.muted);assert(first.paused,'Sound must not resume a paused video');
 first.soundButton.click();assert(!first.muted);visible(first,.8);await settle();assert(first.paused,'User pause must survive intersection updates');
 first.pauseButton.click();await settle();assert(!first.paused);assert(!first.muted,'Resume must retain sound choice');
 visible(first,0);visible(second,1);await settle();assert(first.paused);assert(second.muted,'Next Project must start muted');
 second.volume=0;second.soundButton.click();assert(!second.muted);assert.equal(second.volume,1);second.muted=true;second.emit('volumechange');assert.equal(second.soundButton.attrs['aria-label'],'Unmute video');
 visible(second,0);visible(first,1);await settle();assert(!first.muted,'Re-entry must retain this Project sound choice');
 desktop.matches=false;desktop.emit('change');await settle();assert(first.controls);assert(first.soundButton.hidden);assert(first.pauseButton.hidden);
 desktop.matches=true;desktop.emit('change');await settle();assert(!first.soundButton.hidden);
 reduced.matches=true;reduced.emit('change');await settle();assert(first.controls);assert(first.soundButton.hidden);
 reduced.matches=false;reduced.emit('change');await settle();first.source.emit('error');await settle();assert(first.hidden);assert(first.soundButton.hidden);assert(!first.failure.hidden);
 visible(first,0);visible(simple,1);await settle();assert(simple.soundButton.hidden,'Sound control is only for immersive chapters');
 visible(simple,0);second.reject=true;visible(second,1);await settle();assert(second.controls);assert(second.soundButton.hidden,'Autoplay failure uses native controls');
 const fresh=new Video();videos=[fresh];window.emit('john-platt:projects-updated');visible(fresh,1);await settle();assert(fresh.muted);assert(!fresh.soundButton.hidden);
 window.emit('john-platt:projects-updated');await settle();assert.equal(fresh.soundButton.events.click.length,1,'No duplicate listeners');
 document.hidden=true;document.emit('visibilitychange');await settle();assert(fresh.paused);
 assert.equal(first.src,'original.mp4');assert.equal(first.currentTime,time);
 console.log('PASS: muted defaults, mute/unmute labels, zero volume, pause/resume, scroll/re-entry continuity, independent Projects, mobile/reduced motion, playback errors, filters and one-time listeners.');
}
run().catch(error=>{console.error(error);process.exitCode=1;});
