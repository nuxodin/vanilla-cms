!function() {
	'use strict';

	var borderStyles = ['none','solid','dotted','dashed']
		,overflow = ['auto','hidden','visible','scroll'];
	window.qgCssProps = {
		color:{
			color:true
		},
		fontFamily:{
			// http://www.mightymeta.co.uk/web-safe-fonts-cheat-sheet-v-3-with-font-face-fonts-and-os-breakdown/
			options:['Arial','Times','Verdana','Trebuchet','Georgia','Courier']
		},
		fontSize:{
			length:true
		},
		fontStyle:{
			options:['normal','italic']
		},
		fontWeight:{
			options:['normal','bold']
		},
		textTransform:{
			options:['uppercase','lowercase','capitalize','none']
		},
		textAlign:{
			options:['left','right','center','justify']
		},
		textIndent:{
			length:true
		},
		lineHeight:{
			length:true
		},
		letterSpacing:{
			length:true
		},
		hyphens:{
			options:['auto','manual','none'],
			vendorPrefix:true
		},
		textDecoration:{
			options:['underline','none','overline','line-through'/*,'blink'*/],
		},
		whiteSpace:{
			options:['nowrap','pre','pre-wrap','pre-line','normal']
		},
		borderStyle:{
			options:borderStyles
		},
		borderTopStyle:{
			options:borderStyles
		},
		borderRightStyle:{
			options:borderStyles
		},
		borderBottomStyle:{
			options:borderStyles
		},
		borderLeftStyle:{
			options:borderStyles
		},
		borderColor:{
			color:true
		},
		borderRightColor:{
			color:true
		},
		borderTopColor:{
			color:true
		},
		borderBottomColor:{
			color:true
		},
		borderLeftColor:{
			color:true
		},
		borderWidth:{
			length:true
		},
		borderTopWidth:{
			length:true
		},
		borderRightWidth:{
			length:true
		},
		borderBottomWidth:{
			length:true
		},
		borderLeftWidth:{
			length:true
		},
		borderRadius:{
			length:true
		},
		borderTopLeftRadius:{
			length:true
		},
		borderTopRightRadius:{
			length:true
		},
		borderBottomRightRadius:{
			length:true
		},
		borderBottomLeftRadius:{
			length:true
		},
		backgroundColor:{
			color:true
		},
		backgroundImage:{
			image:true,
			multiple:true,
		},
		backgroundPosition:{
		},
		backgroundRepeat:{
			options:['no-repeat','repeat-x','repeat-y']
		},
		display:{
			options:['block','none','inline','inline-block','list-item','run-in','inline-table','table','table-cell','table-row']
		},
		float:{
			options:['none','left','right']
		},
		clear:{
			options:['none','left','right','both']
		},
		position:{
			options:['static','absolute','fixed']
		},
		width:{
			length:true
		},
		minWidth:{
			length:true
		},
		maxWidth:{
			length:true
		},
		height:{
			length:true
		},
		minHeight:{
			length:true
		},
		maxHeight:{
			length:true
		},
		margin:{
			length:true
		},
		marginTop:{
			length:true
		},
		marginRight:{
			length:true
		},
		marginBottom:{
			length:true
		},
		marginLeft:{
			length:true
		},
		padding:{
			length:true
		},
		paddingTop:{
			length:true
		},
		paddingRight:{
			length:true
		},
		paddingBottom:{
			length:true
		},
		paddingLeft:{
			length:true
		},
		boxShadow:{
			options:['none']
		},
		textShadow:{
			options:['none']
		},
		transform:{
			vendorPrefix:true
		},
		transformOrigin:{
			vendorPrefix:true
		},
		opacity:{
			float:true
		},
		top:{
			length:true
		},
		left:{
			length:true
		},
		right:{
			length:true
		},
		bottom:{
			length:true
		},
		columnCount:{
			float:true,
			vendorPrefix:true
		},
		overflow:{
			options:overflow
		},
		overflowX:{
			options:overflow
		},
		overflowY:{
			options:overflow
		},
		transitionDuration:{vendorPrefix:true},
		transitionTimingFunction:{vendorPrefix:true},
		transitionProperty:{vendorPrefix:true},
		cssText:{}
	};
}();