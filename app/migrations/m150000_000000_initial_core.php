<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\appFarm\migrations;

class m150000_000000_initial_core extends \canis\db\Migration
{
    public function up()
    {
        $this->db->createCommand()->checkIntegrity(false)->execute();
        // role
        $this->dropExistingTable('instance');

        $this->createTable('instance', [
            'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
            'application_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'name' => 'string DEFAULT NULL',
            'data' => 'blob DEFAULT NULL',
            'active' => 'bool NOT NULL DEFAULT 0',
            'initialized' => 'bool NOT NULL DEFAULT 0',
            'checked' => 'datetime DEFAULT NULL',
            'created' => 'datetime DEFAULT NULL',
            'modified' => 'datetime DEFAULT NULL'
        ]);

        $this->addForeignKey('instanceRegistry', 'instance', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('instanceApplication', 'instance', 'application_id', 'application', 'id', 'CASCADE', 'CASCADE');
        $this->db->createCommand()->checkIntegrity(true)->execute();

        // data_interface
        $this->dropExistingTable('application');
        $this->createTable('application', [
            'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
            'name' => 'string DEFAULT NULL',
            'system_id' => 'string NOT NULL',
            'created' => 'datetime DEFAULT NULL',
            'modified' => 'datetime DEFAULT NULL',
        ]);
        // $this->addPrimaryKey('dataInterfacePk', 'data_interface', 'id');
        $this->addForeignKey('applicationRegistry', 'application', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');

        return true;
    }

    public function down()
    {
        $this->db->createCommand()->checkIntegrity(false)->execute();

        // $this->dropExistingTable('storage');

        $this->db->createCommand()->checkIntegrity(true)->execute();

        return true;
    }
}
