<?php

/**
 * Use like:
 * use module\helpers\Toolbox;
 * Toolbox::renameAssetRandom();
 */

namespace modules\helpers;

use modules\Module;
use modules\helpers\LogToFile as LogToFileHelper;

use Craft;
use craft\helpers\StringHelper;


/**
 * Class Toolbox
 */
class Toolbox
{

    const BAD_WORDS = ['arsehole', 'assface', 'assfaces', 'asshole', 'assholes', 'bastard', 'bastards', 'beaner', 'bellend', 'bint', 'bitch', 'bitches', 'bitchy', 'blowjob', 'blump', 'blumpkin', 'bollocks', 'bollox', 'boner', 'bukkake', 'bullshit', 'bunghole', 'buttcheeks', 'butthole', 'buttpirate', 'buttplug', 'carpetmuncher', 'chinc', 'chink', 'choad', 'chode', 'circlejerk', 'clit', 'clunge', 'cock', 'cocksucker', 'cocksuckers', 'cocksucking', 'coochie', 'coochy', 'coon', 'cooter', 'cornhole', 'cum', 'cunnie', 'cunt', 'cunts', 'dago', 'dic', 'dick', 'dickhead', 'dickheads', 'dik', 'dike', 'dildo', 'doochbag', 'doosh', 'douche', 'douchebag', 'dumbass', 'dumbasses', 'dyke', 'fag', 'fagget', 'faggit', 'faggot', 'faggots', 'fagtard', 'fanny', 'feck', 'felch', 'feltch', 'figging', 'fingerbang', 'frotting', 'fuc', 'fuck', 'fucked', 'fuckedup', 'fucker', 'fuckers', 'fucking', 'fuckoff', 'fucks', 'fuckup', 'fudgepacker', 'fuk', 'fukker', 'fukkers', 'fuq', 'gangbang', 'gash', 'goddamn', 'goddamnit', 'gokkun', 'gooch', 'gook', 'guido', 'heeb', 'honkey', 'hooker', 'jackoff', 'jap', 'jerkoff', 'jigaboo', 'jiggerboo', 'jizz', 'junglebunny', 'kike', 'knobbing', 'kooch', 'kootch', 'kraut', 'kyke', 'lesbo', 'lezzie', 'milf', 'mindfuck', 'minge', 'motherfucker', 'motherfuckers', 'motherfucking', 'muff', 'muffdiver', 'muffdiving', 'munging', 'munter', 'ngga', 'niga', 'nigga', 'nigger', 'niggers', 'niglet', 'nigr', 'paki', 'panooch', 'pecker', 'peckerhead', 'pillock', 'piss', 'pissed', 'pollock', 'poon', 'poonani', 'poonany', 'poontang', 'porchmonkey', 'prick', 'punani', 'punanny', 'punany', 'pussie', 'pussies', 'pussy', 'puta', 'puto', 'quim', 'raghead', 'ruski', 'schlong', 'scrote', 'shag', 'shit', 'shite', 'shithead', 'shitheads', 'shits', 'shittier', 'shittiest', 'shitting', 'shitty', 'skank', 'skeet', 'slag', 'slanteye', 'slut', 'smartass', 'smartasses', 'smeg', 'snatch', 'spic', 'spick', 'splooge', 'spooge', 'teabagging', 'tit', 'tities', 'tits', 'titties', 'titty', 'tosser', 'towelhead', 'twat', 'vibrator', 'wank', 'wanker', 'wetback', 'whore', 'wiseass', 'wiseasses', 'wop'];

    /**
     * Rename the file
     * @param model $asset [the asset model to use]
     * @param model $folder [the folder model to upload to]
     */
    public static function renameAssetRandom($asset, $folder)
    {
        // File extension
        $extension = $asset->getExtension();

        // Asset ID
        $id = $asset->id;

        // Hash the asset ID
        $assetIdHash = Module::$instance->hashid->encode($id);

        // Generate random string
        //StringHelper::randomStringWithChars('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890_', 36);

        // New file name
        $newfileName = $assetIdHash . '.' . $extension;

        // Update file title
        $asset->title = $assetIdHash;

        // Save asset
        Craft::$app->assets->moveAsset($asset, $folder, $newfileName);
    }

    /**
     * Check the passed in $text for any BAD_WORDS
     *
     * @param  string $text [the text to check]
     *
     * @return array        [array of bad words found]
     */
    public static function badWordCheck(string $text): array
    {
        $matches = [];

        $matchFound = preg_match_all(
            "/\b(" . implode("|", self::BAD_WORDS) . ")\b/i",
            $text,
            $matches
        );

        return $matches;
    }
}
