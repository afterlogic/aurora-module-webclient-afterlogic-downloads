'use strict';

var
	_ = require('underscore'),
	ko = require('knockout'),
	$ = require('jquery'),
	moment = require('moment'),
	
	
	TextUtils = require('%PathToCoreWebclientModule%/js/utils/Text.js'),
	Types = require('%PathToCoreWebclientModule%/js/utils/Types.js'),
	Utils = require('%PathToCoreWebclientModule%/js/utils/Common.js'),
	LinksUtils = require('modules/%ModuleName%/js/utils/Links.js'),
	Routing = require('%PathToCoreWebclientModule%/js/Routing.js'),
	
	CSelector = require('%PathToCoreWebclientModule%/js/CSelector.js'),
	CPageSwitcherView = require('%PathToCoreWebclientModule%/js/views/CPageSwitcherView.js'),
	
	Api = require('%PathToCoreWebclientModule%/js/Api.js'),
	App = require('%PathToCoreWebclientModule%/js/App.js'),
//	Screens = require('%PathToCoreWebclientModule%/js/Screens.js'),
	Ajax = require('%PathToCoreWebclientModule%/js/Ajax.js'),
	Popups = require('%PathToCoreWebclientModule%/js/Popups.js'),
	ConfirmPopup = require('%PathToCoreWebclientModule%/js/popups/ConfirmPopup.js'),
	
	CDownloadListItemModel = require('modules/%ModuleName%/js/models/CDownloadListItemModel.js'),
	
	CAbstractScreenView = require('%PathToCoreWebclientModule%/js/views/CAbstractScreenView.js'),
	
	Settings = require('modules/%ModuleName%/js/Settings.js'),
	Chart = require('modules/%ModuleName%/js/vendor/chart.js')
;

/**
 * View that is used as screen of the module. Inherits from CAbstractScreenView that has showing and hiding methods.
 * 
 * @constructor
 */

function CMainView()
{
	CAbstractScreenView.call(this, '%ModuleName%');
	
	/**
	 * Text for displaying in browser title.
	 */
	this.browserTitle = ko.observable(TextUtils.i18n('%MODULENAME%/HEADING_BROWSER_TAB'));
	this.downloadsList = ko.observableArray([]);
	this.downloadsCount = ko.observable(0);
	
//	list, fSelectCallback, fDeleteCallback, fDblClickCallback, fEnterCallback, multiplyLineFactor,
//	bResetCheckedOnClick, bCheckOnSelect, bUnselectOnCtrl, bDisableMultiplySelection
	this.selector = new CSelector(
		this.downloadsList,
		_.bind(this.viewItem, this),
		_.bind(this.deleteItem, this)
		
	);
	
	this.isCheckedOrSelected = ko.observable(false);
	
	this.selector.listCheckedOrSelected.subscribe(function (aList) {
		this.isCheckedOrSelected(0 < this.selector.listCheckedOrSelected().length);
	}, this);
	
	this.checkAll = this.selector.koCheckAll();
	this.checkAllIncomplite = this.selector.koCheckAllIncomplete();

//	this.isCheckedOrSelected = ko.computed(function () {
//		return 0 < this.selector.listCheckedOrSelected().length;
//	}, this);

	this.pageSwitcherLocked = ko.observable(false);
	this.oPageSwitcher = new CPageSwitcherView(0, Settings.ItemsPerPage);
	this.oPageSwitcher.currentPage.subscribe(function (iCurrentpage) {
		if (!this.pageSwitcherLocked())
		{
			Routing.setHash(LinksUtils.getItemsHash(this.search(), this.oPageSwitcher.currentPage()));
		}
	}, this);
	this.currentPage = ko.observable(1);
	

	this.loadingList = ko.observable(false);
	this.preLoadingList = ko.observable(false);
	this.loadingList.subscribe(function (bLoading) {
		this.preLoadingList(bLoading);
	}, this);
	
	this.isEmptyList = ko.computed(function () {
		return 0 === this.downloadsList().length;
	}, this);
	
	this.listChanged = ko.computed(function () {
		return [
			this.oPageSwitcher.currentPage(),
			this.oPageSwitcher.perPage()
		];
	}, this);
	
	this.selectedItem = ko.observable(null);
	this.loadingViewPane = ko.observable(false);
	
	this.isSearchFocused = ko.observable(false);
	this.searchInput = ko.observable('');
	this.search = ko.observable('');
	
	this.isSearch = ko.computed(function () {
		return this.search() !== '';
	}, this);
	
	this.searchText = ko.computed(function () {
		return TextUtils.i18n('%MODULENAME%/INFO_SEARCH_RESULT', {
			'SEARCH': this.search()
		});
	}, this);
	
	this.searchSubmitCommand = Utils.createCommand(this, function () {
		Routing.setHash(LinksUtils.getItemsHash(this.searchInput()));
	});
	
	this.deleteCommand = Utils.createCommand(this, this.deleteItem, this.isCheckedOrSelected);

	this.chartCont = ko.observable(null);

	this.rangeType = ko.observable('month');

	this.currentRange = ko.observable();

	this.chartList = ko.observable([]);

	this.oChart = null;

	this.loadingChartDataStatus = ko.observable(false);

	App.broadcastEvent('%ModuleName%::ConstructView::after', {'Name': this.ViewConstructorName, 'View': this});
}

