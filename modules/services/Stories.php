<?php

/**
 * This has been instantiated in the Module.php file
 * Use like:
 * Module::$instance->stories->slugifyTitle();
 * Module::$instance->stories->checkStoryForProfanity();
 */

namespace modules\services;

use modules\Module;
use modules\helpers\Toolbox as ToolboxHelper;

use Craft;
use craft\base\Component;
use craft\elements\Entry;
use craft\helpers\Json as JsonHelper;
use craft\helpers\StringHelper;

class Stories extends Component
{
    /**
     * Slugify title where we add a hash of the entry ID to the end of the slug
     * The hash will be used later to decode back to an ID and show the Entry
     *
     * @param  Entry  $entry [the entry model]
     */
    public function slugifyTitle(Entry $entry)
    {
        $title = $entry->title;
        if ($title !== null) {
            // Encode entry ID
            $idHash = Module::$instance->hashid->encode($entry->id);

            // Create slug form title
            $titlePart = StringHelper::slugify($entry->title);

            // Update entry slug
            $entry->slug = $titlePart . '-' . $idHash;
        }
    }// END slugifyTitle

    /**
     * Check story for profanity
     *
     * loop through JSON content and update the lightswitch field `storyContainsBadWords` if so.
     * Also list the words which are rude in the field `storyContainsBadWordsContents`
     *
     * This creates an array of banned words, and uses a regular expression to find instances of these words:
     *
     * \b in the Regex indicates a word boundary (i.e. the beginning or end of a word, determined by either the beginning/end of the string or a non-word character). This is done to prevent "clbuttic" mistakes - i.e. you don't want to ban the word "banner" when you only want to match the word "ban".
     * The implode function creates a single string containing all your banned words, separated by a pipe character, which is the or operator in the Regex.
     * The implode portion of the Regex is surrounded with parentheses so that preg_match_all will capture the banned word as the match.
     * The i modifier at the end of the Regex indicates that the match should be case-sensitive - i.e. it will match each word regardless of capitalization - "Ban, "ban", and "BAN" will all match against the word "ban" in the $badWords array.
     * Next, the code checks if any matches were found. If there are, it uses array_unique to ensure only one instance of each word is reported, and then it outputs the list of matches in an unordered list.
     *
     * @param  Entry  $entry [the entry model]
     */
    public function checkStoryForProfanity(Entry $entry)
    {
        $data = JsonHelper::decodeIfJson($entry->storyContent, false);
        //$data = json_decode($entry->storyContent);

        if ($data !== null) {

            $blocks = $data->blocks;

            $editorContent = $entry->title . ' ';

            foreach($blocks as $block) {
                if ($block->type == 'paragraph') {
                    $editorContent .= strip_tags($block->data->text) . ' ';
                }
                elseif ($block->type == 'header') {
                    $editorContent .= strip_tags($block->data->text) . ' ';
                }
                elseif ($block->type == 'quote') {
                    $cite = $block->data->caption;
                    $citeMarkup = $cite ? ' ' . $cite : '';

                    $editorContent .= strip_tags($block->data->text) . strip_tags($citeMarkup) . ' ';
                }
            }

            // Strip any HTML from the block data
            //$editorContent = strip_tags($editorContent);

            // Match any bad words in the story
            $matches = ToolboxHelper::badWordCheck($editorContent);

            if (!empty($matches)) {
                // Set the `storyContainsBadWords` lightswitch field to on
                $entry->setFieldValue('storyContainsBadWords', 1);

                // Add list of bad words found to the `storyContainsBadWordsContents` field
                $words = array_unique($matches[0]);

                $badWordsInStory = '<ul>';
                foreach($words as $word) {
                    $badWordsInStory .= '<li>' . $word . '</li>';
                }
                $badWordsInStory .= '</ul>';

                // Put the content in the field
                $entry->setFieldValue('storyContainsBadWordsContents', $badWordsInStory);
            }
            else {
                // Set the `storyContainsBadWords` lightswitch field to off
                $entry->setFieldValue('storyContainsBadWords', 0);

                // Empty the list of bad words
                $entry->setFieldValue('storyContainsBadWordsContents', '');
            }
        }// END if
    }// END checkStoryForProfanity

    /**
     * Converts the JSON string from EdiotrJS to HTML markup
     * @param string $json [the json to put into markup]
     * @return string The markup output
     */
    public function editorToMarkup($json)
    {
        $data = JsonHelper::decodeIfJson($json, false);
        //$data = json_decode($json);
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
     * Generate excerpt from story content
     * @param string $json [the json to put into markup]
     * @return string The markup output
     */
    public function generateExcerpt($json)
    {
        $content = self::editorToMarkup($json); //$this->editorToMarkup($json);

        $cleanContent = strip_tags($content, '<br>'); // Strip HTML, apart from line breaks
        $cleanContent = str_replace('<br>', ' ', $cleanContent); // Replace line breaks with space

        // Convert all HTML entities to their applicable characters.
        $cleanContent = StringHelper::htmlDecode($cleanContent);

        // Truncate the string
        $excerptContent = StringHelper::safeTruncate($cleanContent, 160, '...', false);

        return $excerptContent;
    }// END generateExcerpt
}
