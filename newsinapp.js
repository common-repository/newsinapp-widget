var newsinapp = (function ($) {
	"use strict";

	function Newsinapp(publicKey, privateKey) {
		this.publicKey = publicKey;
		this.privateKey = privateKey;

		this.news = (function (newsinapp) {
			var my = {};
			var baseUrl = "http://api.newsinapp.io/topics/news/v1/?page=1&perPage=20&auth_api_key=";

			var formatNews = function (news) {
        		news.imageAvailable = news['images'][0] !== undefined;
	          	news.linkHref = news['links'] != null ? news['links'][0]['href'] : "";
	          	news.imageHref = news.imageAvailable ? news['images'][0]['href'] : "";
	          	news.title = news['title'] != null ? news['title'] : "";
	          	news.source = news['source'] != null ? news['source'] : "";
	          	news.subtitle = news['subTitle'] != null ? news['subTitle'] : "";
	          	return news;
			};

			my.getNews = function (options) {

				var jqxhr = $.getJSON(baseUrl + newsinapp.publicKey)
					.done(function(data) { 
						var col = [];
						for (var news in data.response.news) {
							col.push(formatNews(data.response.news[news]));
						};
						options.success(data, col); 
					})
					.fail(function(data) { 
						options.error(data); 
					});
			};

			my.startListening = function (options) {
				
			};

			my.stopListening = function (options) {
				
			};
			
			return my;
		}(this));

		this.topic = (function (newsinapp) {
			var my = {};
			var baseUrl = "http://api.newsinapp.io/topics/v1/?page=1&perPage=20&auth_api_key=";

			my.getTopics = function (options) {

				var jqxhr = $.getJSON(baseUrl + newsinapp.privateKey)
					.done(function(data) { 
						var col = [];
						for (var topic in data.response.topics) {
							col.push(data.response.topics[topic]);
						};
						options.success(data, col); 
					})
					.fail(function(data) { 
						options.error(data); 
					});
			};
			
			return my;
		}(this));

	}

	return {
		Newsinapp : Newsinapp
	};

}(jQuery));
