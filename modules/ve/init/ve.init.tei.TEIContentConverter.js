ve.init.tei.TeiContentConverter = function () {
	this.api = new mw.Api();
};

OO.initClass( ve.init.tei.TeiContentConverter );

/**
 * @param {string} htmlText
 * @param {boolean} normalize
 * @return {Promise<string>}
 */
ve.init.tei.TeiContentConverter.prototype.getTeiFromHtml = function ( htmlText, normalize ) {
	return this.convertContent( htmlText, 'text/html', 'application/tei+xml', normalize );
};

/**
 * @param {string} teiText
 * @param {boolean} normalize
 * @return {Promise<string>}
 */
ve.init.tei.TeiContentConverter.prototype.getHtmlFromTei = function ( teiText, normalize ) {
	return this.convertContent( teiText, 'application/tei+xml', 'text/html', normalize );
};

/**
 * @param {string} content
 * @param {string} from
 * @param {string} to
 * @param {boolean} normalize
 * @return {Promise<string>}
 */
ve.init.tei.TeiContentConverter.prototype.convertContent = function ( content, from, to, normalize ) {
	return this.api.post( {
		action: 'teiconvert',
		text: content,
		from: from,
		to: to,
		normalize: normalize
	} ).then( function ( data ) {
		return data.convert.text;
	}, function ( code, data ) {
		throw new OO.ui.Error( data.error.info );
	} );
};

ve.init.tei.teiContentConverter = new ve.init.tei.TeiContentConverter();
