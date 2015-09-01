function CanisInstanceManager($element, settings) {
    CanisComponent.call(this);
	this.$element = $element.addClass('instance-manager');
	this.instances = {};
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
	this._refresh();
	this.$element.on('refresh', function() {
		_this._refresh();
	});
	this.elements.canvas = this.generatePanel(this.$element, panelHeading);
	this.elements.$notice = $("<div />", {'class': 'alert alert-warning'}).html('').appendTo(this.elements.canvas.$body).hide();
	this.elements.$list = $("<div />", {'class': 'list-group'}).appendTo(this.elements.canvas.$body);
};

CanisInstanceManager.prototype.getApplication = function(applicationId) {
	if (this.settings.applications[applicationId] !== undefined) {
		return this.settings.applications[applicationId];
	}
	return false;
};

CanisInstanceManager.prototype._handleResponse = function(data) {
    var _this = this;
    var hasInstances = false;
    var current = _.keys(_this.instances);
    jQuery.each(data.instances, function(id, instance) {
    	current = _.without(current, id);
    	hasInstances = true;
    	if (_this.instances[id] === undefined) {
    		_this.instances[id] = new CanisInstance(_this, instance);
    	}
    	_this.instances[id].update(instance);
    });
	if (!hasInstances) {
		this.elements.$notice.show().html('No instances have been created');
	} else {
		this.elements.$notice.hide();
	}
	jQuery.each(current, function(i, id) {
		_this.instances[id].hide();
		delete _this.instances[id];
	});
};

CanisInstanceManager.prototype.scheduleRefresh = function() {
	var _this = this;
	if (this.scheduledRefresh !== undefined) {
		clearTimeout(this.scheduledRefresh);
	}
	this.scheduledRefresh = setTimeout(function() {
		_this._refresh();
	}, 5000);
};

CanisInstanceManager.prototype._refresh = function() {
	var _this = this;
	var ajaxSettings = {};
	ajaxSettings.url = this.settings.packageUrl;
	ajaxSettings.complete = function() {
		_this.scheduleRefresh();
	};
	ajaxSettings.success = function(data) {
		_this._handleResponse(data);
	};

	jQuery.ajax(ajaxSettings);
};

function CanisInstance(manager, instance) {
	this.manager = manager;
	this.instance = instance;
	this.elements = {};
	this.init();
}


CanisInstance.prototype.sendAction = function(action) {
	var _this = this;
	_this.startPendingAction(action.label);
	var ajaxSettings = {};
	ajaxSettings.type = 'GET';
	ajaxSettings.data = {
		'id': this.instance.id,
		'action': action.id
	};
	ajaxSettings.url = this.manager.settings.actionUrl;
	ajaxSettings.complete = function() {
		_this.clearPendingAction();
	};
	ajaxSettings.success = function(data) {
	};
	jQuery.ajax(ajaxSettings);
};

CanisInstance.prototype.clearPendingAction = function() {
	this.elements.actions.$buttonGroup.show();
};

CanisInstance.prototype.startPendingAction = function(actionDescription) {
	this.elements.actions.$buttonGroup.hide();
};

CanisInstance.prototype.init = function() {
	this.elements.$canvas = $("<div />", {'class': 'list-group-item'}).appendTo(this.manager.elements.$list);
	this.elements.$pendingAction = $("<div />", {'class': 'label label-default'}).hide().appendTo(this.elements.$canvas);
	this.elements.actions = {};
	this.elements.actions.$buttonGroup = $("<div />", {'class': 'btn-group pull-right'}).appendTo(this.elements.$canvas);
	this.elements.actions.$button = $("<a />", {'class': 'btn fa fa-chevron-down dropdown-toggle', 'href': '#', 'data-toggle': 'dropdown'}).appendTo(this.elements.actions.$buttonGroup);
	this.elements.actions.$menu = $("<ul />", {'class': 'dropdown-menu'}).appendTo(this.elements.actions.$buttonGroup);
	
	this.elements.$titleContainer = $("<h4 />", {'class': 'list-group-item-heading'}).appendTo(this.elements.$canvas);
	this.elements.$title = $("<span />", {'class': ''}).appendTo(this.elements.$titleContainer);
	this.elements.$titleBuffer = $("<span />", {'class': ''}).html(' ').appendTo(this.elements.$titleContainer);
	this.elements.$application = $("<small />", {'class': ''}).appendTo(this.elements.$titleContainer);
	this.elements.$info = $("<div />", {'class': 'list-group-item-text'}).appendTo(this.elements.$canvas);
	this.elements.$status = $("<div />", {'class': 'canis-instance-status'}).appendTo(this.elements.$info);
	this.elements.$uptime = $("<div />", {'class': 'canis-instance-uptime'}).hide().appendTo(this.elements.$info);
	this.elements.$serviceStatus = $("<div />", {'class': 'canis-instance-service-status'}).hide().appendTo(this.elements.$info);
}

CanisInstance.prototype.setState = function(state) {
	this.elements.$canvas.removeClass('list-group-item-success list-group-item-info list-group-item-danger list-group-item-warning');
	if (state) {
		this.elements.$canvas.addClass('list-group-item-'+state);
	}
};

CanisInstance.prototype.updateActions = function() {
	var _this = this;
	if (_.isEmpty(this.instance.instanceActions)) {
		this.elements.actions.$button.hide();
	} else {
		this.elements.actions.$button.show();
		this.elements.actions.$menu.html('');
		jQuery.each(this.instance.instanceActions, function(id, action) {
			action.id = id;
			var $li = $('<li />').appendTo(_this.elements.actions.$menu);
			var iconExtra = '';
			if (action.icon !== undefined) {
				iconExtra = '<span class="'+ action.icon +'"></span> ';
			}
			var $a = $('<a />', {'href': '#'}).html(iconExtra + action.label).appendTo($li);
			if (action.attributes !== undefined) {
				$a.attr(action.attributes);
			} 
			if (action.url !== undefined) {
				$a.attr({'href': action.url});
				if (action.background !== undefined && action.background) {
					$a.attr({'data-handler': 'background'});
				}
			} else {
				$a.on('click', function(e) {
					_this.elements.actions.$button.dropdown("toggle");
					e.preventDefault();
					_this.sendAction(action);
					return false;
				});
			}
		});
	}
};
CanisInstance.prototype.show = function() {
	this.elements.$canvas.show();
};
CanisInstance.prototype.hide = function() {
	this.elements.$canvas.hide();
};
CanisInstance.prototype.updateUptime = function() {
	
};
CanisInstance.prototype.updateServiceStatus = function() {
	
};

CanisInstance.prototype.update = function(instance) {
	this.instance = instance;
	this.elements.$title.html(instance.name);
	var application = this.manager.getApplication(instance.application_id);
	if (application) {
		this.elements.$application.html(application.name);
	}
	if (!instance.initialized) {
		this.setState('warning');
	} else {
		this.setState(false);
	}
	this.updateActions();
	switch (instance.status) {
		case 'uninitialized':
			this.elements.$status.html('Initialization queued');
		break;
		case 'starting':
			this.elements.$status.html('Initialization starting...');
		break;
		case 'creating_services':
			this.elements.$status.html('Initializing: Creating services');
		break;
		case 'starting_services':
			this.elements.$status.html('Initializing: Starting services');
		break;
		case 'waiting':
			this.elements.$status.html('Initializing: Waiting for services to start');
		break;
		case 'setting_up':
			this.elements.$status.html('Initializing: Setting up application');
		break;
		case 'verifying':
			this.elements.$status.html('Initializing: Verifying');
		break;
		case 'failed':
			this.elements.$status.html('Setup Failed');
			this.setState('danger');
		break;
		case 'running':
			this.elements.$status.html('Running');
			this.setState(false);
		break;
		case 'partially_running':
			this.elements.$status.html('Partially Running');
			this.setState('warning');
		break;
		case 'ready':
		case 'stopped':
			this.elements.$status.html('Stopped');
			this.setState('info');
		break;
		case 'terminating':
			this.elements.$status.html('Terminating');
			this.setState('danger');
		break;
		case 'terminated':
			this.elements.$status.html('Terminated');
			this.setState('danger');
		break;
		default:
			this.elements.$status.html('Unknown status: '+instance.status);
			this.setState('danger');
		break;
	}
}

$(function() {
	$('[data-instance-manager]').each(function() {
		var settings = $(this).data('instance-manager');
		$(this).data('instance-manager', new CanisInstanceManager($(this), settings));
	});
});