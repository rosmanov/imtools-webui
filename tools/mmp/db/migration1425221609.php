<?php

class Migration1425221609 extends AbstractMigration
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
      "DROP TABLE IF EXISTS `thumbs`",
      "CREATE TABLE `thumbs` (\n"
      . "  `image_id` int(10) unsigned NOT NULL DEFAULT '0',\n"
      . "  `format_id` int(10) unsigned NOT NULL DEFAULT '0',\n"
      . "  `filename` varchar(255) NOT NULL DEFAULT '',\n"
      . "  PRIMARY KEY (`image_id`)\n"
      . ") ENGINE=InnoDB DEFAULT CHARSET=utf8",
      "ALTER TABLE `albums` ADD `modified` timestamp NOT NULL DEFAULT 'CURRENT_TIMESTAMP'",
    );
  }

  protected function buildDown()
  {
    return array(
      "DROP TABLE IF EXISTS `thumbs`",
      "ALTER TABLE `albums` DROP `modified`",
    );
  }

  protected function getRev() { return 1425221609; }

}
