<?php

namespace dokuwiki\plugin\struct\types;
use dokuwiki\plugin\struct\meta\TranslationUtilities;
use dokuwiki\plugin\struct\meta\TranslationPluginCompat;

class Dropdown extends AbstractBaseType
{
    use TranslationUtilities;

    public function __construct($config = null, $label = '', $ismulti = false, $tid = 0)
    {
        global $conf;

        $this->initTransConfig(['values']);
        $this->config['values'][$conf['plugin']['struct']['fallback_language']] = 'one, two, three';
        // If needed, migrate the stored value to new format.
        if(isset($config['values']) && is_string($config['values'])){
            $config['values'] = [
                $conf['plugin']['struct']['fallback_language'] => $config['values']
            ];
        }
        parent::__construct($config, $label, $ismulti, $tid);
    }

    protected $config = [
        'values' => array()
    ];

    /**
     * Creates the options array
     *
     * @return array
     */
    protected function getOptions($fallback = false)
    {
        global $conf;
        $language = TranslationPluginCompat::getCurrentLanguage();
        $this_lang_config = $this->config['values'][$fallback ? $conf['plugin']['struct']['fallback_language'] : $language];
        if(!$fallback && (!isset($this_lang_config) || $this_lang_config === '')){
            return $this->getOptions(true);
        }
        $options = explode(',', $this_lang_config);
        $options = array_map('trim', $options);
        $options = array_filter($options);
        array_unshift($options, '');
        return $options;
    }

    /**
     * This is called when a single string is needed to represent this Type's current
     * value as a single (non-HTML) string. Eg. in a dropdown or in autocompletion.
     *
     * @param string $value
     * @return string
     */
    public function displayValue($value)
    {
        global $conf;

        $opts = $this->getOptions();
        $raw = $this->rawValue($value);
        if(!isset($opts[$raw])) {
            $opts = $this->config['values'][$conf['plugin']['struct']['fallback_language']];
        }
        if(!isset($opts[$raw])) {
            return $raw;
        }
        return $opts[$raw];
    }

    /**
     * A Dropdown with a single value to pick
     *
     * @param string $name
     * @param string $rawvalue
     * @return string
     */
    public function valueEditor($name, $rawvalue, $htmlID)
    {
        $params = [
            'name' => $name,
            'class' => 'struct_' . strtolower($this->getClass()),
            'id' => $htmlID
        ];
        $attributes = buildAttributes($params, true);
        $html = "<select $attributes>";
        foreach ($this->getOptions() as $opt => $val) {
            if ($opt == $rawvalue || $val == $rawvalue) {
                $selected = 'selected="selected"';
            } else {
                $selected = '';
            }

            $html .= "<option $selected value=\"" . hsc($opt) . "\">" . hsc($val) . '</option>';
        }
        $html .= '</select>';

        return $html;
    }

    /**
     * A dropdown that allows to pick multiple values
     *
     * @param string $name
     * @param \string[] $rawvalues
     * @param string $htmlID
     *
     * @return string
     */
    public function multiValueEditor($name, $rawvalues, $htmlID)
    {
        $params = [
            'name' => $name . '[]',
            'class' => 'struct_' . strtolower($this->getClass()),
            'multiple' => 'multiple',
            'size' => '5',
            'id' => $htmlID
        ];
        $attributes = buildAttributes($params, true);
        $html = "<select $attributes>";
        foreach ($this->getOptions() as $raw => $opt) {
            if (in_array($raw, $rawvalues) || in_array($opt, $rawvalues)) {
                $selected = 'selected="selected"';
            } else {
                $selected = '';
            }

            $html .= "<option $selected value=\"" . hsc($raw) . "\">" . hsc($opt) . '</option>';
        }
        $html .= '</select> ';
        $html .= '<small>' . $this->getLang('multidropdown') . '</small>';
        return $html;
    }
}
