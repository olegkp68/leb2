window.addEvent('domready', function() {
					var Tips2 = new Tips($$('.mod_vm_universal_1 a', '.mod_vm_universal_2 a'), {
						maxTitleChars: 500,
						initialize:function(){
						this.fx = new Fx.Style(this.toolTip, 'opacity', {duration: 400, wait: true}).set(0);
						},
						onShow: function(toolTip) {
						this.fx.start(1);
						},
						onHide: function(toolTip) {
						this.fx.start(0);
						}

					});
});