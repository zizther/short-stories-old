<?php
/**
 * General Configuration
 *
 * All of your system's general configuration settings go in here. You can see a
 * list of the available settings in vendor/craftcms/cms/src/config/GeneralConfig.php.
 *
 * @see craft\config\GeneralConfig
 */

 // The site basepath
 define('BASEPATH', realpath(dirname(__FILE__) . '/../') . '/');

 // The site URL
 define('SITEURL', getenv('PRIMARY_SITE_URL'));

 $env = getenv('ENVIRONMENT');
 $isDev = $env === 'dev';
 $isProd = $env === 'production';

return [
    // Global settings
    '*' => [

        // Aliases / ENV variables
        'aliases' => [
            '@baseUrl' => SITEURL,
            '@basePath' => BASEPATH,
            '@web' => SITEURL,
            '@webroot' => dirname(__DIR__) . '/web',
            '@assetBaseUrl' => '@web/assets',
            '@assetBasePath' => '@webroot/assets',
        ],

        // Whether administrative changes should be allowed
        'allowAdminChanges' => $isDev,

        // Cache duration
	    'cacheDuration' => 'P1W',

        // Control Panel trigger word
        'cpTrigger' => getenv('CP_TRIGGER') ?: 'admin',

        // Custom
        'custom' => [
            'craftEnv' => getenv('ENVIRONMENT'), // server env
            'beta' => false, // whether to show Beta features
            'production' => true, // whether to use the production assets and files
            'unitPrefixMin' => 999, // Min number before prefixing numbers
            'verifiedFollowersCount' => 1000, // Number of followers before being allowed to be verified
            'verifiedStoriesCount' => 2, // Number of stories before being allowed to be verified
            'slugHash' => [
                'length' => 11,
                'alphabet' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_',
            ],
        ],

        // Whether Dev Mode should be enabled (see https://craftcms.com/guides/what-dev-mode-does)
        'devMode' => $isDev,

        // Whether crawlers should be allowed to index pages and following links
        'disallowRobots' => !$isProd,

        // Template caching
        'enableTemplateCaching' => !$isDev,

        // Override the session location - appId is also required
        // this is to make sure that sessions and logins are not lost between
        // deployments. It's very annoying to lose access to the backend every time a deployment gets run.
        'overridePhpSessionLocation' => true,

        // Default Week Start Day (0 = Sunday, 1 = Monday...)
        'defaultWeekStartDay' => 0,

        // Whether generated URLs should omit "index.php"
        'omitScriptNameInUrls' => true,

        // The secure key Craft will use for hashing and encrypting data
        'securityKey' => getenv('SECURITY_KEY'),

        // Remove Craft from header request
		// Services won't be able to detect what CMS is being used
		'sendPoweredByHeader' => false,

        // Word separater
	  	'slugWordSeparator' => '-',

	    // Cache duration
	    'cacheDuration' => 'P3M',

        // DB backup on update
        'backupOnUpdate' => true,

        // Use email as username
        'useEmailAsUsername' => false,

        // Login Path when using {% loginRequired %}
        'loginPath' => 'login',

        // Generate image transform before page load
        'generateTransformsBeforePageLoad' => true,

        // Max revisions
        'maxRevisions' => 8,

        // Default search term options
        'defaultSearchTermOptions' => array(
            'subLeft' => true,
            'subRight' => true,
        ),

        // Ascii character support
        'limitAutoSlugsToAscii' => true,
        'convertFilenamesToAscii' => true,

        // The maximum number of increments Craft will apply to a slug while
        // searching for one that will result in an element having a unique URL,
        // before giving up and throwing an error.
        //
        // Uncommnent to set limit, the reason for not setting as limit is so
        // Craft can use it as a default before it being updated

        //'maxSlugIncrement' => 1,

        // Max invalid logins
        'maxInvalidLogins' => 8,

        // The amount of time a user must wait before re-attempting to log in
        // after their account is locked due to too many failed login attempts.
        'cooldownDuration' => 'PT5M', // five mins

        // Max file upload size
	    'maxUploadFileSize' => 5242880, // 5mb

        // The amount of time a user verification code can be used before expiring.
        'verificationCodeDuration' => 'P1D', // one day

        // The URI that users without access to the Control Panel should be
        // redirected to after verifying a new email address.
        'verifyEmailSuccessPath' => 'account',

        // Time to wait before purging pending accounts.
        'purgePendingUsersDuration' => 'P3M', // three months

        // Allow capitals in slugs
        'allowUppercaseInSlug' => true,
    ],

    // Dev environment settings
    'dev' => [
        // {% if craft.app.config.general.custom.production %} ... {% endif %}
        'custom' => array(
            'beta' => false, // whether to show Beta features
            'production' => false, // whether to use the production assets and files
        ),
    ],

    // Staging environment settings
    'staging' => [
    ],

    // Production environment settings
    'production' => [
    ],
];
