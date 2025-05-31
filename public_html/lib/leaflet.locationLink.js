L.Control.locationLink = L.Control.extend({
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
    window.linkButton = this._createButton('', 'Position Link',
    'leaflet-control-locationLink leaflet-bar-part leaflet-bar-part-top-and-bottom',
    container, this._toggleTool, this)

    return container
  },

  onRemove: function (map) {

  },

  _createButton: function (html, title, className, container, fn, context) {
    
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
			this.active = true;
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
    var tempThis = this;
    this.reticle.eachLayer(function (layer) {
		layer.setLatLng(e.latlng);
		if(layer.options.shareLink == true)tempThis._updatePopup(layer);
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
 
   var distance = 10;

		var ring = L.circle(latlng, {
		  color: 'red',
		  opacity: 1,
		  weight: 1,
		  fillColor: 'red',
		  fill: true,
		  fillOpacity: .5,
		  radius: distance / this.options.distanceFactor,
		 // clickable: Boolean(this._lastCircle)
		})	

		reticle.addLayer(ring);   

    
		var popup = L.popup({shareLink:true, autoClose: false, closeButton: false, closeOnClick: false, autoPan:false, offset: [0,0]})
		.setLatLng(latlng);
		//.setContent(distance+'m');
		
		this._updatePopup(popup);
		
		reticle.addLayer(popup);
   
   
		reticle.addTo(map);
    return reticle;
  },
  
  
  _updatePopup(layer){
  
	layer.setContent('<a class="shareLocation" href="https://foxholestats.com/index.php?lat='+layer.getLatLng().lat.toFixed(1)+'&lng='+layer.getLatLng().lng.toFixed(1)+'" style="font-size:17px;white-space:no-wrap;">'+layer.getLatLng().lat.toFixed(1) + ', '+ layer.getLatLng().lng.toFixed(1) + ' <img  style="width:20px;height:20px;vertical-align:middle;" src="/lib/copy.png" /></a>');
  
  },



})

L.control.locationLink = function (options) {
  return new L.Control.locationLink(options)
}

L.Map.mergeOptions({
  locationLinkControl: false
})

L.Map.addInitHook(function () {
  if (this.options.locationLinkControl) {
    this.locationLinkControl = new L.Control.locationLink()
    this.addControl(this.locationLinkControl)
  }
})
