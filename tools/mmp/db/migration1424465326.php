<?php

class Migration1424465326 extends AbstractMigration
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
      "DROP TABLE IF EXISTS `images`",
      "CREATE TABLE `images` (\n"
      . "  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,\n"
      . "  `album_id` int(10) unsigned NOT NULL DEFAULT '0',\n"
      . "  `name` varchar(255) NOT NULL DEFAULT '',\n"
      . "  `filename` varchar(255) NOT NULL DEFAULT '',\n"
      . "  `filename_hash` char(40) DEFAULT NULL COMMENT 'sha1 of filename',\n"
      . "  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n"
      . "  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',\n"
      . "  PRIMARY KEY (`id`),\n"
      . "  KEY `filename_hash` (`filename_hash`)\n"
      . ") ENGINE=InnoDB DEFAULT CHARSET=utf8",
      "ALTER TABLE `albums` ADD `modified` timestamp NOT NULL DEFAULT 'CURRENT_TIMESTAMP'",
      "ALTER TABLE `albums` ADD `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'",
    );
  }

  protected function buildDown()
  {
    return array(
      "DROP TABLE IF EXISTS `images`",
      "ALTER TABLE `albums` DROP `modified`",
      "ALTER TABLE `albums` DROP `created`",
    );
  }

  protected function getRev() { return 1424465326; }

}
