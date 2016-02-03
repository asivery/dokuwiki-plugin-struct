<?php
/**
 * DokuWiki Plugin struct (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr, Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

use plugin\struct\meta\SchemaData;

class action_plugin_struct_entry extends DokuWiki_Action_Plugin {


    /** @var helper_plugin_sqlite */
    protected $sqlite;

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {

       $controller->register_hook('HTML_EDITFORM_OUTPUT', 'BEFORE', $this, 'handle_editform');

    }

    /**
     * Enhance the editing form with structural data editing
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  [the parameters passed as fifth argument to register_hook() when this
     *                           handler was registered]
     * @return bool
     */
     public function handle_editform(Doku_Event $event, $param) {

        /** @var \helper_plugin_struct_db $helper */
        $helper = plugin_load('helper', 'struct_db');
        $this->sqlite = $helper->getDB();

        global $ID;

        $res = $this->sqlite->query("SELECT tbl FROM schema_assignments WHERE assign = ?",array($ID,));
        if (!$this->sqlite->res2count($res)) return false;

        $tables = array_map(function ($value){return $value['tbl'];},$this->sqlite->res2arr($res));


        foreach ($tables as $table) {
            $this->createForm($table, $event->data);
        }

        return true;
    }

    /**
     * Adds the form to edit schemadata
     *
     * @param string $tablename
     * @param Doku_Form $form The editor form
     */
    protected function createForm($tablename, $form) {
        global $ID;
        global $REV;
        $schema = new SchemaData($tablename, $ID, $REV);
        $schemadata = $schema->getData();

        $form->insertElement(4, "<h3>$tablename</h3>");

        $cols = $schema->getColumns(false);
        foreach ($cols as $index => $col) {
            $type = $col->getType();
            $label = $type->getLabel();
            $name = "Schema[$tablename][$label]";
            $input = $type->valueEditor($name, $schemadata[$label]);
            $element = "<label>$label $input</label><br />";
            $form->insertElement(5 + $index, $element);
        }
    }

}

// vim:ts=4:sw=4:et:
