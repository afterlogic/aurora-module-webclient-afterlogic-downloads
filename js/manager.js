'use strict';

module.exports = function (oAppData) {
	var
		_ = require('underscore'),

		App = require('%PathToCoreWebclientModule%/js/App.js'),
		
		TextUtils = require('%PathToCoreWebclientModule%/js/utils/Text.js'),

		Settings = require('modules/%ModuleName%/js/Settings.js'),
		oSettings = _.extend({}, oAppData[Settings.ServerModuleName] || {}, oAppData['%ModuleName%'] || {})
	;
	
	Settings.init(oSettings);
	
	require('modules/%ModuleName%/js/enums.js');

//	var sAppHash = Settings.AppName ? TextUtils.getUrlFriendlyName(Settings.AppName) : Settings.HashModuleName;

//	if (App.getUserRole() === Enums.UserRole.SuperAdmin)
//	{
//		return {
//			/**
//			 * Registers admin settings tabs before application start.
//			 *
//			 * @param {Object} ModulesManager
//			 */
//			start: function (ModulesManager)
//			{
//				ModulesManager.run('AdminPanelWebclient', 'registerAdminPanelTab', [
//					function(resolve) {
//						require.ensure(
//							['modules/%ModuleName%/js/views/PerUserAdminSettingsView.js'],
//							function() {
//								resolve(require('modules/%ModuleName%/js/views/PerUserAdminSettingsView.js'));
//							},
//							"admin-bundle"
//						);
//					},
//					Settings.HashModuleName + '-user',
//					TextUtils.i18n('%MODULENAME%/LABEL_SETTINGS_TAB')
//				]);
//				ModulesManager.run('AdminPanelWebclient', 'registerAdminPanelTab', [
//					function(resolve) {
//						require.ensure(
//							['modules/%ModuleName%/js/views/AdminSettingsView.js'],
//							function() {
//								resolve(require('modules/%ModuleName%/js/views/AdminSettingsView.js'));
//							},
//							"admin-bundle"
//						);
//					},
//					Settings.HashModuleName + '-system',
//					TextUtils.i18n('%MODULENAME%/LABEL_SETTINGS_TAB')
//				]);
//			}
//		};
//	}
	if (App.getUserRole() === Enums.UserRole.NormalUser || App.getUserRole() === Enums.UserRole.Customer)
	{
		return {
			/**
			 * Returns list of functions that are return module screens.
			 * 
			 * @returns {Object}
			 */
			getScreens: function ()
			{
				var oScreens = {};
				
				oScreens[Settings.HashModuleName] = function () {
					return require('modules/%ModuleName%/js/views/MainView.js');
				};
				
				return oScreens;
			},

			/**
			 * Returns object of header item view of the module.
			 * 
			 * @returns {Object}
			 */
			getHeaderItem: function ()
			{
				var 
					CHeaderItemView = require('%PathToCoreWebclientModule%/js/views/CHeaderItemView.js'),
					oHeaderEntry = 	{};
				;

				oHeaderEntry = {
					item: new CHeaderItemView(TextUtils.i18n('%MODULENAME%/HEADING_BROWSER_TAB')),
					name: Settings.HashModuleName
				};
				
				return oHeaderEntry;
			}
		};
	}
	
	return null;
};