_.extendOwn(CMainView.prototype, CAbstractScreenView.prototype);


CMainView.prototype.ViewTemplate = '%ModuleName%_MainView';
CMainView.prototype.ViewConstructorName = 'CMainView';
CMainView.prototype.onBind = function ()
{

};
CMainView.prototype.onRoute = function (aParams)
{
	var 
		oParams = LinksUtils.parseHash(aParams),
		bNeedToRequestItems = false
	;
	this.pageSwitcherLocked(true);
	if (this.oPageSwitcher.perPage() !== Settings.ItemsPerPage)
	{
		bNeedToRequestItems = true;
	}
	if (this.search() !== oParams.Search)
	{
		this.oPageSwitcher.clear();
		this.oPageSwitcher.perPage(Settings.ItemsPerPage);
	}
	else
	{
		this.oPageSwitcher.setPage(oParams.Page, Settings.ItemsPerPage);
	}
	this.pageSwitcherLocked(false);
	
	if (oParams.Page !== this.oPageSwitcher.currentPage())
	{
		Routing.replaceHash(LinksUtils.getItemsHash(oParams.Search, this.oPageSwitcher.currentPage()));
	}

	if (this.downloadsList().length === 0)
	{
		bNeedToRequestItems = true;
	}
	
	if (this.currentPage() !== oParams.Page)
	{
		this.currentPage(oParams.Page);
		bNeedToRequestItems = true;
	}
	
	if (this.search() !== oParams.Search)
	{
		this.search(oParams.Search);
		this.searchInput(oParams.Search);
		bNeedToRequestItems = true;
	}
	
	if (oParams.ItemUUID && (!this.selectedItem() || this.selectedItem().UUID !== oParams.ItemUUID))
	{
//		if (this.downloadsList().length === 0)
//		{
//			this.contactUidForRequest(oParams.ItemUUID);
//		}
//		else
//		{
			this.requestItem(oParams.ItemUUID);
//		}
	}
	
	if (bNeedToRequestItems)
	{
		this.requestDownloadsList();
	}
};

CMainView.prototype.requestDownloadsList = function ()
{
	this.loadingList(true);
	Ajax.send(
		Settings.ServerModuleName,
		'GetItems', 
		{
			'Offset': (this.currentPage() - 1) * Settings.ItemsPerPage,
			'Limit': Settings.ItemsPerPage,
//			'Limit': 500,
	//		'SortField': Enums.ContactSortField.Name,
			'Search': this.search()
	//		'GroupUUID': sGroupUUID,
	//		'Storage': sStorage
		},
		this.onGetDownloadsListResponse,
		this
	);
};

CMainView.prototype.refreshDownloads = function ()
{
	this.requestDownloadsList();
	this.requestDownloadsCartData();
};

CMainView.prototype.changeRange = function (sRangeType)
{
	this.rangeType(sRangeType);
	this.requestDownloadsCartData();
};

CMainView.prototype.getSpecificDateRange = function (oDate, iDayCount, sInterval, sDateFormat)
{
	var 
		oResult = {},
		i = iDayCount,
		oMoment = moment(oDate).subtract(iDayCount+1, sInterval)
	;

	for (; i >= 0; i--){
		var oDay = oMoment.add(1, sInterval);

		oResult[oDay.format(sDateFormat)] = 0;
	}

	return oResult;
};

