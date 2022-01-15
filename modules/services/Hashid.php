<?php

/**
 * This has been instantiated in the Module.php file
 * Use like:
 * Module::$instance->hashids->encode();
 * Module::$instance->hashids->decode();
 */

namespace modules\services;

use Hashids\Hashids;

use Craft;
use craft\base\Component;

class Hashid extends Component
{

    private function setup() {
        $hashKey = Craft::$app->config->general->securityKey;
        $hashLength = Craft::$app->config->general->custom['slugHash']['length'];
        $hashAlphabet = Craft::$app->config->general->custom['slugHash']['alphabet'];

        // Init hashids
        return new Hashids(
                $hashKey,
                $hashLength,
                $hashAlphabet
            );
    }

    /**
     * Hashs a given ID
     * @param int $id the ID to encode
     * @return string|false The hashed ID
     */
    public function encode(int $id)
    {
        $hashids = self::setup();

        // Encode entry ID
        return $hashids->encode($id);
    }// END encode

    /**
     * Converts a hash to an ID
     * @param string $hsh the Hash to decode
     * @return int|false The unhashed ID
     */
    public function decode(string $hash)
    {
        $hashids = self::setup();

        // Decode the hash
        return $hashids->decode($hash);
    }// END decode

}
