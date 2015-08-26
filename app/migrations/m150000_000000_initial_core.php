<?php
/**
 * @link http://canis.io
 *
 * @copyright Copyright (c) 2015 Canis
 * @license http://canis.io/license/
 */

namespace canis\wdf\migrations;

class m150000_000000_initial_core extends \canis\db\Migration
{
    public function up()
    {
        $this->db->createCommand()->checkIntegrity(false)->execute();

        $this->db->createCommand()->checkIntegrity(true)->execute();

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
