<?php

function xmldb_block_odsoaipmh_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    $result = TRUE;

    if ($oldversion < 2014051215) {
        $field = array();

         // Define table block_odsoaipmh to be created.
        $table = new xmldb_table('block_odsoaipmh');

        // Adding fields to table block_odsoaipmh.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('uniq_id', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('type', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('moodle_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('deleted', XMLDB_TYPE_CHAR, '1', null, XMLDB_NOTNULL, null, null);
        $table->add_field('title', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('description', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('language', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('uri', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('license_en', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('license_local', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('taxonomy', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('datestamp', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL, null, null);

        // Adding keys to table block_odsoaipmh.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch drop / create table (since it contains only cached values should not be great loss)
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
        $dbman->create_table($table);

        // Odsoaipmh savepoint reached.
        upgrade_block_savepoint(true, 2014051215, 'odsoaipmh');
    }

    return $result;
}
?>