<?php

namespace dokuwiki\plugin\struct\meta;

/**
 * Class TranslationPluginCompat
 *
 * Provides compatibility with the Translation plugin
 *
 * @package dokuwiki\plugin\struct\meta
 */
class TranslationPluginCompat
{
    /**
     * Returns the root (untranslated) page id
     *
     * Stripes the translation namespace (if it exists)
     *
     * @param string $pid
     *
     * @return string
     */
    public static function getRootPageID($pid) {
        if(plugin_isdisabled('translation')) return $pid;

        $translation_helper = plugin_load('helper', 'translation');
        list($_, $pid) = $translation_helper->getTransParts($pid);
        return $pid;
    }

    /**
     * Invalidates all translated versions of a given page.
     *
     * @return
     */
    public static function invalidatePageCache($pid) {
        if(plugin_isdisabled('translation')) return;

        $translation_helper = plugin_load('helper', 'translation');
        $translation_helper->loadTranslationNamespaces();
        list($ln, $pid) = $translation_helper->getTransParts($pid);

        foreach ($translation_helper->getAvailableTranslations($pid) as $trans_pid) {
            p_set_metadata(
                $trans_pid,
                array('cache' => 'expire'),
                false,
                false
            );
        }
    }

    /**
     * Gets the currently-viewed language of the current page
     *
     * Determines the language by trying to read the translation namespace from the
     * current page id. If it can't find it, returns the provided default
     * 
     * @param string $default the default to return if language can't be determined
     * 
     * @return
     */

    public static function getCurrentLanguage(){
        global $INFO;
        $id = $INFO['id'];
        $translation_helper = plugin_load('helper', 'translation');

        if(!$id) return $conf['plugin']['struct']['fallback_language'];
        $translation = $translation_helper->getLangPart($id);
        if(!$translation) $translation = $conf['plugin']['struct']['fallback_language'];
        return $translation;
    }
}
