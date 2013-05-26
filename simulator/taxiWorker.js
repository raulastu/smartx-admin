M_PI = 3.14159265358979323846
var tdriver_id;
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
		tdriver_id=e.data.tdriver_id;
		lat=e.data.lat;
		lng=e.data.lng;
		interval=1+Math.random()*3;
		seconds=interval;
	}
	init();
  	self.postMessage({id:tdriver_id, data:"init with value "+interval+" interval time to send gps locations"});
  	
}, false);

var di=[1,-1,0,0];
var dj=[0,0,1,-1];
var factor=1000;

function init(){
	setInterval(function(){
		var newlat;
		var newlng;
		var comment='wandering';
		if(pointToGoLat!=null && pointToGoLng!=null){ // There is a user waiting for this driver
			//from 166 meters to 250 meters - 60km/h to 90km/h
			var distanceInMettersIWouldTravelInSecondsSeconds = 166+Math.random()*84*10/seconds;
			distanceInMettersIWouldTravelInSecondsSeconds/=1000;
			// distanceInMettersIWouldTravelInSecondsSeconds = 10/seconds;	
			var distanceToUser = distanceFromTwoPoints(lat, lng, pointToGoLat, pointToGoLng);

			//SOME TWEAKING MAY BE NEEDED - sometimes it does not get to the same point as the user is, even though the locations are ident
			if(distanceToUser==0){	
				self.postMessage({id:tdriver_id,data:"Arrived"});
			}

			var maxDistanceToTravel = Math.min(distanceToUser,distanceInMettersIWouldTravelInSecondsSeconds);
			var factorX = (maxDistanceToTravel)/distanceToUser;

			if(factorX==1){
				newlat=pointToGoLat;
				newlng=pointToGoLng
			}else{
				var latDirection=1;
				if(lat>pointToGoLat){
					latDirection=-1;
				}
				var lngDirection=1;
				if(lng>pointToGoLng){
					lngDirection=-1;
				}
				newlat = lat+Math.abs(pointToGoLat-lat)*factorX*latDirection;
				newlng = lng+Math.abs(pointToGoLng-lng)*factorX*lngDirection;
			}
			
			comment="lat+parseFloat(pointToGoLat)="+(lat+(pointToGoLat))/factorX+"Math.sin(3)="+Math.sin(3)+" pointToGoLat="+pointToGoLat+" pointToGoLng="+pointToGoLng+" distance="+distanceInMettersIWouldTravelInSecondsSeconds+" distanceToUser="+distanceToUser+" factorX="+factorX+" picking someone up";
		}else{
			//you can wander around
			var si=Math.floor(Math.random()*4);
			
			var distance = Math.random()*5;

			newlat = lat+di[si]*distance/factor;
			newlng = lng+dj[si]*distance/factor;
		}
		self.postMessage({id:tdriver_id,data:comment+" going from "+lat+" "+lng+" to "+newlat+" "+newlng});
		// self.postMessage({id:tdriver_id,data:});
		lat=newlat;
		lng=newlng;
		 $.ajax.post({
	        url: "../tdrivers/update_driver_location",
	        data:"data="+tdriver_id+":"+newlat+":"+newlng,
	        type:'json',
	        success: function(data) {
	        	var a = {id:tdriver_id, data:data};
	        	self.postMessage(a);
	        	if(data.json!=null && data.json.length!=0){
	        		pointToGoLat = parseFloat(data.json.lat);
	        		pointToGoLng = parseFloat(data.json.lng);
	        		var a = {id:tdriver_id, data:data};
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



