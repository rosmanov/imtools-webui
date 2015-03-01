<?php

class Migration1425230281 extends AbstractMigration
{
  /**
   * @todo Return action which should run before db modification
   */
  protected function buildPreup() { return array(); }
  /**
   * @todo Return action which should run after db modification
   */
  protected function buildPostup() { return array(); }
  /**
   * @todo Return action which should run before db rollback
   */
  protected function buildPredown() { return array(); }
  /**
   * @todo Return action which should run after db rollback
   */
  protected function buildPostdown() { return array(); }

  protected function buildUp()
  {
    return array(
      "ALTER TABLE `albums` ADD `modified` timestamp NOT NULL DEFAULT 'CURRENT_TIMESTAMP'",
      "ALTER TABLE `albums` ADD `format_id` int(10) unsigned NOT NULL DEFAULT '1'",
    );
  }

  protected function buildDown()
  {
    return array(
      "ALTER TABLE `albums` DROP `modified`",
      "ALTER TABLE `albums` DROP `format_id`",
    );
  }

  protected function getRev() { return 1425230281; }

}
