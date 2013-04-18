// Script .js
// Jan Mochnak 2013

var addEvent = (function () {
	if (document.addEventListener) {
	return function (el, type, fn) {
		if (el && el.nodeName || el === window) {
			el.addEventListener(type, fn, false);
		} else if (el && el.length) {
		for (var i = 0; i < el.length; i++) {
				addEvent(el[i], type, fn);
			}
		}
	};
	} else {
	return function (el, type, fn) {
		if (el && el.nodeName || el === window) {
		el.attachEvent('on' + type, function () { return fn.call(el, window.event); });
		} else if (el && el.length) {
		for (var i = 0; i < el.length; i++) {
			addEvent(el[i], type, fn);
		}
		}
	};
	}
})();

function ImageHandler() {
	this.canvases = [];
	this.files = [];
	this.putWatermark = true;
	this.post = [];
	this.url = null;
	this.watermarkUrl = null;
	this.watermarkThumbUrl = null;
};

ImageHandler.prototype.addWatermark = function () {
	this.watermark = new Image();
	this.watermark.src = this.watermarkUrl;
	this.watermarkThumb = new Image();
	this.watermarkThumb.src = this.watermarkThumbUrl;
};

ImageHandler.prototype.add = function (file) {
	this.files.push(file);
};

ImageHandler.prototype.resize = function (img) {
	var MAX_WIDTH = 1280;
	var MAX_HEIGHT = 720;
	var width = img.width;
	var height = img.height;
	var newWidth = img.width;
	var newHeight = img.height;
	var ratio = 0;

	// Check if the current width is larger than the max
	if(width > MAX_WIDTH){
		ratio = MAX_WIDTH / width;   // get ratio for scaling image
		newWidth = MAX_WIDTH;
		newHeight = height * ratio;
		height = height * ratio;    // Reset height to match scaled image
		width = width * ratio;    // Reset width to match scaled image
	}

	// Check if current height is larger than max
	if(height > MAX_HEIGHT){
		ratio = MAX_HEIGHT / height; // get ratio for scaling image
		newHeight = MAX_HEIGHT;
		newWidth = width * ratio;
		width *= ratio;
	}

	return {width: Math.round(newWidth), height: Math.round(newHeight)};
};

ImageHandler.prototype.createCanvas = function (file, callback) {
	var $self = this;
	var reader = new FileReader();
	reader.onload = function(e) {
		var img = new Image();
		img.onload = function () {
			var size = $self.resize(img);

			// prepare
			var source = document.createElement('canvas');
			var sourceContext = source.getContext('2d');
			source.width = img.width;
			source.height = img.height;
			sourceContext.drawImage(img, 0, 0, img.width, img.height, 0, 0, img.width, img.height);
			var dataToScale = sourceContext.getImageData(0, 0, img.width, img.height).data;

			var canvas = document.createElement('canvas');
			canvas.width = size.width;
			canvas.height = size.height;
			var context = canvas.getContext('2d');
			canvas["fileName"] = file.name;

			var resized0 = new Resize(img.width, img.height, size.width, size.height, true, true, false, function (buffer) {
				updateCanvas(context, context.createImageData(size.width, size.height), buffer);
				callback(null, canvas);
			});
			resized0.resize(dataToScale);
		};
		img.src = e.target.result;
	};

	reader.readAsDataURL(file);
};

