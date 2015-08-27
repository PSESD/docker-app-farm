function CanisInstanceManager($element, settings) {
    CanisComponent.call(this);
	this.$element = $element;
	this.elements = {};
	this.settings = jQuery.extend(true, {}, this.defaultSettings, settings);
	console.log(this);
	this.init();
	this.isInitializing = false;
}

CanisInstanceManager.prototype = jQuery.extend(true, {}, CanisComponent.prototype);

CanisInstanceManager.prototype.objectClass = 'CanisInstanceManager';

CanisInstanceManager.prototype.defaultSettings = {
	'applications': {}
};

CanisInstanceManager.prototype.init = function() {
	var _this = this;
	var panelMenu = {};
	panelMenu['create'] = {};
	panelMenu['create']['icon'] = 'fa fa-plus';
	panelMenu['create']['label'] = 'Start Instance';
	panelMenu['create']['items'] = {};
	jQuery.each(this.settings.applications, function(id, app) {
		panelMenu['create']['items'][id] = {};
		// panelMenu['create']['items'][id]['onClick'] = function() { _this.startInstance(id); return false; };
		panelMenu['create']['items'][id]['label'] = app.name;
		panelMenu['create']['items'][id]['url'] = _this.settings.createInstanceUrl + '?application_id=' + id;
		panelMenu['create']['items'][id]['background'] = true;
	});
	var panelHeading = {
		'label': 'Instances',
		'menu': panelMenu
	};
	this.elements.$canvas = this.generatePanel(this.$element, panelHeading);
};


CanisInstanceManager.prototype.startInstance = function(applicationId) {
	console.log(applicationId);
};

$(function() {
	$('[data-instance-manager]').each(function() {
		var settings = $(this).data('instance-manager');
		$(this).data('instance-manager', new CanisInstanceManager($(this), settings));
	});
});