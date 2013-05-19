var tdriver_id;
var interval;
var lat;
var lng;
self.addEventListener('message', function(e) {
	importScripts('../js/third/jquery.hive.pollen.js');
	if(e.data.type=='init'){
		tdriver_id=e.data.tdriver_id;
		lat=e.data.lat;
		lng=e.data.lng;
		interval=Math.random()*10;
	}
	init();
  	self.postMessage(tdriver_id+" init with value "+interval);
}, false);

var di=[1,-1,0,0];
var dj=[0,0,1,-1];
var factor=1000;

function init(){
	setInterval(function(){
		var si=Math.round(Math.random()*3);
		var distance = Math.random()*5;
		var newlat = lat+di[si]*distance/factor;
		var newlng = lng+dj[si]*distance/factor;
		self.postMessage("Hello from "+tdriver_id+ " at "+lat+" "+lng);
		self.postMessage("new latlng si ="+si+" "+newlat+" "+newlng);
		lat= newlat;
		lng=newlng;
		 $.ajax.post({
	        url: "../tdrivers/update_driver_location",
	        data:"data="+tdriver_id+":"+newlat+":"+newlng,
	        success: function(data) {
	        	self.postMessage(data);
	      	}
	      })
		
	},interval*1000);
}
