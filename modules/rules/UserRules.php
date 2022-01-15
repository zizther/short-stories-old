<?php
/**
 * Site module for Craft CMS 3.x
 *
 */

namespace modules\rules;

use modules\validators\UsernameValidator;

use Craft;
use craft\helpers\StringHelper;

class UserRules
{
    // Constants
    // =========================================================================

    const USERNAME_MIN_LENGTH = 5;
    const USERNAME_MAX_LENGTH = 15;
    const PASSWORD_MIN_LENGTH = 7;

    // Public Methods
    // =========================================================================

    /**
     * Return an array of Yii2 validator rules to be added to the User element
     * https://www.yiiframework.com/doc/guide/2.0/en/input-validation
     *
     * @return array
     */
    public static function define(): array
    {
        return [
            [
                'username',
                'string',
                'length' => [self::USERNAME_MIN_LENGTH, self::USERNAME_MAX_LENGTH],
                'tooLong' => 'Your username must be {max} characters or shorter.',
                'tooShort' => 'Your username must be {min} characters or longer.'
            ],
            [
                'username',
                'match',
                'pattern' => '/^[A-Za-z0-9_.]+$/',
                'message' => 'Your username can only contain alphanumeric, underscore or period characters.'
            ],
            [
                'username',
                UsernameValidator::class
            ],
            [
                'password',
                'string',
                'min' => self::PASSWORD_MIN_LENGTH,
                'tooShort' => 'Your password must be at least {min} characters.'
            ],
            [
                'password',
                'match',
                'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{7,})/',
                'message' => 'Your password must contain at least one of each of the following: A number, a lower-case character, an upper-case character, and a special character.'
            ],
        ];
    }
}
