<?php

namespace modules\fields;

use Craft;
use craft\base\ElementInterface;
use craft\elements\db\ElementQuery;

class Users extends \craft\fields\Users
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('app', 'Users (count)');
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        /** @var ElementQuery $value */
        return '<span class="">Count: '.$value->count().'</span>';
    }
}