CMainView.prototype.requestDownloadsCartData = function ()
{
	this.loadingChartDataStatus(false);
	
	var 
		oCurrentDate = new Date(),
		oMoment = moment(oCurrentDate),
		sFromDate = '',
		sTillDate = moment(oCurrentDate).add(1, 'days').format('YYYY-MM-DD')
	;

	switch (this.rangeType())
	{
		case 'week':
			sFromDate = oMoment.subtract(7, 'days').format('YYYY-MM-DD');
			break;
		case 'month':
			sFromDate = oMoment.subtract(30, 'day').format('YYYY-MM-DD');
			break;
		case 'year':
			sFromDate = oMoment.subtract(12, 'months').format('YYYY-MM-DD');
			break;
	}
	Ajax.send(
		Settings.ServerModuleName,
		'GetItemsForChart', 
		{
			'Search': this.search(),
			'FromDate': sFromDate,
			'TillDate': sTillDate
		},
//		this.onGetDownloadsListResponse,
		function (oResponse) {
			this.loadingChartDataStatus(true);
			
			if(oResponse.Result)
			{
				this.chartList(oResponse.Result.List);
			}
		},
		this
	);
};

CMainView.prototype.onGetDownloadsListResponse = function (oResponse)
{
	var oResult = oResponse.Result;
	if (oResult)
	{
		var
			iItemsCount = Types.pInt(oResult.ItemsCount),
			aNewCollection = Types.isNonEmptyArray(oResult.List) ? _.compact(_.map(oResult.List, function (oItemData) {
				var oItem = new CDownloadListItemModel();
				oItem.parse(oItemData);
				return oItem;
			})) : [],
			oSelected  = this.selector.itemSelected(),
			oNewSelected  = oSelected ? _.find(aNewCollection, function (oItem) {
//				return oSelected.UUID() === oItem.UUID();
			}) : null,
			aChecked = this.selector.listChecked(),
			aCheckedIds = (aChecked && 0 < aChecked.length) ? _.map(aChecked, function (oItem) {
//				return oItem.UUID();
			}) : []
		;

		if (Types.isNonEmptyArray(aCheckedIds))
		{
			_.each(aNewCollection, function (oContactItem) {
				oContactItem.checked(-1 < $.inArray(oContactItem.UUID(), aCheckedIds));
			});
		}

		this.downloadsList(aNewCollection);
		this.oPageSwitcher.setCount(iItemsCount);
		this.downloadsCount(iItemsCount);

		if (oNewSelected)
		{
//			this.selector.itemSelected(oNewSelected);
//			this.requestContact(oNewSelected.UUID());
		}
		console.log(this.downloadsList())
	}
	else
	{
		Api.showErrorByCode(oResponse);
	}
	
	this.loadingList(false);
};

/**
 * @param {string} sItemUUID
 */
CMainView.prototype.requestItem = function (sItemUUID)
{
	this.loadingViewPane(true);
	
	var oItem = _.find(this.downloadsList(), function (oItem) {
		return oItem.UUID === sItemUUID;
	});
	
	if (oItem)
	{
		this.selector.itemSelected(oItem);
		this.selectedItem(oItem);
	}
	else
	{
		Ajax.send(Settings.ServerModuleName, 'GetItem', { 'UUID': sItemUUID }, this.onGetItemResponse, this);
//		this.contactUidForRequest(sItemUUID);
		this.selector.itemSelected(null);
		this.selectedItem(null);
	}
};

/**
 * @param {Object} oResponse
 */
CMainView.prototype.onGetItemResponse = function (oResponse)
{
	var oResult = oResponse.Result;
	if (oResult)
	{
		var
			oObject = new CDownloadListItemModel()
//			oSelected = this.selector.itemSelected()
		;

		oObject.parse(oResult);
		
//		if (oSelected && oSelected.UUID() === oObject.uuid())
//		{
			this.selectedItem(oObject);
//		}
	}
	else
	{
		Api.showErrorByCode(oResponse);
	}
};

CMainView.prototype.viewItem = function (oItem)
{
	this.selectedItem(oItem);
	Routing.setHash(LinksUtils.getItemsHash(this.search(), this.oPageSwitcher.currentPage(), oItem.UUID));
};

