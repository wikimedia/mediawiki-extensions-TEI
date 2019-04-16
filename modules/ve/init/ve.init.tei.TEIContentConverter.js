ve.init.tei.TeiContentConverter = function () {
	this.api = new mw.Api();
};

OO.initClass( ve.init.tei.TeiContentConverter );

/**
 * @param {string} htmlText
 * @param {boolean} normalize
 * @param {mw.Title} title
 * @return {Promise<string>}
 */
ve.init.tei.TeiContentConverter.prototype.getTeiFromHtml = function ( htmlText, normalize, title ) {
	return this.convertContent( htmlText, 'text/html', 'application/tei+xml', normalize, title );
};

/**
 * @param {string} teiText
 * @param {boolean} normalize
 * @param {mw.Title} title
 * @return {Promise<string>}
 */
ve.init.tei.TeiContentConverter.prototype.getHtmlFromTei = function ( teiText, normalize, title ) {
	return this.convertContent( teiText, 'application/tei+xml', 'text/html', normalize, title );
};

/**
 * @param {string} content
 * @param {string} from
 * @param {string} to
 * @param {boolean} normalize
 * @param {mw.Title} title
 * @return {Promise<string>}
 */
ve.init.tei.TeiContentConverter.prototype.convertContent = function ( content, from, to, normalize, title ) {
	return this.api.post( {
		action: 'teiconvert',
		text: content,
		from: from,
		to: to,
		normalize: normalize,
		title: title.toString()
	} ).then( function ( data ) {
		return data.convert.text;
	}, function ( code, data ) {
		throw new OO.ui.Error( data.error.info );
	} );
};

ve.init.tei.teiContentConverter = new ve.init.tei.TeiContentConverter();
