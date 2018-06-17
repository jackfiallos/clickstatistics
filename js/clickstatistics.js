/*
Class:
	ClickStatistics
Opciones:
	container			Requerido si la pagina esta basada en un container, se puede definir aqui el elemento
	pathScript			Contiene solamente la ruta del archivo que genera el archivo de coordenadas
	waitBettweenClick	Tiempo de espera entre clicks usado en el evento mouseclick
	periodicalCheckCoord	Tiempo de espera para recolectar informacion y enviarla usado en el evento mousemove
	updateLinePosition	Actualiza la posicion del mouse sobre una linea
	tolerance			Limite maximo para saber cual es el alto de una linea
	trackMouseMove		Activa o desactiva la recoleccion de coordenadas al mover el mouse
Acerca de:
	clickstatistics para mootools v.02 - Last Update on 11/2008
	Qbit Mexhico http://qbit.com.mx
	Creado por Jack Fiallos http://jack.xtremdesign.net
	Para en la url ?debug para debuggear
	Escribir en la url ?disabled para desactivar
Creditos:
	Inspirado en estadisticas de Web Heatmap y  algoritmos de Eyetracking
*/
var ClickStatistics = new Class ({
	clickStatisticsDocument : '',
	clickStatisticsTime : '',
	clickStatisticsDebug : (window.location.href.search(/debug/) != -1),
	clickStatisticsDisable : false,
	clickStatisticsArrGroup : [],
	x : 0, y : 0,
	wd : 0, ht : 0,
	scrollx : 0, scrolly : 0,
	params : '',
	Ypos : 0,
	
	Implements: [Options],
	
	options: {
		container : '',
		pathScript : '../',
		waitBettweenClick : 1000,
		periodicalCheckCoord : 10000,
		updateLinePosition : 1000,
		tolerance : 4,
		trackMouseMove : false
	},
	
	initialize: function(options) {
		this.setOptions(options);
		this.wd = this.clickStatisticsDocument.clientWidth != undefined ? this.clickStatisticsDocument.clientWidth : window.innerWidth;
		this.ht = this.clickStatisticsDocument.clientHeight != undefined ? this.clickStatisticsDocument.clientHeight : window.innerHeight;
		this.scrollx = window.pageXOffset == undefined ? this.clickStatisticsDocument.scrollLeft : window.pageXOffset;
		this.scrolly = window.pageYOffset == undefined ? this.clickStatisticsDocument.scrollTop : window.pageYOffset;
		this.clickStatisticsDisable = (window.location.href.search(/disabled/) != -1);
		
		if (this.clickStatisticsDisable == false) {
			if (this.clickStatisticsDebug == true) {
				var Debugger = new Element('div', {
					'id': 'DebuggerDiv',
					'html': '<b>Debugger Window:</b><br /><br /><span id="DebuggerContent"></span>',
					'styles': {
						'padding': '5px',
						'cursor': 'move',
						'display': 'block',
						'width': '280px',
						'position': 'absolute',
						'top':'0',
						'left': '0',
						'border': '1px dotted #fff',
						'background-color': '#000',
						'color': '#fff',
						'z-index': '99'
					}
				}).injectInside(document.body);
				
				var DragDebugger = Debugger;
				DragDebugger.makeDraggable ({
					snap: 0,
					onSnap: function(el){
						el.setOpacity(.5);
					},
					onComplete: function(el){
						el.setOpacity(1);
					},
					limit: {
						x: [
							window.getScrollLeft, 
							function() { return window.getWidth() + window.getScrollLeft() - DragDebugger.offsetWidth;	}
						], 
						y: [
							window.getScrollTop,
							function() { return window.getHeight() + window.getScrollTop() - DragDebugger.offsetHeight; }
						]
					}
				});
			}
			
			this.clickStatisticsDocument = (document.documentElement !== undefined && document.documentElement.clientHeight !== 0) ? document.documentElement : document.body;
			this.showDebuggerWindow(':: ClickStatistics Started ::<br />');
			
			if (this.options.container != '') {
				this.options.container.addEvent('mousemove', this.catchMoveEvent.bind(this));
				this.options.container.addEvent('click', this.catchClickEvent.bind(this));
			}
			else {
				document.addEvent('mousemove', this.catchMoveEvent.bind(this));
				document.addEvent('click', this.catchClickEvent.bind(this));
			}
			
			this.SendCoord.bind(this).periodical(this.options.periodicalCheckCoord);
			this.updateYposition.bind(this).periodical(this.options.updateLinePosition);
		}
	},
	
	SendCoord: function() {
		if (this.clickStatisticsArrGroup.getLast() != null) {
			this.sendCollectedData('a='+this.clickStatisticsArrGroup + '&res=' + window.getWidth() + 'x' + window.getHeight());
			this.clickStatisticsArrGroup.empty();
		}
	},
	
	showDebuggerWindow: function(bodyStr) {
		if (this.clickStatisticsDebug === true) {
			$('DebuggerContent').set('html', bodyStr);
		}
	},
	
	sendCollectedData: function(StrData) {
		new Request({
			method: 'get', 
			url: this.options.pathScript + 'process.php',
			onSuccess: function(e) {
				if (e != '') alert(e);
			}
		}).send(StrData);
	},

	catchClickEvent: function(e) {
		try {
			if (e == undefined) {
				e = window.event;
				c = e.button;
				element = e.srcElement;
			}
			else {
				c = e.which;
				element = null;
			}
			
			if (c == 0) {
				this.showDebuggerWindow('There\'s no pressed any button');
				return true;
			}
			
			if (this.options.container != '') {
				this.x = e.client.x - this.options.container.offsetLeft;
				this.y = e.client.y - this.options.container.offsetTop + this.scrolly;
			}
			else {
				this.x = e.client.x + window.getScrollLeft();
				this.y = e.client.y + window.getScrollTop();
			}
			
			/*if (this.x > window.getWidth() || this.y > window.getHeight()) {
				this.showDebuggerWindow('JavaScript Error: Outside of active area');
				return true;
			}*/
			
			clickTime = new Date();
			if (clickTime.getTime() - this.clickStatisticsTime < this.options.waitBettweenClick) {
				this.showDebuggerWindow('JavaScript Error: Wait at least 1 second betteween clicks');
				return true;
			}
			
			this.clickStatisticsTime = clickTime.getTime();
			//this.params = 'x=' + (this.x + this.scrollx) + '&y=' + (this.y + this.scrolly) + '&res=' + window.getWidth() + 'x' + window.getHeight();
			this.params = 'x=' + this.x + '&y=' + this.y + '&res=' + window.getWidth() + 'x' + window.getHeight();
			this.sendCollectedData(this.params);
		}
		catch (error) {
			this.showDebuggerWindow('JavaScript Error: ' + error.message);
		}
		
		return true;
	},
	
	catchMoveEvent: function(e) {
		try {
			if (this.options.container != '') {
				this.x = e.client.x - this.options.container.offsetLeft;
				this.y = e.client.y - this.options.container.offsetTop + this.scrolly;
			}
			else {
				this.x = e.client.x + window.getScrollLeft();
				this.y = e.client.y + window.getScrollTop();
			}
			
			/*if (this.x > this.wd || this.y > this.ht) {
				console.log(this.y +' y '+window.getHeight());
				this.showDebuggerWindow('JavaScript Error: Outside of active area');
				return true;
			}*/
			
			output = 'Data Collection:<br />';
			output += 'Mouse.client.x: '+ this.x +'<br />';
			output += 'Mouse.client.y: '+ this.y +'<br />';
			output += 'Browser Width: ' + window.getWidth() +'<br />';
			output += 'Browser Height: ' + window.getHeight() +'<br />';
			this.showDebuggerWindow(output);
			
			if (this.options.trackMouseMove) {
				up = (this.Ypos - this.y) < 0 ? (this.Ypos - this.y)*(-1) : (this.Ypos - this.y);
				if (up <= this.options.tolerance) {
					this.clickStatisticsArrGroup.include(this.x+','+this.y);
				}
			}
		}
		catch (error) {
			this.showDebuggerWindow('JavaScript Error: ' + error.message);
		}
		
		return true;
	},
	
	updateYposition : function() {
		this.Ypos = this.y;
	}
});