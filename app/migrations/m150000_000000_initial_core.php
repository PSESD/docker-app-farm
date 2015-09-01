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
        // instance
        $this->dropExistingTable('instance');
        $this->createTable('instance', [
            'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
            'application_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL',
            'name' => 'string DEFAULT NULL',
            'data' => 'longblob DEFAULT NULL',
            'active' => 'bool NOT NULL DEFAULT 0',
            'initialized' => 'bool NOT NULL DEFAULT 0',
            'checked' => 'datetime DEFAULT NULL',
            'created' => 'datetime DEFAULT NULL',
            'modified' => 'datetime DEFAULT NULL',
            'terminated' => 'datetime DEFAULT NULL'
        ]);

        $this->addForeignKey('instanceRegistry', 'instance', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('instanceApplication', 'instance', 'application_id', 'application', 'id', 'CASCADE', 'CASCADE');


        // backups
        $this->dropExistingTable('backup');
        $this->createTable('backup', [
            'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
            'instance_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL',
            'local_storage_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL',
            'cloud_storage_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL',
            'data' => 'longblob DEFAULT NULL',
            'created' => 'datetime DEFAULT NULL'
        ]);

        $this->addForeignKey('backupRegistry', 'backup', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('backupInstance', 'backup', 'instance_id', 'instance', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('backupCloudStorage', 'backup', 'cloud_storage_id', 'storage', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('backupLocalStorage', 'backup', 'local_storage_id', 'storage', 'id', 'SET NULL', 'CASCADE');

        // application
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

        // certificates
        $this->dropExistingTable('certificate');
        $this->createTable('certificate', [
            'id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL PRIMARY KEY',
            'crt_storage_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL',
            'key_storage_id' => 'char(36) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL',
            'name' => 'string DEFAULT NULL',
            'is_wildcard' => 'bool NOT NULL DEFAULT 0',
            'expires' => 'datetime NOT NULL',
            'created' => 'datetime DEFAULT NULL',
            'modified' => 'datetime DEFAULT NULL',
        ]);
        // $this->addPrimaryKey('dataInterfacePk', 'data_interface', 'id');
        $this->addForeignKey('certificateRegistry', 'certificate', 'id', 'registry', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('certificateKeyStorage', 'certificate', 'key_storage_id', 'storage', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('certificateCrtStorage', 'certificate', 'crt_storage_id', 'storage', 'id', 'SET NULL', 'CASCADE');
        $this->db->createCommand()->checkIntegrity(true)->execute();

        return true;
    }

    public function down()
    {
        $this->db->createCommand()->checkIntegrity(false)->execute();

        $this->dropExistingTable('instance');
        $this->dropExistingTable('application');
        $this->dropExistingTable('backup');

        $this->db->createCommand()->checkIntegrity(true)->execute();

        return true;
    }
}
