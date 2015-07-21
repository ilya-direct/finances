<?php
use yii\helpers\Html;
use app\assets\PkAsset;

/* @var $this \yii\web\View */
/* @var $content string */

$asset= PkAsset::register($this);
$jsfolder=$asset->baseUrl.'/js';
$cssfolder=$asset->baseUrl.'/css';
$this->registerJs('var jsfolder="'.$jsfolder.'";'."\n".
				  'var cssfolder="'.$cssfolder.'";',\yii\web\View::POS_HEAD);

$this->beginPage();
?><!DOCTYPE html>
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge"/>
    <meta charset="utf-8"/>
	<title>univecom</title>
	<meta name="fb_admins_meta_tag" content=""/>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
	<link rel="apple-touch-icon" href="favicon.ico" type="image/x-icon"/>
	<link rel="canonical" href="http://vladspitsyn.wix.com/univecom"/>
	<meta http-equiv="X-Wix-Renderer-Server" content="app47.aus"/>
	<meta http-equiv="X-Wix-Meta-Site-Id" content="8966cfe2-984f-45aa-bf7c-ced3edd59df6"/>
	<meta http-equiv="X-Wix-Application-Instance-Id" content="e58cbd83-da8e-4725-ad56-9e933d6b2257"/>
	<meta http-equiv="X-Wix-Published-Version" content="72"/>
	<meta http-equiv="etag" content="8c2a35e2d1b49b0d93d2e46e10948ed9"/>
	<meta name="robots" content="noindex"/>
	<meta property="og:title" content="univecom"/>
	<meta property="og:type" content="article"/>
	<meta property="og:url" content="http://vladspitsyn.wix.com/univecom"/>
	<meta property="og:site_name" content="univecom"/>
	<meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE"/>
	<meta id="wixMobileViewport" name="viewport" content="minimum-scale=0.25, maximum-scale=1.2"/>
	<script>
            // BEAT MESSAGE
    try {
	    window.wixBiSession = {
		    initialTimestamp : Date.now(),
            viewerSessionId: 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c)
                    { var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8); return v.toString(16); }
            )
        };
    } catch (e){}
    // BEAT MESSAGE END
	</script>
    <!-- META DATA -->
	<script type="text/javascript">
    var serviceTopology = {
	    "serverName":"app47.aus",
	    "cacheKillerVersion":"1",
	    "staticServerUrl":"http://yii2/",
	    "usersScriptsRoot":"http://static.parastorage.com/services/wix-users/2.516.0",
	    "biServerUrl":"http://frog.wix.com/","userServerUrl":"http://users.wix.com/",
	    "billingServerUrl":"http://premium.wix.com/","mediaRootUrl":"http://static.wixstatic.com/",
	    "logServerUrl":"http://frog.wix.com/plebs","monitoringServerUrl":"http://TODO/",
	    "usersClientApiUrl":"https://users.wix.com/wix-users",
	    "publicStaticBaseUri":"http://static.parastorage.com/services/wix-public/1.165.0",
	    "basePublicUrl":"http://www.wix.com/","postLoginUrl":"http://www.wix.com/my-account",
	    "postSignUpUrl":"http://www.wix.com/new/account","baseDomain":"wix.com",
	    "staticMediaUrl":"http://yii2/images",
	    "staticAudioUrl":"http://storage.googleapis.com/static.wixstatic.com/mp3",
	    "emailServer":"http://assets.wix.com/common-services/notification/invoke",
	    "blobUrl":"http://static.parastorage.com/wix_blob",
	    "htmlEditorUrl":"http://editor.wix.com/html",
	    "siteMembersUrl":"https://users.wix.com/wix-sm",
	    "scriptsLocationMap":{
		    "wixapps":"http://static.parastorage.com/services/wixapps/2.461.16",
		    "tpa":"http://static.parastorage.com/services/tpa/2.1050.0",
		    "santa-resources":"http://static.parastorage.com/services/santa-resources/1.0.0",
		    "bootstrap":"http://static.parastorage.com/services/bootstrap/2.1229.4",
		    "ck-editor":"http://static.parastorage.com/services/ck-editor/1.87.2",
		    "it":"http://static.parastorage.com/services/experiments/it/1.37.0",
		    "skins":"http://static.parastorage.com/services/skins/2.1229.4",
		    "core":"http://static.parastorage.com/services/core/2.1229.4",
		    "sitemembers":"http://static.parastorage.com/services/sm-js-sdk/1.31.0",
		    "automation":"http://static.parastorage.com/services/automation/1.23.0",
		    "web":"http://static.parastorage.com/services/web/2.1229.4",
		    "ecommerce":"http://static.parastorage.com/services/ecommerce/1.193.0",
		    "hotfixes":"http://static.parastorage.com/services/experiments/hotfixes/1.15.0",
		    "langs":"http://static.parastorage.com/services/langs/2.554.0",
		    "santa-versions":"http://static.parastorage.com/services/santa-versions/1.315.0",
		    "ut":"http://static.parastorage.com/services/experiments/ut/1.2.0"},
	    "developerMode":false,"productionMode":true,"staticServerFallbackUrl":"https://sslstatic.wix.com/",
	    "staticVideoUrl":"http://video.wixstatic.com/","killerFeatureUrl":"http://api.aus.wixpress.com/wix-killer-feature-webapp",
	    "scriptsDomainUrl":"http://yii2/","userFilesUrl":"http://static.parastorage.com/",
	    "staticHTMLComponentUrl":"http://vladspitsyn.wix.com.usrfiles.com/","secured":false,
	    "ecommerceCheckoutUrl":"https://www.safer-checkout.com/","premiumServerUrl":"https://premium.wix.com/",
	    "appRepoUrl":"http://assets.wix.com/wix-lists-ds-webapp","digitalGoodsServerUrl":"http://dgs.wixapps.net/",
	    "wixCloudBaseDomain":"cloud.wix.com","publicStaticsUrl":"http://static.parastorage.com/services/wix-public/1.165.0",
	    "staticDocsUrl":"http://media.wix.com/ugd"};
        var santaModels = true;
    var rendererModel = {"metaSiteId":"8966cfe2-984f-45aa-bf7c-ced3edd59df6",
	    "siteInfo":{
		    "documentType":"UGC",
		    "applicationType":"HtmlWeb",
		    "siteId":"e58cbd83-da8e-4725-ad56-9e933d6b2257",
		    "siteTitleSEO":"univecom"},
	        "clientSpecMap":{"17":{"type":"sitemembers","applicationId":17,"collectionType":"Open","smcollectionId":"44374844-08a6-40f4-b5d1-df7d91c89061"},"6":{"type":"appbuilder","applicationId":6,"appDefinitionId":"3d590cbc-4907-4cc4-b0b1-ddf2c5edf297","instanceId":"13ef9b18-668f-9ffc-5ba4-0310a7056396","state":"Initialized"},"18":{"type":"public","applicationId":18,"appDefinitionId":"139ef4fa-c108-8f9a-c7be-d5f492a2c939","appDefinitionName":"Wix Smart Actions","instance":"GBSXBJR1M7QJD3swejrxcoCvjs7euStOep-_1ETpmEw.eyJpbnN0YW5jZUlkIjoiMTNmMDdkZTgtNWQ2Mi1iNmEwLTU2OTItN2RjZjEwNzBlZjZiIiwic2lnbkRhdGUiOiIyMDE1LTA3LTE4VDIyOjAyOjExLjY4MVoiLCJpcEFuZFBvcnQiOiI0Ni4yNDIuOC4yOC81MDUzMCIsImRlbW9Nb2RlIjpmYWxzZSwiYmlUb2tlbiI6IjlhOTZiMjBhLWM1MmQtZjMwYS1lOWVlLWIzMWNmZGE1NzI5ZCJ9","sectionPublished":true,"sectionMobilePublished":false,"sectionSeoEnabled":true,"widgets":{},"appRequirements":{"requireSiteMembers":false},"installedAtDashboard":true,"permissions":{"revoked":false}},"19":{"type":"public","applicationId":19,"appDefinitionId":"135c3d92-0fea-1f9d-2ba5-2a1dfb04297e","appDefinitionName":"Wix ShoutOut","instance":"5TSLlKSDfK85p0rJ3QelPPzR4ensZ_mc0qhmL5OuqXs.eyJpbnN0YW5jZUlkIjoiMTNmMDdkZTgtNjAxZC01ODhmLWY3MzItZGU5MTA3MWQ5YWQ4Iiwic2lnbkRhdGUiOiIyMDE1LTA3LTE4VDIyOjAyOjExLjY4MVoiLCJpcEFuZFBvcnQiOiI0Ni4yNDIuOC4yOC81MDUzMCIsImRlbW9Nb2RlIjpmYWxzZX0","sectionPublished":true,"sectionMobilePublished":false,"sectionSeoEnabled":true,"widgets":{},"appRequirements":{"requireSiteMembers":false},"installedAtDashboard":true,"permissions":{"revoked":false}}},"premiumFeatures":[],"geo":"RUS","languageCode":"ru","previewMode":false,"userId":"4503474f-eb52-4451-84f6-7977fb09f31f","siteMetaData":{"preloader":{"enabled":false},"adaptiveMobileOn":true,"quickActions":{"socialLinks":[],"colorScheme":"dark","configuration":{"quickActionsMenuEnabled":false,"navigationMenuEnabled":true,"phoneEnabled":false,"emailEnabled":false,"addressEnabled":false,"socialLinksEnabled":false}},"contactInfo":{"companyName":"","phone":"","fax":"","email":"","address":""}},"runningExperiments":{"autogeneratedshapesskins":"new","lang_no":"new","ngcore":"new","sandboxiframeineditor":"new","lang_nl":"new","wixappsgalleries":"new","ecomgalleries":"new","disablehorizontalmenu":"new","lazyprovision":"new","atntfixlists":"new","newfacebooklikebox":"new","lang_da":"new","exitmobilemode":"new","workaroundsaveddeadcompskin":"new","nougcanalytics":"new","appbuilderdeletetype":"new","sitepagesvalidation":"new","redirectfeature301data":"new","linkfixeroverride":"new","redirectfeature301":"new","lazyprovisiontemp":"new","animation3dfix":"new","landingpagesupport":"new","lang_sv":"new","wixappstranslation":"new","subscribeformsendnewsletter":"new","lesserwidthissue":"new","appbuildertags":"new","sitenavigationrefactor":"new","blogrss":"new","animationnewbehaviors":"new","customsitemenu":"new","blogmanager":"new"}};
    var publicModel = {
	    "domain":"wix.com","externalBaseUrl":"http:\/\/vladspitsyn.wix.com\/univecom",
	    "externalBaseUrl2":"http:\/\/yii2\/",
	    "unicodeExternalBaseUrl":"http:\/\/vladspitsyn.wix.com\/univecom",
	    "pageList":{"masterPage":[
		    "http:\/\/yii2/js/_71.json",
	  "http:\/\/yii2/js/_71.json",
      "http:\/\/fallback.wix.com\/wix-html-editor-pages-webapp\/page\/450347_b78a8d31409957e593d4cc3abf6ec166_71.json"],
		    "pages":[{"pageId":"c16s4",
			    "title":"ТЕХНОЛОГИИ",
			    "urls":[
				    "http:\/\/static.wixstatic.com\/sites\/450347_0681118e9701f3be4a2fb8fdd24da891_47.json.z?v=3",
				    "http:\/\/staticorigin.wixstatic.com\/sites\/450347_0681118e9701f3be4a2fb8fdd24da891_47.json.z?v=3",
				    "http:\/\/fallback.wix.com\/wix-html-editor-pages-webapp\/page\/450347_0681118e9701f3be4a2fb8fdd24da891_47.json"]},
			    {"pageId":"c1gd9","title":"ГЛАВНАЯ",
			"urls":["http:\/\/yii2/js/_65.json",
				"http:\/\/yii2/js/_65.json",
				"http:\/\/fallback.wix.com\/wix-html-editor-pages-webapp\/page\/450347_d4e5b1f59ddd06f271c686d05ba0fce0_65.json"
			]},{"pageId":"c1se","title":"О КАФЕДРЕ","urls":["http:\/\/static.wixstatic.com\/sites\/450347_510079d24e8568c86f8ab79c10cdc9c8_47.json.z?v=3","http:\/\/staticorigin.wixstatic.com\/sites\/450347_510079d24e8568c86f8ab79c10cdc9c8_47.json.z?v=3","http:\/\/fallback.wix.com\/wix-html-editor-pages-webapp\/page\/450347_510079d24e8568c86f8ab79c10cdc9c8_47.json"]},{"pageId":"c11m6","title":"ОБУЧЕНИЕ","urls":["http:\/\/static.wixstatic.com\/sites\/450347_460fdf3ac038877d50ae0daa3d74b424_47.json.z?v=3","http:\/\/staticorigin.wixstatic.com\/sites\/450347_460fdf3ac038877d50ae0daa3d74b424_47.json.z?v=3","http:\/\/fallback.wix.com\/wix-html-editor-pages-webapp\/page\/450347_460fdf3ac038877d50ae0daa3d74b424_47.json"]},{"pageId":"ccjp","title":"НАУКА","urls":["http:\/\/static.wixstatic.com\/sites\/450347_e0392d95c26e3c4cd57ebae9943db148_47.json.z?v=3","http:\/\/staticorigin.wixstatic.com\/sites\/450347_e0392d95c26e3c4cd57ebae9943db148_47.json.z?v=3","http:\/\/fallback.wix.com\/wix-html-editor-pages-webapp\/page\/450347_e0392d95c26e3c4cd57ebae9943db148_47.json"]},{"pageId":"c1ylq","title":"ФЕДЕРАЛЬНЫЙ ЦЕНТР","urls":["http:\/\/static.wixstatic.com\/sites\/450347_85f59f11e6d2c8a386e695abf376a112_47.json.z?v=3","http:\/\/staticorigin.wixstatic.com\/sites\/450347_85f59f11e6d2c8a386e695abf376a112_47.json.z?v=3","http:\/\/fallback.wix.com\/wix-html-editor-pages-webapp\/page\/450347_85f59f11e6d2c8a386e695abf376a112_47.json"]},{"pageId":"c6kh","title":"О Нас 1","urls":["http:\/\/static.wixstatic.com\/sites\/450347_fa536120f22a8cb3e0bf7124b10fc4fb_47.json.z?v=3","http:\/\/staticorigin.wixstatic.com\/sites\/450347_fa536120f22a8cb3e0bf7124b10fc4fb_47.json.z?v=3","http:\/\/fallback.wix.com\/wix-html-editor-pages-webapp\/page\/450347_fa536120f22a8cb3e0bf7124b10fc4fb_47.json"]},{"pageId":"c1mc5","title":"ЭЛЕКТРОННЫЙ ЖУРНАЛ","urls":["http:\/\/static.wixstatic.com\/sites\/450347_03bce9eaf0d9bdc7ef6106d58043333a_47.json.z?v=3","http:\/\/staticorigin.wixstatic.com\/sites\/450347_03bce9eaf0d9bdc7ef6106d58043333a_47.json.z?v=3","http:\/\/fallback.wix.com\/wix-html-editor-pages-webapp\/page\/450347_03bce9eaf0d9bdc7ef6106d58043333a_47.json"]},{"pageId":"c13w","title":"КОНТАКТНАЯ ИНФОРМАЦИЯ","urls":["http:\/\/static.wixstatic.com\/sites\/450347_05b08b8526c518670614abcfa7faea0c_47.json.z?v=3","http:\/\/staticorigin.wixstatic.com\/sites\/450347_05b08b8526c518670614abcfa7faea0c_47.json.z?v=3","http:\/\/fallback.wix.com\/wix-html-editor-pages-webapp\/page\/450347_05b08b8526c518670614abcfa7faea0c_47.json"]},{"pageId":"c70d","title":"БИБЛИОТЕКА","urls":["http:\/\/static.wixstatic.com\/sites\/450347_9c14db1e0eac46c3efe6c13d6d9c2073_47.json.z?v=3","http:\/\/staticorigin.wixstatic.com\/sites\/450347_9c14db1e0eac46c3efe6c13d6d9c2073_47.json.z?v=3","http:\/\/fallback.wix.com\/wix-html-editor-pages-webapp\/page\/450347_9c14db1e0eac46c3efe6c13d6d9c2073_47.json"]}],"mainPageId":"c1gd9"},"timeSincePublish":189558692,"favicon":""};
    var googleAnalytics = "";
    var googleRemarketing = "";
    var facebookRemarketing = "";
    var yandexMetrikaData = {};

</script>
    <meta name="fragment" content="!"/>
    <script type="text/javascript">
         var santaBase = '';
        var clientSideRender = true;
    </script>
	<?php $this->head(); ?>
</head>
<body>
<?php $this->beginBody() ?>
	<div id="SITE_CONTAINER"></div>
	<div comp="wysiwyg.viewer.components.WixAds" skin="wysiwyg.viewer.skins.wixadsskins.WixAdsWebSkin" id="wixFooter"></div>
<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage() ?>