CMainView.prototype.deleteItem = function ()
{
	var
		aChecked = this.selector.listCheckedOrSelected(),
		iCount = aChecked.length,
		sConfirmText = TextUtils.i18n('%MODULENAME%/CONFIRM_DELETE_CONTACTS_PLURAL', {
            'COUNT': iCount,
		}),
		fDeleteItems = _.bind(function (bResult) {
			if (bResult)
			{
				this.deleteItems(aChecked);
			}
		}, this)
	;
	console.log('test1 - ');
	console.log(sConfirmText);
	console.log('test2 - ');

	Popups.showPopup(ConfirmPopup, [sConfirmText, fDeleteItems]);
};

CMainView.prototype.deleteItems = function (aCheckedItems)
{
	var
		self = this,
//		oMainContact = this.selectedContact(),
		aDownloadIds = _.map(aCheckedItems, function (oItem) {
			return oItem.id;
		})
//		aContactUUIDs = _.map(aChecked, function (oItem) {
//			return oItem.UUID;
//		})
	;
		this.preLoadingList(true);

//		_.each(aCheckedItems, function (oItem) {
//			if (oItem)
//			{
//				ContactsCache.clearInfoAboutEmail(oContact.Email());

//				if (oMainContact && !oContact.IsGroup() && !oContact.ReadOnly() && !oMainContact.readOnly() && oMainContact.uuid() === oContact.UUID())
//				{
//					oMainContact = null;
//					this.selectedContact(null);
//				}
//			}
//		}, this);

		_.each(this.downloadsList(), function (oItem) {
			if (_.contains(aCheckedItems, oItem))
			{
				oItem.deleted(true);
			}
		});

		_.delay(function () {
			self.downloadsList.remove(function (oItem) {
				return oItem.deleted();
			});
		}, 500);

    console.log(aDownloadIds)
		Ajax.send(Settings.ServerModuleName, 'DeleteItems', { 'Ids': aDownloadIds}, function (oResponse) {
			if (!oResponse.Result)
			{
				Api.showErrorByCode(oResponse, TextUtils.i18n('%MODULENAME%/ERROR_DELETE_CONTACTS'));
			}
			this.requestDownloadsList();
		}, this);
};

CMainView.prototype.onClearSearchClick = function ()
{
	// initiation empty search
	this.searchInput('');
	this.searchSubmitCommand();
};

CMainView.prototype.onShow = function ()
{
	this.selector.useKeyboardKeys(true);
	this.oPageSwitcher.show();
};

CMainView.prototype.onHide = function ()
{
	this.selector.listCheckedOrSelected(false);
	this.selector.useKeyboardKeys(false);
//	this.selectedItem(null);
	this.oPageSwitcher.hide();
};