ImageHandler.prototype.parse = function () {
	var $self = this;
	console.log( this.files.length );
	for (var i = 0; i < this.files.length; i++) {
		var file = this.files[i];
		console.log( i );
		if ( /^image\//.test(file.type) ) {
			this.createCanvas(file, function (err, canvas) {
				$self.canvases.push(canvas);
				var canvasContext = canvas.getContext('2d');

				var ratio = 210 / 140;
				var ratio0 = canvas.width / canvas.height;

				var x = 0;
				if (ratio.toFixed(2) !== ratio0.toFixed(2)) x = canvas.height - (canvas.width * 140 / 210);

				var imData = null;

				var source = document.createElement('canvas');
				var sourceContext = source.getContext('2d');
				source.width = 210;
				source.height = 140;
				var n = { width: canvas.width, height: canvas.height-x };

				if ( x > -1 ) {
					imData = canvasContext.getImageData(0, x/2, canvas.width, canvas.height-x).data;
				} else {
					x *= -1;
					var imData = canvasContext.getImageData(x/2, 0, canvas.width-x, canvas.height).data;
					n.width = canvas.width - x;
					n.height = canvas.height;
				}

				console.log(n);

				var resized0 = new Resize(Math.ceil(n.width), Math.round(n.height), 210, 140, true, false, false, function (buffer) {
					updateCanvas(sourceContext, sourceContext.createImageData(210, 140), buffer);
					if ($self.putWatermark) {
						sourceContext.drawImage($self.watermarkThumb, 210 - $self.watermarkThumb.width - 5, 140 - $self.watermarkThumb.height - 5);
					}
					var imm = new Image();
					imm.src = source.toDataURL('image/png');
					document.body.appendChild(imm);
					var im = new Image();
					if ($self.putWatermark) {
						canvasContext.drawImage($self.watermark, canvas.width - $self.watermark.width - 30, canvas.height - $self.watermark.height - 30);
					}
					im.src = canvas.toDataURL('image/png');
					$self.post.push([canvas.toDataURL('image/png'), source.toDataURL('image/png'), canvas.fileName]);
				});
				resized0.resize(imData);
			});
		}
	}
};

ImageHandler.prototype.send = function () {
	var $self = this;
	$.ajax({
		url: $self.url,  //server script to process data
		type: 'POST',
		xhr: function() {  // custom xhr
			myXhr = $.ajaxSettings.xhr();
			if(myXhr.upload){ // check if upload property exists
				myXhr.upload.addEventListener('progress',progressHandlingFunction, false); // for handling the progress of the upload
			}
			return myXhr;
		},
		success: function () {
			console.log('sended');
		},
		error: function (e) {
			console.error(e);
		},
		data: JSON.stringify({files: $self.post}),
		cache: false,
		contentType: false,
		processData: false
	});
	function progressHandlingFunction (e) {
	if (e.lengthComputable) {
        var progress = Math.round((e.loaded*100)/e.total);
        if ( progress === 100 ) {
            window.document.title = windowTitle;
        } else {
            window.document.title = Math.round((e.loaded*100)/e.total) + "% " + windowTitle;
        }
		$('progress').attr({value:e.loaded, max:e.total});
		$('#progress-label').text(Math.round(e.loaded/1024) + "/" + Math.round(e.total/1024) + " " + Math.round((e.loaded*100)/e.total) + "%");
	}
}
};

var windowTitle = window.document.title;

var imagehandler = new ImageHandler();

var drop = document.querySelector('#dropbox');
var watermark_check = document.querySelector('input[name=add_watermark]');

var cancel = function (e) {
	e.preventDefault();
	return false;
};

// Tells the browser that we *can* drop on this target
addEvent(drop, 'dragover', cancel);
addEvent(drop, 'dragenter', cancel);
addEvent(watermark_check, 'change', function (e) {
	console.log(e, watermark_check.checked);
	if (watermark_check.checked) {
		imagehandler.putWatermark = true;
	} else {
		imagehandler.putWatermark = false;
	}
	imagehandler.canvases = [];
	imagehandler.parse();
});

addEvent(drop, 'drop', function (e) {
	e.preventDefault();
	var files = e.dataTransfer.files;
	imagehandler.files = [];
	for (var i = 0; i < files.length; i++) {
		imagehandler.add(files[i]);
	}
	imagehandler.parse();
	return false;
});


function updateCanvas(contextHandlePassed, imageBuffer, frameBuffer) {
	var data = imageBuffer.data;
	var length = data.length;
	for (var x = 0; x < length; ++x) {
		data[x] = frameBuffer[x] & 0xFF;
	}
	contextHandlePassed.putImageData(imageBuffer, 0, 0);
}
