SignaturePad.prototype.removeBlanks = function () {
	var imgWidth = this._ctx.canvas.width;
	var imgHeight = this._ctx.canvas.height;
	var imageData = this._ctx.getImageData(0, 0, imgWidth, imgHeight),
	data = imageData.data,
	getAlpha = function(x, y) {
		return data[(imgWidth*y + x) * 4 + 3]
	},
	scanY = function (fromTop) {
		var offset = fromTop ? 1 : -1;

		// loop through each row
		for(var y = fromTop ? 0 : imgHeight - 1; fromTop ? (y < imgHeight) : (y > -1); y += offset) {

			// loop through each column
			for(var x = 0; x < imgWidth; x++) {
				if (getAlpha(x, y)) {
					return y;
				}
			}
		}
		return null; // all image is white
	},
	scanX = function (fromLeft) {
		var offset = fromLeft? 1 : -1;

		// loop through each column
		for(var x = fromLeft ? 0 : imgWidth - 1; fromLeft ? (x < imgWidth) : (x > -1); x += offset) {

			// loop through each row
			for(var y = 0; y < imgHeight; y++) {
				if (getAlpha(x, y)) {
					return x;                        
				}      
			}
		}
		return null; // all image is white
	};

	var cropTop = scanY(true),
	cropBottom = scanY(false),
	cropLeft = scanX(true),
	cropRight = scanX(false);

	var relevantData = this._ctx.getImageData(cropLeft, cropTop, cropRight-cropLeft, cropBottom-cropTop);
	this._canvas.width = cropRight-cropLeft;
	this._canvas.height = cropBottom-cropTop;
	this._ctx.clearRect(0, 0, cropRight-cropLeft, cropBottom-cropTop);
	this._ctx.putImageData(relevantData, 0, 0);
};