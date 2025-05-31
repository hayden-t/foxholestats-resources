L.Control.RangeFinder = L.Control.extend({
  options: {
 

  },
  
  

  initialize: function (options) {
    //  apply options to instance
    this.active = false;
    L.Util.setOptions(this, options)
  },

  onAdd: function (map) {
    var className = 'leaflet-control-zoom leaflet-bar leaflet-control'
    var container = L.DomUtil.create('div', className)
    this._createButton('', 'Range Finder',
    'leaflet-control-rangefinder leaflet-bar-part',
    container, this._toggleTool, this)

    return container
  },

  onRemove: function (map) {

  },

  _createButton: function (html, title, className, container, fn, context) {
  
    var input = L.DomUtil.create('input', className, container)
    input.title = title +' Distance in Meters'
    input.value = '100'
    input.id = 'rangeDistance'
    
    var link = L.DomUtil.create('a', className, container)
    link.innerHTML = html
    link.href = '#'
    link.title = title    
    
	
	
    L.DomEvent
      .on(link, 'click', L.DomEvent.stopPropagation)
      .on(link, 'click', L.DomEvent.preventDefault)
      .on(link, 'click', fn, context)
      
    return link
  },

	_toggleTool: function(e){
	
		if(!this.active){
			//this.active = true;
			this.reticle = this._createCircle([0,0])
		 
			 L.DomEvent
			  .on(this._map, 'mousemove', this._mouseMove, this)
			  .on(this._map, 'click', this._mouseClick, this)
		}else{
		
			L.DomEvent
			  .on(this._map, 'mousemove', this._mouseMove, this)
			  .on(this._map, 'click', this._mouseClick, this)
			this.reticle.remove();
			this.active = false;
		}	
		

	},

  _mouseMove: function (e) {
    if (!e.latlng) {
      return
    }
    
    this.reticle.eachLayer(function (layer) {
		layer.setLatLng(e.latlng);
	});

  },

  _mouseClick: function (e) {  

    if (!e.latlng) {
      return
    }
    
		 L.DomEvent
		  .off(this._map, 'mousemove', this._mouseMove, this)
		  .off(this._map, 'click', this._mouseClick, this)


    
  },



  _createCircle: function (latlng) {
	var reticle =  L.layerGroup();
 
    var distance = parseInt(L.DomUtil.get('rangeDistance').value)
    
    if(distance < 1 || distance > 2000){
		L.DomUtil.get('rangeDistance').value = 100
		distance = 100
    }
    

   // var rings = [80,100,200,300,400,500];
  //  for ( var i = 0, l = rings.length; i < l; i++ ) {
		var ring = L.circle(latlng, {
		  color: 'red',
		  opacity: 1,
		  weight: 1,
		  fillColor: 'grey',
		  fill: true,
		  fillOpacity: .2,
		  radius: distance / this.options.distanceFactor,
		 // clickable: Boolean(this._lastCircle)
		})	

		reticle.addLayer(ring);   
 //   }
    
		var tooltip = L.tooltip({permanent: true,offset: [0,0]})
		.setLatLng(latlng)
		.setContent(distance+'m');
		
		reticle.addLayer(tooltip);
   
   
		reticle.addTo(map);
    return reticle;
  },



})

L.control.rangefinder = function (options) {
  return new L.Control.RangeFinder(options)
}

L.Map.mergeOptions({
  rangefinderControl: false
})

L.Map.addInitHook(function () {
  if (this.options.rangefinderControl) {
    this.rangefinderControl = new L.Control.RangeFinder()
    this.addControl(this.rangefinderControl)
  }
})
