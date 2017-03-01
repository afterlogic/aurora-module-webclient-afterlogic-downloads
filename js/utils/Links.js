'use strict';

var
	Types = require('%PathToCoreWebclientModule%/js/utils/Types.js'),
	
	Settings = require('modules/%ModuleName%/js/Settings.js'),
	
	LinksUtils = {},
	
	sItemMarker = 'item',
	iItemMarkerLength = 4
;

/**
 * @param {string} sTemp
 * 
 * @return {boolean}
 */
function IsPageParam(sTemp)
{
	return ('p' === sTemp.substr(0, 1) && (/^[1-9][\d]*$/).test(sTemp.substr(1)));
};

/**
 * @param {string} sTemp
 * 
 * @return {boolean}
 */
function IsItemUUIDParam(sTemp)
{
	return sItemMarker === sTemp.substr(0, iItemMarkerLength);
};

/**
 * @param {string=} sSearch
 * @param {number=} iPage
 * @param {string=} sItemUUID
 * @param {string=} sAction
 * @returns {Array}
 */
LinksUtils.getItemsHash = function (sSearch, iPage, sItemUUID, sAction)
{
	var aParams = [Settings.HashModuleName];
	
	if (sSearch && sSearch !== '')
	{
		aParams.push(sSearch);
	}
	
	if (Types.isNumber(iPage))
	{
		aParams.push('p' + iPage);
	}
	
	if (sItemUUID && sItemUUID !== '')
	{
		aParams.push(sItemMarker + sItemUUID);
	}
	
	if (sAction && sAction !== '')
	{
		aParams.push(sAction);
	}
	
	return aParams;
};

/**
 * @param {Array} aParam
 * 
 * @return {Object}
 */
LinksUtils.parseHash = function (aParam)
{
	var
		iIndex = 0,
		sSearch = '',
		iPage = 1,
		sItemUUID = '',
		sAction = ''
	;

	if (Types.isNonEmptyArray(aParam))
	{
		if (aParam.length > iIndex && !IsPageParam(aParam[iIndex]) && !IsItemUUIDParam(aParam[iIndex]))
		{
			sSearch = Types.pString(aParam[iIndex]);
			iIndex++;
		}
		
		if (aParam.length > iIndex && IsPageParam(aParam[iIndex]))
		{
			iPage = Types.pInt(aParam[iIndex].substr(1));
			iIndex++;
			if (iPage <= 0)
			{
				iPage = 1;
			}
		}
		
		if (aParam.length > iIndex)
		{
			if (IsItemUUIDParam(aParam[iIndex]))
			{
				sItemUUID = Types.pString(aParam[iIndex].substr(iItemMarkerLength));
			}
			else
			{
				sAction = Types.pString(aParam[iIndex]);
			}
			iIndex++;
		}
	}
	
	return {
		'Search': sSearch,
		'Page': iPage,
		'ItemUUID': sItemUUID,
		'Action': sAction
	};
};

module.exports = LinksUtils;
