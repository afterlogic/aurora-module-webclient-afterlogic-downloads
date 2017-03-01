'use strict';

var
	ko = require('knockout'),
	moment = require('moment'),
	
//	AddressUtils = require('%PathToCoreWebclientModule%/js/utils/Address.js'),
	Types = require('%PathToCoreWebclientModule%/js/utils/Types.js')
	
//	Settings = require('modules/%ModuleName%/js/Settings.js')
;

/**
 * @constructor
 */
function CDownloadListItemModel()
{
	this.id = '';
	this.UUID = '';
	
	this.sDate = '';
	this.sEmail = '';
	this.sReferer = '';
	this.sIp = '';
	this.iProductId = '';
	this.iExternalProductId = '';
	this.sProductName = '';
	this.sProductVersion = '';
	this.sLicenseKey = '';
	this.bProductCommercial = '';
	this.iPackageId = '';
	this.sPackageName = '';
	
	this.deleted = ko.observable(false);
	this.checked = ko.observable(false);
	this.selected = ko.observable(false);
	this.recivedAnim = ko.observable(false).extend({'autoResetToFalse': 500});
}

/**
 *
 * @param {Object} oData
 */
CDownloadListItemModel.prototype.parse = function (oData)
{
	this.id =  Types.pInt(oData['iObjectId']);
	this.UUID =  Types.pString(oData['sUUID']);
	
	this.sDate =  moment.unix(oData.Date).format('YYYY-MM-DD HH:mm');
	this.sEmail = Types.pString(oData.Email);
	this.sReferer = Types.pString(oData.Referer);
	this.sIp = Types.pString(oData.Ip);
	this.iProductId = Types.pInt(oData.ProductId);
	this.iExternalProductId = Types.pInt(oData.ExternalProductId);
	this.sProductName = Types.pString(oData.ProductName);
	this.sProductVersion = Types.pString(oData.ProductVersion);
	this.sLicenseKey = Types.pString(oData.LicenseKey);
	this.bProductCommercial = !!oData.LicenseKey;
	this.iPackageId = Types.pInt(oData.PackageId);
	this.sPackageName = Types.pString(oData.PackageName);
};

module.exports = CDownloadListItemModel;
