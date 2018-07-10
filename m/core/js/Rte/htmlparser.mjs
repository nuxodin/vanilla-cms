let startTag = /^<([\w\:\-]+)((?:\s+[\w\:\-]+(?:\s*=\s*(?:(?:"[^"]*")|(?:'[^']*')|[^>\s]+))?)*)\s*(\/?)>/,
    endTag = /^<\/([\w\:\-]+)[^>]*>/,
    attr = /([\w\:\-]+)(?:\s*=\s*(?:(?:"((?:\\.|[^"])*)")|(?:'((?:\\.|[^'])*)')|([^>\s]+)))?/g;
// Empty Elements - HTML 4.01
let empty = makeMap("area,base,basefont,br,col,frame,hr,img,input,isindex,link,meta,param,embed");

// Block Elements - HTML 4.01
let block = makeMap("address,applet,blockquote,button,center,dd,del,dir,div,dl,dt,fieldset,form,frameset,hr,iframe,ins,isindex,li,map,menu,noframes,noscript,object,ol,p,pre,script,table,tbody,td,tfoot,th,thead,tr,ul");

// Inline Elements - HTML 4.01
let inline = makeMap("a,abbr,acronym,applet,b,basefont,bdo,big,br,button,cite,code,del,dfn,em,font,i,iframe,img,input,ins,kbd,label,map,object,q,s,samp,script,select,small,span,strike,strong,sub,sup,textarea,tt,u,var");

// Elements that you can, intentionally, leave open
// (and which close themselves)
let closeSelf = makeMap("colgroup,dd,dt,li,options,p,td,tfoot,th,thead,tr");

// Attributes that have their values filled in disabled="disabled"
let fillAttrs = makeMap("checked,compact,declare,defer,disabled,ismap,multiple,nohref,noresize,noshade,nowrap,readonly,selected");

// Special Elements (can contain anything)
let special = makeMap("script,style");

let HTMLParser  = function(html, handler) {
	let index, chars, match, stack = [], last = html;
	stack.last = function() {
		return this[ this.length - 1 ];
	};

	while (html) {
		chars = true;

		// Make sure we're not in a script or style element
		if (!stack.last() || !special[ stack.last() ]) {

			// Comment
			if (html.indexOf("<!--") === 0) {
				index = html.indexOf("-->");

				if (index >= 0) {
					if (handler.comment )
						handler.comment( html.substring( 4, index ) );
					html = html.substring( index + 3 );
					chars = false;
				}

			// end tag
			} else if (html.indexOf("</") === 0) {
	            //$log("HTMLParser: endtag ");
				match = html.match( endTag );

				if (match) {
	                //$log("HTMLParser: endtag match : "+match[0]);
					html = html.substring( match[0].length );
					match[0].replace( endTag, parseEndTag );
					chars = false;
				}

			// start tag
			} else if (html.indexOf("<") === 0) {
	            //$log("HTMLParser: starttag ");
				match = html.match( startTag );

				if (match) {
	                //$log("HTMLParser: starttag match : "+match[0]);
					html = html.substring( match[0].length );
					match[0].replace( startTag, parseStartTag );
					chars = false;
				}
			}

			if (chars) {
	            //$log("HTMLParser: other ");
				index = html.indexOf("<");
				let text = index < 0 ? html : html.substring( 0, index );
				html = index < 0 ? "" : html.substring( index );
				if (handler.chars) {
	                //$log("HTMLParser: chars " + text);
				    handler.chars( text );
			    }
			}
		} else {
	        //$log("HTMLParser: special ");
			html = html.replace(new RegExp("(.*)<\/" + stack.last() + "[^>]*>"), function(all, text) {
				text = text.replace(/<!--(.*?)-->/g, "$1").
					replace(/<!\[CDATA\[(.*?)]]>/g, "$1");
				if (handler.chars) {
	                //$log("HTMLParser: special chars " + text);
				    handler.chars( text );
			    }
				return "";
			});
			parseEndTag( "", stack.last() );
		}
		if (html == last) {throw "Parse Error: " + html;}
		last = html;
	}

	// Clean up any remaining tags
	parseEndTag();

	function parseStartTag(tag, tagName, rest, unary) {
		tagName = tagName.toLowerCase();
		if (block[ tagName ]) {
			while ( stack.last() && inline[ stack.last() ]) {
				parseEndTag('', stack.last());
			}
		}

		if (closeSelf[ tagName ] && stack.last() == tagName) {
			parseEndTag('', tagName);
		}

		unary = empty[ tagName ] || !!unary;

		if (!unary )
			stack.push( tagName );

		if (handler.start) {
			let attrs = [];

			rest.replace(attr, function(match, name) {
				let value = arguments[2] ? arguments[2] :
					arguments[3] ? arguments[3] :
					arguments[4] ? arguments[4] :
					fillAttrs[name] ? name : "";

				attrs.push({
					name: name,
					value: value,
					escaped: value.replace(/(^|[^\\])"/g, '$1\\\"') //"
				});
			});

			if (handler.start) {
			    //$log("unary ? : "+unary);
				handler.start( tagName, attrs, unary );
			}
		}
	}

	function parseEndTag(tag, tagName) {
	  let pos;
		// If no tag name is provided, clean shop
		if (!tagName) {
			pos = 0;
		} else {
		// Find the closest opened tag of the same type
			for (pos = stack.length - 1; pos >= 0; pos--) {
				//$log("parseEndTag : "+stack[ pos ] );
				if (stack[ pos ] == tagName) {
				    break;
			    }
			}
        }
		if (pos >= 0) {
			// Close all the open elements, up the stack
			for (let i = stack.length - 1; i >= pos; i--) {
                if (handler.end) {
				    //$log("end : "+stack[ i ] );
                    handler.end( stack[ i ] );
                }
			}
			// Remove the open elements from the stack
			//$log("setting stack length : " + stack.length + " -> " +pos );
			stack.length = pos;
		}
	}
};
function makeMap(str) {
	let obj = {}, items = str.split(",");
	for (let i = 0; i < items.length; i++ )
		obj[ items[i] ] = true;
	return obj;
}

export {
	HTMLParser
};
