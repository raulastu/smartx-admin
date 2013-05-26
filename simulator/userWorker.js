M_PI = 3.14159265358979323846
var user_id;
var interval;
var lat;
var lng;
var pointToGoLat;
var pointToGoLng;
var distancePerSeconds;
var seconds;

self.addEventListener('message', function(e) {
	importScripts('../js/third/jquery.hive.pollen.js');
	if(e.data.type=='init'){
		user_id=e.data.user_id;
		lat=e.data.lat;
		lng=e.data.lng;
		interval=1+Math.random()*3;
		seconds=interval;
	}
	init();
  	self.postMessage({id:user_id, data:"init with value "+interval+" interval time to send gps locations"});
  	
}, false);

var di=[1,-1,0,0];
var dj=[0,0,1,-1];
var factor=1000;

function init(){
	setInterval(function(){
		var newlat;
		var newlng;
		var comment='wandering';
		var si=Math.floor(Math.random()*4);
		
		var distance = Math.random()*5;

		newlat = lat+di[si]*distance/factor;
		newlng = lng+dj[si]*distance/factor;

		self.postMessage({id:user_id,data:comment+" going from "+lat+" "+lng+" to "+newlat+" "+newlng});
		// self.postMessage({id:user_id,data:});
		lat=newlat;
		lng=newlng;
		$.ajax.post({
	        url: "../user/update_location",
	        data:"data="+user_id+":"+newlat+":"+newlng,
	        type:'json',
	        success: function(data) {
	        	var a = {id:user_id, data:data};
	        	self.postMessage(a);
	        	if(data.json!=null && data.json.length!=0){
	        		pointToGoLat = parseFloat(data.json.lat);
	        		pointToGoLng = parseFloat(data.json.lng);
	        		var a = {id:user_id, data:data};
	        		self.postMessage(a);
	        	}else{
	        		pointToGoLat=null;
	        		pointToGoLng=null;
	        	}
	      	}
	      })
	},interval*1000);
	var distanceFromTwoPoints = function(lat1, lon1, lat2, lon2) {
		var R = 6371; // km
		var dLat = toRad(lat2 - lat1);
		var dLon = toRad(lon2 - lon1);
		var lat1 = toRad(lat1);
		var lat2 = toRad(lat2);

		var a = Math.sin(dLat / 2.0) * Math.sin(dLat / 2.0)
				+ Math.sin(dLon / 2.0) * Math.sin(dLon / 2.0) * Math.cos(lat1)
				* Math.cos(lat2);
		var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1.0 - a));
		var d = R * c;
		return d;
	}

	function toRad(val) {
		return val *  M_PI/ 180;
	}

}



