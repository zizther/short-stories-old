<?php
namespace modules;

use yii\helpers\Inflector;

use modules\helpers\Toolbox as ToolboxHelper;
use modules\services\Stories as StoriesService;

use Craft;
use craft\db\Query;
use craft\elements\Entry;
use craft\helpers\StringHelper;
use craft\helpers\Template;

class TwigExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    public function getFunctions()
    {
        return [
            new \Twig_Function('readTime', [$this, 'readTimeFunction']),
            new \Twig_Function('getPopularRelations', [$this, 'getPopularRelationsFunction']),
        ];
    }

    //Module::$instance->myService->myMethod();
    public function getFilters()
    {
        return [
            new \Twig_Filter('truncate', [StringHelper::class, 'safeTruncate']),
            new \Twig_Filter('camel2words', [Inflector::class, 'camel2words']),
            new \Twig_Filter('editor', [Module::$instance->stories, 'editorToMarkup']),
            new \Twig_Filter('slugHashDecode', [Module::$instance->hashid, 'decode']),
            new \Twig_Filter('storyExcerpt', [StoriesService::class, 'generateExcerpt']),
            new \Twig_Filter('widno', [$this, 'widnoFilter']),
            new \Twig_Filter('rv', [$this, 'rvFilter']),
            new \Twig_Filter('qo', [$this, 'qoFilter']),
            new \Twig_Filter('readTime', [$this, 'readTimeFilter']),
            new \Twig_Filter('readTimeSearch', [$this, 'readTimeSearchFilter']),
            new \Twig_Filter('unitPrefix', [$this, 'unitPrefixFilter']),
            new \Twig_Filter('isCurrentAuthor', [$this, 'isCurrentAuthorFilter']),
            new \Twig_Filter('wordCount', [$this, 'wordCountFilter']),
        ];
    }

    public function getGlobals()
    {
        return [
            'foo' => 'bar',
        ];
    }

    ////////////////////////////////////////////////////////////////////////////
    // Functions
    ////////////////////////////////////////////////////////////////////////////
    /**
     * Decode the entry hash to reveal the entry ID
     * @param  [string] $hash [The hash to decode ]
     * @return [int]       [id of the entry]
     */
    public function slugHashDecodeFilter($hash) {

        // Init Hashids
        //$hashids = new Hashids(Craft::$app->config->general->securityKey, Craft::$app->config->general->custom['slugHash']['length'], Craft::$app->config->general->custom['slugHash']['alphabet']);

        $idHash = ToolboxHelper::hashEncode($hash);

        return $hashids->decode($hash);

    }// END slugHashDecodeFilter


    /**
     * Stops widows
     *
     * @param [string] $str [The string to add the &nbsp; to]
     * @return [string] [The updated string]
     */
    public function widnoFilter($str)
    {

        $str = preg_replace( '|([^\s])\s+([^\s]+)\s*$|', '$1&nbsp;$2', $str);

        return Template::raw($str);

    }// END widnoFilter

    /**
     * Replace double quote `"` with `\"` for JSON encoding
     * @param  [string] $string [the string to replace the double quotes in]
     * @return [string]         [the string with the replaced doubled quotes]
     */
    public function qoFilter($string)
    {
        return str_replace('"', '\"', $string);
    }// END qoFilter

    /**
     * EditorJS output. Converts the JSON to HTML
     * @param  [json] $json [encoded JSON to read through]
     * @return [string]       [outputs the editorJS content as HTML.]
     */
    public function editorFilter($json)
    {
        return $this->_editorToMarkup($json);
    }// END editorFilter

    /**
     * Ouput the excerpt based on the X number of chars from the start of the story
     * @param  [json] $json [encoded JSON to read through]
     * @return [string]       [outputs the editorJS content as HTML]
     */
    public function excerptFilter($json)
    {
        $content = StoriesService::editorToMarkup($json); //$this->_editorToMarkup($json);

        $cleanContent = strip_tags($content, '<br>'); // Strip HTML, apart from line breaks
        $cleanContent = str_replace('<br>', ' ', $cleanContent); // Replace line breaks with space

        // Convert all HTML entities to their applicable characters.
        $cleanContent = StringHelper::htmlDecode($cleanContent);

        // Truncate the string
        $excerptContent = StringHelper::safeTruncate($cleanContent, 160, '...', false);

        return $excerptContent;
    }// END excerptFilter

    /**
     * Replace Variable filter
     * Can match general or config variables and global fields
     * E.G.
     * {siteName}
     * {config.general.custom.variableName}
     * {config.customConfigFile.variableName}
     * {globalHandle.fieldHandle}
     *
     * @param [string] $string [The string to test against]
     * @return [string] [The updated string]
     */
    public function rvFilter($string)
    {
        // Regex to get keys within `{}`
        $regex = '/( { ( (?: [^{}]* | (?1) )* ) } )/x';

        $keys = [];
        $replace = [];

        // Put all matches into the $matches array
        preg_match_all($regex, $string, $keys);

        foreach ($keys[0] as $value) {
            $var = substr($value, 1, -1);
            $var = explode('.', $var);

            if ($var[0] == 'siteName') {
                array_push($replace, Craft::$app->sites->currentSite->name);
            }
            else {
                $configVar = $var[0] == 'config';
                $var1 = $var[1] ?? null;
                $isGlobal = !$configVar && $var1;

                if ($isGlobal) {
                    array_push($replace, $this->parseGlobal($var[0], $var[1]));
                }
                else {
                    if ($configVar) {
                        // Custom general config var
                        if ($var[1] == 'general' && $var[2] == 'custom'){
                            $cleanedVar = str_replace('"', '', $var[3]); // Remove quotes from array item
                            array_push($replace, Craft::$app->config->general->custom->$cleanedVar);
                        }
                        else {
                            $cleanedVar1 = str_replace('"', '', $var[1]); // Remove quotes from array item
                            $cleanedVar2 = str_replace('"', '', $var[2]); // Remove quotes from array item
                            array_push($replace, Craft::$app->config->getConfigFromFile($cleanedVar1)[$cleanedVar2]);
                        }
                    }
                    else {
                        $cleanedVar = str_replace('"', '', $var[0]); // Remove quotes from array item
                        array_push($replace, Craft::$app->config->general->$cleanedVar);
                    }
                }
            }
        }

        return Template::raw(str_replace($keys[0], $replace, $string));
    }// END rvFilter

    public function parseGlobal($set, $field)
    {

        $value = Craft::$app->getGlobals()->getSetByHandle($set)[$field];

        return $value;

    }// END parseGlobal


    // /**
    //  * [readTimeFunction description]
    //  * @param  [type] $element         [description]
    //  * @param  [type] $wordIndentifier [description]
    //  * @return [type]                  [description]
    //  */
    // public function readTimeFunction($element, $wordIndentifier = null)
    // {
    //     $totalSeconds = 0;
    //     $vals = '';
    //
    //     foreach ($element->getFieldLayout()->getFields() as $field) {
    //         try {
    //             // If field is a matrix then loop through fields in block
    //             if ($field instanceof craft\fields\Matrix) {
    //                 foreach($element->getFieldValue($field->handle)->all() as $block) {
    //                     $blockFields = $block->getFieldLayout()->getFields();
    //
    //                     foreach($blockFields as $blockField){
    //                         $value = $block->getFieldValue($blockField->handle);
    //                         $seconds = $this->_valToTime($value);
    //                         $totalSeconds = $totalSeconds + $seconds;
    //                     }
    //                 }
    //             } else {
    //               $value = $element->getFieldValue($field->handle);
    //               $seconds = $this->_valToTime($value);
    //               $totalSeconds = $totalSeconds + $seconds;
    //             }
    //         } catch (ErrorException $e) {
    //             continue;
    //         }
    //     }
    //
    //     return $this->_valToFormat($totalSeconds, $wordIndentifier);
    // }// END readTimeFunction


    /**
     * [readTimeFilter description]
     * @param  [string] $value - the string to calculate the read time of
     * @return [object] - the time object
     */
    public function readTimeFilter($string)
    {
        return $this->valToTime($string);
    }// END readTimeFilter


    /**
     * Read time search - makes the search param for searching by read time
     * @param  [int] $time [The search time]
     * @return [string] [The search query part for the read time]
     */
    public function readTimeSearchFilter($time)
    {
        if ($time >= 0 && $time <= 9) {
            $minReadTime = $time - 2;
            $maxReadTime = $time + 2;
        }
        elseif ($time >= 10 && $time <= 19) {
            $minReadTime = $time - 3;
            $maxReadTime = $time + 3;
        }
        elseif ($time >= 20 && $time <= 40) {
            $minReadTime = $time - 5;
            $maxReadTime = $time + 5;
        }
        else {
            $minReadTime = $time - 10;
            $maxReadTime = $time + 10;
        }

        return 'and, >= ' . $minReadTime . ', <= ' . $maxReadTime;
    }// END readTimeSearchFilter

    /**
     * Is the user logged in.
     * If so, is that user the author of the entry
     * @param  [int] $userID [The ID of the author to test against]
     * @return [boolean] [If the user is the author of the post]
     */
    public function isCurrentAuthorFilter($userID)
    {
        $isCurrentAuthor = false;

        $user = Craft::$app->getUser()->getIdentity();

        if ($user && $user->id == $userID) {
            $isCurrentAuthor = true;
        }

        return $isCurrentAuthor;
    }// END isCurrentAuthorFilter

    /**
     * Formats a number with unit prefixes.
     *
     * @param [float] $float [The float value to transform]
     * @param [mixed] $system [Either a string (e.g. "decimal") to use a predefined configuration or an array of custom settings]
     * @param [int] $decimals [The number of decimal points]
     * @param [bool] $trailingZeros [Whether to show trailing zeros]
     * @param [string] $decPoint [The separator for the decimal point]
     * @param [string] $thousandsSep [The thousands separator]
     * @param [string] $unitSep [The separator between number and unit]
     *
     * @return [string] The prefixed number
     */
    public function unitPrefixFilter($float, $system = 'decimal', $decimals = 1, $trailingZeros = false, $decPoint = '.', $thousandsSep = ',', $unitSep = '')
    {
        if (is_string($system)) {
            $system = $this->_getUnitPrefixSettings($system);
        }
        if (!array_key_exists('map', $system)) {
            return $float;
        }

        $base = array_key_exists('base', $system) ? $system['base'] : 10;
        $map = $system['map'];

        foreach ($map as $exp => $prefix) {
            if ($float >= pow($base, $exp)) {
                $number = $float / pow($base, $exp);
                $number = number_format($number, $decimals, $decPoint, $thousandsSep);

                if (!$trailingZeros) {
                    $number = $this->trimTrailingZeroes($number, $decPoint);
                }

                return $number.$unitSep.$prefix;
            }
        }

        return $float;
    }// END unitPrefixFilter


    /**
     * Word count - calculates the number of words
     * @param  [string] $string - the string to calculate against
     * @return [object] - word count
     */
    public function wordCountFilter($string)
    {
        $convertedString = StringHelper::toString($string); // Convert to string
        $convertedString = StringHelper::stripHtml($convertedString); // Strip HTML
        $wordCount = StringHelper::countWords($convertedString); // Count number of words

        return $wordCount;
    }// END wordCountFilter


    /**
     * Returns the most popular relations
     *
     * @param int $limit
     * @param string $fieldHandle
     * @param string $elementType // TODO: find solution to remove
     *
     * @return array
     */
    public function getPopularRelationsFunction($limit, $fieldHandle)
    {
    	$field = Craft::$app->fields->getFieldByHandle($fieldHandle);

    	if (!$field) {
    		throw new Exception("No field exists with handle {$fieldHandle}");
    	}

        $query = (new Query())
            ->select('targetId as id, COUNT(*) as count')
            ->from('craft_relations craft_relations')
            ->where('fieldId = :fieldId', [':fieldId' => $field->id])
            ->groupBy('targetId')
            ->orderBy('count DESC')
            ->limit($limit);

    	$results = $query->queryAll();

    	//$criteria = Craft::$app->elements->getCriteria($elementType);
        $criteria = Entry::find()
            ->all();


    	$criteria->id = array_column($results, 'id');
    	$criteria->indexBy = 'id';
    	$elements = $criteria->all();

    	return array_map(function($result) use ($elements) {
    		$elementId = $result['id'];

    		if (!in_array($elementId, array_keys($elements))) {
    			throw new Exception("Could not find an element with ID of {$elementId}");
    		}
    		return array_merge($result, [
    			'element' => $elements[$elementId],
    		]);
    	}, $results);
    }// END getPopularRelationsFunction


    ////////////////////////////////////////////////////////////////////////////
    // Protected functions
    ////////////////////////////////////////////////////////////////////////////
    /**
     * Trims trailing zeroes.
     *
     * @param [integer] $float
     * @param [string] $decPoint
     *
     * @return string
     */
    protected function trimTrailingZeroes($float, $decPoint = '.')
    {
        return strpos($float, $decPoint) !== false ? rtrim(rtrim($float, '0'), $decPoint) : $float;
    }// END trimTrailingZeroes




    ////////////////////////////////////////////////////////////////////////////
    // Private functions
    ////////////////////////////////////////////////////////////////////////////

    /**
     * EditorJS output. Converts the JSON to HTML
     * @param  [string] $json [the json data to process]
     * @return [string]       [the markup output]
     */
    private function _editorToMarkup($json)
    {
        $data = json_decode($json);
        $blocks = $data->blocks;

        $editorContent = '';

        foreach($blocks as $block) {
            if ($block->type == 'paragraph') {
                $editorContent .= '<p>' . $block->data->text . '</p>';
            }
            elseif ($block->type == 'header') {
                $editorContent .= '<h' . $block->data->level . '>' . $block->data->text . '</h' . $block->data->level . '>';
            }
            elseif ($block->type == 'quote') {
                $cite = $block->data->caption;
                $citeMarkup = $cite ? '<cite>' . $cite . '</cite>' : '';

                $editorContent .= '<blockquote class="blockquote--align-' . $block->data->alignment . '">' . $block->data->text . $citeMarkup . '</blockquote>';
            }
            elseif ($block->type == 'delimiter') {
                $editorContent .= '<div class="story__delimiter"></div>';
            }
        }

        return $editorContent;
    }// END editorToMarkup

    /**
     * Returns configuration settings for unit prefixes.
     *
     * @param [string] $preset
     *
     * @return array
     */
    private function _getUnitPrefixSettings($preset)
    {
        $settings = [];

        switch ($preset) {
            case 'names':
                $settings['map'] = [12 => 'trillion', 9 => 'billion', 6 => 'million', 3 => 'thousand', 2 => 'hundred', 0 => ''];
                break;
            case 'decimal':
            case 'decimalSymbol':
                $settings['map'] = [15 => 'P', 12 => 'T', 9 => 'G', 6 => 'M', 3 => 'k', 0 => '', -2 => 'c', -3 => 'm', -6 => 'µ', -9 => 'n'];
                break;
            case 'decimalNames':
                $settings['map'] = [15 => 'peta', 12 => 'tera', 9 => 'giga', 6 => 'mega', 3 => 'kilo', 0 => '', -2 => 'centi', -3 => 'milli', -6 => 'micro', -9 => 'nano'];
                break;
            case 'binary':
            case 'binarySymbol':
                $settings['base'] = 2;
                $settings['map'] = [50 => 'Pi', 40 => 'Ti', 30 => 'Gi', 20 => 'Mi', 10 => 'Ki', 0 => ''];
                break;
            case 'binaryNames':
                $settings['base'] = 2;
                $settings['map'] = [50 => 'pebi', 40 => 'tebi', 30 => 'gibi', 20 => 'mebi', 10 => 'kibi', 0 => ''];
                break;
            }

        return $settings;
    }// END _getUnitPrefixSettings

    /**
     * Truncate string
     * Remove all HTML from the string
     * @param  [string]  $string  [the text to truncate]
     * @param  [integer] $chars [The number of characters to limit to]
     * @return [string]         [the truncated string]
     */
    private function _truncate($string, $chars = 25) {
        $cleanString = strip_tags($string);

        if (strlen($cleanString) <= $chars) {
            return $cleanString;
        }

        $cleanString = $cleanString . ' ';
        $cleanString = substr($cleanString, 0, $chars);
        $cleanString = substr($cleanString, 0, strrpos($cleanString, ' '));
        $cleanString = $cleanString . '...';

        return $cleanString;
    }// END truncate

    /**
     * Value to time - calculates the time it take read a string
     * @param  [string] $string - the string to calculate against
     * @return [object] - the time object
     */
    private function _valToTime($string)
    {
        $wpm = 200; //  AVG words per minute read

        $convertedString = StringHelper::toString($string); // Convert to string
        $convertedString = StringHelper::stripHtml($convertedString); // Strip HTML
        $wordCount = StringHelper::countWords($convertedString); // Count number of words

        $minutes = $wordCount / $wpm; // Calculate minutes

        return $minutes;
    }// END _valToTime


    /**
     * [valToFormat]
     * @param  [type] $seconds         [description]
     * @return [type]                  [description]
     */
    private function _valToFormat($value)
    {

        // If less than 1 minute
        if ($value < 1) {
			// Test if identifier
			$readTime = $wordIndentifier ? '< 1 '.$wordIndentifier : $value;
        }
        else {
            // Round minutes up
            $resultTime = ceil($value);

			if ($wordIndentifier) {
	            $txt = $resultTime == 1 ? $wordIndentifier : $wordIndentifier.'s';
	            $readTime = $resultTime.' '.$txt;
			}
			else {
				$readTime = $resultTime;
			}
        }

        return $readTime;
    }// END _valToFormat

}
