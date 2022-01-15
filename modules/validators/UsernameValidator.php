<?php

namespace modules\validators;

use Craft;
use craft\helpers\StringHelper;

use yii\validators\Validator;

/**
 * Class UsernameValidator.
 */
class UsernameValidator extends Validator
{
    /**
     * @inheritdoc
     */
    public function validateValue($value)
    {
        $username = StringHelper::toLowerCase($value);
        $usernameLength = mb_strlen($username);
        $usernameFirstChar = $username[0];
        $usernameLastChar = $username[$usernameLength-1];

        if (StringHelper::containsAny($username, ['shortstories', 'short_stories', 'short.stories', 'admin'])) {
            return ['Username can\'t contain these eserved words; shortstories, short_stories, short.stories or admin.', []];
        }
        else if ($usernameFirstChar === '.' || $usernameLastChar === '.') {
            // Username can't start or finish with a period
            // This also stop usernames being all periods
            return ['Your username can\'t start or finish with a period.', []];
        }
        else if (preg_match('/[.]{2,}/', $username)) {
            // Username can't have 2 or more periods inbetween characters
            // e.g. NOT ALLOWED `a..a` or `user..name....b`
            return ['Your username can\'t contain 2 or more periods after another.', []];
        }

        return null;
    }

}
