<?php
namespace modules;

use modules\fields\Entries;
use modules\fields\Users;
use modules\helpers\LogToFile as LogToFileHelper;
use modules\helpers\Toolbox as ToolboxHelper;
use modules\rules\UserRules;
use modules\services\Hashid as HashidService;
use modules\services\Stories as StoriesService;

use Craft;
use craft\elements\Asset;
use craft\events\DefineRulesEvent;
use craft\elements\Entry;
use craft\elements\User;
use craft\events\AssetEvent;
use craft\events\ModelEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\StringHelper;
use craft\services\Elements;
use craft\services\Fields;

use yii\base\Event;

/**
 * Custom module class.
 *
 * This class will be available throughout the system via:
 * `Craft::$app->getModule('my-module')`.
 *
 * You can change its module ID ("my-module") to something else from
 * config/app.php.
 *
 * If you want the module to get loaded on every request, uncomment this line
 * in config/app.php:
 *
 *     'bootstrap' => ['my-module']
 *
 * Learn more about Yii module development in Yii's documentation:
 * http://www.yiiframework.com/doc-2.0/guide-structure-modules.html
 */
class Module extends \yii\base\Module
{
    const STORIES_SECTION_ID = 4; // Stories section. Use sctionId over handle, as the handle can change
    const RENAME_ASSET_VOLUME_FOLDER_IDS = [2,3]; // Folder ID 2 = story covers & Folder ID 3 = profile images

    // Set $instance
    public static $instance;

    /**
     * Initializes the module.
     */
    public function init()
    {
        // Set a @modules alias pointed to the modules/ directory
        Craft::setAlias('@modules', __DIR__);

        // Set the controllerNamespace based on whether this is a console or web request
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->controllerNamespace = 'modules\\console\\controllers';
        } else {
            $this->controllerNamespace = 'modules\\controllers';
        }

        parent::init();

        // Register our components (services)
        // Allow us to use Module::$instance->myService->myMethod();
        self::$instance = $this;
        $this->setComponents([
            'hashid' => HashidService::class,
            'stories' => StoriesService::class,
        ]);

        // Custom initialization code goes here...
        Craft::$app->getView()->registerTwigExtension(new TwigExtension());

        // Register listeners
        $this->registerListeners();

    }// END init


    // =========================================================================
    // Public functions
    // =========================================================================
    /**
     * Registers the field types.
     *
     * @param RegisterComponentTypesEvent $event
     */
    public function handleRegisterFieldTypes(RegisterComponentTypesEvent $event)
    {
        $event->types[] = Entries::class;
        $event->types[] = Users::class;
    }// END handleRegisterFieldTypes


    // =========================================================================
    // Private functions
    // =========================================================================
    private function registerListeners()
    {
        // Add in our custom rules for the User element validation
        Event::on(
            User::class,
            User::EVENT_DEFINE_RULES,
            static function(DefineRulesEvent $event) {
                foreach(UserRules::define() as $rule) {
                    $event->rules[] = $rule;
                }
            }
        );

        // Fields - EVENT_REGISTER_FIELD_TYPES
        // User likes and follows
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            [$this, 'handleRegisterFieldTypes']
        );

        // Entry - EVENT_BEFORE_SAVE
        Event::on(
            Entry::class,
            Entry::EVENT_BEFORE_SAVE,
            function (ModelEvent $event) {
                $this->handleEventBeforeSave($event);
            }
        );

        // Elements - EVENT_AFTER_SAVE_ELEMENT
        Event::on(
            Elements::class,
            Elements::EVENT_AFTER_SAVE_ELEMENT,
            function(Event $event) {
                $this->handleEventAfterSaveElement($event);
            }
        );
    }

    /**
     * Handle to manage all the event before save functionality
     * @param  [type] $event [description]
     */
    private function handleEventBeforeSave($event)
    {
        $entry = $event->sender;

        // Check if there is an entry
        if ($entry !== null) {
            // Test for entry section
            switch ($entry->sectionId) {
                case self::STORIES_SECTION_ID:
                    // Story entries
                    Module::$instance->stories->slugifyTitle($entry);
                    Module::$instance->stories->checkStoryForProfanity($entry);
                break;
            }

            /**
             * Create lowercase slugs for all entries apart from those in the stories section
             */
            if ((int)$entry->sectionId !== self::STORIES_SECTION_ID) {
                if ($entry->title !== null) {
                    $entry->slug = StringHelper::slugify($entry->title);
                }
            }
        }
    }

    /**
     * [handleEventBeforeSave description]
     * @param  [type] $event [description]
     * @return [type]        [description]
     */
    private function handleEventAfterSaveElement($event)
    {
        // Get element
        $asset = $event->element;

        // Check for asset
        // Update story cover and profile image file names
        // Do this check first as none required asset fields with no image will return an error when checking for asset folder
        if ($asset !== null && $asset instanceof Asset) {
            // If the event isnew, is an asset.
            if ($event->isNew) {
                // Get folder
                $folder = $asset->getFolder();

                // Folder ID
                $folderId = $folder->id;

                // Test if asset is in story cover or profile image volumes
                if (in_array($folderId, self::RENAME_ASSET_VOLUME_FOLDER_IDS)) {
                    ToolboxHelper::renameAssetRandom($asset, $folder);
                }
            }
        }
    }

}