CMainView.prototype.onBind = function ()
{
	this.requestDownloadsCartData();

	this.selector.initOnApplyBindings(
		'.items_sub_list .item',
		'.items_sub_list .selected.item',
		'.items_sub_list .item .custom_checkbox',
		$('.contact_list', this.$viewDom),
		$('.contact_list_scroll.scroll-inner', this.$viewDom)
	);
	
	this.hotKeysBind();

	if (this.chartCont()[0])
	{
		// this.oChart = new Chartist.Line(this.chartCont()[0], null, {
		// 	fullWidth: true,
		// 	chartPadding: {
		// 		right: 40
		// 	}
		// });

		var ctx = document.getElementById("myPieChart");
		this.oChart = new Chart(ctx, {
            type: 'line',
			data: {
				datasets: [
                    {
                        label: 'Advert',
                        backgroundColor: [
                            'rgba(249, 242, 180, 0.5)'
                        ],
                        borderColor: [
                            'rgb(249, 242, 180)'
                        ],
                        pointBackgroundColor: "rgb(249, 242, 180)",
                        pointHoverBackgroundColor: "rgb(249, 242, 180)",
                        pointHoverBorderColor: "rgb(249, 242, 180)",
                        borderWidth: 1,
                        pointRadius: 3,
                        pointBorderWidth: 1,
                        pointHoverRadius: 5,
                        lineTension: 0,
                    },
					{
						label: 'General',
						backgroundColor: [
							'rgba(120, 184, 240, 0.5)'
						],
						borderColor: [
							'rgba(120, 184, 240, 1)'
						],
						pointBackgroundColor: "rgba(120, 184, 240, 1)",
						pointHoverBackgroundColor: "rgba(120, 184, 240, 1)",
						pointHoverBorderColor: "rgba(40, 123, 139, 1)",
						borderWidth: 1,
						pointRadius: 3,
						pointBorderWidth: 1,
						pointHoverRadius: 5,
						lineTension: 0,
					},
				]
			},
			options: {
				maintainAspectRatio: false,
				scales: {
					yAxes: [{

						ticks: {
							beginAtZero:true
						}
					}]
				},
				legend:{
					//display: false
				},
				tooltips:{
					displayColors: false,
					backgroundColor: "rgba(40, 123, 139, 1)"
				},
				animation:{
					duration: 0
				}
			}
		});
	}

	this.chartList.subscribe(function (aDownloads) {
		if(this.oChart)
		{
			var 
				oGroupedDownloads,
                oGroupedDownloadsGa,
				allRangeDays,
				oDate = new Date(),
				sDisplayRange = ''
			;

			switch(this.rangeType())
			{
				case 'week':
					allRangeDays = this.getSpecificDateRange(oDate, 7, 'days', 'MM-DD');
					sDisplayRange = moment(oDate).subtract(7, 'days').format("YYYY-MM-DD") + ' / ' + moment(oDate).format("YYYY-MM-DD");
					
					aDownloads.forEach(function (i) {
						i.Date = moment(i.Date).format('MM-DD');
					});
					break;
					
				case 'month':
					allRangeDays = this.getSpecificDateRange(oDate, 30, 'days', 'MM-DD');
					sDisplayRange = moment(oDate).subtract(30, 'days').format("YYYY-MM-DD") + ' / ' + moment(oDate).format("YYYY-MM-DD");
					
					aDownloads.forEach(function (i) {
						i.Date = moment(i.Date).format('MM-DD');
					});
					break;
					
				case 'year':
					allRangeDays = this.getSpecificDateRange(oDate, 12, 'months', 'YYYY-MM');
					sDisplayRange = moment(oDate).subtract(12, 'months').format("YYYY-MM-DD") + ' / ' + moment(oDate).format("YYYY-MM-DD");

					aDownloads.forEach(function (i) {
						i.Date = moment(i.Date).format('YYYY-MM');
					});
					break;
			}
			this.currentRange(sDisplayRange);

            var oAllGaDownloads = _.filter(aDownloads, function(item){ return item.Ga !== 0});

			oGroupedDownloads = _.extendOwn(_.clone(allRangeDays), _.countBy(aDownloads, "Date"));
			oGroupedDownloadsGa = _.extendOwn(_.clone(allRangeDays), _.countBy(oAllGaDownloads, "Date"));

            this.oChart.data.datasets[0].data = _.values(oGroupedDownloadsGa);
            this.oChart.data.datasets[1].data = _.values(oGroupedDownloads);
			this.oChart.data.labels = _.keys(allRangeDays);
			this.oChart.update();
		}
	}, this);
};

CMainView.prototype.searchFocus = function ()
{
	if (this.selector.useKeyboardKeys() && !Utils.isTextFieldFocused())
	{
		this.isSearchFocused(true);
	}
};

CMainView.prototype.hotKeysBind = function ()
{
	var bFirstItemFlag = false;

	$(document).on('keydown', _.bind(function(ev) {
		var
			nKey = ev.keyCode,
			oFirstItem = this.downloadsList()[0],
			bListIsFocused = this.isSearchFocused(),
			bFirstItemSelected = false
		;

		if (this.shown() && !Utils.isTextFieldFocused() && !bListIsFocused && ev && nKey === Enums.Key.s)
		{
			ev.preventDefault();
			this.searchFocus();
		}

		else if (oFirstItem)
		{
			bFirstItemSelected = oFirstItem.selected();

			if (oFirstItem && bListIsFocused && ev && nKey === Enums.Key.Down)
			{
				this.isSearchFocused(false);
				this.selector.itemSelected(oFirstItem);

				bFirstItemFlag = true;
			}
			else if (!bListIsFocused && bFirstItemFlag && bFirstItemSelected && ev && nKey === Enums.Key.Up)
			{
				this.isSearchFocused(true);
				this.selector.itemSelected(false);
				
				bFirstItemFlag = false;
			}
			else if (bFirstItemSelected)
			{
				bFirstItemFlag = true;
			}
			else if (!bFirstItemSelected)
			{
				bFirstItemFlag = false;
			}
		}
	}, this));
};

module.exports = new CMainView();