'use strict';
var
	_ = require('underscore'),
	
	TextUtils = require('%PathToCoreWebclientModule%/js/utils/Text.js'),
	Types = require('%PathToCoreWebclientModule%/js/utils/Types.js')
;

module.exports = {
	ServerModuleName: '%ModuleName%',
	HashModuleName: TextUtils.getUrlFriendlyName('%ModuleName%'),
	
	ItemsPerPage: 20,

	/**
	 * Initializes settings from AppData object sections.
	 * 
	 * @param {Object} oAppData Object contained modules settings.
	 */
	init: function (oAppData)
	{
		var oAppDataSection = oAppData['%ModuleName%'];
		
		if (!_.isEmpty(oAppDataSection))
		{
			this.ItemsPerPage = Types.pPositiveInt(oAppDataSection.ItemsPerPage, this.ItemsPerPage);
			
//			this.EIframeAppAuthMode = oAppDataSection.EIframeAppAuthMode;
//			this.EIframeAppTokenMode = oAppDataSection.EIframeAppTokenMode;
		}
	}
	
	/**
	 * Updates module settings after editing.
	 * 
	 * @param {string} sLogin New value of setting 'Login'
	 * @param {boolean} bHasPassword Indicates if user has custom password
	 */
//	update: function (sLogin, bHasPassword)
//	{
//		this.Login = sLogin;
//		this.HasPassword = bHasPassword;
//	},
	
	/**
	 * Updates admin module settings after editing.
	 * 
	 * @param {int} iAuthMode
	 */
//	updateAdmin: function (sAppName, iAuthMode, iTokenMode, sUrl)
//	{
//		this.AppName = sAppName;
//		this.AuthMode = iAuthMode;
//		this.TokenMode = iTokenMode;
//		this.Url = sUrl;
//	}
};
