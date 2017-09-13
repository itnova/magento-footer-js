<?php

class Meanbee_Footerjs_Helper_Data extends Mage_Core_Helper_Abstract {

    // Regular expression that matches one or more script tags (including conditions but not comments)
    const REGEX_JS            = '#(\s*<!--(\[if[^\n]*>)?\s*(<script.*</script>)+\s*(<!\[endif\])?-->)|(\s*<script.*</script>)#isU';
    const REGEX_DOCUMENT_END  = '#</body>\s*</html>#isU';

    const XML_CONFIG_FOOTERJS_ENABLED = 'dev/js/meanbee_footer_js_enabled';
    const XML_CONFIG_FOOTERJS_EXCLUDED_BLOCKS = 'dev/js/meanbee_footer_js_excluded_blocks';
    const XML_CONFIG_FOOTERJS_EXCLUDED_FILES = 'dev/js/meanbee_footer_js_excluded_files';

    const EXCLUDE_FLAG = 'data-footer-js-skip="true"';
    const EXCLUDE_FLAG_PATTERN = 'data-footer-js-skip';

    const PAGESPEED_NO_DEFER_FLAG = 'pagespeed_no_defer';

    /** @var array */
    protected $_blocksToExclude;

    /** @var string */
    protected $skippedFilesRegex;

    /**
     * @param null $store
     *
     * @return bool
     */
    public function isEnabled($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_CONFIG_FOOTERJS_ENABLED, $store);
    }

    public function removeJs($html)
    {
        $patterns = array(
            'js'             => self::REGEX_JS
        );

        foreach($patterns as $pattern) {
            $matches = array();

            $success = preg_match_all($pattern, $html, $matches);
            if ($success) {
                foreach ($matches[0] as $key => $js) {
                    if ($this->_excludeFromFooter($js)) {
                        // Excluded, so remove the js block from the matches.
                        unset($matches[0][$key]);
                    }
                }
                $html = str_replace($matches[0], '', $html);
            }
        }

        return $html;
    }

    public function moveJsToEnd($html)
    {
        $patterns = array(
            'js'             => self::REGEX_JS,
            'document_end'   => self::REGEX_DOCUMENT_END
        );

        foreach($patterns as $pattern) {
            $matches = array();

            $addDeferFlag = array();
            $success = preg_match_all($pattern, $html, $matches);
            if ($success) {
                // Strip excluded files
                if ($this->getSkippedFilesRegex() !== false) {
                    $matches[0] = preg_grep($this->getSkippedFilesRegex(), $matches[0], PREG_GREP_INVERT);
                }
                foreach ($matches[0] as $key => $js) {
                    if ($this->_excludeFromFooter($js)) {
                        // Excluded, so remove the js block from the matches.
                        unset($matches[0][$key]);
					} else if (strpos($js, ' defer') === false && strpos($js, ' src') !== false) {
                        // Move to footer, mark this js block to be marked with the `defer` flag.
                        $addDeferFlag[] = $key;
                    }
                }

                // Remove all js blocks that will be added to the footer.
                $html = str_replace($matches[0], '', $html);

                // Mark the js blocks as `defered`.
                foreach ($addDeferFlag as $key) {
                    $matches[0][$key] = str_replace("<script", "<script defer", $matches[0][$key]);
                }

                // Combine all matches that will be added to the footer.
                $text = implode('', $matches[0]);

                // The html with the removed js blocks + the js footer blocks.
                $html = $html . $text;
            }
        }

        return $html;
    }

    /**
     * Check if we need to exclude the given js block.
     * Look for `Meanbee` and `pagespeed` exclude flags.
     *
     * @param $js
     * @return boolean
     */
    protected function _excludeFromFooter($js)
    {
        return strpos($js, self::EXCLUDE_FLAG_PATTERN) !== false ||
               strpos($js, self::PAGESPEED_NO_DEFER_FLAG) !== false;
    }

    public function getSkippedFilesRegex()
    {
        if ($this->skippedFilesRegex === null) {
            $skipConfig = trim(Mage::getStoreConfig(self::XML_CONFIG_FOOTERJS_EXCLUDED_FILES));
            if ($skipConfig !== '') {
                $skippedFiles = preg_replace('/\s*,\s*/', '|', $skipConfig);
                $this->skippedFilesRegex = sprintf("@src=.*?(%s)@", $skippedFiles);
            } else {
                $this->skippedFilesRegex = false;
            }
        }
        return $this->skippedFilesRegex;
    }

    /**
     * Add skip flag to all js in given html
     *
     * @param string $html
     * @return string
     */
    public function addJsToExclusion($html)
    {
        return str_replace('<script', '<script ' . self::EXCLUDE_FLAG, $html);
    }

    /**
     * Get list of block names (in layout) to exclude their JS from moving to footer
     *
     * @return array
     */
    public function getBlocksToSkipMoveJs()
    {
        if (is_null($this->_blocksToExclude)) {
            $string = Mage::getStoreConfig(self::XML_CONFIG_FOOTERJS_EXCLUDED_BLOCKS);
            $exludedBlocks = explode(',', $string);
            foreach ($exludedBlocks as $key => $blockName) {
                $exludedBlocks[$key] = trim($blockName);
                if (strpos($exludedBlocks[$key], "\n") || strpos($exludedBlocks[$key], ' ')) {
                    Mage::log('Missing comma in setting "' . self::XML_CONFIG_FOOTERJS_EXCLUDED_BLOCKS . '"', Zend_Log::ERR, null, true);
                }
            }
            $this->_blocksToExclude = array_filter($exludedBlocks);
        }
        return $this->_blocksToExclude;
    }
}
